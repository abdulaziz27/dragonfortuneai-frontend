@extends('layouts.app')

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

                    <button class="btn btn-primary" @click="refreshAll()" :disabled="loading">
                        <span x-show="!loading">Refresh All</span>
                        <span x-show="loading" class="spinner-border spinner-border-sm"></span>
                    </button>
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
                        <small class="text-secondary">Real-time volatilitas untuk <span x-text="selectedPair"></span></small>
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

                        <!-- Enhanced Volatility Metrics Cards (Task 3.3) -->
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
                                                <div class="small text-secondary">HV (30d)</div>
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
                                                <div class="small text-secondary">RV (30d)</div>
                                                <div class="badge badge-sm" :class="getMetricPercentileBadge('rv')" x-text="getMetricPercentileLabel('rv')">--</div>
                                            </div>
                                            <div class="h5 mb-0 fw-bold" :class="getMetricValueClass('rv')" x-text="formatMetricValue(metrics.rv30, '%')">--</div>
                                            <div class="small text-muted mt-1" x-text="getMetricTrend('rv')">--</div>
                                        </div>
                                    </div>
                                    
                                    <!-- 24h Price Change Card -->
                                    <div class="col-6">
                                        <div class="p-2 rounded h-100 position-relative" 
                                             :class="getMetricCardClass('change24h')"
                                             :title="getMetricTooltip('change24h')"
                                             data-bs-toggle="tooltip">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <div class="small text-secondary">24h Change</div>
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
                                                <div class="small text-secondary">ATR (14)</div>
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

        <!-- Regime Transition Probability + Volume Profile -->
        <div class="row g-3">
            <!-- Regime Transition Probability -->
            <div class="col-lg-6">
                <div class="df-panel p-3 h-100">
                    <div class="mb-3">
                        <h5 class="mb-0">Regime Transition Probability</h5>
                        <small class="text-secondary">Forecast probabilitas perubahan regime berikutnya</small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-semibold">Current: <span x-text="currentRegime.name"></span></span>
                            <span class="badge text-bg-info">Next 6-12h</span>
                        </div>

                        <template x-for="transition in regimeTransitions" :key="transition.id">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small" x-text="'→ ' + formatTransitionLabel(transition.to)">--</span>
                                    <span class="small" :class="getTransitionProbabilityClass(transition.probability)" x-text="transition.probability + '%'">--</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar"
                                         :class="getTransitionBarClass(transition.probability)"
                                         :style="'width: ' + transition.probability + '%'"
                                         :title="'Confidence: ' + (transition.confidence * 100).toFixed(1) + '%'"></div>
                                </div>
                                <div class="small text-muted mt-1" x-show="transition.timeframe" x-text="'Timeframe: ' + transition.timeframe">--</div>
                            </div>
                        </template>
                        
                        <!-- No transitions message -->
                        <div x-show="regimeTransitions.length === 0" class="text-center py-3">
                            <div class="text-muted">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="mb-2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M12 6v6l4 2"/>
                                </svg>
                                <div class="small">Loading transition probabilities...</div>
                            </div>
                        </div>
                        
                        <!-- High probability alert -->
                        <div x-show="isRegimeChangelikely()" class="alert alert-warning py-2 mt-3">
                            <div class="d-flex align-items-center">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                    <line x1="12" y1="9" x2="12" y2="13"/>
                                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                                </svg>
                                <div class="small">
                                    <strong>Regime Change Alert:</strong> High probability of transition detected. Monitor positions closely.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-2 rounded" style="background: rgba(139, 92, 246, 0.1);">
                        <div class="small text-secondary">
                            <strong>Forecast Model:</strong> Based on HV/RV crossover, volume patterns, and historical regime duration. Update setiap 5 menit.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Volume Profile by Price Level -->
            <div class="col-lg-6">
                <div class="df-panel p-3 h-100">
                    <div class="mb-3">
                        <h5 class="mb-0">Volume Profile - Last 24h</h5>
                        <small class="text-secondary">Distribusi volume per level harga</small>
                    </div>
                    <div style="min-height: 280px;">
                        <canvas id="volumeProfileChart"></canvas>
                    </div>
                    <div class="mt-2">
                        <div class="row g-2 small">
                            <div class="col-4">
                                <div class="p-2 rounded text-center" style="background: rgba(59, 130, 246, 0.1);">
                                    <div class="text-secondary">POC</div>
                                    <div class="fw-bold text-primary" x-text="'$' + volumeProfile.poc.toLocaleString()">--</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded text-center" style="background: rgba(34, 197, 94, 0.1);">
                                    <div class="text-secondary">VAH</div>
                                    <div class="fw-bold text-success" x-text="'$' + volumeProfile.vah.toLocaleString()">--</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded text-center" style="background: rgba(239, 68, 68, 0.1);">
                                    <div class="text-secondary">VAL</div>
                                    <div class="fw-bold text-danger" x-text="'$' + volumeProfile.val.toLocaleString()">--</div>
                                </div>
                            </div>
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
        <div class="row g-3">
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
        </div>

        <!-- Intraday Volatility Heatmap + Bollinger Squeeze -->
        <div class="row g-3">
            <!-- Intraday Volatility Heatmap -->
            <div class="col-lg-8">
                <div class="df-panel p-3 h-100">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">Intraday Volatility Heatmap</h5>
                                <small class="text-secondary">Identifikasi jam-jam dengan volatilitas tertinggi (UTC)</small>
                            </div>
                            <span class="badge text-bg-info">24h Pattern</span>
                        </div>
                    </div>
                    <div style="min-height: 280px;">
                        <canvas id="volatilityHeatmapChart"></canvas>
                    </div>
                    <div class="mt-2 p-2 rounded" style="background: rgba(139, 92, 246, 0.1);">
                        <div class="small text-secondary">
                            <strong>Trading Hours Insight:</strong> Volatilitas biasanya spike saat overlap session (London-NY: 12-16 UTC). Asian session (00-08 UTC) cenderung lebih tenang.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bollinger Bands Squeeze Indicator -->
            <div class="col-lg-4">
                <div class="df-panel p-4 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="mb-1">Bollinger Squeeze</h5>
                        <small class="text-secondary">Volatility breakout indicator</small>
                    </div>

                    <div class="text-center mb-3 flex-grow-1 d-flex align-items-center justify-content-center">
                        <div>
                            <div class="mb-3">
                                <div class="position-relative d-inline-block" style="width: 120px; height: 120px;">
                                    <svg viewBox="0 0 120 120" class="w-100 h-100">
                                        <circle cx="60" cy="60" r="50" fill="none" stroke="#e5e7eb" stroke-width="10"/>
                                        <circle cx="60" cy="60" r="50" fill="none"
                                                :stroke="squeezeData.status === 'squeeze' ? '#ef4444' : squeezeData.status === 'expansion' ? '#22c55e' : '#f59e0b'"
                                                stroke-width="10"
                                                :stroke-dasharray="314"
                                                :stroke-dashoffset="314 - (314 * squeezeData.intensity / 100)"
                                                transform="rotate(-90 60 60)"/>
                                    </svg>
                                    <div class="position-absolute top-50 start-50 translate-middle text-center">
                                        <div class="h4 mb-0 fw-bold" x-text="squeezeData.intensity + '%'">--</div>
                                    </div>
                                </div>
                            </div>
                            <div class="badge fs-6 mb-2"
                                 :class="squeezeData.status === 'squeeze' ? 'text-bg-danger' : squeezeData.status === 'expansion' ? 'text-bg-success' : 'text-bg-warning'"
                                 x-text="squeezeData.label">--</div>
                            <div class="small text-secondary" x-text="squeezeData.message">--</div>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <div class="row g-2 small">
                            <div class="col-6">
                                <div class="p-2 rounded text-center" style="background: rgba(239, 68, 68, 0.1);">
                                    <div class="text-secondary">BB Width</div>
                                    <div class="fw-bold text-danger" x-text="squeezeData.bbWidth + '%'">--</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded text-center" style="background: rgba(34, 197, 94, 0.1);">
                                    <div class="text-secondary">Duration</div>
                                    <div class="fw-bold text-success" x-text="squeezeData.duration + 'h'">--</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Multi-Timeframe Volatility + Exchange Divergence -->
        <div class="row g-3">
            <!-- Multi-Timeframe Volatility -->
            <div class="col-lg-6">
                <div class="df-panel p-3 h-100">
                    <div class="mb-3">
                        <h5 class="mb-0">Multi-Timeframe Volatility</h5>
                        <small class="text-secondary">Perbandingan volatilitas across timeframes</small>
                    </div>

                    <!-- Simplified Timeframe Volatility Bars -->
                    <div class="mb-3">
                        <template x-for="tf in timeframeVolatility" :key="tf.timeframe">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge text-bg-secondary" style="width: 50px;" x-text="tf.timeframe">--</span>
                                        <span class="small fw-semibold" x-text="tf.current + '%'">--</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="small text-secondary">7d avg: <span x-text="tf.average + '%'"></span></span>
                                        <span class="badge"
                                              :class="tf.current > tf.average ? 'text-bg-danger' : tf.current < tf.average ? 'text-bg-success' : 'text-bg-warning'">
                                            <span x-show="tf.current > tf.average">↑ Above</span>
                                            <span x-show="tf.current < tf.average">↓ Below</span>
                                            <span x-show="tf.current === tf.average">→ Avg</span>
                                        </span>
                                    </div>
                                </div>
                                <div class="progress" style="height: 12px;">
                                    <div class="progress-bar"
                                         :class="tf.current > 60 ? 'bg-danger' : tf.current > 40 ? 'bg-warning' : 'bg-success'"
                                         :style="'width: ' + tf.current + '%'"></div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="p-2 rounded" style="background: rgba(59, 130, 246, 0.1);">
                        <div class="small text-secondary">
                            <strong>Timeframe Analysis:</strong> Higher TF > Lower TF = sustained trend. Lower TF > Higher TF = choppy consolidation.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exchange Price Divergence Monitor -->
            <div class="col-lg-6">
                <div class="df-panel p-3 h-100">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">Exchange Price Divergence</h5>
                                <small class="text-secondary">Real-time arbitrage opportunity detector</small>
                            </div>
                            <span class="badge" :class="divergenceData.opportunity ? 'text-bg-success' : 'text-bg-secondary'">
                                <span x-show="divergenceData.opportunity">Arbitrage Alert</span>
                                <span x-show="!divergenceData.opportunity">Normal</span>
                            </span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Exchange Pair</th>
                                    <th class="text-end">Price Diff</th>
                                    <th class="text-end">Spread %</th>
                                    <th class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="div in divergenceData.pairs" :key="div.id">
                                    <tr>
                                        <td class="fw-semibold" x-text="div.pair">--</td>
                                        <td class="text-end" :class="Math.abs(div.diff) > 50 ? 'text-danger fw-bold' : 'text-secondary'" x-text="'$' + div.diff">--</td>
                                        <td class="text-end" x-text="div.spreadPct + '%'">--</td>
                                        <td class="text-end">
                                            <span class="badge" :class="div.opportunity ? 'text-bg-success' : 'text-bg-secondary'" x-text="div.opportunity ? 'ARB' : '-'">--</span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <div class="row g-2 small">
                            <div class="col-4">
                                <div class="p-2 rounded text-center" style="background: rgba(239, 68, 68, 0.1);">
                                    <div class="text-secondary">Max Spread</div>
                                    <div class="fw-bold text-danger" x-text="divergenceData.maxSpread + '%'">--</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded text-center" style="background: rgba(245, 158, 11, 0.1);">
                                    <div class="text-secondary">Avg Spread</div>
                                    <div class="fw-bold text-warning" x-text="divergenceData.avgSpread + '%'">--</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded text-center" style="background: rgba(34, 197, 94, 0.1);">
                                    <div class="text-secondary">Opportunities</div>
                                    <div class="fw-bold text-success" x-text="divergenceData.opportunities">--</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spot Prices Overview -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-3">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">Spot Prices Overview - <span x-text="selectedPair"></span></h5>
                                <small class="text-secondary">Data OHLCV terbaru dari berbagai exchange</small>
                            </div>
                            <span class="badge text-bg-success">Live</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Exchange</th>
                                    <th>Pair</th>
                                    <th class="text-end">Open</th>
                                    <th class="text-end">High</th>
                                    <th class="text-end">Low</th>
                                    <th class="text-end">Close</th>
                                    <th class="text-end">Volume</th>
                                    <th class="text-end">Change</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loading state -->
                                <template x-if="loading && (!spotPrices || spotPrices.length === 0)">
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                                <span class="text-secondary">Loading spot prices...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </template>

                                <!-- No data state -->
                                <template x-if="!loading && (!spotPrices || spotPrices.length === 0)">
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="mb-2">
                                                    <circle cx="12" cy="12" r="10"/>
                                                    <path d="M12 6v6l4 2"/>
                                                </svg>
                                                <div class="small">No spot price data available</div>
                                                <button class="btn btn-sm btn-outline-primary mt-2" @click="refreshAll()">
                                                    Retry
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>

                                <!-- Data rows -->
                                <template x-for="price in spotPrices" :key="price.exchange + '_' + price.pair">
                                    <tr>
                                        <td class="small text-secondary" x-text="formatTimestamp(price.timestamp)">--</td>
                                        <td class="fw-semibold" x-text="price.exchange">--</td>
                                        <td x-text="price.pair">--</td>
                                        <td class="text-end" x-text="formatPrice(price.open)">--</td>
                                        <td class="text-end text-success" x-text="formatPrice(price.high)">--</td>
                                        <td class="text-end text-danger" x-text="formatPrice(price.low)">--</td>
                                        <td class="text-end fw-semibold" x-text="formatPrice(price.close)">--</td>
                                        <td class="text-end" x-text="formatVolume(price.volume)">--</td>
                                        <td class="text-end">
                                            <span :class="price.change >= 0 ? 'text-success' : 'text-danger'" x-text="formatChange(price.change) + '%'">--</span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    
    <!-- Load the volatility regime controller -->
    <script src="{{ asset('js/volatility-regime-controller.js') }}"></script>

    <script>
        // Simple initialization without conflicts
        document.addEventListener('DOMContentLoaded', () => {
            console.log('📊 DOM loaded, volatility controller should be available');
            
            // Check if controller function exists
            if (typeof volatilityRegimeController === 'function') {
                console.log('✅ volatilityRegimeController function is available');
            } else {
                console.error('❌ volatilityRegimeController function not found');
            }
        });
                
                // Bollinger Squeeze data (integrated with API)
                squeezeData: {
                    status: 'normal',
                    intensity: 0,
                    bbWidth: 0,
                    duration: 0,
                    label: 'Loading...',
                    message: 'Calculating squeeze indicator...'
                },

                // Multi-Timeframe Volatility Data (integrated with API)
                timeframeVolatility: [
                    { timeframe: '1h', current: 0, average: 0 },
                    { timeframe: '4h', current: 0, average: 0 },
                    { timeframe: '1d', current: 0, average: 0 },
                    { timeframe: '1w', current: 0, average: 0 }
                ],

                // Exchange Divergence Data (integrated with API)
                divergenceData: {
                    opportunity: false,
                    maxSpread: 0,
                    avgSpread: 0,
                    opportunities: 0,
                    pairs: []
                },

                // Formatting methods for spot prices display
                formatPrice(price) {
                    if (!price || isNaN(price)) return '--';
                    if (price >= 1000) {
                        return '$' + price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    } else if (price >= 1) {
                        return '$' + price.toFixed(4);
                    } else {
                        return '$' + price.toFixed(6);
                    }
                },

                formatVolume(volume) {
                    if (!volume || isNaN(volume)) return '--';
                    if (volume >= 1e9) {
                        return (volume / 1e9).toFixed(2) + 'B';
                    } else if (volume >= 1e6) {
                        return (volume / 1e6).toFixed(2) + 'M';
                    } else if (volume >= 1e3) {
                        return (volume / 1e3).toFixed(2) + 'K';
                    } else {
                        return volume.toFixed(2);
                    }
                },

                formatChange(change) {
                    if (!change || isNaN(change)) return '--';
                    const sign = change >= 0 ? '+' : '';
                    return sign + change.toFixed(2);
                },

                formatTimestamp(timestamp) {
                    if (!timestamp) return '--';
                    try {
                        const date = new Date(timestamp);
                        return date.toLocaleTimeString('en-US', { 
                            hour12: false, 
                            hour: '2-digit', 
                            minute: '2-digit', 
                            second: '2-digit' 
                        });
                    } catch (error) {
                        return '--';
                    }
                },

                // Initialize charts after Alpine is ready
                init() {
                    // Call the original init from the controller
                    if (this.__proto__.init) {
                        this.__proto__.init.call(this);
                    }
                    
                    // Initialize dummy charts
                    this.$nextTick(() => {
                        this.initDummyCharts();
                        this.startPriceSimulation();
                    });
                },

                initDummyCharts() {
                    // Volatility Trend Chart
                    const volTrendCtx = document.getElementById('volatilityTrendChart');
                    if (volTrendCtx) {
                        new Chart(volTrendCtx, {
                            type: 'line',
                            data: {
                                labels: this.generateDateLabels(30),
                                datasets: [
                                    {
                                        label: 'Historical Volatility (HV)',
                                        data: this.generateVolatilityData(30, 45, 55),
                                        borderColor: 'rgb(59, 130, 246)',
                                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                        tension: 0.4,
                                        fill: true,
                                        borderWidth: 2
                                    },
                                    {
                                        label: 'Realized Volatility (RV)',
                                        data: this.generateVolatilityData(30, 48, 58),
                                        borderColor: 'rgb(139, 92, 246)',
                                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                                        tension: 0.4,
                                        fill: true,
                                        borderWidth: 2
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: { mode: 'index', intersect: false },
                                plugins: {
                                    legend: { display: true, position: 'top' }
                                },
                                scales: {
                                    y: {
                                        position: 'right',
                                        title: { display: true, text: 'Volatility (%)' }
                                    },
                                    x: {
                                        title: { display: true, text: 'Date' }
                                    }
                                }
                            }
                        });
                    }

                    // Volatility Heatmap Chart
                    const volHeatmapCtx = document.getElementById('volatilityHeatmapChart');
                    if (volHeatmapCtx) {
                        new Chart(volHeatmapCtx, {
                            type: 'bar',
                            data: {
                                labels: Array.from({length: 24}, (_, i) => i + ':00'),
                                datasets: [{
                                    label: 'Hourly Volatility',
                                    data: this.generateHourlyVolatilityData(),
                                    backgroundColor: function(context) {
                                        const value = context.parsed.y;
                                        if (value > 60) return 'rgba(239, 68, 68, 0.8)';
                                        if (value > 40) return 'rgba(245, 158, 11, 0.8)';
                                        return 'rgba(34, 197, 94, 0.8)';
                                    }
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: { title: { display: true, text: 'Volatility (%)' } },
                                    x: { title: { display: true, text: 'Hour (UTC)' } }
                                }
                            }
                        });
                    }
                },

                generateDateLabels(days) {
                    const labels = [];
                    for (let i = days - 1; i >= 0; i--) {
                        const date = new Date();
                        date.setDate(date.getDate() - i);
                        labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
                    }
                    return labels;
                },

                generateVolatilityData(days, min, max) {
                    const data = [];
                    for (let i = 0; i < days; i++) {
                        data.push(parseFloat((Math.random() * (max - min) + min).toFixed(2)));
                    }
                    return data;
                },

                generateHourlyVolatilityData() {
                    const data = [];
                    const peakHours = [2, 8, 14, 20];
                    for (let i = 0; i < 24; i++) {
                        let baseVol = 30 + Math.random() * 20;
                        if (peakHours.includes(i)) {
                            baseVol += 20 + Math.random() * 15;
                        }
                        data.push(parseFloat(baseVol.toFixed(1)));
                    }
                    return data;
                },

                startPriceSimulation() {
                    setInterval(() => {
                        // Update spot prices with small random changes
                        this.spotPrices.forEach(price => {
                            const changePercent = (Math.random() - 0.5) * 0.1;
                            const newClose = price.close * (1 + changePercent / 100);
                            price.close = Math.round(newClose);
                            price.change = parseFloat(((price.close - price.open) / price.open * 100).toFixed(2));
                            price.ts = new Date().toLocaleTimeString();
                        });
                    }, 3000);
                }
            }));
    </script>

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

        .text-purple {
            color: #8b5cf6 !important;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .badge-sm {
            font-size: 0.65rem;
        }

        /* Tooltip styling */
        .tooltip {
            font-size: 0.8rem;
        }
    </style>
@endsection