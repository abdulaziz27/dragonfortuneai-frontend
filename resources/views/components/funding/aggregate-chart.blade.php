{{--
    Komponen: Funding Rate Aggregate Chart
    Menampilkan perbandingan funding rate per exchange dalam bar chart

    Props:
    - $symbol: string (default: 'BTC')
    - $rangeStr: string (default: '7d')

    Interpretasi:
    - Bar hijau tinggi → Exchange dengan funding rate positif tinggi → Longs crowded di exchange ini
    - Bar merah dalam → Shorts crowded di exchange ini
    - Perbandingan antar exchange → Arbitrage opportunities
--}}

<div class="df-panel p-3" x-data="aggregateFundingChart('{{ $symbol ?? 'BTC' }}', '{{ $rangeStr ?? '7d' }}')">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <h5 class="mb-0">📊 Funding Rate by Exchange</h5>
            <span class="badge text-bg-secondary" x-text="'(' + rangeStr + ' accumulated)'">( 7d accumulated)</span>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <select class="form-select form-select-sm" style="width: auto;" x-model="rangeStr" @change="loadData()">
                <option value="1d">1 Day</option>
                <option value="7d">7 Days</option>
                <option value="30d">30 Days</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary" @click="refresh()" :disabled="loading">
                <span x-show="!loading">🔄</span>
                <span x-show="loading" class="spinner-border spinner-border-sm"></span>
            </button>
        </div>
    </div>

    <!-- Chart Canvas -->
    <div style="position: relative; height: 380px;">
        <canvas id="aggregateChart"></canvas>
    </div>

    <!-- Exchange Spread Alert -->
    <template x-if="spreadAlert">
        <div class="alert alert-warning mt-3 mb-0" role="alert">
            <div class="d-flex align-items-start gap-2">
                <div>⚡</div>
                <div class="flex-grow-1">
                    <div class="fw-semibold small">Large Exchange Spread Detected</div>
                    <div class="small" x-text="spreadAlert"></div>
                </div>
            </div>
        </div>
    </template>

    <!-- Insight Panel -->
    <div class="row g-2 mt-3">
        <div class="col-md-4">
            <div class="text-center p-2 bg-light rounded">
                <div class="small text-secondary">Highest Exchange</div>
                <div class="fw-bold text-success" x-text="highestExchange">--</div>
                <div class="small" x-text="formatRate(highestRate)">--</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center p-2 bg-light rounded">
                <div class="small text-secondary">Lowest Exchange</div>
                <div class="fw-bold text-danger" x-text="lowestExchange">--</div>
                <div class="small" x-text="formatRate(lowestRate)">--</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center p-2 bg-light rounded">
                <div class="small text-secondary">Spread</div>
                <div class="fw-bold text-warning" x-text="formatRate(spreadRate)">--</div>
                <div class="small" x-text="spreadPercentage + '% difference'">--</div>
            </div>
        </div>
    </div>
</div>

