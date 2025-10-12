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
                            <option value="BTC">BTC</option>
                            <option value="ETH">ETH</option>
                            <option value="SOL">SOL</option>
                            <option value="XRP">XRP</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-secondary mb-0">Pair:</label>
                        <select class="form-select" style="max-width: 120px;" x-model="globalPair" @change="updatePair()">
                            <option value="">All Pairs</option>
                            <option value="BTCUSDT">BTCUSDT</option>
                            <option value="ETHUSDT">ETHUSDT</option>
                            <option value="SOLUSDT">SOLUSDT</option>
                            <option value="XRPUSDT">XRPUSDT</option>
                        </select>
                    </div>

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

                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-secondary mb-0">Interval:</label>
                        <select class="form-select" style="max-width: 120px;" x-model="globalInterval" @change="updateInterval()">
                            <option value="1m">1 Minute</option>
                            <option value="5m">5 Minutes</option>
                            <option value="15m">15 Minutes</option>
                            <option value="1h">1 Hour</option>
                            <option value="4h">4 Hours</option>
                            <option value="1d">1 Day</option>
                        </select>
        </div>

                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-secondary mb-0">Limit:</label>
                        <select class="form-select" style="max-width: 100px;" x-model="globalLimit" @change="updateLimit()">
                            <option value="100">100</option>
                            <option value="500">500</option>
                            <option value="1000">1000</option>
                            <option value="2000">2000</option>
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
                globalSymbol: "",
                globalPair: "",
                globalExchange: "",
                globalInterval: "1h",
                globalLimit: 1000,
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
                    this.loadOverview();
                },

                updatePair() {
                    console.log("📈 Pair changed to:", this.globalPair);
                    this.loadOverview();
                },

                updateExchange() {
                    console.log("🏦 Exchange changed to:", this.globalExchange);
                    this.loadOverview();
                },

                updateInterval() {
                    console.log("⏰ Interval changed to:", this.globalInterval);
                    this.loadOverview();
                },

                updateLimit() {
                    console.log("📊 Limit changed to:", this.globalLimit);
                    this.loadOverview();
                },

                // Refresh all components
                refreshAll() {
                    this.globalLoading = true;
                    console.log("🔄 Refreshing all components...");
                    this.loadOverview().catch((e) => console.warn("Refresh failed:", e));
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

                async init() {
                    // Listen for overview ready
                    window.addEventListener('open-interest-overview-ready', (e) => {
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
                        // Build params - exchange endpoint uses base symbol format (BTC)
                        const params = new URLSearchParams();

                        // Exchange endpoint uses base symbol format (BTC)
                        if (this.$root.globalSymbol) {
                            params.append('symbol', this.$root.globalSymbol);
                        } else if (this.$root.globalPair) {
                            // Extract base symbol from pair (BTCUSDT -> BTC)
                            const baseSymbol = this.$root.globalPair.replace('USDT', '');
                            params.append('symbol', baseSymbol);
                        }

                        if (this.$root.globalExchange) params.append('exchange', this.$root.globalExchange);
                        params.append('limit', this.$root.globalLimit || 1000);
                        params.append('pivot', 'true');

                        const baseMeta = document.querySelector('meta[name="api-base-url"]');
                        const configuredBase = (baseMeta?.content || "").trim();
                        const apiBase = configuredBase ? configuredBase : 'http://202.155.90.20:8000';
                        const url = `${apiBase}/api/open-interest/exchange?${params.toString()}`;


                        const response = await fetch(url);
                        const data = await response.json();
                        this.exchangeData = data.data || [];
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

                async init() {
                    // Listen for overview ready
                    window.addEventListener('open-interest-overview-ready', (e) => {
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
                        // Build params - history endpoint uses pair format (BTCUSDT)
                        const params = new URLSearchParams();
                        params.append('interval', this.$root.globalInterval || '1h');
                        params.append('limit', this.$root.globalLimit || 1000);
                        params.append('pivot', 'true');
                        params.append('with_price', 'true');

                        // History endpoint uses pair format (BTCUSDT)
                        if (this.$root.globalPair) {
                            params.append('symbol', this.$root.globalPair);
                        } else if (this.$root.globalSymbol) {
                            params.append('symbol', this.$root.globalSymbol + 'USDT');
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


                        const response = await fetch(url);
                        const data = await response.json();
                        this.historyData = data.data || [];
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

                async init() {
                    // Listen for overview ready
                    window.addEventListener('open-interest-overview-ready', (e) => {
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
                        // Build params - overview endpoint uses pair format (BTCUSDT)
                        const params = new URLSearchParams();
                        params.append('unit', 'usd');
                        params.append('limit', this.$root.globalLimit || 1000);

                        // Overview endpoint uses pair format (BTCUSDT)
                        if (this.$root.globalPair) {
                            params.append('symbol', this.$root.globalPair);
                        } else if (this.$root.globalSymbol) {
                            params.append('symbol', this.$root.globalSymbol + 'USDT');
                        }

                        const baseMeta = document.querySelector('meta[name="api-base-url"]');
                        const configuredBase = (baseMeta?.content || "").trim();
                        const apiBase = configuredBase ? configuredBase : 'http://202.155.90.20:8000';
                        const url = `${apiBase}/api/open-interest/overview?${params.toString()}`;


                        const response = await fetch(url);
                        const data = await response.json();
                        this.overview = data;
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
