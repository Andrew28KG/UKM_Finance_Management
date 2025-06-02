<?php
require_once 'config.php';
require_once '../class/finance.php';

// Set API headers
setApiHeaders();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

try {
    // Get JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        sendError('Invalid JSON data');
    }

    $api = new Finance();
    $result = $api->tambahTransaksi($data);
    
    if ($result['status'] === 1) {
        sendSuccess(null, $result['status_pesan']);
    } else {
        sendError($result['status_pesan']);
    }
} catch (Exception $e) {
    sendError("Failed to process transaction: " . $e->getMessage(), 500);
}
?> 