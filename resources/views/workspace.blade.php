@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100">
        <!-- Trading Chart Section -->
        <section class="df-panel flex-grow-1 p-0 overflow-hidden">
            <div class="df-chart-container" id="tradingChart" style="min-height: 520px;">
                <!-- Chart will be rendered here -->
            </div>
        </section>

        <!-- Price Info Panel -->
        <div class="df-panel mt-3 p-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-muted small">Last Price</div>
                    <div class="h4 fw-bold mb-0" id="lastPrice">$65,420.00</div>
                    <div class="text-success small" id="priceChange">+1,250.00 (+1.95%)</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">24h High</div>
                    <div class="fw-semibold" id="high24h">$66,800.00</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">24h Low</div>
                    <div class="fw-semibold" id="low24h">$64,200.00</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Volume</div>
                    <div class="fw-semibold" id="volume24h">28.5B BTC</div>
                </div>
            </div>
        </div>
    </div>

    <!-- TradingView Widget Script -->
    <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            new TradingView.widget({
                "autosize": true,
                "symbol": "BINANCE:BTCUSDT",
                "interval": "D",
                "timezone": "Etc/UTC",
                "theme": "dark",
                "style": "1",
                "locale": "en",
                "toolbar_bg": "#1e293b",
                "enable_publishing": false,
                "withdateranges": true,
                "range": "1M",
                "hide_side_toolbar": false,
                "allow_symbol_change": true,
                "details": true,
                "hotlist": true,
                "calendar": false,
                "studies": [
                    "RSI@tv-basicstudies",
                    "MACD@tv-basicstudies"
                ],
                "container_id": "tradingChart"
            });

            // Simulate real-time price updates
            function updatePrice() {
                const basePrice = 65420;
                const change = (Math.random() - 0.5) * 2000;
                const newPrice = basePrice + change;
                const changePercent = (change / basePrice) * 100;

                document.getElementById('lastPrice').textContent = '$' + newPrice.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                const changeElement = document.getElementById('priceChange');
                const changeText = (change >= 0 ? '+' : '') + change.toFixed(2) + ' (' +
                                 (changePercent >= 0 ? '+' : '') + changePercent.toFixed(2) + '%)';
                changeElement.textContent = changeText;
                changeElement.className = changePercent >= 0 ? 'text-success small' : 'text-danger small';
            }

            // Update price every 2 seconds
            setInterval(updatePrice, 2000);
        });
    </script>
@endsection
