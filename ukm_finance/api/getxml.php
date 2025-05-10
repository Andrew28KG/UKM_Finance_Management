<?php
header("Access-Control-Allow-Origin: *");

$requestMethod = $_SERVER["REQUEST_METHOD"];
include('../class/finance.php');
$api = new Finance();

// Check if UKM ID is provided
$ukm_id = isset($_GET['ukm_id']) ? $_GET['ukm_id'] : null;
$xml = $api->getXml($ukm_id);

// Redirect to the generated XML file
header('Location: transaksi.xml');
?> 