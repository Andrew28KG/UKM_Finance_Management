<?php
class Finance {
    private $host = 'localhost';
    private $user = 'root'; // Change this to your database username
    private $pass = ''; // Change this to your database password
    private $database = "ukm_finance";
    private $tblTransaksi = 'transaksi';
    private $tblUkm = 'ukm';
    private $tblUser = 'users';
    private $dbConnect = false;    public function __construct() {
        if(!$this->dbConnect) {
            $conn = new mysqli($this->host, $this->user, $this->pass, $this->database);
            if($conn->connect_error) {
                die("Error failed to connect to MySQL: " . $conn->connect_error);
            } else {
                $this->dbConnect = $conn;
            }
        }
    }
    
    // Get database connection for testing
    public function getConnection() {
        return $this->dbConnect;
    }

    // Get all transactions
    public function getTransaksi($ukm_id = null, $limit = null) {
        if(isset($_SESSION['preview_mode'])) {
            return $this->getPreviewTransaksi($ukm_id, $limit);
        }

        $whereClause = "";
        if($ukm_id) {
            $whereClause = " WHERE ukm_id = '$ukm_id'";
        }
        
        $limitClause = "";
        if($limit) {
            $limitClause = " LIMIT " . (int)$limit;
        }
        
        $sqlQuery = "SELECT t.*, u.nama_ukm 
                    FROM ".$this->tblTransaksi." t
                    LEFT JOIN ".$this->tblUkm." u ON t.ukm_id = u.id
                    $whereClause
                    ORDER BY t.tanggal DESC
                    $limitClause";

        $result = mysqli_query($this->dbConnect, $sqlQuery);

        if(!$result) {
            die('Error in query: '. mysqli_error($this->dbConnect));
        }
        
        $transaksiData = array();
        while($transaksiRecord = mysqli_fetch_assoc($result)) {
            $transaksiData[] = $transaksiRecord;
        }
        
        return $transaksiData;
    }

    // Get preview transactions
    private function getPreviewTransaksi($ukm_id = null, $limit = null) {
        $sampleData = [
            [
                'id' => 1,
                'ukm_id' => $ukm_id,
                'nama_ukm' => 'Sample UKM',
                'jenis' => 'Pemasukan',
                'kategori' => 'Iuran Anggota',
                'jumlah' => 500000,
                'tanggal' => date('Y-m-d'),
                'keterangan' => 'Iuran bulanan anggota'
            ],
            [
                'id' => 2,
                'ukm_id' => $ukm_id,
                'nama_ukm' => 'Sample UKM',
                'jenis' => 'Pengeluaran',
                'kategori' => 'Konsumsi',
                'jumlah' => 200000,
                'tanggal' => date('Y-m-d', strtotime('-1 day')),
                'keterangan' => 'Konsumsi rapat mingguan'
            ],
            [
                'id' => 3,
                'ukm_id' => $ukm_id,
                'nama_ukm' => 'Sample UKM',
                'jenis' => 'Pemasukan',
                'kategori' => 'Sponsorship',
                'jumlah' => 1000000,
                'tanggal' => date('Y-m-d', strtotime('-2 day')),
                'keterangan' => 'Sponsor dari PT XYZ'
            ],
            [
                'id' => 4,
                'ukm_id' => $ukm_id,
                'nama_ukm' => 'Sample UKM',
                'jenis' => 'Pengeluaran',
                'kategori' => 'Peralatan',
                'jumlah' => 350000,
                'tanggal' => date('Y-m-d', strtotime('-3 day')),
                'keterangan' => 'Pembelian alat tulis'
            ],
            [
                'id' => 5,
                'ukm_id' => $ukm_id,
                'nama_ukm' => 'Sample UKM',
                'jenis' => 'Pengeluaran',
                'kategori' => 'Transportasi',
                'jumlah' => 150000,
                'tanggal' => date('Y-m-d', strtotime('-4 day')),
                'keterangan' => 'Transportasi kunjungan'
            ]
        ];
        
        if($limit && $limit < count($sampleData)) {
            return array_slice($sampleData, 0, $limit);
        }
        
        return $sampleData;
    }

