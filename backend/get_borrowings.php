<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

try {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $userId = isset($_GET['userId']) ? trim($_GET['userId']) : '';
    
    $sql = "SELECT 
                b.borrowID,
                bk.bookname,
                u.username as fullname,
                u.email as STN,
                b.dateborrowed,
                b.datetoreturn,
                b.borrowstatus,
                b.fine
            FROM borrowed b
            JOIN users u ON b.userID = u.userID
            JOIN books bk ON b.bookID = bk.bookID
            WHERE b.borrowstatus = 'Borrowed'";
            
    if ($userId) {
        $sql .= " AND u.userID = :userId";
    }
    
    if ($search) {
        $sql .= " AND (bk.bookname LIKE :search OR u.username LIKE :search OR u.email LIKE :search)";
    }
    
    $sql .= " ORDER BY b.dateborrowed DESC";
            
    $stmt = $conn->prepare($sql);
    
    if ($userId) {
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    }
    
    if ($search) {
        $searchParam = "%$search%";
        $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $borrowings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'borrowings' => $borrowings
    ]);

} catch (PDOException $e) {
    error_log('Error in get_borrowings.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading borrowings: ' . $e->getMessage()
    ]);
}
?>