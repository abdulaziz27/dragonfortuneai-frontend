{{--
    Liquidation Heatmap Chart Component
    Visualizes liquidation intensity across time and exchanges
    Uses pair-history data with bucket aggregation
--}}

<div class="df-panel p-4 h-100"
     x-data="liquidationsHeatmapChart()"
     x-init="init()">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h5 class="mb-0">🔥 Liquidation Heatmap</h5>
            <small class="text-secondary">Intensity across time & exchanges</small>
        </div>
        <span x-show="loading" class="spinner-border spinner-border-sm text-primary"></span>
    </div>

    <!-- Chart Container -->
    <div style="height: 400px; position: relative;">
        <canvas x-ref="heatmapCanvas"></canvas>
    </div>

    <!-- Legend -->
    <div class="mt-3 d-flex justify-content-center gap-3">
        <div class="d-flex align-items-center gap-2">
            <div style="width: 20px; height: 20px; background: rgba(239, 68, 68, 0.8); border-radius: 4px;"></div>
            <span class="small">Long Liquidations</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div style="width: 20px; height: 20px; background: rgba(34, 197, 94, 0.8); border-radius: 4px;"></div>
            <span class="small">Short Liquidations</span>
        </div>
    </div>

    <!-- No Data State -->
    <div x-show="!loading && !hasData"
         class="position-absolute top-50 start-50 translate-middle text-center"
         style="z-index: 10;">
        <div class="text-secondary mb-2" style="font-size: 3rem;">🔥</div>
        <div class="text-secondary">No liquidation heatmap data available</div>
        <div class="small text-muted mt-2">Try changing symbol, exchange, or time interval</div>
    </div>
</div>

