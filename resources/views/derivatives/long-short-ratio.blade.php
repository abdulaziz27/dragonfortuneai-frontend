@extends('layouts.app')

@section('content')
    {{--
        Long/Short Ratio Analytics Dashboard
        Think like a trader • Build like an engineer • Visualize like a designer

        Trading Interpretasi:
        - Long/Short ratio tinggi → Retail bullish → Potensi correction
        - Long/Short ratio rendah → Retail bearish → Potensi reversal
        - Accounts vs Positions → Different market segments
        - Exchange comparison → Market sentiment across venues
    --}}

    <div class="d-flex flex-column h-100 gap-3" x-data="longShortRatioController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">📊 Long/Short Ratio</h1>
                        <span class="pulse-dot pulse-info"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Monitor retail vs professional trader positioning and market sentiment
                    </p>
                </div>

                <!-- Global Controls -->
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <select class="form-select" style="width: 120px;" x-model="globalSymbol" @change="updateSymbol()">
                        <option value="BTC">Bitcoin</option>
                        <option value="ETH">Ethereum</option>
                        <option value="SOL">Solana</option>
                        <option value="BNB">BNB</option>
                        <option value="XRP">XRP</option>
                        <!-- <option value="ADA">Cardano</option>
                        <option value="DOGE">Dogecoin</option>
                        <option value="MATIC">Polygon</option>
                        <option value="DOT">Polkadot</option>
                        <option value="AVAX">Avalanche</option> -->
                    </select>

                    <select class="form-select" style="width: 140px;" x-model="globalRatioType" @change="updateRatioType()">
                        <option value="accounts">Accounts</option>
                        <option value="positions">Positions</option>
                    </select>

                    <!-- <select class="form-select" style="width: 140px;" x-model="globalExchange" @change="updateExchange()">
                        <option value="">All Exchanges</option>
                        <option value="Binance">Binance</option>
                        <option value="Bybit">Bybit</option>
                        <option value="OKX">OKX</option>
                        <option value="Bitget">Bitget</option>
                        <option value="Gate.io">Gate.io</option>
                    </select> -->

                    <select class="form-select" style="width: 120px;" x-model="globalInterval" @change="updateInterval()">
                        <option value="15m">15 Minutes</option>
                        <option value="30m">30 Minutes</option>
                        <option value="1h">1 Hour</option>
                        <option value="4h">4 Hours</option>
                        <option value="1d">1 Day</option>
                    </select>

                    <select class="form-select" style="width: 100px;" x-model="globalLimit" @change="updateLimit()">
                        <option value="100">100</option>
                        <option value="500">500</option>
                        <option value="1000">1000</option>
                        <option value="2000">2000</option>
                        <option value="5000">5000</option>
                    </select>

                    <button class="btn btn-primary" @click="refreshAll()" :disabled="globalLoading">
                        <span x-show="!globalLoading">🔄 Refresh All</span>
                        <span x-show="globalLoading" class="spinner-border spinner-border-sm"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Market Overview Card (Full Width) -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-3" x-data="marketOverviewCard()" x-init="init()">
                    <div class="row g-3">
                        <!-- Current Ratio -->
                        <div class="col-md-3">
                            <div class="stat-card bg-primary bg-opacity-10 p-3 rounded">
                                <div class="small text-secondary mb-1">Current Ratio</div>
                                <div class="h4 mb-0 fw-bold text-primary" x-text="formatRatio(currentRatio)">--</div>
                                <div class="small text-secondary mt-1" x-text="ratioType + ' based'">--</div>
                            </div>
                        </div>

                        <!-- Long Percentage -->
                        <div class="col-md-3">
                            <div class="stat-card bg-success bg-opacity-10 p-3 rounded">
                                <div class="small text-secondary mb-1">Long Percentage</div>
                                <div class="h4 mb-0 fw-bold text-success" x-text="formatPercentage(longPercentage)">--</div>
                                <div class="small text-secondary mt-1">of total</div>
                            </div>
                        </div>

                        <!-- Short Percentage -->
                        <div class="col-md-3">
                            <div class="stat-card bg-danger bg-opacity-10 p-3 rounded">
                                <div class="small text-secondary mb-1">Short Percentage</div>
                                <div class="h4 mb-0 fw-bold text-danger" x-text="formatPercentage(shortPercentage)">--</div>
                                <div class="small text-secondary mt-1">of total</div>
                            </div>
                        </div>

                        <!-- Market Sentiment -->
                        <div class="col-md-3">
                            <div class="stat-card bg-warning bg-opacity-10 p-3 rounded">
                                <div class="small text-secondary mb-1">Market Sentiment</div>
                                <div class="h4 mb-0 fw-bold" :class="getSentimentClass()" x-text="getSentimentText()">--</div>
                                <div class="small text-secondary mt-1" x-text="getSentimentDescription()">--</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Tables Row -->
        <div class="row g-3">
            <!-- Overview Summary -->
            <div class="col-lg-6">
                <div class="df-panel p-4 h-100" x-data="overviewSummaryTable()" x-init="init()">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-0">📊 Overview Summary</h5>
                            <small class="text-secondary">Accounts vs Positions comparison</small>
                        </div>
                        <span x-show="loading" class="spinner-border spinner-border-sm text-primary"></span>
                    </div>

                    <!-- Summary Table -->
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Metric</th>
                                    <th>Accounts</th>
                                    <th>Positions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Latest Ratio</strong></td>
                                    <td x-text="formatRatio(accountsSummary?.latest_ratio)">--</td>
                                    <td x-text="formatRatio(positionsSummary?.latest_ratio)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Average Ratio</strong></td>
                                    <td x-text="formatRatio(accountsSummary?.average_ratio)">--</td>
                                    <td x-text="formatRatio(positionsSummary?.average_ratio)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Max Ratio</strong></td>
                                    <td x-text="formatRatio(accountsSummary?.max_ratio)">--</td>
                                    <td x-text="formatRatio(positionsSummary?.max_ratio)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Min Ratio</strong></td>
                                    <td x-text="formatRatio(accountsSummary?.min_ratio)">--</td>
                                    <td x-text="formatRatio(positionsSummary?.min_ratio)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Avg Long %</strong></td>
                                    <td x-text="formatPercentage(accountsSummary?.avg_long_pct)">--</td>
                                    <td x-text="formatPercentage(positionsSummary?.avg_long_pct)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Avg Short %</strong></td>
                                    <td x-text="formatPercentage(accountsSummary?.avg_short_pct)">--</td>
                                    <td x-text="formatPercentage(positionsSummary?.avg_short_pct)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Bias</strong></td>
                                    <td><span class="badge" :class="getBiasClass(accountsSummary?.bias)" x-text="accountsSummary?.bias || '--'">--</span></td>
                                    <td><span class="badge" :class="getBiasClass(positionsSummary?.bias)" x-text="positionsSummary?.bias || '--'">--</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Observations</strong></td>
                                    <td x-text="accountsSummary?.observations || '--'">--</td>
                                    <td x-text="positionsSummary?.observations || '--'">--</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Signals & Insights -->
            <div class="col-lg-6">
                <div class="df-panel p-4 h-100" x-data="signalsPanel()" x-init="init()">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-0">🚨 Signals & Insights</h5>
                            <small class="text-secondary">Market alerts and analysis</small>
                        </div>
                        <span x-show="loading" class="spinner-border spinner-border-sm text-primary"></span>
                    </div>

                    <!-- Signals List -->
                    <div x-show="signals && signals.length > 0">
                        <template x-for="(signal, index) in signals" :key="'signal-' + index + '-' + signal.type">
                            <div class="alert" :class="getSignalClass(signal.severity)" role="alert">
                                <div class="d-flex align-items-start">
                                    <div class="me-2">
                                        <span x-text="getSignalIcon(signal.severity)">⚠️</span>
                                    </div>
                                    <div>
                                        <strong x-text="signal.type">Signal Type</strong>
                                        <p class="mb-0 mt-1" x-text="signal.message">Signal message</p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="!signals || signals.length === 0" class="text-center text-muted py-4">
                        <div>📊</div>
                        <div>No signals available</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Data Tables -->
        <div class="row g-3">
            <!-- Top Accounts Table -->
            <div class="col-lg-6">
                <div class="df-panel p-3" x-data="topAccountsTable()" x-init="init()">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-0">👥 Top Accounts Data</h5>
                            <small class="text-secondary">Recent account positioning data</small>
                        </div>
                        <span x-show="loading" class="spinner-border spinner-border-sm text-primary"></span>
                    </div>

                    <!-- Accounts Table -->
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-striped">
                            <thead class="sticky-top bg-white">
                                <tr>
                                    <th>Time</th>
                                    <th>Exchange</th>
                                    <th>Long %</th>
                                    <th>Short %</th>
                                    <th>L/S Ratio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(account, index) in topAccounts" :key="'account-' + index + '-' + account.ts">
                                    <tr>
                                        <td x-text="formatTimestamp(account.ts)">--</td>
                                        <td x-text="account.exchange">--</td>
                                        <td class="text-success" x-text="formatPercentage(account.long_accounts)">--</td>
                                        <td class="text-danger" x-text="formatPercentage(account.short_accounts)">--</td>
                                        <td class="fw-bold" x-text="formatRatio(account.ls_ratio_accounts)">--</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div x-show="!topAccounts || topAccounts.length === 0" class="text-center text-muted py-4">
                        <div>📊</div>
                        <div>No accounts data available</div>
                    </div>
                </div>
            </div>

            <!-- Top Positions Table -->
            <div class="col-lg-6">
                <div class="df-panel p-3" x-data="topPositionsTable()" x-init="init()">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-0">💰 Top Positions Data</h5>
                            <small class="text-secondary">Recent position sizing data</small>
                        </div>
                        <span x-show="loading" class="spinner-border spinner-border-sm text-primary"></span>
                    </div>

                    <!-- Positions Table -->
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-striped">
                            <thead class="sticky-top bg-white">
                                <tr>
                                    <th>Time</th>
                                    <th>Exchange</th>
                                    <th>Long %</th>
                                    <th>Short %</th>
                                    <th>L/S Ratio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(position, index) in topPositions" :key="'position-' + index + '-' + position.ts">
                                    <tr>
                                        <td x-text="formatTimestamp(position.ts)">--</td>
                                        <td x-text="position.exchange">--</td>
                                        <td class="text-success" x-text="formatPercentage(position.long_positions_percent)">--</td>
                                        <td class="text-danger" x-text="formatPercentage(position.short_positions_percent)">--</td>
                                        <td class="fw-bold" x-text="formatRatio(position.ls_ratio_positions)">--</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div x-show="!topPositions || topPositions.length === 0" class="text-center text-muted py-4">
                        <div>📊</div>
                        <div>No positions data available</div>
                    </div>
                </div>
            </div>
        </div>



    </div>
