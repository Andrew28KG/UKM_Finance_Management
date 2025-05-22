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

// Get financial report for the selected UKM
$laporan = $finance->getLaporanKeuangan($ukm_id);
$transaksi = $finance->getTransaksi($ukm_id);

// Get UKM name
$ukm_name = "";
foreach($ukms as $ukm) {
    if($ukm['id'] == $ukm_id) {
        $ukm_name = $ukm['nama_ukm'];
        break;
    }
}

// Prepare data for charts
$kategoriLabels = [];
$kategoriData = [];
$kategoriColors = [];

foreach($laporan['kategori'] as $kategori) {
    $kategoriLabels[] = $kategori['kategori'];
    $kategoriData[] = $kategori['total'];
    
    // Generate random color
    $r = rand(100, 200);
    $g = rand(100, 200);
    $b = rand(100, 200);
    $kategoriColors[] = "rgba($r, $g, $b, 0.7)";
}

// Prepare monthly data
$monthlyData = [
    'labels' => [],
    'pemasukan' => [],
    'pengeluaran' => []
];

// Get data for the last 6 months
for($i = 5; $i >= 0; $i--) {
    $month = date('m', strtotime("-$i month"));
    $year = date('Y', strtotime("-$i month"));
    $monthName = date('M Y', strtotime("-$i month"));
    
    $monthlyData['labels'][] = $monthName;
    
    // Calculate monthly income
    $monthlyIncome = 0;
    $monthlyExpense = 0;
    
    foreach($transaksi as $t) {
        $tMonth = date('m', strtotime($t['tanggal']));
        $tYear = date('Y', strtotime($t['tanggal']));
        
        if($tMonth == $month && $tYear == $year) {
            if($t['jenis'] == 'pemasukan') {
                $monthlyIncome += $t['jumlah'];
            } else {
                $monthlyExpense += $t['jumlah'];
            }
        }
    }
    
    $monthlyData['pemasukan'][] = $monthlyIncome;
    $monthlyData['pengeluaran'][] = $monthlyExpense;
}
?>

<section class="laporan-page">
    <div class="page-header">
        <h1>Laporan Keuangan <?php echo $ukm_name ? "- $ukm_name" : ""; ?></h1>
        <?php include('inc/profile_bar.php'); ?>
    </div>
    
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
        
        <div class="charts-container">
            <div class="chart-card">
                <h3>Ringkasan Keuangan</h3>
                <canvas id="summaryChart"></canvas>
            </div>
            
            <div class="chart-card">
                <h3>Distribusi per Kategori</h3>
                <canvas id="categoryChart"></canvas>
            </div>
            
            <div class="chart-card full-width">
                <h3>Tren Keuangan 6 Bulan Terakhir</h3>
                <canvas id="trendChart"></canvas>
            </div>
        </div>
        
        <div class="export-section">
            <h3>Ekspor Data</h3>
            <div class="export-buttons">
                <a href="api/getxml.php?ukm_id=<?php echo $ukm_id; ?>" class="btn btn-export">Ekspor ke XML</a>
                <button id="printReport" class="btn btn-export">Cetak Laporan</button>
            </div>
        </div>
        
        <script>
            // Summary Chart
            const ctxSummary = document.getElementById('summaryChart').getContext('2d');
            const summaryChart = new Chart(ctxSummary, {
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
            
            // Category Chart
            const ctxCategory = document.getElementById('categoryChart').getContext('2d');
            const categoryChart = new Chart(ctxCategory, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode($kategoriLabels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($kategoriData); ?>,
                        backgroundColor: <?php echo json_encode($kategoriColors); ?>,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
            
            // Trend Chart
            const ctxTrend = document.getElementById('trendChart').getContext('2d');
            const trendChart = new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($monthlyData['labels']); ?>,
                    datasets: [
                        {
                            label: 'Pemasukan',
                            data: <?php echo json_encode($monthlyData['pemasukan']); ?>,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 2,
                            tension: 0.3
                        },
                        {
                            label: 'Pengeluaran',
                            data: <?php echo json_encode($monthlyData['pengeluaran']); ?>,
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 2,
                            tension: 0.3
                        }
                    ]
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
            
            // Print report
            document.getElementById('printReport').addEventListener('click', function() {
                window.print();
            });
        </script>
    <?php else: ?>
        <div class="no-ukm">
            <p>Tidak ada UKM yang tersedia. Silakan hubungi administrator.</p>
        </div>
    <?php endif; ?>
</section>

<?php include('inc/footer.php'); ?> 