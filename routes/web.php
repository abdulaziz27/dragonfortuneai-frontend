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
Route::view('/derivatives/basis-term-structure', 'derivatives.basis-term-structure')->name('derivatives.basis-term-structure');
Route::view('/derivatives/perp-quarterly-spread', 'derivatives.perp-quarterly-spread')->name('derivatives.perp-quarterly-spread');

// Spot Microstructure Routes
Route::view('/spot-microstructure/trades', 'spot-microstructure.trades')->name('spot-microstructure.trades');
Route::view('/spot-microstructure/orderbook-snapshots', 'spot-microstructure.orderbook-snapshots')->name('spot-microstructure.orderbook-snapshots');
Route::view('/spot-microstructure/vwap-twap', 'spot-microstructure.vwap-twap')->name('spot-microstructure.vwap-twap');
Route::view('/spot-microstructure/volume-trade-stats', 'spot-microstructure.volume-trade-stats')->name('spot-microstructure.volume-trade-stats');

// On-Chain Metrics Routes
Route::view('/onchain-metrics', 'onchain-metrics.dashboard')->name('onchain-metrics.index');
Route::view('/onchain-metrics/dashboard', 'onchain-metrics.dashboard')->name('onchain-metrics.dashboard');

// Advanced On-Chain Metrics Routes
Route::view('/onchain-ethereum', 'onchain-ethereum.dashboard')->name('onchain-ethereum.dashboard');
Route::view('/onchain-exchange', 'onchain-exchange.dashboard')->name('onchain-exchange.dashboard');
Route::view('/onchain-mining-price', 'onchain-mining-price.dashboard')->name('onchain-mining-price.dashboard');

// Options Intelligence Route
Route::view('/options-metrics/dashboard', 'options-metrics.dashboard')->name('options-metrics.dashboard');

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

// API consumption happens directly from frontend using meta api-base-url
