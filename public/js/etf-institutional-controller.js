/**
 * ETF & Institutional Dashboard Controller
 *
 * Global controller untuk mengoordinasikan semua komponen ETF & institutional data
 *
 * Think like a trader:
 * - Positive ETF Flow → Institutional accumulation → Bullish medium-term
 * - Premium > 50bps → Overvaluation risk → Take profit consideration
 * - COT Long/Short ratio → Track smart money positioning
 * - High creations/low redemptions → Strong demand signal
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

function etfInstitutionalController() {
    return {
        // Global state
        selectedAsset: "BTC", // Fixed to BTC since ETF data is Bitcoin-specific
        selectedPeriod: "30", // Time period filter (days)
        loading: false,
        lastUpdated: null,

        // Data state
        etfFlows: [],
        etfSummary: {},
        premiumDiscount: [],
        creationsRedemptions: [],
        cmeOpenInterest: [],
        cotData: [],
        cmeSummary: {},

        // Flow meter state
        flowMeter: {
            daily_flow: 0,
        },

        // Institutional overview state
        overview: {
            net_inflow_24h: 0,
            change_24h: 0,
            total_aum: 0,
            top_issuer: '',
            top_issuer_flow: 0,
            total_shares: 0,
            btc_equivalent: 0
        },

        // Charts
        etfFlowChart: null,
        premiumDiscountChart: null,
        cmeOiChart: null,
        cotComparisonChart: null,

        // Error state
        errors: {},

        // Auto-refresh timer
        autoRefreshTimer: null,
        autoRefreshInterval: 300000, // 5 minutes

        // API endpoints
        API_ENDPOINTS: {
            spotFlows: '/api/etf-institutional/spot/daily-flows',
            spotSummary: '/api/etf-institutional/spot/summary',
            premiumDiscount: '/api/etf-institutional/spot/premium-discount',
            creationsRedemptions: '/api/etf-institutional/spot/creations-redemptions',
            cmeOI: '/api/etf-institutional/cme/oi',
            cmeCOT: '/api/etf-institutional/cme/cot',
            cmeSummary: '/api/etf-institutional/cme/summary'
        },

        // Initialize dashboard
        init() {
            // Initialize from URL parameters
            this.initFromURL();

            console.log("🚀 ETF & Institutional Dashboard initialized");
            console.log("📊 Asset:", this.selectedAsset);
            console.log("📅 Period:", this.selectedPeriod, "days");

            // Setup event listeners
            this.setupEventListeners();

            // Setup auto-refresh
            this.setupAutoRefresh();

            // Initialize charts first, then load data
            this.initializeChartsAndData();

            // Log dashboard ready
            setTimeout(() => {
                console.log("✅ ETF Dashboard components loaded");
                this.logDashboardStatus();
            }, 2000);
        },

        // Initialize from URL parameters
        initFromURL() {
            const urlParams = new URLSearchParams(window.location.search);

            // Get period from URL, default to 30
            const periodParam = urlParams.get('period');
            if (periodParam && ['30', '60', '90', '180'].includes(periodParam)) {
                this.selectedPeriod = periodParam;
            }

            // Asset is fixed to BTC for ETF data
            this.selectedAsset = 'BTC';
        },

        // Initialize charts and load data in proper sequence
        async initializeChartsAndData() {
            try {
                console.log("🚀 Starting data-driven initialization");

                // Load data first (this will trigger chart rendering)
                await this.loadAllData();

            } catch (error) {
                console.error("❌ Failed to initialize charts and data:", error);
            }
        },

        // Setup global event listeners
        setupEventListeners() {
            // Listen for asset changes (kept for compatibility)
            window.addEventListener("asset-changed", (event) => {
                console.log("Asset change ignored - ETF data is Bitcoin-specific");
            });

            // Listen for manual refresh
            window.addEventListener("refresh-etf-data", () => {
                this.refreshAll();
            });

            // Page visibility API for auto-refresh optimization
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.pauseAutoRefresh();
                } else {
                    this.resumeAutoRefresh();
                }
            });
        },

        // Update time period filter
        updatePeriod() {
            console.log("🔄 Updating period to:", this.selectedPeriod, "days");

            // Update browser URL (optional, for bookmarking)
            this.updateURL();

            // Reload data for new period (use setTimeout to prevent circular reference)
            setTimeout(() => {
                this.loadAllData().catch((e) => {
                    console.warn("Period change data reload failed:", e);
                });
            }, 10);
        },

        // Update asset globally (kept for compatibility)
        updateAsset() {
            console.log("🔄 Asset remains:", this.selectedAsset, "(ETF data is Bitcoin-specific)");
            // ETF data is Bitcoin-specific, so no need to reload
        },

        // Load all ETF data in parallel
        async loadAllData() {
            // Prevent multiple simultaneous loads
            if (this.loading) {
                console.log("⏳ Data loading already in progress, skipping...");
                return;
            }

            this.loading = true;
            console.log("📡 Loading all ETF data...");

            try {
                // Execute all API calls in parallel for better performance
                const [
                    flowsResponse,
                    summaryResponse,
                    premiumResponse,
                    creationsResponse,
                    cmeOIResponse,
                    cotResponse,
                    cmeSummaryResponse
                ] = await Promise.all([
                    this.fetchETFFlows(),
                    this.fetchETFSummary(),
                    this.fetchPremiumDiscount(),
                    this.fetchCreationsRedemptions(),
                    this.fetchCMEOpenInterest(),
                    this.fetchCOTData(),
                    this.fetchCMESummary()
                ]);

                // Process and update state
                this.processETFFlows(flowsResponse);
                this.processETFSummary(summaryResponse);
                this.processPremiumDiscount(premiumResponse);
                this.processCreationsRedemptions(creationsResponse);
                this.processCMEOpenInterest(cmeOIResponse);
                this.processCOTData(cotResponse);
                this.processCMESummary(cmeSummaryResponse);

                // Update overview metrics
                this.updateOverviewMetrics();

                // Render charts with data
                this.renderCharts();

                this.lastUpdated = Date.now();
                console.log("✅ All ETF data loaded successfully");

            } catch (error) {
                console.error("❌ Failed to load ETF data:", error);
                this.handleError(error, 'loadAllData');
            } finally {
                this.loading = false;
            }
        },

        // Fetch ETF flows data
        async fetchETFFlows() {
            return this.fetchAPI(this.API_ENDPOINTS.spotFlows, {
                symbol: this.selectedAsset,
                limit: parseInt(this.selectedPeriod) // Use selected period
            });
        },

        // Fetch ETF summary data
        async fetchETFSummary() {
            return this.fetchAPI(this.API_ENDPOINTS.spotSummary, {
                symbol: this.selectedAsset,
                limit: 180
            });
        },

        // Fetch premium/discount data
        async fetchPremiumDiscount() {
            return this.fetchAPI(this.API_ENDPOINTS.premiumDiscount, {
                symbol: this.selectedAsset,
                limit: parseInt(this.selectedPeriod) // Use selected period
            });
        },

        // Fetch creations/redemptions data
        async fetchCreationsRedemptions() {
            return this.fetchAPI(this.API_ENDPOINTS.creationsRedemptions, {
                symbol: this.selectedAsset,
                limit: 10 // Recent activity
            });
        },

        // Fetch CME open interest data
        async fetchCMEOpenInterest() {
            return this.fetchAPI(this.API_ENDPOINTS.cmeOI, {
                symbol: this.selectedAsset,
                limit: parseInt(this.selectedPeriod) // Use selected period
            });
        },

        // Fetch COT data
        async fetchCOTData() {
            return this.fetchAPI(this.API_ENDPOINTS.cmeCOT, {
                symbol: this.selectedAsset,
                limit: 10 // Last 10 weeks
            });
        },

        // Fetch CME summary data
        async fetchCMESummary() {
            return this.fetchAPI(this.API_ENDPOINTS.cmeSummary, {
                symbol: this.selectedAsset,
                limit: 180
            });
        },

        // Process ETF flows data
        processETFFlows(response) {
            if (response && Array.isArray(response.data)) {
                this.etfFlows = response.data.map(flow => ({
                    ...flow,
                    id: `${flow.date}-${flow.ticker}`,
                    flow_usd: parseFloat(flow.flow_usd) || 0,
                    aum_usd: parseFloat(flow.aum_usd) || 0,
                    shares_outstanding: parseFloat(flow.shares_outstanding) || 0
                }));

                // Sort by date to ensure we get the latest date correctly
                this.etfFlows.sort((a, b) => new Date(b.date) - new Date(a.date));

                // Calculate daily flow for meter - sum all flows for the most recent date
                if (this.etfFlows.length > 0) {
                    const latestDate = this.etfFlows[0].date;
                    const latestFlows = this.etfFlows.filter(f => f.date === latestDate);
                    
                    // Sum all flows for the latest date and convert to millions
                    this.flowMeter.daily_flow = latestFlows.reduce((sum, flow) => sum + flow.flow_usd, 0) / 1000000;
                    
                    console.log(`📊 Daily Flow Calculation:`, {
                        latestDate,
                        flowCount: latestFlows.length,
                        totalFlowUSD: latestFlows.reduce((sum, flow) => sum + flow.flow_usd, 0),
                        dailyFlowM: this.flowMeter.daily_flow
                    });
                } else {
                    this.flowMeter.daily_flow = 0;
                }
            }
        },

        // Process ETF summary data
        processETFSummary(response) {
            if (response && response.data) {
                this.etfSummary = response.data;
            }
        },

        // Process premium/discount data
        processPremiumDiscount(response) {
            if (response && Array.isArray(response.data)) {
                this.premiumDiscount = response.data.map(item => ({
                    ...item,
                    id: `${item.date}-${item.ticker}`,
                    nav: parseFloat(item.nav) || 0,
                    market_price: parseFloat(item.market_price) || 0,
                    premium_discount_bps: parseFloat(item.premium_discount_bps) || 0,
                    // Calculate basis points if not provided
                    calculated_bps: item.premium_discount_bps || this.calculateBasisPoints(item.nav, item.market_price),
                    // Threshold detection
                    is_overvalued: (parseFloat(item.premium_discount_bps) || 0) > 50,
                    is_undervalued: (parseFloat(item.premium_discount_bps) || 0) < -50,
                    is_alert_threshold: Math.abs(parseFloat(item.premium_discount_bps) || 0) >= 50
                }));
            }
        },

        // Process creations/redemptions data
        processCreationsRedemptions(response) {
            if (response && Array.isArray(response.data)) {
                this.creationsRedemptions = response.data.map(item => ({
                    ...item,
                    id: `${item.date}-${item.ticker}`,
                    creations_shares: parseFloat(item.creations_shares) || 0,
                    redemptions_shares: parseFloat(item.redemptions_shares) || 0,
                    net_creation: (parseFloat(item.creations_shares) || 0) - (parseFloat(item.redemptions_shares) || 0),
                    // Calculate additional metrics
                    total_activity: (parseFloat(item.creations_shares) || 0) + (parseFloat(item.redemptions_shares) || 0),
                    creation_ratio: this.calculateCreationRatio(item.creations_shares, item.redemptions_shares),
                    // Badge logic
                    badge_type: this.getCreationBadgeType(item.creations_shares, item.redemptions_shares),
                    badge_class: this.getCreationBadgeClass(item.creations_shares, item.redemptions_shares),
                    badge_text: this.getCreationBadgeText(item.creations_shares, item.redemptions_shares)
                }));

                // Sort by date (most recent first)
                this.creationsRedemptions.sort((a, b) => new Date(b.date) - new Date(a.date));
            }
        },

        // Process CME open interest data
        processCMEOpenInterest(response) {
            if (response && Array.isArray(response.data)) {
                this.cmeOpenInterest = response.data.map(item => ({
                    ...item,
                    oi_usd: parseFloat(item.oi_usd) || 0,
                    oi_contracts: parseFloat(item.oi_contracts) || 0
                }));
            }
        },

        // Process COT data
        processCOTData(response) {
            if (response && Array.isArray(response.data)) {
                this.cotData = response.data.map(item => ({
                    ...item,
                    id: `${item.week}-${item.report_group}`,
                    long_contracts: parseFloat(item.long_contracts) || 0,
                    short_contracts: parseFloat(item.short_contracts) || 0,
                    net: (parseFloat(item.long_contracts) || 0) - (parseFloat(item.short_contracts) || 0)
                }));
            }
        },

        // Process CME summary data
        processCMESummary(response) {
            if (response && response.data) {
                this.cmeSummary = response.data;
            }
        },

        // Update overview metrics from processed data
        updateOverviewMetrics() {
            // Calculate net inflow 24h from latest flows
            const latestDate = this.etfFlows[0]?.date;
            const latestFlows = this.etfFlows.filter(f => f.date === latestDate);
            this.overview.net_inflow_24h = latestFlows.reduce((sum, flow) => sum + flow.flow_usd, 0) / 1000000; // Millions

            // Calculate total AUM
            const latestAUM = latestFlows.reduce((sum, flow) => sum + flow.aum_usd, 0) / 1000000000; // Billions
            this.overview.total_aum = latestAUM;

            // Find top issuer by flow
            const issuerFlows = {};
            latestFlows.forEach(flow => {
                issuerFlows[flow.issuer] = (issuerFlows[flow.issuer] || 0) + flow.flow_usd;
            });

            const topIssuer = Object.entries(issuerFlows).reduce((max, [issuer, flow]) =>
                flow > max.flow ? { issuer, flow } : max, { issuer: '', flow: 0 });

            this.overview.top_issuer = topIssuer.issuer;
            this.overview.top_issuer_flow = topIssuer.flow / 1000000; // Millions

            // Calculate total shares and BTC equivalent (approximate)
            this.overview.total_shares = latestFlows.reduce((sum, flow) => sum + flow.shares_outstanding, 0);
            this.overview.btc_equivalent = Math.round(this.overview.total_shares / 1000); // Rough estimate

            // Calculate 24h change (if we have previous day data)
            const previousDate = this.etfFlows.find(f => f.date !== latestDate)?.date;
            if (previousDate) {
                const previousFlows = this.etfFlows.filter(f => f.date === previousDate);
                const previousInflow = previousFlows.reduce((sum, flow) => sum + flow.flow_usd, 0) / 1000000;
                this.overview.change_24h = previousInflow !== 0 ?
                    ((this.overview.net_inflow_24h - previousInflow) / Math.abs(previousInflow)) * 100 : 0;
            }
        },

        // Update URL with current filters
        updateURL() {
            if (window.history && window.history.pushState) {
                const url = new URL(window.location);
                url.searchParams.set("period", this.selectedPeriod);
                // Keep asset for reference but it's fixed to BTC
                url.searchParams.set("asset", this.selectedAsset);
                window.history.pushState({}, "", url);
            }
        },

        // Refresh all data manually
        refreshAll() {
            console.log("🔄 Manual refresh triggered");

            // Use setTimeout to prevent circular reference issues
            setTimeout(() => {
                this.loadAllData().catch((e) => {
                    console.warn("Manual refresh failed:", e);
                });
            }, 10);

            this.resetAutoRefreshTimer();
        },

        // Setup auto-refresh timer
        setupAutoRefresh() {
            this.autoRefreshTimer = setInterval(() => {
                if (!document.hidden) {
                    console.log("🔄 Auto-refresh triggered");
                    // Use setTimeout to prevent circular reference issues
                    setTimeout(() => {
                        this.loadAllData().catch((e) => {
                            console.warn("Auto-refresh failed:", e);
                        });
                    }, 10);
                }
            }, this.autoRefreshInterval);
        },

        // Pause auto-refresh (when tab is hidden)
        pauseAutoRefresh() {
            if (this.autoRefreshTimer) {
                clearInterval(this.autoRefreshTimer);
                this.autoRefreshTimer = null;
                console.log("⏸️ Auto-refresh paused");
            }
        },

        // Resume auto-refresh (when tab becomes visible)
        resumeAutoRefresh() {
            if (!this.autoRefreshTimer) {
                this.setupAutoRefresh();
                // Immediately refresh data when tab becomes active
                setTimeout(() => {
                    this.loadAllData().catch((e) => {
                        console.warn("Resume refresh failed:", e);
                    });
                }, 10);
                console.log("▶️ Auto-refresh resumed");
            }
        },

        // Reset auto-refresh timer
        resetAutoRefreshTimer() {
            this.pauseAutoRefresh();
            this.setupAutoRefresh();
        },

        // API Helper: Fetch with error handling
        async fetchAPI(endpoint, params = {}) {
            const queryString = new URLSearchParams(params).toString();
            const baseMeta = document.querySelector('meta[name="api-base-url"]');
            const configuredBase = (baseMeta?.content || "").trim();

            let url = `${endpoint}?${queryString}`; // default relative
            if (configuredBase) {
                const normalizedBase = configuredBase.endsWith("/")
                    ? configuredBase.slice(0, -1)
                    : configuredBase;
                url = `${normalizedBase}${endpoint}?${queryString}`;
            }

            try {
                console.log("📡 Fetching:", endpoint, params);
                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                const itemCount = Array.isArray(data?.data) ? data.data.length : "summary";
                console.log("✅ Received:", endpoint, itemCount, typeof itemCount === "number" ? "items" : "");

                // Clear any previous errors for this endpoint
                delete this.errors[endpoint];

                return data;
            } catch (error) {
                console.error("❌ API Error:", endpoint, error);
                this.errors[endpoint] = error.message;
                throw error;
            }
        },

        // Handle errors with user feedback
        handleError(error, component) {
            console.error(`❌ Error in ${component}:`, error);

            // Store error for UI display
            this.errors[component] = error.message || 'An error occurred';

            // You could add toast notifications here
            // this.showErrorToast(`Failed to load ${component} data`);
        },

        // Log dashboard status
        logDashboardStatus() {
            console.group("📊 ETF Dashboard Status");
            console.log("Asset:", this.selectedAsset);
            console.log("ETF Flows:", this.etfFlows.length, "records");
            console.log("Premium/Discount:", this.premiumDiscount.length, "records");
            console.log("COT Data:", this.cotData.length, "records");
            console.log("Errors:", Object.keys(this.errors).length);
            const baseMeta = document.querySelector('meta[name="api-base-url"]');
            const baseUrl = (baseMeta?.content || "").trim() || "(relative)";
            console.log("API Base:", baseUrl);
            console.groupEnd();
        },

        // Test API connectivity
        async testAPIConnectivity() {
            try {
                console.log("🔍 Testing API connectivity...");
                const response = await this.fetchAPI(this.API_ENDPOINTS.spotSummary, {
                    symbol: this.selectedAsset,
                    limit: 1
                });
                console.log("✅ API connectivity test successful:", response);
            } catch (error) {
                console.warn("⚠️ API connectivity test failed:", error.message);
                console.log("📝 This is expected if the API is not accessible from this environment");
            }
        },

        // Initialize ETF flow chart
        initETFFlowChart() {
            const ctx = document.getElementById('etfFlowChart');
            if (!ctx) {
                console.warn("ETF flow chart canvas not found");
                return;
            }

            // Destroy existing chart if it exists
            if (this.etfFlowChart) {
                this.etfFlowChart.destroy();
            }

            this.etfFlowChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: []
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 10,
                            bottom: 10
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        title: {
                            display: false
                        },
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                padding: 15,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const value = context.parsed.y;
                                    const sign = value >= 0 ? '+' : '';
                                    return `${context.dataset.label}: ${sign}$${Math.abs(value).toFixed(1)}M`;
                                },
                                footer: function (tooltipItems) {
                                    // Calculate total flow for this date
                                    let total = 0;
                                    tooltipItems.forEach(item => {
                                        total += item.parsed.y;
                                    });
                                    const sign = total >= 0 ? '+' : '';
                                    return `Total: ${sign}$${Math.abs(total).toFixed(1)}M`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Date',
                                font: {
                                    size: 12
                                }
                            },
                            ticks: {
                                maxRotation: 45,
                                font: {
                                    size: 10
                                }
                            },
                            stacked: true // Enable stacking on x-axis
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Flow (USD Millions)',
                                font: {
                                    size: 12
                                }
                            },
                            stacked: true, // Enable stacking on y-axis
                            grid: {
                                color: function (context) {
                                    // Highlight zero line
                                    if (context.tick.value === 0) {
                                        return '#374151';
                                    }
                                    return '#e5e7eb';
                                },
                                lineWidth: function (context) {
                                    if (context.tick.value === 0) {
                                        return 2;
                                    }
                                    return 1;
                                }
                            },
                            ticks: {
                                callback: function (value) {
                                    // Format y-axis labels
                                    if (value >= 0) {
                                        return `$${value}M`;
                                    } else {
                                        return `-$${Math.abs(value)}M`;
                                    }
                                },
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });

            console.log("✅ ETF flow chart initialized");

            // Add resize observer to handle container size changes
            if (window.ResizeObserver) {
                const resizeObserver = new ResizeObserver(() => {
                    if (this.etfFlowChart) {
                        this.etfFlowChart.resize();
                    }
                });
                resizeObserver.observe(ctx.parentElement);
            }
        },

        // Initialize premium/discount chart
        initPremiumDiscountChart() {
            const ctx = document.getElementById('premiumDiscountChart');
            if (!ctx) {
                console.warn("Premium/discount chart canvas not found");
                return;
            }

            // Destroy existing chart if it exists
            if (this.premiumDiscountChart) {
                this.premiumDiscountChart.destroy();
            }

            this.premiumDiscountChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: []
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 10,
                            bottom: 10
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        title: {
                            display: false
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const value = context.parsed.y;
                                    const sign = value >= 0 ? '+' : '';
                                    return `${context.dataset.label}: ${sign}${value.toFixed(1)}bps`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Premium/Discount (bps)'
                            },
                            grid: {
                                color: function (context) {
                                    // Highlight ±50bps threshold lines
                                    if (context.tick.value === 50 || context.tick.value === -50) {
                                        return '#ef4444';
                                    }
                                    if (context.tick.value === 0) {
                                        return '#6b7280';
                                    }
                                    return '#e5e7eb';
                                }
                            }
                        }
                    },
                    // Add threshold lines at ±50bps
                    plugins: [{
                        id: 'thresholdLines',
                        beforeDraw: (chart) => {
                            const ctx = chart.ctx;
                            const chartArea = chart.chartArea;
                            const yScale = chart.scales.y;

                            // Draw +50bps threshold line
                            const y50 = yScale.getPixelForValue(50);
                            if (y50 >= chartArea.top && y50 <= chartArea.bottom) {
                                ctx.save();
                                ctx.strokeStyle = '#ef4444';
                                ctx.lineWidth = 1;
                                ctx.setLineDash([5, 5]);
                                ctx.beginPath();
                                ctx.moveTo(chartArea.left, y50);
                                ctx.lineTo(chartArea.right, y50);
                                ctx.stroke();
                                ctx.restore();
                            }

                            // Draw -50bps threshold line
                            const yMinus50 = yScale.getPixelForValue(-50);
                            if (yMinus50 >= chartArea.top && yMinus50 <= chartArea.bottom) {
                                ctx.save();
                                ctx.strokeStyle = '#ef4444';
                                ctx.lineWidth = 1;
                                ctx.setLineDash([5, 5]);
                                ctx.beginPath();
                                ctx.moveTo(chartArea.left, yMinus50);
                                ctx.lineTo(chartArea.right, yMinus50);
                                ctx.stroke();
                                ctx.restore();
                            }
                        }
                    }]
                }
            });

            console.log("✅ Premium/discount chart initialized");

            // Add resize observer to handle container size changes
            if (window.ResizeObserver) {
                const resizeObserver = new ResizeObserver(() => {
                    if (this.premiumDiscountChart) {
                        this.premiumDiscountChart.resize();
                    }
                });
                resizeObserver.observe(ctx.parentElement);
            }
        },

        // Test API connectivity
        async testAPIConnectivity() {
            try {
                console.log("🔍 Testing API connectivity...");
                const response = await this.fetchAPI(this.API_ENDPOINTS.spotSummary, {
                    symbol: this.selectedAsset,
                    limit: 1
                });
                console.log("✅ API connectivity test successful:", response);
            } catch (error) {
                console.warn("⚠️ API connectivity test failed:", error.message);
                console.log("📝 This is expected if the API is not accessible from this environment");
            }
        },

        // Render charts with data (data-driven pattern)
        renderCharts() {
            console.log("📊 Rendering ETF charts with data...");

            // Render ETF flow chart
            this.renderETFFlowChart();

            // Render premium/discount chart
            this.renderPremiumDiscountChart();

            // Render CME open interest chart
            this.renderCMEOpenInterestChart();

            // Render COT comparison chart
            this.renderCOTComparisonChart();
        },

        // Render ETF flow chart with data (data-driven pattern)
        renderETFFlowChart() {
            const canvas = document.getElementById('etfFlowChart');
            if (!canvas) {
                console.warn("❌ ETF flow chart canvas not found");
                return;
            }

            // Destroy existing chart to prevent memory leaks
            if (this.etfFlowChart) {
                this.etfFlowChart.destroy();
                this.etfFlowChart = null;
            }

            // Check if we have data to render
            if (!this.etfFlows || this.etfFlows.length === 0) {
                console.warn("❌ No ETF flow data to render");
                return;
            }

            const chartData = this.transformETFFlowChartData();

            // Debug logging
            console.log("📊 ETF Flow Chart Data:", {
                labels: chartData.labels.length,
                datasets: chartData.datasets.length,
                issuers: chartData.datasets.map(d => d.label)
            });

            // Create chart with actual data
            const ctx = canvas.getContext('2d');
            this.etfFlowChart = new Chart(ctx, {
                type: 'bar',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 10,
                            bottom: 10
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        title: {
                            display: false
                        },
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                padding: 15,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const value = context.parsed.y;
                                    const sign = value >= 0 ? '+' : '';
                                    return `${context.dataset.label}: ${sign}$${Math.abs(value).toFixed(1)}M`;
                                },
                                footer: function (tooltipItems) {
                                    // Calculate total flow for this date
                                    let total = 0;
                                    tooltipItems.forEach(item => {
                                        total += item.parsed.y;
                                    });
                                    const sign = total >= 0 ? '+' : '';
                                    return `Net Total: ${sign}$${Math.abs(total).toFixed(1)}M`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Date',
                                font: {
                                    size: 12
                                }
                            },
                            ticks: {
                                maxRotation: 45,
                                font: {
                                    size: 10
                                }
                            },
                            stacked: true
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Flow (USD Millions)',
                                font: {
                                    size: 12
                                }
                            },
                            stacked: true,
                            grid: {
                                color: function (context) {
                                    if (context.tick.value === 0) {
                                        return '#374151';
                                    }
                                    return '#e5e7eb';
                                },
                                lineWidth: function (context) {
                                    if (context.tick.value === 0) {
                                        return 2;
                                    }
                                    return 1;
                                }
                            },
                            ticks: {
                                callback: function (value) {
                                    if (value >= 0) {
                                        return `$${value}M`;
                                    } else {
                                        return `-$${Math.abs(value)}M`;
                                    }
                                },
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });

            console.log("✅ ETF flow chart rendered with", chartData.datasets.length, "datasets");
        },

        // Render premium/discount chart with data (data-driven pattern)
        renderPremiumDiscountChart() {
            const canvas = document.getElementById('premiumDiscountChart');
            if (!canvas) {
                console.warn("❌ Premium/discount chart canvas not found");
                return;
            }

            // Destroy existing chart to prevent memory leaks
            if (this.premiumDiscountChart) {
                this.premiumDiscountChart.destroy();
                this.premiumDiscountChart = null;
            }

            // Check if we have data to render
            if (!this.premiumDiscount || this.premiumDiscount.length === 0) {
                console.warn("❌ No premium/discount data to render");
                return;
            }

            const chartData = this.transformPremiumDiscountChartData();

            // Debug logging
            console.log("📊 Premium/Discount Chart Data:", {
                labels: chartData.labels.length,
                datasets: chartData.datasets.length,
                tickers: chartData.datasets.map(d => d.label),
                rawDataCount: this.premiumDiscount.length
            });

            // Create chart with actual data
            const ctx = canvas.getContext('2d');
            this.premiumDiscountChart = new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 10,
                            bottom: 10
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        title: {
                            display: false
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const value = context.parsed.y;
                                    const sign = value >= 0 ? '+' : '';
                                    return `${context.dataset.label}: ${sign}${value.toFixed(1)}bps`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Premium/Discount (bps)'
                            },
                            grid: {
                                color: function (context) {
                                    if (context.tick.value === 50 || context.tick.value === -50) {
                                        return '#ef4444';
                                    }
                                    if (context.tick.value === 0) {
                                        return '#6b7280';
                                    }
                                    return '#e5e7eb';
                                }
                            }
                        }
                    },
                    plugins: [{
                        id: 'thresholdLines',
                        beforeDraw: (chart) => {
                            const ctx = chart.ctx;
                            const chartArea = chart.chartArea;
                            const yScale = chart.scales.y;

                            // Draw +50bps threshold line
                            const y50 = yScale.getPixelForValue(50);
                            if (y50 >= chartArea.top && y50 <= chartArea.bottom) {
                                ctx.save();
                                ctx.strokeStyle = '#ef4444';
                                ctx.lineWidth = 1;
                                ctx.setLineDash([5, 5]);
                                ctx.beginPath();
                                ctx.moveTo(chartArea.left, y50);
                                ctx.lineTo(chartArea.right, y50);
                                ctx.stroke();
                                ctx.restore();
                            }

                            // Draw -50bps threshold line
                            const yMinus50 = yScale.getPixelForValue(-50);
                            if (yMinus50 >= chartArea.top && yMinus50 <= chartArea.bottom) {
                                ctx.save();
                                ctx.strokeStyle = '#ef4444';
                                ctx.lineWidth = 1;
                                ctx.setLineDash([5, 5]);
                                ctx.beginPath();
                                ctx.moveTo(chartArea.left, yMinus50);
                                ctx.lineTo(chartArea.right, yMinus50);
                                ctx.stroke();
                                ctx.restore();
                            }
                        }
                    }]
                }
            });

            console.log("✅ Premium/discount chart rendered with", chartData.datasets.length, "datasets");
        },

        // Render CME open interest chart with data (data-driven pattern)
        renderCMEOpenInterestChart() {
            const canvas = document.getElementById('cmeOiChart');
            if (!canvas) {
                console.warn("❌ CME open interest chart canvas not found");
                return;
            }

            // Destroy existing chart to prevent memory leaks
            if (this.cmeOiChart) {
                this.cmeOiChart.destroy();
                this.cmeOiChart = null;
            }

            // Check if we have data to render
            if (!this.cmeOpenInterest || this.cmeOpenInterest.length === 0) {
                console.warn("❌ No CME open interest data to render");
                return;
            }

            const chartData = this.transformCMEOpenInterestChartData();

            // Debug logging
            console.log("📊 CME Open Interest Chart Data:", {
                labels: chartData.labels.length,
                datasets: chartData.datasets.length,
                dataPoints: this.cmeOpenInterest.length
            });

            // Create chart with actual data
            const ctx = canvas.getContext('2d');
            this.cmeOiChart = new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 10,
                            bottom: 10
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        title: {
                            display: false
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const value = context.parsed.y;
                                    if (context.dataset.label.includes('USD')) {
                                        return `${context.dataset.label}: $${value.toFixed(0)}M`;
                                    } else {
                                        return `${context.dataset.label}: ${value.toLocaleString()} contracts`;
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Open Interest (USD Millions)'
                            },
                            ticks: {
                                callback: function (value) {
                                    return '$' + value.toFixed(0) + 'M';
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Contracts'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                            ticks: {
                                callback: function (value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            console.log("✅ CME open interest chart rendered with", chartData.datasets.length, "datasets");
        },

        // Render COT comparison chart with data (data-driven pattern)
        renderCOTComparisonChart() {
            const canvas = document.getElementById('cotComparisonChart');
            if (!canvas) {
                console.warn("❌ COT comparison chart canvas not found");
                return;
            }

            // Destroy existing chart to prevent memory leaks
            if (this.cotComparisonChart) {
                this.cotComparisonChart.destroy();
                this.cotComparisonChart = null;
            }

            // Check if we have data to render
            if (!this.cotData || this.cotData.length === 0) {
                console.warn("❌ No COT data to render");
                return;
            }

            const chartData = this.transformCOTComparisonChartData();

            // Debug logging
            console.log("📊 COT Comparison Chart Data:", {
                labels: chartData.labels.length,
                datasets: chartData.datasets.length,
                reportGroups: [...new Set(this.cotData.map(item => item.report_group))]
            });

            // Create chart with actual data
            const ctx = canvas.getContext('2d');
            this.cotComparisonChart = new Chart(ctx, {
                type: 'bar',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 10,
                            bottom: 10
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        title: {
                            display: false
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const value = context.parsed.y;
                                    const sign = value >= 0 ? '+' : '';
                                    return `${context.dataset.label}: ${sign}${value.toLocaleString()} contracts`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Report Group'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Net Position (Contracts)'
                            },
                            grid: {
                                color: function (context) {
                                    if (context.tick.value === 0) {
                                        return '#374151';
                                    }
                                    return '#e5e7eb';
                                },
                                lineWidth: function (context) {
                                    if (context.tick.value === 0) {
                                        return 2;
                                    }
                                    return 1;
                                }
                            },
                            ticks: {
                                callback: function (value) {
                                    const sign = value >= 0 ? '+' : '';
                                    return sign + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            console.log("✅ COT comparison chart rendered with", chartData.datasets.length, "datasets");
        },

        // === ETF FLOW CHART FUNCTIONS ===

        // Helper function to adjust color opacity
        adjustColorOpacity(color, opacity) {
            // Convert hex to rgba with specified opacity
            if (color.startsWith('#')) {
                const hex = color.slice(1);
                const r = parseInt(hex.substr(0, 2), 16);
                const g = parseInt(hex.substr(2, 2), 16);
                const b = parseInt(hex.substr(4, 2), 16);
                return `rgba(${r}, ${g}, ${b}, ${opacity})`;
            }
            return color;
        },

        // Transform ETF flow data for Chart.js (per issuer stacked bars)
        transformETFFlowChartData() {
            if (!this.etfFlows || this.etfFlows.length === 0) {
                console.warn("📊 No ETF flow data available for chart");
                return {
                    labels: [],
                    datasets: []
                };
            }

            // Debug: Log sample data structure
            console.log("📊 Sample ETF flow data:", this.etfFlows.slice(0, 3));

            // Group by date and issuer
            const dateIssuerGroups = {};
            this.etfFlows.forEach(flow => {
                if (!dateIssuerGroups[flow.date]) {
                    dateIssuerGroups[flow.date] = {};
                }
                if (!dateIssuerGroups[flow.date][flow.issuer]) {
                    dateIssuerGroups[flow.date][flow.issuer] = 0;
                }
                dateIssuerGroups[flow.date][flow.issuer] += flow.flow_usd / 1000000; // Convert to millions
            });

            // Sort dates chronologically
            const sortedDates = Object.keys(dateIssuerGroups).sort((a, b) => new Date(a) - new Date(b));

            // Get all unique issuers and sort them for consistent ordering
            const allIssuers = [...new Set(this.etfFlows.map(flow => flow.issuer))].sort();

            // Debug: Log chart data structure
            console.log("📊 ETF Flow Chart Data:", {
                dateCount: sortedDates.length,
                issuerCount: allIssuers.length,
                issuers: allIssuers,
                dateRange: sortedDates.length > 0 ? `${sortedDates[0]} to ${sortedDates[sortedDates.length - 1]}` : 'No dates'
            });

            // Create labels with better formatting
            const labels = sortedDates.map(date => {
                const d = new Date(date);
                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });

            // Define colors for each issuer (matching real ETF providers)
            const issuerColors = {
                'BlackRock': '#3b82f6',      // Blue (iShares)
                'Fidelity': '#f59e0b',       // Orange (FBTC)
                'Grayscale': '#8b5cf6',      // Purple (GBTC)
                'VanEck': '#10b981',         // Green (HODL)
                'Bitwise': '#ef4444',        // Red (BITB)
                'Invesco': '#06b6d4',        // Cyan (BTCO)
                'Valkyrie': '#f97316',       // Orange (BRRR)
                'ProShares': '#84cc16',      // Lime (BITO)
                'ARK': '#ec4899',            // Pink (ARKB)
                'WisdomTree': '#a855f7',     // Purple (BTCW)
                'Franklin': '#14b8a6',       // Teal (EZBC)
                'Hashdex': '#f43f5e'         // Rose (DEFI)
            };

            // Create datasets for each issuer
            const datasets = allIssuers.map(issuer => {
                const data = sortedDates.map(date => {
                    return dateIssuerGroups[date][issuer] || 0;
                });

                // Calculate total flow for this issuer to determine visibility
                const totalFlow = data.reduce((sum, val) => sum + Math.abs(val), 0);

                return {
                    label: issuer,
                    data: data,
                    backgroundColor: issuerColors[issuer] || '#94a3b8',
                    borderColor: issuerColors[issuer] || '#64748b',
                    borderWidth: 0.5,
                    stack: 'flows', // Stack all flows to show cumulative effect
                    hidden: totalFlow < 10 // Hide issuers with very small flows
                };
            });

            // Sort datasets by total absolute flow (largest first)
            datasets.sort((a, b) => {
                const totalA = a.data.reduce((sum, val) => sum + Math.abs(val), 0);
                const totalB = b.data.reduce((sum, val) => sum + Math.abs(val), 0);
                return totalB - totalA;
            });

            return {
                labels: labels,
                datasets: datasets
            };
        },

        // === CME OPEN INTEREST CHART FUNCTIONS ===

        // Transform CME open interest data for Chart.js
        transformCMEOpenInterestChartData() {
            if (!this.cmeOpenInterest || this.cmeOpenInterest.length === 0) {
                console.warn("📊 No CME open interest data available for chart");
                return {
                    labels: [],
                    datasets: []
                };
            }

            // Sort by date
            const sortedData = [...this.cmeOpenInterest].sort((a, b) => new Date(a.date) - new Date(b.date));

            // Create labels
            const labels = sortedData.map(item =>
                new Date(item.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
            );

            // Create datasets for USD value and contracts
            const datasets = [
                {
                    label: 'Open Interest (USD)',
                    data: sortedData.map(item => item.oi_usd / 1000000), // Convert to millions
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.1,
                    yAxisID: 'y'
                },
                {
                    label: 'Open Interest (Contracts)',
                    data: sortedData.map(item => item.oi_contracts),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.1,
                    yAxisID: 'y1'
                }
            ];

            return { labels, datasets };
        },

        // === COT COMPARISON CHART FUNCTIONS ===

        // Transform COT data for Chart.js comparison chart
        transformCOTComparisonChartData() {
            if (!this.cotData || this.cotData.length === 0) {
                console.warn("📊 No COT data available for chart");
                return {
                    labels: [],
                    datasets: []
                };
            }

            // Get latest week data
            const latestWeek = this.cotData.reduce((latest, item) => {
                return new Date(item.week) > new Date(latest) ? item.week : latest;
            }, this.cotData[0].week);

            const latestData = this.cotData.filter(item => item.week === latestWeek);

            // Create labels (report groups)
            const labels = latestData.map(item => item.report_group);

            // Create datasets for long, short, and net positions
            const datasets = [
                {
                    label: 'Long Positions',
                    data: latestData.map(item => item.long_contracts),
                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                    borderColor: '#22c55e',
                    borderWidth: 1
                },
                {
                    label: 'Short Positions',
                    data: latestData.map(item => -item.short_contracts), // Negative for visual separation
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    borderColor: '#ef4444',
                    borderWidth: 1
                },
                {
                    label: 'Net Position',
                    data: latestData.map(item => item.net),
                    backgroundColor: latestData.map(item =>
                        item.net >= 0 ? 'rgba(59, 130, 246, 0.8)' : 'rgba(245, 158, 11, 0.8)'
                    ),
                    borderColor: latestData.map(item =>
                        item.net >= 0 ? '#3b82f6' : '#f59e0b'
                    ),
                    borderWidth: 2
                }
            ];

            return { labels, datasets };
        },

        // Get COT institutional sentiment analysis
        getCOTSentimentAnalysis() {
            if (!this.cotData || this.cotData.length === 0) {
                return {
                    overall_sentiment: 'neutral',
                    sentiment_score: 0,
                    key_insights: [],
                    bullish_signals: 0,
                    bearish_signals: 0
                };
            }

            // Get latest week data
            const latestWeek = this.cotData.reduce((latest, item) => {
                return new Date(item.week) > new Date(latest) ? item.week : latest;
            }, this.cotData[0].week);

            const latestData = this.cotData.filter(item => item.week === latestWeek);

            let bullishSignals = 0;
            let bearishSignals = 0;
            const insights = [];

            latestData.forEach(item => {
                const netPosition = item.net;
                const reportGroup = item.report_group;

                // Asset Managers (institutional funds) - bullish when net long
                if (reportGroup.includes('Asset Manager') || reportGroup.includes('Fund')) {
                    if (netPosition > 0) {
                        bullishSignals++;
                        insights.push(`${reportGroup} net long: +${netPosition.toLocaleString()} contracts`);
                    } else if (netPosition < 0) {
                        bearishSignals++;
                        insights.push(`${reportGroup} net short: ${netPosition.toLocaleString()} contracts`);
                    }
                }

                // Dealers - contrarian indicator (bearish when net long)
                if (reportGroup.includes('Dealer')) {
                    if (netPosition > 0) {
                        bearishSignals++;
                        insights.push(`${reportGroup} net long (contrarian bearish): +${netPosition.toLocaleString()}`);
                    } else if (netPosition < 0) {
                        bullishSignals++;
                        insights.push(`${reportGroup} net short (contrarian bullish): ${netPosition.toLocaleString()}`);
                    }
                }

                // Other reportable traders
                if (reportGroup.includes('Other') && !reportGroup.includes('Non-Reportable')) {
                    if (Math.abs(netPosition) > 1000) { // Significant position
                        if (netPosition > 0) {
                            bullishSignals++;
                        } else {
                            bearishSignals++;
                        }
                    }
                }
            });

            // Calculate overall sentiment
            const sentimentScore = bullishSignals - bearishSignals;
            let overallSentiment = 'neutral';

            if (sentimentScore >= 2) {
                overallSentiment = 'bullish';
            } else if (sentimentScore <= -2) {
                overallSentiment = 'bearish';
            } else if (sentimentScore === 1) {
                overallSentiment = 'slightly_bullish';
            } else if (sentimentScore === -1) {
                overallSentiment = 'slightly_bearish';
            }

            return {
                overall_sentiment: overallSentiment,
                sentiment_score: sentimentScore,
                key_insights: insights.slice(0, 3), // Top 3 insights
                bullish_signals: bullishSignals,
                bearish_signals: bearishSignals,
                latest_week: latestWeek
            };
        },

        // Get COT sentiment badge class
        getCOTSentimentBadgeClass() {
            const sentiment = this.getCOTSentimentAnalysis();
            switch (sentiment.overall_sentiment) {
                case 'bullish':
                    return 'text-bg-success';
                case 'slightly_bullish':
                    return 'text-bg-success-subtle text-success';
                case 'bearish':
                    return 'text-bg-danger';
                case 'slightly_bearish':
                    return 'text-bg-danger-subtle text-danger';
                default:
                    return 'text-bg-secondary';
            }
        },

        // Get COT sentiment label
        getCOTSentimentLabel() {
            const sentiment = this.getCOTSentimentAnalysis();
            switch (sentiment.overall_sentiment) {
                case 'bullish':
                    return 'Bullish Institutional';
                case 'slightly_bullish':
                    return 'Slightly Bullish';
                case 'bearish':
                    return 'Bearish Institutional';
                case 'slightly_bearish':
                    return 'Slightly Bearish';
                default:
                    return 'Neutral';
            }
        },

        // === CREATIONS/REDEMPTIONS CALCULATION FUNCTIONS ===

        // Calculate creation ratio (creations / total activity)
        calculateCreationRatio(creations, redemptions) {
            const total = (parseFloat(creations) || 0) + (parseFloat(redemptions) || 0);
            if (total === 0) return 0;
            return ((parseFloat(creations) || 0) / total) * 100;
        },

        // Get badge type based on net creation/redemption
        getCreationBadgeType(creations, redemptions) {
            const net = (parseFloat(creations) || 0) - (parseFloat(redemptions) || 0);
            if (net > 0) return 'creation';
            if (net < 0) return 'redemption';
            return 'neutral';
        },

        // Get badge CSS class based on creation/redemption activity
        getCreationBadgeClass(creations, redemptions) {
            const net = (parseFloat(creations) || 0) - (parseFloat(redemptions) || 0);
            const absNet = Math.abs(net);

            if (net > 0) {
                // Net creation - green variants
                if (absNet > 1000000) return 'text-bg-success'; // Strong creation
                return 'text-bg-success-subtle text-success'; // Moderate creation
            } else if (net < 0) {
                // Net redemption - red variants
                if (absNet > 1000000) return 'text-bg-danger'; // Strong redemption
                return 'text-bg-danger-subtle text-danger'; // Moderate redemption
            }

            return 'text-bg-secondary'; // Neutral
        },

        // Get badge text based on creation/redemption activity
        getCreationBadgeText(creations, redemptions) {
            const net = (parseFloat(creations) || 0) - (parseFloat(redemptions) || 0);
            const absNet = Math.abs(net);

            if (net > 0) {
                if (absNet > 1000000) return 'Strong Creation';
                return 'Net Creation';
            } else if (net < 0) {
                if (absNet > 1000000) return 'Strong Redemption';
                return 'Net Redemption';
            }

            return 'Balanced';
        },

        // Calculate aggregate creation/redemption metrics
        getCreationRedemptionSummary() {
            if (!this.creationsRedemptions || this.creationsRedemptions.length === 0) {
                return {
                    total_net_creation: 0,
                    total_creations: 0,
                    total_redemptions: 0,
                    creation_ratio: 0,
                    dominant_trend: 'neutral'
                };
            }

            const totals = this.creationsRedemptions.reduce((acc, item) => {
                acc.creations += item.creations_shares;
                acc.redemptions += item.redemptions_shares;
                return acc;
            }, { creations: 0, redemptions: 0 });

            const netCreation = totals.creations - totals.redemptions;
            const totalActivity = totals.creations + totals.redemptions;
            const creationRatio = totalActivity > 0 ? (totals.creations / totalActivity) * 100 : 0;

            let dominantTrend = 'neutral';
            if (netCreation > 0 && creationRatio > 60) dominantTrend = 'creation';
            else if (netCreation < 0 && creationRatio < 40) dominantTrend = 'redemption';

            return {
                total_net_creation: netCreation,
                total_creations: totals.creations,
                total_redemptions: totals.redemptions,
                creation_ratio: creationRatio,
                dominant_trend: dominantTrend
            };
        },

        // === PREMIUM/DISCOUNT CALCULATION FUNCTIONS ===

        // Calculate basis points from NAV and market price
        calculateBasisPoints(nav, marketPrice) {
            if (!nav || nav === 0) return 0;
            const premium = ((marketPrice - nav) / nav) * 10000; // Convert to basis points
            return Math.round(premium * 100) / 100; // Round to 2 decimal places
        },

        // Transform premium/discount data for Chart.js
        transformPremiumDiscountChartData() {
            if (!this.premiumDiscount || this.premiumDiscount.length === 0) {
                return {
                    labels: [],
                    datasets: []
                };
            }

            // Group by ticker for multiple ETFs
            const tickerGroups = {};
            this.premiumDiscount.forEach(item => {
                if (!tickerGroups[item.ticker]) {
                    tickerGroups[item.ticker] = [];
                }
                tickerGroups[item.ticker].push(item);
            });

            // Sort each group by date
            Object.keys(tickerGroups).forEach(ticker => {
                tickerGroups[ticker].sort((a, b) => new Date(a.date) - new Date(b.date));
            });

            // Create labels from the first ticker's dates
            const firstTicker = Object.keys(tickerGroups)[0];
            const labels = tickerGroups[firstTicker]?.map(item =>
                new Date(item.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
            ) || [];

            // Create datasets for each ticker
            const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
            const datasets = Object.keys(tickerGroups).map((ticker, index) => ({
                label: ticker,
                data: tickerGroups[ticker].map(item => item.calculated_bps),
                borderColor: colors[index % colors.length],
                backgroundColor: colors[index % colors.length] + '20',
                borderWidth: 2,
                fill: false,
                tension: 0.1,
                pointBackgroundColor: tickerGroups[ticker].map(item =>
                    item.is_alert_threshold ? '#ef4444' : colors[index % colors.length]
                ),
                pointBorderColor: tickerGroups[ticker].map(item =>
                    item.is_alert_threshold ? '#ef4444' : colors[index % colors.length]
                ),
                pointRadius: tickerGroups[ticker].map(item =>
                    item.is_alert_threshold ? 6 : 4
                )
            }));

            return { labels, datasets };
        },

        // Get premium/discount alert status (simplified guidance only)
        getPremiumDiscountAlerts() {
            if (!this.premiumDiscount || this.premiumDiscount.length === 0) {
                return [];
            }

            // Check if there are any alerts (threshold breaches)
            const hasAlerts = this.premiumDiscount.some(item => item.is_alert_threshold);

            if (hasAlerts) {
                // Count premium vs discount alerts
                const premiumCount = this.premiumDiscount.filter(item => item.is_overvalued).length;
                const discountCount = this.premiumDiscount.filter(item => item.is_undervalued).length;

                // Return single guidance message based on dominant condition
                if (premiumCount > discountCount) {
                    return [{
                        type: 'guidance',
                        message: 'Some ETFs trading at premium (>50bps) - potential overvaluation risk, consider taking profits'
                    }];
                } else if (discountCount > premiumCount) {
                    return [{
                        type: 'guidance',
                        message: 'Some ETFs trading at discount (<-50bps) - potential buying opportunity, consider accumulating'
                    }];
                } else {
                    return [{
                        type: 'guidance',
                        message: 'Mixed premium/discount signals - monitor individual ETF valuations for entry/exit opportunities'
                    }];
                }
            }

            // No alerts - return empty array to show default insight
            return [];
        },

        // Calculate basis points from NAV and market price
        calculateBasisPoints(nav, marketPrice) {
            if (!nav || nav === 0) return 0;
            const premium = ((marketPrice - nav) / nav) * 10000; // Convert to basis points
            return Math.round(premium * 100) / 100; // Round to 2 decimal places
        },

        // Transform premium/discount data for Chart.js
        transformPremiumDiscountChartData() {
            if (!this.premiumDiscount || this.premiumDiscount.length === 0) {
                return {
                    labels: [],
                    datasets: []
                };
            }

            // Group by ticker for multiple ETFs
            const tickerGroups = {};
            this.premiumDiscount.forEach(item => {
                if (!tickerGroups[item.ticker]) {
                    tickerGroups[item.ticker] = [];
                }
                tickerGroups[item.ticker].push(item);
            });

            // Sort each group by date
            Object.keys(tickerGroups).forEach(ticker => {
                tickerGroups[ticker].sort((a, b) => new Date(a.date) - new Date(b.date));
            });

            // Create labels from the first ticker's dates
            const firstTicker = Object.keys(tickerGroups)[0];
            const labels = tickerGroups[firstTicker]?.map(item =>
                new Date(item.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
            ) || [];

            // Create datasets for each ticker
            const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
            const datasets = Object.keys(tickerGroups).map((ticker, index) => ({
                label: ticker,
                data: tickerGroups[ticker].map(item => item.calculated_bps),
                borderColor: colors[index % colors.length],
                backgroundColor: colors[index % colors.length] + '20',
                borderWidth: 2,
                fill: false,
                tension: 0.1,
                pointBackgroundColor: tickerGroups[ticker].map(item =>
                    item.is_alert_threshold ? '#ef4444' : colors[index % colors.length]
                ),
                pointBorderColor: tickerGroups[ticker].map(item =>
                    item.is_alert_threshold ? '#ef4444' : colors[index % colors.length]
                ),
                pointRadius: tickerGroups[ticker].map(item =>
                    item.is_alert_threshold ? 6 : 4
                )
            }));

            return { labels, datasets };
        },

        // Get premium/discount alert status
        getPremiumDiscountAlerts() {
            if (!this.premiumDiscount || this.premiumDiscount.length === 0) {
                return [];
            }

            return this.premiumDiscount
                .filter(item => item.is_alert_threshold)
                .map(item => ({
                    ticker: item.ticker,
                    date: item.date,
                    bps: item.calculated_bps,
                    type: item.is_overvalued ? 'overvalued' : 'undervalued',
                    message: item.is_overvalued
                        ? `${item.ticker} trading at ${item.calculated_bps}bps premium - potential overvaluation risk`
                        : `${item.ticker} trading at ${item.calculated_bps}bps discount - potential buying opportunity`
                }));
        },

        // === UTILITY FUNCTIONS FOR UI ===

        // Format currency values
        formatCurrency(value) {
            if (value === null || value === undefined || isNaN(value)) return "--";

            const absValue = Math.abs(value);
            if (absValue >= 1000000000) {
                return `$${(value / 1000000000).toFixed(1)}B`;
            } else if (absValue >= 1000000) {
                return `$${(value / 1000000).toFixed(1)}M`;
            } else if (absValue >= 1000) {
                return `$${(value / 1000).toFixed(1)}K`;
            } else {
                return `$${value.toFixed(2)}`;
            }
        },

        // Format flow values with proper sign and color
        formatFlowValue(value) {
            if (value === null || value === undefined || isNaN(value)) return "--";

            const absValue = Math.abs(value);
            let formatted;

            if (absValue >= 1000) {
                formatted = `${(value / 1000).toFixed(1)}B`;
            } else {
                formatted = `${value.toFixed(1)}M`;
            }

            return value >= 0 ? `+${formatted}` : formatted;
        },

        // Format numbers with abbreviations
        formatNumber(value) {
            if (value === null || value === undefined || isNaN(value)) return "--";

            const absValue = Math.abs(value);
            if (absValue >= 1000000000) {
                return `${(value / 1000000000).toFixed(1)}B`;
            } else if (absValue >= 1000000) {
                return `${(value / 1000000).toFixed(1)}M`;
            } else if (absValue >= 1000) {
                return `${(value / 1000).toFixed(1)}K`;
            } else {
                return value.toLocaleString();
            }
        },

        // Format percentage changes
        formatChange(value) {
            if (value === null || value === undefined || isNaN(value)) return "--";
            return value >= 0 ? `+${value.toFixed(1)}` : value.toFixed(1);
        },

        // Format signed numbers
        formatSigned(value) {
            if (value === null || value === undefined || isNaN(value)) return "--";
            return value >= 0 ? `+${this.formatNumber(value)}` : this.formatNumber(value);
        },

        // Format date to simple format (e.g., "Thu, 25 Sep 2025")
        formatSimpleDate(dateString) {
            if (!dateString) return "--";
            
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return "--";
                
                return date.toLocaleDateString('en-US', {
                    weekday: 'short',
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });
            } catch (error) {
                console.warn("Date formatting error:", error);
                return "--";
            }
        },
        formatSigned(value) {
            if (value === null || value === undefined || isNaN(value)) return "--";
            return value >= 0 ? `+${this.formatNumber(value)}` : this.formatNumber(value);
        },

        // Format basis points
        formatBasisPoints(value) {
            if (value === null || value === undefined || isNaN(value)) return "--";
            const sign = value >= 0 ? '+' : '';
            return `${sign}${value.toFixed(1)}bps`;
        },

        // Format shares with appropriate units
        formatShares(value) {
            if (value === null || value === undefined || isNaN(value)) return "--";

            const absValue = Math.abs(value);
            if (absValue >= 1000000) {
                return `${(value / 1000000).toFixed(1)}M`;
            } else if (absValue >= 1000) {
                return `${(value / 1000).toFixed(1)}K`;
            } else {
                return value.toLocaleString();
            }
        },

        // === FLOW METER GAUGE FUNCTIONS ===

        // Calculate flow angle for gauge (maps -500M to +500M to 180° arc)
        getFlowAngle() {
            const flow = this.flowMeter.daily_flow || 0;
            const maxFlow = 500; // ±500M range
            
            // Clamp flow to the range
            const clampedFlow = Math.max(-maxFlow, Math.min(maxFlow, flow));
            
            // Map -500M to +500M to 0° to 180° (semicircle)
            // -500M = 0°, 0M = 90°, +500M = 180°
            const angle = ((clampedFlow + maxFlow) / (2 * maxFlow)) * 180;
            
            return angle;
        },

        // Get flow badge class
        getFlowBadge() {
            const flow = this.flowMeter.daily_flow || 0;
            if (flow > 200) return 'text-bg-success';
            if (flow > 50) return 'text-bg-warning';
            if (flow < -200) return 'text-bg-danger';
            if (flow < -50) return 'text-bg-warning';
            return 'text-bg-secondary';
        },

        // Get flow label
        getFlowLabel() {
            const flow = this.flowMeter.daily_flow || 0;
            if (flow > 200) return 'Strong Inflow';
            if (flow > 50) return 'Moderate Inflow';
            if (flow > -50) return 'Neutral';
            if (flow > -200) return 'Moderate Outflow';
            return 'Strong Outflow';
        },

        // Get flow alert class
        getFlowAlert() {
            const flow = this.flowMeter.daily_flow || 0;
            if (Math.abs(flow) > 200) return 'alert alert-warning';
            return 'alert alert-info';
        },

        // Get flow title
        getFlowTitle() {
            const flow = this.flowMeter.daily_flow || 0;
            if (flow > 200) return 'Bullish Signal';
            if (flow < -200) return 'Bearish Signal';
            return 'Market Analysis';
        },

        // Get flow message
        getFlowMessage() {
            const flow = this.flowMeter.daily_flow || 0;
            if (flow > 200) {
                return 'Strong institutional accumulation detected. Bullish medium-term outlook.';
            } else if (flow > 50) {
                return 'Moderate institutional buying. Watch for continuation.';
            } else if (flow < -200) {
                return 'Heavy institutional selling. Bearish pressure building.';
            } else if (flow < -50) {
                return 'Moderate institutional selling. Monitor for reversal.';
            } else {
                return 'Balanced institutional flow. No clear directional bias.';
            }
        },

        // === DATE GENERATION FOR CHARTS ===

        // Generate date labels for charts
        generateDateLabels(days) {
            const labels = [];
            const today = new Date();

            for (let i = days - 1; i >= 0; i--) {
                const date = new Date(today);
                date.setDate(date.getDate() - i);
                labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
            }

            return labels;
        },

        // Generate mock flow data for charts (will be replaced with real data)
        generateFlowData(days, min, max) {
            const data = [];
            for (let i = 0; i < days; i++) {
                data.push(Math.random() * (max - min) + min);
            }
            return data;
        }
    };
}

console.log("✅ ETF Institutional Controller loaded");