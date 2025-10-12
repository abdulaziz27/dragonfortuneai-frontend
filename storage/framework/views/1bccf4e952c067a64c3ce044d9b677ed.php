<?php $__env->startSection('content'); ?>
    

    <div class="d-flex flex-column h-100 gap-3" x-data="volumeTradeStatsController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">📊 Volume & Trade Stats</h1>
                        <span class="pulse-dot pulse-success"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Comprehensive volume analysis and trade statistics for spot microstructure insights
                    </p>
                </div>

                <!-- Global Controls -->
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <select class="form-select" style="width: 150px;" x-model="globalSymbol" @change="updateSymbol()">
                        <option value="BTCUSDT">BTC/USDT</option>
                        <option value="ETHUSDT">ETH/USDT</option>
                        <option value="SOLUSDT">SOL/USDT</option>
                        <option value="BNBUSDT">BNB/USDT</option>
                        <option value="XRPUSDT">XRP/USDT</option>
                    </select>

                    <select class="form-select" style="width: 120px;" x-model="globalTimeframe" @change="updateTimeframe()">
                        <option value="1m">1 Minute</option>
                        <option value="5m" selected>5 Minutes</option>
                        <option value="15m">15 Minutes</option>
                        <option value="1h">1 Hour</option>
                    </select>

                    <button class="btn btn-primary" @click="refreshAll()" :disabled="globalLoading">
                        <span x-show="!globalLoading">🔄 Refresh</span>
                        <span x-show="globalLoading" class="spinner-border spinner-border-sm"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Key Metrics Row -->
        <div class="row g-3">
            <div class="col-md-6 col-lg-3">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-secondary small">Total Trades</span>
                        <span class="badge bg-primary">📈</span>
                    </div>
                    <div class="h3 mb-1 fw-bold" x-text="formatNumber(metrics.totalTrades)">--</div>
                    <div class="small text-secondary">Buy: <span x-text="formatNumber(metrics.buyTrades)">--</span> / Sell: <span x-text="formatNumber(metrics.sellTrades)">--</span></div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-secondary small">Buy/Sell Ratio</span>
                        <span class="badge" :class="metrics.buySellRatio > 1 ? 'bg-success' : 'bg-danger'">
                            <span x-show="metrics.buySellRatio > 1">🟢</span>
                            <span x-show="metrics.buySellRatio <= 1">🔴</span>
                        </span>
                    </div>
                    <div class="h3 mb-1 fw-bold" :class="metrics.buySellRatio > 1 ? 'text-success' : 'text-danger'" x-text="metrics.buySellRatio.toFixed(2)">--</div>
                    <div class="small text-secondary">
                        <span x-show="metrics.buySellRatio > 1">Buying dominance</span>
                        <span x-show="metrics.buySellRatio <= 1">Selling dominance</span>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-secondary small">Total Volume</span>
                        <span class="badge bg-info">💰</span>
                    </div>
                    <div class="h3 mb-1 fw-bold" x-text="formatNumber(metrics.totalVolume)">--</div>
                    <div class="small text-secondary">Std Dev: <span x-text="formatNumber(metrics.volumeStd)">--</span></div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-secondary small">Avg Trade Size</span>
                        <span class="badge bg-warning">📏</span>
                    </div>
                    <div class="h3 mb-1 fw-bold" x-text="formatNumber(metrics.avgTradeSize)">--</div>
                    <div class="small text-secondary">Max: <span x-text="formatNumber(metrics.maxTradeSize)">--</span></div>
                </div>
            </div>
        </div>

        <!-- Main Charts Row -->
        <div class="row g-3">
            <!-- Trade Statistics Over Time -->
            <div class="col-lg-8">
                <div class="df-panel p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">📈 Trade Activity Over Time</h5>
                            <p class="text-secondary small mb-0">Buy vs Sell trade frequency analysis</p>
                        </div>
                        <span class="badge bg-light text-dark border">Line Chart</span>
                    </div>
                    <div style="height: 350px;">
                        <canvas x-ref="tradeStatsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Buy/Sell Distribution -->
            <div class="col-lg-4">
                <div class="df-panel p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">🎯 Trade Distribution</h5>
                            <p class="text-secondary small mb-0">Buy vs Sell breakdown</p>
                        </div>
                    </div>
                    <div style="height: 300px;">
                        <canvas x-ref="buySellChart"></canvas>
                    </div>

                    <!-- Buy/Sell Insight -->
                    <div class="mt-3" x-data="{ insight: getBuySellInsight() }">
                        <div class="alert mb-0" :class="insight.class">
                            <div class="fw-semibold small mb-1" x-text="insight.icon + ' ' + insight.title"></div>
                            <div class="small" x-text="insight.message"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Volume Analysis Row -->
        <div class="row g-3">
            <!-- Volume Time Series -->
            <div class="col-lg-8">
                <div class="df-panel p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">📊 Volume Flow Analysis</h5>
                            <p class="text-secondary small mb-0">Buy and sell volume over time</p>
                        </div>
                        <span class="badge bg-light text-dark border">Bar Chart</span>
                    </div>
                    <div style="height: 350px;">
                        <canvas x-ref="volumeTimeSeriesChart"></canvas>
                    </div>

                    <!-- Volume Insight -->
                    <div class="mt-3" x-data="{ insight: getVolumeInsight() }">
                        <div class="p-3 rounded bg-light">
                            <div class="fw-semibold small mb-1 d-flex align-items-center gap-2">
                                <span x-text="insight.icon"></span>
                                <span x-text="insight.title"></span>
                            </div>
                            <div class="small text-secondary" x-text="insight.message"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Volume Profile Summary -->
            <div class="col-lg-4">
                <div class="df-panel p-4 h-100">
                    <h5 class="mb-3">📋 Volume Profile Summary</h5>

                    <div class="d-flex flex-column gap-3">
                        <!-- Period Info -->
                        <div class="stat-item">
                            <div class="small text-secondary mb-1">Analysis Period</div>
                            <template x-if="volumeProfileData">
                                <div>
                                    <div class="fw-semibold small" x-text="volumeProfileData.period_start || 'N/A'"></div>
                                    <div class="fw-semibold small" x-text="'to ' + (volumeProfileData.period_end || 'N/A')"></div>
                                </div>
                            </template>
                            <template x-if="!volumeProfileData">
                                <div class="text-secondary">Loading...</div>
                            </template>
                        </div>

                        <hr class="my-2">

                        <!-- Volume Metrics -->
                        <div class="stat-item">
                            <div class="small text-secondary mb-2">Buy Volume</div>
                            <div class="h5 mb-0 text-success" x-text="formatNumber(metrics.buyVolume)">--</div>
                        </div>

                        <div class="stat-item">
                            <div class="small text-secondary mb-2">Sell Volume</div>
                            <div class="h5 mb-0 text-danger" x-text="formatNumber(metrics.sellVolume)">--</div>
                        </div>

                        <hr class="my-2">

                        <!-- POC Price -->
                        <div class="stat-item">
                            <div class="small text-secondary mb-1">Point of Control (POC)</div>
                            <div class="h4 mb-0 fw-bold text-primary">
                                $<span x-text="formatNumber(metrics.pocPrice)">--</span>
                            </div>
                            <div class="small text-secondary">Highest volume price level</div>
                        </div>

                        <div class="alert alert-info mb-0 small">
                            <strong>💡 POC Insight:</strong> The Point of Control represents the price level with the highest traded volume, acting as a potential support or resistance zone.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Volume Profile & Trade Size Row -->
        <div class="row g-3">
            <!-- Volume Profile by Price -->
            <div class="col-lg-6">
                <div class="df-panel p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">💎 Volume Profile by Price Level</h5>
                            <p class="text-secondary small mb-0">Top 20 price levels by volume (POC highlighted)</p>
                        </div>
                        <span class="badge bg-light text-dark border">Horizontal Bar</span>
                    </div>
                    <div style="height: 400px;">
                        <canvas x-ref="volumeProfileChart"></canvas>
                    </div>

                    <div class="mt-3">
                        <div class="p-3 rounded" style="background: rgba(139, 92, 246, 0.1);">
                            <div class="fw-semibold small mb-1">📚 Understanding Volume Profile</div>
                            <div class="small text-secondary">
                                Volume Profile shows the distribution of volume across different price levels. The highest bar (purple) is the POC - a critical level where most trading activity occurred. These levels often act as support/resistance.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trade Size Distribution -->
            <div class="col-lg-6">
                <div class="df-panel p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">📏 Trade Size Evolution</h5>
                            <p class="text-secondary small mb-0">Average and maximum trade sizes over time</p>
                        </div>
                        <span class="badge bg-light text-dark border">Line Chart</span>
                    </div>
                    <div style="height: 400px;">
                        <canvas x-ref="tradeSizeChart"></canvas>
                    </div>

                    <div class="mt-3">
                        <div class="p-3 rounded bg-light">
                            <div class="fw-semibold small mb-1">🔍 Trade Size Analysis</div>
                            <div class="small text-secondary">
                                Large spikes in maximum trade size often indicate institutional participation or whale activity. Consistent average trade size suggests retail dominance, while increasing average size may signal institutional accumulation.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Statistics Table -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">📋 Detailed Trade Statistics</h5>
                            <p class="text-secondary small mb-0">Recent trade activity breakdown</p>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge bg-light text-dark border" x-text="'Showing ' + Math.min(20, tradeStatsData.length) + ' records'"></span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Exchange</th>
                                    <th>Pair</th>
                                    <th class="text-end">Total Trades</th>
                                    <th class="text-end">Buy Trades</th>
                                    <th class="text-end">Sell Trades</th>
                                    <th class="text-end">Avg Size</th>
                                    <th class="text-end">Max Size</th>
                                    <th class="text-center">B/S Ratio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in tradeStatsData.slice(-20).reverse()" :key="index">
                                    <tr>
                                        <td class="small" x-text="new Date(item.timestamp).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false })"></td>
                                        <td>
                                            <span class="badge bg-light text-dark border text-uppercase" x-text="item.exchange"></span>
                                        </td>
                                        <td class="fw-semibold small" x-text="item.symbol"></td>
                                        <td class="text-end" x-text="formatNumber(item.total_trades)"></td>
                                        <td class="text-end text-success" x-text="formatNumber(item.buy_trades)"></td>
                                        <td class="text-end text-danger" x-text="formatNumber(item.sell_trades)"></td>
                                        <td class="text-end" x-text="formatNumber(item.avg_trade_size)"></td>
                                        <td class="text-end fw-semibold" x-text="formatNumber(item.max_trade_size)"></td>
                                        <td class="text-center">
                                            <span class="badge"
                                                  :class="(item.buy_trades / item.sell_trades) > 1 ? 'bg-success' : 'bg-danger'"
                                                  x-text="(item.buy_trades / item.sell_trades).toFixed(2)"></span>
                                        </td>
                                    </tr>
                                </template>

                                <template x-if="tradeStatsData.length === 0">
                                    <tr>
                                        <td colspan="9" class="text-center text-secondary py-4">
                                            <div class="spinner-border spinner-border-sm me-2" role="status" x-show="globalLoading"></div>
                                            <span x-show="globalLoading">Loading trade statistics...</span>
                                            <span x-show="!globalLoading">No data available</span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Volume Statistics Table -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">💰 Volume Statistics Breakdown</h5>
                            <p class="text-secondary small mb-0">Detailed volume metrics with buy/sell breakdown</p>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge bg-light text-dark border" x-text="'Showing ' + Math.min(20, volumeStatsData.length) + ' records'"></span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Exchange</th>
                                    <th>Timeframe</th>
                                    <th class="text-end">Buy Volume</th>
                                    <th class="text-end">Sell Volume</th>
                                    <th class="text-end">Total Volume</th>
                                    <th class="text-end">Avg Volume</th>
                                    <th class="text-end">Vol Std Dev</th>
                                    <th class="text-center">Dominance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in volumeStatsData.slice(-20).reverse()" :key="index">
                                    <tr>
                                        <td class="small" x-text="new Date(item.timestamp).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false })"></td>
                                        <td>
                                            <span class="badge bg-light text-dark border text-uppercase" x-text="item.exchange"></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary" x-text="item.timeframe"></span>
                                        </td>
                                        <td class="text-end text-success fw-semibold" x-text="formatNumber(item.buy_volume)"></td>
                                        <td class="text-end text-danger fw-semibold" x-text="formatNumber(item.sell_volume)"></td>
                                        <td class="text-end fw-bold" x-text="formatNumber(item.total_volume)"></td>
                                        <td class="text-end" x-text="formatNumber(item.avg_volume)"></td>
                                        <td class="text-end text-secondary" x-text="formatNumber(item.volume_std)"></td>
                                        <td class="text-center">
                                            <span class="badge"
                                                  :class="item.buy_volume > item.sell_volume ? 'bg-success' : 'bg-danger'"
                                                  x-text="item.buy_volume > item.sell_volume ? 'BUY' : 'SELL'"></span>
                                        </td>
                                    </tr>
                                </template>

                                <template x-if="volumeStatsData.length === 0">
                                    <tr>
                                        <td colspan="9" class="text-center text-secondary py-4">
                                            <div class="spinner-border spinner-border-sm me-2" role="status" x-show="globalLoading"></div>
                                            <span x-show="globalLoading">Loading volume statistics...</span>
                                            <span x-show="!globalLoading">No data available</span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Educational Section -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4">
                    <h5 class="mb-3">📚 Understanding Volume & Trade Statistics</h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e;">
                                <div class="fw-bold mb-2 text-success">🟩 Buy/Sell Ratio</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Ratio > 1.5: Strong buying pressure, bullish sentiment</li>
                                        <li>Ratio 0.9-1.1: Balanced market, no clear bias</li>
                                        <li>Ratio < 0.7: Strong selling pressure, bearish sentiment</li>
                                        <li>Use with price action for confirmation</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(139, 92, 246, 0.1); border-left: 4px solid #8b5cf6;">
                                <div class="fw-bold mb-2 text-primary">💎 Volume Profile (POC)</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>POC = Point of Control (highest volume price)</li>
                                        <li>Acts as strong support/resistance level</li>
                                        <li>Price tends to return to high volume areas</li>
                                        <li>Use for entry/exit planning and stop placement</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6;">
                                <div class="fw-bold mb-2 text-info">📏 Trade Size Analysis</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Large max trades = whale/institutional activity</li>
                                        <li>Rising avg size = accumulation phase</li>
                                        <li>Falling avg size = distribution phase</li>
                                        <li>Spikes in trade size often precede volatility</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <!-- Chart.js -->
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
    </script>

    <!-- Load controller -->
    <script src="<?php echo e(asset('js/volume-trade-stats-controller.js')); ?>"></script>

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

        /* Table styling */
        .table-hover tbody tr:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.05);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .derivatives-header h1 {
                font-size: 1.5rem;
            }

            .table-responsive {
                font-size: 0.875rem;
            }
        }

        /* Loading state */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\DATASAID\Said\Bisnis\quantwaru\frontend\dragonfortuneai-frontend\resources\views/spot-microstructure/volume-trade-stats.blade.php ENDPATH**/ ?>