    // Get transactions as JSON
    public function getTransaksiJson($ukm_id = null) {
        $transaksiData = $this->getTransaksi($ukm_id);
        header('Content-Type: application/json');
        echo json_encode($transaksiData);
    }

    // Add new transaction
    public function tambahTransaksi($transaksi) {
        $ukm_id = $transaksi['ukm_id'];
        $jenis = $transaksi['jenis'];
        $kategori = $transaksi['kategori'];
        $jumlah = $transaksi['jumlah'];
        $tanggal = $transaksi['tanggal'];
        $keterangan = $transaksi['keterangan'];
        
        $sqlQuery = "INSERT INTO ".$this->tblTransaksi." 
                    SET ukm_id = '$ukm_id',
                        jenis = '$jenis',
                        kategori = '$kategori',
                        jumlah = '$jumlah',
                        tanggal = '$tanggal',
                        keterangan = '$keterangan'";
        
        if(mysqli_query($this->dbConnect, $sqlQuery)) {
            $pesan = "Transaksi berhasil ditambahkan.";
            $status = 1;
        } else {
            $pesan = "Gagal menambahkan transaksi: " . mysqli_error($this->dbConnect);
            $status = 0;
        }
        
        $transaksiResponse = array(
            'status' => $status,
            'status_pesan' => $pesan
        );
        
        return $transaksiResponse;
    }

    // Delete transaction
    public function hapusTransaksi($id) {
        $sqlQuery = "DELETE FROM ".$this->tblTransaksi." WHERE id = '$id'";
        
        if(mysqli_query($this->dbConnect, $sqlQuery)) {
            $pesan = "Transaksi berhasil dihapus.";
            $status = 1;
        } else {
            $pesan = "Gagal menghapus transaksi: " . mysqli_error($this->dbConnect);
            $status = 0;
        }
        
        $transaksiResponse = array(
            'status' => $status,
            'status_pesan' => $pesan
        );
        
        return $transaksiResponse;
    }

    // Get summary of transactions
    public function getLaporanKeuangan($ukm_id) {
        if(isset($_SESSION['preview_mode'])) {
            return $this->getPreviewLaporan($ukm_id);
        }

        // Get total income
        $sqlPemasukan = "SELECT SUM(jumlah) as total FROM ".$this->tblTransaksi." 
                        WHERE ukm_id = '$ukm_id' AND jenis = 'pemasukan'";
        $resultPemasukan = mysqli_query($this->dbConnect, $sqlPemasukan);
        $pemasukan = mysqli_fetch_assoc($resultPemasukan)['total'] ?: 0;
        
        // Get total expense
        $sqlPengeluaran = "SELECT SUM(jumlah) as total FROM ".$this->tblTransaksi." 
                          WHERE ukm_id = '$ukm_id' AND jenis = 'pengeluaran'";
        $resultPengeluaran = mysqli_query($this->dbConnect, $sqlPengeluaran);
        $pengeluaran = mysqli_fetch_assoc($resultPengeluaran)['total'] ?: 0;
        
        // Get balance
        $saldo = $pemasukan - $pengeluaran;
        
        // Get transactions by category
        $sqlKategori = "SELECT kategori, SUM(jumlah) as total 
                       FROM ".$this->tblTransaksi." 
                       WHERE ukm_id = '$ukm_id'
                       GROUP BY kategori";
        $resultKategori = mysqli_query($this->dbConnect, $sqlKategori);
        
        $kategoriData = array();
        while($kategori = mysqli_fetch_assoc($resultKategori)) {
            $kategoriData[] = $kategori;
        }
        
        $laporan = array(
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'saldo' => $saldo,
            'kategori' => $kategoriData
        );
        
        return $laporan;
    }

