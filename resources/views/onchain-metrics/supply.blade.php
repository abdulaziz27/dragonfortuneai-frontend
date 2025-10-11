@extends('layouts.app')

@section('content')
<div class="container-fluid" x-data="supplyModule()">
    @include('onchain-metrics.partials.global-controls')
    @include('onchain-metrics.partials.module-nav')

    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-4">
            <div class="df-panel p-3 shadow-sm rounded h-100">
                <span class="text-uppercase text-muted small fw-semibold d-block mb-1">LTH Share</span>
                <div class="fs-4 fw-bold text-dark" x-text="metrics.lthShare"></div>
                <span class="small text-muted" x-text="metrics.lthTrend"></span>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="df-panel p-3 shadow-sm rounded h-100">
                <span class="text-uppercase text-muted small fw-semibold d-block mb-1">STH Share</span>
                <div class="fs-4 fw-bold text-dark" x-text="metrics.sthShare"></div>
                <span class="small text-muted" x-text="metrics.sthTrend"></span>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="df-panel p-3 shadow-sm rounded h-100">
                <span class="text-uppercase text-muted small fw-semibold d-block mb-1">Realized Cap</span>
                <div class="fs-4 fw-bold text-dark" x-text="metrics.realizedCap"></div>
                <span class="small text-muted" x-text="metrics.realizedCapTrend"></span>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="df-panel p-4 shadow-sm rounded h-100 d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <h3 class="h5 mb-1">LTH vs STH Supply Breakdown</h3>
                        <p class="text-muted small mb-0">Stacked holder structure showing proportions over time</p>
                    </div>
                    <span class="badge bg-light text-dark border">Stacked</span>
                </div>
                <div class="flex-grow-1">
                    <canvas x-ref="supplyChart" style="max-height: 360px;"></canvas>
                </div>
                <div class="mt-3">
                    <div class="p-3 rounded bg-light">
                        <span class="text-uppercase small fw-semibold text-muted d-block mb-1">Insight</span>
                        <p class="mb-0 small" x-text="insights.supply"></p>
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
                        <h3 class="h5 mb-1">Realized Cap Trend</h3>
                        <p class="text-muted small mb-0">Line chart showing realized capitalization growth across cycles</p>
                    </div>
                    <span class="badge bg-light text-dark border">Line</span>
                </div>
                <div class="flex-grow-1">
                    <canvas x-ref="realizedCapChart" style="max-height: 340px;"></canvas>
                </div>
                <div class="mt-3">
                    <div class="p-3 rounded bg-light">
                        <span class="text-uppercase small fw-semibold text-muted d-block mb-1">Insight</span>
                        <p class="mb-0 small" x-text="insights.realizedCap"></p>
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
                        <h3 class="h5 mb-1">HODL Waves (Coin Age Distribution)</h3>
                        <p class="text-muted small mb-0">Stacked gradient area chart showing age cohorts (<1M, 1-3M, 3-6M, 6-12M, 1-2Y, 2Y+)</p>
                    </div>
                    <span class="badge bg-light text-dark border">Gradient</span>
                </div>
                <div class="flex-grow-1">
                    <canvas x-ref="hodlChart" style="max-height: 360px;"></canvas>
                </div>
                <div class="mt-3">
                    <div class="p-3 rounded bg-light">
                        <span class="text-uppercase small fw-semibold text-muted d-block mb-1">Insight</span>
                        <p class="mb-0 small" x-text="insights.hodl"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
