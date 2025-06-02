<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Database connection
    $host = 'localhost';
    $dbname = 'ukm_finance';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch transactions with UKM names
    $query = "SELECT t.*, u.nama_ukm 
              FROM transaksi t 
              LEFT JOIN ukm u ON t.ukm_id = u.id 
              ORDER BY t.tanggal DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data
    $formattedTransactions = array_map(function($transaction) {
        return [
            'id' => $transaction['id'],
            'tanggal' => $transaction['tanggal'],
            'nama_ukm' => $transaction['nama_ukm'],
            'jenis' => $transaction['jenis'],
            'kategori' => $transaction['kategori'],
            'jumlah' => $transaction['jumlah'],
            'keterangan' => $transaction['keterangan'],
            'image' => $transaction['image']
        ];
    }, $transactions);
    
    // Return JSON response
    echo json_encode([
        'status' => 1,
        'message' => 'Success',
        'data' => $formattedTransactions
    ]);
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log('Database Error: ' . $e->getMessage());
    
    // Return error response
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'status' => 0,
        'message' => $e->getMessage(),
        'data' => []
    ]);
}
?> 