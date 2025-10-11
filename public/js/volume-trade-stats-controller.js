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
        globalSymbol: "BTCUSDT",
        globalExchange: "binance",
        globalTimeframe: "5m",
        globalLimit: 1000,
        globalLoading: false,

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
            console.log("🚀 Volume & Trade Stats Dashboard initialized");
            this.loadAllData();

            // Auto refresh every 60 seconds
            setInterval(() => {
                if (!this.globalLoading) {
                    this.loadAllData();
                }
            }, 60000);
        },

        // Load all data from APIs
        async loadAllData() {
            this.globalLoading = true;
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
                console.log("✅ All data loaded successfully");
            } catch (error) {
                console.error("❌ Error loading data:", error);
            } finally {
                this.globalLoading = false;
            }
        },

        // API: Load Trade Stats
        async loadTradeStats() {
            try {
                const params = new URLSearchParams({
                    symbol: this.globalSymbol,
                    timeframe: this.globalTimeframe,
                    limit: this.globalLimit,
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
                // NOTE: Volume Profile endpoint does NOT support timeframe parameter
                const params = new URLSearchParams({
                    symbol: this.globalSymbol,
                    limit: this.globalLimit,
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
                    this.volumeProfileData = data;
                    console.log("✅ Volume Profile loaded");
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
                    symbol: this.globalSymbol,
                    limit: 2000,
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
                    symbol: this.globalSymbol,
                    timeframe: this.globalTimeframe,
                    limit: this.globalLimit,
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
            // From volume profile
            if (this.volumeProfileData) {
                this.metrics.totalTrades =
                    this.volumeProfileData.total_trades || 0;
                this.metrics.avgTradeSize =
                    this.volumeProfileData.avg_trade_size || 0;
                this.metrics.maxTradeSize =
                    this.volumeProfileData.max_trade_size || 0;
                this.metrics.buyTrades =
                    this.volumeProfileData.total_buy_trades || 0;
                this.metrics.sellTrades =
                    this.volumeProfileData.total_sell_trades || 0;
                this.metrics.buySellRatio =
                    this.volumeProfileData.buy_sell_ratio || 0;
            }

            // From volume stats (latest)
            if (this.volumeStatsData.length > 0) {
                const latest =
                    this.volumeStatsData[this.volumeStatsData.length - 1];
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
                this.formatTimestamp(d.timestamp)
            );
            const totalTrades = this.tradeStatsData.map((d) => d.total_trades);
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
                                    return `${
                                        context.label
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
                this.formatTimestamp(d.timestamp)
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

        // Build API URL with base URL from meta tag
        buildAPIUrl(endpoint) {
            const baseMeta = document.querySelector(
                'meta[name="api-base-url"]'
            );
            const configuredBase = (baseMeta?.content || "").trim();

            if (configuredBase) {
                const normalizedBase = configuredBase.endsWith("/")
                    ? configuredBase.slice(0, -1)
                    : configuredBase;
                return `${normalizedBase}${endpoint}`;
            }

            return endpoint;
        },

        // Format timestamp
        formatTimestamp(timestamp) {
            if (!timestamp) return "N/A";

            try {
                const date = new Date(timestamp);
                return date.toLocaleTimeString("en-US", {
                    hour: "2-digit",
                    minute: "2-digit",
                    hour12: false,
                });
            } catch (e) {
                return "Invalid";
            }
        },

        // Format number with commas
        formatNumber(num) {
            if (!num && num !== 0) return "N/A";
            return parseFloat(num).toLocaleString(undefined, {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2,
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

            // Handle no data or invalid ratio
            if (!ratio || ratio === 0 || isNaN(ratio)) {
                return {
                    icon: "⏳",
                    title: "Waiting for Data",
                    message:
                        "Trade statistics are loading or unavailable. Please wait or try refreshing.",
                    class: "alert-secondary",
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
            // Handle empty data
            if (
                this.volumeStatsData.length === 0 ||
                !this.metrics.totalVolume
            ) {
                return {
                    icon: "⏳",
                    title: "Waiting for Data",
                    message:
                        "Volume statistics are loading or unavailable. Please wait or try refreshing.",
                    class: "alert-secondary",
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
