@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100">
        <!-- Header Section -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1>Open Interest</h1>
                    <p>Measure contract participation; OI spikes = liquidation risk</p>
                </div>
                <div class="derivatives-filters">
                    <select class="form-select">
                        <option>All Exchanges</option>
                        <option>Binance</option>
                        <option>Bybit</option>
                        <option>OKX</option>
                        <option>Deribit</option>
                    </select>
                    <select class="form-select">
                        <option>USD Value</option>
                        <option>Contract Count</option>
                        <option>Coin Amount</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row g-3 flex-grow-1">
            <!-- Open Interest Chart -->
            <div class="col-lg-8">
                <div class="derivatives-chart-container">
                    <div class="derivatives-chart-header">
                        <h5 class="derivatives-chart-title">Open Interest Trend</h5>
                        <div class="derivatives-timeframe-buttons">
                            <button class="btn btn-outline-secondary">1H</button>
                            <button class="btn btn-outline-secondary">4H</button>
                            <button class="btn btn-primary">1D</button>
                            <button class="btn btn-outline-secondary">7D</button>
                        </div>
                    </div>
                    <div id="openInterestChart" style="height: 400px;">
                        <!-- Chart will be rendered here -->
                    </div>
                </div>
            </div>

            <!-- Open Interest Table -->
            <div class="col-lg-4">
                <div class="derivatives-table-container">
                    <h5 class="derivatives-table-title">Current Open Interest</h5>
                    <div class="table-responsive">
                        <table class="table derivatives-table">
                            <thead>
                                <tr>
                                    <th>Exchange</th>
                                    <th>Pair</th>
                                    <th class="text-end">OI (USD)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Binance</td>
                                    <td class="fw-semibold">BTCUSDT</td>
                                    <td class="text-end">$2.45B</td>
                                </tr>
                                <tr>
                                    <td>Bybit</td>
                                    <td class="fw-semibold">BTCUSDT</td>
                                    <td class="text-end">$1.89B</td>
                                </tr>
                                <tr>
                                    <td>OKX</td>
                                    <td class="fw-semibold">BTCUSDT</td>
                                    <td class="text-end">$1.23B</td>
                                </tr>
                                <tr>
                                    <td>Binance</td>
                                    <td class="fw-semibold">ETHUSDT</td>
                                    <td class="text-end">$1.67B</td>
                                </tr>
                                <tr>
                                    <td>Bybit</td>
                                    <td class="fw-semibold">ETHUSDT</td>
                                    <td class="text-end">$1.34B</td>
                                </tr>
                                <tr>
                                    <td>OKX</td>
                                    <td class="fw-semibold">ETHUSDT</td>
                                    <td class="text-end">$987M</td>
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
                        <div class="derivatives-stat-label">Total OI</div>
                        <div class="derivatives-stat-value">$8.56B</div>
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
                        <div class="derivatives-stat-label">24h Change</div>
                        <div class="derivatives-stat-value text-success">+5.2%</div>
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
                        <div class="derivatives-stat-label">Dominant Exchange</div>
                        <div class="derivatives-stat-value">Binance</div>
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
                        <div class="derivatives-stat-label">Market Share</div>
                        <div class="derivatives-stat-value">28.6%</div>
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

    <!-- Chart.js for Open Interest Chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Open Interest Chart
            const ctx = document.getElementById('openInterestChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00'],
                    datasets: [{
                        label: 'BTCUSDT OI',
                        data: [2.1, 2.3, 2.4, 2.6, 2.5, 2.4, 2.45],
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1
                    }, {
                        label: 'ETHUSDT OI',
                        data: [1.2, 1.4, 1.5, 1.7, 1.6, 1.5, 1.67],
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
                                    return '$' + value + 'B';
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
