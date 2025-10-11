/**
 * Perp-Quarterly Spread Dashboard Controller
 *
 * Global controller untuk mengoordinasikan semua komponen perp-quarterly spread
 *
 * Think like a trader:
 * - Spread positif (Perp > Quarterly) → Contango → Market expects higher prices
 * - Spread negatif (Quarterly > Perp) → Backwardation → Supply shortage or high demand
 * - Spread widening → Increasing contango/backwardation
 * - Spread narrowing → Convergence approaching (normal menjelang expiry)
 *
 * Build like an engineer:
 * - Modular components dengan event communication
 * - Efficient data fetching dengan caching
 * - Error handling dan fallback data
 *
 * Visualize like a designer:
 * - Color coded untuk quick insights
 * - Real-time updates tanpa page refresh
 * - Responsive dan smooth animations
 */

function perpQuarterlySpreadController() {
    return {
        // Global state
        globalSymbol: "BTC",
        globalExchange: "Binance",
        globalInterval: "1h",
        globalLoading: false,

        // Component references
        components: {
            analyticsCard: null,
            spreadChart: null,
            heatmap: null,
            insightsPanel: null,
        },

        // Aggregated overview state
        overview: null,

        // Initialize dashboard
        init() {
            console.log("🚀 Perp-Quarterly Spread Dashboard initialized");
            console.log("📊 Symbol:", this.globalSymbol);
            console.log("🏦 Exchange:", this.globalExchange);
            console.log("⏱️ Interval:", this.globalInterval);

            // Setup event listeners
            this.setupEventListeners();

            // Prime overview on load
            this.loadOverview().catch((e) =>
                console.warn("Initial overview load failed:", e)
            );

            // Log dashboard ready
            setTimeout(() => {
                console.log("✅ All components loaded");
                this.logDashboardStatus();
            }, 2000);
        },

        // Setup global event listeners
        setupEventListeners() {
            // Count components
            let attempts = 0;
            const maxAttempts = 5;

            const countComponents = () => {
                attempts++;
                const componentElements = document.querySelectorAll(
                    '[x-data*="perpQuarterly"], [x-data*="spreadAnalytics"]'
                );
                console.log(
                    `📊 Found ${componentElements.length} components (attempt ${attempts})`
                );

                if (componentElements.length >= 3 || attempts >= maxAttempts) {
                    this.components.count = componentElements.length;
                    console.log(
                        `✅ Final component count: ${componentElements.length}`
                    );
                } else {
                    setTimeout(countComponents, 1000);
                }
            };

            setTimeout(countComponents, 2000);

            // Reload overview on global filter changes
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
                this.loadOverview().catch((e) =>
                    console.warn("Overview reload failed:", e)
                );
            });
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

        // Update exchange globally
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

        // Update interval globally
        updateInterval() {
            console.log("🔄 Updating interval to:", this.globalInterval);

            window.dispatchEvent(
                new CustomEvent("interval-changed", {
                    detail: {
                        symbol: this.globalSymbol,
                        exchange: this.globalExchange,
                        interval: this.globalInterval,
                    },
                })
            );

            this.updateURL();
        },

        // Fetch and compose overview from existing endpoints
        async loadOverview() {
            const base = this.globalSymbol;
            const exchange = this.globalExchange;
            const interval = this.globalInterval;
            const quote = "USDT";

            // Execute in parallel
            const [analytics, history] = await Promise.all([
                this.fetchAPI("analytics", {
                    exchange,
                    base,
                    quote,
                    interval,
                    limit: 2000,
                }),
                this.fetchAPI("history", {
                    exchange,
                    base,
                    quote,
                    interval,
                    limit: 2000,
                }),
            ]);

            const historyRows = Array.isArray(history?.data)
                ? history.data
                : [];

            // Normalize timeseries
            const normalizedTimeseries = historyRows
                .map((r) => ({
                    ts: r.ts,
                    exchange: r.exchange,
                    perp_symbol: r.perp_symbol,
                    quarterly_symbol: r.quarterly_symbol,
                    spread_abs: parseFloat(r.spread_abs),
                    spread_bps: parseFloat(r.spread_bps),
                }))
                .filter((r) => !Number.isNaN(r.spread_abs));

            this.overview = {
                meta: {
                    base,
                    quote,
                    exchange,
                    interval,
                    perp_symbol:
                        analytics?.perp_symbol || history?.meta?.perp_symbol,
                    quarterly_symbol:
                        analytics?.quarterly_symbol ||
                        history?.meta?.quarterly_symbol,
                    last_updated: Date.now(),
                },
                analytics: analytics || null,
                timeseries: normalizedTimeseries,
            };

            // Broadcast overview-ready event
            window.dispatchEvent(
                new CustomEvent("perp-quarterly-overview-ready", {
                    detail: this.overview,
                })
            );
            return this.overview;
        },

        // Update URL with all filters
        updateURL() {
            if (window.history && window.history.pushState) {
                const url = new URL(window.location);
                url.searchParams.set("symbol", this.globalSymbol);
                url.searchParams.set("exchange", this.globalExchange);
                url.searchParams.set("interval", this.globalInterval);
                window.history.pushState({}, "", url);
            }
        },

        // Refresh all components
        refreshAll() {
            this.globalLoading = true;
            console.log("🔄 Refreshing all components...");

            window.dispatchEvent(
                new CustomEvent("refresh-all", {
                    detail: {
                        symbol: this.globalSymbol,
                        exchange: this.globalExchange,
                        interval: this.globalInterval,
                    },
                })
            );

            setTimeout(() => {
                this.globalLoading = false;
                console.log("✅ All components refreshed");
            }, 2000);
        },

        // Log dashboard status
        logDashboardStatus() {
            console.group("📊 Dashboard Status");
            console.log("Symbol:", this.globalSymbol);
            console.log("Exchange:", this.globalExchange);
            console.log("Interval:", this.globalInterval);
            console.log("Components loaded:", this.components.count || 0);
            const baseMeta = document.querySelector(
                'meta[name="api-base-url"]'
            );
            const baseUrl = (baseMeta?.content || "").trim() || "(relative)";
            console.log("API Base:", baseUrl);
            console.groupEnd();
        },

        // Utility: Format spread value
        formatSpread(value, decimals = 2) {
            if (value === null || value === undefined || isNaN(value))
                return "N/A";
            const num = parseFloat(value);
            return (num >= 0 ? "+" : "") + num.toFixed(decimals);
        },

        // Utility: Format spread BPS
        formatBPS(value) {
            if (value === null || value === undefined || isNaN(value))
                return "N/A";
            const num = parseFloat(value);
            return (num >= 0 ? "+" : "") + num.toFixed(2) + " bps";
        },

        // Utility: Get spread color class
        getSpreadColorClass(value) {
            if (value > 0) return "text-success";
            if (value < 0) return "text-danger";
            return "text-secondary";
        },

        // Utility: Get market structure
        getMarketStructure(spread) {
            if (spread > 50) return "Strong Contango";
            if (spread > 0) return "Contango";
            if (spread < -50) return "Strong Backwardation";
            if (spread < 0) return "Backwardation";
            return "Neutral";
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
                hour12: false,
            });
        },

        // API Helper: Fetch with error handling
        async fetchAPI(endpoint, params = {}) {
            const queryString = new URLSearchParams(params).toString();
            const baseMeta = document.querySelector(
                'meta[name="api-base-url"]'
            );
            const configuredBase = (baseMeta?.content || "").trim();

            let url = `/api/perp-quarterly/${endpoint}?${queryString}`;
            if (configuredBase) {
                const normalizedBase = configuredBase.endsWith("/")
                    ? configuredBase.slice(0, -1)
                    : configuredBase;
                url = `${normalizedBase}/api/perp-quarterly/${endpoint}?${queryString}`;
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
                    : data?.spread_bps || data?.analytics
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
                throw error;
            }
        },
    };
}

