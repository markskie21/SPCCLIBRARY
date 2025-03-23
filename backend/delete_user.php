<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'No data provided'
    ]);
    exit;
}

try {
    // Validate required fields
    if (!isset($data['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing user ID'
        ]);
        exit;
    }

    // Check if user exists and is not an admin
    $stmt = $conn->prepare("
        SELECT id, role 
        FROM users 
        WHERE id = :id
    ");
    $stmt->execute([':id' => $data['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }

    if ($user['role'] === 'admin') {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete admin users'
        ]);
        exit;
    }

    // Check if user has active borrowings
    $stmt = $conn->prepare("
        SELECT id 
        FROM borrowings 
        WHERE user_id = :id AND status = 'active'
    ");
    $stmt->execute([':id' => $data['id']]);
    
    if ($stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete user with active borrowings'
        ]);
        exit;
    }

    // Delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $data['id']]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 