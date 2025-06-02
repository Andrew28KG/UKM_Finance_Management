<?php
class Finance {
    private $host = 'localhost';
    private $user = 'root'; // Default XAMPP username
    private $pass = ''; // Default XAMPP password is empty
    private $database = "ukm_finance";
    private $tblTransaksi = 'transaksi';
    private $tblUkm = 'ukm';
    private $tblUser = 'users';
    private $dbConnect = false;

    public function __construct() {
        if(!$this->dbConnect) {
            try {
                $conn = new mysqli($this->host, $this->user, $this->pass, $this->database);
                if($conn->connect_error) {
                    error_log("Database connection failed: " . $conn->connect_error);
                    throw new Exception("Database connection failed: " . $conn->connect_error);
                }
                $this->dbConnect = $conn;
            } catch (Exception $e) {
                error_log("Database connection error: " . $e->getMessage());
                throw new Exception("Database connection error: " . $e->getMessage());
            }
        }
    }
    
    // Get database connection for testing
    public function getConnection() {
        if (!$this->dbConnect) {
            throw new Exception("Database connection not established");
        }
        return $this->dbConnect;
    }

    // Get all transactions
    public function getTransaksi($ukm_id = null, $limit = null) {
        if (!$this->dbConnect) {
            throw new Exception("Database connection not established");
        }

        $sqlQuery = "SELECT t.*, u.nama_ukm 
                    FROM ".$this->tblTransaksi." t
                    LEFT JOIN ".$this->tblUkm." u ON t.ukm_id = u.id";
        
        $params = [];
        $types = "";
        
        if($ukm_id) {
            $sqlQuery .= " WHERE t.ukm_id = ?";
            $params[] = (int)$ukm_id;
            $types .= "i";
        }
        
        $sqlQuery .= " ORDER BY t.tanggal DESC";
        
        if($limit) {
            $sqlQuery .= " LIMIT ?";
            $params[] = (int)$limit;
            $types .= "i";
        }

        $stmt = $this->dbConnect->prepare($sqlQuery);
        if (!$stmt) {
            throw new Exception("Database prepare statement failed: " . $this->dbConnect->error);
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception("Failed to execute query: " . $error);
        }

        $result = $stmt->get_result();
        $transaksiData = [];
        
        while($transaksiRecord = $result->fetch_assoc()) {
            $transaksiData[] = $transaksiRecord;
        }
        
        $stmt->close();
        return $transaksiData;
    }

    // Get summary of transactions
    public function getLaporanKeuangan($ukm_id, $month = null) {
        if (!$this->dbConnect) {
            throw new Exception("Database connection not established");
        }

        $whereClause = "WHERE ukm_id = ?";
        $params = [$ukm_id];
        $types = "i";

        if ($month) {
            $whereClause .= " AND DATE_FORMAT(tanggal, '%Y-%m') = ?";
            $params[] = $month;
            $types .= "s";
        }

        // Get total income
        $sqlPemasukan = "SELECT SUM(jumlah) as total FROM ".$this->tblTransaksi." 
                        $whereClause AND jenis = 'pemasukan'";
        $stmt = $this->dbConnect->prepare($sqlPemasukan);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $resultPemasukan = $stmt->get_result();
        $pemasukan = $resultPemasukan->fetch_assoc()['total'] ?: 0;
        $stmt->close();
        
        // Get total expense
        $sqlPengeluaran = "SELECT SUM(jumlah) as total FROM ".$this->tblTransaksi." 
                          $whereClause AND jenis = 'pengeluaran'";
        $stmt = $this->dbConnect->prepare($sqlPengeluaran);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $resultPengeluaran = $stmt->get_result();
        $pengeluaran = $resultPengeluaran->fetch_assoc()['total'] ?: 0;
        $stmt->close();
        
        // Get balance
        $saldo = $pemasukan - $pengeluaran;
        
        // Get transactions by category
        $sqlKategori = "SELECT kategori, SUM(jumlah) as total 
                       FROM ".$this->tblTransaksi." 
                       $whereClause
                       GROUP BY kategori";
        $stmt = $this->dbConnect->prepare($sqlKategori);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $resultKategori = $stmt->get_result();
        
        $kategoriData = array();
        while($kategori = $resultKategori->fetch_assoc()) {
            $kategoriData[] = $kategori;
        }
        $stmt->close();
        
        $laporan = array(
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'saldo' => $saldo,
            'kategori' => $kategoriData
        );
        
        return $laporan;
    }

