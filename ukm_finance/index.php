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

// Handle UKM selection from GET parameter
if (isset($_GET['ukm_id'])) {
    $_SESSION['ukm_id'] = $_GET['ukm_id'];
}

$ukm_id = isset($_SESSION['ukm_id']) ? $_SESSION['ukm_id'] : (count($ukms) > 0 ? $ukms[0]['id'] : null);

// If in preview mode, use the first UKM
if (isPreviewMode() && !$ukm_id && count($ukms) > 0) {
    $ukm_id = $ukms[0]['id'];
}

// Get notifications and requests from database
$notifications = [];
$requests = [];
if ($ukm_id) {
    try {
        // Get notifications for the current user
        $notifications = $finance->getNotifications($_SESSION['user_id'], 2); // Limit to 2 for dashboard
        
        // Get pending requests for the current UKM
        $requests = $finance->getPendingRequests($ukm_id, 2); // Limit to 2 for dashboard
    } catch (Exception $e) {
        error_log("Error fetching dashboard data: " . $e->getMessage());
    }
}

// Get period-over-period changes
$periodChanges = [];
if ($ukm_id) {
    try {
        $periodChanges = $finance->getPeriodChanges($ukm_id);
    } catch (Exception $e) {
        error_log("Error fetching period changes: " . $e->getMessage());
    }
}
?>

<!-- Add Chart.js DataLabels plugin -->
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

