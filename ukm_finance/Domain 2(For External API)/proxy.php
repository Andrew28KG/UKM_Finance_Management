<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, X-User-ID, X-User-Email');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get the endpoint from URL
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

if (empty($endpoint)) {
    echo json_encode([
        'status' => 0,
        'message' => 'No endpoint specified'
    ]);
    exit();
}

// Initialize cURL
$ch = curl_init();

// Set the target URL based on the endpoint
if ($endpoint === 'transaksi.php') {
    // For transaksi.php, use the external API
    $targetUrl = 'https://ukmfinancepraditas.infinityfreeapp.com/api/transaksi.php';
} else {
    // For other endpoints, use the external API
    $targetUrl = 'https://ukmfinancepraditas.infinityfreeapp.com/api/' . $endpoint;
}

// Log the target URL for debugging
error_log("Attempting to connect to: " . $targetUrl);

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $targetUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing only
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Forward user headers if present
$headers = [];
if (isset($_SERVER['HTTP_X_USER_ID'])) {
    $headers[] = 'X-User-ID: ' . $_SERVER['HTTP_X_USER_ID'];
}
if (isset($_SERVER['HTTP_X_USER_EMAIL'])) {
    $headers[] = 'X-User-Email: ' . $_SERVER['HTTP_X_USER_EMAIL'];
}

// If it's a POST request, forward the data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = file_get_contents('php://input');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Content-Length: ' . strlen($postData);
}

// Set headers
if (!empty($headers)) {
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
}

// Execute the request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

// Log the request details
error_log("Proxy request to: " . $targetUrl);
error_log("HTTP Code: " . $httpCode);
error_log("Response: " . $response);
if ($error) {
    error_log("cURL Error: " . $error);
}

curl_close($ch);

// Check for cURL errors
if ($error) {
    echo json_encode([
        'status' => 0,
        'message' => 'Failed to connect to API: ' . $error,
        'debug' => [
            'url' => $targetUrl,
            'http_code' => $httpCode
        ]
    ]);
    exit();
}

// If the response is XML, convert it to JSON
if (strpos($response, '<?xml') !== false) {
    try {
        $xml = simplexml_load_string($response);
        if ($xml === false) {
            throw new Exception('Failed to parse XML');
        }
        
        // Convert XML to array
        $transactions = [];
        foreach ($xml->transaksi as $transaksi) {
            $transactions[] = [
                'id' => (string)$transaksi['id'],
                'ukm_id' => (string)$transaksi->ukm_id,
                'nama_ukm' => (string)$transaksi->nama_ukm,
                'jenis' => (string)$transaksi->jenis,
                'kategori' => (string)$transaksi->kategori,
                'jumlah' => (string)$transaksi->jumlah,
                'tanggal' => (string)$transaksi->tanggal,
                'keterangan' => (string)$transaksi->keterangan
            ];
        }
        
        // Return JSON response
        echo json_encode([
            'status' => 1,
            'message' => 'Success',
            'data' => $transactions
        ]);
        exit();
    } catch (Exception $e) {
        echo json_encode([
            'status' => 0,
            'message' => 'Failed to parse XML: ' . $e->getMessage(),
            'debug' => [
                'response' => $response,
                'http_code' => $httpCode,
                'url' => $targetUrl
            ]
        ]);
        exit();
    }
}

// Check if response is valid JSON
$decoded = json_decode($response);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'status' => 0,
        'message' => 'Invalid JSON response from API',
        'debug' => [
            'response' => $response,
            'http_code' => $httpCode,
            'url' => $targetUrl,
            'json_error' => json_last_error_msg()
        ]
    ]);
    exit();
}

// Return the response
echo $response;
?> 