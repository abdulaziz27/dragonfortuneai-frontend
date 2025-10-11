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
    return "http://202.155.90.20:8000";
}

const API_BASE_URL = getApiBaseUrl() + "/api/spot-microstructure";

/**
 * Main Orderbook Controller
 */
function orderbookController() {
    return {
        // Global state
        globalSymbol: "BTCUSDT",
        globalExchange: "binance",
        globalLoading: false,

        // Initialize
        init() {
            console.log("🚀 Orderbook Snapshots Dashboard initialized");
            console.log("📊 Symbol:", this.globalSymbol);
            console.log("🏦 Exchange:", this.globalExchange);
        },

        // Update symbol globally
        updateSymbol() {
            console.log("📊 Symbol changed to:", this.globalSymbol);
            window.dispatchEvent(
                new CustomEvent("symbol-changed", {
                    detail: {
                        symbol: this.globalSymbol,
                        exchange: this.globalExchange,
                    },
                })
            );
        },

        // Update exchange globally
        updateExchange() {
            console.log("🏦 Exchange changed to:", this.globalExchange);
            window.dispatchEvent(
                new CustomEvent("exchange-changed", {
                    detail: {
                        symbol: this.globalSymbol,
                        exchange: this.globalExchange,
                    },
                })
            );
        },

        // Refresh all components
        async refreshAll() {
            this.globalLoading = true;
            console.log("🔄 Refreshing all components...");

            window.dispatchEvent(
                new CustomEvent("refresh-all", {
                    detail: {
                        symbol: this.globalSymbol,
                        exchange: this.globalExchange,
                    },
                })
            );

            setTimeout(() => {
                this.globalLoading = false;
                console.log("✅ All components refreshed");
            }, 2000);
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

            // Listen to global events
            window.addEventListener("symbol-changed", () =>
                this.loadPressure()
            );
            window.addEventListener("exchange-changed", () =>
                this.loadPressure()
            );
            window.addEventListener("refresh-all", () => this.loadPressure());
        },

        async loadPressure() {
            this.loading = true;
            const symbol = this.$root?.globalSymbol || "BTCUSDT";
            const exchange = this.$root?.globalExchange || "binance";

            try {
                const response = await fetch(
                    `${API_BASE_URL}/book-pressure?symbol=${symbol}&exchange=${exchange}&limit=100`
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

            // Listen to global events
            window.addEventListener("symbol-changed", () =>
                this.loadLiquidity()
            );
            window.addEventListener("refresh-all", () => this.loadLiquidity());
        },

        async loadLiquidity() {
            this.loading = true;
            const symbol = this.$root?.globalSymbol || "BTCUSDT";

            try {
                const response = await fetch(
                    `${API_BASE_URL}/orderbook/liquidity?symbol=${symbol}&depth=20`
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
            const symbol = this.$root?.globalSymbol || "BTCUSDT";
            const exchange = this.$root?.globalExchange || "binance";

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
            const symbol = this.$root?.globalSymbol || "BTCUSDT";

            try {
                const response = await fetch(
                    `${API_BASE_URL}/orderbook/snapshot?symbol=${symbol}&depth=1`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch orderbook snapshot");
                }

                const data = await response.json();

                if (
                    data.asks &&
                    data.asks.length > 0 &&
                    data.bids &&
                    data.bids.length > 0
                ) {
                    const bestAsk = data.asks[0].price;
                    const bestBid = data.bids[0].price;

                    this.currentSpread = bestAsk - bestBid;
                    this.midPrice = (bestAsk + bestBid) / 2;
                    this.spreadPercent =
                        (this.currentSpread / this.midPrice) * 100;

                    console.log("✅ Quick stats loaded");
                } else {
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
            return (
                "$" +
                value.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                })
            );
        },

        formatPercent(value) {
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

            // Auto refresh every 5 seconds
            setInterval(() => this.loadSnapshot(), 5000);

            // Listen to global events
            window.addEventListener("symbol-changed", () =>
                this.loadSnapshot()
            );
            window.addEventListener("refresh-all", () => this.loadSnapshot());
        },

        async loadSnapshot() {
            this.loading = true;
            const symbol = this.$root?.globalSymbol || "BTCUSDT";

            try {
                const response = await fetch(
                    `${API_BASE_URL}/orderbook/snapshot?symbol=${symbol}&depth=15`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch orderbook snapshot");
                }

                const data = await response.json();

                this.bids = (data.bids || []).slice(0, 10);
                this.asks = (data.asks || []).slice(0, 10).reverse(); // Reverse asks for display

                if (this.bids.length > 0 && this.asks.length > 0) {
                    const bestAsk = this.asks[0].price;
                    const bestBid = this.bids[0].price;
                    this.midPrice = (bestAsk + bestBid) / 2;
                    this.spread = bestAsk - bestBid;
                }

                console.log("✅ Orderbook snapshot loaded");
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
            return Math.max(...orders.map((o) => o.quantity));
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

        formatQuantity(quantity) {
            return quantity.toFixed(4);
        },

        formatTotal(price, quantity) {
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
function bookPressureChart() {
    return {
        chart: null,
        loading: false,
        dataPoints: 0,

        init() {
            this.loadChart();

            // Listen to global events
            window.addEventListener("symbol-changed", () => this.loadChart());
            window.addEventListener("exchange-changed", () => this.loadChart());
            window.addEventListener("refresh-all", () => this.loadChart());
        },

        async loadChart() {
            this.loading = true;
            const symbol = this.$root?.globalSymbol || "BTCUSDT";
            const exchange = this.$root?.globalExchange || "binance";

            try {
                const response = await fetch(
                    `${API_BASE_URL}/book-pressure?symbol=${symbol}&exchange=${exchange}&limit=100`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch book pressure data");
                }

                const result = await response.json();
                const data = result.data || [];

                this.dataPoints = data.length;

                if (data.length > 0) {
                    this.renderChart(data);
                    console.log(
                        "✅ Book pressure chart loaded:",
                        data.length,
                        "points"
                    );
                } else {
                    if (this.chart) {
                        this.chart.destroy();
                        this.chart = null;
                    }
                }
            } catch (error) {
                console.error("❌ Error loading book pressure chart:", error);
                this.dataPoints = 0;
            } finally {
                this.loading = false;
            }
        },

        renderChart(data) {
            const ctx = document.getElementById("bookPressureChart");
            if (!ctx) return;

            if (this.chart) {
                this.chart.destroy();
            }

            const labels = data.map((d) => new Date(d.timestamp));
            const bidPressure = data.map((d) => d.bid_pressure);
            const askPressure = data.map((d) => d.ask_pressure);

            this.chart = new Chart(ctx, {
                type: "line",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "Bid Pressure",
                            data: bidPressure,
                            borderColor: "#22c55e",
                            backgroundColor: "rgba(34, 197, 94, 0.1)",
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2,
                        },
                        {
                            label: "Ask Pressure",
                            data: askPressure,
                            borderColor: "#ef4444",
                            backgroundColor: "rgba(239, 68, 68, 0.1)",
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: "top",
                        },
                    },
                    scales: {
                        x: {
                            type: "time",
                            time: {
                                unit: "minute",
                            },
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: "rgba(255, 255, 255, 0.1)",
                            },
                        },
                    },
                },
            });
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
            const symbol = this.$root?.globalSymbol || "BTCUSDT";
            const exchange = this.$root?.globalExchange || "binance";

            try {
                const response = await fetch(
                    `${API_BASE_URL}/liquidity-heatmap?symbol=${symbol}&exchange=${exchange}&limit=20`
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
            const symbol = this.$root?.globalSymbol || "BTCUSDT";
            const exchange = this.$root?.globalExchange || "binance";

            try {
                const response = await fetch(
                    `${API_BASE_URL}/market-depth?symbol=${symbol}&exchange=${exchange}&limit=20`
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
            const symbol = this.$root?.globalSymbol || "BTCUSDT";
            const exchange = this.$root?.globalExchange || "binance";

            try {
                const response = await fetch(
                    `${API_BASE_URL}/orderbook-depth?symbol=${symbol}&exchange=${exchange}&limit=20`
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch orderbook depth data");
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
            return (
                "$" +
                price.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                })
            );
        },

        formatQuantity(quantity) {
            return quantity.toFixed(4);
        },

        formatTotal(total) {
            if (total >= 1000000) {
                return "$" + (total / 1000000).toFixed(2) + "M";
            } else if (total >= 1000) {
                return "$" + (total / 1000).toFixed(2) + "K";
            }
            return "$" + total.toFixed(2);
        },
    };
}

console.log("✅ Orderbook controller loaded");
