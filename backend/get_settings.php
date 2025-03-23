<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Get settings from the settings table
    $stmt = $conn->query("SELECT * FROM settings WHERE id = 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        // If no settings exist, create default settings
        $stmt = $conn->prepare("INSERT INTO settings (maxBorrowDays, finePerDay, maxBooksPerUser) VALUES (7, 10, 3)");
        $stmt->execute();
        
        $settings = [
            'maxBorrowDays' => 7,
            'finePerDay' => 10,
            'maxBooksPerUser' => 3
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $settings
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 