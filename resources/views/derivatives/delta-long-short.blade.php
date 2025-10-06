@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100">
        <!-- Header Section -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1>Delta Long vs Short</h1>
                    <p>See who dominates on short timeframes</p>
                </div>
                <div class="derivatives-filters">
                    <select class="form-select">
                        <option>1H</option>
                        <option>4H</option>
                        <option>1D</option>
                        <option>7D</option>
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
            <!-- Delta Chart -->
            <div class="col-lg-8">
                <div class="derivatives-chart-container">
                    <div class="derivatives-chart-header">
                        <h5 class="derivatives-chart-title">Delta Long vs Short</h5>
                        <div class="derivatives-timeframe-buttons">
                            <button class="btn btn-outline-secondary">15m</button>
                            <button class="btn btn-outline-secondary">1H</button>
                            <button class="btn btn-primary">4H</button>
                            <button class="btn btn-outline-secondary">1D</button>
                        </div>
                    </div>
                    <div id="deltaLongShortChart" style="height: 400px;">
                        <!-- Chart will be rendered here -->
                    </div>
                </div>
            </div>

            <!-- Delta Analysis Table -->
            <div class="col-lg-4">
                <div class="derivatives-table-container">
                    <h5 class="derivatives-table-title">Delta Analysis</h5>
                    <div class="table-responsive">
                        <table class="table derivatives-table">
                            <thead>
                                <tr>
                                    <th>Pair</th>
                                    <th class="text-end">Long Delta</th>
                                    <th class="text-end">Short Delta</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-semibold">BTCUSDT</td>
                                    <td class="text-end text-success">+$45.2M</td>
                                    <td class="text-end text-danger">-$32.8M</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">ETHUSDT</td>
                                    <td class="text-end text-danger">-$28.5M</td>
                                    <td class="text-end text-success">+$41.7M</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">SOLUSDT</td>
                                    <td class="text-end text-success">+$18.9M</td>
                                    <td class="text-end text-danger">-$12.3M</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">ADAUSDT</td>
                                    <td class="text-end text-success">+$8.7M</td>
                                    <td class="text-end text-danger">-$6.2M</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">DOTUSDT</td>
                                    <td class="text-end text-danger">-$15.8M</td>
                                    <td class="text-end text-success">+$22.1M</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">LINKUSDT</td>
                                    <td class="text-end text-success">+$12.4M</td>
                                    <td class="text-end text-danger">-$9.8M</td>
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
                        <div class="derivatives-stat-label">Net Delta</div>
                        <div class="derivatives-stat-value text-success">+$28.7M</div>
                    </div>
                    <div class="derivatives-stat-icon success">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Long Dominance</div>
                        <div class="derivatives-stat-value">58.3%</div>
                    </div>
                    <div class="derivatives-stat-icon primary">
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
                        <div class="derivatives-stat-label">Delta Ratio</div>
                        <div class="derivatives-stat-value">1.38</div>
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
                        <div class="derivatives-stat-label">Market Sentiment</div>
                        <div class="derivatives-stat-value text-success">Bullish</div>
                    </div>
                    <div class="derivatives-stat-icon info">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M8 12h8"/>
                            <path d="M12 8v8"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js for Delta Long vs Short Chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delta Long vs Short Chart
            const ctx = document.getElementById('deltaLongShortChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00'],
                    datasets: [{
                        label: 'Long Delta',
                        data: [35.2, 42.8, 38.5, 45.2, 41.7, 39.8, 45.2],
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Short Delta',
                        data: [28.5, 31.2, 29.8, 32.8, 30.5, 28.9, 32.8],
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Net Delta',
                        data: [6.7, 11.6, 8.7, 12.4, 11.2, 10.9, 12.4],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderDash: [5, 5]
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