<div class="wrapper">
    <?php include('inc/sidebar.php'); ?>      <div id="main-content">
        <section class="dashboard">
            <div class="welcome-section">
                <div class="welcome-message">
                    <h1>Selamat Datang, <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Pengguna'; ?></h1>
                </div>
                <?php include('inc/profile_bar.php'); ?>
            </div>
            
            <!-- Loading overlay for data fetching -->
            <div class="loading-overlay" id="loadingOverlay" style="display: none;">
                <div class="loading-spinner-large"></div>
                <p>Memuat data...</p>
            </div>
            
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
                <script>
                    function showLoading() {
                        document.getElementById('loadingOverlay').style.display = 'flex';
                    }
                </script>                <div class="dashboard-column">
                    <div class="finance-summary-container">
                        <div class="finance-summary-header">
                            <h1>Dashboard</h1>
                            <div class="ukm-selector">
                                <form method="GET" action="" id="ukmSelectorForm">
                                    <select name="ukm_id" id="ukm" onchange="showLoading(); this.form.submit();">
                                        <?php foreach($ukms as $ukm): ?>
                                            <option value="<?php echo $ukm['id']; ?>" <?php echo ($ukm['id'] == $ukm_id) ? 'selected' : ''; ?>>
                                                <?php echo $ukm['nama_ukm']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </div>
                        </div>
                        
                        <div class="finance-summary-content">
                            <div class="summary-cards">
                                <div class="summary-card income" data-type="income" data-value="<?php echo $laporan['pemasukan']; ?>" data-previous="<?php echo $periodChanges['pemasukan']['previous']; ?>">
                                    <div class="card-header">
                                        <div class="card-icon income">
                                            <i class="fas fa-arrow-down"></i>
                                        </div>
                                        <div class="indicator <?php echo $periodChanges['pemasukan']['change'] >= 0 ? 'positive' : 'negative'; ?>">
                                            <i class="fas fa-chevron-<?php echo $periodChanges['pemasukan']['change'] >= 0 ? 'up' : 'down'; ?>"></i>
                                            <?php echo abs(round($periodChanges['pemasukan']['change'], 1)); ?>%
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="card-label">Total Pemasukan</div>
                                        <div class="card-value" data-value="<?php echo $laporan['pemasukan']; ?>">
                                            <?php 
                                            $value = $laporan['pemasukan'];
                                            $sign = $value < 0 ? '-' : '';
                                            $absValue = abs($value);
                                            echo $sign . 'Rp ' . number_format($absValue, 0, ',', '.');
                                            ?>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <small>Dibandingkan dengan periode sebelumnya</small>
                                    </div>
                                    <div class="card-decoration"></div>
                                </div>
                                
                                <div class="summary-card expense" data-type="expense" data-value="<?php echo $laporan['pengeluaran']; ?>" data-previous="<?php echo $periodChanges['pengeluaran']['previous']; ?>">
                                    <div class="card-header">
                                        <div class="card-icon expense">
                                            <i class="fas fa-arrow-up"></i>
                                        </div>
                                        <div class="indicator <?php echo $periodChanges['pengeluaran']['change'] >= 0 ? 'positive' : 'negative'; ?>">
                                            <i class="fas fa-chevron-<?php echo $periodChanges['pengeluaran']['change'] >= 0 ? 'up' : 'down'; ?>"></i>
                                            <?php echo abs(round($periodChanges['pengeluaran']['change'], 1)); ?>%
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="card-label">Total Pengeluaran</div>
                                        <div class="card-value" data-value="<?php echo $laporan['pengeluaran']; ?>">
                                            <?php 
                                            $value = $laporan['pengeluaran'];
                                            $sign = $value < 0 ? '-' : '';
                                            $absValue = abs($value);
                                            echo $sign . 'Rp ' . number_format($absValue, 0, ',', '.');
                                            ?>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <small>Dibandingkan dengan periode sebelumnya</small>
                                    </div>
                                    <div class="card-decoration"></div>
                                </div>
                                
                                <div class="summary-card balance" data-type="balance" data-value="<?php echo $laporan['saldo']; ?>" data-previous="<?php echo $periodChanges['saldo']['previous']; ?>">
                                    <div class="card-header">
                                        <div class="card-icon balance">
                                            <i class="fas fa-wallet"></i>
                                        </div>
                                        <div class="indicator <?php echo $periodChanges['saldo']['change'] >= 0 ? 'positive' : 'negative'; ?>">
                                            <i class="fas fa-chevron-<?php echo $periodChanges['saldo']['change'] >= 0 ? 'up' : 'down'; ?>"></i>
                                            <?php echo abs(round($periodChanges['saldo']['change'], 1)); ?>%
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="card-label">Saldo</div>
                                        <div class="card-value" data-value="<?php echo $laporan['saldo']; ?>">
                                            <?php 
                                            $value = $laporan['saldo'];
                                            $sign = $value < 0 ? '-' : '';
                                            $absValue = abs($value);
                                            echo $sign . 'Rp ' . number_format($absValue, 0, ',', '.');
                                            ?>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <small>Dibandingkan dengan periode sebelumnya</small>
                                    </div>
                                    <div class="card-decoration"></div>
                                </div>
                            </div>
                            
                            <div class="chart-container">
                                <div class="chart-card">
                                    <h3>Grafik Keuangan</h3>
                                    <canvas id="financeChart" 
                                        data-pemasukan="<?php echo $laporan['pemasukan']; ?>" 
                                        data-pengeluaran="<?php echo $laporan['pengeluaran']; ?>" 
                                        data-saldo="<?php echo $laporan['saldo']; ?>">
                                    </canvas>
                                </div>
                                
                                <div class="chart-card pie-chart-container">
                                    <h3>Distribusi Saldo UKM</h3>
                                    <div class="pie-chart-content">
                                        <div class="pie-chart-canvas">
                                            <canvas id="ukm-saldo-chart"></canvas>
                                        </div>
                                        <div class="pie-chart-info">
                                            <h4>Daftar UKM</h4>
                                            <div class="ukm-saldo-list">
                                                <?php 
                                                $finance = new Finance();
                                                $allUkmSaldo = $finance->getAllUkmSaldo();
                                                
                                                foreach($allUkmSaldo as $index => $ukmData):
                                                    $hue = ($index * 137.5) % 360;
                                                    $color = "hsl($hue, 70%, 60%)";
                                                ?>
                                                <div class="ukm-saldo-item">
                                                    <div class="ukm-color-indicator" style="background-color: <?php echo $color; ?>"></div>
                                                    <div class="ukm-name"><?php echo $ukmData['nama_ukm']; ?></div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    
                <!-- Dashboard Grid Layout -->
                <div class="dashboard-grid full-width">
                    <!-- First Column: Recent Transactions -->
                    <div class="recent-transactions compact">
                        <div class="section-header">
                            <h2>Transaksi Terbaru</h2>
                            <div class="section-actions">
                                <a href="transaksi.php" class="btn-sm">Lihat Semua</a>
                            </div>
                        </div>
                        <table class="compact-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody id="transactionTable">
                                <?php 
                                $count = 0;
                                foreach($transaksi as $t): 
                                    if ($count >= 5) break; // Limit to 5 recent transactions
                                ?>
                                <tr class="<?php echo $t['jenis']; ?>">
                                    <td><?php echo date('d/m/Y', strtotime($t['tanggal'])); ?></td>
                                    <td><?php echo $t['keterangan']; ?></td>
                                    <td class="<?php echo $t['jenis'] == 'Pemasukan' ? 'text-success' : 'text-danger'; ?>">
                                        Rp <?php echo number_format($t['jumlah'], 0, ',', '.'); ?>
                                    </td>
                                </tr>
                                <?php 
                                    $count++;
                                endforeach; 
                                
                                if(count($transaksi) == 0):
                                ?>
                                <tr>
                                    <td colspan="3" class="no-data">Belum ada transaksi</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Second Column: Notifications -->
                    
                    
                    <!-- Third Column: Pending Requests -->
                    
                </div>
            </div>
        </section>    </div>
