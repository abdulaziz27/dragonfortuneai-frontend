@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h4 mb-1 fw-semibold">Perp Basis vs Spot Index</h2>
                    <p class="text-muted mb-0">Measure imbalance, potential squeeze & arbitrage opportunities</p>
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
                            <h6 class="card-title mb-1">Current Basis</h6>
                            <h4 class="mb-0 text-success">+0.12%</h4>
                            <small class="text-muted">Perp premium</small>
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
                            <h6 class="card-title mb-1">7d Avg Basis</h6>
                            <h4 class="mb-0 text-info">+0.08%</h4>
                            <small class="text-muted">Weekly average</small>
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
                            <h6 class="card-title mb-1">Basis Volatility</h6>
                            <h4 class="mb-0 text-warning">0.35%</h4>
                            <small class="text-muted">7d std dev</small>
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
                            <h6 class="card-title mb-1">Arbitrage Signal</h6>
                            <h4 class="mb-0 text-primary">Weak</h4>
                            <small class="text-muted">Opportunity level</small>
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
                        <h5 class="card-title mb-0">Basis Analysis</h5>
                        <div class="d-flex gap-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showBasis" checked>
                                <label class="form-check-label" for="showBasis">Basis</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showFunding" checked>
                                <label class="form-check-label" for="showFunding">Funding Rate</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showArbitrage" checked>
                                <label class="form-check-label" for="showArbitrage">Arbitrage</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="basisChart" style="height: 400px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exchange Basis Comparison -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Exchange Basis Comparison</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Exchange</th>
                                    <th class="text-end">Basis</th>
                                    <th class="text-end">Funding</th>
                                    <th class="text-end">OI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle p-1 me-2" style="width: 24px; height: 24px;">
                                                <span class="small fw-bold text-primary">B</span>
                                            </div>
                                            Binance
                                        </div>
                                    </td>
                                    <td class="text-end text-success">+0.15%</td>
                                    <td class="text-end">0.01%</td>
                                    <td class="text-end">$2.1B</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-info bg-opacity-10 rounded-circle p-1 me-2" style="width: 24px; height: 24px;">
                                                <span class="small fw-bold text-info">O</span>
                                            </div>
                                            OKX
                                        </div>
                                    </td>
                                    <td class="text-end text-success">+0.12%</td>
                                    <td class="text-end">0.01%</td>
                                    <td class="text-end">$1.8B</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success bg-opacity-10 rounded-circle p-1 me-2" style="width: 24px; height: 24px;">
                                                <span class="small fw-bold text-success">B</span>
                                            </div>
                                            Bybit
                                        </div>
                                    </td>
                                    <td class="text-end text-success">+0.08%</td>
                                    <td class="text-end">0.01%</td>
                                    <td class="text-end">$1.2B</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-warning bg-opacity-10 rounded-circle p-1 me-2" style="width: 24px; height: 24px;">
                                                <span class="small fw-bold text-warning">D</span>
                                            </div>
                                            Deribit
                                        </div>
                                    </td>
                                    <td class="text-end text-warning">-0.05%</td>
                                    <td class="text-end">-0.01%</td>
                                    <td class="text-end">$0.8B</td>
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
                    <h5 class="card-title mb-0">Arbitrage Opportunity</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="position-relative d-inline-block">
                            <svg width="120" height="120" viewBox="0 0 120 120" class="position-relative">
                                <circle cx="60" cy="60" r="50" fill="none" stroke="#e9ecef" stroke-width="8"/>
                                <circle cx="60" cy="60" r="50" fill="none" stroke="#ffc107" stroke-width="8"
                                        stroke-dasharray="314" stroke-dashoffset="251.2" transform="rotate(-90 60 60)"/>
                            </svg>
                            <div class="position-absolute top-50 start-50 translate-middle text-center">
                                <h3 class="mb-0 text-warning">20%</h3>
                                <small class="text-muted">Weak</small>
                            </div>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end">
                                <h6 class="text-success mb-1">Strong</h6>
                                <small class="text-muted">80%</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h6 class="text-warning mb-1">Weak</h6>
                                <small class="text-muted">20%</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h6 class="text-danger mb-1">None</h6>
                            <small class="text-muted">0%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Basis Metrics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Basis Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-success mb-1">Current Basis</h6>
                                <h4 class="mb-1">+0.12%</h4>
                                <small class="text-muted">Perp premium</small>
                                <div class="mt-2">
                                    <span class="badge bg-success">Positive</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-info mb-1">7d Average</h6>
                                <h4 class="mb-1">+0.08%</h4>
                                <small class="text-muted">Weekly avg</small>
                                <div class="mt-2">
                                    <span class="badge bg-info">Stable</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-warning mb-1">Volatility</h6>
                                <h4 class="mb-1">0.35%</h4>
                                <small class="text-muted">7d std dev</small>
                                <div class="mt-2">
                                    <span class="badge bg-warning">Moderate</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-primary mb-1">Arbitrage</h6>
                                <h4 class="mb-1">Weak</h4>
                                <small class="text-muted">Opportunity</small>
                                <div class="mt-2">
                                    <span class="badge bg-primary">Low</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Basis Analysis -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Basis vs Funding Rate</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Timeframe</th>
                                    <th class="text-end">Basis</th>
                                    <th class="text-end">Funding</th>
                                    <th class="text-end">Correlation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1 Hour</td>
                                    <td class="text-end text-success">+0.12%</td>
                                    <td class="text-end">0.01%</td>
                                    <td class="text-end text-success">0.85</td>
                                </tr>
                                <tr>
                                    <td>4 Hours</td>
                                    <td class="text-end text-success">+0.10%</td>
                                    <td class="text-end">0.01%</td>
                                    <td class="text-end text-success">0.78</td>
                                </tr>
                                <tr>
                                    <td>24 Hours</td>
                                    <td class="text-end text-success">+0.08%</td>
                                    <td class="text-end">0.01%</td>
                                    <td class="text-end text-success">0.72</td>
                                </tr>
                                <tr>
                                    <td>7 Days</td>
                                    <td class="text-end text-success">+0.08%</td>
                                    <td class="text-end">0.01%</td>
                                    <td class="text-end text-success">0.68</td>
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
                    <h5 class="card-title mb-0">Squeeze Risk Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-success mb-1">Long Squeeze</h6>
                                <h4 class="mb-1">Low</h4>
                                <small class="text-muted">Risk level</small>
                                <div class="mt-2">
                                    <span class="badge bg-success">15%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-warning mb-1">Short Squeeze</h6>
                                <h4 class="mb-1">Medium</h4>
                                <small class="text-muted">Risk level</small>
                                <div class="mt-2">
                                    <span class="badge bg-warning">35%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-info mb-1">Arbitrage</h6>
                                <h4 class="mb-1">Weak</h4>
                                <small class="text-muted">Opportunity</small>
                                <div class="mt-2">
                                    <span class="badge bg-info">20%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-primary mb-1">Market Balance</h6>
                                <h4 class="mb-1">Good</h4>
                                <small class="text-muted">Overall</small>
                                <div class="mt-2">
                                    <span class="badge bg-primary">75%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Basis Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Basis Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <div class="mb-3">
                                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-success">
                                        <path d="M3 3v18h18"/>
                                        <path d="M7 12l3-3 3 3 5-5"/>
                                    </svg>
                                </div>
                                <h6 class="text-success">Positive Basis</h6>
                                <p class="small text-muted mb-0">Perpetual trading at +0.12% premium to spot, indicating bullish sentiment and demand.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <div class="mb-3">
                                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-info">
                                        <path d="M3 3v18h18"/>
                                        <path d="M7 12l3-3 3 3 5-5"/>
                                    </svg>
                                </div>
                                <h6 class="text-info">Stable Funding</h6>
                                <p class="small text-muted mb-0">Funding rate at 0.01% indicates balanced long/short positions with low squeeze risk.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <div class="mb-3">
                                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-warning">
                                        <path d="M3 3v18h18"/>
                                        <path d="M7 12l3-3 3 3 5-5"/>
                                    </svg>
                                </div>
                                <h6 class="text-warning">Arbitrage Opportunity</h6>
                                <p class="small text-muted mb-0">Weak arbitrage signal suggests efficient market pricing with limited profit opportunities.</p>
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
                                        <h6 class="alert-heading">Positive Basis</h6>
                                        <p class="mb-0 small">Perpetual trading at +0.12% premium to spot, indicating bullish sentiment and demand.</p>
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
                                        <h6 class="alert-heading">Stable Funding</h6>
                                        <p class="mb-0 small">Funding rate at 0.01% indicates balanced long/short positions with low squeeze risk.</p>
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
                                        <h6 class="alert-heading">Arbitrage Opportunity</h6>
                                        <p class="mb-0 small">Weak arbitrage signal suggests efficient market pricing with limited profit opportunities.</p>
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
    console.log('Perp Basis vs Spot Index chart initialized');
});
</script>
@endsection
