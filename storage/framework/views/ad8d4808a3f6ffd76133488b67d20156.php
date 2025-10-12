

<div class="df-panel p-4" x-data="vwapBandsChart('<?php echo e($symbol ?? 'BTCUSDT'); ?>', '<?php echo e($timeframe ?? '5min'); ?>', '<?php echo e($exchange ?? 'binance'); ?>', <?php echo e($limit ?? 100); ?>)">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-1">📈 VWAP & Bands</h5>
            <p class="small text-secondary mb-0">Volume-Weighted Average Price with volatility bands</p>
        </div>
        <button class="btn btn-sm btn-outline-secondary" @click="refresh()" :disabled="loading">
            <span x-show="!loading">🔄</span>
            <span x-show="loading" class="spinner-border spinner-border-sm"></span>
        </button>
    </div>

    <!-- Loading State -->
    <template x-if="loading && !chartInstance">
        <div class="text-center py-5" style="height: 400px;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-secondary mt-2 mb-0">Loading chart data...</p>
        </div>
    </template>

    <!-- Error State -->
    <template x-if="!loading && error">
        <div class="alert alert-warning text-center" style="height: 400px; display: flex; align-items: center; justify-content: center;">
            <div>
                <i class="bi bi-exclamation-triangle fs-2 d-block mb-2"></i>
                <p class="mb-0" x-text="error">Unable to fetch data</p>
            </div>
        </div>
    </template>

    <!-- Chart Canvas -->
    <div class="position-relative" style="height: 400px;">
        <canvas x-ref="chartCanvas"></canvas>
    </div>

    <!-- Legend Info -->
    <div class="row g-2 mt-3">
        <div class="col-auto">
            <div class="d-flex align-items-center gap-2">
                <div style="width: 16px; height: 3px; background: #10b981;"></div>
                <span class="small">VWAP</span>
            </div>
        </div>
        <div class="col-auto">
            <div class="d-flex align-items-center gap-2">
                <div style="width: 16px; height: 3px; background: #ef4444; border-style: dashed;"></div>
                <span class="small">Upper Band</span>
            </div>
        </div>
        <div class="col-auto">
            <div class="d-flex align-items-center gap-2">
                <div style="width: 16px; height: 3px; background: #ef4444; border-style: dashed;"></div>
                <span class="small">Lower Band</span>
            </div>
        </div>
    </div>
</div>

<script>
function vwapBandsChart(initialSymbol = 'BTCUSDT', initialTimeframe = '5min', initialExchange = 'binance', initialLimit = 100) {
    return {
        symbol: initialSymbol,
        timeframe: initialTimeframe,
        exchange: initialExchange,
        limit: initialLimit,
        loading: false,
        error: null,
        chartInstance: null,
        data: [],

        init() {
            // Wait for Chart.js to be ready
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js not loaded yet, waiting...');
                setTimeout(() => this.init(), 200);
                return;
            }

            // Use centralized data approach only
            this.listenForData();
        },

        listenForData() {
            // Listen for centralized data (primary source)
            const handleData = (e) => {
                if (e.detail?.historical && Array.isArray(e.detail.historical)) {
                    this.data = e.detail.historical;
                    this.error = null;
                    this.loading = false;
                    // Small delay to ensure DOM is ready
                    setTimeout(() => this.renderChart(), 100);
                }
            };

            window.addEventListener('vwap-data-ready', handleData);

            // Listen to filter changes (will trigger controller to reload data)
            window.addEventListener('symbol-changed', () => {
                this.loading = true;
            });
            window.addEventListener('timeframe-changed', () => {
                this.loading = true;
            });
            window.addEventListener('exchange-changed', () => {
                this.loading = true;
            });
        },

        refresh() {
            // Trigger global refresh which will broadcast vwap-data-ready
            window.dispatchEvent(new CustomEvent('refresh-all'));
        },

        renderChart() {
            if (!this.data || this.data.length === 0) {
                console.warn('No data to render chart');
                return;
            }
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js not loaded');
                return;
            }

            const canvas = this.$refs.chartCanvas;
            if (!canvas) {
                console.warn('Canvas not found');
                return;
            }

            // Destroy existing chart instance FIRST
            if (this.chartInstance) {
                try {
                    this.chartInstance.destroy();
                    this.chartInstance = null;
                } catch (e) {
                    console.warn('Error destroying chart:', e);
                }
            }

            const ctx = canvas.getContext('2d');
            if (!ctx) {
                console.warn('Cannot get canvas context');
                return;
            }

            // Prepare data - take only recent data points to avoid overload
            const sortedData = [...this.data]
                .sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp))
                .slice(-100); // Take last 100 points

            // Format labels as readable strings (avoid time scale issues)
            const labels = sortedData.map(d => {
                const date = new Date(d.timestamp);
                return date.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });
            });

            const vwapData = sortedData.map(d => parseFloat(d.vwap));
            const upperBandData = sortedData.map(d => parseFloat(d.upper_band));
            const lowerBandData = sortedData.map(d => parseFloat(d.lower_band));

            try {
                // Create new chart with category scale (simpler than time scale)
                this.chartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'VWAP',
                                data: vwapData,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                borderWidth: 2,
                                pointRadius: 0,
                                pointHoverRadius: 4,
                                fill: false,
                                tension: 0.4,
                            },
                            {
                                label: 'Upper Band',
                                data: upperBandData,
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239, 68, 68, 0.05)',
                                borderWidth: 1.5,
                                borderDash: [5, 5],
                                pointRadius: 0,
                                pointHoverRadius: 3,
                                fill: false,
                                tension: 0.4,
                            },
                            {
                                label: 'Lower Band',
                                data: lowerBandData,
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239, 68, 68, 0.05)',
                                borderWidth: 1.5,
                                borderDash: [5, 5],
                                pointRadius: 0,
                                pointHoverRadius: 3,
                                fill: '-1',
                                tension: 0.4,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                borderColor: 'rgba(255, 255, 255, 0.1)',
                                borderWidth: 1,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += new Intl.NumberFormat('en-US', {
                                                style: 'currency',
                                                currency: 'USD',
                                            }).format(context.parsed.y);
                                        }
                                        return label;
                                    },
                                },
                            },
                        },
                        scales: {
                            x: {
                                ticks: {
                                    color: '#94a3b8',
                                    font: { size: 10 },
                                    maxRotation: 0,
                                    autoSkipPadding: 30,
                                    maxTicksLimit: 10,
                                },
                                grid: {
                                    display: false,
                                },
                            },
                            y: {
                                ticks: {
                                    color: '#94a3b8',
                                    font: { size: 10 },
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    },
                                },
                                grid: {
                                    color: 'rgba(148, 163, 184, 0.1)',
                                },
                            },
                        },
                    },
                });

                console.log('✅ VWAP chart rendered with', sortedData.length, 'data points');
            } catch (error) {
                console.error('❌ Error creating chart:', error);
                this.error = 'Failed to render chart. Please refresh the page.';
            }
        },
    };
}
</script>

<?php /**PATH C:\DATASAID\Said\Bisnis\quantwaru\frontend\dragonfortuneai-frontend\resources\views/components/vwap/bands-chart.blade.php ENDPATH**/ ?>