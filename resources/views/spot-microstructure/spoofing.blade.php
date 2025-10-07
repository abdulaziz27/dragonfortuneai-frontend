@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100">
        <!-- Header Section -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1>Spoofing Detection</h1>
                    <p>Identifikasi manipulasi orderbook untuk deteksi dini aktivitas tidak wajar</p>
                </div>
                <div class="derivatives-filters">
                    <select class="form-select">
                        <option>All Exchanges</option>
                        <option>Binance</option>
                        <option>Coinbase</option>
                        <option>Kraken</option>
                        <option>KuCoin</option>
                    </select>
                    <select class="form-select">
                        <option>BTCUSDT</option>
                        <option>ETHUSDT</option>
                        <option>SOLUSDT</option>
                        <option>ADAUSDT</option>
                    </select>
                    <select class="form-select">
                        <option>Sensitivity: High</option>
                        <option>Sensitivity: Medium</option>
                        <option>Sensitivity: Low</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row g-3 flex-grow-1">
            <!-- Spoofing Chart -->
            <div class="col-lg-8">
                <div class="derivatives-chart-container">
                    <div class="derivatives-chart-header">
                        <h5 class="derivatives-chart-title">Spoofing Activity Detection</h5>
                        <div class="derivatives-timeframe-buttons">
                            <button class="btn btn-sm btn-outline-primary active">Live</button>
                            <button class="btn btn-sm btn-outline-primary">1m</button>
                            <button class="btn btn-sm btn-outline-primary">5m</button>
                            <button class="btn btn-sm btn-outline-primary">15m</button>
                        </div>
                    </div>
                    <div class="df-chart-container" id="spoofingChart" style="height: 400px;">
                        <!-- Spoofing Chart will be rendered here -->
                    </div>
                </div>
            </div>

            <!-- Spoofing Metrics -->
            <div class="col-lg-4">
                <div class="derivatives-table-container">
                    <h5 class="derivatives-table-title">Spoofing Metrics</h5>
                    <div class="table-responsive">
                        <table class="table derivatives-table">
                            <thead>
                                <tr>
                                    <th>Metric</th>
                                    <th class="text-end">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Spoofing Score</td>
                                    <td class="text-end text-warning">6.8/10</td>
                                </tr>
                                <tr>
                                    <td>Active Spoofs</td>
                                    <td class="text-end">3</td>
                                </tr>
                                <tr>
                                    <td>Cancel Rate</td>
                                    <td class="text-end text-danger">85.2%</td>
                                </tr>
                                <tr>
                                    <td>Avg Spoof Size</td>
                                    <td class="text-end">$45K</td>
                                </tr>
                                <tr>
                                    <td>Detection Confidence</td>
                                    <td class="text-end">
                                        <span class="badge bg-warning">Medium</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Last Detection</td>
                                    <td class="text-end">2 min ago</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Spoofing Events -->
                <div class="derivatives-table-container mt-3">
                    <h5 class="derivatives-table-title">Recent Events</h5>
                    <div class="table-responsive">
                        <table class="table derivatives-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Type</th>
                                    <th class="text-end">Size</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>14:25</td>
                                    <td>
                                        <span class="badge bg-danger">Sell Spoof</span>
                                    </td>
                                    <td class="text-end">$65K</td>
                                </tr>
                                <tr>
                                    <td>14:18</td>
                                    <td>
                                        <span class="badge bg-success">Buy Spoof</span>
                                    </td>
                                    <td class="text-end">$42K</td>
                                </tr>
                                <tr>
                                    <td>14:12</td>
                                    <td>
                                        <span class="badge bg-warning">Suspicious</span>
                                    </td>
                                    <td class="text-end">$28K</td>
                                </tr>
                                <tr>
                                    <td>14:05</td>
                                    <td>
                                        <span class="badge bg-danger">Sell Spoof</span>
                                    </td>
                                    <td class="text-end">$38K</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spoofing Patterns Analysis -->
        <div class="row g-3 mt-3">
            <div class="col-lg-6">
                <div class="derivatives-table-container">
                    <h5 class="derivatives-table-title">Spoofing Patterns</h5>
                    <div class="table-responsive">
                        <table class="table derivatives-table">
                            <thead>
                                <tr>
                                    <th>Pattern</th>
                                    <th class="text-end">Count</th>
                                    <th class="text-end">Success Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Large Order Cancel</td>
                                    <td class="text-end">15</td>
                                    <td class="text-end text-success">92%</td>
                                </tr>
                                <tr>
                                    <td>Rapid Order Changes</td>
                                    <td class="text-end">8</td>
                                    <td class="text-end text-warning">75%</td>
                                </tr>
                                <tr>
                                    <td>Price Manipulation</td>
                                    <td class="text-end">12</td>
                                    <td class="text-end text-danger">83%</td>
                                </tr>
                                <tr>
                                    <td>Volume Spoofing</td>
                                    <td class="text-end">6</td>
                                    <td class="text-end text-success">100%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="derivatives-table-container">
                    <h5 class="derivatives-table-title">Market Impact Analysis</h5>
                    <div class="table-responsive">
                        <table class="table derivatives-table">
                            <thead>
                                <tr>
                                    <th>Impact Type</th>
                                    <th class="text-end">Magnitude</th>
                                    <th class="text-end">Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Price Deviation</td>
                                    <td class="text-end text-danger">-0.8%</td>
                                    <td class="text-end">3 min</td>
                                </tr>
                                <tr>
                                    <td>Volume Spike</td>
                                    <td class="text-end text-warning">+45%</td>
                                    <td class="text-end">2 min</td>
                                </tr>
                                <tr>
                                    <td>Spread Widening</td>
                                    <td class="text-end text-danger">+120%</td>
                                    <td class="text-end">5 min</td>
                                </tr>
                                <tr>
                                    <td>Liquidity Drain</td>
                                    <td class="text-end text-warning">-25%</td>
                                    <td class="text-end">8 min</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="derivatives-stats-grid">
            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Spoofing Score</div>
                        <div class="derivatives-stat-value text-warning">6.8/10</div>
                    </div>
                    <div class="derivatives-stat-icon warning">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Active Spoofs</div>
                        <div class="derivatives-stat-value">3</div>
                    </div>
                    <div class="derivatives-stat-icon danger">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Cancel Rate</div>
                        <div class="derivatives-stat-value text-danger">85.2%</div>
                    </div>
                    <div class="derivatives-stat-icon danger">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6L6 18M6 6l12 12"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Detection Accuracy</div>
                        <div class="derivatives-stat-value text-success">94.5%</div>
                    </div>
                    <div class="derivatives-stat-icon success">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4"/>
                            <path d="M21 12c-1 0-3-1-3-3s2-3 3-3 3 1 3 3-2 3-3 3"/>
                            <path d="M3 12c1 0 3-1 3-3s-2-3-3-3-3 1-3 3 2 3 3 3"/>
                            <path d="M12 3c0 1-1 3-3 3s-3-2-3-3 1-3 3-3 3 2 3 3"/>
                            <path d="M12 21c0-1 1-3 3-3s3 2 3 3-1 3-3 3-3-2-3-3"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js for Spoofing Visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Spoofing Chart Implementation
            const ctx = document.getElementById('spoofingChart').getContext('2d');

            // Sample spoofing data
            const labels = [];
            const priceData = [];
            const spoofingScoreData = [];
            const cancelRateData = [];

            // Generate sample data
            let currentPrice = 47250;
            let currentSpoofingScore = 5;

            for (let i = 0; i < 30; i++) {
                const time = new Date(Date.now() - (30 - i) * 120000); // 2 minute intervals
                labels.push(time.toLocaleTimeString());

                // Simulate price movement
                currentPrice += (Math.random() - 0.5) * 20;
                priceData.push(currentPrice);

                // Simulate spoofing score (0-10)
                const spoofingChange = (Math.random() - 0.5) * 2;
                currentSpoofingScore = Math.max(0, Math.min(10, currentSpoofingScore + spoofingChange));
                spoofingScoreData.push(currentSpoofingScore);

                // Simulate cancel rate (0-100%)
                const cancelRate = Math.random() * 100;
                cancelRateData.push(cancelRate);
            }

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Price',
                            data: priceData,
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            yAxisID: 'y',
                            tension: 0.1
                        },
                        {
                            label: 'Spoofing Score',
                            data: spoofingScoreData,
                            borderColor: 'rgb(245, 158, 11)',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            yAxisID: 'y1',
                            tension: 0.1
                        },
                        {
                            label: 'Cancel Rate (%)',
                            data: cancelRateData,
                            borderColor: 'rgb(239, 68, 68)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            yAxisID: 'y2',
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Time'
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Price (USDT)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Spoofing Score'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                            min: 0,
                            max: 10
                        },
                        y2: {
                            type: 'linear',
                            display: false,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Cancel Rate (%)'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                            min: 0,
                            max: 100
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Spoofing Detection Analysis'
                        }
                    }
                }
            });
        });
    </script>
@endsection
