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

    // Check if user exists
    $stmt = $conn->prepare("SELECT id, role FROM users WHERE id = :id");
    $stmt->execute([':id' => $data['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }

    // Build update query based on provided fields
    $updateFields = [];
    $params = [':id' => $data['id']];

    if (isset($data['username'])) {
        // Check if username is already taken
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
        $stmt->execute([':username' => $data['username'], ':id' => $data['id']]);
        if ($stmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Username already exists'
            ]);
            exit;
        }
        $updateFields[] = "username = :username";
        $params[':username'] = $data['username'];
    }

    if (isset($data['email'])) {
        // Check if email is already taken
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
        $stmt->execute([':email' => $data['email'], ':id' => $data['id']]);
        if ($stmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Email already exists'
            ]);
            exit;
        }
        $updateFields[] = "email = :email";
        $params[':email'] = $data['email'];
    }

    if (isset($data['password'])) {
        $updateFields[] = "password = :password";
        $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    if (isset($data['role']) && $user['role'] !== 'admin') {
        $updateFields[] = "role = :role";
        $params[':role'] = $data['role'];
    }

    if (empty($updateFields)) {
        echo json_encode([
            'success' => false,
            'message' => 'No fields to update'
        ]);
        exit;
    }

    // Update the user
    $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'User updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No changes made to the user'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 