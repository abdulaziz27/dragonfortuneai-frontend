@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h4 mb-1 fw-semibold">Options Skew (25d RR)</h2>
                    <p class="text-muted mb-0">Protection bias analysis - call/put preference</p>
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
                            <h6 class="card-title mb-1">25d Risk Reversal</h6>
                            <h4 class="mb-0 text-success">-0.15</h4>
                            <small class="text-muted">Put skew</small>
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
                            <h6 class="card-title mb-1">Put Skew</h6>
                            <h4 class="mb-0 text-info">0.85</h4>
                            <small class="text-muted">25d Put IV</small>
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
                            <h6 class="card-title mb-1">Call Skew</h6>
                            <h4 class="mb-0 text-warning">0.70</h4>
                            <small class="text-muted">25d Call IV</small>
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
                            <h6 class="card-title mb-1">Skew Level</h6>
                            <h4 class="mb-0 text-primary">High</h4>
                            <small class="text-muted">Protection demand</small>
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
                        <h5 class="card-title mb-0">Volatility Skew Surface</h5>
                        <div class="d-flex gap-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showSkew" checked>
                                <label class="form-check-label" for="showSkew">Vol Skew</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showRR" checked>
                                <label class="form-check-label" for="showRR">Risk Reversal</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showButterfly" checked>
                                <label class="form-check-label" for="showButterfly">Butterfly</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="skewChart" style="height: 400px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Skew Analysis by Expiry -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Skew by Expiry</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Expiry</th>
                                    <th class="text-end">25d RR</th>
                                    <th class="text-end">Put Skew</th>
                                    <th class="text-end">Call Skew</th>
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
                                    <td class="text-end text-warning">-0.25</td>
                                    <td class="text-end">0.90</td>
                                    <td class="text-end">0.65</td>
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
                                    <td class="text-end text-warning">-0.15</td>
                                    <td class="text-end">0.85</td>
                                    <td class="text-end">0.70</td>
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
                                    <td class="text-end text-warning">-0.10</td>
                                    <td class="text-end">0.80</td>
                                    <td class="text-end">0.70</td>
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
                                    <td class="text-end text-warning">-0.08</td>
                                    <td class="text-end">0.78</td>
                                    <td class="text-end">0.70</td>
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
                    <h5 class="card-title mb-0">Skew Analysis</h5>
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
                                <h3 class="mb-0 text-warning">80%</h3>
                                <small class="text-muted">Put Skew</small>
                            </div>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end">
                                <h6 class="text-danger mb-1">High Put Skew</h6>
                                <small class="text-muted">80%</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h6 class="text-warning mb-1">Medium Skew</h6>
                                <small class="text-muted">15%</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h6 class="text-success mb-1">Low Skew</h6>
                            <small class="text-muted">5%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Skew Metrics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Skew Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-warning mb-1">25d Risk Reversal</h6>
                                <h4 class="mb-1">-0.15</h4>
                                <small class="text-muted">Put bias</small>
                                <div class="mt-2">
                                    <span class="badge bg-warning">High</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-info mb-1">50d Risk Reversal</h6>
                                <h4 class="mb-1">-0.08</h4>
                                <small class="text-muted">Moderate</small>
                                <div class="mt-2">
                                    <span class="badge bg-info">Medium</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-success mb-1">75d Risk Reversal</h6>
                                <h4 class="mb-1">-0.03</h4>
                                <small class="text-muted">Low bias</small>
                                <div class="mt-2">
                                    <span class="badge bg-success">Low</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-primary mb-1">Skew Slope</h6>
                                <h4 class="mb-1">-0.12</h4>
                                <small class="text-muted">Steepness</small>
                                <div class="mt-2">
                                    <span class="badge bg-primary">Steep</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Options Flow by Skew -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Options Flow by Skew</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Strike</th>
                                    <th class="text-end">Delta</th>
                                    <th class="text-end">Put Flow</th>
                                    <th class="text-end">Call Flow</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>$95,000</td>
                                    <td class="text-end">0.25</td>
                                    <td class="text-end text-warning">$2.8M</td>
                                    <td class="text-end">$1.2M</td>
                                </tr>
                                <tr>
                                    <td>$100,000</td>
                                    <td class="text-end">0.50</td>
                                    <td class="text-end text-warning">$2.1M</td>
                                    <td class="text-end">$3.5M</td>
                                </tr>
                                <tr>
                                    <td>$105,000</td>
                                    <td class="text-end">0.75</td>
                                    <td class="text-end">$1.8M</td>
                                    <td class="text-end text-success">$4.2M</td>
                                </tr>
                                <tr>
                                    <td>$110,000</td>
                                    <td class="text-end">0.90</td>
                                    <td class="text-end">$0.9M</td>
                                    <td class="text-end text-success">$2.1M</td>
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
                    <h5 class="card-title mb-0">Skew Interpretation</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-warning mb-1">Put Skew</h6>
                                <h4 class="mb-1">0.85</h4>
                                <small class="text-muted">25d Put IV</small>
                                <div class="mt-2">
                                    <span class="badge bg-warning">High Demand</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-info mb-1">Call Skew</h6>
                                <h4 class="mb-1">0.70</h4>
                                <small class="text-muted">25d Call IV</small>
                                <div class="mt-2">
                                    <span class="badge bg-info">Lower Demand</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-success mb-1">Skew Spread</h6>
                                <h4 class="mb-1">0.15</h4>
                                <small class="text-muted">Put-Call IV</small>
                                <div class="mt-2">
                                    <span class="badge bg-success">Wide</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-primary mb-1">Skew Level</h6>
                                <h4 class="mb-1">High</h4>
                                <small class="text-muted">Protection</small>
                                <div class="mt-2">
                                    <span class="badge bg-primary">Fear</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Skew Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Skew Analysis</h5>
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
                                <h6 class="text-warning">Put Skew Dominant</h6>
                                <p class="small text-muted mb-0">25d Risk Reversal at -0.15 indicates strong put protection demand and fear sentiment.</p>
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
                                <p class="small text-muted mb-0">Short-term skew steeper than long-term, suggesting near-term event risk and uncertainty.</p>
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
                                <h6 class="text-success">Trading Opportunity</h6>
                                <p class="small text-muted mb-0">High put skew creates opportunity for put selling strategies and volatility arbitrage.</p>
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
                                        <h6 class="alert-heading">High Put Skew</h6>
                                        <p class="mb-0 small">25d Risk Reversal at -0.15 indicates strong put protection demand and fear sentiment.</p>
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
                                        <h6 class="alert-heading">Protection Demand</h6>
                                        <p class="mb-0 small">Put options trading at 0.85 IV vs Call options at 0.70 IV, indicating downside protection demand.</p>
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
                                        <h6 class="alert-heading">Trading Opportunity</h6>
                                        <p class="mb-0 small">High put skew creates opportunity for put selling strategies and volatility arbitrage.</p>
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
    console.log('Options Skew chart initialized');
});
</script>
@endsection
