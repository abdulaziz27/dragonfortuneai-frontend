@extends('layouts.app')

@section('content')
    {{--
        Bitcoin: Exchange Inflow CDD Dashboard
        Think like a trader • Build like an engineer • Visualize like a designer

        Interpretasi Trading:
        - CDD (Coin Days Destroyed) mengukur "age" dari coins yang bergerak
        - Exchange Inflow CDD tinggi → Old coins masuk exchange → Potensi selling pressure
        - Spike CDD → Long-term holders mulai distribute → Bearish signal
        - Low CDD → Mostly young coins moving → Normal trading activity
    --}}

    <div class="d-flex flex-column h-100 gap-3" x-data="exchangeInflowCDDController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">₿ Bitcoin: Exchange Inflow CDD</h1>
                        <span class="pulse-dot pulse-success"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Monitor coin age of exchange inflows to detect long-term holder distribution and potential selling pressure
                    </p>
                </div>

                <!-- Global Controls -->
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <select class="form-select" style="width: 140px;" x-model="globalPeriod" @change="updatePeriod()">
                        <option value="7d">7 Days</option>
                        <option value="30d" selected>30 Days</option>
                        <option value="90d">90 Days</option>
                        <option value="180d">6 Months</option>
                        <option value="1y">1 Year</option>
                    </select>

                    <button class="btn btn-primary" @click="refreshAll()" :disabled="globalLoading">
                        <span x-show="!globalLoading">🔄 Refresh</span>
                        <span x-show="globalLoading" class="spinner-border spinner-border-sm"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Cards Row -->
        <div class="row g-3">
            <!-- Current CDD -->
            <div class="col-md-3">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Current CDD</span>
                        <span class="badge text-bg-primary">Latest</span>
                    </div>
                    <div class="h3 mb-1" x-text="formatCDD(currentCDD)">--</div>
                    <div class="small" :class="getTrendClass(cddChange)">
                        <span x-text="formatChange(cddChange)">--</span> vs 24h ago
                    </div>
                </div>
            </div>

            <!-- Average CDD -->
            <div class="col-md-3">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Period Average</span>
                        <span class="badge text-bg-info">Avg</span>
                    </div>
                    <div class="h3 mb-1" x-text="formatCDD(avgCDD)">--</div>
                    <div class="small text-secondary">
                        Median: <span x-text="formatCDD(medianCDD)">--</span>
                    </div>
                </div>
            </div>

            <!-- Peak CDD -->
            <div class="col-md-3">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Peak CDD</span>
                        <span class="badge text-bg-danger">Max</span>
                    </div>
                    <div class="h3 mb-1 text-danger" x-text="formatCDD(maxCDD)">--</div>
                    <div class="small text-secondary" x-text="peakDate">--</div>
                </div>
            </div>

            <!-- Market Signal -->
            <div class="col-md-3">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Market Signal</span>
                        <span class="badge" :class="getSignalBadgeClass()" x-text="signalStrength">--</span>
                    </div>
                    <div class="h4 mb-1" :class="getSignalColorClass()" x-text="marketSignal">--</div>
                    <div class="small text-secondary" x-text="signalDescription">--</div>
                </div>
            </div>
        </div>

        <!-- Main Chart (TradingView Style) -->
        <div class="row g-3">
            <div class="col-12">
                <div class="tradingview-chart-container">
                    <div class="chart-header">
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="mb-0">Exchange Inflow CDD</h5>
                            <div class="chart-info">
                                <span class="current-value" x-text="formatCDD(currentCDD)">--</span>
                                <span class="change-badge" :class="cddChange >= 0 ? 'positive' : 'negative'" x-text="formatChange(cddChange)">--</span>
                            </div>
                        </div>
                        <div class="chart-controls">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn" :class="chartType === 'line' ? 'btn-primary' : 'btn-outline-secondary'" @click="chartType = 'line'; renderChart()">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                        <path d="M2 12l3-3 3 3 6-6"/>
                                    </svg>
                                </button>
                                <button type="button" class="btn" :class="chartType === 'bar' ? 'btn-primary' : 'btn-outline-secondary'" @click="chartType = 'bar'; renderChart()">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                        <rect x="2" y="6" width="3" height="8"/>
                                        <rect x="6" y="4" width="3" height="10"/>
                                        <rect x="10" y="8" width="3" height="6"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="chart-body">
                        <canvas id="cddMainChart"></canvas>
                    </div>
                    <div class="chart-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor" style="margin-right: 4px;">
                                    <circle cx="6" cy="6" r="5" fill="none" stroke="currentColor" stroke-width="1"/>
                                    <path d="M6 3v3l2 2" stroke="currentColor" stroke-width="1" fill="none"/>
                                </svg>
                                Higher CDD values indicate older coins moving to exchanges (potential distribution)
                            </small>
                            <small class="text-muted" x-data="{ source: 'Loading...' }" x-init="
                                fetch('/api/cryptoquant/exchange-inflow-cdd?start_date=' + new Date(Date.now() - 7*24*60*60*1000).toISOString().split('T')[0] + '&end_date=' + new Date().toISOString().split('T')[0])
                                    .then(r => r.json())
                                    .then(d => source = d.meta?.source || 'Unknown')
                                    .catch(() => source = 'Error')
                            ">
                                <span class="badge" :class="source.includes('CryptoQuant') ? 'text-bg-success' : 'text-bg-warning'" x-text="source">Loading...</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analysis Row -->
        <div class="row g-3">
            <!-- Distribution Analysis -->
            <div class="col-lg-6">
                <div class="df-panel p-3 h-100">
                    <h5 class="mb-3">📈 Distribution Analysis</h5>
                    <div style="height: 300px; position: relative;">
                        <canvas id="cddDistributionChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small text-secondary">High CDD Events (>2σ)</span>
                            <span class="badge text-bg-warning" x-text="highCDDEvents">0</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="small text-secondary">Extreme Events (>3σ)</span>
                            <span class="badge text-bg-danger" x-text="extremeCDDEvents">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Moving Averages -->
            <div class="col-lg-6">
                <div class="df-panel p-3 h-100">
                    <h5 class="mb-3">📉 Moving Averages</h5>
                    <div style="height: 300px; position: relative;">
                        <canvas id="cddMAChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small">7-Day MA:</span>
                            <span class="fw-bold" x-text="formatCDD(ma7)">--</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="small">30-Day MA:</span>
                            <span class="fw-bold" x-text="formatCDD(ma30)">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trading Interpretation -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4">
                    <h5 class="mb-3">📚 Understanding Exchange Inflow CDD</h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444;">
                                <div class="fw-bold mb-2 text-danger">🔴 High CDD (Distribution)</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Old coins (long-term holders) moving to exchanges</li>
                                        <li>Potential selling pressure incoming</li>
                                        <li>Often precedes price corrections</li>
                                        <li>Strategy: Watch for resistance, consider taking profits</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e;">
                                <div class="fw-bold mb-2 text-success">🟢 Low CDD (Accumulation)</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Young coins moving (normal trading activity)</li>
                                        <li>Long-term holders not distributing</li>
                                        <li>Healthy market conditions</li>
                                        <li>Strategy: Look for dip buying opportunities</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6;">
                                <div class="fw-bold mb-2 text-primary">⚡ CDD Spikes</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Sudden large movements of old coins</li>
                                        <li>Major holders repositioning</li>
                                        <li>High volatility expected</li>
                                        <li>Strategy: Wait for confirmation, manage risk carefully</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>💡 Pro Tip:</strong> Combine CDD analysis with price action and volume. High CDD during price rallies often signals distribution, while high CDD during crashes may indicate capitulation.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Chart.js with Date Adapter -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

    <!-- Wait for Chart.js to load -->
    <script>
        window.chartJsReady = new Promise((resolve) => {
            if (typeof Chart !== 'undefined') {
                console.log('✅ Chart.js loaded');
                resolve();
            } else {
                setTimeout(() => resolve(), 100);
            }
        });
    </script>

    <!-- Exchange Inflow CDD Controller -->
    <script src="{{ asset('js/exchange-inflow-cdd-controller.js') }}"></script>

    <style>
        /* Light Theme Chart Container */
        .tradingview-chart-container {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            background: rgba(59, 130, 246, 0.03);
        }

        .chart-header h5 {
            color: #1e293b;
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        .chart-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .current-value {
            color: #3b82f6;
            font-size: 20px;
            font-weight: 700;
            font-family: 'Courier New', monospace;
        }

        .change-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Courier New', monospace;
        }

        .change-badge.positive {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
        }

        .change-badge.negative {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }

        .chart-controls .btn-group {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 6px;
            padding: 2px;
        }

        .chart-controls .btn {
            border: none;
            padding: 6px 12px;
            color: #94a3b8;
            background: transparent;
            transition: all 0.2s;
        }

        .chart-controls .btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }

        .chart-controls .btn-primary {
            background: #3b82f6;
            color: #fff;
        }

        .chart-body {
            padding: 20px;
            height: 500px;
            position: relative;
            background: #ffffff;
        }

        .chart-footer {
            padding: 12px 20px;
            border-top: 1px solid rgba(0, 0, 0, 0.08);
            background: rgba(59, 130, 246, 0.02);
        }

        .chart-footer small {
            color: #64748b;
            display: flex;
            align-items: center;
        }

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

        /* Enhanced Summary Cards */
        .df-panel {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
            border: 1px solid rgba(59, 130, 246, 0.1);
            transition: all 0.3s ease;
        }

        .df-panel:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.15);
            border-color: rgba(59, 130, 246, 0.3);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .derivatives-header h1 {
                font-size: 1.5rem;
            }
            
            .chart-body {
                height: 350px;
                padding: 12px;
            }
            
            .chart-header {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }
            
            .current-value {
                font-size: 16px;
            }
        }
    </style>
@endsection
