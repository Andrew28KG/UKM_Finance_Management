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

<section class="transaksi-page">
    <h1>Manajemen Transaksi <?php echo $ukm_name ? "- $ukm_name" : ""; ?></h1>
    
    <?php if($ukm_id): ?>
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
                <form id="transaksiForm">
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
                <div id="responseMessage"></div>
            </div>
            
            <div class="transaksi-list">
                <h2>Daftar Transaksi</h2>
                
                <div class="filter-container">
                    <input type="text" id="searchTransaksi" placeholder="Cari transaksi...">
                    <select id="filterJenis">
                        <option value="">Semua Jenis</option>
                        <option value="pemasukan">Pemasukan</option>
                        <option value="pengeluaran">Pengeluaran</option>
                    </select>
                </div>
                
                <table id="transaksiTable">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kategori</th>
                            <th>Jenis</th>
                            <th>Jumlah</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
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
                                        <button class="btn-delete" data-id="<?php echo $t['id']; ?>">Hapus</button>
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
                // Add transaction
                $('#transaksiForm').submit(function(e) {
                    e.preventDefault();
                    
                    const formData = {
                        ukm_id: $('input[name="ukm_id"]').val(),
                        jenis: $('#jenis').val(),
                        kategori: $('#kategori').val(),
                        jumlah: $('#jumlah').val(),
                        tanggal: $('#tanggal').val(),
                        keterangan: $('#keterangan').val()
                    };
                    
                    $.ajax({
                        url: 'api/transaksi.php',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(formData),
                        success: function(response) {
                            if(response.status === 1) {
                                $('#responseMessage').html('<div class="alert alert-success">' + response.status_pesan + '</div>');
                                $('#transaksiForm')[0].reset();
                                // Reload page after 1 second
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            } else {
                                $('#responseMessage').html('<div class="alert alert-danger">' + response.status_pesan + '</div>');
                            }
                        },
                        error: function() {
                            $('#responseMessage').html('<div class="alert alert-danger">Terjadi kesalahan. Silakan coba lagi.</div>');
                        }
                    });
                });
                
                // Delete transaction
                $('.btn-delete').click(function() {
                    if(confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) {
                        const id = $(this).data('id');
                        
                        $.ajax({
                            url: 'api/transaksi.php?id=' + id,
                            type: 'DELETE',
                            success: function(response) {
                                if(response.status === 1) {
                                    alert(response.status_pesan);
                                    location.reload();
                                } else {
                                    alert(response.status_pesan);
                                }
                            },
                            error: function() {
                                alert('Terjadi kesalahan. Silakan coba lagi.');
                            }
                        });
                    }
                });
                
                // Search and filter transactions
                $('#searchTransaksi').on('keyup', function() {
                    const value = $(this).val().toLowerCase();
                    filterTable(value, $('#filterJenis').val());
                });
                
                $('#filterJenis').on('change', function() {
                    const value = $('#searchTransaksi').val().toLowerCase();
                    filterTable(value, $(this).val());
                });
                
                function filterTable(search, jenis) {
                    $('#transaksiTable tbody tr').filter(function() {
                        const jenisMatch = jenis === '' || $(this).hasClass(jenis);
                        const textMatch = $(this).text().toLowerCase().indexOf(search) > -1;
                        $(this).toggle(jenisMatch && textMatch);
                    });
                    
                    // Show "no data" message if no rows are visible
                    const visibleRows = $('#transaksiTable tbody tr:visible').length;
                    if(visibleRows === 0) {
                        if($('#transaksiTable tbody tr.no-data-filtered').length === 0) {
                            $('#transaksiTable tbody').append('<tr class="no-data-filtered"><td colspan="6" class="no-data">Tidak ada transaksi yang sesuai dengan filter</td></tr>');
                        }
                    } else {
                        $('#transaksiTable tbody tr.no-data-filtered').remove();
                    }
                }
            });
        </script>
    <?php else: ?>
        <div class="no-ukm">
            <p>Tidak ada UKM yang tersedia. Silakan hubungi administrator.</p>
        </div>
    <?php endif; ?>
</section>

<?php include('inc/footer.php'); ?> 