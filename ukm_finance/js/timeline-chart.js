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
            
            // Use dummy data by default instead of fetching from API
            const mockData = generateMockData(range);
            renderChart(mockData);
        }
        
        // Add event listener to time range dropdown
        const timeRangeDropdown = document.getElementById('timeRangeDropdown');
        if (timeRangeDropdown) {
            timeRangeDropdown.addEventListener('change', function() {
                updateChartData(this.value);
            });
        }        // Function to fetch data from API (not used by default now, we use mock data)
        function fetchData(range) {
            // Show loading state
            timelineChartCanvas.style.opacity = 0.5;
            
            // Always use mock data for demonstration
            const mockData = generateMockData(range);
            renderChart(mockData);
            timelineChartCanvas.style.opacity = 1;
            
            /* Original API fetching code is commented out
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
                        // If API fails, use mock data for demonstration
                        const mockData = generateMockData(range);
                        renderChart(mockData);
                    }
                    timelineChartCanvas.style.opacity = 1;
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    timelineChartCanvas.style.opacity = 1;
                    
                    // If API fails, use mock data for demonstration
                    const mockData = generateMockData(range);
                    renderChart(mockData);
                });
            */
        }
          // Process API data for chart
        function processDataForChart(data, range) {
            // This function is no longer necessary as the API returns pre-formatted data
            // But we'll keep it here for backwards compatibility or future custom processing
            
            if (data.status === 'success') {
                return {
                    labels: data.labels,
                    pemasukan: data.pemasukan,
                    pengeluaran: data.pengeluaran
                };
            } else {
                // If data is not in the expected format, use mock data
                return generateMockData(range);
            }
        }
        
        // Generate mock data for demonstration
        function generateMockData(range) {
            const labels = [];
            const pemasukan = [];
            const pengeluaran = [];
            
            if (range === 'month') {
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                for (let i = 0; i < 12; i++) {
                    labels.push(months[i]);
                    pemasukan.push(Math.floor(Math.random() * 5000000) + 1000000);
                    pengeluaran.push(Math.floor(Math.random() * 4000000) + 800000);
                }
            } else if (range === 'week') {
                for (let i = 1; i <= 8; i++) {
                    labels.push('W' + i);
                    pemasukan.push(Math.floor(Math.random() * 1000000) + 200000);
                    pengeluaran.push(Math.floor(Math.random() * 800000) + 150000);
                }
            } else if (range === 'day') {
                const days = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                for (let i = 0; i < 7; i++) {
                    labels.push(days[i]);
                    pemasukan.push(Math.floor(Math.random() * 300000) + 50000);
                    pengeluaran.push(Math.floor(Math.random() * 250000) + 40000);
                }
            }
            
            return {
                labels: labels,
                pemasukan: pemasukan,
                pengeluaran: pengeluaran
            };
        }
        
        // Helper function to get the week number
        function getWeekNumber(date) {
            const d = new Date(date);
            d.setHours(0, 0, 0, 0);
            d.setDate(d.getDate() + 4 - (d.getDay() || 7));
            const yearStart = new Date(d.getFullYear(), 0, 1);
            return Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
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
