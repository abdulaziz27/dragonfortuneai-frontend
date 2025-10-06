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
