<?php
header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Transaction ID is required');
    }

    $id = intval($_GET['id']);
    
    // Database connection
    $host = 'sql207.infinityfree.com';
    $dbname = 'if0_39124219_ukm_finance';
    $username = 'if0_39124219';
    $password = 'zl6dkZruF5';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch transaction with UKM name
    $query = "SELECT t.*, u.nama_ukm 
              FROM transaksi t 
              LEFT JOIN ukm u ON t.ukm_id = u.id 
              WHERE t.id = :id";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$transaction) {
        throw new Exception('Transaction not found');
    }
    
    echo json_encode([
        'status' => 1,
        'message' => 'Success',
        'data' => $transaction
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 0,
        'message' => $e->getMessage()
    ]);
}
?> 