@endsection

@section('scripts')
    <!-- Chart.js - Load BEFORE Alpine components -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

    <!-- Wait for Chart.js to load -->
    <script>
        window.chartJsReady = new Promise((resolve) => {
            if (typeof Chart !== 'undefined') {
                resolve();
            } else {
                setTimeout(() => resolve(), 100);
            }
        });

        // Fix Chart.js context issues
        Chart.register({
            id: 'clipArea',
            beforeDraw: (chart) => {
                const { ctx, chartArea } = chart;
                if (!chartArea) return;

                ctx.save();
                ctx.beginPath();
                ctx.rect(chartArea.left, chartArea.top, chartArea.right - chartArea.left, chartArea.bottom - chartArea.top);
                ctx.clip();
            },
            afterDraw: (chart) => {
                chart.ctx.restore();
            }
        });
    </script>

    <!-- Load long-short-ratio controller BEFORE Alpine processes x-data -->
    <script src="{{ asset('js/long-short-ratio-controller.js') }}"></script>

    <script>
        // Main Controller - Similar to Funding Rate pattern
        function longShortRatioController() {
            return {
                // Global state
                globalSymbol: "BTC",
                globalRatioType: "accounts",
                globalExchange: "Binance",
                globalInterval: "1h",
                globalLimit: 1000,
                globalLoading: false,

                // Overview data
                overview: null,

                // Initialize dashboard
                init() {
                    console.log("🚀 Long/Short Ratio Dashboard initialized");
                    console.log("📊 Symbol:", this.globalSymbol);
                    console.log("📈 Ratio Type:", this.globalRatioType);
                    console.log("🏦 Exchange:", this.globalExchange || "All");

                    // Setup event listeners
                    this.setupEventListeners();

                    // Load initial overview
                    this.loadOverview().catch((e) =>
                        console.warn("Initial overview load failed:", e)
                    );

                    // Setup auto-refresh every 5 seconds
                    this.setupAutoRefresh();

                    // Log dashboard ready
                    setTimeout(() => {
                        console.log("✅ Long/Short Ratio dashboard loaded");
                        this.logDashboardStatus();
                    }, 2000);
                },

                // Setup global event listeners
                setupEventListeners() {
                    // Listen for filter changes
                    window.addEventListener("symbol-changed", () => {
                        this.loadOverview().catch((e) =>
                            console.warn("Overview reload failed:", e)
                        );
                    });

                    window.addEventListener("ratio-type-changed", () => {
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

                    // Auto-refresh every 60 seconds
                    setInterval(() => {
                        if (!this.globalLoading) {
                            this.loadOverview().catch((e) =>
                                console.warn("Auto refresh failed:", e)
                            );
                        }
                    }, 60000);
                },

                // Load overview data from all endpoints
                async loadOverview() {
                    this.globalLoading = true;
                    console.log("🔄 Loading Long/Short Ratio overview...");

                    try {
                        const baseSymbol = this.globalSymbol;
                        const pair = `${baseSymbol}USDT`;
                        const interval = this.globalInterval;
                        const ratioType = this.globalRatioType;
                        const exchange = this.globalExchange || null;
                        const limit = this.globalLimit;

                        // Fetch overview data first, then detailed data
                        const [overview, analytics, topAccounts, topPositions] = await Promise.all([
                            this.fetchAPI("overview", {
                                symbol: pair,
                                interval: interval,
                                limit: limit,
                            }),
                            this.fetchAPI("analytics", {
                                symbol: pair,
                                exchange: exchange,
                                interval: interval,
                                ratio_type: ratioType,
                                limit: limit,
                            }),
                            this.fetchAPI("top-accounts", {
                                symbol: pair,
                                exchange: exchange,
                                interval: interval,
                                limit: limit,
                            }),
                            this.fetchAPI("top-positions", {
                                symbol: pair,
                                exchange: exchange,
                                interval: interval,
                                limit: limit,
                            }),
                        ]);

                        // Process overview data
                        const overviewData = overview || {};
                        const accountsSummary = overviewData.accounts_summary || {};
                        const positionsSummary = overviewData.positions_summary || {};
                        const signals = overviewData.signals || [];

                        // Process analytics data
                        const analyticsData = analytics || {};
                        const ratioStats = analyticsData.ratio_stats || {};
                        const positioning = analyticsData.positioning || {};
                        const trend = analyticsData.trend || {};

                        // Process timeseries data from top-accounts or top-positions
                        const timeseries = [];
                        const sourceData = ratioType === "accounts" ?
                            (topAccounts && topAccounts.data ? topAccounts.data : []) :
                            (topPositions && topPositions.data ? topPositions.data : []);

                        if (sourceData && Array.isArray(sourceData)) {
                            timeseries.push(...sourceData.map(item => ({
                                ts: item.ts,
                                ratio: parseFloat(ratioType === "accounts" ? item.ls_ratio_accounts : item.ls_ratio_positions),
                                longPct: parseFloat(ratioType === "accounts" ? item.long_accounts : item.long_positions_percent),
                                shortPct: parseFloat(ratioType === "accounts" ? item.short_accounts : item.short_positions_percent),
                                exchange: item.exchange,
                                pair: item.pair,
                            })));
                        }

                        // Process exchange data - create comparison from multiple exchanges
                        const exchangeData = {};
                        const exchanges = ["Binance", "Bybit", "OKX"];

                        // For now, we'll use the current exchange data
                        if (exchange && sourceData && sourceData.length > 0) {
                            const latest = sourceData[sourceData.length - 1];
                            exchangeData[exchange] = {
                                ratio: parseFloat(ratioType === "accounts" ? latest.ls_ratio_accounts : latest.ls_ratio_positions),
                                longPct: parseFloat(ratioType === "accounts" ? latest.long_accounts : latest.long_positions_percent),
                                shortPct: parseFloat(ratioType === "accounts" ? latest.short_accounts : latest.short_positions_percent),
                                pair: latest.pair,
                                timestamp: latest.ts,
                            };
                        }

                        this.overview = {
                            meta: {
                                symbol: baseSymbol,
                                pair: pair,
                                ratioType: ratioType,
                                exchange: exchange,
                                interval: interval,
                                units: { ratio: "ratio", percentage: "percentage" },
                                last_updated: Date.now(),
                            },
                            // Overview data
                            accountsSummary: accountsSummary,
                            positionsSummary: positionsSummary,
                            signals: signals,
                            // Analytics data
                            analytics: analyticsData,
                            ratioStats: ratioStats,
                            positioning: positioning,
                            trend: trend,
                            // Timeseries data
                            timeseries: timeseries,
                            exchangeData: exchangeData,
                            // Sort by timestamp descending (newest first)
                            topAccounts: ((topAccounts && topAccounts.data) ? topAccounts.data : []).sort((a, b) => {
                                if (!a.ts) return 1;
                                if (!b.ts) return -1;
                                return new Date(b.ts) - new Date(a.ts);
                            }),
                            topPositions: ((topPositions && topPositions.data) ? topPositions.data : []).sort((a, b) => {
                                if (!a.ts) return 1;
                                if (!b.ts) return -1;
                                return new Date(b.ts) - new Date(a.ts);
                            }),
                        };

                        // Broadcast overview ready event
                        window.dispatchEvent(
                            new CustomEvent("long-short-ratio-overview-ready", {
                                detail: this.overview,
                            })
                        );

                        console.log("✅ Overview loaded:", this.overview);
                        return this.overview;
                    } catch (error) {
                        console.error("❌ Error loading overview:", error);
                        throw error;
                    } finally {
                        this.globalLoading = false;
                    }
                },

                // Update symbol and reload
                updateSymbol() {
                    console.log("📊 Symbol changed to:", this.globalSymbol);
                    window.dispatchEvent(
                        new CustomEvent("symbol-changed", {
                            detail: { symbol: this.globalSymbol },
                        })
                    );
                },

                // Update ratio type and reload
                updateRatioType() {
                    console.log("📈 Ratio type changed to:", this.globalRatioType);
                    window.dispatchEvent(
                        new CustomEvent("ratio-type-changed", {
                            detail: { ratioType: this.globalRatioType },
                        })
                    );
                },

                // Update exchange and reload
                updateExchange() {
                    console.log("🏦 Exchange changed to:", this.globalExchange || "All");
                    window.dispatchEvent(
                        new CustomEvent("exchange-changed", {
                            detail: { exchange: this.globalExchange },
                        })
                    );
                },

                // Update interval and reload
                updateInterval() {
                    console.log("⏰ Interval changed to:", this.globalInterval);
                    window.dispatchEvent(
                        new CustomEvent("interval-changed", {
                            detail: { interval: this.globalInterval },
                        })
                    );
                },

                // Update limit and reload
                updateLimit() {
                    console.log("📊 Limit changed to:", this.globalLimit);
                    window.dispatchEvent(
                        new CustomEvent("limit-changed", {
                            detail: { limit: this.globalLimit },
                        })
                    );
                },

                // Setup auto-refresh every 5 seconds
                setupAutoRefresh() {
                    console.log("🔄 Setting up auto-refresh every 5 seconds");
                    
                    setInterval(() => {
                        this.refreshAll();
                    }, 5000); // 5 seconds
                },

                // Refresh all components
                refreshAll() {
                    this.globalLoading = true;
                    console.log("🔄 Auto-refreshing all components...");

                    // Dispatch refresh event
                    window.dispatchEvent(
                        new CustomEvent("refresh-all", {
                            detail: {
                                symbol: this.globalSymbol,
                                ratioType: this.globalRatioType,
                                exchange: this.globalExchange,
                                interval: this.globalInterval,
                                limit: this.globalLimit,
                            },
                        })
                    );

                    // Reload overview
                    this.loadOverview().catch((e) =>
                        console.warn("Auto-refresh failed:", e)
                    );

                    // Reset loading state after delay
                    setTimeout(() => {
                        this.globalLoading = false;
                        console.log("✅ All components auto-refreshed");
                    }, 2000);
                },

                // API Helper: Fetch with error handling
                async fetchAPI(endpoint, params = {}) {
                    const queryString = new URLSearchParams(params).toString();
                    const baseMeta = document.querySelector(
                        'meta[name="api-base-url"]'
                    );
                    const configuredBase = (baseMeta?.content || "").trim();

                    let url = `/api/long-short-ratio/${endpoint}?${queryString}`;
                    if (configuredBase) {
                        const normalizedBase = configuredBase.endsWith("/")
                            ? configuredBase.slice(0, -1)
                            : configuredBase;
                        url = `${normalizedBase}/api/long-short-ratio/${endpoint}?${queryString}`;
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
                            : data?.analytics || data?.ratio_stats || data?.timeseries
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

                // Log dashboard status
                logDashboardStatus() {
                    console.log("📊 Long/Short Ratio Dashboard Status");
                    console.log("Symbol:", this.globalSymbol);
                    console.log("Ratio Type:", this.globalRatioType);
                    console.log("Exchange:", this.globalExchange || "All");
                    console.log("Interval:", this.globalInterval);
                    console.log("Limit:", this.globalLimit);
                    console.log("Loading:", this.globalLoading);
                    console.log("Overview:", this.overview ? "Loaded" : "Not loaded");
                },
            };
        }

        // Market Overview Card Component
        function marketOverviewCard() {
            return {
                loading: false,
                hasData: false,
                currentRatio: 0,
                longPercentage: 0,
                shortPercentage: 0,
                ratioType: 'accounts',

                async init() {
                    // Listen for overview ready
                    window.addEventListener('long-short-ratio-overview-ready', (e) => {
                        this.applyOverview(e.detail);
                    });

                    // Listen for filter changes
                    window.addEventListener('symbol-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('ratio-type-changed', (e) => {
                        this.ratioType = e.detail.ratioType;
                        this.loadData();
                    });

                    window.addEventListener('exchange-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('interval-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('limit-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('refresh-all', () => {
                        this.loadData();
                    });

                    // Initial load with delay to ensure DOM is ready
                    setTimeout(() => {
                        if (this.$root?.overview) {
                            this.applyOverview(this.$root.overview);
                        } else {
                            this.loadData();
                        }
                    }, 100);
                },

                applyOverview(overview) {
                    if (!overview?.ratioStats) return;

                    this.ratioType = overview.meta?.ratioType || 'accounts';
                    this.currentRatio = overview.ratioStats.current || 0;

                    // Calculate percentages based on ratio
                    if (this.currentRatio > 0) {
                        this.longPercentage = (this.currentRatio / (1 + this.currentRatio)) * 100;
                        this.shortPercentage = (1 / (1 + this.currentRatio)) * 100;
                    } else {
                        this.longPercentage = 0;
                        this.shortPercentage = 0;
                    }

                    this.hasData = true;
                },

                async loadData() {
                    this.loading = true;
                    setTimeout(() => {
                        this.loading = false;
                    }, 1000);
                },

                formatRatio(value) {
                    if (value === null || value === undefined) return 'N/A';
                    const num = parseFloat(value);
                    if (isNaN(num)) return 'N/A';
                    return num.toFixed(3);
                },

                formatPercentage(value) {
                    if (value === null || value === undefined) return 'N/A';
                    const num = parseFloat(value);
                    if (isNaN(num)) return 'N/A';
                    return num.toFixed(1) + '%';
                },

                getSentimentClass() {
                    if (this.currentRatio > 1.2) return 'text-success';
                    if (this.currentRatio < 0.8) return 'text-danger';
                    return 'text-warning';
                },

                getSentimentText() {
                    if (this.currentRatio > 1.2) return 'Bullish';
                    if (this.currentRatio < 0.8) return 'Bearish';
                    return 'Neutral';
                },

                getSentimentDescription() {
                    if (this.currentRatio > 1.2) return 'Retail bullish bias';
                    if (this.currentRatio < 0.8) return 'Retail bearish bias';
                    return 'Balanced positioning';
                },
            };
        }

        // Ratio History Chart Component
        function ratioHistoryChart() {
            return {
                loading: false,
                hasData: false,
                chart: null,
                timeseries: [],
                chartType: 'line',

                // Stats
                dataPoints: 0,
                avgRatio: 0,
                maxRatio: 0,
                minRatio: 0,

                async init() {
                    // Wait for Chart.js
                    await window.chartJsReady;

                    // Listen for overview ready
                    window.addEventListener('long-short-ratio-overview-ready', (e) => {
                        this.applyOverview(e.detail);
                    });

                    // Listen for filter changes
                    window.addEventListener('symbol-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('ratio-type-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('exchange-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('interval-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('limit-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('refresh-all', () => {
                        this.loadData();
                    });

                    // Initial load with delay to ensure DOM is ready
                    setTimeout(() => {
                        if (this.$root?.overview) {
                            this.applyOverview(this.$root.overview);
                        } else {
                            this.loadData();
                        }
                    }, 100);
                },

                applyOverview(overview) {
                    if (!overview?.timeseries || !Array.isArray(overview.timeseries)) {
                        return;
                    }

                    this.timeseries = overview.timeseries.sort((a, b) => a.ts - b.ts);
                    this.calculateStats();
                    this.renderChart();
                },

                calculateStats() {
                    if (this.timeseries.length === 0) return;

                    this.dataPoints = this.timeseries.length;
                    const ratios = this.timeseries.map(d => d.ratio);
                    this.avgRatio = ratios.reduce((a, b) => a + b, 0) / ratios.length;
                    this.maxRatio = Math.max(...ratios);
                    this.minRatio = Math.min(...ratios);
                },

                renderChart() {
                    if (!this.timeseries || this.timeseries.length === 0) {
                        this.hasData = false;
                        return;
                    }

                    this.hasData = true;

                    // Prepare data
                    const labels = this.timeseries.map(d => this.formatTimestamp(d.ts));
                    const ratioData = this.timeseries.map(d => d.ratio);

                    // Determine chart type config
                    let chartTypeConfig = 'line';
                    let fillConfig = false;
                    let tensionConfig = 0.4;

                    if (this.chartType === 'bar') {
                        chartTypeConfig = 'bar';
                    } else if (this.chartType === 'area') {
                        chartTypeConfig = 'line';
                        fillConfig = true;
                        tensionConfig = 0.4;
                    }

                    // Destroy existing chart
                    if (this.chart) {
                        this.chart.destroy();
                    }

                    // Create new chart
                    const canvas = this.$refs.ratioCanvas;
                    if (!canvas) {
                        console.error('Canvas element not found');
                        return;
                    }

                    const ctx = canvas.getContext('2d');
                    if (!ctx) {
                        console.error('Canvas context not available');
                        return;
                    }

                    this.chart = new Chart(ctx, {
                        type: chartTypeConfig,
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Long/Short Ratio',
                                    data: ratioData,
                                    backgroundColor: fillConfig ? 'rgba(59, 130, 246, 0.2)' : 'rgba(59, 130, 246, 0.8)',
                                    borderColor: 'rgb(59, 130, 246)',
                                    borderWidth: 2,
                                    fill: fillConfig,
                                    tension: tensionConfig,
                                    pointRadius: this.chartType === 'bar' ? 0 : 2,
                                    pointHoverRadius: this.chartType === 'bar' ? 0 : 4,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: {
                                        color: '#94a3b8',
                                        font: { size: 11 },
                                        usePointStyle: true,
                                    },
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 12,
                                    callbacks: {
                                        label: (context) => {
                                            const label = context.dataset.label || '';
                                            const value = this.formatRatio(context.parsed.y);
                                            return `${label}: ${value}`;
                                        },
                                    },
                                },
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        color: '#94a3b8',
                                        font: { size: 9 },
                                        maxRotation: 45,
                                        minRotation: 45,
                                        maxTicksLimit: 15,
                                    },
                                    grid: {
                                        display: false,
                                    },
                                },
                                y: {
                                    ticks: {
                                        color: '#94a3b8',
                                        font: { size: 10 },
                                        callback: (value) => this.formatRatio(value),
                                    },
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.1)',
                                    },
                                },
                            },
                        },
                    });
                },

                async loadData() {
                    this.loading = true;
                    setTimeout(() => {
                        this.loading = false;
                    }, 1000);
                },

                formatRatio(value) {
                    if (value === null || value === undefined) return 'N/A';
                    const num = parseFloat(value);
                    if (isNaN(num)) return 'N/A';
                    return num.toFixed(3);
                },

                formatTimestamp(timestamp) {
                    if (!timestamp) return 'N/A';
                    const date = new Date(timestamp);
                    return date.toLocaleString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false,
                    });
                },
            };
        }

        // Distribution Chart Component
        function distributionChart() {
            return {
                loading: false,
                hasData: false,
                chart: null,
                longPercentage: 0,
                shortPercentage: 0,

                async init() {
                    // Wait for Chart.js
                    await window.chartJsReady;

                    // Listen for overview ready
                    window.addEventListener('long-short-ratio-overview-ready', (e) => {
                        this.applyOverview(e.detail);
                    });

                    // Listen for filter changes
                    window.addEventListener('symbol-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('ratio-type-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('exchange-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('interval-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('limit-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('refresh-all', () => {
                        this.loadData();
                    });

                    // Initial load with delay to ensure DOM is ready
                    setTimeout(() => {
                        if (this.$root?.overview) {
                            this.applyOverview(this.$root.overview);
                        } else {
                            this.loadData();
                        }
                    }, 100);
                },

                applyOverview(overview) {
                    if (!overview?.ratioStats) return;

                    const currentRatio = overview.ratioStats.current || 0;

                    // Calculate percentages based on ratio
                    if (currentRatio > 0) {
                        this.longPercentage = (currentRatio / (1 + currentRatio)) * 100;
                        this.shortPercentage = (1 / (1 + currentRatio)) * 100;
                    } else {
                        this.longPercentage = 50;
                        this.shortPercentage = 50;
                    }

                    this.hasData = true;
                    this.renderChart();
                },

                renderChart() {
                    if (!this.hasData) {
                        return;
                    }

                    // Destroy existing chart
                    if (this.chart) {
                        this.chart.destroy();
                    }

                    // Create new chart
                    const canvas = this.$refs.distributionCanvas;
                    if (!canvas) {
                        console.error('Canvas element not found');
                        return;
                    }

                    const ctx = canvas.getContext('2d');
                    if (!ctx) {
                        console.error('Canvas context not available');
                        return;
                    }

                    this.chart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Long Positions', 'Short Positions'],
                            datasets: [
                                {
                                    data: [this.longPercentage, this.shortPercentage],
                                    backgroundColor: [
                                        'rgba(34, 197, 94, 0.8)',
                                        'rgba(239, 68, 68, 0.8)',
                                    ],
                                    borderColor: [
                                        'rgb(34, 197, 94)',
                                        'rgb(239, 68, 68)',
                                    ],
                                    borderWidth: 2,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false, // We have custom legend
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 12,
                                    callbacks: {
                                        label: (context) => {
                                            const label = context.label || '';
                                            const value = context.parsed.toFixed(1);
                                            return `${label}: ${value}%`;
                                        },
                                    },
                                },
                            },
                        },
                    });
                },

                async loadData() {
                    this.loading = true;
                    setTimeout(() => {
                        this.loading = false;
                    }, 1000);
                },
            };
        }

        // Exchange Comparison Table Component
        function exchangeComparisonTable() {
            return {
                loading: false,
                exchangeData: {},

                // Comparison-specific filters
                comparisonSymbol: "BTC",
                comparisonRatioType: "accounts",
                comparisonInterval: "1h",

                // Available exchanges for comparison
                availableExchanges: ["Binance", "Bybit", "OKX", "Bitget", "Gate.io"],

                async init() {
                    // Listen for overview ready
                    window.addEventListener('long-short-ratio-overview-ready', (e) => {
                        this.applyOverview(e.detail);
                    });

                    // Initial load with delay to ensure DOM is ready
                    setTimeout(() => {
                        this.loadComparisonData();
                    }, 100);
                },

                applyOverview(overview) {
                    if (!overview?.exchangeData) return;
                    this.exchangeData = overview.exchangeData;
                },

                async loadComparisonData() {
                    this.loading = true;
                    try {
                        const pair = `${this.comparisonSymbol}USDT`;
                        const exchangeData = {};

                        // Fetch data for each exchange
                        const exchangePromises = this.availableExchanges.map(async (exchange) => {
                            try {
                                const endpoint = this.comparisonRatioType === "accounts" ? "top-accounts" : "top-positions";
                                const response = await this.fetchAPI(endpoint, {
                                    symbol: pair,
                                    exchange: exchange,
                                    interval: this.comparisonInterval,
                                    limit: 1, // Get latest data only
                                });

                                if (response?.data && response.data.length > 0) {
                                    const latest = response.data[response.data.length - 1];
                                    return {
                                        exchange: exchange,
                                        data: {
                                            ratio: parseFloat(this.comparisonRatioType === "accounts" ? latest.ls_ratio_accounts : latest.ls_ratio_positions),
                                            longPct: parseFloat(this.comparisonRatioType === "accounts" ? latest.long_accounts : latest.long_positions_percent),
                                            shortPct: parseFloat(this.comparisonRatioType === "accounts" ? latest.short_accounts : latest.short_positions_percent),
                                            pair: latest.pair,
                                            timestamp: latest.ts,
                                        }
                                    };
                                }
                                return null;
                            } catch (error) {
                                console.log(`No data for ${exchange}:`, error.message);
                                return null;
                            }
                        });

                        const results = await Promise.all(exchangePromises);

                        // Process results
                        results.forEach(result => {
                            if (result) {
                                exchangeData[result.exchange] = result.data;
                            }
                        });

                        this.exchangeData = exchangeData;
                        console.log("✅ Exchange comparison data loaded:", this.exchangeData);

                    } catch (error) {
                        console.error("❌ Error loading comparison data:", error);
                        this.exchangeData = {};
                    } finally {
                        this.loading = false;
                    }
                },

                // Filter update methods
                updateComparisonSymbol() {
                    this.loadComparisonData();
                },

                updateComparisonRatioType() {
                    this.loadComparisonData();
                },

                updateComparisonInterval() {
                    this.loadComparisonData();
                },

                // API helper method
                async fetchAPI(endpoint, params = {}) {
                    const queryString = new URLSearchParams(params).toString();
                    const baseMeta = document.querySelector('meta[name="api-base-url"]');
                    const configuredBase = (baseMeta?.content || "").trim();

                    let url = `/api/long-short-ratio/${endpoint}?${queryString}`;
                    if (configuredBase) {
                        const normalizedBase = configuredBase.endsWith("/")
                            ? configuredBase.slice(0, -1)
                            : configuredBase;
                        url = `${normalizedBase}/api/long-short-ratio/${endpoint}?${queryString}`;
                    }

                    try {
                        const response = await fetch(url);
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        return await response.json();
                    } catch (error) {
                        throw error;
                    }
                },

                formatRatio(value) {
                    if (value === null || value === undefined) return 'N/A';
                    const num = parseFloat(value);
                    if (isNaN(num)) return 'N/A';
                    return num.toFixed(3);
                },

                formatPercentage(value) {
                    if (value === null || value === undefined) return 'N/A';
                    const num = parseFloat(value);
                    if (isNaN(num)) return 'N/A';
                    return num.toFixed(1) + '%';
                },

                formatTimestamp(timestamp) {
                    if (!timestamp) return 'N/A';
                    const date = new Date(timestamp);
                    return date.toLocaleString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false,
                    });
                },
            };
        }

        // Overview Summary Table Component
        function overviewSummaryTable() {
            return {
                loading: false,
                accountsSummary: null,
                positionsSummary: null,

                async init() {
                    // Listen for overview ready
                    window.addEventListener('long-short-ratio-overview-ready', (e) => {
                        this.applyOverview(e.detail);
                    });

                    // Listen for filter changes
                    window.addEventListener('symbol-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('ratio-type-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('exchange-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('interval-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('limit-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('refresh-all', () => {
                        this.loadData();
                    });

                    // Initial load with delay to ensure DOM is ready
                    setTimeout(() => {
                        if (this.$root?.overview) {
                            this.applyOverview(this.$root.overview);
                        } else {
                            this.loadData();
                        }
                    }, 100);
                },

                applyOverview(overview) {
                    if (!overview) {
                        return;
                    }

                    this.accountsSummary = overview.accountsSummary || null;
                    this.positionsSummary = overview.positionsSummary || null;
                },

                async loadData() {
                    this.loading = true;
                    setTimeout(() => {
                        this.loading = false;
                    }, 1000);
                },

                formatRatio(value) {
                    if (value === null || value === undefined) return 'N/A';
                    const num = parseFloat(value);
                    if (isNaN(num)) return 'N/A';
                    return num.toFixed(3);
                },

                formatPercentage(value) {
                    if (value === null || value === undefined) return 'N/A';
                    const num = parseFloat(value);
                    if (isNaN(num)) return 'N/A';
                    return num.toFixed(1) + '%';
                },

                getBiasClass(bias) {
                    if (bias === 'long') return 'bg-success';
                    if (bias === 'short') return 'bg-danger';
                    return 'bg-secondary';
                },
            };
        }

        // Signals Panel Component
        function signalsPanel() {
            return {
                loading: false,
                signals: [],

                async init() {
                    // Listen for overview ready
                    window.addEventListener('long-short-ratio-overview-ready', (e) => {
                        this.applyOverview(e.detail);
                    });

                    // Listen for filter changes
                    window.addEventListener('symbol-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('ratio-type-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('exchange-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('interval-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('limit-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('refresh-all', () => {
                        this.loadData();
                    });

                    // Initial load with delay to ensure DOM is ready
                    setTimeout(() => {
                        if (this.$root?.overview) {
                            this.applyOverview(this.$root.overview);
                        } else {
                            this.loadData();
                        }
                    }, 100);
                },

                applyOverview(overview) {
                    if (!overview) {
                        return;
                    }

                    this.signals = overview.signals || [];
                },

                async loadData() {
                    this.loading = true;
                    setTimeout(() => {
                        this.loading = false;
                    }, 1000);
                },

                getSignalClass(severity) {
                    if (severity === 'high') return 'alert-danger';
                    if (severity === 'medium') return 'alert-warning';
                    if (severity === 'low') return 'alert-info';
                    return 'alert-secondary';
                },

                getSignalIcon(severity) {
                    if (severity === 'high') return '🚨';
                    if (severity === 'medium') return '⚠️';
                    if (severity === 'low') return 'ℹ️';
                    return '📊';
                },
            };
        }


        // Top Accounts Table Component
        function topAccountsTable() {
            return {
                loading: false,
                topAccounts: [],

                async init() {
                    // Listen for overview ready
                    window.addEventListener('long-short-ratio-overview-ready', (e) => {
                        this.applyOverview(e.detail);
                    });

                    // Listen for filter changes
                    window.addEventListener('symbol-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('ratio-type-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('exchange-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('interval-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('limit-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('refresh-all', () => {
                        this.loadData();
                    });

                    // Initial load with delay to ensure DOM is ready
                    setTimeout(() => {
                        if (this.$root?.overview) {
                            this.applyOverview(this.$root.overview);
                        } else {
                            this.loadData();
                        }
                    }, 100);
                },

                applyOverview(overview) {
                    if (!overview) {
                        return;
                    }

                    this.topAccounts = overview.topAccounts || [];
                },

                async loadData() {
                    this.loading = true;
                    setTimeout(() => {
                        this.loading = false;
                    }, 1000);
                },

                formatTimestamp(ts) {
                    const date = new Date(ts);
                    return date.toLocaleString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                    });
                },

                formatPercentage(value) {
                    if (value === null || value === undefined) return 'N/A';
                    const num = parseFloat(value);
                    if (isNaN(num)) return 'N/A';
                    return num.toFixed(1) + '%';
                },

                formatRatio(value) {
                    if (value === null || value === undefined) return 'N/A';
                    const num = parseFloat(value);
                    if (isNaN(num)) return 'N/A';
                    return num.toFixed(3);
                },
            };
        }

        // Top Positions Table Component
        function topPositionsTable() {
            return {
                loading: false,
                topPositions: [],

                async init() {
                    // Listen for overview ready
                    window.addEventListener('long-short-ratio-overview-ready', (e) => {
                        this.applyOverview(e.detail);
                    });

                    // Listen for filter changes
                    window.addEventListener('symbol-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('ratio-type-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('exchange-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('interval-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('limit-changed', () => {
                        this.loadData();
                    });

                    window.addEventListener('refresh-all', () => {
                        this.loadData();
                    });

                    // Initial load with delay to ensure DOM is ready
                    setTimeout(() => {
                        if (this.$root?.overview) {
                            this.applyOverview(this.$root.overview);
                        } else {
                            this.loadData();
                        }
                    }, 100);
                },

                applyOverview(overview) {
                    if (!overview) {
                        return;
                    }

                    this.topPositions = overview.topPositions || [];
                },

                async loadData() {
                    this.loading = true;
                    setTimeout(() => {
                        this.loading = false;
                    }, 1000);
                },

                formatTimestamp(ts) {
                    const date = new Date(ts);
                    return date.toLocaleString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                    });
                },

                formatPercentage(value) {
                    if (value === null || value === undefined) return 'N/A';
                    const num = parseFloat(value);
                    if (isNaN(num)) return 'N/A';
                    return num.toFixed(1) + '%';
                },

                formatRatio(value) {
                    if (value === null || value === undefined) return 'N/A';
                    const num = parseFloat(value);
                    if (isNaN(num)) return 'N/A';
                    return num.toFixed(3);
                },
            };
        }
    </script>

    <style>
        /* Alpine.js cloaking */
        [x-cloak] {
            display: none !important;
        }

        /* Smooth transitions */
        .stat-card {
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Loading spinner */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
        }

        /* Chart containers */
        .derivatives-chart-body {
            position: relative;
            min-height: 400px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .derivatives-header h1 {
                font-size: 1.5rem;
            }

            .derivatives-filters {
                width: 100%;
            }

            .derivatives-filters select,
            .derivatives-filters button {
                flex: 1;
            }
        }
    </style>
@endsection
