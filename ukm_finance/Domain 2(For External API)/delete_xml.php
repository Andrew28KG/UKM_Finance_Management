<?php
header('Content-Type: application/json');

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['index'])) {
    echo json_encode([
        'status' => 0,
        'message' => 'Invalid data received'
    ]);
    exit();
}

// Get the XML file path
$xmlFile = 'api/transaksi.xml';

if (!file_exists($xmlFile)) {
    echo json_encode([
        'status' => 0,
        'message' => 'XML file not found'
    ]);
    exit();
}

try {
    // Load the XML file
    $xml = simplexml_load_file($xmlFile);
    
    if ($xml === false) {
        throw new Exception('Failed to load XML file');
    }
    
    // Get all transactions
    $transactions = $xml->xpath('//transaksi');
    if (!isset($transactions[$data['index']])) {
        throw new Exception('Transaction not found');
    }
    
    // Remove the transaction at the specified index
    $dom = dom_import_simplexml($transactions[$data['index']]);
    $dom->parentNode->removeChild($dom);
    
    // Save the updated XML
    if ($xml->asXML($xmlFile)) {
        echo json_encode([
            'status' => 1,
            'message' => 'Transaction deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to save XML file');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 0,
        'message' => $e->getMessage()
    ]);
}
?> 