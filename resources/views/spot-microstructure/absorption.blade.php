@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100">
        <!-- Header Section -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1>Absorption Analysis</h1>
                    <p>Deteksi indikasi big player menahan arah harga untuk identifikasi reversal patterns</p>
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
                        <option>Threshold: 1%</option>
                        <option>Threshold: 2%</option>
                        <option>Threshold: 5%</option>
                        <option>Threshold: 10%</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row g-3 flex-grow-1">
            <!-- Absorption Chart -->
            <div class="col-lg-8">
                <div class="derivatives-chart-container">
                    <div class="derivatives-chart-header">
                        <h5 class="derivatives-chart-title">Absorption Patterns</h5>
                        <div class="derivatives-timeframe-buttons">
                            <button class="btn btn-sm btn-outline-primary active">1m</button>
                            <button class="btn btn-sm btn-outline-primary">5m</button>
                            <button class="btn btn-sm btn-outline-primary">15m</button>
                            <button class="btn btn-sm btn-outline-primary">1h</button>
                        </div>
                    </div>
                    <div class="df-chart-container" id="absorptionChart" style="height: 400px;">
                        <!-- Absorption Chart will be rendered here -->
                    </div>
                </div>
            </div>

            <!-- Absorption Metrics -->
            <div class="col-lg-4">
                <div class="derivatives-table-container">
                    <h5 class="derivatives-table-title">Absorption Metrics</h5>
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
                                    <td>Absorption Rate</td>
                                    <td class="text-end text-warning">73.2%</td>
                                </tr>
                                <tr>
                                    <td>Big Player Activity</td>
                                    <td class="text-end">
                                        <span class="badge bg-warning">High</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Absorption Events</td>
                                    <td class="text-end">12</td>
                                </tr>
                                <tr>
                                    <td>Avg Event Size</td>
                                    <td class="text-end">$125K</td>
                                </tr>
                                <tr>
                                    <td>Resistance Level</td>
                                    <td class="text-end">$47,500</td>
                                </tr>
                                <tr>
                                    <td>Support Level</td>
                                    <td class="text-end">$46,800</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Absorption Events -->
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
                                    <td>14:23</td>
                                    <td>
                                        <span class="badge bg-danger">Sell Absorption</span>
                                    </td>
                                    <td class="text-end">$250K</td>
                                </tr>
                                <tr>
                                    <td>14:15</td>
                                    <td>
                                        <span class="badge bg-success">Buy Absorption</span>
                                    </td>
                                    <td class="text-end">$180K</td>
                                </tr>
                                <tr>
                                    <td>14:08</td>
                                    <td>
                                        <span class="badge bg-warning">Neutral</span>
                                    </td>
                                    <td class="text-end">$95K</td>
                                </tr>
                                <tr>
                                    <td>14:02</td>
                                    <td>
                                        <span class="badge bg-danger">Sell Absorption</span>
                                    </td>
                                    <td class="text-end">$320K</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Absorption Heatmap -->
        <div class="row g-3 mt-3">
            <div class="col-12">
                <div class="derivatives-chart-container">
                    <h5 class="derivatives-chart-title">Absorption Heatmap by Price Level</h5>
                    <div class="df-chart-container" id="heatmapChart" style="height: 300px;">
                        <!-- Heatmap Chart will be rendered here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="derivatives-stats-grid">
            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Absorption Rate</div>
                        <div class="derivatives-stat-value text-warning">73.2%</div>
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
                        <div class="derivatives-stat-label">Big Player Events</div>
                        <div class="derivatives-stat-value">12</div>
                    </div>
                    <div class="derivatives-stat-icon primary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Avg Event Size</div>
                        <div class="derivatives-stat-value">$125K</div>
                    </div>
                    <div class="derivatives-stat-icon info">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2v20m9-9H3"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Market Impact</div>
                        <div class="derivatives-stat-value text-danger">High</div>
                    </div>
                    <div class="derivatives-stat-icon danger">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 3v18h18"/>
                            <path d="M7 12l3-3 3 3 5-5"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js for Absorption Visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Absorption Chart Implementation
            const ctx = document.getElementById('absorptionChart').getContext('2d');

            // Sample absorption data
            const labels = [];
            const priceData = [];
            const volumeData = [];
            const absorptionData = [];

            // Generate sample data
            let currentPrice = 47250;
            let currentAbsorption = 0;

            for (let i = 0; i < 60; i++) {
                const time = new Date(Date.now() - (60 - i) * 60000);
                labels.push(time.toLocaleTimeString());

                // Simulate price movement
                currentPrice += (Math.random() - 0.5) * 50;
                priceData.push(currentPrice);

                // Simulate volume
                const volume = Math.random() * 1000 + 500;
                volumeData.push(volume);

                // Simulate absorption (cumulative)
                const absorptionChange = (Math.random() - 0.3) * 10; // Bias towards positive
                currentAbsorption += absorptionChange;
                absorptionData.push(currentAbsorption);
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
                            label: 'Absorption',
                            data: absorptionData,
                            borderColor: 'rgb(245, 158, 11)',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            yAxisID: 'y1',
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
                                text: 'Absorption Index'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Price vs Absorption Analysis'
                        }
                    }
                }
            });

            // Heatmap Chart Implementation
            const heatmapCtx = document.getElementById('heatmapChart').getContext('2d');

            // Generate heatmap data
            const heatmapData = [];
            const heatmapLabels = [];

            for (let i = 0; i < 10; i++) {
                const price = (47200 + i * 10).toFixed(0);
                heatmapLabels.push('$' + price);

                const absorption = Math.random() * 100;
                heatmapData.push(absorption);
            }

            new Chart(heatmapCtx, {
                type: 'bar',
                data: {
                    labels: heatmapLabels,
                    datasets: [{
                        label: 'Absorption Level',
                        data: heatmapData,
                        backgroundColor: heatmapData.map(value => {
                            if (value > 80) return 'rgba(239, 68, 68, 0.8)';
                            if (value > 60) return 'rgba(245, 158, 11, 0.8)';
                            if (value > 40) return 'rgba(59, 130, 246, 0.8)';
                            return 'rgba(16, 185, 129, 0.8)';
                        }),
                        borderColor: heatmapData.map(value => {
                            if (value > 80) return 'rgb(239, 68, 68)';
                            if (value > 60) return 'rgb(245, 158, 11)';
                            if (value > 40) return 'rgb(59, 130, 246)';
                            return 'rgb(16, 185, 129)';
                        }),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Price Level'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Absorption Level (%)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Absorption Heatmap by Price Level'
                        }
                    }
                }
            });
        });
    </script>
@endsection
