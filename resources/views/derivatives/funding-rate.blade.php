@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100 gap-3" x-data="fundingRateData()">
        <!-- Header Section with Live Metrics -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">Funding Rate Analytics</h1>
                        <span class="badge-df-primary pulse-dot pulse-success"></span>
                    </div>
                    <p class="mb-0">Monitor funding rates across exchanges to detect leverage bias & potential short/long squeeze</p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <!-- Quick Stats -->
                    <div class="df-panel p-2 px-3">
                        <div class="small text-secondary">Market Bias</div>
                        <div class="fw-bold" :class="avgFunding >= 0 ? 'text-success' : 'text-danger'" x-text="(avgFunding >= 0 ? 'LONG' : 'SHORT') + ' (' + (avgFunding * 100).toFixed(3) + '%)'">
                            LONG (+0.085%)
                        </div>
                    </div>
                    <div class="df-panel p-2 px-3">
                        <div class="small text-secondary">Next Funding</div>
                        <div class="fw-bold" x-text="nextFundingCountdown">2h 15m</div>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <select class="form-select" style="max-width: 180px;" x-model="selectedPair">
                    <option value="BTCUSDT">BTCUSDT</option>
                    <option value="ETHUSDT">ETHUSDT</option>
                    <option value="SOLUSDT">SOLUSDT</option>
                    <option value="BNBUSDT">BNBUSDT</option>
                </select>
                <select class="form-select" style="max-width: 180px;" x-model="selectedTimeframe">
                    <option value="1h">1 Hour</option>
                    <option value="4h">4 Hours</option>
                    <option value="8h">8 Hours (Current)</option>
                    <option value="1d">24 Hours</option>
                </select>
            </div>
        </div>

        <!-- Cross-Exchange Heatmap -->
        <div class="df-panel p-3" style="min-height: 180px;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">🔥 Cross-Exchange Funding Heatmap</h5>
                <span class="small text-secondary">Real-time comparison • Updated <span x-text="lastUpdate">5s ago</span></span>
            </div>
            <div class="funding-heatmap-grid">
                <template x-for="exchange in exchanges" :key="exchange.name">
                    <div class="heatmap-cell"
                         :class="getHeatmapClass(exchange.funding)"
                         :title="exchange.name + ': ' + (exchange.funding * 100).toFixed(4) + '%'">
                        <div class="heatmap-exchange" x-text="exchange.name">Binance</div>
                        <div class="heatmap-value" x-text="(exchange.funding * 100).toFixed(4) + '%'">+0.0125%</div>
                        <div class="heatmap-trend">
                            <svg width="16" height="16" :class="exchange.trend >= 0 ? '' : 'rotate-180'">
                                <path d="M8 3 L12 9 L4 9 Z" :fill="exchange.trend >= 0 ? '#22c55e' : '#ef4444'" />
                            </svg>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row g-3">
            <!-- Funding Rate Chart + Whale Alerts -->
            <div class="col-lg-8">
                <div class="derivatives-chart-container mb-3" style="min-height: 380px;">
                    <div class="derivatives-chart-header">
                        <h5 class="derivatives-chart-title">📈 Historical Funding Rate</h5>
                        <div class="d-flex gap-2 align-items-center">
                            <div class="small text-secondary me-2">Current: <span :class="currentFunding >= 0 ? 'text-success' : 'text-danger'" x-text="(currentFunding * 100).toFixed(4) + '%'">+0.0125%</span></div>
                            <button class="btn btn-sm btn-outline-secondary" @click="toggleMultiExchange">
                                <template x-if="showMultiExchange">All Exchanges</template>
                                <template x-if="!showMultiExchange">Single</template>
                            </button>
                        </div>
                    </div>
                    <div id="fundingRateChart" style="height: 100%; min-height: 340px;">
                        <!-- Chart will be rendered here -->
                    </div>
                </div>
            </div>

            <!-- Enhanced Funding Table + Divergence Alerts -->
            <div class="col-lg-4">
                <div class="derivatives-table-container mb-3" style="min-height: 340px;">
                    <h5 class="derivatives-table-title">💹 Live Funding Rates</h5>
                    <div class="table-responsive">
                        <table class="table derivatives-table">
                            <thead>
                                <tr>
                                    <th>Exchange</th>
                                    <th class="text-end">Rate</th>
                                    <th class="text-end">8h APR</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="exchange in exchanges" :key="exchange.name">
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span x-text="exchange.name">Binance</span>
                                                <template x-if="exchange.funding === Math.max(...exchanges.map(e => e.funding))">
                                                    <span class="badge-df-success" style="font-size: 0.65rem;">HIGH</span>
                                                </template>
                                            </div>
                                        </td>
                                        <td class="text-end" :class="exchange.funding >= 0 ? 'text-success' : 'text-danger'">
                                            <span x-text="(exchange.funding * 100).toFixed(4) + '%'">+0.0125%</span>
                                        </td>
                                        <td class="text-end">
                                            <span x-text="(exchange.funding * 3 * 365).toFixed(2) + '%'">13.69%</span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="derivatives-stats-grid">
            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Avg Funding Rate</div>
                        <div class="derivatives-stat-value">+0.0085%</div>
                    </div>
                    <div class="derivatives-stat-icon success">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M7 17L17 7"/>
                            <path d="M7 7h10v10"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Positive Rate</div>
                        <div class="derivatives-stat-value">65.2%</div>
                    </div>
                    <div class="derivatives-stat-icon info">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Max Rate</div>
                        <div class="derivatives-stat-value">+0.0250%</div>
                    </div>
                    <div class="derivatives-stat-icon warning">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 9v4"/>
                            <path d="M12 17h.01"/>
                            <circle cx="12" cy="12" r="10"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Next Funding</div>
                        <div class="derivatives-stat-value">2h 15m</div>
                    </div>
                    <div class="derivatives-stat-icon primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12,6 12,12 16,14"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js for Funding Rate Chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function fundingRateData() {
            const state = {
                selectedPair: 'BTCUSDT',
                selectedTimeframe: '8h',
                showMultiExchange: false,
                lastUpdate: '5s ago',
                avgFunding: 0.00085,
                currentFunding: 0.00125,
                nextFundingCountdown: '2h 15m',

                // Cross-exchange data
                exchanges: [
                    { name: 'Binance', funding: 0.00125, trend: 1 },
                    { name: 'Bybit', funding: 0.00108, trend: 1 },
                    { name: 'OKX', funding: 0.00095, trend: -1 },
                    { name: 'Deribit', funding: 0.00142, trend: 1 },
                    { name: 'Kraken', funding: 0.00089, trend: -1 },
                    { name: 'Huobi', funding: 0.00118, trend: 1 }
                ],

                // Whale alerts with severity scoring
                whaleAlerts: [
                    {
                        id: 1,
                        exchange: 'Binance',
                        type: 'long',
                        size: 12.5e6,
                        leverage: 10,
                        entry: 65420,
                        timeAgo: '2m ago',
                        severity: 'high'
                    },
                    {
                        id: 2,
                        exchange: 'Bybit',
                        type: 'short',
                        size: 8.2e6,
                        leverage: 15,
                        entry: 65850,
                        timeAgo: '5m ago',
                        severity: 'critical'
                    },
                    {
                        id: 3,
                        exchange: 'OKX',
                        type: 'long',
                        size: 15.8e6,
                        leverage: 5,
                        entry: 64950,
                        timeAgo: '8m ago',
                        severity: 'high'
                    },
                    {
                        id: 4,
                        exchange: 'Binance',
                        type: 'short',
                        size: 6.3e6,
                        leverage: 20,
                        entry: 66100,
                        timeAgo: '12m ago',
                        severity: 'critical'
                    },
                    {
                        id: 5,
                        exchange: 'Deribit',
                        type: 'long',
                        size: 4.7e6,
                        leverage: 3,
                        entry: 65200,
                        timeAgo: '15m ago',
                        severity: 'medium'
                    }
                ],

                // Divergence alerts (OI vs Price, Funding vs Price)
                divergenceAlerts: [
                    {
                        id: 1,
                        severity: 'danger',
                        title: 'Bearish Divergence Detected',
                        description: 'Funding rate rising (+15% 4h) while BTC price declining (-2.3%)',
                        timeAgo: '5m ago'
                    },
                    {
                        id: 2,
                        severity: 'warning',
                        title: 'Exchange Spread Widening',
                        description: 'Binance funding 48% higher than OKX - potential arbitrage',
                        timeAgo: '12m ago'
                    },
                    {
                        id: 3,
                        severity: 'info',
                        title: 'Funding Rate Normalization',
                        description: 'Funding returning to neutral zone after spike',
                        timeAgo: '18m ago'
                    }
                ],

                chartInstance: null
            };

            // Heatmap color classification
            state.getHeatmapClass = (funding) => {
                const absValue = Math.abs(funding * 100);
                if (funding > 0.0015) return 'heatmap-extreme-positive';
                if (funding > 0.0010) return 'heatmap-high-positive';
                if (funding > 0.0005) return 'heatmap-medium-positive';
                if (funding > 0) return 'heatmap-low-positive';
                if (funding > -0.0005) return 'heatmap-low-negative';
                if (funding > -0.0010) return 'heatmap-medium-negative';
                if (funding > -0.0015) return 'heatmap-high-negative';
                return 'heatmap-extreme-negative';
            };

            // Toggle multi-exchange view
            state.toggleMultiExchange = () => {
                state.showMultiExchange = !state.showMultiExchange;
                state.updateChart();
            };

            // Update chart data
            state.updateChart = () => {
                if (!state.chartInstance) return;

                // Regenerate data based on current settings
                const labels = Array.from({length: 24}, (_, i) => {
                    const hour = i;
                    return hour.toString().padStart(2, '0') + ':00';
                });

                const datasets = state.showMultiExchange ? [
                    {
                        label: 'Binance',
                        data: Array.from({length: 24}, (_, i) => 0.0008 + Math.sin(i/4) * 0.0005),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: false,
                        borderWidth: 2
                    },
                    {
                        label: 'Bybit',
                        data: Array.from({length: 24}, (_, i) => 0.0007 + Math.sin((i+2)/4) * 0.0004),
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4,
                        fill: false,
                        borderWidth: 2
                    },
                    {
                        label: 'OKX',
                        data: Array.from({length: 24}, (_, i) => 0.0006 + Math.sin((i+4)/4) * 0.0003),
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.4,
                        fill: false,
                        borderWidth: 2
                    }
                ] : [
                    {
                        label: state.selectedPair + ' Funding Rate',
                        data: Array.from({length: 24}, (_, i) => 0.0008 + Math.sin(i/4) * 0.0005),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3
                    }
                ];

                state.chartInstance.data.labels = labels;
                state.chartInstance.data.datasets = datasets;
                state.chartInstance.update();
            };

            // Simulate real-time updates
            setInterval(() => {
                // Update funding rates slightly
                state.exchanges = state.exchanges.map(ex => ({
                    ...ex,
                    funding: ex.funding + (Math.random() - 0.5) * 0.00005,
                    trend: Math.random() > 0.5 ? 1 : -1
                }));

                // Calculate average
                state.avgFunding = state.exchanges.reduce((sum, ex) => sum + ex.funding, 0) / state.exchanges.length;
                state.currentFunding = state.exchanges[0].funding;

                // Add random whale alert
                if (Math.random() > 0.95) {
                    const newAlert = {
                        id: Date.now(),
                        exchange: ['Binance', 'Bybit', 'OKX'][Math.floor(Math.random() * 3)],
                        type: Math.random() > 0.5 ? 'long' : 'short',
                        size: (Math.random() * 15 + 3) * 1e6,
                        leverage: Math.floor(Math.random() * 18 + 3),
                        entry: 65000 + Math.random() * 2000,
                        timeAgo: 'Just now',
                        severity: ['medium', 'high', 'critical'][Math.floor(Math.random() * 3)]
                    };
                    state.whaleAlerts.unshift(newAlert);
                    if (state.whaleAlerts.length > 20) state.whaleAlerts.pop();
                }

                // Update timestamps
                state.whaleAlerts = state.whaleAlerts.map((alert, i) => ({
                    ...alert,
                    timeAgo: i === 0 && alert.timeAgo === 'Just now' ? 'Just now' :
                             (parseInt(alert.timeAgo) + 1 || 1) + 'm ago'
                }));

            }, 6000);

            // Countdown timer for next funding
            let fundingSeconds = 2 * 3600 + 15 * 60;
            setInterval(() => {
                fundingSeconds--;
                if (fundingSeconds < 0) fundingSeconds = 8 * 3600;
                const hours = Math.floor(fundingSeconds / 3600);
                const mins = Math.floor((fundingSeconds % 3600) / 60);
                state.nextFundingCountdown = `${hours}h ${mins}m`;
            }, 1000);

            return state;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Chart
            const ctx = document.getElementById('fundingRateChart').getContext('2d');

            const labels = Array.from({length: 24}, (_, i) => {
                const hour = i;
                return hour.toString().padStart(2, '0') + ':00';
            });

            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'BTCUSDT Funding Rate',
                        data: Array.from({length: 24}, (_, i) => 0.0008 + Math.sin(i/4) * 0.0005),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
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
                            display: true,
                            labels: {
                                color: getComputedStyle(document.documentElement).getPropertyValue('--foreground'),
                                padding: 15,
                                font: { size: 12, weight: '600' }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: { size: 13, weight: 'bold' },
                            bodyFont: { size: 12 },
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + (context.parsed.y * 100).toFixed(4) + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: getComputedStyle(document.documentElement).getPropertyValue('--muted-foreground'),
                                font: { size: 11 }
                            },
                            grid: {
                                color: getComputedStyle(document.documentElement).getPropertyValue('--border'),
                                drawBorder: false
                            }
                        },
                        y: {
                            ticks: {
                                color: getComputedStyle(document.documentElement).getPropertyValue('--muted-foreground'),
                                font: { size: 11 },
                                callback: function(value) {
                                    return (value * 100).toFixed(3) + '%';
                                }
                            },
                            grid: {
                                color: getComputedStyle(document.documentElement).getPropertyValue('--border'),
                                drawBorder: false
                            }
                        }
                    }
                }
            });

            // Store chart instance in Alpine state
            if (window.Alpine) {
                window.Alpine.store('fundingChart', chart);
            }
        });
    </script>
@endsection
