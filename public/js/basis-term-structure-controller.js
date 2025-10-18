/**
 * Basis & Term Structure Dashboard Controller
 *
 * Global controller untuk mengoordinasikan semua komponen basis & term structure
 *
 * Think like a trader:
 * - Basis positif → Contango → Futures > Spot → Potential arbitrage
 * - Basis negatif → Backwardation → Spot > Futures → Supply shortage
 * - Term structure slope → Market expectations untuk masa depan
 * - Convergence patterns → Expiry approaching → Basis → 0
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

function basisTermStructureController() {
    return {
        // Global state
        globalSymbol: "BTC",
        globalExchange: "Binance",
        globalInterval: "5m",
        globalLimit: "2000", // Data limit for API calls
        globalLoading: false,

        // Auto refresh state
        autoRefreshEnabled: true,
        autoRefreshInterval: 30000, // 30 seconds
        autoRefreshTimer: null,
        autoRefreshCountdown: 30,
        autoRefreshCountdownTimer: null,
        lastUpdated: null,

        // Component references
        components: {
            analyticsPanel: null,
            historyChart: null,
            termStructureChart: null,
            insightsPanel: null,
        },

        // Aggregated overview state
        overview: null,

        // Initialize dashboard
        init() {
            console.log("🚀 Basis & Term Structure Dashboard initialized");
            console.log("📊 Symbol:", this.globalSymbol);
            console.log("🏢 Exchange:", this.globalExchange);
            console.log("⏱️ Interval:", this.globalInterval);
            console.log("📈 Data Limit:", this.globalLimit);
            console.log("🔄 Auto Refresh:", this.autoRefreshEnabled ? "Enabled" : "Disabled");

            // Setup event listeners
            this.setupEventListeners();

            // Setup auto refresh
            this.setupAutoRefresh();

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
            // Count components with multiple attempts
            let attempts = 0;
            const maxAttempts = 5;

            const countComponents = () => {
                attempts++;
                const componentElements =
                    document.querySelectorAll('[x-data*="basis"]');
                console.log(
                    `📊 Found ${componentElements.length} basis components (attempt ${attempts})`
                );

                if (componentElements.length >= 3 || attempts >= maxAttempts) {
                    this.components.count = componentElements.length;
                    console.log(
                        `✅ Final component count: ${componentElements.length}`
                    );
                } else {
                    // Try again after delay
                    setTimeout(countComponents, 1000);
                }
            };

            // Start counting after initial delay
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

            // Dispatch event to all components
            window.dispatchEvent(
                new CustomEvent("symbol-changed", {
                    detail: {
                        symbol: this.globalSymbol,
                        exchange: this.globalExchange,
                        interval: this.globalInterval,
                        limit: this.globalLimit,
                    },
                })
            );

            // Update browser URL (optional, for bookmarking)
            this.updateURL();
        },

        // Update exchange globally
        updateExchange() {
            console.log("🔄 Updating exchange to:", this.globalExchange);

            // Dispatch event to all components
            window.dispatchEvent(
                new CustomEvent("exchange-changed", {
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

        // Update interval globally
        updateInterval() {
            console.log("🔄 Updating interval to:", this.globalInterval);

            // Dispatch event to all components
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

            // Dispatch event to all components
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

            console.log("▶️ Starting auto refresh timer");
            
            // Start countdown
            this.autoRefreshCountdown = this.autoRefreshInterval / 1000;
            this.startCountdown();
            
            // Start main refresh timer
            this.autoRefreshTimer = setInterval(() => {
                if (!this.globalLoading && this.autoRefreshEnabled) {
                    console.log("🔄 Auto refresh triggered");
                    this.refreshAll();
                    this.autoRefreshCountdown = this.autoRefreshInterval / 1000;
                }
            }, this.autoRefreshInterval);
        },

        // Stop auto refresh timer
        stopAutoRefresh() {
            if (this.autoRefreshTimer) {
                clearInterval(this.autoRefreshTimer);
                this.autoRefreshTimer = null;
                console.log("⏹️ Auto refresh timer stopped");
            }
            
            if (this.autoRefreshCountdownTimer) {
                clearInterval(this.autoRefreshCountdownTimer);
                this.autoRefreshCountdownTimer = null;
            }
        },

        // Pause auto refresh (when tab hidden)
        pauseAutoRefresh() {
            if (this.autoRefreshTimer) {
                this.stopAutoRefresh();
                console.log("⏸️ Auto refresh paused (tab hidden)");
            }
        },

        // Resume auto refresh (when tab visible)
        resumeAutoRefresh() {
            if (this.autoRefreshEnabled && !this.autoRefreshTimer) {
                this.startAutoRefresh();
                console.log("▶️ Auto refresh resumed (tab visible)");
            }
        },

        // Start countdown timer
        startCountdown() {
            if (this.autoRefreshCountdownTimer) {
                clearInterval(this.autoRefreshCountdownTimer);
            }
            
            this.autoRefreshCountdownTimer = setInterval(() => {
                this.autoRefreshCountdown--;
                if (this.autoRefreshCountdown <= 0) {
                    this.autoRefreshCountdown = this.autoRefreshInterval / 1000;
                }
            }, 1000);
        },

        // Build pair (symbol + quote) consistently for endpoints that require pair
        buildPair(baseSymbol) {
            const sym = (
                baseSymbol ||
                this.globalSymbol ||
                "BTC"
            ).toUpperCase();
            // Default quote USDT; can be extended later
            return `${sym}USDT`;
        },

        // Build futures symbol for basis calculation
        buildFuturesSymbol(baseSymbol) {
            const sym = (
                baseSymbol ||
                this.globalSymbol ||
                "BTC"
            ).toUpperCase();
            // Use same symbol as spot pair for perpetual contracts
            return `${sym}USDT`; // Perpetual futures
        },

        // Fetch and compose overview from existing endpoints
        async loadOverview({
            includeAnalytics = true,
            includeHistory = true,
            includeTermStructure = true,
        } = {}) {
            const baseSymbol = this.globalSymbol;
            const pair = this.buildPair(baseSymbol);
            const futuresSymbol = this.buildFuturesSymbol(baseSymbol);
            const exchange = this.globalExchange;
            const interval = "5m"; // Use 5m interval that works with API
            const limit = this.globalLimit;

            console.log("🔄 Loading Basis Overview:", {
                baseSymbol,
                pair,
                futuresSymbol,
                exchange,
                interval,
                limit,
            });

            // Prepare request params
            const commonRange = {}; // Placeholder to propagate start_time/end_time if later added

            // Execute in parallel
            const promises = [];

            if (includeAnalytics) {
                promises.push(
                    this.fetchAPI("analytics", {
                        exchange,
                        spot_pair: pair,
                        futures_symbol: futuresSymbol,
                        interval,
                        limit: parseInt(limit),
                        ...commonRange,
                    })
                );
            }

            if (includeHistory) {
                promises.push(
                    this.fetchAPI("history", {
                        exchange,
                        spot_pair: pair,
                        futures_symbol: futuresSymbol,
                        interval,
                        limit: parseInt(limit),
                        ...commonRange,
                    })
                );
            }

            if (includeTermStructure) {
                promises.push(
                    this.fetchAPI("term-structure", {
                        exchange,
                        spot_pair: pair,
                        max_contracts: 20,
                    })
                );
            }

            const results = await Promise.all(promises);

            const [analytics, history, termStructure] = results;

            const historyRows = Array.isArray(history?.data)
                ? history.data
                : [];

            // Convert numeric strings to floats and normalize keys
            const normalizedTimeseries = historyRows
                .map((r) => ({
                    ts: r.ts,
                    exchange: r.exchange,
                    spot_pair: r.spot_pair,
                    futures_symbol: r.futures_symbol,
                    price_spot: parseFloat(r.price_spot),
                    price_futures: parseFloat(r.price_futures),
                    basis_abs: parseFloat(r.basis_abs),
                    basis_annualized: r.basis_annualized
                        ? parseFloat(r.basis_annualized)
                        : null,
                    expiry: r.expiry,
                }))
                .filter((r) => !Number.isNaN(r.basis_abs));

            const termStructureData = Array.isArray(termStructure?.data)
                ? termStructure.data
                : [];

            // Normalize term structure data
            const normalizedTermStructure = termStructureData
                .map((r) => ({
                    exchange: r.exchange,
                    spot_pair: r.spot_pair,
                    futures_symbol: r.futures_symbol,
                    instrument_id: r.instrument_id,
                    expiry: r.expiry,
                    price_spot: parseFloat(r.price_spot),
                    price_futures: parseFloat(r.price_futures),
                    basis_abs: parseFloat(r.basis_abs),
                    basis_annualized: r.basis_annualized
                        ? parseFloat(r.basis_annualized)
                        : null,
                }))
                .filter((r) => !Number.isNaN(r.basis_abs));

            this.overview = {
                meta: {
                    pair,
                    futuresSymbol,
                    exchange,
                    interval,
                    units: {
                        basis_abs: "USD",
                        basis_annualized: "percentage",
                        price: "USD",
                    },
                    last_updated: Date.now(),
                },
                analytics: analytics || null,
                timeseries: normalizedTimeseries,
                termStructure: normalizedTermStructure,
            };

            // Broadcast overview-ready event
            window.dispatchEvent(
                new CustomEvent("basis-overview-ready", {
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
                url.searchParams.set("interval", this.globalInterval || "1h");
                window.history.pushState({}, "", url);
            }
        },

        // Refresh all components
        refreshAll() {
            this.globalLoading = true;
            console.log("🔄 Refreshing all components...");

            // Dispatch refresh event to all components
            window.dispatchEvent(
                new CustomEvent("refresh-all", {
                    detail: {
                        symbol: this.globalSymbol,
                        exchange: this.globalExchange,
                        interval: this.globalInterval,
                        limit: this.globalLimit,
                    },
                })
            );

            // Reload overview data
            this.loadOverview().catch((e) =>
                console.warn("Overview reload failed:", e)
            );

            // Reset loading state after delay
            setTimeout(() => {
                this.globalLoading = false;
                this.lastUpdated = new Date().toLocaleTimeString();
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

        // Utility: Format basis value
        formatBasis(value, type = "abs") {
            if (value === null || value === undefined) return "N/A";
            if (type === "annualized") {
                const percent = (parseFloat(value) * 100).toFixed(2);
                return (parseFloat(value) >= 0 ? "+" : "") + percent + "%";
            }
            return "$" + parseFloat(value).toFixed(2);
        },

        // Utility: Get basis color class
        getBasisColorClass(basis) {
            if (basis > 0) return "text-success";
            if (basis < 0) return "text-danger";
            return "text-secondary";
        },

        // Utility: Get market structure color class
        getMarketStructureClass(structure) {
            const structureLower = (structure || "").toLowerCase();
            if (structureLower.includes("contango")) return "text-warning";
            if (structureLower.includes("backwardation")) return "text-success";
            return "text-secondary";
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

        // Utility: Calculate time until expiry
        timeUntilExpiry(expiryTimestamp) {
            if (!expiryTimestamp || expiryTimestamp <= Date.now()) return "N/A";
            const diff = expiryTimestamp - Date.now();
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor(
                (diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
            );
            return `${days}d ${hours}h`;
        },

        // Trading insights helper
        getTradingInsight(analytics) {
            if (!analytics)
                return {
                    icon: "💡",
                    title: "Market Analysis",
                    message: "Loading market analysis...",
                    severity: "info",
                };

            const marketStructure = analytics.market_structure || "unknown";
            const trend = analytics.trend || {};
            const basisAbs = analytics.basis_abs || {};
            const insights = analytics.insights || [];

            // Determine primary insight
            if (insights.length > 0) {
                const primaryInsight = insights[0];
                return {
                    icon: this.getInsightIcon(primaryInsight.severity),
                    title: primaryInsight.type || "Market Insight",
                    message:
                        primaryInsight.message ||
                        "No specific insight available",
                    severity: primaryInsight.severity || "info",
                };
            }

            // Fallback insights based on market structure
            if (marketStructure === "contango") {
                return {
                    icon: "📈",
                    title: "Contango Market",
                    message: `Futures trading above spot (${this.formatBasis(
                        basisAbs.current
                    )}). Market expects higher prices. Watch for convergence as expiry approaches.`,
                    severity: "warning",
                };
            }

            if (marketStructure === "backwardation") {
                return {
                    icon: "📉",
                    title: "Backwardation Market",
                    message: `Spot trading above futures (${this.formatBasis(
                        basisAbs.current
                    )}). Supply shortage or high demand. Potential arbitrage opportunity.`,
                    severity: "success",
                };
            }

            return {
                icon: "💡",
                title: "Neutral Market",
                message: `Basis at ${this.formatBasis(
                    basisAbs.current
                )}. Market showing balanced expectations. Monitor for convergence patterns.`,
                severity: "info",
            };
        },

        // Get insight icon based on severity
        getInsightIcon(severity) {
            switch (severity?.toLowerCase()) {
                case "high":
                case "danger":
                    return "🚨";
                case "medium":
                case "warning":
                    return "⚠️";
                case "low":
                case "info":
                    return "💡";
                default:
                    return "💡";
            }
        },

        // API Helper: Fetch with error handling
        async fetchAPI(endpoint, params = {}) {
            const queryString = new URLSearchParams(params).toString();
            const baseMeta = document.querySelector(
                'meta[name="api-base-url"]'
            );
            const configuredBase = (baseMeta?.content || "").trim();

            let url = `/api/basis/${endpoint}?${queryString}`; // default relative
            if (configuredBase) {
                const normalizedBase = configuredBase.endsWith("/")
                    ? configuredBase.slice(0, -1)
                    : configuredBase;
                url = `${normalizedBase}/api/basis/${endpoint}?${queryString}`;
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
                    : data?.basis_abs ||
                      data?.market_structure ||
                      data?.insights
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
window.BasisTermStructureCharts = {
    // Default chart colors
    colors: {
        primary: "#3b82f6",
        success: "#22c55e",
        danger: "#ef4444",
        warning: "#f59e0b",
        purple: "#8b5cf6",
        gray: "#6b7280",
        contango: "#f59e0b",
        backwardation: "#22c55e",
    },

    // Common chart options
    getCommonOptions(title = "") {
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
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
window.BasisTermStructureUtils = {
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
                    `basis_term_structure_${key}`,
                    JSON.stringify(value)
                );
            } catch (e) {
                console.warn("LocalStorage not available");
            }
        },
        get(key, defaultValue = null) {
            try {
                const item = localStorage.getItem(
                    `basis_term_structure_${key}`
                );
                return item ? JSON.parse(item) : defaultValue;
            } catch (e) {
                return defaultValue;
            }
        },
        remove(key) {
            try {
                localStorage.removeItem(`basis_term_structure_${key}`);
            } catch (e) {
                console.warn("LocalStorage not available");
            }
        },
    },
};

console.log("✅ Basis & Term Structure Controller loaded");
