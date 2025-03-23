<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'No data provided'
    ]);
    exit;
}

try {
    // Validate required fields
    if (!isset($data['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing book ID'
        ]);
        exit;
    }

    // Check if book exists and is not borrowed
    $stmt = $conn->prepare("
        SELECT b.id 
        FROM books b 
        LEFT JOIN borrowings br ON b.id = br.book_id 
        WHERE b.id = :id AND br.id IS NULL
    ");
    $stmt->execute([':id' => $data['id']]);
    
    if (!$stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Book not found or is currently borrowed'
        ]);
        exit;
    }

    // Delete the book
    $stmt = $conn->prepare("DELETE FROM books WHERE id = :id");
    $stmt->execute([':id' => $data['id']]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Book deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Book not found'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 