@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h4 mb-1 fw-semibold">Volatility Regime Detector</h2>
                    <p class="text-muted mb-0">σ pendek vs σ panjang — klasifikasi pasar: Trending / Ranging / Event</p>
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
                            <h6 class="card-title mb-1">Current Regime</h6>
                            <h4 class="mb-0 text-success">Trending</h4>
                            <small class="text-muted">Market state</small>
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
                            <h6 class="card-title mb-1">Short Volatility</h6>
                            <h4 class="mb-0 text-info">72.3%</h4>
                            <small class="text-muted">7-day σ</small>
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
                            <h6 class="card-title mb-1">Long Volatility</h6>
                            <h4 class="mb-0 text-warning">68.5%</h4>
                            <small class="text-muted">30-day σ</small>
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
                            <h6 class="card-title mb-1">Volatility Ratio</h6>
                            <h4 class="mb-0 text-primary">1.06</h4>
                            <small class="text-muted">Short/Long</small>
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
                        <h5 class="card-title mb-0">Volatility Regime Analysis</h5>
                        <div class="d-flex gap-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showShortVol" checked>
                                <label class="form-check-label" for="showShortVol">Short Volatility</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showLongVol" checked>
                                <label class="form-check-label" for="showLongVol">Long Volatility</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showRegime" checked>
                                <label class="form-check-label" for="showRegime">Regime</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="volatilityRegimeChart" style="height: 400px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Regime Classification -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Regime Classification</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Regime</th>
                                    <th class="text-end">Vol Ratio</th>
                                    <th class="text-end">Duration</th>
                                    <th class="text-end">Signal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success bg-opacity-10 rounded-circle p-1 me-2" style="width: 24px; height: 24px;">
                                                <span class="small fw-bold text-success">T</span>
                                            </div>
                                            Trending
                                        </div>
                                    </td>
                                    <td class="text-end text-success">>1.05</td>
                                    <td class="text-end">5 days</td>
                                    <td class="text-end text-success">Active</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-info bg-opacity-10 rounded-circle p-1 me-2" style="width: 24px; height: 24px;">
                                                <span class="small fw-bold text-info">R</span>
                                            </div>
                                            Ranging
                                        </div>
                                    </td>
                                    <td class="text-end text-info">0.95-1.05</td>
                                    <td class="text-end">12 days</td>
                                    <td class="text-end text-info">Inactive</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-warning bg-opacity-10 rounded-circle p-1 me-2" style="width: 24px; height: 24px;">
                                                <span class="small fw-bold text-warning">E</span>
                                            </div>
                                            Event
                                        </div>
                                    </td>
                                    <td class="text-end text-warning"><1.05</td>
                                    <td class="text-end">2 days</td>
                                    <td class="text-end text-warning">Inactive</td>
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
                    <h5 class="card-title mb-0">Current Regime Status</h5>
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
                                <h3 class="mb-0 text-success">Trending</h3>
                                <small class="text-muted">Active</small>
                            </div>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end">
                                <h6 class="text-success mb-1">Trending</h6>
                                <small class="text-muted">80%</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h6 class="text-info mb-1">Ranging</h6>
                                <small class="text-muted">15%</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h6 class="text-warning mb-1">Event</h6>
                            <small class="text-muted">5%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Volatility Metrics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Volatility Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-success mb-1">Short Volatility</h6>
                                <h4 class="mb-1">72.3%</h4>
                                <small class="text-muted">7-day σ</small>
                                <div class="mt-2">
                                    <span class="badge bg-success">High</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-info mb-1">Long Volatility</h6>
                                <h4 class="mb-1">68.5%</h4>
                                <small class="text-muted">30-day σ</small>
                                <div class="mt-2">
                                    <span class="badge bg-info">Medium</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-warning mb-1">Volatility Ratio</h6>
                                <h4 class="mb-1">1.06</h4>
                                <small class="text-muted">Short/Long</small>
                                <div class="mt-2">
                                    <span class="badge bg-warning">Trending</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-primary mb-1">Regime Duration</h6>
                                <h4 class="mb-1">5 days</h4>
                                <small class="text-muted">Current</small>
                                <div class="mt-2">
                                    <span class="badge bg-primary">Stable</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Signal Activation -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Signal Activation Status</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Signal Type</th>
                                    <th class="text-end">Status</th>
                                    <th class="text-end">Regime</th>
                                    <th class="text-end">Last Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Trend Following</td>
                                    <td class="text-end text-success">Active</td>
                                    <td class="text-end text-success">Trending</td>
                                    <td class="text-end">2 hours ago</td>
                                </tr>
                                <tr>
                                    <td>Mean Reversion</td>
                                    <td class="text-end text-info">Inactive</td>
                                    <td class="text-end text-info">Ranging</td>
                                    <td class="text-end">5 days ago</td>
                                </tr>
                                <tr>
                                    <td>Breakout</td>
                                    <td class="text-end text-success">Active</td>
                                    <td class="text-end text-success">Trending</td>
                                    <td class="text-end">1 hour ago</td>
                                </tr>
                                <tr>
                                    <td>Volatility</td>
                                    <td class="text-end text-warning">Inactive</td>
                                    <td class="text-end text-warning">Event</td>
                                    <td class="text-end">3 days ago</td>
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
                    <h5 class="card-title mb-0">Regime Transition History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th class="text-end">From</th>
                                    <th class="text-end">To</th>
                                    <th class="text-end">Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Today</td>
                                    <td class="text-end text-info">Ranging</td>
                                    <td class="text-end text-success">Trending</td>
                                    <td class="text-end">5 days</td>
                                </tr>
                                <tr>
                                    <td>5 days ago</td>
                                    <td class="text-end text-warning">Event</td>
                                    <td class="text-end text-info">Ranging</td>
                                    <td class="text-end">2 days</td>
                                </tr>
                                <tr>
                                    <td>7 days ago</td>
                                    <td class="text-end text-success">Trending</td>
                                    <td class="text-end text-warning">Event</td>
                                    <td class="text-end">3 days</td>
                                </tr>
                                <tr>
                                    <td>10 days ago</td>
                                    <td class="text-end text-info">Ranging</td>
                                    <td class="text-end text-success">Trending</td>
                                    <td class="text-end">8 days</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Regime Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Regime Analysis</h5>
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
                                <h6 class="text-success">Trending Regime</h6>
                                <p class="small text-muted mb-0">Short volatility > Long volatility (ratio >1.05). Trend following signals active.</p>
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
                                <h6 class="text-info">Ranging Regime</h6>
                                <p class="small text-muted mb-0">Short volatility ≈ Long volatility (ratio 0.95-1.05). Mean reversion signals active.</p>
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
                                <h6 class="text-warning">Event Regime</h6>
                                <p class="small text-muted mb-0">Short volatility < Long volatility (ratio <1.05). Volatility signals active.</p>
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
                                        <h6 class="alert-heading">Trending Regime Active</h6>
                                        <p class="mb-0 small">Volatility ratio 1.06 indicates trending market. Trend following signals activated.</p>
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
                                        <h6 class="alert-heading">Signal Optimization</h6>
                                        <p class="mb-0 small">Only trending signals active in current regime. Mean reversion signals deactivated.</p>
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
                                        <h6 class="alert-heading">Regime Duration</h6>
                                        <p class="mb-0 small">Current trending regime lasting 5 days. Monitor for potential transition to ranging.</p>
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
    console.log('Volatility Regime Detector chart initialized');
});
</script>
@endsection
