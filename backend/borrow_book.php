<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['book_id']) || !isset($data['student_name']) || !isset($data['student_id'])) {
        throw new Exception('Missing required fields');
    }

    // Start transaction
    $conn->beginTransaction();

    // Get book details and verify availability
    $stmt = $conn->prepare("SELECT bookID, quantity FROM books WHERE bookID = :bookID");
    $stmt->execute([':bookID' => $data['book_id']]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        throw new Exception('Book not found');
    }

    if ($book['quantity'] <= 0) {
        throw new Exception('Book is not available for borrowing');
    }

    // Get settings
    $stmt = $conn->prepare("SELECT max_borrow_days FROM settings WHERE id = 1");
    $stmt->execute();
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    $maxBorrowDays = $settings ? $settings['max_borrow_days'] : 7;

    // Calculate dates
    $borrowDate = date('Y-m-d');
    $returnDate = date('Y-m-d', strtotime("+$maxBorrowDays days"));

    // Create a temporary user for the student
    $stmt = $conn->prepare("
        INSERT INTO users (username, email, password, role) 
        VALUES (:username, :email, :password, 'user')
        ON DUPLICATE KEY UPDATE userID = LAST_INSERT_ID(userID)
    ");
    
    $stmt->execute([
        ':username' => $data['student_id'],
        ':email' => $data['student_id'] . '@student.spcc.edu.ph',
        ':password' => password_hash($data['student_id'], PASSWORD_DEFAULT)
    ]);
    
    $userID = $conn->lastInsertId();

    // Insert into borrowed table with correct column names
    $stmt = $conn->prepare("
        INSERT INTO borrowed (
            userID,
            bookID,
            dateborrowed,
            datetoreturn,
            borrowstatus
        ) VALUES (
            :userID,
            :bookID,
            :dateborrowed,
            :datetoreturn,
            'Borrowed'
        )
    ");

    $stmt->execute([
        ':userID' => $userID,
        ':bookID' => $book['bookID'],
        ':dateborrowed' => $borrowDate,
        ':datetoreturn' => $returnDate
    ]);

    // Update book quantity
    $stmt = $conn->prepare("UPDATE books SET quantity = quantity - 1 WHERE bookID = :bookID");
    $stmt->execute([':bookID' => $data['book_id']]);

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Book borrowed successfully'
    ]);
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>