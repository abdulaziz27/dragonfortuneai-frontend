/**
 * Orderbook Snapshots Controller
 *
 * Handles all API interactions for orderbook microstructure analysis
 *
 * API Endpoints:
 * - /api/spot-microstructure/book-pressure
 * - /api/spot-microstructure/liquidity-heatmap
 * - /api/spot-microstructure/market-depth
 * - /api/spot-microstructure/orderbook
 * - /api/spot-microstructure/orderbook-depth
 * - /api/spot-microstructure/orderbook/liquidity
 * - /api/spot-microstructure/orderbook/snapshot
 */

// Get API base URL from meta tag or use default
function getApiBaseUrl() {
    const baseMeta = document.querySelector('meta[name="api-base-url"]');
    const configuredBase = (baseMeta?.content || "").trim();
    if (configuredBase) {
        return configuredBase.endsWith("/")
            ? configuredBase.slice(0, -1)
            : configuredBase;
    }
    return "";
}

const API_BASE_URL = getApiBaseUrl() + "/api/spot-microstructure";

/**
 * Main Orderbook Controller
 */
function orderbookController() {
    return {
        // Global state
        loading: false,
        selectedSymbol: 'BTCUSDT',
        selectedInterval: '5m',
        selectedLimit: 200,
        selectedExchange: 'binance',

        // Auto-refresh State
        autoRefreshEnabled: true,
        autoRefreshTimer: null,
        autoRefreshInterval: 5000,   // 5 seconds
        lastUpdated: null,

        // Debouncing
        filterDebounceTimer: null,
        filterDebounceDelay: 300,

        // Data storage
        bookPressureData: [],
        liquidityData: [],
        marketDepthData: [],
        orderbookSnapshot: null,

        // Chart instances
        bookPressureChart: null,
        liquidityChart: null,

        // Initialize
        init() {
            console.log("🚀 Enhanced Orderbook Snapshots Dashboard initialized");
            console.log("📊 Symbol:", this.selectedSymbol);
            console.log("🏦 Exchange:", this.selectedExchange);
            console.log("🔄 Auto-refresh:", this.autoRefreshEnabled ? 'ON' : 'OFF');

            // Initialize shared state
            this.initializeSharedState();

            // Load all data
            this.loadAllData();

            // Start auto-refresh
            this.startAutoRefresh();

            // Setup visibility API
            this.setupVisibilityAPI();
        },

        // Initialize shared state management
        initializeSharedState() {
            if (!window.SpotMicrostructureSharedState) {
                window.SpotMicrostructureSharedState = {
                    filters: {
                        selectedSymbol: this.selectedSymbol,
                        selectedInterval: this.selectedInterval,
                        selectedLimit: this.selectedLimit,
                        selectedExchange: this.selectedExchange
                    },
                    subscribers: {},

                    setFilter(key, value) {
                        this.filters[key] = value;
                        this.notifySubscribers(key, value);
                    },

                    subscribe(key, callback) {
                        if (!this.subscribers[key]) {
                            this.subscribers[key] = [];
                        }
                        this.subscribers[key].push(callback);
                    },

                    notifySubscribers(key, value) {
                        if (this.subscribers[key]) {
                            this.subscribers[key].forEach(callback => callback(value));
                        }
                    }
                };
            }

            // Subscribe to shared state changes
            window.SpotMicrostructureSharedState.subscribe('selectedSymbol', (value) => {
                if (this.selectedSymbol !== value) {
                    this.selectedSymbol = value;
                    this.handleFilterChange();
                }
            });

            window.SpotMicrostructureSharedState.subscribe('selectedInterval', (value) => {
                if (this.selectedInterval !== value) {
                    this.selectedInterval = value;
                    this.handleFilterChange();
                }
            });

            window.SpotMicrostructureSharedState.subscribe('selectedLimit', (value) => {
                if (this.selectedLimit !== value) {
                    this.selectedLimit = value;
                    this.handleFilterChange();
                }
            });

            window.SpotMicrostructureSharedState.subscribe('selectedExchange', (value) => {
                if (this.selectedExchange !== value) {
                    this.selectedExchange = value;
                    this.handleFilterChange();
                }
            });
        },

        // Handle filter changes with debouncing
        handleFilterChange() {
            if (this.filterDebounceTimer) {
                clearTimeout(this.filterDebounceTimer);
            }

            this.filterDebounceTimer = setTimeout(() => {
                console.log('🎛️ Filter changed:', {
                    symbol: this.selectedSymbol,
                    interval: this.selectedInterval,
                    limit: this.selectedLimit,
                    exchange: this.selectedExchange
                });

                this.loadAllData();
            }, this.filterDebounceDelay);
        },

        // Load all data
        async loadAllData() {
            this.loading = true;
            try {
                await Promise.all([
                    this.loadBookPressureData(),
                    this.loadLiquidityData(),
                    this.loadMarketDepthData(),
                    this.loadOrderbookSnapshot()
                ]);

                this.lastUpdated = new Date().toLocaleTimeString();
                console.log('✅ All orderbook data loaded at:', this.lastUpdated);
            } catch (error) {
                console.error('❌ Error loading orderbook data:', error);
            } finally {
                this.loading = false;
            }
        },

        // Auto-refresh methods
        startAutoRefresh() {
            if (this.autoRefreshTimer) {
                clearInterval(this.autoRefreshTimer);
            }

            if (this.autoRefreshEnabled) {
                this.autoRefreshTimer = setInterval(() => {
                    if (this.autoRefreshEnabled && !document.hidden) {
                        console.log('🔄 Auto-refreshing orderbook data...');
                        this.loadAllData();
                    }
                }, this.autoRefreshInterval);

                console.log('✅ Auto-refresh started (5s intervals)');
            }
        },

        stopAutoRefresh() {
            if (this.autoRefreshTimer) {
                clearInterval(this.autoRefreshTimer);
                this.autoRefreshTimer = null;
                console.log('⏹️ Auto-refresh stopped');
            }
        },

        toggleAutoRefresh() {
            this.autoRefreshEnabled = !this.autoRefreshEnabled;
            console.log('🔄 Auto-refresh toggled:', this.autoRefreshEnabled ? 'ON' : 'OFF');

            if (this.autoRefreshEnabled) {
                this.startAutoRefresh();
            } else {
                this.stopAutoRefresh();
            }
        },

        // Setup Visibility API for tab switching
        setupVisibilityAPI() {
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    console.log('👁️ Tab hidden - pausing auto-refresh');
                } else {
                    console.log('👁️ Tab visible - resuming auto-refresh');
                    if (this.autoRefreshEnabled) {
                        this.loadAllData(); // Immediate refresh when tab becomes visible
                    }
                }
            });
        },

        // Filter change handlers
        onSymbolChange() {
            window.SpotMicrostructureSharedState.setFilter('selectedSymbol', this.selectedSymbol);
        },

        onIntervalChange() {
            window.SpotMicrostructureSharedState.setFilter('selectedInterval', this.selectedInterval);
        },

        onLimitChange() {
            window.SpotMicrostructureSharedState.setFilter('selectedLimit', this.selectedLimit);
        },

        onExchangeChange() {
            window.SpotMicrostructureSharedState.setFilter('selectedExchange', this.selectedExchange);
        },

        // Cleanup on destroy
        beforeDestroy() {
            this.stopAutoRefresh();
            if (this.filterDebounceTimer) {
                clearTimeout(this.filterDebounceTimer);
            }
        },

        // Load book pressure data
        async loadBookPressureData() {
            try {
                const response = await fetch(
                    `${API_BASE_URL}/book-pressure?symbol=${this.selectedSymbol}&exchange=${this.selectedExchange}&limit=${this.selectedLimit}`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch book pressure");
                }

                const data = await response.json();
                this.bookPressureData = data.data || [];

                console.log('✅ Book pressure data loaded:', this.bookPressureData.length, 'records');
            } catch (error) {
                console.error('❌ Error loading book pressure data:', error);
                this.bookPressureData = [];
            }
        },

        // Load liquidity data
        async loadLiquidityData() {
            try {
                const response = await fetch(
                    `${API_BASE_URL}/orderbook/liquidity?symbol=${this.selectedSymbol}&depth=${Math.min(this.selectedLimit, 50)}`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch liquidity data");
                }

                const data = await response.json();
                // Fix: Liquidity endpoint returns object, not array
                this.liquidityData = data ? [data] : [];

                console.log('✅ Liquidity data loaded:', this.liquidityData.length, 'records');
            } catch (error) {
                console.error('❌ Error loading liquidity data:', error);
                this.liquidityData = [];
            }
        },

        // Load market depth data
        async loadMarketDepthData() {
            try {
                const response = await fetch(
                    `${API_BASE_URL}/market-depth?symbol=${this.selectedSymbol}&exchange=${this.selectedExchange}&limit=${Math.min(this.selectedLimit, 100)}`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch market depth");
                }

                const data = await response.json();
                this.marketDepthData = data.data || [];

                console.log('✅ Market depth data loaded:', this.marketDepthData.length, 'records');
            } catch (error) {
                console.error('❌ Error loading market depth data:', error);
                this.marketDepthData = [];
            }
        },

        // Load orderbook snapshot
        async loadOrderbookSnapshot() {
            try {
                const response = await fetch(
                    `${API_BASE_URL}/orderbook/snapshot?symbol=${this.selectedSymbol}&depth=${Math.min(this.selectedLimit, 50)}`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch orderbook snapshot");
                }

                const data = await response.json();
                this.orderbookSnapshot = data.data?.[0] || null;

                console.log('✅ Orderbook snapshot loaded');
            } catch (error) {
                console.error('❌ Error loading orderbook snapshot:', error);
                this.orderbookSnapshot = null;
            }
        },

        // Manual refresh method
        async manualRefresh() {
            console.log("🔄 Manual refresh triggered");
            await this.loadAllData();
        },
    };
}

