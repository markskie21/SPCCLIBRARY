<?php
require_once 'config.php';

try {
    // Test database connection
    echo "Testing database connection...\n";
    $conn->query("SELECT 1");
    echo "Database connection successful!\n\n";

    // Check if users table exists
    echo "Checking users table...\n";
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->rowCount() > 0) {
        echo "Users table exists!\n";
    } else {
        echo "Users table does not exist!\n";
    }
    echo "\n";

    // Check admin user
    echo "Checking admin user...\n";
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "Admin user found:\n";
        echo "ID: " . $admin['id'] . "\n";
        echo "Username: " . $admin['username'] . "\n";
        echo "Email: " . $admin['email'] . "\n";
        echo "Role: " . $admin['role'] . "\n";
        echo "Password Hash: " . $admin['password'] . "\n";
        
        // Test password verification
        if (password_verify('admin123', $admin['password'])) {
            echo "\nPassword verification successful!\n";
        } else {
            echo "\nPassword verification failed!\n";
            echo "Resetting password...\n";
            
            // Reset password
            $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = :password WHERE username = 'admin'");
            $stmt->execute([':password' => $hashed_password]);
            echo "Password reset complete!\n";
        }
    } else {
        echo "Admin user not found!\n";
        echo "Creating admin user...\n";
        
        // Create admin user
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@spcc.edu', :password, 'admin')");
        $stmt->execute([':password' => $hashed_password]);
        echo "Admin user created successfully!\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 