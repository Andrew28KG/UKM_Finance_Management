// Enhanced cards for UKM Finance Management
document.addEventListener('DOMContentLoaded', function() {
    // Get all cards
    const cards = document.querySelectorAll('.summary-card');
    
    // Add hover animation effect
    cards.forEach((card, index) => {
        // Add sequence animation delay
        card.style.animationDelay = `${index * 0.1}s`;
        
        card.addEventListener('mouseenter', function() {
            this.classList.add('card-hover');
        });
        
        card.addEventListener('mouseleave', function() {
            this.classList.remove('card-hover');
        });
        
        // Add tilt effect
        card.addEventListener('mousemove', function(e) {
            const cardRect = this.getBoundingClientRect();
            const x = e.clientX - cardRect.left;
            const y = e.clientY - cardRect.top;
            
            const centerX = cardRect.width / 2;
            const centerY = cardRect.height / 2;
            
            const deltaX = (x - centerX) / centerX;
            const deltaY = (y - centerY) / centerY;
            
            this.style.transform = `perspective(1000px) rotateX(${deltaY * -3}deg) rotateY(${deltaX * 3}deg) translateZ(10px)`;
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateZ(0)';
        });
    });
    
    // Animate numbers (count up effect)
    const valueElements = document.querySelectorAll('.card-value');
    
    valueElements.forEach(element => {
        const finalValue = element.getAttribute('data-value');
        const isNegative = finalValue.includes('-');
        const absValue = parseInt(finalValue.replace(/[^0-9]/g, ''));
        const startValue = isNegative ? -absValue : 0;
        const endValue = isNegative ? -absValue : absValue;
        
        animateValue(element, 0, endValue, 1500);
    });
    
    // Function to animate counting
    function animateValue(obj, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            // Use easeOutExpo for smoother animation
            const easing = 1 - Math.pow(1 - progress, 3);
            const currentValue = Math.floor(easing * (end - start) + start);
            
            // Format the number with thousand separators and handle negative sign
            const absValue = Math.abs(currentValue);
            const sign = currentValue < 0 ? '-' : '';
            obj.innerHTML = `Rp ${sign}${absValue.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`;
            
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }
    
    // Initialize indicator arrows
    updateArrows();
    
    // Add export button to download chart as image
    addExportButton();
});

// Function to update indicator arrows
function updateArrows() {
    const cards = document.querySelectorAll('.summary-card');
    
    cards.forEach(card => {
        const type = card.getAttribute('data-type');
        const value = parseInt(card.getAttribute('data-value').replace(/[^0-9]/g, ''));
        const previousValue = parseInt(card.getAttribute('data-previous').replace(/[^0-9]/g, '') || '0');
        const indicator = card.querySelector('.indicator');
        
        if (!indicator) return;
        
        // Calculate percentage change
        let percentChange = 0;
        if (previousValue > 0) {
            percentChange = Math.round(((value - previousValue) / previousValue) * 100);
        }
        
        // Determine the direction (positive or negative trend)
        let direction = 'neutral';
        if (percentChange > 0) {
            direction = (type === 'expense') ? 'negative' : 'positive';
        } else if (percentChange < 0) {
            direction = (type === 'expense') ? 'positive' : 'negative';
        }
        
        // Update indicator class and text
        indicator.className = 'indicator ' + direction;
        indicator.innerHTML = `<i class="fas fa-chevron-${percentChange > 0 ? 'up' : (percentChange < 0 ? 'down' : 'right')}"></i> ${Math.abs(percentChange)}%`;
    });
}

// Function to add export button
function addExportButton() {
    const chartCard = document.querySelector('.chart-card');
    if (!chartCard) return;
    
    const exportBtn = document.createElement('button');
    exportBtn.className = 'export-chart-btn';
    exportBtn.innerHTML = '<i class="fas fa-download"></i>';
    exportBtn.title = 'Unduh Grafik';
    
    chartCard.appendChild(exportBtn);
    
    exportBtn.addEventListener('click', function() {
        const canvas = document.getElementById('financeChart');
        if (!canvas) return;
        
        // Convert canvas to image
        const image = canvas.toDataURL('image/png');
        
        // Create download link
        const link = document.createElement('a');
        link.download = 'financeChart.png';
        link.href = image;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
}