/**
 * Book Pressure Card Component
 */
function bookPressureCard() {
    return {
        pressureDirection: "neutral",
        pressureRatio: 0,
        bidPressure: 0,
        askPressure: 0,
        sampleSize: 0,
        loading: false,

        init() {
            this.loadPressure();

            // Listen to shared state events
            if (window.SpotMicrostructureSharedState) {
                window.SpotMicrostructureSharedState.subscribe('selectedSymbol', () => this.loadPressure());
                window.SpotMicrostructureSharedState.subscribe('selectedExchange', () => this.loadPressure());
                window.SpotMicrostructureSharedState.subscribe('selectedLimit', () => this.loadPressure());
            }
        },

        async loadPressure() {
            this.loading = true;
            const symbol = this.$root?.selectedSymbol || "BTCUSDT";
            const exchange = this.$root?.selectedExchange || "binance";
            const limit = this.$root?.selectedLimit || 200;

            try {
                const response = await fetch(
                    `${API_BASE_URL}/book-pressure?symbol=${symbol}&exchange=${exchange}&limit=${limit}`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch book pressure");
                }

                const result = await response.json();
                const data = result.data || [];

                if (data.length > 0) {
                    // Calculate averages from recent data
                    const avgBidPressure =
                        data.reduce(
                            (sum, d) => sum + (d.bid_pressure || 0),
                            0
                        ) / data.length;
                    const avgAskPressure =
                        data.reduce(
                            (sum, d) => sum + (d.ask_pressure || 0),
                            0
                        ) / data.length;
                    const avgRatio =
                        data.reduce(
                            (sum, d) => sum + (d.pressure_ratio || 0),
                            0
                        ) / data.length;

                    this.bidPressure = avgBidPressure;
                    this.askPressure = avgAskPressure;
                    this.pressureRatio = avgRatio;
                    this.sampleSize = data.length;

                    // Determine direction from most recent data
                    this.pressureDirection =
                        data[0].pressure_direction || "neutral";

                    console.log(
                        "✅ Book pressure loaded:",
                        this.pressureDirection
                    );
                } else {
                    this.resetData();
                    console.warn("⚠️ No book pressure data available");
                }
            } catch (error) {
                console.error("❌ Error loading book pressure:", error);
                this.resetData();
            } finally {
                this.loading = false;
            }
        },

        resetData() {
            this.pressureDirection = "neutral";
            this.pressureRatio = 0;
            this.bidPressure = 0;
            this.askPressure = 0;
            this.sampleSize = 0;
        },

        getDirectionClass() {
            if (this.pressureDirection === "bullish") return "bg-success";
            if (this.pressureDirection === "bearish") return "bg-danger";
            return "bg-secondary";
        },

        formatNumber(value) {
            if (value === 0) return "0";
            return value.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },
    };
}

