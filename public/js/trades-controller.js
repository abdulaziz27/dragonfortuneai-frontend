/**
 * Trades Analysis Controller
 *
 * Handles all API interactions for CVD & Buy/Sell Ratio analysis
 *
 * API Endpoints:
 * - /api/spot-microstructure/cvd
 * - /api/spot-microstructure/trade-bias
 * - /api/spot-microstructure/trades
 * - /api/spot-microstructure/trades/summary
 * - /api/spot-microstructure/buyer-seller-ratio
 * - /api/spot-microstructure/trade-flow
 * - /api/spot-microstructure/volume-delta
 */

// Base API URL
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
 * Main Trades Controller
 */
function tradesController() {
    return {
        // Global state
        loading: false,
        selectedSymbol: 'BTCUSDT',
        selectedInterval: '5m',
        selectedLimit: 200,

        // Auto-refresh State
        autoRefreshEnabled: true,
        autoRefreshTimer: null,
        autoRefreshInterval: 5000,   // 5 seconds
        lastUpdated: null,

        // Debouncing
        filterDebounceTimer: null,
        filterDebounceDelay: 300,

        // Initialize
        init() {
            console.log('🚀 Initializing Trades Analysis Controller');

            // Load shared state
            this.loadSharedState();

            // Subscribe to shared state changes
            this.subscribeToSharedState();

            this.loadAllData();

            // Start auto-refresh system
            this.startAutoRefresh();

            // Add visibility API integration
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    console.log('👁️ Tab hidden, pausing auto-refresh');
                    this.pauseAutoRefresh();
                } else {
                    console.log('👁️ Tab visible, resuming auto-refresh');
                    this.resumeAutoRefresh();
                }
            });
        },

        // Load shared state
        loadSharedState() {
            if (window.SpotMicrostructureSharedState) {
                const sharedFilters = window.SpotMicrostructureSharedState.getAllFilters();
                this.selectedSymbol = sharedFilters.selectedSymbol || 'BTCUSDT';
                this.selectedInterval = sharedFilters.selectedInterval || '5m';
                this.selectedLimit = sharedFilters.selectedLimit || 200;
            }
        },

        // Subscribe to shared state changes
        subscribeToSharedState() {
            if (window.SpotMicrostructureSharedState) {
                // Subscribe to symbol changes
                window.SpotMicrostructureSharedState.subscribe('selectedSymbol', (value) => {
                    if (this.selectedSymbol !== value) {
                        this.selectedSymbol = value;
                        this.refreshAll();
                    }
                });

                // Subscribe to interval changes
                window.SpotMicrostructureSharedState.subscribe('selectedInterval', (value) => {
                    if (this.selectedInterval !== value) {
                        this.selectedInterval = value;
                        this.refreshAll();
                    }
                });

                // Subscribe to limit changes
                window.SpotMicrostructureSharedState.subscribe('selectedLimit', (value) => {
                    if (this.selectedLimit !== value) {
                        this.selectedLimit = value;
                        this.refreshAll();
                    }
                });
            }
        },

        // Update shared state when local state changes
        updateSharedState() {
            if (window.SpotMicrostructureSharedState) {
                window.SpotMicrostructureSharedState.setFilter('selectedSymbol', this.selectedSymbol);
                window.SpotMicrostructureSharedState.setFilter('selectedInterval', this.selectedInterval);
                window.SpotMicrostructureSharedState.setFilter('selectedLimit', this.selectedLimit);
            }
        },

        // Load all data
        async loadAllData() {
            this.loading = true;
            try {
                // Dispatch refresh event to all components
                this.$dispatch('refresh-all');

                // Update timestamp on successful load
                this.updateLastUpdated();
            } catch (error) {
                console.error('❌ Error loading trades data:', error);
            } finally {
                this.loading = false;
            }
        },

        // Refresh all components
        async refreshAll() {
            this.updateSharedState();
            await this.loadAllData();
        },

        // Debounced refresh for better performance
        handleFilterChange() {
            console.log('🔄 Filter changed');

            // Clear existing timer
            if (this.filterDebounceTimer) {
                clearTimeout(this.filterDebounceTimer);
            }

            // Set new timer with debouncing
            this.filterDebounceTimer = setTimeout(() => {
                console.log('⏰ Debounced filter change executing...');
                this.loadAllData();
            }, this.filterDebounceDelay);
        },

        // Auto-refresh system methods
        startAutoRefresh() {
            if (this.autoRefreshTimer) {
                clearInterval(this.autoRefreshTimer);
            }

            console.log('🔄 Starting auto-refresh with', this.autoRefreshInterval, 'ms interval');
            this.autoRefreshTimer = setInterval(() => {
                if (this.autoRefreshEnabled && !document.hidden) {
                    console.log('⏰ Auto-refresh triggered');
                    this.loadAllData();
                }
            }, this.autoRefreshInterval);
        },

        pauseAutoRefresh() {
            console.log('⏸️ Pausing auto-refresh');
            if (this.autoRefreshTimer) {
                clearInterval(this.autoRefreshTimer);
                this.autoRefreshTimer = null;
            }
        },

        resumeAutoRefresh() {
            if (this.autoRefreshEnabled) {
                console.log('▶️ Resuming auto-refresh');
                this.startAutoRefresh();
            }
        },

        toggleAutoRefresh() {
            this.autoRefreshEnabled = !this.autoRefreshEnabled;
            console.log('🔄 Auto-refresh toggled:', this.autoRefreshEnabled ? 'ON' : 'OFF');

            if (this.autoRefreshEnabled) {
                this.startAutoRefresh();
            } else {
                this.pauseAutoRefresh();
            }
        },

        // Update timestamp on successful data load
        updateLastUpdated() {
            this.lastUpdated = new Date().toLocaleTimeString('en-US', {
                hour12: true,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            console.log('🕒 Last updated:', this.lastUpdated);
        },

        // Legacy methods for backward compatibility
        updateSymbol() {
            this.handleFilterChange();
        },

        updateInterval() {
            this.handleFilterChange();
        },
    };
}

