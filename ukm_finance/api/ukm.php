<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to client

// Set CORS headers directly at the start
header("Access-Control-Allow-Origin: https://sistemkaskecil.infinityfreeapp.com");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, Accept, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 3600");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Function to send JSON response
function sendJsonResponse($status, $message, $data = null) {
    $response = [
        'status' => $status,
        'message' => $message
    ];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit();
}

// Function to handle errors
function handleError($message, $code = 500) {
    error_log("UKM API Error: " . $message);
    http_response_code($code);
    sendJsonResponse(0, $message);
}

try {
    // Log the request
    error_log("UKM API Request: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);
    
    // Define the base path
    define('BASE_PATH', dirname(__DIR__));
    
    // Include the Finance class
    require_once BASE_PATH . '/class/finance.php';
    
    $api = new Finance();
    
    // Test database connection
    $conn = $api->getConnection();
    if (!$conn) {
        handleError("Database connection failed");
    }
    
    $ukmData = $api->getUkm();
    
    // Log successful response
    error_log("UKM API Success: Retrieved " . count($ukmData) . " UKMs");
    
    sendJsonResponse(1, 'Success', $ukmData);
} catch (Exception $e) {
    error_log("UKM API Exception: " . $e->getMessage());
    handleError("Failed to load UKM data: " . $e->getMessage());
}
?> 