@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h4 mb-1 fw-semibold">Spot BTC ETF Netflow (daily)</h2>
                    <p class="text-muted mb-0">Institutional fund flow analysis; long-term demand indicator</p>
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
                            <h6 class="card-title mb-1">Daily Netflow</h6>
                            <h4 class="mb-0 text-success">+$245M</h4>
                            <small class="text-muted">Institutional inflow</small>
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
                            <h6 class="card-title mb-1">Total AUM</h6>
                            <h4 class="mb-0 text-info">$12.8B</h4>
                            <small class="text-muted">ETF assets</small>
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
                            <h6 class="card-title mb-1">7d Netflow</h6>
                            <h4 class="mb-0 text-warning">+$1.2B</h4>
                            <small class="text-muted">Weekly inflow</small>
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
                            <h6 class="card-title mb-1">Market Share</h6>
                            <h4 class="mb-0 text-primary">3.2%</h4>
                            <small class="text-muted">Of BTC supply</small>
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
                        <h5 class="card-title mb-0">ETF Netflow Trend</h5>
                        <div class="d-flex gap-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showNetflow" checked>
                                <label class="form-check-label" for="showNetflow">Daily Netflow</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showAUM" checked>
                                <label class="form-check-label" for="showAUM">Total AUM</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showCumulative" checked>
                                <label class="form-check-label" for="showCumulative">Cumulative</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="etfNetflowChart" style="height: 400px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ETF Breakdown -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Top ETFs by Netflow</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>ETF</th>
                                    <th class="text-end">Daily Netflow</th>
                                    <th class="text-end">AUM</th>
                                    <th class="text-end">Change</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle p-1 me-2" style="width: 24px; height: 24px;">
                                                <span class="small fw-bold text-primary">B</span>
                                            </div>
                                            BITB (Bitwise)
                                        </div>
                                    </td>
                                    <td class="text-end text-success">+$89M</td>
                                    <td class="text-end">$2.1B</td>
                                    <td class="text-end text-success">+4.3%</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-info bg-opacity-10 rounded-circle p-1 me-2" style="width: 24px; height: 24px;">
                                                <span class="small fw-bold text-info">I</span>
                                            </div>
                                            IBIT (BlackRock)
                                        </div>
                                    </td>
                                    <td class="text-end text-success">+$156M</td>
                                    <td class="text-end">$4.8B</td>
                                    <td class="text-end text-success">+3.3%</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success bg-opacity-10 rounded-circle p-1 me-2" style="width: 24px; height: 24px;">
                                                <span class="small fw-bold text-success">F</span>
                                            </div>
                                            FBTC (Fidelity)
                                        </div>
                                    </td>
                                    <td class="text-end text-success">+$78M</td>
                                    <td class="text-end">$3.2B</td>
                                    <td class="text-end text-success">+2.4%</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-warning bg-opacity-10 rounded-circle p-1 me-2" style="width: 24px; height: 24px;">
                                                <span class="small fw-bold text-warning">A</span>
                                            </div>
                                            ARKB (Ark)
                                        </div>
                                    </td>
                                    <td class="text-end text-success">+$45M</td>
                                    <td class="text-end">$1.8B</td>
                                    <td class="text-end text-success">+2.5%</td>
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
                    <h5 class="card-title mb-0">Institutional Demand Indicator</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="position-relative d-inline-block">
                            <svg width="120" height="120" viewBox="0 0 120 120" class="position-relative">
                                <circle cx="60" cy="60" r="50" fill="none" stroke="#e9ecef" stroke-width="8"/>
                                <circle cx="60" cy="60" r="50" fill="none" stroke="#28a745" stroke-width="8"
                                        stroke-dasharray="314" stroke-dashoffset="62.8" transform="rotate(-90 60 60)"/>
                            </svg>
                            <div class="position-absolute top-50 start-50 translate-middle text-center">
                                <h3 class="mb-0 text-success">80%</h3>
                                <small class="text-muted">Strong</small>
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
                                <h6 class="text-warning mb-1">Moderate</h6>
                                <small class="text-muted">15%</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h6 class="text-danger mb-1">Weak</h6>
                            <small class="text-muted">5%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ETF Performance Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">ETF Performance Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-success mb-1">Total Inflow</h6>
                                <h4 class="mb-1">$8.2B</h4>
                                <small class="text-muted">Since launch</small>
                                <div class="mt-2">
                                    <span class="badge bg-success">+12.3%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-info mb-1">Avg Daily</h6>
                                <h4 class="mb-1">$156M</h4>
                                <small class="text-muted">30-day avg</small>
                                <div class="mt-2">
                                    <span class="badge bg-info">+8.5%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-warning mb-1">Outflow Days</h6>
                                <h4 class="mb-1">12</h4>
                                <small class="text-muted">Last 30 days</small>
                                <div class="mt-2">
                                    <span class="badge bg-warning">40%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-primary mb-1">Market Impact</h6>
                                <h4 class="mb-1">High</h4>
                                <small class="text-muted">Price influence</small>
                                <div class="mt-2">
                                    <span class="badge bg-primary">Strong</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ETF vs Spot Analysis -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">ETF vs Spot Performance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Metric</th>
                                    <th class="text-end">ETF</th>
                                    <th class="text-end">Spot BTC</th>
                                    <th class="text-end">Difference</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>30d Return</td>
                                    <td class="text-end text-success">+12.3%</td>
                                    <td class="text-end text-success">+11.8%</td>
                                    <td class="text-end text-success">+0.5%</td>
                                </tr>
                                <tr>
                                    <td>Volatility</td>
                                    <td class="text-end">68.5%</td>
                                    <td class="text-end">72.1%</td>
                                    <td class="text-end text-success">-3.6%</td>
                                </tr>
                                <tr>
                                    <td>Sharpe Ratio</td>
                                    <td class="text-end text-success">0.85</td>
                                    <td class="text-end text-success">0.78</td>
                                    <td class="text-end text-success">+0.07</td>
                                </tr>
                                <tr>
                                    <td>Max Drawdown</td>
                                    <td class="text-end text-warning">-15.2%</td>
                                    <td class="text-end text-warning">-18.7%</td>
                                    <td class="text-end text-success">+3.5%</td>
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
                    <h5 class="card-title mb-0">ETF Flow Patterns</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-success mb-1">Inflow Days</h6>
                                <h4 class="mb-1">18</h4>
                                <small class="text-muted">Last 30 days</small>
                                <div class="mt-2">
                                    <span class="badge bg-success">60%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-warning mb-1">Outflow Days</h6>
                                <h4 class="mb-1">12</h4>
                                <small class="text-muted">Last 30 days</small>
                                <div class="mt-2">
                                    <span class="badge bg-warning">40%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-info mb-1">Avg Inflow</h6>
                                <h4 class="mb-1">$189M</h4>
                                <small class="text-muted">Per inflow day</small>
                                <div class="mt-2">
                                    <span class="badge bg-info">Strong</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-danger mb-1">Avg Outflow</h6>
                                <h4 class="mb-1">$67M</h4>
                                <small class="text-muted">Per outflow day</small>
                                <div class="mt-2">
                                    <span class="badge bg-danger">Moderate</span>
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
                                        <h6 class="alert-heading">Strong Institutional Demand</h6>
                                        <p class="mb-0 small">Daily netflow of +$245M indicates strong institutional demand and long-term bullish sentiment.</p>
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
                                        <h6 class="alert-heading">Market Impact</h6>
                                        <p class="mb-0 small">ETFs holding 3.2% of BTC supply with $12.8B AUM creating significant market impact.</p>
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
                                        <h6 class="alert-heading">Outflow Risk</h6>
                                        <p class="mb-0 small">12 outflow days in 30 days (40%) indicates potential selling pressure during market stress.</p>
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
    console.log('Spot BTC ETF Netflow chart initialized');
});
</script>
@endsection