/**
 * Trade Bias Card Component
 */
function tradeBiasCard() {
    return {
        bias: "neutral",
        avgBuyerRatio: 0,
        avgSellerRatio: 0,
        strength: 0,
        sampleSize: 0,
        loading: false,

        init() {
            this.loadBias();

            // Listen to global events
            this.$watch("$root.selectedSymbol", () => this.loadBias());
            this.$watch("$root.selectedLimit", () => this.loadBias());
            window.addEventListener("refresh-all", () => this.loadBias());
        },

        async loadBias() {
            this.loading = true;
            const symbol = this.$root.selectedSymbol || "BTCUSDT";
            const limit = this.$root.selectedLimit || 200;

            try {
                const response = await fetch(
                    `${API_BASE_URL}/trade-bias?symbol=${symbol}&limit=${limit}`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch trade bias");
                }

                const data = await response.json();

                this.bias = data.bias || "neutral";
                this.avgBuyerRatio = data.avg_buyer_ratio || 0;
                this.avgSellerRatio = data.avg_seller_ratio || 0;
                this.strength = data.strength || 0;
                this.sampleSize = data.n || 0;

                console.log("✅ Trade bias loaded:", this.bias);
            } catch (error) {
                console.error("❌ Error loading trade bias:", error);
                this.bias = "neutral";
                this.avgBuyerRatio = 0;
                this.avgSellerRatio = 0;
            } finally {
                this.loading = false;
            }
        },

        getBiasClass() {
            if (this.bias === "buy") return "bg-success";
            if (this.bias === "sell") return "bg-danger";
            return "bg-secondary";
        },

        formatPercent(value) {
            return (value * 100).toFixed(2) + "%";
        },
    };
}

/**
 * CVD Table Component
 */
