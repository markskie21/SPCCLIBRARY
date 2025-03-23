<?php
// Prevent any output before headers
ob_start();

// Enable error reporting but don't display errors
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set headers for JSON response and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Max-Age: 3600');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Clear any previous output
ob_clean();

try {
    // Check if config.php exists
    if (!file_exists('config.php')) {
        throw new Exception('config.php not found');
    }

    require_once 'config.php';

    // Check database connection
    if (!isset($conn) || !$conn) {
        throw new Exception('Database connection not established');
    }

    // Get total books
    $stmt = $conn->query("SELECT COUNT(*) as total FROM books");
    if (!$stmt) {
        throw new Exception('Failed to query books table');
    }
    $totalBooks = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get total users
    $stmt = $conn->query("SELECT COUNT(*) as total FROM users");
    if (!$stmt) {
        throw new Exception('Failed to query users table');
    }
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get active borrowings
    $stmt = $conn->query("SELECT COUNT(*) as total FROM borrowed WHERE borrowstatus = 'Borrowed'");
    if (!$stmt) {
        throw new Exception('Failed to query borrowed table');
    }
    $activeBorrowings = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        'success' => true,
        'totalBooks' => $totalBooks,
        'totalUsers' => $totalUsers,
        'activeBorrowings' => $activeBorrowings
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $e->getMessage()
    ]);
}

// End output buffering and send response
ob_end_flush();
?> 