@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100">
        <!-- Header Section -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1>Long/Short Ratio</h1>
                    <p>Compare retail vs pro trader positioning</p>
                </div>
                <div class="derivatives-filters">
                    <select class="form-select">
                        <option>Accounts</option>
                        <option>Positions</option>
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
            <!-- Long/Short Ratio Chart -->
            <div class="col-lg-8">
                <div class="derivatives-chart-container">
                    <div class="derivatives-chart-header">
                        <h5 class="derivatives-chart-title">Long/Short Ratio Trend</h5>
                        <div class="derivatives-timeframe-buttons">
                            <button class="btn btn-outline-secondary">15m</button>
                            <button class="btn btn-outline-secondary">1H</button>
                            <button class="btn btn-primary">4H</button>
                            <button class="btn btn-outline-secondary">1D</button>
                        </div>
                    </div>
                    <div id="longShortRatioChart" style="height: 400px;">
                        <!-- Chart will be rendered here -->
                    </div>
                </div>
            </div>

            <!-- Long/Short Ratio Table -->
            <div class="col-lg-4">
                <div class="derivatives-table-container">
                    <h5 class="derivatives-table-title">Current Ratios</h5>
                    <div class="table-responsive">
                        <table class="table derivatives-table">
                            <thead>
                                <tr>
                                    <th>Exchange</th>
                                    <th>Pair</th>
                                    <th class="text-end">Ratio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Binance</td>
                                    <td class="fw-semibold">BTCUSDT</td>
                                    <td class="text-end text-success">1.45</td>
                                </tr>
                                <tr>
                                    <td>Bybit</td>
                                    <td class="fw-semibold">BTCUSDT</td>
                                    <td class="text-end text-success">1.32</td>
                                </tr>
                                <tr>
                                    <td>OKX</td>
                                    <td class="fw-semibold">BTCUSDT</td>
                                    <td class="text-end text-success">1.28</td>
                                </tr>
                                <tr>
                                    <td>Binance</td>
                                    <td class="fw-semibold">ETHUSDT</td>
                                    <td class="text-end text-danger">0.85</td>
                                </tr>
                                <tr>
                                    <td>Bybit</td>
                                    <td class="fw-semibold">ETHUSDT</td>
                                    <td class="text-end text-danger">0.92</td>
                                </tr>
                                <tr>
                                    <td>OKX</td>
                                    <td class="fw-semibold">ETHUSDT</td>
                                    <td class="text-end text-danger">0.88</td>
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
                        <div class="derivatives-stat-label">Avg Ratio</div>
                        <div class="derivatives-stat-value">1.15</div>
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
                        <div class="derivatives-stat-label">Long Bias</div>
                        <div class="derivatives-stat-value text-success">68.5%</div>
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
                        <div class="derivatives-stat-label">Retail vs Pro</div>
                        <div class="derivatives-stat-value">1.8x</div>
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
                        <div class="derivatives-stat-label">Positioning Risk</div>
                        <div class="derivatives-stat-value text-warning">Medium</div>
                    </div>
                    <div class="derivatives-stat-icon danger">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 8v4"/>
                            <path d="M12 16h.01"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js for Long/Short Ratio Chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Long/Short Ratio Chart
            const ctx = document.getElementById('longShortRatioChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00'],
                    datasets: [{
                        label: 'BTCUSDT L/S Ratio',
                        data: [1.2, 1.35, 1.4, 1.5, 1.45, 1.35, 1.45],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'ETHUSDT L/S Ratio',
                        data: [0.9, 0.85, 0.8, 0.75, 0.85, 0.9, 0.85],
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Neutral Line',
                        data: [1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0],
                        borderColor: 'rgb(156, 163, 175)',
                        backgroundColor: 'rgba(156, 163, 175, 0.1)',
                        borderDash: [5, 5],
                        fill: false
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
                                color: getComputedStyle(document.documentElement).getPropertyValue('--muted-foreground')
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