function cvdTable() {
    return {
        loading: false,
        cvdData: [],
        currentCvd: 0,
        avgCvd: 0,
        maxCvd: 0,
        minCvd: 0,

        init() {
            // Initialize with empty data first
            this.cvdData = [];
            this.loading = false;

            // Load data after a short delay to ensure DOM is ready
            setTimeout(() => {
                this.loadData();
            }, 100);

            // Listen to global events
            this.$watch("$root.selectedSymbol", () => this.loadData());
            this.$watch("$root.selectedLimit", () => this.loadData());
            window.addEventListener("refresh-all", () => this.loadData());
        },

        async loadData() {
            this.loading = true;
            console.log('📊 CVD Table: Loading data...');
            const symbol = this.$root.selectedSymbol || "BTCUSDT";
            const limit = this.$root.selectedLimit || 200;

            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout

                const response = await fetch(
                    `${API_BASE_URL}/cvd?exchange=binance&symbol=${symbol.toLowerCase()}&limit=${limit}`,
                    { signal: controller.signal }
                );

                clearTimeout(timeoutId);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: Failed to fetch CVD data`);
                }

                const result = await response.json();
                const data = result.data || [];

                if (data.length > 0) {
                    // Fix: API returns 'ts' field, not 'timestamp'
                    this.cvdData = data.sort((a, b) => new Date(b.ts) - new Date(a.ts));
                    this.calculateStats();
                    console.log("✅ CVD table loaded:", data.length, "records");
                } else {
                    console.warn("⚠️ No CVD data available");
                    this.cvdData = [];
                }
            } catch (error) {
                console.error("❌ Error loading CVD:", error);
                this.cvdData = [];
            } finally {
                this.loading = false;
                console.log("🔄 CVD loading finished, loading =", this.loading);
            }
        },

        calculateStats() {
            if (this.cvdData.length === 0) {
                this.currentCvd = 0;
                this.avgCvd = 0;
                this.maxCvd = 0;
                this.minCvd = 0;
                return;
            }

            const cvdValues = this.cvdData.map(d => parseFloat(d.cvd || 0));

            this.currentCvd = cvdValues[0]; // Most recent (first after sorting)
            this.avgCvd = cvdValues.reduce((a, b) => a + b, 0) / cvdValues.length;
            this.maxCvd = Math.max(...cvdValues);
            this.minCvd = Math.min(...cvdValues);
        },

        formatCvd(value) {
            if (value === null || value === undefined) return 'N/A';
            const num = parseFloat(value);
            if (isNaN(num)) return 'N/A';

            if (Math.abs(num) >= 1e6) return (num / 1e6).toFixed(2) + 'M';
            if (Math.abs(num) >= 1e3) return (num / 1e3).toFixed(1) + 'K';
            return num.toFixed(2);
        },

        formatTime(timestamp) {
            if (!timestamp) return 'N/A';
            const date = new Date(timestamp);
            if (isNaN(date.getTime())) return 'N/A';
            return date.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
            });
        },

        getCvdClass(value) {
            const num = parseFloat(value || 0);
            if (num > 0) return 'text-success';
            if (num < 0) return 'text-danger';
            return 'text-secondary';
        },

        getTrendClass(value) {
            const num = parseFloat(value || 0);
            if (num > 0) return 'bg-success';
            if (num < 0) return 'bg-danger';
            return 'bg-secondary';
        },

        getTrendText(value) {
            const num = parseFloat(value || 0);
            if (num > 0) return 'BULLISH';
            if (num < 0) return 'BEARISH';
            return 'NEUTRAL';
        },
    };
}

/**
 * CVD Stats Component
 */
function cvdStats() {
    return {
        currentCVD: 0,
        cvdChange: 0,
        maxCVD: 0,
        minCVD: 0,
        loading: false,

        init() {
            this.loadStats();

            // Listen to global events
            this.$watch("$root.selectedSymbol", () => this.loadStats());
            this.$watch("$root.selectedLimit", () => this.loadStats());
            window.addEventListener("refresh-all", () => this.loadStats());
        },

        async loadStats() {
            this.loading = true;
            const symbol = this.$root.selectedSymbol || "BTCUSDT";
            const limit = this.$root.selectedLimit || 200;

            try {
                const response = await fetch(
                    `${API_BASE_URL}/cvd?exchange=binance&symbol=${symbol.toLowerCase()}&limit=${limit}`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch CVD data");
                }

                const result = await response.json();
                const data = result.data || [];

                if (data.length > 0) {
                    const cvdValues = data.map((d) => d.cvd);
                    this.currentCVD = cvdValues[cvdValues.length - 1];
                    this.maxCVD = Math.max(...cvdValues);
                    this.minCVD = Math.min(...cvdValues);

                    if (cvdValues.length > 1) {
                        this.cvdChange =
                            this.currentCVD - cvdValues[cvdValues.length - 2];
                    }

                    console.log("✅ CVD stats loaded");
                } else {
                    this.resetStats();
                }
            } catch (error) {
                console.error("❌ Error loading CVD stats:", error);
                this.resetStats();
            } finally {
                this.loading = false;
            }
        },

        resetStats() {
            this.currentCVD = 0;
            this.cvdChange = 0;
            this.maxCVD = 0;
            this.minCVD = 0;
        },

        formatNumber(value) {
            if (value === 0) return "0";
            return value.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },

        formatChange(value) {
            const sign = value >= 0 ? "+" : "";
            return sign + this.formatNumber(value);
        },
    };
}

/**
 * Trade Summary Table Component
 */
function tradeSummaryTable() {
    return {
        trades: [],
        loading: false,

        init() {
            // Initialize with empty data first
            this.trades = [];
            this.loading = false;

            // Load data after a short delay to ensure DOM is ready
            setTimeout(() => {
                this.loadSummary();
            }, 100);

            // Listen to global events
            this.$watch("$root.selectedSymbol", () => this.loadSummary());
            this.$watch("$root.selectedInterval", () => this.loadSummary());
            this.$watch("$root.selectedLimit", () => this.loadSummary());
            window.addEventListener("refresh-all", () => this.loadSummary());
        },

        async loadSummary() {
            this.loading = true;
            const symbol = this.$root.selectedSymbol || "BTCUSDT";
            const interval = this.$root.selectedInterval || "5m";
            const limit = this.$root.selectedLimit || 200;

            try {
                console.log("🔄 Loading trade summary for:", symbol, interval, limit);

                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout

                const response = await fetch(
                    `${API_BASE_URL}/trades/summary?symbol=${symbol}&interval=${interval}&limit=${limit}`,
                    { signal: controller.signal }
                );

                clearTimeout(timeoutId);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: Failed to fetch trade summary`);
                }

                const result = await response.json();
                this.trades = result.data || [];

                console.log(
                    "✅ Trade summary loaded:",
                    this.trades.length,
                    "records"
                );
            } catch (error) {
                console.error("❌ Error loading trade summary:", error);
                this.trades = [];
            } finally {
                this.loading = false;
                console.log("🔄 Trade summary loading finished, loading =", this.loading);
            }
        },

        formatTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleTimeString("en-US", {
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
            });
        },

        formatPrice(price) {
            return (
                "$" +
                price.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                })
            );
        },

        formatVolume(volume) {
            if (volume === 0) return "$0";
            const absVolume = Math.abs(volume);
            if (absVolume >= 1000000) {
                return (
                    (volume >= 0 ? "$" : "-$") +
                    (absVolume / 1000000).toFixed(2) +
                    "M"
                );
            } else if (absVolume >= 1000) {
                return (
                    (volume >= 0 ? "$" : "-$") +
                    (absVolume / 1000).toFixed(2) +
                    "K"
                );
            }
            return (volume >= 0 ? "$" : "-$") + absVolume.toFixed(2);
        },
    };
}

