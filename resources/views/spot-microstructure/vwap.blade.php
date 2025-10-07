@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100">
        <!-- Header Section -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1>VWAP + Deviation Bands</h1>
                    <p>Anchor harga institusi dengan filter overbought/oversold yang valid untuk trading decisions</p>
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
                        <option>Session: 24H</option>
                        <option>Session: 12H</option>
                        <option>Session: 6H</option>
                        <option>Session: 1H</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row g-3 flex-grow-1">
            <!-- VWAP Chart -->
            <div class="col-lg-8">
                <div class="derivatives-chart-container">
                    <div class="derivatives-chart-header">
                        <h5 class="derivatives-chart-title">VWAP with Deviation Bands</h5>
                        <div class="derivatives-timeframe-buttons">
                            <button class="btn btn-sm btn-outline-primary active">1m</button>
                            <button class="btn btn-sm btn-outline-primary">5m</button>
                            <button class="btn btn-sm btn-outline-primary">15m</button>
                            <button class="btn btn-sm btn-outline-primary">1h</button>
                        </div>
                    </div>
                    <div class="df-chart-container" id="vwapChart" style="height: 400px;">
                        <!-- VWAP Chart will be rendered here -->
                    </div>
                </div>
            </div>

            <!-- VWAP Metrics -->
            <div class="col-lg-4">
                <div class="derivatives-table-container">
                    <h5 class="derivatives-table-title">VWAP Metrics</h5>
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
                                    <td>Current VWAP</td>
                                    <td class="text-end">$47,125.50</td>
                                </tr>
                                <tr>
                                    <td>Price vs VWAP</td>
                                    <td class="text-end text-success">+0.26%</td>
                                </tr>
                                <tr>
                                    <td>Upper Band</td>
                                    <td class="text-end">$47,850.00</td>
                                </tr>
                                <tr>
                                    <td>Lower Band</td>
                                    <td class="text-end">$46,400.00</td>
                                </tr>
                                <tr>
                                    <td>Band Width</td>
                                    <td class="text-end">3.07%</td>
                                </tr>
                                <tr>
                                    <td>Band Position</td>
                                    <td class="text-end">
                                        <span class="badge bg-success">Above VWAP</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Deviation Analysis -->
                <div class="derivatives-table-container mt-3">
                    <h5 class="derivatives-table-title">Deviation Analysis</h5>
                    <div class="table-responsive">
                        <table class="table derivatives-table">
                            <thead>
                                <tr>
                                    <th>Level</th>
                                    <th class="text-end">Distance</th>
                                    <th class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>+2σ Band</td>
                                    <td class="text-end">$724.50</td>
                                    <td class="text-end">
                                        <span class="badge bg-warning">Near</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>+1σ Band</td>
                                    <td class="text-end">$362.25</td>
                                    <td class="text-end">
                                        <span class="badge bg-success">Safe</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>-1σ Band</td>
                                    <td class="text-end">$362.25</td>
                                    <td class="text-end">
                                        <span class="badge bg-success">Safe</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>-2σ Band</td>
                                    <td class="text-end">$724.50</td>
                                    <td class="text-end">
                                        <span class="badge bg-success">Safe</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- VWAP Signals -->
        <div class="row g-3 mt-3">
            <div class="col-lg-6">
                <div class="derivatives-table-container">
                    <h5 class="derivatives-table-title">Recent VWAP Signals</h5>
                    <div class="table-responsive">
                        <table class="table derivatives-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Signal</th>
                                    <th class="text-end">Strength</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>14:30</td>
                                    <td>
                                        <span class="badge bg-success">VWAP Support</span>
                                    </td>
                                    <td class="text-end">Strong</td>
                                </tr>
                                <tr>
                                    <td>14:15</td>
                                    <td>
                                        <span class="badge bg-warning">Band Rejection</span>
                                    </td>
                                    <td class="text-end">Medium</td>
                                </tr>
                                <tr>
                                    <td>14:00</td>
                                    <td>
                                        <span class="badge bg-danger">VWAP Resistance</span>
                                    </td>
                                    <td class="text-end">Strong</td>
                                </tr>
                                <tr>
                                    <td>13:45</td>
                                    <td>
                                        <span class="badge bg-info">Mean Reversion</span>
                                    </td>
                                    <td class="text-end">Weak</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="derivatives-table-container">
                    <h5 class="derivatives-table-title">Band Statistics</h5>
                    <div class="table-responsive">
                        <table class="table derivatives-table">
                            <thead>
                                <tr>
                                    <th>Statistic</th>
                                    <th class="text-end">Value</th>
                                    <th class="text-end">% of Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Above +2σ</td>
                                    <td class="text-end text-danger">Overbought</td>
                                    <td class="text-end">2.3%</td>
                                </tr>
                                <tr>
                                    <td>Between +1σ & +2σ</td>
                                    <td class="text-end text-warning">Bullish</td>
                                    <td class="text-end">13.6%</td>
                                </tr>
                                <tr>
                                    <td>Between -1σ & +1σ</td>
                                    <td class="text-end text-success">Neutral</td>
                                    <td class="text-end">68.2%</td>
                                </tr>
                                <tr>
                                    <td>Between -2σ & -1σ</td>
                                    <td class="text-end text-warning">Bearish</td>
                                    <td class="text-end">13.6%</td>
                                </tr>
                                <tr>
                                    <td>Below -2σ</td>
                                    <td class="text-end text-danger">Oversold</td>
                                    <td class="text-end">2.3%</td>
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
                        <div class="derivatives-stat-label">Current VWAP</div>
                        <div class="derivatives-stat-value">$47,125.50</div>
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
                        <div class="derivatives-stat-label">Price Deviation</div>
                        <div class="derivatives-stat-value text-success">+0.26%</div>
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
                        <div class="derivatives-stat-label">Band Width</div>
                        <div class="derivatives-stat-value">3.07%</div>
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
                        <div class="derivatives-stat-label">Signal Strength</div>
                        <div class="derivatives-stat-value text-warning">Medium</div>
                    </div>
                    <div class="derivatives-stat-icon warning">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js for VWAP Visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // VWAP Chart Implementation
            const ctx = document.getElementById('vwapChart').getContext('2d');

            // Sample VWAP data
            const labels = [];
            const priceData = [];
            const vwapData = [];
            const upperBandData = [];
            const lowerBandData = [];
            const upperBand2Data = [];
            const lowerBand2Data = [];

            // Generate sample data
            let currentPrice = 47250;
            let currentVWAP = 47125;
            let currentUpperBand = 47850;
            let currentLowerBand = 46400;
            let currentUpperBand2 = 48500;
            let currentLowerBand2 = 45750;

            for (let i = 0; i < 100; i++) {
                const time = new Date(Date.now() - (100 - i) * 60000);
                labels.push(time.toLocaleTimeString());

                // Simulate price movement
                currentPrice += (Math.random() - 0.5) * 50;
                priceData.push(currentPrice);

                // Simulate VWAP (more stable than price)
                currentVWAP += (Math.random() - 0.5) * 20;
                vwapData.push(currentVWAP);

                // Simulate bands (following VWAP)
                currentUpperBand = currentVWAP + 725;
                currentLowerBand = currentVWAP - 725;
                currentUpperBand2 = currentVWAP + 1375;
                currentLowerBand2 = currentVWAP - 1375;

                upperBandData.push(currentUpperBand);
                lowerBandData.push(currentLowerBand);
                upperBand2Data.push(currentUpperBand2);
                lowerBand2Data.push(currentLowerBand2);
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
                            tension: 0.1,
                            borderWidth: 2
                        },
                        {
                            label: 'VWAP',
                            data: vwapData,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.1,
                            borderWidth: 2
                        },
                        {
                            label: '+2σ Band',
                            data: upperBand2Data,
                            borderColor: 'rgba(239, 68, 68, 0.5)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.1,
                            borderWidth: 1,
                            borderDash: [5, 5]
                        },
                        {
                            label: '+1σ Band',
                            data: upperBandData,
                            borderColor: 'rgba(245, 158, 11, 0.5)',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            tension: 0.1,
                            borderWidth: 1,
                            borderDash: [5, 5]
                        },
                        {
                            label: '-1σ Band',
                            data: lowerBandData,
                            borderColor: 'rgba(245, 158, 11, 0.5)',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            tension: 0.1,
                            borderWidth: 1,
                            borderDash: [5, 5]
                        },
                        {
                            label: '-2σ Band',
                            data: lowerBand2Data,
                            borderColor: 'rgba(239, 68, 68, 0.5)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.1,
                            borderWidth: 1,
                            borderDash: [5, 5]
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
                            display: true,
                            title: {
                                display: true,
                                text: 'Price (USDT)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'VWAP with Deviation Bands Analysis'
                        }
                    }
                }
            });
        });
    </script>
@endsection
