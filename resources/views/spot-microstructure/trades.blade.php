@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column gap-3 h-100" x-data="tradesController()" x-init="init()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">🐉 Trades & Large Orders Intelligence</h1>
                        <span class="pulse-dot pulse-success"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Live spot microstructure feed powered by CoinGlass API. Monitor buyer pressure, CVD and volume flow from taker buy/sell data.
                    </p>
                </div>

                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <select class="form-select" style="width: 150px;" x-model="selectedSymbol" @change="handleFilterChange()">
                        <option value="BTCUSDT">BTC / USDT</option>
                        <option value="ETHUSDT">ETH / USDT</option>
                        <option value="SOLUSDT">SOL / USDT</option>
                        <option value="BNBUSDT">BNB / USDT</option>
                        <option value="XRPUSDT">XRP / USDT</option>
                        <option value="ADAUSDT">ADA / USDT</option>
                        <option value="DOGEUSDT">DOGE / USDT</option>
                        <option value="MATICUSDT">MATIC / USDT</option>
                    </select>

                    <select class="form-select" style="width: 130px;" x-model="selectedInterval" @change="handleFilterChange()">
                        <option value="1m">1 Minute</option>
                        <option value="5m">5 Minutes</option>
                        <option value="15m">15 Minutes</option>
                        <option value="1h">1 Hour</option>
                        <option value="4h">4 Hours</option>
                    </select>

                    <select class="form-select" style="width: 150px;" x-model="selectedLimit" @change="handleFilterChange()">
                        <option value="50">50 Records</option>
                        <option value="100">100 Records</option>
                        <option value="200">200 Records</option>
                        <option value="500">500 Records</option>
                        <option value="1000">1000 Records</option>
                    </select>

                    <button class="btn btn-primary" @click="refreshAll()" :disabled="loading">
                        <span x-show="!loading">🔄 Refresh</span>
                        <span x-show="loading" class="spinner-border spinner-border-sm"></span>
                    </button>

                    <button class="btn" @click="toggleAutoRefresh()"
                            :class="autoRefreshEnabled ? 'btn-success' : 'btn-outline-secondary'">
                        <span x-text="autoRefreshEnabled ? 'Auto-refresh: ON' : 'Auto-refresh: OFF'"></span>
                    </button>

                    <div class="d-flex align-items-center gap-1 text-muted small" x-show="lastUpdated">
                        <span>Last updated:</span>
                        <span class="fw-bold" x-text="lastUpdated"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3" x-data="tradeOverview()" x-init="init()">
            <div class="col-sm-6 col-lg-3">
                <div class="df-panel summary-card h-100 p-3">
                    <div class="small text-secondary mb-1">Spot Price</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <div class="h3 mb-0" x-text="formatPrice(metrics.currentPrice)">--</div>
                        <span class="badge"
                              :class="metrics.priceChange >= 0 ? 'text-bg-success' : 'text-bg-danger'"
                              x-text="formatPercent(metrics.priceChange)">0.00%</span>
                    </div>
                    <small class="text-muted">Snapshot from latest trade summary bucket.</small>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="df-panel summary-card h-100 p-3">
                    <div class="small text-secondary mb-1">Buyer Dominance</div>
                    <div class="h3 mb-1" x-text="formatRatio(metrics.buyRatio)">--</div>
                    <div class="progress buyer-progress">
                        <div class="progress-bar bg-success"
                             role="progressbar"
                             :style="`width: ${(metrics.buyRatio * 100).toFixed(1)}%`">
                        </div>
                    </div>
                    <small class="text-muted">Buy volume share over selected window.</small>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="df-panel summary-card h-100 p-3">
                    <div class="small text-secondary mb-1">Net Flow (Quote)</div>
                    <div class="h3 mb-1"
                         :class="metrics.netFlow >= 0 ? 'text-success' : 'text-danger'"
                         x-text="formatFlow(metrics.netFlow)">--</div>
                    <small class="text-muted">Positive = aggressive buyers dominating.</small>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="df-panel summary-card h-100 p-3">
                    <div class="small text-secondary mb-1">Volume Flow</div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div>
                            <div class="h4 mb-0" x-text="metrics.buyRatio.toFixed(1) + '%'">--</div>
                            <small class="text-muted">Buy Volume Ratio</small>
                        </div>
                        <div>
                            <span class="badge" :class="getBiasBadgeClass()" x-text="metrics.bias.toUpperCase()">NEUTRAL</span>
                        </div>
                    </div>
                    <div class="text-muted small">Net Flow:
                        <span class="fw-semibold" :class="metrics.netFlow >= 0 ? 'text-success' : 'text-danger'" x-text="formatCurrency(metrics.netFlow)">$0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart + Stats -->
        <div class="row g-3 align-items-stretch">
            <div class="col-lg-8">
                <div class="df-panel h-100 p-3 cvd-panel" x-data="cvdChartPanel()" x-init="init()">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                        <div>
                            <h5 class="mb-1">📈 CVD vs Price</h5>
                            <small class="text-secondary">Same data flow as derivatives dashboards – direct provider feed.</small>
                            <div class="small text-info">
                                <span x-show="loading">Loading CVD data...</span>
                                <span x-show="!loading && cvdData.length > 0">✅ <span x-text="cvdData.length"></span> data points loaded</span>
                                <span x-show="!loading && cvdData.length === 0">❌ No data loaded</span>
                            </div>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <template x-for="range in ranges" :key="range.value">
                                <button class="btn btn-sm"
                                        @click="setRange(range.value)"
                                        :class="selectedRange === range.value ? 'btn-primary' : 'btn-outline-secondary'"
                                        x-text="range.label"></button>
                            </template>
                        </div>
                    </div>

                    <div class="chart-wrapper position-relative">
                        <div class="loading-overlay" x-show="loading">
                            <div class="spinner-border text-primary"></div>
                        </div>
                        <!-- Temporarily removed "No CVD data available" message to test chart -->
                        <canvas x-ref="cvdChart" style="width: 100%; height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="df-panel h-100 p-3" x-data="volumeFlowStats()" x-init="init()">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">⚖️ Flow Breakdown</h5>
                        <span class="badge bg-light text-dark" x-show="loading">Updating…</span>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between small text-secondary mb-1">
                            <span>Buy Volume</span>
                            <span x-text="formatVolume(totalBuyVolume)">--</span>
                        </div>
                        <div class="progress buyer-progress">
                            <div class="progress-bar bg-success" role="progressbar"
                                 :style="`width: ${totalBuyVolume === 0 && totalSellVolume === 0 ? 50 : (totalBuyVolume / (totalBuyVolume + totalSellVolume)) * 100}%`">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between small text-secondary mb-1">
                            <span>Sell Volume</span>
                            <span x-text="formatVolume(totalSellVolume)">--</span>
                        </div>
                        <div class="progress seller-progress">
                            <div class="progress-bar bg-danger" role="progressbar"
                                 :style="`width: ${totalBuyVolume === 0 && totalSellVolume === 0 ? 50 : (totalSellVolume / (totalBuyVolume + totalSellVolume)) * 100}%`">
                            </div>
                        </div>
                    </div>

                    <div class="whale-stat mb-3" :class="netFlow >= 0 ? 'net-flow-positive' : 'net-flow-negative'">
                        <div class="small text-secondary">Net Flow</div>
                        <div class="h4 mb-0" x-text="formatVolume(netFlow)">--</div>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-secondary small">Data Points</span>
                            <span class="fw-semibold" x-text="totalTrades.toLocaleString()">--</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-secondary small">Avg Volume/Point</span>
                            <span class="fw-semibold" x-text="formatVolume(avgTradeSize)">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Large Orders + Recent Trades -->
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="df-panel h-100 p-3" x-data="largeOrdersPanel()" x-init="init()">
                    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                        <div>
                            <h5 class="mb-1">📊 Volume Analysis</h5>
                            <small class="text-secondary">Generated from CoinGlass taker buy/sell volume data.</small>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-sm"
                                    :class="minNotional === 50000 ? 'btn-outline-primary active' : 'btn-outline-secondary'"
                                    @click="setThreshold(50000)">≥ $50K</button>
                            <button class="btn btn-sm"
                                    :class="minNotional === 100000 ? 'btn-outline-primary active' : 'btn-outline-secondary'"
                                    @click="setThreshold(100000)">≥ $100K</button>
                            <button class="btn btn-sm"
                                    :class="minNotional === 250000 ? 'btn-outline-primary active' : 'btn-outline-secondary'"
                                    @click="setThreshold(250000)">≥ $250K</button>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="whale-stat">
                                <div class="small text-secondary">Total Notional</div>
                                <div class="h5 mb-0" x-text="formatNotional(stats.totalNotional)">--</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="whale-stat">
                                <div class="small text-secondary">Largest Print</div>
                                <template x-if="stats.largestOrder">
                                    <div>
                                        <div class="h5 mb-0" x-text="formatNotional(stats.largestOrder.notional)">--</div>
                                        <small class="text-muted" x-text="formatQty(stats.largestOrder.qty) + ' BTC @ ' + formatPrice(stats.largestOrder.price)">--</small>
                                    </div>
                                </template>
                                <template x-if="!stats.largestOrder">
                                    <div class="h5 mb-0 text-muted">--</div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="table-container">
                        <div class="table-responsive large-orders-table">
                            <table class="table table-sm align-middle">
                                <thead class="sticky-top">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Side</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Qty (BTC)</th>
                                    <th class="text-end">Notional</th>
                                </tr>
                                </thead>
                                <tbody>
                                <template x-for="order in orders" :key="order.trade_id">
                                    <tr>
                                        <td class="small">
                                            <div x-text="formatDateTime(order.ts || order.timestamp)"></div>
                                        </td>
                                        <td>
                                            <span class="badge" :class="getSideBadge(order.side)" x-text="order.side.toUpperCase()"></span>
                                        </td>
                                        <td class="text-end" x-text="formatPrice(order.price)"></td>
                                        <td class="text-end" x-text="formatQty(order.qty)"></td>
                                        <td class="text-end fw-semibold" x-text="formatNotional(order.notional)"></td>
                                    </tr>
                                </template>
                                <tr x-show="!loading && orders.length === 0">
                                    <td colspan="5" class="text-center text-secondary py-3">No whale activity for the selected threshold.</td>
                                </tr>
                                </tbody>
                            </table>
                            <div class="text-center py-3" x-show="loading">
                                <div class="spinner-border spinner-border-sm text-primary"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="df-panel h-100 p-3" x-data="recentTradesStream()" x-init="init()">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">📈 Volume Buckets</h5>
                        <span class="badge bg-light text-dark" x-show="loading">Updating…</span>
                    </div>
                    <div class="table-container">
                        <div class="table-responsive trades-table">
                            <table class="table table-sm align-middle">
                                <thead class="sticky-top">
                                <tr>
                                    <th>Date & Time</th>
                                    <th class="text-end">Buy Volume</th>
                                    <th class="text-end">Sell Volume</th>
                                    <th class="text-end">Net Flow</th>
                                    <th class="text-end">Total Volume</th>
                                </tr>
                                </thead>
                                <tbody>
                                <template x-for="bucket in trades" :key="bucket.ts_ms">
                                    <tr>
                                        <td class="small">
                                            <div x-text="formatDateTime(bucket.ts_ms || bucket.bucket_time)"></div>
                                        </td>
                                        <td class="text-end text-success" x-text="formatVolume(bucket.buy_volume_quote)"></td>
                                        <td class="text-end text-danger" x-text="formatVolume(bucket.sell_volume_quote)"></td>
                                        <td class="text-end" :class="bucket.net_flow_quote >= 0 ? 'text-success' : 'text-danger'" x-text="formatVolume(bucket.net_flow_quote)"></td>
                                        <td class="text-end" x-text="formatVolume(bucket.volume_quote)"></td>
                                    </tr>
                                </template>
                                <tr x-show="!loading && trades.length === 0">
                                    <td colspan="5" class="text-center text-secondary py-3">No volume data available.</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aggregated Buckets -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-3" x-data="tradeSummaryTable()" x-init="init()">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">📊 Interval Summary</h5>
                            <small class="text-secondary">VWAP, trades and flow per selected interval – same aggregation flow as derivatives dashboards.</small>
                        </div>
                        <span class="badge bg-light text-dark" x-show="loading">Updating…</span>
                    </div>

                    <div class="table-container">
                        <div class="table-responsive trades-table">
                            <table class="table table-sm align-middle">
                                <thead class="sticky-top">
                                <tr>
                                    <th>Date & Time</th>
                                    <th class="text-end">Trades</th>
                                    <th class="text-end">VWAP</th>
                                    <th class="text-end">Buy Share</th>
                                    <th class="text-end">Net Flow</th>
                                </tr>
                                </thead>
                                <tbody>
                                <template x-for="bucket in trades" :key="bucket.ts_ms">
                                    <tr>
                                        <td class="small">
                                            <div x-text="formatDateTime(bucket.ts_ms || bucket.bucket_time)"></div>
                                        </td>
                                        <td class="text-end fw-semibold" x-text="bucket.trades_count.toLocaleString()">0</td>
                                        <td class="text-end" x-text="formatPrice(bucket.avg_price)"></td>
                                        <td class="text-end">
                                            <span class="badge rounded-pill"
                                                  :class="bucket.buy_volume_quote >= bucket.sell_volume_quote ? 'text-bg-success' : 'text-bg-danger'"
                                                  x-text="buyRatio(bucket)"></span>
                                        </td>
                                        <td class="text-end" :class="bucket.net_flow_quote >= 0 ? 'text-success' : 'text-danger'"
                                            x-text="formatVolume(bucket.net_flow_quote)"></td>
                                    </tr>
                                </template>
                                <tr x-show="!loading && trades.length === 0">
                                    <td colspan="5" class="text-center text-secondary py-3">No summary data available.</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    <script src="{{ asset('js/trades-controller.js') }}"></script>

    <style>
        .pulse-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
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
                box-shadow: 0 0 0 10px rgba(34, 197, 94, 0);
            }
        }

        .summary-card {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(37, 99, 235, 0.04));
            border: 1px solid rgba(148, 163, 184, 0.2);
        }

        .cvd-panel {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.35), rgba(30, 64, 175, 0.25));
            border: 1px solid rgba(59, 130, 246, 0.3);
            box-shadow: 0 15px 40px rgba(15, 23, 42, 0.35);
        }

        .chart-wrapper {
            height: 340px;
        }

        .loading-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.35);
            backdrop-filter: blur(2px);
        }

        .buyer-progress,
        .seller-progress {
            height: 6px;
            border-radius: 999px;
        }

        .whale-stat {
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 0.75rem;
            padding: 0.75rem;
            background: rgba(15, 23, 42, 0.4);
        }

        .net-flow-positive {
            background: rgba(34, 197, 94, 0.08);
            border-color: rgba(34, 197, 94, 0.2);
        }

        .net-flow-negative {
            background: rgba(239, 68, 68, 0.08);
            border-color: rgba(239, 68, 68, 0.2);
        }

        .table-container {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 0.75rem;
            background: rgba(15, 23, 42, 0.4);
        }

        .table-container::-webkit-scrollbar {
            width: 8px;
        }

        .table-container::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.3);
            border-radius: 4px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.5);
            border-radius: 4px;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.7);
        }

        .large-orders-table table,
        .trades-table table {
            border-color: rgba(148, 163, 184, 0.2);
            margin-bottom: 0;
        }

        .sticky-top {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(8px);
            border-bottom: 2px solid rgba(59, 130, 246, 0.3);
        }

        .sticky-top th {
            border-bottom: none;
            font-weight: 600;
            color: #e2e8f0;
            padding: 12px 8px;
        }

        .btn-outline-primary.active {
            color: #fff;
            background-color: #2563eb;
            border-color: #2563eb;
        }

        @media (max-width: 768px) {
            .derivatives-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
@endsection