/**
 * Volume Flow Stats Component
 */
function volumeFlowStats() {
    return {
        totalBuyVolume: 0,
        totalSellVolume: 0,
        netFlow: 0,
        totalTrades: 0,
        avgTradeSize: 0,
        loading: false,

        init() {
            this.loadStats();

            // Listen to global events
            this.$watch("$root.selectedSymbol", () => this.loadStats());
            this.$watch("$root.selectedInterval", () => this.loadStats());
            this.$watch("$root.selectedLimit", () => this.loadStats());
            window.addEventListener("refresh-all", () => this.loadStats());
        },

        async loadStats() {
            this.loading = true;
            const symbol = this.$root.selectedSymbol || "BTCUSDT";
            const interval = this.$root.selectedInterval || "5m";
            const limit = this.$root.selectedLimit || 200;

            try {
                const response = await fetch(
                    `${API_BASE_URL}/trades/summary?symbol=${symbol}&interval=${interval}&limit=${limit}`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch trade summary");
                }

                const result = await response.json();
                const data = result.data || [];

                if (data.length > 0) {
                    this.totalBuyVolume = data.reduce(
                        (sum, d) => sum + (d.buy_volume_quote || 0),
                        0
                    );
                    this.totalSellVolume = data.reduce(
                        (sum, d) => sum + (d.sell_volume_quote || 0),
                        0
                    );
                    this.netFlow = data.reduce(
                        (sum, d) => sum + (d.net_flow_quote || 0),
                        0
                    );
                    this.totalTrades = data.reduce(
                        (sum, d) => sum + (d.trades_count || 0),
                        0
                    );

                    if (this.totalTrades > 0) {
                        this.avgTradeSize =
                            (this.totalBuyVolume + this.totalSellVolume) /
                            this.totalTrades;
                    }

                    console.log("✅ Volume flow stats loaded");
                } else {
                    this.resetStats();
                }
            } catch (error) {
                console.error("❌ Error loading volume flow stats:", error);
                this.resetStats();
            } finally {
                this.loading = false;
            }
        },

        resetStats() {
            this.totalBuyVolume = 0;
            this.totalSellVolume = 0;
            this.netFlow = 0;
            this.totalTrades = 0;
            this.avgTradeSize = 0;
        },

        formatVolume(volume) {
            if (volume === 0) return "$0";
            const absVolume = Math.abs(volume);
            if (absVolume >= 1000000) {
                return (
                    (volume >= 0 ? "$" : "-$") +
                    (absVolume / 1000000).toFixed(2) +
                    "M"
                );
            } else if (absVolume >= 1000) {
                return (
                    (volume >= 0 ? "$" : "-$") +
                    (absVolume / 1000).toFixed(2) +
                    "K"
                );
            }
            return (volume >= 0 ? "$" : "-$") + absVolume.toFixed(2);
        },
    };
}

