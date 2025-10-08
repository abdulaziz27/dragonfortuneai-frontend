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

        // Initialize dashboard
        init() {
            console.log("🚀 Funding Rate Dashboard initialized");
            console.log("📊 Symbol:", this.globalSymbol);
            console.log("💰 Margin Type:", this.globalMarginType || "All");

            // Setup event listeners
            this.setupEventListeners();

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
                console.log(
                    "✅ Received:",
                    endpoint,
                    data.data?.length || "N/A",
                    "items"
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
