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
    if (!isset($data['id']) || !isset($data['status'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields'
        ]);
        exit;
    }

    // Prepare SQL statement
    $stmt = $conn->prepare("
        UPDATE borrowings 
        SET status = :status, 
            return_date = CASE 
                WHEN :status = 'returned' THEN CURRENT_TIMESTAMP 
                ELSE return_date 
            END
        WHERE id = :id
    ");

    // Execute with parameters
    $stmt->execute([
        ':id' => $data['id'],
        ':status' => $data['status']
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Borrowing status updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Borrowing not found'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 