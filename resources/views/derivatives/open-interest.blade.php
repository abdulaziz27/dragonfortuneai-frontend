@extends('layouts.app')

@section('title', 'Open Interest Analytics')

@section('content')
    <div class="d-flex flex-column h-100 gap-3" x-data="openInterestController()">
        <!-- Header -->
        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-3">
                <div>
                        <h4 class="mb-1">📊 Open Interest Analytics</h4>
                        <p class="text-secondary mb-0 small">Track leverage buildup & liquidation zones • OI rising = more contracts at risk</p>
                </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row g-2">
                    <!-- Current OI Card -->
                    <div class="col-md-6">
                        <div class="df-panel p-3 h-100 d-flex flex-column justify-content-center align-items-center text-center">
                            <div class="small text-secondary mb-1">Current OI</div>
                            <div class="fw-bold" x-text="formatOI(currentOI)">--</div>
                </div>
            </div>
                    <!-- OI Change Card -->
                    <div class="col-md-6">
                        <div class="df-panel p-3 h-100 d-flex flex-column justify-content-center align-items-center text-center">
                            <div class="small text-secondary mb-1">OI Change</div>
                            <div class="fw-bold" :class="getChangeClass(oiChange)" x-text="formatChange(oiChange)">--</div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>

        <!-- Filters -->
        <div class="df-panel p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex gap-3 align-items-center flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-secondary mb-0">Symbol:</label>
                        <select class="form-select" style="max-width: 120px;" x-model="globalSymbol" @change="updateSymbol()">
                            <option value="">All Symbols</option>
                            <option value="BTC" selected>BTC</option>
                            <option value="ETH">ETH</option>
                            <option value="SOL">SOL</option>
                            <option value="XRP">XRP</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-secondary mb-0">Pair:</label>
                        <select class="form-select" style="max-width: 120px;" x-model="globalPair" @change="updatePair()">
                            <option value="">All Pairs</option>
                            <option value="BTCUSDT" selected>BTCUSDT</option>
                            <option value="ETHUSDT">ETHUSDT</option>
                            <option value="SOLUSDT">SOLUSDT</option>
                            <option value="XRPUSDT">XRPUSDT</option>
                        </select>
                    </div>

                    <!-- Exchange Filter Hidden - Default to Binance
                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-secondary mb-0">Exchange:</label>
                        <select class="form-select" style="max-width: 120px;" x-model="globalExchange" @change="updateExchange()">
                            <option value="">All Exchanges</option>
                            <option value="Binance">Binance</option>
                            <option value="Bybit">Bybit</option>
                            <option value="OKX">OKX</option>
                            <option value="Bitget">Bitget</option>
                            <option value="Gate.io">Gate.io</option>
                        </select>
                    </div>
                    -->

                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-secondary mb-0">Interval:</label>
                        <select class="form-select" style="max-width: 120px;" x-model="globalInterval" @change="updateInterval()">
                            <option value="1m">1 Minute</option>
                            <option value="5m" selected>5 Minutes</option>
                        </select>
        </div>

                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-secondary mb-0">Limit:</label>
                        <select class="form-select" style="max-width: 100px;" x-model="globalLimit" @change="updateLimit()">
                            <option value="100">100</option>
                            <option value="500">500</option>
                            <option value="1000">1000</option>
                            <option value="2000" selected>2000</option>
                            <option value="5000">5000</option>
                        </select>
        </div>

                    <button class="btn btn-primary" @click="refreshAll()" :disabled="globalLoading">
                        <span x-show="!globalLoading">🔄 Refresh All</span>
                        <span x-show="globalLoading" class="spinner-border spinner-border-sm"></span>
                    </button>
                        </div>
                    </div>
        </div>

        <!-- Analytics Summary -->
            <div class="row g-3">
            <!-- Analytics Data -->
            <div class="col-lg-6">
                <div class="df-panel p-4 h-100" x-data="analyticsPanel()" x-init="init()">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-0">📊 Analytics Summary</h5>
                            <small class="text-secondary">Open Interest analytics and insights</small>
                    </div>
                        <span x-show="loading" class="spinner-border spinner-border-sm text-primary"></span>
                </div>

                    <!-- Analytics Table -->
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <td><strong>Current OI (USD)</strong></td>
                                    <td x-text="formatOI(analytics?.open_interest?.current_usd)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Average OI (USD)</strong></td>
                                    <td x-text="formatOI(analytics?.open_interest?.average_usd)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Max OI (USD)</strong></td>
                                    <td x-text="formatOI(analytics?.open_interest?.max_usd)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Min OI (USD)</strong></td>
                                    <td x-text="formatOI(analytics?.open_interest?.min_usd)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Recent Change %</strong></td>
                                    <td :class="getChangeClass(analytics?.open_interest?.recent_change_pct)" x-text="formatChange(analytics?.open_interest?.recent_change_pct)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Change %</strong></td>
                                    <td :class="getChangeClass(analytics?.open_interest?.total_change_pct)" x-text="formatChange(analytics?.open_interest?.total_change_pct)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Trend</strong></td>
                                    <td><span class="badge" :class="getTrendClass(analytics?.open_interest?.trend)" x-text="analytics?.open_interest?.trend || '--'">--</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Data Points</strong></td>
                                    <td x-text="analytics?.data_points || '--'">--</td>
                                </tr>
                            </tbody>
                        </table>
                </div>
            </div>
        </div>

            <!-- Insights Panel -->
            <div class="col-lg-6">
                <div class="df-panel p-4 h-100" x-data="insightsPanel()" x-init="init()">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-0">🚨 Insights & Alerts</h5>
                            <small class="text-secondary">Market insights and alerts</small>
                    </div>
                        <span x-show="loading" class="spinner-border spinner-border-sm text-primary"></span>
            </div>

                    <!-- Insights List -->
                    <div x-show="insights && insights.length > 0">
                        <template x-for="(insight, index) in insights" :key="'insight-' + index + '-' + insight.type">
                            <div class="alert" :class="getInsightClass(insight.severity)" role="alert">
                                <div class="d-flex align-items-start">
                                    <div class="me-2">
                                        <span x-text="getInsightIcon(insight.severity)">⚠️</span>
                    </div>
                                    <div>
                                        <strong x-text="insight.type">Insight Type</strong>
                                        <p class="mb-0 mt-1" x-text="insight.message">Insight message</p>
                    </div>
                        </div>
                    </div>
                        </template>
            </div>

                    <div x-show="!insights || insights.length === 0" class="text-center text-muted py-4">
                        <div>📊</div>
                        <div>No insights available</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Tables -->
            <div class="row g-3">
            <!-- Exchange Data Table -->
            <div class="col-lg-6">
                <div class="df-panel p-3" x-data="exchangeDataTable()" x-init="init()">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-0">🏦 Exchange Data</h5>
                            <small class="text-secondary">Open Interest by exchange</small>
                    </div>
                        <span x-show="loading" class="spinner-border spinner-border-sm text-primary"></span>
                </div>

                    <!-- Exchange Table -->
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-striped">
                            <thead class="sticky-top bg-white">
                                <tr>
                                    <th>Time</th>
                                    <th>Exchange</th>
                                    <th>Symbol</th>
                                    <th>OI USD</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in exchangeData" :key="'exchange-' + index + '-' + item.ts">
                                    <tr>
                                        <td x-text="formatTimestamp(item.ts)">--</td>
                                        <td x-text="item.exchange">--</td>
                                        <td x-text="item.symbol_coin">--</td>
                                        <td class="fw-bold" x-text="formatOI(item.oi_usd)">--</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div x-show="!exchangeData || exchangeData.length === 0" class="text-center text-muted py-4">
                        <div>📊</div>
                        <div>No exchange data available</div>
                </div>
                    </div>
                </div>

            <!-- History Data Table -->
            <div class="col-lg-6">
                <div class="df-panel p-3" x-data="historyDataTable()" x-init="init()">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-0">📈 History Data</h5>
                            <small class="text-secondary">Open Interest history by pair</small>
                    </div>
                        <span x-show="loading" class="spinner-border spinner-border-sm text-primary"></span>
        </div>

                    <!-- History Table -->
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-striped">
                            <thead class="sticky-top bg-white">
                                <tr>
                                    <th>Time</th>
                                    <th>Exchange</th>
                                    <th>Pair</th>
                                    <th>OI USD</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in historyData" :key="'history-' + index + '-' + item.ts">
                                    <tr>
                                        <td x-text="formatTimestamp(item.ts)">--</td>
                                        <td x-text="item.exchange">--</td>
                                        <td x-text="item.pair">--</td>
                                        <td class="fw-bold" x-text="formatOI(item.oi_usd)">--</td>
                                    </tr>
                    </template>
                            </tbody>
                        </table>
                </div>

                    <div x-show="!historyData || historyData.length === 0" class="text-center text-muted py-4">
                        <div>📊</div>
                        <div>No history data available</div>
            </div>
            </div>
                        </div>
        </div>

        <!-- Overview Summary -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-3" x-data="overviewPanel()" x-init="init()">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-0">📊 Overview Summary</h5>
                            <small class="text-secondary">Overall Open Interest statistics</small>
                    </div>
                        <span x-show="loading" class="spinner-border spinner-border-sm text-primary"></span>
        </div>

                    <!-- Overview Table -->
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <td><strong>Total OI</strong></td>
                                    <td x-text="formatOI(overview?.summary?.total_oi)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Average OI</strong></td>
                                    <td x-text="formatOI(overview?.summary?.average_oi)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Max OI</strong></td>
                                    <td x-text="formatOI(overview?.summary?.max_oi)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Min OI</strong></td>
                                    <td x-text="formatOI(overview?.summary?.min_oi)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Observations</strong></td>
                                    <td x-text="overview?.summary?.observations || '--'">--</td>
                                </tr>
                            </tbody>
                        </table>
            </div>

                    <!-- Top Symbols -->
                    <div x-show="overview && overview.top_symbols && overview.top_symbols.length > 0" class="mt-3">
                        <h6>Top Symbols</h6>
                    <div class="table-responsive">
                <table class="table table-sm">
                            <thead>
                                <tr>
                                        <th>Pair</th>
                                    <th>Exchange</th>
                                        <th>Close OI</th>
                                        <th>High OI</th>
                                        <th>Low OI</th>
                                </tr>
                            </thead>
                            <tbody>
                                    <template x-for="(symbol, index) in (overview?.top_symbols || [])" :key="'symbol-' + index + '-' + symbol.pair + '-' + symbol.exchange">
                                        <tr>
                                            <td x-text="symbol.pair">--</td>
                                            <td x-text="symbol.exchange">--</td>
                                            <td x-text="formatOI(symbol.close)">--</td>
                                            <td x-text="formatOI(symbol.high)">--</td>
                                            <td x-text="formatOI(symbol.low)">--</td>
                                </tr>
                        </template>
                            </tbody>
                        </table>
                        </div>
                    </div>
            </div>
        </div>
    </div>

    </div>

    <script>
        // Main Open Interest Controller
        function openInterestController() {
            return {
                // Global state
                globalSymbol: "BTC", // Default to BTC
                globalPair: "BTCUSDT", // Default to BTCUSDT
                globalExchange: "Binance", // Default to Binance since exchange filter is hidden
                globalInterval: "5m", // Default to 5 minutes
                globalLimit: 2000, // Default to 2000
                globalLoading: false,

                // Data state
                currentOI: 0,
                oiChange: 0,

                // Initialize dashboard
                async init() {
                    console.log("🚀 Open Interest Dashboard initialized");
                    console.log("📊 Symbol:", this.globalSymbol);
                    console.log("📈 Pair:", this.globalPair);
                    console.log("🏦 Exchange:", this.globalExchange);

                    // Load initial data
                    await this.loadOverview();
                    
                    // Broadcast initial filter state with delay to ensure components are ready
                    setTimeout(() => {
                        this.broadcastFilterChange();
                    }, 100);
                    
                    // Setup auto-refresh every 5 seconds
                    this.setupAutoRefresh();
                    
                    console.log("✅ Open Interest dashboard loaded");
                },

                // Load overview data
                async loadOverview() {
                    this.globalLoading = true;
                    console.log("🔄 Loading Open Interest overview...");

                    try {
                        // Build analytics params - analytics endpoint needs pair format (BTCUSDT)
                        const analyticsParams = {
                            interval: this.globalInterval,
                            limit: this.globalLimit,
                            with_price: true,
                        };

                        // Analytics endpoint uses pair format (BTCUSDT)
                        if (this.globalPair) {
                            analyticsParams.symbol = this.globalPair;
                        } else if (this.globalSymbol) {
                            analyticsParams.symbol = this.globalSymbol + "USDT";
                        }
                        if (this.globalExchange) analyticsParams.exchange = this.globalExchange;

                        // Fetch analytics data
                        const analytics = await this.fetchAPI("analytics", analyticsParams);

                        if (analytics && analytics.open_interest) {
                            this.currentOI = parseFloat(analytics.open_interest.current_usd) || 0;
                            this.oiChange = parseFloat(analytics.open_interest.recent_change_pct) || 0;
                        }

                        // Broadcast overview ready event
                        window.dispatchEvent(
                            new CustomEvent("open-interest-overview-ready", {
                                detail: {
                                    analytics: analytics,
                                    currentOI: this.currentOI,
                                    oiChange: this.oiChange,
                                },
                            })
                        );
                    } catch (error) {
                        console.error("❌ Error loading overview:", error);
                        throw error;
                    } finally {
                        this.globalLoading = false;
                    }
                },

                // API Helper: Fetch with error handling
                async fetchAPI(endpoint, params = {}) {
                    const queryString = new URLSearchParams(params).toString();
                    const baseMeta = document.querySelector('meta[name="api-base-url"]');
                    const configuredBase = (baseMeta?.content || "").trim();

                    // Use relative URL as default (follows same pattern as funding rate)
                    let url = `/api/open-interest/${endpoint}?${queryString}`; // default relative
                    if (configuredBase) {
                        const normalizedBase = configuredBase.endsWith("/")
                            ? configuredBase.slice(0, -1)
                            : configuredBase;
                        url = `${normalizedBase}/api/open-interest/${endpoint}?${queryString}`;
                    }

                    try {
                        console.log("📡 Fetching:", endpoint, params);
                        const response = await fetch(url);

                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }

                        const data = await response.json();
                        const itemCount = Array.isArray(data?.data)
                            ? data.data.length
                            : data?.analytics || data?.summary
                            ? "summary"
                            : "N/A";
                        console.log("✅ Received:", endpoint, itemCount, typeof itemCount === "number" ? "items" : "");
                        return data;
                    } catch (error) {
                        console.error("❌ API Error:", endpoint, error);
                        throw error;
                    }
                },

                // Update filters
                updateSymbol() {
                    console.log("📊 Symbol changed to:", this.globalSymbol);
                    // Auto-update pair when symbol changes
                    if (this.globalSymbol) {
                        this.globalPair = this.globalSymbol + "USDT";
                    } else {
                        this.globalPair = "";
                    }
                    
                    console.log("📈 Pair auto-updated to:", this.globalPair);
                    
                    this.loadOverview();
                    // Trigger refresh for Exchange Data and History Data
                    this.broadcastFilterChange();
                },

                updatePair() {
                    console.log("📈 Pair changed to:", this.globalPair);
                    this.loadOverview();
                    // Trigger refresh for Exchange Data and History Data
                    this.broadcastFilterChange();
                },

                // Broadcast filter changes to all components
                broadcastFilterChange() {
                    const filterData = {
                        symbol: this.globalSymbol,
                        pair: this.globalPair,
                        exchange: this.globalExchange,
                        interval: this.globalInterval,
                        limit: this.globalLimit,
                    };
                    
                    console.log("📡 Broadcasting filter change:", filterData);
                    
                    window.dispatchEvent(
                        new CustomEvent("open-interest-filter-changed", {
                            detail: filterData,
                        })
                    );
                },

                updateExchange() {
                    console.log("🏦 Exchange changed to:", this.globalExchange);
                    this.loadOverview();
                    this.broadcastFilterChange();
                },

                updateInterval() {
                    console.log("⏰ Interval changed to:", this.globalInterval);
                    this.loadOverview();
                    this.broadcastFilterChange();
                },

                updateLimit() {
                    console.log("📊 Limit changed to:", this.globalLimit);
                    this.loadOverview();
                    this.broadcastFilterChange();
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
                    
                    // Load fresh overview data
                    this.loadOverview().catch((e) => console.warn("Auto-refresh failed:", e));
                    
                    // Broadcast current filter state to refresh all components
                    this.broadcastFilterChange();
                    
                    // Reset loading state after delay
                    setTimeout(() => {
                        this.globalLoading = false;
                        console.log("✅ All components auto-refreshed");
                    }, 2000);
                },

                // Utility functions
                formatOI(value) {
                    if (value === null || value === undefined) return 'N/A';
                    const num = parseFloat(value);
                    if (isNaN(num)) return 'N/A';
                    if (num >= 1e9) return (num / 1e9).toFixed(2) + 'B';
                    if (num >= 1e6) return (num / 1e6).toFixed(2) + 'M';
                    if (num >= 1e3) return (num / 1e3).toFixed(2) + 'K';
                    return num.toFixed(2);
                },

                formatChange(value) {
                    if (value === null || value === undefined) return 'N/A';
                    const num = parseFloat(value);
                    if (isNaN(num)) return 'N/A';
                    return (num > 0 ? '+' : '') + num.toFixed(2) + '%';
                },

                getChangeClass(value) {
                    if (value === null || value === undefined) return '';
                    const num = parseFloat(value);
                    if (isNaN(num)) return '';
                    return num > 0 ? 'text-success' : num < 0 ? 'text-danger' : '';
                },
            };
        }

        // Analytics Panel Component
        function analyticsPanel() {
            return {
                loading: false,
                analytics: null,

                async init() {
                    // Listen for overview ready
                    window.addEventListener('open-interest-overview-ready', (e) => {
                        this.applyOverview(e.detail);
                    });

                    // Initial load
                    setTimeout(() => {
                        if (this.$root?.analytics) {
                            this.applyOverview({ analytics: this.$root.analytics });
                        }
                    }, 100);
                },

                applyOverview(overview) {
                    if (!overview) return;
                    this.analytics = overview.analytics || null;
                },

                formatOI(value) {
                    if (value === null || value === undefined) return 'N/A';
                    const num = parseFloat(value);
                    if (isNaN(num)) return 'N/A';
                    if (num >= 1e9) return (num / 1e9).toFixed(2) + 'B';
                    if (num >= 1e6) return (num / 1e6).toFixed(2) + 'M';
                    if (num >= 1e3) return (num / 1e3).toFixed(2) + 'K';
                    return num.toFixed(2);
                },

                formatChange(value) {
                    if (value === null || value === undefined) return 'N/A';
                    const num = parseFloat(value);
                    if (isNaN(num)) return 'N/A';
                    return (num > 0 ? '+' : '') + num.toFixed(2) + '%';
                },

                getChangeClass(value) {
                    if (value === null || value === undefined) return '';
                    const num = parseFloat(value);
                    if (isNaN(num)) return '';
                    return num > 0 ? 'text-success' : num < 0 ? 'text-danger' : '';
                },

                getTrendClass(trend) {
                    if (trend === 'strong_decline') return 'bg-danger';
                    if (trend === 'decline') return 'bg-warning';
                    if (trend === 'strong_increase') return 'bg-success';
                    if (trend === 'increase') return 'bg-info';
                    return 'bg-secondary';
                },
            };
        }

        // Insights Panel Component
        function insightsPanel() {
            return {
                loading: false,
                insights: [],

                async init() {
                    // Listen for overview ready
                    window.addEventListener('open-interest-overview-ready', (e) => {
                        this.applyOverview(e.detail);
                    });

                    // Initial load
                    setTimeout(() => {
                        if (this.$root?.analytics) {
                            this.applyOverview({ analytics: this.$root.analytics });
                        }
                    }, 100);
                },

                applyOverview(overview) {
                    if (!overview) return;
                    this.insights = overview.analytics?.insights || [];
                },

                getInsightClass(severity) {
                    if (severity === 'high') return 'alert-danger';
                    if (severity === 'medium') return 'alert-warning';
                    if (severity === 'low') return 'alert-info';
                    return 'alert-secondary';
                },

                getInsightIcon(severity) {
                    if (severity === 'high') return '🚨';
                    if (severity === 'medium') return '⚠️';
                    if (severity === 'low') return 'ℹ️';
                    return '📊';
                },
            };
        }

        // Exchange Data Table Component
        function exchangeDataTable() {
            return {
                loading: false,
                exchangeData: [],
                currentFilters: {
                    symbol: 'BTC',
                    pair: 'BTCUSDT',
                    exchange: 'Binance',
                    limit: 2000
                },

                async init() {
                    // Listen for overview ready
                    window.addEventListener('open-interest-overview-ready', (e) => {
                        this.loadData();
                    });

                    // Listen for filter changes
                    window.addEventListener('open-interest-filter-changed', (e) => {
                        console.log("🏦 Exchange Data: Filter changed, reloading data...", e.detail);
                        if (e.detail) {
                            this.currentFilters = { ...e.detail };
                        }
                        this.loadData();
                    });

                    // Initial load with delay to ensure parent is ready
                    setTimeout(() => {
                        this.loadData();
                    }, 500);
                },

                async loadData() {
                    this.loading = true;
                    try {
                        // Use current filters from event or defaults
                        const currentSymbol = this.currentFilters.symbol || 'BTC';
                        const currentPair = this.currentFilters.pair || 'BTCUSDT';
                        const currentExchange = this.currentFilters.exchange || 'Binance';
                        const currentLimit = this.currentFilters.limit || 1000;

                        // Build params - exchange endpoint uses base symbol format (BTC)
                        const params = new URLSearchParams();

                        // Always set exchange to Binance (default)
                        params.append('exchange', currentExchange);
                        
                        // Exchange endpoint uses base symbol format (BTC)
                        if (currentSymbol) {
                            params.append('symbol', currentSymbol);
                        } else if (currentPair) {
                            // Extract base symbol from pair (BTCUSDT -> BTC)
                            const baseSymbol = currentPair.replace('USDT', '');
                            params.append('symbol', baseSymbol);
                        } else {
                            // Default to BTC if no symbol specified
                            params.append('symbol', 'BTC');
                        }

                        params.append('limit', currentLimit);
                        params.append('pivot', 'true');

                        const baseMeta = document.querySelector('meta[name="api-base-url"]');
                        const configuredBase = (baseMeta?.content || "").trim();
                        const apiBase = configuredBase ? configuredBase : 'https://test.dragonfortune.ai';
                        const url = `${apiBase}/api/open-interest/exchange?${params.toString()}`;

                        console.log('🏦 Exchange Data API call:', {
                            symbol: currentSymbol,
                            pair: currentPair,
                            exchange: currentExchange,
                            url: url
                        });


                        const response = await fetch(url);
                        const data = await response.json();
                        
                        // Filter and sort data
                        let filteredData = data.data || [];
                        
                        // Filter by symbol if specified
                        const targetSymbol = currentSymbol || (currentPair ? currentPair.replace('USDT', '') : 'BTC');
                        if (targetSymbol) {
                            filteredData = filteredData.filter(item => 
                                item.symbol_coin === targetSymbol || 
                                item.symbol === targetSymbol ||
                                (item.symbol_coin && item.symbol_coin.toUpperCase() === targetSymbol.toUpperCase())
                            );
                        }
                        
                        // Filter by exchange (default Binance)
                        const targetExchange = currentExchange;
                        filteredData = filteredData.filter(item => 
                            item.exchange === targetExchange ||
                            (item.exchange && item.exchange.toLowerCase() === targetExchange.toLowerCase())
                        );
                        
                        // Sort by timestamp descending (newest first)
                        this.exchangeData = filteredData.sort((a, b) => {
                            if (!a.ts) return 1;  // Move items without ts to end
                            if (!b.ts) return -1;
                            return new Date(b.ts) - new Date(a.ts);
                        });

                        console.log('🏦 Exchange Data filtered results:', {
                            totalReceived: (data.data || []).length,
                            afterFiltering: this.exchangeData.length,
                            targetSymbol: targetSymbol,
                            targetExchange: targetExchange,
                            sampleData: this.exchangeData.slice(0, 3)
                        });
                    } catch (error) {
                        console.error('❌ Error loading exchange data:', error);
                        this.exchangeData = [];
                    } finally {
                        this.loading = false;
                    }
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

                formatOI(value) {
                    if (value === null || value === undefined) return 'N/A';
                    const num = parseFloat(value);
                    if (isNaN(num)) return 'N/A';
                    if (num >= 1e9) return (num / 1e9).toFixed(2) + 'B';
                    if (num >= 1e6) return (num / 1e6).toFixed(2) + 'M';
                    if (num >= 1e3) return (num / 1e3).toFixed(2) + 'K';
                    return num.toFixed(2);
                },
            };
        }

        // History Data Table Component
        function historyDataTable() {
            return {
                loading: false,
                historyData: [],
                currentFilters: {
                    symbol: 'BTC',
                    pair: 'BTCUSDT',
                    interval: '5m',
                    limit: 2000
                },

                async init() {
                    // Listen for overview ready
                    window.addEventListener('open-interest-overview-ready', (e) => {
                        this.loadData();
                    });

                    // Listen for filter changes
                    window.addEventListener('open-interest-filter-changed', (e) => {
                        console.log("📈 History Data: Filter changed, reloading data...", e.detail);
                        if (e.detail) {
                            this.currentFilters = { ...e.detail };
                        }
                        this.loadData();
                    });

                    // Initial load with delay to ensure parent is ready
                    setTimeout(() => {
                        this.loadData();
                    }, 500);
                },

                async loadData() {
                    this.loading = true;
                    try {
                        // Use current filters from event or defaults
                        const currentSymbol = this.currentFilters.symbol || 'BTC';
                        const currentPair = this.currentFilters.pair || 'BTCUSDT';
                        const currentInterval = this.currentFilters.interval || '1h';
                        const currentLimit = this.currentFilters.limit || 1000;

                        // Build params - history endpoint uses pair format (BTCUSDT)
                        const params = new URLSearchParams();
                        params.append('interval', currentInterval);
                        params.append('limit', currentLimit);
                        params.append('pivot', 'true');
                        params.append('with_price', 'true');

                        // History endpoint uses pair format (BTCUSDT)
                        if (currentPair) {
                            params.append('symbol', currentPair);
                        } else if (currentSymbol) {
                            params.append('symbol', currentSymbol + 'USDT');
                        } else {
                            // Default to BTCUSDT if no pair specified
                            params.append('symbol', 'BTCUSDT');
                        }

                        const baseMeta = document.querySelector('meta[name="api-base-url"]');
                        const configuredBase = (baseMeta?.content || "").trim();
                        
                        // Use relative URL as default (same pattern as funding rate)
                        let url = `/api/open-interest/history?${params.toString()}`;
                        if (configuredBase) {
                            const normalizedBase = configuredBase.endsWith("/")
                                ? configuredBase.slice(0, -1)
                                : configuredBase;
                            url = `${normalizedBase}/api/open-interest/history?${params.toString()}`;
                        }


                        console.log('📈 History Data API call:', {
                            pair: currentPair,
                            symbol: currentSymbol,
                            url: url
                        });

                        const response = await fetch(url);
                        const data = await response.json();
                        
                        // Filter and sort data
                        let filteredData = data.data || [];
                        
                        // Filter by pair if specified
                        const targetPair = currentPair || (currentSymbol ? currentSymbol + 'USDT' : 'BTCUSDT');
                        if (targetPair) {
                            filteredData = filteredData.filter(item => 
                                item.pair === targetPair ||
                                item.symbol === targetPair ||
                                (item.pair && item.pair.toUpperCase() === targetPair.toUpperCase())
                            );
                        }
                        
                        // Sort by timestamp descending (newest first)
                        this.historyData = filteredData.sort((a, b) => {
                            if (!a.ts) return 1;  // Move items without ts to end
                            if (!b.ts) return -1;
                            return new Date(b.ts) - new Date(a.ts);
                        });

                        console.log('📈 History Data filtered results:', {
                            totalReceived: (data.data || []).length,
                            afterFiltering: this.historyData.length,
                            targetPair: targetPair,
                            sampleData: this.historyData.slice(0, 3)
                        });
                    } catch (error) {
                        console.error('Error loading history data:', error);
                        this.historyData = [];
                    } finally {
                        this.loading = false;
                    }
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

                formatOI(value) {
                    if (value === null || value === undefined) return 'N/A';
                    const num = parseFloat(value);
                    if (isNaN(num)) return 'N/A';
                    if (num >= 1e9) return (num / 1e9).toFixed(2) + 'B';
                    if (num >= 1e6) return (num / 1e6).toFixed(2) + 'M';
                    if (num >= 1e3) return (num / 1e3).toFixed(2) + 'K';
                    return num.toFixed(2);
                },
            };
        }

        // Overview Panel Component
        function overviewPanel() {
            return {
                loading: false,
                overview: null,
                currentFilters: {
                    symbol: 'BTC',
                    pair: 'BTCUSDT',
                    limit: 2000
                },

                async init() {
                    // Listen for overview ready
                    window.addEventListener('open-interest-overview-ready', (e) => {
                        this.loadData();
                    });

                    // Listen for filter changes
                    window.addEventListener('open-interest-filter-changed', (e) => {
                        console.log("📊 Overview Summary: Filter changed, reloading data...", e.detail);
                        if (e.detail) {
                            this.currentFilters = { ...e.detail };
                        }
                        this.loadData();
                    });

                    // Initial load with delay to ensure parent is ready
                    setTimeout(() => {
                        this.loadData();
                    }, 500);
                },

                async loadData() {
                    this.loading = true;
                    try {
                        // Use current filters from event or defaults
                        const currentSymbol = this.currentFilters.symbol || 'BTC';
                        const currentPair = this.currentFilters.pair || 'BTCUSDT';
                        const currentLimit = this.currentFilters.limit || 2000;

                        const baseMeta = document.querySelector('meta[name="api-base-url"]');
                        const configuredBase = (baseMeta?.content || "").trim();
                        const apiBase = configuredBase ? configuredBase : 'https://test.dragonfortune.ai';

                        // 1. Get filtered summary statistics (with symbol filter)
                        const summaryParams = new URLSearchParams();
                        summaryParams.append('unit', 'usd');
                        summaryParams.append('limit', currentLimit);
                        
                        if (currentPair) {
                            summaryParams.append('symbol', currentPair);
                        } else if (currentSymbol) {
                            summaryParams.append('symbol', currentSymbol + 'USDT');
                        }

                        const summaryUrl = `${apiBase}/api/open-interest/overview?${summaryParams.toString()}`;
                        
                        console.log('📊 Overview Summary API call (filtered):', {
                            pair: currentPair,
                            symbol: currentSymbol,
                            limit: currentLimit,
                            url: summaryUrl
                        });

                        const summaryResponse = await fetch(summaryUrl);
                        const summaryData = await summaryResponse.json();

                        // 2. Get all top symbols (without symbol filter)
                        const topSymbolsParams = new URLSearchParams();
                        topSymbolsParams.append('unit', 'usd');
                        topSymbolsParams.append('limit', currentLimit);
                        // No symbol parameter = get all symbols

                        const topSymbolsUrl = `${apiBase}/api/open-interest/overview?${topSymbolsParams.toString()}`;
                        
                        console.log('📊 Top Symbols API call (all pairs):', {
                            limit: currentLimit,
                            url: topSymbolsUrl
                        });

                        const topSymbolsResponse = await fetch(topSymbolsUrl);
                        const topSymbolsData = await topSymbolsResponse.json();

                        // Combine results: filtered summary + all top symbols
                        this.overview = {
                            summary: summaryData.summary,
                            exchange_breakdown: summaryData.exchange_breakdown,
                            top_symbols: topSymbolsData.top_symbols || []
                        };

                        console.log('📊 Overview Summary results:', {
                            observations: summaryData?.summary?.observations,
                            totalOI: summaryData?.summary?.total_oi,
                            topSymbolsCount: topSymbolsData?.top_symbols?.length || 0
                        });
                    } catch (error) {
                        console.error('Error loading overview data:', error);
                        this.overview = null;
                    } finally {
                        this.loading = false;
                    }
                },

                formatOI(value) {
                    if (value === null || value === undefined) return 'N/A';
                    const num = parseFloat(value);
                    if (isNaN(num)) return 'N/A';
                    if (num >= 1e9) return (num / 1e9).toFixed(2) + 'B';
                    if (num >= 1e6) return (num / 1e6).toFixed(2) + 'M';
                    if (num >= 1e3) return (num / 1e3).toFixed(2) + 'K';
                    return num.toFixed(2);
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
    </style>
@endsection
