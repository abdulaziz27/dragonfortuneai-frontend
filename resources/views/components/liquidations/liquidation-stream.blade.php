{{--
    Liquidation Stream Component
    Real-time liquidation orders blotter
    Shows: timestamp, exchange, pair, side, qty_usd, price
--}}

<div class="df-panel p-4 h-100"
     x-data="liquidationsStream()"
     x-init="init()">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h5 class="mb-0">⚡ Live Liquidation Stream</h5>
            <small class="text-secondary">Real-time order feed</small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <span x-show="isStreaming" class="pulse-dot pulse-danger"></span>
            <span x-show="loading" class="spinner-border spinner-border-sm text-primary"></span>
        </div>
    </div>

    <!-- Filters -->
    <div class="row g-2 mb-3">
        <div class="col-md-6">
            <select class="form-select form-select-sm" x-model="filterSide" @change="applyFilters()">
                <option value="">All Sides</option>
                <option value="long">Long Only</option>
                <option value="short">Short Only</option>
            </select>
        </div>
        <div class="col-md-6">
            <select class="form-select form-select-sm" x-model="filterExchange" @change="applyFilters()">
                <option value="">All Exchanges</option>
                <option value="Binance">Binance</option>
                <option value="Bybit">Bybit</option>
                <option value="OKX">OKX</option>
                <option value="Bitget">Bitget</option>
                <option value="Hyperliquid">Hyperliquid</option>
            </select>
        </div>
    </div>

    <!-- Stream Stats Bar -->
    <div class="d-flex gap-3 mb-3 p-2 rounded bg-dark bg-opacity-10">
        <div class="flex-fill text-center">
            <div class="small text-secondary">Total Orders</div>
            <div class="fw-bold" x-text="orders.length">0</div>
        </div>
        <div class="flex-fill text-center">
            <div class="small text-secondary">Avg Size</div>
            <div class="fw-bold" x-text="formatUSD(getAverageSize())">--</div>
        </div>
        <div class="flex-fill text-center">
            <div class="small text-secondary">Largest</div>
            <div class="fw-bold" x-text="formatUSD(getLargestOrder())">--</div>
        </div>
    </div>

    <!-- Liquidation Feed -->
    <div class="liquidation-feed" style="max-height: 450px; overflow-y: auto;">
        <template x-for="(order, index) in filteredOrders.slice(0, 100)" :key="index">
            <div class="liquidation-item p-2 mb-2 rounded"
                 :class="getSideClass(order.side)"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100">

                <div class="d-flex justify-content-between align-items-start">
                    <!-- Left Side: Time, Exchange, Pair -->
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge"
                                  :class="order.side_label === 'long' ? 'bg-danger' : 'bg-success'"
                                  x-text="order.side_label?.toUpperCase()">
                                LONG
                            </span>
                            <span class="badge bg-secondary" x-text="order.exchange">Exchange</span>
                            <span class="small text-secondary" x-text="formatTime(order.ts)">00:00:00</span>
                        </div>
                        <div class="fw-bold" x-text="order.pair">BTCUSDT</div>
                    </div>

                    <!-- Right Side: Amount & Price -->
                    <div class="text-end">
                        <div class="fw-bold"
                             :class="order.side_label === 'long' ? 'text-danger' : 'text-success'"
                             x-text="formatUSD(order.qty_usd)">
                            $0.00
                        </div>
                        <div class="small text-secondary">
                            @ $<span x-text="formatPrice(order.price)">0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Load More Button -->
    <div x-show="filteredOrders.length > 100" class="text-center mt-3">
        <button class="btn btn-sm btn-outline-primary" @click="showMore()">
            Show More (showing 100 of <span x-text="filteredOrders.length">0</span>)
        </button>
    </div>

    <!-- No Data State -->
    <div x-show="!loading && orders.length === 0" class="text-center py-5">
        <div class="text-secondary mb-2" style="font-size: 3rem;">⚡</div>
        <div class="text-secondary">Waiting for liquidation data...</div>
    </div>
</div>