    // Get all UKM saldo/balances
    public function getAllUkmSaldo() {
        if (!$this->dbConnect) {
            throw new Exception("Database connection not established");
        }

        // Get all UKMs
        $ukms = $this->getUkm();
        $result = [];
        
        // For each UKM, calculate saldo
        foreach ($ukms as $ukm) {
            $ukm_id = $ukm['id'];
            
            // Get total income
            $sqlPemasukan = "SELECT SUM(jumlah) as total FROM ".$this->tblTransaksi." 
                          WHERE ukm_id = ? AND jenis = 'pemasukan'";
            $stmt = $this->dbConnect->prepare($sqlPemasukan);
            $stmt->bind_param("i", $ukm_id);
            $stmt->execute();
            $resultPemasukan = $stmt->get_result();
            $pemasukan = $resultPemasukan->fetch_assoc()['total'] ?: 0;
            $stmt->close();
            
            // Get total expense
            $sqlPengeluaran = "SELECT SUM(jumlah) as total FROM ".$this->tblTransaksi." 
                            WHERE ukm_id = ? AND jenis = 'pengeluaran'";
            $stmt = $this->dbConnect->prepare($sqlPengeluaran);
            $stmt->bind_param("i", $ukm_id);
            $stmt->execute();
            $resultPengeluaran = $stmt->get_result();
            $pengeluaran = $resultPengeluaran->fetch_assoc()['total'] ?: 0;
            $stmt->close();
            
            // Calculate balance
            $saldo = $pemasukan - $pengeluaran;
            
            $result[] = [
                'id' => $ukm_id,
                'nama_ukm' => $ukm['nama_ukm'],
                'saldo' => $saldo
            ];
        }
        
        return $result;
    }
    
    // Get UKM list
    public function getUkm() {
        if (!$this->dbConnect) {
            throw new Exception("Database connection not established");
        }

        try {
            $sqlQuery = "SELECT * FROM ".$this->tblUkm." ORDER BY nama_ukm";
            $result = $this->dbConnect->query($sqlQuery);
            
            if(!$result) {
                throw new Exception("Error in query: ". $this->dbConnect->error);
            }
            
            $ukmData = array();
            while($ukmRecord = $result->fetch_assoc()) {
                $ukmData[] = $ukmRecord;
            }
            
            return $ukmData;
        } catch (Exception $e) {
            error_log("Error in getUkm: " . $e->getMessage());
            throw new Exception("Failed to get UKM list: " . $e->getMessage());
        }
    }

