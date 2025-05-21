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
$ukm_id = isset($_SESSION['ukm_id']) ? $_SESSION['ukm_id'] : (count($ukms) > 0 ? $ukms[0]['id'] : null);

// If in preview mode, use the first UKM
if (isPreviewMode() && !$ukm_id && count($ukms) > 0) {
    $ukm_id = $ukms[0]['id'];
}
?>

<header id="slideCarousel">
    <div class="slide-image" id="slide1">
        <div class="slide-content">
            <h1>UKM Finance Management</h1>
            <p>Kelola keuangan UKM dengan mudah, transparan, dan akuntabel</p>
        </div>
    </div>
</header>

<section class="dashboard">
    <h1>Dashboard Keuangan</h1>
    
    <?php if($ukm_id): ?>
        <?php 
        $laporan = $finance->getLaporanKeuangan($ukm_id);
        $transaksi = $finance->getTransaksi($ukm_id);
        $ukm_name = "";
        foreach($ukms as $ukm) {
            if($ukm['id'] == $ukm_id) {
                $ukm_name = $ukm['nama_ukm'];
                break;
            }
        }
        ?>
        
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
        
        <div class="summary-cards">
            <div class="card">
                <div class="card-icon income">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="card-content">
                    <h3>Total Pemasukan</h3>
                    <h2>Rp <?php echo number_format($laporan['pemasukan'], 0, ',', '.'); ?></h2>
                </div>
            </div>
            
            <div class="card">
                <div class="card-icon expense">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="card-content">
                    <h3>Total Pengeluaran</h3>
                    <h2>Rp <?php echo number_format($laporan['pengeluaran'], 0, ',', '.'); ?></h2>
                </div>
            </div>
            
            <div class="card">
                <div class="card-icon balance">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="card-content">
                    <h3>Saldo</h3>
                    <h2>Rp <?php echo number_format($laporan['saldo'], 0, ',', '.'); ?></h2>
                </div>
            </div>
        </div>
        
        <div class="chart-container">
            <div class="chart-card">
                <h3>Grafik Keuangan</h3>
                <canvas id="financeChart"></canvas>
            </div>
        </div>
        
        <div class="recent-transactions">
            <h2>Transaksi Terbaru</h2>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $count = 0;
                    foreach($transaksi as $t): 
                        if($count >= 5) break; // Only show 5 latest transactions
                    ?>
                    <tr class="<?php echo $t['jenis']; ?>">
                        <td><?php echo date('d/m/Y', strtotime($t['tanggal'])); ?></td>
                        <td><?php echo $t['kategori']; ?></td>
                        <td><?php echo ucfirst($t['jenis']); ?></td>
                        <td>Rp <?php echo number_format($t['jumlah'], 0, ',', '.'); ?></td>
                        <td><?php echo $t['keterangan']; ?></td>
                    </tr>
                    <?php 
                        $count++;
                    endforeach; 
                    
                    if(count($transaksi) == 0):
                    ?>
                    <tr>
                        <td colspan="5" class="no-data">Belum ada transaksi</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <div class="view-all">
                <a href="transaksi.php" class="btn">Lihat Semua Transaksi</a>
            </div>
        </div>
        
        <script>
            // Chart data
            const ctx = document.getElementById('financeChart').getContext('2d');
            const financeChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Pemasukan', 'Pengeluaran', 'Saldo'],
                    datasets: [{
                        label: 'Jumlah (Rp)',
                        data: [
                            <?php echo $laporan['pemasukan']; ?>, 
                            <?php echo $laporan['pengeluaran']; ?>, 
                            <?php echo $laporan['saldo']; ?>
                        ],
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.5)',
                            'rgba(255, 99, 132, 0.5)',
                            'rgba(54, 162, 235, 0.5)'
                        ],
                        borderColor: [
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
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