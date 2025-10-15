@extends('layouts.app')

@section('content')
<div class="container-fluid" x-data="gammaExposureController()">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h4 mb-1 fw-semibold">Gamma Exposure (GEX)</h2>
                    <p class="text-muted mb-0">Price magnet levels due to dealer hedging</p>
                </div>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" style="width: auto;" x-model="selectedAsset">
                        <option value="BTC">BTC</option>
                        <option value="ETH">ETH</option>
                    </select>
                    <select class="form-select form-select-sm" style="width: auto;" x-model="selectedExchange">
                        <option value="Deribit">Deribit</option>
                        <option value="OKX">OKX</option>
                    </select>
                    <select class="form-select form-select-sm" style="width: auto;" x-model="selectedTimeframe">
                        <option value="24h">24 Hours</option>
                        <option value="7d" selected>7 Days</option>
                        <option value="30d">30 Days</option>
                        <option value="90d">90 Days</option>
                    </select>
                    <button class="btn btn-outline-secondary btn-sm" @click="loadData()" :disabled="loading">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                            <path d="M3 3v5h5"/>
                        </svg>
                        <span x-text="loading ? 'Loading...' : 'Refresh'"></span>
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
                            <h6 class="card-title mb-1">Total GEX</h6>
                            <h4 class="mb-0 text-success" x-text="formatCurrency(metrics.totalGex)">Loading...</h4>
                            <small class="text-muted">Dealer exposure</small>
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
                            <h6 class="card-title mb-1">Call GEX</h6>
                            <h4 class="mb-0 text-info" x-text="formatCurrency(metrics.callGex)">Loading...</h4>
                            <small class="text-muted">Positive gamma</small>
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
                            <h6 class="card-title mb-1">Put GEX</h6>
                            <h4 class="mb-0 text-warning" x-text="formatCurrency(metrics.putGex)">Loading...</h4>
                            <small class="text-muted">Negative gamma</small>
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
                            <h6 class="card-title mb-1">Net GEX</h6>
                            <h4 class="mb-0 text-primary" x-text="formatCurrency(metrics.netGex)">Loading...</h4>
                            <small class="text-muted" x-text="metrics.gammaType">Long gamma</small>
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
                        <h5 class="card-title mb-0">Gamma Exposure Profile</h5>
                        <div class="d-flex gap-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showTotalGEX" checked>
                                <label class="form-check-label" for="showTotalGEX">Total GEX</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showCallGEX" checked>
                                <label class="form-check-label" for="showCallGEX">Call GEX</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="showPutGEX" checked>
                                <label class="form-check-label" for="showPutGEX">Put GEX</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="gexChart" style="height: 400px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- GEX Analysis by Strike -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">GEX by Strike</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Strike</th>
                                    <th class="text-end">Call GEX</th>
                                    <th class="text-end">Put GEX</th>
                                    <th class="text-end">Net GEX</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>$95,000</td>
                                    <td class="text-end">$0.2B</td>
                                    <td class="text-end text-warning">-$0.8B</td>
                                    <td class="text-end text-warning">-$0.6B</td>
                                </tr>
                                <tr>
                                    <td>$100,000</td>
                                    <td class="text-end text-success">$0.8B</td>
                                    <td class="text-end text-warning">-$0.3B</td>
                                    <td class="text-end text-success">$0.5B</td>
                                </tr>
                                <tr>
                                    <td>$105,000</td>
                                    <td class="text-end text-success">$1.2B</td>
                                    <td class="text-end">$0.1B</td>
                                    <td class="text-end text-success">$1.3B</td>
                                </tr>
                                <tr>
                                    <td>$110,000</td>
                                    <td class="text-end text-success">$0.6B</td>
                                    <td class="text-end">$0.2B</td>
                                    <td class="text-end text-success">$0.8B</td>
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
                    <h5 class="card-title mb-0">GEX Impact Analysis</h5>
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
                                <small class="text-muted">Positive</small>
                            </div>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end">
                                <h6 class="text-success mb-1">Positive GEX</h6>
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
                            <h6 class="text-danger mb-1">Negative GEX</h6>
                            <small class="text-muted">10%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GEX Levels -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">GEX Levels & Price Magnets</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-success mb-1">Strong Support</h6>
                                <h4 class="mb-1">$100,000</h4>
                                <small class="text-muted">High positive GEX</small>
                                <div class="mt-2">
                                    <span class="badge bg-success">$0.8B</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-info mb-1">Resistance</h6>
                                <h4 class="mb-1">$105,000</h4>
                                <small class="text-muted">Maximum GEX</small>
                                <div class="mt-2">
                                    <span class="badge bg-info">$1.2B</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-warning mb-1">Weak Support</h6>
                                <h4 class="mb-1">$95,000</h4>
                                <small class="text-muted">Negative GEX</small>
                                <div class="mt-2">
                                    <span class="badge bg-warning">-$0.8B</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-primary mb-1">Next Target</h6>
                                <h4 class="mb-1">$110,000</h4>
                                <small class="text-muted">Positive GEX</small>
                                <div class="mt-2">
                                    <span class="badge bg-primary">$0.6B</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dealer Hedging Analysis -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Dealer Hedging Activity</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Price Level</th>
                                    <th class="text-end">Hedging</th>
                                    <th class="text-end">Impact</th>
                                    <th class="text-end">Direction</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>$95,000</td>
                                    <td class="text-end">$0.8B</td>
                                    <td class="text-end text-danger">Downward</td>
                                    <td class="text-end text-danger">Sell</td>
                                </tr>
                                <tr>
                                    <td>$100,000</td>
                                    <td class="text-end">$0.8B</td>
                                    <td class="text-end text-success">Upward</td>
                                    <td class="text-end text-success">Buy</td>
                                </tr>
                                <tr>
                                    <td>$105,000</td>
                                    <td class="text-end">$1.2B</td>
                                    <td class="text-end text-success">Upward</td>
                                    <td class="text-end text-success">Buy</td>
                                </tr>
                                <tr>
                                    <td>$110,000</td>
                                    <td class="text-end">$0.6B</td>
                                    <td class="text-end text-success">Upward</td>
                                    <td class="text-end text-success">Buy</td>
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
                    <h5 class="card-title mb-0">GEX Impact Zones</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-success mb-1">Positive GEX Zone</h6>
                                <h4 class="mb-1">$100k-$110k</h4>
                                <small class="text-muted">Price magnet up</small>
                                <div class="mt-2">
                                    <span class="badge bg-success">$2.6B</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-warning mb-1">Negative GEX Zone</h6>
                                <h4 class="mb-1">$95k-$100k</h4>
                                <small class="text-muted">Price magnet down</small>
                                <div class="mt-2">
                                    <span class="badge bg-warning">-$0.8B</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-info mb-1">Maximum GEX</h6>
                                <h4 class="mb-1">$105,000</h4>
                                <small class="text-muted">Strongest magnet</small>
                                <div class="mt-2">
                                    <span class="badge bg-info">$1.2B</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded">
                                <h6 class="text-primary mb-1">Support Level</h6>
                                <h4 class="mb-1">$100,000</h4>
                                <small class="text-muted">Key support</small>
                                <div class="mt-2">
                                    <span class="badge bg-primary">$0.8B</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GEX Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">GEX Analysis</h5>
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
                                <h6 class="text-success">Positive GEX Dominant</h6>
                                <p class="small text-muted mb-0">Net GEX of $1.0B creates upward price pressure and support levels.</p>
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
                                <h6 class="text-info">Dealer Hedging</h6>
                                <p class="small text-muted mb-0">Dealers buying on dips and selling on rallies, creating price stability.</p>
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
                                <h6 class="text-warning">Price Magnets</h6>
                                <p class="small text-muted mb-0">$105k strike acts as strongest price magnet with $1.2B GEX.</p>
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
                                        <h6 class="alert-heading">Positive GEX Support</h6>
                                        <p class="mb-0 small">Net GEX of $1.0B creates strong support at $100k and upward pressure.</p>
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
                                        <h6 class="alert-heading">Dealer Hedging</h6>
                                        <p class="mb-0 small">Dealers buying on dips and selling on rallies, creating price stability.</p>
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
                                        <h6 class="alert-heading">Price Magnets</h6>
                                        <p class="mb-0 small">$105k strike acts as strongest price magnet with $1.2B GEX.</p>
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

