<?php $__env->startSection('content'); ?>
    

    <div class="d-flex flex-column h-100 gap-3" x-data="basisTermStructureController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">📊 Basis & Term Structure</h1>
                        <span class="pulse-dot pulse-info"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Monitor futures basis, term structure patterns, and arbitrage opportunities
                    </p>
                </div>

                <!-- Global Controls -->
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <select class="form-select" style="width: 120px;" x-model="globalSymbol" @change="updateSymbol()">
                        <option value="BTC">Bitcoin</option>
                        <option value="ETH">Ethereum</option>
                        <option value="SOL">Solana</option>
                        <option value="BNB">BNB</option>
                        <option value="XRP">XRP</option>
                        <option value="ADA">Cardano</option>
                        <option value="DOGE">Dogecoin</option>
                        <option value="MATIC">Polygon</option>
                        <option value="DOT">Polkadot</option>
                        <option value="AVAX">Avalanche</option>
                    </select>

                    <select class="form-select" style="width: 140px;" x-model="globalExchange" @change="updateExchange()">
                        <option value="Binance">Binance</option>
                        <option value="Bybit">Bybit</option>
                        <option value="OKX">OKX</option>
                        <option value="Bitget">Bitget</option>
                        <option value="Gate.io">Gate.io</option>
                        <option value="Deribit">Deribit</option>
                    </select>

                    <select class="form-select" style="width: 120px;" x-model="globalInterval" @change="updateInterval()">
                        <option value="5m">5 Minutes</option>
                        <option value="15m">15 Minutes</option>
                        <option value="1h">1 Hour</option>
                        <option value="4h">4 Hours</option>
                        <option value="1d">1 Day</option>
                    </select>

                    <!-- Data Limit -->
                    <select class="form-select" style="width: 120px;" x-model="globalLimit" @change="updateLimit()">
                        <option value="100">100</option>
                        <option value="500">500</option>
                        <option value="1000">1,000</option>
                        <option value="2000">2,000</option>
                        <option value="5000">5,000</option>
                        <option value="10000">10,000</option>
                    </select>

                    <button class="btn btn-primary" @click="refreshAll()" :disabled="globalLoading">
                        <span x-show="!globalLoading">🔄 Refresh All</span>
                        <span x-show="globalLoading" class="spinner-border spinner-border-sm"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Market Structure Overview Card (Full Width) -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4" x-data="marketStructureCard()" x-init="init()">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">Market Structure Overview</h5>
                            <small class="text-secondary">Current basis analysis and market structure</small>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge" :class="getMarketStructureBadge()" x-text="marketStructure">Loading...</span>
                            <span class="badge" :class="getTrendBadge()" x-text="trendDirection">Loading...</span>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Current Basis -->
                        <div class="col-md-3">
                            <div class="text-center p-3 rounded" style="background: rgba(59, 130, 246, 0.1);">
                                <div class="small text-secondary mb-1">Current Basis</div>
                                <div class="h4 mb-0" :class="getBasisColorClass(currentBasis)" x-text="formatBasis(currentBasis)">--</div>
                                <div class="small text-secondary">Absolute</div>
                            </div>
                        </div>

                        <!-- Annualized Basis -->
                        <div class="col-md-3">
                            <div class="text-center p-3 rounded" style="background: rgba(34, 197, 94, 0.1);">
                                <div class="small text-secondary mb-1">Annualized Basis</div>
                                <div class="h4 mb-0" :class="getBasisColorClass(annualizedBasis, 'annualized')" x-text="formatBasis(annualizedBasis, 'annualized')">--</div>
                                <div class="small text-secondary">Per Annum</div>
                            </div>
                        </div>

                        <!-- Basis Range -->
                        <div class="col-md-3">
                            <div class="text-center p-3 rounded" style="background: rgba(245, 158, 11, 0.1);">
                                <div class="small text-secondary mb-1">Basis Range</div>
                                <div class="h4 mb-0" x-text="formatBasis(basisRange)">--</div>
                                <div class="small text-secondary">Min to Max</div>
                            </div>
                        </div>

                        <!-- Volatility -->
                        <div class="col-md-3">
                            <div class="text-center p-3 rounded" style="background: rgba(139, 92, 246, 0.1);">
                                <div class="small text-secondary mb-1">Basis Volatility</div>
                                <div class="h4 mb-0" x-text="formatBasis(basisVolatility)">--</div>
                                <div class="small text-secondary">Standard Deviation</div>
                            </div>
                        </div>
                    </div>

                    <!-- Market Insights -->
                    <div class="mt-3">
                        <div class="alert" :class="getInsightAlertClass()">
                            <div class="fw-semibold small mb-1" x-text="getInsightIcon() + ' ' + getInsightTitle()">💡 Market Insight</div>
                            <div class="small" x-text="getInsightMessage()">Loading market analysis...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-3">
            <!-- Basis History Chart -->
            <div class="col-lg-8">
                <?php echo $__env->make('components.basis.history-chart', ['symbol' => 'BTC'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <!-- Quick Stats Panel -->
            <div class="col-lg-4">
                <div class="df-panel p-3 h-100" x-data="quickStatsPanel()" x-init="init()">
                    <h5 class="mb-3">📈 Quick Stats</h5>

                    <div class="d-flex flex-column gap-3">
                        <!-- Basis Distribution -->
                        <div class="stat-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-secondary">Basis Distribution</span>
                            </div>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                    :style="'width: ' + positivePercentage + '%'" :aria-valuenow="positivePercentage">
                                    <span class="fw-semibold" x-text="'Positive ' + positivePercentage + '%'">Positive 0%</span>
                                </div>
                                <div class="progress-bar bg-danger" role="progressbar"
                                    :style="'width: ' + negativePercentage + '%'" :aria-valuenow="negativePercentage">
                                    <span class="fw-semibold" x-text="'Negative ' + negativePercentage + '%'">Negative 0%</span>
                                </div>
                            </div>
                            <div class="small text-secondary mt-1">Based on historical basis values</div>
                        </div>

                        <hr class="my-2">

                        <!-- Average Basis -->
                        <div class="stat-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-secondary">Average Basis</span>
                            </div>
                            <div class="h5 mb-0" :class="averageBasis >= 0 ? 'text-success' : 'text-danger'" x-text="formatBasis(averageBasis)">--</div>
                            <div class="small text-secondary">Historical average</div>
                        </div>

                        <hr class="my-2">

                        <!-- Data Points -->
                        <div class="stat-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-secondary">Data Points</span>
                            </div>
                            <div class="h3 mb-0 fw-bold" x-text="dataPoints">--</div>
                            <div class="small text-secondary" x-text="timeRange">Loading...</div>
                        </div>

                        <hr class="my-2">

                        <!-- Trading Insight -->
                        <div class="alert mb-0" :class="getTradingInsightAlertClass()">
                            <div class="fw-semibold small mb-1" x-text="getTradingInsightIcon() + ' ' + getTradingInsightTitle()">💡 Trading Insight</div>
                            <div class="small" x-text="getTradingInsightMessage()">Loading market analysis...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Term Structure Chart (Full Width) -->
        <div class="row g-3">
            <div class="col-12">
                <?php echo $__env->make('components.basis.term-structure-chart', ['symbol' => 'BTC'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>

        <!-- Basis Analytics Table (Full Width) -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-3" x-data="analyticsTable()" x-init="init()">
                    <div class="mb-3">
                        <h5 class="mb-1">Basis Analytics Summary</h5>
                        <small class="text-secondary">Comprehensive basis analysis and insights</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Metric</th>
                                    <th>Value</th>
                                    <th>Description</th>
                                    <th>Signal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="metric in analyticsMetrics" :key="metric.name">
                                    <tr>
                                        <td class="fw-semibold" x-text="metric.name">--</td>
                                        <td>
                                            <span :class="metric.colorClass" x-text="metric.value">--</span>
                                        </td>
                                        <td class="small text-secondary" x-text="metric.description">--</td>
                                        <td>
                                            <span class="badge" :class="metric.badgeClass" x-text="metric.signal">--</span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trading Notes & Interpretation -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4">
                    <h5 class="mb-3">📚 Understanding Basis & Term Structure</h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(245, 158, 11, 0.1); border-left: 4px solid #f59e0b;">
                                <div class="fw-bold mb-2 text-warning">🟨 Contango (Positive Basis)</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Futures price > Spot price</li>
                                        <li>Market expects higher prices</li>
                                        <li>Storage costs and convenience yield</li>
                                        <li>Strategy: Consider shorting futures or spot-futures arbitrage</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e;">
                                <div class="fw-bold mb-2 text-success">🟩 Backwardation (Negative Basis)</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Spot price > Futures price</li>
                                        <li>Supply shortage or high demand</li>
                                        <li>Convenience yield exceeds storage costs</li>
                                        <li>Strategy: Consider going long futures or wait for convergence</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6;">
                                <div class="fw-bold mb-2 text-primary">⚡ Arbitrage Opportunities</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Large basis spreads → Profit potential</li>
                                        <li>Convergence as expiry approaches</li>
                                        <li>Cross-exchange basis differences</li>
                                        <li>Strategy: Monitor for entry/exit timing</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <!-- Chart.js - Load BEFORE Alpine components -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- Wait for Chart.js to load -->
    <script>
        // Ensure Chart.js is loaded before initializing components
        window.chartJsReady = new Promise((resolve) => {
            if (typeof Chart !== 'undefined') {
                resolve();
            } else {
                setTimeout(() => resolve(), 100);
            }
        });
    </script>

    <!-- Load basis controller BEFORE Alpine processes x-data -->
    <script src="<?php echo e(asset('js/basis-term-structure-controller.js')); ?>"></script>

    <!-- Market Structure Card Component -->
    <script>
        function marketStructureCard() {
            return {
                symbol: 'BTC',
                exchange: 'Binance',
                interval: '5m',
                limit: '2000',
                marketStructure: 'Loading...',
                trendDirection: 'Loading...',
                currentBasis: 0,
                annualizedBasis: 0,
                basisRange: 0,
                basisVolatility: 0,
                analyticsData: null,
                loading: false,

                init() {
                    // Get initial from parent
                    this.symbol = this.$root?.globalSymbol || 'BTC';
                    this.exchange = this.$root?.globalExchange || 'Binance';
                    this.interval = this.$root?.globalInterval || '1h';

                    this.loadData();
                    // Auto refresh every 30 seconds
                    setInterval(() => this.loadData(), 30000);

                    // Listen for filter changes
                    window.addEventListener('symbol-changed', (e) => {
                        this.symbol = e.detail?.symbol || this.symbol;
                        this.exchange = e.detail?.exchange || this.exchange;
                        this.interval = e.detail?.interval || this.interval;
                        this.limit = e.detail?.limit || this.limit;
                        this.loadData();
                    });
                    window.addEventListener('exchange-changed', (e) => {
                        this.exchange = e.detail?.exchange || this.exchange;
                        this.limit = e.detail?.limit || this.limit;
                        this.loadData();
                    });
                    window.addEventListener('interval-changed', (e) => {
                        this.interval = e.detail?.interval || this.interval;
                        this.limit = e.detail?.limit || this.limit;
                        this.loadData();
                    });
                    window.addEventListener('limit-changed', (e) => {
                        this.limit = e.detail?.limit || this.limit;
                        this.loadData();
                    });

                    // Listen for overview composite
                    window.addEventListener('basis-overview-ready', (e) => {
                        try {
                            const o = e.detail || {};
                            if (o.analytics) {
                                this.analyticsData = o.analytics;
                                this.updateMetrics();
                            }
                        } catch (err) {
                            console.warn('Overview apply failed:', err);
                        }
                    });
                },

                async loadData() {
                    this.loading = true;
                    try {
                        // Use stored symbol and exchange
                        const symbol = this.symbol;
                        const exchange = this.exchange;

                        // Load analytics data
                        await this.loadAnalyticsData(symbol, exchange);

                        this.updateMetrics();
                    } catch (error) {
                        console.error('❌ Error loading market structure:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async loadAnalyticsData(symbol, exchange) {
                    try {
                        const pair = `${symbol}USDT`;
                        const futuresSymbol = `${symbol}USDT`; // Use same symbol for futures

                        const params = new URLSearchParams({
                            exchange: exchange,
                            spot_pair: pair,
                            futures_symbol: futuresSymbol,
                            interval: '5m', // Use 5m interval that works
                            limit: this.limit
                        });

                        const response = await fetch(
                            (function(){
                                const baseMeta = document.querySelector('meta[name="api-base-url"]');
                                const configuredBase = (baseMeta?.content || '').trim();
                                const base = configuredBase ? (configuredBase.endsWith('/') ? configuredBase.slice(0, -1) : configuredBase) : '';
                                const url = base ? `${base}/api/basis/analytics?${params}` : `/api/basis/analytics?${params}`;
                                return url;
                            })());
                        const data = await response.json();
                        this.analyticsData = data;
                    } catch (error) {
                        console.error('❌ Error loading analytics data:', error);
                        this.analyticsData = null;
                    }
                },

                updateMetrics() {
                    if (this.analyticsData) {
                        this.marketStructure = this.analyticsData.market_structure || 'Unknown';
                        this.trendDirection = this.analyticsData.trend?.direction || 'Unknown';

                        const basisAbs = this.analyticsData.basis_abs || {};
                        this.currentBasis = basisAbs.current || 0;
                        this.basisRange = basisAbs.range || 0;
                        this.basisVolatility = basisAbs.std_dev || 0;

                        // Calculate annualized basis if available
                        this.annualizedBasis = this.analyticsData.basis_annualized?.current || 0;
                    }
                },

                getMarketStructureBadge() {
                    const structure = (this.marketStructure || '').toLowerCase();
                    if (structure.includes('contango')) return 'text-bg-warning';
                    if (structure.includes('backwardation')) return 'text-bg-success';
                    return 'text-bg-secondary';
                },

                getTrendBadge() {
                    const trend = (this.trendDirection || '').toLowerCase();
                    if (trend.includes('widening')) return 'text-bg-danger';
                    if (trend.includes('narrowing')) return 'text-bg-success';
                    return 'text-bg-secondary';
                },

                getBasisColorClass(basis, type = 'abs') {
                    if (type === 'annualized') {
                        if (basis > 0.05) return 'text-danger';
                        if (basis > 0.02) return 'text-warning';
                        if (basis < -0.05) return 'text-success';
                        if (basis < -0.02) return 'text-info';
                        return 'text-secondary';
                    }
                    if (basis > 0) return 'text-success';
                    if (basis < 0) return 'text-danger';
                    return 'text-secondary';
                },

                formatBasis(value, type = 'abs') {
                    if (value === null || value === undefined) return 'N/A';
                    if (type === 'annualized') {
                        const percent = (parseFloat(value) * 100).toFixed(2);
                        return (parseFloat(value) >= 0 ? '+' : '') + percent + '%';
                    }
                    return '$' + parseFloat(value).toFixed(2);
                },

                getInsightAlertClass() {
                    if (!this.analyticsData) return 'alert-info';

                    const structure = (this.marketStructure || '').toLowerCase();
                    const trend = (this.trendDirection || '').toLowerCase();

                    if (structure.includes('contango') && trend.includes('widening')) return 'alert-warning';
                    if (structure.includes('backwardation') && trend.includes('narrowing')) return 'alert-success';
                    return 'alert-info';
                },

                getInsightIcon() {
                    if (!this.analyticsData) return '💡';

                    const structure = (this.marketStructure || '').toLowerCase();
                    if (structure.includes('contango')) return '📈';
                    if (structure.includes('backwardation')) return '📉';
                    return '💡';
                },

                getInsightTitle() {
                    if (!this.analyticsData) return 'Market Analysis';

                    const structure = (this.marketStructure || '').toLowerCase();
                    if (structure.includes('contango')) return 'Contango Market';
                    if (structure.includes('backwardation')) return 'Backwardation Market';
                    return 'Neutral Market';
                },

                getInsightMessage() {
                    if (!this.analyticsData) return 'Loading market analysis...';

                    const structure = (this.marketStructure || '').toLowerCase();
                    const trend = (this.trendDirection || '').toLowerCase();
                    const currentBasis = this.currentBasis || 0;

                    if (structure.includes('contango')) {
                        return `Futures trading above spot (${this.formatBasis(currentBasis)}). Market expects higher prices. ${trend.includes('widening') ? 'Basis widening - monitor for arbitrage opportunities.' : 'Normal contango conditions.'}`;
                    }

                    if (structure.includes('backwardation')) {
                        return `Spot trading above futures (${this.formatBasis(currentBasis)}). Supply shortage or high demand. ${trend.includes('narrowing') ? 'Basis narrowing - convergence approaching.' : 'Strong backwardation signal.'}`;
                    }

                    return `Basis at ${this.formatBasis(currentBasis)}. Market showing balanced expectations. Monitor for convergence patterns.`;
                }
            };
        }
    </script>

    <!-- Quick Stats Panel Component -->
    <script>
        function quickStatsPanel() {
            return {
                symbol: 'BTC',
                exchange: 'Binance',
                interval: '5m',
                limit: '2000',
                positivePercentage: 0,
                negativePercentage: 0,
                averageBasis: 0,
                dataPoints: 0,
                timeRange: 'Loading...',
                analyticsData: null,
                timeseriesData: [],
                loading: false,

                init() {
                    // Get initial from parent
                    this.symbol = this.$root?.globalSymbol || 'BTC';
                    this.exchange = this.$root?.globalExchange || 'Binance';
                    this.interval = this.$root?.globalInterval || '1h';

                    this.loadData();
                    // Auto refresh every 30 seconds
                    setInterval(() => this.loadData(), 30000);

                    // Listen for filter changes
                    window.addEventListener('symbol-changed', (e) => {
                        this.symbol = e.detail?.symbol || this.symbol;
                        this.exchange = e.detail?.exchange || this.exchange;
                        this.interval = e.detail?.interval || this.interval;
                        this.limit = e.detail?.limit || this.limit;
                        this.loadData();
                    });
                    window.addEventListener('exchange-changed', (e) => {
                        this.exchange = e.detail?.exchange || this.exchange;
                        this.limit = e.detail?.limit || this.limit;
                        this.loadData();
                    });
                    window.addEventListener('interval-changed', (e) => {
                        this.interval = e.detail?.interval || this.interval;
                        this.limit = e.detail?.limit || this.limit;
                        this.loadData();
                    });
                    window.addEventListener('limit-changed', (e) => {
                        this.limit = e.detail?.limit || this.limit;
                        this.loadData();
                    });

                    // Listen for overview composite
                    window.addEventListener('basis-overview-ready', (e) => {
                        try {
                            const o = e.detail || {};
                            if (o.analytics) {
                                this.analyticsData = o.analytics;
                            }
                            if (Array.isArray(o.timeseries)) {
                                this.timeseriesData = o.timeseries;
                            }
                            this.calculateStats();
                        } catch (err) {
                            console.warn('Overview apply failed:', err);
                        }
                    });
                },

                async loadData() {
                    this.loading = true;
                    try {
                        // Use stored symbol and exchange
                        const symbol = this.symbol;
                        const exchange = this.exchange;

                        // Load analytics data
                        await this.loadAnalyticsData(symbol, exchange);

                        // Load history data
                        await this.loadHistoryData(symbol, exchange);

                        this.calculateStats();
                    } catch (error) {
                        console.error('❌ Error loading quick stats:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async loadAnalyticsData(symbol, exchange) {
                    try {
                        const pair = `${symbol}USDT`;
                        const futuresSymbol = `${symbol}USDT`;

                        const params = new URLSearchParams({
                            exchange: exchange,
                            spot_pair: pair,
                            futures_symbol: futuresSymbol,
                            interval: '5m', // Use 5m interval that works
                            limit: this.limit
                        });

                        const response = await fetch(
                            (function(){
                                const baseMeta = document.querySelector('meta[name="api-base-url"]');
                                const configuredBase = (baseMeta?.content || '').trim();
                                const base = configuredBase ? (configuredBase.endsWith('/') ? configuredBase.slice(0, -1) : configuredBase) : '';
                                const url = base ? `${base}/api/basis/analytics?${params}` : `/api/basis/analytics?${params}`;
                                return url;
                            })());
                        const data = await response.json();
                        this.analyticsData = data;
                    } catch (error) {
                        console.error('❌ Error loading analytics data:', error);
                        this.analyticsData = null;
                    }
                },

                async loadHistoryData(symbol, exchange) {
                    try {
                        const pair = `${symbol}USDT`;
                        const futuresSymbol = `${symbol}USDT`; // Use same symbol for futures

                        const params = new URLSearchParams({
                            exchange: exchange,
                            spot_pair: pair,
                            futures_symbol: futuresSymbol,
                            interval: '5m', // Use 5m interval that works
                            limit: this.limit
                        });

                        const response = await fetch(
                            (function(){
                                const baseMeta = document.querySelector('meta[name="api-base-url"]');
                                const configuredBase = (baseMeta?.content || '').trim();
                                const base = configuredBase ? (configuredBase.endsWith('/') ? configuredBase.slice(0, -1) : configuredBase) : '';
                                const url = base ? `${base}/api/basis/history?${params}` : `/api/basis/history?${params}`;
                                return url;
                            })());
                        const data = await response.json();
                        this.timeseriesData = data.data || [];
                    } catch (error) {
                        console.error('❌ Error loading history data:', error);
                        this.timeseriesData = [];
                    }
                },

                calculateStats() {
                    // Calculate basis distribution
                    if (this.timeseriesData.length > 0) {
                        const validBasis = this.timeseriesData
                            .map(r => parseFloat(r.basis_abs))
                            .filter(b => !isNaN(b));

                        const positiveCount = validBasis.filter(b => b > 0).length;
                        const negativeCount = validBasis.filter(b => b < 0).length;
                        const total = positiveCount + negativeCount;

                        if (total > 0) {
                            this.positivePercentage = Math.round((positiveCount / total) * 100);
                            this.negativePercentage = Math.round((negativeCount / total) * 100);
                        }

                        // Calculate average basis
                        this.averageBasis = validBasis.length ?
                            (validBasis.reduce((sum, b) => sum + b, 0) / validBasis.length) : 0;

                        // Update data points and time range
                        this.dataPoints = this.timeseriesData.length;
                        this.updateTimeRange();
                    }

                    // Update from analytics if available
                    if (this.analyticsData) {
                        const basisAbs = this.analyticsData.basis_abs || {};
                        if (basisAbs.average !== undefined) {
                            this.averageBasis = basisAbs.average;
                        }
                        if (this.analyticsData.data_points) {
                            this.dataPoints = this.analyticsData.data_points;
                        }
                        if (this.analyticsData.time_range) {
                            this.timeRange = `${Math.round(this.analyticsData.time_range.duration_hours)}h duration`;
                        }
                    }
                },

                updateTimeRange() {
                    if (this.timeseriesData.length > 0) {
                        const firstTs = Math.min(...this.timeseriesData.map(r => r.ts));
                        const lastTs = Math.max(...this.timeseriesData.map(r => r.ts));
                        const durationHours = Math.round((lastTs - firstTs) / (1000 * 60 * 60));
                        this.timeRange = `${durationHours}h duration`;
                    }
                },

                formatBasis(value) {
                    if (value === null || value === undefined) return 'N/A';
                    return '$' + parseFloat(value).toFixed(2);
                },

                getTradingInsightAlertClass() {
                    if (!this.analyticsData) return 'alert-info';

                    const structure = (this.analyticsData.market_structure || '').toLowerCase();
                    const trend = (this.analyticsData.trend?.direction || '').toLowerCase();

                    if (structure.includes('contango') && trend.includes('widening')) return 'alert-warning';
                    if (structure.includes('backwardation') && trend.includes('narrowing')) return 'alert-success';
                    return 'alert-info';
                },

                getTradingInsightIcon() {
                    if (!this.analyticsData) return '💡';

                    const structure = (this.analyticsData.market_structure || '').toLowerCase();
                    if (structure.includes('contango')) return '📈';
                    if (structure.includes('backwardation')) return '📉';
                    return '💡';
                },

                getTradingInsightTitle() {
                    if (!this.analyticsData) return 'Market Analysis';

                    const structure = (this.analyticsData.market_structure || '').toLowerCase();
                    if (structure.includes('contango')) return 'Contango Signal';
                    if (structure.includes('backwardation')) return 'Backwardation Signal';
                    return 'Neutral Market';
                },

                getTradingInsightMessage() {
                    if (!this.analyticsData) return 'Loading market analysis...';

                    const structure = (this.analyticsData.market_structure || '').toLowerCase();
                    const trend = (this.analyticsData.trend?.direction || '').toLowerCase();
                    const currentBasis = this.analyticsData.basis_abs?.current || 0;

                    if (structure.includes('contango')) {
                        return `Futures premium at ${this.formatBasis(currentBasis)}. ${trend.includes('widening') ? 'Basis widening - consider arbitrage opportunities.' : 'Normal contango conditions.'}`;
                    }

                    if (structure.includes('backwardation')) {
                        return `Spot premium at ${this.formatBasis(currentBasis)}. ${trend.includes('narrowing') ? 'Basis narrowing - convergence approaching.' : 'Strong backwardation signal.'}`;
                    }

                    return `Basis at ${this.formatBasis(currentBasis)}. Market showing balanced expectations. Monitor for convergence patterns.`;
                }
            };
        }
    </script>

    <!-- Analytics Table Component -->
    <script>
        function analyticsTable() {
            return {
                symbol: 'BTC',
                exchange: 'Binance',
                interval: '5m',
                limit: '2000',
                analyticsData: null,
                analyticsMetrics: [],
                loading: false,

                init() {
                    // Get initial from parent
                    this.symbol = this.$root?.globalSymbol || 'BTC';
                    this.exchange = this.$root?.globalExchange || 'Binance';
                    this.interval = this.$root?.globalInterval || '1h';

                    this.loadData();
                    // Auto refresh every 30 seconds
                    setInterval(() => this.loadData(), 30000);

                    // Listen for filter changes
                    window.addEventListener('symbol-changed', (e) => {
                        this.symbol = e.detail?.symbol || this.symbol;
                        this.exchange = e.detail?.exchange || this.exchange;
                        this.interval = e.detail?.interval || this.interval;
                        this.limit = e.detail?.limit || this.limit;
                        this.loadData();
                    });
                    window.addEventListener('exchange-changed', (e) => {
                        this.exchange = e.detail?.exchange || this.exchange;
                        this.limit = e.detail?.limit || this.limit;
                        this.loadData();
                    });
                    window.addEventListener('interval-changed', (e) => {
                        this.interval = e.detail?.interval || this.interval;
                        this.limit = e.detail?.limit || this.limit;
                        this.loadData();
                    });
                    window.addEventListener('limit-changed', (e) => {
                        this.limit = e.detail?.limit || this.limit;
                        this.loadData();
                    });

                    // Listen for overview composite
                    window.addEventListener('basis-overview-ready', (e) => {
                        try {
                            const o = e.detail || {};
                            if (o.analytics) {
                                this.analyticsData = o.analytics;
                                this.updateMetrics();
                            }
                        } catch (err) {
                            console.warn('Overview apply failed:', err);
                        }
                    });
                },

                async loadData() {
                    this.loading = true;
                    try {
                        // Use stored symbol and exchange
                        const symbol = this.symbol;
                        const exchange = this.exchange;

                        // Load analytics data
                        await this.loadAnalyticsData(symbol, exchange);

                        this.updateMetrics();
                    } catch (error) {
                        console.error('❌ Error loading analytics table:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async loadAnalyticsData(symbol, exchange) {
                    try {
                        const pair = `${symbol}USDT`;
                        const futuresSymbol = `${symbol}USDT`;

                        const params = new URLSearchParams({
                            exchange: exchange,
                            spot_pair: pair,
                            futures_symbol: futuresSymbol,
                            interval: '5m', // Use 5m interval that works
                            limit: this.limit
                        });

                        const response = await fetch(
                            (function(){
                                const baseMeta = document.querySelector('meta[name="api-base-url"]');
                                const configuredBase = (baseMeta?.content || '').trim();
                                const base = configuredBase ? (configuredBase.endsWith('/') ? configuredBase.slice(0, -1) : configuredBase) : '';
                                const url = base ? `${base}/api/basis/analytics?${params}` : `/api/basis/analytics?${params}`;
                                return url;
                            })());
                        const data = await response.json();
                        this.analyticsData = data;
                    } catch (error) {
                        console.error('❌ Error loading analytics data:', error);
                        this.analyticsData = null;
                    }
                },

                updateMetrics() {
                    if (!this.analyticsData) {
                        this.analyticsMetrics = [];
                        return;
                    }

                    const basisAbs = this.analyticsData.basis_abs || {};
                    const basisDist = this.analyticsData.basis_distribution || {};
                    const trend = this.analyticsData.trend || {};
                    const insights = this.analyticsData.insights || [];

                    this.analyticsMetrics = [
                        {
                            name: 'Current Basis',
                            value: this.formatBasis(basisAbs.current),
                            description: 'Current absolute basis value',
                            colorClass: this.getBasisColorClass(basisAbs.current),
                            badgeClass: this.getBasisBadgeClass(basisAbs.current),
                            signal: this.getBasisSignal(basisAbs.current)
                        },
                        {
                            name: 'Average Basis',
                            value: this.formatBasis(basisAbs.average),
                            description: 'Historical average basis',
                            colorClass: this.getBasisColorClass(basisAbs.average),
                            badgeClass: this.getBasisBadgeClass(basisAbs.average),
                            signal: this.getBasisSignal(basisAbs.average)
                        },
                        {
                            name: 'Basis Range',
                            value: this.formatBasis(basisAbs.range),
                            description: 'Range between min and max basis',
                            colorClass: 'text-info',
                            badgeClass: 'text-bg-info',
                            signal: 'Volatility'
                        },
                        {
                            name: 'Standard Deviation',
                            value: this.formatBasis(basisAbs.std_dev),
                            description: 'Basis volatility measure',
                            colorClass: 'text-warning',
                            badgeClass: 'text-bg-warning',
                            signal: 'Risk'
                        },
                        {
                            name: 'Positive Periods',
                            value: `${basisDist.positive_periods || 0} (${((basisDist.positive_pct || 0) * 100).toFixed(1)}%)`,
                            description: 'Periods with positive basis',
                            colorClass: 'text-success',
                            badgeClass: 'text-bg-success',
                            signal: 'Contango'
                        },
                        {
                            name: 'Negative Periods',
                            value: `${basisDist.negative_periods || 0} (${((basisDist.negative_pct || 0) * 100).toFixed(1)}%)`,
                            description: 'Periods with negative basis',
                            colorClass: 'text-danger',
                            badgeClass: 'text-bg-danger',
                            signal: 'Backwardation'
                        },
                        {
                            name: 'Trend Direction',
                            value: trend.direction || 'Unknown',
                            description: 'Current basis trend direction',
                            colorClass: this.getTrendColorClass(trend.direction),
                            badgeClass: this.getTrendBadgeClass(trend.direction),
                            signal: this.getTrendSignal(trend.direction)
                        },
                        {
                            name: 'Trend Magnitude',
                            value: this.formatBasis(trend.magnitude),
                            description: 'Strength of current trend',
                            colorClass: 'text-info',
                            badgeClass: 'text-bg-info',
                            signal: 'Momentum'
                        },
                        {
                            name: 'Market Structure',
                            value: this.analyticsData.market_structure || 'Unknown',
                            description: 'Overall market structure',
                            colorClass: this.getMarketStructureColorClass(this.analyticsData.market_structure),
                            badgeClass: this.getMarketStructureBadgeClass(this.analyticsData.market_structure),
                            signal: this.getMarketStructureSignal(this.analyticsData.market_structure)
                        },
                        {
                            name: 'Data Points',
                            value: this.analyticsData.data_points || 0,
                            description: 'Number of data points analyzed',
                            colorClass: 'text-secondary',
                            badgeClass: 'text-bg-secondary',
                            signal: 'Sample Size'
                        }
                    ];

                    // Add insights if available
                    if (insights.length > 0) {
                        insights.forEach((insight, index) => {
                            this.analyticsMetrics.push({
                                name: `Insight ${index + 1}`,
                                value: insight.message || 'N/A',
                                description: insight.type || 'Market insight',
                                colorClass: this.getInsightColorClass(insight.severity),
                                badgeClass: this.getInsightBadgeClass(insight.severity),
                                signal: insight.severity || 'Info'
                            });
                        });
                    }
                },

                formatBasis(value) {
                    if (value === null || value === undefined) return 'N/A';
                    return '$' + parseFloat(value).toFixed(2);
                },

                getBasisColorClass(basis) {
                    if (basis > 0) return 'text-success';
                    if (basis < 0) return 'text-danger';
                    return 'text-secondary';
                },

                getBasisBadgeClass(basis) {
                    if (basis > 0) return 'text-bg-success';
                    if (basis < 0) return 'text-bg-danger';
                    return 'text-bg-secondary';
                },

                getBasisSignal(basis) {
                    if (basis > 0) return 'Contango';
                    if (basis < 0) return 'Backwardation';
                    return 'Neutral';
                },

                getTrendColorClass(trend) {
                    const trendLower = (trend || '').toLowerCase();
                    if (trendLower.includes('widening')) return 'text-danger';
                    if (trendLower.includes('narrowing')) return 'text-success';
                    return 'text-secondary';
                },

                getTrendBadgeClass(trend) {
                    const trendLower = (trend || '').toLowerCase();
                    if (trendLower.includes('widening')) return 'text-bg-danger';
                    if (trendLower.includes('narrowing')) return 'text-bg-success';
                    return 'text-bg-secondary';
                },

                getTrendSignal(trend) {
                    const trendLower = (trend || '').toLowerCase();
                    if (trendLower.includes('widening')) return 'Expanding';
                    if (trendLower.includes('narrowing')) return 'Converging';
                    return 'Stable';
                },

                getMarketStructureColorClass(structure) {
                    const structureLower = (structure || '').toLowerCase();
                    if (structureLower.includes('contango')) return 'text-warning';
                    if (structureLower.includes('backwardation')) return 'text-success';
                    return 'text-secondary';
                },

                getMarketStructureBadgeClass(structure) {
                    const structureLower = (structure || '').toLowerCase();
                    if (structureLower.includes('contango')) return 'text-bg-warning';
                    if (structureLower.includes('backwardation')) return 'text-bg-success';
                    return 'text-bg-secondary';
                },

                getMarketStructureSignal(structure) {
                    const structureLower = (structure || '').toLowerCase();
                    if (structureLower.includes('contango')) return 'Futures Premium';
                    if (structureLower.includes('backwardation')) return 'Spot Premium';
                    return 'Balanced';
                },

                getInsightColorClass(severity) {
                    const severityLower = (severity || '').toLowerCase();
                    if (severityLower.includes('high') || severityLower.includes('danger')) return 'text-danger';
                    if (severityLower.includes('medium') || severityLower.includes('warning')) return 'text-warning';
                    return 'text-info';
                },

                getInsightBadgeClass(severity) {
                    const severityLower = (severity || '').toLowerCase();
                    if (severityLower.includes('high') || severityLower.includes('danger')) return 'text-bg-danger';
                    if (severityLower.includes('medium') || severityLower.includes('warning')) return 'text-bg-warning';
                    return 'text-bg-info';
                }
            };
        }
    </script>


    <style>
        /* Pulse animation for live indicator */
        .pulse-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }

        .pulse-info {
            background-color: #3b82f6;
            box-shadow: 0 0 0 rgba(59, 130, 246, 0.7);
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
            }
            50% {
                box-shadow: 0 0 0 8px rgba(59, 130, 246, 0);
            }
        }

        /* Stat item styling */
        .stat-item {
            padding: 0.75rem;
            border-radius: 0.5rem;
            background: rgba(var(--bs-light-rgb), 0.5);
            transition: all 0.2s ease;
        }

        .stat-item:hover {
            background: rgba(var(--bs-light-rgb), 0.8);
            transform: translateX(4px);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .derivatives-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\DATASAID\Said\Bisnis\quantwaru\frontend\dragonfortuneai-frontend\resources\views/derivatives/basis-term-structure.blade.php ENDPATH**/ ?>