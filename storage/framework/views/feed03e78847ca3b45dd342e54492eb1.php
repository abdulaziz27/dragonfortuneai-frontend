<?php $__env->startSection('content'); ?>
    

    <div class="d-flex flex-column h-100 gap-3" x-data="fundingRateController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">💰 Funding Rate Analytics</h1>
                        <span class="pulse-dot pulse-success"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Monitor funding rates to detect leverage bias, positioning crowding, and potential squeeze setups
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

                    <select class="form-select" style="width: 140px;" x-model="globalMarginType" @change="updateMarginType()">
                        <option value="">All Margin Types</option>
                        <option value="stablecoin">Stablecoin</option>
                        <option value="token">Token</option>
                    </select>

                    <select class="form-select" style="width: 120px;" x-model="globalInterval" @change="updateInterval()">
                        <option value="1h">1 Hour</option>
                        <option value="4h" disabled>4 Hours (soon)</option>
                        <option value="8h" disabled>8 Hours (soon)</option>
                        <option value="1d" disabled>1 Day (soon)</option>
                    </select>

                    <button class="btn btn-primary" @click="refreshAll()" :disabled="globalLoading">
                        <span x-show="!globalLoading">🔄 Refresh All</span>
                        <span x-show="globalLoading" class="spinner-border spinner-border-sm"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Market Bias Card (Full Width) -->
        <div class="row g-3">
            <div class="col-12">
                <?php echo $__env->make('components.funding.bias-card', ['symbol' => 'BTC'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-3">
            <!-- Exchange Comparison Chart -->
            <div class="col-lg-8">
                <?php echo $__env->make('components.funding.exchange-comparison', ['symbol' => 'BTC'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <!-- Quick Stats Panel -->
            <div class="col-lg-4">
                <div class="df-panel p-3 h-100" x-data="quickStatsPanel()" x-init="init()">
                    <h5 class="mb-3">📈 Quick Stats</h5>

                    <div class="d-flex flex-column gap-3">
                        <!-- Funding Trend (Window) -->
                        <div class="stat-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-secondary">Funding Trend (Window)</span>
                                <span class="badge" :class="getTrendBadgeClass(windowAvgFunding)" x-text="getTrendText(windowAvgFunding)">Loading...</span>
                            </div>
                            <div class="h4 mb-0" :class="getTrendClass(windowAvgFunding)" x-text="formatRate(windowAvgFunding)">--</div>
                            <div class="small text-secondary">Window avg based on Bias (interval/limit)</div>
                        </div>

                        <!-- Snapshot Avg Across Exchanges -->
                        <div class="stat-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-secondary">Snapshot Avg Across Exchanges</span>
                            </div>
                            <div class="h5 mb-0" :class="snapshotAvgFunding >= 0 ? 'text-success' : 'text-danger'" x-text="formatRate(snapshotAvgFunding)">--</div>
                            <div class="small text-secondary">Current avg across exchanges</div>
                        </div>

                        <hr class="my-2">

                        <!-- Market Sentiment -->
                        <div class="stat-item">
                            <div class="small text-secondary mb-2">Market Sentiment</div>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                    :style="'width: ' + positivePercentage + '%'" :aria-valuenow="positivePercentage">
                                    <span class="fw-semibold" x-text="'Long ' + positivePercentage + '%'">Long 0%</span>
                                </div>
                                <div class="progress-bar bg-danger" role="progressbar"
                                    :style="'width: ' + negativePercentage + '%'" :aria-valuenow="negativePercentage">
                                    <span class="fw-semibold" x-text="'Short ' + negativePercentage + '%'">Short 0%</span>
                                </div>
                            </div>
                            <div class="small text-secondary mt-1">Based on positive vs negative rates</div>
                        </div>

                        <hr class="my-2">

                        <!-- Next Funding -->
                        <div class="stat-item">
                            <div class="small text-secondary mb-2">Next Major Funding</div>
                            <div class="h3 mb-0 fw-bold" x-text="nextFundingTime">--</div>
                            <div class="small text-secondary" x-text="nextFundingDetails">Loading...</div>
                        </div>

                        <hr class="my-2">

                        <!-- Trading Insight -->
                        <div class="alert mb-0" :class="getInsightAlertClass()">
                            <div class="fw-semibold small mb-1" x-text="getInsightIcon() + ' ' + getInsightTitle()">💡 Trading Insight</div>
                            <div class="small" x-text="getInsightMessage()">Loading market analysis...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exchange Table (Full Width) -->
        <div class="row g-3">
            <div class="col-12">
                <?php echo $__env->make('components.funding.exchange-table', ['symbol' => 'BTC', 'limit' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                </div>

        <!-- Additional Charts Row -->
        <div class="row g-3">
            <!-- Historical Chart -->
            <div class="col-lg-6">
                <?php echo $__env->make('components.funding.history-chart', ['symbol' => 'BTC', 'interval' => '1h'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <!-- Analytics Insights -->
            <div class="col-lg-6">
                <?php echo $__env->make('components.funding.analytics-insights', ['symbol' => 'BTC'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>

        <!-- Heatmap Row -->
        <div class="row g-3">
            <div class="col-12">
                <?php echo $__env->make('components.funding.heatmap', ['title' => 'Exchange × Time Funding Heatmap'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
                    </div>

        <!-- Trading Notes & Interpretation -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4">
                    <h5 class="mb-3">📚 Understanding Funding Rates</h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e;">
                                <div class="fw-bold mb-2 text-success">🟩 Positive Funding (Longs Pay Shorts)</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Market too bullish / overleveraged long</li>
                                        <li>Long positions paying funding to shorts</li>
                                        <li>Risk: Long squeeze if price fails to rally</li>
                                        <li>Strategy: Consider shorting on resistance or wait for correction</li>
                                    </ul>
                    </div>
                </div>
            </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444;">
                                <div class="fw-bold mb-2 text-danger">🟥 Negative Funding (Shorts Pay Longs)</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Market too bearish / overleveraged short</li>
                                        <li>Short positions paying funding to longs</li>
                                        <li>Risk: Short squeeze on positive catalysts</li>
                                        <li>Strategy: Look for bounce setups or wait for flush</li>
                                    </ul>
                    </div>
                </div>
            </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6;">
                                <div class="fw-bold mb-2 text-primary">⚡ Exchange Spreads</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Large spreads → Arbitrage opportunities</li>
                                        <li>Negative funding on one exchange → Check for local factors</li>
                                        <li>Consistent high funding → Sustained directional bias</li>
                                        <li>Strategy: Compare with price action for confirmation</li>
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
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

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

    <!-- Load funding controller BEFORE Alpine processes x-data -->
    <script src="<?php echo e(asset('js/funding-rate-controller.js')); ?>"></script>

    <!-- Quick Stats Panel Component -->
    <script>
        function quickStatsPanel() {
            return {
                symbol: 'BTC',
                marginType: '',
                snapshotAvgFunding: 0,
                windowAvgFunding: 0,
                positivePercentage: 0,
                negativePercentage: 0,
                nextFundingTime: '--',
                nextFundingDetails: 'Loading...',
                exchangeData: [],
                biasData: null,
                loading: false,

                init() {
                    // Get initial from parent
                    this.symbol = this.$root?.globalSymbol || 'BTC';
                    this.marginType = this.$root?.globalMarginType || '';

                    this.loadData();
                    // Auto refresh every 30 seconds
                    setInterval(() => this.loadData(), 30000);

                    // Listen for filter changes
                    window.addEventListener('symbol-changed', (e) => {
                        this.symbol = e.detail?.symbol || this.symbol;
                        this.marginType = e.detail?.marginType ?? this.marginType;
                        this.loadData();
                    });
                    window.addEventListener('margin-type-changed', (e) => {
                        this.marginType = e.detail?.marginType ?? '';
                        this.loadData();
                    });

                    // Listen for overview composite (analytics + exchanges + resampled history)
                    window.addEventListener('funding-overview-ready', (e) => {
                        try {
                            const o = e.detail || {};
                            if (Array.isArray(o.exchanges)) {
                                this.exchangeData = o.exchanges;
                            }
                            if (o.analytics) {
                                const avg = o.analytics?.summary?.average ?? 0;
                                this.biasData = {
                                    bias: o.analytics?.bias?.direction || 'neutral',
                                    strength: Math.abs(o.analytics?.bias?.strength || 0),
                                    avg_funding_close: avg,
                                };
                                this.windowAvgFunding = avg;
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
                        // Use stored symbol and marginType
                        const symbol = this.symbol;

                        // Load exchange data for calculations
                        await this.loadExchangeData(symbol);

                        // Load bias data for insights
                        await this.loadBiasData(symbol);

                        this.calculateStats();
                    } catch (error) {
                        console.error('❌ Error loading quick stats:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async loadExchangeData(symbol) {
                    try {
                        const params = new URLSearchParams({
                            symbol: symbol,
                            limit: '50',
                            ...(this.marginType && { margin_type: this.marginType })
                        });
                        const response = await fetch(
                            (function(){
                                const baseMeta = document.querySelector('meta[name="api-base-url"]');
                                const configuredBase = (baseMeta?.content || '').trim();
                                const base = configuredBase ? (configuredBase.endsWith('/') ? configuredBase.slice(0, -1) : configuredBase) : '';
                                const url = base ? `${base}/api/funding-rate/exchanges?${params}` : `/api/funding-rate/exchanges?${params}`;
                                return url;
                            })());
                        const data = await response.json();
                        this.exchangeData = data.data || [];
                    } catch (error) {
                        console.error('❌ Error loading exchange data:', error);
                        this.exchangeData = [];
                    }
                },

                async loadBiasData(symbol) {
                    try {
                        const response = await fetch(
                            (function(){
                                const baseMeta = document.querySelector('meta[name="api-base-url"]');
                                const configuredBase = (baseMeta?.content || '').trim();
                                const base = configuredBase ? (configuredBase.endsWith('/') ? configuredBase.slice(0, -1) : configuredBase) : '';
                                const url = base ? `${base}/api/funding-rate/bias?symbol=${symbol}USDT&limit=1000&with_price=true` : `/api/funding-rate/bias?symbol=${symbol}USDT&limit=1000&with_price=true`;
                                return url;
                            })());
                        const data = await response.json();
                        this.biasData = data;
                    } catch (error) {
                        console.error('❌ Error loading bias data:', error);
                        this.biasData = null;
                    }
                },

                calculateStats() {
                    if (this.exchangeData.length > 0) {
                        const validRates = this.exchangeData
                            .map(e => parseFloat(e.funding_rate))
                            .filter(r => !isNaN(r));
                        this.snapshotAvgFunding = validRates.length ? (validRates.reduce((sum, r) => sum + r, 0) / validRates.length) : 0;
                    }

                    // window average from bias
                    this.windowAvgFunding = this.biasData?.avg_funding_close ?? 0;

                    // Calculate sentiment percentages
                    const positiveCount = this.exchangeData.filter(e => parseFloat(e.funding_rate) > 0).length;
                    const negativeCount = this.exchangeData.filter(e => parseFloat(e.funding_rate) < 0).length;
                    const total = positiveCount + negativeCount;

                    if (total > 0) {
                        this.positivePercentage = Math.round((positiveCount / total) * 100);
                        this.negativePercentage = Math.round((negativeCount / total) * 100);
                    }

                    // Calculate next funding time
                    this.calculateNextFunding();
                },

                calculateNextFunding() {
                    if (this.exchangeData.length === 0) return;

                    // Find the nearest funding time
                    const now = Date.now();
                    let nearestTime = null;
                    let nearestExchange = '';

                    this.exchangeData.forEach(exchange => {
                        if (exchange.next_funding_time && exchange.next_funding_time > now) {
                            if (!nearestTime || exchange.next_funding_time < nearestTime) {
                                nearestTime = exchange.next_funding_time;
                                nearestExchange = exchange.exchange;
                            }
                        }
                    });

                    if (nearestTime) {
                        const diff = nearestTime - now;
                        const hours = Math.floor(diff / (1000 * 60 * 60));
                        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

                        this.nextFundingTime = `${hours}h ${minutes}m`;

                        const time = new Date(nearestTime);
                        this.nextFundingDetails = `${time.toLocaleTimeString('en-US', {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: false
                        })} UTC • ${nearestExchange}`;
                    } else {
                        this.nextFundingTime = 'N/A';
                        this.nextFundingDetails = 'No upcoming funding times available';
                    }
                },

                getTrendBadgeClass(val) {
                    if (val > 0.0001) return 'text-bg-success';
                    if (val < -0.0001) return 'text-bg-danger';
                    return 'text-bg-secondary';
                },

                getTrendText(val) {
                    if (val > 0.0001) return 'Bullish';
                    if (val < -0.0001) return 'Bearish';
                    return 'Neutral';
                },

                getTrendClass(val) {
                    if (val > 0) return 'text-success';
                    if (val < 0) return 'text-danger';
                    return 'text-secondary';
                },

                getInsightAlertClass() {
                    if (!this.biasData) return 'alert-info';

                    const bias = (this.biasData.bias || '').toLowerCase();
                    const strength = this.biasData.strength || 0;

                    if (strength > 70) return 'alert-danger';
                    if (strength > 40) return 'alert-warning';
                    if (bias.includes('long') || bias.includes('short')) return 'alert-info';
                    return 'alert-secondary';
                },

                getInsightIcon() {
                    if (!this.biasData) return '💡';

                    const bias = (this.biasData.bias || '').toLowerCase();
                    const strength = this.biasData.strength || 0;

                    if (strength > 70) return '🚨';
                    if (bias.includes('long')) return '📈';
                    if (bias.includes('short')) return '📉';
                    return '💡';
                },

                getInsightTitle() {
                    if (!this.biasData) return 'Market Analysis';

                    const bias = (this.biasData.bias || '').toLowerCase();
                    const strength = this.biasData.strength || 0;

                    if (strength > 70) return 'High Risk Alert';
                    if (bias.includes('long')) return 'Long Dominance';
                    if (bias.includes('short')) return 'Short Pressure';
                    return 'Balanced Market';
                },

                getInsightMessage() {
                    if (!this.biasData) return 'Loading market analysis...';

                    const bias = (this.biasData.bias || '').toLowerCase();
                    const strength = this.biasData.strength || 0;
                    const avgFunding = this.biasData.avg_funding_close || 0;

                    if (strength > 70 && bias.includes('long')) {
                        return `Extreme long positioning detected (${strength.toFixed(0)}% strength). Funding rate at ${this.formatRate(avgFunding)}. High risk of long squeeze - consider taking profits.`;
                    }

                    if (strength > 70 && bias.includes('short')) {
                        return `Heavy short accumulation (${strength.toFixed(0)}% strength). Negative funding at ${this.formatRate(avgFunding)}. Watch for short squeeze on positive catalysts.`;
                    }

                    if (bias.includes('long')) {
                        return `Long positions building up with positive funding (${this.formatRate(avgFunding)}). Monitor for funding rate spikes as potential squeeze indicator.`;
                    }

                    if (bias.includes('short')) {
                        return `Short interest increasing with negative funding (${this.formatRate(avgFunding)}). Potential short squeeze setup if price bounces.`;
                    }

                    return `Market showing neutral bias with funding rate at ${this.formatRate(avgFunding)}. No extreme positioning detected - normal trading conditions.`;
                },

                formatRate(value) {
                    if (value === null || value === undefined || isNaN(value)) return 'N/A';
                    const percent = (parseFloat(value) * 100).toFixed(4);
                    return (parseFloat(value) >= 0 ? '+' : '') + percent + '%';
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

        .pulse-success {
            background-color: #22c55e;
            box-shadow: 0 0 0 rgba(34, 197, 94, 0.7);
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            }
            50% {
                box-shadow: 0 0 0 8px rgba(34, 197, 94, 0);
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\DATASAID\Said\Bisnis\quantwaru\frontend\dragonfortuneai-frontend\resources\views/derivatives/funding-rate.blade.php ENDPATH**/ ?>