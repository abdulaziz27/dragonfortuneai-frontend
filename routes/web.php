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
Route::view('/derivatives/funding-rate', 'derivatives.funding-rate')->name('derivatives.funding-rate');
Route::view('/derivatives/open-interest', 'derivatives.open-interest')->name('derivatives.open-interest');
Route::view('/derivatives/long-short-ratio', 'derivatives.long-short-ratio')->name('derivatives.long-short-ratio');
Route::view('/derivatives/liquidations', 'derivatives.liquidations')->name('derivatives.liquidations');
Route::view('/derivatives/volume-change', 'derivatives.volume-change')->name('derivatives.volume-change');
Route::view('/derivatives/delta-long-short', 'derivatives.delta-long-short')->name('derivatives.delta-long-short');

// Spot Microstructure Routes
Route::view('/spot-microstructure/cvd', 'spot-microstructure.cvd')->name('spot-microstructure.cvd');
Route::view('/spot-microstructure/orderbook-depth', 'spot-microstructure.orderbook-depth')->name('spot-microstructure.orderbook-depth');
Route::view('/spot-microstructure/absorption', 'spot-microstructure.absorption')->name('spot-microstructure.absorption');
Route::view('/spot-microstructure/spoofing', 'spot-microstructure.spoofing')->name('spot-microstructure.spoofing');
Route::view('/spot-microstructure/vwap', 'spot-microstructure.vwap')->name('spot-microstructure.vwap');
Route::view('/spot-microstructure/liquidity-cluster', 'spot-microstructure.liquidity-cluster')->name('spot-microstructure.liquidity-cluster');

// On-Chain Metrics Routes
Route::view('/onchain-metrics/exchange-netflow', 'onchain-metrics.exchange-netflow')->name('onchain-metrics.exchange-netflow');
Route::view('/onchain-metrics/whale-activity', 'onchain-metrics.whale-activity')->name('onchain-metrics.whale-activity');
Route::view('/onchain-metrics/stablecoin-supply', 'onchain-metrics.stablecoin-supply')->name('onchain-metrics.stablecoin-supply');
Route::view('/onchain-metrics/miner-flow', 'onchain-metrics.miner-flow')->name('onchain-metrics.miner-flow');

// Options Metrics Routes
Route::view('/options-metrics/implied-volatility', 'options-metrics.implied-volatility')->name('options-metrics.implied-volatility');
Route::view('/options-metrics/put-call-ratio', 'options-metrics.put-call-ratio')->name('options-metrics.put-call-ratio');
Route::view('/options-metrics/options-skew', 'options-metrics.options-skew')->name('options-metrics.options-skew');
Route::view('/options-metrics/gamma-exposure', 'options-metrics.gamma-exposure')->name('options-metrics.gamma-exposure');

// ETF & Institutional Routes
Route::view('/etf-institutional/dashboard', 'etf-institutional.dashboard')->name('etf-institutional.dashboard');

// Volatility Regime Routes
Route::view('/volatility-regime/dashboard', 'volatility-regime.dashboard')->name('volatility-regime.dashboard');

// Macro Overlay Routes
Route::view('/macro-overlay/dashboard', 'macro-overlay.dashboard')->name('macro-overlay.dashboard');

// Sentiment & Flow Routes
Route::view('/sentiment-flow/dashboard', 'sentiment-flow.dashboard')->name('sentiment-flow.dashboard');

// API Routes
Route::prefix('api')->group(function () {
    // Open Interest API Routes
    Route::prefix('open-interest')->group(function () {
        Route::get('/bias', function () {
            return response()->json([
                'bias' => 'neutral',
                'trend' => 'stable',
                'average_oi' => 1500000000
            ]);
        });
        
        Route::get('/aggregate', function () {
            return response()->json([
                'data' => [
                    ['time' => time() * 1000, 'open' => 1500000000, 'high' => 1600000000, 'low' => 1400000000, 'close' => 1550000000]
                ]
            ]);
        });
        
        Route::get('/coins', function () {
            return response()->json([
                'data' => [
                    ['symbol' => 'BTC', 'exchange_list_str' => 'binance,okx,bybit', 'open' => 1500000000, 'high' => 1600000000, 'low' => 1400000000, 'close' => 1550000000]
                ]
            ]);
        });
        
        Route::get('/exchange', function () {
            return response()->json([
                'data' => [
                    ['exchange' => 'binance', 'value' => 800000000, 'time' => time() * 1000],
                    ['exchange' => 'okx', 'value' => 400000000, 'time' => time() * 1000],
                    ['exchange' => 'bybit', 'value' => 300000000, 'time' => time() * 1000]
                ]
            ]);
        });
        
        Route::get('/stable', function () {
            return response()->json([
                'data' => [
                    ['time' => time() * 1000, 'open' => 500000000, 'high' => 550000000, 'low' => 450000000, 'close' => 520000000]
                ]
            ]);
        });
        
        Route::get('/history', function () {
            return response()->json([
                'data' => [
                    ['time' => time() * 1000, 'open' => 1500000000, 'high' => 1600000000, 'low' => 1400000000, 'close' => 1550000000, 'exchange' => 'binance', 'symbol' => 'BTC']
                ]
            ]);
        });
    });
    
    // Funding Rate API Routes
    Route::prefix('funding-rate')->group(function () {
        Route::get('/bias', function () {
            return response()->json([
                'bias' => 'neutral',
                'strength' => 50,
                'avg_funding_close' => 0.0001,
                'n' => 100
            ]);
        });
        
        Route::get('/exchanges', function () {
            return response()->json([
                'data' => [
                    ['exchange' => 'binance', 'funding_rate' => 0.0001, 'next_funding_time' => time() + 3600, 'symbol' => 'BTC'],
                    ['exchange' => 'okx', 'funding_rate' => -0.0001, 'next_funding_time' => time() + 3600, 'symbol' => 'BTC']
                ]
            ]);
        });
        
        Route::get('/aggregate', function () {
            return response()->json([
                'data' => [
                    ['exchange' => 'binance', 'funding_rate' => 0.0001, 'time_ms' => time() * 1000],
                    ['exchange' => 'okx', 'funding_rate' => -0.0001, 'time_ms' => time() * 1000]
                ]
            ]);
        });
        
        Route::get('/weighted', function () {
            return response()->json([
                'oi_weight' => [
                    ['time' => time() * 1000, 'open' => 0.0001, 'high' => 0.0002, 'low' => -0.0001, 'close' => 0.0001]
                ]
            ]);
        });
        
        Route::get('/history', function () {
            return response()->json([
                'data' => [
                    ['time' => time() * 1000, 'open' => 0.0001, 'high' => 0.0002, 'low' => -0.0001, 'close' => 0.0001]
                ]
            ]);
        });
    });
});