/**
 * Chart Configuration Helpers
 */
window.PerpQuarterlyCharts = {
    // Default chart colors
    colors: {
        primary: "#3b82f6",
        success: "#22c55e",
        danger: "#ef4444",
        warning: "#f59e0b",
        purple: "#8b5cf6",
        gray: "#6b7280",
        contango: "#22c55e",
        backwardation: "#ef4444",
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
                    type: "time",
                    time: {
                        unit: "hour",
                        displayFormats: {
                            hour: "MMM d HH:mm",
                        },
                    },
                    ticks: {
                        color: "#94a3b8",
                        font: { size: 10 },
                    },
                    grid: {
                        display: false,
                    },
                },
                y: {
                    position: "right",
                    ticks: {
                        color: "#94a3b8",
                        font: { size: 10 },
                    },
                    grid: {
                        color: "rgba(148, 163, 184, 0.1)",
                    },
                },
            },
        };
    },

    // Create gradient for chart background
    createGradient(ctx, color, alpha = 0.3) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(
            0,
            color.replace("rgb", "rgba").replace(")", `, ${alpha})`)
        );
        gradient.addColorStop(
            1,
            color.replace("rgb", "rgba").replace(")", ", 0)")
        );
        return gradient;
    },
};

/**
 * Utility Functions
 */
window.PerpQuarterlyUtils = {
    // Debounce function for performance
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
                    `perp_quarterly_${key}`,
                    JSON.stringify(value)
                );
            } catch (e) {
                console.warn("LocalStorage not available");
            }
        },
        get(key, defaultValue = null) {
            try {
                const item = localStorage.getItem(`perp_quarterly_${key}`);
                return item ? JSON.parse(item) : defaultValue;
            } catch (e) {
                return defaultValue;
            }
        },
        remove(key) {
            try {
                localStorage.removeItem(`perp_quarterly_${key}`);
            } catch (e) {
                console.warn("LocalStorage not available");
            }
        },
    },
};

console.log("✅ Perp-Quarterly Spread Controller loaded");
