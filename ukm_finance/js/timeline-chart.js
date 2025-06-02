// Line Chart for Income and Expense Over Time
document.addEventListener('DOMContentLoaded', function() {
    console.log('Line Chart DOM loaded, checking timelineChart element');
    
    const timelineChartCanvas = document.getElementById('timelineChart');
    if (!timelineChartCanvas) {
        console.error('Timeline chart canvas element not found!');
        return;
    }
    
    try {
        // Get context
        const ctx = timelineChartCanvas.getContext('2d');
        
        // Set canvas dimensions
        timelineChartCanvas.height = timelineChartCanvas.parentElement.clientHeight - 50;
        timelineChartCanvas.width = timelineChartCanvas.parentElement.clientWidth - 40;
        
        // Default to 'month' view
        let timeRange = 'month';
        let lineChart;

        // Function to update chart data based on selected time range from dropdown
        function updateChartData(range) {
            timeRange = range;
            const timeRangeDropdown = document.getElementById('timeRangeDropdown');
            if (timeRangeDropdown) {
                timeRangeDropdown.value = range;
            }
            
            // Fetch real data from API
            fetchData(range);
        }
        
        // Add event listener to time range dropdown
        const timeRangeDropdown = document.getElementById('timeRangeDropdown');
        if (timeRangeDropdown) {
            timeRangeDropdown.addEventListener('change', function() {
                updateChartData(this.value);
            });
        }

        // Function to fetch data from API
        function fetchData(range) {
            // Show loading state
            timelineChartCanvas.style.opacity = 0.5;
            
            // Get UKM ID from the page
            const ukm_id = timelineChartCanvas.getAttribute('data-ukm-id');
            
            fetch(`api/getdata.php?ukm_id=${ukm_id}&time_range=${range}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        renderChart({
                            labels: data.labels,
                            pemasukan: data.pemasukan,
                            pengeluaran: data.pengeluaran
                        });
                    } else {
                        console.error('API returned error:', data.message);
                        // Show error message to user
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'alert alert-danger';
                        errorMessage.textContent = 'Error loading chart data. Please try again later.';
                        timelineChartCanvas.parentNode.insertBefore(errorMessage, timelineChartCanvas);
                    }
                    timelineChartCanvas.style.opacity = 1;
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    timelineChartCanvas.style.opacity = 1;
                    
                    // Show error message to user
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'alert alert-danger';
                    errorMessage.textContent = 'Error loading chart data. Please try again later.';
                    timelineChartCanvas.parentNode.insertBefore(errorMessage, timelineChartCanvas);
                });
        }
        
        // Render chart with the provided data
        function renderChart(data) {
            if (lineChart) {
                lineChart.destroy();
            }
            
            lineChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Pemasukan',
                            data: data.pemasukan,
                            borderColor: 'rgba(238, 173, 85, 1)',
                            backgroundColor: 'rgba(238, 173, 85, 0.2)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointBackgroundColor: 'rgba(238, 173, 85, 1)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 1,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        },
                        {
                            label: 'Pengeluaran',
                            data: data.pengeluaran,
                            borderColor: 'rgba(255, 82, 82, 1)',
                            backgroundColor: 'rgba(255, 82, 82, 0.2)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointBackgroundColor: 'rgba(255, 82, 82, 1)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 1,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    size: 12
                                },
                                usePointStyle: true,
                                padding: 20
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
                                    return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                                }
                            }
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: {
                                    size: 12
                                },
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
                            ticks: {
                                font: {
                                    size: 12
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
        }
        
        // Initialize with default range (month)
        updateChartData(timeRange);
        
    } catch (error) {
        console.error('Error initializing timeline chart:', error);
    }
});
