<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['borrowID'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Borrow ID is required'
        ]);
        exit;
    }

    // Check if borrow record exists and is not already returned
    $stmt = $conn->prepare("SELECT b.*, bk.bookname FROM borrowed b JOIN books bk ON b.bookID = bk.bookID WHERE b.borrowID = :borrowID AND b.borrowstatus = 'Borrowed'");
    $stmt->execute([':borrowID' => $data['borrowID']]);
    $borrow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$borrow) {
        echo json_encode([
            'success' => false,
            'message' => 'Borrow record not found or already returned'
        ]);
        exit;
    }

    // Calculate fine if returned late
    $returnDate = date('Y-m-d');
    $dueDate = $borrow['datetoreturn'];
    $fine = 0;

    if (strtotime($returnDate) > strtotime($dueDate)) {
        // Get fine per day from settings
        $stmt = $conn->prepare("SELECT fine_per_day FROM settings WHERE id = 1");
        $stmt->execute();
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        $finePerDay = $settings ? $settings['fine_per_day'] : 5;

        $daysLate = ceil((strtotime($returnDate) - strtotime($dueDate)) / (60 * 60 * 24));
        $fine = $daysLate * $finePerDay;
    }

    // Start transaction
    $conn->beginTransaction();

    try {
        // Update borrow record
        $stmt = $conn->prepare("UPDATE borrowed SET datereturned = :returnDate, borrowstatus = 'Returned', fine = :fine WHERE borrowID = :borrowID");
        $stmt->execute([
            ':returnDate' => $returnDate,
            ':fine' => $fine,
            ':borrowID' => $data['borrowID']
        ]);

        // Update book quantity and status
        $stmt = $conn->prepare("UPDATE books SET quantity = quantity + 1, bookstatus = 'Available' WHERE bookID = :bookID");
        $stmt->execute([':bookID' => $borrow['bookID']]);

        // Add to archive
        $stmt = $conn->prepare("INSERT INTO archive (userID, bookID, bookname, dateborrowed, datetoreturn, datereturned, fine) VALUES (:userID, :bookID, :bookname, :dateborrowed, :datetoreturn, :datereturned, :fine)");
        $stmt->execute([
            ':userID' => $borrow['userID'],
            ':bookID' => $borrow['bookID'],
            ':bookname' => $borrow['bookname'],
            ':dateborrowed' => $borrow['dateborrowed'],
            ':datetoreturn' => $borrow['datetoreturn'],
            ':datereturned' => $returnDate,
            ':fine' => $fine
        ]);

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Book returned successfully',
            'fine' => $fine
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