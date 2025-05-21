// Finance Chart Initialization
document.addEventListener('DOMContentLoaded', function() {
    // Debug information
    console.log('DOM loaded, checking Chart.js');
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        return;
    } else {
        console.log('Chart.js is loaded correctly.');
    }
    
    const chartCanvas = document.getElementById('financeChart');
    if (!chartCanvas) {
        console.error('Canvas element not found!');
        return;
    } else {
        console.log('Canvas element found:', chartCanvas);
    }
    
    try {
        // Get context and make sure it's available
        const ctx = chartCanvas.getContext('2d');
        if (!ctx) {
            console.error('Failed to get canvas context');
            return;
        }
        
        // Get data from PHP
        const pemasukan = parseFloat(chartCanvas.getAttribute('data-pemasukan') || 0);
        const pengeluaran = parseFloat(chartCanvas.getAttribute('data-pengeluaran') || 0);
        const saldo = parseFloat(chartCanvas.getAttribute('data-saldo') || 0);
        
        console.log('Chart Data:', { pemasukan, pengeluaran, saldo });
        
        // Set canvas dimensions explicitly
        chartCanvas.height = chartCanvas.parentElement.clientHeight - 50;
        chartCanvas.width = chartCanvas.parentElement.clientWidth - 40;
        
        // Create the chart
        const financeChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Pemasukan', 'Pengeluaran', 'Saldo'],
                datasets: [{
                    label: 'Jumlah (Rp)',
                    data: [pemasukan, pengeluaran, saldo],
                    backgroundColor: [
                        'rgba(238, 173, 85, 0.8)',
                        'rgba(255, 82, 82, 0.8)',
                        'rgba(45, 102, 74, 0.8)'
                    ],
                    borderColor: [
                        'rgba(238, 173, 85, 1)',
                        'rgba(255, 82, 82, 1)',
                        'rgba(45, 102, 74, 1)'
                    ],
                    borderWidth: 1,
                    borderRadius: 8,
                    maxBarThickness: 100
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 14
                        },
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            },
                            callback: function(value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 14
                            }
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
        console.log('Chart initialized successfully');
    } catch (error) {
        console.error('Error initializing chart:', error);
    }
});
