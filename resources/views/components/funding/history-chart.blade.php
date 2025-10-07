{{--
    Komponen: Funding Rate History Chart
    Menampilkan historical funding rate dengan OHLC data

    Props:
    - $symbol: string (default: 'BTC')
    - $interval: string (default: '4h')

    Interpretasi:
    - Candlestick untuk melihat volatilitas funding rate
    - Wick panjang → High volatility dalam periode
    - Trend naik konsisten → Long bias strengthening
    - Spike tiba-tiba → Extreme positioning / squeeze risk
--}}

<div class="df-panel p-3" style="min-height: 350px;" x-data="historyFundingChart('{{ $symbol ?? 'BTC' }}', '{{ $interval ?? '4h' }}')">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <h5 class="mb-0">📉 Funding Rate History</h5>
            <span class="badge text-bg-secondary" x-text="chartData.length + ' periods'">0 periods</span>
        </div>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm" style="width: auto;" x-model="interval" @change="loadData()">
                <option value="1h">1 Hour</option>
                <option value="4h">4 Hours</option>
                <option value="8h">8 Hours</option>
                <option value="1d">1 Day</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary" @click="refresh()" :disabled="loading">
                <span x-show="!loading">🔄</span>
                <span x-show="loading" class="spinner-border spinner-border-sm"></span>
            </button>
        </div>
    </div>

    <!-- Chart Canvas -->
    <div style="position: relative; height: 280px;">
        <canvas :id="chartId"></canvas>
    </div>

    <!-- OHLC Stats -->
    <div class="row g-2 mt-2">
        <div class="col-3">
            <div class="text-center p-2 bg-light rounded">
                <div class="small text-secondary">Open</div>
                <div class="fw-bold" x-text="formatRate(lastOHLC.open)">--</div>
            </div>
        </div>
        <div class="col-3">
            <div class="text-center p-2 bg-success bg-opacity-10 rounded">
                <div class="small text-secondary">High</div>
                <div class="fw-bold text-success" x-text="formatRate(lastOHLC.high)">--</div>
            </div>
        </div>
        <div class="col-3">
            <div class="text-center p-2 bg-danger bg-opacity-10 rounded">
                <div class="small text-secondary">Low</div>
                <div class="fw-bold text-danger" x-text="formatRate(lastOHLC.low)">--</div>
            </div>
        </div>
        <div class="col-3">
            <div class="text-center p-2 bg-primary bg-opacity-10 rounded">
                <div class="small text-secondary">Close</div>
                <div class="fw-bold text-primary" x-text="formatRate(lastOHLC.close)">--</div>
            </div>
        </div>
    </div>
</div>

