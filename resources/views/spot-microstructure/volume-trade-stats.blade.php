@extends('layouts.app')

@section('title', 'Volume Trade Stats')

@section('content')
<div class="df-container">
    <div class="df-header">
        <h1 class="df-title">Volume Trade Statistics</h1>
        <p class="df-subtitle">Comprehensive volume analysis and trade statistics</p>
    </div>

    <div class="df-grid">
        <!-- Volume Chart -->
        <div class="df-card">
            <div class="df-card-header">
                <h3>Volume Analysis</h3>
                <div class="df-card-actions">
                    <select class="df-select">
                        <option value="1h">1 Hour</option>
                        <option value="4h">4 Hours</option>
                        <option value="1d">1 Day</option>
                        <option value="7d">7 Days</option>
                    </select>
                </div>
            </div>
            <div class="df-card-content">
                <div id="volume-chart" style="height: 400px;"></div>
            </div>
        </div>

        <!-- Volume Distribution -->
        <div class="df-card">
            <div class="df-card-header">
                <h3>Volume Distribution</h3>
            </div>
            <div class="df-card-content">
                <div id="volume-distribution-chart" style="height: 300px;"></div>
            </div>
        </div>

        <!-- Volume Statistics -->
        <div class="df-card">
            <div class="df-card-header">
                <h3>Volume Statistics</h3>
            </div>
            <div class="df-card-content">
                <div class="df-stats-grid">
                    <div class="df-stat">
                        <div class="df-stat-value">$2.4M</div>
                        <div class="df-stat-label">24h Volume</div>
                    </div>
                    <div class="df-stat">
                        <div class="df-stat-value">$156K</div>
                        <div class="df-stat-label">Avg Hourly</div>
                    </div>
                    <div class="df-stat">
                        <div class="df-stat-value">+12.5%</div>
                        <div class="df-stat-label">vs Yesterday</div>
                    </div>
                    <div class="df-stat">
                        <div class="df-stat-value">847</div>
                        <div class="df-stat-label">Total Trades</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exchange Volume Comparison -->
        <div class="df-card">
            <div class="df-card-header">
                <h3>Exchange Volume</h3>
            </div>
            <div class="df-card-content">
                <div id="exchange-volume-chart" style="height: 300px;"></div>
            </div>
        </div>

        <!-- Volume Profile -->
        <div class="df-card">
            <div class="df-card-header">
                <h3>Volume Profile</h3>
            </div>
            <div class="df-card-content">
                <div id="volume-profile-chart" style="height: 300px;"></div>
            </div>
        </div>

        <!-- Trade Size Analysis -->
        <div class="df-card">
            <div class="df-card-header">
                <h3>Trade Size Analysis</h3>
            </div>
            <div class="df-card-content">
                <div class="df-stats-grid">
                    <div class="df-stat">
                        <div class="df-stat-value">$421</div>
                        <div class="df-stat-label">Avg Trade Size</div>
                    </div>
                    <div class="df-stat">
                        <div class="df-stat-value">$2,847</div>
                        <div class="df-stat-label">Median Trade</div>
                    </div>
                    <div class="df-stat">
                        <div class="df-stat-value">$45,200</div>
                        <div class="df-stat-label">Largest Trade</div>
                    </div>
                    <div class="df-stat">
                        <div class="df-stat-value">$12</div>
                        <div class="df-stat-label">Smallest Trade</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Volume Table -->
        <div class="df-card df-card-full">
            <div class="df-card-header">
                <h3>Volume Breakdown by Exchange</h3>
                <div class="df-card-actions">
                    <button class="df-btn df-btn-sm">Export</button>
                </div>
            </div>
            <div class="df-card-content">
                <div class="df-table-container">
                    <table class="df-table">
                        <thead>
                            <tr>
                                <th>Exchange</th>
                                <th>24h Volume</th>
                                <th>Volume %</th>
                                <th>Trade Count</th>
                                <th>Avg Trade Size</th>
                                <th>Market Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Binance</td>
                                <td>$856,420</td>
                                <td>35.7%</td>
                                <td>312</td>
                                <td>$2,745</td>
                                <td>
                                    <div class="df-progress">
                                        <div class="df-progress-bar" style="width: 35.7%"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Coinbase</td>
                                <td>$642,180</td>
                                <td>26.8%</td>
                                <td>198</td>
                                <td>$3,243</td>
                                <td>
                                    <div class="df-progress">
                                        <div class="df-progress-bar" style="width: 26.8%"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Kraken</td>
                                <td>$481,350</td>
                                <td>20.1%</td>
                                <td>156</td>
                                <td>$3,086</td>
                                <td>
                                    <div class="df-progress">
                                        <div class="df-progress-bar" style="width: 20.1%"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Others</td>
                                <td>$420,050</td>
                                <td>17.5%</td>
                                <td>181</td>
                                <td>$2,321</td>
                                <td>
                                    <div class="df-progress">
                                        <div class="df-progress-bar" style="width: 17.5%"></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.df-progress {
    width: 100%;
    height: 8px;
    background-color: var(--muted);
    border-radius: 4px;
    overflow: hidden;
}

.df-progress-bar {
    height: 100%;
    background-color: var(--primary);
    transition: width 0.3s ease;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize volume chart
    const volumeChart = new Chart(document.getElementById('volume-chart'), {
        type: 'bar',
        data: {
            labels: ['14:00', '14:15', '14:30', '14:45', '15:00'],
            datasets: [{
                label: 'Volume ($)',
                data: [120000, 180000, 250000, 320000, 210000],
                backgroundColor: '#3b82f6',
                borderColor: '#2563eb',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + (value / 1000) + 'K';
                        }
                    }
                }
            }
        }
    });

    // Initialize volume distribution chart
    const volumeDistributionChart = new Chart(document.getElementById('volume-distribution-chart'), {
        type: 'doughnut',
        data: {
            labels: ['Binance', 'Coinbase', 'Kraken', 'Others'],
            datasets: [{
                data: [35.7, 26.8, 20.1, 17.5],
                backgroundColor: [
                    '#3b82f6',
                    '#10b981',
                    '#f59e0b',
                    '#ef4444'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Initialize exchange volume chart
    const exchangeVolumeChart = new Chart(document.getElementById('exchange-volume-chart'), {
        type: 'bar',
        data: {
            labels: ['Binance', 'Coinbase', 'Kraken', 'Others'],
            datasets: [{
                label: 'Volume ($)',
                data: [856420, 642180, 481350, 420050],
                backgroundColor: [
                    '#3b82f6',
                    '#10b981',
                    '#f59e0b',
                    '#ef4444'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + (value / 1000) + 'K';
                        }
                    }
                }
            }
        }
    });

    // Initialize volume profile chart
    const volumeProfileChart = new Chart(document.getElementById('volume-profile-chart'), {
        type: 'bar',
        data: {
            labels: ['$43,200', '$43,220', '$43,240', '$43,260', '$43,280', '$43,300'],
            datasets: [{
                label: 'Volume at Price',
                data: [1200, 1800, 3200, 2800, 1500, 800],
                backgroundColor: '#8b5cf6',
                borderColor: '#7c3aed',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endsection
