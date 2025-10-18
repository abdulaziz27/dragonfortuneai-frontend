/**
 * Liquidations Dashboard Controller
 *
 * Comprehensive liquidation analytics dengan 6 API endpoints
 *
 * Think like a trader:
 * - Liquidations = forced market orders = volatility drivers
 * - Cascade events = chain reactions = extreme volatility
 * - Long liquidations = sell pressure = price drops
 * - Short liquidations = buy pressure = price pumps
 *
 * Build like an engineer:
 * - Modular components dengan event communication
 * - Real-time streaming data support
 * - Efficient data caching dan aggregation
 *
 * Visualize like a designer:
 * - Color-coded untuk quick insights (red=long liq, green=short liq)
 * - Real-time updates dengan smooth transitions
 * - Multi-timeframe analysis
 */

function liquidationsController() {
    return {
        // Global state
        globalSymbol: "BTC",
        globalExchange: "Binance", // Default to Binance since exchange filter is hidden
        globalInterval: "5m", // Use 5m as default since it has data
        globalLimit: "2000",
        globalLoading: false,

        // Auto refresh state
        autoRefreshEnabled: true,
        autoRefreshInterval: 5000, // 5 seconds
        autoRefreshTimer: null,
        lastUpdated: null,

        // Overview data dari semua endpoints
        overview: null,

        // Cache untuk optimize performance
        cache: {
            analytics: null,
            coinList: null,
            exchangeList: null,
            orders: null,
            pairHistory: null,
        },

        // Initialize dashboard
        init() {
            console.log("🚀 Liquidations Dashboard initialized");
            console.log("📊 Symbol:", this.globalSymbol);
            console.log("🏦 Exchange:", this.globalExchange || "All");

            // Setup event listeners
            this.setupEventListeners();

            // Setup auto refresh
            this.setupAutoRefresh();

            // Load initial overview
            this.loadOverview().catch((e) =>
                console.warn("Initial overview load failed:", e)
            );

            // Log dashboard ready
            setTimeout(() => {
                console.log("✅ Liquidations dashboard loaded");
                this.logDashboardStatus();
            }, 2000);
        },

        // Setup global event listeners
        setupEventListeners() {
            // Listen for filter changes
            window.addEventListener("symbol-changed", () => {
                this.loadOverview().catch((e) =>
                    console.warn("Overview reload failed:", e)
                );
            });

            window.addEventListener("exchange-changed", () => {
                this.loadOverview().catch((e) =>
                    console.warn("Overview reload failed:", e)
                );
            });

            window.addEventListener("interval-changed", () => {
                this.loadOverview().catch((e) =>
                    console.warn("Overview reload failed:", e)
                );
            });

            window.addEventListener("refresh-all", () => {
                this.cache = {}; // Clear cache on manual refresh
                this.loadOverview().catch((e) =>
                    console.warn("Overview reload failed:", e)
                );
            });

            // Removed old auto-refresh - now handled by setupAutoRefresh()
        },

        // Update symbol globally
        updateSymbol() {
            console.log("🔄 Updating symbol to:", this.globalSymbol);

            window.dispatchEvent(
                new CustomEvent("symbol-changed", {
                    detail: {
                        symbol: this.globalSymbol,
                        exchange: this.globalExchange,
                        interval: this.globalInterval,
                    },
                })
            );

            this.updateURL();
        },

        // Update exchange filter
        updateExchange() {
            console.log("🔄 Updating exchange to:", this.globalExchange);

            window.dispatchEvent(
                new CustomEvent("exchange-changed", {
                    detail: {
                        symbol: this.globalSymbol,
                        exchange: this.globalExchange,
                        interval: this.globalInterval,
                    },
                })
            );

            this.updateURL();
        },

        // Update interval
        updateInterval() {
            console.log("🔄 Updating interval to:", this.globalInterval);

            window.dispatchEvent(
                new CustomEvent("interval-changed", {
                    detail: {
                        symbol: this.globalSymbol,
                        exchange: this.globalExchange,
                        interval: this.globalInterval,
                        limit: this.globalLimit,
                    },
                })
            );

            this.updateURL();
        },

        // Update data limit
        updateLimit() {
            console.log("🔄 Updating data limit to:", this.globalLimit);

            window.dispatchEvent(
                new CustomEvent("limit-changed", {
                    detail: {
                        symbol: this.globalSymbol,
                        exchange: this.globalExchange,
                        interval: this.globalInterval,
                        limit: this.globalLimit,
                    },
                })
            );

            this.updateURL();
        },

        // Toggle auto refresh
        toggleAutoRefresh() {
            this.autoRefreshEnabled = !this.autoRefreshEnabled;
            console.log("🔄 Auto refresh:", this.autoRefreshEnabled ? "Enabled" : "Disabled");
            
            if (this.autoRefreshEnabled) {
                this.startAutoRefresh();
            } else {
                this.stopAutoRefresh();
            }
        },

        // Setup auto refresh system
        setupAutoRefresh() {
            if (this.autoRefreshEnabled) {
                this.startAutoRefresh();
            }

            // Listen for visibility changes to pause/resume auto refresh
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.pauseAutoRefresh();
                } else if (this.autoRefreshEnabled) {
                    this.resumeAutoRefresh();
                }
            });
        },

        // Start auto refresh timer
        startAutoRefresh() {
            this.stopAutoRefresh(); // Clear any existing timers
            
            if (!this.autoRefreshEnabled) return;

            console.log("▶️ Starting liquidations auto refresh timer");
            
            // Start main refresh timer
            this.autoRefreshTimer = setInterval(() => {
                if (!this.globalLoading && this.autoRefreshEnabled) {
                    console.log("🔄 Liquidations auto refresh triggered");
                    this.refreshAll();
                }
            }, this.autoRefreshInterval);
        },

        // Stop auto refresh timer
        stopAutoRefresh() {
            if (this.autoRefreshTimer) {
                clearInterval(this.autoRefreshTimer);
                this.autoRefreshTimer = null;
                console.log("⏹️ Liquidations auto refresh timer stopped");
            }
        },

        // Pause auto refresh (when tab hidden)
        pauseAutoRefresh() {
            if (this.autoRefreshTimer) {
                this.stopAutoRefresh();
                console.log("⏸️ Liquidations auto refresh paused (tab hidden)");
            }
        },

        // Resume auto refresh (when tab visible)
        resumeAutoRefresh() {
            if (this.autoRefreshEnabled && !this.autoRefreshTimer) {
                this.startAutoRefresh();
                console.log("▶️ Liquidations auto refresh resumed (tab visible)");
            }
        },

        // Build pair from symbol
        buildPair(baseSymbol) {
            const sym = (
                baseSymbol ||
                this.globalSymbol ||
                "BTC"
            ).toUpperCase();
            return `${sym}USDT`;
        },

        // Load comprehensive overview dari semua endpoints
        async loadOverview() {
            this.globalLoading = true;
            const symbol = this.globalSymbol;
            const pair = this.buildPair(symbol);
            const exchange = this.globalExchange;
            const interval = this.globalInterval;
            const limit = this.globalLimit;

            try {
                // Execute all API calls in parallel
                // Fetch all data in parallel with error handling
                const results = await Promise.allSettled([
                    // 1. Analytics - comprehensive metrics
                    this.fetchAPI("analytics", {
                        symbol: pair,
                        interval,
                        limit,
                        ...(exchange && { exchange }),
                    }),

                    // 2. Coin List - multi-range snapshot per exchange
                    this.fetchAPI("coin-list", {
                        symbol: symbol,
                        limit: 1000,
                        ...(exchange && { exchange }),
                    }),

                    // 3-6. Exchange List - different time ranges
                    this.fetchAPI("exchange-list", {
                        symbol: symbol,
                        range_str: "1h",
                        limit: 1000,
                    }),
                    this.fetchAPI("exchange-list", {
                        symbol: symbol,
                        range_str: "4h",
                        limit: 1000,
                    }),
                    this.fetchAPI("exchange-list", {
                        symbol: symbol,
                        range_str: "12h",
                        limit: 1000,
                    }),
                    this.fetchAPI("exchange-list", {
                        symbol: symbol,
                        range_str: "24h",
                        limit: 1000,
                    }),

                    // 7. Orders - real-time liquidation stream
                    this.fetchAPI("orders", {
                        limit: 500,
                        ...(exchange && { exchange }),
                        ...(symbol && { symbol: pair }),
                    }),

                    // 8. Pair History - bucketed time series
                    this.fetchAPI("pair-history", {
                        symbol: pair,
                        interval,
                        limit,
                        with_price: true,
                        ...(exchange && { exchange }),
                    }),
                ]);

                // Process results with error handling
                const analytics =
                    results[0].status === "fulfilled" ? results[0].value : null;
                const coinList =
                    results[1].status === "fulfilled" ? results[1].value : null;
                const exchangeList1h =
                    results[2].status === "fulfilled" ? results[2].value : null;
                const exchangeList4h =
                    results[3].status === "fulfilled" ? results[3].value : null;
                const exchangeList12h =
                    results[4].status === "fulfilled" ? results[4].value : null;
                const exchangeList24h =
                    results[5].status === "fulfilled" ? results[5].value : null;
                const orders =
                    results[6].status === "fulfilled" ? results[6].value : null;
                const pairHistory =
                    results[7].status === "fulfilled" ? results[7].value : null;

                // Log any failed requests
                results.forEach((result, index) => {
                    if (result.status === "rejected") {
                        console.warn(
                            `❌ API request ${index + 1} failed:`,
                            result.reason
                        );
                    }
                });

                // Store in cache
                this.cache.analytics = analytics;
                this.cache.coinList = coinList;
                this.cache.exchangeList = {
                    "1h": exchangeList1h,
                    "4h": exchangeList4h,
                    "12h": exchangeList12h,
                    "24h": exchangeList24h,
                };
                this.cache.orders = orders;
                this.cache.pairHistory = pairHistory;

                // Process pair history data for charts
                console.log(
                    "📊 Liquidations Controller: Processing pair history data",
                    pairHistory
                );
                const processedPairHistory = Array.isArray(pairHistory?.data)
                    ? pairHistory.data.map((item) => ({
                          ts: item.ts || item.time || Date.now(),
                          exchange: item.exchange || "Unknown",
                          long_liq_usd: parseFloat(
                              item.long_liq_usd || item.long_usd || 0
                          ),
                          short_liq_usd: parseFloat(
                              item.short_liq_usd || item.short_usd || 0
                          ),
                          liq_usd: parseFloat(
                              item.liq_usd || item.total_usd || 0
                          ),
                          price: parseFloat(item.price || 0),
                      }))
                    : [];

                console.log(
                    "📊 Liquidations Controller: Processed pair history data points:",
                    processedPairHistory.length
                );

                // Build comprehensive overview
                this.overview = {
                    meta: {
                        symbol,
                        pair,
                        exchange: exchange || "All",
                        interval,
                        limit,
                        last_updated: Date.now(),
                    },
                    analytics: analytics || {},
                    coinList: coinList?.data || [],
                    exchangeList: {
                        "1h": exchangeList1h?.data || [],
                        "4h": exchangeList4h?.data || [],
                        "12h": exchangeList12h?.data || [],
                        "24h": exchangeList24h?.data || [],
                    },
                    orders: orders?.data || [],
                    pairHistory: processedPairHistory,
                };

                // Broadcast overview ready event
                console.log(
                    "📊 Liquidations Controller: Broadcasting overview ready event"
                );
                window.dispatchEvent(
                    new CustomEvent("liquidations-overview-ready", {
                        detail: this.overview,
                    })
                );

                // Update last updated timestamp
                this.lastUpdated = new Date().toLocaleTimeString();

                console.log("✅ Overview loaded:", this.overview);
                console.log(
                    "📊 Liquidations Controller: Pair history data points:",
                    this.overview.pairHistory.length
                );
                return this.overview;
            } catch (error) {
                console.error("❌ Error loading overview:", error);
                throw error;
            } finally {
                this.globalLoading = false;
            }
        },

        // Update URL with filters
        updateURL() {
            if (window.history && window.history.pushState) {
                const url = new URL(window.location);
                url.searchParams.set("symbol", this.globalSymbol);
                url.searchParams.set("exchange", this.globalExchange || "");
                url.searchParams.set("interval", this.globalInterval);
                window.history.pushState({}, "", url);
            }
        },

        // Refresh all components
        refreshAll() {
            this.globalLoading = true;
            console.log("🔄 Refreshing all components...");

            // Clear cache
            this.cache = {};

            // Dispatch refresh event
            window.dispatchEvent(
                new CustomEvent("refresh-all", {
                    detail: {
                        symbol: this.globalSymbol,
                        exchange: this.globalExchange,
                        interval: this.globalInterval,
                    },
                })
            );

            // Update last updated timestamp
            this.lastUpdated = new Date().toLocaleTimeString();

            // Reset loading state
            setTimeout(() => {
                this.globalLoading = false;
                console.log("✅ All components refreshed");
            }, 2000);
        },

        // Log dashboard status
        logDashboardStatus() {
            console.group("📊 Liquidations Dashboard Status");
            console.log("Symbol:", this.globalSymbol);
            console.log("Exchange:", this.globalExchange || "All");
            console.log("Interval:", this.globalInterval);
            const baseMeta = document.querySelector(
                'meta[name="api-base-url"]'
            );
            const baseUrl = (baseMeta?.content || "").trim() || "(relative)";
            console.log("API Base:", baseUrl);
            console.groupEnd();
        },

        // Utility: Format USD value
        formatUSD(value) {
            if (value === null || value === undefined) return "N/A";
            const num = parseFloat(value);
            if (isNaN(num)) return "N/A";

            if (num >= 1e9) return "$" + (num / 1e9).toFixed(2) + "B";
            if (num >= 1e6) return "$" + (num / 1e6).toFixed(2) + "M";
            if (num >= 1e3) return "$" + (num / 1e3).toFixed(2) + "K";
            return "$" + num.toFixed(2);
        },

        // Utility: Format timestamp
        formatTimestamp(timestamp) {
            if (!timestamp) return "N/A";
            const date = new Date(timestamp);
            return date.toLocaleString("en-US", {
                month: "short",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
                hour12: false,
            });
        },

        // Utility: Format time ago
        timeAgo(timestamp) {
            if (!timestamp) return "N/A";
            const diff = Date.now() - timestamp;
            const seconds = Math.floor(diff / 1000);
            const minutes = Math.floor(seconds / 60);
            const hours = Math.floor(minutes / 60);
            const days = Math.floor(hours / 24);

            if (days > 0) return `${days}d ago`;
            if (hours > 0) return `${hours}h ago`;
            if (minutes > 0) return `${minutes}m ago`;
            return `${seconds}s ago`;
        },

        // Utility: Get side color class
        getSideColorClass(side) {
            const sideStr = (side || "").toString().toLowerCase();
            if (sideStr === "long" || sideStr === "1") return "text-danger";
            if (sideStr === "short" || sideStr === "2") return "text-success";
            return "text-secondary";
        },

        // Utility: Get side label
        getSideLabel(side) {
            const sideStr = (side || "").toString().toLowerCase();
            if (sideStr === "long" || sideStr === "1") return "Long";
            if (sideStr === "short" || sideStr === "2") return "Short";
            return "Unknown";
        },

        // Utility: Calculate long/short ratio
        calculateRatio(longValue, shortValue) {
            const long = parseFloat(longValue) || 0;
            const short = parseFloat(shortValue) || 0;
            if (short === 0) return long > 0 ? "∞" : "N/A";
            return (long / short).toFixed(2);
        },

        // API Helper: Fetch with error handling
        async fetchAPI(endpoint, params = {}) {
            const queryString = new URLSearchParams(params).toString();
            const baseMeta = document.querySelector(
                'meta[name="api-base-url"]'
            );
            const configuredBase = (baseMeta?.content || "").trim();

            let url = `/api/liquidations/${endpoint}?${queryString}`;
            if (configuredBase) {
                const normalizedBase = configuredBase.endsWith("/")
                    ? configuredBase.slice(0, -1)
                    : configuredBase;
                url = `${normalizedBase}/api/liquidations/${endpoint}?${queryString}`;
            }

            try {
                console.log("📡 Fetching:", endpoint, params);
                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error(
                        `HTTP ${response.status}: ${response.statusText}`
                    );
                }

                const data = await response.json();
                const itemCount = Array.isArray(data?.data)
                    ? data.data.length
                    : data?.liquidation_summary || data?.insights
                    ? "summary"
                    : "N/A";
                console.log(
                    "✅ Received:",
                    endpoint,
                    itemCount,
                    typeof itemCount === "number" ? "items" : ""
                );
                return data;
            } catch (error) {
                console.error("❌ API Error:", endpoint, error);
                return null; // Return null instead of throwing to prevent cascade failures
            }
        },
    };
}

