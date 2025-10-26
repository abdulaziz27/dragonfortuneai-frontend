/**
 * Spot Trades & Large Orders Dashboard
 * Mirrors the live data flow used by Funding Rate & Open Interest pages,
 * but focused on spot microstructure data (trades + whale orders).
 */

const spotTradesRefreshEvent = "spot-trades-refresh";

function getSpotMicrostructureBaseUrl() {
    const overrideMeta = document.querySelector('meta[name="spot-microstructure-api"]');
    const globalMeta = document.querySelector('meta[name="api-base-url"]');
    const override = (overrideMeta?.content || "").trim();
    const configured = override || (globalMeta?.content || "").trim();

    if (configured) {
        const normalized = configured.endsWith("/")
            ? configured.slice(0, -1)
            : configured;
        return `${normalized}/api/spot-microstructure`;
    }

    return `${window.location.origin}/api/spot-microstructure`;
}

const API_BASE_URL = getSpotMicrostructureBaseUrl();

function getSharedFilter(key, fallback) {
    if (window.SpotMicrostructureSharedState && typeof window.SpotMicrostructureSharedState.getFilter === "function") {
        const value = window.SpotMicrostructureSharedState.getFilter(key);
        return typeof value === "undefined" ? fallback : value;
    }
    return fallback;
}

function onSharedFilterChange(keys, handler) {
    if (!window.SpotMicrostructureSharedState) return;
    keys.forEach((key) => {
        window.SpotMicrostructureSharedState.subscribe(key, handler);
    });
}

function tradesController() {
    return {
        loading: false,
        selectedSymbol: "BTCUSDT",
        selectedInterval: "5m",
        selectedLimit: 200,
        autoRefreshEnabled: true,
        autoRefreshInterval: 15000,
        autoRefreshTimer: null,
        lastUpdated: null,
        filterDebounceTimer: null,
        filterDebounceDelay: 300,

        init() {
            console.log("🚀 Initializing Spot Trades & Large Orders controller");
            this.loadSharedState();
            this.registerSharedSubscriptions();
            this.loadAllData();
            this.startAutoRefresh();

            document.addEventListener("visibilitychange", () => {
                if (document.hidden) {
                    this.pauseAutoRefresh();
                } else {
                    this.resumeAutoRefresh();
                }
            });
        },

        loadSharedState() {
            const shared = window.SpotMicrostructureSharedState?.getAllFilters?.();
            if (!shared) return;

            this.selectedSymbol = shared.selectedSymbol || this.selectedSymbol;
            this.selectedInterval = shared.selectedInterval || this.selectedInterval;
            this.selectedLimit = shared.selectedLimit || this.selectedLimit;
        },

        registerSharedSubscriptions() {
            if (!window.SpotMicrostructureSharedState) return;

            ["selectedSymbol", "selectedInterval", "selectedLimit"].forEach((key) => {
                window.SpotMicrostructureSharedState.subscribe(key, (value) => {
                    if (typeof value === "undefined") return;
                    if (this[key] === value) return;
                    this[key] = value;
                    this.refreshAll();
                });
            });
        },

        updateSharedState() {
            if (!window.SpotMicrostructureSharedState) return;

            window.SpotMicrostructureSharedState.setFilter("selectedSymbol", this.selectedSymbol);
            window.SpotMicrostructureSharedState.setFilter("selectedInterval", this.selectedInterval);
            window.SpotMicrostructureSharedState.setFilter("selectedLimit", this.selectedLimit);
        },

        loadAllData() {
            this.loading = true;
            try {
                this.updateSharedState();
                window.dispatchEvent(new CustomEvent(spotTradesRefreshEvent));
                this.updateLastUpdated();
            } catch (error) {
                console.error("❌ Failed coordinating trades refresh:", error);
            } finally {
                this.loading = false;
            }
        },

        refreshAll() {
            this.loadAllData();
        },

        handleFilterChange() {
            if (this.filterDebounceTimer) {
                clearTimeout(this.filterDebounceTimer);
            }
            this.filterDebounceTimer = setTimeout(() => this.loadAllData(), this.filterDebounceDelay);
        },

        startAutoRefresh() {
            if (this.autoRefreshTimer) {
                clearInterval(this.autoRefreshTimer);
            }

            if (!this.autoRefreshEnabled) return;

            this.autoRefreshTimer = setInterval(() => {
                if (!document.hidden) {
                    this.loadAllData();
                }
            }, this.autoRefreshInterval);
        },

        pauseAutoRefresh() {
            if (this.autoRefreshTimer) {
                clearInterval(this.autoRefreshTimer);
                this.autoRefreshTimer = null;
            }
        },

        resumeAutoRefresh() {
            if (this.autoRefreshEnabled && !this.autoRefreshTimer) {
                this.startAutoRefresh();
            }
        },

        toggleAutoRefresh() {
            this.autoRefreshEnabled = !this.autoRefreshEnabled;
            if (this.autoRefreshEnabled) {
                this.startAutoRefresh();
            } else {
                this.pauseAutoRefresh();
            }
        },

        updateLastUpdated() {
            this.lastUpdated = new Date().toLocaleTimeString("en-US", {
                hour12: true,
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
            });
        },
    };
}

