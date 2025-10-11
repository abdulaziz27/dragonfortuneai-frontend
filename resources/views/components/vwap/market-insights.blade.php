{{--
    Komponen: Market Insights
    Menampilkan analisis trading dan insights berdasarkan VWAP

    Props:
    - $symbol: string (default: 'BTCUSDT')
    - $timeframe: string (default: '5min')
    - $exchange: string (default: 'binance')

    Interpretasi:
    - Price > VWAP → Bullish bias, buyers in control
    - Price < VWAP → Bearish bias, sellers in control
    - Price near bands → Potential reversal or breakout
--}}

<div class="df-panel p-4 h-100" x-data="marketInsightsCard('{{ $symbol ?? 'BTCUSDT' }}', '{{ $timeframe ?? '5min' }}', '{{ $exchange ?? 'binance' }}')">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">🎯 Market Insights</h5>
        <button class="btn btn-sm btn-outline-secondary" @click="refresh()" :disabled="loading">
            <span x-show="!loading">🔄</span>
            <span x-show="loading" class="spinner-border spinner-border-sm"></span>
        </button>
    </div>

    <!-- Loading State -->
    <template x-if="loading && !latestData">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-secondary mt-2 mb-0">Analyzing market...</p>
        </div>
    </template>

    <!-- Error State -->
    <template x-if="!loading && error">
        <div class="alert alert-warning text-center py-4">
            <i class="bi bi-exclamation-triangle fs-2 d-block mb-2"></i>
            <p class="mb-0" x-text="error">Unable to fetch insights</p>
        </div>
    </template>

    <!-- Insights Display -->
    <template x-if="!loading && latestData && !error">
        <div>
            <!-- Market Bias Indicator -->
            <div class="mb-4 p-4 rounded-3 text-center"
                 :class="getBiasBackgroundClass()"
                 :style="getBiasGradient()">
                <div class="small text-white text-opacity-75 mb-2">Market Bias</div>
                <div class="display-6 fw-bold text-white text-uppercase" x-text="getBiasText()">
                    Neutral
                </div>
                <div class="small text-white text-opacity-75 mt-2" x-text="getBiasDescription()">
                    Price trading near VWAP
                </div>
            </div>

            <!-- Signal Alert -->
            <div class="alert mb-3" :class="getSignalAlertClass()" role="alert">
                <div class="d-flex align-items-start gap-2">
                    <div style="font-size: 1.5rem;" x-text="getSignalIcon()">💡</div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold mb-1" x-text="getSignalTitle()">Trading Signal</div>
                        <div class="small" x-text="getSignalMessage()">Analyzing market conditions...</div>
                    </div>
                </div>
            </div>

            <!-- Price Position -->
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <div class="p-3 rounded bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-secondary">Price Position</span>
                            <span class="badge" :class="getPositionBadge()" x-text="getPricePositionText()">
                                Near VWAP
                            </span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar"
                                 :class="getPositionColor()"
                                 :style="'width: ' + getPositionPercentage() + '%'"
                                 role="progressbar"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="small text-danger">Lower Band</span>
                            <span class="small text-success">VWAP</span>
                            <span class="small text-danger">Upper Band</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 rounded bg-light text-center">
                        <div class="small text-secondary mb-1">Distance from VWAP</div>
                        <div class="h5 mb-0 fw-bold"
                             :class="getDistanceColor()"
                             x-text="getDistanceFromVWAP()">
                            0.00%
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded bg-light text-center">
                        <div class="small text-secondary mb-1">Band Width</div>
                        <div class="h5 mb-0 fw-bold"
                             :class="getBandWidthColorClass()"
                             x-text="getBandWidth()">
                            0.00%
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trading Strategy -->
            <div class="mt-3 p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border-left: 3px solid #3b82f6;">
                <div class="fw-semibold mb-2 text-primary">💡 Trading Strategy</div>
                <div class="small" x-text="getTradingStrategy()">
                    Wait for clear directional signal
                </div>
            </div>
        </div>
    </template>

    <!-- Last Updated -->
    <div class="text-center mt-3">
        <small class="text-secondary">
            Last updated: <span x-text="lastUpdate">--</span>
        </small>
    </div>
</div>

