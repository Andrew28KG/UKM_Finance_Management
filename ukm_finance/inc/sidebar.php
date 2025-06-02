<?php
// You can put PHP code here if needed
// Assume $notificationCount is passed to this file
$notificationCount = isset($notificationCount) ? $notificationCount : 0;
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="assets/img/logo.svg" alt="UKM Finance" class="logo">
        <button id="sidebar-toggle" class="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    <div class="sidebar-menu">
        <ul>
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" style="--item-index: 0;">
                <a href="index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'transaksi.php') ? 'active' : ''; ?>" style="--item-index: 1;">
                <a href="transaksi.php">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Transaksi</span>
                </a>
            </li>
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'tambah_transaksi_external.php') ? 'active' : ''; ?>" style="--item-index: 2;">
                <a href="tambah_transaksi_external.php">
                    <i class="fas fa-external-link-alt"></i>
                    <span>Tambah Transaksi External</span>
                </a>
            </li>
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'wallet.php') ? 'active' : ''; ?>" style="--item-index: 3;">
                <a href="wallet.php">
                    <i class="fas fa-wallet"></i>
                    <span>Dompet Saya</span>
                </a>
            </li>
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'convert.php') ? 'active' : ''; ?>" style="--item-index: 4;">
                <a href="convert.php">
                    <i class="fas fa-file-code"></i>
                    <span>Konversi XML</span>
                </a>
            </li>
        </ul>
    </div>    <div class="sidebar-footer">
        <div class="logout">
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Keluar</span>
            </a>
        </div>
    </div>
</div>

<script>
    // Toggle sidebar on mobile
    document.getElementById('sidebar-toggle').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
        
        // Save sidebar state to localStorage
        if(sidebar.classList.contains('collapsed')) {
            localStorage.setItem('sidebar', 'collapsed');
        } else {
            localStorage.setItem('sidebar', 'expanded');
        }
        
        // Add overlay effect for mobile
        if (window.innerWidth <= 768) {
            if (sidebar.classList.contains('collapsed')) {
                document.body.classList.remove('overlay-active');
            } else {
                document.body.classList.add('overlay-active');
            }
        }
    });
    
    // Check for saved sidebar state and initialize other functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Remove any dark mode class if it exists
        document.body.classList.remove('dark-mode');
        
        // Clear any saved theme preference
        localStorage.removeItem('theme');
        
        // Check for saved sidebar state
        const sidebarState = localStorage.getItem('sidebar');
        if(sidebarState === 'collapsed') {
            document.getElementById('sidebar').classList.add('collapsed');
            document.getElementById('main-content').classList.add('expanded');
        }
        
        // Add mobile detection
        function checkMobile() {
            if(window.innerWidth <= 768) {
                document.getElementById('sidebar').classList.add('collapsed');
                document.getElementById('main-content').classList.add('expanded');
            } else if(sidebarState !== 'collapsed') {
                document.getElementById('sidebar').classList.remove('collapsed');
                document.getElementById('main-content').classList.remove('expanded');
            }
        }
        
        // Initialize on page load
        checkMobile();
        
        // Listen for window resize events
        window.addEventListener('resize', checkMobile);
        
        // Add touch swipe support for mobile
        let touchStartX = 0;
        let touchEndX = 0;
        
        document.addEventListener('touchstart', e => {
            touchStartX = e.changedTouches[0].screenX;
        }, false);
        
        document.addEventListener('touchend', e => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, false);
        
        function handleSwipe() {
            const sidebar = document.getElementById('sidebar');
            const swipeThreshold = 100;
            
            if (touchEndX - touchStartX > swipeThreshold) {
                // Swipe right - open sidebar
                if (sidebar.classList.contains('collapsed')) {
                    sidebar.classList.remove('collapsed');
                    document.getElementById('main-content').classList.remove('expanded');
                    document.body.classList.add('overlay-active');
                    localStorage.setItem('sidebar', 'expanded');
                }
            } else if (touchStartX - touchEndX > swipeThreshold) {
                // Swipe left - close sidebar
                if (!sidebar.classList.contains('collapsed') && window.innerWidth <= 768) {
                    sidebar.classList.add('collapsed');
                    document.getElementById('main-content').classList.add('expanded');
                    document.body.classList.remove('overlay-active');
                    localStorage.setItem('sidebar', 'collapsed');
                }
            }
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !sidebar.classList.contains('collapsed') &&
                !e.target.closest('#content-toggle-btn')) {
                sidebar.classList.add('collapsed');
                document.getElementById('main-content').classList.add('expanded');
                document.body.classList.remove('overlay-active');
                localStorage.setItem('sidebar', 'collapsed');
            }
        });
    });
</script>