<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Get the raw input
    $raw_input = file_get_contents('php://input');
    $data = json_decode($raw_input, true);

    if (!$data) {
        throw new Exception("Invalid JSON data");
    }

    // Validate required fields
    $required_fields = ['title', 'author', 'isbn', 'quantity', 'genre', 'publisher', 'location', 'school_level'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Check if ISBN already exists
    $stmt = $conn->prepare("SELECT bookID FROM books WHERE ISBN = ?");
    $stmt->execute([$data['isbn']]);
    if ($stmt->rowCount() > 0) {
        throw new Exception("A book with this ISBN already exists");
    }

    // Insert the new book
    $stmt = $conn->prepare("
        INSERT INTO books (
            bookname, author, ISBN, quantity, genre, 
            publisher, location, school_level, bookstatus
        ) VALUES (
            ?, ?, ?, ?, ?, 
            ?, ?, ?, 'Available'
        )
    ");

    $stmt->execute([
        $data['title'],
        $data['author'],
        $data['isbn'],
        $data['quantity'],
        $data['genre'],
        $data['publisher'],
        $data['location'],
        $data['school_level']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Book added successfully'
    ]);

} catch (Exception $e) {
    error_log("Error adding book: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 