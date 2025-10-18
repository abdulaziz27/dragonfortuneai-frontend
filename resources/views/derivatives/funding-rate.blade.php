@extends('layouts.app')

@section('content')
    {{--
        Funding Rate Analytics Dashboard
        Think like a trader • Build like an engineer • Visualize like a designer

        Interpretasi Trading:
        - Funding rate positif → Longs crowded → Bayar shorts → Potensi long squeeze
        - Funding rate negatif → Shorts crowded → Bayar longs → Potensi short squeeze
        - Spread antar exchange → Arbitrage opportunity
        - Perubahan cepat → Leverage positioning berubah
    --}}

    <div class="d-flex flex-column h-100 gap-3" x-data="fundingRateController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">💰 Funding Rate Analytics</h1>
                        <span class="pulse-dot pulse-success"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Monitor funding rates to detect leverage bias, positioning crowding, and potential squeeze setups
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
                        <option value="ADA">Cardano</option>
                        <option value="DOGE">Dogecoin</option>
                        <option value="MATIC">Polygon</option>
                        <option value="DOT">Polkadot</option>
                        <option value="AVAX">Avalanche</option>
                    </select>

                    <select class="form-select" style="width: 120px;" x-model="globalInterval" @change="updateInterval()">
                        <option value="1h">1 Hour</option>
                        <option value="4h" disabled>4 Hours (soon)</option>
                        <option value="8h" disabled>8 Hours (soon)</option>
                        <option value="1d" disabled>1 Day (soon)</option>
                    </select>

                    <button class="btn btn-primary" @click="refreshAll()" :disabled="globalLoading">
                        <span x-show="!globalLoading">🔄 Refresh All</span>
                        <span x-show="globalLoading" class="spinner-border spinner-border-sm"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Market Bias Card (Full Width) -->
        <div class="row g-3">
            <div class="col-12">
                @include('components.funding.bias-card', ['symbol' => 'BTC'])
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-3">
            <!-- Exchange Comparison Chart -->
            <div class="col-lg-8">
                @include('components.funding.exchange-comparison', ['symbol' => 'BTC'])
            </div>

            <!-- Quick Stats Panel -->
            <div class="col-lg-4">
                <div class="df-panel p-3 h-100" x-data="quickStatsPanel()" x-init="init()">
                    <h5 class="mb-3">📈 Quick Stats</h5>

                    <div class="d-flex flex-column gap-2">
                        <!-- Current vs Average -->
                        <div class="stat-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-secondary">Current Rate</span>
                                <span class="badge" :class="currentRate >= 0 ? 'text-bg-success' : 'text-bg-danger'" x-text="currentRate >= 0 ? 'Positive' : 'Negative'">--</span>
                            </div>
                            <div class="h4 mb-1" :class="currentRate >= 0 ? 'text-success' : 'text-danger'" x-text="formatRate(currentRate)">--</div>
                            <div class="small text-secondary">
                                Avg: <span :class="getTrendClass(windowAvgFunding)" x-text="formatRate(windowAvgFunding)">--</span>
                                <span class="mx-1">•</span>
                                Median: <span x-text="formatRate(medianRate)">--</span>
                            </div>
                        </div>

                        <!-- Range & Volatility -->
                        <div class="stat-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-secondary">Volatility</span>
                                <span class="badge" :class="getVolatilityBadgeClass()" x-text="volatility">--</span>
                            </div>
                            <div class="small">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-secondary">Range:</span>
                                    <span x-text="formatRate(minRate) + ' to ' + formatRate(maxRate)">--</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-secondary">Std Dev:</span>
                                    <span x-text="formatRate(stdDev)">--</span>
                                </div>
                            </div>
                        </div>

                        <!-- Trend Direction -->
                        <div class="stat-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-secondary">Trend</span>
                                <span class="badge" :class="getTrendDirectionBadgeClass()" x-text="trendDirection">--</span>
                            </div>
                            <div class="small">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-secondary">Recent:</span>
                                    <span :class="getTrendClass(recentAvg)" x-text="formatRate(recentAvg)">--</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-secondary">Change:</span>
                                    <span :class="getTrendClass(trendChange)" x-text="formatRate(trendChange)">--</span>
                                </div>
                            </div>
                        </div>

                        <!-- Market Bias Summary -->
                        <div class="stat-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-secondary">Market Bias</span>
                                <span class="badge" :class="getStrengthBadgeClass()" x-text="biasStrengthLabel">--</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="h5 mb-0 text-uppercase" :class="getBiasColorClass()" x-text="biasDirection">--</div>
                                <span class="small text-secondary" x-text="'(' + Math.round(biasStrength * 100) + '%)'">0%</span>
                            </div>
                            <div class="small text-secondary" x-text="getBiasExplanation()">Loading...</div>
                        </div>

                        <!-- Extremes Alert -->
                        <div class="alert alert-warning mb-2 py-2" x-show="extremePercentage > 3">
                            <div class="small">
                                <strong>⚠️ <span x-text="extremePercentage.toFixed(1)">0</span>% Extreme Events</strong><br>
                                <span x-text="extremeCount">0</span> outliers detected beyond threshold
                            </div>
                        </div>

                        <!-- Next Funding -->
                        <div class="stat-item">
                            <div class="small text-secondary mb-1">Next Funding</div>
                            <div class="h5 mb-1 fw-bold" x-text="nextFundingTime">--</div>
                            <div class="small text-secondary" x-text="nextFundingDetails">Loading...</div>
                        </div>

                        <!-- Data Coverage -->
                        <div class="text-center pt-2 border-top">
                            <small class="text-secondary">
                                <span x-text="dataPoints">0</span> data points • 
                                <span x-text="durationHours">0</span>h coverage
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exchange Table (Full Width) -->
        <div class="row g-3">
            <div class="col-12">
                @include('components.funding.exchange-table', ['symbol' => 'BTC', 'limit' => 20])
                    </div>
                </div>

        <!-- Additional Charts Row -->
        <div class="row g-3">
            <!-- Historical Chart -->
            <div class="col-lg-6">
                @include('components.funding.history-chart', ['symbol' => 'BTC', 'interval' => '1h'])
            </div>

            <!-- Analytics Insights -->
            <div class="col-lg-6">
                @include('components.funding.analytics-insights', ['symbol' => 'BTC'])
            </div>
        </div>

        <!-- Heatmap Row -->
        <div class="row g-3">
            <div class="col-12">
                @include('components.funding.heatmap', ['title' => 'Exchange × Time Funding Heatmap'])
            </div>
                    </div>

        <!-- Trading Notes & Interpretation -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4">
                    <h5 class="mb-3">📚 Understanding Funding Rates</h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e;">
                                <div class="fw-bold mb-2 text-success">🟩 Positive Funding (Longs Pay Shorts)</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Market too bullish / overleveraged long</li>
                                        <li>Long positions paying funding to shorts</li>
                                        <li>Risk: Long squeeze if price fails to rally</li>
                                        <li>Strategy: Consider shorting on resistance or wait for correction</li>
                                    </ul>
                    </div>
                </div>
            </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444;">
                                <div class="fw-bold mb-2 text-danger">🟥 Negative Funding (Shorts Pay Longs)</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Market too bearish / overleveraged short</li>
                                        <li>Short positions paying funding to longs</li>
                                        <li>Risk: Short squeeze on positive catalysts</li>
                                        <li>Strategy: Look for bounce setups or wait for flush</li>
                                    </ul>
                    </div>
                </div>
            </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6;">
                                <div class="fw-bold mb-2 text-primary">⚡ Exchange Spreads</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Large spreads → Arbitrage opportunities</li>
                                        <li>Negative funding on one exchange → Check for local factors</li>
                                        <li>Consistent high funding → Sustained directional bias</li>
                                        <li>Strategy: Compare with price action for confirmation</li>
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
    <!-- Chart.js - Load BEFORE Alpine components -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

    <!-- Wait for Chart.js to load -->
    <script>
        // Ensure Chart.js is loaded before initializing components
        window.chartJsReady = new Promise((resolve) => {
            if (typeof Chart !== 'undefined') {
                resolve();
            } else {
                setTimeout(() => resolve(), 100);
            }
        });
    </script>

    <!-- Load funding controller BEFORE Alpine processes x-data -->
    <script src="{{ asset('js/funding-rate-controller.js') }}"></script>

    <!-- Quick Stats Panel Component -->
    <script>
        function quickStatsPanel() {
            return {
                symbol: 'BTC',
                // Summary data
                windowAvgFunding: 0,
                currentRate: 0,
                medianRate: 0,
                minRate: 0,
                maxRate: 0,
                stdDev: 0,
                volatility: 'low',
                // Bias data
                biasDirection: 'neutral',
                biasStrength: 0,
                biasStrengthLabel: 'neutral',
                // Trend data
                trendDirection: 'stable',
                trendChange: 0,
                recentAvg: 0,
                olderAvg: 0,
                // Extremes
                extremeCount: 0,
                extremePercentage: 0,
                // Meta
                dataPoints: 0,
                durationHours: 0,
                nextFundingTime: '--',
                nextFundingDetails: 'Loading...',
                loading: false,

                init() {
                    // Get initial from parent
                    this.symbol = this.$root?.globalSymbol || 'BTC';

                    // Load data immediately on init
                    this.loadData();
                    
                    // Auto refresh every 30 seconds
                    setInterval(() => this.loadData(), 30000);

                    // Listen for filter changes
                    window.addEventListener('symbol-changed', (e) => {
                        this.symbol = e.detail?.symbol || this.symbol;
                        this.loadData();
                    });
                },

                async loadData() {
                    this.loading = true;
                    try {
                        // Use analytics endpoint for consistent data
                        const pair = `${this.symbol}USDT`.toLowerCase();
                        const baseMeta = document.querySelector('meta[name="api-base-url"]');
                        const configuredBase = (baseMeta?.content || '').trim();
                        const base = configuredBase ? (configuredBase.endsWith('/') ? configuredBase.slice(0, -1) : configuredBase) : '';
                        const url = base ? `${base}/api/funding-rate/analytics?symbol=${pair}&exchange=binance&interval=1h&limit=2000` : `/api/funding-rate/analytics?symbol=${pair}&exchange=binance&interval=1h&limit=2000`;
                        
                        const response = await fetch(url);
                        const data = await response.json();

                        // Extract summary data
                        this.windowAvgFunding = data.summary?.average || 0;
                        this.currentRate = data.summary?.current || 0;
                        this.medianRate = data.summary?.median || 0;
                        this.minRate = data.summary?.min || 0;
                        this.maxRate = data.summary?.max || 0;
                        this.stdDev = data.summary?.std_dev || 0;
                        this.volatility = data.summary?.volatility || 'low';

                        // Extract bias data
                        this.biasDirection = data.bias?.direction || 'neutral';
                        this.biasStrength = data.bias?.strength || 0;
                        this.biasStrengthLabel = data.bias?.strength_label || 'neutral';

                        // Extract trend data
                        this.trendDirection = data.trend?.direction || 'stable';
                        this.trendChange = data.trend?.change || 0;
                        this.recentAvg = data.trend?.recent_avg || 0;
                        this.olderAvg = data.trend?.older_avg || 0;

                        // Extract extremes
                        this.extremeCount = data.extremes?.count || 0;
                        this.extremePercentage = data.extremes?.percentage || 0;

                        // Extract meta
                        this.dataPoints = data.data_points || 0;
                        this.durationHours = Math.round(data.time_range?.duration_hours || 0);

                        // Calculate next funding time (8-hour intervals)
                        this.calculateNextFunding();

                        console.log('✅ Quick stats loaded:', data);
                    } catch (error) {
                        console.error('❌ Error loading quick stats:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                calculateNextFunding() {
                    const now = new Date();
                    const hours = now.getUTCHours();
                    const nextHour = Math.ceil((hours + 1) / 8) * 8;
                    const next = new Date(now);
                    next.setUTCHours(nextHour, 0, 0, 0);
                    
                    if (next <= now) {
                        next.setUTCDate(next.getUTCDate() + 1);
                    }
                    
                    const diff = next - now;
                    const hoursLeft = Math.floor(diff / (1000 * 60 * 60));
                    const minutesLeft = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    
                    this.nextFundingTime = `${hoursLeft}h ${minutesLeft}m`;
                    this.nextFundingDetails = `Next funding at ${next.getUTCHours()}:00 UTC`;
                },

                getBiasColorClass() {
                    if (this.biasDirection === 'long') return 'text-success';
                    if (this.biasDirection === 'short') return 'text-danger';
                    return 'text-secondary';
                },

                getStrengthBadgeClass() {
                    const label = this.biasStrengthLabel.toLowerCase();
                    if (label === 'strong') return 'text-bg-danger';
                    if (label === 'moderate') return 'text-bg-warning';
                    if (label === 'weak') return 'text-bg-info';
                    return 'text-bg-secondary';
                },

                getVolatilityBadgeClass() {
                    const vol = this.volatility.toLowerCase();
                    if (vol === 'high') return 'text-bg-danger';
                    if (vol === 'moderate') return 'text-bg-warning';
                    return 'text-bg-success';
                },

                getTrendDirectionBadgeClass() {
                    const dir = this.trendDirection.toLowerCase();
                    if (dir === 'increasing') return 'text-bg-success';
                    if (dir === 'decreasing') return 'text-bg-danger';
                    return 'text-bg-secondary';
                },

                getBiasExplanation() {
                    if (this.biasDirection === 'long') {
                        return 'Longs paying shorts • Bullish positioning';
                    }
                    if (this.biasDirection === 'short') {
                        return 'Shorts paying longs • Bearish positioning';
                    }
                    return 'Balanced market conditions';
                },

                getTrendClass(val) {
                    if (val > 0) return 'text-success';
                    if (val < 0) return 'text-danger';
                    return 'text-secondary';
                },

                formatRate(value) {
                    if (value === null || value === undefined || isNaN(value)) return 'N/A';
                    const percent = (parseFloat(value) * 100).toFixed(4);
                    return (parseFloat(value) >= 0 ? '+' : '') + percent + '%';
                }
            };
        }
    </script>

    <style>
        /* Pulse animation for live indicator */
        .pulse-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }

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

        /* Stat item styling */
        .stat-item {
            padding: 0.75rem;
            border-radius: 0.5rem;
            background: rgba(var(--bs-light-rgb), 0.5);
            transition: all 0.2s ease;
        }

        .stat-item:hover {
            background: rgba(var(--bs-light-rgb), 0.8);
            transform: translateX(4px);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .derivatives-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
@endsection
