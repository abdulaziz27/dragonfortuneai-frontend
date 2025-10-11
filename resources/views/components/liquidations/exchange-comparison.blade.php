{{--
    Exchange Comparison Component
    Compares liquidation volumes across exchanges for different time ranges
    Uses exchange-list endpoint data
--}}

<div class="df-panel p-4 h-100"
     x-data="liquidationsExchangeComparison()"
     x-init="init()">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h5 class="mb-0">📊 Exchange Comparison</h5>
            <small class="text-secondary">Volume breakdown by exchange</small>
        </div>
        <span x-show="loading" class="spinner-border spinner-border-sm text-primary"></span>
    </div>

    <!-- Time Range Tabs -->
    <ul class="nav nav-pills mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link" :class="selectedRange === '1h' ? 'active' : ''"
                    @click="selectedRange = '1h'" type="button">
                1H
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" :class="selectedRange === '4h' ? 'active' : ''"
                    @click="selectedRange = '4h'" type="button">
                4H
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" :class="selectedRange === '12h' ? 'active' : ''"
                    @click="selectedRange = '12h'" type="button">
                12H
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" :class="selectedRange === '24h' ? 'active' : ''"
                    @click="selectedRange = '24h'" type="button">
                24H
            </button>
        </li>
    </ul>

    <!-- Chart Container -->
    <div style="height: 350px; position: relative;">
        <canvas x-ref="comparisonCanvas"></canvas>
    </div>

    <!-- Exchange Stats Grid -->
    <div class="row g-2 mt-3">
        <template x-for="(exchange, index) in topExchanges" :key="index">
            <div class="col-6 col-md-4">
                <div class="p-2 rounded bg-dark bg-opacity-10">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-bold" x-text="exchange.name">Exchange</span>
                        <span class="badge bg-primary" x-text="'#' + (index + 1)">#1</span>
                    </div>
                    <div class="fw-bold" x-text="formatUSD(exchange.total)">$0</div>
                    <div class="small text-secondary" x-text="exchange.percentage + '%'">0%</div>
                </div>
            </div>
        </template>
    </div>

    <!-- No Data State -->
    <div x-show="!loading && !hasData"
         class="position-absolute top-50 start-50 translate-middle text-center"
         style="z-index: 10;">
        <div class="text-secondary mb-2" style="font-size: 3rem;">📊</div>
        <div class="text-secondary">No comparison data available</div>
    </div>
</div>

<script>
function liquidationsExchangeComparison() {
    return {
        loading: false,
        hasData: false,
        chart: null,
        exchangeListData: {},
        selectedRange: '1h',
        topExchanges: [],

        async init() {
            // Wait for Chart.js
            await window.chartJsReady;

            // Listen for overview ready
            window.addEventListener('liquidations-overview-ready', (e) => {
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

            // Watch selected range
            this.$watch('selectedRange', () => {
                this.renderChart();
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
            if (!overview?.exchangeList) return;
            this.exchangeListData = overview.exchangeList;
            this.renderChart();
        },

        renderChart() {
            const data = this.exchangeListData[this.selectedRange];
            if (!data || !Array.isArray(data) || data.length === 0) {
                this.hasData = false;
                return;
            }

            this.hasData = true;

            // Filter out "All" exchange and get top 8
            const exchanges = data
                .filter(ex => ex.exchange !== 'All')
                .map(ex => ({
                    name: ex.exchange,
                    total: parseFloat(ex.liquidation_usd) || 0,
                    long: parseFloat(ex.long_liquidation_usd) || 0,
                    short: parseFloat(ex.short_liquidation_usd) || 0,
                }))
                .sort((a, b) => b.total - a.total)
                .slice(0, 8);

            // Calculate percentages
            const totalVolume = exchanges.reduce((sum, ex) => sum + ex.total, 0);
            this.topExchanges = exchanges.map(ex => ({
                ...ex,
                percentage: ((ex.total / totalVolume) * 100).toFixed(1),
            }));

            // Prepare chart data
            const labels = exchanges.map(ex => ex.name);
            const longData = exchanges.map(ex => ex.long);
            const shortData = exchanges.map(ex => ex.short);

            // Destroy existing chart
            if (this.chart) {
                this.chart.destroy();
            }

            // Create new chart
            const canvas = this.$refs.comparisonCanvas;
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
                    datasets: [
                        {
                            label: 'Long Liquidations',
                            data: longData,
                            backgroundColor: 'rgba(239, 68, 68, 0.8)',
                            borderColor: 'rgb(239, 68, 68)',
                            borderWidth: 1,
                        },
                        {
                            label: 'Short Liquidations',
                            data: shortData,
                            backgroundColor: 'rgba(34, 197, 94, 0.8)',
                            borderColor: 'rgb(34, 197, 94)',
                            borderWidth: 1,
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
                            stacked: false,
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 10 },
                            },
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            stacked: false,
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
        },

        async loadData() {
            this.loading = true;
            setTimeout(() => {
                this.loading = false;
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
    };
}
</script>

