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
Route::get('/api/cryptoquant/funding-rate', [App\Http\Controllers\CryptoQuantController::class, 'getFundingRates'])->name('api.cryptoquant.funding-rate');
Route::get('/api/cryptoquant/funding-rates', [App\Http\Controllers\CryptoQuantController::class, 'getFundingRates'])->name('api.cryptoquant.funding-rates');
Route::get('/api/cryptoquant/open-interest', [App\Http\Controllers\CryptoQuantController::class, 'getOpenInterest'])->name('api.cryptoquant.open-interest');
Route::get('/api/cryptoquant/funding-rates-comparison', [App\Http\Controllers\CryptoQuantController::class, 'getFundingRatesComparison'])->name('api.cryptoquant.funding-rates-comparison');

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

// API consumption happens directly from frontend using meta api-base-url
