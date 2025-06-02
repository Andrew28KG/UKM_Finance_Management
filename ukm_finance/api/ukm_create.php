<?php
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Allow POST and preflight OPTIONS
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../class/finance.php';

$finance = new Finance();

// Get incoming data
$data = json_decode(file_get_contents("php://input"));

// Ensure it's a POST request and data is not empty and contains nama_ukm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($data) && isset($data->nama_ukm)) {

    $ukmName = $data->nama_ukm;

    try {
        // Create UKM using Finance class method
        $result = $finance->createUkm($ukmName);

        if ($result['status'] === 1) {
            http_response_code(201); // Created
            echo json_encode(array("status" => 1, "message" => $result['status_pesan'], "ukm_id" => $result['ukm_id']));
        } else {
            http_response_code(400); // Bad Request or other client error
            echo json_encode(array("status" => 0, "message" => $result['status_pesan']));
        }

    } catch (Exception $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(array("status" => 0, "message" => "Server error: " . $e->getMessage()));
    }

} else {
    // Method not allowed or missing data
    http_response_code(405); // Method Not Allowed
    echo json_encode(array("status" => 0, "message" => "Only POST method with 'nama_ukm' data is allowed."));
}
?> 