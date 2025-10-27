<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'workspace')->name('workspace');
Route::view('/login', 'auth.login')->name('login');

// Profile & Auth Routes
Route::view('/profile', 'profile.show')->name('profile.show');
Route::post('/logout', function () {
    // Logout logic akan ditambahkan nanti
    return redirect()->route('login');
})->name('logout');

// Derivatives Core Routes
Route::view('/derivatives/funding-rate', 'derivatives.funding-rate-exact')->name('derivatives.funding-rate');
Route::view('/derivatives/open-interest', 'derivatives.open-interest')->name('derivatives.open-interest');
Route::view('/derivatives/open-interest-old', 'derivatives.open-interest-old')->name('derivatives.open-interest-old');
Route::view('/derivatives/long-short-ratio', 'derivatives.long-short-ratio')->name('derivatives.long-short-ratio');

Route::view('/derivatives/liquidations', 'derivatives.liquidations')->name('derivatives.liquidations');
Route::view('/derivatives/basis-term-structure', 'derivatives.basis-term-structure')->name('derivatives.basis-term-structure');
Route::view('/derivatives/perp-quarterly-spread', 'derivatives.perp-quarterly-spread')->name('derivatives.perp-quarterly-spread');
Route::view('/derivatives/exchange-inflow-cdd', 'derivatives.exchange-inflow-cdd')->name('derivatives.exchange-inflow-cdd');

// Spot Microstructure Routes
Route::view('/spot-microstructure/trades', 'spot-microstructure.trades')->name('spot-microstructure.trades');
Route::view('/spot-microstructure/orderbook-snapshots', 'spot-microstructure.orderbook-snapshots')->name('spot-microstructure.orderbook-snapshots');
Route::view('/spot-microstructure/vwap-twap', 'spot-microstructure.vwap-twap')->name('spot-microstructure.vwap-twap');
Route::view('/spot-microstructure/volume-trade-stats', 'spot-microstructure.volume-trade-stats')->name('spot-microstructure.volume-trade-stats');

// On-Chain Metrics Routes (CryptoQuant integrated into main dashboard)
Route::view('/onchain-metrics', 'onchain-metrics.dashboard')->name('onchain-metrics.index');
Route::view('/onchain-metrics/dashboard', 'onchain-metrics.dashboard')->name('onchain-metrics.dashboard');

// Advanced On-Chain Metrics Routes
Route::view('/onchain-ethereum', 'onchain-ethereum.dashboard')->name('onchain-ethereum.dashboard');
Route::view('/onchain-exchange', 'onchain-exchange.dashboard')->name('onchain-exchange.dashboard');
Route::view('/onchain-mining-price', 'onchain-mining-price.dashboard')->name('onchain-mining-price.dashboard');

// Options Intelligence Routes
Route::view('/options-metrics/dashboard', 'options-metrics.dashboard')->name('options-metrics.dashboard');
Route::view('/options-metrics/test', 'options-metrics.test')->name('options-metrics.test');
Route::view('/options-metrics/implied-volatility', 'options-metrics.implied-volatility')->name('options-metrics.iv');
Route::view('/options-metrics/options-skew', 'options-metrics.options-skew')->name('options-metrics.skew');
Route::view('/options-metrics/gamma-exposure', 'options-metrics.gamma-exposure')->name('options-metrics.gex');
Route::view('/options-metrics/put-call-ratio', 'options-metrics.put-call-ratio')->name('options-metrics.pcr');

// ETF & Institutional Routes
Route::view('/etf-institutional/dashboard', 'etf-institutional.dashboard')->name('etf-institutional.dashboard');

// Volatility Regime Routes
Route::view('/volatility-regime/dashboard', 'volatility-regime.dashboard')->name('volatility-regime.dashboard');

// Macro Overlay Routes
Route::view('/macro-overlay', 'macro-overlay.raw-dashboard')->name('macro-overlay.index');
Route::view('/macro-overlay/dashboard', 'macro-overlay.raw-dashboard')->name('macro-overlay.dashboard');
Route::view('/macro-overlay/raw-dashboard', 'macro-overlay.raw-dashboard')->name('macro-overlay.raw-dashboard');
Route::view('/macro-overlay/dashboard-legacy', 'macro-overlay.dashboard-legacy')->name('macro-overlay.dashboard-legacy');

// Sentiment & Flow Routes
Route::view('/sentiment-flow/dashboard', 'sentiment-flow.dashboard')->name('sentiment-flow.dashboard');

