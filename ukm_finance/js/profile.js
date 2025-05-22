// Profile and Notification Dropdown Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Profile Dropdown
    const profileBtn = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdown');
    const profileDropdownContainer = document.querySelector('.profile-dropdown');
    
    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close notification dropdown if open
            if (notificationDropdown && notificationDropdown.classList.contains('show')) {
                notificationDropdown.classList.remove('show');
                notificationDropdownContainer.classList.remove('show');
            }
            
            // Toggle profile dropdown
            profileDropdown.classList.toggle('show');
            profileDropdownContainer.classList.toggle('show');
        });
    }
    
    // Notification Dropdown
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationDropdownContainer = document.querySelector('.notification-dropdown');
    
    if (notificationBtn && notificationDropdown) {
        notificationBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close profile dropdown if open
            if (profileDropdown && profileDropdown.classList.contains('show')) {
                profileDropdown.classList.remove('show');
                profileDropdownContainer.classList.remove('show');
            }
            
            // Toggle notification dropdown
            notificationDropdown.classList.toggle('show');
            notificationDropdownContainer.classList.toggle('show');
        });
    }
    
    // Mark all as read functionality
    const markAllReadBtn = document.querySelector('.mark-all-read');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // In a real application, this would send an AJAX request to mark all notifications as read
            // For this demo, we'll just update the UI
            const unreadItems = document.querySelectorAll('.dropdown-notification-item.unread');
            unreadItems.forEach(item => {
                item.classList.remove('unread');
                item.classList.add('read');
            });
            
            // Hide the notification badge counter
            const badge = document.querySelector('.notification-badge-count');
            if (badge) {
                badge.style.display = 'none';
            }
            
            // Show a toast message (if toast.js is included)
            if (typeof showToast === 'function') {
                showToast('Semua notifikasi telah ditandai sebagai telah dibaca', 'success');
            }
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (profileDropdown && !profileDropdownContainer.contains(e.target)) {
            profileDropdown.classList.remove('show');
            profileDropdownContainer.classList.remove('show');
        }
        
        if (notificationDropdown && !notificationDropdownContainer.contains(e.target)) {
            notificationDropdown.classList.remove('show');
            notificationDropdownContainer.classList.remove('show');
        }
    });
    
    // Make notification items clickable
    const notificationItems = document.querySelectorAll('.dropdown-notification-item');
    if (notificationItems) {
        notificationItems.forEach(item => {
            item.addEventListener('click', function() {
                // In a real app this would redirect to the notification detail or mark as read
                if (item.classList.contains('unread')) {
                    item.classList.remove('unread');
                    item.classList.add('read');
                    
                    // Update notification counter
                    updateNotificationCounter();
                    
                    // Show a toast message (if toast.js is included)
                    if (typeof showToast === 'function') {
                        showToast('Notifikasi ditandai sebagai telah dibaca', 'info');
                    }
                }
            });
        });
    }
    
    // Function to update the notification counter
    function updateNotificationCounter() {
        const unreadItems = document.querySelectorAll('.dropdown-notification-item.unread');
        const badge = document.querySelector('.notification-badge-count');
        
        if (badge) {
            if (unreadItems.length > 0) {
                badge.textContent = unreadItems.length;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    }
    
    // Initialize notification counter on page load
    updateNotificationCounter();
});
