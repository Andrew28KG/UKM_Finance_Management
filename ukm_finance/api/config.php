<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ukm_finance');

// API Response Headers
function setApiHeaders() {
    // Set CORS headers directly
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
}

// Database Connection
function getDbConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// API Response Helper
function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit();
}

// Error Response Helper
function sendError($message, $status = 400) {
    error_log("API Error: " . $message);
    sendResponse([
        'status' => 0,
        'message' => $message
    ], $status);
}

// Success Response Helper
function sendSuccess($data = null, $message = 'Success') {
    sendResponse([
        'status' => 1,
        'message' => $message,
        'data' => $data
    ]);
}
?> 