    // Get timeline data for income and expenses over time
    public function getTimelineData($ukm_id = null, $time_range = 'month') {
        if (!$this->dbConnect) {
            throw new Exception("Database connection not established");
        }
        
        // Set time constraints based on time_range
        $timeConstraint = '';
        $groupBy = '';
        $now = date('Y-m-d');
        
        switch($time_range) {
            case 'day':
                // Last 7 days
                $startDate = date('Y-m-d', strtotime('-6 days'));
                $timeConstraint = " AND t.tanggal >= ? AND t.tanggal <= ?";
                $groupBy = "DATE(t.tanggal)";
                break;
                
            case 'week':
                // Last 8 weeks
                $startDate = date('Y-m-d', strtotime('-7 weeks'));
                $timeConstraint = " AND t.tanggal >= ? AND t.tanggal <= ?";
                $groupBy = "YEARWEEK(t.tanggal, 1)";
                break;
                
            case 'month':
            default:
                // Last 12 months
                $startDate = date('Y-m-d', strtotime('-11 months'));
                $timeConstraint = " AND t.tanggal >= ? AND t.tanggal <= ?";
                $groupBy = "YEAR(t.tanggal), MONTH(t.tanggal)";
                break;
        }
        
        $whereClause = "WHERE 1=1";
        if($ukm_id) {
            $whereClause .= " AND t.ukm_id = ?";
        }
        
        // Query for pemasukan (income)
        $sqlPemasukan = "SELECT 
                            $groupBy as period,
                            DATE_FORMAT(MIN(t.tanggal), '%Y-%m-%d') as date_start,
                            SUM(t.jumlah) as total 
                        FROM ".$this->tblTransaksi." t 
                        $whereClause 
                        AND t.jenis = 'pemasukan' 
                        $timeConstraint 
                        GROUP BY $groupBy 
                        ORDER BY t.tanggal ASC";
        
        // Query for pengeluaran (expense)
        $sqlPengeluaran = "SELECT 
                            $groupBy as period,
                            DATE_FORMAT(MIN(t.tanggal), '%Y-%m-%d') as date_start,
                            SUM(t.jumlah) as total 
                        FROM ".$this->tblTransaksi." t 
                        $whereClause 
                        AND t.jenis = 'pengeluaran' 
                        $timeConstraint 
                        GROUP BY $groupBy 
                        ORDER BY t.tanggal ASC";
        
        $stmt = $this->dbConnect->prepare($sqlPemasukan);
        if (!$stmt) {
            throw new Exception("Database prepare statement failed: " . $this->dbConnect->error);
        }

        // Bind parameters
        $params = [];
        $types = "";
        if($ukm_id) {
            $params[] = $ukm_id;
            $types .= "i";
        }
        $params[] = $startDate;
        $params[] = $now;
        $types .= "ss";

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $resultPemasukan = $stmt->get_result();
        $stmt->close();

        $stmt = $this->dbConnect->prepare($sqlPengeluaran);
        if (!$stmt) {
            throw new Exception("Database prepare statement failed: " . $this->dbConnect->error);
        }

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $resultPengeluaran = $stmt->get_result();
        $stmt->close();
        
        $pemasukanData = [];
        $pengeluaranData = [];
        $labels = [];
        
        // Process pemasukan data
        while($row = $resultPemasukan->fetch_assoc()) {
            $date = new DateTime($row['date_start']);
            
            if($time_range == 'day') {
                $label = $date->format('d M');
            } else if($time_range == 'week') {
                $weekNumber = $date->format('W');
                $label = 'W' . $weekNumber;
            } else {
                $label = $date->format('M y');
            }
            
            if(!in_array($label, $labels)) {
                $labels[] = $label;
            }
            
            $pemasukanData[$label] = (int)$row['total'];
        }
        
        // Process pengeluaran data
        while($row = $resultPengeluaran->fetch_assoc()) {
            $date = new DateTime($row['date_start']);
            
            if($time_range == 'day') {
                $label = $date->format('d M');
            } else if($time_range == 'week') {
                $weekNumber = $date->format('W');
                $label = 'W' . $weekNumber;
            } else {
                $label = $date->format('M y');
            }
            
            if(!in_array($label, $labels)) {
                $labels[] = $label;
            }
            
            $pengeluaranData[$label] = (int)$row['total'];
        }
        
        // Prepare final arrays
        $pemasukanValues = [];
        $pengeluaranValues = [];
        
        foreach($labels as $label) {
            $pemasukanValues[] = isset($pemasukanData[$label]) ? $pemasukanData[$label] : 0;
            $pengeluaranValues[] = isset($pengeluaranData[$label]) ? $pengeluaranData[$label] : 0;
        }
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'labels' => $labels,
            'pemasukan' => $pemasukanValues,
            'pengeluaran' => $pengeluaranValues,
            'time_range' => $time_range
        ]);
        exit;
    }

    // Add new transaction
    public function tambahTransaksi($transaksi) {
        if (!$this->dbConnect) {
            throw new Exception("Database connection not established");
        }

        // Validate required fields
        $required_fields = ['ukm_id', 'jenis', 'kategori', 'jumlah', 'tanggal'];
        foreach ($required_fields as $field) {
            if (!isset($transaksi[$field]) || empty($transaksi[$field])) {
                return [
                    'status' => 0,
                    'status_pesan' => "Field $field harus diisi"
                ];
            }
        }

        // Sanitize and validate data
        $ukm_id = (int)$transaksi['ukm_id'];
        $jenis = mysqli_real_escape_string($this->dbConnect, strtolower($transaksi['jenis']));
        $kategori = mysqli_real_escape_string($this->dbConnect, $transaksi['kategori']);
        $jumlah = (float)$transaksi['jumlah'];
        $tanggal = mysqli_real_escape_string($this->dbConnect, $transaksi['tanggal']);
        $keterangan = isset($transaksi['keterangan']) ? mysqli_real_escape_string($this->dbConnect, $transaksi['keterangan']) : '';

        // Validate jenis
        if (!in_array($jenis, ['pemasukan', 'pengeluaran'])) {
            return [
                'status' => 0,
                'status_pesan' => "Jenis transaksi tidak valid"
            ];
        }

        // Validate jumlah
        if ($jumlah <= 0) {
            return [
                'status' => 0,
                'status_pesan' => "Jumlah harus lebih dari 0"
            ];
        }

        // Prepare statement
        $stmt = $this->dbConnect->prepare("INSERT INTO ".$this->tblTransaksi." (ukm_id, jenis, kategori, jumlah, tanggal, keterangan) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            return [
                'status' => 0,
                'status_pesan' => "Database prepare statement failed: " . $this->dbConnect->error
            ];
        }

        $stmt->bind_param("issdss", $ukm_id, $jenis, $kategori, $jumlah, $tanggal, $keterangan);
        
        if ($stmt->execute()) {
            $stmt->close();
            return [
                'status' => 1,
                'status_pesan' => "Transaksi berhasil ditambahkan"
            ];
        } else {
            $error = $stmt->error;
            $stmt->close();
            return [
                'status' => 0,
                'status_pesan' => "Gagal menambahkan transaksi: " . $error
            ];
        }
    }

    // Delete transaction
    public function hapusTransaksi($id) {
        if (!$this->dbConnect) {
            throw new Exception("Database connection not established");
        }

        // Validate ID
        $id = (int)$id;
        if ($id <= 0) {
            return [
                'status' => 0,
                'status_pesan' => "ID transaksi tidak valid"
            ];
        }

        // Prepare statement
        $stmt = $this->dbConnect->prepare("DELETE FROM ".$this->tblTransaksi." WHERE id = ?");
        if (!$stmt) {
            return [
                'status' => 0,
                'status_pesan' => "Database prepare statement failed: " . $this->dbConnect->error
            ];
        }

        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
            
            if ($affected_rows > 0) {
                return [
                    'status' => 1,
                    'status_pesan' => "Transaksi berhasil dihapus"
                ];
            } else {
                return [
                    'status' => 0,
                    'status_pesan' => "Transaksi tidak ditemukan"
                ];
            }
        } else {
            $error = $stmt->error;
            $stmt->close();
            return [
                'status' => 0,
                'status_pesan' => "Gagal menghapus transaksi: " . $error
            ];
        }
    }

    // Get transactions as JSON
    public function getTransaksiJson($ukm_id = null) {
        $transaksiData = $this->getTransaksi($ukm_id);
        header('Content-Type: application/json');
        echo json_encode($transaksiData);
    }

    // User authentication
    public function userLogin($email, $password) {
        if (!$this->dbConnect) {
            throw new Exception("Database connection not established");
        }

        // Sanitize email input
        $email = mysqli_real_escape_string($this->dbConnect, $email);
        
        // Prepare statement for better security
        $stmt = $this->dbConnect->prepare("SELECT * FROM ".$this->tblUser." WHERE email = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Database prepare statement failed: " . $this->dbConnect->error);
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // For development/testing: allow login with password123 for any user
            if ($password === 'password123') {
                $stmt->close();
                return $user;
            }
            
            // For production: use password_verify
            if (password_verify($password, $user['password'])) {
                $stmt->close();
                return $user;
            }
        }
        
        $stmt->close();
        return false;
    }

    // Create XML file
    function createXMLfile($transaksiArray) {
        $filePath = 'api/transaksi.xml';
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;
        $root = $dom->createElement('ukmFinance');
        
        for($i=0; $i<count($transaksiArray); $i++) {
            $id = $transaksiArray[$i]['id'];
            $ukm_id = $transaksiArray[$i]['ukm_id'];
            $jenis = $transaksiArray[$i]['jenis'];
            $kategori = htmlspecialchars($transaksiArray[$i]['kategori']);
            $jumlah = $transaksiArray[$i]['jumlah'];
            $tanggal = $transaksiArray[$i]['tanggal'];
            $keterangan = htmlspecialchars($transaksiArray[$i]['keterangan']);
            $nama_ukm = htmlspecialchars($transaksiArray[$i]['nama_ukm']);
            
            $transaksi = $dom->createElement('transaksi');
            $transaksi->setAttribute('id', $id);
            
            $ukmIdElement = $dom->createElement('ukm_id', $ukm_id);
            $transaksi->appendChild($ukmIdElement);
            
            $namaUkmElement = $dom->createElement('nama_ukm', $nama_ukm);
            $transaksi->appendChild($namaUkmElement);
            
            $jenisElement = $dom->createElement('jenis', $jenis);
            $transaksi->appendChild($jenisElement);
            
            $kategoriElement = $dom->createElement('kategori', $kategori);
            $transaksi->appendChild($kategoriElement);
            
            $jumlahElement = $dom->createElement('jumlah', $jumlah);
            $transaksi->appendChild($jumlahElement);
            
            $tanggalElement = $dom->createElement('tanggal', $tanggal);
            $transaksi->appendChild($tanggalElement);
            
            $keteranganElement = $dom->createElement('keterangan', $keterangan);
            $transaksi->appendChild($keteranganElement);
            
            $root->appendChild($transaksi);
        }
        
        $dom->appendChild($root);
        $dom->save($filePath);
    }    // Get transactions as XML
    public function getXml($ukm_id = null) {
        $transaksiArray = $this->getTransaksi($ukm_id);
        
        if(count($transaksiArray)) {
            $this->createXMLfile($transaksiArray);
        }
    }

    // Get notifications for a user
    public function getNotifications($user_id, $limit = null) {
        if (!$this->dbConnect) {
            throw new Exception("Database connection not established");
        }

        $sqlQuery = "SELECT * FROM notifications WHERE user_id = ? ORDER BY date DESC";
        if ($limit) {
            $sqlQuery .= " LIMIT " . (int)$limit;
        }

        $stmt = $this->dbConnect->prepare($sqlQuery);
        if (!$stmt) {
            throw new Exception("Database prepare statement failed: " . $this->dbConnect->error);
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $notifications = array();
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }

        $stmt->close();
        return $notifications;
    }

    // Get pending requests for a UKM
    public function getPendingRequests($ukm_id, $limit = null) {
        if (!$this->dbConnect) {
            throw new Exception("Database connection not established");
        }

        $sqlQuery = "SELECT * FROM requests WHERE ukm_id = ? AND status = 'pending' ORDER BY request_date DESC";
        if ($limit) {
            $sqlQuery .= " LIMIT " . (int)$limit;
        }

        $stmt = $this->dbConnect->prepare($sqlQuery);
        if (!$stmt) {
            throw new Exception("Database prepare statement failed: " . $this->dbConnect->error);
        }

        $stmt->bind_param("i", $ukm_id);
        
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception("Failed to execute query: " . $error);
        }

        $result = $stmt->get_result();

        $requests = array();
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }

        $stmt->close();
        return $requests;
    }

    // Get period-over-period changes for financial metrics
    public function getPeriodChanges($ukm_id) {
        $current_month = date('Y-m');
        $previous_month = date('Y-m', strtotime('-1 month'));
        
        $current_data = $this->getLaporanKeuangan($ukm_id, $current_month);
        $previous_data = $this->getLaporanKeuangan($ukm_id, $previous_month);
        
        $changes = [
            'pemasukan' => [
                'current' => $current_data['pemasukan'],
                'previous' => $previous_data['pemasukan'],
                'change' => 0,
                'percentage' => 0
            ],
            'pengeluaran' => [
                'current' => $current_data['pengeluaran'],
                'previous' => $previous_data['pengeluaran'],
                'change' => 0,
                'percentage' => 0
            ],
            'saldo' => [
                'current' => $current_data['saldo'],
                'previous' => $previous_data['saldo'],
                'change' => 0,
                'percentage' => 0
            ]
        ];
        
        // Calculate changes
        foreach ($changes as $key => &$value) {
            $value['change'] = $value['current'] - $value['previous'];
            
            // Calculate percentage change only if previous value is not zero
            if ($value['previous'] != 0) {
                $value['percentage'] = ($value['change'] / $value['previous']) * 100;
            } else if ($value['current'] > 0) {
                // If previous was zero and current is positive, it's a 100% increase
                $value['percentage'] = 100;
            } else if ($value['current'] < 0) {
                // If previous was zero and current is negative, it's a -100% decrease
                $value['percentage'] = -100;
            }
            // If both current and previous are zero, percentage remains 0
        }
        
        return $changes;
    }

    public function getUkmSettings($ukm_id) {
        try {
            $stmt = $this->dbConnect->prepare("SELECT * FROM settings WHERE ukm_id = ?");
            $stmt->bind_param("i", $ukm_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            // If no settings exist, create default settings
            $stmt = $this->dbConnect->prepare("INSERT INTO settings (ukm_id) VALUES (?)");
            $stmt->bind_param("i", $ukm_id);
            $stmt->execute();
            
            // Return the newly created settings
            $stmt = $this->dbConnect->prepare("SELECT * FROM settings WHERE ukm_id = ?");
            $stmt->bind_param("i", $ukm_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            error_log("Error in getUkmSettings: " . $e->getMessage());
            return null;
        }
    }

    // Create a new UKM
    public function createUkm($ukmName) {
        if (!$this->dbConnect) {
            return [
                'status' => 0,
                'status_pesan' => "Database connection not established"
            ];
        }

        // Validate UKM name
        if (empty($ukmName)) {
            return [
                'status' => 0,
                'status_pesan' => "Nama UKM tidak boleh kosong"
            ];
        }

        // Sanitize input
        $ukmName = mysqli_real_escape_string($this->dbConnect, $ukmName);

        // Check if UKM name already exists (optional, but good practice)
        $checkSql = "SELECT id FROM ".$this->tblUkm." WHERE nama_ukm = ? LIMIT 1";
        $stmtCheck = $this->dbConnect->prepare($checkSql);
        if ($stmtCheck) {
            $stmtCheck->bind_param("s", $ukmName);
            $stmtCheck->execute();
            $stmtCheck->store_result();
            if ($stmtCheck->num_rows > 0) {
                $stmtCheck->close();
                return [
                    'status' => 0,
                    'status_pesan' => "Nama UKM sudah ada"
                ];
            }
            $stmtCheck->close();
        } else {
             // Handle prepare error for check
             error_log("Database prepare statement failed for UKM check: " . $this->dbConnect->error);
             // Continue without check, or return error based on desired strictness
        }


        // Prepare statement for insertion
        $stmt = $this->dbConnect->prepare("INSERT INTO ".$this->tblUkm." (nama_ukm) VALUES (?)");
        if (!$stmt) {
             return [
                'status' => 0,
                'status_pesan' => "Database prepare statement failed for insert: " . $this->dbConnect->error
            ];
        }

        $stmt->bind_param("s", $ukmName);

        if ($stmt->execute()) {
            $stmt->close();
            return [
                'status' => 1,
                'status_pesan' => "UKM berhasil ditambahkan",
                'ukm_id' => $this->dbConnect->insert_id // Return the ID of the newly created UKM
            ];
        } else {
            $error = $stmt->error;
            $stmt->close();
            return [
                'status' => 0,
                'status_pesan' => "Gagal menambahkan UKM: " . $error
            ];
        }
    }
}
?>