/**
 * Liquidity Imbalance Component
 */
function liquidityImbalance() {
    return {
        totalBidLiquidity: 0,
        totalAskLiquidity: 0,
        totalLiquidity: 0,
        bidAskRatio: 0,
        imbalance: 0,
        imbalancePct: 0,
        loading: false,

        init() {
            this.loadLiquidity();

            // Listen to shared state events
            if (window.SpotMicrostructureSharedState) {
                window.SpotMicrostructureSharedState.subscribe('selectedSymbol', () => this.loadLiquidity());
                window.SpotMicrostructureSharedState.subscribe('selectedLimit', () => this.loadLiquidity());
            }
        },

        async loadLiquidity() {
            this.loading = true;
            const symbol = this.$root?.selectedSymbol || "BTCUSDT";
            const limit = Math.min(this.$root?.selectedLimit || 200, 50);

            try {
                const response = await fetch(
                    `${API_BASE_URL}/orderbook/liquidity?symbol=${symbol}&depth=${limit}`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch liquidity data");
                }

                const data = await response.json();

                this.totalBidLiquidity = data.total_bid_liquidity || 0;
                this.totalAskLiquidity = data.total_ask_liquidity || 0;
                this.totalLiquidity = data.total_liquidity || 0;
                this.bidAskRatio = data.bid_ask_ratio || 0;
                this.imbalance = data.imbalance || 0;
                this.imbalancePct = data.imbalance_pct || 0;

                console.log("✅ Liquidity imbalance loaded");
            } catch (error) {
                console.error("❌ Error loading liquidity:", error);
                this.resetData();
            } finally {
                this.loading = false;
            }
        },

        resetData() {
            this.totalBidLiquidity = 0;
            this.totalAskLiquidity = 0;
            this.totalLiquidity = 0;
            this.bidAskRatio = 0;
            this.imbalance = 0;
            this.imbalancePct = 0;
        },

        formatLiquidity(value) {
            if (value === 0) return "0";
            const absValue = Math.abs(value);
            if (absValue >= 1000000) {
                return (
                    (value >= 0 ? "" : "-") +
                    (absValue / 1000000).toFixed(2) +
                    "M"
                );
            } else if (absValue >= 1000) {
                return (
                    (value >= 0 ? "" : "-") + (absValue / 1000).toFixed(2) + "K"
                );
            }
            return value.toFixed(2);
        },

        formatPercent(value) {
            return (value >= 0 ? "+" : "") + value.toFixed(2) + "%";
        },

        getImbalanceClass() {
            if (this.imbalancePct > 10) return "text-success";
            if (this.imbalancePct < -10) return "text-danger";
            return "text-secondary";
        },
    };
}

