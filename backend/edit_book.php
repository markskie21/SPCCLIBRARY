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
    $requiredFields = ['id', 'title', 'author', 'isbn', 'category', 'quantity', 'location'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            echo json_encode([
                'success' => false,
                'message' => "Missing required field: $field"
            ]);
            exit;
        }
    }

    // Check if book exists
    $stmt = $conn->prepare("SELECT id FROM books WHERE id = :id");
    $stmt->execute([':id' => $data['id']]);
    
    if (!$stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Book not found'
        ]);
        exit;
    }

    // Update the book
    $stmt = $conn->prepare("
        UPDATE books 
        SET title = :title,
            author = :author,
            isbn = :isbn,
            category = :category,
            quantity = :quantity,
            location = :location
        WHERE id = :id
    ");

    // Execute with parameters
    $stmt->execute([
        ':id' => $data['id'],
        ':title' => $data['title'],
        ':author' => $data['author'],
        ':isbn' => $data['isbn'],
        ':category' => $data['category'],
        ':quantity' => $data['quantity'],
        ':location' => $data['location']
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Book updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No changes made to the book'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 