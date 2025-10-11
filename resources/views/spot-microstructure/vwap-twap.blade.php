@extends('layouts.app')

@section('content')
    {{--
        VWAP/TWAP Analysis Dashboard
        Think like a trader • Build like an engineer • Visualize like a designer

        Interpretasi Trading:
        - VWAP = Volume-Weighted Average Price → Benchmark harga wajar berdasarkan volume
        - Price > VWAP → Bullish bias, buyers in control, dip-buying opportunities
        - Price < VWAP → Bearish bias, sellers dominant, bounce-selling opportunities
        - Upper/Lower Bands → Volatility envelope, breakout/reversion signals
        - Band width → Market volatility indicator
    --}}

    <div class="d-flex flex-column h-100 gap-3" x-data="vwapController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">📊 VWAP/TWAP Analysis</h1>
                        <span class="pulse-dot pulse-success"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Volume-Weighted Average Price analysis for institutional-grade trading insights
                    </p>
                </div>

                <!-- Global Controls -->
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <select class="form-select" style="width: 150px;" x-model="globalSymbol" @change="updateSymbol()">
                        <option value="BTCUSDT">Bitcoin (BTC)</option>
                        <option value="ETHUSDT">Ethereum (ETH)</option>
                        <option value="SOLUSDT">Solana (SOL)</option>
                        <option value="BNBUSDT">BNB</option>
                        <option value="XRPUSDT">XRP</option>
                        <option value="ADAUSDT">Cardano (ADA)</option>
                        <option value="DOGEUSDT">Dogecoin (DOGE)</option>
                        <option value="MATICUSDT">Polygon (MATIC)</option>
                        <option value="DOTUSDT">Polkadot (DOT)</option>
                        <option value="AVAXUSDT">Avalanche (AVAX)</option>
                    </select>

                    <select class="form-select" style="width: 130px;" x-model="globalTimeframe" @change="updateTimeframe()">
                        <option value="1min">1 Minute</option>
                        <option value="5min" selected>5 Minutes</option>
                        <option value="15min">15 Minutes</option>
                        <option value="30min">30 Minutes</option>
                        <option value="1h">1 Hour</option>
                        <option value="4h">4 Hours</option>
                    </select>

                    <select class="form-select" style="width: 130px;" x-model="globalExchange" @change="updateExchange()">
                        <option value="binance" selected>Binance</option>
                        <option value="bybit">Bybit</option>
                        <option value="okx">OKX</option>
                        <option value="bitget">Bitget</option>
                    </select>

                    <button class="btn btn-primary" @click="refreshAll()" :disabled="globalLoading">
                        <span x-show="!globalLoading">🔄 Refresh All</span>
                        <span x-show="globalLoading" class="spinner-border spinner-border-sm"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="row g-3">
            <!-- Latest Stats Card -->
            <div class="col-lg-4">
                @include('components.vwap.latest-stats', [
                    'symbol' => 'BTCUSDT',
                    'timeframe' => '5min',
                    'exchange' => 'binance'
                ])
            </div>

            <!-- Market Insights -->
            <div class="col-lg-8">
                @include('components.vwap.market-insights', [
                    'symbol' => 'BTCUSDT',
                    'timeframe' => '5min',
                    'exchange' => 'binance'
                ])
            </div>
        </div>

        <!-- VWAP Bands Chart Row -->
        <div class="row g-3">
            <div class="col-12">
                @include('components.vwap.bands-chart', [
                    'symbol' => 'BTCUSDT',
                    'timeframe' => '5min',
                    'exchange' => 'binance',
                    'limit' => 200
                ])
            </div>
        </div>

        <!-- Historical Data Table -->
        <div class="row g-3">
            <div class="col-12">
                @include('components.vwap.history-table', [
                    'symbol' => 'BTCUSDT',
                    'timeframe' => '5min',
                    'exchange' => 'binance',
                    'limit' => 100
                ])
            </div>
        </div>

        <!-- Trading Notes & Interpretation -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4">
                    <h5 class="mb-3">📚 Understanding VWAP Trading</h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(16, 185, 129, 0.1); border-left: 4px solid #10b981;">
                                <div class="fw-bold mb-2 text-success">🟢 What is VWAP?</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li><strong>Volume-Weighted Average Price</strong> - harga rata-rata tertimbang berdasarkan volume</li>
                                        <li>Digunakan oleh trader institusional sebagai benchmark eksekusi order</li>
                                        <li>Menunjukkan "harga wajar" berdasarkan aktivitas trading real</li>
                                        <li>Reset setiap hari pada session baru (00:00 UTC)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6;">
                                <div class="fw-bold mb-2 text-primary">📈 Trading with VWAP</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li><strong>Price > VWAP:</strong> Bullish bias - look for dip-buying opportunities back to VWAP</li>
                                        <li><strong>Price < VWAP:</strong> Bearish bias - look for bounce-selling at VWAP resistance</li>
                                        <li><strong>VWAP as support/resistance:</strong> Dynamic level that changes with volume</li>
                                        <li><strong>Mean reversion:</strong> Price tends to revert back to VWAP after extremes</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444;">
                                <div class="fw-bold mb-2 text-danger">⚠️ VWAP Bands</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li><strong>Upper Band:</strong> Resistance level - potential overbought, watch for rejection or breakout</li>
                                        <li><strong>Lower Band:</strong> Support level - potential oversold, watch for bounce or breakdown</li>
                                        <li><strong>Band Width:</strong> Volatility indicator - wide bands = high volatility</li>
                                        <li><strong>Strategy:</strong> Range trade between bands or trade breakouts beyond bands</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light">
                                <div class="fw-bold mb-2">🎯 Institutional Use Cases</div>
                                <div class="small text-secondary">
                                    <ol class="mb-0 ps-3">
                                        <li><strong>Execution Benchmarking:</strong> Instituional traders aim to execute large orders at prices better than VWAP</li>
                                        <li><strong>Algorithmic Trading:</strong> Many algos are programmed to buy below VWAP and sell above VWAP</li>
                                        <li><strong>Fair Value Assessment:</strong> VWAP represents the "true" market price during the session</li>
                                        <li><strong>Performance Evaluation:</strong> Traders are judged on whether they beat VWAP or not</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light">
                                <div class="fw-bold mb-2">💡 Trading Strategies</div>
                                <div class="small text-secondary">
                                    <ol class="mb-0 ps-3">
                                        <li><strong>VWAP Pullback:</strong> When price is above VWAP, buy pullbacks to VWAP support</li>
                                        <li><strong>VWAP Rejection:</strong> When price is below VWAP, short bounces to VWAP resistance</li>
                                        <li><strong>Band Bounce:</strong> Buy at lower band, sell at upper band (range trading)</li>
                                        <li><strong>Band Breakout:</strong> Trade continuation when price breaks and holds above/below bands</li>
                                        <li><strong>Morning Range:</strong> VWAP from market open can define day's trading range</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Educational Panel -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4">
                    <h5 class="mb-3">🎓 VWAP vs TWAP: Key Differences</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 20%;">Aspect</th>
                                    <th style="width: 40%;">VWAP (Volume-Weighted Average Price)</th>
                                    <th style="width: 40%;">TWAP (Time-Weighted Average Price)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-semibold">Calculation</td>
                                    <td>Weighted by volume - higher volume periods have more influence</td>
                                    <td>Weighted by time - all time periods have equal influence regardless of volume</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Best For</td>
                                    <td>Understanding where the market is trading based on actual activity</td>
                                    <td>Smoothing out price movements over time, less influenced by volume spikes</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Use Case</td>
                                    <td>Institutional execution benchmark, fair value assessment</td>
                                    <td>Algorithmic order execution strategy to minimize market impact</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Market Signal</td>
                                    <td>More reactive to high-volume moves (captures market sentiment)</td>
                                    <td>More stable, less reactive to volume spikes (better for trend following)</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Trading Strategy</td>
                                    <td>Mean reversion, support/resistance, band trading</td>
                                    <td>Smoothed trend following, reducing noise from volume anomalies</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <div class="d-flex align-items-start gap-2">
                            <div style="font-size: 1.5rem;">💡</div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold mb-1">Pro Tip</div>
                                <div class="small">
                                    In this dashboard, we focus on <strong>VWAP</strong> because it's the most widely used by institutional traders and provides the best
                                    representation of market-driven fair value. VWAP bands give us volatility context, while TWAP can be derived by simply averaging prices
                                    over time periods. Use VWAP as your primary reference for support/resistance levels and execution benchmarking.
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

    <!-- Load VWAP controller BEFORE Alpine processes x-data -->
    <script src="{{ asset('js/vwap-controller.js') }}"></script>

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

        /* Panel styling */
        .df-panel {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .df-panel:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .derivatives-header h1 {
                font-size: 1.5rem;
            }
        }

        /* Table responsive styling */
        .table-responsive {
            border-radius: 8px;
        }
    </style>
@endsection
