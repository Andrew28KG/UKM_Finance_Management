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

// Get UKM name for display
$ukm_name = "";
if ($ukm_id) {
    foreach($ukms as $ukm) {
        if($ukm['id'] == $ukm_id) {
            $ukm_name = $ukm['nama_ukm'];
            break;
        }
    }
}

// Get financial data for the UKM
$laporan = $finance->getLaporanKeuangan($ukm_id);
$transaksi = $finance->getTransaksi($ukm_id, 5); // Get only the last 5 transactions

// Create mock wallet data - in a real app, this would be retrieved from the database
$wallet = [
    'cash_balance' => isset($laporan['saldo']) ? $laporan['saldo'] : 0,
    'pending_requests' => 2,
    'last_updated' => date('Y-m-d H:i:s'),
    'monthly_budget' => 5000000,
    'budget_remaining' => 3500000,
    'budget_spent' => 1500000
];

// Wallet cards data
$wallet_cards = [
    [
        'name' => 'Kas Utama',
        'balance' => $wallet['cash_balance'],
        'icon' => 'fas fa-money-bill-wave',
        'color' => 'primary'
    ],
    [
        'name' => 'Dana Kegiatan',
        'balance' => $wallet['budget_remaining'],
        'icon' => 'fas fa-calendar-alt',
        'color' => 'secondary'
    ],
    [
        'name' => 'Dana Darurat',
        'balance' => 1000000,
        'icon' => 'fas fa-first-aid',
        'color' => 'danger'
    ]
];
?>

<div class="wrapper">
    <?php include('inc/sidebar.php'); ?>
    <div id="main-content">
        <div class="content-toggle">
            <button id="content-toggle-btn">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>
        <section class="wallet-page">
            <div class="page-header">
                <h1>Dompet Saya</h1>
                <p class="ukm-title">
                    <?php echo $ukm_name ? "$ukm_name" : "Pilih UKM terlebih dahulu"; ?>
                </p>
            </div>
            
            <div class="wallet-overview">
                <?php foreach ($wallet_cards as $card): ?>
                    <div class="wallet-card <?php echo $card['color']; ?>">
                        <div class="wallet-card-icon">
                            <i class="<?php echo $card['icon']; ?>"></i>
                        </div>
                        <div class="wallet-card-content">
                            <h3><?php echo $card['name']; ?></h3>
                            <h2>Rp <?php echo number_format($card['balance'], 0, ',', '.'); ?></h2>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="budget-progress">
                <h2>Anggaran Bulan Ini</h2>
                <div class="budget-details">
                    <div class="budget-info">
                        <span>Total Anggaran:</span>
                        <span>Rp <?php echo number_format($wallet['monthly_budget'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="budget-info">
                        <span>Terpakai:</span>
                        <span>Rp <?php echo number_format($wallet['budget_spent'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="budget-info">
                        <span>Tersisa:</span>
                        <span>Rp <?php echo number_format($wallet['budget_remaining'], 0, ',', '.'); ?></span>
                    </div>
                </div>
                <div class="progress-bar-container">
                    <?php $percentage = ($wallet['budget_spent'] / $wallet['monthly_budget']) * 100; ?>
                    <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                </div>
                <div class="progress-label">
                    <span>0%</span>
                    <span><?php echo round($percentage, 1); ?>% Terpakai</span>
                    <span>100%</span>
                </div>
            </div>
            
            <div class="recent-transactions">
                <div class="card-header">
                    <h2>Transaksi Terbaru</h2>
                    <a href="transaksi.php" class="btn btn-sm btn-outline">Lihat Semua</a>
                </div>
                <div class="transaction-list">
                    <?php if (empty($transaksi)): ?>
                        <div class="empty-state">
                            <i class="fas fa-receipt"></i>
                            <p>Belum ada transaksi</p>
                        </div>
                    <?php else: ?>
                        <table class="transaction-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                    <th>Jenis</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($transaksi as $t): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($t['tanggal'])); ?></td>
                                        <td><?php echo $t['keterangan']; ?></td>
                                        <td>
                                            <span class="badge <?php echo $t['jenis'] == 'Pemasukan' ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo $t['jenis']; ?>
                                            </span>
                                        </td>
                                        <td class="<?php echo $t['jenis'] == 'Pemasukan' ? 'text-success' : 'text-danger'; ?>">
                                            Rp <?php echo number_format($t['jumlah'], 0, ',', '.'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="pending-requests">
                <div class="card-header">
                    <h2>Pengajuan Dana</h2>
                </div>
                <div class="request-list">
                    <?php if ($wallet['pending_requests'] == 0): ?>
                        <div class="empty-state">
                            <i class="fas fa-file-invoice"></i>
                            <p>Tidak ada pengajuan dana yang tertunda</p>
                        </div>
                    <?php else: ?>
                        <!-- Mock pending requests data -->
                        <div class="request-card">
                            <div class="request-info">
                                <h3>Pembelian Alat Musik</h3>
                                <p>Diajukan oleh: Ketua UKM</p>
                                <p class="request-date">15 Mei 2023</p>
                            </div>
                            <div class="request-amount">
                                <h3>Rp 750.000</h3>
                                <span class="badge badge-warning">Menunggu Persetujuan</span>
                            </div>
                        </div>
                        <div class="request-card">
                            <div class="request-info">
                                <h3>Konsumsi Rapat Anggota</h3>
                                <p>Diajukan oleh: Sekretaris</p>
                                <p class="request-date">12 Mei 2023</p>
                            </div>
                            <div class="request-amount">
                                <h3>Rp 300.000</h3>
                                <span class="badge badge-warning">Menunggu Persetujuan</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
// Content toggle button functionality
document.getElementById('content-toggle-btn').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('collapsed');
    document.getElementById('main-content').classList.toggle('expanded');
    
    // Save sidebar state to localStorage
    if (document.getElementById('sidebar').classList.contains('collapsed')) {
        localStorage.setItem('sidebar', 'collapsed');
    } else {
        localStorage.setItem('sidebar', 'expanded');
    }
});

// Mobile detection on page load
function checkMobile() {
    if (window.innerWidth <= 768) {
        document.getElementById('sidebar').classList.add('collapsed');
        document.getElementById('main-content').classList.add('expanded');
    } else if (localStorage.getItem('sidebar') !== 'collapsed') {
        document.getElementById('sidebar').classList.remove('collapsed');
        document.getElementById('main-content').classList.remove('expanded');
    }
}

// Check on page load and resize
window.addEventListener('load', checkMobile);
window.addEventListener('resize', checkMobile);
</script>
