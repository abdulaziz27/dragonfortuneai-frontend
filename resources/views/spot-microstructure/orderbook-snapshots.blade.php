@extends('layouts.app')

@section('content')
    {{--
        Orderbook Snapshots - Market Microstructure Analysis
        Think like a trader • Build like an engineer • Visualize like a designer

        Interpretasi Trading:
        - Bid pressure > Ask pressure → Bullish momentum → Potensi kenaikan harga
        - Ask pressure > Bid pressure → Bearish momentum → Potensi penurunan harga
        - High depth score → Market stabil dengan likuiditas tinggi
        - Imbalance tinggi → Ketidakseimbangan pasar → Potensi pergerakan cepat
        - Liquidity walls → Support/Resistance kuat di level tertentu
    --}}

    <div class="d-flex flex-column h-100 gap-3" x-data="orderbookController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">📊 Orderbook Snapshots - Market Microstructure</h1>
                        <span class="pulse-dot pulse-success"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Real-time orderbook analysis: pressure, depth, liquidity distribution, dan imbalance detection
                    </p>
                </div>

                <!-- Global Controls -->
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <select class="form-select" style="width: 140px;" x-model="globalSymbol" @change="updateSymbol()">
                        <option value="BTCUSDT">BTC/USDT</option>
                        <option value="ETHUSDT">ETH/USDT</option>
                        <option value="SOLUSDT">SOL/USDT</option>
                        <option value="BNBUSDT">BNB/USDT</option>
                        <option value="XRPUSDT">XRP/USDT</option>
                        <option value="ADAUSDT">ADA/USDT</option>
                    </select>

                    <select class="form-select" style="width: 130px;" x-model="globalExchange" @change="updateExchange()">
                        <option value="binance">Binance</option>
                        <option value="okx">OKX</option>
                        <option value="bybit">Bybit</option>
                        <option value="bitget">Bitget</option>
                    </select>

                    <button class="btn btn-primary" @click="refreshAll()" :disabled="globalLoading">
                        <span x-show="!globalLoading">🔄 Refresh All</span>
                        <span x-show="globalLoading" class="spinner-border spinner-border-sm"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Book Pressure Card (Full Width) -->
        <div class="row g-3">
            <div class="col-12">
                @include('components.orderbook.pressure-card')
            </div>
        </div>

        <!-- Liquidity Metrics Row -->
        <div class="row g-3">
            <!-- Liquidity Imbalance Card -->
            <div class="col-lg-4">
                @include('components.orderbook.liquidity-imbalance')
            </div>

            <!-- Market Depth Stats -->
            <div class="col-lg-4">
                @include('components.orderbook.market-depth-stats')
            </div>

            <!-- Quick Stats -->
            <div class="col-lg-4">
                @include('components.orderbook.quick-stats')
            </div>
        </div>

        <!-- Live Orderbook Snapshot -->
        <div class="row g-3">
            <div class="col-12">
                @include('components.orderbook.live-snapshot')
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-3">
            <!-- Book Pressure History Chart -->
            <div class="col-lg-6">
                @include('components.orderbook.pressure-chart')
            </div>

            <!-- Liquidity Heatmap Chart -->
            <div class="col-lg-6">
                @include('components.orderbook.liquidity-heatmap-chart')
            </div>
        </div>

        <!-- Market Depth & Orderbook Depth -->
        <div class="row g-3">
            <!-- Market Depth Table -->
            <div class="col-lg-6">
                @include('components.orderbook.market-depth-table')
            </div>

            <!-- Orderbook Depth Table -->
            <div class="col-lg-6">
                @include('components.orderbook.orderbook-depth-table')
            </div>
        </div>

        <!-- Trading Insights -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4">
                    <h5 class="mb-3">📚 Understanding Orderbook Microstructure</h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e;">
                                <div class="fw-bold mb-2 text-success">🟩 Bullish Signals</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Bid pressure > Ask pressure (ratio > 1)</li>
                                        <li>Positive liquidity imbalance</li>
                                        <li>High bid depth at key levels</li>
                                        <li>Pressure direction: "bullish"</li>
                                        <li>Strong bid liquidity walls</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444;">
                                <div class="fw-bold mb-2 text-danger">🟥 Bearish Signals</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Ask pressure > Bid pressure (ratio < 1)</li>
                                        <li>Negative liquidity imbalance</li>
                                        <li>High ask depth at resistance</li>
                                        <li>Pressure direction: "bearish"</li>
                                        <li>Strong ask liquidity walls</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6;">
                                <div class="fw-bold mb-2 text-primary">⚡ Key Concepts</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li><strong>Book Pressure:</strong> Rasio kekuatan bid vs ask</li>
                                        <li><strong>Depth Score:</strong> Ukuran stabilitas & keseimbangan market</li>
                                        <li><strong>Imbalance:</strong> Ketidakseimbangan likuiditas bid/ask</li>
                                        <li><strong>Liquidity Walls:</strong> Level dengan volume besar</li>
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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

    <!-- Orderbook Controller -->
    <script src="{{ asset('js/orderbook-controller.js') }}"></script>

    <style>
        /* Pulse animation */
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

        /* Sticky table header */
        .sticky-top {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        /* Orderbook visualization */
        .orderbook-row {
            position: relative;
            padding: 0.25rem 0.5rem;
            transition: background-color 0.2s;
        }

        .orderbook-row:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .orderbook-bg {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            opacity: 0.2;
            transition: width 0.3s ease;
        }

        .orderbook-bg-bid {
            background: linear-gradient(to left, #22c55e, transparent);
        }

        .orderbook-bg-ask {
            background: linear-gradient(to left, #ef4444, transparent);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .derivatives-header h1 {
                font-size: 1.5rem;
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
    </style>
@endsection