function tradeOverview() {
    return {
        loading: false,
        metrics: {
            currentPrice: 0,
            priceChange: 0,
            buyRatio: 0.5,
            netFlow: 0,
            largeOrderCount: 0,
            largeOrderNotional: 0,
            bias: "neutral",
            biasStrength: 0,
        },

        init() {
            this.loadOverview();
            onSharedFilterChange(["selectedSymbol", "selectedInterval", "selectedLimit"], () => this.loadOverview());
            window.addEventListener(spotTradesRefreshEvent, () => this.loadOverview());
        },

        async loadOverview() {
            this.loading = true;
            const symbol = getSharedFilter("selectedSymbol", "BTCUSDT");
            const interval = getSharedFilter("selectedInterval", "5m");
            const limit = getSharedFilter("selectedLimit", 200);

            try {
                const [summaryRes, largeOrdersRes, biasRes] = await Promise.all([
                    fetch(`${getSpotMicrostructureBaseUrl()}/trades/summary?symbol=${symbol}&interval=${interval}&limit=${limit}`),
                    fetch(`${getSpotMicrostructureBaseUrl()}/large-orders?symbol=${symbol}&limit=50&min_notional=100000`),
                    fetch(`${getSpotMicrostructureBaseUrl()}/trade-bias?symbol=${symbol}&limit=${limit}`),
                ]);

                if (!summaryRes.ok || !largeOrdersRes.ok || !biasRes.ok) {
                    throw new Error("Overview fetch failed");
                }

                const summary = await summaryRes.json();
                const largeOrders = await largeOrdersRes.json();
                const bias = await biasRes.json();

                this.processSummary(summary.data || []);
                this.processLargeOrders(largeOrders.data || []);
                this.processBias(bias);
            } catch (error) {
                console.error("❌ Overview metrics failed:", error);
                this.resetMetrics();
            } finally {
                this.loading = false;
            }
        },

        processSummary(summary) {
            if (!summary.length) {
                this.metrics.currentPrice = 0;
                this.metrics.priceChange = 0;
                this.metrics.buyRatio = 0.5;
                this.metrics.netFlow = 0;
                return;
            }

            const latest = summary[0];
            const oldest = summary[summary.length - 1];

            // CoinGlass doesn't provide price data, use base price
            this.metrics.currentPrice = 63000; // Base BTC price
            this.metrics.priceChange = 0; // No price change data available

            const totals = summary.reduce(
                (acc, bucket) => {
                    acc.buy += bucket.buy_volume_quote || 0;
                    acc.sell += bucket.sell_volume_quote || 0;
                    acc.net += bucket.net_flow_quote || 0;
                    return acc;
                },
                { buy: 0, sell: 0, net: 0 }
            );

            const totalVolume = totals.buy + totals.sell;
            this.metrics.buyRatio = totalVolume ? totals.buy / totalVolume : 0.5;
            this.metrics.netFlow = totals.net;
        },

        processLargeOrders(orders) {
            this.metrics.largeOrderCount = orders.length;
            this.metrics.largeOrderNotional = orders.reduce(
                (sum, order) => sum + (order.quote_quantity || 0),
                0
            );
        },

        processBias(payload) {
            this.metrics.bias = payload.bias || "neutral";
            this.metrics.biasStrength = payload.strength || 0;
        },

        resetMetrics() {
            this.metrics = {
                currentPrice: 0,
                priceChange: 0,
                buyRatio: 0.5,
                netFlow: 0,
                largeOrderCount: 0,
                largeOrderNotional: 0,
                bias: "neutral",
                biasStrength: 0,
            };
        },

        formatPrice(value) {
            if (!value || isNaN(value)) return "--";
            return (
                "$" +
                Number(value).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                })
            );
        },

        formatPercent(value) {
            if (!value || isNaN(value)) return "0.00%";
            const sign = value >= 0 ? "+" : "";
            return `${sign}${value.toFixed(2)}%`;
        },

        formatRatio(value) {
            return ((value || 0) * 100).toFixed(1) + "%";
        },

        formatCurrency(value) {
            if (!value || isNaN(value)) return "$0";
            if (value >= 1e9) return "$" + (value / 1e9).toFixed(2) + "B";
            if (value >= 1e6) return "$" + (value / 1e6).toFixed(2) + "M";
            if (value >= 1e3) return "$" + (value / 1e3).toFixed(2) + "K";
            return "$" + value.toFixed(0);
        },

        formatFlow(value) {
            if (!value || isNaN(value)) return "$0";
            const abs = Math.abs(value);
            if (abs >= 1e9) return `${value >= 0 ? "+" : "-"}$${(abs / 1e9).toFixed(2)}B`;
            if (abs >= 1e6) return `${value >= 0 ? "+" : "-"}$${(abs / 1e6).toFixed(2)}M`;
            if (abs >= 1e3) return `${value >= 0 ? "+" : "-"}$${(abs / 1e3).toFixed(2)}K`;
            return `${value >= 0 ? "+" : "-"}$${abs.toFixed(0)}`;
        },

        formatLargeOrdersTotal(value) {
            if (!value || isNaN(value)) return "$0";
            if (value >= 1e9) return "$" + (value / 1e9).toFixed(2) + "B";
            if (value >= 1e6) return "$" + (value / 1e6).toFixed(2) + "M";
            if (value >= 1e3) return "$" + (value / 1e3).toFixed(2) + "K";
            return "$" + value.toFixed(0);
        },

        getBiasBadgeClass() {
            if (this.metrics.bias === "buy") return "text-bg-success";
            if (this.metrics.bias === "sell") return "text-bg-danger";
            return "text-bg-secondary";
        },
    };
}

