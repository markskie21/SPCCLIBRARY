<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    if (!empty($search)) {
        // Search query without borrowings table
        $stmt = $conn->prepare("
            SELECT 
                bookID,
                bookname as title,
                author,
                ISBN as isbn,
                quantity as available_copies,
                genre,
                publisher,
                location,
                school_level,
                bookstatus
            FROM books 
            WHERE bookname LIKE ? OR author LIKE ? OR ISBN LIKE ?
            ORDER BY bookname
        ");
        
        $searchTerm = "%$search%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    } else {
        // Regular query without search
        $stmt = $conn->prepare("
            SELECT 
                bookID,
                bookname as title,
                author,
                ISBN as isbn,
                quantity as available_copies,
                genre,
                publisher,
                location,
                school_level,
                bookstatus
            FROM books 
            ORDER BY bookname
        ");
        $stmt->execute();
    }

    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'books' => $books
    ]);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 