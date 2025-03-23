-- Drop database if exists and create new one
DROP DATABASE IF EXISTS library_db;
CREATE DATABASE library_db;
USE library_db;

-- Create users table
CREATE TABLE users (
    userID INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create books table
CREATE TABLE books (
    bookID INT PRIMARY KEY AUTO_INCREMENT,
    bookname VARCHAR(255) NOT NULL,
    author VARCHAR(100) NOT NULL,
    ISBN VARCHAR(13) UNIQUE NOT NULL,
    genre VARCHAR(50) NOT NULL,
    publisher VARCHAR(100),
    quantity INT NOT NULL DEFAULT 1,
    location VARCHAR(50) NOT NULL,
    school_level ENUM('Elementary', 'Junior High', 'Senior High', 'College') NOT NULL,
    bookstatus ENUM('Available', 'Not Available') NOT NULL DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create borrowed table
CREATE TABLE borrowed (
    borrowID INT PRIMARY KEY AUTO_INCREMENT,
    userID INT NOT NULL,
    bookID INT NOT NULL,
    dateborrowed DATE NOT NULL,
    datetoreturn DATE NOT NULL,
    datereturned DATE,
    borrowstatus ENUM('Borrowed', 'Returned', 'Overdue') NOT NULL DEFAULT 'Borrowed',
    fine DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (userID) REFERENCES users(userID),
    FOREIGN KEY (bookID) REFERENCES books(bookID)
);

-- Create archive table
CREATE TABLE archive (
    archiveID INT PRIMARY KEY AUTO_INCREMENT,
    userID INT NOT NULL,
    bookID INT NOT NULL,
    bookname VARCHAR(255) NOT NULL,
    dateborrowed DATE NOT NULL,
    datetoreturn DATE NOT NULL,
    datereturned DATE NOT NULL,
    fine DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userID) REFERENCES users(userID),
    FOREIGN KEY (bookID) REFERENCES books(bookID)
);

-- Create settings table
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    library_name VARCHAR(100) NOT NULL DEFAULT 'SPCC Library',
    max_borrow_days INT NOT NULL DEFAULT 7,
    max_borrow_books INT NOT NULL DEFAULT 3,
    fine_per_day DECIMAL(10,2) NOT NULL DEFAULT 5.00,
    email_notifications BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@spcc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert default settings
INSERT INTO settings (library_name, max_borrow_days, max_borrow_books, fine_per_day, email_notifications) VALUES 
('SPCC Library', 7, 3, 5.00, TRUE);