<?php
header('Content-Type: application/json');

try {
    // Database connection
    $host = 'sql207.infinityfree.com';
    $dbname = 'if0_39124219_ukm_finance';
    $username = 'if0_39124219';
    $password = 'zl6dkZruF5';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Validate required fields
    $requiredFields = ['id', 'tanggal', 'nama_ukm', 'jenis', 'kategori', 'jumlah'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    // Get UKM ID from name
    $stmt = $pdo->prepare("SELECT id FROM ukm WHERE nama_ukm = :nama_ukm");
    $stmt->execute(['nama_ukm' => $_POST['nama_ukm']]);
    $ukm = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ukm) {
        throw new Exception('UKM not found');
    }
    
    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.');
        }
        
        $fileName = uniqid() . '.' . $fileExtension;
        $uploadFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $imagePath = $uploadFile;
        } else {
            throw new Exception('Failed to upload image');
        }
    } elseif (isset($_POST['image']) && !empty($_POST['image'])) {
        $imagePath = $_POST['image'];
    }
    
    // Update transaction
    $query = "UPDATE transaksi SET 
              tanggal = :tanggal,
              ukm_id = :ukm_id,
              jenis = :jenis,
              kategori = :kategori,
              jumlah = :jumlah,
              keterangan = :keterangan,
              image = :image
              WHERE id = :id";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'id' => $_POST['id'],
        'tanggal' => $_POST['tanggal'],
        'ukm_id' => $ukm['id'],
        'jenis' => $_POST['jenis'],
        'kategori' => $_POST['kategori'],
        'jumlah' => $_POST['jumlah'],
        'keterangan' => $_POST['keterangan'] ?? null,
        'image' => $imagePath
    ]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Transaction not found or no changes made');
    }
    
    echo json_encode([
        'status' => 1,
        'message' => 'Transaction updated successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 0,
        'message' => $e->getMessage()
    ]);
}
?> 