<?php
require_once 'config.php';

try {
    // First, check if the users table exists
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "Users table checked/created successfully\n";

    // Check if admin user exists
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        // Create admin user
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@spcc.edu', :password, 'admin')");
        $stmt->execute([':password' => $hashed_password]);
        echo "Admin user created successfully\n";
        echo "Username: admin\n";
        echo "Password: admin123\n";
    } else {
        echo "Admin user found:\n";
        echo "Username: " . $admin['username'] . "\n";
        echo "Role: " . $admin['role'] . "\n";
        
        // Reset password
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = :password WHERE username = 'admin'");
        $stmt->execute([':password' => $hashed_password]);
        echo "Admin password reset successfully\n";
        echo "New password: admin123\n";
    }

    // Verify the password
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = 'admin'");
    $stmt->execute();
    $stored_hash = $stmt->fetchColumn();
    
    if (password_verify('admin123', $stored_hash)) {
        echo "Password verification successful!\n";
    } else {
        echo "Password verification failed!\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 