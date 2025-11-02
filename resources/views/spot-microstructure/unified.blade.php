@extends('layouts.app')

@section('title', 'Spot Microstructure | DragonFortune')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Spot Microstructure</h1>
                    <p class="text-muted mb-0">Real-time raw data from providers - no processing, no filtering</p>
                </div>
                <div class="d-flex gap-2">
                    <select id="symbolSelect" class="form-select form-select-sm" style="width: auto;">
                        <option value="BTCUSDT">BTC/USDT</option>
                        <option value="ETHUSDT">ETH/USDT</option>
                        <option value="ADAUSDT">ADA/USDT</option>
                        <option value="SOLUSDT">SOL/USDT</option>
                    </select>
                    <select id="exchangeSelect" class="form-select form-select-sm" style="width: auto;">
                        <option value="binance">Binance</option>
                        <option value="coinbase">Coinbase</option>
                        <option value="kraken">Kraken</option>
                        <option value="okx">OKX</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 1: Raw Trades (for CVD & Buy/Sell Ratio) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Raw Trades (CVD & Buy/Sell Ratio)</h5>
                    <small class="text-muted">Fields: ts, exchange, pair, side, qty, price | Cadence: real-time</small>
                </div>
                <div class="card-body">
                    <!-- CVD Chart -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div id="cvdChart" style="height: 300px;"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Buy/Sell Ratio</h6>
                                            <h4 id="buySellRatio" class="text-primary">-</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">CVD Delta</h6>
                                            <h4 id="cvdDelta" class="text-success">-</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Trades Table -->
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Exchange</th>
                                    <th>Pair</th>
                                    <th>Side</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Notional</th>
                                </tr>
                            </thead>
                            <tbody id="tradesTableBody">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Loading trades data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: VWAP/TWAP (calculated from trades) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">VWAP/TWAP (Raw)</h5>
                    <small class="text-muted">Calculated from trades - not stored | Real-time computation | Note: Values may appear identical due to aggregated spot flow data</small>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Current VWAP</h6>
                                    <h4 id="currentVWAP" class="text-primary">-</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Current TWAP</h6>
                                    <h4 id="currentTWAP" class="text-info">-</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">VWAP Deviation</h6>
                                    <h4 id="vwapDeviation" class="text-warning">-</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Market Price</h6>
                                    <h4 id="marketPrice" class="text-dark">-</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="vwapChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Volume & Trade Stats -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Volume & Trade Stats</h5>
                    <small class="text-muted">Fields: ts, exchange, pair, trades_count, volume_base, volume_quote, avg_trade_size | Cadence: 1-5m</small>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Trades Count</h6>
                                    <h4 id="tradesCount" class="text-primary">-</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Volume Base</h6>
                                    <h4 id="volumeBase" class="text-success">-</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Volume Quote</h6>
                                    <h4 id="volumeQuote" class="text-info">-</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Avg Trade Size</h6>
                                    <h4 id="avgTradeSize" class="text-warning">-</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="volumeChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Large Orders Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Large Orders & Institutional Flow</h5>
                    <small class="text-muted">Real provider data - CoinGlass & CryptoQuant integration</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Exchange</th>
                                    <th>Symbol</th>
                                    <th>Side</th>
                                    <th>Size</th>
                                    <th>Price</th>
                                    <th>Notional USD</th>
                                    <th>Source</th>
                                </tr>
                            </thead>
                            <tbody id="largeOrdersTableBody">
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Loading large orders data...</td>
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
<script src="https://cdn.plot.ly/plotly-2.26.0.min.js"></script>
<script src="{{ asset('js/spot-microstructure-unified.js') }}"></script>
@endsection