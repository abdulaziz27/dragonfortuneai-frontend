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
Route::redirect('/onchain-metrics', '/onchain-metrics/mvrv-zscore')->name('onchain-metrics.index');
Route::view('/onchain-metrics/mvrv-zscore', 'onchain-metrics.mvrv-zscore')->name('onchain-metrics.mvrv-zscore');
Route::view('/onchain-metrics/lth-sth-supply', 'onchain-metrics.lth-sth-supply')->name('onchain-metrics.lth-sth-supply');
Route::view('/onchain-metrics/exchange-netflow', 'onchain-metrics.exchange-netflow')->name('onchain-metrics.exchange-netflow');
Route::view('/onchain-metrics/realized-cap-hodl', 'onchain-metrics.realized-cap-hodl')->name('onchain-metrics.realized-cap-hodl');
Route::view('/onchain-metrics/reserve-risk-sopr', 'onchain-metrics.reserve-risk-sopr')->name('onchain-metrics.reserve-risk-sopr');
Route::view('/onchain-metrics/miner-metrics', 'onchain-metrics.miner-metrics')->name('onchain-metrics.miner-metrics');
Route::view('/onchain-metrics/whale-holdings', 'onchain-metrics.whale-holdings')->name('onchain-metrics.whale-holdings');

// Options Intelligence Route
Route::view('/options-metrics/dashboard', 'options-metrics.dashboard')->name('options-metrics.dashboard');

// ETF & Institutional Routes
Route::view('/etf-institutional/dashboard', 'etf-institutional.dashboard')->name('etf-institutional.dashboard');

// Volatility Regime Routes
Route::view('/volatility-regime/dashboard', 'volatility-regime.dashboard')->name('volatility-regime.dashboard');

// Macro Overlay Routes
Route::view('/macro-overlay/dashboard', 'macro-overlay.dashboard')->name('macro-overlay.dashboard');

// Sentiment & Flow Routes
Route::view('/sentiment-flow/dashboard', 'sentiment-flow.dashboard')->name('sentiment-flow.dashboard');

// API consumption happens directly from frontend using meta api-base-url
