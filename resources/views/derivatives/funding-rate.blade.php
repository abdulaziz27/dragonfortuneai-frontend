@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100">
        <!-- Header Section -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1>Funding Rate</h1>
                    <p>Monitor funding rates across exchanges to detect bias leverage & potential short/long squeeze</p>
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
                        <option>All Pairs</option>
                        <option>BTCUSDT</option>
                        <option>ETHUSDT</option>
                        <option>SOLUSDT</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row g-3 flex-grow-1">
            <!-- Funding Rate Chart -->
            <div class="col-lg-8">
                <div class="derivatives-chart-container">
                    <div class="derivatives-chart-header">
                        <h5 class="derivatives-chart-title">Funding Rate Trend</h5>
                        <div class="derivatives-timeframe-buttons">
                            <button class="btn btn-outline-secondary">1H</button>
                            <button class="btn btn-outline-secondary">4H</button>
                            <button class="btn btn-primary">8H</button>
                            <button class="btn btn-outline-secondary">1D</button>
                        </div>
                    </div>
                    <div id="fundingRateChart" style="height: 400px;">
                        <!-- Chart will be rendered here -->
                    </div>
                </div>
            </div>

            <!-- Funding Rate Table -->
            <div class="col-lg-4">
                <div class="derivatives-table-container">
                    <h5 class="derivatives-table-title">Current Funding Rates</h5>
                    <div class="table-responsive">
                        <table class="table derivatives-table">
                            <thead>
                                <tr>
                                    <th>Exchange</th>
                                    <th>Pair</th>
                                    <th class="text-end">Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Binance</td>
                                    <td class="fw-semibold">BTCUSDT</td>
                                    <td class="text-end text-success">+0.0125%</td>
                                </tr>
                                <tr>
                                    <td>Bybit</td>
                                    <td class="fw-semibold">BTCUSDT</td>
                                    <td class="text-end text-success">+0.0108%</td>
                                </tr>
                                <tr>
                                    <td>OKX</td>
                                    <td class="fw-semibold">BTCUSDT</td>
                                    <td class="text-end text-success">+0.0095%</td>
                                </tr>
                                <tr>
                                    <td>Binance</td>
                                    <td class="fw-semibold">ETHUSDT</td>
                                    <td class="text-end text-danger">-0.0032%</td>
                                </tr>
                                <tr>
                                    <td>Bybit</td>
                                    <td class="fw-semibold">ETHUSDT</td>
                                    <td class="text-end text-danger">-0.0028%</td>
                                </tr>
                                <tr>
                                    <td>OKX</td>
                                    <td class="fw-semibold">ETHUSDT</td>
                                    <td class="text-end text-danger">-0.0041%</td>
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
                        <div class="derivatives-stat-label">Avg Funding Rate</div>
                        <div class="derivatives-stat-value">+0.0085%</div>
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
                        <div class="derivatives-stat-label">Positive Rate</div>
                        <div class="derivatives-stat-value">65.2%</div>
                    </div>
                    <div class="derivatives-stat-icon info">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="derivatives-stat-card">
                <div class="derivatives-stat-content">
                    <div class="derivatives-stat-info">
                        <div class="derivatives-stat-label">Max Rate</div>
                        <div class="derivatives-stat-value">+0.0250%</div>
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
                        <div class="derivatives-stat-label">Next Funding</div>
                        <div class="derivatives-stat-value">2h 15m</div>
                    </div>
                    <div class="derivatives-stat-icon primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12,6 12,12 16,14"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js for Funding Rate Chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Funding Rate Chart
            const ctx = document.getElementById('fundingRateChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00'],
                    datasets: [{
                        label: 'BTCUSDT Funding Rate',
                        data: [0.0085, 0.0125, 0.0095, 0.0150, 0.0110, 0.0085, 0.0125],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'ETHUSDT Funding Rate',
                        data: [-0.0025, -0.0032, -0.0018, -0.0045, -0.0020, -0.0032, -0.0025],
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true
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
                                    return value.toFixed(4) + '%';
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
