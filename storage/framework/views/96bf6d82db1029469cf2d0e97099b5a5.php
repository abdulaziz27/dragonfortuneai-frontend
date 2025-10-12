<?php $__env->startSection('content'); ?>
    

    <div class="d-flex flex-column h-100 gap-3" x-data="onchainMetricsController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">On-Chain Metrics</h1>
                        <span class="pulse-dot pulse-success"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Monitor Bitcoin on-chain data: MVRV, supply distribution, exchange flows, miner activity & whale movements
                    </p>
                </div>

                <!-- Global Controls -->
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <!-- Asset Filter (for flows) -->
                    <select class="form-select" style="width: 140px;" x-model="selectedAsset" @change="refreshAll()">
                        <option value="ALL">All Assets</option>
                        <option value="BTC">BTC</option>
                        <option value="USDT">USDT</option>
                    </select>

                    <!-- Exchange Filter -->
                    <select class="form-select" style="width: 140px;" x-model="selectedExchange" @change="refreshAll()">
                        <option value="ALL">All Exchanges</option>
                        <option value="binance">Binance</option>
                        <option value="coinbase">Coinbase</option>
                        <option value="okx">OKX</option>
                    </select>

                    <!-- Date Range -->
                    <select class="form-select" style="width: 120px;" x-model="selectedDateRange" @change="refreshAll()">
                        <option value="30d">30 Days</option>
                        <option value="90d">90 Days</option>
                        <option value="180d">180 Days</option>
                        <option value="365d">1 Year</option>
                    </select>

                    <!-- Refresh Button -->
                    <button class="btn btn-primary" @click="refreshAll()" :disabled="loading">
                        <span x-show="!loading">🔄 Refresh All</span>
                        <span x-show="loading" class="spinner-border spinner-border-sm"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Stats Row -->
        <div class="row g-3">
            <div class="col-lg-3 col-md-6">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small">MVRV Z-Score</span>
                        <span class="badge bg-info bg-opacity-10 text-info">Valuation</span>
                    </div>
                    <div class="h3 mb-1 fw-bold" :class="getMVRVZScoreClass()" x-text="formatValue(metrics.mvrvZScore, 2)"></div>
                    <div class="small text-muted" x-text="metrics.mvrvZScoreStatus"></div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small">Exchange Netflow (24h)</span>
                        <span class="badge bg-warning bg-opacity-10 text-warning">Flow</span>
                    </div>
                    <div class="h3 mb-1 fw-bold" :class="getNetflowClass(metrics.btcNetflow)" x-text="formatValue(metrics.btcNetflow, 0, 'BTC')"></div>
                    <div class="small text-muted" x-text="metrics.btcNetflowStatus"></div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small">Puell Multiple</span>
                        <span class="badge bg-danger bg-opacity-10 text-danger">Miners</span>
                    </div>
                    <div class="h3 mb-1 fw-bold" :class="getPuellMultipleClass()" x-text="formatValue(metrics.puellMultiple, 2)"></div>
                    <div class="small text-muted" x-text="metrics.puellMultipleStatus"></div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small">LTH/STH Ratio</span>
                        <span class="badge bg-success bg-opacity-10 text-success">Supply</span>
                    </div>
                    <div class="h3 mb-1 fw-bold" :class="getLthSthRatioClass()" x-text="formatValue(metrics.lthSthRatio, 2)"></div>
                    <div class="small text-muted" x-text="metrics.lthSthRatioStatus"></div>
                </div>
            </div>
        </div>

        <!-- MVRV & Valuation Metrics Row -->
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="df-panel p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">📊 MVRV Z-Score & Realized Price</h5>
                            <small class="text-secondary">Market valuation cycle indicator</small>
                        </div>
                        <div class="d-flex gap-2">
                            <span x-show="loadingStates.mvrv" class="spinner-border spinner-border-sm text-primary"></span>
                            <button class="btn btn-sm btn-outline-primary" @click="refreshAll()">Refresh</button>
                        </div>
                    </div>
                    <div style="height: 350px; position: relative;">
                        <canvas x-ref="mvrvChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="df-panel p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">🎯 Market Valuation</h5>
                            <small class="text-secondary">Current position</small>
                        </div>
                    </div>

                    <!-- Valuation Zones -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">Z-Score</span>
                            <span class="fw-bold" x-text="formatValue(metrics.mvrvZScore, 2)"></span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar"
                                 :class="getZScoreColorClass(metrics.mvrvZScore)"
                                 :style="`width: ${getZScoreProgress(metrics.mvrvZScore)}%`"
                                 x-text="getZScoreLabel(metrics.mvrvZScore)">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">Undervalued</small>
                            <small class="text-muted">Overvalued</small>
                        </div>
                    </div>

                    <!-- Interpretation Guide -->
                    <div class="mt-4">
                        <div class="small mb-3">
                            <div class="fw-bold mb-2">📚 Interpretation:</div>
                            <div class="p-2 rounded mb-2" style="background: rgba(239, 68, 68, 0.1);">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-danger">Z > 7</span>
                                    <span class="text-danger small">Extreme overvaluation</span>
                                </div>
                            </div>
                            <div class="p-2 rounded mb-2" style="background: rgba(34, 197, 94, 0.1);">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-success">Z < 0</span>
                                    <span class="text-success small">Extreme undervaluation</span>
                                </div>
                            </div>
                            <div class="p-2 rounded" style="background: rgba(59, 130, 246, 0.1);">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-info">0-7</span>
                                    <span class="text-info small">Normal range</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exchange Flows Row -->
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="df-panel p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">🔄 Exchange Netflow (BTC & Stablecoins)</h5>
                            <small class="text-secondary">Daily inflow/outflow per exchange</small>
                        </div>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" style="width: 100px;" x-model="selectedAsset" @change="refreshAll()">
                                <option value="ALL">All Assets</option>
                                <option value="BTC">BTC</option>
                                <option value="USDT">USDT</option>
                            </select>
                            <span x-show="loadingStates.flows" class="spinner-border spinner-border-sm text-primary"></span>
                            <button class="btn btn-sm btn-outline-primary" @click="refreshAll()">Refresh</button>
                        </div>
                    </div>
                    <div style="height: 350px; position: relative;">
                        <canvas x-ref="exchangeFlowChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="df-panel p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">📈 Exchange Summary</h5>
                            <small class="text-secondary">Cumulative flows</small>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="sticky-top bg-white">
                                <tr class="text-muted small">
                                    <th>Exchange</th>
                                    <th class="text-end">Netflow</th>
                                    <th class="text-center">Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, idx) in exchangeSummary" :key="idx">
                                    <tr>
                                        <td class="small" x-text="item.exchange"></td>
                                        <td class="text-end small">
                                            <span :class="item.total_netflow < 0 ? 'text-success' : 'text-danger'"
                                                  x-text="formatNumber(item.total_netflow)"></span>
                                        </td>
                                        <td class="text-center">
                                            <span x-show="item.total_netflow < 0" class="text-success">📉</span>
                                            <span x-show="item.total_netflow > 0" class="text-danger">📈</span>
                                            <span x-show="item.total_netflow === 0" class="text-muted">➖</span>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="exchangeSummary.length === 0">
                                    <td colspan="3" class="text-center text-muted small py-3">
                                        <span x-show="!loadingStates.flows">No data available</span>
                                        <span x-show="loadingStates.flows">Loading...</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Supply Distribution & HODL Waves Row -->
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="df-panel p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">💎 LTH vs STH Supply</h5>
                            <small class="text-secondary">Long-term vs Short-term holders</small>
                        </div>
                        <div class="d-flex gap-2">
                            <span x-show="loadingStates.supply" class="spinner-border spinner-border-sm text-primary"></span>
                            <button class="btn btn-sm btn-outline-primary" @click="refreshAll()">Refresh</button>
                        </div>
                    </div>
                    <div style="height: 350px; position: relative;">
                        <canvas x-ref="supplyChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="df-panel p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">🌊 HODL Waves</h5>
                            <small class="text-secondary">Age-based supply distribution</small>
                        </div>
                        <div class="d-flex gap-2">
                            <span x-show="loadingStates.hodl" class="spinner-border spinner-border-sm text-primary"></span>
                            <button class="btn btn-sm btn-outline-primary" @click="refreshAll()">Refresh</button>
                        </div>
                    </div>
                    <div style="height: 350px; position: relative;">
                        <canvas x-ref="hodlChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chain Health Indicators Row -->
        <div class="row g-3">
            <div class="col-lg-12">
                <div class="df-panel p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">🏥 Chain Health Indicators</h5>
                            <small class="text-secondary">Reserve Risk, SOPR, Adjusted SOPR, Dormancy, CDD</small>
                        </div>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" style="width: 160px;" x-model="chainHealthMetric" @change="refreshAll()">
                                <option value="RESERVE_RISK">Reserve Risk</option>
                                <option value="SOPR">SOPR</option>
                                <option value="ADJUSTED_SOPR">Adjusted SOPR</option>
                                <option value="DORMANCY">Dormancy</option>
                                <option value="CDD">CDD</option>
                            </select>
                            <span x-show="loadingStates.chainHealth" class="spinner-border spinner-border-sm text-primary"></span>
                            <button class="btn btn-sm btn-outline-primary" @click="refreshAll()">Refresh</button>
                        </div>
                    </div>
                    <div style="height: 300px; position: relative;">
                        <canvas x-ref="chainHealthChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Miner Metrics Row -->
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="df-panel p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">⛏️ Miner Metrics</h5>
                            <small class="text-secondary">Miner reserves, Puell Multiple & hash rate</small>
                        </div>
                        <div class="d-flex gap-2">
                            <span x-show="loadingStates.miners" class="spinner-border spinner-border-sm text-primary"></span>
                            <button class="btn btn-sm btn-outline-primary" @click="refreshAll()">Refresh</button>
                        </div>
                    </div>
                    <div style="height: 350px; position: relative;">
                        <canvas x-ref="minerChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="df-panel p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">📊 Current Metrics</h5>
                            <small class="text-secondary">Latest values</small>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1);">
                            <div class="small text-muted mb-1">Miner Reserve</div>
                            <div class="h5 mb-0 fw-bold" x-text="(minerMetrics.reserve || '--') + ' BTC'"></div>
                        </div>

                        <div class="p-3 rounded" style="background: rgba(139, 92, 246, 0.1);">
                            <div class="small text-muted mb-1">Puell Multiple</div>
                            <div class="h5 mb-0 fw-bold" x-text="minerMetrics.puell || '--'"></div>
                            <div class="small" :class="getPuellClass(minerMetrics.puell)">
                                <span x-text="getPuellLabel(minerMetrics.puell)"></span>
                            </div>
                        </div>

                        <div class="p-3 rounded" style="background: rgba(34, 197, 94, 0.1);">
                            <div class="small text-muted mb-1">Hash Rate</div>
                            <div class="h5 mb-0 fw-bold" x-text="(minerMetrics.hashRate || '--') + ' EH/s'"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Whale Activity Row -->
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="df-panel p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">🐋 Whale Holdings</h5>
                            <small class="text-secondary">Large holder cohorts & daily changes</small>
                        </div>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" style="width: 180px;" x-model="whaleCohort" @change="refreshAll()">
                                <option value="">All Cohorts</option>
                                <option value="Exchange Treasuries">Exchange Treasuries</option>
                                <option value="1k-10k BTC">1k-10k BTC</option>
                                <option value="10k+ BTC">10k+ BTC</option>
                                <option value="ETF Custodians">ETF Custodians</option>
                            </select>
                            <span x-show="loadingStates.whales" class="spinner-border spinner-border-sm text-primary"></span>
                            <button class="btn btn-sm btn-outline-primary" @click="refreshAll()">Refresh</button>
                        </div>
                    </div>
                    <div style="height: 350px; position: relative;">
                        <canvas x-ref="whaleChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="df-panel p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">📊 Whale Summary</h5>
                            <small class="text-secondary">Aggregate statistics</small>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="sticky-top bg-white">
                                <tr class="text-muted small">
                                    <th>Cohort</th>
                                    <th class="text-end">Change</th>
                                    <th class="text-center">Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, idx) in whaleSummary" :key="idx">
                                    <tr>
                                        <td class="small" x-text="item.cohort"></td>
                                        <td class="text-end small">
                                            <span :class="item.balance_change_btc > 0 ? 'text-success' : 'text-danger'"
                                                  x-text="formatNumber(item.balance_change_btc)"></span>
                                        </td>
                                        <td class="text-center">
                                            <span x-show="item.balance_change_btc > 0" class="text-success">📈</span>
                                            <span x-show="item.balance_change_btc < 0" class="text-danger">📉</span>
                                            <span x-show="item.balance_change_btc === 0" class="text-muted">➖</span>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="whaleSummary.length === 0">
                                    <td colspan="3" class="text-center text-muted small py-3">
                                        <span x-show="!loadingStates.whales">No data available</span>
                                        <span x-show="loadingStates.whales">Loading...</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Realized Cap & Thermocap Row -->
        <div class="row g-3">
            <div class="col-lg-12">
                <div class="df-panel p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">💰 Realized Cap & Thermocap</h5>
                            <small class="text-secondary">Network valuation metrics</small>
                        </div>
                        <div class="d-flex gap-2">
                            <span x-show="loadingStates.realizedCap" class="spinner-border spinner-border-sm text-primary"></span>
                            <button class="btn btn-sm btn-outline-primary" @click="refreshAll()">Refresh</button>
                        </div>
                    </div>
                    <div style="height: 300px; position: relative;">
                        <canvas x-ref="realizedCapChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- CryptoQuant Advanced Metrics Section -->
        <div class="row g-3 mt-4">
            <div class="col-12">
                <div class="df-panel p-3 bg-gradient" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(147, 51, 234, 0.1) 100%);">
                    <h5 class="mb-2">🔬 CryptoQuant Advanced Metrics</h5>
                    <p class="text-muted small mb-0">Professional-grade on-chain data from CryptoQuant API</p>
                </div>
            </div>
        </div>

        <!-- CryptoQuant Charts Row 1: MPI & Miner Reserve -->
        <div class="row g-3 mt-2">
            <div class="col-lg-6">
                <div class="df-panel p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="mb-1">⛏️ Miners Position Index (MPI)</h6>
                            <small class="text-secondary">Miner selling pressure indicator</small>
                        </div>
                        <span x-show="loadingStates.cqMPI" class="spinner-border spinner-border-sm text-primary"></span>
                    </div>
                    <div style="height: 250px; position: relative;">
                        <canvas x-ref="cqMPIChart"></canvas>
                    </div>
                    <div class="mt-2 d-flex justify-content-around text-center" x-show="cryptoquant.mpi.length > 0">
                        <div>
                            <small class="text-muted">Latest MPI</small>
                            <div class="fw-bold" x-text="cryptoquant.mpi[0]?.mpi?.toFixed(4) || 'N/A'"></div>
                        </div>
                        <div>
                            <small class="text-muted">Date</small>
                            <div class="fw-bold" x-text="cryptoquant.mpi[0]?.date || 'N/A'"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="df-panel p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="mb-1">💎 Miner Reserve (BTC)</h6>
                            <small class="text-secondary">Bitcoin held by miners</small>
                        </div>
                        <span x-show="loadingStates.cqMinerReserve" class="spinner-border spinner-border-sm text-primary"></span>
                    </div>
                    <div style="height: 250px; position: relative;">
                        <canvas x-ref="cqMinerReserveChart"></canvas>
                    </div>
                    <div class="mt-2 d-flex justify-content-around text-center" x-show="cryptoquant.minerReserve.length > 0">
                        <div>
                            <small class="text-muted">Latest Reserve</small>
                            <div class="fw-bold" x-text="(cryptoquant.minerReserve[0]?.value || 0).toLocaleString() + ' BTC'"></div>
                        </div>
                        <div>
                            <small class="text-muted">Date</small>
                            <div class="fw-bold" x-text="cryptoquant.minerReserve[0]?.date || 'N/A'"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CryptoQuant Charts Row 2: ETH Gas & ETH Staking -->
        <div class="row g-3 mt-2">
            <div class="col-lg-6">
                <div class="df-panel p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="mb-1">⛽ ETH Gas Price</h6>
                            <small class="text-secondary">Ethereum network congestion</small>
                        </div>
                        <span x-show="loadingStates.cqETHGas" class="spinner-border spinner-border-sm text-primary"></span>
                    </div>
                    <div style="height: 250px; position: relative;">
                        <canvas x-ref="cqETHGasChart"></canvas>
                    </div>
                    <div class="mt-2 d-flex justify-content-around text-center" x-show="cryptoquant.ethGas.length > 0">
                        <div>
                            <small class="text-muted">Latest Gas Price</small>
                            <div class="fw-bold" x-text="(cryptoquant.ethGas[0]?.gas_price || 0).toFixed(2) + ' Gwei'"></div>
                        </div>
                        <div>
                            <small class="text-muted">Timestamp</small>
                            <div class="fw-bold" x-text="new Date(cryptoquant.ethGas[0]?.timestamp).toLocaleDateString() || 'N/A'"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="df-panel p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="mb-1">🔒 ETH Staking Total</h6>
                            <small class="text-secondary">Total ETH staked on beacon chain</small>
                        </div>
                        <span x-show="loadingStates.cqETHStaking" class="spinner-border spinner-border-sm text-primary"></span>
                    </div>
                    <div style="height: 250px; position: relative;">
                        <canvas x-ref="cqETHStakingChart"></canvas>
                    </div>
                    <div class="mt-2 d-flex justify-content-around text-center" x-show="cryptoquant.ethStaking.length > 0">
                        <div>
                            <small class="text-muted">Total Staked</small>
                            <div class="fw-bold" x-text="(cryptoquant.ethStaking[0]?.staking_total / 1e6 || 0).toFixed(2) + 'M ETH'"></div>
                        </div>
                        <div>
                            <small class="text-muted">Date</small>
                            <div class="fw-bold" x-text="cryptoquant.ethStaking[0]?.date || 'N/A'"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CryptoQuant Charts Row 3: Price OHLCV -->
        <div class="row g-3 mt-2">
            <div class="col-12">
                <div class="df-panel p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="mb-1">📊 BTC Price OHLCV (CryptoQuant)</h6>
                            <small class="text-secondary">Historical price data with OHLCV</small>
                        </div>
                        <span x-show="loadingStates.cqPrice" class="spinner-border spinner-border-sm text-primary"></span>
                    </div>
                    <div style="height: 300px; position: relative;">
                        <canvas x-ref="cqPriceChart"></canvas>
                    </div>
                    <div class="mt-2 d-flex justify-content-around text-center" x-show="cryptoquant.priceOHLCV.length > 0">
                        <div>
                            <small class="text-muted">Latest Close</small>
                            <div class="fw-bold text-primary" x-text="'$' + (cryptoquant.priceOHLCV[0]?.close || 0).toLocaleString()"></div>
                        </div>
                        <div>
                            <small class="text-muted">High</small>
                            <div class="fw-bold text-success" x-text="'$' + (cryptoquant.priceOHLCV[0]?.high || 0).toLocaleString()"></div>
                        </div>
                        <div>
                            <small class="text-muted">Low</small>
                            <div class="fw-bold text-danger" x-text="'$' + (cryptoquant.priceOHLCV[0]?.low || 0).toLocaleString()"></div>
                        </div>
                        <div>
                            <small class="text-muted">Volume</small>
                            <div class="fw-bold" x-text="(cryptoquant.priceOHLCV[0]?.volume || 0).toLocaleString()"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/date-fns@2.29.3/index.min.js"></script>
    <script src="<?php echo e(asset('js/onchain-metrics-controller.js')); ?>"></script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\DATASAID\Said\Bisnis\quantwaru\frontend\dragonfortuneai-frontend\resources\views/onchain-metrics/dashboard.blade.php ENDPATH**/ ?>