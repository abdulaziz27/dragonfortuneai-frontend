@extends('layouts.app')

@section('scripts')
    <!-- Chart.js and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    
    <!-- Load modular architecture: Service → Renderer → Controller -->
    <script src="{{ asset('js/volatility/volatility-data-service.js') }}"></script>
    <script src="{{ asset('js/volatility/volatility-chart-renderer.js') }}"></script>
    <script src="{{ asset('js/volatility/volatility-regime-controller.js') }}"></script>
@endsection

@section('content')
    {{--
        Volatility & Regime Dashboard
        Think like a trader • Build like an engineer • Visualize like a designer

        Interpretasi Trading:
        - HV > RV → High volatility regime → Scalping & short-term strategies
        - HV < RV → Low volatility regime → Position trading & trend following
        - Volatility spike → Event-driven moves → Risk management critical
        - Calm volatility → Range-bound → Mean reversion strategies
    --}}

    <div class="d-flex flex-column h-100 gap-3" x-data="volatilityRegimeController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">Volatility & Regime Analysis</h1>
                        <span class="pulse-dot pulse-warning"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Monitor volatilitas pasar real-time, regime detection & spot price movement
                    </p>
                </div>

                <!-- Global Controls -->
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <select class="form-select" style="width: 140px;" x-model="selectedPair" @change="handlePairChange()">
                        <option value="BTCUSDT">BTCUSDT</option>
                        <option value="ETHUSDT">ETHUSDT</option>
                        <option value="ADAUSDT">ADAUSDT</option>
                        <option value="SOLUSDT">SOLUSDT</option>
                        <option value="DOTUSDT">DOTUSDT</option>
                    </select>

                    <!-- Cadence Filter -->
                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-secondary mb-0">Cadence:</label>
                        <select class="form-select form-select-sm" style="width: 100px;" x-model="selectedCadence" @change="handleCadenceChange()">
                            <option value="1m">1 Min</option>
                            <option value="5m">5 Min</option>
                            <option value="1h">1 Hour</option>
                            <option value="1d">EOD</option>
                        </select>
                    </div>

                    <!-- Period Filter (Cadence-Aware) -->
                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-secondary mb-0">Period:</label>
                        <select class="form-select form-select-sm" style="width: 100px;" x-model="selectedPeriod" @change="handlePeriodChange()">
                            <template x-for="option in currentPeriodOptions" :key="option.value">
                                <option :value="option.value" x-text="option.label"></option>
                            </template>
                        </select>
                    </div>

                    <button class="btn btn-primary" @click="refreshAll()" :disabled="loading">
                        <span x-show="!loading">🔄</span>
                        <span x-show="loading" class="spinner-border spinner-border-sm"></span>
                    </button>

                    <!-- Auto-Refresh Toggle -->
                    <button 
                        class="btn btn-sm"
                        :class="autoRefreshEnabled ? 'btn-success' : 'btn-secondary'"
                        @click="toggleAutoRefresh()"
                        :disabled="loading">
                        <span x-show="autoRefreshEnabled">⏸ Auto (5s)</span>
                        <span x-show="!autoRefreshEnabled">▶ Auto Off</span>
                    </button>

                    <!-- Last Updated Indicator -->
                    <div class="text-secondary small" x-show="lastUpdated" style="min-width: 150px;">
                        <span x-text="'Last updated: ' + lastUpdated"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Volatility Meter + Market Regime Panel -->
        <div class="row g-3">
            <!-- Volatility Meter Gauge -->
            <div class="col-lg-4">
                <div class="df-panel p-4 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="mb-1">Volatility Meter</h5>
                        <small class="text-secondary">
                            <span x-text="selectedPair"></span> • 
                            <span x-text="getCadenceDisplayName(selectedCadence)"></span>
                        </small>
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

                                <!-- Colored Segments: Calm → Volatile → Extreme -->
                                <path d="M 20 100 A 80 80 0 0 1 60 38"
                                      fill="none"
                                      stroke="#22c55e"
                                      stroke-width="20"
                                      stroke-linecap="round"/>
                                <path d="M 60 38 A 80 80 0 0 1 100 20"
                                      fill="none"
                                      stroke="#f59e0b"
                                      stroke-width="20"
                                      stroke-linecap="round"/>
                                <path d="M 100 20 A 80 80 0 0 1 140 38"
                                      fill="none"
                                      stroke="#ef4444"
                                      stroke-width="20"
                                      stroke-linecap="round"/>
                                <path d="M 140 38 A 80 80 0 0 1 180 100"
                                      fill="none"
                                      stroke="#dc2626"
                                      stroke-width="20"
                                      stroke-linecap="round"/>

                                <!-- Indicator Needle -->
                                <!-- Map volatility 0-100 to 180°-360° arc -->
                                <line x1="100" y1="100"
                                      :x2="100 + 70 * Math.cos((180 + (volatilityScore || 0) * 1.8) * Math.PI / 180)"
                                      :y2="100 + 70 * Math.sin((180 + (volatilityScore || 0) * 1.8) * Math.PI / 180)"
                                      stroke="#1f2937"
                                      stroke-width="3"
                                      stroke-linecap="round"/>
                                <circle cx="100" cy="100" r="8" fill="#1f2937"/>
                            </svg>
                        </div>

                        <div class="mt-3">
                            <div class="h1 mb-1 fw-bold" x-text="volatilityScore + '%'">--</div>
                            <div class="badge fs-6" :class="getVolatilityBadge()" x-text="getVolatilityLabel()">--</div>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <div class="p-2 rounded mb-3" :class="getVolatilityAlert()">
                            <div class="small fw-semibold mb-1" x-text="getVolatilityTitle()">Analysis</div>
                            <div class="small" x-text="getVolatilityMessage()">Loading...</div>
                        </div>

                        <div class="d-flex justify-content-between small text-secondary">
                            <span>Calm</span>
                            <span>Extreme</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Market Regime Panel -->
            <div class="col-lg-8">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="mb-1">Market Regime Status</h5>
                        <small class="text-secondary">Identifikasi kondisi pasar saat ini berdasarkan volatilitas</small>
                    </div>

                    <div class="row g-3 flex-grow-1">
                        <!-- Current Regime Display -->
                        <div class="col-md-6">
                            <div class="h-100 p-3 rounded" :class="getCurrentRegimeBackground()">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle p-2 me-3" :class="getCurrentRegimeIconBg()">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :class="getCurrentRegimeIconColor()">
                                                <path d="M3 3v18h18"/>
                                                <path d="M7 12l3-3 3 3 5-5"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="small text-secondary">Current Regime</div>
                                            <div class="h4 mb-0 fw-bold" x-text="currentRegime.name">--</div>
                                        </div>
                                    </div>
                                    <!-- Confidence Badge -->
                                    <div class="text-end">
                                        <div class="badge" :class="getRegimeConfidenceBadge()" x-text="getFormattedConfidence()">--</div>
                                        <div class="small text-secondary mt-1" x-text="getRegimeConfidenceLabel()">--</div>
                                    </div>
                                </div>
                                
                                <div class="small text-secondary mb-3" x-text="currentRegime.description">--</div>
                                
                                <!-- Trading Strategy & Risk Assessment -->
                                <div class="mt-auto">
                                    <div class="mb-2">
                                        <div class="small fw-semibold text-primary mb-1">Strategy Recommendation:</div>
                                        <div class="small" x-text="getTradingStrategy()">--</div>
                                    </div>
                                    <div>
                                        <div class="small fw-semibold text-warning mb-1">Risk Management:</div>
                                        <div class="small" x-text="getRiskAdvice()">--</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Enhanced Volatility Metrics Cards -->
                        <div class="col-md-6">
                            <div class="h-100">
                                <div class="row g-2 h-100">
                                    <!-- Historical Volatility Card -->
                                    <div class="col-6">
                                        <div class="p-2 rounded h-100 position-relative" 
                                             :class="getMetricCardClass('hv')"
                                             :title="getMetricTooltip('hv')"
                                             data-bs-toggle="tooltip">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <div class="small text-secondary" x-text="getHVLabel(selectedCadence)">HV (30d)</div>
                                                <div class="badge badge-sm" :class="getMetricPercentileBadge('hv')" x-text="getMetricPercentileLabel('hv')">--</div>
                                            </div>
                                            <div class="h5 mb-0 fw-bold" :class="getMetricValueClass('hv')" x-text="formatMetricValue(metrics.hv30, '%')">--</div>
                                            <div class="small text-muted mt-1" x-text="getMetricTrend('hv')">--</div>
                                        </div>
                                    </div>
                                    
                                    <!-- Realized Volatility Card -->
                                    <div class="col-6">
                                        <div class="p-2 rounded h-100 position-relative" 
                                             :class="getMetricCardClass('rv')"
                                             :title="getMetricTooltip('rv')"
                                             data-bs-toggle="tooltip">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <div class="small text-secondary" x-text="getRVLabel(selectedCadence)">RV (30d)</div>
                                                <div class="badge badge-sm" :class="getMetricPercentileBadge('rv')" x-text="getMetricPercentileLabel('rv')">--</div>
                                            </div>
                                            <div class="h5 mb-0 fw-bold" :class="getMetricValueClass('rv')" x-text="formatMetricValue(metrics.rv30, '%')">--</div>
                                            <div class="small text-muted mt-1" x-text="getMetricTrend('rv')">--</div>
                                        </div>
                                    </div>
                                    
                                    <!-- Price Change Card -->
                                    <div class="col-6">
                                        <div class="p-2 rounded h-100 position-relative" 
                                             :class="getMetricCardClass('change24h')"
                                             :title="getMetricTooltip('change24h')"
                                             data-bs-toggle="tooltip">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <div class="small text-secondary" x-text="getChangeLabel(selectedCadence)">24h Change</div>
                                                <div class="badge badge-sm" :class="getChangeDirectionBadge()" x-text="getChangeDirectionLabel()">--</div>
                                            </div>
                                            <div class="h5 mb-0 fw-bold" :class="metrics.change24h >= 0 ? 'text-success' : 'text-danger'" x-text="formatChange(metrics.change24h) + '%'">--</div>
                                            <div class="small text-muted mt-1" x-text="getChangeImplication()">--</div>
                                        </div>
                                    </div>
                                    
                                    <!-- ATR Card -->
                                    <div class="col-6">
                                        <div class="p-2 rounded h-100 position-relative" 
                                             :class="getMetricCardClass('atr')"
                                             :title="getMetricTooltip('atr')"
                                             data-bs-toggle="tooltip">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <div class="small text-secondary" x-text="getATRLabel(selectedCadence)">ATR (14)</div>
                                                <div class="badge badge-sm" :class="getMetricPercentileBadge('atr')" x-text="getMetricPercentileLabel('atr')">--</div>
                                            </div>
                                            <div class="h5 mb-0 fw-bold" :class="getMetricValueClass('atr')" x-text="formatMetricValue(metrics.atr14, '%')">--</div>
                                            <div class="small text-muted mt-1" x-text="getMetricTrend('atr')">--</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Price Action Candlestick Chart -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-3">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">Price Action (Candlestick)</h5>
                                <small class="text-secondary">
                                    <span x-text="selectedPair"></span> • 
                                    <span x-text="getCadenceDisplayName(selectedCadence)"></span> • 
                                    <span x-text="selectedPeriod"></span> • 
                                    <span x-text="ohlcData.length + ' candles'"></span>
                                </small>
                            </div>
                            <div x-show="loadingStates.ohlc" class="spinner-border spinner-border-sm text-primary"></div>
                            <div x-show="errors.ohlc" class="text-danger small" x-text="errors.ohlc"></div>
                        </div>
                    </div>
                    
                    <!-- Candlestick Chart -->
                    <div style="min-height: 400px;">
                        <canvas id="candlestickChart"></canvas>
                    </div>
                    
                    <!-- Volume Chart -->
                    <div style="min-height: 100px;" class="mt-2">
                        <canvas id="volumeChart"></canvas>
                    </div>

                    <div class="mt-3 p-2 rounded" style="background: rgba(59, 130, 246, 0.1);">
                        <div class="small text-secondary">
                            <strong>Candlestick Insight:</strong> Green candles = bullish (close > open), Red candles = bearish (close < open). Volume bars show trading activity intensity.
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- Volatility Trend Chart (HV + RV) -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-3">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">Volatility Analysis</h5>
                                <small class="text-secondary">Analyze volatility trends and patterns last 30 days</small>
                            </div>
                            <div x-show="loadingStates.trends" class="spinner-border spinner-border-sm text-primary"></div>
                            <div x-show="errors.trends" class="text-danger small" x-text="errors.trends"></div>
                        </div>
                    </div>
                    
                    <div style="min-height: 320px;">
                        <canvas id="volatilityTrendChart"></canvas>
                    </div>
                    
                    <div class="mt-3 p-2 rounded" style="background: rgba(59, 130, 246, 0.1);">
                        <div class="small text-secondary">
                            <strong>Volatility Insight:</strong> Track volatility patterns to identify market regime changes and adjust trading strategies accordingly.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trading Regime Insights -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4">
                    <div class="mb-3">
                        <h5 class="mb-1">Trading Insights - Market Regime</h5>
                        <small class="text-secondary">Strategi optimal berdasarkan kondisi volatilitas saat ini</small>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e;">
                                <div class="fw-bold mb-2 text-success">Low Volatility Regime</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Volatility < 30%</li>
                                        <li>Range-bound market</li>
                                        <li>Mean reversion strategies</li>
                                        <li>Sell options premium</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(245, 158, 11, 0.1); border-left: 4px solid #f59e0b;">
                                <div class="fw-bold mb-2 text-warning">Normal Volatility Regime</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Volatility 30-60%</li>
                                        <li>Balanced market</li>
                                        <li>Trend following + swing trading</li>
                                        <li>Normal position sizing</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444;">
                                <div class="fw-bold mb-2 text-danger">High Volatility Regime</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Volatility > 60%</li>
                                        <li>Event-driven moves</li>
                                        <li>Reduce position size</li>
                                        <li>Wider stop losses</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Volatility Trend Chart -->
        <!-- <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-3">
                    <div class="mb-3">
                        <h5 class="mb-1">Volatility Trend - Last 30 Days</h5>
                        <small class="text-secondary">Historical Volatility (HV) vs Realized Volatility (RV)</small>
                    </div>
                    <div style="min-height: 320px;">
                        <canvas id="volatilityTrendChart"></canvas>
                    </div>
                    <div class="mt-3 p-2 rounded" style="background: rgba(59, 130, 246, 0.1);">
                        <div class="small text-secondary">
                            <strong>Volatility Insight:</strong> HV > RV → Market ekspektasi volatilitas naik. HV < RV → Market overestimate risk. Crossover points = potential regime change.
                        </div>
                    </div>
                </div>
            </div>
        </div> -->



        <!-- Intraday Volatility Heatmap -->
        <div class="row g-3 mt-3">
            <div class="col-12">
                <div class="df-panel p-3 h-100">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">Intraday Volatility Heatmap</h5>
                                <small class="text-secondary">Identifikasi jam-jam dengan volatilitas tertinggi (UTC)</small>
                            </div>
                            <div x-show="loadingStates.heatmap" class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>

                    <div style="min-height: 320px;">
                        <canvas id="intradayHeatmapChart"></canvas>
                    </div>

                    <div class="mt-3 p-2 rounded" style="background: rgba(139, 92, 246, 0.1);">
                        <div class="small text-secondary">
                            <strong>Trading Insight:</strong> Volatilitas cenderung tinggi saat overlap sesi trading (Asia-Europe: 07:00-09:00 UTC, Europe-US: 12:00-16:00 UTC). Gunakan informasi ini untuk timing entry/exit yang optimal.
                        </div>
                    </div>

                    <div x-show="errors.heatmap" class="alert alert-warning mt-3 mb-0" role="alert">
                        <small x-text="errors.heatmap"></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Sections Row -->
        <div class="row g-3">
            <!-- Volume Profile - Last 24h -->
            <div class="col-lg-6">
                <div class="df-panel p-3 h-100">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">Volume Profile - Last 24h</h5>
                                <small class="text-secondary">Price levels with highest trading volume</small>
                            </div>
                            <div x-show="loadingStates.volumeProfile" class="spinner-border spinner-border-sm text-primary"></div>
                        </div>
                    </div>
                    
                    <div style="min-height: 320px;">
                        <canvas id="volumeProfileChart"></canvas>
                    </div>
                    
                    <div class="row g-2 mt-2">
                        <div class="col-4">
                            <div class="p-2 rounded text-center" style="background: rgba(59, 130, 246, 0.1);">
                                <div class="text-secondary small">POC</div>
                                <div class="fw-bold text-primary" x-text="'$' + volumeProfile.poc.toLocaleString()">--</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded text-center" style="background: rgba(34, 197, 94, 0.1);">
                                <div class="text-secondary small">VAH</div>
                                <div class="fw-bold text-success" x-text="'$' + volumeProfile.vah.toLocaleString()">--</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded text-center" style="background: rgba(34, 197, 94, 0.1);">
                                <div class="text-secondary small">VAL</div>
                                <div class="fw-bold text-success" x-text="'$' + volumeProfile.val.toLocaleString()">--</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3 p-2 rounded" style="background: rgba(59, 130, 246, 0.1);">
                        <div class="small text-secondary">
                            <strong>Volume Insight:</strong> POC (Point of Control) shows price with highest volume. VAH/VAL define 70% value area. Price tends to return to high-volume zones.
                        </div>
                    </div>
                    
                    <div x-show="errors.volumeProfile" class="alert alert-warning mt-3 mb-0">
                        <small x-text="errors.volumeProfile"></small>
                    </div>
                </div>
            </div>

            <!-- Regime Transition Probability -->
            <div class="col-lg-6">
                <div class="df-panel p-3 h-100">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">Regime Transition Probability</h5>
                                <small class="text-secondary">Forecast probabilitas perubahan regime berikutnya</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-info text-white" x-show="regimeTransitionData.nextTimeframe" x-text="'Next ' + regimeTransitionData.nextTimeframe"></span>
                                <div x-show="loadingStates.regimeTransition" class="spinner-border spinner-border-sm text-primary"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Current Regime Display -->
                    <div class="mb-3 p-2 rounded" style="background: rgba(59, 130, 246, 0.1);">
                        <div class="small text-secondary mb-1">Current:</div>
                        <div class="fw-bold" x-text="regimeTransitionData.currentLabel || 'Normal Volatility'"></div>
                    </div>
                    
                    <!-- Transition Probabilities with Timeframes -->
                    <div class="mb-3">
                        <template x-for="transition in regimeTransitions" :key="transition.to + transition.timeframe">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">→ <span x-text="transition.to"></span></div>
                                        <div class="small text-muted" x-text="'Timeframe: ' + transition.timeframe"></div>
                                    </div>
                                    <div class="text-end ms-2">
                                        <span class="fw-bold" x-text="transition.probability.toFixed(0) + '%'"></span>
                                    </div>
                                </div>
                                <div class="progress" style="height: 12px;">
                                    <div class="progress-bar" 
                                         :class="'bg-' + transition.color"
                                         :style="'width: ' + transition.probability + '%'"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                    
                    <!-- Insight Box -->
                    <div class="mt-3 p-2 rounded" style="background: rgba(139, 92, 246, 0.1);">
                        <div class="small text-secondary">
                            <strong>Forecast Model:</strong> Based on HV/RV crossover, volume patterns, and historical regime duration. Update setiap <span x-text="getCadenceDisplayName(selectedCadence)"></span>.
                        </div>
                    </div>
                    
                    <div x-show="errors.regimeTransition" class="alert alert-warning mt-3 mb-0">
                        <small x-text="errors.regimeTransition"></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Volatility Comparison (Multi-Asset) -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-3">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">Volatility Comparison</h5>
                                <small class="text-secondary">Compare volatility across multiple assets</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge" :class="volatilityRanking.opportunity ? 'text-bg-success' : 'text-bg-secondary'">
                                    <span x-show="volatilityRanking.opportunity">🎯 Opportunity Detected</span>
                                    <span x-show="!volatilityRanking.opportunity">Normal</span>
                                </span>
                                <div x-show="loadingStates.volatilityRanking" class="spinner-border spinner-border-sm text-primary"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Asset</th>
                                    <th>Volatility</th>
                                    <th>24h Change</th>
                                    <th>Current Price</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(asset, index) in volatilityRanking.ranking" :key="asset.symbol">
                                    <tr>
                                        <td>
                                            <span class="badge" :class="index === 0 ? 'text-bg-danger' : (index === volatilityRanking.ranking.length - 1 ? 'text-bg-success' : 'text-bg-secondary')"
                                                  x-text="'#' + (index + 1)"></span>
                                        </td>
                                        <td class="fw-semibold" x-text="asset.symbol"></td>
                                        <td>
                                            <span class="fw-bold" x-text="asset.volatility.toFixed(2) + '%'"></span>
                                        </td>
                                        <td>
                                            <span :class="asset.change_24h >= 0 ? 'text-success' : 'text-danger'"
                                                  x-text="(asset.change_24h >= 0 ? '+' : '') + (asset.change_24h * 100).toFixed(2) + '%'"></span>
                                        </td>
                                        <td x-text="'$' + asset.current_price.toLocaleString()"></td>
                                        <td>
                                            <span class="badge badge-sm" 
                                                  :class="asset.volatility > volatilityRanking.statistics.mean ? 'text-bg-danger' : 'text-bg-success'">
                                                <span x-show="asset.volatility > volatilityRanking.statistics.mean">High Vol</span>
                                                <span x-show="asset.volatility <= volatilityRanking.statistics.mean">Low Vol</span>
                                            </span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="row g-2 mt-2">
                        <div class="col-md-3">
                            <div class="p-2 rounded text-center" style="background: rgba(239, 68, 68, 0.1);">
                                <div class="text-secondary small">Max Spread</div>
                                <div class="fw-bold text-danger" x-text="volatilityRanking.maxSpread.toFixed(2) + '%'">--</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-2 rounded text-center" style="background: rgba(245, 158, 11, 0.1);">
                                <div class="text-secondary small">Avg Spread</div>
                                <div class="fw-bold text-warning" x-text="volatilityRanking.avgSpread.toFixed(2) + '%'">--</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-2 rounded text-center" style="background: rgba(34, 197, 94, 0.1);">
                                <div class="text-secondary small">High Vol Assets</div>
                                <div class="fw-bold text-success" x-text="volatilityRanking.opportunities">--</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-2 rounded text-center" style="background: rgba(59, 130, 246, 0.1);">
                                <div class="text-secondary small">Mean Volatility</div>
                                <div class="fw-bold text-primary" x-text="volatilityRanking.statistics.mean?.toFixed(2) + '%'">--</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3 p-2 rounded" style="background: rgba(59, 130, 246, 0.1);">
                        <div class="small text-secondary">
                            <strong>Comparison Insight:</strong> High spread (>20%) indicates divergence opportunities. Assets above mean + 1σ are flagged as high volatility. Use for relative value trading and pair selection.
                        </div>
                    </div>
                    
                    <div x-show="errors.volatilityRanking" class="alert alert-warning mt-3 mb-0">
                        <small x-text="errors.volatilityRanking"></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        .pulse-warning {
            background-color: #f59e0b;
            box-shadow: 0 0 0 rgba(245, 158, 11, 0.7);
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7);
            }
            50% {
                box-shadow: 0 0 0 8px rgba(245, 158, 11, 0);
            }
        }

        .badge-sm {
            font-size: 0.65rem;
        }

        .df-panel {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
    </style>
@endsection