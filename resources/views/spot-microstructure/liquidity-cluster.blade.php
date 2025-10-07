@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100">
        <!-- Header Section -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1>Liquidity Cluster Analysis</h1>
                    <p>Ukur kekuatan level support/resistance real di orderbook untuk identifikasi key levels</p>
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
                        <option>Cluster Size: 0.1%</option>
                        <option>Cluster Size: 0.2%</option>
                        <option>Cluster Size: 0.5%</option>
                        <option>Cluster Size: 1.0%</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row g-3 flex-grow-1">
            <!-- Liquidity Cluster Chart -->
            <div class="col-lg-8">
                <div class="derivatives-chart-container">
                    <div class="derivatives-chart-header">
                        <h5 class="derivatives-chart-title">Liquidity Cluster Strength</h5>
                        <div class="derivatives-timeframe-buttons">
                            <button class="btn btn-sm btn-outline-primary active">Live</button>
                            <button class="btn btn-sm btn-outline-primary">1m</button>
                            <button class="btn btn-sm btn-outline-primary">5m</button>
                            <button class="btn btn-sm btn-outline-primary">15m</button>
                        </div>
                    </div>
                    <div class="df-chart-container" id="liquidityChart" style="height: 400px;">
                        <!-- Liquidity Chart will be rendered here -->
                    </div>
                </div>
            </div>

            <!-- Cluster Metrics -->
            <div class="col-lg-4">
                <div class="derivatives-table-container">
                    <h5 class="derivatives-table-title">Cluster Metrics</h5>
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
                                    <td>Active Clusters</td>
                                    <td class="text-end">8</td>
                                </tr>
                                <tr>
                                    <td>Strongest Cluster</td>
                                    <td class="text-end">$47,500</td>
                                </tr>
                                    <td>Cluster Strength</td>
                                    <td class="text-end">
                                        <span class="badge bg-success">High</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Imbalance Ratio</td>
                                    <td class="text-end text-warning">1.35</td>
                                </tr>
                                <tr>
                                    <td>Liquidity Score</td>
                                    <td class="text-end">8.7/10</td>
                                </tr>
                                <tr>
                                    <td>Last Update</td>
                                    <td class="text-end">2 sec ago</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Key Levels -->
                <div class="derivatives-table-container mt-3">
                    <h5 class="derivatives-table-title">Key Levels</h5>
                    <div class="table-responsive">
                        <table class="table derivatives-table">
                            <thead>
                                <tr>
                                    <th>Level</th>
                                    <th class="text-end">Strength</th>
                                    <th class="text-end">Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>$47,500</td>
                                    <td class="text-end">
                                        <span class="badge bg-danger">9.2</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-danger">Resistance</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>$47,000</td>
                                    <td class="text-end">
                                        <span class="badge bg-success">8.5</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-success">Support</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>$46,800</td>
                                    <td class="text-end">
                                        <span class="badge bg-warning">6.8</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-success">Support</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>$47,200</td>
                                    <td class="text-end">
                                        <span class="badge bg-warning">5.2</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-warning">Neutral</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cluster Analysis -->
        <div class="row g-3 mt-3">
            <div class="col-lg-6">
                <div class="derivatives-table-container">
                    <h5 class="derivatives-table-title">Cluster Breakdown</h5>
                    <div class="table-responsive">
                        <table class="table derivatives-table">
                            <thead>
                                <tr>
                                    <th>Price Range</th>
                                    <th class="text-end">Liquidity</th>
                                    <th class="text-end">Bid/Ask</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>$47,450 - $47,550</td>
                                    <td class="text-end">$2.45M</td>
                                    <td class="text-end text-danger">35/65</td>
                                </tr>
                                <tr>
                                    <td>$47,350 - $47,450</td>
                                    <td class="text-end">$1.89M</td>
                                    <td class="text-end text-success">60/40</td>
                                </tr>
                                <tr>
                                    <td>$47,250 - $47,350</td>
                                    <td class="text-end">$1.67M</td>
                                    <td class="text-end text-success">55/45</td>
                                </tr>
                                <tr>
                                    <td>$47,150 - $47,250</td>
                                    <td class="text-end">$1.34M</td>
                                    <td class="text-end text-warning">50/50</td>
                                </tr>
                                <tr>
                                    <td>$47,050 - $47,150</td>
                                    <td class="text-end">$1.12M</td>
                                    <td class="text-end text-success">65/35</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="derivatives-table-container">
                    <h5 class="derivatives-table-title">Imbalance Analysis</h5>
                    <div class="table-responsive">
                        <table class="table derivatives-table">
                            <thead>
                                <tr>
                                    <th>Level</th>
                                    <th class="text-end">Bid Liquidity</th>
                                    <th class="text-end">Ask Liquidity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>$47,500</td>
                                    <td class="text-end text-success">$850K</td>
                                    <td class="text-end text-danger">$1.6M</td>
                                </tr>
                                <tr>
                                    <td>$47,400</td>
                                    <td class="text-end text-success">$1.1M</td>
                                    <td class="text-end text-danger">$790K</td>
                                </tr>
                                <tr>
                                    <td>$47,300</td>
                                    <td class="text-end text-success">$920K</td>
                                    <td class="text-end text-danger">$750K</td>
                                </tr>
                                <tr>
                                    <td>$47,200</td>
                                    <td class="text-end text-warning">$670K</td>
                                    <td class="text-end text-warning">$670K</td>
                                </tr>
                                <tr>
                                    <td>$47,100</td>
                                    <td class="text-end text-success">$728K</td>
                                    <td class="text-end text-danger">$392K</td>
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
                        <div class="derivatives-stat-label">Active Clusters</div>
                        <div class="derivatives-stat-value">8</div>
                    </div>
                    <div class="derivatives-stat-icon primary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Strongest Level</div>
                        <div class="derivatives-stat-value">$47,500</div>
                    </div>
                    <div class="derivatives-stat-icon danger">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Imbalance Ratio</div>
                        <div class="derivatives-stat-value text-warning">1.35</div>
                    </div>
                    <div class="derivatives-stat-icon warning">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18"/>
                            <path d="M3 12h18"/>
                            <path d="M3 18h18"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Liquidity Score</div>
                        <div class="derivatives-stat-value text-success">8.7/10</div>
                    </div>
                    <div class="derivatives-stat-icon success">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js for Liquidity Cluster Visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Liquidity Cluster Chart Implementation
            const ctx = document.getElementById('liquidityChart').getContext('2d');

            // Sample liquidity cluster data
            const labels = [];
            const bidLiquidityData = [];
            const askLiquidityData = [];
            const totalLiquidityData = [];

            // Generate sample data
            const basePrice = 47250;
            for (let i = 0; i < 20; i++) {
                const price = (basePrice - 500 + i * 50).toFixed(0);
                labels.push('$' + price);

                // Simulate bid liquidity (higher at lower prices)
                const bidLiquidity = Math.max(0, 1000 - i * 20 + Math.random() * 200);
                bidLiquidityData.push(bidLiquidity);

                // Simulate ask liquidity (higher at higher prices)
                const askLiquidity = Math.max(0, 200 + i * 30 + Math.random() * 150);
                askLiquidityData.push(askLiquidity);

                // Total liquidity
                totalLiquidityData.push(bidLiquidity + askLiquidity);
            }

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Bid Liquidity',
                            data: bidLiquidityData,
                            backgroundColor: 'rgba(16, 185, 129, 0.6)',
                            borderColor: 'rgb(16, 185, 129)',
                            borderWidth: 1
                        },
                        {
                            label: 'Ask Liquidity',
                            data: askLiquidityData,
                            backgroundColor: 'rgba(239, 68, 68, 0.6)',
                            borderColor: 'rgb(239, 68, 68)',
                            borderWidth: 1
                        },
                        {
                            label: 'Total Liquidity',
                            data: totalLiquidityData,
                            type: 'line',
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.1
                        }
                    ]
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
                                text: 'Liquidity ($)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Liquidity Cluster Strength Analysis'
                        }
                    }
                }
            });
        });
    </script>
@endsection
