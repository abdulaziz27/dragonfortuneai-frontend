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
                <div class="d-flex gap-2 align-items-center">
                    <select class="form-select" style="width: 120px;" x-model="globalSymbol" @change="updateSymbol()">
                        <option value="BTC">Bitcoin</option>
                        <option value="ETH">Ethereum</option>
                        <option value="SOL">Solana</option>
                        <option value="BNB">BNB</option>
                        <option value="XRP">XRP</option>
                    </select>

                    <select class="form-select" style="width: 140px;" x-model="globalMarginType">
                        <option value="">All Margin Types</option>
                        <option value="stablecoin">Stablecoin</option>
                        <option value="token">Token</option>
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
            <!-- Aggregate Chart -->
            <div class="col-lg-8">
                @include('components.funding.aggregate-chart', ['symbol' => 'BTC', 'rangeStr' => '7d'])
            </div>

            <!-- Quick Stats Panel -->
            <div class="col-lg-4">
                <div class="df-panel p-3 h-100">
                    <h5 class="mb-3">📈 Quick Stats</h5>

                    <div class="d-flex flex-column gap-3">
                        <!-- Funding Trend -->
                        <div class="stat-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-secondary">Funding Trend</span>
                                <span class="badge text-bg-success">Bullish</span>
                            </div>
                            <div class="h4 mb-0 text-success">+0.0125%</div>
                            <div class="small text-secondary">Avg across exchanges</div>
                        </div>

                        <hr class="my-2">

                        <!-- Market Sentiment -->
                        <div class="stat-item">
                            <div class="small text-secondary mb-2">Market Sentiment</div>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100">
                                    <span class="fw-semibold">Long 65%</span>
                                </div>
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 35%" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100">
                                    <span class="fw-semibold">Short 35%</span>
                                </div>
                            </div>
                            <div class="small text-secondary mt-1">Based on positive vs negative rates</div>
                        </div>

                        <hr class="my-2">

                        <!-- Next Funding -->
                        <div class="stat-item">
                            <div class="small text-secondary mb-2">Next Major Funding</div>
                            <div class="h3 mb-0 fw-bold">2h 15m</div>
                            <div class="small text-secondary">14:00 UTC • Most exchanges</div>
                        </div>

                        <hr class="my-2">

                        <!-- Trading Insight -->
                        <div class="alert alert-info mb-0">
                            <div class="fw-semibold small mb-1">💡 Trading Insight</div>
                            <div class="small">
                                Positive funding indicates long dominance. Watch for potential long squeeze if price fails to break resistance.
                            </div>
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
                @include('components.funding.history-chart', ['symbol' => 'BTC', 'interval' => '4h'])
            </div>

            <!-- Weighted Chart -->
            <div class="col-lg-6">
                @include('components.funding.weighted-chart', ['symbol' => 'BTC', 'interval' => '4h'])
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
