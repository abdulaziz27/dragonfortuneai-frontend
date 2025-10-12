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
        globalSymbol: "BTCUSDT",
        globalInterval: "1m",
        globalLoading: false,

        // Initialize
        init() {
            console.log("🚀 Trades Analysis Dashboard initialized");
            console.log("📊 Symbol:", this.globalSymbol);
            console.log("⏱️ Interval:", this.globalInterval);
        },

        // Update symbol globally
        updateSymbol() {
            console.log("📊 Symbol changed to:", this.globalSymbol);
            this.$dispatch("symbol-changed", { symbol: this.globalSymbol });
        },

        // Update interval globally
        updateInterval() {
            console.log("⏱️ Interval changed to:", this.globalInterval);
            this.$dispatch("interval-changed", {
                interval: this.globalInterval,
            });
        },

        // Refresh all components
        async refreshAll() {
            this.globalLoading = true;
            console.log("🔄 Refreshing all components...");
            this.$dispatch("refresh-all");

            setTimeout(() => {
                this.globalLoading = false;
            }, 2000);
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
            this.$watch("$root.globalSymbol", () => this.loadBias());
            window.addEventListener("refresh-all", () => this.loadBias());
        },

        async loadBias() {
            this.loading = true;
            const symbol = this.$root.globalSymbol || "BTCUSDT";

            try {
                const response = await fetch(
                    `${API_BASE_URL}/trade-bias?symbol=${symbol}&limit=1000`
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
            this.loadData();

            // Listen to global events
            this.$watch("$root.globalSymbol", () => this.loadData());
            window.addEventListener("refresh-all", () => this.loadData());
        },

        async loadData() {
            this.loading = true;
            console.log('📊 CVD Table: Loading data...');
            const symbol = this.$root.globalSymbol || "BTCUSDT";

            try {
                const response = await fetch(
                    `${API_BASE_URL}/cvd?exchange=binance&symbol=${symbol.toLowerCase()}&limit=100`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch CVD data");
                }

                const result = await response.json();
                const data = result.data || [];

                if (data.length > 0) {
                    this.cvdData = data.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
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
            this.$watch("$root.globalSymbol", () => this.loadStats());
            window.addEventListener("refresh-all", () => this.loadStats());
        },

        async loadStats() {
            this.loading = true;
            const symbol = this.$root.globalSymbol || "BTCUSDT";

            try {
                const response = await fetch(
                    `${API_BASE_URL}/cvd?exchange=binance&symbol=${symbol.toLowerCase()}&limit=500`
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
            this.loadSummary();

            // Listen to global events
            this.$watch("$root.globalSymbol", () => this.loadSummary());
            this.$watch("$root.globalInterval", () => this.loadSummary());
            window.addEventListener("refresh-all", () => this.loadSummary());
        },

        async loadSummary() {
            this.loading = true;
            const symbol = this.$root.globalSymbol || "BTCUSDT";
            const interval = this.$root.globalInterval || "1m";

            try {
                const response = await fetch(
                    `${API_BASE_URL}/trades/summary?symbol=${symbol}&interval=${interval}&limit=100`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch trade summary");
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
            this.$watch("$root.globalSymbol", () => this.loadStats());
            this.$watch("$root.globalInterval", () => this.loadStats());
            window.addEventListener("refresh-all", () => this.loadStats());
        },

        async loadStats() {
            this.loading = true;
            const symbol = this.$root.globalSymbol || "BTCUSDT";
            const interval = this.$root.globalInterval || "1m";

            try {
                const response = await fetch(
                    `${API_BASE_URL}/trades/summary?symbol=${symbol}&interval=${interval}&limit=100`
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
            this.loadTrades();

            // Listen to global events
            this.$watch("$root.globalSymbol", () => this.loadTrades());
            window.addEventListener("refresh-all", () => this.loadTrades());
        },

        async loadTrades() {
            this.loading = true;
            const symbol = this.$root.globalSymbol || "BTCUSDT";

            try {
                const response = await fetch(
                    `${API_BASE_URL}/trades?symbol=${symbol}&limit=50`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch recent trades");
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

console.log("✅ Trades controller loaded");
