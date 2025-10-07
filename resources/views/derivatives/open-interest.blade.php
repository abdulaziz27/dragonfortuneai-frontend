@extends('layouts.app')

@section('title', 'Open Interest Analytics')

@section('content')
    <div class="d-flex flex-column h-100 gap-3" x-data="openInterestData()">
        <!-- Header -->
        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-3">
                <div>
                        <h4 class="mb-1">Open Interest Analytics</h4>
                        <p class="text-secondary mb-0 small">Track leverage buildup & liquidation zones • OI rising = more contracts at risk</p>
                </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row g-2">
                    <!-- Market Bias Card -->
                    <div class="col-md-6">
                        <div class="df-panel p-3 h-100 d-flex flex-column justify-content-center align-items-center text-center"
                             :style="bias === 'long buildup' ? 'background: linear-gradient(135deg, #22c55e, #16a34a); color: white;' : bias === 'short buildup' ? 'background: linear-gradient(135deg, #ef4444, #dc2626); color: white;' : 'background: linear-gradient(135deg, #6b7280, #4b5563); color: white;'">
                            <div class="small text-white-75 mb-1">Market Bias</div>
                            <div class="fw-bold d-flex align-items-center gap-2 text-white">
                                <span class="pulse-dot" :class="bias === 'long buildup' ? 'pulse-success' : bias === 'short buildup' ? 'pulse-danger' : ''"></span>
                                <svg width="16" height="16" :class="trend === 'increasing' ? '' : 'rotate-180'">
                                    <path d="M8 2 L12 10 L4 10 Z" fill="white"></path>
                                </svg>
                                <span x-text="bias" class="text-uppercase"></span>
                </div>
            </div>
        </div>
                    <!-- Avg OI Card -->
                    <div class="col-md-6">
                        <div class="df-panel p-3 h-100 d-flex flex-column justify-content-center align-items-center text-center">
                            <div class="small text-secondary mb-1">Avg OI</div>
                            <div class="fw-bold" x-text="formatOI(averageOI)"></div>
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
                        <select class="form-select" style="max-width: 180px;" x-model="selectedSymbol" @change="loadAllData">
                            <option value="BTC">BTC</option>
                            <option value="ETH">ETH</option>
                            <option value="SOL">SOL</option>
                        </select>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-secondary mb-0">Interval:</label>
                        <select class="form-select" style="max-width: 180px;" x-model="selectedInterval" @change="loadAllData">
                            <option value="15m" disabled>15 Minutes (No Data)</option>
                            <option value="30m" disabled>30 Minutes (No Data)</option>
                            <option value="1h">1 Hour</option>
                            <option value="1d" disabled>1 Day (No Data)</option>
                            <option value="1w" disabled>1 Week (No Data)</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2" @click="loadAllData">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
                        <path d="M21 3v5h-5"/>
                        <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
                        <path d="M3 21v-5h5"/>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        <!-- Trading Insight Cards -->
        <div class="row g-3 mb-3">
            <div class="col-lg-4">
                <div class="df-panel p-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="text-primary">⭐</div>
                        <div class="fw-semibold">Insight</div>
                    </div>
                    <div class="fw-semibold" x-text="biasInsight"></div>
                    <div class="small" x-text="biasDetail"></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="df-panel p-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="text-info">📈</div>
                        <div class="fw-semibold">Trend Strength</div>
                    </div>
                    <div class="fw-semibold" x-text="getTrendStrength()"></div>
                    <div class="small" x-text="getTrendDetail()"></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="df-panel p-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div :class="riskLevel === 'High' ? 'text-danger' : riskLevel === 'Moderate' ? 'text-warning' : 'text-success'">
                            <span x-text="riskLevel === 'High' ? '🔴' : riskLevel === 'Moderate' ? '⚠️' : '✅'">⚠️</span>
                        </div>
                        <div class="fw-semibold">Risk Level</div>
                    </div>
                    <div class="fw-semibold"
                         :class="riskLevel === 'High' ? 'text-danger' : riskLevel === 'Moderate' ? 'text-warning' : 'text-success'"
                         x-text="riskLevel"></div>
                    <div class="small" x-text="riskDetail"></div>
                </div>
            </div>
        </div>

        <!-- Key Metrics Overview - Most Important Section -->
        <!-- Exchange Comparison Cards -->
        <div class="row g-3 mb-3">
            <template x-for="exchange in topExchanges" :key="exchange.exchange">
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="df-panel p-3 text-center">
                        <div class="fw-semibold mb-1" x-text="exchange.exchange"></div>
                        <div class="h5 mb-1" x-text="formatOI(exchange.value)"></div>
                        <div class="small text-secondary">Market Share</div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-primary" :style="'width: ' + (parseFloat(exchange.value) / maxExchangeOI * 100) + '%'"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- OI Volatility Analysis -->
        <div class="df-panel p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">📈 OI Volatility Analysis</h5>
                <span class="small text-secondary" x-text="historyData.length > 0 ? 'Based on OHLC data' : 'Based on aggregate data'">Based on OHLC data</span>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="small text-secondary">Avg Volatility</div>
                        <div class="h5 mb-0" x-text="getAvgVolatility() + '%'"></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="small text-secondary">Max OI</div>
                        <div class="h5 mb-0" x-text="formatOI(getMaxOI())"></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="small text-secondary">Min OI</div>
                        <div class="h5 mb-0" x-text="formatOI(getMinOI())"></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="small text-secondary">OI Range</div>
                        <div class="h5 mb-0" x-text="getOIRange() + '%'"></div>
                    </div>
                </div>
            </div>
            <div class="mt-3 p-2 rounded" style="background: rgba(99, 102, 241, 0.05);">
                <div class="small">
                    <strong>Analysis:</strong> <span x-text="getHistoryInsight()"></span>
                </div>
            </div>
        </div>

        <!-- Advanced Analytics Section -->
        <div class="row g-3 mb-3">
            <!-- OI Momentum Indicator -->
            <div class="col-lg-4">
                <div class="df-panel p-3">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="text-warning">⚡</div>
                        <h6 class="mb-0">OI Momentum</h6>
                    </div>
                    <div class="text-center">
                        <div class="h4 mb-1" :class="getMomentumColor()" x-text="getOIMomentum()"></div>
                        <div class="small text-secondary" x-text="getMomentumDetail()"></div>
                    </div>
                    <div class="mt-2">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar" :class="getMomentumColor().replace('text-', 'bg-')"
                                 :style="'width: ' + Math.min(Math.abs(getOIMomentumValue()) * 10, 100) + '%'"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exchange Competition Index -->
            <div class="col-lg-4">
                <div class="df-panel p-3">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="text-info">🏆</div>
                        <h6 class="mb-0">Competition Index</h6>
                    </div>
                    <div class="text-center">
                        <div class="h4 mb-1" x-text="getCompetitionIndex()"></div>
                        <div class="small text-secondary" x-text="getCompetitionDetail()"></div>
                    </div>
                    <div class="mt-2 small">
                        <div class="d-flex justify-content-between">
                            <span>Market Leader:</span>
                            <span class="fw-bold" x-text="dominantExchange"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Market Microstructure -->
            <div class="col-lg-4">
                <div class="df-panel p-3">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="text-success">🔬</div>
                        <h6 class="mb-0">Microstructure</h6>
                    </div>
                    <div class="text-center">
                        <div class="h4 mb-1" x-text="getMicrostructureHealth()"></div>
                        <div class="small text-secondary" x-text="getMicrostructureDetail()"></div>
                    </div>
                    <div class="mt-2 small">
                        <div class="d-flex justify-content-between">
                            <span>Liquidity:</span>
                            <span class="fw-bold" :class="getLiquidityColor()" x-text="getLiquidityStatus()"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Time Pattern Analysis -->
        <div class="df-panel p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">⏰ Time Pattern Analysis</h5>
                <span class="small text-secondary">Last 24 hours</span>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="small text-secondary">Peak OI Hour</div>
                        <div class="h5 mb-0" x-text="getPeakOIHour()"></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="small text-secondary">Most Active Exchange</div>
                        <div class="h5 mb-0" x-text="getMostActiveExchange()"></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="small text-secondary">OI Acceleration</div>
                        <div class="h5 mb-0" x-text="getOIAcceleration() + '%/h'"></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="small text-secondary">Market Efficiency</div>
                        <div class="h5 mb-0" x-text="getMarketEfficiency() + '%'"></div>
                    </div>
                </div>
            </div>
            <div class="mt-3 p-2 rounded" style="background: rgba(139, 69, 19, 0.05);">
                <div class="small">
                    <strong>Pattern Insight:</strong> <span x-text="getTimePatternInsight()"></span>
                </div>
            </div>
        </div>

        <!-- Aggregate OI Trend Chart + Price Overlay -->
        <div class="df-panel p-3" style="min-height: 420px;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">📊 Total Market OI Trend</h5>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <span class="small text-secondary">Current: <span class="fw-bold" x-text="formatOI(currentOI)"></span></span>
                    <span class="badge" :class="oiChange >= 0 ? 'badge-df-success' : 'badge-df-danger'" x-text="signed(oiChange) + '%'"></span>
                    <!-- Divergence Alert -->
                    <template x-if="divergenceDetected">
                        <span class="badge badge-df-warning d-flex align-items-center gap-1">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                            </svg>
                            Divergence Alert
                        </span>
                    </template>
                </div>
            </div>
            <div style="position: relative; height: 340px; width: 100%;">
                <canvas id="aggregateOIChart"></canvas>
            </div>
            <!-- Divergence Insight -->
            <template x-if="divergenceDetected">
                <div class="alert alert-warning mt-3 mb-0" role="alert">
                    <div class="d-flex align-items-start gap-2">
                        <div>⚠️</div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">OI vs Price Divergence Detected</div>
                            <div class="small" x-text="divergenceText"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Exchange OI Distribution + Liquidation Heatmap -->
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="df-panel p-3" style="min-height: 380px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">🏢 Exchange Distribution</h5>
                        <span class="small text-secondary">Dominance: <span class="fw-bold" x-text="dominantExchange"></span></span>
                    </div>
                    <div style="position: relative; height: 280px; width: 100%;">
                        <canvas id="exchangeOIChart"></canvas>
                    </div>
                    <!-- Exchange Flow Insight -->
                    <div class="mt-3 p-2 rounded" style="background: rgba(59, 130, 246, 0.05);">
                        <div class="small">
                            <strong>Capital Flow:</strong> <span x-text="exchangeFlowInsight"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="df-panel p-3" style="min-height: 380px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">💵 Stablecoin OI Trend</h5>
                        <span class="badge" :class="stablecoinTrend >= 0 ? 'badge-df-success' : 'badge-df-danger'" x-text="signed(stablecoinTrend) + '%'"></span>
                    </div>
                    <div style="position: relative; height: 280px; width: 100%;">
                        <canvas id="stablecoinOIChart"></canvas>
                    </div>
                    <!-- Stablecoin Insight -->
                    <div class="mt-3 p-2 rounded" style="background: rgba(34, 197, 94, 0.05);">
                        <div class="small">
                            <strong>Leverage Health:</strong> <span x-text="stablecoinInsight"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- OI per Coin Table -->
        <div class="df-panel p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">🪙 Open Interest by Coin</h5>
                <span class="small text-secondary" x-text="'Last update: ' + lastUpdate"></span>
            </div>
                    <div class="table-responsive">
                <table class="table table-sm">
                            <thead>
                                <tr>
                            <th>Coin</th>
                                    <th>Exchange</th>
                            <th>Current OI</th>
                            <th>24h High</th>
                            <th>24h Low</th>
                            <th>Change</th>
                                </tr>
                            </thead>
                            <tbody>
                        <template x-for="coin in coinOIData.slice(0, 10)" :key="coin.symbol + coin.time">
                            <tr>
                                <td class="fw-semibold" x-text="coin.symbol"></td>
                                <td class="small text-secondary" x-text="coin.exchange_list_str"></td>
                                <td x-text="formatOI(coin.close)"></td>
                                <td x-text="formatOI(coin.high)"></td>
                                <td x-text="formatOI(coin.low)"></td>
                                <td>
                                    <span class="badge"
                                          :class="((parseFloat(coin.close) - parseFloat(coin.open)) / parseFloat(coin.open) * 100) >= 0 ? 'badge-df-success' : 'badge-df-danger'"
                                          x-text="signed((parseFloat(coin.close) - parseFloat(coin.open)) / parseFloat(coin.open) * 100) + '%'">
                                    </span>
                                </td>
                                </tr>
                        </template>
                            </tbody>
                        </table>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>

    <!-- Fix for infinite scroll -->
    <style>
        .df-panel {
            overflow: hidden !important;
        }

        canvas {
            max-width: 100% !important;
            height: auto !important;
        }

        .chart-container {
            position: relative;
            overflow: hidden;
        }

        /* Prevent Chart.js from causing infinite scroll */
        .chartjs-render-monitor {
            animation: none !important;
        }
    </style>

    <script>
        function openInterestData() {
            return {
                // API Configuration
                API_BASE: 'http://202.155.90.20:8000/api/open-interest',

                // Data State
                selectedSymbol: 'BTC',
                selectedInterval: '1h',
                bias: 'loading...',
                trend: 'loading...',
                averageOI: 0,
                currentOI: 0,
                oiChange: 0,
                lastUpdate: 'Loading...',

                aggregateData: [],
                coinOIData: [],
                exchangeOIData: [],
                stablecoinData: [],
                historyData: [],
                topExchanges: [],
                maxExchangeOI: 0,

                // Enhanced insights
                divergenceDetected: false,
                divergenceText: '',
                dominantExchange: 'Loading...',
                exchangeFlowInsight: 'Loading data...',
                stablecoinTrend: 0,
                stablecoinInsight: 'Loading...',

                biasInsight: 'Loading...',
                biasDetail: 'Loading data from API...',
                riskLevel: 'Loading...',
                riskDetail: 'Loading...',

                // Charts
                aggregateChart: null,
                exchangeChart: null,
                stablecoinChart: null,

                // Init
                init() {
                    console.log('✅ OI Analytics initialized');
                    // Delay to ensure DOM is fully ready
                    setTimeout(() => this.loadAllData(), 1000);
                },

                // Format helpers
                formatOI(value) {
                    const num = parseFloat(value);
                    if (num >= 1e9) return '$' + (num / 1e9).toFixed(2) + 'B';
                    if (num >= 1e6) return '$' + (num / 1e6).toFixed(2) + 'M';
                    if (num >= 1e3) return '$' + (num / 1e3).toFixed(2) + 'K';
                    return '$' + num.toFixed(2);
                },

                signed(v) {
                    return (v >= 0 ? '+' : '') + v.toFixed(2);
                },

                // Calculate dynamic risk level
                calculateRiskLevel() {
                    let riskScore = 0;

                    // Factor 1: OI Change magnitude
                    if (Math.abs(this.oiChange) > 10) riskScore += 3;
                    else if (Math.abs(this.oiChange) > 5) riskScore += 2;
                    else if (Math.abs(this.oiChange) > 2) riskScore += 1;

                    // Factor 2: Divergence detection
                    if (this.divergenceDetected) riskScore += 2;

                    // Factor 3: Extreme bias
                    if (this.bias === 'long buildup' || this.bias === 'short buildup') riskScore += 1;

                    // Factor 4: Stablecoin trend volatility
                    if (Math.abs(this.stablecoinTrend) > 5) riskScore += 2;
                    else if (Math.abs(this.stablecoinTrend) > 3) riskScore += 1;

                    // Determine risk level
                    if (riskScore >= 6) {
                        this.riskLevel = 'High';
                        this.riskDetail = 'Multiple risk factors detected • Extreme positioning • High volatility expected';
                    } else if (riskScore >= 3) {
                        this.riskLevel = 'Moderate';
                        this.riskDetail = 'Some risk factors present • Monitor for position changes';
                    } else {
                        this.riskLevel = 'Low';
                        this.riskDetail = 'OI at healthy levels • No extreme positioning detected';
                    }
                },

                // Get trend strength based on OI change
                getTrendStrength() {
                    if (this.trend === 'increasing') {
                        if (this.oiChange > 5) return 'Very Strong Uptrend';
                        if (this.oiChange > 2) return 'Strong Uptrend';
                        return 'Moderate Uptrend';
                    } else if (this.trend === 'decreasing') {
                        if (this.oiChange < -5) return 'Strong Downtrend';
                        if (this.oiChange < -2) return 'Moderate Downtrend';
                        return 'Weak Downtrend';
                    }
                    return 'Sideways';
                },

                // Get trend detail
                getTrendDetail() {
                    if (this.trend === 'increasing') {
                        return `OI increasing (+${this.oiChange.toFixed(1)}%) → More capital flowing into futures market`;
                    } else if (this.trend === 'decreasing') {
                        return `OI decreasing (${this.oiChange.toFixed(1)}%) → Capital leaving futures market`;
                    }
                    return 'OI stable → Balanced market conditions';
                },

                // History data analysis methods
                getAvgVolatility() {
                    if (this.historyData.length === 0) {
                        // Fallback: calculate from aggregate data if history is empty
                        if (this.aggregateData.length > 24) {
                            const recent = this.aggregateData.slice(-24);
                            const volatilities = recent.map(item => {
                                const high = parseFloat(item.high);
                                const low = parseFloat(item.low);
                                const close = parseFloat(item.close);
                                return ((high - low) / close) * 100;
                            });
                            const avg = volatilities.reduce((a, b) => a + b, 0) / volatilities.length;
                            return avg.toFixed(1);
                        }
                        return '0.0';
                    }

                    const volatilities = this.historyData.slice(-24).map(item => {
                        const high = parseFloat(item.high);
                        const low = parseFloat(item.low);
                        const close = parseFloat(item.close);
                        return ((high - low) / close) * 100;
                    });

                    const avg = volatilities.reduce((a, b) => a + b, 0) / volatilities.length;
                    return avg.toFixed(1);
                },

                getMaxOI() {
                    if (this.historyData.length === 0) {
                        // Fallback: use aggregate data
                        if (this.aggregateData.length > 0) {
                            return Math.max(...this.aggregateData.map(item => parseFloat(item.high)));
                        }
                        return this.currentOI;
                    }
                    return Math.max(...this.historyData.map(item => parseFloat(item.high)));
                },

                getMinOI() {
                    if (this.historyData.length === 0) {
                        // Fallback: use aggregate data
                        if (this.aggregateData.length > 0) {
                            return Math.min(...this.aggregateData.map(item => parseFloat(item.low)));
                        }
                        return this.currentOI;
                    }
                    return Math.min(...this.historyData.map(item => parseFloat(item.low)));
                },

                getOIRange() {
                    if (this.historyData.length === 0) return '0.0';
                    const max = this.getMaxOI();
                    const min = this.getMinOI();
                    const range = ((max - min) / min) * 100;
                    return range.toFixed(1);
                },

                getHistoryInsight() {
                    const avgVol = parseFloat(this.getAvgVolatility());
                    const range = parseFloat(this.getOIRange());

                    if (this.historyData.length === 0) {
                        return `Analysis based on aggregate data • Volatility: ${avgVol}% • ${avgVol > 8 ? 'Active positioning' : 'Stable conditions'}`;
                    }

                    if (avgVol > 15) {
                        return `High volatility (${avgVol}%) indicates extreme position changes and potential liquidation cascades`;
                    } else if (avgVol > 8) {
                        return `Moderate volatility (${avgVol}%) suggests active position management and market uncertainty`;
                    } else if (range > 20) {
                        return `Wide OI range (${range}%) shows significant position building and unwinding cycles`;
                    } else {
                        return `Low volatility (${avgVol}%) indicates stable positioning and balanced market conditions`;
                    }
                },

                // Advanced Analytics Methods
                getOIMomentumValue() {
                    if (this.aggregateData.length < 3) return 0;

                    const recent = this.aggregateData.slice(-3);
                    const changes = recent.map((item, i) => {
                        if (i === 0) return 0;
                        const prev = parseFloat(recent[i-1].close);
                        const curr = parseFloat(item.close);
                        return ((curr - prev) / prev) * 100;
                    });

                    // Calculate momentum as acceleration of changes
                    if (changes.length < 2) return 0;
                    return changes[changes.length - 1] - changes[changes.length - 2];
                },

                getOIMomentum() {
                    const momentum = this.getOIMomentumValue();
                    if (Math.abs(momentum) < 0.1) return 'Neutral';
                    if (momentum > 2) return 'Strong Bull';
                    if (momentum > 0.5) return 'Bullish';
                    if (momentum < -2) return 'Strong Bear';
                    if (momentum < -0.5) return 'Bearish';
                    return 'Neutral';
                },

                getMomentumColor() {
                    const momentum = this.getOIMomentumValue();
                    if (momentum > 1) return 'text-success';
                    if (momentum > 0) return 'text-info';
                    if (momentum < -1) return 'text-danger';
                    if (momentum < 0) return 'text-warning';
                    return 'text-secondary';
                },

                getMomentumDetail() {
                    const momentum = this.getOIMomentumValue();
                    if (Math.abs(momentum) < 0.1) return 'OI momentum flat • Sideways market';
                    if (momentum > 0) return `Accelerating upward • +${momentum.toFixed(2)}%/h`;
                    return `Decelerating • ${momentum.toFixed(2)}%/h`;
                },

                getCompetitionIndex() {
                    if (this.exchangeOIData.length === 0) return '0';

                    // Calculate Herfindahl-Hirschman Index (HHI) for market concentration
                    const totalOI = this.exchangeOIData.reduce((sum, ex) => sum + parseFloat(ex.value), 0);
                    const marketShares = this.exchangeOIData.map(ex => parseFloat(ex.value) / totalOI);
                    const hhi = marketShares.reduce((sum, share) => sum + (share * share), 0);

                    // Convert to competition index (0-100, higher = more competitive)
                    const competitionIndex = Math.max(0, (1 - hhi) * 100);
                    return competitionIndex.toFixed(0);
                },

                getCompetitionDetail() {
                    const index = parseFloat(this.getCompetitionIndex());
                    if (index > 80) return 'Highly competitive market';
                    if (index > 60) return 'Moderate competition';
                    if (index > 40) return 'Concentrated market';
                    return 'Monopolistic tendencies';
                },

                getMicrostructureHealth() {
                    // Combine multiple factors for microstructure health
                    let healthScore = 50; // Base score

                    // Factor 1: Exchange distribution
                    const competitionIndex = parseFloat(this.getCompetitionIndex());
                    healthScore += (competitionIndex - 50) * 0.3;

                    // Factor 2: Volatility (lower is better for microstructure)
                    const avgVol = parseFloat(this.getAvgVolatility());
                    if (avgVol < 5) healthScore += 15;
                    else if (avgVol > 15) healthScore -= 20;

                    // Factor 3: OI stability
                    if (Math.abs(this.oiChange) < 2) healthScore += 10;
                    else if (Math.abs(this.oiChange) > 10) healthScore -= 15;

                    healthScore = Math.max(0, Math.min(100, healthScore));

                    if (healthScore > 80) return 'Excellent';
                    if (healthScore > 65) return 'Good';
                    if (healthScore > 50) return 'Fair';
                    if (healthScore > 35) return 'Poor';
                    return 'Critical';
                },

                getMicrostructureDetail() {
                    const health = this.getMicrostructureHealth();
                    if (health === 'Excellent') return 'Optimal market conditions • Low slippage expected';
                    if (health === 'Good') return 'Healthy market structure • Normal trading conditions';
                    if (health === 'Fair') return 'Adequate liquidity • Some friction possible';
                    if (health === 'Poor') return 'Fragmented liquidity • Higher costs expected';
                    return 'Stressed conditions • Avoid large orders';
                },

                getLiquidityStatus() {
                    const totalOI = this.exchangeOIData.reduce((sum, ex) => sum + parseFloat(ex.value), 0);
                    if (totalOI > 500000) return 'Deep';
                    if (totalOI > 200000) return 'Good';
                    if (totalOI > 50000) return 'Moderate';
                    return 'Thin';
                },

                getLiquidityColor() {
                    const status = this.getLiquidityStatus();
                    if (status === 'Deep') return 'text-success';
                    if (status === 'Good') return 'text-info';
                    if (status === 'Moderate') return 'text-warning';
                    return 'text-danger';
                },

                getPeakOIHour() {
                    if (this.aggregateData.length === 0) return '--:--';

                    // Find hour with highest OI in last 24 data points
                    const recent24h = this.aggregateData.slice(-24);
                    const maxOI = Math.max(...recent24h.map(item => parseFloat(item.high)));
                    const peakItem = recent24h.find(item => parseFloat(item.high) === maxOI);

                    if (!peakItem) return '--:--';

                    const date = new Date(peakItem.time);
                    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                },

                getMostActiveExchange() {
                    if (this.exchangeOIData.length === 0) return 'N/A';

                    // Find exchange with highest value
                    const maxValue = Math.max(...this.exchangeOIData.map(ex => parseFloat(ex.value)));
                    const mostActive = this.exchangeOIData.find(ex => parseFloat(ex.value) === maxValue);

                    return mostActive ? mostActive.exchange : 'N/A';
                },

                getOIAcceleration() {
                    if (this.aggregateData.length < 4) return '0.0';

                    const recent = this.aggregateData.slice(-4);
                    const changes = recent.map((item, i) => {
                        if (i === 0) return 0;
                        const prev = parseFloat(recent[i-1].close);
                        const curr = parseFloat(item.close);
                        return ((curr - prev) / prev) * 100;
                    }).slice(1); // Remove first 0

                    if (changes.length < 2) return '0.0';

                    // Calculate acceleration (change of change)
                    const acceleration = changes[changes.length - 1] - changes[changes.length - 2];
                    return acceleration.toFixed(2);
                },

                getMarketEfficiency() {
                    // Market efficiency based on price discovery across exchanges
                    if (this.exchangeOIData.length < 2) return '0';

                    const values = this.exchangeOIData.map(ex => parseFloat(ex.value));
                    const mean = values.reduce((a, b) => a + b, 0) / values.length;
                    const variance = values.reduce((sum, val) => sum + Math.pow(val - mean, 2), 0) / values.length;
                    const cv = Math.sqrt(variance) / mean; // Coefficient of variation

                    // Lower CV = higher efficiency
                    const efficiency = Math.max(0, (1 - cv) * 100);
                    return efficiency.toFixed(0);
                },

                getTimePatternInsight() {
                    const peakHour = this.getPeakOIHour();
                    const acceleration = parseFloat(this.getOIAcceleration());
                    const efficiency = parseFloat(this.getMarketEfficiency());

                    if (efficiency > 85) {
                        return `Highly efficient market • Peak activity at ${peakHour} • ${acceleration > 0 ? 'Accelerating' : 'Decelerating'} momentum`;
                    } else if (efficiency > 70) {
                        return `Good price discovery • Most active around ${peakHour} • Monitor for ${acceleration > 1 ? 'rapid changes' : 'stability'}`;
                    } else {
                        return `Fragmented market • Peak at ${peakHour} • ${efficiency < 50 ? 'Poor efficiency' : 'Moderate efficiency'} detected`;
                    }
                },


                // Load Aggregate OI
                async loadAggregate() {
                    try {
                        console.log('📊 Loading aggregate OI...');
                        const response = await fetch(`${this.API_BASE}/aggregate?symbol=${this.selectedSymbol}&interval=${this.selectedInterval}&limit=2000`);
                        const json = await response.json();
                        this.aggregateData = json.data || [];
                        console.log('✅ Aggregate data loaded:', this.aggregateData.length, 'items');

                        if (this.aggregateData.length > 0) {
                            const latest = this.aggregateData[this.aggregateData.length - 1];
                            const previous = this.aggregateData[Math.max(0, this.aggregateData.length - 24)];
                            this.currentOI = parseFloat(latest.close);
                            this.oiChange = ((this.currentOI - parseFloat(previous.close)) / parseFloat(previous.close)) * 100;

                            this.detectDivergence();
                        }

                        this.renderAggregateChart();
                        // Update risk level after OI data changes
                        this.calculateRiskLevel();
                    } catch (error) {
                        console.error('❌ Error loading aggregate OI:', error);
                    }
                },

                // Divergence Detection
                detectDivergence() {
                    const oiIncreasing = this.oiChange > 2;
                    const oiDecreasing = this.oiChange < -2;

                    if (oiIncreasing && this.bias === 'short buildup') {
                        this.divergenceDetected = true;
                        this.divergenceText = `OI increasing (+${this.oiChange.toFixed(1)}%) with short buildup → Potential short squeeze if price bounces`;
                    } else if (oiDecreasing && this.bias === 'long buildup') {
                        this.divergenceDetected = true;
                        this.divergenceText = `OI decreasing (${this.oiChange.toFixed(1)}%) with long buildup → Long liquidations or position closing`;
                    } else if (Math.abs(this.oiChange) > 5) {
                        this.divergenceDetected = true;
                        this.divergenceText = `Extreme OI movement (${this.oiChange >= 0 ? '+' : ''}${this.oiChange.toFixed(1)}%) → High volatility expected`;
                    } else {
                        this.divergenceDetected = false;
                    }
                },

                // Load Bias
                async loadBias() {
                    try {
                        console.log('📊 Loading bias...');
                        const response = await fetch(`${this.API_BASE}/bias?symbol=${this.selectedSymbol}&limit=1000`);
                        const json = await response.json();

                        this.bias = json.bias || 'neutral';
                        this.trend = json.trend || 'stable';
                        this.averageOI = parseFloat(json.average_oi || 0);
                        console.log('✅ Bias loaded:', this.bias, this.trend);

                        // Generate insights
                        if (this.bias === 'long buildup') {
                            this.biasInsight = 'Long positions building up';
                            this.biasDetail = 'OI increasing with price → more longs entering → potential squeeze if price reverses down';
                        } else if (this.bias === 'short buildup') {
                            this.biasInsight = 'Short positions accumulating';
                            this.biasDetail = 'OI rising while price falls → shorts piling in → risk of short squeeze on reversal';
                        } else {
                            this.biasInsight = 'Neutral market positioning';
                            this.biasDetail = 'OI stable → balanced market with no extreme directional bias';
                        }

                        // Calculate dynamic risk level
                        this.calculateRiskLevel();
                    } catch (error) {
                        console.error('❌ Error loading bias:', error);
                    }
                },

                // Load Coin OI
                async loadCoins() {
                    try {
                        console.log('📊 Loading coin OI...');
                        const response = await fetch(`${this.API_BASE}/coins?symbol=${this.selectedSymbol}&interval=${this.selectedInterval}&limit=2000`);
                        const json = await response.json();
                        this.coinOIData = json.data || [];
                        console.log('✅ Coin OI loaded:', this.coinOIData.length, 'items');
                    } catch (error) {
                        console.error('❌ Error loading coin OI:', error);
                    }
                },

                // Load Exchange OI
                async loadExchange() {
                    try {
                        console.log('📊 Loading exchange OI...');
                        const response = await fetch(`${this.API_BASE}/exchange?symbol=${this.selectedSymbol}&limit=2000`);
                        const json = await response.json();
                        this.exchangeOIData = json.data || [];

                        // Get top exchanges
                        const exchangeMap = {};
                        this.exchangeOIData.forEach(item => {
                            if (!exchangeMap[item.exchange] || item.time > exchangeMap[item.exchange].time) {
                                exchangeMap[item.exchange] = item;
                            }
                        });

                        this.topExchanges = Object.values(exchangeMap)
                            .sort((a, b) => parseFloat(b.value) - parseFloat(a.value))
                            .slice(0, 8);

                        this.maxExchangeOI = this.topExchanges.length > 0
                            ? parseFloat(this.topExchanges[0].value)
                            : 0;

                        // Dominant exchange
                        if (this.topExchanges.length > 0) {
                            this.dominantExchange = this.topExchanges[0].exchange;

                            // Calculate market share change (simplified - compare to previous data)
                            const topOI = parseFloat(this.topExchanges[0].value);
                            const secondOI = this.topExchanges.length > 1 ? parseFloat(this.topExchanges[1].value) : 0;
                            const shareGap = ((topOI - secondOI) / topOI * 100);

                            if (shareGap > 30) {
                                this.exchangeFlowInsight = `${this.dominantExchange} dominates with ${shareGap.toFixed(1)}% lead → High concentration risk`;
                            } else {
                                this.exchangeFlowInsight = `Balanced distribution across exchanges → Healthy market liquidity`;
                            }
                        }

                        this.renderExchangeChart();
                        console.log('✅ Exchange OI loaded:', this.topExchanges.length, 'exchanges');
                    } catch (error) {
                        console.error('❌ Error loading exchange OI:', error);
                    }
                },

                // Load Stablecoin OI
                async loadStablecoin() {
                    try {
                        console.log('📊 Loading stablecoin OI...');
                        const response = await fetch(`${this.API_BASE}/stable?symbol=${this.selectedSymbol}&interval=${this.selectedInterval}&limit=2000`);
                        const json = await response.json();
                        this.stablecoinData = json.data || [];

                        // Calculate stablecoin trend
                        if (this.stablecoinData.length > 0) {
                            const latest = this.stablecoinData[this.stablecoinData.length - 1];
                            const previous = this.stablecoinData[Math.max(0, this.stablecoinData.length - 24)];
                            const latestVal = parseFloat(latest.close);
                            const previousVal = parseFloat(previous.close);
                            this.stablecoinTrend = ((latestVal - previousVal) / previousVal) * 100;

                            // Generate insight
                            if (this.stablecoinTrend > 3) {
                                this.stablecoinInsight = 'Stablecoin OI spiking → New leverage entering market (potential volatility)';
                            } else if (this.stablecoinTrend < -3) {
                                this.stablecoinInsight = 'Stablecoin OI dropping → De-leveraging event (risk-off mode)';
                            } else {
                                this.stablecoinInsight = 'Stable OI suggests healthy leverage levels';
                            }
                        }

                        this.renderStablecoinChart();
                        console.log('✅ Stablecoin OI loaded:', this.stablecoinData.length, 'items');
                        // Update risk level after stablecoin data changes
                        this.calculateRiskLevel();
                    } catch (error) {
                        console.error('❌ Error loading stablecoin OI:', error);
                    }
                },

                // Load History OI (OHLC per symbol)
                async loadHistory() {
                    try {
                        console.log('📊 Loading history OI...');
                        // Note: /history endpoint doesn't support symbol filter, load all data
                        const response = await fetch(`${this.API_BASE}/history?interval=${this.selectedInterval}&limit=2000`);
                        const json = await response.json();

                        // Filter by symbol on frontend if needed
                        let historyData = json.data || [];

                        // /history endpoint returns BTCUSDT data, filter based on selected symbol
                        if (this.selectedSymbol === 'BTC') {
                            // Keep BTCUSDT data for BTC selection
                            historyData = historyData.filter(item =>
                                item.symbol === 'BTCUSDT'
                            );
                        } else if (this.selectedSymbol) {
                            // Filter for other symbols (e.g., ETH -> ETHUSDT)
                            const targetSymbol = this.selectedSymbol + 'USDT';
                            historyData = historyData.filter(item =>
                                item.symbol === targetSymbol
                            );
                        }

                        this.historyData = historyData;
                        console.log('✅ History OI loaded:', this.historyData.length, 'items');

                        // Could be used for additional analysis or detailed charts
                        // For now, we'll use it to enhance our insights
                        this.analyzeHistoryData();
                    } catch (error) {
                        console.error('❌ Error loading history OI:', error);
                    }
                },

                // Analyze history data for additional insights
                analyzeHistoryData() {
                    if (this.historyData.length === 0) {
                        console.log('⚠️ No history data available for analysis');
                        return;
                    }

                    // Calculate volatility from OHLC data
                    const volatilities = this.historyData.slice(-24).map(item => {
                        const high = parseFloat(item.high);
                        const low = parseFloat(item.low);
                        const close = parseFloat(item.close);
                        return ((high - low) / close) * 100;
                    });

                    const avgVolatility = volatilities.reduce((a, b) => a + b, 0) / volatilities.length;

                    // Update risk level based on volatility
                    if (avgVolatility > 15) {
                        this.riskLevel = 'High';
                        this.riskDetail = `High OI volatility (${avgVolatility.toFixed(1)}%) • Extreme position changes detected`;
                    }
                },

                // Load all data
                async loadAllData() {
                    console.log('🔄 Loading all OI data...');
                    this.lastUpdate = 'Loading...';

                    // Force interval to 1h if other intervals are selected (since API currently only supports 1h)
                    if (this.selectedInterval !== '1h') {
                        console.log('⚠️ API currently only supports 1h interval, forcing interval to 1h');
                        this.selectedInterval = '1h';
                    }

                    await Promise.all([
                        this.loadAggregate(),
                        this.loadBias(),
                        this.loadCoins(),
                        this.loadExchange(),
                        this.loadStablecoin(),
                        this.loadHistory()
                    ]);

                    this.lastUpdate = new Date().toLocaleTimeString();
                    console.log('✅ All OI data loaded successfully');
                },

                // Render Aggregate Chart
                renderAggregateChart() {
                    const ctx = document.getElementById('aggregateOIChart');
                    if (!ctx) return;

                    if (this.aggregateChart) {
                        this.aggregateChart.destroy();
                    }

                    // Prevent infinite resize loop
                    ctx.style.display = 'block';
                    ctx.style.maxHeight = '340px';

                    const labels = this.aggregateData.map(d => new Date(d.time));
                    const data = this.aggregateData.map(d => parseFloat(d.close) / 1e9);

                    this.aggregateChart = new Chart(ctx, {
                        type: 'line',
                data: {
                            labels: labels,
                    datasets: [{
                                label: 'Total Market OI (Billions USD)',
                                data: data,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 0,
                                pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                            resizeDelay: 0,
                            animation: false,
                            interaction: { intersect: false, mode: 'index' },
                    plugins: {
                                legend: { display: true },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => 'OI: $' + context.parsed.y.toFixed(2) + 'B'
                            }
                        }
                    },
                    scales: {
                        x: {
                                    type: 'time',
                                    time: { unit: 'hour' },
                                    ticks: { color: '#94a3b8' },
                                    grid: { color: 'rgba(148, 163, 184, 0.1)' }
                                },
                                y: {
                            ticks: {
                                        color: '#94a3b8',
                                        callback: (value) => '$' + value.toFixed(1) + 'B'
                                    },
                                    grid: { color: 'rgba(148, 163, 184, 0.1)' }
                                }
                            }
                        }
                    });
                },

                // Render Exchange Chart
                renderExchangeChart() {
                    const ctx = document.getElementById('exchangeOIChart');
                    if (!ctx) return;

                    if (this.exchangeChart) {
                        this.exchangeChart.destroy();
                    }

                    // Prevent infinite resize loop
                    ctx.style.display = 'block';
                    ctx.style.maxHeight = '280px';

                    this.exchangeChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: this.topExchanges.map(e => e.exchange),
                            datasets: [{
                                label: 'OI (USD)',
                                data: this.topExchanges.map(e => parseFloat(e.value)),
                                backgroundColor: ['#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899', '#14b8a6']
                            }]
                        },
                         options: {
                             responsive: true,
                             maintainAspectRatio: false,
                             resizeDelay: 0,
                             animation: false,
                             plugins: {
                                 legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => '$' + (context.parsed.y / 1000).toFixed(1) + 'K'
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    ticks: {
                                        callback: (value) => '$' + (value / 1000).toFixed(0) + 'K'
                                    }
                                }
                            }
                        }
                    });
                },

                // Render Stablecoin Chart
                renderStablecoinChart() {
                    const ctx = document.getElementById('stablecoinOIChart');
                    if (!ctx) return;

                    if (this.stablecoinChart) {
                        this.stablecoinChart.destroy();
                    }

                    // Prevent infinite resize loop
                    ctx.style.display = 'block';
                    ctx.style.maxHeight = '280px';

                    const labels = this.stablecoinData.map(d => new Date(d.time));
                    const data = this.stablecoinData.map(d => parseFloat(d.close));

                    this.stablecoinChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Stablecoin OI',
                                data: data,
                                borderColor: '#22c55e',
                                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 0,
                                pointHoverRadius: 4
                            }]
                        },
                         options: {
                             responsive: true,
                             maintainAspectRatio: false,
                             resizeDelay: 0,
                             animation: false,
                             plugins: {
                                 legend: { display: true },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => 'OI: $' + context.parsed.y.toFixed(2)
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    type: 'time',
                                    time: { unit: 'hour' },
                                    ticks: { color: '#94a3b8' },
                                    grid: { color: 'rgba(148, 163, 184, 0.1)' }
                                },
                                y: {
                                    ticks: { color: '#94a3b8' },
                                    grid: { color: 'rgba(148, 163, 184, 0.1)' }
                                }
                            }
                        }
                    });
                }
            };
        }
    </script>
@endsection