/**
 * Chart Configuration Helpers
 */
window.LiquidationsCharts = {
    // Default chart colors
    colors: {
        long: "#ef4444", // red untuk long liquidations
        short: "#22c55e", // green untuk short liquidations
        total: "#3b82f6", // blue untuk total
        cascade: "#f59e0b", // orange untuk cascade events
        neutral: "#6b7280", // gray
    },

    // Common chart options
    getCommonOptions(title = "") {
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: "top",
                    labels: {
                        color: "#94a3b8",
                        font: { size: 11 },
                        usePointStyle: true,
                    },
                },
                title: {
                    display: !!title,
                    text: title,
                    color: "#94a3b8",
                    font: { size: 14, weight: "normal" },
                },
                tooltip: {
                    backgroundColor: "rgba(0, 0, 0, 0.8)",
                    padding: 12,
                    titleColor: "#fff",
                    bodyColor: "#fff",
                    borderColor: "rgba(255, 255, 255, 0.1)",
                    borderWidth: 1,
                },
            },
            scales: {
                x: {
                    ticks: {
                        color: "#94a3b8",
                        font: { size: 10 },
                    },
                    grid: {
                        display: false,
                    },
                },
                y: {
                    ticks: {
                        color: "#94a3b8",
                        font: { size: 10 },
                        callback: function (value) {
                            if (value >= 1e6)
                                return "$" + (value / 1e6).toFixed(1) + "M";
                            if (value >= 1e3)
                                return "$" + (value / 1e3).toFixed(0) + "K";
                            return "$" + value;
                        },
                    },
                    grid: {
                        color: "rgba(148, 163, 184, 0.1)",
                    },
                },
            },
        };
    },
};

/**
 * Utility Functions
 */
window.LiquidationsUtils = {
    // Debounce function
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // Local storage helper
    storage: {
        set(key, value) {
            try {
                localStorage.setItem(
                    `liquidations_${key}`,
                    JSON.stringify(value)
                );
            } catch (e) {
                console.warn("LocalStorage not available");
            }
        },
        get(key, defaultValue = null) {
            try {
                const item = localStorage.getItem(`liquidations_${key}`);
                return item ? JSON.parse(item) : defaultValue;
            } catch (e) {
                return defaultValue;
            }
        },
        remove(key) {
            try {
                localStorage.removeItem(`liquidations_${key}`);
            } catch (e) {
                console.warn("LocalStorage not available");
            }
        },
    },
};

console.log("✅ Liquidations Controller loaded");
