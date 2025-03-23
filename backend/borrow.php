<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['bookID']) || !isset($data['userID'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Book ID and User ID are required'
        ]);
        exit;
    }

    // Check if book exists and is available
    $stmt = $conn->prepare("SELECT quantity, bookstatus FROM books WHERE bookID = :bookID");
    $stmt->execute([':bookID' => $data['bookID']]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        echo json_encode([
            'success' => false,
            'message' => 'Book not found'
        ]);
        exit;
    }

    if ($book['quantity'] <= 0 || $book['bookstatus'] !== 'Available') {
        echo json_encode([
            'success' => false,
            'message' => 'Book is not available for borrowing'
        ]);
        exit;
    }

    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE userID = :userID");
    $stmt->execute([':userID' => $data['userID']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }

    // Get settings for max borrow days
    $stmt = $conn->prepare("SELECT max_borrow_days FROM settings WHERE id = 1");
    $stmt->execute();
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    $maxBorrowDays = $settings ? $settings['max_borrow_days'] : 7;

    // Calculate return date
    $borrowDate = date('Y-m-d');
    $returnDate = date('Y-m-d', strtotime("+$maxBorrowDays days"));

    // Start transaction
    $conn->beginTransaction();

    try {
        // Insert borrow record
        $stmt = $conn->prepare("INSERT INTO borrowed (userID, bookID, dateborrowed, datetoreturn, borrowstatus) VALUES (:userID, :bookID, :dateborrowed, :datetoreturn, 'Borrowed')");
        $stmt->execute([
            ':userID' => $data['userID'],
            ':bookID' => $data['bookID'],
            ':dateborrowed' => $borrowDate,
            ':datetoreturn' => $returnDate
        ]);

        // Update book quantity and status
        $stmt = $conn->prepare("UPDATE books SET quantity = quantity - 1, bookstatus = CASE WHEN quantity - 1 = 0 THEN 'Not Available' ELSE 'Available' END WHERE bookID = :bookID");
        $stmt->execute([':bookID' => $data['bookID']]);

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Book borrowed successfully',
            'return_date' => $returnDate
        ]);

    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 