<script>
function liquidationsStream() {
    return {
        orders: [],
        filteredOrders: [],
        filterSide: '',
        filterExchange: '',
        loading: false,
        isStreaming: true,

        init() {
            // Listen for overview ready
            window.addEventListener('liquidations-overview-ready', (e) => {
                this.applyOverview(e.detail);
            });

            // Listen for filter changes
            window.addEventListener('symbol-changed', (e) => {
                this.loadData();
            });

            window.addEventListener('exchange-changed', (e) => {
                this.loadData();
            });

            window.addEventListener('refresh-all', () => {
                this.loadData();
            });

            // Initial load with delay to ensure DOM is ready
            setTimeout(() => {
                if (this.$root?.overview) {
                    this.applyOverview(this.$root.overview);
                } else {
                    this.loadData();
                }
            }, 100);

            // Auto-refresh every 10 seconds for real-time feel
            setInterval(() => {
                if (!this.loading && this.isStreaming) {
                    this.loadData();
                }
            }, 10000);
        },

        applyOverview(overview) {
            if (!overview?.orders) return;
            this.orders = overview.orders.sort((a, b) => b.ts - a.ts); // Sort by newest first
            this.applyFilters();
        },

        applyFilters() {
            this.filteredOrders = this.orders.filter(order => {
                // Filter by side
                if (this.filterSide) {
                    const orderSide = (order.side_label || '').toLowerCase();
                    if (orderSide !== this.filterSide.toLowerCase()) {
                        return false;
                    }
                }

                // Filter by exchange
                if (this.filterExchange) {
                    if (order.exchange !== this.filterExchange) {
                        return false;
                    }
                }

                return true;
            });
        },

        async loadData() {
            this.loading = true;
            setTimeout(() => {
                this.loading = false;
            }, 1000);
        },

        getAverageSize() {
            if (this.orders.length === 0) return 0;
            const total = this.orders.reduce((sum, o) => sum + parseFloat(o.qty_usd || 0), 0);
            return total / this.orders.length;
        },

        getLargestOrder() {
            if (this.orders.length === 0) return 0;
            return Math.max(...this.orders.map(o => parseFloat(o.qty_usd || 0)));
        },

        getSideClass(side) {
            const sideStr = (side || '').toString().toLowerCase();
            if (sideStr === 'long' || sideStr === '1') {
                return 'bg-danger bg-opacity-10 border-start border-danger border-3';
            }
            return 'bg-success bg-opacity-10 border-start border-success border-3';
        },

        formatUSD(value) {
            if (value === null || value === undefined) return 'N/A';
            const num = parseFloat(value);
            if (isNaN(num)) return 'N/A';

            if (num >= 1e6) return '$' + (num / 1e6).toFixed(2) + 'M';
            if (num >= 1e3) return '$' + (num / 1e3).toFixed(1) + 'K';
            return '$' + num.toFixed(0);
        },

        formatPrice(value) {
            if (value === null || value === undefined) return 'N/A';
            const num = parseFloat(value);
            if (isNaN(num)) return 'N/A';
            return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 4 });
        },

        formatTime(timestamp) {
            if (!timestamp) return 'N/A';
            const date = new Date(timestamp);
            return date.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
            });
        },

        showMore() {
            // Placeholder for pagination
            console.log('Show more clicked');
        },
    };
}
</script>

<style scoped>
.liquidation-feed {
    scrollbar-width: thin;
    scrollbar-color: rgba(var(--bs-primary-rgb), 0.3) transparent;
}

.liquidation-feed::-webkit-scrollbar {
    width: 6px;
}

.liquidation-feed::-webkit-scrollbar-track {
    background: transparent;
}

.liquidation-feed::-webkit-scrollbar-thumb {
    background-color: rgba(var(--bs-primary-rgb), 0.3);
    border-radius: 3px;
}

.liquidation-item {
    transition: all 0.2s ease;
}

.liquidation-item:hover {
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.pulse-dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    animation: pulse 2s ease-in-out infinite;
}

.pulse-danger {
    background-color: #ef4444;
    box-shadow: 0 0 0 rgba(239, 68, 68, 0.7);
}

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
    }
    50% {
        box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
    }
}
</style>

