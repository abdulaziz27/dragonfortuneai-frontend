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
        loading: false,
        selectedSymbol: 'BTCUSDT',  // Only BTCUSDT has data
        selectedInterval: '5m',     // Only 5m works properly
        selectedLimit: 200,         // This filter works
        selectedExchange: 'binance', // Only Binance has data
        
        // Auto-refresh State
        autoRefreshEnabled: true,
        autoRefreshTimer: null,
        autoRefreshInterval: 5000,   // 5 seconds
        lastUpdated: null,
        
        // Debouncing
        filterDebounceTimer: null,
        filterDebounceDelay: 300,

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
            console.log("🚀 Enhanced VWAP/TWAP Dashboard initialized");
            console.log("📊 Symbol:", this.selectedSymbol);
            console.log("⏱️ Interval:", this.selectedInterval);
            console.log("🏢 Exchange:", this.selectedExchange);
            console.log("🔄 Auto-refresh:", this.autoRefreshEnabled ? 'ON' : 'OFF');

            // Initialize shared state
            this.initializeSharedState();

            // Setup event listeners
            this.setupEventListeners();

            // Initial load
            this.loadAllData().catch((e) =>
                console.warn("Initial data load failed:", e)
            );

            // Start auto-refresh
            this.startAutoRefresh();
            
            // Setup visibility API
            this.setupVisibilityAPI();

            // Log dashboard ready
            setTimeout(() => {
                console.log("✅ All components loaded");
                this.logDashboardStatus();
            }, 2000);
        },

        // Initialize shared state management
        initializeSharedState() {
            if (!window.SpotMicrostructureSharedState) {
                window.SpotMicrostructureSharedState = {
                    filters: {
                        selectedSymbol: this.selectedSymbol,
                        selectedInterval: this.selectedInterval,
                        selectedLimit: this.selectedLimit,
                        selectedExchange: this.selectedExchange
                    },
                    subscribers: {},
                    
                    setFilter(key, value) {
                        this.filters[key] = value;
                        this.notifySubscribers(key, value);
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
            }
            
            // Subscribe to shared state changes
            window.SpotMicrostructureSharedState.subscribe('selectedSymbol', (value) => {
                if (this.selectedSymbol !== value) {
                    this.selectedSymbol = value;
                    this.handleFilterChange();
                }
            });
            
            window.SpotMicrostructureSharedState.subscribe('selectedInterval', (value) => {
                if (this.selectedInterval !== value) {
                    this.selectedInterval = value;
                    this.handleFilterChange();
                }
            });
            
            window.SpotMicrostructureSharedState.subscribe('selectedLimit', (value) => {
                if (this.selectedLimit !== value) {
                    this.selectedLimit = value;
                    this.handleFilterChange();
                }
            });
            
            window.SpotMicrostructureSharedState.subscribe('selectedExchange', (value) => {
                if (this.selectedExchange !== value) {
                    this.selectedExchange = value;
                    this.handleFilterChange();
                }
            });
        },

        // Handle filter changes with debouncing
        handleFilterChange() {
            if (this.filterDebounceTimer) {
                clearTimeout(this.filterDebounceTimer);
            }
            
            this.filterDebounceTimer = setTimeout(() => {
                console.log('🎛️ Filter changed:', {
                    symbol: this.selectedSymbol,
                    interval: this.selectedInterval,
                    limit: this.selectedLimit,
                    exchange: this.selectedExchange
                });
                
                this.loadAllData().catch((e) =>
                    console.warn("Data reload failed:", e)
                );
            }, this.filterDebounceDelay);
        },

        // Setup global event listeners
        setupEventListeners() {
            // Legacy event listeners for backward compatibility
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

        // Legacy methods for backward compatibility
        updateSymbol() {
            this.onSymbolChange();
        },

        updateTimeframe() {
            this.onIntervalChange();
        },

        updateExchange() {
            this.onExchangeChange();
        },

        // Auto-refresh methods
        startAutoRefresh() {
            if (this.autoRefreshTimer) {
                clearInterval(this.autoRefreshTimer);
            }
            
            if (this.autoRefreshEnabled) {
                this.autoRefreshTimer = setInterval(() => {
                    if (this.autoRefreshEnabled && !document.hidden) {
                        console.log('🔄 Auto-refreshing VWAP data...');
                        this.loadAllData();
                    }
                }, this.autoRefreshInterval);
                
                console.log('✅ Auto-refresh started (5s intervals)');
            }
        },

        stopAutoRefresh() {
            if (this.autoRefreshTimer) {
                clearInterval(this.autoRefreshTimer);
                this.autoRefreshTimer = null;
                console.log('⏹️ Auto-refresh stopped');
            }
        },

        toggleAutoRefresh() {
            this.autoRefreshEnabled = !this.autoRefreshEnabled;
            console.log('🔄 Auto-refresh toggled:', this.autoRefreshEnabled ? 'ON' : 'OFF');
            
            if (this.autoRefreshEnabled) {
                this.startAutoRefresh();
            } else {
                this.stopAutoRefresh();
            }
        },

        // Setup Visibility API for tab switching
        setupVisibilityAPI() {
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    console.log('👁️ Tab hidden - pausing auto-refresh');
                } else {
                    console.log('👁️ Tab visible - resuming auto-refresh');
                    if (this.autoRefreshEnabled) {
                        this.loadAllData(); // Immediate refresh when tab becomes visible
                    }
                }
            });
        },

        // Filter change handlers
        onSymbolChange() {
            window.SpotMicrostructureSharedState.setFilter('selectedSymbol', this.selectedSymbol);
        },

        onIntervalChange() {
            window.SpotMicrostructureSharedState.setFilter('selectedInterval', this.selectedInterval);
        },

        onLimitChange() {
            window.SpotMicrostructureSharedState.setFilter('selectedLimit', this.selectedLimit);
        },

        onExchangeChange() {
            window.SpotMicrostructureSharedState.setFilter('selectedExchange', this.selectedExchange);
        },

        // Manual refresh method
        async manualRefresh() {
            console.log("🔄 Manual refresh triggered");
            await this.loadAllData();
        },

        // Load all VWAP data with enhanced error handling
        async loadAllData() {
            this.loading = true;
            const startTime = Date.now();
            
            try {
                console.log('🔄 Loading VWAP data...', {
                    symbol: this.selectedSymbol,
                    interval: this.selectedInterval,
                    exchange: this.selectedExchange,
                    limit: this.selectedLimit
                });

                // Load historical and latest data in parallel
                const [historical, latest] = await Promise.all([
                    this.fetchHistoricalVWAP(),
                    this.fetchLatestVWAP(),
                ]);

                // Validate and store data
                this.historicalData = Array.isArray(historical) ? historical : [];
                this.latestData = latest || null;

                // Log data quality
                const loadTime = Date.now() - startTime;
                console.log('✅ VWAP data loaded:', {
                    historical_count: this.historicalData.length,
                    latest_available: !!this.latestData,
                    current_price_available: !!(this.latestData?.current_price),
                    load_time_ms: loadTime
                });

                // Broadcast enhanced data-ready event
                window.dispatchEvent(
                    new CustomEvent("vwap-data-ready", {
                        detail: {
                            historical: this.historicalData,
                            latest: this.latestData,
                            symbol: this.selectedSymbol,
                            timeframe: this.selectedInterval,
                            exchange: this.selectedExchange,
                            timestamp: new Date().toISOString(),
                            load_time: loadTime
                        },
                    })
                );

                this.lastUpdated = new Date().toLocaleTimeString();
                console.log("✅ VWAP data broadcast completed at:", this.lastUpdated);
                
            } catch (error) {
                console.error("❌ Error loading VWAP data:", error);
                
                // Broadcast error event for components to handle
                window.dispatchEvent(
                    new CustomEvent("vwap-data-error", {
                        detail: {
                            error: error.message || 'Unknown error',
                            symbol: this.selectedSymbol,
                            timeframe: this.selectedInterval,
                            exchange: this.selectedExchange,
                            timestamp: new Date().toISOString()
                        },
                    })
                );
                
            } finally {
                this.loading = false;
            }
        },

        // Cleanup on destroy
        beforeDestroy() {
            this.stopAutoRefresh();
            if (this.filterDebounceTimer) {
                clearTimeout(this.filterDebounceTimer);
            }
        },

        // Fetch historical VWAP data with field normalization
        async fetchHistoricalVWAP() {
            const params = {
                symbol: this.selectedSymbol,
                interval: this.selectedInterval,
                exchange: this.selectedExchange,
                limit: this.selectedLimit,
            };

            try {
                const response = await this.fetchAPI("vwap", params);
                
                // Handle API response field variations and normalize data
                if (response?.data && Array.isArray(response.data)) {
                    return response.data.map(item => this.normalizeVWAPData(item));
                }
                
                return [];
            } catch (error) {
                console.error("❌ Error fetching historical VWAP:", error);
                return [];
            }
        },

        // Fetch latest VWAP data with field normalization
        async fetchLatestVWAP() {
            const params = {
                symbol: this.selectedSymbol,
                interval: this.selectedInterval,
                exchange: this.selectedExchange,
            };

            try {
                const data = await this.fetchAPI("vwap/latest", params);
                
                // Normalize latest data format
                if (data) {
                    const normalizedData = this.normalizeVWAPData(data);
                    
                    // Use VWAP as current price (most accurate for our use case)
                    normalizedData.current_price = normalizedData.vwap;
                    
                    return normalizedData;
                }
                
                return null;
            } catch (error) {
                console.error("❌ Error fetching latest VWAP:", error);
                return null;
            }
        },

        // Normalize VWAP data to handle API response field variations
        normalizeVWAPData(item) {
            if (!item) return null;
            
            return {
                ...item,
                // Handle symbol/pair field variations
                symbol: item.symbol || item.pair || this.selectedSymbol,
                // Handle timestamp/ts field variations
                timestamp: item.timestamp || item.ts,
                // Ensure numeric values are properly parsed
                vwap: parseFloat(item.vwap) || 0,
                upper_band: parseFloat(item.upper_band) || 0,
                lower_band: parseFloat(item.lower_band) || 0,
                // Add exchange and timeframe if missing
                exchange: item.exchange || this.selectedExchange,
                timeframe: item.timeframe || this.selectedInterval
            };
        },

        // Use VWAP as current price (no external API needed)
        async fetchCurrentSpotPrice() {
            // We already have accurate data from our API
            // No need for external Binance API calls
            console.log('ℹ️ Using VWAP as current price (internal API data)');
            return null; // Will fallback to VWAP
        },

        // Update URL with current filters
        updateURL() {
            if (window.history && window.history.pushState) {
                const url = new URL(window.location);
                url.searchParams.set("symbol", this.selectedSymbol);
                url.searchParams.set("interval", this.selectedInterval);
                url.searchParams.set("exchange", this.selectedExchange);
                window.history.pushState({}, "", url);
            }
        },

        // Legacy refresh method
        refreshAll() {
            this.manualRefresh();
        },

        // Log dashboard status
        logDashboardStatus() {
            console.group("📊 Enhanced VWAP Dashboard Status");
            console.log("Symbol:", this.selectedSymbol);
            console.log("Interval:", this.selectedInterval);
            console.log("Exchange:", this.selectedExchange);
            console.log("Limit:", this.selectedLimit);
            console.log("Auto-refresh:", this.autoRefreshEnabled ? 'ON' : 'OFF');
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