function cvdChartPanel() {
    return {
        loading: false,
        cvdData: [],
        largeOrders: [],
        recentTrades: [],
        volumeStats: [],
        chartInstance: null,
        selectedRange: "2h",
        metrics: {
            currentPrice: 0,
            priceChange: 0,
            buyRatio: 0.5,
            netFlow: 0,
            bias: "neutral",
        },
        ranges: [
            { label: "30m", value: "30m", limit: 120 },
            { label: "1h", value: "1h", limit: 240 },
            { label: "2h", value: "2h", limit: 360 },
            { label: "6h", value: "6h", limit: 720 },
            { label: "12h", value: "12h", limit: 1200 },
        ],

        init() {
            console.log("🚀 Initializing CVD Chart Panel...");
            this.loadData();
            onSharedFilterChange(["selectedSymbol"], () => this.loadData());
            window.addEventListener(spotTradesRefreshEvent, () => this.loadData());
            
            // Debug method to check data state
            window.debugCvd = () => {
                console.log("🔍 CVD Debug State:", {
                    loading: this.loading,
                    cvdDataLength: this.cvdData.length,
                    cvdDataSample: this.cvdData.slice(0, 2),
                    hasCanvas: !!this.$refs.cvdChart
                });
                return this.cvdData;
            };
        },

        async loadData() {
            this.loading = true;
            const symbol = getSharedFilter("selectedSymbol", "BTCUSDT");
            const limit = this.ranges.find((r) => r.value === this.selectedRange)?.limit || 360;

            try {
                console.log('📡 Fetching spot microstructure data...');

                // Fetch all data in parallel
                const [cvdData, largeOrdersData, recentTradesData, overviewData, volumeStatsData] = await Promise.allSettled([
                    this.loadCvd(),
                    this.loadOrders(),
                    this.loadRecentTrades(),
                    this.loadOverview(),
                    this.loadStats()
                ]);

                // Handle CVD data
                if (cvdData.status === 'fulfilled') {
                    const loadedData = cvdData.value;
                    console.log(`✅ Loaded ${loadedData.length} CVD data points`);
                    console.log("📊 CVD data sample:", loadedData.slice(0, 3));
                    
                    // Force reactive update by reassigning the array
                    this.cvdData = [...loadedData];
                    
                    // Debug alert to see if data is loaded
                    if (this.cvdData.length > 0) {
                        console.log("🎉 CVD DATA LOADED SUCCESSFULLY! Length:", this.cvdData.length);
                        console.log("🔍 CVD data after assignment:", this.cvdData.slice(0, 2));
                    } else {
                        console.log("❌ CVD DATA IS EMPTY!");
                    }
                    
                    // Additional debugging for CVD data
                    if (this.cvdData.length > 0) {
                        console.log("🔍 First CVD point:", this.cvdData[0]);
                        console.log("🔍 Last CVD point:", this.cvdData[this.cvdData.length - 1]);
                        console.log("🔍 CVD data structure check:", {
                            hasTs: this.cvdData[0].hasOwnProperty('ts'),
                            hasCvd: this.cvdData[0].hasOwnProperty('cvd'),
                            hasPrice: this.cvdData[0].hasOwnProperty('price'),
                            hasParsedTs: this.cvdData[0].hasOwnProperty('parsedTs')
                        });
                        
                        // Force chart render after data is loaded
                        setTimeout(() => {
                            console.log("🔄 Force rendering chart after CVD data load");
                            this.renderChart();
                        }, 100);
                    } else {
                        console.warn("⚠️ CVD data array is empty after loading!");
                    }
                } else {
                    console.error('❌ Error loading CVD data:', cvdData.reason);
                    this.cvdData = [];
                }

                // Handle large orders data
                if (largeOrdersData.status === 'fulfilled') {
                    this.largeOrders = largeOrdersData.value;
                    console.log(`✅ Loaded ${this.largeOrders.length} large orders`);
                } else {
                    console.error('❌ Error loading large orders:', largeOrdersData.reason);
                    this.largeOrders = [];
                }

                // Handle recent trades data
                if (recentTradesData.status === 'fulfilled') {
                    this.recentTrades = recentTradesData.value;
                    console.log(`✅ Loaded ${this.recentTrades.length} recent trades`);
                } else {
                    console.error('❌ Error loading recent trades:', recentTradesData.reason);
                    this.recentTrades = [];
                }

                // Handle overview data
                if (overviewData.status === 'fulfilled') {
                    this.processSummary(overviewData.value);
                    console.log('✅ Loaded overview metrics');
                } else {
                    console.error('❌ Error loading overview:', overviewData.reason);
                    this.processSummary([]);
                }

                // Handle volume stats data
                if (volumeStatsData.status === 'fulfilled') {
                    this.volumeStats = volumeStatsData.value;
                    console.log(`✅ Loaded ${this.volumeStats.length} volume stats`);
                } else {
                    console.error('❌ Error loading volume stats:', volumeStatsData.reason);
                    this.volumeStats = [];
                }

                // Render chart after all data is loaded with proper timing
                setTimeout(() => {
                    console.log("🎨 About to render chart with CVD data:", this.cvdData.length);
                    this.renderChart();
                }, 200); // Reduced timeout for faster rendering

            } catch (error) {
                console.error('❌ Error loading data:', error);
                // Set empty data on error
                this.cvdData = [];
                this.largeOrders = [];
                this.recentTrades = [];
                this.volumeStats = [];
                this.processSummary([]);
            } finally {
                this.loading = false;
                console.log("🏁 CVD Chart Panel loading complete. Final state:", {
                    loading: this.loading,
                    cvdDataLength: this.cvdData.length,
                    hasCanvas: !!this.$refs.cvdChart
                });
                
                // Force a final check after loading is complete
                setTimeout(() => {
                    console.log("🔍 Final CVD state check:", {
                        loading: this.loading,
                        cvdDataLength: this.cvdData.length,
                        templateShouldShow: !this.loading && this.cvdData.length > 0,
                        templateShouldHide: !this.loading && this.cvdData.length === 0
                    });
                }, 500);
            }
        },

        async loadCvd() {
            const symbol = getSharedFilter("selectedSymbol", "BTCUSDT");
            const limit = this.ranges.find((r) => r.value === this.selectedRange)?.limit || 360;

            console.log(`🔍 Loading CVD data: symbol=${symbol}, limit=${limit}`);

            try {
                const response = await fetch(`${getSpotMicrostructureBaseUrl()}/cvd?symbol=${symbol}&limit=${limit}`);
                if (!response.ok) throw new Error("Failed to load CVD");

                const payload = await response.json();
                const serverData = payload.data || [];

                console.log(`📊 CVD API response: ${serverData.length} data points`);

                console.log("🔍 Server data check:", {
                    serverDataLength: serverData.length,
                    serverDataSample: serverData.slice(0, 2)
                });
                
                if (!serverData.length) {
                    console.warn("⚠️ CVD API returned no data, synthesizing stub series");
                    return this.buildStubSeries(limit);
                } else {
                    console.log("✅ Processing real CVD data from server");
                    const normalizedData = this.normalizeServerCvd(serverData);
                    console.log(`✅ Normalized CVD data: ${normalizedData.length} points`);
                    console.log("🔍 Normalized data sample:", normalizedData.slice(0, 2));
                    return normalizedData;
                }
            } catch (error) {
                console.error("❌ CVD chart error:", error);
                return [];
            }
        },

        async loadRecentTrades() {
            const symbol = getSharedFilter("selectedSymbol", "BTCUSDT");
            const interval = getSharedFilter("selectedInterval", "5m");
            const limit = 50;

            try {
                const response = await fetch(`${getSpotMicrostructureBaseUrl()}/trades/summary?symbol=${symbol}&interval=${interval}&limit=${limit}`);
                if (!response.ok) throw new Error("Failed to load volume buckets");

                const payload = await response.json();
                return payload.data || [];
            } catch (error) {
                console.error("❌ Volume buckets error:", error);
                return [];
            }
        },

        processSummary(summary) {
            // This method is called from loadData but doesn't need to do anything
            // as cvdChartPanel doesn't have metrics to update
            console.log('processSummary called with', summary.length, 'items');
        },

        async loadOverview() {
            const symbol = getSharedFilter("selectedSymbol", "BTCUSDT");
            const interval = getSharedFilter("selectedInterval", "5m");
            const limit = getSharedFilter("selectedLimit", 200);

            try {
                const response = await fetch(`${getSpotMicrostructureBaseUrl()}/trades/summary?symbol=${symbol}&interval=${interval}&limit=${limit}`);
                if (!response.ok) throw new Error("Overview fetch failed");

                const payload = await response.json();
                return payload.data || [];
            } catch (error) {
                console.error("❌ Overview error:", error);
                return [];
            }
        },

        async loadOrders() {
            const symbol = getSharedFilter("selectedSymbol", "BTCUSDT");
            const limit = 40;
            const minNotional = 100000;

            try {
                const response = await fetch(`${getSpotMicrostructureBaseUrl()}/large-orders?symbol=${symbol}&limit=${limit}&min_notional=${minNotional}`);
                if (!response.ok) throw new Error("Failed to load large orders");

                const payload = await response.json();
                return payload.data || [];
            } catch (error) {
                console.error("❌ Large orders error:", error);
                return [];
            }
        },

        async loadStats() {
            const symbol = getSharedFilter("selectedSymbol", "BTCUSDT");
            const interval = getSharedFilter("selectedInterval", "5m");
            const limit = getSharedFilter("selectedLimit", 200);

            try {
                const response = await fetch(`${getSpotMicrostructureBaseUrl()}/trades/summary?symbol=${symbol}&interval=${interval}&limit=${limit}`);
                if (!response.ok) throw new Error("Volume stats failed");

                const payload = await response.json();
                return payload.data || [];
            } catch (error) {
                console.error("❌ Volume stats error:", error);
                return [];
            }
        },

        renderChart() {
            console.log("🎨 Starting chart render...");
            console.log("🔍 Chart render prerequisites check:", {
                chartJsLoaded: typeof Chart !== "undefined",
                canvasExists: !!this.$refs.cvdChart,
                cvdDataLength: this.cvdData.length,
                cvdDataSample: this.cvdData.slice(0, 2)
            });
            
            // Check prerequisites
            if (typeof Chart === "undefined") {
                console.error("❌ Chart.js not loaded");
                return;
            }
            
            if (!this.$refs.cvdChart) {
                console.error("❌ Canvas element not found");
                console.log("🔍 Available refs:", Object.keys(this.$refs || {}));
                return;
            }
            
            console.log("✅ Canvas element found:", {
                width: this.$refs.cvdChart.width,
                height: this.$refs.cvdChart.height,
                offsetWidth: this.$refs.cvdChart.offsetWidth,
                offsetHeight: this.$refs.cvdChart.offsetHeight
            });
            
            if (!this.cvdData.length) {
                console.warn("⚠️ No CVD data available for chart rendering");
                console.log("🔍 CVD data state:", this.cvdData);
                this.destroyChart();
                return;
            }

            console.log(`📊 Rendering chart with ${this.cvdData.length} data points`);

            // Destroy existing chart first
            this.destroyChart();

            // Use multiple attempts with delays to ensure canvas is ready
            this.attemptChartRender(0);
        },

        attemptChartRender(attempt) {
            const maxAttempts = 10;
            const delay = 100 * (attempt + 1); // Increasing delay: 100ms, 200ms, 300ms, etc.

            setTimeout(() => {
                try {
                    // Check if canvas is still available
                    if (!this.$refs.cvdChart) {
                        console.error(`❌ Canvas lost on attempt ${attempt + 1}`);
                        return;
                    }

                    // Check canvas dimensions
                    const canvas = this.$refs.cvdChart;
                    if (canvas.width === 0 || canvas.height === 0) {
                        console.warn(`⚠️ Canvas has zero dimensions on attempt ${attempt + 1}`);
                        if (attempt < maxAttempts - 1) {
                            this.attemptChartRender(attempt + 1);
                        }
                        return;
                    }

                    // Get context with error handling
                    let ctx;
                    try {
                        ctx = canvas.getContext("2d");
                    } catch (contextError) {
                        console.error(`❌ Context error on attempt ${attempt + 1}:`, contextError);
                        if (attempt < maxAttempts - 1) {
                            this.attemptChartRender(attempt + 1);
                        }
                        return;
                    }

                    if (!ctx) {
                        console.error(`❌ No context on attempt ${attempt + 1}`);
                        if (attempt < maxAttempts - 1) {
                            this.attemptChartRender(attempt + 1);
                        }
                        return;
                    }

                    // Test context functionality
                    try {
                        ctx.save();
                        ctx.restore();
                    } catch (contextTestError) {
                        console.error(`❌ Context test failed on attempt ${attempt + 1}:`, contextTestError);
                        if (attempt < maxAttempts - 1) {
                            this.attemptChartRender(attempt + 1);
                        }
                        return;
                    }

                    console.log(`✅ Canvas ready on attempt ${attempt + 1}`);

                    // Prepare data
                    console.log("🎨 Preparing chart data from CVD data:", this.cvdData.length, "points");
                    
                    const labels = this.cvdData.map((item) =>
                        this.formatTimestamp(item.parsedTs || item.ts || item.timestamp)
                    );
                    const cvdValues = this.cvdData.map((item) => item.cvd);
                    const priceValues = this.cvdData.map((item) => item.price);
                    
                    console.log("📊 Chart data prepared:", {
                        labels: labels.length,
                        cvdValues: cvdValues.length,
                        priceValues: priceValues.length,
                        sampleLabel: labels[0],
                        sampleCvd: cvdValues[0],
                        samplePrice: priceValues[0],
                        labelsValid: labels.every(l => l && typeof l === 'string'),
                        cvdValid: cvdValues.every(v => v !== null && !isNaN(v)),
                        priceValid: priceValues.every(v => v !== null && !isNaN(v))
                    });

                    // Create gradient safely
                    let gradient;
                    try {
                        gradient = ctx.createLinearGradient(0, 0, 0, 240);
                        gradient.addColorStop(0, "rgba(34, 197, 94, 0.35)");
                        gradient.addColorStop(1, "rgba(34, 197, 94, 0)");
                    } catch (gradientError) {
                        console.error("❌ Gradient creation failed:", gradientError);
                        gradient = "#22c55e"; // Fallback to solid color
                    }

                    // Create chart with comprehensive error handling
                    console.log("🚀 ABOUT TO CREATE CHART.JS INSTANCE!");
                    console.log("📊 Final data check before chart creation:", {
                        labels: labels.slice(0, 3),
                        cvdValues: cvdValues.slice(0, 3),
                        priceValues: priceValues.slice(0, 3)
                    });
                    
                    this.chartInstance = new Chart(ctx, {
                        type: "line",
                        data: {
                            labels,
                            datasets: [
                                {
                                    label: "CVD (quote)",
                                    data: cvdValues,
                                    borderColor: "#10b981",
                                    backgroundColor: gradient,
                                    fill: true,
                                    tension: 0.4,
                                    borderWidth: 2.5,
                                    pointRadius: 0,
                                    pointHoverRadius: 6,
                                    pointHoverBackgroundColor: "#10b981",
                                    pointHoverBorderColor: "#ffffff",
                                    pointHoverBorderWidth: 2,
                                    yAxisID: "y",
                                },
                                {
                                    label: "Price (USD)",
                                    data: priceValues,
                                    borderColor: "#3b82f6",
                                    backgroundColor: "rgba(59, 130, 246, 0.1)",
                                    borderWidth: 2.5,
                                    tension: 0.4,
                                    pointRadius: 0,
                                    pointHoverRadius: 6,
                                    pointHoverBackgroundColor: "#3b82f6",
                                    pointHoverBorderColor: "#ffffff",
                                    pointHoverBorderWidth: 2,
                                    fill: false,
                                    yAxisID: "y1",
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: {
                                duration: 0 // Disable animation to prevent timing issues
                            },
                            interaction: {
                                mode: "index",
                                intersect: false,
                            },
                            scales: {
                                x: {
                                    type: "category",
                                    ticks: {
                                        color: "#64748b",
                                        maxRotation: 45,
                                        autoSkip: true,
                                        maxTicksLimit: 10,
                                        font: {
                                            size: 11,
                                            weight: '500'
                                        }
                                    },
                                    grid: {
                                        color: "rgba(148, 163, 184, 0.15)",
                                        drawOnChartArea: true,
                                        drawTicks: true,
                                    },
                                    border: {
                                        color: "rgba(148, 163, 184, 0.3)",
                                        width: 1
                                    }
                                },
                                y: {
                                    position: "left",
                                    ticks: {
                                        callback: (value) => {
                                            if (Math.abs(value) >= 1e6) {
                                                return value >= 0 ? `+${(value / 1e6).toFixed(1)}M` : `${(value / 1e6).toFixed(1)}M`;
                                            } else if (Math.abs(value) >= 1e3) {
                                                return value >= 0 ? `+${(value / 1e3).toFixed(0)}K` : `${(value / 1e3).toFixed(0)}K`;
                                            }
                                            return value >= 0 ? `+${value.toFixed(0)}` : `${value.toFixed(0)}`;
                                        },
                                        color: "#10b981",
                                        font: {
                                            size: 11,
                                            weight: '600'
                                        }
                                    },
                                    grid: {
                                        color: "rgba(16, 185, 129, 0.1)",
                                        drawOnChartArea: true,
                                    },
                                    border: {
                                        color: "rgba(16, 185, 129, 0.3)",
                                        width: 1
                                    }
                                },
                                y1: {
                                    position: "right",
                                    ticks: {
                                        callback: (value) => "$" + Number(value).toLocaleString(),
                                        color: "#3b82f6",
                                        font: {
                                            size: 11,
                                            weight: '600'
                                        }
                                    },
                                    grid: {
                                        drawOnChartArea: false,
                                    },
                                    border: {
                                        color: "rgba(59, 130, 246, 0.3)",
                                        width: 1
                                    }
                                },
                            },
                            plugins: {
                                legend: {
                                    labels: {
                                        color: "#64748b",
                                        font: {
                                            size: 12,
                                            weight: '600'
                                        },
                                        usePointStyle: true,
                                        pointStyle: 'line',
                                        padding: 20
                                    },
                                    position: 'top',
                                    align: 'end'
                                },
                                tooltip: {
                                    callbacks: {
                                        title: (items) => {
                                            // Get the original timestamp from cvdData using dataIndex
                                            const index = items[0]?.dataIndex;
                                            if (index !== undefined && this.cvdData[index]) {
                                                const item = this.cvdData[index];
                                                // Use the formatted timestamp from API
                                                if (item.timestamp) {
                                                    const date = new Date(item.timestamp);
                                                    return date.toLocaleString('id-ID', {
                                                        weekday: 'short',
                                                        year: 'numeric',
                                                        month: 'short',
                                                        day: '2-digit',
                                                        hour: '2-digit',
                                                        minute: '2-digit'
                                                    });
                                                }
                                                // Fallback to ts
                                                if (item.ts) {
                                                    const date = new Date(item.ts);
                                                    return date.toLocaleString('id-ID', {
                                                        weekday: 'short',
                                                        year: 'numeric',
                                                        month: 'short',
                                                        day: '2-digit',
                                                        hour: '2-digit',
                                                        minute: '2-digit'
                                                    });
                                                }
                                            }
                                            return items[0]?.label || "Data";
                                        },
                                        label: (context) => {
                                            if (context.dataset.label === "CVD (quote)") {
                                                const value = context.parsed.y;
                                                if (Math.abs(value) >= 1e6) {
                                                    return `💹 CVD: ${value >= 0 ? '+' : ''}${(value / 1e6).toFixed(2)}M`;
                                                } else if (Math.abs(value) >= 1e3) {
                                                    return `💹 CVD: ${value >= 0 ? '+' : ''}${(value / 1e3).toFixed(1)}K`;
                                                }
                                                return `💹 CVD: ${value >= 0 ? '+' : ''}${value.toFixed(0)}`;
                                            }
                                            return `💰 Price: $${context.parsed.y.toLocaleString()}`;
                                        },
                                    },
                                },
                            },
                        },
                    });

                    console.log("✅ Chart created successfully!");

                } catch (error) {
                    console.error(`❌ Chart creation failed on attempt ${attempt + 1}:`, error);
                    if (attempt < maxAttempts - 1) {
                        console.log(`🔄 Retrying in ${delay}ms...`);
                        this.attemptChartRender(attempt + 1);
                    } else {
                        console.error("❌ Max attempts reached, chart creation failed");
                        this.chartInstance = null;
                    }
                }
            }, delay);
        },

        ensureCanvasReady() {
            return new Promise((resolve) => {
                const checkCanvas = () => {
                    if (this.$refs.cvdChart && this.$refs.cvdChart.getContext) {
                        const ctx = this.$refs.cvdChart.getContext("2d");
                        if (ctx && this.$refs.cvdChart.width > 0 && this.$refs.cvdChart.height > 0) {
                            resolve(true);
                            return;
                        }
                    }
                    setTimeout(checkCanvas, 50);
                };
                checkCanvas();
            });
        },

        destroyChart() {
            if (this.chartInstance) {
                try {
                    console.log("🗑️ Destroying existing chart...");
                    this.chartInstance.destroy();
                    console.log("✅ Chart destroyed successfully");
                } catch (error) {
                    console.warn("⚠️ Error destroying chart:", error);
                } finally {
                    this.chartInstance = null;
                }
            }
        },

        setRange(range) {
            if (this.selectedRange === range) return;
            this.selectedRange = range;
            this.loadData();
        },

        buildStubSeries(limit) {
            const now = Date.now();
            const basePrice = 63000;
            const data = [];
            let cumulative = 0;

            for (let i = 0; i < limit; i++) {
                const delta = Math.sin(i / 6) * 180;
                cumulative += delta;
                const ts = now - (limit - i) * 15000;
                data.push({
                    ts,
                    parsedTs: ts,
                    cvd: parseFloat(cumulative.toFixed(2)),
                    price: parseFloat((basePrice + Math.cos(i / 8) * 120).toFixed(2)),
                });
            }

            return data;
        },

        normalizeServerCvd(serverData) {
            console.log("🔄 Normalizing CVD server data:", serverData.length, "points");
            
            return serverData.map((point, idx) => {
                const parsedTs = this.parseTimestamp(point.ts || point.timestamp);
                const cvd = parseFloat(point.cvd || 0);
                const price = parseFloat(point.price || 67000); // Use real price from API

                console.log(`CVD Point ${idx}:`, {
                    ts: point.ts,
                    cvd: cvd,
                    price: price,
                    parsedTs: parsedTs
                });

                return {
                    ts: point.ts,
                    timestamp: point.timestamp,
                    parsedTs,
                    cvd,
                    price: price,
                };
            });
        },

        parseTimestamp(value) {
            if (typeof value === "number") {
                // CoinGlass timestamps are already in milliseconds
                return value;
            }
            const parsed = Date.parse(value);
            if (!Number.isNaN(parsed)) return parsed;
            return Date.now();
        },

        formatCurrency(value) {
            if (!value || isNaN(value)) return "$0";
            if (value >= 1e9) return "$" + (value / 1e9).toFixed(2) + "B";
            if (value >= 1e6) return "$" + (value / 1e6).toFixed(2) + "M";
            if (value >= 1e3) return "$" + (value / 1e3).toFixed(2) + "K";
            return "$" + value.toFixed(0);
        },

        getBiasBadgeClass() {
            if (this.metrics.bias === "buy") return "text-bg-success";
            if (this.metrics.bias === "sell") return "text-bg-danger";
            return "text-bg-secondary";
        },

        formatRatio(value) {
            if (!value || isNaN(value)) return "0%";
            return (value * 100).toFixed(1) + "%";
        },

        formatFlow(value) {
            if (!value || isNaN(value)) return "$0";
            if (value >= 1e9) return "$" + (value / 1e9).toFixed(2) + "B";
            if (value >= 1e6) return "$" + (value / 1e6).toFixed(2) + "M";
            if (value >= 1e3) return "$" + (value / 1e3).toFixed(2) + "K";
            return "$" + value.toFixed(0);
        },

        formatTimestamp(raw) {
            const parsed = this.parseTimestamp(raw);
            const date = new Date(parsed);
            return date.toLocaleTimeString("en-US", {
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
            });
        },
    };
}

function largeOrdersPanel() {
    return {
        loading: false,
        orders: [],
        minNotional: 100000,
        stats: {
            totalNotional: 0,
            buyOrders: 0,
            sellOrders: 0,
            largestOrder: null,
        },

        init() {
            this.loadOrders();
            onSharedFilterChange(["selectedSymbol"], () => this.loadOrders());
            window.addEventListener(spotTradesRefreshEvent, () => this.loadOrders());
        },

        async loadOrders() {
            this.loading = true;
            const symbol = getSharedFilter("selectedSymbol", "BTCUSDT");

            try {
                const response = await fetch(
                    `${getSpotMicrostructureBaseUrl()}/large-orders?symbol=${symbol}&limit=40&min_notional=${this.minNotional}`
                );
                if (!response.ok) throw new Error("Failed to load large orders");

                const payload = await response.json();
                this.orders = (payload.data || []).map((order) => ({
                    ...order,
                    notional: order.quote_quantity || 0,
                }));
                this.calculateStats();
            } catch (error) {
                console.error("❌ Large orders error:", error);
                this.orders = [];
                this.stats = {
                    totalNotional: 0,
                    buyOrders: 0,
                    sellOrders: 0,
                    largestOrder: null,
                };
            } finally {
                this.loading = false;
            }
        },

        calculateStats() {
            const totalNotional = this.orders.reduce((sum, order) => sum + (order.notional || 0), 0);
            const buyOrders = this.orders.filter((o) => o.side === "buy").length;
            const sellOrders = this.orders.filter((o) => o.side === "sell").length;
            const largestOrder = this.orders.length ? this.orders[0] : null;

            this.stats = {
                totalNotional,
                buyOrders,
                sellOrders,
                largestOrder,
            };
        },

        setThreshold(value) {
            if (this.minNotional === value) return;
            this.minNotional = value;
            this.loadOrders();
        },

        formatNotional(value) {
            if (!value || isNaN(value)) return "$0";
            if (value >= 1e9) return "$" + (value / 1e9).toFixed(2) + "B";
            if (value >= 1e6) return "$" + (value / 1e6).toFixed(2) + "M";
            if (value >= 1e3) return "$" + (value / 1e3).toFixed(2) + "K";
            return "$" + value.toFixed(0);
        },

        formatQty(value) {
            if (!value || isNaN(value)) return "0.00";
            if (Math.abs(value) >= 1) return value.toFixed(2);
            return value.toFixed(4);
        },

        formatPrice(value) {
            if (!value || isNaN(value)) return "--";
            return (
                "$" +
                Number(value).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                })
            );
        },

        formatTime(ts) {
            if (!ts) return "--";
            return new Date(Number(ts)).toLocaleTimeString("en-US", {
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
            });
        },

        formatDateTime(ts) {
            if (!ts) return "--";
            const date = new Date(typeof ts === 'string' ? ts : Number(ts));
            return date.toLocaleString('id-ID', {
                month: 'short',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        getSideBadge(side) {
            return side === "buy" ? "bg-success-subtle text-success" : "bg-danger-subtle text-danger";
        },
    };
}

function tradeSummaryTable() {
    return {
        trades: [],
        loading: false,

        init() {
            this.loadSummary();
            onSharedFilterChange(["selectedSymbol", "selectedInterval", "selectedLimit"], () => this.loadSummary());
            window.addEventListener(spotTradesRefreshEvent, () => this.loadSummary());
        },

        async loadSummary() {
            this.loading = true;
            const symbol = getSharedFilter("selectedSymbol", "BTCUSDT");
            const interval = getSharedFilter("selectedInterval", "5m");
            const limit = getSharedFilter("selectedLimit", 200);

            try {
                const response = await fetch(
                    `${getSpotMicrostructureBaseUrl()}/trades/summary?symbol=${symbol}&interval=${interval}&limit=${limit}`
                );
                if (!response.ok) throw new Error("Summary fetch failed");

                const payload = await response.json();
                this.trades = payload.data || [];
            } catch (error) {
                console.error("❌ Trade summary error:", error);
                this.trades = [];
            } finally {
                this.loading = false;
            }
        },

        formatTime(timestamp) {
            if (!timestamp) return "--";
            return new Date(timestamp).toLocaleTimeString("en-US", {
                hour: "2-digit",
                minute: "2-digit",
            });
        },

        formatPrice(price) {
            if (!price || isNaN(price)) return "--";
            return "$" + Number(price).toLocaleString(undefined, { maximumFractionDigits: 2 });
        },

        formatVolume(volume) {
            if (!volume || isNaN(volume)) return "$0";
            if (Math.abs(volume) >= 1e6) return "$" + (volume / 1e6).toFixed(2) + "M";
            if (Math.abs(volume) >= 1e3) return "$" + (volume / 1e3).toFixed(2) + "K";
            return "$" + volume.toFixed(0);
        },

        buyRatio(bucket) {
            const total = (bucket.buy_volume_quote || 0) + (bucket.sell_volume_quote || 0);
            if (!total) return "50%";
            return ((bucket.buy_volume_quote / total) * 100).toFixed(1) + "%";
        },

        formatDateTime(ts) {
            if (!ts) return "--";
            const date = new Date(typeof ts === 'string' ? ts : Number(ts));
            return date.toLocaleString('id-ID', {
                month: 'short',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        },
    };
}

function volumeFlowStats() {
    return {
        loading: false,
        totalBuyVolume: 0,
        totalSellVolume: 0,
        netFlow: 0,
        totalTrades: 0,
        avgTradeSize: 0,

        init() {
            this.loadStats();
            onSharedFilterChange(["selectedSymbol", "selectedInterval", "selectedLimit"], () => this.loadStats());
            window.addEventListener(spotTradesRefreshEvent, () => this.loadStats());
        },

        async loadStats() {
            this.loading = true;
            const symbol = getSharedFilter("selectedSymbol", "BTCUSDT");
            const interval = getSharedFilter("selectedInterval", "5m");
            const limit = getSharedFilter("selectedLimit", 200);

            try {
                const response = await fetch(
                    `${getSpotMicrostructureBaseUrl()}/trades/summary?symbol=${symbol}&interval=${interval}&limit=${limit}`
                );
                if (!response.ok) throw new Error("Volume stats failed");

                const payload = await response.json();
                const data = payload.data || [];

                if (!data.length) {
                    this.reset();
                    return;
                }

                this.totalBuyVolume = data.reduce((sum, d) => sum + (d.buy_volume_quote || 0), 0);
                this.totalSellVolume = data.reduce((sum, d) => sum + (d.sell_volume_quote || 0), 0);
                this.netFlow = data.reduce((sum, d) => sum + (d.net_flow_quote || 0), 0);
                // Since CoinGlass doesn't provide individual trade counts, use data buckets as proxy
                this.totalTrades = data.length; // Number of volume buckets
                this.avgTradeSize = this.totalTrades > 0 ? (this.totalBuyVolume + this.totalSellVolume) / this.totalTrades : 0;
            } catch (error) {
                console.error("❌ Volume stats error:", error);
                this.reset();
            } finally {
                this.loading = false;
            }
        },

        reset() {
            this.totalBuyVolume = 0;
            this.totalSellVolume = 0;
            this.netFlow = 0;
            this.totalTrades = 0;
            this.avgTradeSize = 0;
        },

        formatVolume(value) {
            if (!value || isNaN(value)) return "$0";
            if (Math.abs(value) >= 1e9) return "$" + (value / 1e9).toFixed(2) + "B";
            if (Math.abs(value) >= 1e6) return "$" + (value / 1e6).toFixed(2) + "M";
            if (Math.abs(value) >= 1e3) return "$" + (value / 1e3).toFixed(2) + "K";
            return "$" + value.toFixed(0);
        },
    };
}

function recentTradesStream() {
    return {
        loading: false,
        trades: [],

        init() {
            this.loadTrades();
            onSharedFilterChange(["selectedSymbol", "selectedLimit"], () => this.loadTrades());
            window.addEventListener(spotTradesRefreshEvent, () => this.loadTrades());
        },

        async loadTrades() {
            this.loading = true;
            const symbol = getSharedFilter("selectedSymbol", "BTCUSDT");
            const interval = getSharedFilter("selectedInterval", "5m");
            const limit = Math.min(getSharedFilter("selectedLimit", 200), 100);

            try {
                const response = await fetch(`${getSpotMicrostructureBaseUrl()}/trades/summary?symbol=${symbol}&interval=${interval}&limit=${limit}`);
                if (!response.ok) throw new Error("Volume buckets failed");

                const payload = await response.json();
                this.trades = payload.data || [];
            } catch (error) {
                console.error("❌ Volume buckets error:", error);
                this.trades = [];
            } finally {
                this.loading = false;
            }
        },

        formatTime(timestamp) {
            if (!timestamp) return "--";
            return new Date(timestamp).toLocaleTimeString("en-US", {
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
            });
        },

        formatDateTime(ts) {
            if (!ts) return "--";
            const date = new Date(typeof ts === 'string' ? ts : Number(ts));
            return date.toLocaleString('id-ID', {
                month: 'short',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        formatPrice(price) {
            if (!price || isNaN(price)) return "--";
            return (
                "$" +
                Number(price).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                })
            );
        },

        formatQty(qty) {
            if (!qty || isNaN(qty)) return "0.0000";
            return qty >= 1 ? qty.toFixed(3) : qty.toFixed(5);
        },

        formatVolume(volume) {
            if (!volume || isNaN(volume)) return "$0";
            if (Math.abs(volume) >= 1e6) return "$" + (volume / 1e6).toFixed(2) + "M";
            if (Math.abs(volume) >= 1e3) return "$" + (volume / 1e3).toFixed(2) + "K";
            return "$" + volume.toFixed(2);
        },
    };
}

(function bootstrapSharedState() {
    const defaults = {
        selectedSymbol: "BTCUSDT",
        selectedInterval: "5m",
        selectedLimit: 200,
        selectedExchange: "binance",
    };

    if (!window.SpotMicrostructureSharedState) {
        window.SpotMicrostructureSharedState = {
            filters: { ...defaults },
            subscribers: {},
            setFilter(key, value) {
                if (this.filters[key] === value) return;
                this.filters[key] = value;
                this.notifySubscribers(key, value);
            },
            getFilter(key) {
                return this.filters[key];
            },
            getAllFilters() {
                return { ...this.filters };
            },
            subscribe(key, callback) {
                if (!this.subscribers[key]) {
                    this.subscribers[key] = [];
                }
                this.subscribers[key].push(callback);
            },
            notifySubscribers(key, value) {
                if (!this.subscribers[key]) return;
                this.subscribers[key].forEach((callback) => {
                    try {
                        callback(value);
                    } catch (error) {
                        console.error("SpotMicrostructureSharedState subscriber failed:", error);
                    }
                });
            },
        };
    } else {
        Object.keys(defaults).forEach((key) => {
            if (typeof window.SpotMicrostructureSharedState.filters?.[key] === "undefined") {
                window.SpotMicrostructureSharedState.filters[key] = defaults[key];
            }
        });
    }
})();

console.log("✅ Trades & Large Orders controller loaded");
