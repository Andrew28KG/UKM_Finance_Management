<?php
/**
 * Enhanced Profile Bar Section with Notifications Dropdown
 */
?>
<div class="top-right-section">
    <div class="search-container">
        <form action="search_results.php" method="GET" class="search-form">
            <input type="text" name="query" placeholder="Cari transaksi, laporan, atau kegiatan..." class="search-input">
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
    
    <div class="profile-actions">
        <!-- Notification Icon with Dropdown -->
        <div class="notification-dropdown">
            <button class="notification-btn" id="notificationBtn">
                <i class="fas fa-bell"></i>
                <?php if (isset($notifications) && count(array_filter($notifications, function($n) { return !$n['is_read']; })) > 0): ?>
                <span class="notification-badge-count"><?php echo count(array_filter($notifications, function($n) { return !$n['is_read']; })); ?></span>
                <?php endif; ?>
            </button>
            <div class="dropdown-content notification-dropdown-content" id="notificationDropdown">
                <div class="dropdown-header">
                    <h3>Notifikasi</h3>
                    <a href="#" class="mark-all-read">Tandai Semua Dibaca</a>
                </div>
                <div class="dropdown-body notification-list">
                    <?php if (isset($notifications) && !empty($notifications)): ?>
                        <?php 
                        $notifCount = 0;
                        foreach ($notifications as $notification): 
                            if($notifCount >= 5) break; // Only show 5 notifications
                        ?>
                            <div class="dropdown-notification-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?> <?php echo $notification['type']; ?>">
                                <div class="notification-icon-small">
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
                                <div class="notification-content-small">
                                    <h4><?php echo $notification['title']; ?></h4>
                                    <p><?php echo substr($notification['message'], 0, 40) . (strlen($notification['message']) > 40 ? '...' : ''); ?></p>
                                    <span class="notification-time-small"><?php echo date('d M', strtotime($notification['date'])); ?></span>
                                </div>
                            </div>
                        <?php 
                            $notifCount++;
                        endforeach; 
                        ?>
                    <?php else: ?>
                        <div class="empty-dropdown-state">
                            <i class="fas fa-bell-slash"></i>
                            <p>Tidak ada notifikasi</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="dropdown-footer">
                    <a href="notifications.php">Lihat Semua Notifikasi</a>
                </div>
            </div>
        </div>
        
        <!-- User Profile Icon with Dropdown -->
        <div class="profile-dropdown">
            <button class="profile-btn" id="profileBtn">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <span class="profile-name-short"><?php echo isset($_SESSION['user_name']) ? explode(' ', $_SESSION['user_name'])[0] : 'Pengguna'; ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="dropdown-content profile-dropdown-content" id="profileDropdown">
                <div class="dropdown-header profile-header">
                    <div class="profile-avatar-large">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="profile-details">
                        <h3><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Pengguna'; ?></h3>
                        <p><?php echo isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'Pengunjung'; ?></p>
                    </div>
                </div>
                <div class="dropdown-body">
                    <a href="profile.php" class="dropdown-item">
                        <i class="fas fa-user-circle"></i> Profil Saya
                    </a>
                    <a href="settings.php" class="dropdown-item">
                        <i class="fas fa-cog"></i> Pengaturan
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt"></i> Keluar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
