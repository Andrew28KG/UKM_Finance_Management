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

// Get search query
$search_query = isset($_GET['query']) ? $_GET['query'] : '';

// Function to perform search (this would connect to database in a real implementation)
function performSearch($query, $ukm_id, $finance) {
    $results = [
        'transactions' => [],
        'reports' => [],
        'activities' => []
    ];
    
    // If we have a search term
    if (!empty($query)) {
        // Search in transactions
        $allTransactions = $finance->getTransaksi($ukm_id);
        foreach ($allTransactions as $transaction) {
            if (stripos($transaction['keterangan'], $query) !== false || 
                stripos($transaction['jenis'], $query) !== false ||
                stripos($transaction['kategori'], $query) !== false) {
                $results['transactions'][] = $transaction;
            }
        }
        
        // In a real implementation, you would also search in other areas
        // like reports and activities
    }
    
    return $results;
}

// Get search results
$results = performSearch($search_query, $ukm_id, $finance);
$total_results = count($results['transactions']) + count($results['reports']) + count($results['activities']);
?>

<div class="wrapper">
    <?php include('inc/sidebar.php'); ?>
    <div id="main-content">
        <div class="content-toggle">
            <button id="content-toggle-btn">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>
        <section class="search-results-page">
            <div class="page-header">
                <h1>Hasil Pencarian</h1>
                <p class="ukm-title">
                    <?php echo $ukm_name ? "$ukm_name" : "Pilih UKM terlebih dahulu"; ?>
                </p>
            </div>
            
            <div class="search-summary">
                <div class="search-info">
                    <p>Hasil untuk: <strong>"<?php echo htmlspecialchars($search_query); ?>"</strong></p>
                    <p><?php echo $total_results; ?> hasil ditemukan</p>
                </div>
                <div class="search-container">
                    <form action="search_results.php" method="GET" class="search-form">
                        <input type="text" name="query" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Cari transaksi, laporan, atau kegiatan..." class="search-input">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <?php if ($total_results == 0): ?>
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <p>Tidak ada hasil yang sesuai dengan pencarian Anda</p>
                    <p class="empty-state-hint">Coba dengan kata kunci lain atau periksa ejaan</p>
                </div>
            <?php else: ?>
                <!-- Transaction Results -->
                <?php if (!empty($results['transactions'])): ?>
                    <div class="result-section">
                        <h2>Transaksi</h2>
                        <div class="transaction-list">
                            <table class="transaction-table">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Keterangan</th>
                                        <th>Jenis</th>
                                        <th>Kategori</th>
                                        <th>Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($results['transactions'] as $t): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($t['tanggal'])); ?></td>
                                            <td><?php echo $t['keterangan']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $t['jenis'] == 'Pemasukan' ? 'badge-success' : 'badge-danger'; ?>">
                                                    <?php echo $t['jenis']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $t['kategori']; ?></td>
                                            <td class="<?php echo $t['jenis'] == 'Pemasukan' ? 'text-success' : 'text-danger'; ?>">
                                                Rp <?php echo number_format($t['jumlah'], 0, ',', '.'); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Other result sections would go here (reports, activities, etc.) -->
            <?php endif; ?>
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
