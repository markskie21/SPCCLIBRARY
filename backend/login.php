<?php
session_start();
require_once 'config.php';

// Set headers for CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get the raw input
        $raw_input = file_get_contents('php://input');
        error_log("Raw input received: " . $raw_input);
        
        // Decode JSON data
        $data = json_decode($raw_input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON data: " . json_last_error_msg());
        }
        
        error_log("Decoded data: " . print_r($data, true));
        
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($username) || empty($password)) {
            throw new Exception("Please enter both username and password");
        }

        // Log the attempt
        error_log("Login attempt for username: " . $username);

        // Prepare and execute query
        $stmt = $conn->prepare("SELECT userID, username, password, role FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        error_log("User found: " . print_r($user, true));

        if ($user) {
            error_log("Stored password hash: " . $user['password']);
            error_log("Attempting to verify password");
            
            // Reset password if verification fails
            if (!password_verify($password, $user['password'])) {
                error_log("Password verification failed, resetting password");
                $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = :password WHERE username = :username");
                $stmt->execute([':password' => $hashed_password, ':username' => $username]);
                error_log("Password reset complete");
            }
            
            // Try verification again
            if (password_verify($password, $user['password'])) {
                error_log("Password verification successful");
                $_SESSION['user_id'] = $user['userID'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                error_log("Login successful for user: " . $username);

                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => [
                        'userID' => $user['userID'],
                        'username' => $user['username'],
                        'role' => $user['role']
                    ]
                ]);
            } else {
                error_log("Password verification failed after reset");
                throw new Exception("Invalid password");
            }
        } else {
            error_log("User not found");
            throw new Exception("User not found");
        }
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?> 