// Toast notification system for UKM Finance Management

// Function to show a toast notification
function showToast(message, type = 'info', duration = 3000) {
    // Remove any existing toasts
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    
    // Add icon based on type
    let icon = '';
    switch(type) {
        case 'success':
            icon = '<i class="fas fa-check-circle"></i> ';
            break;
        case 'error':
            icon = '<i class="fas fa-times-circle"></i> ';
            break;
        case 'warning':
            icon = '<i class="fas fa-exclamation-triangle"></i> ';
            break;
        default:
            icon = '<i class="fas fa-info-circle"></i> ';
    }
    
    // Set toast content
    toast.innerHTML = `${icon} ${message}`;
    
    // Add toast to document
    document.body.appendChild(toast);
    
    // Remove toast after duration
    setTimeout(() => {
        toast.classList.add('hide');
        setTimeout(() => {
            toast.remove();
        }, 300); // Match the animation duration
    }, duration);
}

// Helper functions for specific toast types
function showSuccessToast(message, duration = 3000) {
    showToast(message, 'success', duration);
}

function showErrorToast(message, duration = 3000) {
    showToast(message, 'error', duration);
}

function showWarningToast(message, duration = 3000) {
    showToast(message, 'warning', duration);
}

function showInfoToast(message, duration = 3000) {
    showToast(message, 'info', duration);
}

// Function to add loading state to buttons
function setButtonLoading(button, isLoading = true) {
    if (isLoading) {
        const originalText = button.innerHTML;
        button.setAttribute('data-original-text', originalText);
        button.innerHTML = '<span class="loading-spinner"></span> Loading...';
        button.disabled = true;
    } else {
        const originalText = button.getAttribute('data-original-text');
        button.innerHTML = originalText;
        button.removeAttribute('data-original-text');
        button.disabled = false;
    }
}

// Initialize common UI elements
document.addEventListener('DOMContentLoaded', function() {
    // Initialize filter period button
    const filterButton = document.querySelector('.filter-period');
    if (filterButton) {
        filterButton.addEventListener('click', function() {
            showInfoToast('Fitur filter periode akan segera tersedia!');
        });
    }
    
    // Add confirmation to logout button
    const logoutButton = document.querySelector('.logout a');
    if (logoutButton) {
        logoutButton.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin keluar?')) {
                e.preventDefault();
            }
        });
    }
    
    // Add smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Show welcome toast on first visit
    const isFirstVisit = !localStorage.getItem('visited');
    if (isFirstVisit) {
        setTimeout(() => {
            showSuccessToast('Selamat datang di UKM Finance Management!', 5000);
            localStorage.setItem('visited', 'true');
        }, 1000);
    }
});
