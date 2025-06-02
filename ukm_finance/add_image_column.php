<?php
// Script to add image column to transaksi table
try {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $database = 'ukm_finance';
    
    $conn = new mysqli($host, $user, $pass, $database);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Check if image column already exists
    $checkQuery = "SHOW COLUMNS FROM transaksi LIKE 'image'";
    $result = $conn->query($checkQuery);
    
    if ($result->num_rows == 0) {
        // Add image column
        $alterQuery = "ALTER TABLE `transaksi` ADD COLUMN `image` TEXT DEFAULT NULL COMMENT 'Image URL or file path for transaction receipt/proof'";
        
        if ($conn->query($alterQuery) === TRUE) {
            echo "Image column added successfully to transaksi table.\n";
        } else {
            echo "Error adding column: " . $conn->error . "\n";
        }
    } else {
        echo "Image column already exists in transaksi table.\n";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
