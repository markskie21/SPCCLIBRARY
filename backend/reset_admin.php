<?php
require_once 'config.php';

try {
    // First, delete the existing admin user if it exists
    $stmt = $conn->prepare("DELETE FROM users WHERE username = 'admin'");
    $stmt->execute();
    echo "Existing admin user removed (if any)\n";

    // Create a new admin user with a fresh password hash
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@spcc.edu', :password, 'admin')");
    $stmt->execute([':password' => $hashed_password]);
    echo "New admin user created successfully!\n";
    echo "Username: admin\n";
    echo "Password: admin123\n";

    // Verify the password
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = 'admin'");
    $stmt->execute();
    $stored_hash = $stmt->fetchColumn();
    
    if (password_verify('admin123', $stored_hash)) {
        echo "\nPassword verification successful!\n";
    } else {
        echo "\nPassword verification failed!\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 