/**
 * Total Liquidations Chart Controller
 * Menggunakan Chart.js untuk menampilkan aggregated liquidation data
 */

function totalLiquidationsChart() {
    return {
        // State
        _initialized: false,
        _refreshInterval: null,
        loading: false,
        chartData: [],
        chart: null,
        
        // Filters
        selectedSymbol: 'BTC',
        selectedInterval: '1h',
        chartType: 'bar',
        scaleType: 'linear', // 'linear' or 'logarithmic'
        
        // Available options
        availableSymbols: ['ALL', 'BTC', 'ETH', 'SOL', 'XRP', 'ADA'],
        availableIntervals: [
            { value: '1h', label: '1H' },
            { value: '4h', label: '4H' },
            { value: '12h', label: '12H' },
            { value: '24h', label: '24H' }
        ],
        
        // API Configuration
        baseUrl: '/api/coinglass',
        
        init() {
            // Prevent double initialization
            if (this._initialized) {
                console.warn('⚠️ Total Liquidations Chart already initialized, skipping...');
                return;
            }
            this._initialized = true;
            
            console.log('🚀 Initializing Total Liquidations Chart');
            
            // Wait for Chart.js to be ready before initializing
            if (typeof Chart === 'undefined') {
                console.log('Waiting for Chart.js to load...');
                this._initialized = false; // Reset flag
                setTimeout(() => this.init(), 100);
                return;
            }
            
            // Wait a bit more to ensure DOM is ready and other charts are initialized
            setTimeout(() => {
                this.loadData();
            }, 500);
            
            // Auto refresh every 2 minutes (only set once)
            if (!this._refreshInterval) {
                this._refreshInterval = setInterval(() => {
                    this.loadData();
                }, 120000);
            }
        },
        
        async loadData() {
            this.loading = true;
            
            try {
                const params = new URLSearchParams({
                    exchange_list: 'Binance,OKX,Bybit',
                    symbol: this.selectedSymbol === 'ALL' ? 'BTC' : this.selectedSymbol,
                    interval: this.selectedInterval,
                    limit: 50
                });
                
                const response = await fetch(`${this.baseUrl}/liquidation-aggregated-history?${params}`, {
                    headers: {
                        'accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success && data.data) {
                    this.processChartData(data.data);
                    this.updateChart();
                } else {
                    console.error('API Error:', data);
                    this.useFallbackData();
                }
                
            } catch (error) {
                console.error('🚨 Failed to load chart data:', error);
                this.useFallbackData();
            } finally {
                this.loading = false;
            }
        },
        
        processChartData(data) {
            // Sort by time and process for Chart.js
            this.chartData = data
                .sort((a, b) => a.time - b.time)
                .map(item => {
                    // Handle timestamp properly - could be in seconds or milliseconds
                    let timestamp = parseInt(item.time);
                    // If timestamp is in seconds (less than year 2100), convert to milliseconds
                    if (timestamp < 4102444800) {
                        timestamp = timestamp * 1000;
                    }
                    
                    return {
                        time: new Date(timestamp),
                        longLiquidation: parseFloat(item.aggregated_long_liquidation_usd || 0),
                        shortLiquidation: parseFloat(item.aggregated_short_liquidation_usd || 0),
                        totalLiquidation: parseFloat(item.aggregated_long_liquidation_usd || 0) + parseFloat(item.aggregated_short_liquidation_usd || 0)
                    };
                });
            
            console.log(`✅ Processed ${this.chartData.length} total liquidation data points`);
        },
        
        useFallbackData() {
            // Generate demo data for testing
            const now = Date.now();
            const oneHour = 60 * 60 * 1000;
            
            this.chartData = Array.from({ length: 24 }, (_, i) => ({
                time: new Date(now - (23 - i) * oneHour),
                longLiquidation: Math.random() * 5000000 + 1000000,
                shortLiquidation: Math.random() * 5000000 + 1000000,
                totalLiquidation: 0
            })).map(item => ({
                ...item,
                totalLiquidation: item.longLiquidation + item.shortLiquidation
            }));
            
            this.updateChart();
        },
        
        updateChart() {
            const canvas = document.getElementById('totalLiquidationsChart');
            if (!canvas) {
                console.warn('Chart canvas not found: totalLiquidationsChart');
                return;
            }
            
            // Wait for Chart.js to be ready
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js not loaded yet, retrying...');
                setTimeout(() => this.updateChart(), 100);
                return;
            }
            
            // Destroy existing chart properly
            if (this.chart) {
                console.log('Destroying existing chart...');
                this.chart.destroy();
                this.chart = null;
            }
            
            // Check if canvas is already in use by another chart
            const existingChart = Chart.getChart(canvas);
            if (existingChart) {
                console.log('Found existing chart on canvas, destroying...');
                existingChart.destroy();
            }
            
            // Clear canvas context
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            // Reset canvas size
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;
            
            const chartConfig = {
                type: this.chartType === 'line' ? 'line' : 'bar',
                data: {
                    labels: this.chartData.map(item => {
                        // Safe date formatting with fallback
                        try {
                            if (item.time && item.time instanceof Date && !isNaN(item.time)) {
                                return item.time.toLocaleTimeString('en-US', { 
                                    month: 'short',
                                    day: '2-digit',
                                    hour: '2-digit', 
                                    minute: '2-digit' 
                                });
                            } else {
                                return 'Invalid Date';
                            }
                        } catch (error) {
                            console.warn('Date formatting error:', error);
                            return 'Invalid Date';
                        }
                    }),
                    datasets: [
                        {
                            label: 'Long Liquidations',
                            data: this.chartData.map(item => item.longLiquidation),
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Short Liquidations',
                            data: this.chartData.map(item => item.shortLiquidation),
                            backgroundColor: 'rgba(34, 197, 94, 0.7)',
                            borderColor: 'rgba(34, 197, 94, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: `Total Liquidations - ${this.selectedSymbol} (${this.selectedInterval})`,
                            color: '#e2e8f0',
                            font: { size: 16, weight: 'bold' }
                        },
                        legend: {
                            labels: { color: '#e2e8f0' }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleColor: '#e2e8f0',
                            bodyColor: '#e2e8f0',
                            borderColor: 'rgba(59, 130, 246, 0.5)',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed.y;
                                    const formatted = value >= 1000000 ? 
                                        '$' + (value / 1000000).toFixed(2) + 'M' :
                                        '$' + (value / 1000).toFixed(1) + 'K';
                                    return context.dataset.label + ': ' + formatted;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            type: 'category',
                            grid: { color: 'rgba(59, 130, 246, 0.1)' },
                            ticks: { 
                                color: '#94a3b8',
                                callback: function(value, index, values) {
                                    const date = new Date(this.getLabelForValue(value));
                                    return date.toLocaleTimeString('en-US', { 
                                        hour: '2-digit', 
                                        minute: '2-digit' 
                                    });
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(59, 130, 246, 0.1)' },
                            ticks: { 
                                color: '#94a3b8',
                                callback: function(value) {
                                    return value >= 1000000 ? 
                                        '$' + (value / 1000000).toFixed(1) + 'M' :
                                        '$' + (value / 1000).toFixed(0) + 'K';
                                }
                            }
                        }
                    }
                }
            };
            
            try {
                this.chart = new Chart(canvas, chartConfig);
                console.log('✅ Total Liquidations Chart created successfully');
            } catch (error) {
                console.error('🚨 Failed to create Total Liquidations Chart:', error);
                
                // Show error message in chart area
                ctx.fillStyle = '#ef4444';
                ctx.font = '16px Arial';
                ctx.fillText('Chart Error: ' + error.message, 20, 50);
                ctx.fillStyle = '#94a3b8';
                ctx.font = '14px Arial';
                ctx.fillText('Please refresh the page or check console for details', 20, 80);
            }
        },
        
        // Filter Methods
        setSymbol(symbol) {
            this.selectedSymbol = symbol;
            this.loadData();
        },
        
        setInterval(interval) {
            this.selectedInterval = interval;
            this.loadData();
        },
        
        toggleChartType(type) {
            this.chartType = type;
            this.updateChart();
        },
        
        refreshChart() {
            this.loadData();
        },
        
        // Helper Methods
        formatValue(value) {
            if (value >= 1000000) {
                return '$' + (value / 1000000).toFixed(2) + 'M';
            } else if (value >= 1000) {
                return '$' + (value / 1000).toFixed(1) + 'K';
            } else {
                return '$' + value.toFixed(0);
            }
        },
        
        getTotalLiquidations() {
            return this.chartData.reduce((sum, item) => sum + item.totalLiquidation, 0);
        },
        
        getLongShortRatio() {
            const totalLong = this.chartData.reduce((sum, item) => sum + item.longLiquidation, 0);
            const totalShort = this.chartData.reduce((sum, item) => sum + item.shortLiquidation, 0);
            return totalShort > 0 ? (totalLong / totalShort).toFixed(2) : 0;
        },

        // New functions to match Liquidation History Chart
        toggleScale(type) {
            this.scaleType = type;
            this.updateChart();
        },

        resetZoom() {
            if (this.chart && this.chart.resetZoom) {
                this.chart.resetZoom();
            }
        },

        exportChart(format) {
            if (!this.chart) return;
            
            const canvas = this.chart.canvas;
            if (format === 'png') {
                const url = canvas.toDataURL('image/png');
                const link = document.createElement('a');
                link.download = `total-liquidations-${this.selectedSymbol}-${new Date().toISOString().split('T')[0]}.png`;
                link.href = url;
                link.click();
            } else if (format === 'svg') {
                // For SVG export, we'd need additional library like canvas2svg
                console.log('SVG export not implemented yet');
            }
        },

        refreshChart() {
            this.loadData();
        },

        // Additional functions needed by template
        formatValue(value) {
            return this.formatLiquidation(value);
        },

        formatLiquidation(value) {
            if (!value || isNaN(value)) return '--';
            
            const num = parseFloat(value);
            if (num >= 1000000000) {
                return '$' + (num / 1000000000).toFixed(2) + 'B';
            } else if (num >= 1000000) {
                return '$' + (num / 1000000).toFixed(2) + 'M';
            } else if (num >= 1000) {
                return '$' + (num / 1000).toFixed(1) + 'K';
            } else {
                return '$' + num.toFixed(0);
            }
        }
    };
}