/**
 * Market Depth Stats Component
 */
function marketDepthStats() {
    return {
        bidLevels: 0,
        askLevels: 0,
        totalBidVolume: 0,
        totalAskVolume: 0,
        depthScore: 0,
        loading: false,

        init() {
            this.loadDepthStats();

            // Listen to global events
            window.addEventListener("symbol-changed", () =>
                this.loadDepthStats()
            );
            window.addEventListener("exchange-changed", () =>
                this.loadDepthStats()
            );
            window.addEventListener("refresh-all", () => this.loadDepthStats());
        },

        async loadDepthStats() {
            this.loading = true;
            const symbol = this.$root?.selectedSymbol || "BTCUSDT";
            const exchange = this.$root?.selectedExchange || "binance";

            try {
                const response = await fetch(
                    `${API_BASE_URL}/market-depth?symbol=${symbol}&exchange=${exchange}&limit=1`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch market depth");
                }

                const result = await response.json();
                const data = result.data || [];

                if (data.length > 0) {
                    const latest = data[0];
                    this.bidLevels = latest.bid_levels || 0;
                    this.askLevels = latest.ask_levels || 0;
                    this.totalBidVolume = latest.total_bid_volume || 0;
                    this.totalAskVolume = latest.total_ask_volume || 0;
                    this.depthScore = latest.depth_score || 0;

                    console.log("✅ Market depth stats loaded");
                } else {
                    this.resetData();
                }
            } catch (error) {
                console.error("❌ Error loading market depth:", error);
                this.resetData();
            } finally {
                this.loading = false;
            }
        },

        resetData() {
            this.bidLevels = 0;
            this.askLevels = 0;
            this.totalBidVolume = 0;
            this.totalAskVolume = 0;
            this.depthScore = 0;
        },

        formatVolume(value) {
            if (value === 0) return "0";
            if (value >= 1000000) {
                return (value / 1000000).toFixed(2) + "M";
            } else if (value >= 1000) {
                return (value / 1000).toFixed(2) + "K";
            }
            return value.toFixed(2);
        },
    };
}

/**
 * Quick Stats Component
 */
