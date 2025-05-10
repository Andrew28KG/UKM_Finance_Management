<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/finance.php');
$api = new Finance();

switch($requestMethod) {
    case 'GET':
        $ukmData = $api->getUkm();
        echo json_encode($ukmData);
        break;
    default:
        header("HTTP/1.0 405 Method Not Allowed");
        break;
}
?> 