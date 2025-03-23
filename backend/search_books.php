<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $query = isset($_GET['query']) ? $_GET['query'] : '';
    
    if (empty($query)) {
        throw new Exception('Search query is required');
    }

    $searchQuery = "%$query%";
    $sql = "SELECT * FROM books WHERE 
            title LIKE ? OR 
            author LIKE ? OR 
            isbn LIKE ? OR 
            genre LIKE ? 
            ORDER BY title";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $searchQuery, $searchQuery, $searchQuery, $searchQuery);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $books = array();
    while ($row = $result->fetch_assoc()) {
        $books[] = array(
            'bookID' => $row['bookID'],
            'title' => $row['title'],
            'author' => $row['author'],
            'isbn' => $row['isbn'],
            'available_copies' => $row['available_copies'],
            'location' => $row['location'],
            'genre' => $row['genre']
        );
    }
    
    echo json_encode(array(
        'success' => true,
        'books' => $books
    ));
    
} catch (Exception $e) {
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage()
    ));
}

$conn->close();
?> 