<script>
function historyFundingChart(initialSymbol = 'BTC', initialInterval = '4h') {
    return {
        symbol: initialSymbol,
        interval: initialInterval,
        loading: false,
        chart: null,
        chartId: 'historyChart_' + Math.random().toString(36).substr(2, 9),
        chartData: [],
        lastOHLC: { open: 0, high: 0, low: 0, close: 0 },
        updatePending: false,

        async init() {
            // Wait for Chart.js to be loaded
            if (typeof Chart === 'undefined') {
                console.log('⏳ Waiting for Chart.js to load...');
                await window.chartJsReady;
            }

            setTimeout(() => {
                this.initChart();
                this.loadData();
            }, 1000);

            // Listen for global refresh
            this.$watch('symbol', () => this.loadData());
        },

        initChart() {
            const canvas = document.getElementById(this.chartId);
            if (!canvas) {
                console.warn('⚠️ Canvas not found for history chart');
                return;
            }

            if (typeof Chart === 'undefined') {
                console.error('❌ Chart.js not loaded');
                return;
            }

            const ctx = canvas.getContext('2d');

            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Funding Rate',
                        data: [],
                        borderColor: '#3b82f6',
                        backgroundColor: (context) => {
                            const ctx = context.chart.ctx;
                            const gradient = ctx.createLinearGradient(0, 0, 0, 280);
                            gradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
                            gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');
                            return gradient;
                        },
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 2,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#3b82f6'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            callbacks: {
                                title: (items) => {
                                    return 'Time: ' + items[0].label;
                                },
                                label: (context) => {
                                    const dataPoint = this.chartData[context.dataIndex];
                                    if (!dataPoint) return '';

                                    return [
                                        `Close: ${this.formatRate(parseFloat(dataPoint.close))}`,
                                        `High: ${this.formatRate(parseFloat(dataPoint.high))}`,
                                        `Low: ${this.formatRate(parseFloat(dataPoint.low))}`,
                                        `Open: ${this.formatRate(parseFloat(dataPoint.open))}`
                                    ];
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 10 },
                                maxRotation: 0
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 10 },
                                callback: (value) => {
                                    return (value >= 0 ? '+' : '') + value.toFixed(3) + '%';
                                }
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.1)'
                            }
                        }
                    }
                }
            });

            console.log('✅ History chart initialized');
        },

        async loadData() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    symbol: this.symbol,
                    interval: this.interval,
                    limit: '100'
                });

                const response = await fetch(`http://202.155.90.20:8000/api/funding-rate/history?${params}`);
                const data = await response.json();

                this.chartData = data.data || [];

                if (this.chartData.length > 0) {
                    const last = this.chartData[this.chartData.length - 1];
                    this.lastOHLC = {
                        open: parseFloat(last.open || 0),
                        high: parseFloat(last.high || 0),
                        low: parseFloat(last.low || 0),
                        close: parseFloat(last.close || 0)
                    };
                } else {
                    console.warn('⚠️ No history data available, using fallback');
                    try {
                        // Use fallback mock data for development
                        this.chartData = this.generateMockData();
                        if (this.chartData.length > 0) {
                            const last = this.chartData[this.chartData.length - 1];
                            this.lastOHLC = {
                                open: parseFloat(last.open || 0),
                                high: parseFloat(last.high || 0),
                                low: parseFloat(last.low || 0),
                                close: parseFloat(last.close || 0)
                            };
                        }
                        console.log('✅ Mock history data generated:', this.chartData.length, 'candles');
                    } catch (mockError) {
                        console.error('❌ Error generating mock history data:', mockError);
                        this.chartData = [];
                        this.lastOHLC = { open: 0, high: 0, low: 0, close: 0 };
                    }
                }

                this.updateChart();

                console.log('✅ History data loaded:', this.chartData.length, 'candles');
            } catch (error) {
                console.error('❌ Error loading history data:', error);
            } finally {
                this.loading = false;
            }
        },

        updateChart() {
            if (!this.chart || !this.chartData || this.chartData.length === 0) {
                console.warn('⚠️ Cannot update history chart: missing chart or data');
                return;
            }

            // Prevent multiple simultaneous updates
            if (this.updatePending) {
                console.warn('⚠️ History chart update already pending, skipping...');
                return;
            }

            this.updatePending = true;

            try {
                const labels = this.chartData.map(item => {
                    const date = new Date(item.time);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ' ' +
                           date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
                });

                // Use close values for the line
                const values = this.chartData.map(item => {
                    const close = parseFloat(item.close);
                    return isNaN(close) ? 0 : close * 100;
                });

                // Safely update chart data
                if (this.chart.data && this.chart.data.datasets[0]) {
                    this.chart.data.labels = labels;
                    this.chart.data.datasets[0].data = values;

                    // Use requestAnimationFrame to prevent stack overflow
                    requestAnimationFrame(() => {
                        try {
                            if (this.chart && this.chart.update && typeof this.chart.update === 'function') {
                                this.chart.update('none');
                            }
                        } catch (updateError) {
                            console.error('❌ History chart update error:', updateError);
                        } finally {
                            this.updatePending = false;
                        }
                    });
                } else {
                    this.updatePending = false;
                }
            } catch (error) {
                console.error('❌ Error updating history chart:', error);
                this.updatePending = false;
            }
        },

        refresh() {
            this.loadData();
        },

        generateMockData() {
            // Generate mock history funding data for development
            const mockData = [];
            const now = Date.now();
            const baseRate = 0.000125;
            let currentRate = baseRate;

            for (let i = 100; i >= 0; i--) {
                const intervalMs = this.interval === '1h' ? 60 * 60 * 1000 :
                                 this.interval === '4h' ? 4 * 60 * 60 * 1000 :
                                 this.interval === '8h' ? 8 * 60 * 60 * 1000 :
                                 24 * 60 * 60 * 1000; // 1d

                const time = now - (i * intervalMs);
                const variation = (Math.random() - 0.5) * 0.00005;
                currentRate += variation;

                const open = currentRate;
                const high = currentRate + Math.random() * 0.00002;
                const low = currentRate - Math.random() * 0.00002;
                const close = low + Math.random() * (high - low);

                mockData.push({
                    time: time,
                    open: open,
                    high: high,
                    low: low,
                    close: close,
                    interval: this.interval,
                    symbol: this.symbol
                });

                currentRate = close; // Use close as next open
            }

            return mockData;
        },

        formatRate(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const percent = (value * 100).toFixed(4);
            return (value >= 0 ? '+' : '') + percent + '%';
        }
    };
}
</script>

