{{--
    Komponen: Perp-Quarterly Spread History Chart
    Menampilkan historical spread movement dengan Chart.js

    Props:
    - $symbol: string (default: 'BTC')
    - $exchange: string (default: 'Binance')
    - $height: string (default: '400px')
--}}

<div class="df-panel p-3 h-100 d-flex flex-column"
     x-data="spreadHistoryChart('{{ $symbol ?? 'BTC' }}', '{{ $exchange ?? 'Binance' }}')">
    <!-- Header -->
    <div class="mb-3 flex-shrink-0">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">📈 Spread History</h5>
                <small class="text-secondary">Perp-Quarterly spread movement over time</small>
            </div>
            <button class="btn btn-sm btn-outline-secondary" @click="refresh()" :disabled="loading">
                <span x-show="!loading">🔄</span>
                <span x-show="loading" class="spinner-border spinner-border-sm"></span>
            </button>
        </div>
    </div>

    <!-- Chart Container -->
    <div class="flex-grow-1" style="min-height: {{ $height ?? '400px' }};">
        <canvas :id="chartId"></canvas>
    </div>

    <!-- Chart Legend Info -->
    <div class="mt-2 d-flex justify-content-between align-items-center text-secondary small">
        <div>
            <span class="badge bg-success bg-opacity-10 text-success">Contango (Perp > Quarterly)</span>
            <span class="badge bg-danger bg-opacity-10 text-danger ms-1">Backwardation (Quarterly > Perp)</span>
        </div>
        <div x-show="dataPoints > 0">
            <span x-text="dataPoints + ' data points'">-- data points</span>
        </div>
    </div>
</div>

