<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/finance.php');
$api = new Finance();

switch($requestMethod) {
    case 'POST':
        // Get posted data
        $data = json_decode(file_get_contents("php://input"), true);
        
        if(!empty($data)) {
            $transaksiResponse = $api->tambahTransaksi($data);
            echo json_encode($transaksiResponse);
        } else {
            $response = array(
                "status" => 0,
                "status_pesan" => "Data transaksi tidak valid"
            );
            echo json_encode($response);
        }
        break;
        
    case 'DELETE':
        // Get transaction ID from URL
        $transaksiId = isset($_GET['id']) ? $_GET['id'] : '';
        
        if(!empty($transaksiId)) {
            $transaksiResponse = $api->hapusTransaksi($transaksiId);
            echo json_encode($transaksiResponse);
        } else {
            $response = array(
                "status" => 0,
                "status_pesan" => "ID transaksi tidak ditemukan"
            );
            echo json_encode($response);
        }
        break;
        
    default:
        header("HTTP/1.0 405 Method Not Allowed");
        break;
}
?> 