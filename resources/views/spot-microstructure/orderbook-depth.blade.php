@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100">
        <!-- Header Section -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1>Orderbook Depth Analysis</h1>
                    <p>Ukur ketebalan likuiditas untuk identifikasi level support/resistance yang valid</p>
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
                        <option>Level 10</option>
                        <option>Level 20</option>
                        <option>Level 50</option>
                        <option>Level 100</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row g-3 flex-grow-1">
            <!-- Orderbook Depth Chart -->
            <div class="col-lg-8">
                <div class="derivatives-chart-container">
                    <div class="derivatives-chart-header">
                        <h5 class="derivatives-chart-title">Orderbook Depth Visualization</h5>
                        <div class="derivatives-timeframe-buttons">
                            <button class="btn btn-sm btn-outline-primary active">Live</button>
                            <button class="btn btn-sm btn-outline-primary">1m</button>
                            <button class="btn btn-sm btn-outline-primary">5m</button>
                            <button class="btn btn-sm btn-outline-primary">15m</button>
                        </div>
                    </div>
                    <div class="df-chart-container" id="orderbookChart" style="height: 400px;">
                        <!-- Orderbook Chart will be rendered here -->
                    </div>
                </div>
            </div>

            <!-- Orderbook Metrics -->
            <div class="col-lg-4">
                <div class="derivatives-table-container">
                    <h5 class="derivatives-table-title">Depth Metrics</h5>
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
                                    <td>Bid Depth (10 levels)</td>
                                    <td class="text-end text-success">$2.45M</td>
                                </tr>
                                <tr>
                                    <td>Ask Depth (10 levels)</td>
                                    <td class="text-end text-danger">$1.89M</td>
                                </tr>
                                <tr>
                                    <td>Depth Imbalance</td>
                                    <td class="text-end text-success">+29.6%</td>
                                </tr>
                                <tr>
                                    <td>Spread (bps)</td>
                                    <td class="text-end">2.5</td>
                                </tr>
                                <tr>
                                    <td>Mid Price</td>
                                    <td class="text-end">$47,250.00</td>
                                </tr>
                                <tr>
                                    <td>Liquidity Score</td>
                                    <td class="text-end">
                                        <span class="badge bg-success">High</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Support/Resistance Levels -->
                <div class="derivatives-table-container mt-3">
                    <h5 class="derivatives-table-title">Key Levels</h5>
                    <div class="table-responsive">
                        <table class="table derivatives-table">
                            <thead>
                                <tr>
                                    <th>Level</th>
                                    <th>Type</th>
                                    <th class="text-end">Strength</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>$47,500</td>
                                    <td>
                                        <span class="badge bg-danger">Resistance</span>
                                    </td>
                                    <td class="text-end">Strong</td>
                                </tr>
                                <tr>
                                    <td>$47,000</td>
                                    <td>
                                        <span class="badge bg-success">Support</span>
                                    </td>
                                    <td class="text-end">Medium</td>
                                </tr>
                                <tr>
                                    <td>$46,800</td>
                                    <td>
                                        <span class="badge bg-success">Support</span>
                                    </td>
                                    <td class="text-end">Weak</td>
                                </tr>
                                <tr>
                                    <td>$47,200</td>
                                    <td>
                                        <span class="badge bg-warning">Neutral</span>
                                    </td>
                                    <td class="text-end">Medium</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Orderbook Table -->
        <div class="row g-3 mt-3">
            <div class="col-lg-6">
                <div class="derivatives-table-container">
                    <h5 class="derivatives-table-title">Live Orderbook - Bids</h5>
                    <div class="table-responsive">
                        <table class="table derivatives-table">
                            <thead>
                                <tr>
                                    <th>Price</th>
                                    <th class="text-end">Size</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-success">
                                    <td>$47,250.00</td>
                                    <td class="text-end">0.125</td>
                                    <td class="text-end">$5,906.25</td>
                                </tr>
                                <tr class="table-success">
                                    <td>$47,249.50</td>
                                    <td class="text-end">0.250</td>
                                    <td class="text-end">$11,812.38</td>
                                </tr>
                                <tr class="table-success">
                                    <td>$47,249.00</td>
                                    <td class="text-end">0.500</td>
                                    <td class="text-end">$23,624.50</td>
                                </tr>
                                <tr class="table-success">
                                    <td>$47,248.50</td>
                                    <td class="text-end">1.000</td>
                                    <td class="text-end">$47,248.50</td>
                                </tr>
                                <tr class="table-success">
                                    <td>$47,248.00</td>
                                    <td class="text-end">2.500</td>
                                    <td class="text-end">$118,120.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="derivatives-table-container">
                    <h5 class="derivatives-table-title">Live Orderbook - Asks</h5>
                    <div class="table-responsive">
                        <table class="table derivatives-table">
                            <thead>
                                <tr>
                                    <th>Price</th>
                                    <th class="text-end">Size</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-danger">
                                    <td>$47,250.50</td>
                                    <td class="text-end">0.100</td>
                                    <td class="text-end">$4,725.05</td>
                                </tr>
                                <tr class="table-danger">
                                    <td>$47,251.00</td>
                                    <td class="text-end">0.200</td>
                                    <td class="text-end">$9,450.20</td>
                                </tr>
                                <tr class="table-danger">
                                    <td>$47,251.50</td>
                                    <td class="text-end">0.400</td>
                                    <td class="text-end">$18,900.60</td>
                                </tr>
                                <tr class="table-danger">
                                    <td>$47,252.00</td>
                                    <td class="text-end">0.800</td>
                                    <td class="text-end">$37,801.60</td>
                                </tr>
                                <tr class="table-danger">
                                    <td>$47,252.50</td>
                                    <td class="text-end">1.500</td>
                                    <td class="text-end">$70,878.75</td>
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
                        <div class="derivatives-stat-label">Bid Depth</div>
                        <div class="derivatives-stat-value text-success">$2.45M</div>
                    </div>
                    <div class="derivatives-stat-icon success">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Ask Depth</div>
                        <div class="derivatives-stat-value text-danger">$1.89M</div>
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
                        <div class="derivatives-stat-label">Spread</div>
                        <div class="derivatives-stat-value">2.5 bps</div>
                    </div>
                    <div class="derivatives-stat-icon info">
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
                        <div class="derivatives-stat-value">8.7/10</div>
                    </div>
                    <div class="derivatives-stat-icon primary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js for Orderbook Visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Orderbook Depth Chart Implementation
            const ctx = document.getElementById('orderbookChart').getContext('2d');

            // Sample orderbook data
            const prices = [];
            const bidVolumes = [];
            const askVolumes = [];

            // Generate sample data around current price
            const midPrice = 47250;
            for (let i = 0; i < 20; i++) {
                const price = midPrice - (10 - i) * 0.5;
                prices.push(price.toFixed(2));

                // Simulate bid volume (higher near mid price)
                const bidVol = Math.max(0, 100 - Math.abs(10 - i) * 5 + Math.random() * 20);
                bidVolumes.push(bidVol);

                // Simulate ask volume (higher near mid price)
                const askVol = Math.max(0, 80 - Math.abs(10 - i) * 4 + Math.random() * 15);
                askVolumes.push(askVol);
            }

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: prices,
                    datasets: [
                        {
                            label: 'Bid Volume',
                            data: bidVolumes,
                            backgroundColor: 'rgba(16, 185, 129, 0.6)',
                            borderColor: 'rgb(16, 185, 129)',
                            borderWidth: 1
                        },
                        {
                            label: 'Ask Volume',
                            data: askVolumes,
                            backgroundColor: 'rgba(239, 68, 68, 0.6)',
                            borderColor: 'rgb(239, 68, 68)',
                            borderWidth: 1
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
                                text: 'Price (USDT)'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Volume'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Orderbook Depth Analysis'
                        }
                    }
                }
            });
        });
    </script>
@endsection