/**
 * Recent Trades Stream Component
 */
function recentTradesStream() {
    return {
        trades: [],
        loading: false,

        init() {
            // Initialize with empty data first
            this.trades = [];
            this.loading = false;

            // Load data after a short delay to ensure DOM is ready
            setTimeout(() => {
                this.loadTrades();
            }, 100);

            // Listen to global events
            this.$watch("$root.selectedSymbol", () => this.loadTrades());
            this.$watch("$root.selectedLimit", () => this.loadTrades());
            window.addEventListener("refresh-all", () => this.loadTrades());
        },

        async loadTrades() {
            this.loading = true;
            const symbol = this.$root.selectedSymbol || "BTCUSDT";
            const limit = Math.min(this.$root.selectedLimit || 200, 100); // Cap at 100 for recent trades

            try {
                console.log("🔄 Loading recent trades for:", symbol, limit);

                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout

                const response = await fetch(
                    `${API_BASE_URL}/trades?symbol=${symbol}&limit=${limit}`,
                    { signal: controller.signal }
                );

                clearTimeout(timeoutId);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: Failed to fetch recent trades`);
                }

                const result = await response.json();
                this.trades = result.data || [];

                console.log(
                    "✅ Recent trades loaded:",
                    this.trades.length,
                    "trades"
                );
            } catch (error) {
                console.error("❌ Error loading recent trades:", error);
                this.trades = [];
            } finally {
                this.loading = false;
                console.log("🔄 Recent trades loading finished, loading =", this.loading);
            }
        },

        refresh() {
            this.loadTrades();
        },

        formatTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleTimeString("en-US", {
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
            });
        },

        formatPrice(price) {
            return (
                "$" +
                price.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                })
            );
        },

        formatVolume(volume) {
            if (volume === 0) return "$0";
            const absVolume = Math.abs(volume);
            if (absVolume >= 1000) {
                return "$" + (absVolume / 1000).toFixed(2) + "K";
            }
            return "$" + absVolume.toFixed(2);
        },
    };
}

// Shared State Management for Spot Microstructure
window.SpotMicrostructureSharedState = {
    filters: {
        selectedSymbol: 'BTCUSDT',
        selectedInterval: '5m',
        selectedLimit: 200,
        selectedExchange: 'binance'
    },

    subscribers: {},

    setFilter(key, value) {
        if (this.filters[key] !== value) {
            this.filters[key] = value;
            this.notifySubscribers(key, value);
        }
    },

    getFilter(key) {
        return this.filters[key];
    },

    getAllFilters() {
        return { ...this.filters };
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

console.log("✅ Trades controller loaded with shared state management");