function quickStats() {
    return {
        currentSpread: 0,
        spreadPercent: 0,
        midPrice: 0,
        loading: false,

        init() {
            this.loadQuickStats();

            // Listen to global events
            window.addEventListener("symbol-changed", () =>
                this.loadQuickStats()
            );
            window.addEventListener("refresh-all", () => this.loadQuickStats());
        },

        async loadQuickStats() {
            this.loading = true;
            const symbol = this.$root?.selectedSymbol || "BTCUSDT";

            try {
                const response = await fetch(
                    `${API_BASE_URL}/orderbook/snapshot?symbol=${symbol}&depth=20`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch orderbook snapshot");
                }

                const data = await response.json();

                // Fix: Access orderbook data from data.data[0] structure
                const orderbook = data.data && data.data[0];

                if (
                    orderbook &&
                    orderbook.asks &&
                    orderbook.asks.length > 0 &&
                    orderbook.bids &&
                    orderbook.bids.length > 0
                ) {
                    const bestAsk = orderbook.asks[0].price;
                    const bestBid = orderbook.bids[0].price;

                    this.currentSpread = bestAsk - bestBid;
                    this.midPrice = (bestAsk + bestBid) / 2;
                    this.spreadPercent =
                        (this.currentSpread / this.midPrice) * 100;

                    console.log("✅ Quick stats loaded:", {
                        spread: this.currentSpread,
                        midPrice: this.midPrice,
                        spreadPercent: this.spreadPercent
                    });
                } else {
                    console.warn("⚠️ No orderbook data available for quick stats");
                    this.resetData();
                }
            } catch (error) {
                console.error("❌ Error loading quick stats:", error);
                this.resetData();
            } finally {
                this.loading = false;
            }
        },

        resetData() {
            this.currentSpread = 0;
            this.spreadPercent = 0;
            this.midPrice = 0;
        },

        formatPrice(value) {
            // Fix: Handle NaN, null, undefined values
            if (value === null || value === undefined || isNaN(value) || value === 0) {
                return "$0.00";
            }
            return (
                "$" +
                value.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                })
            );
        },

        formatPercent(value) {
            // Fix: Handle NaN, null, undefined values
            if (value === null || value === undefined || isNaN(value)) {
                return "0.0000%";
            }
            return value.toFixed(4) + "%";
        },
    };
}

/**
 * Live Orderbook Snapshot Component
 */
function liveOrderbookSnapshot() {
    return {
        bids: [],
        asks: [],
        midPrice: 0,
        spread: 0,
        loading: false,

        init() {
            this.loadSnapshot();

            // Listen to global events (auto-refresh handled by main controller)
            window.addEventListener("symbol-changed", () =>
                this.loadSnapshot()
            );
            window.addEventListener("refresh-all", () => this.loadSnapshot());
        },

        async loadSnapshot() {
            this.loading = true;
            const symbol = this.$root?.selectedSymbol || "BTCUSDT";
            const depth = Math.min(this.$root?.selectedLimit || 200, 50);

            try {
                const response = await fetch(
                    `${API_BASE_URL}/orderbook/snapshot?symbol=${symbol}&depth=${depth}`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch orderbook snapshot");
                }

                const data = await response.json();

                // Fix: Access orderbook data from data.data[0] structure
                const orderbook = data.data && data.data[0];

                if (orderbook) {
                    this.bids = (orderbook.bids || []).slice(0, 10);
                    this.asks = (orderbook.asks || []).slice(0, 10).reverse(); // Reverse asks for display

                    if (this.bids.length > 0 && this.asks.length > 0) {
                        // Note: asks are reversed, so get the last item for best ask
                        const bestAsk = this.asks[this.asks.length - 1].price;
                        const bestBid = this.bids[0].price;
                        this.midPrice = (bestAsk + bestBid) / 2;
                        this.spread = bestAsk - bestBid;
                    }

                    console.log("✅ Orderbook snapshot loaded:", {
                        bids: this.bids.length,
                        asks: this.asks.length,
                        spread: this.spread,
                        midPrice: this.midPrice
                    });
                } else {
                    console.warn("⚠️ No orderbook data available for snapshot");
                    this.bids = [];
                    this.asks = [];
                }
            } catch (error) {
                console.error("❌ Error loading orderbook snapshot:", error);
                this.bids = [];
                this.asks = [];
            } finally {
                this.loading = false;
            }
        },

        calculateDepthPercentage(quantity, maxQuantity) {
            if (maxQuantity === 0) return 0;
            return (quantity / maxQuantity) * 100;
        },

        getMaxQuantity(orders) {
            if (orders.length === 0) return 0;
            // Fix: Use 'size' field instead of 'quantity' (API returns 'size')
            return Math.max(...orders.map((o) => o.size || 0));
        },

        formatPrice(price) {
            // Fix: Handle NaN, null, undefined values
            if (price === null || price === undefined || isNaN(price) || price === 0) {
                return "$0.00";
            }
            return (
                "$" +
                price.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                })
            );
        },

        formatQuantity(quantity) {
            // Fix: Handle NaN, null, undefined values
            if (quantity === null || quantity === undefined || isNaN(quantity)) {
                return "0.0000";
            }
            return quantity.toFixed(4);
        },

        formatTotal(price, quantity) {
            // Fix: Handle NaN, null, undefined values
            if (price === null || price === undefined || isNaN(price) || 
                quantity === null || quantity === undefined || isNaN(quantity)) {
                return "$0.00";
            }
            const total = price * quantity;
            if (total >= 1000) {
                return "$" + (total / 1000).toFixed(2) + "K";
            }
            return "$" + total.toFixed(2);
        },
    };
}