<script>
function liquidationsHeatmapChart() {
    return {
        loading: false,
        hasData: false,
        chart: null,
        pairHistoryData: [],

        async init() {
            console.log('🔥 Heatmap Chart: Initializing component');

            // Wait for Chart.js to load
            await window.chartJsReady;
            console.log('🔥 Heatmap Chart: Chart.js ready');

            // Listen for overview ready
            window.addEventListener('liquidations-overview-ready', (e) => {
                console.log('🔥 Heatmap Chart: Received overview ready event');
                this.applyOverview(e.detail);
            });

            // Listen for filter changes
            window.addEventListener('symbol-changed', () => {
                this.loadData();
            });

            window.addEventListener('exchange-changed', () => {
                this.loadData();
            });

            window.addEventListener('interval-changed', () => {
                this.loadData();
            });

            window.addEventListener('refresh-all', () => {
                this.loadData();
            });

            // Initial load with delay to ensure DOM is ready
            setTimeout(() => {
                if (this.$root?.overview) {
                    this.applyOverview(this.$root.overview);
                } else {
                    this.loadData();
                }
            }, 100);
        },

        applyOverview(overview) {
            console.log('🔥 Heatmap Chart: Applying overview', overview);

            if (!overview?.pairHistory || !Array.isArray(overview.pairHistory)) {
                console.warn('🔥 Heatmap Chart: No pair history data in overview');
                this.hasData = false;
                return;
            }

            if (overview.pairHistory.length === 0) {
                console.warn('🔥 Heatmap Chart: Empty pair history data');
                this.hasData = false;
                return;
            }

            this.pairHistoryData = overview.pairHistory;
            console.log('🔥 Heatmap Chart: Processed data points:', this.pairHistoryData.length);
            this.renderChart();
        },

        renderChart() {
            console.log('🔥 Heatmap Chart: Rendering chart with', this.pairHistoryData?.length || 0, 'data points');

            if (!this.pairHistoryData || this.pairHistoryData.length === 0) {
                console.warn('🔥 Heatmap Chart: No data to render');
                this.hasData = false;
                return;
            }

            this.hasData = true;

            // Group data by exchange
            const exchangeData = {};
            this.pairHistoryData.forEach(row => {
                if (!exchangeData[row.exchange]) {
                    exchangeData[row.exchange] = [];
                }
                exchangeData[row.exchange].push({
                    ts: row.ts,
                    long: parseFloat(row.long_liq_usd || 0),
                    short: parseFloat(row.short_liq_usd || 0),
                    total: parseFloat(row.liq_usd || 0),
                });
            });

            // Sort and limit to recent data
            Object.keys(exchangeData).forEach(exchange => {
                exchangeData[exchange] = exchangeData[exchange]
                    .sort((a, b) => a.ts - b.ts)
                    .slice(-20); // Last 20 buckets
            });

            // Get all unique timestamps
            const allTimestamps = [...new Set(
                Object.values(exchangeData)
                    .flat()
                    .map(d => d.ts)
            )].sort((a, b) => a - b);

            // Prepare datasets for stacked bar chart (simulating heatmap)
            const exchanges = Object.keys(exchangeData).slice(0, 5); // Top 5 exchanges
            const labels = allTimestamps.map(ts => this.formatTimestamp(ts));

            const longDatasets = exchanges.map((exchange, idx) => ({
                label: `${exchange} - Long`,
                data: allTimestamps.map(ts => {
                    const point = exchangeData[exchange]?.find(d => d.ts === ts);
                    return point ? point.long : 0;
                }),
                backgroundColor: this.getExchangeColor(idx, 'long'),
                stack: 'long',
            }));

            const shortDatasets = exchanges.map((exchange, idx) => ({
                label: `${exchange} - Short`,
                data: allTimestamps.map(ts => {
                    const point = exchangeData[exchange]?.find(d => d.ts === ts);
                    return point ? point.short : 0;
                }),
                backgroundColor: this.getExchangeColor(idx, 'short'),
                stack: 'short',
            }));

            // Destroy existing chart
            if (this.chart) {
                this.chart.destroy();
            }

            // Create new chart
            const canvas = this.$refs.heatmapCanvas;
            if (!canvas) {
                console.error('Canvas element not found');
                return;
            }

            const ctx = canvas.getContext('2d');
            if (!ctx) {
                console.error('Canvas context not available');
                return;
            }

            this.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [...longDatasets, ...shortDatasets],
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
                            display: false, // Too many series, hide legend
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            callbacks: {
                                label: (context) => {
                                    const label = context.dataset.label || '';
                                    const value = this.formatUSD(context.parsed.y);
                                    return `${label}: ${value}`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            stacked: true,
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 10 },
                                maxRotation: 45,
                                minRotation: 45,
                            },
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            stacked: true,
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 10 },
                                callback: (value) => this.formatUSD(value),
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.1)',
                            },
                        },
                    },
                },
                    });

                    console.log('✅ Heatmap Chart: Chart rendered successfully');
                },

                getExchangeColor(index, side) {
            const baseColors = [
                '#3b82f6',  // blue
                '#8b5cf6',  // purple
                '#f59e0b',  // orange
                '#06b6d4',  // cyan
                '#ec4899',  // pink
            ];

            const color = baseColors[index % baseColors.length];

            // Adjust opacity based on side
            if (side === 'long') {
                return color.replace(')', ', 0.7)').replace('#', 'rgba(') || color + 'b3';
            } else {
                return color.replace(')', ', 0.4)').replace('#', 'rgba(') || color + '66';
            }
        },

        async loadData() {
            this.loading = true;
            console.log('🔥 Heatmap Chart: Loading data...');
            setTimeout(() => {
                this.loading = false;
                console.log('🔥 Heatmap Chart: Data loading completed');
            }, 1000);
        },

        formatUSD(value) {
            if (value === null || value === undefined) return 'N/A';
            const num = parseFloat(value);
            if (isNaN(num)) return 'N/A';

            if (num >= 1e9) return '$' + (num / 1e9).toFixed(2) + 'B';
            if (num >= 1e6) return '$' + (num / 1e6).toFixed(1) + 'M';
            if (num >= 1e3) return '$' + (num / 1e3).toFixed(0) + 'K';
            return '$' + num.toFixed(0);
        },

        formatTimestamp(timestamp) {
            if (!timestamp) return 'N/A';
            const date = new Date(timestamp);
            return date.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
            });
        },
    };
}
</script>

