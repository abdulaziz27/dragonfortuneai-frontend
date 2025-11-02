@extends('layouts.app')

@section('title', 'Macro Overlay | DragonFortune')

@section('content')
    {{--
        Macro Overlay (Raw) Dashboard
        Think like a trader • Build like an engineer • Visualize like a designer

        Blueprint Implementation:
        - DXY, Yields, Fed Funds, CPI, NFP, M2, RRP, TGA
        - Fields: date/ts, metric, value
        - Cadence: DXY/Yields harian, CPI/NFP event-based, M2/RRP/TGA sesuai publikasi

        API Endpoints Consumed:
        1. /api/macro-overlay/raw - Raw macro data
        2. /api/macro-overlay/summary - Summary statistics
        3. /api/macro-overlay/analytics - Comprehensive analytics
        4. /api/macro-overlay/enhanced-analytics - Correlation & volatility analysis
        5. /api/macro-overlay/available-metrics - Available metrics info
        6. /api/macro-overlay/events - Economic events (CPI, NFP)
        7. /api/macro-overlay/events-summary - Events statistics

        Trading Insights:
        - DXY ↑ → USD strong → BTC tends down (inverse correlation -0.72)
        - Yields ↑ → Risk-off → Crypto bearish
        - Fed Funds ↑ → Higher cost of capital → Leverage down
        - CPI high → Inflation → Fed hawkish → Risk assets down
        - M2 ↑ → Liquidity ↑ → Risk assets bullish (+0.81 correlation)
        - RRP ↓ → Money flows to market → Bullish signal
        - NFP strong → Fed hawkish → Risk-off
    --}}

    <div class="d-flex flex-column h-100 gap-3" x-data="macroOverlayRawController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">Macro Overlay (Raw)</h1>
                        <span class="pulse-dot pulse-info"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Raw macro economic data - DXY, Yields, Fed Funds, CPI, NFP, M2, RRP, TGA dengan analitik komprehensif
                    </p>
                </div>

                <!-- Global Controls -->
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <select class="form-select" style="width: 120px;" x-model="globalMetric" @change="updateGlobalFilters()">
                        <option value="">All Metrics</option>
                        <option value="DXY">DXY</option>
                        <option value="YIELD_10Y">10Y Yield</option>
                        <option value="YIELD_2Y">2Y Yield</option>
                        <option value="FED_FUNDS">Fed Funds</option>
                        <option value="M2">M2</option>
                        <option value="RRP">RRP</option>
                        <option value="TGA">TGA</option>
                    </select>

                    <select class="form-select" style="width: 100px;" x-model="globalDaysBack" @change="updateGlobalFilters()">
                        <option value="30">30D</option>
                        <option value="90" selected>90D</option>
                        <option value="180">180D</option>
                        <option value="365">1Y</option>
                    </select>

                    <select class="form-select" style="width: 120px;" x-model="globalCadence" @change="updateGlobalFilters()">
                        <option value="">All Cadence</option>
                        <option value="Daily">Daily</option>
                        <option value="Weekly">Weekly</option>
                        <option value="Monthly">Monthly</option>
                    </select>

                    <button class="btn btn-primary" @click="refreshAllData()" :disabled="globalLoading">
                        <span x-show="!globalLoading">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </span>
                        <span x-show="globalLoading" class="spinner-border spinner-border-sm"></span>
                    </button>
                    
                    <!-- Auto-refresh toggle -->
                    <button 
                        class="btn" 
                        :class="autoRefreshEnabled ? 'btn-success' : 'btn-outline-secondary'"
                        @click="toggleAutoRefresh()"
                        title="Toggle auto-refresh every 5 seconds">
                        <i class="fas" :class="autoRefreshEnabled ? 'fa-pause' : 'fa-play'"></i>
                        <span x-text="autoRefreshEnabled ? 'Auto (5s)' : 'Auto Off'"></span>
                    </button>
                </div>
            </div>
            
            <!-- Last refresh indicator -->
            <div x-show="lastRefreshTime" class="mt-2">
                <small class="text-secondary">
                    <i class="fas fa-clock"></i> Last updated: <span x-text="lastRefreshTime ? lastRefreshTime.toLocaleTimeString() : 'Never'"></span>
                </small>
            </div>
        </div>

        <!-- Quick Stats Overview -->
        <div class="row g-3" x-show="analytics">
            <div class="col-md-3">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="small text-secondary">Market Sentiment</div>
                            <div class="h4 mb-0 fw-bold" x-text="analytics?.market_sentiment?.risk_appetite || 'Loading...'">--</div>
                        </div>
                        <div class="badge" :class="getSentimentBadge(analytics?.market_sentiment?.risk_appetite)">
                            <span x-text="analytics?.market_sentiment?.dollar_strengthening ? 'USD Strong' : 'USD Weak'">--</span>
                        </div>
                    </div>
                    <div class="small text-secondary">
                        Inflation Pressure: <span class="fw-semibold" x-text="analytics?.market_sentiment?.inflation_pressure || 'N/A'">--</span>
                    </div>
                    <div class="small text-secondary">
                        DXY Change: <span class="fw-semibold" x-text="formatPercentage(analytics?.market_sentiment?.details?.dxy_change)">--</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="small text-secondary">Fed Stance</div>
                            <div class="h4 mb-0 fw-bold" x-text="analytics?.monetary_policy?.fed_stance || 'Loading...'">--</div>
                        </div>
                        <div class="badge" :class="getFedStanceBadge(analytics?.monetary_policy?.fed_stance)">
                            <span x-text="analytics?.monetary_policy?.yield_curve || 'N/A'">--</span>
                        </div>
                    </div>
                    <div class="small text-secondary">
                        Liquidity Condition: <span class="fw-semibold" x-text="analytics?.monetary_policy?.liquidity_conditions || 'N/A'">--</span>
                    </div>
                    <div class="small text-secondary">
                        Fed Funds: <span class="fw-semibold" x-text="formatNumber(analytics?.monetary_policy?.details?.fed_funds_rate) + '%'">--</span>
                    </div>
                    <div class="small text-secondary">
                        M2 Growth: <span class="fw-semibold" x-text="formatPercentage(analytics?.monetary_policy?.details?.m2_growth_pct)">--</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="small text-secondary">Dollar Trend</div>
                            <div class="h4 mb-0 fw-bold" x-text="analytics?.trends?.dollar_trend || 'Loading...'">--</div>
                        </div>
                        <div class="badge" :class="getTrendBadge(analytics?.trends?.dollar_trend)">
                            <span x-text="formatPercentage(analytics?.trends?.details?.dxy_trend)">--</span>
                        </div>
                    </div>
                    <div class="small text-secondary">
                        Yield Trend: <span class="fw-semibold" x-text="analytics?.trends?.yield_trend || 'N/A'">--</span>
                    </div>
                    <div class="small text-secondary">
                        Yield Change: <span class="fw-semibold" x-text="formatPercentage(analytics?.trends?.details?.yield_trend)">--</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="small text-secondary">Total Records</div>
                            <div class="h4 mb-0 fw-bold" x-text="analytics?.summary?.total_records || 'Loading...'">--</div>
                        </div>
                        <div class="text-end">
                            <div class="small text-secondary">Date Range</div>
                            <div class="small fw-semibold" x-text="formatDateRange()">--</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Metrics Info -->
        <div class="row g-3" x-show="availableMetrics">
            <div class="col-12">
                <div class="df-panel p-3">
                    <h5 class="mb-3">Available Metrics Overview</h5>
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <div class="row g-2">
                                <template x-for="(metric, index) in (availableMetrics?.overlay_metrics || []).filter(m => !globalCadence || m?.cadence === globalCadence)" :key="`metric-${index}-${metric?.metric || 'unknown'}`">
                                    <div class="col-md-6">
                                        <div class="p-2 rounded border" :class="globalMetric === metric?.metric ? 'border-primary bg-primary bg-opacity-10' : ''">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="fw-semibold small" x-text="metric?.metric || 'N/A'">--</div>
                                                    <div class="small text-secondary" x-text="metric?.description || 'N/A'">--</div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="badge text-bg-info small" x-text="metric?.cadence || 'N/A'">--</div>
                                                </div>
                                            </div>
                                            <div class="mt-1 small text-secondary" x-text="metric?.significance || 'N/A'">--</div>
                                        </div>
                                    </div>
                                </template>
                                
                                <!-- Empty state for metrics -->
                                <div x-show="!availableMetrics?.overlay_metrics || availableMetrics?.overlay_metrics?.filter(m => !globalCadence || m?.cadence === globalCadence)?.length === 0" class="col-12">
                                    <div class="text-center py-4">
                                        <div class="small text-secondary">
                                            <i class="fas fa-chart-line mb-2"></i><br>
                                            <span x-show="globalCadence">No <span x-text="globalCadence"></span> metrics available</span>
                                            <span x-show="!globalCadence">No metrics data available</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1);">
                                <div class="fw-semibold mb-2">Data Sources & Usage</div>
                                <div class="small text-secondary">
                                    <div><strong>Total Overlay Metrics:</strong> <span x-text="availableMetrics?.metadata?.total_overlay_metrics || 0">--</span></div>
                                    <div><strong>Total Event Metrics:</strong> <span x-text="availableMetrics?.metadata?.total_event_metrics || 0">--</span></div>
                                    <div><strong>Update Frequency:</strong> <span x-text="availableMetrics?.metadata?.update_frequency || 'N/A'">--</span></div>
                                    <div class="mt-2"><strong>Metrics Analyzed:</strong></div>
                                    <div x-show="analytics?.summary?.metrics_analyzed" class="small">
                                        <template x-for="metric in analytics?.summary?.metrics_analyzed || []" :key="metric">
                                            <span class="badge text-bg-secondary me-1 mb-1" x-text="metric">--</span>
                                        </template>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div class="fw-semibold small mb-1">Use Cases:</div>
                                    <template x-for="(useCase, index) in (availableMetrics?.metadata?.use_cases || [])" :key="`useCase-${index}-${useCase || 'unknown'}`">
                                        <div class="small text-secondary">• <span x-text="useCase || 'N/A'">--</span></div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Raw Data Visualization -->
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Raw Macro Data</h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" style="width: 150px;" x-model="selectedRawMetric" @change="loadRawData()">
                                <option value="">Select Metric</option>
                                <option value="DXY">DXY (Dollar Index)</option>
                                <option value="YIELD_10Y">10Y Treasury Yield</option>
                                <option value="YIELD_2Y">2Y Treasury Yield</option>
                                <option value="FED_FUNDS">Fed Funds Rate</option>
                                <option value="M2">M2 Money Supply</option>
                                <option value="RRP">Reverse Repo</option>
                                <option value="TGA">Treasury Account</option>
                            </select>
                        </div>
                    </div>
                    <div style="height: 400px;">
                        <canvas id="rawDataChart"></canvas>
                    </div>
                    <div class="mt-3 p-2 rounded" style="background: rgba(34, 197, 94, 0.1);" x-show="selectedRawMetric">
                        <div class="small text-secondary">
                            <strong>Trading Insight:</strong> 
                            <span x-text="getMetricInsight(selectedRawMetric)">Select a metric to see trading insights</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="df-panel p-3 h-100">
                    <h5 class="mb-3">Summary Statistics</h5>
                    <div x-show="summary?.data">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-secondary">Data Points</span>
                                <span class="fw-semibold" x-text="summary?.data?.count || 'N/A'">--</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-secondary">Average Value</span>
                                <span class="fw-semibold" x-text="formatNumber(summary?.data?.avg_value)">--</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-secondary">Max Value</span>
                                <span class="fw-semibold" x-text="formatNumber(summary?.data?.max_value)">--</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-secondary">Min Value</span>
                                <span class="fw-semibold" x-text="formatNumber(summary?.data?.min_value)">--</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-secondary">Trend</span>
                                <span class="fw-semibold" :class="getTrendClass(summary?.data?.trend)" x-text="summary?.data?.trend || 'N/A'">--</span>
                            </div>
                        </div>
                        
                        <!-- Analytics Summary -->
                        <div x-show="analytics?.summary" class="mt-3 pt-3 border-top">
                            <div class="small text-secondary mb-2">Analytics Summary:</div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-secondary">Total Records</span>
                                <span class="fw-semibold" x-text="analytics?.summary?.total_records || 'N/A'">--</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-secondary">Date Range</span>
                                <span class="fw-semibold" x-text="formatDateRange(analytics?.summary?.date_range)">--</span>
                            </div>
                        </div>
                    </div>
                    <div x-show="!summary?.data" class="text-center py-4">
                        <div class="small text-secondary">Select a metric to view statistics</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Analytics: Correlation Matrix -->
        <div class="row g-3" x-show="enhancedAnalytics">
            <div class="col-12">
                <div class="df-panel p-3">
                    <h5 class="mb-3">Enhanced Analytics - Correlation Matrix & Individual Metrics</h5>
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Metric</th>
                                            <th>Current Value</th>
                                            <th>Avg Value</th>
                                            <th>Volatility</th>
                                            <th>Trend</th>
                                            <th>Data Points</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(analytics, metric) in (enhancedAnalytics?.data?.individual_analytics || {})" :key="`analytics-${metric || 'unknown'}`">
                                            <tr>
                                                <td class="fw-semibold" x-text="metric || 'N/A'">--</td>
                                                <td x-text="formatNumber(analytics?.current_value)">--</td>
                                                <td x-text="formatNumber(analytics?.avg_value)">--</td>
                                                <td x-text="formatNumber(analytics?.volatility)">--</td>
                                                <td :class="getTrendClass(analytics?.trend)" x-text="analytics?.trend || 'neutral'">--</td>
                                                <td x-text="analytics?.data_points || 0">--</td>
                                            </tr>
                                        </template>
                                        
                                        <!-- Empty state for analytics table -->
                                        <tr x-show="!enhancedAnalytics?.data?.individual_analytics || Object.keys(enhancedAnalytics?.data?.individual_analytics || {}).length === 0">
                                            <td colspan="6" class="text-center py-4">
                                                <div class="small text-secondary">
                                                    <i class="fas fa-chart-bar mb-2"></i><br>
                                                    No enhanced analytics data available
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="p-3 rounded" style="background: rgba(245, 158, 11, 0.1);">
                                <div class="fw-semibold mb-2">Correlation Insights</div>
                                <div class="small text-secondary">
                                    <div class="mb-2">
                                        <strong>Strong Correlations:</strong>
                                        <ul class="mb-1 ps-3">
                                            <li>DXY vs BTC: -0.72 (Strong Inverse)</li>
                                            <li>M2 vs BTC: +0.81 (Strong Positive)</li>
                                            <li>Yields vs BTC: -0.65 (Moderate Inverse)</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <strong>Trading Implications:</strong>
                                        <ul class="mb-0 ps-3">
                                            <li>USD strength typically bearish for crypto</li>
                                            <li>Money supply expansion bullish for risk assets</li>
                                            <li>Rising yields indicate risk-off sentiment</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Economic Events Section -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Economic Events (CPI, NFP, Core CPI)</h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" style="width: 120px;" x-model="selectedEventType" @change="loadEvents()">
                                <option value="">All Events</option>
                                <option value="CPI">CPI</option>
                                <option value="CPI_CORE">Core CPI</option>
                                <option value="NFP">NFP</option>
                            </select>
                        </div>
                    </div>
                    <!-- Events Table -->
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-hover">
                            <thead class="sticky-top" style="background: var(--bs-body-bg); z-index: 1;">
                                <tr>
                                    <th>Event Type</th>
                                    <th>Release Date</th>
                                    <th class="text-end">Actual</th>
                                    <th class="text-end">Forecast</th>
                                    <th class="text-end">Previous</th>
                                    <th>Series ID</th>
                                    <th>Source</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(event, index) in (events?.data || [])" :key="`event-${index}-${event?.event_type || 'unknown'}-${event?.ts || Date.now()}`">
                                    <tr>
                                        <td>
                                            <span class="badge" :class="getEventTypeBadge(event?.event_type)" x-text="event?.event_type || 'N/A'">--</span>
                                        </td>
                                        <td class="small" x-text="formatDate(event?.release_date)">--</td>
                                        <td class="text-end fw-bold" x-text="formatNumber(event?.actual_value)">--</td>
                                        <td class="text-end" x-text="event?.forecast_value !== null ? formatNumber(event?.forecast_value) : 'N/A'">--</td>
                                        <td class="text-end" x-text="event?.previous_value !== null ? formatNumber(event?.previous_value) : 'N/A'">--</td>
                                        <td class="small text-secondary" x-text="event?.series_id || 'N/A'">--</td>
                                        <td class="small text-secondary" x-text="event?.source || 'N/A'">--</td>
                                    </tr>
                                </template>
                                
                                <!-- Empty state -->
                                <tr x-show="!events?.data || events?.data?.length === 0">
                                    <td colspan="7" class="text-center py-4">
                                        <div class="small text-secondary">
                                            <i class="fas fa-calendar-alt mb-2"></i><br>
                                            No economic events data available
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Event Impact Guide (moved from Events Summary) -->
                    <div class="mt-3 p-3 rounded" style="background: rgba(59, 130, 246, 0.1);">
                        <div class="fw-semibold mb-2">📊 Event Impact Guide</div>
                        <div class="small text-secondary">
                            <div class="mb-1"><strong>CPI > Expected:</strong> Fed hawkish → Crypto bearish</div>
                            <div class="mb-1"><strong>NFP Strong (>200K):</strong> Fed hawkish → Risk-off</div>
                            <div><strong>Core CPI Rising:</strong> Persistent inflation → Rate hikes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Market Sentiment & Trading Insights -->
        <div class="row g-3" x-show="analytics">
            <div class="col-12">
                <div class="df-panel p-4">
                    <h5 class="mb-3">Market Sentiment & Trading Insights</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444;">
                                <div class="fw-bold mb-2 text-danger">Risk-Off Indicators</div>
                                <div class="small text-secondary">
                                    <div class="mb-1">
                                        <strong>DXY Level:</strong> <span x-text="formatNumber(analytics?.market_sentiment?.details?.dxy_level)">--</span>
                                        <span x-show="analytics?.market_sentiment?.dollar_strengthening" class="text-danger ms-1">↗️ Strengthening</span>
                                    </div>
                                    <div class="mb-1">
                                        <strong>10Y Yield:</strong> <span x-text="formatNumber(analytics?.market_sentiment?.details?.yield_10y_level) + '%'">--</span>
                                    </div>
                                    <div class="mb-1">
                                        <strong>Fed Stance:</strong> <span x-text="analytics?.monetary_policy?.fed_stance">--</span>
                                    </div>
                                    <div class="small mt-2 p-2 rounded" style="background: rgba(255,255,255,0.5);">
                                        <strong>Trading Signal:</strong> 
                                        <span x-show="analytics?.market_sentiment?.dollar_strengthening && analytics?.monetary_policy?.fed_stance === 'Tightening'">
                                            Strong USD + Fed Tightening = Crypto Bearish
                                        </span>
                                        <span x-show="!analytics?.market_sentiment?.dollar_strengthening || analytics?.monetary_policy?.fed_stance !== 'Tightening'">
                                            Mixed signals - Monitor closely
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e;">
                                <div class="fw-bold mb-2 text-success">Risk-On Indicators</div>
                                <div class="small text-secondary">
                                    <div class="mb-1">
                                        <strong>RRP Level:</strong> <span x-text="formatNumber(analytics?.market_sentiment?.details?.rrp_level) + 'B'">--</span>
                                    </div>
                                    <div class="mb-1">
                                        <strong>M2 Growth:</strong> <span x-text="formatPercentage(analytics?.monetary_policy?.details?.m2_growth_pct)">--</span>
                                    </div>
                                    <div class="mb-1">
                                        <strong>Liquidity:</strong> <span x-text="analytics?.monetary_policy?.liquidity_conditions">--</span>
                                    </div>
                                    <div class="small mt-2 p-2 rounded" style="background: rgba(255,255,255,0.5);">
                                        <strong>Trading Signal:</strong>
                                        <span x-show="analytics?.monetary_policy?.liquidity_conditions === 'Easing'">
                                            Liquidity Easing = Crypto Bullish
                                        </span>
                                        <span x-show="analytics?.monetary_policy?.liquidity_conditions !== 'Easing'">
                                            Liquidity Tightening = Cautious
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(245, 158, 11, 0.1); border-left: 4px solid #f59e0b;">
                                <div class="fw-bold mb-2 text-warning">Trend Analysis</div>
                                <div class="small text-secondary">
                                    <div class="mb-1">
                                        <strong>Dollar Trend:</strong> 
                                        <span :class="getTrendClass(analytics?.trends?.dollar_trend)" x-text="analytics?.trends?.dollar_trend">--</span>
                                        <span x-text="formatPercentage(analytics?.trends?.details?.dxy_trend)">--</span>
                                    </div>
                                    <div class="mb-1">
                                        <strong>Yield Trend:</strong> 
                                        <span :class="getTrendClass(analytics?.trends?.yield_trend)" x-text="analytics?.trends?.yield_trend">--</span>
                                        <span x-text="formatPercentage(analytics?.trends?.details?.yield_trend)">--</span>
                                    </div>
                                    <div class="mb-1">
                                        <strong>Liquidity Trend:</strong> <span x-text="analytics?.trends?.liquidity_trend">--</span>
                                    </div>
                                    <div class="small mt-2 p-2 rounded" style="background: rgba(255,255,255,0.5);">
                                        <strong>Strategy:</strong> 
                                        <span x-show="analytics?.trends?.dollar_trend === 'Weakening' && analytics?.trends?.yield_trend === 'Falling'">
                                            USD Weak + Yields Down = Risk-On Setup
                                        </span>
                                        <span x-show="analytics?.trends?.dollar_trend === 'Strengthening' || analytics?.trends?.yield_trend === 'Rising'">
                                            Monitor for risk-off conditions
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Macro Correlation Matrix -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4">
                    <h5 class="mb-3">Macro Correlation with BTC</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444;">
                                <div class="fw-bold mb-2 text-danger">Inverse Correlation (Bearish Signals)</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li><strong>DXY ↑</strong> → BTC ↓ (r = -0.72)</li>
                                        <li><strong>Yields ↑</strong> → BTC ↓ (r = -0.65)</li>
                                        <li><strong>Fed Funds ↑</strong> → BTC ↓ (r = -0.58)</li>
                                        <li><strong>CPI ↑</strong> → Fed hawkish → BTC ↓</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e;">
                                <div class="fw-bold mb-2 text-success">Positive Correlation (Bullish Signals)</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li><strong>M2 ↑</strong> → BTC ↑ (r = +0.81)</li>
                                        <li><strong>RRP ↓</strong> → BTC ↑ (r = +0.68)</li>
                                        <li><strong>Risk Assets ↑</strong> → BTC ↑ (r = +0.75)</li>
                                        <li><strong>DXY ↓</strong> → Liquidity flows to BTC</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(245, 158, 11, 0.1); border-left: 4px solid #f59e0b;">
                                <div class="fw-bold mb-2 text-warning">Event-Based Impact</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li><strong>CPI > Expected</strong> → Volatility spike</li>
                                        <li><strong>NFP Strong</strong> → Fed hawkish → Risk-off</li>
                                        <li><strong>FOMC Meeting</strong> → High volatility window</li>
                                        <li><strong>Yield Inversion</strong> → Recession fears → Flight to safety</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Simple Loading Indicator -->
        <div x-show="globalLoading" class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <span class="text-secondary">Loading data...</span>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    <script src="{{ asset('js/macro-overlay-raw-controller.js') }}"></script>

    <script>
        function macroOverlayRawController() {
            return {
                // Global state
                globalLoading: false,
                globalMetric: '',
                globalDaysBack: 90,
                globalCadence: '',
                
                // Calculated date range (NEW)
                startDate: null,
                endDate: null,
                
                // Selected filters
                selectedRawMetric: 'DXY',
                selectedEventType: '',
                
                // API data
                rawData: null,
                summary: null,
                analytics: null,
                enhancedAnalytics: null,
                availableMetrics: null,
                events: null,
                eventsSummary: null,
                
                // Controller instance
                controller: null,
                
                // Chart ID for DOM storage
                chartId: 'rawDataChart',
                
                // Auto-refresh
                autoRefreshInterval: null,
                autoRefreshEnabled: true,
                autoRefreshSeconds: 5,
                lastRefreshTime: null,

                // Chart storage methods (DOM-based to avoid Alpine reactivity)
                getChart() {
                    const canvas = document.getElementById(this.chartId);
                    return canvas ? canvas._chartInstance : null;
                },

                setChart(chartInstance) {
                    const canvas = document.getElementById(this.chartId);
                    if (canvas) canvas._chartInstance = chartInstance;
                },

                // Calculate date range from daysBack
                // This will be used for BOTH start_date/end_date AND days_back parameters
                // Example: If globalDaysBack = 30
                //   - start_date = today - 30 days (e.g., "2024-09-15")
                //   - end_date = today (e.g., "2024-10-15")
                //   - days_back = 30
                calculateDateRange() {
                    const today = new Date();
                    const startDate = new Date(today);
                    startDate.setDate(today.getDate() - this.globalDaysBack);
                    
                    this.endDate = today.toISOString().split('T')[0];
                    this.startDate = startDate.toISOString().split('T')[0];
                    
                    console.log(`📅 Date range calculated: ${this.startDate} to ${this.endDate} (${this.globalDaysBack} days)`);
                },

                // Initialize dashboard
                async init() {
                    console.log("🚀 Macro Overlay (Raw) Dashboard initialized");
                    
                    // Initialize controller
                    this.controller = new MacroOverlayRawController();
                    
                    // Calculate initial date range
                    this.calculateDateRange();
                    
                    // Load initial data first
                    await this.loadInitialData();
                    
                    // Initialize charts after data is loaded
                    this.$nextTick(() => {
                        this.initCharts();
                    });
                    
                    // Start auto-refresh
                    this.startAutoRefresh();
                    
                    console.log("✅ Macro Overlay (Raw) dashboard ready");
                },

                // Start auto-refresh timer
                startAutoRefresh() {
                    if (!this.autoRefreshEnabled) return;
                    
                    console.log(`🔄 Auto-refresh enabled: every ${this.autoRefreshSeconds} seconds`);
                    
                    this.autoRefreshInterval = setInterval(async () => {
                        if (this.autoRefreshEnabled && !this.globalLoading) {
                            console.log('🔄 Auto-refreshing data...');
                            this.lastRefreshTime = new Date();
                            
                            // Recalculate date range to get latest data
                            this.calculateDateRange();
                            
                            // Refresh all data
                            await this.loadInitialData();
                            
                            // Refresh raw data if metric is selected
                            if (this.selectedRawMetric) {
                                await this.loadRawData();
                            }
                        }
                    }, this.autoRefreshSeconds * 1000);
                },

                // Stop auto-refresh
                stopAutoRefresh() {
                    if (this.autoRefreshInterval) {
                        clearInterval(this.autoRefreshInterval);
                        this.autoRefreshInterval = null;
                        console.log('⏸️ Auto-refresh stopped');
                    }
                },

                // Toggle auto-refresh
                toggleAutoRefresh() {
                    this.autoRefreshEnabled = !this.autoRefreshEnabled;
                    
                    if (this.autoRefreshEnabled) {
                        this.startAutoRefresh();
                    } else {
                        this.stopAutoRefresh();
                    }
                },

                // Load all initial data
                async loadInitialData() {
                    this.globalLoading = true;
                    
                    try {
                        console.log("📊 Loading initial macro data...");
                        console.log(`🔍 Parameters: start_date=${this.startDate}, end_date=${this.endDate}, days_back=${this.globalDaysBack}`);
                        
                        // Load all data in parallel with date range parameters
                        const results = await this.controller.fetchAllData({
                            start_date: this.startDate,
                            end_date: this.endDate,
                            days_back: this.globalDaysBack,
                            metric: this.globalMetric || null
                        });
                        
                        // Assign results
                        this.analytics = results.analytics;
                        this.enhancedAnalytics = results.enhancedAnalytics;
                        this.availableMetrics = results.availableMetrics;
                        this.events = results.events;
                        this.eventsSummary = results.eventsSummary;
                        
                        // Sort events by release_date descending (newest first)
                        if (this.events?.data && Array.isArray(this.events.data)) {
                            this.events.data.sort((a, b) => {
                                return new Date(b.release_date) - new Date(a.release_date);
                            });
                            console.log(`✅ Events loaded and sorted: ${this.events.data.length} events`);
                            if (this.events.data.length > 0) {
                                console.log(`📅 First event (newest): ${this.events.data[0]?.event_type} - ${this.events.data[0]?.release_date}`);
                                console.log(`📅 Last event (oldest): ${this.events.data[this.events.data.length - 1]?.event_type} - ${this.events.data[this.events.data.length - 1]?.release_date}`);
                            }
                        }
                        
                        console.log("✅ Initial data loaded successfully");
                        console.log(`📈 Analytics date range: ${this.analytics?.summary?.date_range?.earliest} to ${this.analytics?.summary?.date_range?.latest}`);
                        console.log(`📅 Events count: ${this.events?.data?.length || 0}, Events summary: ${this.eventsSummary?.data?.total_events || 0}`);
                        
                    } catch (error) {
                        console.error("❌ Error loading initial data:", error);
                        
                        // Set fallback data on complete failure
                        this.analytics = {
                            market_sentiment: { risk_appetite: 'N/A', dollar_strengthening: false, inflation_pressure: 'N/A' },
                            monetary_policy: { fed_stance: 'N/A', liquidity_conditions: 'N/A', yield_curve: 'N/A' },
                            trends: { dollar_trend: 'N/A', yield_trend: 'N/A', liquidity_trend: 'N/A' },
                            summary: { total_records: 0, date_range: { earliest: null, latest: null } }
                        };
                        this.enhancedAnalytics = { data: { individual_analytics: {}, correlation_matrix: {} } };
                        this.availableMetrics = { overlay_metrics: [], metadata: { total_overlay_metrics: 0, use_cases: [] } };
                        this.events = { data: [] };
                        this.eventsSummary = { data: { total_events: 0, avg_surprise_pct: null } };
                        
                    } finally {
                        this.globalLoading = false;
                    }
                },

                // Load raw data for selected metric
                async loadRawData() {
                    if (!this.selectedRawMetric) return;
                    
                    try {
                        console.log(`📈 Loading raw data for ${this.selectedRawMetric}`);
                        console.log(`🔍 Date range: ${this.startDate} to ${this.endDate}`);
                        
                        // Fetch raw data for selected metric with date range
                        const rawData = await this.controller.fetchRawData({ 
                            metric: this.selectedRawMetric,
                            start_date: this.startDate,
                            end_date: this.endDate,
                            limit: 2000 
                        });
                        
                        // Get summary data
                        const summary = await this.controller.fetchSummary({ 
                            metric: this.selectedRawMetric,
                            source: 'FRED',
                            days_back: this.globalDaysBack 
                        });
                        
                        // Assign results
                        this.rawData = rawData || { data: [] };
                        this.summary = summary || { 
                            data: { count: 0, avg_value: null, max_value: null, min_value: null, trend: 'neutral' }
                        };
                        
                        // Sort data by date descending (newest first) for display
                        if (this.rawData.data && Array.isArray(this.rawData.data)) {
                            this.rawData.data.sort((a, b) => {
                                return new Date(b.date) - new Date(a.date);
                            });
                            console.log(`✅ Raw data sorted: ${this.rawData.data.length} records, newest: ${this.rawData.data[0]?.date}`);
                        }
                        
                        // Update chart
                        this.updateRawDataChart();
                        
                    } catch (error) {
                        console.error("❌ Error loading raw data:", error);
                        // Set fallback data
                        this.rawData = { data: [] };
                        this.summary = { data: { count: 0, avg_value: null, max_value: null, min_value: null, trend: 'neutral' } };
                    }
                },

                // Load events for selected type
                async loadEvents() {
                    try {
                        console.log(`📅 Loading events for ${this.selectedEventType || 'all types'}`);
                        console.log(`🔍 Date range: ${this.startDate} to ${this.endDate}`);
                        
                        // Calculate months_back from globalDaysBack
                        const monthsBack = Math.ceil(this.globalDaysBack / 30);
                        console.log(`🔍 Months back: ${monthsBack}`);
                        
                        const [events, eventsSummary] = await Promise.all([
                            this.controller.fetchEvents({ 
                                event_type: this.selectedEventType || null,
                                start_date: this.startDate,
                                end_date: this.endDate,
                                limit: 100 
                            }),
                            this.controller.fetchEventsSummary({ 
                                event_type: this.selectedEventType || null,
                                months_back: monthsBack 
                            })
                        ]);
                        
                        this.events = events;
                        this.eventsSummary = eventsSummary;
                        
                        // Sort events by release_date descending (newest first)
                        if (this.events.data && Array.isArray(this.events.data)) {
                            this.events.data.sort((a, b) => {
                                return new Date(b.release_date) - new Date(a.release_date);
                            });
                            console.log(`✅ Events sorted: ${this.events.data.length} events, newest: ${this.events.data[0]?.release_date}`);
                        }
                        
                    } catch (error) {
                        console.error("❌ Error loading events:", error);
                    }
                },

                // Update global filters
                async updateGlobalFilters() {
                    console.log(`🔄 Updating filters: ${this.globalDaysBack} days, metric: ${this.globalMetric || 'all'}`);
                    
                    // Recalculate date range based on new globalDaysBack
                    this.calculateDateRange();
                    
                    // Reload all data with new filters
                    // Note: loadInitialData() already loads events, so no need to call loadEvents() separately
                    await this.loadInitialData();
                    
                    // Reload raw data if metric is selected
                    if (this.selectedRawMetric) {
                        await this.loadRawData();
                    }
                },

                // Refresh all data
                async refreshAllData() {
                    console.log('🔄 Refreshing all data...');
                    
                    // Recalculate date range
                    this.calculateDateRange();
                    
                    // Reload all data (including events)
                    await this.loadInitialData();
                    
                    // Reload raw data if metric is selected
                    if (this.selectedRawMetric) {
                        await this.loadRawData();
                    }
                },

                // Initialize charts
                initCharts() {
                    // Wait for Chart.js to be ready
                    if (typeof Chart !== 'undefined') {
                        this.createRawDataChart();
                        
                        // Load raw data after chart is created
                        setTimeout(() => {
                            this.loadRawData();
                        }, 100);
                    } else {
                        setTimeout(() => this.initCharts(), 100);
                    }
                },

                // Create raw data chart
                createRawDataChart() {
                    const canvas = document.getElementById(this.chartId);
                    if (!canvas) {
                        console.warn('⚠️ Canvas not found for raw data chart');
                        return;
                    }

                    if (typeof Chart === 'undefined') {
                        console.error('❌ Chart.js not loaded');
                        return;
                    }

                    const ctx = canvas.getContext('2d');

                    // CRITICAL: Create chart OUTSIDE Alpine reactivity scope using queueMicrotask
                    queueMicrotask(() => {
                        // Create gradient outside Alpine reactivity to prevent infinite loop
                        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
                        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

                        const chartInstance = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: [],
                                datasets: [{
                                    label: 'Value',
                                    data: [],
                                    borderColor: 'rgb(59, 130, 246)',
                                    backgroundColor: gradient,
                                    tension: 0.4,
                                    fill: true,
                                    borderWidth: 2,
                                    pointRadius: 0,
                                    pointHoverRadius: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: (context) => {
                                                const metric = this.selectedRawMetric;
                                                const value = context.parsed.y;
                                                return `${metric}: ${this.formatNumber(value)}`;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: { 
                                        display: true,
                                        grid: { display: false }
                                    },
                                    y: { 
                                        display: true, 
                                        position: 'right',
                                        grid: { display: true }
                                    }
                                }
                            }
                        });

                        this.setChart(chartInstance);
                        console.log('✅ Raw data chart initialized');
                    });
                },

                // Update raw data chart
                updateRawDataChart() {
                    const chart = this.getChart();
                    if (!chart) {
                        console.warn('⚠️ Cannot update chart: chart not found');
                        return;
                    }
                    
                    try {
                        // Handle empty or no data
                        if (!this.rawData?.data || this.rawData.data.length === 0) {
                            chart.data.labels = ['No Data'];
                            chart.data.datasets[0].data = [0];
                            chart.data.datasets[0].label = `${this.selectedRawMetric} (No Data)`;
                        } else {
                            const chartData = this.controller.formatForChart(this.rawData);
                            
                            chart.data.labels = chartData.labels;
                            chart.data.datasets[0].data = chartData.values;
                            chart.data.datasets[0].label = this.selectedRawMetric;
                            
                            // Update colors based on metric
                            const colors = this.getMetricColors(this.selectedRawMetric);
                            chart.data.datasets[0].borderColor = colors.border;
                            chart.data.datasets[0].backgroundColor = colors.background;
                        }
                        
                        // CRITICAL: Use queueMicrotask to break Alpine reactivity cycle
                        queueMicrotask(() => {
                            try {
                                if (chart && chart.update && typeof chart.update === 'function') {
                                    chart.update('none');
                                }
                            } catch (updateError) {
                                console.error('❌ Chart update error:', updateError);
                            }
                        });
                        
                    } catch (error) {
                        console.error('❌ Error updating chart:', error);
                    }
                },

                // Get metric colors
                getMetricColors(metric) {
                    const colorMap = {
                        'DXY': { border: 'rgb(239, 68, 68)', background: 'rgba(239, 68, 68, 0.1)' },
                        'YIELD_10Y': { border: 'rgb(59, 130, 246)', background: 'rgba(59, 130, 246, 0.1)' },
                        'YIELD_2Y': { border: 'rgb(34, 197, 94)', background: 'rgba(34, 197, 94, 0.1)' },
                        'FED_FUNDS': { border: 'rgb(245, 158, 11)', background: 'rgba(245, 158, 11, 0.1)' },
                        'M2': { border: 'rgb(168, 85, 247)', background: 'rgba(168, 85, 247, 0.1)' },
                        'RRP': { border: 'rgb(236, 72, 153)', background: 'rgba(236, 72, 153, 0.1)' },
                        'TGA': { border: 'rgb(14, 165, 233)', background: 'rgba(14, 165, 233, 0.1)' }
                    };
                    return colorMap[metric] || { border: 'rgb(156, 163, 175)', background: 'rgba(156, 163, 175, 0.1)' };
                },

                // Get metric trading insight
                getMetricInsight(metric) {
                    const insights = {
                        'DXY': 'DXY ↑ → USD strong → BTC tends down (inverse correlation -0.72). Watch for 105+ levels as resistance for crypto.',
                        'YIELD_10Y': '10Y Yield ↑ → Risk-off → Crypto bearish. Above 4.5% typically signals risk-off environment.',
                        'YIELD_2Y': '2Y Yield reflects Fed policy expectations. Rising = hawkish Fed = crypto bearish.',
                        'FED_FUNDS': 'Fed Funds ↑ → Higher cost of capital → Leverage down → Crypto down. Rate cuts are crypto bullish.',
                        'M2': 'M2 ↑ → More liquidity → Risk assets bullish (+0.81 correlation with BTC). Money supply expansion is crypto positive.',
                        'RRP': 'RRP ↓ → Money flows to market → Bullish signal. Lower RRP means more liquidity in the system.',
                        'TGA': 'TGA ↑ → Government withdraws from market → Bearish. Treasury spending (TGA down) adds liquidity.'
                    };
                    return insights[metric] || 'Select a metric to see trading insights.';
                },

                // Format helpers
                formatNumber(value, decimals = 2) {
                    return this.controller?.formatNumber(value, decimals) || 'N/A';
                },

                formatPercentage(value, decimals = 2) {
                    return this.controller?.formatPercentage(value, decimals) || 'N/A';
                },

                formatDate(dateString) {
                    if (!dateString) return 'N/A';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-US', { 
                        month: 'short', 
                        day: 'numeric', 
                        year: 'numeric' 
                    });
                },

                formatDateRange() {
                    if (!this.analytics?.summary?.date_range) return 'N/A';
                    const range = this.analytics.summary.date_range;
                    const earliest = this.formatDate(range.earliest);
                    const latest = this.formatDate(range.latest);
                    return `${earliest} - ${latest}`;
                },

                // Badge helpers
                getSentimentBadge(sentiment) {
                    switch (sentiment?.toLowerCase()) {
                        case 'high': return 'text-bg-success';
                        case 'moderate': return 'text-bg-warning';
                        case 'low': return 'text-bg-danger';
                        default: return 'text-bg-secondary';
                    }
                },

                getFedStanceBadge(stance) {
                    switch (stance?.toLowerCase()) {
                        case 'tightening': return 'text-bg-danger';
                        case 'easing': return 'text-bg-success';
                        case 'neutral': return 'text-bg-warning';
                        default: return 'text-bg-secondary';
                    }
                },

                getTrendBadge(trend) {
                    switch (trend?.toLowerCase()) {
                        case 'strengthening':
                        case 'rising': return 'text-bg-success';
                        case 'weakening':
                        case 'falling': return 'text-bg-danger';
                        default: return 'text-bg-secondary';
                    }
                },

                getTrendClass(trend) {
                    return this.controller?.getTrendClass(trend) || 'text-secondary';
                },

                getEventTypeClass(eventType) {
                    switch (eventType) {
                        case 'CPI': return 'border-danger';
                        case 'CPI_CORE': return 'border-warning';
                        case 'NFP': return 'border-success';
                        default: return 'border-secondary';
                    }
                },

                getEventTypeBadge(eventType) {
                    switch (eventType) {
                        case 'CPI': return 'text-bg-danger';
                        case 'CPI_CORE': return 'text-bg-warning';
                        case 'NFP': return 'text-bg-success';
                        default: return 'text-bg-secondary';
                    }
                },


                // Cleanup when component is destroyed
                destroy() {
                    // Stop auto-refresh
                    this.stopAutoRefresh();
                    
                    // Destroy chart
                    const chart = this.getChart();
                    if (chart) {
                        chart.destroy();
                        this.setChart(null);
                    }
                    
                    console.log('🧹 Macro Overlay dashboard cleaned up');
                }
            };
        }
    </script>

    <style>
        .pulse-info {
            background-color: #3b82f6;
            box-shadow: 0 0 0 rgba(59, 130, 246, 0.7);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
            }
            50% {
                box-shadow: 0 0 0 8px rgba(59, 130, 246, 0);
            }
        }
    </style>
@endsection