<script src="/js/options-metrics-controller.js"></script>
<script>
function gammaExposureController() {
    return {
        // API Controller instance
        apiController: null,
        
        // UI State
        selectedAsset: 'BTC',
        selectedExchange: 'Deribit',
        selectedTimeframe: '7d',
        loading: false,
        error: null,

        // Data from API
        metrics: {
            totalGex: null,
            callGex: null,
            putGex: null,
            netGex: null,
            gammaType: 'Loading...'
        },
        
        gexData: [],
        gexTimeline: [],

        async init() {
            console.log('🚀 Initializing Gamma Exposure page...');
            
            // Initialize API controller
            this.apiController = new OptionsMetricsController();
            
            // Load initial data
            await this.loadData();
            
            // Setup watchers
            this.$watch('selectedAsset', () => this.loadData());
            this.$watch('selectedExchange', () => this.loadData());
            this.$watch('selectedTimeframe', () => this.loadData());
        },

        async loadData() {
            if (!this.apiController) return;
            
            this.loading = true;
            this.error = null;
            
            try {
                console.log(`📊 Loading GEX data for ${this.selectedAsset} on ${this.selectedExchange}...`);
                
                // Fetch dealer greeks summary
                const gexSummary = await this.apiController.fetchDealerGreeksSummary(this.selectedExchange, this.selectedAsset);
                if (gexSummary && gexSummary.summary) {
                    const summary = gexSummary.summary;
                    this.metrics.totalGex = summary.total_gamma || 0;
                    this.metrics.callGex = summary.call_gamma || 0;
                    this.metrics.putGex = summary.put_gamma || 0;
                    this.metrics.netGex = summary.net_gamma || 0;
                    this.metrics.gammaType = (summary.net_gamma || 0) >= 0 ? 'Long gamma' : 'Short gamma';
                }
                
                // Fetch GEX data
                const gexData = await this.apiController.fetchDealerGreeksGex(this.selectedExchange, this.selectedAsset);
                if (gexData) {
                    this.gexData = gexData;
                }
                
                // Fetch GEX timeline
                const gexTimeline = await this.apiController.fetchDealerGreeksTimeline(this.selectedExchange, this.selectedAsset);
                if (gexTimeline) {
                    this.gexTimeline = gexTimeline;
                }
                
                console.log('✅ GEX data loaded successfully');
                
            } catch (error) {
                this.error = error.message;
                console.error('❌ Error loading GEX data:', error);
            } finally {
                this.loading = false;
            }
        },

        // Utility functions
        formatCurrency(value) {
            if (value === null || value === undefined) return 'N/A';
            if (value >= 1000000000) {
                return `$${(value / 1000000000).toFixed(1)}B`;
            } else if (value >= 1000000) {
                return `$${(value / 1000000).toFixed(1)}M`;
            } else if (value >= 1000) {
                return `$${(value / 1000).toFixed(1)}K`;
            }
            return `$${value.toFixed(0)}`;
        }
    };
}
</script>
@endsection
