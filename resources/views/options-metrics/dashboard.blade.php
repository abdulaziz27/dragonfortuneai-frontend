@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100 gap-3" x-data="optionsMetricsController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">Options Metrics</h1>
                        <span class="pulse-dot pulse-success"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Comprehensive options analytics including IV Smile & Surface, 25D Skew, Open Interest & Volume distribution, and GEX/Dealer Greeks positioning.
                    </p>
                </div>

                <!-- Global Controls -->
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <select class="form-select" style="width: 120px;" x-model="selectedAsset">
                        <option value="BTC">BTC</option>
                        <option value="ETH">ETH</option>
                    </select>

                    <select class="form-select" style="width: 140px;" x-model="selectedExchange">
                        <option value="Deribit">Deribit</option>
                        <option value="OKX">OKX</option>
                    </select>

                    <select class="form-select" style="width: 120px;" x-model="selectedTimeframe">
                        <option value="5m">5m</option>
                        <option value="15m">15m</option>
                        <option value="1h">1h</option>
                        <option value="4h">4h</option>
                        <option value="1d">1d</option>
                    </select>

                    <button class="btn btn-primary" @click="applyProfile(); refreshAll();">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                            <path d="M21 12a9 9 0 1 1-9-9c2.5 0 4.8 1 6.4 2.6M21 3v6h-6"/>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Key Metrics Overview -->
        <div class="row g-3">
            <div class="col-sm-6 col-xl-3">
                <div class="df-panel p-4 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary">ATM IV (30D)</div>
                            <div class="h2 mb-1" x-text="formatPercent(metrics.atmIv)"></div>
                        </div>
                        <div class="badge rounded-pill"
                             :class="metrics.ivChange >= 0 ? 'text-bg-success' : 'text-bg-danger'"
                             x-text="formatDelta(metrics.ivChange, 'pts')"></div>
                    </div>
                    <div class="small text-secondary mt-3" x-text="metrics.ivNarrative"></div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="df-panel p-4 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary">25D Risk Reversal</div>
                            <div class="h2 mb-1" x-text="formatDelta(metrics.skew, '%')"></div>
                        </div>
                        <div class="badge rounded-pill"
                             :class="metrics.skewChange <= 0 ? 'text-bg-warning' : 'text-bg-success'"
                             x-text="formatDelta(metrics.skewChange, 'bps')"></div>
                    </div>
                    <div class="small text-secondary mt-3" x-text="metrics.skewNarrative"></div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="df-panel p-4 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary">Total OI</div>
                            <div class="h2 mb-1" x-text="formatCompact(metrics.totalOi)"></div>
                        </div>
                        <div class="badge rounded-pill"
                             :class="metrics.oiChange >= 0 ? 'text-bg-info' : 'text-bg-secondary'"
                             x-text="formatDelta(metrics.oiChange, '%')"></div>
                    </div>
                    <div class="small text-secondary mt-3" x-text="metrics.oiNarrative"></div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="df-panel p-4 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary">Net Gamma</div>
                            <div class="h2 mb-1" x-text="formatGamma(metrics.netGamma)"></div>
                        </div>
                        <div class="badge rounded-pill"
                             :class="metrics.gammaTag === 'Short Gamma' ? 'text-bg-danger' : 'text-bg-success'"
                             x-text="metrics.gammaTag"></div>
                    </div>
                    <div class="small text-secondary mt-3" x-text="metrics.gammaNarrative"></div>
                </div>
            </div>
        </div>

        <!-- 1. IV Smile & Surface -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h5 class="mb-1">IV Smile & Surface</h5>
                            <small class="text-secondary">Implied volatility structure across strikes and tenors (ts, exchange, tenor, strike, iv) - 5-15m intervals</small>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <template x-for="tenor in smileTenors" :key="tenor">
                                <span class="badge" :style="`background-color:${smilePalette[tenor]}20;color:${smilePalette[tenor]};`" x-text="tenor"></span>
                            </template>
                            <span class="badge text-bg-info" x-text="`${selectedTimeframe} intervals`"></span>
                        </div>
                    </div>
                    <div class="flex-grow-1 position-relative" style="z-index: 1201; min-height: 400px;">
                        <canvas id="ivSmileChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. 25D Skew -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">25D Skew</h5>
                            <small class="text-secondary">Risk reversal 25 delta across time series (ts, exchange, tenor, rr25) - 5-15m intervals</small>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="badge text-bg-warning" x-text="`${selectedTimeframe} intervals`"></span>
                            <span class="badge text-bg-secondary" x-text="`Last ${getTimeRange()}h`"></span>
                        </div>
                    </div>
                    <div class="flex-grow-1 position-relative" style="z-index: 1201; min-height: 350px;">
                        <canvas id="skewChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. OI & Volume by Strike/Expiry -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">OI & Volume by Strike/Expiry</h5>
                            <small class="text-secondary">Open interest and volume distribution across strikes and expiries (ts, exchange, expiry, strike, call_oi, put_oi, call_vol, put_vol) - 15-60m intervals</small>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="badge text-bg-success" x-text="`${selectedTimeframe} intervals`"></span>
                            <span class="badge text-bg-info" x-text="`Spot ${currentProfile().spotLabel}`"></span>
                        </div>
                    </div>
                    <div class="flex-grow-1 position-relative" style="z-index: 1201; min-height: 400px;">
                        <canvas id="oiVolumeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. GEX / Dealer Greeks -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">GEX / Dealer Greeks</h5>
                            <small class="text-secondary">Gamma exposure and dealer positioning across price levels (ts, price_level, gamma_exposure) - 15-60m intervals (if vendor available)</small>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge rounded-pill text-bg-danger" x-text="formatGamma(gammaSummary.netGamma)"></span>
                            <span class="badge rounded-pill text-bg-secondary" x-text="`Pivot ${formatPrice(gammaSummary.pivot)}`"></span>
                            <span class="badge text-bg-warning" x-text="`${selectedTimeframe} intervals`"></span>
                        </div>
                    </div>
                    <div class="flex-grow-1 position-relative" style="z-index: 1201; min-height: 400px;">
                        <canvas id="gammaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script src="/js/options-metrics-controller.js"></script>
    <script>
        // Wait for OptionsMetricsController to be available
        function waitForOptionsMetricsController() {
            return new Promise((resolve) => {
                if (typeof OptionsMetricsController !== 'undefined') {
                    resolve();
                } else {
                    setTimeout(() => waitForOptionsMetricsController().then(resolve), 100);
                }
            });
        }
        
        function optionsMetricsController() {
            return {
                // API Controller instance
                apiController: null,
                
                // UI State
                selectedAsset: 'BTC',
                selectedExchange: 'Deribit',
                selectedTimeframe: '15m',
                loading: false,
                error: null,

                // Chart instances
                smileChart: null,
                skewChart: null,
                oiVolumeChart: null,
                gammaChart: null,

                // Data from API
                metrics: {
                    atmIv: null,
                    ivChange: null,
                    ivNarrative: 'Loading...',
                    skew: null,
                    skewChange: null,
                    skewNarrative: 'Loading...',
                    totalOi: null,
                    oiChange: null,
                    oiNarrative: 'Loading...',
                    netGamma: null,
                    gammaTag: 'Loading...',
                    gammaNarrative: 'Loading...'
                },
                
                // Chart data
                smileDatasets: {},
                skewDatasets: {},
                oiSeries: [],
                gammaData: { labels: [], exposures: [], netGamma: 0 },
                gammaSummary: {},

                // Chart configuration
                smileTenors: ['7D', '14D', '30D', '90D'],
                smilePalette: {
                    '7D': '#3b82f6',
                    '14D': '#10b981',
                    '30D': '#f59e0b',
                    '90D': '#8b5cf6'
                },
                relativeStrikes: [-40, -30, -20, -10, 0, 10, 20, 30, 40],
                rrTenors: ['7D', '14D', '30D', '90D'],
                intradayLabels: [],

                // Formatters
                percentFormatter: new Intl.NumberFormat('en-US', { minimumFractionDigits: 1, maximumFractionDigits: 1 }),
                compactFormatter: new Intl.NumberFormat('en-US', { notation: 'compact', maximumFractionDigits: 1 }),

                getTimeRange() {
                    const ranges = {
                        '5m': 2,
                        '15m': 6,
                        '1h': 24,
                        '4h': 48,
                        '1d': 168
                    };
                    return ranges[this.selectedTimeframe] || 6;
                },

                async init() {
                    console.log('🚀 Initializing Options Metrics Dashboard...');
                    
                    // Wait for OptionsMetricsController to be available
                    await waitForOptionsMetricsController();
                    console.log('✅ OptionsMetricsController is now available');
                    
                    // Initialize API controller
                    this.apiController = new OptionsMetricsController();
                    
                    // Generate chart labels
                    this.generateIntradayLabels();
                    
                    // Load initial data
                    await this.loadDashboardData();
                    
                    // Setup watchers
                    this.$watch('selectedAsset', () => this.loadDashboardData());
                    this.$watch('selectedExchange', () => this.loadDashboardData());
                    this.$watch('selectedTimeframe', () => {
                        this.generateIntradayLabels();
                        this.loadDashboardData();
                    });
                    
                    // Wait for Chart.js and render charts
                    this.waitForChart(() => this.renderAllCharts());
                },

                generateIntradayLabels() {
                    const points = 12;
                    const now = new Date();
                    const labels = [];
                    for (let i = points - 1; i >= 0; i--) {
                        const stamp = new Date(now.getTime() - i * 60 * 60 * 1000);
                        labels.push(stamp.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }));
                    }
                    this.intradayLabels = labels;
                },

                async loadDashboardData() {
                    if (!this.apiController) return;
                    
                    this.loading = true;
                    this.error = null;
                    
                    try {
                        console.log(`📊 Loading data for ${this.selectedAsset} on ${this.selectedExchange}...`);
                        
                        // Fetch all dashboard data
                        const data = await this.apiController.fetchDashboardData(this.selectedExchange, this.selectedAsset);
                        
                        if (data) {
                            this.updateMetricsFromAPI(data);
                            this.updateChartDataFromAPI(data);
                            console.log('✅ Dashboard data loaded successfully');
                        } else {
                            this.error = 'Failed to load data from API';
                            console.error('❌ No data received from API');
                        }
                        
                    } catch (error) {
                        this.error = error.message;
                        console.error('❌ Error loading dashboard data:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                updateMetricsFromAPI(data) {
                    // Debug logging to see actual data structure
                    console.log('🔍 Received data structure:', data);
                    console.log('🔍 IV Summary:', data.ivSummary);
                    console.log('🔍 Skew Summary:', data.skewSummary);
                    console.log('🔍 OI Summary:', data.oiSummary);
                    console.log('🔍 Dealer Greeks:', data.dealerGreeksSummary);
                    
                    // DEEP DEBUG - Show actual field names and values
                    if (data.ivSummary && data.ivSummary.data) {
                        console.log('🔬 IV Data Fields:', Object.keys(data.ivSummary.data));
                        console.log('🔬 IV Data Values:', data.ivSummary.data);
                    }
                    
                    if (data.skewSummary && data.skewSummary.data && data.skewSummary.data.length > 0) {
                        console.log('🔬 Skew Data Fields:', Object.keys(data.skewSummary.data[0]));
                        console.log('🔬 Skew Data Values:', data.skewSummary.data[0]);
                    } else {
                        console.log('⚠️ Skew data is empty array');
                    }
                    
                    if (data.oiSummary && data.oiSummary.data) {
                        console.log('🔬 OI Data Fields:', Object.keys(data.oiSummary.data));
                        console.log('🔬 OI Data Values:', data.oiSummary.data);
                    }
                    
                    if (data.dealerGreeksSummary && data.dealerGreeksSummary.data) {
                        console.log('🔬 Gamma Data Fields:', Object.keys(data.dealerGreeksSummary.data));
                        console.log('🔬 Gamma Data Values:', data.dealerGreeksSummary.data);
                    }
                    
                    // Update IV metrics
                    if (data.ivSummary && data.ivSummary.data && data.ivSummary.data.headline) {
                        const iv = data.ivSummary.data.headline;
                        this.metrics.atmIv = iv.atm_iv;
                        this.metrics.ivChange = iv.term_slope || 0;
                        this.metrics.ivNarrative = `ATM IV: ${this.formatPercent(iv.atm_iv)}. Term structure slope: ${this.formatDelta(iv.term_slope || 0, 'pts')}`;
                    }
                    
                    // Update Skew metrics
                    if (data.skewSummary && data.skewSummary.data && data.skewSummary.data.length > 0) {
                        const latest = data.skewSummary.data[0];
                        this.metrics.skew = latest.rr25?.avg || 0;
                        this.metrics.skewChange = 0; // Calculate from timeseries if available
                        this.metrics.skewNarrative = `25D Risk Reversal: ${this.formatDelta(latest.rr25?.avg || 0, '%')}`;
                    }
                    
                    // Update OI metrics
                    if (data.oiSummary && data.oiSummary.data && data.oiSummary.data.headline) {
                        const oi = data.oiSummary.data.headline;
                        this.metrics.totalOi = oi.total_oi;
                        this.metrics.oiChange = 0; // Calculate from timeseries if available
                        this.metrics.oiNarrative = `Total OI: ${this.formatCompact(oi.total_oi)}`;
                    }
                    
                    // Update Gamma metrics
                    if (data.dealerGreeksSummary && data.dealerGreeksSummary.data && data.dealerGreeksSummary.data.summary) {
                        const gamma = data.dealerGreeksSummary.data.summary;
                        this.metrics.netGamma = gamma.gamma_net || 0;
                        this.metrics.gammaTag = (gamma.gamma_net || 0) >= 0 ? 'Long Gamma' : 'Short Gamma';
                        this.metrics.gammaNarrative = `Net Gamma: ${this.formatGamma(gamma.gamma_net || 0)}`;
                    }
                },

                updateChartDataFromAPI(data) {
                    // Update IV Smile data
                    if (data.ivSmile && data.ivSmile.data) {
                        console.log('🎯 IV Smile raw data:', data.ivSmile.data);
                        this.smileDatasets = this.apiController.transformIVSmileData(data.ivSmile.data);
                        console.log('🎯 IV Smile transformed data:', this.smileDatasets);
                    } else {
                        console.log('❌ No IV Smile data available');
                    }
                    
                    // Update Skew data
                    if (data.skewHistory && data.skewHistory.data) {
                        console.log('🎯 Skew History raw data:', data.skewHistory.data);
                        this.skewDatasets = this.apiController.transformSkewData(data.skewHistory.data);
                        console.log('🎯 Skew History transformed data:', this.skewDatasets);
                    } else {
                        console.log('❌ No Skew History data available');
                    }
                    
                    // Update OI data
                    if (data.oiByExpiry && data.oiByExpiry.data) {
                        this.oiSeries = this.apiController.transformOIData(data.oiByExpiry.data);
                    }
                    
                    // Update Gamma data
                    if (data.dealerGreeksGex && data.dealerGreeksGex.data) {
                        const gammaData = this.apiController.transformGammaData(data.dealerGreeksGex.data);
                        this.gammaData = {
                            labels: gammaData.map(item => this.formatPriceLevel(item.priceLevel)),
                            exposures: gammaData.map(item => item.gammaExposure / 1000), // Convert to k
                            netGamma: gammaData.reduce((sum, item) => sum + item.gammaExposure, 0) / 1000
                        };
                    }
                },

                async refreshAll() {
                    console.log('🔄 Refreshing all data...');
                    this.destroyCharts();
                    await this.loadDashboardData();
                    this.waitForChart(() => this.renderAllCharts());
                },

                waitForChart(callback) {
                    if (typeof Chart !== 'undefined') {
                        callback();
                    } else {
                        setTimeout(() => this.waitForChart(callback), 80);
                    }
                },

                destroyCharts() {
                    if (this.smileChart) {
                        this.smileChart.destroy();
                        this.smileChart = null;
                    }
                    if (this.skewChart) {
                        this.skewChart.destroy();
                        this.skewChart = null;
                    }
                    if (this.oiVolumeChart) {
                        this.oiVolumeChart.destroy();
                        this.oiVolumeChart = null;
                    }
                    if (this.gammaChart) {
                        this.gammaChart.destroy();
                        this.gammaChart = null;
                    }
                },

                renderAllCharts() {
                    this.renderSmileChart();
                    this.renderSkewChart();
                    this.renderOiVolumeChart();
                    this.renderGammaChart();
                },

                renderSmileChart() {
                    const ctx = document.getElementById('ivSmileChart');
                    console.log('🎯 renderSmileChart called');
                    console.log('🎯 ctx:', ctx);
                    console.log('🎯 smileDatasets:', this.smileDatasets);
                    console.log('🎯 smileDatasets keys:', this.smileDatasets ? Object.keys(this.smileDatasets) : 'null');
                    
                    if (!ctx) {
                        console.log('❌ No chart context found');
                        return;
                    }
                    if (!this.smileDatasets || Object.keys(this.smileDatasets).length === 0) {
                        console.log('❌ No smile datasets available');
                        return;
                    }

                    const datasets = this.smileTenors.map((tenor) => {
                        const tenorData = this.smileDatasets[tenor] || [];
                        console.log(`🎯 Tenor ${tenor} data:`, tenorData);
                        return {
                        label: tenor,
                            data: tenorData.map(item => item.iv), // Extract IV values
                        borderColor: this.smilePalette[tenor],
                        backgroundColor: this.smilePalette[tenor] + '33',
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        fill: false
                        };
                    });

                    // Get actual strike prices from data for labels
                    const firstTenor = this.smileTenors.find(tenor => this.smileDatasets[tenor] && this.smileDatasets[tenor].length > 0);
                    const strikeLabels = firstTenor ? this.smileDatasets[firstTenor].map(item => `$${Math.round(item.strike)}`) : [];
                    
                    console.log('🎯 Strike labels:', strikeLabels);

                    this.smileChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: strikeLabels,
                            datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.2)'
                                    },
                                    ticks: {
                                        callback: (value) => `${value}%`
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.15)',
                                        borderDash: [4, 4]
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    align: 'end'
                                },
                                tooltip: {
                                    backgroundColor: '#0f172a',
                                    callbacks: {
                                        label: (context) => `${context.dataset.label}: ${context.parsed.y}%`
                                    }
                                }
                            }
                        }
                    });
                },

                renderSkewChart() {
                    const ctx = document.getElementById('skewChart');
                    console.log('🎯 renderSkewChart called');
                    console.log('🎯 ctx:', ctx);
                    console.log('🎯 skewDatasets:', this.skewDatasets);
                    console.log('🎯 skewDatasets length:', this.skewDatasets ? this.skewDatasets.length : 'null');
                    
                    if (!ctx) {
                        console.log('❌ No skew chart context found');
                        return;
                    }
                    if (!this.skewDatasets || this.skewDatasets.length === 0) {
                        console.log('❌ No skew datasets available');
                        return;
                    }

                    const colors = ['#38bdf8', '#10b981', '#f59e0b', '#8b5cf6'];
                    const datasets = this.rrTenors.map((tenor, idx) => {
                        const tenorData = this.skewDatasets.filter(item => item.tenor === tenor);
                        console.log(`🎯 Tenor ${tenor} skew data:`, tenorData);
                        return {
                        label: tenor,
                            data: tenorData.map(item => item.rr25 * 100), // Convert to percentage
                        borderColor: colors[idx % colors.length],
                        backgroundColor: colors[idx % colors.length] + '33',
                        tension: 0.35,
                        borderWidth: 2,
                        fill: false
                        };
                    });

                    // Generate labels from actual data timestamps
                    const timeLabels = this.skewDatasets.map(item => {
                        const date = new Date(item.timestamp);
                        return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                    });
                    
                    console.log('🎯 Skew time labels:', timeLabels);

                    this.skewChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: timeLabels,
                            datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.2)'
                                    },
                                    ticks: {
                                        callback: (value) => `${value}%`
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.15)',
                                        borderDash: [4, 4]
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    align: 'end'
                                },
                                tooltip: {
                                    backgroundColor: '#0f172a',
                                    callbacks: {
                                        label: (context) => `${context.dataset.label}: ${context.parsed.y}%`
                                    }
                                }
                            }
                        }
                    });
                },

                renderOiVolumeChart() {
                    const ctx = document.getElementById('oiVolumeChart');
                    if (!ctx || !this.oiSeries || this.oiSeries.length === 0) return;

                    this.oiVolumeChart = new Chart(ctx, {
                        data: {
                            labels: this.oiSeries.map(item => item.expiry),
                            datasets: [
                                {
                                    type: 'bar',
                                    label: 'Call OI',
                                    data: this.oiSeries.map(item => item.callOi),
                                    backgroundColor: 'rgba(59, 130, 246, 0.75)',
                                    borderRadius: 4,
                                    stack: 'oi'
                                },
                                {
                                    type: 'bar',
                                    label: 'Put OI',
                                    data: this.oiSeries.map(item => item.putOi),
                                    backgroundColor: 'rgba(239, 68, 68, 0.75)',
                                    borderRadius: 4,
                                    stack: 'oi'
                                },
                                {
                                    type: 'line',
                                    label: 'Total Volume',
                                    data: this.oiSeries.map(item => item.totalVol),
                                    borderColor: '#22c55e',
                                    backgroundColor: '#22c55e33',
                                    tension: 0.3,
                                    yAxisID: 'y1',
                                    fill: false,
                                    pointRadius: 3,
                                    pointHoverRadius: 5
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    stacked: true,
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.2)'
                                    },
                                    ticks: {
                                        callback: (value) => this.compactFormatter.format(value)
                                    },
                                    title: {
                                        display: true,
                                        text: 'Open Interest'
                                    }
                                },
                                y1: {
                                    beginAtZero: true,
                                    position: 'right',
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        callback: (value) => this.compactFormatter.format(value)
                                    },
                                    title: {
                                        display: true,
                                        text: 'Volume'
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.15)',
                                        borderDash: [4, 4]
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    align: 'end'
                                },
                                tooltip: {
                                    backgroundColor: '#0f172a',
                                    callbacks: {
                                        label: (context) => {
                                            const value = context.parsed.y ?? context.parsed;
                                            return `${context.dataset.label}: ${this.compactFormatter.format(value)}`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                },

                renderGammaChart() {
                    const ctx = document.getElementById('gammaChart');
                    if (!ctx || !this.gammaData || this.gammaData.exposures.length === 0) return;

                    this.gammaChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: this.gammaData.labels,
                            datasets: [
                                {
                                    label: 'Gamma Exposure',
                                    data: this.gammaData.exposures,
                                    backgroundColor: this.gammaData.exposures.map(value =>
                                        value >= 0 ? 'rgba(34, 197, 94, 0.8)' : 'rgba(239, 68, 68, 0.8)'
                                    ),
                                    borderRadius: 4
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            scales: {
                                x: {
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.2)'
                                    },
                                    ticks: {
                                        callback: (value) => `${value}k`
                                    },
                                    title: {
                                        display: true,
                                        text: 'Gamma (k)'
                                    }
                                },
                                y: {
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.15)',
                                        borderDash: [4, 4]
                                    }
                                }
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#0f172a',
                                    callbacks: {
                                        label: (context) => `Gamma: ${context.parsed.x}k`
                                    }
                                }
                            }
                        }
                    });
                },

                // Utility functions
                formatPercent(value) {
                    if (value === null || value === undefined) return 'N/A';
                    return `${parseFloat(value).toFixed(1)}%`;
                },

                formatDelta(value, suffix = '') {
                    if (value === null || value === undefined) return 'N/A';
                    let formatted;
                    if (suffix === 'bps') {
                        formatted = Math.round(value);
                    } else if (Math.abs(value) < 1) {
                        formatted = value.toFixed(2);
                    } else {
                        formatted = value.toFixed(1);
                    }
                    const sign = value > 0 ? '+' : '';
                    return `${sign}${formatted}${suffix ? ` ${suffix}` : ''}`;
                },

                formatPrice(value) {
                    if (value === null || value === undefined) return 'N/A';
                    if (value >= 1000) {
                        return `${(value / 1000).toFixed(1)}k`;
                    }
                    return value.toLocaleString();
                },

                formatGamma(value) {
                    if (value === null || value === undefined) return 'N/A';
                    const sign = value > 0 ? '+' : '';
                    return `${sign}${value}k gamma`;
                },

                formatPriceLevel(value) {
                    if (value === null || value === undefined) return 'N/A';
                    if (value >= 1000) {
                        return `${(value / 1000).toFixed(1)}k`;
                    }
                    return value.toLocaleString();
                },

                formatCompact(value) {
                    if (value === null || value === undefined) return 'N/A';
                    return this.compactFormatter.format(value);
                }
            };
        }
    </script>
@endsection
