@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100">
        <!-- Header Section -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1>Volume + Change</h1>
                    <p>Confirm price movement strength with volume analysis</p>
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
            <!-- Volume Chart -->
            <div class="col-lg-8">
                <div class="derivatives-chart-container">
                    <div class="derivatives-chart-header">
                        <h5 class="derivatives-chart-title">Volume vs Price Change</h5>
                        <div class="derivatives-timeframe-buttons">
                            <button class="btn btn-outline-secondary">15m</button>
                            <button class="btn btn-outline-secondary">1H</button>
                            <button class="btn btn-primary">4H</button>
                            <button class="btn btn-outline-secondary">1D</button>
                        </div>
                    </div>
                    <div id="volumeChangeChart" style="height: 400px;">
                        <!-- Chart will be rendered here -->
                    </div>
                </div>
            </div>

            <!-- Volume Analysis Table -->
            <div class="col-lg-4">
                <div class="derivatives-table-container">
                    <h5 class="derivatives-table-title">Volume Analysis</h5>
                    <div class="table-responsive">
                        <table class="table derivatives-table">
                            <thead>
                                <tr>
                                    <th>Pair</th>
                                    <th class="text-end">Volume</th>
                                    <th class="text-end">Change</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-semibold">BTCUSDT</td>
                                    <td class="text-end">$2.8B</td>
                                    <td class="text-end text-success">+12.5%</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">ETHUSDT</td>
                                    <td class="text-end">$1.9B</td>
                                    <td class="text-end text-danger">-8.3%</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">SOLUSDT</td>
                                    <td class="text-end">$890M</td>
                                    <td class="text-end text-success">+25.7%</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">ADAUSDT</td>
                                    <td class="text-end">$456M</td>
                                    <td class="text-end text-success">+5.2%</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">DOTUSDT</td>
                                    <td class="text-end">$234M</td>
                                    <td class="text-end text-danger">-15.8%</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">LINKUSDT</td>
                                    <td class="text-end">$567M</td>
                                    <td class="text-end text-success">+18.9%</td>
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
                        <div class="derivatives-stat-label">Total Volume</div>
                        <div class="derivatives-stat-value">$6.8B</div>
                    </div>
                    <div class="derivatives-stat-icon primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Volume Change</div>
                        <div class="derivatives-stat-value text-success">+8.7%</div>
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
                        <div class="derivatives-stat-label">Volume Spike</div>
                        <div class="derivatives-stat-value text-warning">SOL</div>
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
                        <div class="derivatives-stat-label">Price Correlation</div>
                        <div class="derivatives-stat-value">0.73</div>
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

    <!-- Chart.js for Volume Change Chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Volume Change Chart
            const ctx = document.getElementById('volumeChangeChart').getContext('2d');
            new Chart(ctx, {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: 'BTCUSDT',
                        data: [
                            {x: 2.8, y: 12.5},
                            {x: 2.1, y: 8.2},
                            {x: 3.2, y: 15.8},
                            {x: 2.5, y: 6.4},
                            {x: 3.1, y: 18.9},
                            {x: 2.9, y: 11.2},
                            {x: 2.8, y: 12.5}
                        ],
                        backgroundColor: 'rgba(59, 130, 246, 0.6)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 2
                    }, {
                        label: 'ETHUSDT',
                        data: [
                            {x: 1.9, y: -8.3},
                            {x: 1.6, y: -5.2},
                            {x: 2.1, y: -12.1},
                            {x: 1.8, y: -7.8},
                            {x: 2.0, y: -9.5},
                            {x: 1.7, y: -6.9},
                            {x: 1.9, y: -8.3}
                        ],
                        backgroundColor: 'rgba(239, 68, 68, 0.6)',
                        borderColor: 'rgb(239, 68, 68)',
                        borderWidth: 2
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
                            title: {
                                display: true,
                                text: 'Volume (Billions USD)',
                                color: getComputedStyle(document.documentElement).getPropertyValue('--foreground')
                            },
                            ticks: {
                                color: getComputedStyle(document.documentElement).getPropertyValue('--muted-foreground'),
                                callback: function(value) {
                                    return '$' + value + 'B';
                                }
                            },
                            grid: {
                                color: getComputedStyle(document.documentElement).getPropertyValue('--border')
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Price Change (%)',
                                color: getComputedStyle(document.documentElement).getPropertyValue('--foreground')
                            },
                            ticks: {
                                color: getComputedStyle(document.documentElement).getPropertyValue('--muted-foreground'),
                                callback: function(value) {
                                    return value + '%';
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
