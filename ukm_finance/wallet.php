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
$settings = $finance->getUkmSettings($ukm_id);

// Add checks for null values
$laporan = $laporan ?? []; // If $laporan is null, set it to an empty array
$transaksi = $transaksi ?? []; // If $transaksi is null, set it to an empty array

// Provide default values for settings keys if $settings is null or keys are missing
$default_settings = [
    'monthly_budget' => 0,
    'emergency_fund' => 0
];
$settings = $settings ?? $default_settings;
$settings = array_merge($default_settings, $settings); // Ensure all default keys exist

// Calculate wallet data from database
$wallet = [
    'cash_balance' => isset($laporan['saldo']) ? $laporan['saldo'] : 0,
    'last_updated' => date('Y-m-d H:i:s'),
    'monthly_budget' => $settings['monthly_budget'],
    'budget_remaining' => ($settings['monthly_budget'] ?? 0) - ($laporan['pengeluaran'] ?? 0),
    'budget_spent' => ($laporan['pengeluaran'] ?? 0)
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
        'balance' => $settings['emergency_fund'],
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
        <section class="wallet-page">            <div class="page-header">
                <h1>Dompet Saya</h1>
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
            <?php else: ?>
                <div class="no-ukm">
                    <p>Tidak ada UKM yang tersedia. Silakan hubungi administrator.</p>
                </div>
            <?php endif; ?>

            <p class="ukm-title">
                <?php echo $ukm_name ? "$ukm_name" : "Pilih UKM terlebih dahulu"; ?>
            </p>
            
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
                    <?php $percentage = ($wallet['monthly_budget'] > 0) ? ($wallet['budget_spent'] / $wallet['monthly_budget']) * 100 : 0; ?>
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
