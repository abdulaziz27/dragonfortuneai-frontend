@extends('layouts.app')

@section('content')
    {{--
        Perp–Quarterly Spread Analytics Dashboard
        Think like a trader • Build like an engineer • Visualize like a designer

        Interpretasi Trading:
        - Spread positif → Perp > Quarterly → Contango structure → Market expects higher prices
        - Spread negatif → Quarterly > Perp → Backwardation structure → Supply shortage or high demand
        - Spread widening → Increasing contango/backwardation
        - Spread narrowing → Convergence approaching (normal menjelang expiry)

        API Endpoints Used:
        1. /api/perp-quarterly/analytics - Spread analysis, trend, insights
        2. /api/perp-quarterly/history - Historical spread timeseries
    --}}

    <div class="d-flex flex-column h-100 gap-3" x-data="perpQuarterlySpreadController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">📊 Perp–Quarterly Spread</h1>
                        <span class="pulse-dot pulse-success"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Monitor perpetual to quarterly futures spread dynamics, convergence patterns, and arbitrage opportunities
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

                    <select class="form-select" style="width: 140px;" x-model="globalExchange" @change="updateExchange()">
                        <option value="Binance">Binance</option>
                        <option value="Bybit">Bybit</option>
                        <option value="OKX">OKX</option>
                        <option value="Bitget">Bitget</option>
                        <option value="Gate.io">Gate.io</option>
                        <option value="Deribit">Deribit</option>
                    </select>

                    <select class="form-select" style="width: 120px;" x-model="globalInterval" @change="updateInterval()">
                        <option value="5m">5 Minutes</option>
                        <option value="15m">15 Minutes</option>
                        <option value="1h">1 Hour</option>
                        <option value="4h">4 Hours</option>
                        <option value="1d">1 Day</option>
                    </select>

                    <button class="btn btn-primary" @click="refreshAll()" :disabled="globalLoading">
                        <span x-show="!globalLoading">🔄 Refresh All</span>
                        <span x-show="globalLoading" class="spinner-border spinner-border-sm"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Analytics Card (Full Width) -->
        <div class="row g-3">
            <div class="col-12">
                @include('components.perp-quarterly.analytics-card', ['symbol' => 'BTC', 'exchange' => 'Binance'])
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-3">
            <!-- Spread History Chart -->
            <div class="col-lg-8">
                @include('components.perp-quarterly.spread-history-chart', ['symbol' => 'BTC', 'exchange' => 'Binance', 'height' => '400px'])
            </div>

            <!-- Trading Insights Panel -->
            <div class="col-lg-4">
                @include('components.perp-quarterly.insights-panel', ['symbol' => 'BTC', 'exchange' => 'Binance'])
            </div>
        </div>

        <!-- Spread Data Table (Full Width) -->
        <div class="row g-3">
            <div class="col-12">
                @include('components.perp-quarterly.spread-table', ['symbol' => 'BTC', 'exchange' => 'Binance', 'limit' => 20])
            </div>
        </div>

        <!-- Trading Notes & Interpretation -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4">
                    <h5 class="mb-3">📚 Understanding Perp–Quarterly Spread</h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e;">
                                <div class="fw-bold mb-2 text-success">🟩 Contango (Positive Spread)</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Perpetual > Quarterly price</li>
                                        <li>Market expects higher future prices</li>
                                        <li>Normal in bull markets</li>
                                        <li>Higher funding cost for perp longs</li>
                                        <li><strong>Strategy:</strong> Short perpetual / Long quarterly for convergence</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444;">
                                <div class="fw-bold mb-2 text-danger">🟥 Backwardation (Negative Spread)</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Quarterly > Perpetual price</li>
                                        <li>Supply shortage or high spot demand</li>
                                        <li>Unusual in crypto (often arbitrage opportunity)</li>
                                        <li>Negative funding on perp</li>
                                        <li><strong>Strategy:</strong> Long perpetual / Short quarterly or wait for normalization</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6;">
                                <div class="fw-bold mb-2 text-primary">⚡ Convergence & Arbitrage</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Spread narrows as expiry approaches</li>
                                        <li>At expiry, both contracts converge to spot</li>
                                        <li>Wide spreads = arbitrage opportunities</li>
                                        <li>Consider execution costs and slippage</li>
                                        <li><strong>Strategy:</strong> Calendar spreads for basis trading</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h6 class="mb-2">💡 Key Trading Considerations</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="small">
                                    <div class="fw-semibold mb-1">✅ When to Trade Spread</div>
                                    <ul class="mb-0">
                                        <li>Spread > 50 bps: Strong arbitrage potential</li>
                                        <li>Widening spread: Enter calendar spread</li>
                                        <li>High volatility: Wider spreads, more opportunities</li>
                                        <li>Near expiry: Guaranteed convergence</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="small">
                                    <div class="fw-semibold mb-1">⚠️ Risks to Monitor</div>
                                    <ul class="mb-0">
                                        <li>Execution risk: Slippage and fees</li>
                                        <li>Funding rate changes: Can accelerate or reverse spread</li>
                                        <li>Liquidity: Ensure sufficient depth for both contracts</li>
                                        <li>Expiry mechanics: Settlement and rollover costs</li>
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

    <!-- Load perp-quarterly controller BEFORE Alpine processes x-data -->
    <script src="{{ asset('js/perp-quarterly-controller.js') }}"></script>

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

        /* Card hover effects */
        .df-panel {
            transition: all 0.2s ease;
        }

        .df-panel:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .derivatives-header h1 {
                font-size: 1.5rem;
            }

            .form-select {
                width: 100% !important;
            }
        }

        /* Code styling */
        code {
            background: rgba(var(--bs-dark-rgb), 0.05);
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.875em;
            color: #e83e8c;
        }

        /* List styling */
        ul {
            line-height: 1.6;
        }

        /* Badge styling */
        .badge {
            font-weight: 500;
            letter-spacing: 0.3px;
        }
    </style>
@endsection
