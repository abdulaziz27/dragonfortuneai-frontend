@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100">
        <!-- Header Section -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1>CVD Analysis</h1>
                    <p>Deteksi divergence antara order flow vs harga untuk identifikasi momentum reversal</p>
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
                        <option>1m</option>
                        <option>5m</option>
                        <option>15m</option>
                        <option>1h</option>
                        <option>4h</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row g-3 flex-grow-1">
            <!-- CVD Chart -->
            <div class="col-lg-8">
                <div class="derivatives-chart-container">
                    <div class="derivatives-chart-header">
                        <h5 class="derivatives-chart-title">Cumulative Volume Delta</h5>
                        <div class="derivatives-timeframe-buttons">
                            <button class="btn btn-sm btn-outline-primary active">1H</button>
                            <button class="btn btn-sm btn-outline-primary">4H</button>
                            <button class="btn btn-sm btn-outline-primary">1D</button>
                            <button class="btn btn-sm btn-outline-primary">1W</button>
                        </div>
                    </div>
                    <div class="df-chart-container" id="cvdChart" style="height: 400px;">
                        <!-- CVD Chart will be rendered here -->
                    </div>
                </div>
            </div>

            <!-- CVD Metrics -->
            <div class="col-lg-4">
                <div class="derivatives-table-container">
                    <h5 class="derivatives-table-title">CVD Metrics</h5>
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
                                    <td>CVD Current</td>
                                    <td class="text-end text-success">+2,456,789</td>
                                </tr>
                                <tr>
                                    <td>CVD Change (1H)</td>
                                    <td class="text-end text-danger">-123,456</td>
                                </tr>
                                <tr>
                                    <td>CVD Divergence</td>
                                    <td class="text-end">
                                        <span class="badge bg-warning">Bearish</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Volume Ratio</td>
                                    <td class="text-end">0.65</td>
                                </tr>
                                <tr>
                                    <td>Buy Pressure</td>
                                    <td class="text-end text-success">65.2%</td>
                                </tr>
                                <tr>
                                    <td>Sell Pressure</td>
                                    <td class="text-end text-danger">34.8%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Divergence Alerts -->
                <div class="derivatives-table-container mt-3">
                    <h5 class="derivatives-table-title">Divergence Alerts</h5>
                    <div class="table-responsive">
                        <table class="table derivatives-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Type</th>
                                    <th class="text-end">Strength</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>14:23</td>
                                    <td>
                                        <span class="badge bg-danger">Bearish</span>
                                    </td>
                                    <td class="text-end">Strong</td>
                                </tr>
                                <tr>
                                    <td>13:45</td>
                                    <td>
                                        <span class="badge bg-success">Bullish</span>
                                    </td>
                                    <td class="text-end">Medium</td>
                                </tr>
                                <tr>
                                    <td>12:15</td>
                                    <td>
                                        <span class="badge bg-warning">Hidden</span>
                                    </td>
                                    <td class="text-end">Weak</td>
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
                        <div class="derivatives-stat-label">Current CVD</div>
                        <div class="derivatives-stat-value text-success">+2.46M</div>
                    </div>
                    <div class="derivatives-stat-icon primary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 3v18h18"/>
                            <path d="M7 12l3-3 3 3 5-5"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Divergence Count</div>
                        <div class="derivatives-stat-value">3</div>
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
                        <div class="derivatives-stat-label">Volume Imbalance</div>
                        <div class="derivatives-stat-value text-danger">-15.3%</div>
                    </div>
                    <div class="derivatives-stat-icon danger">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Buy/Sell Ratio</div>
                        <div class="derivatives-stat-value">1.87</div>
                    </div>
                    <div class="derivatives-stat-icon info">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js for CVD Visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // CVD Chart Implementation
            const ctx = document.getElementById('cvdChart').getContext('2d');

            // Sample CVD data
            const labels = [];
            const cvdData = [];
            const priceData = [];
            const volumeData = [];

            // Generate sample data
            let currentCVD = 0;
            let currentPrice = 47000;

            for (let i = 0; i < 100; i++) {
                const time = new Date(Date.now() - (100 - i) * 60000);
                labels.push(time.toLocaleTimeString());

                // Simulate CVD movement
                const volumeChange = (Math.random() - 0.5) * 10000;
                currentCVD += volumeChange;
                cvdData.push(currentCVD);

                // Simulate price movement
                currentPrice += (Math.random() - 0.5) * 100;
                priceData.push(currentPrice);

                // Simulate volume
                volumeData.push(Math.random() * 1000 + 500);
            }

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'CVD',
                            data: cvdData,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            yAxisID: 'y',
                            tension: 0.1
                        },
                        {
                            label: 'Price',
                            data: priceData,
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
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
                                text: 'CVD'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Price (USDT)'
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
                            text: 'CVD vs Price Divergence Analysis'
                        }
                    }
                }
            });
        });
    </script>
@endsection
