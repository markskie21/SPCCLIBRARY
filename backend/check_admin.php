<?php
require_once 'config.php';

try {
    // Check if admin user exists
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        // Create admin user if it doesn't exist
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@spcc.edu', :password, 'admin')");
        $stmt->execute([':password' => $hashed_password]);
        
        echo "Admin user created successfully!\n";
        echo "Username: admin\n";
        echo "Password: admin123\n";
    } else {
        echo "Admin user already exists:\n";
        echo "Username: " . $admin['username'] . "\n";
        echo "Role: " . $admin['role'] . "\n";
        
        // Test password verification
        if (password_verify('admin123', $admin['password'])) {
            echo "Password verification successful!\n";
        } else {
            echo "Password verification failed! Resetting password...\n";
            
            // Reset password
            $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = :password WHERE username = 'admin'");
            $stmt->execute([':password' => $hashed_password]);
            echo "Password has been reset to: admin123\n";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 