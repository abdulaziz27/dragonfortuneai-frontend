<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'workspace')->name('workspace');
Route::view('/login', 'auth.login')->name('login');

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

// ETF & Basis Routes
Route::view('/etf-basis/spot-etf-netflow', 'etf-basis.spot-etf-netflow')->name('etf-basis.spot-etf-netflow');
Route::view('/etf-basis/perp-basis', 'etf-basis.perp-basis')->name('etf-basis.perp-basis');

// Volatility Regime Routes
Route::view('/volatility-regime/dashboard', 'volatility-regime.dashboard')->name('volatility-regime.dashboard');

// Macro Overlay Routes
Route::view('/macro-overlay/dashboard', 'macro-overlay.dashboard')->name('macro-overlay.dashboard');

// Sentiment & Flow Routes
Route::view('/sentiment-flow/dashboard', 'sentiment-flow.dashboard')->name('sentiment-flow.dashboard');
