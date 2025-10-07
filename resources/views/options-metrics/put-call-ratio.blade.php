@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h4 mb-1 fw-semibold">Put/Call Ratio</h2>
                    <p class="text-muted mb-0">Sentiment of hedging vs speculation in options market</p>
                </div>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" style="width: auto;">
                        <option value="24h">24 Hours</option>
                        <option value="7d" selected>7 Days</option>
                        <option value="30d">30 Days</option>
                        <option value="90d">90 Days</option>
                    </select>
                    <button class="btn btn-outline-secondary btn-sm">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                            <path d="M3 3v5h5"/>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-2">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-success">
                                    <path d="M3 3v18h18"/>
                                    <path d="M7 12l3-3 3 3 5-5"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title mb-1">Current Ratio</h6>
                            <h4 class="mb-0 text-success">0.85</h4>
                            <small class="text-muted">Put/Call</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-circle p-2">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-info">
                                    <path d="M3 3v18h18"/>
                                    <path d="M7 12l3-3 3 3 5-5"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title mb-1">Call Volume</h6>
                            <h4 class="mb-0 text-info">$12.4M</h4>
                            <small class="text-muted">24h volume</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-2">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-warning">
                                    <path d="M3 3v18h18"/>
                                    <path d="M7 12l3-3 3 3 5-5"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title mb-1">Put Volume</h6>
                            <h4 class="mb-0 text-warning">$10.5M</h4>
                            <small class="text-muted">24h volume</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-primary">
                                    <path d="M3 3v18h18"/>
                                    <path d="M7 12l3-3 3 3 5-5"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title mb-1">Sentiment</h6>
                            <h4 class="mb-0 text-primary">Bullish</h4>
                            <small class="text-muted">Market bias</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Chart Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Put/Call Ratio Trend</h5>
                        <div class="d-flex gap-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showRatio" checked>
                                <label class="form-check-label" for="showRatio">Put/Call Ratio</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showVolume" checked>
                                <label class="form-check-label" for="showVolume">Volume</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showSentiment" checked>
                                <label class="form-check-label" for="showSentiment">Sentiment</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="putCallChart" style="height: 400px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Options Flow Analysis -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Options Flow by Strike</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Strike</th>
                                    <th class="text-end">Call Flow</th>
                                    <th class="text-end">Put Flow</th>
                                    <th class="text-end">Ratio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>$95,000</td>
                                    <td class="text-end">$1.2M</td>
                                    <td class="text-end">$2.8M</td>
                                    <td class="text-end text-warning">2.33</td>
                                </tr>
                                <tr>
                                    <td>$100,000</td>
                                    <td class="text-end">$3.5M</td>
                                    <td class="text-end">$2.1M</td>
                                    <td class="text-end text-success">0.60</td>
                                </tr>
                                <tr>
                                    <td>$105,000</td>
                                    <td class="text-end">$4.2M</td>
                                    <td class="text-end">$1.8M</td>
                                    <td class="text-end text-success">0.43</td>
                                </tr>
                                <tr>
                                    <td>$110,000</td>
                                    <td class="text-end">$2.1M</td>
                                    <td class="text-end">$0.9M</td>
                                    <td class="text-end text-success">0.43</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Sentiment Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="position-relative d-inline-block">
                            <svg width="120" height="120" viewBox="0 0 120 120" class="position-relative">
                                <circle cx="60" cy="60" r="50" fill="none" stroke="#e9ecef" stroke-width="8"/>
                                <circle cx="60" cy="60" r="50" fill="none" stroke="#28a745" stroke-width="8"
                                        stroke-dasharray="314" stroke-dashoffset="94.2" transform="rotate(-90 60 60)"/>
                            </svg>
                            <div class="position-absolute top-50 start-50 translate-middle text-center">
                                <h3 class="mb-0 text-success">70%</h3>
                                <small class="text-muted">Bullish</small>
                            </div>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end">
                                <h6 class="text-success mb-1">Bullish</h6>
                                <small class="text-muted">70%</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h6 class="text-warning mb-1">Neutral</h6>
                                <small class="text-muted">20%</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h6 class="text-danger mb-1">Bearish</h6>
                            <small class="text-muted">10%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Options Activity by Type -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Options Activity by Type</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-success mb-1">Call Buying</h6>
                                <h4 class="mb-1">$8.2M</h4>
                                <small class="text-muted">Speculation</small>
                                <div class="mt-2">
                                    <span class="badge bg-success">+15%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-info mb-1">Call Selling</h6>
                                <h4 class="mb-1">$4.2M</h4>
                                <small class="text-muted">Income</small>
                                <div class="mt-2">
                                    <span class="badge bg-info">+8%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-warning mb-1">Put Buying</h6>
                                <h4 class="mb-1">$6.8M</h4>
                                <small class="text-muted">Hedging</small>
                                <div class="mt-2">
                                    <span class="badge bg-warning">-5%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-danger mb-1">Put Selling</h6>
                                <h4 class="mb-1">$3.7M</h4>
                                <small class="text-muted">Income</small>
                                <div class="mt-2">
                                    <span class="badge bg-danger">-12%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Options Flow Analysis -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Large Options Trades</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Strike</th>
                                    <th class="text-end">Type</th>
                                    <th class="text-end">Size</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>14:32</td>
                                    <td>$105,000</td>
                                    <td class="text-end text-success">Call Buy</td>
                                    <td class="text-end">$2.1M</td>
                                </tr>
                                <tr>
                                    <td>13:45</td>
                                    <td>$95,000</td>
                                    <td class="text-end text-warning">Put Buy</td>
                                    <td class="text-end">$1.8M</td>
                                </tr>
                                <tr>
                                    <td>12:15</td>
                                    <td>$100,000</td>
                                    <td class="text-end text-success">Call Buy</td>
                                    <td class="text-end">$1.5M</td>
                                </tr>
                                <tr>
                                    <td>11:30</td>
                                    <td>$110,000</td>
                                    <td class="text-end text-success">Call Buy</td>
                                    <td class="text-end">$1.2M</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Options Sentiment Indicators</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-success mb-1">Call/Put Ratio</h6>
                                <h4 class="mb-1">1.18</h4>
                                <small class="text-muted">Bullish</small>
                                <div class="mt-2">
                                    <span class="badge bg-success">Above 1.0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-info mb-1">Volume Ratio</h6>
                                <h4 class="mb-1">0.85</h4>
                                <small class="text-muted">Neutral</small>
                                <div class="mt-2">
                                    <span class="badge bg-info">0.8-1.2</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-warning mb-1">Premium Ratio</h6>
                                <h4 class="mb-1">0.92</h4>
                                <small class="text-muted">Slightly Bearish</small>
                                <div class="mt-2">
                                    <span class="badge bg-warning">Below 1.0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-primary mb-1">OI Ratio</h6>
                                <h4 class="mb-1">1.05</h4>
                                <small class="text-muted">Bullish</small>
                                <div class="mt-2">
                                    <span class="badge bg-primary">Above 1.0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Insights Section -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Trading Insights</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="alert alert-success border-0">
                                <div class="d-flex align-items-start">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-success me-2 mt-1">
                                        <path d="M9 12l2 2 4-4"/>
                                        <path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"/>
                                    </svg>
                                    <div>
                                        <h6 class="alert-heading">Bullish Sentiment</h6>
                                        <p class="mb-0 small">Put/Call ratio at 0.85 indicates bullish sentiment with more call buying than put buying.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-info border-0">
                                <div class="d-flex align-items-start">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-info me-2 mt-1">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                    <div>
                                        <h6 class="alert-heading">Call Dominance</h6>
                                        <p class="mb-0 small">Call volume $12.4M vs Put volume $10.5M. Speculation outweighs hedging.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-warning border-0">
                                <div class="d-flex align-items-start">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-warning me-2 mt-1">
                                        <path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                    </svg>
                                    <div>
                                        <h6 class="alert-heading">Watch for Reversal</h6>
                                        <p class="mb-0 small">Extreme call buying at $105k-$110k strikes may indicate overbought conditions.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Put/Call Ratio chart initialized');
});
</script>
@endsection
