<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Get JSON data from request body
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }

    // Validate required fields
    $requiredFields = ['maxBorrowDays', 'finePerDay', 'maxBooksPerUser'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Update settings
    $stmt = $conn->prepare("
        UPDATE settings 
        SET maxBorrowDays = :maxBorrowDays,
            finePerDay = :finePerDay,
            maxBooksPerUser = :maxBooksPerUser
        WHERE id = 1
    ");

    $stmt->execute([
        ':maxBorrowDays' => $data['maxBorrowDays'],
        ':finePerDay' => $data['finePerDay'],
        ':maxBooksPerUser' => $data['maxBooksPerUser']
    ]);

    if ($stmt->rowCount() === 0) {
        // If no settings exist, create new ones
        $stmt = $conn->prepare("
            INSERT INTO settings (maxBorrowDays, finePerDay, maxBooksPerUser)
            VALUES (:maxBorrowDays, :finePerDay, :maxBooksPerUser)
        ");
        
        $stmt->execute([
            ':maxBorrowDays' => $data['maxBorrowDays'],
            ':finePerDay' => $data['finePerDay'],
            ':maxBooksPerUser' => $data['maxBooksPerUser']
        ]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Settings updated successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 