<script>
function aggregateFundingChart(initialSymbol = 'BTC', initialRangeStr = '7d') {
    return {
        symbol: initialSymbol,
        rangeStr: initialRangeStr,
        loading: false,
        aggregateData: [],
        chart: null,
        highestExchange: '--',
        highestRate: 0,
        lowestExchange: '--',
        lowestRate: 0,

        get spreadRate() {
            return this.highestRate - this.lowestRate;
        },

        get spreadPercentage() {
            if (this.lowestRate === 0) return 0;
            return Math.abs((this.spreadRate / this.lowestRate) * 100).toFixed(1);
        },

        get spreadAlert() {
            const spreadPercent = parseFloat(this.spreadPercentage);
            if (spreadPercent > 50) {
                return `Extreme spread of ${this.spreadPercentage}% between ${this.highestExchange} (${this.formatRate(this.highestRate)}) and ${this.lowestExchange} (${this.formatRate(this.lowestRate)}). Potential arbitrage opportunity or exchange-specific risk.`;
            }
            return null;
        },

        async init() {
            // Wait for Chart.js to be loaded
            if (typeof Chart === 'undefined') {
                console.log('⏳ Waiting for Chart.js to load...');
                await window.chartJsReady;
            }

            setTimeout(() => {
                this.initChart();
                this.loadData();
            }, 500);
        },

        initChart() {
            const canvas = document.getElementById('aggregateChart');
            if (!canvas) {
                console.warn('⚠️ Canvas not found for aggregate chart');
                return;
            }

            if (typeof Chart === 'undefined') {
                console.error('❌ Chart.js not loaded');
                return;
            }

            const ctx = canvas.getContext('2d');

            this.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Funding Rate (%)',
                        data: [],
                        backgroundColor: [],
                        borderColor: [],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            callbacks: {
                                label: (context) => {
                                    const value = context.parsed.y;
                                    return `Funding Rate: ${(value >= 0 ? '+' : '')}${value.toFixed(4)}%`;
                                },
                                afterLabel: (context) => {
                                    const item = this.aggregateData[context.dataIndex];
                                    if (!item) return '';
                                    return [
                                        `Margin: ${item.margin_type}`,
                                        `Period: ${item.range_str}`
                                    ];
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 11 }
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 11 },
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

            console.log('✅ Aggregate chart initialized');
        },

        async loadData() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    limit: '2000',
                    ...(this.symbol && { symbol: this.symbol }),
                    ...(this.rangeStr && { range_str: this.rangeStr })
                });

                const response = await fetch(`http://202.155.90.20:8000/api/funding-rate/aggregate?${params}`);
                const data = await response.json();

                this.aggregateData = (data.data || []).filter(item => item.funding_rate !== null);

                // Group by exchange and get the latest
                const exchangeMap = {};
                this.aggregateData.forEach(item => {
                    if (!exchangeMap[item.exchange] || item.time_ms > exchangeMap[item.exchange].time_ms) {
                        exchangeMap[item.exchange] = item;
                    }
                });

                const latestData = Object.values(exchangeMap)
                    .sort((a, b) => parseFloat(b.funding_rate) - parseFloat(a.funding_rate));

                // Calculate highest and lowest
                if (latestData.length > 0) {
                    this.highestExchange = latestData[0].exchange;
                    this.highestRate = parseFloat(latestData[0].funding_rate);
                    this.lowestExchange = latestData[latestData.length - 1].exchange;
                    this.lowestRate = parseFloat(latestData[latestData.length - 1].funding_rate);
                }

                this.updateChart(latestData);

                console.log('✅ Aggregate data loaded:', latestData.length, 'exchanges');
            } catch (error) {
                console.error('❌ Error loading aggregate data:', error);
            } finally {
                this.loading = false;
            }
        },

        updateChart(latestData) {
            if (!this.chart || !latestData || latestData.length === 0) {
                console.warn('⚠️ Cannot update chart: missing chart or data');
                return;
            }

            try {
                const labels = latestData.map(item => item.exchange || 'Unknown');
                const values = latestData.map(item => {
                    const rate = parseFloat(item.funding_rate);
                    return isNaN(rate) ? 0 : rate * 100;
                });

                // Color based on value (green for positive, red for negative)
                const backgroundColors = values.map(value => {
                    if (value > 0.1) return 'rgba(34, 197, 94, 0.8)';
                    if (value > 0) return 'rgba(134, 239, 172, 0.8)';
                    if (value < -0.1) return 'rgba(239, 68, 68, 0.8)';
                    return 'rgba(252, 165, 165, 0.8)';
                });

                const borderColors = values.map(value => {
                    if (value > 0) return '#16a34a';
                    return '#dc2626';
                });

                // Safely update chart data
                if (this.chart.data) {
                    this.chart.data.labels = labels;
                    if (this.chart.data.datasets[0]) {
                        this.chart.data.datasets[0].data = values;
                        this.chart.data.datasets[0].backgroundColor = backgroundColors;
                        this.chart.data.datasets[0].borderColor = borderColors;
                    }

                    // Use requestAnimationFrame to prevent stack overflow
                    requestAnimationFrame(() => {
                        try {
                            if (this.chart && this.chart.update && typeof this.chart.update === 'function') {
                                this.chart.update('none');
                            }
                        } catch (updateError) {
                            console.error('❌ Chart update error:', updateError);
                        }
                    });
                }
            } catch (error) {
                console.error('❌ Error updating aggregate chart:', error);
            }
        },

        refresh() {
            this.loadData();
        },

        formatRate(value) {
            if (value === null || value === undefined) return 'N/A';
            const percent = (value * 100).toFixed(4);
            return (value >= 0 ? '+' : '') + percent + '%';
        }
    };
}
</script>