</div>
<!-- Mobile action button -->
<div class="mobile-fab" id="mobile-fab">
    <i class="fas fa-plus"></i>
</div>
<!-- Mobile menu overlay -->
<div class="overlay" id="overlay"></div>

<script>
    // Mobile FAB actions
    document.getElementById('mobile-fab').addEventListener('click', function() {
        showToast('Fitur tambah transaksi akan segera tersedia', 'info');
    });
    
    // Close sidebar when clicking overlay
    document.getElementById('overlay').addEventListener('click', function() {
        document.getElementById('sidebar').classList.add('collapsed');
        document.getElementById('main-content').classList.add('expanded');
        document.body.classList.remove('overlay-active');
    });
</script>

<script>
    // Debug function to check if Chart.js is loaded
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, checking Chart.js');
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded!');
        } else {
            console.log('Chart.js is loaded correctly.');
        }
        
        const chartCanvas = document.getElementById('financeChart');
        if (!chartCanvas) {
            console.error('Canvas element not found!');
        } else {
            console.log('Canvas element found:', chartCanvas);
        }
    });

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
                    'rgba(238, 173, 85, 0.8)',
                    'rgba(255, 82, 82, 0.8)',
                    'rgba(45, 102, 74, 0.8)'
                ],
                borderColor: [
                    'rgba(238, 173, 85, 1)',
                    'rgba(255, 82, 82, 1)',
                    'rgba(45, 102, 74, 1)'
                ],
                borderWidth: 1,
                borderRadius: 8,
                maxBarThickness: 100
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            layout: {
                padding: 10
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    padding: 10,
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 14
                    },
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        font: {
                            size: 12
                        },
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 14
                        }
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });
</script>

<script>
    // Handle expandable transaction list
    const expandBtn = document.getElementById('expandTransactions');
    if (expandBtn) {
        expandBtn.addEventListener('click', function() {
            const extraTransactions = document.querySelectorAll('.extra-transaction');
            const isExpanded = this.getAttribute('data-expanded') === 'true';
            
            extraTransactions.forEach(tr => {
                tr.style.display = isExpanded ? 'none' : 'table-row';
            });
            
            this.setAttribute('data-expanded', !isExpanded);
            
            if (!isExpanded) {
                this.innerHTML = '<i class="fas fa-chevron-up"></i> Sembunyikan';
            } else {
                this.innerHTML = '<i class="fas fa-chevron-down"></i> Tampilkan Semua';
            }
        });
    }
</script>

