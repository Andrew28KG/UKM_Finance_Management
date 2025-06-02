// Pie Chart for All UKM Saldo
document.addEventListener('DOMContentLoaded', function() {
    console.log('UKM Pie Chart DOM loaded, checking ukmPieChart element');
    
    const ukmPieChartCanvas = document.getElementById('ukmPieChart');
    if (!ukmPieChartCanvas) {
        console.error('UKM pie chart canvas element not found!');
        return;
    }
    
    try {
        // Get context
        const ctx = ukmPieChartCanvas.getContext('2d');
        
        // Set canvas dimensions
        ukmPieChartCanvas.height = 280;
        
        // Fetch data from API
        fetchUkmData()
            .then(ukms => {
                // Create the chart with the fetched data
                createPieChart(ctx, ukms);
            })
            .catch(error => {
                console.error('Error fetching UKM saldo data:', error);
                // Show error message to user
                const errorMessage = document.createElement('div');
                errorMessage.className = 'alert alert-danger';
                errorMessage.textContent = 'Error loading chart data. Please try again later.';
                ukmPieChartCanvas.parentNode.insertBefore(errorMessage, ukmPieChartCanvas);
            });
        
    } catch (error) {
        console.error('Error initializing UKM pie chart:', error);
    }
});

// Fetch real data from API
function fetchUkmData() {
    return fetch('api/get_ukm_saldo.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                return data.data;
            } else {
                throw new Error(data.message || 'Failed to fetch UKM data');
            }
        });
}

// Create the pie chart
function createPieChart(ctx, ukms) {
    // Filter out UKMs with zero or negative saldo
    const filteredUkms = ukms.filter(ukm => ukm.saldo > 0);
    
    // Sort by saldo (highest first) if not already sorted
    if (!filteredUkms.every((ukm, i, arr) => i === 0 || arr[i-1].saldo >= ukm.saldo)) {
        filteredUkms.sort((a, b) => b.saldo - a.saldo);
    }
    
    // Use colors from the UKM data if available, otherwise generate them
    const bgColors = [];
    const borderColors = [];
    
    filteredUkms.forEach(ukm => {
        if (ukm.color) {
            bgColors.push(ukm.color);
            // Create border color by replacing opacity
            const borderColor = ukm.color.replace(/, [0-9.]+\)$/, ', 1)');
            borderColors.push(borderColor);
        }
    });
    
    // If some UKMs don't have colors, generate the missing ones
    if (bgColors.length !== filteredUkms.length) {
        const generatedColors = generateColors(filteredUkms.length);
        for (let i = 0; i < filteredUkms.length; i++) {
            if (!bgColors[i]) {
                bgColors[i] = generatedColors.bg[i];
                borderColors[i] = generatedColors.border[i];
            }
        }
    }
    
    // Create the chart
    const ukmPieChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: filteredUkms.map(ukm => ukm.nama_ukm),
            datasets: [{
                data: filteredUkms.map(ukm => ukm.saldo),
                backgroundColor: bgColors,
                borderColor: borderColors,
                borderWidth: 1,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '50%',
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        font: {
                            size: 12
                        },
                        usePointStyle: true,
                        padding: 15,
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                const dataset = data.datasets[0];
                                const total = dataset.data.reduce((acc, value) => acc + value, 0);
                                
                                return data.labels.map((label, i) => {
                                    const value = dataset.data[i];
                                    const percentage = Math.round((value / total) * 100);
                                    
                                    return {
                                        text: `${label} (${percentage}%)`,
                                        fillStyle: dataset.backgroundColor[i],
                                        strokeStyle: dataset.borderColor[i],
                                        lineWidth: dataset.borderWidth,
                                        hidden: isNaN(dataset.data[i]) || chart.getDatasetMeta(0).data[i].hidden,
                                        index: i
                                    };
                                });
                            }
                            return [];
                        }
                    }
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
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: Rp ${new Intl.NumberFormat('id-ID').format(value)} (${percentage}%)`;
                        }
                    }
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true
            }
        }
    });
    
    // Add center text with total
    addCenterText(ctx, ukmPieChart, filteredUkms);
}

// Add center text showing total saldo
function addCenterText(ctx, chart, ukms) {
    const totalSaldo = ukms.reduce((total, ukm) => total + ukm.saldo, 0);
    
    function afterDraw() {
        const width = chart.chartArea.width;
        const height = chart.chartArea.height;
        const x = chart.chartArea.left + width / 2;
        const y = chart.chartArea.top + height / 2;
        
        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        
        // Title
        ctx.font = '14px Poppins';
        ctx.fillStyle = '#666';
        ctx.fillText('Total Saldo', x, y - 15);
        
        // Value
        ctx.font = 'bold 16px Poppins';
        ctx.fillStyle = '#333';
        const formattedTotal = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(totalSaldo);
        
        ctx.fillText(formattedTotal, x, y + 15);
        ctx.restore();
    }
    
    // Attach afterDraw to the chart's render event
    chart.options.plugins.afterDraw = afterDraw;
}

// Generate complementary colors for the pie chart
function generateColors(count) {
    const bgColors = [];
    const borderColors = [];
    
    // Start with some nice colors
    const baseColors = [
        [45, 102, 74],    // Primary green
        [238, 173, 85],   // Gold
        [76, 140, 200],   // Blue
        [255, 82, 82],    // Red
        [138, 90, 180],   // Purple
        [85, 195, 195],   // Teal
        [255, 155, 85],   // Orange
        [98, 181, 133],   // Light green
        [255, 118, 170],  // Pink
        [180, 180, 180]   // Gray
    ];
    
    // If we need more colors than in our base set, generate them
    for (let i = 0; i < count; i++) {
        if (i < baseColors.length) {
            const [r, g, b] = baseColors[i];
            bgColors.push(`rgba(${r}, ${g}, ${b}, 0.7)`);
            borderColors.push(`rgba(${r}, ${g}, ${b}, 1)`);
        } else {
            // Generate random colors if we run out of base colors
            const r = Math.floor(Math.random() * 200) + 50;
            const g = Math.floor(Math.random() * 200) + 50;
            const b = Math.floor(Math.random() * 200) + 50;
            bgColors.push(`rgba(${r}, ${g}, ${b}, 0.7)`);
            borderColors.push(`rgba(${r}, ${g}, ${b}, 1)`);
        }
    }
    
    return {
        bg: bgColors,
        border: borderColors
    };
}