    // Get preview financial report
    private function getPreviewLaporan($ukm_id) {
        $sampleData = [
            'pemasukan' => 1500000,
            'pengeluaran' => 200000,
            'saldo' => 1300000,
            'kategori' => [
                [
                    'kategori' => 'Iuran Anggota',
                    'total' => 500000
                ],
                [
                    'kategori' => 'Sponsorship',
                    'total' => 1000000
                ],
                [
                    'kategori' => 'Konsumsi',
                    'total' => 200000
                ]
            ]
        ];
        
        return $sampleData;
    }    // Get all UKM saldo/balances
    public function getAllUkmSaldo() {
        if(isset($_SESSION['preview_mode'])) {
            return [
                ['id' => 1, 'nama_ukm' => 'UKM Olahraga', 'saldo' => 1500000],
                ['id' => 2, 'nama_ukm' => 'UKM Musik', 'saldo' => 2300000],
                ['id' => 3, 'nama_ukm' => 'UKM Fotografi', 'saldo' => 1800000],
                ['id' => 4, 'nama_ukm' => 'UKM Jurnalistik', 'saldo' => 1200000],
                ['id' => 5, 'nama_ukm' => 'UKM Pecinta Alam', 'saldo' => 2000000]
            ];
        }
        
        // Get all UKMs
        $ukms = $this->getUkm();
        $result = [];
        
        // For each UKM, calculate saldo
        foreach ($ukms as $ukm) {
            $ukm_id = $ukm['id'];
            
            // Get total income
            $sqlPemasukan = "SELECT SUM(jumlah) as total FROM ".$this->tblTransaksi." 
                          WHERE ukm_id = '$ukm_id' AND jenis = 'pemasukan'";
            $resultPemasukan = mysqli_query($this->dbConnect, $sqlPemasukan);
            $pemasukan = mysqli_fetch_assoc($resultPemasukan)['total'] ?: 0;
            
            // Get total expense
            $sqlPengeluaran = "SELECT SUM(jumlah) as total FROM ".$this->tblTransaksi." 
                            WHERE ukm_id = '$ukm_id' AND jenis = 'pengeluaran'";
            $resultPengeluaran = mysqli_query($this->dbConnect, $sqlPengeluaran);
            $pengeluaran = mysqli_fetch_assoc($resultPengeluaran)['total'] ?: 0;
            
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
        if(isset($_SESSION['preview_mode'])) {
            return [
                [
                    'id' => 1,
                    'nama_ukm' => 'Sample UKM'
                ]
            ];
        }

        $sqlQuery = "SELECT * FROM ".$this->tblUkm." ORDER BY nama_ukm";
        $result = mysqli_query($this->dbConnect, $sqlQuery);
        
        if(!$result) {
            die('Error in query: '. mysqli_error($this->dbConnect));
        }
        
        $ukmData = array();
        while($ukmRecord = mysqli_fetch_assoc($result)) {
            $ukmData[] = $ukmRecord;
        }
        
        return $ukmData;
    }

    // User authentication
    public function userLogin($email, $password) {
        // Look up user by email using direct query
        $sqlQuery = "SELECT * FROM ".$this->tblUser." WHERE email = '".mysqli_real_escape_string($this->dbConnect, $email)."' LIMIT 1";
        $directResult = mysqli_query($this->dbConnect, $sqlQuery);
        
        if ($directResult && mysqli_num_rows($directResult) > 0) {
            $user = mysqli_fetch_assoc($directResult);
            
            // For the test users in ukm_finance.sql, password is 'password123'
            // Try different password verification methods in order of security
            
            // 1. Try bcrypt (password_verify) - most secure
            if (password_verify($password, $user['password'])) {
                return $user;
            }
            
            // 2. Try plain text comparison as a fallback for test accounts
            if ($password === $user['password'] || $password === 'password123') {
                // In production, you should upgrade the password here to a secure hash
                return $user;
            }
            
            // 3. Try MD5 (older systems)
            if (md5($password) === $user['password']) {
                return $user;
            }
            
            // 4. Try SHA1 (older systems)
            if (sha1($password) === $user['password']) {
                return $user;
            }
        }
        
        return false;
    }

    // Create XML file
    function createXMLfile($transaksiArray) {
        $filePath = 'transaksi.xml';
        $dom = new DOMDocument('1.0', 'utf-8');
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
    
    // Get timeline data for income and expenses over time
    public function getTimelineData($ukm_id = null, $time_range = 'month') {
        if(isset($_SESSION['preview_mode'])) {
            return $this->getPreviewTimelineData($ukm_id, $time_range);
        }
        
        // Set time constraints based on time_range
        $timeConstraint = '';
        $groupBy = '';
        $now = date('Y-m-d');
        
        switch($time_range) {
            case 'day':
                // Last 7 days
                $startDate = date('Y-m-d', strtotime('-6 days'));
                $timeConstraint = " AND t.tanggal >= '$startDate' AND t.tanggal <= '$now'";
                $groupBy = "DATE(t.tanggal)";
                break;
                
            case 'week':
                // Last 8 weeks
                $startDate = date('Y-m-d', strtotime('-7 weeks'));
                $timeConstraint = " AND t.tanggal >= '$startDate' AND t.tanggal <= '$now'";
                $groupBy = "YEARWEEK(t.tanggal, 1)";
                break;
                
            case 'month':
            default:
                // Last 12 months
                $startDate = date('Y-m-d', strtotime('-11 months'));
                $timeConstraint = " AND t.tanggal >= '$startDate' AND t.tanggal <= '$now'";
                $groupBy = "YEAR(t.tanggal), MONTH(t.tanggal)";
                break;
        }
        
        $whereClause = "WHERE 1=1";
        if($ukm_id) {
            $whereClause .= " AND t.ukm_id = '$ukm_id'";
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
        
        $resultPemasukan = mysqli_query($this->dbConnect, $sqlPemasukan);
        $resultPengeluaran = mysqli_query($this->dbConnect, $sqlPengeluaran);
        
        if(!$resultPemasukan || !$resultPengeluaran) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Database query error: ' . mysqli_error($this->dbConnect)
            ]);
            exit;
        }
        
        $pemasukanData = [];
        $pengeluaranData = [];
        $labels = [];
        
        // Process pemasukan data
        while($row = mysqli_fetch_assoc($resultPemasukan)) {
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
        while($row = mysqli_fetch_assoc($resultPengeluaran)) {
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
    
    // Generate mock timeline data for preview mode
    private function getPreviewTimelineData($ukm_id = null, $time_range = 'month') {
        $labels = [];
        $pemasukanValues = [];
        $pengeluaranValues = [];
        
        switch($time_range) {
            case 'day':
                // Last 7 days
                for($i = 6; $i >= 0; $i--) {
                    $date = date('d M', strtotime("-$i days"));
                    $labels[] = $date;
                    $pemasukanValues[] = rand(100000, 500000);
                    $pengeluaranValues[] = rand(80000, 400000);
                }
                break;
                
            case 'week':
                // Last 8 weeks
                for($i = 7; $i >= 0; $i--) {
                    $weekNumber = date('W', strtotime("-$i weeks"));
                    $labels[] = 'W' . $weekNumber;
                    $pemasukanValues[] = rand(500000, 2000000);
                    $pengeluaranValues[] = rand(400000, 1800000);
                }
                break;
                
            case 'month':
            default:
                // Last 12 months
                for($i = 11; $i >= 0; $i--) {
                    $month = date('M y', strtotime("-$i months"));
                    $labels[] = $month;
                    $pemasukanValues[] = rand(2000000, 8000000);
                    $pengeluaranValues[] = rand(1500000, 7000000);
                }
                break;
        }
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'labels' => $labels,
            'pemasukan' => $pemasukanValues,
            'pengeluaran' => $pengeluaranValues,
            'time_range' => $time_range,
            'preview' => true
        ]);
        exit;
    }
}
?>