// CryptoQuant API Proxy Routes
Route::get('/api/cryptoquant/exchange-inflow-cdd', [App\Http\Controllers\CryptoQuantController::class, 'getExchangeInflowCDD'])->name('api.cryptoquant.exchange-inflow-cdd');
Route::get('/api/cryptoquant/btc-market-price', [App\Http\Controllers\CryptoQuantController::class, 'getBitcoinPrice'])->name('api.cryptoquant.btc-market-price');
Route::get('/api/cryptoquant/btc-price', [App\Http\Controllers\CryptoQuantController::class, 'getBitcoinPrice'])->name('api.cryptoquant.btc-price');
Route::get('/api/cryptoquant/funding-rate', [App\Http\Controllers\CryptoQuantController::class, 'getFundingRates'])->name('api.cryptoquant.funding-rate');
Route::get('/api/cryptoquant/funding-rates', [App\Http\Controllers\CryptoQuantController::class, 'getFundingRates'])->name('api.cryptoquant.funding-rates');
Route::get('/api/cryptoquant/open-interest', [App\Http\Controllers\CryptoQuantController::class, 'getOpenInterest'])->name('api.cryptoquant.open-interest');
Route::get('/api/cryptoquant/funding-rates-comparison', [App\Http\Controllers\CryptoQuantController::class, 'getFundingRatesComparison'])->name('api.cryptoquant.funding-rates-comparison');

// Coinglass API Proxy Routes
Route::get('/api/coinglass/global-account-ratio', [App\Http\Controllers\CoinglassController::class, 'getGlobalAccountRatio'])->name('api.coinglass.global-account-ratio');
Route::get('/api/coinglass/top-account-ratio', [App\Http\Controllers\CoinglassController::class, 'getTopAccountRatio'])->name('api.coinglass.top-account-ratio');
Route::get('/api/coinglass/top-position-ratio', [App\Http\Controllers\CoinglassController::class, 'getTopPositionRatio'])->name('api.coinglass.top-position-ratio');
Route::get('/api/coinglass/net-position', [App\Http\Controllers\CoinglassController::class, 'getNetPosition'])->name('api.coinglass.net-position');
Route::get('/api/coinglass/taker-buy-sell', [App\Http\Controllers\CoinglassController::class, 'getTakerBuySell'])->name('api.coinglass.taker-buy-sell');
Route::get('/api/coinglass/liquidation-coin-list', [App\Http\Controllers\CoinglassController::class, 'getLiquidationCoinList'])->name('api.coinglass.liquidation-coin-list');
Route::get('/api/coinglass/liquidation-aggregated-history', [App\Http\Controllers\CoinglassController::class, 'getLiquidationAggregatedHistory'])->name('api.coinglass.liquidation-aggregated-history');
Route::get('/api/coinglass/liquidation-exchange-list', [App\Http\Controllers\CoinglassController::class, 'getLiquidationExchangeList'])->name('api.coinglass.liquidation-exchange-list');
Route::get('/api/coinglass/liquidation-history', [App\Http\Controllers\CoinglassController::class, 'getLiquidationHistory'])->name('api.coinglass.liquidation-history');
Route::get('/api/coinglass/liquidation-summary', [App\Http\Controllers\CoinglassController::class, 'getLiquidationSummary'])->name('api.coinglass.liquidation-summary');



// Chart Components Demo
Route::view('/examples/chart-components', 'examples.chart-components-demo')->name('examples.chart-components');

