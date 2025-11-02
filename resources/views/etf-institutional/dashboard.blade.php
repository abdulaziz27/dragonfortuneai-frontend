@extends('layouts.app')

@section('title', 'ETF Institutional | DragonFortune')

@section('content')
    {{--
        ETF & Institutional Dashboard
        Think like a trader • Build like an engineer • Visualize like a designer

        Interpretasi Trading:
        - Positive ETF Flow → Institutional accumulation → Bullish medium-term
        - Premium > 50bps → Overvaluation risk → Take profit consideration
        - COT Long/Short ratio → Track smart money positioning
        - High creations/low redemptions → Strong demand signal
    --}}

    <div class="d-flex flex-column h-100 gap-3" x-data="etfInstitutionalController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">ETF & Institutional Dashboard</h1>
                        <span class="pulse-dot pulse-success"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Monitor arus institusional, ETF flow, premium/discount, dan posisi COT untuk Bitcoin
                    </p>
                </div>

                <!-- Global Controls -->
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <!-- Period Filter -->
                    <select class="form-select" style="width: 140px;" 
                            x-model="selectedPeriod" 
                            @change="handlePeriodChange()">
                        <option value="30">Last 30 Days</option>
                        <option value="60">Last 60 Days</option>
                        <option value="90">Last 90 Days</option>
                        <option value="180">Last 180 Days</option>
                    </select>

                    <!-- Issuer Filter (Global) -->
                    <select class="form-select" style="width: 150px;"
                            x-model="selectedIssuer" 
                            @change="handleIssuerChange()">
                        <option value="all">All Issuers</option>
                        <template x-for="issuer in issuerOptions.slice(1)" :key="issuer">
                            <option :value="issuer" x-text="issuer"></option>
                        </template>
                    </select>

                    <!-- Ticker Filter (Global) -->
                    <select class="form-select" style="width: 150px;"
                            x-model="selectedTicker" 
                            @change="handleTickerChange()">
                        <option value="all">All Tickers</option>
                        <template x-for="ticker in tickerOptions.slice(1)" :key="ticker">
                            <option :value="ticker" x-text="ticker"></option>
                        </template>
                    </select>

                    <!-- Manual Refresh -->
                    <button class="btn btn-primary" @click="refreshAll()" :disabled="loading">
                        <i class="bi bi-arrow-clockwise"></i>
                        <span x-show="!loading">Refresh</span>
                        <span x-show="loading" class="spinner-border spinner-border-sm"></span>
                    </button>

                    <!-- Auto-Refresh Toggle -->
                    <button class="btn" 
                            :class="autoRefreshEnabled ? 'btn-success' : 'btn-outline-secondary'"
                            @click="toggleAutoRefresh()"
                            title="Toggle auto-refresh (5 seconds)">
                        <i class="bi" :class="autoRefreshEnabled ? 'bi-arrow-repeat' : 'bi-pause'"></i>
                        <span x-text="autoRefreshEnabled ? 'Auto (5s)' : 'Paused'"></span>
                    </button>

                    <!-- Last Updated -->
                    <span class="text-secondary small" x-show="lastUpdated" x-cloak>
                        <i class="bi bi-clock"></i>
                        Last updated: <span x-text="lastUpdated"></span>
                    </span>
                </div>
            </div>
        </div>

        <!-- 1. ETF Flow & Institutional Overview -->
        <div class="row g-3">
            <!-- ETF Flow Meter Gauge -->
            <div class="col-lg-4">
                <div class="df-panel p-4 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="mb-1">ETF Flow Meter</h5>
                        <small class="text-secondary">Total arus masuk/keluar harian ETF spot</small>
                    </div>

                    <!-- Gauge Display -->
                    <div class="text-center mb-3 flex-shrink-0">
                        <div class="position-relative d-inline-block" style="width: 200px; height: 200px;">
                            <!-- Circular Gauge Background -->
                            <svg viewBox="0 0 200 200" class="w-100 h-100">
                                <!-- Background Arc -->
                                <path d="M 20 100 A 80 80 0 0 1 180 100"
                                      fill="none"
                                      stroke="#e5e7eb"
                                      stroke-width="20"
                                      stroke-linecap="round"/>

                                <!-- Colored Segments: Outflow → Neutral → Inflow -->
                                <path d="M 20 100 A 80 80 0 0 1 60 38"
                                      fill="none"
                                      stroke="#ef4444"
                                      stroke-width="20"
                                      stroke-linecap="round"/>
                                <path d="M 60 38 A 80 80 0 0 1 100 20"
                                      fill="none"
                                      stroke="#f59e0b"
                                      stroke-width="20"
                                      stroke-linecap="round"/>
                                <path d="M 100 20 A 80 80 0 0 1 140 38"
                                      fill="none"
                                      stroke="#22c55e"
                                      stroke-width="20"
                                      stroke-linecap="round"/>
                                <path d="M 140 38 A 80 80 0 0 1 180 100"
                                      fill="none"
                                      stroke="#10b981"
                                      stroke-width="20"
                                      stroke-linecap="round"/>

                                <!-- Indicator Needle -->
                                <!-- Map flow -500M to +500M to 180°-360° arc -->
                                <line :x1="100" :y1="100"
                                      :x2="100 + 70 * Math.cos((180 + getFlowAngle()) * Math.PI / 180)"
                                      :y2="100 + 70 * Math.sin((180 + getFlowAngle()) * Math.PI / 180)"
                                      stroke="#1f2937"
                                      stroke-width="3"
                                      stroke-linecap="round"/>
                                <circle cx="100" cy="100" r="8" fill="#1f2937"/>
                            </svg>
                        </div>

                        <div class="mt-3">
                            <div class="h1 mb-1 fw-bold" :class="flowMeter.daily_flow >= 0 ? 'text-success' : 'text-danger'" x-text="formatFlowValue(flowMeter.daily_flow)">--</div>
                            <div class="badge fs-6" :class="getFlowBadge()" x-text="getFlowLabel()">--</div>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <div class="p-2 rounded mb-3" :class="getFlowAlert()">
                            <div class="small fw-semibold mb-1" x-text="getFlowTitle()">Analysis</div>
                            <div class="small" x-text="getFlowMessage()">Loading...</div>
                        </div>

                        <div class="d-flex justify-content-between small text-secondary">
                            <span>Outflow</span>
                            <span>Inflow</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Institutional Overview Cards -->
            <div class="col-lg-8">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="mb-1">Institutional Overview</h5>
                        <small class="text-secondary">Snapshot metrics kunci dari aktivitas institusional</small>
                    </div>

                    <div class="row g-3 flex-grow-1">
                        <div class="col-md-6">
                            <div class="p-3 rounded h-100" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="small text-secondary mb-1">Net Inflow 24h</div>
                                        <div class="h3 mb-0 fw-bold text-success" x-text="formatCurrency(overview.net_inflow_24h)">--</div>
                                    </div>
                                    <div class="badge text-bg-success" x-text="formatChange(overview.change_24h) + '%'">--</div>
                                </div>
                                <div class="small text-secondary mt-2">
                                    Arus bersih positif mengindikasikan akumulasi institusional
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 rounded h-100" style="background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="small text-secondary mb-1">Total AUM</div>
                                        <div class="h3 mb-0 fw-bold text-primary" x-text="formatCurrency(overview.total_aum)">--</div>
                                    </div>
                                    <div class="badge text-bg-info">All ETFs</div>
                                </div>
                                <div class="small text-secondary mt-2">
                                    Asset Under Management dari semua ETF spot Bitcoin
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 rounded h-100" style="background: rgba(245, 158, 11, 0.1); border-left: 4px solid #f59e0b;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="small text-secondary mb-1">Top Issuer by Flow</div>
                                        <div class="h4 mb-0 fw-bold text-warning" x-text="overview.top_issuer">--</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small text-secondary">Flow</div>
                                        <div class="fw-semibold" x-text="formatCurrency(overview.top_issuer_flow)">--</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 rounded h-100" style="background: rgba(139, 92, 246, 0.1); border-left: 4px solid #8b5cf6;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="small text-secondary mb-1">Total Shares Outstanding</div>
                                        <div class="h4 mb-0 fw-bold text-purple" x-text="formatNumber(overview.total_shares)">--</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small text-secondary">BTC Equivalent</div>
                                        <div class="fw-semibold" x-text="formatNumber(overview.btc_equivalent) + ' BTC'">--</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Spot ETF Details -->
        <div class="row g-3">
            <!-- ETF Flow Table -->
            <div class="col-lg-6">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="mb-0">Recent ETF Flows by Issuer</h5>
                        <small class="text-secondary">Arus ETF harian dari institusi utama</small>
                    </div>

                    <!-- Loading State -->
                    <div x-show="loadingStates.flows" class="text-center py-4" x-cloak>
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <!-- Error State -->
                    <div x-show="errors.flows" class="alert alert-danger" role="alert" x-cloak>
                        <i class="bi bi-exclamation-triangle"></i>
                        <span x-text="errors.flows"></span>
                    </div>

                    <div class="table-responsive" style="height: 300px; overflow-y: auto;" x-show="!loadingStates.flows && !errors.flows">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Issuer</th>
                                    <th>Ticker</th>
                                    <th class="text-end">Flow (USD)</th>
                                    <th class="text-end">AUM (USD)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="flow in etfFlows" :key="flow.id">
                                    <tr>
                                        <td class="small text-secondary" x-text="formatSimpleDate(flow.date)">--</td>
                                        <td class="fw-semibold" x-text="flow.issuer">--</td>
                                        <td x-text="flow.ticker">--</td>
                                        <td class="text-end">
                                            <span :class="flow.flow_usd >= 0 ? 'text-success fw-semibold' : 'text-danger fw-semibold'" x-text="formatFlowValue(flow.flow_usd / 1000000)">--</span>
                                        </td>
                                        <td class="text-end" x-text="formatCurrency(flow.aum_usd / 1000000)">--</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 p-2 rounded" style="background: rgba(59, 130, 246, 0.1);">
                        <div class="small text-secondary">
                            <strong>Flow Insight:</strong> Positive flow mengindikasikan akumulasi institusi, negative flow menandakan net redemption.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily ETF Flow Chart -->
            <div class="col-lg-6">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="mb-0">Daily ETF Flow (<span x-text="selectedPeriod">30</span> Days)</h5>
                        <small class="text-secondary">Tren arus ETF harian per issuer</small>
                    </div>
                    <div class="flex-grow-1" style="height: 280px; max-height: 280px;">
                        <canvas id="etfFlowChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <!-- Daily ETF Flow Insight -->
                        <div class="p-2 rounded" style="background: rgba(59, 130, 246, 0.1);">
                            <div class="small text-secondary">
                                <strong>Daily ETF Flow Insight:</strong> Consistent positive flows (>$100M) indicate strong institutional accumulation. Negative flows suggest profit-taking or risk-off sentiment. Monitor flow patterns alongside price action for entry/exit timing.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Premium/Discount & COT Insights -->
        <div class="row g-3">
            <!-- Premium vs NAV Chart -->
            <div class="col-lg-8">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="mb-0">Premium vs NAV (Basis Points)</h5>
                        <small class="text-secondary">ETF diperdagangkan di atas NAV mengindikasikan potensi overbought</small>
                    </div>

                    <!-- Loading State -->
                    <div x-show="loadingStates.premium" class="text-center py-4" x-cloak>
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <!-- Error State -->
                    <div x-show="errors.premium" class="alert alert-danger" role="alert" x-cloak>
                        <i class="bi bi-exclamation-triangle"></i>
                        <span x-text="errors.premium"></span>
                    </div>

                    <div class="flex-grow-1" style="height: 300px; max-height: 300px;" x-show="!loadingStates.premium && !errors.premium">
                        <canvas id="premiumDiscountChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <!-- Premium/Discount Insight (Static) -->
                        <div class="p-2 rounded" style="background: rgba(139, 92, 246, 0.1);">
                            <div class="small text-secondary">
                                <strong>Premium/Discount Insight:</strong> Premium > 50bps = Overvaluation risk (consider taking profits). Discount < -50bps = Potential buying opportunity (consider accumulating).
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Creations vs Redemptions -->
            <div class="col-lg-4">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="mb-0">Creations vs Redemptions</h5>
                        <small class="text-secondary">Aktivitas creation/redemption mingguan</small>
                    </div>

                    <!-- Loading State -->
                    <div x-show="loadingStates.creations" class="text-center py-4" x-cloak>
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <!-- Error State -->
                    <div x-show="errors.creations" class="alert alert-danger" role="alert" x-cloak>
                        <i class="bi bi-exclamation-triangle"></i>
                        <span x-text="errors.creations"></span>
                    </div>

                    <div class="flex-grow-1" style="height: 280px; overflow-y: auto; padding-right: 5px;" x-show="!loadingStates.creations && !errors.creations">
                        <template x-for="item in creationsRedemptions" :key="item.id">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <span class="fw-semibold" x-text="item.issuer + ' (' + item.ticker + ')'">--</span>
                                        <div class="small text-secondary" x-text="new Date(item.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })">--</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="badge" :class="item.badge_class" x-text="item.badge_text">--</div>
                                        <div class="small text-secondary mt-1">
                                            Net: <span :class="item.net_creation >= 0 ? 'text-success fw-semibold' : 'text-danger fw-semibold'" x-text="formatSigned(item.net_creation)">--</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 small">
                                    <div class="flex-fill p-2 rounded text-center" style="background: rgba(34, 197, 94, 0.1);">
                                        <div class="text-secondary">Creations</div>
                                        <div class="fw-bold text-success" x-text="formatNumber(item.creations_shares)">--</div>
                                        <div class="small text-secondary" x-text="Math.round(item.creation_ratio) + '%'">--</div>
                                    </div>
                                    <div class="flex-fill p-2 rounded text-center" style="background: rgba(239, 68, 68, 0.1);">
                                        <div class="text-secondary">Redemptions</div>
                                        <div class="fw-bold text-danger" x-text="formatNumber(item.redemptions_shares)">--</div>
                                        <div class="small text-secondary" x-text="Math.round(100 - item.creation_ratio) + '%'">--</div>
                                    </div>
                                </div>
                                <div class="mt-2 small">
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-success" 
                                             :style="'width: ' + item.creation_ratio + '%'"
                                             :title="'Creations: ' + Math.round(item.creation_ratio) + '%'"></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        
                        <!-- Show message when no data -->
                        <div x-show="creationsRedemptions.length === 0" class="text-center py-4 text-secondary">
                            <div class="mb-2">📊</div>
                            <div>No creation/redemption data available</div>
                            <div class="small">Data will appear when API is connected</div>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <!-- Creation/Redemption Summary -->
                        <div class="mb-2 p-2 rounded" 
                             :class="getCreationRedemptionSummary().dominant_trend === 'creation' ? 'alert alert-success' : 
                                     getCreationRedemptionSummary().dominant_trend === 'redemption' ? 'alert alert-warning' : 
                                     'alert alert-info'">
                            <div class="small">
                                <strong>Trend:</strong> 
                                <span x-text="getCreationRedemptionSummary().dominant_trend === 'creation' ? 'Strong Creation Activity' : 
                                             getCreationRedemptionSummary().dominant_trend === 'redemption' ? 'Strong Redemption Activity' : 
                                             'Balanced Activity'">--</span>
                            </div>
                            <div class="small text-secondary mt-1">
                                Creation Ratio: <span x-text="Math.round(getCreationRedemptionSummary().creation_ratio) + '%'">--</span>
                            </div>
                        </div>
                        
                        <div class="p-2 rounded" style="background: rgba(34, 197, 94, 0.1);">
                            <div class="small text-secondary">
                                <strong>Creation Insight:</strong> High creations + low redemptions = strong institutional demand.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CME Futures & COT Analysis -->
        <div class="row g-3">
            <!-- CME Open Interest Trend -->
            <div class="col-lg-8">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="mb-0">CME Futures Open Interest Trend</h5>
                        <small class="text-secondary">Tracking institutional exposure melalui CME Bitcoin Futures</small>
                    </div>
                    <div class="flex-grow-1" style="height: 280px; max-height: 280px;">
                        <canvas id="cmeOiChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <!-- CME Futures Open Interest Insight -->
                        <div class="p-2 rounded" style="background: rgba(245, 158, 11, 0.1);">
                            <div class="small text-secondary">
                                <strong>CME Futures OI Insight:</strong> Rising open interest with price increase = strong bullish momentum. Rising OI with price decrease = strong bearish momentum. Declining OI suggests position unwinding and potential trend reversal.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COT Breakdown -->
            <div class="col-lg-4">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">COT Breakdown</h5>
                                <small class="text-secondary">Commitment of Traders - Weekly</small>
                            </div>
                            <span class="badge text-bg-info">Weekly</span>
                        </div>
                    </div>

                    <div class="flex-grow-1 table-responsive" style="height: 200px; overflow-y: auto; padding-right: 5px;">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Group</th>
                                    <th class="text-end">Long</th>
                                    <th class="text-end">Short</th>
                                    <th class="text-end">Net</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="cot in cotData" :key="cot.id">
                                    <tr>
                                        <td class="fw-semibold" x-text="cot.report_group">--</td>
                                        <td class="text-end text-success" x-text="formatNumber(cot.long_contracts)">--</td>
                                        <td class="text-end text-danger" x-text="formatNumber(cot.short_contracts)">--</td>
                                        <td class="text-end">
                                            <span :class="cot.net >= 0 ? 'text-success fw-semibold' : 'text-danger fw-semibold'" x-text="formatSigned(cot.net)">--</span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <!-- COT Sentiment Analysis -->
                        <div class="mb-2 p-2 rounded" :class="getCOTSentimentAnalysis().overall_sentiment === 'bullish' || getCOTSentimentAnalysis().overall_sentiment === 'slightly_bullish' ? 'alert alert-success' : 
                                                             getCOTSentimentAnalysis().overall_sentiment === 'bearish' || getCOTSentimentAnalysis().overall_sentiment === 'slightly_bearish' ? 'alert alert-warning' : 
                                                             'alert alert-info'">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="small fw-semibold">Institutional Sentiment</div>
                                <div class="badge" :class="getCOTSentimentBadgeClass()" x-text="getCOTSentimentLabel()">--</div>
                            </div>
                            <div class="small text-secondary">
                                Score: <span x-text="getCOTSentimentAnalysis().sentiment_score >= 0 ? '+' + getCOTSentimentAnalysis().sentiment_score : getCOTSentimentAnalysis().sentiment_score">--</span>
                                | Week: <span x-text="getCOTSentimentAnalysis().latest_week">--</span>
                            </div>
                        </div>
                        
                        <!-- Key Insights -->
                        <div class="p-2 rounded" style="background: rgba(245, 158, 11, 0.1);">
                            <div class="small text-secondary">
                                <strong>Key Insights:</strong>
                                <template x-for="insight in getCOTSentimentAnalysis().key_insights" :key="insight">
                                    <div x-text="'• ' + insight" class="mt-1">--</div>
                                </template>
                                <div x-show="getCOTSentimentAnalysis().key_insights.length === 0" class="mt-1">
                                    Net long Funds > Dealers = Bullish institutional positioning.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- COT Long vs Short Comparison -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-3">
                    <div class="mb-3">
                        <h5 class="mb-0">COT Long vs Short Positioning</h5>
                        <small class="text-secondary">Perbandingan posisi long dan short per report group</small>
                    </div>
                    <div style="height: 300px; max-height: 300px;">
                        <canvas id="cotComparisonChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trading Insights -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4">
                    <div class="mb-3">
                        <h5 class="mb-1">Trading Insights - ETF & Institutional</h5>
                        <small class="text-secondary">Panduan interpretasi signal untuk institutional flow analysis</small>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e;">
                                <div class="fw-bold mb-2 text-success">Bullish Institutional Signals</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Positive ETF flow > $200M daily</li>
                                        <li>High creations / low redemptions</li>
                                        <li>Premium to NAV < 50bps (fair value)</li>
                                        <li>COT Funds net long increasing</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444;">
                                <div class="fw-bold mb-2 text-danger">Bearish Institutional Signals</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Negative ETF flow > -$200M daily</li>
                                        <li>Low creations / high redemptions</li>
                                        <li>Premium > 100bps (overvalued)</li>
                                        <li>COT Funds net short increasing</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6;">
                                <div class="fw-bold mb-2 text-primary">Neutral / Monitor Zone</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>ETF flow -$100M to +$100M</li>
                                        <li>Balanced creations/redemptions</li>
                                        <li>Premium -30bps to +30bps</li>
                                        <li>COT positioning unchanged</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    <!-- ETF Data Service - MUST load before controller -->
    <script src="{{ asset('js/etf/etf-data-service.js') }}" defer></script>
    <script src="{{ asset('js/etf-institutional-controller.js') }}" defer></script>

    <style>
        .pulse-success {
            background-color: #22c55e;
            box-shadow: 0 0 0 rgba(34, 197, 94, 0.7);
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            }
            50% {
                box-shadow: 0 0 0 8px rgba(34, 197, 94, 0);
            }
        }

        .text-purple {
            color: #8b5cf6 !important;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        /* Custom scrollbar styling */
        .table-responsive::-webkit-scrollbar,
        div[style*="overflow-y: auto"]::-webkit-scrollbar {
            width: 6px;
        }

        .table-responsive::-webkit-scrollbar-track,
        div[style*="overflow-y: auto"]::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .table-responsive::-webkit-scrollbar-thumb,
        div[style*="overflow-y: auto"]::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover,
        div[style*="overflow-y: auto"]::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Ensure consistent spacing in scrollable areas */
        .table-responsive {
            padding-right: 2px;
        }
    </style>
@endsection