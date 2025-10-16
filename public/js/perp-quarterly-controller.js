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
        globalQuote: "USDT",
        globalExchange: "Binance",
        globalInterval: "5m",
        globalPerpSymbol: "", // Optional override, auto-generated if empty
        globalLimit: "2000", // Data limit for API calls
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
            console.log("📊 Base:", this.globalSymbol);
            console.log("💰 Quote:", this.globalQuote);
            console.log("🏦 Exchange:", this.globalExchange);
            console.log("⏱️ Interval:", this.globalInterval);
            console.log(
                "🔧 Perp Symbol Override:",
                this.globalPerpSymbol || "auto-generated"
            );
            console.log("📈 Data Limit:", this.globalLimit);

            // Setup event listeners
            this.setupEventListeners();
            
            // Setup global error handlers
            this.setupGlobalErrorHandlers();

            this.globalLoading = true;

            // Wait for DOM to be fully ready before loading data
            this.waitForDOMReady().then(() => {
                // Prime overview on load with delay to prevent race conditions
                setTimeout(() => {
                    this.loadOverview().catch((e) => {
                        console.error("Initial overview load failed:", e);
                        this.globalLoading = false;
                    });
                }, 500);
            });

            // Log dashboard ready
            setTimeout(() => {
                console.log("✅ All components loaded");
                this.logDashboardStatus();
            }, 3000);
        },

        // Wait for DOM to be fully ready
        async waitForDOMReady() {
            return new Promise((resolve) => {
                if (document.readyState === 'complete') {
                    resolve();
                } else {
                    window.addEventListener('load', resolve);
                }
            });
        },

        // Setup global error handlers
        setupGlobalErrorHandlers() {
            // Handle unhandled promise rejections
            window.addEventListener('unhandledrejection', (event) => {
                console.error('❌ Unhandled promise rejection:', event.reason);
                event.preventDefault(); // Prevent default error handling
                this.globalLoading = false;
            });

            // Handle general JavaScript errors
            window.addEventListener('error', (event) => {
                console.error('❌ JavaScript error:', event.error);
                this.globalLoading = false;
            });
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
            console.log("🔄 Updating base symbol to:", this.globalSymbol);

            window.dispatchEvent(
                new CustomEvent("symbol-changed", {
                    detail: {
                        symbol: this.globalSymbol,
                        quote: this.globalQuote,
                        exchange: this.globalExchange,
                        interval: this.globalInterval,
                        perpSymbol: this.globalPerpSymbol,
                        limit: this.globalLimit,
                    },
                })
            );

            this.updateURL();
            this.loadOverview().catch((e) =>
                console.warn("Overview reload failed:", e)
            );
        },

        // Update quote globally
        updateQuote() {
            console.log("🔄 Updating quote to:", this.globalQuote);

            window.dispatchEvent(
                new CustomEvent("quote-changed", {
                    detail: {
                        symbol: this.globalSymbol,
                        quote: this.globalQuote,
                        exchange: this.globalExchange,
                        interval: this.globalInterval,
                        perpSymbol: this.globalPerpSymbol,
                        limit: this.globalLimit,
                    },
                })
            );

            this.updateURL();
            this.loadOverview().catch((e) =>
                console.warn("Overview reload failed:", e)
            );
        },

        // Update exchange globally
        updateExchange() {
            console.log("🔄 Updating exchange to:", this.globalExchange);

            window.dispatchEvent(
                new CustomEvent("exchange-changed", {
                    detail: {
                        symbol: this.globalSymbol,
                        quote: this.globalQuote,
                        exchange: this.globalExchange,
                        interval: this.globalInterval,
                        perpSymbol: this.globalPerpSymbol,
                        limit: this.globalLimit,
                    },
                })
            );

            this.updateURL();
            this.loadOverview().catch((e) =>
                console.warn("Overview reload failed:", e)
            );
        },

        // Update interval globally
        updateInterval() {
            console.log("🔄 Updating interval to:", this.globalInterval);

            window.dispatchEvent(
                new CustomEvent("interval-changed", {
                    detail: {
                        symbol: this.globalSymbol,
                        quote: this.globalQuote,
                        exchange: this.globalExchange,
                        interval: this.globalInterval,
                        perpSymbol: this.globalPerpSymbol,
                        limit: this.globalLimit,
                    },
                })
            );

            this.updateURL();
            this.loadOverview().catch((e) =>
                console.warn("Overview reload failed:", e)
            );
        },

        // Update perp symbol override
        updatePerpSymbol() {
            console.log(
                "🔄 Updating perp symbol override to:",
                this.globalPerpSymbol || "auto-generated"
            );

            window.dispatchEvent(
                new CustomEvent("perp-symbol-changed", {
                    detail: {
                        symbol: this.globalSymbol,
                        quote: this.globalQuote,
                        exchange: this.globalExchange,
                        interval: this.globalInterval,
                        perpSymbol: this.globalPerpSymbol,
                        limit: this.globalLimit,
                    },
                })
            );

            this.updateURL();
            this.loadOverview().catch((e) =>
                console.warn("Overview reload failed:", e)
            );
        },

        // Update data limit
        updateLimit() {
            console.log("🔄 Updating data limit to:", this.globalLimit);

            window.dispatchEvent(
                new CustomEvent("limit-changed", {
                    detail: {
                        symbol: this.globalSymbol,
                        quote: this.globalQuote,
                        exchange: this.globalExchange,
                        interval: this.globalInterval,
                        perpSymbol: this.globalPerpSymbol,
                        limit: this.globalLimit,
                    },
                })
            );

            this.updateURL();
            this.loadOverview().catch((e) =>
                console.warn("Overview reload failed:", e)
            );
        },

        // Load overview with fallback data and retry mechanism
        async loadOverview(retryCount = 0) {
            try {
                const base = this.globalSymbol;
                const quote = this.globalQuote;
                const exchange = this.globalExchange;
                const interval = this.globalInterval;
                const perpSymbol = this.globalPerpSymbol || `${base}${quote}`;
                const limit = this.globalLimit;

                // Set loading state
                this.globalLoading = true;


                console.log("🔄 Loading Perp-Quarterly Overview:", {
                    base,
                    quote,
                    exchange,
                    interval,
                    perpSymbol,
                    limit,
                    retryCount,
                });

                // Execute in parallel with timeout
                const [analytics, history] = await Promise.all([
                    this.fetchAPIWithRetry("analytics", {
                        exchange,
                        base,
                        quote,
                        interval,
                        limit: parseInt(limit),
                        perp_symbol: perpSymbol,
                    }, 2).catch(e => {
                        console.warn("Analytics API failed, using fallback:", e.message);
                        return null;
                    }),
                    this.fetchAPIWithRetry("history", {
                        exchange,
                        base,
                        quote,
                        interval,
                        limit: parseInt(limit),
                        perp_symbol: perpSymbol,
                    }, 2).catch(e => {
                        console.warn("History API failed, using fallback:", e.message);
                        return null;
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
                    .filter((r) => !Number.isNaN(r.spread_abs))
                    // Sort by timestamp descending (newest first)
                    .sort((a, b) => {
                        if (!a.ts) return 1;
                        if (!b.ts) return -1;
                        return new Date(b.ts) - new Date(a.ts);
                    });

                this.overview = {
                    meta: {
                        base,
                        quote,
                        exchange,
                        interval,
                        perp_symbol:
                            analytics?.perp_symbol || history?.meta?.perp_symbol || `${base}${quote}_PERP`,
                        quarterly_symbol:
                            analytics?.quarterly_symbol ||
                            history?.meta?.quarterly_symbol || `${base}${quote}`,
                        last_updated: Date.now(),
                    },
                    analytics: analytics,
                    timeseries: normalizedTimeseries,
                };

                // Clear loading state
                this.globalLoading = false;

                // Broadcast overview-ready event
                window.dispatchEvent(
                    new CustomEvent("perp-quarterly-overview-ready", {
                        detail: this.overview,
                    })
                );
                return this.overview;
            } catch (error) {
                console.error("❌ Error loading overview:", error);
                
                // Retry logic for transient errors
                if (retryCount < 2 && (
                    error.message.includes('fetch') || 
                    error.message.includes('network') ||
                    error.message.includes('timeout') ||
                    error.message.includes('Failed to fetch')
                )) {
                    console.log(`🔄 Retrying overview load (${retryCount + 1}/2)...`);
                    await new Promise(resolve => setTimeout(resolve, 1000 * (retryCount + 1)));
                    return this.loadOverview(retryCount + 1);
                }
                
                // Clear loading state
                this.globalLoading = false;
                
                // Let the error propagate
                throw error;
            }
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
        async refreshAll() {
            this.globalLoading = true;
            console.log("🔄 Refreshing all components...");

            try {
                // Dispatch refresh event to all components
                window.dispatchEvent(
                    new CustomEvent("refresh-all", {
                        detail: {
                            symbol: this.globalSymbol,
                            exchange: this.globalExchange,
                            interval: this.globalInterval,
                        },
                    })
                );

                // Reload overview data
                await this.loadOverview();

                console.log("✅ All components refreshed");
            } catch (error) {
                console.error("❌ Error refreshing components:", error);
            } finally {
                this.globalLoading = false;
            }
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

        // API Helper: Fetch with retry mechanism
        async fetchAPIWithRetry(endpoint, params = {}, maxRetries = 3) {
            let lastError;
            
            for (let attempt = 1; attempt <= maxRetries; attempt++) {
                try {
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

                    console.log(`📡 Fetching (attempt ${attempt}/${maxRetries}):`, endpoint, params);
                    
                    // Add timeout to prevent hanging requests
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
                    
                    const response = await fetch(url, {
                        signal: controller.signal,
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });
                    
                    clearTimeout(timeoutId);

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
                    lastError = error;
                    console.warn(`❌ API Error (attempt ${attempt}/${maxRetries}):`, endpoint, error.message);
                    
                    if (attempt < maxRetries) {
                        // Wait before retry (exponential backoff)
                        const delay = Math.min(1000 * Math.pow(2, attempt - 1), 5000);
                        console.log(`⏳ Retrying in ${delay}ms...`);
                        await new Promise(resolve => setTimeout(resolve, delay));
                    }
                }
            }
            
            throw lastError;
        },

        // API Helper: Fetch with error handling (legacy)
        async fetchAPI(endpoint, params = {}) {
            return this.fetchAPIWithRetry(endpoint, params, 1);
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

// Export function for Alpine.js
window.perpQuarterlySpreadController = () => perpQuarterlySpreadController;

console.log("✅ Perp-Quarterly Spread Controller loaded");