// Test Funding Rates API
Route::get('/test/funding-rates-debug', function() {
    try {
        $controller = new App\Http\Controllers\CryptoQuantController();
        $request = new Illuminate\Http\Request([
            'start_date' => now()->subDays(7)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'exchange' => 'binance'
        ]);
        
        return $controller->getFundingRates($request);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Test failed',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('test.funding-rates-debug');

// Test Open Interest API
Route::get('/test/open-interest-debug', function() {
    try {
        $controller = new App\Http\Controllers\CryptoQuantController();
        $request = new Illuminate\Http\Request([
            'start_date' => now()->subDays(7)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'exchange' => 'binance'
        ]);
        
        return $controller->getOpenInterest($request);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Test failed',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('test.open-interest-debug');

// Test CDD API
Route::get('/test/cdd-debug', function() {
    try {
        $controller = new App\Http\Controllers\CryptoQuantController();
        $request = new Illuminate\Http\Request([
            'start_date' => now()->subDays(7)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'exchange' => 'binance'
        ]);
        
        return $controller->getExchangeInflowCDD($request);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Test failed',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('test.cdd-debug');

// Test CDD API with different exchanges
Route::get('/test/cdd-all-exchanges', function() {
    try {
        $controller = new App\Http\Controllers\CryptoQuantController();
        $exchanges = ['binance', 'coinbase', 'kraken', 'bitfinex', 'huobi', 'okex', 'bybit', 'bitstamp', 'gemini'];
        $results = [];
        
        foreach ($exchanges as $exchange) {
            $request = new Illuminate\Http\Request([
                'start_date' => '2025-10-22',
                'end_date' => '2025-10-23',
                'exchange' => $exchange
            ]);
            
            try {
                $response = $controller->getExchangeInflowCDD($request);
                $data = $response->getData(true);
                
                if ($data['success'] && !empty($data['data'])) {
                    $oct22Data = collect($data['data'])->firstWhere('date', '2025-10-22');
                    $results[$exchange] = [
                        'success' => true,
                        'oct_22_value' => $oct22Data['value'] ?? 'No data',
                        'total_points' => count($data['data'])
                    ];
                } else {
                    $results[$exchange] = [
                        'success' => false,
                        'error' => 'No data returned'
                    ];
                }
            } catch (\Exception $e) {
                $results[$exchange] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'comparison_date' => '2025-10-22',
            'cryptoquant_web_value' => '193.2K',
            'our_values' => $results,
            'analysis' => [
                'note' => 'Comparing Oct 22 values across exchanges',
                'web_vs_api_difference' => 'CryptoQuant web shows 193.2K, our API shows much lower values'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Test failed',
            'message' => $e->getMessage()
        ], 500);
    }
})->name('test.cdd-all-exchanges');



// Test Liquidation Summary
Route::get('/test/liquidation-summary', function() {
    try {
        $controller = new App\Http\Controllers\CoinglassController();
        $request = new Illuminate\Http\Request([
            'symbol' => 'BTC'
        ]);
        
        return $controller->getLiquidationSummary($request);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Test failed',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('test.liquidation-summary');

// Test Coinglass API endpoints
Route::get('/test/coinglass-debug', function() {
    try {
        $controller = new App\Http\Controllers\CoinglassController();
        
        $results = [];
        
        // Test Global Account Ratio
        $request1 = new Illuminate\Http\Request([
            'exchange' => 'Binance',
            'symbol' => 'BTCUSDT',
            'interval' => '1h',
            'limit' => 50
        ]);
        $results['global_account'] = $controller->getGlobalAccountRatio($request1)->getData(true);
        
        // Test Top Account Ratio
        $request2 = new Illuminate\Http\Request([
            'exchange' => 'Binance',
            'symbol' => 'BTCUSDT',
            'interval' => '1h',
            'limit' => 5
        ]);
        $results['top_account'] = $controller->getTopAccountRatio($request2)->getData(true);
        
        // Test Top Position Ratio
        $request3 = new Illuminate\Http\Request([
            'exchange' => 'Binance',
            'symbol' => 'BTCUSDT',
            'interval' => '1h',
            'limit' => 5
        ]);
        $results['top_position'] = $controller->getTopPositionRatio($request3)->getData(true);
        
        // Test Net Position
        $request4 = new Illuminate\Http\Request([
            'exchange' => 'Binance',
            'symbol' => 'BTCUSDT',
            'interval' => '1h',
            'limit' => 5
        ]);
        $results['net_position'] = $controller->getNetPosition($request4)->getData(true);
        
        // Test Taker Buy/Sell
        $request5 = new Illuminate\Http\Request([
            'symbol' => 'BTC',
            'range' => '1h'
        ]);
        $results['taker_buysell'] = $controller->getTakerBuySell($request5)->getData(true);
        
        // Test Liquidation Coin List
        $request6 = new Illuminate\Http\Request([
            'exchange' => 'Binance'
        ]);
        $results['liquidation_coinlist'] = $controller->getLiquidationCoinList($request6)->getData(true);
        
        // Test Liquidation Aggregated History
        $request7 = new Illuminate\Http\Request([
            'exchange_list' => 'Binance',
            'symbol' => 'BTC',
            'interval' => '1h',
            'limit' => 10
        ]);
        $results['liquidation_aggregated'] = $controller->getLiquidationAggregatedHistory($request7)->getData(true);
        
        // Test Liquidation Exchange List
        $request8 = new Illuminate\Http\Request([
            'symbol' => 'BTC',
            'range' => '1h'
        ]);
        $results['liquidation_exchange'] = $controller->getLiquidationExchangeList($request8)->getData(true);
        
        // Test Liquidation History
        $request9 = new Illuminate\Http\Request([
            'exchange' => 'Binance',
            'symbol' => 'BTCUSDT',
            'interval' => '1h',
            'limit' => 10
        ]);
        $results['liquidation_history'] = $controller->getLiquidationHistory($request9)->getData(true);
        
        return response()->json([
            'success' => true,
            'timestamp' => now()->toISOString(),
            'results' => $results
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Test failed',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('test.coinglass-debug');

// API consumption happens directly from frontend using meta api-base-url
