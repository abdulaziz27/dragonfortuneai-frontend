@extends('layouts.app')

@section('title', 'On-Chain Metrics | DragonFortune')

@section('content')
    <div class="d-flex flex-column gap-4" x-data="onchainMetricsController()" x-init="init()">
        <!-- Header -->
        <div class="derivatives-header">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">On-Chain Metrics</h1>
                        <span class="pulse-dot" :class="loading ? 'pulse-warning' : 'pulse-success'"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Live Bitcoin chain health: valuation ratios, realized profitability, exchange flows, network usage, and price structure.
                    </p>
                    <div class="small text-muted mt-1" x-show="lastUpdated">
                        <i class="fas fa-clock me-1"></i>
                        <span>Updated </span><span x-text="lastUpdated"></span>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <select class="form-select" style="width: 140px;" x-model.number="selectedLimit" @change="refreshAll()">
                        <option value="30">30 days</option>
                        <option value="60">60 days</option>
                        <option value="90">90 days</option>
                        <option value="120">120 days</option>
                    </select>
                    <button class="btn btn-outline-secondary" :disabled="loading" @click="refreshAll()">
                        <span x-show="!loading">🔄 Refresh</span>
                        <span x-show="loading" class="spinner-border spinner-border-sm"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3">
            <template x-for="card in summaryCards" :key="card.title">
                <div class="col-xl-3 col-md-6">
                    <div class="df-panel h-100 p-4 position-relative overflow-hidden" :style="card.background">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="text-uppercase small fw-semibold text-muted" x-text="card.title"></div>
                                <div class="display-6 fw-bold mt-2" x-text="card.value"></div>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:rgba(255,255,255,0.18);">
                                <span class="fs-4" x-text="card.icon"></span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-3">
                            <span class="fw-semibold" :class="card.deltaClass" x-text="card.delta"></span>
                            <span class="small text-muted" x-text="card.deltaNote"></span>
                        </div>
                        <div class="small mt-3 text-secondary" x-text="card.footer"></div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Valuation + Insights -->
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="df-panel p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">Valuation & Profitability</h5>
                            <small class="text-secondary">MVRV and SOPR give context on realized gains vs losses</small>
                        </div>
                        <span x-show="loadingStates.mvrv || loadingStates.sopr" class="spinner-border spinner-border-sm text-primary"></span>
                    </div>
                    <div class="mb-4" style="height: 240px; position: relative;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-semibold">MVRV Ratio</span>
                            <span class="badge bg-info bg-opacity-10 text-info" x-text="getMvrvLabel(stats.mvrv)"></span>
                        </div>
                        <canvas x-ref="mvrvChart"></canvas>
                    </div>
                    <div style="height: 220px; position: relative;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-semibold">SOPR Breakdown</span>
                            <span class="small text-muted">LTH vs STH realized profits</span>
                        </div>
                        <canvas x-ref="soprChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="df-panel p-4 h-100">
                    <h5 class="mb-3">Key Insights</h5>
                    <div class="d-flex flex-column gap-3" x-show="insights.length">
                        <template x-for="insight in insights" :key="insight.title">
                            <div class="d-flex gap-3">
                                <div class="fs-3 lh-1" x-text="insight.icon"></div>
                                <div>
                                    <div class="fw-semibold" x-text="insight.title"></div>
                                    <div class="small text-secondary" x-text="insight.body"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="text-center text-muted small" x-show="!insights.length">
                        Insights appear after the first successful refresh.
                    </div>
                </div>
            </div>
        </div>

        <!-- Exchange Flows -->
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="df-panel p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">Exchange Netflow by Venue</h5>
                            <small class="text-secondary">Negative values indicate BTC leaving exchanges (bullish)</small>
                        </div>
                        <span x-show="loadingStates.exchangeFlows" class="spinner-border spinner-border-sm text-primary"></span>
                    </div>
                    <div style="height: 360px; position: relative;">
                        <canvas x-ref="exchangeFlowChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="df-panel p-4 h-100">
                    <h6 class="mb-3">Exchange Leaderboard</h6>
                    <div class="d-flex justify-content-between small text-muted mb-2">
                        <span>Exchange</span>
                        <span>24h Netflow (BTC)</span>
                    </div>
                    <div class="df-scrollbar" style="max-height: 320px;">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <template x-for="item in exchangeSummary" :key="item.exchange">
                                    <tr>
                                        <td class="small fw-semibold" x-text="item.exchange"></td>
                                        <td class="text-end small">
                                            <span :class="item.total < 0 ? 'text-success' : item.total > 0 ? 'text-danger' : 'text-muted'"
                                                  x-text="formatNumber(item.total, 0)"></span>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="!exchangeSummary.length && !loadingStates.exchangeFlows">
                                    <td colspan="2" class="text-center text-muted small py-3">Data unavailable</td>
                                </tr>
                                <tr x-show="loadingStates.exchangeFlows">
                                    <td colspan="2" class="text-center text-muted small py-3">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Network & Price -->
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="df-panel p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">Network Transactions</h5>
                            <small class="text-secondary">Total transactions per day</small>
                        </div>
                        <span x-show="loadingStates.transactions" class="spinner-border spinner-border-sm text-primary"></span>
                    </div>
                    <div style="height: 280px; position: relative;">
                        <canvas x-ref="transactionsChart"></canvas>
                    </div>
                    <div class="d-flex justify-content-between small text-secondary mt-3">
                        <div>Latest: <span class="fw-semibold" x-text="formatNumber(stats.transactions, 0)">--</span></div>
                        <div>
                            Change:
                            <span class="fw-semibold" :class="stats.transactionsDeltaClass" x-text="stats.transactionsDeltaText"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="df-panel p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">BTC Price Structure</h5>
                            <small class="text-secondary">Daily close prices with volume context</small>
                        </div>
                        <span x-show="loadingStates.price" class="spinner-border spinner-border-sm text-primary"></span>
                    </div>
                    <div style="height: 280px; position: relative;">
                        <canvas x-ref="priceChart"></canvas>
                    </div>
                    <div class="d-flex justify-content-between small text-secondary mt-3">
                        <div>Close: <span class="fw-semibold text-primary" x-text="formatCurrency(stats.priceClose)">--</span></div>
                        <div>
                            Change:
                            <span class="fw-semibold" :class="stats.priceDeltaClass" x-text="stats.priceDeltaText"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/date-fns@2.30.0/index.min.js"></script>
    <script src="{{ asset('js/onchain-metrics-controller.js') }}?v={{ filemtime(public_path('js/onchain-metrics-controller.js')) }}"></script>
@endsection