<script>
function marketInsightsCard(initialSymbol = 'BTCUSDT', initialTimeframe = '5min', initialExchange = 'binance') {
    return {
        symbol: initialSymbol,
        timeframe: initialTimeframe,
        exchange: initialExchange,
        loading: false,
        error: null,
        latestData: null,
        historicalData: [],
        lastUpdate: '--',

        init() {
            setTimeout(() => {
                this.loadData();
            }, 600);

            // Auto refresh every 30 seconds
            setInterval(() => this.loadData(), 30000);

            // Listen to global filter changes
            window.addEventListener('symbol-changed', (e) => {
                this.symbol = e.detail?.symbol || this.symbol;
                this.timeframe = e.detail?.timeframe || this.timeframe;
                this.exchange = e.detail?.exchange || this.exchange;
                this.loadData();
            });
            window.addEventListener('timeframe-changed', (e) => {
                this.timeframe = e.detail?.timeframe || this.timeframe;
                this.loadData();
            });
            window.addEventListener('exchange-changed', (e) => {
                this.exchange = e.detail?.exchange || this.exchange;
                this.loadData();
            });

            // Listen for centralized data
            window.addEventListener('vwap-data-ready', (e) => {
                if (e.detail?.latest) {
                    this.latestData = e.detail.latest;
                    this.lastUpdate = new Date().toLocaleTimeString();
                    this.error = null;
                }
                if (e.detail?.historical) {
                    this.historicalData = e.detail.historical;
                }
            });
        },

        async loadData() {
            this.loading = true;
            this.error = null;
            try {
                const params = new URLSearchParams({
                    symbol: this.symbol,
                    timeframe: this.timeframe,
                    exchange: this.exchange,
                });

                const baseMeta = document.querySelector('meta[name="api-base-url"]');
                const configuredBase = (baseMeta?.content || '').trim();
                const base = configuredBase ? (configuredBase.endsWith('/') ? configuredBase.slice(0, -1) : configuredBase) : '';
                const url = base ? `${base}/api/spot-microstructure/vwap/latest?${params}` : `/api/spot-microstructure/vwap/latest?${params}`;

                const response = await fetch(url);
                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const data = await response.json();
                this.latestData = data;
                this.lastUpdate = new Date().toLocaleTimeString();

                console.log('✅ Market insights data loaded:', data);
            } catch (error) {
                console.error('❌ Error loading market insights:', error);
                this.error = 'Unable to fetch market insights. Please try again.';
                this.latestData = null;
            } finally {
                this.loading = false;
            }
        },

        refresh() {
            this.loadData();
        },

        // Assume current price is approximately VWAP (simplified)
        // In real scenario, you'd fetch current spot price separately
        getCurrentPrice() {
            return this.latestData?.vwap || 0;
        },

        getBias() {
            if (!this.latestData) return 'neutral';
            const price = this.getCurrentPrice();
            const vwap = this.latestData.vwap;
            const upperBand = this.latestData.upper_band;
            const lowerBand = this.latestData.lower_band;

            const diffPercent = ((price - vwap) / vwap) * 100;

            if (price > upperBand) return 'strong_bullish';
            if (price > vwap && diffPercent > 0.3) return 'bullish';
            if (price < lowerBand) return 'strong_bearish';
            if (price < vwap && diffPercent < -0.3) return 'bearish';
            return 'neutral';
        },

        getBiasText() {
            const bias = this.getBias();
            const map = {
                strong_bullish: 'Strong Bull',
                bullish: 'Bullish',
                strong_bearish: 'Strong Bear',
                bearish: 'Bearish',
                neutral: 'Neutral',
            };
            return map[bias] || 'Neutral';
        },

        getBiasDescription() {
            const bias = this.getBias();
            const map = {
                strong_bullish: 'Price above upper band • Strong momentum',
                bullish: 'Price above VWAP • Buyers in control',
                strong_bearish: 'Price below lower band • Heavy selling',
                bearish: 'Price below VWAP • Sellers dominant',
                neutral: 'Price near VWAP • Balanced market',
            };
            return map[bias] || 'Price near VWAP';
        },

        getBiasBackgroundClass() {
            const bias = this.getBias();
            if (bias.includes('bullish')) return 'bg-success';
            if (bias.includes('bearish')) return 'bg-danger';
            return 'bg-secondary';
        },

        getBiasGradient() {
            const bias = this.getBias();
            if (bias === 'strong_bullish') return 'background: linear-gradient(135deg, #22c55e, #16a34a);';
            if (bias === 'bullish') return 'background: linear-gradient(135deg, #10b981, #059669);';
            if (bias === 'strong_bearish') return 'background: linear-gradient(135deg, #ef4444, #dc2626);';
            if (bias === 'bearish') return 'background: linear-gradient(135deg, #f87171, #ef4444);';
            return 'background: linear-gradient(135deg, #6b7280, #4b5563);';
        },

        getSignalIcon() {
            const bias = this.getBias();
            if (bias === 'strong_bullish') return '🚀';
            if (bias === 'bullish') return '📈';
            if (bias === 'strong_bearish') return '📉';
            if (bias === 'bearish') return '🔻';
            return '⚖️';
        },

        getSignalTitle() {
            const bias = this.getBias();
            const map = {
                strong_bullish: 'Strong Bullish Breakout',
                bullish: 'Bullish Momentum',
                strong_bearish: 'Strong Bearish Breakdown',
                bearish: 'Bearish Pressure',
                neutral: 'Range-Bound Market',
            };
            return map[bias] || 'Neutral Market';
        },

        getSignalMessage() {
            const bias = this.getBias();
            const map = {
                strong_bullish: 'Price has broken above upper VWAP band. Strong buying pressure detected. Watch for continuation or mean reversion back to VWAP.',
                bullish: 'Price trading above VWAP. Buyers are in control. Look for dip-buying opportunities near VWAP support.',
                strong_bearish: 'Price has broken below lower VWAP band. Strong selling pressure detected. Watch for capitulation or bounce back to VWAP.',
                bearish: 'Price trading below VWAP. Sellers dominating the market. Look for bounce setups to VWAP resistance.',
                neutral: 'Price trading near VWAP. No clear directional bias. Wait for breakout above/below bands or range trade within bands.',
            };
            return map[bias] || 'Market is balanced. Wait for clear signal.';
        },

        getSignalAlertClass() {
            const bias = this.getBias();
            if (bias.includes('bullish')) return 'alert-success';
            if (bias.includes('bearish')) return 'alert-danger';
            return 'alert-secondary';
        },

        getPricePositionText() {
            const bias = this.getBias();
            if (bias === 'strong_bullish') return 'Above Upper Band';
            if (bias === 'bullish') return 'Above VWAP';
            if (bias === 'strong_bearish') return 'Below Lower Band';
            if (bias === 'bearish') return 'Below VWAP';
            return 'Near VWAP';
        },

        getPositionBadge() {
            const bias = this.getBias();
            if (bias.includes('bullish')) return 'text-bg-success';
            if (bias.includes('bearish')) return 'text-bg-danger';
            return 'text-bg-secondary';
        },

        getPositionColor() {
            const bias = this.getBias();
            if (bias.includes('bullish')) return 'bg-success';
            if (bias.includes('bearish')) return 'bg-danger';
            return 'bg-secondary';
        },

        getPositionPercentage() {
            if (!this.latestData) return 50;
            const price = this.getCurrentPrice();
            const vwap = this.latestData.vwap;
            const upperBand = this.latestData.upper_band;
            const lowerBand = this.latestData.lower_band;

            // Map price position to 0-100% for progress bar
            if (price >= upperBand) return 100;
            if (price <= lowerBand) return 0;

            const range = upperBand - lowerBand;
            const position = price - lowerBand;
            return Math.max(0, Math.min(100, (position / range) * 100));
        },

        getDistanceFromVWAP() {
            if (!this.latestData) return 'N/A';
            const price = this.getCurrentPrice();
            const vwap = this.latestData.vwap;
            const distance = ((price - vwap) / vwap) * 100;
            return (distance >= 0 ? '+' : '') + distance.toFixed(2) + '%';
        },

        getDistanceColor() {
            if (!this.latestData) return 'text-secondary';
            const price = this.getCurrentPrice();
            const vwap = this.latestData.vwap;
            return price > vwap ? 'text-success' : (price < vwap ? 'text-danger' : 'text-secondary');
        },

        getBandWidth() {
            if (!this.latestData) return 'N/A';
            const width = ((this.latestData.upper_band - this.latestData.lower_band) / this.latestData.vwap) * 100;
            return width.toFixed(2) + '%';
        },

        getBandWidthColorClass() {
            if (!this.latestData) return 'text-secondary';
            const width = ((this.latestData.upper_band - this.latestData.lower_band) / this.latestData.vwap) * 100;
            if (width > 2) return 'text-danger';
            if (width > 1) return 'text-warning';
            return 'text-success';
        },

        getTradingStrategy() {
            const bias = this.getBias();
            const map = {
                strong_bullish: 'Consider taking profits or waiting for pullback to VWAP. Watch for momentum continuation above upper band.',
                bullish: 'Look for buy opportunities on dips back to VWAP. Use VWAP as dynamic support. Target upper band for exits.',
                strong_bearish: 'Consider covering shorts or waiting for bounce to VWAP. Watch for capitulation signals and volume spikes.',
                bearish: 'Look for short opportunities on bounces to VWAP. Use VWAP as dynamic resistance. Target lower band for exits.',
                neutral: 'Range trade between bands or wait for clear breakout. Buy near lower band, sell near upper band. Or wait for directional move.',
            };
            return map[bias] || 'Wait for clear directional signal before entering positions.';
        },
    };
}
</script>

