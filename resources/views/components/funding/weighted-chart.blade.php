{{--
    Komponen: OI-Weighted Funding Chart
    Menampilkan funding rate yang di-weighted berdasarkan Open Interest

    Props:
    - $symbol: string (default: 'BTC')
    - $interval: string (default: '4h')

    Interpretasi:
    - OI-weighted lebih akurat untuk melihat real positioning
    - Exchange dengan OI besar memiliki pengaruh lebih besar
    - Trend naik → Long positioning increasing
    - Trend turun → Short positioning increasing
--}}

<div class="df-panel p-3" style="min-height: 350px;" x-data="weightedFundingChart('{{ $symbol ?? 'BTC' }}', '{{ $interval ?? '4h' }}')">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <h5 class="mb-0">⚖️ OI-Weighted Funding</h5>
            <span class="badge text-bg-info" x-text="'Open Interest Weighted'">Open Interest Weighted</span>
        </div>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm" style="width: auto;" x-model="interval" @change="loadData()">
                <option value="1h">1 Hour</option>
                <option value="4h">4 Hours</option>
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

    <!-- Insight -->
    <div class="small text-secondary mt-2">
        <div class="d-flex align-items-center gap-2">
            <span>💡</span>
            <span>Weighted by open interest to show true market positioning. Higher OI exchanges have more influence.</span>
        </div>
    </div>

    <!-- Current Stats -->
    <div class="row g-2 mt-2">
        <div class="col-4">
            <div class="text-center p-2 bg-light rounded">
                <div class="small text-secondary">Current</div>
                <div class="fw-bold" :class="currentRate >= 0 ? 'text-success' : 'text-danger'" x-text="formatRate(currentRate)">
                    +0.0125%
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="text-center p-2 bg-light rounded">
                <div class="small text-secondary">24h Avg</div>
                <div class="fw-bold" x-text="formatRate(avg24h)">
                    +0.0108%
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="text-center p-2 bg-light rounded">
                <div class="small text-secondary">Trend</div>
                <div class="fw-bold" :class="getTrendClass()" x-text="getTrendText()">
                    ↗️ Rising
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function weightedFundingChart(initialSymbol = 'BTC', initialInterval = '4h') {
    return {
        symbol: initialSymbol,
        interval: initialInterval,
        loading: false,
        chart: null,
        chartId: 'weightedChart_' + Math.random().toString(36).substr(2, 9),
        chartData: [],
        currentRate: 0,
        avg24h: 0,
        trend: 0,
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
            }, 800);

            // Listen for global refresh
            this.$watch('symbol', () => this.loadData());
        },

        initChart() {
            const canvas = document.getElementById(this.chartId);
            if (!canvas) {
                console.warn('⚠️ Canvas not found for weighted chart');
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
                        label: 'OI-Weighted Rate',
                        data: [],
                        borderColor: '#8b5cf6',
                        backgroundColor: (context) => {
                            const ctx = context.chart.ctx;
                            const gradient = ctx.createLinearGradient(0, 0, 0, 280);
                            gradient.addColorStop(0, 'rgba(139, 92, 246, 0.3)');
                            gradient.addColorStop(1, 'rgba(139, 92, 246, 0.0)');
                            return gradient;
                        },
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 6
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
                                    const item = items[0];
                                    return 'Time: ' + item.label;
                                },
                                label: (context) => {
                                    const value = context.parsed.y;
                                    return `Weighted Rate: ${(value >= 0 ? '+' : '')}${value.toFixed(4)}%`;
                                },
                                afterLabel: (context) => {
                                    return 'Based on exchange OI weight';
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

            console.log('✅ Weighted chart initialized');
        },

        async loadData() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    symbol: this.symbol,
                    interval: this.interval,
                    limit: '100'
                });

                const response = await fetch(`http://202.155.90.20:8000/api/funding-rate/weighted?${params}`);
                const data = await response.json();

                this.chartData = data.data || [];

                if (this.chartData.length > 0) {
                    // Calculate stats
                    this.currentRate = parseFloat(this.chartData[this.chartData.length - 1]?.close || 0);

                    // Calculate 24h average
                    const last24Items = this.chartData.slice(-24);
                    if (last24Items.length > 0) {
                        const sum = last24Items.reduce((acc, item) => acc + parseFloat(item.close || 0), 0);
                        this.avg24h = sum / last24Items.length;
                    }

                    // Calculate trend
                    if (this.chartData.length >= 2) {
                        const prevRate = parseFloat(this.chartData[this.chartData.length - 2]?.close || 0);
                        this.trend = this.currentRate - prevRate;
                    }
                } else {
                    console.warn('⚠️ No weighted data available, using fallback');
                    try {
                        // Use fallback mock data for development
                        this.chartData = this.generateMockData();
                        this.currentRate = 0.000125;
                        this.avg24h = 0.000108;
                        this.trend = 0.000017;
                        console.log('✅ Mock weighted data generated:', this.chartData.length, 'points');
                    } catch (mockError) {
                        console.error('❌ Error generating mock weighted data:', mockError);
                        this.chartData = [];
                    }
                }

                this.updateChart();

                console.log('✅ Weighted data loaded:', this.chartData.length, 'points');
            } catch (error) {
                console.error('❌ Error loading weighted data:', error);
            } finally {
                this.loading = false;
            }
        },

        updateChart() {
            if (!this.chart || !this.chartData || this.chartData.length === 0) {
                console.warn('⚠️ Cannot update weighted chart: missing chart or data');
                return;
            }

            // Prevent multiple simultaneous updates
            if (this.updatePending) {
                console.warn('⚠️ Chart update already pending, skipping...');
                return;
            }

            this.updatePending = true;

            try {
                const labels = this.chartData.map(item => {
                    const date = new Date(item.time);
                    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
                });

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
                            console.error('❌ Weighted chart update error:', updateError);
                        } finally {
                            this.updatePending = false;
                        }
                    });
                } else {
                    this.updatePending = false;
                }
            } catch (error) {
                console.error('❌ Error updating weighted chart:', error);
                this.updatePending = false;
            }
        },

        refresh() {
            this.loadData();
        },

        generateMockData() {
            // Generate mock weighted funding data for development
            const mockData = [];
            const now = Date.now();
            const baseRate = 0.000125;

            for (let i = 50; i >= 0; i--) {
                const time = now - (i * 4 * 60 * 60 * 1000); // 4 hour intervals
                const variation = (Math.random() - 0.5) * 0.0001;
                const rate = baseRate + variation;

                mockData.push({
                    time: time,
                    open: rate * 0.98,
                    high: rate * 1.02,
                    low: rate * 0.96,
                    close: rate,
                    interval_name: this.interval,
                    symbol: this.symbol
                });
            }

            return mockData;
        },

        getTrendClass() {
            if (this.trend > 0.0001) return 'text-success';
            if (this.trend < -0.0001) return 'text-danger';
            return 'text-secondary';
        },

        getTrendText() {
            if (this.trend > 0.0001) return '↗️ Rising';
            if (this.trend < -0.0001) return '↘️ Falling';
            return '→ Stable';
        },

        formatRate(value) {
            if (value === null || value === undefined) return 'N/A';
            const percent = (value * 100).toFixed(4);
            return (value >= 0 ? '+' : '') + percent + '%';
        }
    };
}
</script>

