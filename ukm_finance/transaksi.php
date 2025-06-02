<?php
session_start();
header("Access-Control-Allow-Origin: *");
include('inc/header.php');
include('inc/auth.php');
include('class/finance.php');

// Check if user is authenticated (either logged in or in preview mode)
requireAuth();

$finance = new Finance();
$ukms = $finance->getUkm();
$ukm_id = isset($_GET['ukm_id']) ? $_GET['ukm_id'] : (isset($_SESSION['ukm_id']) ? $_SESSION['ukm_id'] : (count($ukms) > 0 ? $ukms[0]['id'] : null));

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $result = $finance->tambahTransaksi([
            'ukm_id' => $_POST['ukm_id'],
            'jenis' => $_POST['jenis'],
            'kategori' => $_POST['kategori'],
            'jumlah' => $_POST['jumlah'],
            'tanggal' => $_POST['tanggal'],
            'keterangan' => $_POST['keterangan']
        ]);
        
        if ($result['status'] === 1) {
            header("Location: transaksi.php?ukm_id=" . $_POST['ukm_id'] . "&success=1");
            exit();
        } else {
            $error_message = $result['status_pesan'];
        }
    }
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $result = $finance->hapusTransaksi($_POST['id']);
    if ($result['status'] === 1) {
        header("Location: transaksi.php?ukm_id=" . $_POST['ukm_id'] . "&success=2");
        exit();
    } else {
        $error_message = $result['status_pesan'];
    }
}

// If in preview mode, use the first UKM
if (isPreviewMode() && !$ukm_id && count($ukms) > 0) {
    $ukm_id = $ukms[0]['id'];
}

// Store selected UKM in session
if($ukm_id) {
    $_SESSION['ukm_id'] = $ukm_id;
}

// Get transactions for the selected UKM
$transaksi = $finance->getTransaksi($ukm_id);

// Get UKM name
$ukm_name = "";
foreach($ukms as $ukm) {
    if($ukm['id'] == $ukm_id) {
        $ukm_name = $ukm['nama_ukm'];
        break;
    }
}
?>

<div class="wrapper">
    <?php include('inc/sidebar.php'); ?>
    
    <div id="main-content">
        <div class="content-toggle">
            <button id="content-toggle-btn">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>
        <section class="transaksi-page">
            <div class="page-header">
                <h1>Manajemen Transaksi <?php echo $ukm_name ? "- $ukm_name" : ""; ?></h1>
                <?php include('inc/profile_bar.php'); ?>
            </div>
    
    <?php if($ukm_id): ?>
        <?php if(isset($_GET['success'])): ?>
            <div class="alert <?php echo $_GET['success'] == 1 ? 'alert-success' : 'alert-success'; ?>">
                <?php 
                switch($_GET['success']) {
                    case 1:
                        echo 'Transaksi berhasil ditambahkan';
                        break;
                    case 2:
                        echo 'Transaksi berhasil dihapus';
                        break;
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="ukm-selector">
            <form method="GET" action="">
                <label for="ukm">Pilih UKM:</label>
                <select name="ukm_id" id="ukm" onchange="this.form.submit()">
                    <?php foreach($ukms as $ukm): ?>
                        <option value="<?php echo $ukm['id']; ?>" <?php echo ($ukm['id'] == $ukm_id) ? 'selected' : ''; ?>>
                            <?php echo $ukm['nama_ukm']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        
        <div class="transaksi-container">
            <div class="transaksi-form">
                <h2>Tambah Transaksi Baru</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="ukm_id" value="<?php echo $ukm_id; ?>">
                    
                    <div class="form-group">
                        <label for="jenis">Jenis Transaksi:</label>
                        <select name="jenis" id="jenis" required>
                            <option value="">Pilih Jenis</option>
                            <option value="pemasukan">Pemasukan</option>
                            <option value="pengeluaran">Pengeluaran</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="kategori">Kategori:</label>
                        <input type="text" name="kategori" id="kategori" required placeholder="Contoh: Iuran, Sponsorship, Konsumsi, dll">
                    </div>
                    
                    <div class="form-group">
                        <label for="jumlah">Jumlah (Rp):</label>
                        <input type="number" name="jumlah" id="jumlah" required min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="tanggal">Tanggal:</label>
                        <input type="date" name="tanggal" id="tanggal" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="keterangan">Keterangan:</label>
                        <textarea name="keterangan" id="keterangan" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                    </div>
                </form>
            </div>
            
            <div class="transaksi-list">
                <h2>Daftar Transaksi</h2>
                
                
                <table id="transaksiTable">
                    <tbody>
                        <?php if(count($transaksi) > 0): ?>
                            <?php foreach($transaksi as $t): ?>
                                <tr class="<?php echo $t['jenis']; ?>">
                                    <td><?php echo date('d/m/Y', strtotime($t['tanggal'])); ?></td>
                                    <td><?php echo $t['kategori']; ?></td>
                                    <td><?php echo ucfirst($t['jenis']); ?></td>
                                    <td>Rp <?php echo number_format($t['jumlah'], 0, ',', '.'); ?></td>
                                    <td><?php echo $t['keterangan']; ?></td>
                                    <td>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                            <input type="hidden" name="ukm_id" value="<?php echo $ukm_id; ?>">
                                            <button type="submit" class="btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-data">Belum ada transaksi</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <script>
            $(document).ready(function() {
                // Search and filter transactions
                function filterTable() {
                    const searchValue = $('#searchTransaksi').val().toLowerCase();
                    const jenisValue = $('#filterJenis').val().toLowerCase();
                    
                    $('#transaksiTable tbody tr').each(function() {
                        const row = $(this);
                        
                        // Skip the "no data" row
                        if (row.hasClass('no-data') || row.hasClass('no-data-filtered')) {
                            return;
                        }

                        const rowText = row.text().toLowerCase();
                        
                        // Get the text content of the 'Jenis' column (assuming it's the 3rd column, index 2)
                        const rowJenisText = row.find('td').eq(2).text().toLowerCase().trim();
                        
                        const matchesSearch = searchValue === '' || rowText.includes(searchValue);
                        // Check if the rowJenisText contains the selected filter value (or if filter is empty)
                        const matchesJenis = jenisValue === '' || rowJenisText.includes(jenisValue);
                        
                        row.toggle(matchesSearch && matchesJenis);
                    });
                    
                    // Check if any rows are visible
                    const visibleRows = $('#transaksiTable tbody tr:visible').not('.no-data, .no-data-filtered').length;
                    
                    // Remove existing "no data" message
                    $('#transaksiTable tbody tr.no-data-filtered').remove();
                    
                    // Add "no data" message if no rows are visible
                    if (visibleRows === 0) {
                        $('#transaksiTable tbody').append(
                            '<tr class="no-data-filtered"><td colspan="6" class="no-data">Tidak ada transaksi yang sesuai dengan filter</td></tr>'
                        );
                    }
                }
                
                // Add event listeners
                $('#searchTransaksi').on('keyup', filterTable);
                $('#filterJenis').on('change', filterTable);
                
                // Initial filter
                filterTable();
            });
        </script>
    <?php else: ?>
        <div class="no-ukm">
            <p>Tidak ada UKM yang tersedia. Silakan hubungi administrator.</p>
        </div>
    <?php endif; ?>
        </section>
    </div>
</div>