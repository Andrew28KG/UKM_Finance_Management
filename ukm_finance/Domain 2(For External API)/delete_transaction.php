<?php
header('Content-Type: application/json');

try {
    // Get JSON data from request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data || !isset($data['id'])) {
        throw new Exception('Transaction ID is required');
    }
    
    $id = intval($data['id']);
    
    // Database connection
    $host = 'sql207.infinityfree.com';
    $dbname = 'if0_39124219_ukm_finance';
    $username = 'if0_39124219';
    $password = 'zl6dkZruF5';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Delete transaction
    $query = "DELETE FROM transaksi WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Transaction not found');
    }
    
    echo json_encode([
        'status' => 1,
        'message' => 'Transaction deleted successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 0,
        'message' => $e->getMessage()
    ]);
}
?> 