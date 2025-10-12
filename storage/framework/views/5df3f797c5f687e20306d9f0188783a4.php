

<div class="df-panel p-4 h-100" x-data="latestStatsCard('<?php echo e($symbol ?? 'BTCUSDT'); ?>', '<?php echo e($timeframe ?? '5min'); ?>', '<?php echo e($exchange ?? 'binance'); ?>')">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <h5 class="mb-0">📊 Latest VWAP Statistics</h5>
            <span class="badge text-bg-secondary" x-text="symbol">BTCUSDT</span>
        </div>
        <button class="btn btn-sm btn-outline-secondary" @click="refresh()" :disabled="loading">
            <span x-show="!loading">🔄</span>
            <span x-show="loading" class="spinner-border spinner-border-sm"></span>
        </button>
    </div>

    <!-- Loading State -->
    <template x-if="loading && !data">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-secondary mt-2 mb-0">Loading VWAP data...</p>
        </div>
    </template>

    <!-- Error State -->
    <template x-if="!loading && error">
        <div class="alert alert-warning text-center py-4">
            <i class="bi bi-exclamation-triangle fs-2 d-block mb-2"></i>
            <p class="mb-0" x-text="error">Unable to fetch data</p>
        </div>
    </template>

    <!-- Data Display -->
    <template x-if="!loading && data && !error">
        <div>
            <!-- Main VWAP Display -->
            <div class="text-center mb-4 p-4 rounded-3"
                 style="background: linear-gradient(135deg, #10b981, #059669);">
                <div class="small text-white text-opacity-75 mb-2">Current VWAP</div>
                <div class="display-4 fw-bold text-white" x-text="formatPrice(data.vwap)">
                    $0.00
                </div>
                <div class="small text-white text-opacity-75 mt-2" x-text="formatTimestamp(data.timestamp)">
                    --
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border-left: 3px solid #ef4444;">
                        <div class="small text-secondary mb-1">Upper Band</div>
                        <div class="h5 mb-0 text-danger fw-bold" x-text="formatPrice(data.upper_band)">
                            $0.00
                        </div>
                        <div class="small text-secondary mt-1" x-text="calculateBandDistance(data.upper_band, data.vwap)">
                            +0%
                        </div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border-left: 3px solid #ef4444;">
                        <div class="small text-secondary mb-1">Lower Band</div>
                        <div class="h5 mb-0 text-danger fw-bold" x-text="formatPrice(data.lower_band)">
                            $0.00
                        </div>
                        <div class="small text-secondary mt-1" x-text="calculateBandDistance(data.lower_band, data.vwap)">
                            -0%
                        </div>
                    </div>
                </div>
            </div>

            <!-- Band Width -->
            <div class="p-3 rounded bg-light mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-secondary">Band Width (Volatility)</span>
                    <span class="badge" :class="getBandWidthBadge()" x-text="calculateBandWidth() + '%'">0%</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar"
                         :class="getBandWidthColor()"
                         :style="'width: ' + Math.min(calculateBandWidth() * 10, 100) + '%'"
                         role="progressbar"></div>
                </div>
                <div class="small text-secondary mt-1" x-text="getBandWidthInterpretation()">
                    Normal volatility
                </div>
            </div>

            <!-- Exchange Info -->
            <div class="d-flex justify-content-between align-items-center text-secondary small">
                <span>Exchange: <strong x-text="data.exchange">--</strong></span>
                <span>Timeframe: <strong x-text="data.timeframe">--</strong></span>
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
function latestStatsCard(initialSymbol = 'BTCUSDT', initialTimeframe = '5min', initialExchange = 'binance') {
    return {
        symbol: initialSymbol,
        timeframe: initialTimeframe,
        exchange: initialExchange,
        loading: false,
        error: null,
        data: null,
        lastUpdate: '--',

        init() {
            setTimeout(() => {
                this.loadData();
            }, 500);

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
                    this.data = e.detail.latest;
                    this.lastUpdate = new Date().toLocaleTimeString();
                    this.error = null;
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
                this.data = data;
                this.lastUpdate = new Date().toLocaleTimeString();

                console.log('✅ Latest VWAP data loaded:', data);
            } catch (error) {
                console.error('❌ Error loading latest VWAP:', error);
                this.error = 'Unable to fetch VWAP data. Please try again.';
                this.data = null;
            } finally {
                this.loading = false;
            }
        },

        refresh() {
            this.loadData();
        },

        formatPrice(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(parseFloat(value));
        },

        formatTimestamp(timestamp) {
            if (!timestamp) return 'N/A';
            const date = new Date(timestamp);
            return date.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
            });
        },

        calculateBandDistance(band, vwap) {
            if (!band || !vwap) return 'N/A';
            const distance = ((band - vwap) / vwap) * 100;
            return (distance >= 0 ? '+' : '') + distance.toFixed(2) + '%';
        },

        calculateBandWidth() {
            if (!this.data || !this.data.upper_band || !this.data.lower_band || !this.data.vwap) return 0;
            const width = ((this.data.upper_band - this.data.lower_band) / this.data.vwap) * 100;
            return width.toFixed(2);
        },

        getBandWidthBadge() {
            const width = this.calculateBandWidth();
            if (width > 2) return 'text-bg-danger';
            if (width > 1) return 'text-bg-warning';
            return 'text-bg-success';
        },

        getBandWidthColor() {
            const width = this.calculateBandWidth();
            if (width > 2) return 'bg-danger';
            if (width > 1) return 'bg-warning';
            return 'bg-success';
        },

        getBandWidthInterpretation() {
            const width = this.calculateBandWidth();
            if (width > 2) return '🔥 High volatility - Wide bands';
            if (width > 1) return '⚡ Moderate volatility';
            return '✅ Low volatility - Tight range';
        },
    };
}
</script>

<style>
.df-panel {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.df-panel:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}
</style>

<?php /**PATH C:\DATASAID\Said\Bisnis\quantwaru\frontend\dragonfortuneai-frontend\resources\views/components/vwap/latest-stats.blade.php ENDPATH**/ ?>