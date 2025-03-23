<?php
require_once 'config.php';
require 'vendor/autoload.php'; // You'll need to install PhpSpreadsheet via Composer

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if (!isset($_FILES['excel_file'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No file uploaded'
    ]);
    exit;
}

try {
    $inputFileName = $_FILES['excel_file']['tmp_name'];
    $spreadsheet = IOFactory::load($inputFileName);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();

    // Remove header row
    array_shift($rows);

    $successCount = 0;
    $errorCount = 0;

    // Prepare SQL statement
    $stmt = $conn->prepare("
        INSERT INTO books (title, author, isbn, category, quantity, location) 
        VALUES (:title, :author, :isbn, :category, :quantity, :location)
    ");

    foreach ($rows as $row) {
        // Skip empty rows
        if (empty(array_filter($row))) {
            continue;
        }

        try {
            // Execute with parameters, using empty strings for missing columns
            $stmt->execute([
                ':title' => $row[0] ?? '',
                ':author' => $row[1] ?? '',
                ':isbn' => $row[2] ?? '',
                ':category' => $row[3] ?? '',
                ':quantity' => $row[4] ?? 1,
                ':location' => $row[5] ?? ''
            ]);

            $successCount++;
        } catch (PDOException $e) {
            $errorCount++;
            continue;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => "Import completed. Successfully imported: $successCount, Failed: $errorCount"
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error processing file: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 