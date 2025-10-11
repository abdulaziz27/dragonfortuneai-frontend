@extends('layouts.app')

@section('content')
<div class="container-fluid" x-data="flowsModule()">
    @include('onchain-metrics.partials.global-controls')
    @include('onchain-metrics.partials.module-nav')

    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-4">
            <div class="df-panel p-3 shadow-sm rounded h-100">
                <span class="text-uppercase text-muted small fw-semibold d-block mb-1">Exchange Netflow</span>
                <div class="fs-4 fw-bold text-dark" x-text="metrics.netflow"></div>
                <span class="small text-muted" x-text="metrics.netflowTone"></span>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="df-panel p-3 shadow-sm rounded h-100">
                <span class="text-uppercase text-muted small fw-semibold d-block mb-1">Stablecoin Netflow</span>
                <div class="fs-4 fw-bold text-dark" x-text="metrics.stablecoinNet"></div>
                <span class="small text-muted" x-text="metrics.stablecoinTone"></span>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="df-panel p-3 shadow-sm rounded h-100">
                <span class="text-uppercase text-muted small fw-semibold d-block mb-1">Dominant Venue</span>
                <div class="fs-5 fw-bold text-dark" x-text="metrics.dominantVenue"></div>
                <span class="small text-muted">Leading venue flow bias</span>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="df-panel p-4 shadow-sm rounded h-100 d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <h3 class="h5 mb-1">Exchange Netflow by Asset</h3>
                        <p class="text-muted small mb-0">Directional pressure measured by net inflow versus outflow</p>
                    </div>
                    <span class="badge bg-light text-dark border">Bar</span>
                </div>
                <div class="flex-grow-1">
                    <canvas x-ref="netflowChart" style="max-height: 320px;"></canvas>
                </div>
                <div class="mt-3">
                    <div class="p-3 rounded bg-light">
                        <span class="text-uppercase small fw-semibold text-muted d-block mb-1">Insight</span>
                        <p class="mb-0 small" x-text="insights.netflow"></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="df-panel p-4 shadow-sm rounded h-100 d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <h3 class="h6 mb-1">Exchange Breakdown</h3>
                        <p class="text-muted small mb-0">Relative venue contribution</p>
                    </div>
                </div>
                <div class="table-responsive flex-grow-1">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr class="text-muted small">
                                <th scope="col">Exchange</th>
                                <th scope="col" class="text-end">Netflow</th>
                                <th scope="col" class="text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="row in exchangeRows" :key="row.venue">
                                <tr>
                                    <td x-text="row.venue"></td>
                                    <td class="text-end">
                                        <span :class="row.netflow >= 0 ? 'text-danger fw-semibold' : 'text-success fw-semibold'"
                                              x-text="`${row.netflow >= 0 ? '+' : ''}${row.netflow.toFixed(2)}%`"></span>
                                    </td>
                                    <td class="text-end"
                                        x-text="`${row.balance.toFixed(2)}M`"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-0">
        <div class="col-12 col-lg-6">
            <div class="df-panel p-4 shadow-sm rounded h-100 d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <h3 class="h5 mb-1">Stablecoin Liquidity Pulse</h3>
                        <p class="text-muted small mb-0">Liquidity runway inferred from stablecoin circulation</p>
                    </div>
                    <span class="badge bg-light text-dark border">Line</span>
                </div>
                <div class="flex-grow-1">
                    <canvas x-ref="stablecoinChart" style="max-height: 300px;"></canvas>
                </div>
                <div class="mt-3">
                    <div class="p-3 rounded bg-light">
                        <span class="text-uppercase small fw-semibold text-muted d-block mb-1">Insight</span>
                        <p class="mb-0 small" x-text="insights.liquidity"></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="df-panel p-4 shadow-sm rounded h-100 d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <h3 class="h5 mb-1">Exchange Comparison Heatmap</h3>
                        <p class="text-muted small mb-0">Venue intensity map to spot liquidity rotations</p>
                    </div>
                    <span class="badge bg-light text-dark border">Heatmap</span>
                </div>
                <div class="flex-grow-1">
                    <canvas x-ref="heatmapChart" style="max-height: 300px;"></canvas>
                </div>
                <div class="mt-3">
                    <div class="p-3 rounded bg-light">
                        <span class="text-uppercase small fw-semibold text-muted d-block mb-1">Insight</span>
                        <p class="mb-0 small" x-text="insights.heatmap"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
