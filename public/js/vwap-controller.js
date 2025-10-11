/**
 * VWAP/TWAP Analysis Controller
 *
 * Global controller untuk mengoordinasikan semua komponen VWAP/TWAP
 *
 * Think like a trader:
 * - VWAP adalah harga rata-rata tertimbang volume, menunjukkan nilai wajar berdasarkan aktivitas trading
 * - Price above VWAP → Market bullish, buyers strong
 * - Price below VWAP → Market bearish, sellers dominant
 * - VWAP bands (upper/lower) → Volatility bands untuk breakout/reversion signals
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

function vwapController() {
    return {
        // Global state
        globalSymbol: "BTCUSDT",
        globalTimeframe: "5min",
        globalExchange: "binance",
        globalLimit: 2000,
        globalLoading: false,

        // Component references
        components: {
            priceChart: null,
            statsCard: null,
            bandsChart: null,
            historyTable: null,
        },

        // Cache
        latestData: null,
        historicalData: [],

        // Initialize dashboard
        init() {
            console.log("🚀 VWAP/TWAP Dashboard initialized");
            console.log("📊 Symbol:", this.globalSymbol);
            console.log("⏱️ Timeframe:", this.globalTimeframe);
            console.log("🏢 Exchange:", this.globalExchange);

            // Setup event listeners
            this.setupEventListeners();

            // Initial load
            this.loadAllData().catch((e) =>
                console.warn("Initial data load failed:", e)
            );

            // Log dashboard ready
            setTimeout(() => {
                console.log("✅ All components loaded");
                this.logDashboardStatus();
            }, 2000);
        },

        // Setup global event listeners
        setupEventListeners() {
            // Listen for filter changes
            window.addEventListener("symbol-changed", () => {
                this.loadAllData().catch((e) =>
                    console.warn("Data reload failed:", e)
                );
            });
            window.addEventListener("timeframe-changed", () => {
                this.loadAllData().catch((e) =>
                    console.warn("Data reload failed:", e)
                );
            });
            window.addEventListener("exchange-changed", () => {
                this.loadAllData().catch((e) =>
                    console.warn("Data reload failed:", e)
                );
            });
            window.addEventListener("refresh-all", () => {
                this.loadAllData().catch((e) =>
                    console.warn("Data reload failed:", e)
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
                        timeframe: this.globalTimeframe,
                        exchange: this.globalExchange,
                    },
                })
            );

            this.updateURL();
        },

        // Update timeframe globally
        updateTimeframe() {
            console.log("🔄 Updating timeframe to:", this.globalTimeframe);

            // Dispatch event to all components
            window.dispatchEvent(
                new CustomEvent("timeframe-changed", {
                    detail: {
                        symbol: this.globalSymbol,
                        timeframe: this.globalTimeframe,
                        exchange: this.globalExchange,
                    },
                })
            );

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
                        timeframe: this.globalTimeframe,
                        exchange: this.globalExchange,
                    },
                })
            );

            this.updateURL();
        },

        // Load all VWAP data
        async loadAllData() {
            this.globalLoading = true;
            try {
                // Load historical and latest data in parallel
                const [historical, latest] = await Promise.all([
                    this.fetchHistoricalVWAP(),
                    this.fetchLatestVWAP(),
                ]);

                this.historicalData = historical || [];
                this.latestData = latest || null;

                // Broadcast data-ready event
                window.dispatchEvent(
                    new CustomEvent("vwap-data-ready", {
                        detail: {
                            historical: this.historicalData,
                            latest: this.latestData,
                            symbol: this.globalSymbol,
                            timeframe: this.globalTimeframe,
                            exchange: this.globalExchange,
                        },
                    })
                );

                console.log("✅ VWAP data loaded successfully");
            } catch (error) {
                console.error("❌ Error loading VWAP data:", error);
            } finally {
                this.globalLoading = false;
            }
        },

        // Fetch historical VWAP data
        async fetchHistoricalVWAP() {
            const params = {
                symbol: this.globalSymbol,
                timeframe: this.globalTimeframe,
                exchange: this.globalExchange,
                limit: this.globalLimit,
            };

            try {
                const data = await this.fetchAPI("vwap", params);
                return Array.isArray(data?.data) ? data.data : [];
            } catch (error) {
                console.error("❌ Error fetching historical VWAP:", error);
                return [];
            }
        },

        // Fetch latest VWAP data
        async fetchLatestVWAP() {
            const params = {
                symbol: this.globalSymbol,
                timeframe: this.globalTimeframe,
                exchange: this.globalExchange,
            };

            try {
                const data = await this.fetchAPI("vwap/latest", params);
                return data || null;
            } catch (error) {
                console.error("❌ Error fetching latest VWAP:", error);
                return null;
            }
        },

        // Update URL with current filters
        updateURL() {
            if (window.history && window.history.pushState) {
                const url = new URL(window.location);
                url.searchParams.set("symbol", this.globalSymbol);
                url.searchParams.set("timeframe", this.globalTimeframe);
                url.searchParams.set("exchange", this.globalExchange);
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
                        timeframe: this.globalTimeframe,
                        exchange: this.globalExchange,
                    },
                })
            );

            // Reload data
            this.loadAllData();

            // Reset loading state after delay
            setTimeout(() => {
                this.globalLoading = false;
                console.log("✅ All components refreshed");
            }, 2000);
        },

        // Log dashboard status
        logDashboardStatus() {
            console.group("📊 VWAP Dashboard Status");
            console.log("Symbol:", this.globalSymbol);
            console.log("Timeframe:", this.globalTimeframe);
            console.log("Exchange:", this.globalExchange);
            console.log("Historical data points:", this.historicalData.length);
            console.log("Latest data:", this.latestData ? "Available" : "N/A");
            const baseMeta = document.querySelector(
                'meta[name="api-base-url"]'
            );
            const baseUrl = (baseMeta?.content || "").trim() || "(relative)";
            console.log("API Base:", baseUrl);
            console.groupEnd();
        },

        // Utility: Format price
        formatPrice(value) {
            if (value === null || value === undefined || isNaN(value))
                return "N/A";
            return new Intl.NumberFormat("en-US", {
                style: "currency",
                currency: "USD",
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(parseFloat(value));
        },

        // Utility: Format percentage
        formatPercent(value) {
            if (value === null || value === undefined || isNaN(value))
                return "N/A";
            const percent = parseFloat(value).toFixed(2);
            return (parseFloat(value) >= 0 ? "+" : "") + percent + "%";
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

        // Utility: Get price position relative to VWAP
        getPricePosition(currentPrice, vwap) {
            if (!currentPrice || !vwap) return "unknown";
            const diff = ((currentPrice - vwap) / vwap) * 100;
            if (diff > 0.5) return "above";
            if (diff < -0.5) return "below";
            return "near";
        },

        // Utility: Get market bias based on VWAP
        getMarketBias(currentPrice, vwap, upperBand, lowerBand) {
            if (!currentPrice || !vwap) return "neutral";

            const position = this.getPricePosition(currentPrice, vwap);

            if (position === "above") {
                if (currentPrice > upperBand) return "strong_bullish";
                return "bullish";
            } else if (position === "below") {
                if (currentPrice < lowerBand) return "strong_bearish";
                return "bearish";
            }
            return "neutral";
        },

        // Utility: Get trading signal
        getTradingSignal(currentPrice, vwap, upperBand, lowerBand) {
            const bias = this.getMarketBias(
                currentPrice,
                vwap,
                upperBand,
                lowerBand
            );

            const signals = {
                strong_bullish: {
                    icon: "🚀",
                    title: "Strong Bullish Breakout",
                    message:
                        "Price has broken above upper VWAP band. Strong buying pressure. Watch for continuation or mean reversion.",
                    badge: "success",
                },
                bullish: {
                    icon: "📈",
                    title: "Bullish Bias",
                    message:
                        "Price trading above VWAP. Buyers in control. Good for dip buying opportunities.",
                    badge: "success",
                },
                strong_bearish: {
                    icon: "📉",
                    title: "Strong Bearish Breakdown",
                    message:
                        "Price has broken below lower VWAP band. Strong selling pressure. Watch for capitulation or bounce.",
                    badge: "danger",
                },
                bearish: {
                    icon: "🔻",
                    title: "Bearish Bias",
                    message:
                        "Price trading below VWAP. Sellers in control. Look for bounce setups to resistance.",
                    badge: "danger",
                },
                neutral: {
                    icon: "⚖️",
                    title: "Neutral / Range-Bound",
                    message:
                        "Price trading near VWAP. No clear directional bias. Wait for breakout or range trade.",
                    badge: "secondary",
                },
            };

            return signals[bias] || signals.neutral;
        },

        // API Helper: Fetch with error handling
        async fetchAPI(endpoint, params = {}) {
            // Clean up params - remove empty values
            const cleanParams = Object.fromEntries(
                Object.entries(params).filter(([_, v]) => v != null && v !== "")
            );

            const queryString = new URLSearchParams(cleanParams).toString();
            const baseMeta = document.querySelector(
                'meta[name="api-base-url"]'
            );
            const configuredBase = (baseMeta?.content || "").trim();

            let url = `/api/spot-microstructure/${endpoint}?${queryString}`; // default relative
            if (configuredBase) {
                const normalizedBase = configuredBase.endsWith("/")
                    ? configuredBase.slice(0, -1)
                    : configuredBase;
                url = `${normalizedBase}/api/spot-microstructure/${endpoint}?${queryString}`;
            }

            try {
                console.log("📡 Fetching:", endpoint, cleanParams);
                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error(
                        `HTTP ${response.status}: ${response.statusText}`
                    );
                }

                const data = await response.json();
                const itemCount = Array.isArray(data?.data)
                    ? data.data.length
                    : "single";
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
window.VWAPCharts = {
    // Default chart colors
    colors: {
        price: "#3b82f6",
        vwap: "#10b981",
        upperBand: "#ef4444",
        lowerBand: "#ef4444",
        volume: "#8b5cf6",
        gray: "#6b7280",
    },

    // Common chart options
    getCommonOptions(title = "") {
        return {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: "index",
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: "top",
                    labels: {
                        color: "#94a3b8",
                        font: { size: 11 },
                        padding: 15,
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
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || "";
                            if (label) {
                                label += ": ";
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat("en-US", {
                                    style: "currency",
                                    currency: "USD",
                                }).format(context.parsed.y);
                            }
                            return label;
                        },
                    },
                },
            },
            scales: {
                x: {
                    type: "time",
                    time: {
                        unit: "minute",
                        displayFormats: {
                            minute: "HH:mm",
                            hour: "HH:mm",
                        },
                    },
                    ticks: {
                        color: "#94a3b8",
                        font: { size: 10 },
                        maxRotation: 0,
                        autoSkipPadding: 20,
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
                            return "$" + value.toLocaleString();
                        },
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
        const rgb = color.match(/\d+/g);
        if (rgb) {
            gradient.addColorStop(
                0,
                `rgba(${rgb[0]}, ${rgb[1]}, ${rgb[2]}, ${alpha})`
            );
            gradient.addColorStop(
                1,
                `rgba(${rgb[0]}, ${rgb[1]}, ${rgb[2]}, 0)`
            );
        }
        return gradient;
    },
};

/**
 * Utility Functions
 */
window.VWAPUtils = {
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
                localStorage.setItem(`vwap_${key}`, JSON.stringify(value));
            } catch (e) {
                console.warn("LocalStorage not available");
            }
        },
        get(key, defaultValue = null) {
            try {
                const item = localStorage.getItem(`vwap_${key}`);
                return item ? JSON.parse(item) : defaultValue;
            } catch (e) {
                return defaultValue;
            }
        },
        remove(key) {
            try {
                localStorage.removeItem(`vwap_${key}`);
            } catch (e) {
                console.warn("LocalStorage not available");
            }
        },
    },
};

console.log("✅ VWAP Controller loaded");