/**
 * Book Pressure Chart Component
 */
function bookPressureTable() {
    return {
        loading: false,
        pressureData: [],
        avgBidPressure: 0,
        avgAskPressure: 0,
        avgRatio: 0,

        init() {
            this.loadData();

            // Listen to global events
            window.addEventListener("symbol-changed", () => this.loadData());
            window.addEventListener("exchange-changed", () => this.loadData());
            window.addEventListener("refresh-all", () => this.loadData());
        },

        async loadData() {
            this.loading = true;
            console.log('📈 Book Pressure Table: Loading data...');

            try {
                const response = await fetch(
                    `${API_BASE_URL}/book-pressure?limit=100`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch book pressure data");
                }

                const result = await response.json();
                const data = result.data || [];

                if (data.length > 0) {
                    this.pressureData = data.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
                    this.calculateStats();
                    console.log("✅ Book pressure table loaded:", data.length, "records");
                } else {
                    console.warn("⚠️ No book pressure data available");
                    this.pressureData = [];
                }
            } catch (error) {
                console.error("❌ Error loading book pressure data:", error);
                this.pressureData = [];
            } finally {
                this.loading = false;
            }
        },

        calculateStats() {
            if (this.pressureData.length === 0) {
                this.avgBidPressure = 0;
                this.avgAskPressure = 0;
                this.avgRatio = 0;
                return;
            }

            const bidPressures = this.pressureData.map(d => parseFloat(d.bid_pressure || 0));
            const askPressures = this.pressureData.map(d => parseFloat(d.ask_pressure || 0));
            const ratios = this.pressureData.map(d => parseFloat(d.pressure_ratio || 0));

            this.avgBidPressure = bidPressures.reduce((a, b) => a + b, 0) / bidPressures.length;
            this.avgAskPressure = askPressures.reduce((a, b) => a + b, 0) / askPressures.length;
            this.avgRatio = ratios.reduce((a, b) => a + b, 0) / ratios.length;
        },

        formatPressure(value) {
            if (value === null || value === undefined) return 'N/A';
            const num = parseFloat(value);
            if (isNaN(num)) return 'N/A';
            return num.toFixed(2);
        },

        formatRatio(value) {
            if (value === null || value === undefined) return 'N/A';
            const num = parseFloat(value);
            if (isNaN(num)) return 'N/A';
            return num.toFixed(3);
        },

        formatTime(timestamp) {
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

        getDirectionClass(direction) {
            switch (direction?.toLowerCase()) {
                case 'bullish': return 'bg-success';
                case 'bearish': return 'bg-danger';
                default: return 'bg-secondary';
            }
        },
    };
}

/**
 * Liquidity Distribution Table Component
 */
function liquidityDistributionTable() {
    return {
        liquidityData: [],
        loading: false,

        init() {
            this.loadData();

            // Listen to global events
            window.addEventListener("symbol-changed", () => this.loadData());
            window.addEventListener("exchange-changed", () => this.loadData());
            window.addEventListener("refresh-all", () => this.loadData());
        },

        async loadData() {
            this.loading = true;
            const symbol = this.$root?.selectedSymbol || "BTCUSDT";
            const exchange = this.$root?.selectedExchange || "binance";
            const limit = Math.min(this.$root?.selectedLimit || 200, 50);

            try {
                const response = await fetch(
                    `${API_BASE_URL}/liquidity-heatmap?symbol=${symbol}&exchange=${exchange}&limit=${limit}`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch liquidity data");
                }

                const result = await response.json();
                this.liquidityData = result.data || [];

                console.log(
                    "✅ Liquidity distribution loaded:",
                    this.liquidityData.length,
                    "levels"
                );
            } catch (error) {
                console.error(
                    "❌ Error loading liquidity distribution:",
                    error
                );
                this.liquidityData = [];
            } finally {
                this.loading = false;
            }
        },

        formatPrice(price) {
            // Fix: Handle NaN, null, undefined values
            if (price === null || price === undefined || isNaN(price) || price === 0) {
                return "$0.00";
            }
            return (
                "$" +
                price.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                })
            );
        },

        formatLiquidity(value) {
            if (value === 0) return "0";
            if (value >= 1000000) {
                return (value / 1000000).toFixed(2) + "M";
            } else if (value >= 1000) {
                return (value / 1000).toFixed(2) + "K";
            }
            return value.toFixed(2);
        },

        getBidPercentage(item) {
            if (item.total_liquidity === 0) return 0;
            return (item.bid_liquidity / item.total_liquidity) * 100;
        },

        getAskPercentage(item) {
            if (item.total_liquidity === 0) return 0;
            return (item.ask_liquidity / item.total_liquidity) * 100;
        },

        getImbalanceText(item) {
            const bidPct = this.getBidPercentage(item);
            const askPct = this.getAskPercentage(item);

            if (bidPct > 60) return "Bid Heavy";
            if (askPct > 60) return "Ask Heavy";
            if (Math.abs(bidPct - askPct) < 10) return "Balanced";

            return bidPct > askPct ? "Bid Favored" : "Ask Favored";
        },
    };
}

