@extends('layouts.app')

@section('content')
    {{--
        Liquidations Dashboard
        Think like a trader • Build like an engineer • Visualize like a designer

        Trading Interpretasi:
        - Liquidations = forced market orders = volatility catalysts
        - Cascade events = chain reactions = extreme price movements
        - Long liquidations = sell pressure = bearish momentum
        - Short liquidations = buy pressure = bullish momentum
        - Cluster detection = potential stop-hunt zones
    --}}

    <div class="d-flex flex-column h-100 gap-3" x-data="liquidationsController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">💥 Liquidations Analytics</h1>
                        <span class="pulse-dot pulse-danger"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Track forced liquidations, cascade events, and market volatility triggers across exchanges
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
                        <option value="">All Exchanges</option>
                        <option value="Binance">Binance</option>
                        <option value="Bybit">Bybit</option>
                        <option value="OKX">OKX</option>
                        <option value="Bitget">Bitget</option>
                        <option value="Hyperliquid">Hyperliquid</option>
                    </select>

                    <select class="form-select" style="width: 120px;" x-model="globalInterval" @change="updateInterval()">
                        <option value="1m">1 Minute</option>
                        <option value="5m">5 Minutes</option>
                        <option value="15m">15 Minutes</option>
                        <option value="1h">1 Hour</option>
                        <option value="4h">4 Hours</option>
                    </select>

                    <button class="btn btn-primary" @click="refreshAll()" :disabled="globalLoading">
                        <span x-show="!globalLoading">🔄 Refresh All</span>
                        <span x-show="globalLoading" class="spinner-border spinner-border-sm"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Top Row: Analytics Summary (Full Width) -->
        <div class="row g-3">
            <div class="col-12">
                @include('components.liquidations.analytics-summary')
            </div>
        </div>

        <!-- Second Row: Historical Chart + Liquidation Stream -->
        <div class="row g-3">
            <!-- Historical Chart -->
            <div class="col-lg-8">
                @include('components.liquidations.historical-chart')
            </div>

            <!-- Live Liquidation Stream -->
            <div class="col-lg-4">
                @include('components.liquidations.liquidation-stream')
            </div>
        </div>

        <!-- Third Row: Heatmap + Exchange Comparison -->
        <div class="row g-3">
            <!-- Liquidation Heatmap -->
            <div class="col-lg-7">
                @include('components.liquidations.heatmap-chart')
            </div>

            <!-- Exchange Comparison -->
            <div class="col-lg-5">
                @include('components.liquidations.exchange-comparison')
            </div>
        </div>

        <!-- Fourth Row: Coin List Table (Full Width) -->
        <div class="row g-3">
            <div class="col-12">
                @include('components.liquidations.coin-list-table')
            </div>
        </div>

        <!-- Trading Notes & Interpretation -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4">
                    <h5 class="mb-3">📚 Understanding Liquidations</h5>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444;">
                                <div class="fw-bold mb-2 text-danger">🔴 Long Liquidations</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Forced selling from long positions</li>
                                        <li>Creates immediate sell pressure</li>
                                        <li>Often happens during sharp drops</li>
                                        <li>Can trigger cascade of further liquidations</li>
                                        <li>Strategy: Watch for oversold bounces</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="p-3 rounded" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e;">
                                <div class="fw-bold mb-2 text-success">🟢 Short Liquidations</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Forced buying from short positions</li>
                                        <li>Creates immediate buy pressure</li>
                                        <li>Often happens during sharp rallies</li>
                                        <li>Can fuel "short squeeze" momentum</li>
                                        <li>Strategy: Ride momentum but watch for exhaustion</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="p-3 rounded" style="background: rgba(245, 158, 11, 0.1); border-left: 4px solid #f59e0b;">
                                <div class="fw-bold mb-2 text-warning">⚡ Cascade Events</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Chain reaction of liquidations</li>
                                        <li>Price triggers one liquidation, causing next</li>
                                        <li>Creates extreme volatility</li>
                                        <li>Often clears leverage from system</li>
                                        <li>Strategy: Wait for calm, then enter</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6;">
                                <div class="fw-bold mb-2 text-primary">🎯 Stop Hunt Zones</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Price levels with liquidation clusters</li>
                                        <li>Market makers may target these zones</li>
                                        <li>Creates "wick" patterns on charts</li>
                                        <li>Often reverses after liquidations clear</li>
                                        <li>Strategy: Place stops beyond clusters</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Key Insights Section -->
                    <div class="mt-4 p-3 rounded" style="background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.3);">
                        <div class="fw-bold mb-2 text-primary">💡 Key Trading Insights</div>
                        <div class="row g-3 small">
                            <div class="col-md-4">
                                <strong>Volume Matters:</strong> Large liquidations (>$1M) have more market impact than many small ones.
                            </div>
                            <div class="col-md-4">
                                <strong>Exchange Context:</strong> Binance liquidations often have broader market implications due to volume.
                            </div>
                            <div class="col-md-4">
                                <strong>Timing is Key:</strong> Liquidations during low liquidity hours can cause larger price swings.
                            </div>
                        </div>
                    </div>

                    <!-- Risk Warning -->
                    <div class="mt-3 alert alert-warning mb-0">
                        <div class="d-flex align-items-start gap-2">
                            <span style="font-size: 1.5rem;">⚠️</span>
                            <div>
                                <div class="fw-bold mb-1">High Risk Warning</div>
                                <div class="small">
                                    Liquidation events represent extreme volatility and risk. Always use proper risk management,
                                    set appropriate stop losses, and never over-leverage your positions. Cascade events can cause
                                    rapid and unpredictable price movements.
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
        window.chartJsReady = new Promise((resolve) => {
            if (typeof Chart !== 'undefined') {
                resolve();
            } else {
                setTimeout(() => resolve(), 100);
            }
        });

        // Fix Chart.js context issues
        Chart.register({
            id: 'clipArea',
            beforeDraw: (chart) => {
                const { ctx, chartArea } = chart;
                if (!chartArea) return;

                ctx.save();
                ctx.beginPath();
                ctx.rect(chartArea.left, chartArea.top, chartArea.right - chartArea.left, chartArea.bottom - chartArea.top);
                ctx.clip();
            },
            afterDraw: (chart) => {
                chart.ctx.restore();
            }
        });
    </script>

    <!-- Load liquidations controller BEFORE Alpine processes x-data -->
    <script src="{{ asset('js/liquidations-controller.js') }}"></script>

    <style>
        /* Pulse animation for live indicator */
        .pulse-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }

        .pulse-danger {
            background-color: #ef4444;
            box-shadow: 0 0 0 rgba(239, 68, 68, 0.7);
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }
            50% {
                box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
            }
        }

        /* Smooth transitions */
        .df-panel {
            transition: all 0.3s ease;
        }

        .df-panel:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(148, 163, 184, 0.1);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.3);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.5);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .derivatives-header h1 {
                font-size: 1.5rem;
            }

            .form-select {
                font-size: 0.875rem;
            }
        }

        /* Loading overlay */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            border-radius: 0.5rem;
        }

        /* Badge animations */
        .badge {
            transition: all 0.2s ease;
        }

        .badge:hover {
            transform: scale(1.05);
        }

        /* Table enhancements */
        .table-hover tbody tr {
            transition: background-color 0.2s ease;
        }

        /* Chart container improvements */
        canvas {
            max-width: 100%;
            height: auto !important;
        }

        /* Alert improvements */
        .alert {
            border-left-width: 4px;
            border-left-style: solid;
        }

        /* Stat card hover effects */
        .stat-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }
    </style>
@endsection
