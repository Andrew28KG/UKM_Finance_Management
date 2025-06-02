<?php
header('Content-Type: application/json');

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode([
        'status' => 0,
        'message' => 'Invalid data received'
    ]);
    exit();
}

// Validate required fields
$required_fields = ['tanggal', 'nama_ukm', 'jenis', 'kategori', 'jumlah'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        echo json_encode([
            'status' => 0,
            'message' => "Field $field harus diisi"
        ]);
        exit();
    }
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
    
    // Get the transaction at the specified index
    $transactions = $xml->xpath('//transaksi');
    if (!isset($transactions[$data['index']])) {
        throw new Exception('Transaction not found');
    }
    
    $transaction = $transactions[$data['index']];
    
    // Update the transaction data
    $transaction->tanggal = $data['tanggal'];
    $transaction->nama_ukm = $data['nama_ukm'];
    $transaction->jenis = $data['jenis'];
    $transaction->kategori = $data['kategori'];
    $transaction->jumlah = $data['jumlah'];
    $transaction->keterangan = $data['keterangan'] ?? '';
    
    // Save the updated XML
    if ($xml->asXML($xmlFile)) {
        echo json_encode([
            'status' => 1,
            'message' => 'Transaction updated successfully'
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