<script>
function spreadHistoryChart(initialSymbol = 'BTC', initialExchange = 'Binance') {
    return {
        symbol: initialSymbol,
        quote: 'USDT',
        exchange: initialExchange,
        interval: '5m',
        perpSymbol: '', // Auto-generated if empty
        limit: '2000', // Data limit
        loading: false,
        chart: null,
        chartId: 'spreadHistoryChart_' + Math.random().toString(36).substr(2, 9),
        dataPoints: 0,

        init() {
            setTimeout(() => {
                this.initChart();
                this.loadData();
            }, 1000);

            // Auto refresh every 60 seconds
            setInterval(() => this.loadData(), 60000);

            // Listen to global filter changes
            window.addEventListener('symbol-changed', (e) => {
                this.symbol = e.detail?.symbol || this.symbol;
                this.quote = e.detail?.quote || this.quote;
                this.exchange = e.detail?.exchange || this.exchange;
                this.interval = e.detail?.interval || this.interval;
                this.perpSymbol = e.detail?.perpSymbol || this.perpSymbol;
                this.limit = e.detail?.limit || this.limit;
                this.loadData();
            });
            window.addEventListener('quote-changed', (e) => {
                this.quote = e.detail?.quote || this.quote;
                this.limit = e.detail?.limit || this.limit;
                this.loadData();
            });
            window.addEventListener('exchange-changed', (e) => {
                this.exchange = e.detail?.exchange || this.exchange;
                this.limit = e.detail?.limit || this.limit;
                this.loadData();
            });
            window.addEventListener('interval-changed', (e) => {
                this.interval = e.detail?.interval || this.interval;
                this.limit = e.detail?.limit || this.limit;
                this.loadData();
            });
            window.addEventListener('perp-symbol-changed', (e) => {
                this.perpSymbol = e.detail?.perpSymbol || this.perpSymbol;
                this.limit = e.detail?.limit || this.limit;
                this.loadData();
            });
            window.addEventListener('limit-changed', (e) => {
                this.limit = e.detail?.limit || this.limit;
                this.loadData();
            });
            window.addEventListener('refresh-all', () => {
                this.loadData();
            });

            // Listen to overview composite
            window.addEventListener('perp-quarterly-overview-ready', (e) => {
                if (e.detail?.timeseries) {
                    this.updateChartFromOverview(e.detail.timeseries);
                }
            });
        },

        initChart() {
            // Destroy existing chart if it exists
            if (this.chart) {
                this.chart.destroy();
                this.chart = null;
            }

            const canvas = document.getElementById(this.chartId);
            if (!canvas) {
                console.warn('Canvas not found:', this.chartId);
                return;
            }

            const ctx = canvas.getContext('2d');

            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    datasets: [
                        {
                            label: 'Spread (BPS)',
                            data: [],
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: this.createGradient(ctx),
                            tension: 0.4,
                            fill: true,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                            borderWidth: 2,
                        },
                        {
                            label: 'Zero Line',
                            data: [],
                            borderColor: 'rgb(156, 163, 175)',
                            borderDash: [5, 5],
                            pointRadius: 0,
                            borderWidth: 1,
                            fill: false,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false, // Disable animations to prevent stack overflow
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            borderWidth: 1,
                            callbacks: {
                                label: (context) => {
                                    if (context.dataset.label === 'Zero Line') return null;
                                    const value = context.parsed.y;
                                    const sign = value >= 0 ? '+' : '';
                                    return `Spread: ${sign}${value.toFixed(2)} bps`;
                                },
                                afterLabel: (context) => {
                                    if (context.dataset.label === 'Zero Line') return null;
                                    const value = context.parsed.y;
                                    if (value > 0) return 'Contango (Perp > Quarterly)';
                                    if (value < 0) return 'Backwardation (Quarterly > Perp)';
                                    return 'Neutral';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 10 },
                                maxRotation: 45,
                                minRotation: 0,
                                callback: function(value, index) {
                                    // Show every 10th label to avoid crowding
                                    return index % 10 === 0 ? `#${index}` : '';
                                }
                            },
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Spread (BPS)',
                                color: '#94a3b8',
                            },
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 10 },
                                callback: (value) => {
                                    const sign = value >= 0 ? '+' : '';
                                    return sign + value.toFixed(0);
                                }
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.1)',
                            },
                        },
                    },
                }
            });

            console.log('✅ Spread history chart initialized');
        },

        async loadData() {
            this.loading = true;
            try {
                const actualPerpSymbol = this.perpSymbol || `${this.symbol}${this.quote}`;
                const params = new URLSearchParams({
                    exchange: this.exchange,
                    base: this.symbol,
                    quote: this.quote,
                    interval: this.interval,
                    limit: this.limit,
                    perp_symbol: actualPerpSymbol
                });

                const baseMeta = document.querySelector('meta[name="api-base-url"]');
                const configuredBase = (baseMeta?.content || '').trim();
                const base = configuredBase ? (configuredBase.endsWith('/') ? configuredBase.slice(0, -1) : configuredBase) : '';
                const url = base ? `${base}/api/perp-quarterly/history?${params}` : `/api/perp-quarterly/history?${params}`;

                console.log('📡 Fetching Perp-Quarterly History:', url);

                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();
                this.updateChart(data.data || []);
                console.log('✅ Spread history loaded:', data.data?.length, 'points');
            } catch (error) {
                console.error('❌ Error loading spread history:', error);
                this.updateChart([]);
            } finally {
                this.loading = false;
            }
        },

        updateChart(historyData) {
            if (!this.chart) return;

            // Limit data points for performance and slice to avoid stack overflow
            const limitedData = historyData.slice(-500);

            // Process data with simple index-based x values
            const chartData = limitedData.map((row, index) => ({
                x: index,
                y: parseFloat(row.spread_bps) || 0
            }));

            // Add zero line data
            const zeroData = chartData.map(point => ({
                x: point.x,
                y: 0
            }));

            // Update chart datasets
            this.chart.data.datasets[0].data = chartData;
            this.chart.data.datasets[1].data = zeroData;

            // Update gradient based on data
            this.updateGradientColor(chartData);

            this.chart.update('none');
            this.dataPoints = chartData.length;
        },

        updateChartFromOverview(timeseries) {
            if (!this.chart || !Array.isArray(timeseries)) return;

            // Limit data points for performance
            const limitedData = timeseries.slice(-500);

            const chartData = limitedData.map((row, index) => ({
                x: index,
                y: parseFloat(row.spread_bps) || 0
            }));

            const zeroData = chartData.map(point => ({
                x: point.x,
                y: 0
            }));

            this.chart.data.datasets[0].data = chartData;
            this.chart.data.datasets[1].data = zeroData;
            this.updateGradientColor(chartData);
            this.chart.update('none');
            this.dataPoints = chartData.length;
        },

        updateGradientColor(data) {
            if (!this.chart || !data.length) return;

            const avgSpread = data.reduce((sum, d) => sum + d.y, 0) / data.length;
            const canvas = document.getElementById(this.chartId);
            const ctx = canvas?.getContext('2d');

            if (!ctx) return;

            let gradient;
            if (avgSpread > 10) {
                // Strong contango - green
                gradient = ctx.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, 'rgba(34, 197, 94, 0.3)');
                gradient.addColorStop(1, 'rgba(34, 197, 94, 0)');
                this.chart.data.datasets[0].borderColor = 'rgb(34, 197, 94)';
            } else if (avgSpread < -10) {
                // Strong backwardation - red
                gradient = ctx.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, 'rgba(239, 68, 68, 0.3)');
                gradient.addColorStop(1, 'rgba(239, 68, 68, 0)');
                this.chart.data.datasets[0].borderColor = 'rgb(239, 68, 68)';
            } else {
                // Neutral - blue
                gradient = this.createGradient(ctx);
                this.chart.data.datasets[0].borderColor = 'rgb(59, 130, 246)';
            }

            this.chart.data.datasets[0].backgroundColor = gradient;
        },

        createGradient(ctx) {
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
            gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');
            return gradient;
        },

        refresh() {
            this.loadData();
        }
    };
}
</script>

