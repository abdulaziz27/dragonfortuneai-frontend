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
                    <select class="form-select" style="width: 120px;" x-model="selectedPair" @change="refreshAll()">
                        <option value="BTCUSDT">BTCUSDT</option>
                        <option value="ETHUSDT">ETHUSDT</option>
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
                                <line :x1="100" :y1="100"
                                      :x2="100 + 70 * Math.cos((180 + volatilityScore * 1.8) * Math.PI / 180)"
                                      :y2="100 + 70 * Math.sin((180 + volatilityScore * 1.8) * Math.PI / 180)"
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
                                <div class="d-flex align-items-center mb-3">
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
                                <div class="small text-secondary" x-text="currentRegime.description">--</div>
                            </div>
                        </div>

                        <!-- Volatility Metrics -->
                        <div class="col-md-6">
                            <div class="h-100">
                                <div class="row g-2 h-100">
                                    <div class="col-6">
                                        <div class="p-2 rounded h-100" style="background: rgba(59, 130, 246, 0.1);">
                                            <div class="small text-secondary">HV (30d)</div>
                                            <div class="h5 mb-0 fw-bold text-primary" x-text="metrics.hv30 + '%'">--</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-2 rounded h-100" style="background: rgba(139, 92, 246, 0.1);">
                                            <div class="small text-secondary">RV (30d)</div>
                                            <div class="h5 mb-0 fw-bold text-purple" x-text="metrics.rv30 + '%'">--</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-2 rounded h-100" style="background: rgba(34, 197, 94, 0.1);">
                                            <div class="small text-secondary">24h Change</div>
                                            <div class="h5 mb-0 fw-bold" :class="metrics.change24h >= 0 ? 'text-success' : 'text-danger'" x-text="formatChange(metrics.change24h) + '%'">--</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-2 rounded h-100" style="background: rgba(245, 158, 11, 0.1);">
                                            <div class="small text-secondary">ATR (14)</div>
                                            <div class="h5 mb-0 fw-bold text-warning" x-text="'$' + metrics.atr14">--</div>
                                        </div>
                                    </div>
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
                                <template x-for="price in spotPrices" :key="price.id">
                                    <tr>
                                        <td class="small text-secondary" x-text="price.ts">--</td>
                                        <td class="fw-semibold" x-text="price.exchange">--</td>
                                        <td x-text="price.pair">--</td>
                                        <td class="text-end" x-text="'$' + price.open.toLocaleString()">--</td>
                                        <td class="text-end text-success" x-text="'$' + price.high.toLocaleString()">--</td>
                                        <td class="text-end text-danger" x-text="'$' + price.low.toLocaleString()">--</td>
                                        <td class="text-end fw-semibold" x-text="'$' + price.close.toLocaleString()">--</td>
                                        <td class="text-end" x-text="price.volume.toLocaleString()">--</td>
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
                                    <span class="small" x-text="'→ ' + transition.to">--</span>
                                    <span class="small fw-bold" x-text="transition.probability + '%'">--</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar"
                                         :class="transition.probability > 50 ? 'bg-danger' : transition.probability > 30 ? 'bg-warning' : 'bg-success'"
                                         :style="'width: ' + transition.probability + '%'"></div>
                                </div>
                            </div>
                        </template>
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

        <!-- Price Action Summary -->
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="df-panel p-3 h-100">
                    <div class="mb-3">
                        <h5 class="mb-0">Price Action Summary</h5>
                        <small class="text-secondary">Analisis pergerakan harga spot terkini</small>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1);">
                                <div class="small text-secondary">Highest Price (24h)</div>
                                <div class="h5 mb-1 fw-bold text-primary" x-text="'$' + priceAction.high24h.toLocaleString()">--</div>
                                <div class="small text-secondary" x-text="priceAction.highExchange">--</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1);">
                                <div class="small text-secondary">Lowest Price (24h)</div>
                                <div class="h5 mb-1 fw-bold text-danger" x-text="'$' + priceAction.low24h.toLocaleString()">--</div>
                                <div class="small text-secondary" x-text="priceAction.lowExchange">--</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded" style="background: rgba(34, 197, 94, 0.1);">
                                <div class="small text-secondary">Total Volume (24h)</div>
                                <div class="h5 mb-0 fw-bold text-success" x-text="priceAction.totalVolume.toLocaleString()">--</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded" style="background: rgba(245, 158, 11, 0.1);">
                                <div class="small text-secondary">Price Spread</div>
                                <div class="h5 mb-0 fw-bold text-warning" x-text="'$' + priceAction.spread.toLocaleString()">--</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="df-panel p-3 h-100">
                    <div class="mb-3">
                        <h5 class="mb-0">Exchange Volume Ranking</h5>
                        <small class="text-secondary">Top exchange berdasarkan volume spot trading</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Exchange</th>
                                    <th class="text-end">Volume</th>
                                    <th class="text-end">Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(vol, index) in volumeRanking" :key="vol.exchange">
                                    <tr>
                                        <td x-text="index + 1">--</td>
                                        <td class="fw-semibold" x-text="vol.exchange">--</td>
                                        <td class="text-end" x-text="vol.volume.toLocaleString()">--</td>
                                        <td class="text-end">
                                            <span class="badge text-bg-secondary" x-text="vol.share + '%'">--</span>
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

    <script>
        function volatilityRegimeController() {
            return {
                selectedPair: 'BTCUSDT',
                loading: false,
                volatilityScore: 52, // 0-100 scale
                volatilityTrendChart: null,
                volatilityHeatmapChart: null,
                volumeProfileChart: null,

                // Current Regime State
                currentRegime: {
                    name: 'Normal Volatility',
                    description: 'Market berada dalam kondisi volatilitas normal. Strategi balanced antara trend following dan swing trading optimal.'
                },

                // Volatility Metrics
                metrics: {
                    hv30: 48.5,  // Historical Volatility 30 days
                    rv30: 52.3,  // Realized Volatility 30 days
                    change24h: 3.42,
                    atr14: 1847
                },

                // Bollinger Squeeze Data
                squeezeData: {
                    status: 'squeeze',  // squeeze, expansion, normal
                    intensity: 75,
                    bbWidth: 2.3,
                    duration: 8,
                    label: 'Squeeze Active',
                    message: 'Breakout imminent'
                },

                // Multi-Timeframe Volatility Data
                timeframeVolatility: [
                    { timeframe: '1m', current: 65, average: 58 },
                    { timeframe: '5m', current: 58, average: 52 },
                    { timeframe: '15m', current: 52, average: 48 },
                    { timeframe: '1h', current: 48, average: 45 },
                    { timeframe: '4h', current: 45, average: 42 },
                    { timeframe: '1d', current: 42, average: 40 }
                ],

                // Exchange Divergence Data
                divergenceData: {
                    opportunity: true,
                    maxSpread: 0.18,
                    avgSpread: 0.07,
                    opportunities: 2,
                    pairs: [
                        { id: 1, pair: 'Binance-Coinbase', diff: 65, spreadPct: 0.15, opportunity: true },
                        { id: 2, pair: 'Kraken-Bybit', diff: 82, spreadPct: 0.19, opportunity: true },
                        { id: 3, pair: 'OKX-Binance', diff: 23, spreadPct: 0.05, opportunity: false },
                        { id: 4, pair: 'Coinbase-Kraken', diff: 18, spreadPct: 0.04, opportunity: false }
                    ]
                },

                // Regime Transition Probability
                regimeTransitions: [
                    { id: 1, to: 'High Volatility', probability: 58 },
                    { id: 2, to: 'Low Volatility', probability: 25 },
                    { id: 3, to: 'Normal Volatility', probability: 17 }
                ],

                // Volume Profile Data
                volumeProfile: {
                    poc: 43080,  // Point of Control
                    vah: 43320,  // Value Area High
                    val: 42780   // Value Area Low
                },

                // Price Action Summary
                priceAction: {
                    high24h: 43250,
                    highExchange: 'Binance',
                    low24h: 42180,
                    lowExchange: 'Coinbase',
                    totalVolume: 28450000,
                    spread: 1070
                },

                // Volume Ranking
                volumeRanking: [
                    { exchange: 'Binance', volume: 12450000, share: 43.8 },
                    { exchange: 'Coinbase', volume: 8920000, share: 31.3 },
                    { exchange: 'Kraken', volume: 4280000, share: 15.0 },
                    { exchange: 'Bybit', volume: 2800000, share: 9.8 }
                ],

                // Spot Prices Data (dummy but realistic)
                // Structure: { ts, exchange, pair, open, high, low, close, volume }
                spotPrices: [
                    {
                        id: 1,
                        ts: '10:45:30',
                        exchange: 'Binance',
                        pair: 'BTCUSDT',
                        open: 42850,
                        high: 43250,
                        low: 42680,
                        close: 43120,
                        volume: 2847,
                        change: 0.63
                    },
                    {
                        id: 2,
                        ts: '10:45:28',
                        exchange: 'Coinbase',
                        pair: 'BTCUSDT',
                        open: 42820,
                        high: 43180,
                        low: 42630,
                        close: 43095,
                        volume: 1923,
                        change: 0.64
                    },
                    {
                        id: 3,
                        ts: '10:45:25',
                        exchange: 'Kraken',
                        pair: 'BTCUSDT',
                        open: 42795,
                        high: 43210,
                        low: 42590,
                        close: 43050,
                        volume: 1156,
                        change: 0.60
                    },
                    {
                        id: 4,
                        ts: '10:45:22',
                        exchange: 'Bybit',
                        pair: 'BTCUSDT',
                        open: 42880,
                        high: 43240,
                        low: 42720,
                        close: 43140,
                        volume: 982,
                        change: 0.61
                    },
                    {
                        id: 5,
                        ts: '10:45:18',
                        exchange: 'OKX',
                        pair: 'BTCUSDT',
                        open: 42805,
                        high: 43195,
                        low: 42655,
                        close: 43080,
                        volume: 847,
                        change: 0.64
                    }
                ],

                init() {
                    // Wait for Chart.js to be ready
                    if (typeof Chart !== 'undefined') {
                        this.initCharts();
                        this.startPriceSimulation();
                    } else {
                        setTimeout(() => {
                            this.initCharts();
                            this.startPriceSimulation();
                        }, 100);
                    }
                },

                initCharts() {
                    // Volatility Trend Chart
                    const volTrendCtx = document.getElementById('volatilityTrendChart');
                    if (volTrendCtx) {
                        this.volatilityTrendChart = new Chart(volTrendCtx, {
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
                                    legend: { display: true, position: 'top' },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + '%';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        position: 'right',
                                        title: { display: true, text: 'Volatility (%)' },
                                        ticks: {
                                            callback: function(value) {
                                                return value + '%';
                                            }
                                        }
                                    },
                                    x: {
                                        title: { display: true, text: 'Date' }
                                    }
                                }
                            }
                        });
                    }

                    // Intraday Volatility Heatmap Chart
                    const heatmapCtx = document.getElementById('volatilityHeatmapChart');
                    if (heatmapCtx) {
                        this.volatilityHeatmapChart = new Chart(heatmapCtx, {
                            type: 'bar',
                            data: {
                                labels: Array.from({length: 24}, (_, i) => i + ':00 UTC'),
                                datasets: [{
                                    label: 'Hourly Volatility',
                                    data: this.generateHourlyVolatility(),
                                    backgroundColor: function(context) {
                                        const value = context.parsed.y;
                                        if (value > 70) return 'rgba(239, 68, 68, 0.8)';
                                        if (value > 50) return 'rgba(245, 158, 11, 0.8)';
                                        if (value > 30) return 'rgba(34, 197, 94, 0.8)';
                                        return 'rgba(59, 130, 246, 0.6)';
                                    },
                                    borderColor: function(context) {
                                        const value = context.parsed.y;
                                        if (value > 70) return 'rgb(239, 68, 68)';
                                        if (value > 50) return 'rgb(245, 158, 11)';
                                        if (value > 30) return 'rgb(34, 197, 94)';
                                        return 'rgb(59, 130, 246)';
                                    },
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return 'Volatility: ' + context.parsed.y.toFixed(1) + '%';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        position: 'right',
                                        title: { display: true, text: 'Volatility (%)' },
                                        ticks: {
                                            callback: function(value) {
                                                return value + '%';
                                            }
                                        }
                                    },
                                    x: {
                                        title: { display: true, text: 'Hour (UTC)' }
                                    }
                                }
                            }
                        });
                    }

                    // Volume Profile Chart
                    const volumeProfileCtx = document.getElementById('volumeProfileChart');
                    if (volumeProfileCtx) {
                        const priceLabels = [];
                        const volumeData = [];
                        const basePrice = this.selectedPair === 'BTCUSDT' ? 43000 : 2280;

                        // Generate price levels and volumes
                        for (let i = -10; i <= 10; i++) {
                            const price = basePrice + (i * 50);
                            priceLabels.push(price.toLocaleString());

                            // POC at center (i=0) has highest volume
                            const distance = Math.abs(i);
                            const volume = Math.max(100, 5000 - (distance * distance * 40));
                            volumeData.push(volume);
                        }

                        this.volumeProfileChart = new Chart(volumeProfileCtx, {
                            type: 'bar',
                            data: {
                                labels: priceLabels,
                                datasets: [{
                                    label: 'Volume',
                                    data: volumeData,
                                    backgroundColor: function(context) {
                                        const index = context.dataIndex;
                                        // Highlight POC (center), VAH (top), VAL (bottom)
                                        if (index === 10) return 'rgba(59, 130, 246, 0.8)'; // POC
                                        if (index >= 15) return 'rgba(34, 197, 94, 0.6)'; // VAH
                                        if (index <= 5) return 'rgba(239, 68, 68, 0.6)'; // VAL
                                        return 'rgba(156, 163, 175, 0.4)';
                                    },
                                    borderColor: 'rgba(75, 85, 99, 0.8)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                indexAxis: 'y',
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return 'Volume: ' + context.parsed.x.toLocaleString();
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        title: { display: true, text: 'Volume' }
                                    },
                                    y: {
                                        title: { display: true, text: 'Price Level' }
                                    }
                                }
                            }
                        });
                    }
                },

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

                generateVolatilityData(days, min, max) {
                    const data = [];
                    let value = (min + max) / 2;
                    for (let i = 0; i < days; i++) {
                        value += (Math.random() - 0.5) * 8;
                        value = Math.max(min, Math.min(max, value));
                        data.push(parseFloat(value.toFixed(2)));
                    }
                    return data;
                },

                generateHourlyVolatility() {
                    // Generate realistic intraday volatility pattern
                    // Higher volatility during London-NY overlap (12-16 UTC)
                    // Lower during Asian session (00-08 UTC)
                    const baseVolatility = [
                        25, 22, 20, 18, 20, 25,  // 00-05 UTC: Asian session (calm)
                        35, 42, 48, 52, 55, 58,  // 06-11 UTC: London open (rising)
                        72, 78, 75, 68,          // 12-15 UTC: London-NY overlap (peak)
                        62, 58, 52, 48,          // 16-19 UTC: NY afternoon (declining)
                        42, 38, 32, 28           // 20-23 UTC: After hours (calm)
                    ];

                    // Add some random variation
                    return baseVolatility.map(vol => {
                        const variation = (Math.random() - 0.5) * 10;
                        return parseFloat((vol + variation).toFixed(1));
                    });
                },

                startPriceSimulation() {
                    // Simulate real-time price updates every 5 seconds
                    setInterval(() => {
                        const exchanges = ['Binance', 'Coinbase', 'Kraken', 'Bybit', 'OKX'];
                        const randomExchange = exchanges[Math.floor(Math.random() * exchanges.length)];

                        const basePrice = this.selectedPair === 'BTCUSDT' ? 43000 : 2280;
                        const variance = basePrice * 0.005; // 0.5% variance

                        const open = basePrice + (Math.random() - 0.5) * variance;
                        const close = open + (Math.random() - 0.5) * variance * 0.5;
                        const high = Math.max(open, close) + Math.random() * variance * 0.3;
                        const low = Math.min(open, close) - Math.random() * variance * 0.3;
                        const change = ((close - open) / open * 100);

                        const newPrice = {
                            id: Date.now(),
                            ts: new Date().toLocaleTimeString(),
                            exchange: randomExchange,
                            pair: this.selectedPair,
                            open: Math.round(open),
                            high: Math.round(high),
                            low: Math.round(low),
                            close: Math.round(close),
                            volume: Math.floor(Math.random() * 2000 + 500),
                            change: parseFloat(change.toFixed(2))
                        };

                        this.spotPrices.unshift(newPrice);
                        if (this.spotPrices.length > 8) {
                            this.spotPrices.pop();
                        }

                        // Update divergence data
                        this.updateDivergenceData();

                        // Update squeeze data every 10 updates
                        if (this.spotPrices.length % 10 === 0) {
                            this.updateSqueezeData();
                        }
                    }, 5000);

                    // Update regime transitions periodically
                    setInterval(() => {
                        this.updateRegimeTransitions();
                    }, 30000); // Every 30 seconds
                },

                updateDivergenceData() {
                    // Simulate price divergence updates
                    this.divergenceData.pairs = this.divergenceData.pairs.map(pair => {
                        const newDiff = pair.diff + (Math.random() - 0.5) * 20;
                        const newSpreadPct = parseFloat((Math.abs(newDiff) / 43000 * 100).toFixed(2));
                        return {
                            ...pair,
                            diff: Math.round(newDiff),
                            spreadPct: newSpreadPct,
                            opportunity: newSpreadPct > 0.15
                        };
                    });

                    const spreads = this.divergenceData.pairs.map(p => p.spreadPct);
                    this.divergenceData.maxSpread = Math.max(...spreads);
                    this.divergenceData.avgSpread = parseFloat((spreads.reduce((a, b) => a + b, 0) / spreads.length).toFixed(2));
                    this.divergenceData.opportunities = this.divergenceData.pairs.filter(p => p.opportunity).length;
                    this.divergenceData.opportunity = this.divergenceData.opportunities > 0;
                },

                updateSqueezeData() {
                    // Simulate BB Squeeze dynamics
                    const statuses = ['squeeze', 'expansion', 'normal'];
                    const randomStatus = statuses[Math.floor(Math.random() * statuses.length)];

                    this.squeezeData.status = randomStatus;
                    this.squeezeData.intensity = Math.floor(Math.random() * 40 + 50);
                    this.squeezeData.bbWidth = parseFloat((Math.random() * 3 + 1.5).toFixed(1));
                    this.squeezeData.duration = Math.floor(Math.random() * 12 + 4);

                    if (randomStatus === 'squeeze') {
                        this.squeezeData.label = 'Squeeze Active';
                        this.squeezeData.message = 'Breakout imminent';
                    } else if (randomStatus === 'expansion') {
                        this.squeezeData.label = 'Expansion';
                        this.squeezeData.message = 'Trending move';
                    } else {
                        this.squeezeData.label = 'Normal';
                        this.squeezeData.message = 'Range-bound';
                    }
                },

                updateRegimeTransitions() {
                    // Simulate regime transition probability updates
                    const total = 100;
                    const prob1 = Math.floor(Math.random() * 60 + 20);
                    const prob2 = Math.floor(Math.random() * (total - prob1 - 10));
                    const prob3 = total - prob1 - prob2;

                    this.regimeTransitions = [
                        { id: 1, to: 'High Volatility', probability: prob1 },
                        { id: 2, to: 'Low Volatility', probability: prob2 },
                        { id: 3, to: 'Normal Volatility', probability: prob3 }
                    ].sort((a, b) => b.probability - a.probability);
                },

                getVolatilityBadge() {
                    if (this.volatilityScore <= 30) return 'text-bg-success';
                    if (this.volatilityScore <= 60) return 'text-bg-warning';
                    return 'text-bg-danger';
                },

                getVolatilityLabel() {
                    if (this.volatilityScore <= 30) return 'Calm';
                    if (this.volatilityScore <= 45) return 'Low';
                    if (this.volatilityScore <= 60) return 'Normal';
                    if (this.volatilityScore <= 75) return 'Elevated';
                    return 'Extreme';
                },

                getVolatilityAlert() {
                    if (this.volatilityScore <= 30) return 'bg-success bg-opacity-10';
                    if (this.volatilityScore <= 60) return 'bg-info bg-opacity-10';
                    return 'bg-danger bg-opacity-10';
                },

                getVolatilityTitle() {
                    if (this.volatilityScore <= 30) return 'Low Volatility - Range Trading';
                    if (this.volatilityScore <= 60) return 'Normal Volatility - Balanced Strategy';
                    return 'High Volatility - Risk Management';
                },

                getVolatilityMessage() {
                    if (this.volatilityScore <= 30) {
                        return `Volatilitas rendah (${this.volatilityScore}%). Market range-bound, cocok untuk mean reversion & sell options premium.`;
                    }
                    if (this.volatilityScore <= 60) {
                        return `Volatilitas normal (${this.volatilityScore}%). Market balanced, gunakan trend following & swing trading strategy.`;
                    }
                    return `Volatilitas tinggi (${this.volatilityScore}%). Event-driven moves, reduce position size & widen stop loss.`;
                },

                getCurrentRegimeBackground() {
                    if (this.volatilityScore <= 30) return 'bg-success bg-opacity-10';
                    if (this.volatilityScore <= 60) return 'bg-warning bg-opacity-10';
                    return 'bg-danger bg-opacity-10';
                },

                getCurrentRegimeIconBg() {
                    if (this.volatilityScore <= 30) return 'bg-success bg-opacity-20';
                    if (this.volatilityScore <= 60) return 'bg-warning bg-opacity-20';
                    return 'bg-danger bg-opacity-20';
                },

                getCurrentRegimeIconColor() {
                    if (this.volatilityScore <= 30) return 'text-success';
                    if (this.volatilityScore <= 60) return 'text-warning';
                    return 'text-danger';
                },

                formatChange(value) {
                    return (value >= 0 ? '+' : '') + value.toFixed(2);
                },

                refreshAll() {
                    this.loading = true;

                    // Simulate data refresh
                    setTimeout(() => {
                        // Update volatility score
                        this.volatilityScore = Math.floor(Math.random() * 40 + 30);

                        // Update metrics
                        this.metrics.hv30 = parseFloat((Math.random() * 20 + 40).toFixed(1));
                        this.metrics.rv30 = parseFloat((Math.random() * 20 + 45).toFixed(1));
                        this.metrics.change24h = parseFloat((Math.random() * 8 - 2).toFixed(2));

                        // Update regime based on volatility
                        if (this.volatilityScore <= 30) {
                            this.currentRegime.name = 'Low Volatility';
                            this.currentRegime.description = 'Market range-bound dengan volatilitas rendah. Optimal untuk mean reversion strategies.';
                        } else if (this.volatilityScore <= 60) {
                            this.currentRegime.name = 'Normal Volatility';
                            this.currentRegime.description = 'Market berada dalam kondisi volatilitas normal. Strategi balanced antara trend following dan swing trading optimal.';
                        } else {
                            this.currentRegime.name = 'High Volatility';
                            this.currentRegime.description = 'Event-driven market dengan volatilitas tinggi. Risk management krusial, reduce position size.';
                        }

                        // Update spot prices for new pair
                        this.updateSpotPricesForPair();

                        this.loading = false;
                    }, 1000);
                },

                updateSpotPricesForPair() {
                    const exchanges = ['Binance', 'Coinbase', 'Kraken', 'Bybit', 'OKX'];
                    const basePrice = this.selectedPair === 'BTCUSDT' ? 43000 : 2280;
                    const variance = basePrice * 0.005;

                    this.spotPrices = exchanges.map((exchange, index) => {
                        const open = basePrice + (Math.random() - 0.5) * variance;
                        const close = open + (Math.random() - 0.5) * variance * 0.5;
                        const high = Math.max(open, close) + Math.random() * variance * 0.3;
                        const low = Math.min(open, close) - Math.random() * variance * 0.3;
                        const change = ((close - open) / open * 100);

                        return {
                            id: index + 1,
                            ts: new Date().toLocaleTimeString(),
                            exchange: exchange,
                            pair: this.selectedPair,
                            open: Math.round(open),
                            high: Math.round(high),
                            low: Math.round(low),
                            close: Math.round(close),
                            volume: Math.floor(Math.random() * 2000 + 500),
                            change: parseFloat(change.toFixed(2))
                        };
                    });
                }
            };
        }
    </script>

    <style>
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
    </style>
@endsection

