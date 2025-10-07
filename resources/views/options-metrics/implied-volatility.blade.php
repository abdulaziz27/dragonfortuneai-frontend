@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h4 mb-1 fw-semibold">Implied Volatility (IV)</h2>
                    <p class="text-muted mb-0">Market expectation of volatility; measure of fear/greed</p>
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
                            <h6 class="card-title mb-1">Current IV</h6>
                            <h4 class="mb-0 text-success">68.5%</h4>
                            <small class="text-muted">30-day ATM</small>
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
                            <h6 class="card-title mb-1">IV Rank</h6>
                            <h4 class="mb-0 text-info">75%</h4>
                            <small class="text-muted">Percentile</small>
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
                            <h6 class="card-title mb-1">Fear/Greed Index</h6>
                            <h4 class="mb-0 text-warning">Fear</h4>
                            <small class="text-muted">Market sentiment</small>
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
                            <h6 class="card-title mb-1">IV Change (7d)</h6>
                            <h4 class="mb-0 text-primary">+12.3%</h4>
                            <small class="text-muted">Weekly change</small>
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
                        <h5 class="card-title mb-0">Implied Volatility Surface</h5>
                        <div class="d-flex gap-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showIV" checked>
                                <label class="form-check-label" for="showIV">Current IV</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showHV" checked>
                                <label class="form-check-label" for="showHV">Historical Vol</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showSkew" checked>
                                <label class="form-check-label" for="showSkew">Vol Skew</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="ivChart" style="height: 400px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- IV Analysis by Expiry -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">IV by Expiry</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Expiry</th>
                                    <th class="text-end">IV</th>
                                    <th class="text-end">Change</th>
                                    <th class="text-end">Volume</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle p-1 me-2" style="width: 24px; height: 24px;">
                                                <span class="small fw-bold text-primary">7</span>
                                            </div>
                                            7 Days
                                        </div>
                                    </td>
                                    <td class="text-end">72.3%</td>
                                    <td class="text-end text-success">+8.5%</td>
                                    <td class="text-end">$2.1M</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-info bg-opacity-10 rounded-circle p-1 me-2" style="width: 24px; height: 24px;">
                                                <span class="small fw-bold text-info">30</span>
                                            </div>
                                            30 Days
                                        </div>
                                    </td>
                                    <td class="text-end">68.5%</td>
                                    <td class="text-end text-success">+12.3%</td>
                                    <td class="text-end">$5.8M</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success bg-opacity-10 rounded-circle p-1 me-2" style="width: 24px; height: 24px;">
                                                <span class="small fw-bold text-success">60</span>
                                            </div>
                                            60 Days
                                        </div>
                                    </td>
                                    <td class="text-end">65.2%</td>
                                    <td class="text-end text-warning">+5.7%</td>
                                    <td class="text-end">$3.2M</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-warning bg-opacity-10 rounded-circle p-1 me-2" style="width: 24px; height: 24px;">
                                                <span class="small fw-bold text-warning">90</span>
                                            </div>
                                            90 Days
                                        </div>
                                    </td>
                                    <td class="text-end">62.8%</td>
                                    <td class="text-end text-warning">+3.1%</td>
                                    <td class="text-end">$1.9M</td>
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
                    <h5 class="card-title mb-0">Fear/Greed Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="position-relative d-inline-block">
                            <svg width="120" height="120" viewBox="0 0 120 120" class="position-relative">
                                <circle cx="60" cy="60" r="50" fill="none" stroke="#e9ecef" stroke-width="8"/>
                                <circle cx="60" cy="60" r="50" fill="none" stroke="#ffc107" stroke-width="8"
                                        stroke-dasharray="314" stroke-dashoffset="188.4" transform="rotate(-90 60 60)"/>
                            </svg>
                            <div class="position-absolute top-50 start-50 translate-middle text-center">
                                <h3 class="mb-0 text-warning">35</h3>
                                <small class="text-muted">Fear</small>
                            </div>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="border-end">
                                <h6 class="text-danger mb-1">Extreme Fear</h6>
                                <small class="text-muted">0-25</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border-end">
                                <h6 class="text-warning mb-1">Fear</h6>
                                <small class="text-muted">25-45</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border-end">
                                <h6 class="text-info mb-1">Neutral</h6>
                                <small class="text-muted">45-55</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <h6 class="text-success mb-1">Greed</h6>
                            <small class="text-muted">55-100</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- IV Skew Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Volatility Skew Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <div class="mb-3">
                                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-warning">
                                        <path d="M3 3v18h18"/>
                                        <path d="M7 12l3-3 3 3 5-5"/>
                                    </svg>
                                </div>
                                <h6 class="text-warning">Put Skew</h6>
                                <p class="small text-muted mb-0">Put options trading at higher IV than calls, indicating fear and downside protection demand.</p>
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
                                <h6 class="text-info">Term Structure</h6>
                                <p class="small text-muted mb-0">Short-term IV higher than long-term, suggesting near-term uncertainty and event risk.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <div class="mb-3">
                                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-success">
                                        <path d="M3 3v18h18"/>
                                        <path d="M7 12l3-3 3 3 5-5"/>
                                    </svg>
                                </div>
                                <h6 class="text-success">Mean Reversion</h6>
                                <p class="small text-muted mb-0">IV at 75th percentile suggests potential mean reversion opportunity for volatility sellers.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Options Volume Analysis -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Options Volume by Strike</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Strike</th>
                                    <th class="text-end">Call Volume</th>
                                    <th class="text-end">Put Volume</th>
                                    <th class="text-end">IV</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>$95,000</td>
                                    <td class="text-end">1,234</td>
                                    <td class="text-end">2,567</td>
                                    <td class="text-end">75.2%</td>
                                </tr>
                                <tr>
                                    <td>$100,000</td>
                                    <td class="text-end">3,456</td>
                                    <td class="text-end">4,321</td>
                                    <td class="text-end">68.5%</td>
                                </tr>
                                <tr>
                                    <td>$105,000</td>
                                    <td class="text-end">2,789</td>
                                    <td class="text-end">1,876</td>
                                    <td class="text-end">62.3%</td>
                                </tr>
                                <tr>
                                    <td>$110,000</td>
                                    <td class="text-end">1,543</td>
                                    <td class="text-end">987</td>
                                    <td class="text-end">58.7%</td>
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
                    <h5 class="card-title mb-0">IV Percentile Ranges</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-danger mb-1">High IV</h6>
                                <h4 class="mb-1">80%+</h4>
                                <small class="text-muted">Percentile</small>
                                <div class="mt-2">
                                    <span class="badge bg-danger">Sell Vol</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-warning mb-1">Medium IV</h6>
                                <h4 class="mb-1">50-80%</h4>
                                <small class="text-muted">Percentile</small>
                                <div class="mt-2">
                                    <span class="badge bg-warning">Neutral</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-info mb-1">Low IV</h6>
                                <h4 class="mb-1">20-50%</h4>
                                <small class="text-muted">Percentile</small>
                                <div class="mt-2">
                                    <span class="badge bg-info">Buy Vol</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-success mb-1">Very Low IV</h6>
                                <h4 class="mb-1">0-20%</h4>
                                <small class="text-muted">Percentile</small>
                                <div class="mt-2">
                                    <span class="badge bg-success">Strong Buy</span>
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
                            <div class="alert alert-warning border-0">
                                <div class="d-flex align-items-start">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-warning me-2 mt-1">
                                        <path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                    </svg>
                                    <div>
                                        <h6 class="alert-heading">High Fear Level</h6>
                                        <p class="mb-0 small">IV at 75th percentile with fear sentiment. Consider volatility selling strategies.</p>
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
                                        <h6 class="alert-heading">Put Skew Dominant</h6>
                                        <p class="mb-0 small">Put options trading at higher IV than calls, indicating downside protection demand.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-success border-0">
                                <div class="d-flex align-items-start">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-success me-2 mt-1">
                                        <path d="M9 12l2 2 4-4"/>
                                        <path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"/>
                                    </svg>
                                    <div>
                                        <h6 class="alert-heading">Mean Reversion Setup</h6>
                                        <p class="mb-0 small">IV at 75th percentile suggests potential mean reversion opportunity for volatility sellers.</p>
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
    console.log('Implied Volatility chart initialized');
});
</script>
@endsection
