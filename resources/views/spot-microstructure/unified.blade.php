@extends('layouts.app')

@section('title', 'Spot Microstructure | DragonFortune')

@section('content')
<div class="container-fluid py-4 spot-microstructure-page">
    <div class="d-flex flex-column gap-3">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">Spot Microstructure</h1>
                        <span class="pulse-dot pulse-success"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Monitor spot orderflow, cumulative delta, VWAP envelopes, and liquidity pressure sourced directly from the backend service.
                    </p>
                    <small class="text-secondary">Data refreshed every 20 seconds - values are delivered as-is from provider.</small>
                </div>

                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <select id="spotSymbolSelect" class="form-select" style="width: 140px;" aria-label="Spot symbol">
                        <option value="BTC/USDT">BTC / USDT</option>
                        <option value="ETH/USDT">ETH / USDT</option>
                        <option value="SOL/USDT">SOL / USDT</option>
                        <option value="BNB/USDT">BNB / USDT</option>
                    </select>

                    <select id="spotExchangeSelect" class="form-select" style="width: 160px;" aria-label="Exchange">
                        <option value="binance">Binance</option>
                        <option value="coinbase">Coinbase</option>
                        <option value="kraken">Kraken</option>
                        <option value="okx">OKX</option>
                    </select>

                    <button id="spotRefreshButton" class="btn btn-outline-light">
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3">
            <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
                <div class="df-panel p-3 h-100">
                    <span class="small text-secondary text-uppercase fw-semibold">Last Trade Price</span>
                    <div class="h3 fw-semibold mb-1" id="spotLastPrice">-</div>
                    <small class="text-secondary">Updated <span id="spotLastPriceTime">-</span></small>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
                <div class="df-panel p-3 h-100">
                    <span class="small text-secondary text-uppercase fw-semibold">Buy / Sell Bias</span>
                    <div class="h3 fw-semibold mb-2" id="spotTradeBias">-</div>
                    <span class="badge text-bg-secondary" id="spotBiasStrength">-</span>
                    <small class="text-warning d-none mt-2 d-block" id="spotBiasCardNotice">Data belum tersedia dari provider.</small>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
                <div class="df-panel p-3 h-100">
                    <span class="small text-secondary text-uppercase fw-semibold">CVD Delta</span>
                    <div class="h3 fw-semibold mb-2" id="spotCvdDelta">-</div>
                    <small class="text-secondary">Across latest series</small>
                    <small class="text-warning d-none mt-2 d-block" id="spotCvdCardNotice">Data belum tersedia dari provider.</small>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
                <div class="df-panel p-3 h-100">
                    <span class="small text-secondary text-uppercase fw-semibold">VWAP Signal</span>
                    <div class="h3 fw-semibold mb-2" id="spotVwapSignal">-</div>
                    <small class="text-secondary">Position: <span id="spotVwapPosition">-</span></small>
                    <small class="text-warning d-none mt-2 d-block" id="spotVwapCardNotice">Data belum tersedia dari provider.</small>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
                <div class="df-panel p-3 h-100">
                    <span class="small text-secondary text-uppercase fw-semibold">Orderbook Spread</span>
                    <div class="h3 fw-semibold mb-2" id="spotSpread">-</div>
                    <small class="text-secondary">Depth <span id="spotOrderbookDepth">-</span></small>
                    <small class="text-warning d-none mt-2 d-block" id="spotOrderbookCardNotice">Data belum tersedia dari provider.</small>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
                <div class="df-panel p-3 h-100">
                    <span class="small text-secondary text-uppercase fw-semibold">Pressure Ratio</span>
                    <div class="h3 fw-semibold mb-2" id="spotPressureRatio">-</div>
                    <small class="text-secondary">Latest imbalance: <span id="spotPressureImbalance">-</span></small>
                    <small class="text-warning d-none mt-2 d-block" id="spotPressureCardNotice">Data belum tersedia dari provider.</small>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="row g-3">
            <div class="col-12 col-xl-6">
                <div class="df-panel h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom border-dark-subtle">
                        <div>
                            <h5 class="mb-0">Cumulative Volume Delta</h5>
                            <small class="text-secondary">Net aggressive flow (buy vs sell)</small>
                        </div>
                        <span class="badge text-bg-primary" id="spotCvdPoints">0 pts</span>
                    </div>
                    <div class="position-relative p-3 pt-4 flex-grow-1">
                        <canvas id="spotCvdChart" height="260"></canvas>
                        <div class="position-absolute top-50 start-50 translate-middle text-warning small text-center d-none" id="spotCvdNotice">
                            Data belum tersedia dari provider.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="df-panel h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom border-dark-subtle">
                        <div>
                            <h5 class="mb-0">VWAP Bands</h5>
                            <small class="text-secondary">Price vs VWAP envelopes</small>
                        </div>
                        <span class="badge text-bg-info" id="spotVwapCount">0 pts</span>
                    </div>
                    <div class="position-relative p-3 pt-4 flex-grow-1">
                        <canvas id="spotVwapChart" height="260"></canvas>
                        <div class="position-absolute top-50 start-50 translate-middle text-warning small text-center d-none" id="spotVwapNotice">
                            Data belum tersedia dari provider.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="row g-3">
            <div class="col-12 col-xl-6">
                <div class="df-panel h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom border-dark-subtle">
                        <div>
                            <h5 class="mb-0">Book Pressure</h5>
                            <small class="text-secondary">Bid vs ask dominance</small>
                        </div>
                        <span class="badge text-bg-warning" id="spotBookPressureCount">0 pts</span>
                    </div>
                    <div class="position-relative p-3 pt-4 flex-grow-1">
                        <canvas id="spotBookPressureChart" height="260"></canvas>
                        <div class="position-absolute top-50 start-50 translate-middle text-warning small text-center d-none" id="spotBookPressureNotice">
                            Data belum tersedia dari provider.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="df-panel h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom border-dark-subtle">
                        <div>
                            <h5 class="mb-0">Trade Summary</h5>
                            <small class="text-secondary">Volume buckets (buy vs sell)</small>
                        </div>
                        <span class="badge text-bg-success" id="spotTradeSummaryCount">0 buckets</span>
                    </div>
                    <div class="position-relative p-3 pt-4 flex-grow-1">
                        <canvas id="spotTradeSummaryChart" height="260"></canvas>
                        <div class="position-absolute top-50 start-50 translate-middle text-warning small text-center d-none" id="spotTradeSummaryNotice">
                            Data belum tersedia dari provider.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables -->
        <div class="row g-3">
            <div class="col-12 col-xl-6">
                <div class="df-panel h-100">
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom border-dark-subtle">
                        <h5 class="mb-0">Recent Trades</h5>
                        <small class="text-secondary">Latest 50 records</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-dark table-striped table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Side</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody id="spotTradesBody">
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Loading trades...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="df-panel h-100">
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom border-dark-subtle">
                        <h5 class="mb-0">Orderbook Snapshot</h5>
                        <small class="text-secondary" id="spotOrderbookTimestamp">-</small>
                    </div>
                    <div class="row g-0">
                        <div class="col-md-6 border-end border-dark-subtle">
                            <div class="table-responsive p-3">
                                <h6 class="text-success mb-2">Bids</h6>
                                <table class="table table-dark table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Price</th>
                                            <th>Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody id="spotBidsBody">
                                        <tr><td colspan="2" class="text-center text-muted py-3">Loading bids...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="table-responsive p-3">
                                <h6 class="text-danger mb-2">Asks</h6>
                                <table class="table table-dark table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Price</th>
                                            <th>Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody id="spotAsksBody">
                                        <tr><td colspan="2" class="text-center text-muted py-3">Loading asks...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-xl-6">
                <div class="df-panel h-100">
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom border-dark-subtle">
                        <h5 class="mb-0">Volume Profile</h5>
                        <small class="text-secondary">Lookback detail</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-dark table-striped align-middle mb-0">
                            <tbody id="spotVolumeProfileBody">
                                <tr>
                                    <td class="text-muted text-center py-4">Loading volume profile...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="df-panel h-100">
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom border-dark-subtle">
                        <h5 class="mb-0">Book Pressure Table</h5>
                        <small class="text-secondary">Recent ratios</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-dark table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Bid Pressure</th>
                                    <th>Ask Pressure</th>
                                    <th>Ratio</th>
                                </tr>
                            </thead>
                            <tbody id="spotBookPressureBody">
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Loading book pressure...</td>
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
<script src="{{ asset('js/spot-microstructure-unified.js') }}"></script>
@endsection

