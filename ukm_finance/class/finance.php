<?php
class Finance {
    private $host = 'localhost';
    private $user = 'root'; // Change this to your database username
    private $pass = ''; // Change this to your database password
    private $database = "ukm_finance";
    private $tblTransaksi = 'transaksi';
    private $tblUkm = 'ukm';
    private $tblUser = 'users';
    private $dbConnect = false;

    public function __construct() {
        if(!$this->dbConnect) {
            $conn = new mysqli($this->host, $this->user, $this->pass, $this->database);
            if($conn->connect_error) {
                die("Error failed to connect to MySQL: " . $conn->connect_error);
            } else {
                $this->dbConnect = $conn;
            }
        }
    }

    // Get all transactions
    public function getTransaksi($ukm_id = null) {
        $whereClause = "";
        if($ukm_id) {
            $whereClause = " WHERE ukm_id = '$ukm_id'";
        }
        
        $sqlQuery = "SELECT t.*, u.nama_ukm 
                    FROM ".$this->tblTransaksi." t
                    LEFT JOIN ".$this->tblUkm." u ON t.ukm_id = u.id
                    $whereClause
                    ORDER BY t.tanggal DESC";

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

    // Get UKM list
    public function getUkm() {
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
        $sqlQuery = "SELECT * FROM ".$this->tblUser." WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($this->dbConnect, $sqlQuery);
        
        if($result) {
            $user = mysqli_fetch_assoc($result);
            if($user && password_verify($password, $user['password'])) {
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
    }

    // Get transactions as XML
    public function getXml($ukm_id = null) {
        $transaksiArray = $this->getTransaksi($ukm_id);
        
        if(count($transaksiArray)) {
            $this->createXMLfile($transaksiArray);
        }
    }
}
?> 