/**
 * Market Depth Table Component
 */
function marketDepthTable() {
    return {
        depths: [],
        loading: false,

        init() {
            this.loadTable();

            // Listen to global events
            window.addEventListener("symbol-changed", () => this.loadTable());
            window.addEventListener("exchange-changed", () => this.loadTable());
            window.addEventListener("refresh-all", () => this.loadTable());
        },

        async loadTable() {
            this.loading = true;
            const symbol = this.$root?.selectedSymbol || "BTCUSDT";
            const exchange = this.$root?.selectedExchange || "binance";
            const limit = Math.min(this.$root?.selectedLimit || 200, 100);

            try {
                const response = await fetch(
                    `${API_BASE_URL}/market-depth?symbol=${symbol}&exchange=${exchange}&limit=${limit}`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch market depth data");
                }

                const result = await response.json();
                this.depths = result.data || [];

                console.log(
                    "✅ Market depth table loaded:",
                    this.depths.length,
                    "records"
                );
            } catch (error) {
                console.error("❌ Error loading market depth table:", error);
                this.depths = [];
            } finally {
                this.loading = false;
            }
        },

        formatTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleTimeString("en-US", {
                hour: "2-digit",
                minute: "2-digit",
            });
        },

        formatVolume(volume) {
            if (volume >= 1000000) {
                return (volume / 1000000).toFixed(2) + "M";
            } else if (volume >= 1000) {
                return (volume / 1000).toFixed(2) + "K";
            }
            return volume.toFixed(2);
        },
    };
}

/**
 * Orderbook Depth Table Component
 */
function orderbookDepthTable() {
    return {
        depths: [],
        loading: false,

        init() {
            this.loadTable();

            // Listen to global events
            window.addEventListener("symbol-changed", () => this.loadTable());
            window.addEventListener("exchange-changed", () => this.loadTable());
            window.addEventListener("refresh-all", () => this.loadTable());
        },

        async loadTable() {
            this.loading = true;
            const symbol = this.$root?.selectedSymbol || "BTCUSDT";
            const exchange = this.$root?.selectedExchange || "binance";
            const limit = Math.min(this.$root?.selectedLimit || 200, 100);

            try {
                // Fix: Use market-depth endpoint instead of non-existent orderbook-depth
                const response = await fetch(
                    `${API_BASE_URL}/market-depth?symbol=${symbol}&exchange=${exchange}&limit=${limit}`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch market depth data");
                }

                const result = await response.json();
                this.depths = result.data || [];

                console.log(
                    "✅ Orderbook depth table loaded:",
                    this.depths.length,
                    "records"
                );
            } catch (error) {
                console.error("❌ Error loading orderbook depth table:", error);
                this.depths = [];
            } finally {
                this.loading = false;
            }
        },

        formatPrice(price) {
            // Fix: Handle NaN, null, undefined values
            if (price === null || price === undefined || isNaN(price) || price === 0) {
                return "$0.00";
            }
            return (
                "$" +
                price.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                })
            );
        },

        formatQuantity(quantity) {
            // Fix: Handle NaN, null, undefined values
            if (quantity === null || quantity === undefined || isNaN(quantity)) {
                return "0.0000";
            }
            return quantity.toFixed(4);
        },

        formatTotal(total) {
            // Fix: Handle NaN, null, undefined values
            if (total === null || total === undefined || isNaN(total)) {
                return "$0.00";
            }
            if (total >= 1000000) {
                return "$" + (total / 1000000).toFixed(2) + "M";
            } else if (total >= 1000) {
                return "$" + (total / 1000).toFixed(2) + "K";
            }
            return "$" + total.toFixed(2);
        },
    };
}

