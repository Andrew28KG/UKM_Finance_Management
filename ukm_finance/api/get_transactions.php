<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-User-ID, X-User-Email");
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once('../class/finance.php');

try {
    $finance = new Finance();
    $transactions = $finance->getTransaksi();
    
    echo json_encode([
        'status' => 1,
        'message' => 'Success',
        'data' => $transactions
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 0,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 