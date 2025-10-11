@extends('layouts.app')

@section('content')
<div class="container-fluid" x-data="minersWhalesModule()">
    @include('onchain-metrics.partials.global-controls')
    @include('onchain-metrics.partials.module-nav')

    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-4">
            <div class="df-panel p-3 shadow-sm rounded h-100">
                <span class="text-uppercase text-muted small fw-semibold d-block mb-1">Miner Reserve</span>
                <div class="fs-4 fw-bold text-dark" x-text="metrics.minerReserve"></div>
                <span class="small text-muted" x-text="metrics.minerTrend"></span>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="df-panel p-3 shadow-sm rounded h-100">
                <span class="text-uppercase text-muted small fw-semibold d-block mb-1">Puell Multiple</span>
                <div class="fs-4 fw-bold text-dark" x-text="metrics.puell"></div>
                <span class="small text-muted" x-text="metrics.puellTone"></span>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="df-panel p-3 shadow-sm rounded h-100">
                <span class="text-uppercase text-muted small fw-semibold d-block mb-1">Whale Momentum</span>
                <div class="fs-5 fw-bold text-dark" x-text="metrics.whaleLeader"></div>
                <span class="small text-muted">Largest cohort delta</span>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="df-panel p-4 shadow-sm rounded h-100 d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <h3 class="h5 mb-1">Miner Reserve vs Puell Multiple</h3>
                        <p class="text-muted small mb-0">Overlay of reserve drawdown against miner profitability</p>
                    </div>
                    <span class="badge bg-light text-dark border">Dual-line</span>
                </div>
                <div class="flex-grow-1">
                    <canvas x-ref="minerChart" style="max-height: 340px;"></canvas>
                </div>
                <div class="mt-3">
                    <div class="p-3 rounded bg-light">
                        <span class="text-uppercase small fw-semibold text-muted d-block mb-1">Insight</span>
                        <p class="mb-0 small" x-text="insights.miner"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="df-panel p-4 shadow-sm rounded h-100 d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <h3 class="h5 mb-1">Whale Holdings by Cohort</h3>
                        <p class="text-muted small mb-0">Live view into strategic accumulation versus exchange balances</p>
                    </div>
                    <span class="badge bg-light text-dark border">Multi-line</span>
                </div>
                <div class="flex-grow-1">
                    <canvas x-ref="whaleChart" style="max-height: 360px;"></canvas>
                </div>
                <div class="mt-3">
                    <div class="p-3 rounded bg-light">
                        <span class="text-uppercase small fw-semibold text-muted d-block mb-1">Insight</span>
                        <p class="mb-0 small" x-text="insights.whale"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="df-panel p-4 shadow-sm rounded h-100 d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <h3 class="h5 mb-1">Net Whale Position Change</h3>
                        <p class="text-muted small mb-0">Bar chart for daily change, with cumulative line overlay</p>
                    </div>
                    <span class="badge bg-light text-dark border">Bar + Line</span>
                </div>
                <div class="flex-grow-1">
                    <canvas x-ref="whaleChangeChart" style="max-height: 360px;"></canvas>
                </div>
                <div class="mt-3">
                    <div class="p-3 rounded bg-light">
                        <span class="text-uppercase small fw-semibold text-muted d-block mb-1">Insight</span>
                        <p class="mb-0 small" x-text="insights.whaleChange"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
