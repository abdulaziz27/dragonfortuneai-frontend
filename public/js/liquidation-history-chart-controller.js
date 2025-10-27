/**
 * Liquidation History Chart Controller
 * Mengganti Open Interest Chart dengan Liquidation History Chart
 * Menggunakan styling yang sudah ada, hanya mengubah data dan logic
 */

function liquidationHistoryChart() {
    return {
        // State
        _initialized: false,
        _refreshInterval: null,
        loading: false,
        chartData: [],
        chart: null,

        // Current values for display
        currentLongLiq: 0,
        currentShortLiq: 0,
        liqChange: 0,

        // Filters (menggunakan variable yang sudah ada di template)
        selectedExchange: 'Binance',
        selectedSymbol: 'BTCUSDT',
        selectedInterval: '1h',
        globalPeriod: '7d',
        chartType: 'bar',
        scaleType: 'linear',

        // Available options (only exchanges that work with liquidation history)
        availableExchanges: ['Binance', 'Bybit'],
        availableSymbols: ['BTCUSDT', 'ETHUSDT', 'SOLUSDT', 'XRPUSDT', 'ADAUSDT'],
        timeRanges: [
            { label: '1D', value: '1d', days: 1 },
            { label: '7D', value: '7d', days: 7 },
            { label: '1M', value: '1m', days: 30 },
            { label: '3M', value: '3m', days: 90 },
            { label: '6M', value: '6m', days: 180 },
            { label: '1Y', value: '1y', days: 365 }
        ],
        chartIntervals: [
            { label: '1H', value: '1h' },
            { label: '4H', value: '4h' },
            { label: '1D', value: '1d' },
            { label: '1W', value: '1w' }
        ],

        // API Configuration
        baseUrl: '/api/coinglass',

        init() {
            // Prevent double initialization
            if (this._initialized) {
                console.warn('⚠️ Liquidation History Chart already initialized, skipping...');
                return;
            }
            this._initialized = true;

            console.log('🚀 Initializing Liquidation History Chart (replacing Open Interest)');

            // Wait for Chart.js to be ready
            if (typeof Chart === 'undefined') {
                console.log('Waiting for Chart.js to load...');
                this._initialized = false; // Reset flag
                setTimeout(() => this.init(), 100);
                return;
            }

            // Delay to ensure DOM is ready and other charts are loaded
            setTimeout(() => {
                this.loadData();
            }, 1200);

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
                // Use symbol as-is for supported exchanges
                let symbol = this.selectedSymbol;

                const params = new URLSearchParams({
                    exchange: this.selectedExchange,
                    symbol: symbol,
                    interval: this.selectedInterval,
                    limit: 100
                });

                const response = await fetch(`${this.baseUrl}/liquidation-history?${params}`, {
                    headers: {
                        'accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success && data.data && data.data.length > 0) {
                    this.processChartData(data.data);
                    this.updateChart();
                } else {
                    console.warn('API returned empty data or error:', data);
                    this.useFallbackData();
                }

            } catch (error) {
                console.warn('⚠️ Failed to load liquidation history, using fallback data:', error);
                this.useFallbackData();
            } finally {
                this.loading = false;
            }
        },

        processChartData(data) {
            if (!data || data.length === 0) {
                console.warn('No data to process');
                return;
            }

            // Sort by time and process for Chart.js
            this.chartData = data
                .sort((a, b) => a.time - b.time)
                .map(item => ({
                    time: new Date(item.time),
                    longLiquidation: parseFloat(item.long_liquidation_usd || 0),
                    shortLiquidation: parseFloat(item.short_liquidation_usd || 0),
                    totalLiquidation: parseFloat(item.long_liquidation_usd || 0) + parseFloat(item.short_liquidation_usd || 0)
                }));

            console.log(`📊 Processed ${this.chartData.length} liquidation data points`);

            // Update current values
            if (this.chartData.length > 0) {
                const latest = this.chartData[this.chartData.length - 1];
                const previous = this.chartData.length > 1 ? this.chartData[this.chartData.length - 2] : null;

                this.currentLongLiq = latest.longLiquidation;
                this.currentShortLiq = latest.shortLiquidation;

                if (previous && previous.totalLiquidation > 0) {
                    const prevTotal = previous.totalLiquidation;
                    const currentTotal = latest.totalLiquidation;
                    this.liqChange = ((currentTotal - prevTotal) / prevTotal) * 100;
                } else {
                    this.liqChange = 0;
                }

                console.log(`💰 Current: Long=$${this.currentLongLiq.toFixed(0)}, Short=$${this.currentShortLiq.toFixed(0)}, Change=${this.liqChange.toFixed(2)}%`);
            }
        },

        useFallbackData() {
            // Generate demo liquidation data
            const now = Date.now();
            const interval = this.selectedInterval === '1h' ? 60 * 60 * 1000 : 4 * 60 * 60 * 1000;

            this.chartData = Array.from({ length: 50 }, (_, i) => {
                const longLiq = Math.random() * 2000000 + 500000;
                const shortLiq = Math.random() * 2000000 + 500000;

                return {
                    time: new Date(now - (49 - i) * interval),
                    longLiquidation: longLiq,
                    shortLiquidation: shortLiq,
                    totalLiquidation: longLiq + shortLiq
                };
            });

            // Update current values
            const latest = this.chartData[this.chartData.length - 1];
            this.currentLongLiq = latest.longLiquidation;
            this.currentShortLiq = latest.shortLiquidation;
            this.liqChange = (Math.random() - 0.5) * 10; // Random change

            this.updateChart();
        },

        updateChart() {
            const canvas = document.getElementById('liquidationsMainChart');
            if (!canvas) {
                console.warn('Chart canvas not found: liquidationsMainChart');
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
                    labels: this.chartData.map(item =>
                        item.time.toLocaleTimeString('en-US', {
                            month: 'short',
                            day: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit'
                        })
                    ),
                    datasets: [
                        {
                            label: 'Long Liquidations',
                            data: this.chartData.map(item => item.longLiquidation),
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 2,
                            fill: false
                        },
                        {
                            label: 'Short Liquidations',
                            data: this.chartData.map(item => item.shortLiquidation),
                            backgroundColor: 'rgba(34, 197, 94, 0.7)',
                            borderColor: 'rgba(34, 197, 94, 1)',
                            borderWidth: 2,
                            fill: false
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
                            display: false
                        },
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                color: '#e2e8f0',
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            titleColor: '#e2e8f0',
                            bodyColor: '#e2e8f0',
                            borderColor: 'rgba(59, 130, 246, 0.5)',
                            borderWidth: 1,
                            callbacks: {
                                title: function (context) {
                                    return 'Liquidations - ' + context[0].label;
                                },
                                label: function (context) {
                                    const value = context.parsed.y;
                                    if (!value || isNaN(value)) return context.dataset.label + ': $0';

                                    const formatted = value >= 1000000 ?
                                        '$' + (value / 1000000).toFixed(2) + 'M' :
                                        value >= 1000 ?
                                            '$' + (value / 1000).toFixed(1) + 'K' :
                                            '$' + value.toFixed(0);
                                    return context.dataset.label + ': ' + formatted;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            type: 'category',
                            grid: {
                                color: 'rgba(59, 130, 246, 0.1)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#94a3b8',
                                maxTicksLimit: 10
                            }
                        },
                        y: {
                            type: this.scaleType === 'logarithmic' ? 'logarithmic' : 'linear',
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(59, 130, 246, 0.1)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#94a3b8',
                                callback: function (value) {
                                    if (!value || isNaN(value)) return '$0';
                                    const num = parseFloat(value);
                                    if (num >= 1000000) {
                                        return '$' + (num / 1000000).toFixed(1) + 'M';
                                    } else if (num >= 1000) {
                                        return '$' + (num / 1000).toFixed(0) + 'K';
                                    } else {
                                        return '$' + num.toFixed(0);
                                    }
                                }
                            }
                        }
                    }
                }
            };

            try {
                this.chart = new Chart(canvas, chartConfig);
                console.log('✅ Liquidation History Chart created successfully');
            } catch (error) {
                console.error('🚨 Failed to create Liquidation History Chart:', error);

                // Show error message safely
                if (ctx && canvas.width > 0 && canvas.height > 0) {
                    ctx.fillStyle = '#ef4444';
                    ctx.font = '16px Arial';
                    ctx.fillText('Chart Error: ' + error.message, 20, 50);
                }
            }
        },

        // Methods yang dibutuhkan template (menggunakan nama yang sama dengan OI chart)
        setTimeRange(range) {
            this.globalPeriod = range;
            this.loadData();
        },

        toggleChartType(type) {
            this.chartType = type;
            this.updateChart();
        },

        setChartInterval(interval) {
            if (this.selectedInterval === interval) return;

            console.log('🔄 Setting chart interval to:', interval);
            this.selectedInterval = interval;
            this.loadData();
        },

        toggleScale(scale) {
            if (this.scaleType === scale) return;

            console.log('🔄 Toggling scale to:', scale);
            this.scaleType = scale;
            this.updateChart();
        },

        resetZoom() {
            if (this.chart && typeof this.chart.resetZoom === 'function') {
                console.log('🔄 Resetting chart zoom');
                this.chart.resetZoom();
            } else {
                console.warn('⚠️ Chart zoom plugin not available');
                // Fallback: just re-render the chart
                this.updateChart();
            }
        },

        exportChart(format) {
            if (!this.chart) return;

            const url = this.chart.toBase64Image();
            const link = document.createElement('a');
            link.download = `liquidation-history-${this.selectedSymbol}-${Date.now()}.${format}`;
            link.href = url;
            link.click();
        },

        shareChart() {
            if (!this.chart) return;

            const url = this.chart.toBase64Image();
            if (navigator.share) {
                navigator.share({
                    title: `Liquidation History - ${this.selectedSymbol}`,
                    text: `Liquidation analysis for ${this.selectedSymbol} on ${this.selectedExchange}`,
                    url: window.location.href
                });
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(window.location.href);
                alert('Chart URL copied to clipboard!');
            }
        },

        updateExchange() {
            this.loadData();
        },

        updateSymbol() {
            this.loadData();
        },

        refreshAll() {
            this.loadData();
        },

        // Helper methods untuk template
        formatLiq(value) {
            if (!value || isNaN(value)) return '$0';
            const num = parseFloat(value);
            if (num >= 1000000) {
                return '$' + (num / 1000000).toFixed(2) + 'M';
            } else if (num >= 1000) {
                return '$' + (num / 1000).toFixed(1) + 'K';
            } else {
                return '$' + num.toFixed(0);
            }
        },

        formatChange(change) {
            if (!change || isNaN(change)) return '0.00%';
            const num = parseFloat(change);
            const sign = num >= 0 ? '+' : '';
            return sign + num.toFixed(2) + '%';
        },

        getPriceTrendClass(change) {
            return change >= 0 ? 'text-success' : 'text-danger';
        },

        // Computed values untuk template
        get currentTotalLiq() {
            return this.currentLongLiq + this.currentShortLiq;
        },

        get longLiqRatio() {
            const total = this.currentTotalLiq;
            return total > 0 ? (this.currentLongLiq / total * 100) : 0;
        },

        get shortLiqRatio() {
            const total = this.currentTotalLiq;
            return total > 0 ? (this.currentShortLiq / total * 100) : 0;
        }
    };
}

// Make controller available globally for Alpine.js
window.liquidationHistoryChart = liquidationHistoryChart;