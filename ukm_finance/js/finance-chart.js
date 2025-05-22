// Finance Chart Initialization with Enhanced Features
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
    
    // Initialize chart type (default to bar)
    let currentChartType = 'bar';
    let chartInstance = null;
    
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
        chartCanvas.height = chartCanvas.parentElement.clientHeight - 60;
        chartCanvas.width = chartCanvas.parentElement.clientWidth - 40;
        
        // Custom gradient backgrounds
        const ctx1 = document.getElementById('financeChart').getContext('2d');
        
        // Enhanced gradients for each bar with better color stops
        const incomeGradient = ctx1.createLinearGradient(0, 0, 0, 400);
        incomeGradient.addColorStop(0, 'rgba(238, 173, 85, 1)');
        incomeGradient.addColorStop(0.4, 'rgba(238, 173, 85, 0.8)');
        incomeGradient.addColorStop(1, 'rgba(238, 173, 85, 0.4)');
        
        const expenseGradient = ctx1.createLinearGradient(0, 0, 0, 400);
        expenseGradient.addColorStop(0, 'rgba(255, 82, 82, 1)');
        expenseGradient.addColorStop(0.4, 'rgba(255, 82, 82, 0.8)');
        expenseGradient.addColorStop(1, 'rgba(255, 82, 82, 0.4)');
        
        const balanceGradient = ctx1.createLinearGradient(0, 0, 0, 400);
        balanceGradient.addColorStop(0, 'rgba(45, 102, 74, 1)');
        balanceGradient.addColorStop(0.4, 'rgba(45, 102, 74, 0.8)');
        balanceGradient.addColorStop(1, 'rgba(45, 102, 74, 0.4)');
        
        // For doughnut/pie chart
        const colorsPie = [
            'rgba(238, 173, 85, 0.9)',
            'rgba(255, 82, 82, 0.9)',
            'rgba(45, 102, 74, 0.9)'
        ];
        
        const hoverColorsPie = [
            'rgba(238, 173, 85, 1)',
            'rgba(255, 82, 82, 1)',
            'rgba(45, 102, 74, 1)'
        ];
        
        // Define datasets for different chart types
        const datasets = {
            bar: [{
                label: 'Jumlah (Rp)',
                data: [pemasukan, pengeluaran, saldo],
                backgroundColor: [
                    incomeGradient,
                    expenseGradient,
                    balanceGradient
                ],
                borderColor: [
                    'rgba(238, 173, 85, 1)',
                    'rgba(255, 82, 82, 1)',
                    'rgba(45, 102, 74, 1)'
                ],
                borderWidth: 2,
                borderRadius: 12,
                borderSkipped: false,
                maxBarThickness: 80,
                hoverBackgroundColor: [
                    'rgba(238, 173, 85, 1)',
                    'rgba(255, 82, 82, 1)',
                    'rgba(45, 102, 74, 1)'
                ],
                hoverBorderWidth: 0
            }],
            doughnut: [{
                data: [pemasukan, pengeluaran, saldo],
                backgroundColor: colorsPie,
                borderColor: [
                    'rgba(238, 173, 85, 1)',
                    'rgba(255, 82, 82, 1)',
                    'rgba(45, 102, 74, 1)'
                ],
                borderWidth: 2,
                hoverBackgroundColor: hoverColorsPie,
                hoverBorderWidth: 0,
                hoverOffset: 10,
                weight: 1
            }],
            polarArea: [{
                data: [pemasukan, pengeluaran, saldo],
                backgroundColor: colorsPie,
                borderColor: [
                    'rgba(238, 173, 85, 1)',
                    'rgba(255, 82, 82, 1)',
                    'rgba(45, 102, 74, 1)'
                ],
                borderWidth: 1,
                hoverBackgroundColor: hoverColorsPie,
                hoverBorderWidth: 2
            }]
        };
        
        // Common options for all chart types
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 1500,
                easing: 'easeOutQuart',
                delay: function(context) {
                    return context.dataIndex * 150;
                }
            }
        };
        
        // Specific options for each chart type
        const chartOptions = {
            bar: {
                ...commonOptions,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: {
                            size: 14,
                            weight: 'bold',
                            family: "'Poppins', sans-serif"
                        },
                        bodyFont: {
                            size: 14,
                            family: "'Poppins', sans-serif"
                        },
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                            }
                        },
                        displayColors: false,
                        caretSize: 6,
                        caretPadding: 10,
                        usePointStyle: true
                    },
                    datalabels: {
                        display: true,
                        color: '#fff',
                        font: {
                            weight: 'bold',
                            size: 12,
                            family: "'Poppins', sans-serif"
                        },
                        formatter: function(value) {
                            if (value > 1000000) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + ' Jt';
                            } else {
                                return 'Rp ' + (value / 1000).toFixed(0) + ' Rb';
                            }
                        },
                        anchor: 'end',
                        align: 'top',
                        offset: -5
                    }
                },
                layout: {
                    padding: {
                        top: 20,
                        right: 25,
                        bottom: 0,
                        left: 25
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)',
                            lineWidth: 1,
                            drawBorder: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                family: "'Poppins', sans-serif"
                            },
                            padding: 10,
                            color: '#6c757d',
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return 'Rp ' + (value / 1000000).toFixed(1) + ' Jt';
                                } else if (value >= 1000) {
                                    return 'Rp ' + (value / 1000).toFixed(0) + ' Rb';
                                }
                                return 'Rp ' + value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 14,
                                weight: 'bold',
                                family: "'Poppins', sans-serif"
                            },
                            color: '#6c757d',
                            padding: 10
                        }
                    }
                },
                elements: {
                    bar: {
                        borderWidth: 2
                    }
                }
            },
            doughnut: {
                ...commonOptions,
                cutout: '60%',
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 12,
                                family: "'Poppins', sans-serif"
                            },
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 15,
                        cornerRadius: 8,
                        titleFont: {
                            size: 14,
                            weight: 'bold',
                            family: "'Poppins', sans-serif"
                        },
                        bodyFont: {
                            size: 14,
                            family: "'Poppins', sans-serif"
                        },
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw;
                                const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: Rp ${new Intl.NumberFormat('id-ID').format(value)} (${percentage}%)`;
                            }
                        },
                        displayColors: true,
                        caretSize: 6,
                        caretPadding: 10,
                        usePointStyle: true
                    },
                    datalabels: {
                        display: function(context) {
                            const index = context.dataIndex;
                            const value = context.dataset.data[index];
                            const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                            return value / total > 0.05; // Only show labels for slices larger than 5%
                        },
                        color: '#fff',
                        font: {
                            weight: 'bold',
                            size: 12,
                            family: "'Poppins', sans-serif"
                        },
                        formatter: function(value, context) {
                            const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return percentage + '%';
                        },
                        anchor: 'center',
                        align: 'center',
                        offset: 0
                    }
                }
            },
            polarArea: {
                ...commonOptions,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 12,
                                family: "'Poppins', sans-serif"
                            },
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'rectRounded'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 15,
                        cornerRadius: 8,
                        titleFont: {
                            size: 14,
                            weight: 'bold',
                            family: "'Poppins', sans-serif"
                        },
                        bodyFont: {
                            size: 14,
                            family: "'Poppins', sans-serif"
                        },
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw;
                                return `${label}: Rp ${new Intl.NumberFormat('id-ID').format(value)}`;
                            }
                        },
                        displayColors: true,
                        usePointStyle: true
                    },
                    datalabels: {
                        display: true,
                        color: '#fff',
                        font: {
                            weight: 'bold',
                            size: 12,
                            family: "'Poppins', sans-serif"
                        },
                        formatter: function(value) {
                            if (value > 1000000) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + ' Jt';
                            } else {
                                return 'Rp ' + (value / 1000).toFixed(0) + ' Rb';
                            }
                        },
                        anchor: 'center',
                        align: 'center',
                        offset: 0
                    }
                },
                scales: {
                    r: {
                        display: true,
                        beginAtZero: true,
                        ticks: {
                            display: false
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        angleLines: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                }
            }
        };
        
        // Create chart function
        function createChart(type) {
            if (chartInstance) {
                chartInstance.destroy();
            }
            
            currentChartType = type;
            
            chartInstance = new Chart(ctx, {
                type: type,
                data: {
                    labels: ['Pemasukan', 'Pengeluaran', 'Saldo'],
                    datasets: datasets[type]
                },
                options: chartOptions[type],
                plugins: [ChartDataLabels]
            });
            
            return chartInstance;
        }
        
        // Initial chart creation
        chartInstance = createChart('bar');
        console.log('Chart initialized successfully');
        
        // Add title element above the chart
        const chartContainer = chartCanvas.parentElement;
        
        // Create chart title
        const chartTitle = document.createElement('div');
        chartTitle.className = 'chart-title';
        chartTitle.innerHTML = '<span>Ringkasan Keuangan</span><div class="chart-subtitle">Perbandingan Pemasukan, Pengeluaran, dan Saldo</div>';
        chartContainer.insertBefore(chartTitle, chartCanvas);
        
        // Create chart controls
        const chartControls = document.createElement('div');
        chartControls.className = 'chart-controls';
        
        // Download button
        const downloadBtn = document.createElement('button');
        downloadBtn.className = 'chart-control-btn download';
        downloadBtn.innerHTML = '<i class="fas fa-download"></i>';
        downloadBtn.title = 'Unduh Grafik';
        downloadBtn.addEventListener('click', function() {
            downloadChart();
        });
        
        // Add chart controls
        chartControls.appendChild(downloadBtn);
        chartContainer.appendChild(chartControls);
        
        // Create chart type switch
        const chartTypeSwitch = document.createElement('div');
        chartTypeSwitch.className = 'chart-type-switch';
        
        // Chart type options
        const chartTypes = [
            { type: 'bar', label: 'Bar', icon: 'fa-chart-bar' },
            { type: 'doughnut', label: 'Pie', icon: 'fa-chart-pie' },
            { type: 'polarArea', label: 'Polar', icon: 'fa-circle-notch' }
        ];
        
        // Create buttons for each chart type
        chartTypes.forEach(chartType => {
            const btn = document.createElement('button');
            btn.className = `chart-type-btn ${chartType.type === currentChartType ? 'active' : ''}`;
            btn.innerHTML = `<i class="fas ${chartType.icon}"></i> ${chartType.label}`;
            btn.dataset.type = chartType.type;
            btn.title = `Tampilkan sebagai grafik ${chartType.label}`;
            
            btn.addEventListener('click', function() {
                // Update active state
                document.querySelectorAll('.chart-type-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Create new chart
                createChart(this.dataset.type);
            });
            
            chartTypeSwitch.appendChild(btn);
        });
        
        // Add chart type switch to container
        chartContainer.appendChild(chartTypeSwitch);
        
        // Add interaction hint
        const interactionHint = document.createElement('div');
        interactionHint.className = 'chart-interaction-hint';
        interactionHint.innerHTML = '<i class="fas fa-mouse-pointer"></i> Arahkan kursor untuk detail';
        chartContainer.appendChild(interactionHint);
        
        // Download chart function
        function downloadChart() {
            const link = document.createElement('a');
            link.download = 'ukm-finance-chart.png';
            
            // Add white background
            const originalState = chartInstance.canvas.style.backgroundColor;
            chartInstance.canvas.style.backgroundColor = 'white';
            
            // Redraw with background
            chartInstance.update();
            
            // Create download link
            link.href = chartInstance.toBase64Image();
            
            // Reset background
            chartInstance.canvas.style.backgroundColor = originalState;
            chartInstance.update();
            
            // Trigger download
            link.click();
        }
        
        // Add window resize handler for responsiveness
        window.addEventListener('resize', function() {
            // Adjust canvas size
            if (chartCanvas.parentElement) {
                chartCanvas.height = chartCanvas.parentElement.clientHeight - 60;
                chartCanvas.width = chartCanvas.parentElement.clientWidth - 40;
                chartInstance.resize();
            }
        });
        
    } catch (error) {
        console.error('Error initializing chart:', error);
    }
});
