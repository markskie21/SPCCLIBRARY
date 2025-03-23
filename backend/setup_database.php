<?php
require_once 'config.php';

try {
    // Create books table
    $conn->exec("CREATE TABLE IF NOT EXISTS books (
        bookID INT AUTO_INCREMENT PRIMARY KEY,
        bookname VARCHAR(255) NOT NULL,
        author VARCHAR(100) NOT NULL,
        ISBN VARCHAR(13) UNIQUE NOT NULL,
        genre VARCHAR(50),
        quantity INT NOT NULL DEFAULT 1,
        location VARCHAR(50),
        school_level VARCHAR(50),
        publisher VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "Books table created/verified successfully\n";

    // Create users table
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        userID INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "Users table created/verified successfully\n";

    // Create borrowed table
    $conn->exec("CREATE TABLE IF NOT EXISTS borrowed (
        borrowID INT AUTO_INCREMENT PRIMARY KEY,
        userID INT NOT NULL,
        bookID INT NOT NULL,
        dateborrowed TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        datetoreturn TIMESTAMP NULL,
        borrowstatus ENUM('Borrowed', 'Returned', 'Overdue') NOT NULL DEFAULT 'Borrowed',
        fine DECIMAL(10,2) DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (userID) REFERENCES users(userID),
        FOREIGN KEY (bookID) REFERENCES books(bookID)
    )");
    echo "Borrowed table created/verified successfully\n";

    // Create settings table
    $conn->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT PRIMARY KEY DEFAULT 1,
        library_name VARCHAR(100) NOT NULL DEFAULT 'SPCC Library',
        max_borrow_days INT NOT NULL DEFAULT 7,
        max_borrow_books INT NOT NULL DEFAULT 3,
        fine_per_day DECIMAL(10,2) NOT NULL DEFAULT 1.00,
        email_notifications BOOLEAN NOT NULL DEFAULT true,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "Settings table created/verified successfully\n";

    // Check if admin user exists
    $stmt = $conn->prepare("SELECT userID FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();

    if (!$admin) {
        // Create admin user
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@spcc.edu', :password, 'admin')");
        $stmt->execute([':password' => $hashed_password]);
        echo "Admin user created successfully\n";
        echo "Username: admin\n";
        echo "Password: admin123\n";
    }

    // Insert default settings if not exists
    $stmt = $conn->prepare("INSERT IGNORE INTO settings (id, library_name, max_borrow_days, max_borrow_books, fine_per_day, email_notifications) VALUES (1, 'SPCC Library', 7, 3, 1.00, true)");
    $stmt->execute();
    echo "Default settings created/verified successfully\n";

    echo "Database setup completed successfully!\n";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 