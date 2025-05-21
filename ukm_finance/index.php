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

<div class="wrapper">
    <?php include('inc/sidebar.php'); ?>      <div id="main-content">
        <div class="content-toggle">
            <button id="content-toggle-btn">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>        <section class="dashboard">
            <div class="welcome-section">
                <div class="welcome-message">
                    <h1>Selamat Datang, <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Pengguna'; ?></h1>
                    <p>Kelola keuangan UKM Anda dengan mudah dan transparan</p>
                </div>
                <div class="search-container">
                    <form action="search_results.php" method="GET" class="search-form">
                        <input type="text" name="query" placeholder="Cari transaksi, laporan, atau kegiatan..." class="search-input">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
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
                
                // Mock notification data for dashboard
                $notifications = [
                    [
                        'id' => 1,
                        'title' => 'Pengajuan Dana Diterima',
                        'message' => 'Pengajuan dana untuk kegiatan Workshop Fotografi telah disetujui.',
                        'date' => '2023-05-15 14:30:00',
                        'is_read' => false,
                        'type' => 'success'
                    ],
                    [
                        'id' => 2,
                        'title' => 'Transaksi Baru',
                        'message' => 'Bendahara telah menambahkan transaksi pengeluaran baru sebesar Rp. 500.000.',
                        'date' => '2023-05-12 10:15:00',
                        'is_read' => true,
                        'type' => 'info'
                    ],
                    [
                        'id' => 3,
                        'title' => 'Pengingat Pelaporan',
                        'message' => 'Jangan lupa untuk menyerahkan laporan keuangan bulanan sebelum tanggal 30.',
                        'date' => '2023-05-10 09:00:00',
                        'is_read' => false,
                        'type' => 'warning'
                    ]
                ];
                
                // Mock pending requests
                $requests = [
                    [
                        'id' => 1,
                        'title' => 'Pembelian Alat Musik',
                        'applicant' => 'Ketua UKM',
                        'date' => '2023-05-15',
                        'amount' => 750000,
                        'status' => 'pending'
                    ],
                    [
                        'id' => 2,
                        'title' => 'Konsumsi Rapat Anggota',
                        'applicant' => 'Sekretaris',
                        'date' => '2023-05-12',
                        'amount' => 300000,
                        'status' => 'pending'
                    ]
                ];
                ?>
                <script>
                    function showLoading() {
                        document.getElementById('loadingOverlay').style.display = 'flex';
                    }
                </script>                <div class="dashboard-column">
                    <div class="finance-summary-container">
                        <div class="finance-summary-header">
                            <h2>Ringkasan Keuangan</h2>
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
                                </div>                            </div>
                            
                            <div class="chart-container">
                                <div class="chart-card">                                    <h3>Grafik Keuangan</h3>
                                    <canvas id="financeChart" 
                                        data-pemasukan="<?php echo $laporan['pemasukan']; ?>" 
                                        data-pengeluaran="<?php echo $laporan['pengeluaran']; ?>" 
                                        data-saldo="<?php echo $laporan['saldo']; ?>">
                                    </canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    
                    <!-- Second column: Recent Transactions and Notifications -->
                    <div class="dashboard-grid">
                        <div class="recent-transactions compact">
                            <div class="section-header">
                                <h2>Transaksi Terbaru</h2>
                                <div class="section-actions">
                                    <button class="btn-sm toggle-transactions" id="expandTransactions">
                                        <i class="fas fa-chevron-down"></i> Tampilkan Semua
                                    </button>
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
                                        $hidden = $count >= 3 ? 'style="display:none;" class="extra-transaction"' : '';
                                    ?>
                                    <tr class="<?php echo $t['jenis']; ?>" <?php echo $hidden; ?>>
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
                        
                        <div class="dashboard-notifications">
                            <div class="section-header">
                                <h2>Notifikasi</h2>
                                <a href="notifications.php" class="btn-sm">Lihat Semua</a>
                            </div>
                            
                            <div class="dashboard-notification-list">
                                <?php if (empty($notifications)): ?>
                                    <div class="empty-state-compact">
                                        <i class="fas fa-bell-slash"></i>
                                        <p>Tidak ada notifikasi</p>
                                    </div>
                                <?php else: ?>
                                    <?php 
                                    $notifCount = 0;
                                    foreach ($notifications as $notification): 
                                        if($notifCount >= 2) break; // Only show 2 notifications
                                    ?>
                                        <div class="notification-card-compact <?php echo $notification['is_read'] ? 'read' : 'unread'; ?> <?php echo $notification['type']; ?>">
                                            <div class="notification-icon-compact">
                                                <?php if ($notification['type'] == 'success'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php elseif ($notification['type'] == 'warning'): ?>
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                <?php elseif ($notification['type'] == 'danger'): ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-info-circle"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="notification-content-compact">
                                                <div class="notification-header-compact">
                                                    <h3><?php echo $notification['title']; ?></h3>
                                                    <span class="notification-time-compact"><?php echo date('d M', strtotime($notification['date'])); ?></span>
                                                </div>
                                                <p><?php echo substr($notification['message'], 0, 60) . (strlen($notification['message']) > 60 ? '...' : ''); ?></p>
                                            </div>
                                        </div>
                                    <?php 
                                        $notifCount++;
                                    endforeach; 
                                    ?>
                                <?php endif; ?>
                            </div>
                        </div>
                                            <!-- Third column: Pending Requests -->
                        <div class="dashboard-column">
                            <div class="pending-requests-dashboard">
                                <div class="section-header">
                                    <h2>Pengajuan Dana</h2>
                                    <a href="request.php" class="btn-sm">Lihat Semua</a>
                                </div>
                                
                                <div class="request-list-dashboard">
                                    <?php if (empty($requests)): ?>
                                        <div class="empty-state-compact">
                                            <i class="fas fa-file-invoice"></i>
                                            <p>Tidak ada pengajuan dana</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($requests as $request): ?>
                                            <div class="request-card-compact">
                                                <div class="request-info-compact">
                                                    <h3><?php echo $request['title']; ?></h3>
                                                    <p><?php echo $request['applicant']; ?> · <?php echo $request['date']; ?></p>
                                                </div>
                                                <div class="request-amount-compact">
                                                    <h3>Rp <?php echo number_format($request['amount'], 0, ',', '.'); ?></h3>
                                                    <span class="badge badge-warning">Menunggu</span>
                                                </div>                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                            maintainAspectRatio: false,
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
                                    }                                }
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
            <?php else: ?>
                <div class="no-ukm">
                    <p>Tidak ada UKM yang tersedia. Silakan hubungi administrator.</p>
                </div>
            <?php endif; ?>
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
