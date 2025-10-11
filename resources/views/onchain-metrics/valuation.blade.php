@extends('layouts.app')

@section('content')
<div class="container-fluid" x-data="valuationModule()">
    @include('onchain-metrics.partials.global-controls')
    @include('onchain-metrics.partials.module-nav')

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-4">
            <div class="df-panel p-3 shadow-sm rounded h-100">
                <span class="text-uppercase text-muted small fw-semibold d-block mb-1">Latest MVRV</span>
                <div class="fs-4 fw-bold text-dark" x-text="metrics.mvrv"></div>
                <span class="small text-muted" x-text="metrics.mvrvDelta"></span>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="df-panel p-3 shadow-sm rounded h-100">
                <span class="text-uppercase text-muted small fw-semibold d-block mb-1">Z-Score Status</span>
                <div class="fs-4 fw-bold text-dark" x-text="metrics.zScore"></div>
                <span class="small text-muted" x-text="metrics.zScoreDelta"></span>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="df-panel p-3 shadow-sm rounded h-100">
                <span class="text-uppercase text-muted small fw-semibold d-block mb-1">SOPR Signal</span>
                <div class="fs-4 fw-bold text-dark" x-text="metrics.sopr"></div>
                <span class="small text-muted" x-text="metrics.soprNarrative"></span>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="df-panel p-4 shadow-sm rounded h-100 d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <h3 class="h5 mb-1">MVRV & Z-Score Trend</h3>
                        <p class="text-muted small mb-0">Dual valuation lens measuring profit sensitivity and extreme deviations</p>
                    </div>
                    <span class="badge bg-light text-dark border">Dynamic</span>
                </div>
                <div class="flex-grow-1 position-relative">
                    <canvas x-ref="mvrvChart" style="max-height: 360px;"></canvas>
                </div>
                <div class="mt-3">
                    <div class="p-3 rounded bg-light">
                        <span class="text-uppercase small fw-semibold text-muted d-block mb-1">Insight</span>
                        <p class="mb-0 small" x-text="insights.mvrv"></p>
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
                        <h3 class="h5 mb-1">Reserve Risk & SOPR Overview</h3>
                        <p class="text-muted small mb-0">Confidence and profit-taking behaviour across the selected asset</p>
                    </div>
                    <span class="badge bg-light text-dark border">Multi-line</span>
                </div>
                <div class="flex-grow-1">
                    <canvas x-ref="reserveChart" style="max-height: 360px;"></canvas>
                </div>
                <div class="mt-3">
                    <div class="p-3 rounded bg-light">
                        <span class="text-uppercase small fw-semibold text-muted d-block mb-1">Insight</span>
                        <p class="mb-0 small" x-text="insights.reserve"></p>
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
                        <h3 class="h5 mb-1">Dormancy & CDD (Coin Days Destroyed)</h3>
                        <p class="text-muted small mb-0">Bar-line overlay showing coin age destruction vs dormant supply movement</p>
                    </div>
                    <span class="badge bg-light text-dark border">Overlay</span>
                </div>
                <div class="flex-grow-1">
                    <canvas x-ref="cddChart" style="max-height: 360px;"></canvas>
                </div>
                <div class="mt-3">
                    <div class="p-3 rounded bg-light">
                        <span class="text-uppercase small fw-semibold text-muted d-block mb-1">Insight</span>
                        <p class="mb-0 small" x-text="insights.cdd"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
