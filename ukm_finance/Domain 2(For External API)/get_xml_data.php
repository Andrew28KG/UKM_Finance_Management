<?php
header('Content-Type: application/json');

// Get the XML file path
$xmlFile = 'api/transaksi.xml';

if (!file_exists($xmlFile)) {
    echo json_encode([
        'status' => 0,
        'message' => 'XML file not found',
        'data' => []
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
    $data = [];
    
    foreach ($transactions as $index => $transaction) {
        $data[] = [
            'index' => $index,
            'tanggal' => (string)$transaction->tanggal,
            'nama_ukm' => (string)$transaction->nama_ukm,
            'jenis' => (string)$transaction->jenis,
            'kategori' => (string)$transaction->kategori,
            'jumlah' => (string)$transaction->jumlah,
            'keterangan' => (string)$transaction->keterangan
        ];
    }
    
    echo json_encode([
        'status' => 1,
        'message' => 'Data retrieved successfully',
        'data' => $data
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 0,
        'message' => $e->getMessage(),
        'data' => []
    ]);
}
?> 