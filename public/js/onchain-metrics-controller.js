/**
 * On-Chain Metrics Dashboard Controller
 *
 * Global controller untuk mengoordinasikan semua on-chain metrics
 *
 * Think like a trader:
 * - MVRV Z-Score > 7 = extreme overvaluation (sell signal)
 * - MVRV Z-Score < 0 = extreme undervaluation (buy signal)
 * - Exchange outflow = accumulation (bullish)
 * - Exchange inflow = distribution (bearish)
 * - Puell Multiple > 4 = miners selling pressure
 * - LTH supply increasing = strong holder conviction
 *
 * Build like an engineer:
 * - Modular data fetching dengan proper error handling
 * - Efficient Chart.js rendering dengan destroy/recreate pattern
 * - Responsive design dengan dynamic chart sizing
 *
 * Visualize like a designer:
 * - Color coded untuk quick insights (red=bearish, green=bullish)
 * - Real-time updates tanpa page refresh
 * - Smooth animations dan transitions
 */

function onchainMetricsController() {
    return {
        // API Base URL
        apiBaseUrl:
            document.querySelector('meta[name="api-base-url"]')?.content ||
            "",

        // Global loading state
        loading: false,

        // Individual loading states
        loadingStates: {
            mvrv: false,
            flows: false,
            supply: false,
            hodl: false,
            chainHealth: false,
            miners: false,
            whales: false,
            realizedCap: false,
            cqMPI: false,
            cqMinerReserve: false,
            cqETHGas: false,
            cqETHStaking: false,
            cqPrice: false,
        },

        // Global Filters
        selectedAsset: "ALL",
        selectedExchange: "ALL",
        selectedDateRange: "365d",

        // Quick stats
        metrics: {
            mvrvZScore: null,
            mvrvZScoreStatus: "Loading...",
            btcNetflow: null,
            btcNetflowStatus: "Loading...",
            puellMultiple: null,
            puellMultipleStatus: "Loading...",
            lthSthRatio: null,
            lthSthRatioStatus: "Loading...",
        },

        // Chart instances
        charts: {
            mvrv: null,
            exchangeFlow: null,
            supply: null,
            hodl: null,
            chainHealth: null,
            miner: null,
            whale: null,
            realizedCap: null,
            cqMPI: null,
            cqMinerReserve: null,
            cqETHGas: null,
            cqETHStaking: null,
            cqPrice: null,
        },

        // Data storage
        exchangeSummary: [],
        whaleSummary: [],
        
        // CryptoQuant data
        cryptoquant: {
            mpi: [],
            minerReserve: [],
            ethGas: [],
            ethStaking: [],
            priceOHLCV: [],
        },

        // Insights
        insights: {
            exchangeFlow: "Loading exchange flow insights...",
            lthSthSupply: "Loading supply distribution insights...",
            hodlWaves: "Loading HODL waves insights...",
            chainHealth: "Loading chain health insights...",
            minerMetrics: "Loading miner metrics insights...",
            whaleHoldings: "Loading whale holdings insights...",
            realizedCap: "Loading realized cap insights...",
        },

        // Current selections
        chainHealthMetric: "RESERVE_RISK",
        whaleCohort: "",

        // Miner metrics display
        minerMetrics: {
            reserve: null,
            puell: null,
            hashRate: null,
        },

        /**
         * Initialize dashboard
         */
        init() {
            console.log("⛓️ On-Chain Metrics Dashboard initialized");
            console.log("🌐 API Base URL:", this.apiBaseUrl);

            // Load all data
            this.refreshAll();
        },

        /**
         * Destroy all charts
         */
        destroyAllCharts() {
            Object.keys(this.charts).forEach((key) => {
                if (this.charts[key]) {
                    this.charts[key].destroy();
                    this.charts[key] = null;
                }
            });
        },

        /**
         * Refresh all data
         */
        async refreshAll() {
            console.log("🔄 Refreshing all on-chain metrics...");
            this.loading = true;

            try {
                // Load all data in parallel
                await Promise.all([
                    this.loadMVRVData(),
                    this.loadExchangeFlows(),
                    this.loadExchangeSummary(),
                    this.loadSupplyDistribution(),
                    this.loadHodlWaves(),
                    this.loadChainHealth(),
                    this.loadMinerMetrics(),
                    this.loadWhaleHoldings(),
                    this.loadWhaleSummary(),
                    this.loadRealizedCap(),
                    this.loadCryptoQuantMPI(),
                    this.loadCryptoQuantMinerReserve(),
                    this.loadCryptoQuantETHGas(),
                    this.loadCryptoQuantETHStaking(),
                    this.loadCryptoQuantPrice(),
                ]);

                console.log("✅ All data loaded successfully");
            } catch (error) {
                console.error("❌ Error refreshing data:", error);
            } finally {
                this.loading = false;
            }
        },

        /**
         * Get limit value from date range
         */
        getLimit() {
            const rangeMap = {
                "30d": 30,
                "90d": 90,
                "180d": 180,
                "365d": 365,
            };
            return rangeMap[this.selectedDateRange] || 365;
        },

        /**
         * Get asset filter for API calls
         */
        getAssetFilter() {
            return this.selectedAsset === "ALL" ? "" : this.selectedAsset;
        },

        /**
         * Get exchange filter for API calls
         */
        getExchangeFilter() {
            return this.selectedExchange === "ALL" ? "" : this.selectedExchange;
        },

        /**
         * Format value for display
         */
        formatValue(value, decimals = 2, suffix = "") {
            if (value === null || value === undefined) return "--";
            return (
                Number(value).toFixed(decimals) + (suffix ? ` ${suffix}` : "")
            );
        },

        /**
         * Format number for display
         */
        formatNumber(value) {
            if (value === null || value === undefined) return "--";
            const num = Number(value);
            if (num >= 1e9) return (num / 1e9).toFixed(2) + "B";
            if (num >= 1e6) return (num / 1e6).toFixed(2) + "M";
            if (num >= 1e3) return (num / 1e3).toFixed(2) + "K";
            return num.toFixed(2);
        },

        /**
         * Get MVRV Z-Score class for styling
         */
        getMVRVZScoreClass() {
            if (!this.metrics.mvrvZScore) return "text-muted";
            const value = Number(this.metrics.mvrvZScore);
            if (value > 7) return "text-danger";
            if (value > 3.7) return "text-warning";
            if (value < 0) return "text-success";
            return "text-info";
        },

        /**
         * Get netflow class for styling
         */
        getNetflowClass(value) {
            if (!value) return "text-muted";
            const numValue = Number(value);
            if (numValue > 0) return "text-danger"; // Inflow (bearish)
            if (numValue < 0) return "text-success"; // Outflow (bullish)
            return "text-muted";
        },

        /**
         * Get Puell Multiple class for styling
         */
        getPuellMultipleClass() {
            if (!this.metrics.puellMultiple) return "text-muted";
            const value = Number(this.metrics.puellMultiple);
            if (value > 4) return "text-danger";
            if (value > 2) return "text-warning";
            return "text-success";
        },

        /**
         * Get LTH/STH Ratio class for styling
         */
        getLthSthRatioClass() {
            if (!this.metrics.lthSthRatio) return "text-muted";
            const value = Number(this.metrics.lthSthRatio);
            if (value > 4) return "text-success"; // High LTH dominance
            if (value > 2) return "text-info";
            return "text-warning";
        },

        /**
         * Get Z-Score color class for progress bar
         */
        getZScoreColorClass(value) {
            if (!value) return "bg-secondary";
            const numValue = Number(value);
            if (numValue > 7) return "bg-danger";
            if (numValue > 3.7) return "bg-warning";
            if (numValue < 0) return "bg-success";
            return "bg-info";
        },

        /**
         * Get Z-Score progress percentage
         */
        getZScoreProgress(value) {
            if (!value) return 0;
            const numValue = Number(value);
            // Map Z-Score to 0-100% progress
            // Z-Score range: -2 to 10, map to 0-100%
            const minZ = -2;
            const maxZ = 10;
            const clampedValue = Math.max(minZ, Math.min(maxZ, numValue));
            return ((clampedValue - minZ) / (maxZ - minZ)) * 100;
        },

        /**
         * Get Z-Score label
         */
        getZScoreLabel(value) {
            if (!value) return "No Data";
            const numValue = Number(value);
            if (numValue > 7) return "Extreme Overvalued";
            if (numValue > 3.7) return "Overvalued";
            if (numValue < 0) return "Undervalued";
            return "Fair Value";
        },

        /**
         * Load MVRV & Z-Score data
         */
        async loadMVRVData() {
            this.loadingStates.mvrv = true;

            try {
                const params = new URLSearchParams({
                    limit: this.getLimit(),
                });

                console.log(
                    `📊 Loading MVRV data: ${this.apiBaseUrl}/api/onchain/valuation/mvrv?${params}`
                );

                const response = await fetch(
                    `${this.apiBaseUrl}/api/onchain/valuation/mvrv?${params}`
                );

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log(`📊 MVRV data loaded:`, data);

                if (data.data && Array.isArray(data.data)) {
                    // Separate MVRV_Z and REALIZED_PRICE
                    const mvrvZData = data.data.filter(
                        (d) => d.metric === "MVRV_Z"
                    );
                    const realizedPriceData = data.data.filter(
                        (d) => d.metric === "REALIZED_PRICE"
                    );

                    // Update metrics
                    if (mvrvZData.length > 0) {
                        const latest = mvrvZData[0];
                        this.metrics.mvrvZScore = latest.value;
                        this.metrics.mvrvZScoreStatus = "Updated";
                    } else {
                        this.metrics.mvrvZScore = null;
                        this.metrics.mvrvZScoreStatus = "No data";
                    }

                    // Render chart
                    this.renderMVRVChart(mvrvZData, realizedPriceData);
                } else {
                    console.warn("No MVRV data available");
                    this.renderMVRVChart([], []);
                }
            } catch (error) {
                console.error("Error loading MVRV data:", error);
                this.renderMVRVChart([], []);
            } finally {
                this.loadingStates.mvrv = false;
            }
        },

        /**
         * Load Exchange Flows
         */
        async loadExchangeFlows() {
            this.loadingStates.flows = true;

            try {
                const params = new URLSearchParams({
                    limit: this.getLimit(),
                });

                const assetFilter = this.getAssetFilter();
                if (assetFilter) {
                    params.append("asset", assetFilter);
                }

                const exchangeFilter = this.getExchangeFilter();
                if (exchangeFilter) {
                    params.append("exchange", exchangeFilter);
                }

                console.log(
                    `📊 Loading Exchange Flows: ${this.apiBaseUrl}/api/onchain/flow/exchange-netflow?${params}`
                );

                const response = await fetch(
                    `${this.apiBaseUrl}/api/onchain/flow/exchange-netflow?${params}`
                );

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log(`📊 Exchange Flows loaded:`, data);

                if (data.data && Array.isArray(data.data)) {
                    // Calculate latest netflow for stats
                    const latestByExchange = {};
                    data.data.forEach((item) => {
                        if (!latestByExchange[item.exchange]) {
                            latestByExchange[item.exchange] = item;
                        }
                    });

                    const totalNetflow = Object.values(latestByExchange).reduce(
                        (sum, item) => sum + (item.netflow || 0),
                        0
                    );
                    this.metrics.btcNetflow = totalNetflow;
                    this.metrics.btcNetflowStatus = "Updated";

                    // Render chart
                    this.renderExchangeFlowChart(data.data);
                } else {
                    console.warn("No exchange flow data available");
                    this.renderExchangeFlowChart([]);
                }
            } catch (error) {
                console.error("Error loading exchange flows:", error);
                this.renderExchangeFlowChart([]);
            } finally {
                this.loadingStates.flows = false;
            }
        },

        /**
         * Load Exchange Summary
         */
        async loadExchangeSummary() {
            try {
                const params = new URLSearchParams({
                    limit: this.getLimit(),
                });

                const assetFilter = this.getAssetFilter();
                if (assetFilter) {
                    params.append("asset", assetFilter);
                }

                const exchangeFilter = this.getExchangeFilter();
                if (exchangeFilter) {
                    params.append("exchange", exchangeFilter);
                }

                const response = await fetch(
                    `${this.apiBaseUrl}/api/onchain/exchange/summary?${params}`
                );

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.data && Array.isArray(data.data)) {
                    this.exchangeSummary = data.data.slice(0, 10); // Top 10
                } else {
                    console.warn("No exchange summary data available");
                    this.exchangeSummary = [];
                }
            } catch (error) {
                console.error("Error loading exchange summary:", error);
                this.exchangeSummary = [];
            }
        },

        /**
         * Load Supply Distribution (LTH vs STH)
         */
        async loadSupplyDistribution() {
            this.loadingStates.supply = true;

            try {
                const params = new URLSearchParams({
                    limit: this.getLimit(),
                });

                console.log(
                    `📊 Loading Supply Distribution: ${this.apiBaseUrl}/api/onchain/supply/lth-sth?${params}`
                );

                const response = await fetch(
                    `${this.apiBaseUrl}/api/onchain/supply/lth-sth?${params}`
                );

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log(`📊 Supply Distribution loaded:`, data);

                if (data.data && Array.isArray(data.data)) {
                    // Update metrics
                    if (data.data.length > 0) {
                        const latest = data.data[0];
                        if (latest.lth_supply_btc && latest.sth_supply_btc) {
                            const ratio =
                                latest.lth_supply_btc / latest.sth_supply_btc;
                            this.metrics.lthSthRatio = ratio;
                            this.metrics.lthSthRatioStatus = "Updated";
                        }
                    } else {
                        this.metrics.lthSthRatio = null;
                        this.metrics.lthSthRatioStatus = "No data";
                    }

                    // Render chart
                    this.renderSupplyChart(data.data);
                } else {
                    console.warn("No supply distribution data available");
                    this.renderSupplyChart([]);
                }
            } catch (error) {
                console.error("Error loading supply distribution:", error);
                this.renderSupplyChart([]);
            } finally {
                this.loadingStates.supply = false;
            }
        },

        /**
         * Load HODL Waves
         */
        async loadHodlWaves() {
            this.loadingStates.hodl = true;

            try {
                const params = new URLSearchParams({
                    limit: this.getLimit(),
                });

                console.log(
                    `📊 Loading HODL Waves: ${this.apiBaseUrl}/api/onchain/supply/hodl-waves?${params}`
                );

                const response = await fetch(
                    `${this.apiBaseUrl}/api/onchain/supply/hodl-waves?${params}`
                );

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.data && Array.isArray(data.data)) {
                    this.renderHodlChart(data.data);
                } else {
                    console.warn("No HODL waves data available");
                    this.renderHodlChart([]);
                }
            } catch (error) {
                console.error("Error loading HODL waves:", error);
                this.renderHodlChart([]);
            } finally {
                this.loadingStates.hodl = false;
            }
        },

        /**
         * Load Chain Health Indicators
         */
        async loadChainHealth() {
            this.loadingStates.chainHealth = true;

            try {
                const params = new URLSearchParams({
                    limit: this.getLimit(),
                    metric: this.chainHealthMetric,
                });

                console.log(
                    `📊 Loading Chain Health: ${this.apiBaseUrl}/api/onchain/chain-health/reserve-risk?${params}`
                );

                const response = await fetch(
                    `${this.apiBaseUrl}/api/onchain/chain-health/reserve-risk?${params}`
                );

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.data && Array.isArray(data.data)) {
                    this.renderChainHealthChart(data.data);
                } else {
                    console.warn("No chain health data available");
                    this.renderChainHealthChart([]);
                }
            } catch (error) {
                console.error("Error loading chain health:", error);
                this.renderChainHealthChart([]);
            } finally {
                this.loadingStates.chainHealth = false;
            }
        },

        /**
         * Load Miner Metrics
         */
        async loadMinerMetrics() {
            this.loadingStates.miners = true;

            try {
                const params = new URLSearchParams({
                    limit: this.getLimit(),
                });

                console.log(
                    `📊 Loading Miner Metrics: ${this.apiBaseUrl}/api/onchain/mining/miner-netflow?${params}`
                );

                const response = await fetch(
                    `${this.apiBaseUrl}/api/onchain/mining/miner-netflow?${params}`
                );

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log(`📊 Miner Metrics loaded:`, data);

                if (data.data && Array.isArray(data.data)) {
                    // Update metrics
                    if (data.data.length > 0) {
                        const latest = data.data[0];
                        this.metrics.puellMultiple = latest.puell_multiple;
                        this.metrics.puellMultipleStatus = "Updated";

                        this.minerMetrics.reserve = latest.miner_reserve_btc
                            ? this.formatNumber(latest.miner_reserve_btc)
                            : "--";
                        this.minerMetrics.puell = latest.puell_multiple
                            ? latest.puell_multiple.toFixed(3)
                            : "--";
                        this.minerMetrics.hashRate = latest.hash_rate
                            ? latest.hash_rate.toFixed(2)
                            : "--";
                    } else {
                        this.metrics.puellMultiple = null;
                        this.metrics.puellMultipleStatus = "No data";
                    }

                    // Render chart
                    this.renderMinerChart(data.data);
                } else {
                    console.warn("No miner metrics data available");
                    this.renderMinerChart([]);
                }
            } catch (error) {
                console.error("Error loading miner metrics:", error);
                this.renderMinerChart([]);
            } finally {
                this.loadingStates.miners = false;
            }
        },

        /**
         * Load Whale Holdings
         */
        async loadWhaleHoldings() {
            this.loadingStates.whales = true;

            try {
                const params = new URLSearchParams({
                    limit: this.getLimit(),
                });

                if (this.whaleCohort) {
                    params.append("cohort", this.whaleCohort);
                }

                console.log(
                    `📊 Loading Whale Holdings: ${this.apiBaseUrl}/api/onchain/whale/holdings?${params}`
                );

                const response = await fetch(
                    `${this.apiBaseUrl}/api/onchain/whale/holdings?${params}`
                );

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.data && Array.isArray(data.data)) {
                    this.renderWhaleChart(data.data);
                } else {
                    console.warn("No whale holdings data available");
                    this.renderWhaleChart([]);
                }
            } catch (error) {
                console.error("Error loading whale holdings:", error);
                this.renderWhaleChart([]);
            } finally {
                this.loadingStates.whales = false;
            }
        },

        /**
         * Load Whale Summary
         */
        async loadWhaleSummary() {
            try {
                const params = new URLSearchParams({
                    limit: this.getLimit(),
                });

                console.log(
                    `📊 Loading Whale Summary: ${this.apiBaseUrl}/api/onchain/whale/transactions?${params}`
                );

                const response = await fetch(
                    `${this.apiBaseUrl}/api/onchain/whale/transactions?${params}`
                );

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.data && Array.isArray(data.data)) {
                    this.whaleSummary = data.data;
                } else {
                    console.warn("No whale summary data available");
                    this.whaleSummary = [];
                }
            } catch (error) {
                console.error("Error loading whale summary:", error);
                this.whaleSummary = [];
            }
        },

        /**
         * Load Realized Cap
         */
        async loadRealizedCap() {
            this.loadingStates.realizedCap = true;

            try {
                const params = new URLSearchParams({
                    limit: this.getLimit(),
                });

                console.log(
                    `📊 Loading Realized Cap: ${this.apiBaseUrl}/api/onchain/valuation/realized-cap?${params}`
                );

                const response = await fetch(
                    `${this.apiBaseUrl}/api/onchain/valuation/realized-cap?${params}`
                );

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.data && Array.isArray(data.data)) {
                    this.renderRealizedCapChart(data.data);
                } else {
                    console.warn("No realized cap data available");
                    this.renderRealizedCapChart([]);
                }
            } catch (error) {
                console.error("Error loading realized cap:", error);
                this.renderRealizedCapChart([]);
            } finally {
                this.loadingStates.realizedCap = false;
            }
        },

        // ==================== Chart Rendering Functions ====================

        /**
         * Render MVRV Chart
         */
        renderMVRVChart(mvrvZData, realizedPriceData) {
            const canvas = this.$refs.mvrvChart;
            if (!canvas) return;

            // Destroy existing chart
            if (this.charts.mvrv) {
                this.charts.mvrv.destroy();
                this.charts.mvrv = null;
            }

            // Sort data by date
            mvrvZData.sort((a, b) => new Date(a.date) - new Date(b.date));
            realizedPriceData.sort(
                (a, b) => new Date(a.date) - new Date(b.date)
            );

            // Generate labels for x-axis
            const labels = mvrvZData.map((d, index) => index);

            const ctx = canvas.getContext("2d");
            this.charts.mvrv = new Chart(ctx, {
                type: "line",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "MVRV Z-Score",
                            data: mvrvZData.map((d) => d.value),
                            borderColor: "#3b82f6",
                            backgroundColor: "rgba(59, 130, 246, 0.1)",
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            yAxisID: "y",
                        },
                        {
                            label: "Realized Price",
                            data: realizedPriceData.map((d) => d.value),
                            borderColor: "#8b5cf6",
                            backgroundColor: "rgba(139, 92, 246, 0.1)",
                            borderWidth: 2,
                            tension: 0.4,
                            fill: false,
                            yAxisID: "y1",
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
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || "";
                                    if (label) {
                                        label += ": ";
                                    }
                                    if (context.parsed.y !== null) {
                                        label +=
                                            context.dataset.yAxisID === "y1"
                                                ? "$" +
                                                  context.parsed.y.toLocaleString()
                                                : context.parsed.y.toFixed(2);
                                    }
                                    return label;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            type: "linear",
                            title: {
                                display: true,
                                text: "Days",
                            },
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            type: "linear",
                            display: true,
                            position: "left",
                            title: {
                                display: true,
                                text: "Z-Score",
                            },
                            grid: {
                                color: "rgba(148, 163, 184, 0.1)",
                            },
                        },
                        y1: {
                            type: "linear",
                            display: true,
                            position: "right",
                            title: {
                                display: true,
                                text: "Realized Price ($)",
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        },
                    },
                },
            });
        },

        /**
         * Render Exchange Flow Chart
         */
        renderExchangeFlowChart(data) {
            const canvas = this.$refs.exchangeFlowChart;
            if (!canvas) return;

            // Destroy existing chart
            if (this.charts.exchangeFlow) {
                this.charts.exchangeFlow.destroy();
                this.charts.exchangeFlow = null;
            }

            // Group by exchange
            const byExchange = {};
            data.forEach((item) => {
                if (!byExchange[item.exchange]) {
                    byExchange[item.exchange] = [];
                }
                byExchange[item.exchange].push(item);
            });

            // Sort each exchange by date
            Object.keys(byExchange).forEach((exchange) => {
                byExchange[exchange].sort(
                    (a, b) => new Date(a.date) - new Date(b.date)
                );
            });

            // Generate labels for x-axis
            const labels =
                Object.keys(byExchange).length > 0
                    ? byExchange[Object.keys(byExchange)[0]].map(
                          (d, index) => index
                      )
                    : [];

            // Create datasets
            const colors = {
                binance: "#f59e0b",
                coinbase: "#3b82f6",
                okx: "#8b5cf6",
            };

            const datasets = Object.keys(byExchange).map((exchange) => ({
                label: exchange.charAt(0).toUpperCase() + exchange.slice(1),
                data: byExchange[exchange].map((d) => d.netflow),
                borderColor: colors[exchange] || "#6b7280",
                backgroundColor: colors[exchange]
                    ? colors[exchange] + "33"
                    : "#6b728033",
                borderWidth: 2,
                tension: 0.4,
                fill: false,
            }));

            const ctx = canvas.getContext("2d");
            this.charts.exchangeFlow = new Chart(ctx, {
                type: "line",
                data: {
                    labels: labels,
                    datasets: datasets,
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
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || "";
                                    if (label) {
                                        label += ": ";
                                    }
                                    if (context.parsed.y !== null) {
                                        label +=
                                            context.parsed.y.toLocaleString();
                                    }
                                    return label;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            type: "linear",
                            title: {
                                display: true,
                                text: "Days",
                            },
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            grid: {
                                color: "rgba(148, 163, 184, 0.1)",
                            },
                            ticks: {
                                callback: function (value) {
                                    return value.toLocaleString();
                                },
                            },
                        },
                    },
                },
            });
        },

        /**
         * Render Supply Chart (LTH vs STH)
         */
        renderSupplyChart(data) {
            const canvas = this.$refs.supplyChart;
            if (!canvas) return;

            // Destroy existing chart
            if (this.charts.supply) {
                this.charts.supply.destroy();
                this.charts.supply = null;
            }

            // Sort by date
            data.sort((a, b) => new Date(a.date) - new Date(b.date));

            // Generate labels for x-axis
            const labels = data.map((d, index) => index);

            const ctx = canvas.getContext("2d");
            this.charts.supply = new Chart(ctx, {
                type: "line",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "LTH Supply",
                            data: data.map((d) => d.lth_supply_btc),
                            borderColor: "#22c55e",
                            backgroundColor: "rgba(34, 197, 94, 0.1)",
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                        },
                        {
                            label: "STH Supply",
                            data: data.map((d) => d.sth_supply_btc),
                            borderColor: "#ef4444",
                            backgroundColor: "rgba(239, 68, 68, 0.1)",
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
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
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || "";
                                    if (label) {
                                        label += ": ";
                                    }
                                    if (context.parsed.y !== null) {
                                        label +=
                                            context.parsed.y.toLocaleString() +
                                            " BTC";
                                    }
                                    return label;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            type: "linear",
                            title: {
                                display: true,
                                text: "Days",
                            },
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            grid: {
                                color: "rgba(148, 163, 184, 0.1)",
                            },
                            ticks: {
                                callback: function (value) {
                                    return (value / 1000000).toFixed(1) + "M";
                                },
                            },
                        },
                    },
                },
            });
        },

        /**
         * Render HODL Waves Chart
         */
        renderHodlChart(data) {
            const canvas = this.$refs.hodlChart;
            if (!canvas) return;

            // Destroy existing chart
            if (this.charts.hodl) {
                this.charts.hodl.destroy();
                this.charts.hodl = null;
            }

            // Group by cohort
            const byCohort = {};
            data.forEach((item) => {
                if (!byCohort[item.cohort_age_band]) {
                    byCohort[item.cohort_age_band] = [];
                }
                byCohort[item.cohort_age_band].push(item);
            });

            // Sort each cohort by date
            Object.keys(byCohort).forEach((cohort) => {
                byCohort[cohort].sort(
                    (a, b) => new Date(a.date) - new Date(b.date)
                );
            });

            // Generate labels for x-axis
            const labels =
                Object.keys(byCohort).length > 0
                    ? byCohort[Object.keys(byCohort)[0]].map(
                          (d, index) => index
                      )
                    : [];

            // Define cohort colors
            const cohortColors = {
                "<1w": "#ef4444",
                "1w-1m": "#f59e0b",
                "1m-3m": "#eab308",
                "3m-6m": "#84cc16",
                "6m-1y": "#22c55e",
                "1y-2y": "#10b981",
                ">2y": "#059669",
            };

            // Create datasets
            const datasets = Object.keys(byCohort).map((cohort) => ({
                label: cohort,
                data: byCohort[cohort].map((d) => d.percent_supply),
                borderColor: cohortColors[cohort] || "#6b7280",
                backgroundColor: cohortColors[cohort]
                    ? cohortColors[cohort] + "33"
                    : "#6b728033",
                borderWidth: 2,
                tension: 0.4,
                fill: true,
            }));

            const ctx = canvas.getContext("2d");
            this.charts.hodl = new Chart(ctx, {
                type: "line",
                data: {
                    labels: labels,
                    datasets: datasets,
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
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || "";
                                    if (label) {
                                        label += ": ";
                                    }
                                    if (context.parsed.y !== null) {
                                        label +=
                                            context.parsed.y.toFixed(2) + "%";
                                    }
                                    return label;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            type: "linear",
                            title: {
                                display: true,
                                text: "Days",
                            },
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            stacked: false,
                            grid: {
                                color: "rgba(148, 163, 184, 0.1)",
                            },
                            ticks: {
                                callback: function (value) {
                                    return value.toFixed(1) + "%";
                                },
                            },
                        },
                    },
                },
            });
        },

        /**
         * Render Chain Health Chart
         */
        renderChainHealthChart(data) {
            const canvas = this.$refs.chainHealthChart;
            if (!canvas) return;

            // Destroy existing chart
            if (this.charts.chainHealth) {
                this.charts.chainHealth.destroy();
                this.charts.chainHealth = null;
            }

            // Sort by date
            data.sort((a, b) => new Date(a.date) - new Date(b.date));

            // Generate labels for x-axis
            const labels = data.map((d, index) => index);

            const ctx = canvas.getContext("2d");
            this.charts.chainHealth = new Chart(ctx, {
                type: "line",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: this.chainHealthMetric.replace("_", " "),
                            data: data.map((d) => d.value),
                            borderColor: "#8b5cf6",
                            backgroundColor: "rgba(139, 92, 246, 0.1)",
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
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
                    },
                    scales: {
                        x: {
                            type: "linear",
                            title: {
                                display: true,
                                text: "Days",
                            },
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            grid: {
                                color: "rgba(148, 163, 184, 0.1)",
                            },
                        },
                    },
                },
            });
        },

        /**
         * Render Miner Chart
         */
        renderMinerChart(data) {
            const canvas = this.$refs.minerChart;
            if (!canvas) return;

            // Destroy existing chart
            if (this.charts.miner) {
                this.charts.miner.destroy();
                this.charts.miner = null;
            }

            // Sort by date
            data.sort((a, b) => new Date(a.date) - new Date(b.date));

            // Generate labels for x-axis
            const labels = data.map((d, index) => index);

            const ctx = canvas.getContext("2d");
            this.charts.miner = new Chart(ctx, {
                type: "line",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "Miner Reserve (BTC)",
                            data: data.map((d) => d.miner_reserve_btc),
                            borderColor: "#3b82f6",
                            backgroundColor: "rgba(59, 130, 246, 0.1)",
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            yAxisID: "y",
                        },
                        {
                            label: "Puell Multiple",
                            data: data.map((d) => d.puell_multiple),
                            borderColor: "#8b5cf6",
                            backgroundColor: "rgba(139, 92, 246, 0.1)",
                            borderWidth: 2,
                            tension: 0.4,
                            fill: false,
                            yAxisID: "y1",
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
                    },
                    scales: {
                        x: {
                            type: "linear",
                            title: {
                                display: true,
                                text: "Days",
                            },
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            type: "linear",
                            display: true,
                            position: "left",
                            title: {
                                display: true,
                                text: "Miner Reserve (BTC)",
                            },
                            grid: {
                                color: "rgba(148, 163, 184, 0.1)",
                            },
                            ticks: {
                                callback: function (value) {
                                    return (value / 1000000).toFixed(2) + "M";
                                },
                            },
                        },
                        y1: {
                            type: "linear",
                            display: true,
                            position: "right",
                            title: {
                                display: true,
                                text: "Puell Multiple",
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        },
                    },
                },
            });
        },

        /**
         * Render Whale Chart
         */
        renderWhaleChart(data) {
            const canvas = this.$refs.whaleChart;
            if (!canvas) return;

            // Destroy existing chart
            if (this.charts.whale) {
                this.charts.whale.destroy();
                this.charts.whale = null;
            }

            // Group by cohort
            const byCohort = {};
            data.forEach((item) => {
                if (!byCohort[item.cohort]) {
                    byCohort[item.cohort] = [];
                }
                byCohort[item.cohort].push(item);
            });

            // Sort each cohort by date
            Object.keys(byCohort).forEach((cohort) => {
                byCohort[cohort].sort(
                    (a, b) => new Date(a.date) - new Date(b.date)
                );
            });

            // Generate labels for x-axis
            const labels =
                Object.keys(byCohort).length > 0
                    ? byCohort[Object.keys(byCohort)[0]].map(
                          (d, index) => index
                      )
                    : [];

            // Define cohort colors
            const cohortColors = {
                "Exchange Treasuries": "#ef4444",
                "1k-10k BTC": "#f59e0b",
                "10k+ BTC": "#22c55e",
                "ETF Custodians": "#3b82f6",
            };

            // Create datasets
            const datasets = Object.keys(byCohort).map((cohort) => ({
                label: cohort,
                data: byCohort[cohort].map((d) => d.balance_btc),
                borderColor: cohortColors[cohort] || "#6b7280",
                backgroundColor: cohortColors[cohort]
                    ? cohortColors[cohort] + "33"
                    : "#6b728033",
                borderWidth: 2,
                tension: 0.4,
                fill: false,
            }));

            const ctx = canvas.getContext("2d");
            this.charts.whale = new Chart(ctx, {
                type: "line",
                data: {
                    labels: labels,
                    datasets: datasets,
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
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || "";
                                    if (label) {
                                        label += ": ";
                                    }
                                    if (context.parsed.y !== null) {
                                        label +=
                                            context.parsed.y.toLocaleString() +
                                            " BTC";
                                    }
                                    return label;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            type: "linear",
                            title: {
                                display: true,
                                text: "Days",
                            },
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            grid: {
                                color: "rgba(148, 163, 184, 0.1)",
                            },
                            ticks: {
                                callback: function (value) {
                                    return (value / 1000000).toFixed(2) + "M";
                                },
                            },
                        },
                    },
                },
            });
        },

        /**
         * Render Realized Cap Chart
         */
        renderRealizedCapChart(data) {
            const canvas = this.$refs.realizedCapChart;
            if (!canvas) return;

            // Destroy existing chart
            if (this.charts.realizedCap) {
                this.charts.realizedCap.destroy();
                this.charts.realizedCap = null;
            }

            // Separate by metric
            const realizedCapData = data.filter(
                (d) => d.metric === "REALIZED_CAP_USD"
            );
            const thermocapData = data.filter(
                (d) => d.metric === "THERMOCAP_USD"
            );

            // Sort by date
            realizedCapData.sort((a, b) => new Date(a.date) - new Date(b.date));
            thermocapData.sort((a, b) => new Date(a.date) - new Date(b.date));

            // Generate labels for x-axis
            const labels = realizedCapData.map((d, index) => index);

            const ctx = canvas.getContext("2d");
            this.charts.realizedCap = new Chart(ctx, {
                type: "line",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "Realized Cap",
                            data: realizedCapData.map((d) => d.value),
                            borderColor: "#22c55e",
                            backgroundColor: "rgba(34, 197, 94, 0.1)",
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                        },
                        {
                            label: "Thermocap",
                            data: thermocapData.map((d) => d.value),
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
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || "";
                                    if (label) {
                                        label += ": ";
                                    }
                                    if (context.parsed.y !== null) {
                                        label +=
                                            "$" +
                                            (context.parsed.y / 1e9).toFixed(
                                                2
                                            ) +
                                            "B";
                                    }
                                    return label;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            type: "linear",
                            title: {
                                display: true,
                                text: "Days",
                            },
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            grid: {
                                color: "rgba(148, 163, 184, 0.1)",
                            },
                            ticks: {
                                callback: function (value) {
                                    return "$" + (value / 1e9).toFixed(0) + "B";
                                },
                            },
                        },
                    },
                },
            });
        },

        // ==================== Helper Functions ====================

        /**
         * Format number with abbreviations
         */
        formatNumber(num) {
            if (num === null || num === undefined) return "--";

            const absNum = Math.abs(num);
            const sign = num < 0 ? "-" : "";

            if (absNum >= 1e9) {
                return sign + (absNum / 1e9).toFixed(2) + "B";
            } else if (absNum >= 1e6) {
                return sign + (absNum / 1e6).toFixed(2) + "M";
            } else if (absNum >= 1e3) {
                return sign + (absNum / 1e3).toFixed(2) + "K";
            } else {
                return sign + absNum.toFixed(2);
            }
        },

        /**
         * Get Z-Score class
         */
        getZScoreClass(value) {
            if (value === null || value === undefined || value === "--")
                return "text-muted";
            const num = parseFloat(value);
            if (num > 7) return "text-danger";
            if (num > 2) return "text-warning";
            if (num < 0) return "text-success";
            return "text-info";
        },

        /**
         * Get Z-Score label
         */
        getZScoreLabel(value) {
            if (value === null || value === undefined || value === "--")
                return "No data";
            const num = parseFloat(value);
            if (num > 7) return "Extreme Overvaluation";
            if (num > 2) return "Overvalued";
            if (num < 0) return "Undervalued";
            return "Normal Range";
        },

        /**
         * Get Z-Score color class for progress bar
         */
        getZScoreColorClass(value) {
            if (value === null || value === undefined || value === "--")
                return "bg-secondary";
            const num = parseFloat(value);
            if (num > 7) return "bg-danger";
            if (num > 2) return "bg-warning";
            if (num < 0) return "bg-success";
            return "bg-info";
        },

        /**
         * Get Z-Score progress percentage
         */
        getZScoreProgress(value) {
            if (value === null || value === undefined || value === "--")
                return 0;
            const num = parseFloat(value);
            // Map -2 to 10 range to 0-100%
            const progress = ((num + 2) / 12) * 100;
            return Math.max(0, Math.min(100, progress));
        },

        /**
         * Get netflow class
         */
        getNetflowClass(value) {
            if (value === null || value === undefined) return "text-muted";
            return value < 0 ? "text-success" : "text-danger";
        },

        /**
         * Get netflow label
         */
        getNetflowLabel(value) {
            if (value === null || value === undefined) return "No data";
            return value < 0 ? "📉 Outflow (Bullish)" : "📈 Inflow (Bearish)";
        },

        /**
         * Get Puell Multiple class
         */
        getPuellClass(value) {
            if (value === null || value === undefined || value === "--")
                return "text-muted";
            const num = parseFloat(value);
            if (num > 4) return "text-danger";
            if (num > 1) return "text-warning";
            if (num < 0.5) return "text-success";
            return "text-info";
        },

        /**
         * Get Puell Multiple label
         */
        getPuellLabel(value) {
            if (value === null || value === undefined || value === "--")
                return "No data";
            const num = parseFloat(value);
            if (num > 4) return "High Selling Pressure";
            if (num > 1) return "Moderate Pressure";
            if (num < 0.5) return "Low Pressure";
            return "Normal";
        },

        /**
         * Get LTH/STH class
         */
        getLthSthClass(value) {
            if (value === null || value === undefined) return "text-muted";
            const num = parseFloat(value);
            if (num > 5) return "text-success";
            if (num > 3) return "text-info";
            if (num < 2) return "text-warning";
            return "text-muted";
        },

        /**
         * Get LTH/STH label
         */
        getLthSthLabel(value) {
            if (value === null || value === undefined) return "No data";
            const num = parseFloat(value);
            if (num > 5) return "Strong Conviction";
            if (num > 3) return "Moderate Conviction";
            if (num < 2) return "Weak Conviction";
            return "Neutral";
        },

        /**
         * Load CryptoQuant MPI data
         */
        async loadCryptoQuantMPI() {
            this.loadingStates.cqMPI = true;
            try {
                const response = await fetch(
                    `${this.apiBaseUrl}/api/onchain/cq/miners-position-index?limit=30`
                );
                const result = await response.json();
                if (result.data && result.data.length > 0) {
                    this.cryptoquant.mpi = result.data;
                    this.renderCQMPIChart();
                    console.log("✅ CQ MPI loaded:", result.data.length);
                }
            } catch (error) {
                console.error("❌ Error loading CQ MPI:", error);
            } finally {
                this.loadingStates.cqMPI = false;
            }
        },

        /**
         * Load CryptoQuant Miner Reserve data
         */
        async loadCryptoQuantMinerReserve() {
            this.loadingStates.cqMinerReserve = true;
            try {
                const response = await fetch(
                    `${this.apiBaseUrl}/api/onchain/cq/miner-reserve?limit=30`
                );
                const result = await response.json();
                if (result.data && result.data.length > 0) {
                    this.cryptoquant.minerReserve = result.data;
                    this.renderCQMinerReserveChart();
                    console.log("✅ CQ Miner Reserve loaded:", result.data.length);
                }
            } catch (error) {
                console.error("❌ Error loading CQ Miner Reserve:", error);
            } finally {
                this.loadingStates.cqMinerReserve = false;
            }
        },

        /**
         * Load CryptoQuant ETH Gas Price data
         */
        async loadCryptoQuantETHGas() {
            this.loadingStates.cqETHGas = true;
            try {
                const response = await fetch(
                    `${this.apiBaseUrl}/api/onchain/cq/eth-gas-price?limit=30`
                );
                const result = await response.json();
                if (result.data && result.data.length > 0) {
                    this.cryptoquant.ethGas = result.data;
                    this.renderCQETHGasChart();
                    console.log("✅ CQ ETH Gas loaded:", result.data.length);
                }
            } catch (error) {
                console.error("❌ Error loading CQ ETH Gas:", error);
            } finally {
                this.loadingStates.cqETHGas = false;
            }
        },

        /**
         * Load CryptoQuant ETH Staking data
         */
        async loadCryptoQuantETHStaking() {
            this.loadingStates.cqETHStaking = true;
            try {
                const response = await fetch(
                    `${this.apiBaseUrl}/api/onchain/cq/eth-staking-total?limit=30`
                );
                const result = await response.json();
                if (result.data && result.data.length > 0) {
                    this.cryptoquant.ethStaking = result.data;
                    this.renderCQETHStakingChart();
                    console.log("✅ CQ ETH Staking loaded:", result.data.length);
                }
            } catch (error) {
                console.error("❌ Error loading CQ ETH Staking:", error);
            } finally {
                this.loadingStates.cqETHStaking = false;
            }
        },

        /**
         * Load CryptoQuant Price OHLCV data
         */
        async loadCryptoQuantPrice() {
            this.loadingStates.cqPrice = true;
            try {
                const response = await fetch(
                    `${this.apiBaseUrl}/api/onchain/cq/price-ohlcv?limit=30`
                );
                const result = await response.json();
                if (result.data && result.data.length > 0) {
                    this.cryptoquant.priceOHLCV = result.data;
                    this.renderCQPriceChart();
                    console.log("✅ CQ Price OHLCV loaded:", result.data.length);
                }
            } catch (error) {
                console.error("❌ Error loading CQ Price OHLCV:", error);
            } finally {
                this.loadingStates.cqPrice = false;
            }
        },

        /**
         * Render CryptoQuant MPI Chart
         */
        renderCQMPIChart() {
            if (this.charts.cqMPI) this.charts.cqMPI.destroy();
            const ctx = this.$refs.cqMPIChart;
            if (!ctx) return;

            this.charts.cqMPI = new Chart(ctx, {
                type: "line",
                data: {
                    labels: this.cryptoquant.mpi.map((d) => d.date).reverse(),
                    datasets: [
                        {
                            label: "MPI",
                            data: this.cryptoquant.mpi.map((d) => d.mpi).reverse(),
                            borderColor: "rgb(255, 159, 64)",
                            backgroundColor: "rgba(255, 159, 64, 0.1)",
                            fill: true,
                            tension: 0.4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => `MPI: ${context.parsed.y.toFixed(4)}`,
                            },
                        },
                    },
                    scales: {
                        y: { beginAtZero: false },
                    },
                },
            });
        },

        /**
         * Render CryptoQuant Miner Reserve Chart
         */
        renderCQMinerReserveChart() {
            if (this.charts.cqMinerReserve) this.charts.cqMinerReserve.destroy();
            const ctx = this.$refs.cqMinerReserveChart;
            if (!ctx) return;

            this.charts.cqMinerReserve = new Chart(ctx, {
                type: "line",
                data: {
                    labels: this.cryptoquant.minerReserve.map((d) => d.date).reverse(),
                    datasets: [
                        {
                            label: "Miner Reserve (BTC)",
                            data: this.cryptoquant.minerReserve.map((d) => d.value).reverse(),
                            borderColor: "rgb(75, 192, 192)",
                            backgroundColor: "rgba(75, 192, 192, 0.1)",
                            fill: true,
                            tension: 0.4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => `Reserve: ${context.parsed.y.toLocaleString()} BTC`,
                            },
                        },
                    },
                    scales: {
                        y: { beginAtZero: false },
                    },
                },
            });
        },

        /**
         * Render CryptoQuant ETH Gas Chart
         */
        renderCQETHGasChart() {
            if (this.charts.cqETHGas) this.charts.cqETHGas.destroy();
            const ctx = this.$refs.cqETHGasChart;
            if (!ctx) return;

            this.charts.cqETHGas = new Chart(ctx, {
                type: "line",
                data: {
                    labels: this.cryptoquant.ethGas.map((d) => new Date(d.timestamp).toLocaleDateString()).reverse(),
                    datasets: [
                        {
                            label: "ETH Gas Price (Gwei)",
                            data: this.cryptoquant.ethGas.map((d) => d.gas_price).reverse(),
                            borderColor: "rgb(153, 102, 255)",
                            backgroundColor: "rgba(153, 102, 255, 0.1)",
                            fill: true,
                            tension: 0.4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => `Gas: ${context.parsed.y.toFixed(2)} Gwei`,
                            },
                        },
                    },
                    scales: {
                        y: { beginAtZero: true },
                    },
                },
            });
        },

        /**
         * Render CryptoQuant ETH Staking Chart
         */
        renderCQETHStakingChart() {
            if (this.charts.cqETHStaking) this.charts.cqETHStaking.destroy();
            const ctx = this.$refs.cqETHStakingChart;
            if (!ctx) return;

            this.charts.cqETHStaking = new Chart(ctx, {
                type: "line",
                data: {
                    labels: this.cryptoquant.ethStaking.map((d) => d.date).reverse(),
                    datasets: [
                        {
                            label: "ETH Staking Total",
                            data: this.cryptoquant.ethStaking.map((d) => d.staking_total).reverse(),
                            borderColor: "rgb(54, 162, 235)",
                            backgroundColor: "rgba(54, 162, 235, 0.1)",
                            fill: true,
                            tension: 0.4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => `Staked: ${(context.parsed.y / 1e6).toFixed(2)}M ETH`,
                            },
                        },
                    },
                    scales: {
                        y: { beginAtZero: false },
                    },
                },
            });
        },

        /**
         * Render CryptoQuant Price Chart
         */
        renderCQPriceChart() {
            if (this.charts.cqPrice) this.charts.cqPrice.destroy();
            const ctx = this.$refs.cqPriceChart;
            if (!ctx) return;

            this.charts.cqPrice = new Chart(ctx, {
                type: "line",
                data: {
                    labels: this.cryptoquant.priceOHLCV.map((d) => d.date).reverse(),
                    datasets: [
                        {
                            label: "Close Price",
                            data: this.cryptoquant.priceOHLCV.map((d) => d.close).reverse(),
                            borderColor: "rgb(59, 130, 246)",
                            backgroundColor: "rgba(59, 130, 246, 0.1)",
                            fill: true,
                            tension: 0.4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => `$${context.parsed.y.toLocaleString()}`,
                            },
                        },
                    },
                    scales: {
                        y: { beginAtZero: false },
                    },
                },
            });
        },
    };
}