/**
 * Market Summary Component
 */
function marketSummary() {
    return {
        loading: false,
        currentDepth: null,
        
        init() {
            this.loadMarketSummary();
            
            // Listen to shared state events
            if (window.SpotMicrostructureSharedState) {
                window.SpotMicrostructureSharedState.subscribe('selectedSymbol', () => this.loadMarketSummary());
                window.SpotMicrostructureSharedState.subscribe('selectedExchange', () => this.loadMarketSummary());
            }
        },
        
        async loadMarketSummary() {
            this.loading = true;
            const symbol = this.$root?.selectedSymbol || "BTCUSDT";
            const exchange = this.$root?.selectedExchange || "binance";
            
            try {
                const response = await fetch(
                    `${API_BASE_URL}/market-depth?symbol=${symbol}&exchange=${exchange}&limit=1`
                );
                
                if (!response.ok) {
                    throw new Error("Failed to fetch market summary");
                }
                
                const result = await response.json();
                const data = result.data || [];
                
                if (data.length > 0) {
                    this.currentDepth = data[0]; // Get latest data
                    console.log("✅ Market summary loaded:", this.currentDepth);
                } else {
                    this.currentDepth = null;
                    console.warn("⚠️ No market depth data available");
                }
            } catch (error) {
                console.error("❌ Error loading market summary:", error);
                this.currentDepth = null;
            } finally {
                this.loading = false;
            }
        },
        
        formatVolume(value) {
            if (!value || value === 0) return "0";
            if (value >= 1000000) {
                return (value / 1000000).toFixed(1) + "M";
            } else if (value >= 1000) {
                return (value / 1000).toFixed(1) + "K";
            }
            return value.toFixed(0);
        },
        
        getAvgVolumePerLevel(totalVolume, levels) {
            if (!totalVolume || !levels || levels === 0) return 0;
            return totalVolume / levels;
        },
        
        getLiquidityAssessment(score) {
            if (!score) return "No Data";
            if (score >= 80) return "Excellent";
            if (score >= 60) return "Good";
            if (score >= 40) return "Moderate";
            if (score >= 20) return "Low";
            return "Very Low";
        },
        
        getLiquidityClass(score) {
            if (!score) return "text-secondary";
            if (score >= 80) return "text-success";
            if (score >= 60) return "text-info";
            if (score >= 40) return "text-warning";
            return "text-danger";
        },
        
        getVolumeRatio(bidVolume, askVolume) {
            if (!bidVolume || !askVolume) return "N/A";
            const ratio = bidVolume / askVolume;
            return ratio.toFixed(2) + ":1";
        },
        
        getMarketInsight(depth) {
            if (!depth) return "No market data available.";
            
            const bidVolume = depth.total_bid_volume || 0;
            const askVolume = depth.total_ask_volume || 0;
            const bidLevels = depth.bid_levels || 0;
            const askLevels = depth.ask_levels || 0;
            const depthScore = depth.depth_score || 0;
            
            // Volume imbalance analysis
            const volumeRatio = bidVolume / (askVolume || 1);
            let insight = "";
            
            if (volumeRatio > 1.5) {
                insight = "Strong buying pressure detected with " + (volumeRatio * 100 - 100).toFixed(0) + "% more bid volume.";
            } else if (volumeRatio < 0.67) {
                insight = "Strong selling pressure detected with " + (100 - volumeRatio * 100).toFixed(0) + "% more ask volume.";
            } else {
                insight = "Balanced market with relatively equal bid/ask volumes.";
            }
            
            // Liquidity assessment
            if (depthScore >= 80) {
                insight += " Excellent liquidity provides tight spreads and low slippage.";
            } else if (depthScore >= 60) {
                insight += " Good liquidity supports efficient trading.";
            } else if (depthScore >= 40) {
                insight += " Moderate liquidity - consider order size impact.";
            } else {
                insight += " Low liquidity may result in higher slippage.";
            }
            
            return insight;
        }
    };
}

console.log("✅ Orderbook controller loaded");