<script>
    // Initialize the UKM Saldo Pie Chart
    document.addEventListener('DOMContentLoaded', function() {
        // Get data for all UKM saldo
        <?php 
        $finance = new Finance();
        $allUkmSaldo = $finance->getAllUkmSaldo();
        ?>
        
        // Prepare data for pie chart
        const ukmLabels = [];
        const ukmSaldoData = [];
        const backgroundColors = [];
        
        <?php foreach($allUkmSaldo as $index => $ukmData): ?>
            ukmLabels.push('<?php echo $ukmData['nama_ukm']; ?>');
            ukmSaldoData.push(<?php echo $ukmData['saldo']; ?>);
            // Generate different colors for each UKM
            backgroundColors.push(getRandomColor(<?php echo $index; ?>));
        <?php endforeach; ?>
        
        // Generate nice colors using hue rotation
        function getRandomColor(index) {
            const hue = index * 137.5 % 360; // Golden angle approximation for good distribution
            return `hsl(${hue}, 70%, 60%)`;
        }
        
        // Create the pie chart if canvas exists
        const ukmsaldoCanvas = document.getElementById('ukm-saldo-chart');
        if (ukmsaldoCanvas) {
            new Chart(ukmsaldoCanvas, {
                type: 'pie',
                data: {
                    labels: ukmLabels,
                    datasets: [{
                        data: ukmSaldoData,
                        backgroundColor: backgroundColors,
                        borderWidth: 1,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    layout: {
                        padding: 10
                    },
                    plugins: {
                        legend: {
                            display: false // Hide legend since we show the info on the side
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw;
                                    const total = context.chart.getDatasetMeta(0).total;
                                    const percentage = Math.round(value / total * 100);
                                    return `${label}: Rp ${new Intl.NumberFormat('id-ID').format(value)} (${percentage}%)`;
                                }
                            }
                        },
                        datalabels: {
                            formatter: function(value, context) {
                                // Format differently based on available space
                                const total = context.chart.getDatasetMeta(0).total;
                                const percentage = Math.round(value / total * 100);
                                if (percentage < 5) {
                                    return '';  // Hide labels for small slices
                                }
                                // Shorten the number format for smaller screens
                                const valueInM = value / 1000000;
                                return 'Rp ' + valueInM.toFixed(1) + 'M';
                            },
                            color: '#fff',
                            font: {
                                weight: 'bold',
                                size: function(context) {
                                    const width = context.chart.width;
                                    return Math.round(width / 32);
                                }
                            },
                            textShadow: true,
                            textStrokeColor: 'rgba(0, 0, 0, 0.5)',
                            textStrokeWidth: 3,
                            align: 'center',
                            anchor: 'center'
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }
    });
</script>

<?php else: ?>
    <div class="no-ukm">
        <p>Tidak ada UKM yang tersedia. Silakan hubungi administrator.</p>
    </div>
<?php endif; ?>

<script src="js/profile.js"></script>

<!-- Improved Sidebar Responsiveness -->
<script>
    // When the page loads, check if we need to apply mobile layout
    document.addEventListener('DOMContentLoaded', function() {
        adjustLayoutForScreenSize();
        
        // Listen for window resize and adjust accordingly
        window.addEventListener('resize', adjustLayoutForScreenSize);
        
        function adjustLayoutForScreenSize() {
            const isMobile = window.innerWidth <= 768;
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            
            if (isMobile) {
                // On mobile, we start with sidebar collapsed
                if (!sidebar.classList.contains('collapsed')) {
                    sidebar.classList.add('collapsed');
                }
                
                // Set main content to full width
                if (mainContent.classList.contains('expanded')) {
                    mainContent.classList.remove('expanded');
                }
            }
        }
    });
</script>

<!-- Override Dark Mode Styles -->
<style>
    /* Global reset of dark mode */
    body.dark-mode,
    body.dark-mode *,
    body[class*='dark-mode'] * {
        background-color: initial !important;
        color: initial !important;
        border-color: initial !important;
        box-shadow: initial !important;
    }
    
    /* Remove any dark mode classes on body */
    body {
        background-color: var(--background-color) !important;
        color: var(--text-color) !important;
    }
    
    /* Hide content-toggle */
    .content-toggle {
        display: none !important;
    }
    
    #content-toggle-btn {
        display: none !important;
    }

    /* Adjust layout for grid */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .dashboard-grid .recent-transactions {
        grid-column: span 1; /* Make it span the single column */
    }
</style>

<!-- Script to remove dark mode functionality -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Remove dark-mode class from body and any elements
        document.body.classList.remove('dark-mode');
        
        // Clear theme preference
        localStorage.removeItem('theme');
        
        // Prevent theme toggle from working by removing event listeners
        const themeToggleCheck = document.getElementById('theme-toggle');
        if (themeToggleCheck) {
            const newToggle = themeToggleCheck.cloneNode(true);
            if (themeToggleCheck.parentNode) {
                themeToggleCheck.parentNode.replaceChild(newToggle, themeToggleCheck);
            }
        }
    });
</script>

</body>
</html>
