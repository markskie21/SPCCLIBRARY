<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

try {
    $sql = "SELECT 
                b.borrowID,
                bk.bookname,
                u.username as fullname,
                u.email as STN,
                b.dateborrowed,
                b.datetoreturn,
                b.datereturned,
                b.borrowstatus,
                b.fine
            FROM borrowed b
            JOIN users u ON b.userID = u.userID
            JOIN books bk ON b.bookID = bk.bookID
            ORDER BY b.dateborrowed DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'transactions' => $transactions
    ]);

} catch (PDOException $e) {
    error_log('Error in get_transaction_history.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading transaction history: ' . $e->getMessage()
    ]);
}
?>