{{--
    Historical Liquidations Chart Component
    Time series visualization of liquidations with price overlay
    Uses pair-history data
--}}

<div class="df-panel p-4 h-100"
     x-data="liquidationsHistoricalChart()"
     x-init="init()">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h5 class="mb-0">📈 Historical Liquidations</h5>
            <small class="text-secondary">Time series with price overlay</small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <select class="form-select form-select-sm" style="width: 120px;"
                    x-model="chartType" @change="renderChart()">
                <option value="line">Line Chart</option>
                <option value="bar">Bar Chart</option>
                <option value="area">Area Chart</option>
            </select>
            <span x-show="loading" class="spinner-border spinner-border-sm text-primary"></span>
        </div>
    </div>

    <!-- Chart Container -->
    <div style="height: 400px; position: relative;">
        <canvas x-ref="historicalCanvas"></canvas>
    </div>

    <!-- Stats Summary -->
    <div class="row g-2 mt-3">
        <div class="col-md-3 col-6">
            <div class="p-2 rounded bg-primary bg-opacity-10 text-center">
                <div class="small text-secondary">Data Points</div>
                <div class="fw-bold" x-text="dataPoints">0</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="p-2 rounded bg-danger bg-opacity-10 text-center">
                <div class="small text-secondary">Avg Long</div>
                <div class="fw-bold text-danger" x-text="formatUSD(avgLong)">$0</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="p-2 rounded bg-success bg-opacity-10 text-center">
                <div class="small text-secondary">Avg Short</div>
                <div class="fw-bold text-success" x-text="formatUSD(avgShort)">$0</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="p-2 rounded bg-warning bg-opacity-10 text-center">
                <div class="small text-secondary">Peak Total</div>
                <div class="fw-bold text-warning" x-text="formatUSD(peakTotal)">$0</div>
            </div>
        </div>
    </div>

    <!-- No Data State -->
    <div x-show="!loading && !hasData"
         class="position-absolute top-50 start-50 translate-middle text-center"
         style="z-index: 10;">
        <div class="text-secondary mb-2" style="font-size: 3rem;">📈</div>
        <div class="text-secondary">No historical liquidation data available</div>
        <div class="small text-muted mt-2">Try changing symbol, exchange, or time interval</div>
    </div>
</div>

<script>
function liquidationsHistoricalChart() {
    return {
        loading: false,
        hasData: false,
        chart: null,
        pairHistoryData: [],
        chartType: 'line',

        // Stats
        dataPoints: 0,
        avgLong: 0,
        avgShort: 0,
        peakTotal: 0,

        async init() {
            console.log('📊 Historical Chart: Initializing component');

            // Wait for Chart.js
            await window.chartJsReady;
            console.log('📊 Historical Chart: Chart.js ready');

            // Listen for overview ready
            window.addEventListener('liquidations-overview-ready', (e) => {
                console.log('📊 Historical Chart: Received overview ready event');
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
            console.log('📊 Historical Chart: Applying overview', overview);

            if (!overview?.pairHistory || !Array.isArray(overview.pairHistory)) {
                console.warn('📊 Historical Chart: No pair history data available');
                this.hasData = false;
                return;
            }

            if (overview.pairHistory.length === 0) {
                console.warn('📊 Historical Chart: Empty pair history data');
                this.hasData = false;
                return;
            }

            this.pairHistoryData = overview.pairHistory.sort((a, b) => a.ts - b.ts);
            console.log('📊 Historical Chart: Processed data points:', this.pairHistoryData.length);
            this.calculateStats();
            this.renderChart();
        },

        calculateStats() {
            if (this.pairHistoryData.length === 0) return;

            this.dataPoints = this.pairHistoryData.length;

            const longValues = this.pairHistoryData.map(d => parseFloat(d.long_liq_usd || 0));
            const shortValues = this.pairHistoryData.map(d => parseFloat(d.short_liq_usd || 0));
            const totalValues = this.pairHistoryData.map(d => parseFloat(d.liq_usd || 0));

            this.avgLong = longValues.reduce((a, b) => a + b, 0) / longValues.length;
            this.avgShort = shortValues.reduce((a, b) => a + b, 0) / shortValues.length;
            this.peakTotal = Math.max(...totalValues);
        },

        renderChart() {
            console.log('📊 Historical Chart: Rendering chart with', this.pairHistoryData?.length || 0, 'data points');

            if (!this.pairHistoryData || this.pairHistoryData.length === 0) {
                console.warn('📊 Historical Chart: No data to render');
                this.hasData = false;
                return;
            }

            this.hasData = true;

            // Prepare data
            const labels = this.pairHistoryData.map(d => this.formatTimestamp(d.ts));
            const longData = this.pairHistoryData.map(d => parseFloat(d.long_liq_usd || 0));
            const shortData = this.pairHistoryData.map(d => parseFloat(d.short_liq_usd || 0));

            // Determine chart type config
            let chartTypeConfig = 'line';
            let fillConfig = false;
            let tensionConfig = 0.4;

            if (this.chartType === 'bar') {
                chartTypeConfig = 'bar';
            } else if (this.chartType === 'area') {
                chartTypeConfig = 'line';
                fillConfig = true;
                tensionConfig = 0.4;
            }

            // Destroy existing chart
            if (this.chart) {
                this.chart.destroy();
            }

            // Create new chart
            const canvas = this.$refs.historicalCanvas;
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
                type: chartTypeConfig,
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Long Liquidations',
                            data: longData,
                            backgroundColor: fillConfig ? 'rgba(239, 68, 68, 0.2)' : 'rgba(239, 68, 68, 0.8)',
                            borderColor: 'rgb(239, 68, 68)',
                            borderWidth: 2,
                            fill: fillConfig,
                            tension: tensionConfig,
                            pointRadius: this.chartType === 'bar' ? 0 : 2,
                            pointHoverRadius: this.chartType === 'bar' ? 0 : 4,
                        },
                        {
                            label: 'Short Liquidations',
                            data: shortData,
                            backgroundColor: fillConfig ? 'rgba(34, 197, 94, 0.2)' : 'rgba(34, 197, 94, 0.8)',
                            borderColor: 'rgb(34, 197, 94)',
                            borderWidth: 2,
                            fill: fillConfig,
                            tension: tensionConfig,
                            pointRadius: this.chartType === 'bar' ? 0 : 2,
                            pointHoverRadius: this.chartType === 'bar' ? 0 : 4,
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
                            display: true,
                            position: 'top',
                            labels: {
                                color: '#94a3b8',
                                font: { size: 11 },
                                usePointStyle: true,
                            },
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
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 9 },
                                maxRotation: 45,
                                minRotation: 45,
                                maxTicksLimit: 15,
                            },
                            grid: {
                                display: false,
                            },
                        },
                        y: {
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

                    console.log('✅ Historical Chart: Chart rendered successfully');
                },

                async loadData() {
                    this.loading = true;
                    console.log('📊 Historical Chart: Loading data...');
                    setTimeout(() => {
                        this.loading = false;
                        console.log('📊 Historical Chart: Data loading completed');
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

