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

// Dummy notification data
$notifications = [
    [
        'id' => 1,
        'title' => 'Pengajuan Dana Diterima',
        'message' => 'Pengajuan dana untuk kegiatan Workshop Fotografi telah disetujui oleh bendahara.',
        'date' => '2023-05-15 14:30:00',
        'is_read' => false,
        'type' => 'success'
    ],
    [
        'id' => 2,
        'title' => 'Transaksi Baru',
        'message' => 'Bendahara telah menambahkan transaksi pengeluaran baru sebesar Rp. 500.000 untuk pembelian peralatan.',
        'date' => '2023-05-12 10:15:00',
        'is_read' => true,
        'type' => 'info'
    ],
    [
        'id' => 3,
        'title' => 'Pengingat Pelaporan',
        'message' => 'Jangan lupa untuk menyerahkan laporan keuangan bulanan sebelum tanggal 30 bulan ini.',
        'date' => '2023-05-10 09:00:00',
        'is_read' => false,
        'type' => 'warning'
    ],
    [
        'id' => 4,
        'title' => 'Masalah Validasi Data',
        'message' => 'Terdapat kesalahan pada data transaksi dengan ID #3421. Mohon periksa kembali.',
        'date' => '2023-05-08 16:45:00',
        'is_read' => true,
        'type' => 'danger'
    ],
    [
        'id' => 5,
        'title' => 'Update Sistem',
        'message' => 'Sistem keuangan telah diperbarui dengan fitur-fitur baru. Klik untuk melihat detail perubahan.',
        'date' => '2023-05-05 08:30:00',
        'is_read' => false,
        'type' => 'info'
    ]
];

// Mark all notifications as read (if requested)
if (isset($_GET['mark_all_read'])) {
    // In a real app, you would update the database here
    foreach ($notifications as $key => $notification) {
        $notifications[$key]['is_read'] = true;
    }
}

// Mark specific notification as read (if requested)
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $notificationId = (int)$_GET['mark_read'];
    foreach ($notifications as $key => $notification) {
        if ($notification['id'] == $notificationId) {
            $notifications[$key]['is_read'] = true;
            break;
        }
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
        <section class="notifications-page">
            <div class="page-header">
                <h1>Notifikasi</h1>
                <?php include('inc/profile_bar.php'); ?>
            </div>
            
            <div class="notification-actions">
                <a href="?mark_all_read=1" class="btn btn-outline">Tandai Semua Dibaca</a>
            </div>
            
            <div class="notification-container">
                <?php if (empty($notifications)): ?>
                    <div class="empty-state">
                        <i class="fas fa-bell-slash"></i>
                        <p>Tidak ada notifikasi</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-card <?php echo $notification['is_read'] ? 'read' : 'unread'; ?> <?php echo $notification['type']; ?>">
                            <div class="notification-icon">
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
                            <div class="notification-content">
                                <div class="notification-header">
                                    <h3><?php echo $notification['title']; ?></h3>
                                    <span class="notification-time"><?php echo date('d M Y, H:i', strtotime($notification['date'])); ?></span>
                                </div>
                                <p><?php echo $notification['message']; ?></p>
                            </div>
                            <?php if (!$notification['is_read']): ?>
                                <a href="?mark_read=<?php echo $notification['id']; ?>" class="notification-badge">Baru</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<!-- Include profile.js for notification and profile functionality -->
<script src="js/profile.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Content toggle button functionality
    const contentToggleBtn = document.getElementById('content-toggle-btn');
    if (contentToggleBtn) {
        contentToggleBtn.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('main-content').classList.toggle('expanded');
            
            // Save sidebar state to localStorage
            if (document.getElementById('sidebar').classList.contains('collapsed')) {
                localStorage.setItem('sidebar', 'collapsed');
            } else {
                localStorage.setItem('sidebar', 'expanded');
            }
        });
    }

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
    checkMobile();
    window.addEventListener('resize', checkMobile);
    
    // Add click handler for notification cards
    const notificationCards = document.querySelectorAll('.notification-card');
    notificationCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Ignore if clicking on the "Mark as read" link
            if (e.target.classList.contains('notification-badge') || e.target.closest('.notification-badge')) return;
            
            // If it's unread, mark it as read
            if (this.classList.contains('unread')) {
                const badge = this.querySelector('.notification-badge');
                if (badge && badge.getAttribute('href')) {
                    window.location.href = badge.getAttribute('href');
                }
            }
        });
    });
});
</script>
