<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once('../api/auth.php');
requireApiAuth();

$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/finance.php');
$api = new Finance();

switch($requestMethod) {
    case 'GET':
        // Check if UKM ID is provided
        $ukm_id = isset($_GET['ukm_id']) ? $_GET['ukm_id'] : null;
        
        // Check if time range is provided (day, week, month)
        $time_range = isset($_GET['time_range']) ? $_GET['time_range'] : null;
        
        if ($time_range) {
            $api->getTimelineData($ukm_id, $time_range);
        } else {
            $api->getTransaksiJson($ukm_id);
        }
        break;
    default:
        header("HTTP/1.0 405 Method Not Allowed");
        break;
}
?> 