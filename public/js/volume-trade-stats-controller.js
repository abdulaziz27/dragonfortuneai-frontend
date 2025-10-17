/**
 * Volume & Trade Stats Controller
 *
 * Comprehensive volume analysis dashboard for spot microstructure
 *
 * API Endpoints:
 * 1. /api/spot-microstructure/trade-stats - Trade statistics over time
 * 2. /api/spot-microstructure/volume-profile - Aggregated volume analysis
 * 3. /api/spot-microstructure/volume-profile-detailed - Volume distribution by price
 * 4. /api/spot-microstructure/volume-stats - Volume statistics with buy/sell breakdown
 */

function volumeTradeStatsController() {
    return {
        // Global state
        loading: false,
        selectedSymbol: 'BTCUSDT',
        selectedInterval: '5m', 
        selectedLimit: 50,
        selectedExchange: 'binance',

        // Auto-refresh State
        autoRefreshEnabled: true,
        autoRefreshTimer: null,
        autoRefreshInterval: 5000,   // 5 seconds
        lastUpdated: null,

        // Debouncing
        filterDebounceTimer: null,
        filterDebounceDelay: 300,

        // Data storage
        tradeStatsData: [],
        volumeProfileData: null,
        volumeProfileDetailedData: [],
        volumeStatsData: [],

        // Computed metrics
        metrics: {
            totalTrades: 0,
            avgTradeSize: 0,
            maxTradeSize: 0,
            buyTrades: 0,
            sellTrades: 0,
            buySellRatio: 0,
            totalVolume: 0,
            buyVolume: 0,
            sellVolume: 0,
            volumeStd: 0,
            pocPrice: 0,
        },

        // Chart instances
        charts: {
            tradeStats: null,
            volumeTimeSeries: null,
            buySellDistribution: null,
            volumeProfile: null,
            tradeSizeDistribution: null,
            volumeHeatmap: null,
        },

        // Initialize
        init() {
            console.log("🚀 Enhanced Volume & Trade Stats Dashboard initialized");
            console.log("📊 Symbol:", this.selectedSymbol);
            console.log("⏱️ Interval:", this.selectedInterval);
            console.log("🏢 Exchange:", this.selectedExchange);
            console.log("🔄 Auto-refresh:", this.autoRefreshEnabled ? 'ON' : 'OFF');

            // Initialize shared state
            this.initializeSharedState();

            // Load all data
            this.loadAllData();

            // Start auto-refresh
            this.startAutoRefresh();

            // Setup visibility API
            this.setupVisibilityAPI();
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

                this.loadAllData();
            }, this.filterDebounceDelay);
        },

        // Auto-refresh methods
        startAutoRefresh() {
            if (this.autoRefreshTimer) {
                clearInterval(this.autoRefreshTimer);
            }

            if (this.autoRefreshEnabled) {
                this.autoRefreshTimer = setInterval(() => {
                    if (this.autoRefreshEnabled && !document.hidden) {
                        console.log('🔄 Auto-refreshing volume data...');
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

        refreshAll() {
            this.manualRefresh();
        },

        // Load all data from APIs
        async loadAllData() {
            this.loading = true;
            console.log("📊 Loading all volume data...");

            try {
                await Promise.all([
                    this.loadTradeStats(),
                    this.loadVolumeProfile(),
                    this.loadVolumeProfileDetailed(),
                    this.loadVolumeStats(),
                ]);

                this.calculateMetrics();
                this.renderAllCharts();
                this.lastUpdated = new Date().toLocaleTimeString();
                console.log("✅ All data loaded successfully at:", this.lastUpdated);
            } catch (error) {
                console.error("❌ Error loading data:", error);
            } finally {
                this.loading = false;
            }
        },

        // Enhanced destroy all charts with error handling
        destroyAllCharts() {
            Object.keys(this.charts).forEach((key) => {
                if (this.charts[key]) {
                    try {
                        if (typeof this.charts[key].stop === 'function') {
                            this.charts[key].stop();
                        }
                        this.charts[key].destroy();
                    } catch (error) {
                        console.warn(`Error destroying chart ${key}:`, error);
                    }
                    this.charts[key] = null;
                }
            });
        },

        // Cleanup on destroy
        beforeDestroy() {
            this.stopAutoRefresh();
            this.destroyAllCharts();
            if (this.filterDebounceTimer) {
                clearTimeout(this.filterDebounceTimer);
            }
        },

        // API: Load Trade Stats
        async loadTradeStats() {
            try {
                const params = new URLSearchParams({
                    timeframe: this.selectedInterval === '5m' ? '5min' : this.selectedInterval,
                    limit: this.selectedLimit,
                });

                const url = this.buildAPIUrl(
                    `/api/spot-microstructure/trade-stats?${params}`
                );
                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();

                if (data.error) {
                    console.warn("⚠️ Trade Stats returned error:", data.error);
                    this.tradeStatsData = [];
                } else {
                    this.tradeStatsData = Array.isArray(data.data)
                        ? data.data
                        : [];
                    console.log(
                        "✅ Trade Stats loaded:",
                        this.tradeStatsData.length,
                        "records"
                    );
                }
            } catch (error) {
                console.error("❌ Error loading trade stats:", error);
                this.tradeStatsData = [];
            }
        },

        // API: Load Volume Profile
        async loadVolumeProfile() {
            try {
                const params = new URLSearchParams({
                    symbol: this.selectedSymbol.toLowerCase(),
                    limit: this.selectedLimit,
                });

                const url = this.buildAPIUrl(
                    `/api/spot-microstructure/volume-profile?${params}`
                );
                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();

                // Check if response has error
                if (data.error) {
                    console.warn(
                        "⚠️ Volume Profile returned error:",
                        data.error
                    );
                    this.volumeProfileData = null;
                } else {
                    // API returns array, take first item
                    this.volumeProfileData = Array.isArray(data.data) && data.data.length > 0 ? data.data[0] : null;
                    console.log("✅ Volume Profile loaded:", this.volumeProfileData);
                }
            } catch (error) {
                console.error("❌ Error loading volume profile:", error);
                this.volumeProfileData = null;
            }
        },

        // API: Load Volume Profile Detailed
        async loadVolumeProfileDetailed() {
            try {
                const params = new URLSearchParams({
                    limit: Math.min(this.selectedLimit * 2, 2000),
                });

                const url = this.buildAPIUrl(
                    `/api/spot-microstructure/volume-profile-detailed?${params}`
                );
                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();

                if (data.error) {
                    console.warn(
                        "⚠️ Volume Profile Detailed returned error:",
                        data.error
                    );
                    this.volumeProfileDetailedData = [];
                } else {
                    this.volumeProfileDetailedData = Array.isArray(data.data)
                        ? data.data
                        : [];
                    console.log(
                        "✅ Volume Profile Detailed loaded:",
                        this.volumeProfileDetailedData.length,
                        "records"
                    );
                }
            } catch (error) {
                console.error(
                    "❌ Error loading volume profile detailed:",
                    error
                );
                this.volumeProfileDetailedData = [];
            }
        },

        // API: Load Volume Stats
        async loadVolumeStats() {
            try {
                const params = new URLSearchParams({
                    timeframe: this.selectedInterval === '5m' ? '5min' : this.selectedInterval,
                    limit: this.selectedLimit,
                });

                const url = this.buildAPIUrl(
                    `/api/spot-microstructure/volume-stats?${params}`
                );
                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();

                if (data.error) {
                    console.warn("⚠️ Volume Stats returned error:", data.error);
                    this.volumeStatsData = [];
                } else {
                    this.volumeStatsData = Array.isArray(data.data)
                        ? data.data
                        : [];
                    console.log(
                        "✅ Volume Stats loaded:",
                        this.volumeStatsData.length,
                        "records"
                    );
                }
            } catch (error) {
                console.error("❌ Error loading volume stats:", error);
                this.volumeStatsData = [];
            }
        },

        // Calculate aggregated metrics
        calculateMetrics() {
            console.log("📊 Calculating metrics...");
            console.log("📈 Trade Stats Data length:", this.tradeStatsData.length);
            console.log("📊 Volume Stats Data length:", this.volumeStatsData.length);
            console.log("💎 Volume Profile Detailed length:", this.volumeProfileDetailedData.length);

            // Reset metrics
            this.metrics = {
                totalTrades: 0,
                avgTradeSize: 0,
                maxTradeSize: 0,
                buyTrades: 0,
                sellTrades: 0,
                buySellRatio: 0,
                totalVolume: 0,
                buyVolume: 0,
                sellVolume: 0,
                volumeStd: 0,
                pocPrice: 0,
            };

            // From trade stats data (aggregate all records)
            if (this.tradeStatsData.length > 0) {
                let totalTrades = 0;
                let totalBuyTrades = 0;
                let totalSellTrades = 0;
                let totalAvgSize = 0;
                let maxTradeSize = 0;

                this.tradeStatsData.forEach(record => {
                    totalTrades += record.trades_count || 0;
                    totalBuyTrades += record.buy_trades || 0;
                    totalSellTrades += record.sell_trades || 0;
                    totalAvgSize += record.avg_trade_size || 0;
                    maxTradeSize = Math.max(maxTradeSize, record.max_trade_size || 0);
                });

                this.metrics.totalTrades = totalTrades;
                this.metrics.buyTrades = totalBuyTrades;
                this.metrics.sellTrades = totalSellTrades;
                this.metrics.avgTradeSize = totalAvgSize / this.tradeStatsData.length;
                this.metrics.maxTradeSize = maxTradeSize;
                this.metrics.buySellRatio = totalSellTrades > 0 ? totalBuyTrades / totalSellTrades : 0;
            }

            // From volume stats (latest record)
            if (this.volumeStatsData.length > 0) {
                const latest = this.volumeStatsData[this.volumeStatsData.length - 1];
                this.metrics.totalVolume = latest.total_volume || 0;
                this.metrics.buyVolume = latest.buy_volume || 0;
                this.metrics.sellVolume = latest.sell_volume || 0;
                this.metrics.volumeStd = latest.volume_std || 0;
            }

            // From volume profile detailed (POC)
            if (this.volumeProfileDetailedData.length > 0) {
                const sorted = [...this.volumeProfileDetailedData].sort(
                    (a, b) => b.volume - a.volume
                );
                this.metrics.pocPrice = sorted[0]?.price_level || 0;
            }

            console.log("✅ Calculated metrics:", this.metrics);
        },

        // Render all charts
        renderAllCharts() {
            this.$nextTick(() => {
                this.renderTradeStatsChart();
                this.renderVolumeTimeSeriesChart();
                this.renderBuySellDistributionChart();
                this.renderVolumeProfileChart();
                this.renderTradeSizeDistributionChart();
            });
        },

        // Chart 1: Trade Stats Over Time
        renderTradeStatsChart() {
            const canvas = this.$refs.tradeStatsChart;
            if (!canvas) return;

            const ctx = canvas.getContext("2d");

            if (this.charts.tradeStats) {
                this.charts.tradeStats.destroy();
            }

            const labels = this.tradeStatsData.map((d) =>
                this.formatTimestamp(d.ts)
            );
            const totalTrades = this.tradeStatsData.map((d) => d.trades_count);
            const buyTrades = this.tradeStatsData.map((d) => d.buy_trades);
            const sellTrades = this.tradeStatsData.map((d) => d.sell_trades);

            this.charts.tradeStats = new Chart(ctx, {
                type: "line",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "Total Trades",
                            data: totalTrades,
                            borderColor: "#3b82f6",
                            backgroundColor: "rgba(59, 130, 246, 0.1)",
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                        },
                        {
                            label: "Buy Trades",
                            data: buyTrades,
                            borderColor: "#22c55e",
                            backgroundColor: "rgba(34, 197, 94, 0.1)",
                            borderWidth: 2,
                            tension: 0.4,
                            fill: false,
                        },
                        {
                            label: "Sell Trades",
                            data: sellTrades,
                            borderColor: "#ef4444",
                            backgroundColor: "rgba(239, 68, 68, 0.1)",
                            borderWidth: 2,
                            tension: 0.4,
                            fill: false,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 0  // ← CRITICAL: Disable animations to prevent flickering
                    },
                    interaction: {
                        mode: "index",
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: "top",
                        },
                        tooltip: {
                            backgroundColor: "rgba(0, 0, 0, 0.8)",
                            padding: 12,
                        },
                    },
                    scales: {
                        x: {
                            display: true,
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            display: true,
                            beginAtZero: true,
                            grid: {
                                color: "rgba(148, 163, 184, 0.1)",
                            },
                        },
                    },
                },
            });
        },

        // Chart 2: Volume Time Series
        renderVolumeTimeSeriesChart() {
            const canvas = this.$refs.volumeTimeSeriesChart;
            if (!canvas) return;

            const ctx = canvas.getContext("2d");

            if (this.charts.volumeTimeSeries) {
                this.charts.volumeTimeSeries.destroy();
            }

            const labels = this.volumeStatsData.map((d) =>
                this.formatTimestamp(d.timestamp)
            );
            const buyVolume = this.volumeStatsData.map((d) => d.buy_volume);
            const sellVolume = this.volumeStatsData.map((d) => -d.sell_volume); // Negative for visual

            this.charts.volumeTimeSeries = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "Buy Volume",
                            data: buyVolume,
                            backgroundColor: "rgba(34, 197, 94, 0.7)",
                            borderColor: "#22c55e",
                            borderWidth: 1,
                        },
                        {
                            label: "Sell Volume",
                            data: sellVolume,
                            backgroundColor: "rgba(239, 68, 68, 0.7)",
                            borderColor: "#ef4444",
                            borderWidth: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 0  // ← CRITICAL: Disable animations
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: "top",
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || "";
                                    if (label) {
                                        label += ": ";
                                    }
                                    label += Math.abs(
                                        context.parsed.y
                                    ).toLocaleString();
                                    return label;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            display: true,
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            display: true,
                            grid: {
                                color: "rgba(148, 163, 184, 0.1)",
                            },
                            ticks: {
                                callback: function (value) {
                                    return Math.abs(value).toLocaleString();
                                },
                            },
                        },
                    },
                },
            });
        },

        // Chart 3: Buy/Sell Distribution
        renderBuySellDistributionChart() {
            const canvas = this.$refs.buySellChart;
            if (!canvas) return;

            const ctx = canvas.getContext("2d");

            if (this.charts.buySellDistribution) {
                this.charts.buySellDistribution.destroy();
            }

            this.charts.buySellDistribution = new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels: ["Buy Trades", "Sell Trades"],
                    datasets: [
                        {
                            data: [
                                this.metrics.buyTrades,
                                this.metrics.sellTrades,
                            ],
                            backgroundColor: [
                                "rgba(34, 197, 94, 0.8)",
                                "rgba(239, 68, 68, 0.8)",
                            ],
                            borderWidth: 2,
                            borderColor: "#fff",
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 0  // ← CRITICAL: Disable animations
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: "bottom",
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const total = context.dataset.data.reduce(
                                        (a, b) => a + b,
                                        0
                                    );
                                    const value = context.parsed;
                                    const percentage = (
                                        (value / total) *
                                        100
                                    ).toFixed(1);
                                    return `${context.label
                                        }: ${value.toLocaleString()} (${percentage}%)`;
                                },
                            },
                        },
                    },
                },
            });
        },

        // Chart 4: Volume Profile (Price Levels)
        renderVolumeProfileChart() {
            const canvas = this.$refs.volumeProfileChart;
            if (!canvas) return;

            const ctx = canvas.getContext("2d");

            if (this.charts.volumeProfile) {
                this.charts.volumeProfile.destroy();
            }

            // Sort by volume and take top 20
            const sorted = [...this.volumeProfileDetailedData]
                .sort((a, b) => b.volume - a.volume)
                .slice(0, 20);

            const labels = sorted.map((d) => `$${d.price_level.toFixed(2)}`);
            const volumes = sorted.map((d) => d.volume);
            const percentages = sorted.map((d) => d.volume_pct);

            this.charts.volumeProfile = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "Volume at Price",
                            data: volumes,
                            backgroundColor: volumes.map((v, i) =>
                                i === 0
                                    ? "rgba(139, 92, 246, 0.8)"
                                    : "rgba(59, 130, 246, 0.6)"
                            ),
                            borderWidth: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 0  // ← CRITICAL: Disable animations
                    },
                    indexAxis: "y",
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const idx = context.dataIndex;
                                    return [
                                        `Volume: ${context.parsed.x.toLocaleString()}`,
                                        `Percentage: ${percentages[idx].toFixed(
                                            2
                                        )}%`,
                                    ];
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            display: true,
                            grid: {
                                color: "rgba(148, 163, 184, 0.1)",
                            },
                        },
                        y: {
                            display: true,
                            grid: {
                                display: false,
                            },
                        },
                    },
                },
            });
        },

        // Chart 5: Trade Size Distribution
        renderTradeSizeDistributionChart() {
            const canvas = this.$refs.tradeSizeChart;
            if (!canvas) return;

            const ctx = canvas.getContext("2d");

            if (this.charts.tradeSizeDistribution) {
                this.charts.tradeSizeDistribution.destroy();
            }

            const labels = this.tradeStatsData.map((d) =>
                this.formatTimestamp(d.ts)
            );
            const avgSize = this.tradeStatsData.map((d) => d.avg_trade_size);
            const maxSize = this.tradeStatsData.map((d) => d.max_trade_size);

            this.charts.tradeSizeDistribution = new Chart(ctx, {
                type: "line",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "Average Trade Size",
                            data: avgSize,
                            borderColor: "#3b82f6",
                            backgroundColor: "rgba(59, 130, 246, 0.1)",
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                        },
                        {
                            label: "Max Trade Size",
                            data: maxSize,
                            borderColor: "#f59e0b",
                            backgroundColor: "rgba(245, 158, 11, 0.1)",
                            borderWidth: 2,
                            tension: 0.4,
                            fill: false,
                            pointRadius: 3,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 0  // ← CRITICAL: Disable animations
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: "top",
                        },
                    },
                    scales: {
                        x: {
                            display: true,
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            display: true,
                            beginAtZero: true,
                            grid: {
                                color: "rgba(148, 163, 184, 0.1)",
                            },
                        },
                    },
                },
            });
        },

        // Update symbol filter
        updateSymbol() {
            console.log("🔄 Updating symbol to:", this.globalSymbol);
            this.loadAllData();
        },

        // Update timeframe filter
        updateTimeframe() {
            console.log("🔄 Updating timeframe to:", this.globalTimeframe);
            this.loadAllData();
        },

        // Refresh all data
        refreshAll() {
            console.log("🔄 Refreshing all data...");
            this.loadAllData();
        },

        // Build API URL with test environment
        buildAPIUrl(endpoint) {
            // Use test environment that has working data
            const testBase = "https://test.dragonfortune.ai";
            return `${testBase}${endpoint}`;
        },

        // Enhanced format timestamp
        formatTimestamp(timestamp) {
            if (!timestamp) return "N/A";

            try {
                // Handle different timestamp formats
                let date;
                if (typeof timestamp === 'string') {
                    // Handle GMT format like "Mon, 06 Oct 2025 15:12:48 GMT"
                    date = new Date(timestamp);
                } else {
                    date = new Date(timestamp);
                }

                if (isNaN(date.getTime())) {
                    return "Invalid Date";
                }

                return date.toLocaleTimeString("en-US", {
                    hour: "2-digit",
                    minute: "2-digit",
                    hour12: false,
                });
            } catch (e) {
                console.warn("Timestamp parsing error:", e, timestamp);
                return "Invalid Date";
            }
        },

        // Enhanced format number with better fallback handling
        formatNumber(num, decimals = 2) {
            if (num === null || num === undefined || isNaN(num)) {
                return "N/A";
            }
            const numValue = parseFloat(num);
            if (numValue === 0) {
                return "0.00";
            }
            return numValue.toLocaleString(undefined, {
                minimumFractionDigits: 0,
                maximumFractionDigits: decimals,
            });
        },

        // Format percentage
        formatPercent(num) {
            if (!num && num !== 0) return "N/A";
            return parseFloat(num).toFixed(2) + "%";
        },

        // Get insight based on buy/sell ratio
        getBuySellInsight() {
            const ratio = this.metrics.buySellRatio;

            // Handle no data or invalid ratio - show balanced market instead of waiting
            if (!ratio || ratio === 0 || isNaN(ratio)) {
                return {
                    icon: "⚖️",
                    title: "Balanced Market",
                    message: "No clear buying or selling dominance detected. Market appears balanced.",
                    class: "alert-info",
                };
            }

            if (ratio > 1.5) {
                return {
                    icon: "🟢",
                    title: "Strong Buying Pressure",
                    message: `Buy/Sell ratio at ${ratio.toFixed(
                        2
                    )}:1 indicates strong buying dominance. Market showing bullish sentiment with ${this.formatNumber(
                        this.metrics.buyTrades
                    )} buy trades vs ${this.formatNumber(
                        this.metrics.sellTrades
                    )} sell trades.`,
                    class: "alert-success",
                };
            } else if (ratio > 1.1) {
                return {
                    icon: "🔵",
                    title: "Moderate Buying Activity",
                    message: `Buy/Sell ratio at ${ratio.toFixed(
                        2
                    )}:1 shows moderate buying interest. Market sentiment is cautiously bullish.`,
                    class: "alert-info",
                };
            } else if (ratio > 0.9) {
                return {
                    icon: "⚪",
                    title: "Balanced Market",
                    message: `Buy/Sell ratio at ${ratio.toFixed(
                        2
                    )}:1 indicates balanced market with no strong directional bias.`,
                    class: "alert-secondary",
                };
            } else if (ratio > 0.6) {
                return {
                    icon: "🟠",
                    title: "Moderate Selling Pressure",
                    message: `Buy/Sell ratio at ${ratio.toFixed(
                        2
                    )}:1 shows moderate selling interest. Market sentiment is cautiously bearish.`,
                    class: "alert-warning",
                };
            } else {
                return {
                    icon: "🔴",
                    title: "Strong Selling Pressure",
                    message: `Buy/Sell ratio at ${ratio.toFixed(
                        2
                    )}:1 indicates heavy selling dominance. Market showing bearish sentiment.`,
                    class: "alert-danger",
                };
            }
        },

        // Get volume insight
        getVolumeInsight() {
            // Handle empty data - show normal volume instead of waiting
            if (
                this.volumeStatsData.length === 0 ||
                !this.metrics.totalVolume
            ) {
                return {
                    icon: "📊",
                    title: "Normal Volume",
                    message: "Volume data is being processed. Current market activity appears normal.",
                    class: "alert-info",
                };
            }

            const avgVol =
                this.volumeStatsData.reduce(
                    (sum, d) => sum + (d.total_volume || 0),
                    0
                ) / this.volumeStatsData.length;
            const latestVol = this.metrics.totalVolume;

            if (latestVol > avgVol * 1.5) {
                return {
                    icon: "⚡",
                    title: "High Volume Spike",
                    message: `Current volume (${this.formatNumber(
                        latestVol
                    )}) is ${((latestVol / avgVol - 1) * 100).toFixed(
                        0
                    )}% above average. Indicates strong market participation and potential volatility.`,
                    class: "alert-warning",
                };
            } else if (latestVol > avgVol * 1.2) {
                return {
                    icon: "📈",
                    title: "Above Average Volume",
                    message: `Volume is ${(
                        (latestVol / avgVol - 1) *
                        100
                    ).toFixed(
                        0
                    )}% above average, suggesting increased market interest.`,
                    class: "alert-info",
                };
            } else if (latestVol < avgVol * 0.7) {
                return {
                    icon: "📉",
                    title: "Low Volume Period",
                    message: `Volume is ${(
                        (1 - latestVol / avgVol) *
                        100
                    ).toFixed(
                        0
                    )}% below average, indicating reduced market activity.`,
                    class: "alert-secondary",
                };
            } else {
                return {
                    icon: "💡",
                    title: "Normal Volume",
                    message: `Volume levels are within normal range, showing steady market activity.`,
                    class: "alert-info",
                };
            }
        },
    };
}

console.log("✅ Volume & Trade Stats Controller loaded");
