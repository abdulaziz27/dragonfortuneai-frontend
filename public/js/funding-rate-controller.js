/**
 * Funding Rate Dashboard Controller
 *
 * Global controller untuk mengoordinasikan semua komponen funding rate
 *
 * Think like a trader:
 * - Funding rate adalah cost of leverage
 * - Positive funding = longs crowded = potential squeeze down
 * - Negative funding = shorts crowded = potential squeeze up
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

function fundingRateController() {
    return {
        // Global state
        globalSymbol: "BTC",
        globalMarginType: "",
        globalInterval: "1h",
        globalLoading: false,

        // Component references
        components: {
            biasCard: null,
            exchangeTable: null,
            aggregateChart: null,
            historyChart: null,
            weightedChart: null,
        },

        // Aggregated overview state
        overview: null,

        // Initialize dashboard
        init() {
            console.log("🚀 Funding Rate Dashboard initialized");
            console.log("📊 Symbol:", this.globalSymbol);
            console.log("💰 Margin Type:", this.globalMarginType || "All");

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
            // Simple component counting instead of complex event system
            // This avoids conflicts with Alpine.js and Livewire

            // Count components with multiple attempts
            let attempts = 0;
            const maxAttempts = 5;

            const countComponents = () => {
                attempts++;
                const componentElements = document.querySelectorAll(
                    '[x-data*="funding"]'
                );
                console.log(
                    `📊 Found ${componentElements.length} funding components (attempt ${attempts})`
                );

                if (componentElements.length >= 5 || attempts >= maxAttempts) {
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
                        marginType: this.globalMarginType,
                        interval: this.globalInterval,
                    },
                })
            );

            // Update browser URL (optional, for bookmarking)
            this.updateURL();
        },

        // Update margin type globally
        updateMarginType() {
            console.log("🔄 Updating margin type to:", this.globalMarginType);

            // Dispatch event to all components
            window.dispatchEvent(
                new CustomEvent("margin-type-changed", {
                    detail: {
                        symbol: this.globalSymbol,
                        marginType: this.globalMarginType,
                        interval: this.globalInterval,
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
                        marginType: this.globalMarginType,
                        interval: this.globalInterval,
                    },
                })
            );

            this.updateURL();
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

        // Fetch and compose overview from existing endpoints
        async loadOverview({
            includePrice = false,
            granularity = "funding_8h",
            heatmapExchanges = ["Binance", "Bybit", "OKX", "Bitget"],
            buckets = 15,
        } = {}) {
            const baseSymbol = this.globalSymbol;
            const pair = this.buildPair(baseSymbol);
            const interval = this.globalInterval || "1h";

            // Prepare request params
            const commonRange = {}; // Placeholder to propagate start_time/end_time if later added

            // Execute in parallel - NOW INCLUDES OVERVIEW ENDPOINT
            const [analytics, exchanges, history, overviewData] = await Promise.all([
                this.fetchAPI("analytics", {
                    symbol: pair,
                    interval,
                    limit: 2000,
                    ...commonRange,
                }),
                this.fetchAPI("exchanges", { symbol: baseSymbol, limit: 1000 }),
                this.fetchAPI("history", {
                    symbol: pair,
                    interval,
                    limit: 2000,
                    ...commonRange,
                }),
                this.fetchAPI("overview", {
                    symbol: pair,
                    limit: 100,
                }).catch(e => {
                    console.warn("Overview endpoint not available:", e);
                    return null;
                })
            ]);

            const historyRows = Array.isArray(history?.data)
                ? history.data
                : [];

            // Convert numeric strings to floats and normalize keys
            const normalizedTimeseries = historyRows
                .map((r) => ({
                    ts: r.time,
                    exchange: r.exchange,
                    pair: r.symbol,
                    funding_rate_open: parseFloat(r.open),
                    funding_rate_high: parseFloat(r.high),
                    funding_rate_low: parseFloat(r.low),
                    funding_rate_close: parseFloat(r.close),
                    interval: r.interval_name,
                    symbol_price:
                        includePrice && r.price ? parseFloat(r.price) : null,
                }))
                .filter((r) => !Number.isNaN(r.funding_rate_close));

            // Optional resample to 8h buckets aligned on 00/08/16 UTC
            const resampled = this.resampleToEightHours(normalizedTimeseries);

            // Build series by exchange for heatmap and small multiples
            const byExchange = {};
            resampled.forEach((row) => {
                if (!byExchange[row.exchange]) byExchange[row.exchange] = [];
                byExchange[row.exchange].push({
                    ts: row.ts,
                    funding_rate: row.funding_rate_close,
                });
            });
            // Keep only requested exchanges if provided
            const filteredByExchange = Object.fromEntries(
                Object.entries(byExchange).filter(([ex]) =>
                    heatmapExchanges.includes(ex)
                )
            );

            // Limit to last N buckets per exchange for heatmap efficiency
            const limitedByExchange = Object.fromEntries(
                Object.entries(filteredByExchange).map(([ex, arr]) => [
                    ex,
                    arr.sort((a, b) => a.ts - b.ts).slice(-buckets),
                ])
            );

            this.overview = {
                meta: {
                    pair,
                    interval,
                    granularity,
                    units: { funding_rate: "fraction", price: "USD" },
                    last_updated: Date.now(),
                },
                analytics: analytics || null,
                exchanges: Array.isArray(exchanges?.data) ? exchanges.data : [],
                timeseries: resampled,
                timeseries_by_exchange: limitedByExchange,
                overview_summary: overviewData?.data || null, // ADD OVERVIEW DATA
            };

            // Broadcast overview-ready event
            window.dispatchEvent(
                new CustomEvent("funding-overview-ready", {
                    detail: this.overview,
                })
            );
            return this.overview;
        },

        // Resample arbitrary intervals to 8h funding buckets (O-H-L-C from constituent points)
        resampleToEightHours(rows) {
            if (!Array.isArray(rows) || rows.length === 0) return [];

            // Helper to get bucket start aligned to 00/08/16 UTC
            const toBucketStart = (tsMs) => {
                const date = new Date(tsMs);
                const utc = Date.UTC(
                    date.getUTCFullYear(),
                    date.getUTCMonth(),
                    date.getUTCDate(),
                    date.getUTCHours(),
                    0,
                    0,
                    0
                );
                const hour = new Date(utc).getUTCHours();
                const bucketHour = hour < 8 ? 0 : hour < 16 ? 8 : 16;
                const bucketDate = Date.UTC(
                    date.getUTCFullYear(),
                    date.getUTCMonth(),
                    date.getUTCDate(),
                    bucketHour,
                    0,
                    0,
                    0
                );
                return bucketDate;
            };

            // Group by exchange + bucket
            const groups = new Map();
            for (const r of rows) {
                const bucket = toBucketStart(r.ts);
                const key = `${r.exchange}__${bucket}`;
                if (!groups.has(key)) groups.set(key, []);
                groups.get(key).push(r);
            }

            // Compute OHLC per group
            const result = [];
            for (const [key, arr] of groups.entries()) {
                const [exchange, bucketStr] = key.split("__");
                const sorted = arr.sort((a, b) => a.ts - b.ts);
                const open =
                    sorted[0].funding_rate_open ?? sorted[0].funding_rate_close;
                const close =
                    sorted[sorted.length - 1].funding_rate_close ??
                    sorted[sorted.length - 1].funding_rate_open;
                const high = Math.max(
                    ...sorted.map((x) =>
                        Math.max(x.funding_rate_high ?? x.funding_rate_close)
                    )
                );
                const low = Math.min(
                    ...sorted.map((x) =>
                        Math.min(x.funding_rate_low ?? x.funding_rate_close)
                    )
                );
                result.push({
                    ts: Number(bucketStr),
                    exchange,
                    pair: sorted[0].pair,
                    funding_rate_open: open,
                    funding_rate_high: high,
                    funding_rate_low: low,
                    funding_rate_close: close,
                    interval: "8h",
                    symbol_price: null,
                });
            }

            return result.sort((a, b) => a.ts - b.ts);
        },

        // Update URL with all filters
        updateURL() {
            if (window.history && window.history.pushState) {
                const url = new URL(window.location);
                url.searchParams.set("symbol", this.globalSymbol);
                url.searchParams.set("marginType", this.globalMarginType || "");
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
                        marginType: this.globalMarginType,
                    },
                })
            );

            // Reset loading state after delay
            setTimeout(() => {
                this.globalLoading = false;
                console.log("✅ All components refreshed");
            }, 2000);
        },

        // Log dashboard status
        logDashboardStatus() {
            console.group("📊 Dashboard Status");
            console.log("Symbol:", this.globalSymbol);
            console.log("Margin Type:", this.globalMarginType || "All");
            console.log("Components loaded:", this.components.count || 0);
            const baseMeta = document.querySelector(
                'meta[name="api-base-url"]'
            );
            const baseUrl = (baseMeta?.content || "").trim() || "(relative)";
            console.log("API Base:", baseUrl);
            console.groupEnd();
        },

        // Utility: Format funding rate
        formatRate(value) {
            if (value === null || value === undefined) return "N/A";
            const percent = (parseFloat(value) * 100).toFixed(4);
            return (parseFloat(value) >= 0 ? "+" : "") + percent + "%";
        },

        // Utility: Get bias color class
        getBiasColorClass(bias) {
            const biasLower = (bias || "").toLowerCase();
            if (biasLower.includes("long")) return "text-success";
            if (biasLower.includes("short")) return "text-danger";
            return "text-secondary";
        },

        // Utility: Calculate APR from funding rate
        calculateAPR(rate, intervalHours) {
            if (!rate || !intervalHours) return "N/A";
            const numRate = parseFloat(rate);
            const periodsPerYear = (365 * 24) / intervalHours;
            const apr = (numRate * periodsPerYear * 100).toFixed(1);
            return (numRate >= 0 ? "+" : "") + apr + "%";
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

        // Utility: Calculate time until
        timeUntil(timestamp) {
            if (!timestamp || timestamp <= Date.now()) return "N/A";
            const diff = timestamp - Date.now();
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            return `${hours}h ${minutes}m`;
        },

        // Trading insights helper
        getTradingInsight(bias, strength, avgFunding) {
            const insights = {
                long_extreme: {
                    icon: "🚨",
                    title: "Extreme Long Positioning",
                    message:
                        "Very high long bias with elevated funding rates. High risk of long squeeze if price fails to break resistance. Consider taking profits or hedging.",
                    severity: "danger",
                },
                long_moderate: {
                    icon: "📈",
                    title: "Long Bias Building",
                    message:
                        "Moderate long positioning with positive funding. Market bullish but not extreme. Watch for resistance levels and funding rate increases.",
                    severity: "warning",
                },
                short_extreme: {
                    icon: "🚨",
                    title: "Extreme Short Positioning",
                    message:
                        "Very high short bias with negative funding rates. High risk of short squeeze on positive catalysts. Stops should be tight.",
                    severity: "danger",
                },
                short_moderate: {
                    icon: "📉",
                    title: "Short Pressure Active",
                    message:
                        "Moderate short positioning with negative funding. Market bearish but not extreme. Look for bounce setups or wait for flush.",
                    severity: "warning",
                },
                neutral: {
                    icon: "💡",
                    title: "Balanced Market",
                    message:
                        "No extreme positioning detected. Funding rates near neutral. Normal trading conditions with no immediate squeeze risk.",
                    severity: "info",
                },
            };

            const biasLower = (bias || "").toLowerCase();
            const strengthNum = parseFloat(strength) || 0;

            let key = "neutral";
            if (biasLower.includes("long") && strengthNum > 70)
                key = "long_extreme";
            else if (biasLower.includes("long")) key = "long_moderate";
            else if (biasLower.includes("short") && strengthNum > 70)
                key = "short_extreme";
            else if (biasLower.includes("short")) key = "short_moderate";

            return insights[key];
        },

        // API Helper: Fetch with error handling
        async fetchAPI(endpoint, params = {}) {
            const queryString = new URLSearchParams(params).toString();
            const baseMeta = document.querySelector(
                'meta[name="api-base-url"]'
            );
            const configuredBase = (baseMeta?.content || "").trim();

            let url = `/api/funding-rate/${endpoint}?${queryString}`; // default relative
            if (configuredBase) {
                const normalizedBase = configuredBase.endsWith("/")
                    ? configuredBase.slice(0, -1)
                    : configuredBase;
                url = `${normalizedBase}/api/funding-rate/${endpoint}?${queryString}`;
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
                    : data?.summary || data?.bias || data?.analytics
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
window.FundingRateCharts = {
    // Default chart colors
    colors: {
        primary: "#3b82f6",
        success: "#22c55e",
        danger: "#ef4444",
        warning: "#f59e0b",
        purple: "#8b5cf6",
        gray: "#6b7280",
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
window.FundingRateUtils = {
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
                    `funding_rate_${key}`,
                    JSON.stringify(value)
                );
            } catch (e) {
                console.warn("LocalStorage not available");
            }
        },
        get(key, defaultValue = null) {
            try {
                const item = localStorage.getItem(`funding_rate_${key}`);
                return item ? JSON.parse(item) : defaultValue;
            } catch (e) {
                return defaultValue;
            }
        },
        remove(key) {
            try {
                localStorage.removeItem(`funding_rate_${key}`);
            } catch (e) {
                console.warn("LocalStorage not available");
            }
        },
    },
};

console.log("✅ Funding Rate Controller loaded");
