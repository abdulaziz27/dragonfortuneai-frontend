@extends('layouts.app')

@section('content')
    {{--
        Spot Microstructure - Trades Analysis (CVD & Buy/Sell Ratio)
        Think like a trader • Build like an engineer • Visualize like a designer

        Interpretasi Trading:
        - CVD positif → Dominasi buyer → Potensi bullish momentum
        - CVD negatif → Dominasi seller → Potensi bearish pressure
        - Buyer ratio > 60% → Strong buying pressure → Bullish signal
        - Seller ratio > 60% → Strong selling pressure → Bearish signal
        - Net flow positif → Accumulation phase
        - Trade bias "buy" → Market sentiment bullish
    --}}

    <div class="d-flex flex-column h-100 gap-3" x-data="tradesController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">💹 Trades Analysis - CVD & Buy/Sell Ratio</h1>
                        <span class="pulse-dot pulse-success"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Monitor Cumulative Volume Delta, buyer-seller pressure, dan trade flow untuk deteksi momentum
                    </p>
                </div>

                <!-- Global Controls -->
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <!-- Symbol Filter -->
                    <select class="form-select" style="width: 140px;" x-model="selectedSymbol" @change="handleFilterChange()">
                        <option value="BTCUSDT">BTC/USDT</option>
                        <option value="ETHUSDT">ETH/USDT</option>
                        <option value="SOLUSDT">SOL/USDT</option>
                        <option value="BNBUSDT">BNB/USDT</option>
                        <option value="XRPUSDT">XRP/USDT</option>
                        <option value="ADAUSDT">ADA/USDT</option>
                        <option value="DOGEUSDT">DOGE/USDT</option>
                        <option value="MATICUSDT">MATIC/USDT</option>
                    </select>

                    <!-- Interval Filter -->
                    <select class="form-select" style="width: 130px;" x-model="selectedInterval" @change="handleFilterChange()">
                        <option value="1m">1 Minute</option>
                        <option value="5m">5 Minutes</option>
                        <option value="15m">15 Minutes</option>
                        <option value="1h">1 Hour</option>
                        <option value="4h">4 Hours</option>
                    </select>

                    <!-- Data Limit -->
                    <select class="form-select" style="width: 140px;" x-model="selectedLimit" @change="handleFilterChange()">
                        <option value="50">50 Records</option>
                        <option value="100">100 Records</option>
                        <option value="200">200 Records</option>
                        <option value="500">500 Records</option>
                        <option value="1000">1000 Records</option>
                    </select>

                    <!-- Manual Refresh Button -->
                    <button class="btn btn-primary" @click="refreshAll()" :disabled="loading">
                        <span x-show="!loading">Refresh All</span>
                        <span x-show="loading" class="spinner-border spinner-border-sm"></span>
                    </button>

                    <!-- Auto-refresh Toggle -->
                    <button class="btn" @click="toggleAutoRefresh()" 
                            :class="autoRefreshEnabled ? 'btn-success' : 'btn-outline-secondary'">
                        <span x-text="autoRefreshEnabled ? 'Auto-refresh: ON' : '⏸️ Auto-refresh: OFF'"></span>
                    </button>

                    <!-- Last Updated -->
                    <div class="d-flex align-items-center gap-1 text-muted small" x-show="lastUpdated">
                        <span>Last updated:</span>
                        <span x-text="lastUpdated" class="fw-bold"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trade Bias Card (Full Width) -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4" x-data="tradeBiasCard()" x-init="init()">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">🎯 Market Bias</h5>
                            <p class="small text-secondary mb-0">Trading sentiment based on buyer-seller ratio analysis</p>
                        </div>
                        <span class="badge" :class="getBiasClass()" x-text="bias.toUpperCase()">Loading...</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="small text-secondary mb-1">Avg Buyer Ratio</div>
                                <div class="h4 mb-0 text-success" x-text="formatPercent(avgBuyerRatio)">--</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="small text-secondary mb-1">Avg Seller Ratio</div>
                                <div class="h4 mb-0 text-danger" x-text="formatPercent(avgSellerRatio)">--</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="small text-secondary mb-1">Bias Strength</div>
                                <div class="h4 mb-0" x-text="formatPercent(strength)">--</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="small text-secondary mb-1">Sample Size</div>
                                <div class="h4 mb-0" x-text="sampleSize">--</div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress bar for buyer/seller ratio -->
                    <div class="mt-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-success">Buy Pressure</span>
                            <span class="text-danger">Sell Pressure</span>
                        </div>
                        <div class="progress" style="height: 24px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                 :style="`width: ${avgBuyerRatio * 100}%`"
                                 x-text="formatPercent(avgBuyerRatio)">
                            </div>
                            <div class="progress-bar bg-danger" role="progressbar"
                                 :style="`width: ${avgSellerRatio * 100}%`"
                                 x-text="formatPercent(avgSellerRatio)">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CVD & Volume Charts -->
        <div class="row g-3">
            <!-- CVD Table -->
            <div class="col-lg-8">
                <div class="df-panel p-3" x-data="cvdTable()" x-init="init()">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-0">📊 Cumulative Volume Delta (CVD)</h5>
                            <small class="text-secondary">Recent CVD data points</small>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <span x-show="loading" class="spinner-border spinner-border-sm text-primary"></span>
                        </div>
                    </div>

                    <!-- CVD Data Table -->
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-striped">
                            <thead class="sticky-top bg-white">
                                <tr>
                                    <th>Time</th>
                                    <th>Exchange</th>
                                    <th>Symbol</th>
                                    <th class="text-end">CVD Value</th>
                                    <th class="text-center">Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in cvdData" :key="'cvd-' + index + '-' + item.ts">
                                    <tr>
                                        <td x-text="formatTime(item.ts)">--</td>
                                        <td>
                                            <span class="badge bg-secondary" x-text="item.exchange">--</span>
                                        </td>
                                        <td x-text="item.symbol">--</td>
                                        <td class="text-end fw-bold" :class="getCvdClass(item.cvd)" x-text="formatCvd(item.cvd)">--</td>
                                        <td class="text-center">
                                            <span class="badge" :class="getTrendClass(item.cvd)" x-text="getTrendText(item.cvd)">--</span>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="!loading && cvdData.length === 0">
                                    <tr>
                                        <td colspan="5" class="text-center text-secondary py-4">No CVD data available</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary Stats -->
                    <div class="mt-3 pt-3 border-top">
                        <div class="row g-2 small">
                            <div class="col-3">
                                <div class="text-secondary">Data Points</div>
                                <div class="fw-bold" x-text="cvdData.length">--</div>
                            </div>
                            <div class="col-3">
                                <div class="text-secondary">Current CVD</div>
                                <div class="fw-bold" :class="getCvdClass(currentCvd)" x-text="formatCvd(currentCvd)">--</div>
                            </div>
                            <div class="col-3">
                                <div class="text-secondary">Avg CVD</div>
                                <div class="fw-bold" x-text="formatCvd(avgCvd)">--</div>
                            </div>
                            <div class="col-3">
                                <div class="text-secondary">CVD Range</div>
                                <div class="fw-bold" x-text="formatCvd(maxCvd - minCvd)">--</div>
                            </div>
                        </div>
                    </div>

                    <!-- No Data State -->
                    <div x-show="!loading && cvdData.length === 0" class="text-center py-4">
                        <div class="text-secondary mb-2" style="font-size: 3rem;">📊</div>
                        <div class="text-secondary">No CVD data available</div>
                        <div class="small text-muted mt-2">Try refreshing or changing the symbol</div>
                    </div>
                </div>
            </div>

            <!-- CVD Stats -->
            <div class="col-lg-4">
                <div class="df-panel p-3" x-data="cvdStats()" x-init="init()">
                    <h5 class="mb-3">📈 CVD Statistics</h5>

                    <div class="d-flex flex-column gap-3">
                        <div class="stat-item">
                            <div class="small text-secondary mb-1">Current CVD</div>
                            <div class="h4 mb-0" :class="currentCVD >= 0 ? 'text-success' : 'text-danger'" x-text="formatNumber(currentCVD)">--</div>
                        </div>

                        <div class="stat-item">
                            <div class="small text-secondary mb-1">CVD Change</div>
                            <div class="h5 mb-0" :class="cvdChange >= 0 ? 'text-success' : 'text-danger'" x-text="formatChange(cvdChange)">--</div>
                        </div>

                        <div class="stat-item">
                            <div class="small text-secondary mb-1">Max CVD (24h)</div>
                            <div class="h5 mb-0 text-success" x-text="formatNumber(maxCVD)">--</div>
                        </div>

                        <div class="stat-item">
                            <div class="small text-secondary mb-1">Min CVD (24h)</div>
                            <div class="h5 mb-0 text-danger" x-text="formatNumber(minCVD)">--</div>
                        </div>

                        <div class="stat-item">
                            <div class="small text-secondary mb-1">CVD Trend</div>
                            <div class="badge" :class="currentCVD >= 0 ? 'bg-success' : 'bg-danger'" x-text="currentCVD >= 0 ? 'Bullish' : 'Bearish'">--</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trade Flow & Summary -->
        <div class="row g-3">
            <!-- Trade Summary Table -->
            <div class="col-lg-8">
                <div class="df-panel p-3" x-data="tradeSummaryTable()" x-init="init()">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">📋 Trade Summary (Bucketed)</h5>
                        <span class="badge bg-secondary" x-show="loading">Loading...</span>
                    </div>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-hover">
                            <thead class="sticky-top bg-dark">
                                <tr>
                                    <th>Time</th>
                                    <th>Avg Price</th>
                                    <th>Buy Vol</th>
                                    <th>Sell Vol</th>
                                    <th>Net Flow</th>
                                    <th>Trades</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="trade in trades" :key="trade.ts_ms">
                                    <tr>
                                        <td class="small" x-text="formatTime(trade.bucket_time)"></td>
                                        <td class="small" x-text="formatPrice(trade.avg_price)"></td>
                                        <td class="small text-success" x-text="formatVolume(trade.buy_volume_quote)"></td>
                                        <td class="small text-danger" x-text="formatVolume(trade.sell_volume_quote)"></td>
                                        <td class="small" :class="trade.net_flow_quote >= 0 ? 'text-success' : 'text-danger'" x-text="formatVolume(trade.net_flow_quote)"></td>
                                        <td class="small" x-text="trade.trades_count"></td>
                                    </tr>
                                </template>
                                <template x-if="!loading && trades.length === 0">
                                    <tr>
                                        <td colspan="6" class="text-center text-secondary py-4">No trade summary data available</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Volume Flow Stats -->
            <div class="col-lg-4">
                <div class="df-panel p-3" x-data="volumeFlowStats()" x-init="init()">
                    <h5 class="mb-3">💰 Volume Flow</h5>

                    <div class="d-flex flex-column gap-3">
                        <div class="stat-item">
                            <div class="small text-secondary mb-1">Total Buy Volume</div>
                            <div class="h5 mb-0 text-success" x-text="formatVolume(totalBuyVolume)">--</div>
                        </div>

                        <div class="stat-item">
                            <div class="small text-secondary mb-1">Total Sell Volume</div>
                            <div class="h5 mb-0 text-danger" x-text="formatVolume(totalSellVolume)">--</div>
                        </div>

                        <div class="stat-item">
                            <div class="small text-secondary mb-1">Net Flow</div>
                            <div class="h4 mb-0" :class="netFlow >= 0 ? 'text-success' : 'text-danger'" x-text="formatVolume(netFlow)">--</div>
                        </div>

                        <div class="stat-item">
                            <div class="small text-secondary mb-1">Total Trades</div>
                            <div class="h5 mb-0" x-text="totalTrades.toLocaleString()">--</div>
                        </div>

                        <div class="stat-item">
                            <div class="small text-secondary mb-1">Avg Trade Size</div>
                            <div class="h5 mb-0" x-text="formatVolume(avgTradeSize)">--</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Trades Stream -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-3" x-data="recentTradesStream()" x-init="init()">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">🔴 Recent Trades (Live Stream)</h5>
                        <div class="d-flex gap-2 align-items-center">
                            <span x-show="loading" class="spinner-border spinner-border-sm text-primary"></span>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-hover">
                            <thead class="sticky-top bg-dark">
                                <tr>
                                    <th>Time</th>
                                    <th>Exchange</th>
                                    <th>Side</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Quote Vol</th>
                                    <th>Trade ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="trade in trades" :key="trade.trade_id">
                                    <tr :class="trade.side === 'buy' ? 'table-success-subtle' : 'table-danger-subtle'">
                                        <td class="small" x-text="formatTime(trade.timestamp)"></td>
                                        <td class="small"><span class="badge bg-secondary" x-text="trade.exchange"></span></td>
                                        <td>
                                            <span class="badge" :class="trade.side === 'buy' ? 'bg-success' : 'bg-danger'" x-text="trade.side.toUpperCase()"></span>
                                        </td>
                                        <td class="small" x-text="formatPrice(trade.price)"></td>
                                        <td class="small" x-text="(trade.qty || 0).toFixed(6)"></td>
                                        <td class="small" x-text="formatVolume(trade.quote_quantity)"></td>
                                        <td class="small text-secondary" x-text="trade.trade_id"></td>
                                    </tr>
                                </template>
                                <template x-if="!loading && trades.length === 0">
                                    <tr>
                                        <td colspan="7" class="text-center text-secondary py-4">No recent trades available</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trading Insights -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4">
                    <h5 class="mb-3">📚 Trading Insights - Understanding Market Microstructure</h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e;">
                                <div class="fw-bold mb-2 text-success">🟩 Bullish Signals</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>CVD trending upward</li>
                                        <li>Buyer ratio > 60%</li>
                                        <li>Positive net flow</li>
                                        <li>Increasing buy volume</li>
                                        <li>Trade bias: "buy"</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444;">
                                <div class="fw-bold mb-2 text-danger">🟥 Bearish Signals</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>CVD trending downward</li>
                                        <li>Seller ratio > 60%</li>
                                        <li>Negative net flow</li>
                                        <li>Increasing sell volume</li>
                                        <li>Trade bias: "sell"</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6;">
                                <div class="fw-bold mb-2 text-primary">⚡ Key Concepts</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li><strong>CVD:</strong> Cumulative volume delta shows net buying/selling pressure</li>
                                        <li><strong>Net Flow:</strong> Difference between buy and sell volume</li>
                                        <li><strong>Trade Bias:</strong> Overall market sentiment (buy/sell/neutral)</li>
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

    <!-- Trades Controller -->
    <script src="{{ asset('js/trades-controller.js') }}"></script>

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

        /* Table row colors */
        .table-success-subtle {
            background-color: rgba(34, 197, 94, 0.05) !important;
        }

        .table-danger-subtle {
            background-color: rgba(239, 68, 68, 0.05) !important;
        }

        /* Smooth transitions for data updates */
        .table-responsive tbody {
            transition: opacity 0.2s ease-in-out;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .derivatives-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
@endsection
