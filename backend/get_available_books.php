<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Content-Type: application/json');

require_once 'config.php';

try {
    // SQL query with original column names
    $sql = "SELECT 
                bookID,
                bookname as title,
                author,
                ISBN as isbn,
                quantity as available_copies,
                location,
                genre
            FROM books 
            WHERE quantity > 0 
            ORDER BY bookname";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($books === false) {
        throw new Exception('Error fetching books');
    }
    
    echo json_encode([
        'success' => true,
        'books' => array_map(function($row) {
            return [
                'bookID' => $row['bookID'],
                'title' => $row['title'],
                'author' => $row['author'],
                'isbn' => $row['isbn'],
                'available_copies' => $row['available_copies'],
                'location' => $row['location'],
                'genre' => $row['genre']
            ];
        }, $books)
    ]);
    
} catch (Exception $e) {
    error_log('Error in get_available_books.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading available books: ' . $e->getMessage()
    ]);
}

// No need to explicitly close PDO connection
?>