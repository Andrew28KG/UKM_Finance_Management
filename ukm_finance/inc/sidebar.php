<?php
// You can put PHP code here if needed
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
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                <a href="index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'transaksi.php') ? 'active' : ''; ?>">
                <a href="transaksi.php">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Transaksi</span>
                </a>
            </li>
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'notifications.php') ? 'active' : ''; ?>">
                <a href="notifications.php">
                    <i class="fas fa-bell"></i>
                    <span>Notifikasi</span>
                    <span class="notification-badge">3</span>
                </a>
            </li>
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'wallet.php') ? 'active' : ''; ?>">
                <a href="wallet.php">
                    <i class="fas fa-wallet"></i>
                    <span>Dompet Saya</span>
                </a>
            </li>            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'request.php') ? 'active' : ''; ?>">
                <a href="request.php">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Request Budget</span>
                </a>
            </li>
        </ul>
    </div>
    <div class="sidebar-footer">
        <div class="theme-toggle">
            <label class="toggle-switch">
                <input type="checkbox" id="theme-toggle">
                <span class="toggle-slider"></span>
                <i class="fas fa-sun"></i>
                <i class="fas fa-moon"></i>
            </label>
        </div>
        <div class="logout">
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Keluar</span>
            </a>
        </div>
    </div>
</div>

<script>    // Toggle sidebar on mobile
    document.getElementById('sidebar-toggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('collapsed');
        document.getElementById('main-content').classList.toggle('expanded');
        
        // Add overlay effect for mobile
        if (window.innerWidth <= 768) {
            if (document.getElementById('sidebar').classList.contains('collapsed')) {
                document.body.classList.remove('overlay-active');
            } else {
                document.body.classList.add('overlay-active');
            }
        }
    });
    
    // Theme toggle functionality
    document.getElementById('theme-toggle').addEventListener('change', function() {
        if(this.checked) {
            document.body.classList.add('dark-mode');
            localStorage.setItem('theme', 'dark');
        } else {
            document.body.classList.remove('dark-mode');
            localStorage.setItem('theme', 'light');
        }
    });
    
    // Check for saved theme preference
    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('theme');
        if(savedTheme === 'dark') {
            document.getElementById('theme-toggle').checked = true;
            document.body.classList.add('dark-mode');
        }
        
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
    });
      // Save sidebar state
    document.getElementById('sidebar-toggle').addEventListener('click', function() {
        if(document.getElementById('sidebar').classList.contains('collapsed')) {
            localStorage.setItem('sidebar', 'collapsed');
        } else {
            localStorage.setItem('sidebar', 'expanded');
        }
    });
    
    // Content toggle button functionality
    if(document.getElementById('content-toggle-btn')) {
        document.getElementById('content-toggle-btn').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('main-content').classList.toggle('expanded');
            
            if(document.getElementById('sidebar').classList.contains('collapsed')) {
                localStorage.setItem('sidebar', 'collapsed');
            } else {
                localStorage.setItem('sidebar', 'expanded');
            }
        });
    }
</script>