@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100">
        <!-- Header Section -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1>Liquidations</h1>
                    <p>Identify cluster stop-hunt & squeeze events</p>
                </div>
                <div class="derivatives-filters">
                    <select class="form-select">
                        <option>Real-time</option>
                        <option>15m Bucket</option>
                        <option>1h Bucket</option>
                    </select>
                    <select class="form-select">
                        <option>All Exchanges</option>
                        <option>Binance</option>
                        <option>Bybit</option>
                        <option>OKX</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row g-3 flex-grow-1">
            <!-- Liquidations Chart -->
            <div class="col-lg-8">
                <div class="derivatives-chart-container">
                    <div class="derivatives-chart-header">
                        <h5 class="derivatives-chart-title">Liquidations Heatmap</h5>
                        <div class="derivatives-timeframe-buttons">
                            <button class="btn btn-outline-secondary">1H</button>
                            <button class="btn btn-outline-secondary">4H</button>
                            <button class="btn btn-primary">1D</button>
                            <button class="btn btn-outline-secondary">7D</button>
                        </div>
                    </div>
                    <div id="liquidationsChart" style="height: 400px;">
                        <!-- Chart will be rendered here -->
                    </div>
                </div>
            </div>

            <!-- Recent Liquidations Table -->
            <div class="col-lg-4">
                <div class="derivatives-table-container">
                    <h5 class="derivatives-table-title">Recent Liquidations</h5>
                    <div class="table-responsive">
                        <table class="table derivatives-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Pair</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>14:32:15</td>
                                    <td class="fw-semibold">BTCUSDT</td>
                                    <td class="text-end text-danger">-$2.5M</td>
                                </tr>
                                <tr>
                                    <td>14:28:42</td>
                                    <td class="fw-semibold">ETHUSDT</td>
                                    <td class="text-end text-danger">-$1.8M</td>
                                </tr>
                                <tr>
                                    <td>14:25:18</td>
                                    <td class="fw-semibold">BTCUSDT</td>
                                    <td class="text-end text-success">+$3.2M</td>
                                </tr>
                                <tr>
                                    <td>14:22:05</td>
                                    <td class="fw-semibold">SOLUSDT</td>
                                    <td class="text-end text-danger">-$890K</td>
                                </tr>
                                <tr>
                                    <td>14:18:33</td>
                                    <td class="fw-semibold">ETHUSDT</td>
                                    <td class="text-end text-success">+$1.5M</td>
                                </tr>
                                <tr>
                                    <td>14:15:27</td>
                                    <td class="fw-semibold">BTCUSDT</td>
                                    <td class="text-end text-danger">-$4.1M</td>
                                </tr>
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
                        <div class="derivatives-stat-label">24h Total</div>
                        <div class="derivatives-stat-value">$45.2M</div>
                    </div>
                    <div class="derivatives-stat-icon danger">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Long Liquidations</div>
                        <div class="derivatives-stat-value text-danger">$28.7M</div>
                    </div>
                    <div class="derivatives-stat-icon danger">
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
                        <div class="derivatives-stat-label">Short Liquidations</div>
                        <div class="derivatives-stat-value text-success">$16.5M</div>
                    </div>
                    <div class="derivatives-stat-icon success">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 7L7 17"/>
                            <path d="M17 17H7V7"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Largest Single</div>
                        <div class="derivatives-stat-value">$12.3M</div>
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
        </div>
    </div>

    <!-- Chart.js for Liquidations Chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Liquidations Chart
            const ctx = document.getElementById('liquidationsChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00'],
                    datasets: [{
                        label: 'Long Liquidations',
                        data: [2.1, 3.2, 4.5, 6.8, 5.2, 3.9, 2.8],
                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        borderColor: 'rgb(239, 68, 68)',
                        borderWidth: 1
                    }, {
                        label: 'Short Liquidations',
                        data: [1.8, 2.5, 3.1, 4.2, 3.8, 2.9, 1.6],
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: getComputedStyle(document.documentElement).getPropertyValue('--foreground')
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: getComputedStyle(document.documentElement).getPropertyValue('--muted-foreground')
                            },
                            grid: {
                                color: getComputedStyle(document.documentElement).getPropertyValue('--border')
                            }
                        },
                        y: {
                            ticks: {
                                color: getComputedStyle(document.documentElement).getPropertyValue('--muted-foreground'),
                                callback: function(value) {
                                    return '$' + value + 'M';
                                }
                            },
                            grid: {
                                color: getComputedStyle(document.documentElement).getPropertyValue('--border')
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
