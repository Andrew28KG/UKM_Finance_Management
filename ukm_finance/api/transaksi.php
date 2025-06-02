<?php
session_start();
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once('../inc/auth.php');
require_once('../class/finance.php');

// Check if user is authenticated
if (!isAuthenticated()) {
    echo json_encode([
        'status' => 0,
        'status_pesan' => 'Unauthorized access'
    ]);
    exit();
}

$finance = new Finance();

// Handle different HTTP methods
switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        // Add new transaction
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        $required_fields = ['ukm_id', 'jenis', 'kategori', 'jumlah', 'tanggal'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                echo json_encode([
                    'status' => 0,
                    'status_pesan' => "Field $field is required"
                ]);
                exit();
            }
        }
        
        // Validate jenis
        if (!in_array($data['jenis'], ['pemasukan', 'pengeluaran'])) {
            echo json_encode([
                'status' => 0,
                'status_pesan' => 'Invalid transaction type'
            ]);
            exit();
        }
        
        // Validate jumlah
        if (!is_numeric($data['jumlah']) || $data['jumlah'] <= 0) {
            echo json_encode([
                'status' => 0,
                'status_pesan' => 'Invalid amount'
            ]);
            exit();
        }
        
        // Validate date
        if (!strtotime($data['tanggal'])) {
            echo json_encode([
                'status' => 0,
                'status_pesan' => 'Invalid date format'
            ]);
            exit();
        }
        
        try {
            $result = $finance->tambahTransaksi($data);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 0,
                'status_pesan' => 'Gagal menambahkan transaksi: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'DELETE':
        // Delete transaction
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            echo json_encode([
                'status' => 0,
                'status_pesan' => 'Transaction ID is required'
            ]);
            exit();
        }
        
        try {
            $result = $finance->hapusTransaksi($id);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 0,
                'status_pesan' => 'Gagal menghapus transaksi: ' . $e->getMessage()
            ]);
        }
        break;
        
    default:
        echo json_encode([
            'status' => 0,
            'status_pesan' => 'Invalid request method'
        ]);
        break;
}
?> 