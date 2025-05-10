<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/finance.php');
$api = new Finance();

switch($requestMethod) {
    case 'GET':
        // Check if UKM ID is provided
        if(isset($_GET['ukm_id'])) {
            $ukm_id = $_GET['ukm_id'];
            $laporan = $api->getLaporanKeuangan($ukm_id);
            echo json_encode($laporan);
        } else {
            $response = array(
                "status" => 0,
                "status_pesan" => "UKM ID diperlukan"
            );
            echo json_encode($response);
        }
        break;
    default:
        header("HTTP/1.0 405 Method Not Allowed");
        break;
}
?> 