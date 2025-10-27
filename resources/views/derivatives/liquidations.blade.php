@extends('layouts.app')

@section('content')
    {{--
        Bitcoin: Liquidations Dashboard (HYBRID)
        Think like a trader • Build like an engineer • Visualize like a designer

        HYBRID API APPROACH:
        - MAIN DATA: Coinglass API for Liquidations data
        - PRICE OVERLAY: CryptoQuant API for Bitcoin price (as reference)
        
        Interpretasi Trading:
        - Liquidations menunjukkan forced closure posisi leverage
        - High liquidations = Extreme volatility dan panic selling/buying
        - Long liquidations = Support levels, Short liquidations = Resistance levels
        - Liquidation clusters = Key price levels untuk entry/exit
    --}}

    <div class="d-flex flex-column h-100 gap-3" x-data="liquidationsHybridController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">💥 Bitcoin: Liquidations</h1>
                        <span class="pulse-dot pulse-success"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Analisis liquidations untuk mengidentifikasi level support/resistance dan momentum pasar dari forced closures.
                    </p>
                </div>

                <!-- Global Controls -->
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <!-- Exchange Selector -->
                    <select class="form-select" style="width: 160px;" x-model="selectedExchange" @change="updateExchange()">
                        <option value="binance">Binance</option>
                        <option value="bybit">Bybit</option>
                        <option value="okx">OKX</option>
                        <option value="bitmex">BitMEX</option>
                        <option value="bitfinex">Bitfinex</option>
                        <option value="all_exchange">All Exchanges</option>
                    </select>

                    <!-- Symbol/Pair Selector -->
                    <select class="form-select" style="width: 140px;" x-model="selectedSymbol" @change="updateSymbol()">
                        <option value="all_symbol">All Symbols</option>
                        <option value="btc_usdt">BTC/USDT</option>
                        <option value="btc_usd">BTC/USD</option>
                        <!-- <option value="btc_busd">BTC/BUSD</option> -->
                    </select>



                    <button class="btn btn-primary" @click="refreshAll()" :disabled="globalLoading">
                        <span x-show="!globalLoading">🔄 Refresh</span>
                        <span x-show="globalLoading" class="spinner-border spinner-border-sm"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Cards Row -->
        <div class="row g-3">
            <!-- Bitcoin Price USD -->
            <div class="col-md-2">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">₿ BTC/USD</span>
                    </div>
                    <div class="h3 mb-1 text-warning" x-text="formatPriceUSD(currentPrice)">--</div>
                    <div class="small" :class="getPriceTrendClass(priceChange)">
                        <span x-text="formatChange(priceChange)">--</span> 24h
                    </div>
                </div>
            </div>

            <!-- Total Liquidations 24h -->
            <div class="col-md-2">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Total Liq 24h</span>
                        <span class="badge text-bg-primary">Latest</span>
                    </div>
                    <div class="h3 mb-1" x-text="formatLiquidation(totalLiquidations)">--</div>
                    <div class="small text-secondary">All Exchanges</div>
                </div>
            </div>

            <!-- Long Liquidations -->
            <div class="col-md-2">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Long Liq</span>
                        <span class="badge text-bg-danger">Shorts Win</span>
                    </div>
                    <div class="h3 mb-1 text-danger" x-text="formatLiquidation(longLiquidations)">--</div>
                    <div class="small text-secondary" x-text="formatPercentage(longLiquidationRatio)">--</div>
                </div>
            </div>

            <!-- Short Liquidations -->
            <div class="col-md-2">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Short Liq</span>
                        <span class="badge text-bg-success">Longs Win</span>
                    </div>
                    <div class="h3 mb-1 text-success" x-text="formatLiquidation(shortLiquidations)">--</div>
                    <div class="small text-secondary" x-text="formatPercentage(shortLiquidationRatio)">--</div>
                </div>
            </div>

            <!-- Liquidation Sentiment -->
            <div class="col-md-4">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Liquidation Sentiment</span>
                        <span class="badge" :class="getLiquidationSentimentBadgeClass()" x-text="liquidationSentimentStrength">--</span>
                    </div>
                    <div class="h4 mb-1" :class="getLiquidationSentimentColorClass()" x-text="liquidationSentiment">--</div>
                    <div class="small text-secondary" x-text="liquidationSentimentDescription">--</div>
                    <!-- Long/Short Ratio Display -->
                    <div class="mt-2 d-flex justify-content-between">
                        <span class="small text-secondary">Long/Short Ratio:</span>
                        <span class="badge" :class="getLongShortRatioBadgeClass(longShortLiqRatio)" x-text="formatRatio(longShortLiqRatio)">--</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-Time Liquidations Table -->
        <div class="row g-3">
            <div class="col-12">
                <div class="realtime-liquidations-container" x-data="realtimeLiquidationsTable()" x-init="init()">
                    <div class="realtime-header">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="mb-0">⚡ Real-Time Liquidations</h5>
                                <span class="pulse-dot pulse-success"></span>
                                <span class="badge text-bg-success" x-show="isConnected">Live</span>
                                <span class="badge text-bg-danger" x-show="!isConnected">Disconnected</span>
                            </div>

                            <!-- Real-time Controls -->
                            <div class="d-flex gap-2 align-items-center flex-wrap">
                                <!-- Symbol Filter -->
                                <select class="form-select form-select-sm" style="width: 120px;" x-model="selectedSymbol" @change="applyFilter()">
                                    <option value="all">All Symbols</option>
                                    <template x-for="symbol in availableSymbols" :key="symbol">
                                        <option :value="symbol" x-text="symbol"></option>
                                    </template>
                                </select>

                                <!-- Filter Dropdown -->
                                <select class="form-select form-select-sm" style="width: 120px;" x-model="selectedFilter" @change="applyFilter()">
                                    <option value="all">All</option>
                                    <option value="long">Long Only</option>
                                    <option value="short">Short Only</option>
                                    <option value="large">Large (>$10K)</option>
                                </select>

                                <!-- Exchange Filter -->
                                <select class="form-select form-select-sm" style="width: 140px;" x-model="selectedExchange" @change="applyFilter()">
                                    <option value="all">All Exchanges</option>
                                    <template x-for="exchange in availableExchanges" :key="exchange">
                                        <option :value="exchange" x-text="exchange"></option>
                                    </template>
                                </select>

                                <!-- Value Filter -->
                                <select class="form-select form-select-sm" style="width: 100px;" x-model="sortBy" @change="applySorting()">
                                    <option value="time">Time ↓</option>
                                    <option value="value">Value ↓</option>
                                    <option value="price">Price ↓</option>
                                </select>

                                <!-- Sound Toggle -->
                                <button class="btn btn-outline-secondary btn-sm" @click="toggleSound()" :title="soundEnabled ? 'Disable Sound' : 'Enable Sound'">
                                    <span x-show="soundEnabled">🔊</span>
                                    <span x-show="!soundEnabled">🔇</span>
                                </button>

                                <!-- Pause/Resume -->
                                <button class="btn btn-outline-primary btn-sm" @click="togglePause()" :disabled="!isConnected">
                                    <span x-show="!isPaused">⏸️ Pause</span>
                                    <span x-show="isPaused">▶️ Resume</span>
                                </button>

                                <!-- Clear Table -->
                                <button class="btn btn-outline-danger btn-sm" @click="clearTable()">
                                    🗑️ Clear
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="realtime-body">
                        <!-- Connection Status -->
                        <div x-show="!isConnected" class="connection-status">
                            <div class="d-flex justify-content-center align-items-center" style="height: 100px;">
                                <div class="text-center">
                                    <div class="spinner-border text-warning mb-3"></div>
                                    <div class="text-secondary">Connecting to real-time liquidation stream...</div>
                                    <button class="btn btn-primary btn-sm mt-2" @click="reconnect()">Reconnect</button>
                                </div>
                            </div>
                        </div>

                        <!-- Real-Time Liquidations Table -->
                        <div x-show="isConnected" class="liquidations-table-container">
                            <div class="table-responsive">
                                <table class="table table-dark liquidations-table">
                                    <thead>
                                        <tr>
                                            <th class="symbol-col">Symbol</th>
                                            <th class="exchange-col">Exchange</th>
                                            <th class="side-col">Side</th>
                                            <th class="price-col">Price</th>
                                            <th class="value-col">Value</th>
                                            <th class="time-col">Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(liquidation, index) in filteredLiquidations" :key="'liq-' + liquidation.id">
                                            <tr class="liquidation-row" 
                                                :class="getLiquidationRowClass(liquidation)"
                                                @click="showLiquidationDetails(liquidation)">
                                                <td class="symbol-cell">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="symbol-icon" :style="'background-color: ' + getSymbolColor(liquidation.baseAsset)"></div>
                                                        <div>
                                                            <div class="symbol-name" x-text="liquidation.symbol"></div>
                                                            <div class="base-asset" x-text="liquidation.baseAsset"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="exchange-cell">
                                                    <div class="exchange-info">
                                                        <div class="exchange-icon" :style="'background-color: ' + getExchangeColor(liquidation.exName)"></div>
                                                        <span class="exchange-name" x-text="liquidation.exName"></span>
                                                    </div>
                                                </td>
                                                <td class="side-cell">
                                                    <span class="side-badge" :class="getSideBadgeClass(liquidation.side)" x-text="getSideText(liquidation.side)"></span>
                                                </td>
                                                <td class="price-cell">
                                                    <span class="price-value" x-text="formatPrice(liquidation.price)"></span>
                                                </td>
                                                <td class="value-cell">
                                                    <span class="value-amount" :class="getValueClass(liquidation.volUsd)" x-text="formatValue(liquidation.volUsd)"></span>
                                                </td>
                                                <td class="time-cell">
                                                    <span class="time-value" x-text="formatTime(liquidation.time)"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Empty State -->
                            <div x-show="filteredLiquidations.length === 0" class="empty-state">
                                <div class="text-center py-5">
                                    <div class="text-secondary mb-3">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                        </svg>
                                    </div>
                                    <div class="text-secondary">No liquidations matching current filters</div>
                                    <button class="btn btn-outline-primary btn-sm mt-2" @click="resetFilters()">Reset Filters</button>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics Footer -->
                        <div x-show="isConnected" class="realtime-footer">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="stats-info">
                                    <small class="text-light">
                                        Total: <span class="fw-bold text-info" x-text="totalLiquidations"></span> | 
                                        Long: <span class="text-danger fw-bold" x-text="longCount"></span> | 
                                        Short: <span class="text-success fw-bold" x-text="shortCount"></span> |
                                        Volume: <span class="text-warning fw-bold" x-text="formatValue(totalVolume)"></span>
                                    </small>
                                </div>
                                <div class="connection-info">
                                    <small class="text-light">
                                        <span class="badge text-bg-success me-1">WebSocket</span>
                                        Last update: <span class="fw-bold text-info" x-text="lastUpdateTime"></span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Liquidations Table -->
        <div class="row g-3">
            <div class="col-12">
                <div class="total-liquidations-container" x-data="totalLiquidationsTable()" x-init="init()">
                    <div class="total-liquidations-header">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="mb-0">📊 Total Liquidations</h5>
                                <span class="badge text-bg-info" x-show="!loading">Updated</span>
                                <span class="badge text-bg-warning" x-show="loading">Loading...</span>
                            </div>

                            <!-- Total Liquidations Controls -->
                            <div class="d-flex gap-2 align-items-center flex-wrap">
                                <!-- Exchange Filter -->
                                <select class="form-select form-select-sm" style="width: 140px;" x-model="selectedExchange" @change="loadData()">
                                    <option value="Binance">Binance</option>
                                    <option value="OKX">OKX</option>
                                    <option value="Bybit">Bybit</option>
                                    <option value="BitMEX">BitMEX</option>
                                    <option value="Bitfinex">Bitfinex</option>
                                </select>

                                <!-- Refresh Button -->
                                <button class="btn btn-primary btn-sm" @click="refreshData()" :disabled="loading">
                                    <span x-show="!loading">🔄 Refresh</span>
                                    <span x-show="loading" class="spinner-border spinner-border-sm"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="total-liquidations-body">
                        <!-- Loading State -->
                        <div x-show="loading" class="loading-state">
                            <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
                                <div class="text-center">
                                    <div class="spinner-border text-primary mb-3"></div>
                                    <div class="text-secondary">Loading total liquidations data...</div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Liquidations Table -->
                        <div x-show="!loading" class="total-liquidations-table-container">
                            <div class="table-responsive">
                                <table class="table table-dark total-liquidations-table">
                                    <thead>
                                        <tr>
                                            <th class="ranking-col">Ranking</th>
                                            <th class="symbol-col">Symbol</th>
                                            <th class="price-col">Price</th>
                                            <th class="change-col">Price (24h%)</th>
                                            <th class="liq-1h-col">1h Long</th>
                                            <th class="liq-1h-col">1h Short</th>
                                            <th class="liq-4h-col">4h Long</th>
                                            <th class="liq-4h-col">4h Short</th>
                                            <th class="liq-24h-col">24h Long</th>
                                            <th class="liq-24h-col">24h Short</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(coin, index) in liquidationsData" :key="'coin-' + coin.symbol">
                                            <tr class="liquidation-coin-row" @click="showCoinDetails(coin)">
                                                <td class="ranking-cell">
                                                    <span class="ranking-number" :class="getRankingClass(index + 1)" x-text="index + 1"></span>
                                                </td>
                                                <td class="symbol-cell">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="coin-icon" :style="'background-color: ' + getCoinColor(coin.symbol)">
                                                            <span x-text="coin.symbol.substring(0, 3)"></span>
                                                        </div>
                                                        <div>
                                                            <div class="coin-symbol" x-text="coin.symbol"></div>
                                                            <div class="coin-name" x-text="getCoinName(coin.symbol)"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="price-cell">
                                                    <span class="price-value" x-text="formatPrice(coin.price || 0)"></span>
                                                </td>
                                                <td class="change-cell">
                                                    <span class="change-value" :class="getChangeClass(coin.priceChange || 0)" x-text="formatPercentage(coin.priceChange || 0)"></span>
                                                </td>
                                                <td class="liq-cell long">
                                                    <span class="liq-value long-liq" x-text="formatLiquidation(coin.long_liquidation_usd_1h)"></span>
                                                </td>
                                                <td class="liq-cell short">
                                                    <span class="liq-value short-liq" x-text="formatLiquidation(coin.short_liquidation_usd_1h)"></span>
                                                </td>
                                                <td class="liq-cell long">
                                                    <span class="liq-value long-liq" x-text="formatLiquidation(coin.long_liquidation_usd_4h)"></span>
                                                </td>
                                                <td class="liq-cell short">
                                                    <span class="liq-value short-liq" x-text="formatLiquidation(coin.short_liquidation_usd_4h)"></span>
                                                </td>
                                                <td class="liq-cell long">
                                                    <span class="liq-value long-liq" x-text="formatLiquidation(coin.long_liquidation_usd_24h)"></span>
                                                </td>
                                                <td class="liq-cell short">
                                                    <span class="liq-value short-liq" x-text="formatLiquidation(coin.short_liquidation_usd_24h)"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Empty State -->
                            <div x-show="liquidationsData.length === 0" class="empty-state">
                                <div class="text-center py-5">
                                    <div class="text-secondary mb-3">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                        </svg>
                                    </div>
                                    <div class="text-secondary">No liquidation data available</div>
                                    <button class="btn btn-outline-primary btn-sm mt-2" @click="refreshData()">Retry</button>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics Footer -->
                        <div x-show="!loading && liquidationsData.length > 0" class="total-liquidations-footer">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="stats-info">
                                    <small class="text-light">
                                        Showing: <span class="fw-bold text-info" x-text="liquidationsData.length"></span> coins | 
                                        Exchange: <span class="fw-bold text-warning" x-text="selectedExchange"></span> |
                                        Total 24h: <span class="fw-bold text-success" x-text="formatLiquidation(getTotalLiquidations())"></span>
                                    </small>
                                </div>
                                <div class="update-info">
                                    <small class="text-light">
                                        <span class="badge text-bg-success me-1">Coinglass API</span>
                                        Last update: <span class="fw-bold text-info" x-text="lastUpdateTime"></span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Liquidations Chart -->
        <div class="row g-3">
            <div class="col-12">
                <div class="total-liquidations-chart-container" x-data="totalLiquidationsChart()" x-init="init()">
                    <div class="chart-header">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="mb-0">📈 Total Liquidations Chart</h5>
                                <span class="badge text-bg-info" x-show="!loading">Live</span>
                                <span class="badge text-bg-warning" x-show="loading">Loading...</span>
                            </div>

                            <!-- Chart Controls -->
                            <div class="d-flex gap-2 align-items-center flex-wrap">
                                <!-- Symbol Filter -->
                                <select class="form-select form-select-sm" style="width: 120px;" x-model="selectedSymbol" @change="setSymbol(selectedSymbol)">
                                    <template x-for="symbol in availableSymbols" :key="symbol">
                                        <option :value="symbol" x-text="symbol"></option>
                                    </template>
                                </select>

                                <!-- Interval Filter -->
                                <div class="btn-group btn-group-sm" role="group">
                                    <template x-for="interval in availableIntervals" :key="interval.value">
                                        <button type="button" 
                                                class="btn"
                                                :class="selectedInterval === interval.value ? 'btn-primary' : 'btn-outline-secondary'"
                                                @click="setInterval(interval.value)"
                                                x-text="interval.label">
                                        </button>
                                    </template>
                                </div>

                                <!-- Chart Type Toggle -->
                                <div class="btn-group btn-group-sm me-3" role="group">
                                    <button type="button" class="btn" :class="chartType === 'line' ? 'btn-primary' : 'btn-outline-secondary'" @click="toggleChartType('line')">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                            <path d="M2 12l3-3 3 3 6-6"/>
                                        </svg>
                                    </button>
                                    <button type="button" class="btn" :class="chartType === 'bar' ? 'btn-primary' : 'btn-outline-secondary'" @click="toggleChartType('bar')">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                            <rect x="2" y="6" width="3" height="8"/>
                                            <rect x="6" y="4" width="3" height="10"/>
                                            <rect x="10" y="8" width="3" height="6"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Chart Tools -->
                                <div class="btn-group btn-group-sm chart-tools" role="group">
                                    <button type="button" class="btn btn-outline-secondary chart-tool-btn" @click="refreshChart()" :disabled="loading" title="Refresh Data">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                            <path d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                                            <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="chart-body" style="position: relative; min-height: 400px;">
                        <!-- Loading State -->
                        <div x-show="loading" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.9); z-index: 10;">
                            <div class="d-flex justify-content-center align-items-center" style="height: 100%;">
                                <div class="text-center">
                                    <div class="spinner-border text-primary mb-3"></div>
                                    <div class="text-secondary">Loading liquidation chart data...</div>
                                </div>
                            </div>
                        </div>

                        <!-- Chart Canvas (Always Visible) -->
                        <div class="chart-canvas-container">
                            <canvas id="totalLiquidationsChart" style="height: 400px;"></canvas>
                        </div>
                    </div>

                    <!-- Chart Footer -->
                    <div x-show="!loading" class="chart-footer">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="chart-stats">
                                <small class="text-light">
                                    Total Volume: <span class="fw-bold text-warning" x-text="formatValue(getTotalLiquidations())"></span> |
                                    Long/Short Ratio: <span class="fw-bold text-info" x-text="getLongShortRatio()"></span> |
                                    Symbol: <span class="fw-bold text-success" x-text="selectedSymbol"></span>
                                </small>
                            </div>
                            <div class="chart-source">
                                <small class="text-light">
                                    <span class="badge text-bg-success me-1">Coinglass</span>
                                    Interval: <span class="fw-bold text-info" x-text="selectedInterval"></span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exchange Liquidations Table -->
        <div class="row g-3">
            <div class="col-12">
                <div class="exchange-liquidations-container" x-data="exchangeLiquidationsTable()" x-init="init()">
                    <div class="exchange-header">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="mb-0">🏢 Exchange Liquidations</h5>
                                <span class="badge text-bg-info" x-show="!loading">Updated</span>
                                <span class="badge text-bg-warning" x-show="loading">Loading...</span>
                            </div>

                            <!-- Exchange Controls -->
                            <div class="d-flex gap-2 align-items-center flex-wrap">
                                <!-- Symbol Filter -->
                                <select class="form-select form-select-sm" style="width: 120px;" x-model="selectedSymbol" @change="setSymbol(selectedSymbol)">
                                    <template x-for="symbol in availableSymbols" :key="symbol">
                                        <option :value="symbol" x-text="symbol"></option>
                                    </template>
                                </select>

                                <!-- Time Range Filter -->
                                <select class="form-select form-select-sm" style="width: 140px;" x-model="selectedTimeRange" @change="setTimeRange(selectedTimeRange)">
                                    <template x-for="range in availableTimeRanges" :key="range.value">
                                        <option :value="range.value" x-text="range.label"></option>
                                    </template>
                                </select>

                                <!-- Refresh Button -->
                                <button class="btn btn-primary btn-sm" @click="refreshData()" :disabled="loading">
                                    <span x-show="!loading">🔄 Refresh</span>
                                    <span x-show="loading" class="spinner-border spinner-border-sm"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="exchange-body">
                        <!-- Loading State -->
                        <div x-show="loading" class="loading-state">
                            <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
                                <div class="text-center">
                                    <div class="spinner-border text-primary mb-3"></div>
                                    <div class="text-secondary">Loading exchange liquidations data...</div>
                                </div>
                            </div>
                        </div>

                        <!-- Exchange Liquidations Table -->
                        <div x-show="!loading" class="exchange-table-container">
                            <div class="table-responsive">
                                <table class="table table-dark exchange-liquidations-table">
                                    <thead>
                                        <tr>
                                            <th class="exchange-col" @click="sortTable('exchange')">
                                                Exchanges <span x-text="getSortIcon('exchange')"></span>
                                            </th>
                                            <th class="liquidations-col" @click="sortTable('liquidation_usd')">
                                                Liquidations <span x-text="getSortIcon('liquidation_usd')"></span>
                                            </th>
                                            <th class="long-col" @click="sortTable('longLiquidation_usd')">
                                                Long <span x-text="getSortIcon('longLiquidation_usd')"></span>
                                            </th>
                                            <th class="short-col" @click="sortTable('shortLiquidation_usd')">
                                                Short <span x-text="getSortIcon('shortLiquidation_usd')"></span>
                                            </th>
                                            <th class="rate-col" @click="sortTable('marketShare')">
                                                Rate <span x-text="getSortIcon('marketShare')"></span>
                                            </th>
                                            <th class="ratio-col" @click="sortTable('longShortRatio')">
                                                Rate <span x-text="getSortIcon('longShortRatio')"></span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(exchange, index) in exchangeData" :key="'exchange-' + index">
                                            <tr class="exchange-row" 
                                                :class="exchange.exchange === 'All' ? 'all-row' : 'normal-row'"
                                                @click="showExchangeDetails(exchange)">
                                                <td class="exchange-cell">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="exchange-icon" :style="'background-color: ' + getExchangeColor(exchange.exchange)"></div>
                                                        <span class="exchange-name" x-text="exchange.exchange"></span>
                                                    </div>
                                                </td>
                                                <td class="liquidations-cell">
                                                    <span class="liquidation-value" x-text="formatLiquidation(exchange.liquidation_usd)"></span>
                                                </td>
                                                <td class="long-cell">
                                                    <span class="long-value" x-text="formatLiquidation(exchange.longLiquidation_usd)"></span>
                                                </td>
                                                <td class="short-cell">
                                                    <span class="short-value" x-text="formatLiquidation(exchange.shortLiquidation_usd)"></span>
                                                </td>
                                                <td class="rate-cell">
                                                    <span class="rate-value" :class="getRateClass(exchange.exchange)" x-text="formatPercentage(exchange.marketShare)"></span>
                                                </td>
                                                <td class="ratio-cell">
                                                    <span class="ratio-value" :class="getLongShortRatioClass(exchange.longShortRatio)" x-text="exchange.longShortRatio"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Empty State -->
                            <div x-show="exchangeData.length === 0" class="empty-state">
                                <div class="text-center py-5">
                                    <div class="text-secondary mb-3">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                        </svg>
                                    </div>
                                    <div class="text-secondary">No exchange data available</div>
                                    <button class="btn btn-outline-primary btn-sm mt-2" @click="refreshData()">Retry</button>
                                </div>
                            </div>
                        </div>

                        <!-- Exchange Footer -->
                        <div x-show="!loading && exchangeData.length > 0" class="exchange-footer">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="exchange-stats">
                                    <small class="text-light">
                                        Exchanges: <span class="fw-bold text-info" x-text="exchangeData.length"></span> | 
                                        Symbol: <span class="fw-bold text-warning" x-text="selectedSymbol"></span> |
                                        Time Range: <span class="fw-bold text-success" x-text="selectedTimeRange"></span>
                                    </small>
                                </div>
                                <div class="exchange-source">
                                    <small class="text-light">
                                        <span class="badge text-bg-success me-1">Coinglass API</span>
                                        Click row for details
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liquidation History Chart -->
        <div class="row g-3">
            <div class="col-12">
                <div class="tradingview-chart-container" x-data="liquidationHistoryChart()" x-init="init()">
                    <div class="chart-header">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <h5 class="mb-0">💥 Liquidation History Chart</h5>
                                <div class="chart-info">
                                    <span class="current-value" x-text="formatLiq(currentTotalLiq)">--</span>
                                    <span class="change-badge" :class="liqChange >= 0 ? 'positive' : 'negative'" x-text="formatChange(liqChange)">--</span>
                                </div>
                            </div>
                            <div class="chart-controls">
                                <!-- Exchange Filter -->
                                <select class="form-select form-select-sm me-3" style="width: 140px;" x-model="selectedExchange" @change="updateExchange()">
                                    <template x-for="exchange in availableExchanges" :key="exchange">
                                        <option :value="exchange" x-text="exchange"></option>
                                    </template>
                                </select>

                                <!-- Symbol Filter -->
                                <select class="form-select form-select-sm me-3" style="width: 120px;" x-model="selectedSymbol" @change="updateSymbol()">
                                    <template x-for="symbol in availableSymbols" :key="symbol">
                                        <option :value="symbol" x-text="symbol"></option>
                                    </template>
                                </select>

                                <!-- Time Range Buttons -->
                                <div class="time-range-selector me-3">
                                    <template x-for="range in timeRanges" :key="range.value">
                                        <button type="button" 
                                                class="btn btn-sm time-range-btn"
                                                :class="globalPeriod === range.value ? 'btn-primary' : 'btn-outline-secondary'"
                                                @click="setTimeRange(range.value)"
                                                x-text="range.label">
                                        </button>
                                    </template>
                                </div>

                                <!-- Chart Type Toggle -->
                                <div class="btn-group btn-group-sm me-3" role="group">
                                    <button type="button" class="btn" :class="chartType === 'line' ? 'btn-primary' : 'btn-outline-secondary'" @click="toggleChartType('line')">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                            <path d="M2 12l3-3 3 3 6-6"/>
                                        </svg>
                                    </button>
                                    <button type="button" class="btn" :class="chartType === 'bar' ? 'btn-primary' : 'btn-outline-secondary'" @click="toggleChartType('bar')">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                            <rect x="2" y="6" width="3" height="8"/>
                                            <rect x="6" y="4" width="3" height="10"/>
                                            <rect x="10" y="8" width="3" height="6"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Interval Dropdown -->
                                <div class="dropdown me-3">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle interval-dropdown-btn" 
                                            type="button" 
                                            data-bs-toggle="dropdown" 
                                            :title="'Chart Interval: ' + (chartIntervals.find(i => i.value === selectedInterval)?.label || '1h')">
                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" class="me-1">
                                            <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                        </svg>
                                        <span x-text="chartIntervals.find(i => i.value === selectedInterval)?.label || '1h'"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-dark">
                                        <template x-for="interval in chartIntervals" :key="interval.value">
                                            <li>
                                                <a class="dropdown-item" 
                                                   href="#" 
                                                   @click.prevent="setChartInterval(interval.value)"
                                                   :class="selectedInterval === interval.value ? 'active' : ''"
                                                   x-text="interval.label">
                                                </a>
                                            </li>
                                        </template>
                                    </ul>
                                </div>

                                <!-- Scale Toggle -->
                                <div class="btn-group btn-group-sm me-3" role="group">
                                    <button type="button" 
                                            class="btn scale-toggle-btn"
                                            :class="scaleType === 'linear' ? 'btn-primary' : 'btn-outline-secondary'"
                                            @click="toggleScale('linear')"
                                            title="Skala Linear - Interval sama, bagus untuk perubahan absolut">
                                        Linear
                                    </button>
                                    <button type="button" 
                                            class="btn scale-toggle-btn"
                                            :class="scaleType === 'logarithmic' ? 'btn-primary' : 'btn-outline-secondary'"
                                            @click="toggleScale('logarithmic')"
                                            title="Skala Logaritmik - Interval eksponensial, bagus untuk perubahan persentase">
                                        Log
                                    </button>
                                </div>

                                <!-- Chart Tools -->
                                <div class="btn-group btn-group-sm chart-tools" role="group">
                                    <button type="button" class="btn btn-outline-secondary chart-tool-btn" @click="resetZoom()" title="Reset Zoom">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                            <path d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                                            <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                                        </svg>
                                    </button>
                                    
                                    <!-- Export Dropdown -->
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-secondary dropdown-toggle chart-tool-btn" data-bs-toggle="dropdown" title="Export Chart">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                                <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
                                            </svg>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-dark">
                                            <li><a class="dropdown-item" href="#" @click.prevent="exportChart('png')">
                                                <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" class="me-2">
                                                    <path d="M4.502 9a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM4 10.5a.5.5 0 1 1 1 0 .5.5 0 0 1-1 0z"/>
                                                    <path d="M14 2H2a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1zM2 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2H2z"/>
                                                    <path d="M10.648 7.646a.5.5 0 0 1 .577-.093L15.002 9.5V13h-14v-1l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71z"/>
                                                </svg>
                                                Export sebagai PNG
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" @click.prevent="exportChart('svg')">
                                                <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" class="me-2">
                                                    <path d="M8.5 2a.5.5 0 0 0-1 0v5.793L5.354 5.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 7.793V2z"/>
                                                    <path d="M3 9.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5z"/>
                                                </svg>
                                                Export sebagai SVG
                                            </a></li>
                                        </ul>
                                    </div>
                                    
                                    <button type="button" class="btn btn-outline-secondary chart-tool-btn" @click="shareChart()" title="Bagikan Chart">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                            <path d="M11 2.5a2.5 2.5 0 1 1 .603 1.628l-6.718 3.12a2.499 2.499 0 0 1 0 1.504l6.718 3.12a2.5 2.5 0 1 1-.488.876l-6.718-3.12a2.5 2.5 0 1 1 0-3.256l6.718-3.12A2.5 2.5 0 0 1 11 2.5z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="chart-body" style="position: relative; min-height: 400px;">
                        <!-- Loading State -->
                        <div x-show="loading" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.9); z-index: 10;">
                            <div class="d-flex justify-content-center align-items-center" style="height: 100%;">
                                <div class="text-center">
                                    <div class="spinner-border text-primary mb-3"></div>
                                    <div class="text-secondary">Loading liquidation history data...</div>
                                </div>
                            </div>
                        </div>

                        <!-- Chart Canvas (Always Visible) -->
                        <div class="chart-canvas-container">
                            <canvas id="liquidationsMainChart" style="height: 400px;"></canvas>
                        </div>
                    </div>
                    <div class="chart-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="chart-footer-text">
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor" style="margin-right: 4px;">
                                    <circle cx="6" cy="6" r="5" fill="none" stroke="currentColor" stroke-width="1"/>
                                    <path d="M6 3v3l2 2" stroke="currentColor" stroke-width="1" fill="none"/>
                                </svg>
                                Liquidation spikes menunjukkan level support/resistance yang kuat dan momentum pasar
                            </small>
                            <small class="text-muted">
                                <span class="badge text-bg-success me-1">Coinglass</span>
                                <span x-text="selectedExchange + ' - ' + selectedSymbol"></span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trading Interpretation -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4">
                    <h5 class="mb-3">📚 Memahami Liquidations</h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444;">
                                <div class="fw-bold mb-2 text-danger">Long Liquidations</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Posisi long dipaksa tutup karena harga turun</li>
                                        <li>Menunjukkan level support yang kuat</li>
                                        <li>Banyak long liquidation = tekanan jual tinggi</li>
                                        <li>Strategi: Cari entry point setelah liquidation cascade</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e;">
                                <div class="fw-bold mb-2 text-success">Short Liquidations</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Posisi short dipaksa tutup karena harga naik</li>
                                        <li>Menunjukkan level resistance yang kuat</li>
                                        <li>Banyak short liquidation = tekanan beli tinggi</li>
                                        <li>Strategi: Ikuti momentum bullish setelah short squeeze</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6;">
                                <div class="fw-bold mb-2 text-primary">⚡ Liquidation Clusters</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Area dengan banyak liquidation = level penting</li>
                                        <li>Liquidation cascade menciptakan volatilitas tinggi</li>
                                        <li>Sering menjadi turning point harga</li>
                                        <li>Strategi: Gunakan sebagai konfirmasi support/resistance</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>💡 Tips Pro:</strong> Liquidations menunjukkan level harga kritis di mana banyak trader terpaksa keluar dari posisi. Spike liquidation yang besar sering diikuti oleh reversal atau continuation yang kuat, tergantung pada struktur pasar saat itu.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Chart.js with Date Adapter and Plugins -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>

    <!-- Wait for Chart.js to load -->
    <script>
        window.chartJsReady = new Promise((resolve) => {
            if (typeof Chart !== 'undefined') {
                console.log('✅ Chart.js loaded');
                resolve();
            } else {
                setTimeout(() => resolve(), 100);
            }
        });
    </script>

    <!-- Liquidations Page Controller (Main Controller for Summary Cards) -->
    <script src="{{ asset('js/liquidations-page-controller.js') }}"></script>
    
    <!-- Liquidation History Chart Controller -->
    <script src="{{ asset('js/liquidation-history-chart-controller.js') }}"></script>
    
    <!-- Real-Time Liquidations Controller -->
    <script src="{{ asset('js/realtime-liquidations-controller.js') }}"></script>
    
    <!-- Total Liquidations Controller -->
    <script src="{{ asset('js/total-liquidations-controller.js') }}"></script>
    
    <!-- Total Liquidations Chart Controller -->
    <script src="{{ asset('js/total-liquidations-chart-controller.js') }}"></script>
    
    <!-- Exchange Liquidations Table Controller -->
    <script src="{{ asset('js/exchange-liquidations-table-controller.js') }}"></script>
    

    
    <!-- Exchange Dominance Heatmap Controller -->
    <script src="{{ asset('js/laevitas-heatmap.js') }}"></script>

    <style>
        /* Light Theme Chart Container */
        .tradingview-chart-container,
        .total-liquidations-chart-container {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            background: rgba(59, 130, 246, 0.03);
        }

        .chart-header h5 {
            color: #1e293b;
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        .chart-info {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: 12px;
        }

        .current-value {
            font-size: 18px;
            font-weight: 700;
            color: #3b82f6;
        }

        .change-badge {
            font-size: 14px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .change-badge.positive {
            color: #059669;
            background-color: rgba(5, 150, 105, 0.1);
        }

        .change-badge.negative {
            color: #dc2626;
            background-color: rgba(220, 38, 38, 0.1);
        }
        }

        .current-value {
            color: #3b82f6;
            font-size: 20px;
            font-weight: 700;
            font-family: 'Courier New', monospace;
        }

        .change-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Courier New', monospace;
        }

        .change-badge.positive {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
        }

        .change-badge.negative {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }

        .chart-controls .btn-group {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 6px;
            padding: 2px;
        }

        .chart-controls .btn {
            border: none;
            padding: 6px 12px;
            color: #94a3b8;
            background: transparent;
            transition: all 0.2s;
        }

        .chart-controls .btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }

        .chart-controls .btn-primary {
            background: #3b82f6;
            color: #fff;
        }

        .chart-body {
            padding: 20px;
            height: 500px;
            position: relative;
            background: #ffffff;
        }

        .chart-footer {
            padding: 12px 20px;
            border-top: 1px solid rgba(0, 0, 0, 0.08);
            background: rgba(59, 130, 246, 0.02);
        }

        .chart-footer small {
            color: #64748b;
            display: flex;
            align-items: center;
        }

        /* Pulse animation for live indicator */
        .pulse-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }

        .pulse-success {
            background-color: #22c55e;
            box-shadow: 0 0 0 rgba(34, 197, 94, 0.7);
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            }
            50% {
                box-shadow: 0 0 0 8px rgba(34, 197, 94, 0);
            }
        }

        /* Enhanced Summary Cards */
        .df-panel {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
            border: 1px solid rgba(59, 130, 246, 0.1);
            transition: all 0.3s ease;
        }

        .df-panel:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.15);
            border-color: rgba(59, 130, 246, 0.3);
        }

        /* Professional Time Range Controls */
        .time-range-selector {
            display: flex;
            gap: 0.125rem;
            background: linear-gradient(135deg, 
                rgba(30, 41, 59, 0.8) 0%, 
                rgba(51, 65, 85, 0.8) 100%);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 8px;
            padding: 0.25rem;
            box-shadow: 
                0 4px 12px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        .time-range-btn {
            padding: 0.5rem 0.875rem !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            border: none !important;
            border-radius: 6px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            min-width: 44px;
            position: relative;
            overflow: hidden;
            color: #94a3b8 !important;
            background: transparent !important;
        }

        .time-range-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, 
                rgba(59, 130, 246, 0.1) 0%, 
                rgba(139, 92, 246, 0.1) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .time-range-btn:hover {
            color: #e2e8f0 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.2) !important;
        }

        .time-range-btn:hover::before {
            opacity: 1;
        }

        .time-range-btn.btn-primary {
            background: linear-gradient(135deg, 
                #3b82f6 0%, 
                #2563eb 100%) !important;
            color: white !important;
            box-shadow: 
                0 4px 12px rgba(59, 130, 246, 0.4),
                0 2px 4px rgba(59, 130, 246, 0.3) !important;
            transform: translateY(-1px);
        }

        .time-range-btn.btn-primary::before {
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.1) 0%, 
                rgba(255, 255, 255, 0.05) 100%);
            opacity: 1;
        }

        .time-range-btn.btn-primary:hover {
            box-shadow: 
                0 6px 16px rgba(59, 130, 246, 0.5),
                0 3px 6px rgba(59, 130, 246, 0.4) !important;
            transform: translateY(-2px);
        }

        .scale-toggle-btn {
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            padding: 0.375rem 0.75rem !important;
            min-width: 50px;
        }

        .chart-controls {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .chart-controls .btn-group {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 6px;
            padding: 2px;
        }

        .chart-controls .btn-outline-secondary {
            border-color: rgba(148, 163, 184, 0.3) !important;
            color: #94a3b8 !important;
        }

        .chart-controls .btn-outline-secondary:hover {
            background: rgba(59, 130, 246, 0.1) !important;
            border-color: rgba(59, 130, 246, 0.4) !important;
            color: #3b82f6 !important;
        }

        /* Enhanced Chart Tools */
        .chart-tools {
            background: linear-gradient(135deg, 
                rgba(30, 41, 59, 0.6) 0%, 
                rgba(51, 65, 85, 0.6) 100%);
            border-radius: 8px;
            padding: 0.25rem;
            border: 1px solid rgba(59, 130, 246, 0.15);
        }

        .chart-tool-btn {
            border: none !important;
            background: transparent !important;
            color: #94a3b8 !important;
            padding: 0.5rem 0.75rem !important;
            border-radius: 6px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            position: relative;
            overflow: hidden;
        }

        .chart-tool-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, 
                rgba(59, 130, 246, 0.1) 0%, 
                rgba(139, 92, 246, 0.1) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .chart-tool-btn:hover {
            color: #e2e8f0 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.2) !important;
        }

        .chart-tool-btn:hover::before {
            opacity: 1;
        }

        .chart-tool-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3) !important;
        }

        /* Dropdown Menu Styling */
        .dropdown-menu-dark {
            background: linear-gradient(135deg, 
                rgba(15, 23, 42, 0.95) 0%, 
                rgba(30, 41, 59, 0.95) 100%) !important;
            border: 1px solid rgba(59, 130, 246, 0.2) !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4) !important;
            backdrop-filter: blur(12px);
        }

        .dropdown-menu-dark .dropdown-item {
            color: #e2e8f0 !important;
            transition: all 0.2s ease !important;
            border-radius: 4px !important;
            margin: 0.125rem !important;
        }

        .dropdown-menu-dark .dropdown-item:hover {
            background: rgba(59, 130, 246, 0.15) !important;
            color: #60a5fa !important;
        }

        /* Professional Chart Container - CryptoQuant Level */
        .tradingview-chart-container {
            background: linear-gradient(135deg, 
                rgba(15, 23, 42, 0.98) 0%, 
                rgba(30, 41, 59, 0.98) 50%,
                rgba(15, 23, 42, 0.98) 100%);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(59, 130, 246, 0.25);
            box-shadow: 
                0 10px 40px rgba(0, 0, 0, 0.4),
                0 4px 16px rgba(59, 130, 246, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .tradingview-chart-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(59, 130, 246, 0.5) 50%, 
                transparent 100%);
            z-index: 1;
        }

        .tradingview-chart-container:hover {
            box-shadow: 
                0 16px 48px rgba(0, 0, 0, 0.5),
                0 6px 20px rgba(59, 130, 246, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.12);
            border-color: rgba(59, 130, 246, 0.4);
            transform: translateY(-1px);
        }

        .chart-header {
            background: linear-gradient(135deg, 
                rgba(59, 130, 246, 0.08) 0%, 
                rgba(139, 92, 246, 0.06) 100%);
            border-bottom: 1px solid rgba(59, 130, 246, 0.25);
            position: relative;
            z-index: 2;
        }

        .chart-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(59, 130, 246, 0.3) 50%, 
                transparent 100%);
        }

        .chart-header h5 {
            color: #f1f5f9;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
            font-weight: 600;
            letter-spacing: 0.025em;
        }

        .current-value {
            color: #60a5fa;
            text-shadow: 0 0 12px rgba(96, 165, 250, 0.4);
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        .chart-body {
            background: linear-gradient(135deg, 
                rgba(15, 23, 42, 0.9) 0%, 
                rgba(30, 41, 59, 0.85) 50%,
                rgba(15, 23, 42, 0.9) 100%);
            position: relative;
        }

        .chart-body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, 
                rgba(59, 130, 246, 0.03) 0%, 
                transparent 70%);
            pointer-events: none;
        }

        .chart-footer {
            background: linear-gradient(135deg, 
                rgba(59, 130, 246, 0.04) 0%, 
                rgba(139, 92, 246, 0.03) 100%);
            border-top: 1px solid rgba(59, 130, 246, 0.2);
            position: relative;
        }

        .chart-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(59, 130, 246, 0.2) 50%, 
                transparent 100%);
        }

        /* Professional Animations */
        @keyframes chartLoad {
            0% {
                opacity: 0;
                transform: translateY(20px) scale(0.95);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes pulseGlow {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4);
            }
            50% {
                box-shadow: 0 0 0 8px rgba(59, 130, 246, 0);
            }
        }

        .tradingview-chart-container {
            animation: chartLoad 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .pulse-dot.pulse-success {
            animation: pulse 2s ease-in-out infinite, pulseGlow 2s ease-in-out infinite;
        }

        /* Loading States */
        .chart-loading {
            position: relative;
            overflow: hidden;
        }

        .chart-loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(59, 130, 246, 0.1) 50%, 
                transparent 100%);
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        /* Enhanced Hover Effects */
        .df-panel {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .df-panel:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 
                0 12px 32px rgba(59, 130, 246, 0.2),
                0 4px 16px rgba(59, 130, 246, 0.1);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .derivatives-header h1 {
                font-size: 1.5rem;
            }
            
            .chart-body {
                height: 350px;
                padding: 12px;
            }
            
            .chart-header {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }
            
            .current-value {
                font-size: 16px;
            }

            .chart-controls {
                flex-direction: column;
                align-items: stretch;
                width: 100%;
                gap: 0.75rem;
            }

            .time-range-selector {
                justify-content: center;
                flex-wrap: wrap;
            }

            .time-range-btn {
                flex: 1;
                min-width: 35px;
            }

            .chart-tools {
                justify-content: center;
            }

            .df-panel:hover {
                transform: translateY(-2px) scale(1.01);
            }
        }

        /* Light Mode Support */
        .chart-footer-text {
            color: var(--bs-body-color, #6c757d);
            transition: color 0.3s ease;
        }

        /* Light mode chart styling */
        @media (prefers-color-scheme: light) {
            .tradingview-chart-container {
                background: linear-gradient(135deg, 
                    rgba(248, 250, 252, 0.98) 0%, 
                    rgba(241, 245, 249, 0.98) 50%,
                    rgba(248, 250, 252, 0.98) 100%);
                border: 1px solid rgba(59, 130, 246, 0.2);
                box-shadow: 
                    0 10px 40px rgba(0, 0, 0, 0.1),
                    0 4px 16px rgba(59, 130, 246, 0.05),
                    inset 0 1px 0 rgba(255, 255, 255, 0.8);
            }

            .chart-header {
                background: linear-gradient(135deg, 
                    rgba(59, 130, 246, 0.05) 0%, 
                    rgba(139, 92, 246, 0.03) 100%);
                border-bottom: 1px solid rgba(59, 130, 246, 0.15);
            }

            .chart-header h5 {
                color: #1e293b;
                text-shadow: none;
            }

            .current-value {
                color: #2563eb;
                text-shadow: none;
            }

            .chart-body {
                background: linear-gradient(135deg, 
                    rgba(248, 250, 252, 0.9) 0%, 
                    rgba(241, 245, 249, 0.85) 50%,
                    rgba(248, 250, 252, 0.9) 100%);
            }

            .chart-footer {
                background: linear-gradient(135deg, 
                    rgba(59, 130, 246, 0.03) 0%, 
                    rgba(139, 92, 246, 0.02) 100%);
                border-top: 1px solid rgba(59, 130, 246, 0.15);
            }

            .chart-footer-text {
                color: #64748b !important;
            }

            .time-range-selector {
                background: linear-gradient(135deg, 
                    rgba(241, 245, 249, 0.8) 0%, 
                    rgba(226, 232, 240, 0.8) 100%);
                border: 1px solid rgba(59, 130, 246, 0.15);
            }

            .time-range-btn {
                color: #64748b !important;
            }

            .time-range-btn:hover {
                color: #1e293b !important;
            }

            .chart-tools {
                background: linear-gradient(135deg, 
                    rgba(241, 245, 249, 0.6) 0%, 
                    rgba(226, 232, 240, 0.6) 100%);
                border: 1px solid rgba(59, 130, 246, 0.1);
            }

            .chart-tool-btn {
                color: #64748b !important;
            }

            .chart-tool-btn:hover {
                color: #1e293b !important;
            }
        }

        /* Dark mode enhancements */
        @media (prefers-color-scheme: dark) {
            .tradingview-chart-container {
                box-shadow: 
                    0 12px 48px rgba(0, 0, 0, 0.6),
                    0 4px 16px rgba(59, 130, 246, 0.1),
                    inset 0 1px 0 rgba(255, 255, 255, 0.1);
            }

            .chart-footer-text {
                color: #94a3b8 !important;
            }
        }

        /* ===== REAL-TIME LIQUIDATIONS TABLE STYLES ===== */
        
        /* Real-Time Liquidations Container */
        .realtime-liquidations-container {
            background: linear-gradient(135deg, 
                rgba(15, 23, 42, 0.98) 0%, 
                rgba(30, 41, 59, 0.98) 50%,
                rgba(15, 23, 42, 0.98) 100%);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(59, 130, 246, 0.25);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 
                0 10px 40px rgba(0, 0, 0, 0.4),
                0 4px 16px rgba(59, 130, 246, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            margin-bottom: 2rem;
        }

        .realtime-liquidations-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(59, 130, 246, 0.5) 50%, 
                transparent 100%);
            z-index: 1;
        }

        .realtime-liquidations-container:hover {
            box-shadow: 
                0 16px 48px rgba(0, 0, 0, 0.5),
                0 6px 20px rgba(59, 130, 246, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.12);
            border-color: rgba(59, 130, 246, 0.4);
            transform: translateY(-1px);
        }

        /* Real-Time Header */
        .realtime-header {
            background: linear-gradient(135deg, 
                rgba(59, 130, 246, 0.08) 0%, 
                rgba(139, 92, 246, 0.06) 100%);
            border-bottom: 1px solid rgba(59, 130, 246, 0.25);
            padding: 20px;
            position: relative;
            z-index: 2;
        }

        .realtime-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(59, 130, 246, 0.3) 50%, 
                transparent 100%);
        }

        .realtime-header h5 {
            color: #f1f5f9;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
            font-weight: 600;
            letter-spacing: 0.025em;
        }

        /* Real-Time Body */
        .realtime-body {
            background: linear-gradient(135deg, 
                rgba(15, 23, 42, 0.9) 0%, 
                rgba(30, 41, 59, 0.85) 50%,
                rgba(15, 23, 42, 0.9) 100%);
            position: relative;
            min-height: 400px;
        }

        .realtime-body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, 
                rgba(59, 130, 246, 0.03) 0%, 
                transparent 70%);
            pointer-events: none;
        }

        /* Connection Status */
        .connection-status {
            padding: 40px 20px;
            text-align: center;
        }

        /* Liquidations Table */
        .liquidations-table-container {
            padding: 0;
            max-height: 500px;
            overflow-y: auto;
        }

        .liquidations-table {
            margin: 0;
            background: transparent;
        }

        .liquidations-table thead th {
            background: linear-gradient(135deg, 
                rgba(30, 41, 59, 0.9) 0%, 
                rgba(51, 65, 85, 0.9) 100%);
            border-bottom: 2px solid rgba(59, 130, 246, 0.3);
            color: #e2e8f0;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 16px 12px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .liquidations-table tbody tr {
            border-bottom: 1px solid rgba(59, 130, 246, 0.1);
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .liquidations-table tbody tr:hover {
            background: rgba(59, 130, 246, 0.05);
            transform: translateX(2px);
        }

        .liquidations-table tbody tr.new-liquidation {
            animation: liquidationFlash 1s ease-out;
            background: rgba(59, 130, 246, 0.1);
        }

        @keyframes liquidationFlash {
            0% {
                background: rgba(59, 130, 246, 0.3);
                transform: scale(1.02);
            }
            100% {
                background: transparent;
                transform: scale(1);
            }
        }

        .liquidations-table td {
            padding: 12px;
            vertical-align: middle;
            border: none;
            font-size: 0.875rem;
        }

        /* Symbol Cell */
        .symbol-cell {
            min-width: 140px;
        }

        .symbol-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
            color: white;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .symbol-name {
            font-weight: 700;
            color: #e2e8f0;
            font-size: 0.875rem;
        }

        .base-asset {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 500;
        }

        /* Exchange Cell */
        .exchange-cell {
            min-width: 120px;
        }

        .exchange-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .exchange-icon {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            font-weight: 700;
            color: white;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .exchange-name {
            font-weight: 600;
            color: #e2e8f0;
            font-size: 0.875rem;
        }

        /* Side Cell */
        .side-cell {
            min-width: 80px;
        }

        .side-badge {
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .side-badge.long {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .side-badge.short {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        /* Price Cell */
        .price-cell {
            min-width: 100px;
        }

        .price-value {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            color: #60a5fa;
            font-size: 0.875rem;
        }

        /* Value Cell */
        .value-cell {
            min-width: 100px;
        }

        .value-amount {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .value-amount.small {
            color: #94a3b8;
        }

        .value-amount.medium {
            color: #fbbf24;
        }

        .value-amount.large {
            color: #f59e0b;
        }

        .value-amount.huge {
            color: #dc2626;
            text-shadow: 0 0 8px rgba(220, 38, 38, 0.4);
        }

        /* Time Cell */
        .time-cell {
            min-width: 80px;
        }

        .time-value {
            font-family: 'Courier New', monospace;
            font-size: 0.75rem;
            color: #94a3b8;
        }

        /* Empty State */
        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }

        /* Real-Time Footer */
        .realtime-footer {
            background: linear-gradient(135deg, 
                rgba(59, 130, 246, 0.08) 0%, 
                rgba(139, 92, 246, 0.06) 100%);
            border-top: 2px solid rgba(59, 130, 246, 0.3);
            padding: 16px 20px;
            position: relative;
            backdrop-filter: blur(8px);
        }

        .realtime-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(59, 130, 246, 0.4) 50%, 
                transparent 100%);
        }

        .realtime-footer .stats-info,
        .realtime-footer .connection-info {
            padding: 4px 0;
        }

        .realtime-footer .text-light {
            color: #e2e8f0 !important;
            font-weight: 500;
        }

        .realtime-footer .text-info {
            color: #60a5fa !important;
        }

        .realtime-footer .badge {
            font-size: 0.7rem;
            padding: 4px 8px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .realtime-header {
                padding: 16px;
            }

            .realtime-header .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 16px;
            }

            .liquidations-table-container {
                max-height: 400px;
            }

            .liquidations-table {
                font-size: 0.75rem;
            }

            .liquidations-table td {
                padding: 8px 6px;
            }

            .symbol-cell,
            .exchange-cell {
                min-width: 100px;
            }

            .side-cell,
            .price-cell,
            .value-cell,
            .time-cell {
                min-width: 70px;
            }

            .symbol-name,
            .exchange-name {
                font-size: 0.75rem;
            }

            .base-asset {
                font-size: 0.625rem;
            }

            .side-badge {
                padding: 2px 8px;
                font-size: 0.625rem;
            }

            .price-value,
            .value-amount {
                font-size: 0.75rem;
            }

            .time-value {
                font-size: 0.625rem;
            }
        }

        /* Light Mode Support */
        @media (prefers-color-scheme: light) {
            .realtime-liquidations-container {
                background: linear-gradient(135deg, 
                    rgba(248, 250, 252, 0.98) 0%, 
                    rgba(241, 245, 249, 0.98) 50%,
                    rgba(248, 250, 252, 0.98) 100%);
                border: 1px solid rgba(59, 130, 246, 0.2);
                box-shadow: 
                    0 10px 40px rgba(0, 0, 0, 0.1),
                    0 4px 16px rgba(59, 130, 246, 0.05),
                    inset 0 1px 0 rgba(255, 255, 255, 0.8);
            }

            .realtime-header {
                background: linear-gradient(135deg, 
                    rgba(59, 130, 246, 0.05) 0%, 
                    rgba(139, 92, 246, 0.03) 100%);
                border-bottom: 1px solid rgba(59, 130, 246, 0.15);
            }

            .realtime-header h5 {
                color: #1e293b;
                text-shadow: none;
            }

            .realtime-body {
                background: linear-gradient(135deg, 
                    rgba(248, 250, 252, 0.9) 0%, 
                    rgba(241, 245, 249, 0.85) 50%,
                    rgba(248, 250, 252, 0.9) 100%);
            }

            .liquidations-table thead th {
                background: linear-gradient(135deg, 
                    rgba(226, 232, 240, 0.9) 0%, 
                    rgba(203, 213, 225, 0.9) 100%);
                color: #1e293b;
                border-bottom: 2px solid rgba(59, 130, 246, 0.2);
            }

            .liquidations-table tbody tr:hover {
                background: rgba(59, 130, 246, 0.03);
            }

            .symbol-name,
            .exchange-name {
                color: #1e293b;
            }

            .base-asset {
                color: #64748b;
            }

            .price-value {
                color: #2563eb;
            }

            .time-value {
                color: #64748b;
            }

            .realtime-footer {
                background: linear-gradient(135deg, 
                    rgba(59, 130, 246, 0.03) 0%, 
                    rgba(139, 92, 246, 0.02) 100%);
                border-top: 1px solid rgba(59, 130, 246, 0.15);
            }
        }

        /* ===== TOTAL LIQUIDATIONS TABLE STYLES ===== */
        
        /* Total Liquidations Container */
        .total-liquidations-container {
            background: linear-gradient(135deg, 
                rgba(15, 23, 42, 0.98) 0%, 
                rgba(30, 41, 59, 0.98) 50%,
                rgba(15, 23, 42, 0.98) 100%);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(59, 130, 246, 0.25);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 
                0 10px 40px rgba(0, 0, 0, 0.4),
                0 4px 16px rgba(59, 130, 246, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            margin-bottom: 2rem;
        }

        .total-liquidations-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(59, 130, 246, 0.5) 50%, 
                transparent 100%);
            z-index: 1;
        }

        .total-liquidations-container:hover {
            box-shadow: 
                0 16px 48px rgba(0, 0, 0, 0.5),
                0 6px 20px rgba(59, 130, 246, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.12);
            border-color: rgba(59, 130, 246, 0.4);
            transform: translateY(-1px);
        }

        /* Total Liquidations Header */
        .total-liquidations-header {
            background: linear-gradient(135deg, 
                rgba(59, 130, 246, 0.08) 0%, 
                rgba(139, 92, 246, 0.06) 100%);
            border-bottom: 1px solid rgba(59, 130, 246, 0.25);
            padding: 20px;
            position: relative;
            z-index: 2;
        }

        .total-liquidations-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(59, 130, 246, 0.3) 50%, 
                transparent 100%);
        }

        .total-liquidations-header h5 {
            color: #f1f5f9;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
            font-weight: 600;
            letter-spacing: 0.025em;
        }

        /* Total Liquidations Body */
        .total-liquidations-body {
            background: linear-gradient(135deg, 
                rgba(15, 23, 42, 0.9) 0%, 
                rgba(30, 41, 59, 0.85) 50%,
                rgba(15, 23, 42, 0.9) 100%);
            position: relative;
            min-height: 400px;
        }

        .total-liquidations-body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, 
                rgba(59, 130, 246, 0.03) 0%, 
                transparent 70%);
            pointer-events: none;
        }

        /* Total Liquidations Table */
        .total-liquidations-table-container {
            padding: 0;
            max-height: 600px;
            overflow-y: auto;
        }

        .total-liquidations-table {
            margin: 0;
            background: transparent;
        }

        .total-liquidations-table thead th {
            background: linear-gradient(135deg, 
                rgba(30, 41, 59, 0.9) 0%, 
                rgba(51, 65, 85, 0.9) 100%);
            border-bottom: 2px solid rgba(59, 130, 246, 0.3);
            color: #e2e8f0;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 16px 12px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .total-liquidations-table tbody tr {
            border-bottom: 1px solid rgba(59, 130, 246, 0.1);
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .total-liquidations-table tbody tr:hover {
            background: rgba(59, 130, 246, 0.05);
            transform: translateX(2px);
        }

        .total-liquidations-table td {
            padding: 12px;
            vertical-align: middle;
            border: none;
            font-size: 0.875rem;
        }

        /* Ranking Cell */
        .ranking-cell {
            min-width: 80px;
            text-align: center;
        }

        .ranking-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .ranking-number.top-rank {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #1f2937;
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
        }

        .ranking-number.high-rank {
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(96, 165, 250, 0.3);
        }

        .ranking-number.normal-rank {
            background: rgba(75, 85, 99, 0.5);
            color: #d1d5db;
            border: 1px solid rgba(156, 163, 175, 0.3);
        }

        /* Symbol Cell */
        .symbol-cell {
            min-width: 140px;
        }

        .coin-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
            color: white;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .coin-symbol {
            font-weight: 700;
            color: #e2e8f0;
            font-size: 0.875rem;
        }

        .coin-name {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 500;
        }

        /* Price Cell */
        .price-cell {
            min-width: 100px;
        }

        .price-value {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            color: #60a5fa;
            font-size: 0.875rem;
        }

        /* Change Cell */
        .change-cell {
            min-width: 100px;
        }

        .change-value {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 0.875rem;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .change-value.positive {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
        }

        .change-value.negative {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }

        /* Liquidation Cells */
        .liq-cell {
            min-width: 90px;
            text-align: center;
        }

        .liq-value {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 0.75rem;
            padding: 6px 10px;
            border-radius: 6px;
            display: inline-block;
            min-width: 70px;
        }

        .liq-value.long-liq {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .liq-value.short-liq {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        /* Total Liquidations Footer */
        .total-liquidations-footer {
            background: linear-gradient(135deg, 
                rgba(59, 130, 246, 0.08) 0%, 
                rgba(139, 92, 246, 0.06) 100%);
            border-top: 2px solid rgba(59, 130, 246, 0.3);
            padding: 16px 20px;
            position: relative;
            backdrop-filter: blur(8px);
        }

        .total-liquidations-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(59, 130, 246, 0.4) 50%, 
                transparent 100%);
        }

        .total-liquidations-footer .text-light {
            color: #e2e8f0 !important;
            font-weight: 500;
        }

        .total-liquidations-footer .text-info {
            color: #60a5fa !important;
        }

        .total-liquidations-footer .badge {
            font-size: 0.7rem;
            padding: 4px 8px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .total-liquidations-header {
                padding: 16px;
            }

            .total-liquidations-header .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 16px;
            }

            .total-liquidations-table-container {
                max-height: 500px;
            }

            .total-liquidations-table {
                font-size: 0.75rem;
            }

            .total-liquidations-table td {
                padding: 8px 6px;
            }

            .symbol-cell {
                min-width: 120px;
            }

            .ranking-cell,
            .price-cell,
            .change-cell,
            .liq-cell {
                min-width: 80px;
            }

            .coin-symbol {
                font-size: 0.75rem;
            }

            .coin-name {
                font-size: 0.625rem;
            }

            .ranking-number {
                width: 28px;
                height: 28px;
                font-size: 0.75rem;
            }

            .price-value,
            .change-value {
                font-size: 0.75rem;
            }

            .liq-value {
                font-size: 0.625rem;
                padding: 4px 6px;
                min-width: 60px;
            }
        }

        /* Light Mode Support */
        @media (prefers-color-scheme: light) {
            .total-liquidations-container {
                background: linear-gradient(135deg, 
                    rgba(248, 250, 252, 0.98) 0%, 
                    rgba(241, 245, 249, 0.98) 50%,
                    rgba(248, 250, 252, 0.98) 100%);
                border: 1px solid rgba(59, 130, 246, 0.2);
                box-shadow: 
                    0 10px 40px rgba(0, 0, 0, 0.1),
                    0 4px 16px rgba(59, 130, 246, 0.05),
                    inset 0 1px 0 rgba(255, 255, 255, 0.8);
            }

            .total-liquidations-header {
                background: linear-gradient(135deg, 
                    rgba(59, 130, 246, 0.05) 0%, 
                    rgba(139, 92, 246, 0.03) 100%);
                border-bottom: 1px solid rgba(59, 130, 246, 0.15);
            }

            .total-liquidations-header h5 {
                color: #1e293b;
                text-shadow: none;
            }

            .total-liquidations-body {
                background: linear-gradient(135deg, 
                    rgba(248, 250, 252, 0.9) 0%, 
                    rgba(241, 245, 249, 0.85) 50%,
                    rgba(248, 250, 252, 0.9) 100%);
            }

            .total-liquidations-table thead th {
                background: linear-gradient(135deg, 
                    rgba(226, 232, 240, 0.9) 0%, 
                    rgba(203, 213, 225, 0.9) 100%);
                color: #1e293b;
                border-bottom: 2px solid rgba(59, 130, 246, 0.2);
            }

            .total-liquidations-table tbody tr:hover {
                background: rgba(59, 130, 246, 0.03);
            }

            .coin-symbol {
                color: #1e293b;
            }

            .coin-name {
                color: #64748b;
            }

            .price-value {
                color: #2563eb;
            }

            .ranking-number.normal-rank {
                background: rgba(226, 232, 240, 0.5);
                color: #64748b;
                border: 1px solid rgba(148, 163, 184, 0.3);
            }

            .total-liquidations-footer {
                background: linear-gradient(135deg, 
                    rgba(59, 130, 246, 0.03) 0%, 
                    rgba(139, 92, 246, 0.02) 100%);
                border-top: 2px solid rgba(59, 130, 246, 0.15);
            }

            .total-liquidations-footer .text-light {
                color: #64748b !important;
            }

            .total-liquidations-footer .text-info {
                color: #2563eb !important;
            }
        }

        /* ===== TOTAL LIQUIDATIONS CHART STYLES ===== */
        
        /* Total Liquidations Chart Container */
        .total-liquidations-chart-container {
            background: linear-gradient(135deg, 
                rgba(15, 23, 42, 0.98) 0%, 
                rgba(30, 41, 59, 0.98) 50%,
                rgba(15, 23, 42, 0.98) 100%);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(59, 130, 246, 0.25);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 
                0 10px 40px rgba(0, 0, 0, 0.4),
                0 4px 16px rgba(59, 130, 246, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            margin-bottom: 2rem;
        }

        .total-liquidations-chart-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(59, 130, 246, 0.5) 50%, 
                transparent 100%);
            z-index: 1;
        }

        .total-liquidations-chart-container:hover {
            box-shadow: 
                0 16px 48px rgba(0, 0, 0, 0.5),
                0 6px 20px rgba(59, 130, 246, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.12);
            border-color: rgba(59, 130, 246, 0.4);
            transform: translateY(-1px);
        }

        /* Chart Canvas Container */
        .chart-canvas-container {
            padding: 20px;
            height: 440px;
            position: relative;
        }

        .chart-loading {
            padding: 20px;
        }

        /* Chart Footer */
        .chart-footer {
            background: linear-gradient(135deg, 
                rgba(59, 130, 246, 0.08) 0%, 
                rgba(139, 92, 246, 0.06) 100%);
            border-top: 2px solid rgba(59, 130, 246, 0.3);
            padding: 16px 20px;
            position: relative;
            backdrop-filter: blur(8px);
        }

        .chart-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(59, 130, 246, 0.4) 50%, 
                transparent 100%);
        }

        .chart-footer .text-light {
            color: #e2e8f0 !important;
            font-weight: 500;
        }

        .chart-footer .text-info {
            color: #60a5fa !important;
        }

        .chart-footer .text-warning {
            color: #fbbf24 !important;
        }

        .chart-footer .text-success {
            color: #22c55e !important;
        }

        .chart-footer .badge {
            font-size: 0.7rem;
            padding: 4px 8px;
        }

        /* Responsive Design for Chart */
        @media (max-width: 768px) {
            .chart-header {
                padding: 16px;
            }

            .chart-header .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 16px;
            }

            .chart-canvas-container {
                padding: 12px;
                height: 350px;
            }

            .chart-footer {
                padding: 12px 16px;
            }

            .chart-footer .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 8px;
            }
        }

        /* Light Mode Support for Chart */
        @media (prefers-color-scheme: light) {
            .total-liquidations-chart-container {
                background: linear-gradient(135deg, 
                    rgba(248, 250, 252, 0.98) 0%, 
                    rgba(241, 245, 249, 0.98) 50%,
                    rgba(248, 250, 252, 0.98) 100%);
                border: 1px solid rgba(59, 130, 246, 0.2);
                box-shadow: 
                    0 10px 40px rgba(0, 0, 0, 0.1),
                    0 4px 16px rgba(59, 130, 246, 0.05),
                    inset 0 1px 0 rgba(255, 255, 255, 0.8);
            }

            .chart-footer {
                background: linear-gradient(135deg, 
                    rgba(59, 130, 246, 0.03) 0%, 
                    rgba(139, 92, 246, 0.02) 100%);
                border-top: 2px solid rgba(59, 130, 246, 0.15);
            }

            .chart-footer .text-light {
                color: #64748b !important;
            }

            .chart-footer .text-info {
                color: #2563eb !important;
            }

            .chart-footer .text-warning {
                color: #d97706 !important;
            }

            .chart-footer .text-success {
                color: #16a34a !important;
            }
        }

        /* ===== EXCHANGE LIQUIDATIONS TABLE STYLES ===== */
        
        /* Exchange Liquidations Container */
        .exchange-liquidations-container {
            background: linear-gradient(135deg, 
                rgba(15, 23, 42, 0.98) 0%, 
                rgba(30, 41, 59, 0.98) 50%,
                rgba(15, 23, 42, 0.98) 100%);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(59, 130, 246, 0.25);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 
                0 10px 40px rgba(0, 0, 0, 0.4),
                0 4px 16px rgba(59, 130, 246, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            margin-bottom: 2rem;
        }

        .exchange-liquidations-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(59, 130, 246, 0.5) 50%, 
                transparent 100%);
            z-index: 1;
        }

        .exchange-liquidations-container:hover {
            box-shadow: 
                0 16px 48px rgba(0, 0, 0, 0.5),
                0 6px 20px rgba(59, 130, 246, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.12);
            border-color: rgba(59, 130, 246, 0.4);
            transform: translateY(-1px);
        }

        /* Exchange Header */
        .exchange-header {
            background: linear-gradient(135deg, 
                rgba(59, 130, 246, 0.08) 0%, 
                rgba(139, 92, 246, 0.06) 100%);
            border-bottom: 1px solid rgba(59, 130, 246, 0.25);
            padding: 20px;
            position: relative;
            z-index: 2;
        }

        .exchange-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(59, 130, 246, 0.3) 50%, 
                transparent 100%);
        }

        .exchange-header h5 {
            color: #f1f5f9;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
            font-weight: 600;
            letter-spacing: 0.025em;
        }

        /* Exchange Body */
        .exchange-body {
            background: linear-gradient(135deg, 
                rgba(15, 23, 42, 0.9) 0%, 
                rgba(30, 41, 59, 0.85) 50%,
                rgba(15, 23, 42, 0.9) 100%);
            position: relative;
            min-height: 300px;
        }

        .exchange-body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, 
                rgba(59, 130, 246, 0.03) 0%, 
                transparent 70%);
            pointer-events: none;
        }

        /* Exchange Table */
        .exchange-table-container {
            padding: 0;
            max-height: 500px;
            overflow-y: auto;
        }

        .exchange-liquidations-table {
            margin: 0;
            background: transparent;
        }

        .exchange-liquidations-table thead th {
            background: linear-gradient(135deg, 
                rgba(30, 41, 59, 0.9) 0%, 
                rgba(51, 65, 85, 0.9) 100%);
            border-bottom: 2px solid rgba(59, 130, 246, 0.3);
            color: #e2e8f0;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 16px 12px;
            position: sticky;
            top: 0;
            z-index: 10;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .exchange-liquidations-table thead th:hover {
            background: linear-gradient(135deg, 
                rgba(59, 130, 246, 0.15) 0%, 
                rgba(139, 92, 246, 0.1) 100%);
        }

        .exchange-liquidations-table tbody tr {
            border-bottom: 1px solid rgba(59, 130, 246, 0.1);
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .exchange-liquidations-table tbody tr:hover {
            background: rgba(59, 130, 246, 0.05);
            transform: translateX(2px);
        }

        .exchange-liquidations-table tbody tr.all-row {
            background: rgba(34, 197, 94, 0.05);
            border-bottom: 2px solid rgba(34, 197, 94, 0.3);
        }

        .exchange-liquidations-table tbody tr.all-row:hover {
            background: rgba(34, 197, 94, 0.1);
        }

        .exchange-liquidations-table td {
            padding: 12px;
            vertical-align: middle;
            border: none;
            font-size: 0.875rem;
        }

        /* Exchange Cell */
        .exchange-cell {
            min-width: 140px;
        }

        .exchange-icon {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            font-weight: 700;
            color: white;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .exchange-name {
            font-weight: 700;
            color: #e2e8f0;
            font-size: 0.875rem;
        }

        /* Value Cells */
        .liquidations-cell,
        .long-cell,
        .short-cell {
            min-width: 100px;
            text-align: right;
        }

        .liquidation-value {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            color: #60a5fa;
            font-size: 0.875rem;
        }

        .long-value {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            color: #ef4444;
            font-size: 0.875rem;
        }

        .short-value {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            color: #22c55e;
            font-size: 0.875rem;
        }

        /* Rate Cell */
        .rate-cell {
            min-width: 80px;
            text-align: center;
        }

        .rate-value {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 0.875rem;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .rate-value.all-rate {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
        }

        .rate-value.high-rate {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }

        .rate-value.medium-rate {
            background: rgba(251, 191, 36, 0.15);
            color: #fbbf24;
        }

        .rate-value.low-rate {
            background: rgba(148, 163, 184, 0.15);
            color: #94a3b8;
        }

        /* Ratio Cell */
        .ratio-cell {
            min-width: 80px;
            text-align: center;
        }

        .ratio-value {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 0.875rem;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .ratio-value.long-heavy {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }

        .ratio-value.balanced {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
        }

        .ratio-value.short-heavy {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
        }

        /* Exchange Footer */
        .exchange-footer {
            background: linear-gradient(135deg, 
                rgba(59, 130, 246, 0.08) 0%, 
                rgba(139, 92, 246, 0.06) 100%);
            border-top: 2px solid rgba(59, 130, 246, 0.3);
            padding: 16px 20px;
            position: relative;
            backdrop-filter: blur(8px);
        }

        .exchange-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(59, 130, 246, 0.4) 50%, 
                transparent 100%);
        }

        .exchange-footer .text-light {
            color: #e2e8f0 !important;
            font-weight: 500;
        }

        .exchange-footer .text-info {
            color: #60a5fa !important;
        }

        .exchange-footer .text-warning {
            color: #fbbf24 !important;
        }

        .exchange-footer .text-success {
            color: #22c55e !important;
        }

        .exchange-footer .badge {
            font-size: 0.7rem;
            padding: 4px 8px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .exchange-header {
                padding: 16px;
            }

            .exchange-header .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 16px;
            }

            .exchange-table-container {
                max-height: 400px;
            }

            .exchange-liquidations-table {
                font-size: 0.75rem;
            }

            .exchange-liquidations-table td {
                padding: 8px 6px;
            }

            .exchange-cell {
                min-width: 100px;
            }

            .liquidations-cell,
            .long-cell,
            .short-cell,
            .rate-cell,
            .ratio-cell {
                min-width: 70px;
            }

            .exchange-name {
                font-size: 0.75rem;
            }

            .liquidation-value,
            .long-value,
            .short-value,
            .rate-value,
            .ratio-value {
                font-size: 0.75rem;
            }
        }

        /* Light Mode Support */
        @media (prefers-color-scheme: light) {
            .exchange-liquidations-container {
                background: linear-gradient(135deg, 
                    rgba(248, 250, 252, 0.98) 0%, 
                    rgba(241, 245, 249, 0.98) 50%,
                    rgba(248, 250, 252, 0.98) 100%);
                border: 1px solid rgba(59, 130, 246, 0.2);
                box-shadow: 
                    0 10px 40px rgba(0, 0, 0, 0.1),
                    0 4px 16px rgba(59, 130, 246, 0.05),
                    inset 0 1px 0 rgba(255, 255, 255, 0.8);
            }

            .exchange-header {
                background: linear-gradient(135deg, 
                    rgba(59, 130, 246, 0.05) 0%, 
                    rgba(139, 92, 246, 0.03) 100%);
                border-bottom: 1px solid rgba(59, 130, 246, 0.15);
            }

            .exchange-header h5 {
                color: #1e293b;
                text-shadow: none;
            }

            .exchange-body {
                background: linear-gradient(135deg, 
                    rgba(248, 250, 252, 0.9) 0%, 
                    rgba(241, 245, 249, 0.85) 50%,
                    rgba(248, 250, 252, 0.9) 100%);
            }

            .exchange-liquidations-table thead th {
                background: linear-gradient(135deg, 
                    rgba(226, 232, 240, 0.9) 0%, 
                    rgba(203, 213, 225, 0.9) 100%);
                color: #1e293b;
                border-bottom: 2px solid rgba(59, 130, 246, 0.2);
            }

            .exchange-liquidations-table tbody tr:hover {
                background: rgba(59, 130, 246, 0.03);
            }

            .exchange-name {
                color: #1e293b;
            }

            .liquidation-value {
                color: #2563eb;
            }

            .long-value {
                color: #dc2626;
            }

            .short-value {
                color: #16a34a;
            }

            .exchange-footer {
                background: linear-gradient(135deg, 
                    rgba(59, 130, 246, 0.03) 0%, 
                    rgba(139, 92, 246, 0.02) 100%);
                border-top: 2px solid rgba(59, 130, 246, 0.15);
            }

            .exchange-footer .text-light {
                color: #64748b !important;
            }

            .exchange-footer .text-info {
                color: #2563eb !important;
            }

            .exchange-footer .text-warning {
                color: #d97706 !important;
            }

            .exchange-footer .text-success {
                color: #16a34a !important;
            }
        }

        /* ===== EXCHANGE DOMINANCE HEATMAP STYLES ===== */
        
        /* Heatmap Container - Professional CryptoQuant Level */
        .heatmap-container {
            background: linear-gradient(135deg, 
                rgba(15, 23, 42, 0.98) 0%, 
                rgba(30, 41, 59, 0.98) 50%,
                rgba(15, 23, 42, 0.98) 100%);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(59, 130, 246, 0.25);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 
                0 10px 40px rgba(0, 0, 0, 0.4),
                0 4px 16px rgba(59, 130, 246, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            margin-bottom: 2rem;
        }

        .heatmap-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(59, 130, 246, 0.5) 50%, 
                transparent 100%);
            z-index: 1;
        }

        .heatmap-container:hover {
            box-shadow: 
                0 16px 48px rgba(0, 0, 0, 0.5),
                0 6px 20px rgba(59, 130, 246, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.12);
            border-color: rgba(59, 130, 246, 0.4);
            transform: translateY(-1px);
        }

        /* Heatmap Header */
        .heatmap-header {
            background: linear-gradient(135deg, 
                rgba(59, 130, 246, 0.08) 0%, 
                rgba(139, 92, 246, 0.06) 100%);
            border-bottom: 1px solid rgba(59, 130, 246, 0.25);
            padding: 20px;
            position: relative;
            z-index: 2;
        }

        .heatmap-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(59, 130, 246, 0.3) 50%, 
                transparent 100%);
        }

        .heatmap-header h5 {
            color: #f1f5f9;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
            font-weight: 600;
            letter-spacing: 0.025em;
        }

        /* Heatmap Time Selector */
        .heatmap-time-selector {
            background: linear-gradient(135deg, 
                rgba(30, 41, 59, 0.8) 0%, 
                rgba(51, 65, 85, 0.8) 100%);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 8px;
            padding: 0.25rem;
            box-shadow: 
                0 4px 12px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        .heatmap-time-btn {
            padding: 0.375rem 0.75rem !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            border: none !important;
            border-radius: 6px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            min-width: 40px;
            position: relative;
            overflow: hidden;
            color: #94a3b8 !important;
            background: transparent !important;
        }

        .heatmap-time-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, 
                rgba(59, 130, 246, 0.1) 0%, 
                rgba(139, 92, 246, 0.1) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .heatmap-time-btn:hover {
            color: #e2e8f0 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.2) !important;
        }

        .heatmap-time-btn:hover::before {
            opacity: 1;
        }

        .heatmap-time-btn.btn-primary {
            background: linear-gradient(135deg, 
                #3b82f6 0%, 
                #2563eb 100%) !important;
            color: white !important;
            box-shadow: 
                0 4px 12px rgba(59, 130, 246, 0.4),
                0 2px 4px rgba(59, 130, 246, 0.3) !important;
            transform: translateY(-1px);
        }

        .heatmap-time-btn.btn-primary::before {
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.1) 0%, 
                rgba(255, 255, 255, 0.05) 100%);
            opacity: 1;
        }

        /* Heatmap Body */
        .heatmap-body {
            padding: 20px;
            background: linear-gradient(135deg, 
                rgba(15, 23, 42, 0.9) 0%, 
                rgba(30, 41, 59, 0.85) 50%,
                rgba(15, 23, 42, 0.9) 100%);
            position: relative;
        }

        .heatmap-body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, 
                rgba(59, 130, 246, 0.03) 0%, 
                transparent 70%);
            pointer-events: none;
        }

        /* Heatmap Canvas */
        .heatmap-canvas-container {
            background: rgba(15, 23, 42, 0.5);
            border-radius: 8px;
            padding: 20px;
            border: 1px solid rgba(59, 130, 246, 0.1);
            position: relative;
            overflow: hidden;
            min-height: 340px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .heatmap-canvas-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, 
                rgba(59, 130, 246, 0.02) 25%, 
                transparent 25%, 
                transparent 75%, 
                rgba(59, 130, 246, 0.02) 75%);
            background-size: 20px 20px;
            pointer-events: none;
        }

        #exchangeDominanceHeatmap {
            width: 100%;
            height: 300px;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            background: #1e293b;
            display: block;
        }

        /* Heatmap Legend */
        .heatmap-legend {
            margin-top: 20px;
            padding: 16px;
            background: rgba(30, 41, 59, 0.4);
            border-radius: 8px;
            border: 1px solid rgba(59, 130, 246, 0.15);
        }

        .legend-scale {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .legend-label {
            color: #e2e8f0;
            font-size: 0.875rem;
            font-weight: 600;
            min-width: 100px;
        }

        .legend-gradient {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .legend-color-bar {
            width: 200px;
            height: 12px;
            background: linear-gradient(90deg, 
                #1e293b 0%,     /* Low dominance - Dark */
                #374151 20%,    /* Low-Medium */
                #f59e0b 40%,    /* Medium - Amber */
                #f97316 60%,    /* Medium-High - Orange */
                #dc2626 80%,    /* High - Red */
                #991b1b 100%    /* Very High - Dark Red */
            );
            border-radius: 6px;
            border: 1px solid rgba(59, 130, 246, 0.2);
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .legend-labels {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            color: #94a3b8;
            width: 200px;
        }

        .legend-info .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }

        /* Heatmap Rankings */
        .heatmap-rankings {
            padding: 20px;
            background: linear-gradient(135deg, 
                rgba(59, 130, 246, 0.04) 0%, 
                rgba(139, 92, 246, 0.03) 100%);
            border-top: 1px solid rgba(59, 130, 246, 0.2);
        }

        .rankings-table h6,
        .market-insights h6 {
            color: #f1f5f9;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .rankings-table .table {
            background: rgba(15, 23, 42, 0.8) !important;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: #e2e8f0 !important;
        }

        .rankings-table .table th {
            background: rgba(59, 130, 246, 0.15) !important;
            color: #e2e8f0 !important;
            font-weight: 600;
            font-size: 0.875rem;
            border: none !important;
            padding: 12px;
        }

        .rankings-table .table td {
            color: #f1f5f9 !important;
            border: none !important;
            padding: 12px;
            border-bottom: 1px solid rgba(59, 130, 246, 0.1) !important;
            background: transparent !important;
        }

        .rankings-table .table tbody tr {
            background: rgba(15, 23, 42, 0.6) !important;
        }

        .rankings-table .table tbody tr:hover {
            background: rgba(59, 130, 246, 0.15) !important;
        }

        /* Rank Badge */
        .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .rank-badge.rank-1 {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #1a1a1a;
            box-shadow: 0 2px 8px rgba(255, 215, 0, 0.4);
        }

        .rank-badge.rank-2 {
            background: linear-gradient(135deg, #c0c0c0, #e5e5e5);
            color: #1a1a1a;
            box-shadow: 0 2px 8px rgba(192, 192, 192, 0.4);
        }

        .rank-badge.rank-3 {
            background: linear-gradient(135deg, #cd7f32, #daa520);
            color: #fff;
            box-shadow: 0 2px 8px rgba(205, 127, 50, 0.4);
        }

        .rank-badge.rank-other {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        /* Exchange Indicator */
        .exchange-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.3);
        }

        /* Market Share Bar */
        .market-share-bar {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 120px;
        }

        .share-percentage {
            font-size: 0.875rem;
            font-weight: 600;
            color: #e2e8f0;
        }

        .share-bar {
            width: 100%;
            height: 8px;
            background: rgba(30, 41, 59, 0.6);
            border-radius: 4px;
            overflow: hidden;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .share-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.2);
        }

        /* Trend Indicator */
        .trend-indicator {
            font-size: 1.2rem;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }

        /* Market Insights */
        .market-insights {
            background: rgba(15, 23, 42, 0.3);
            border-radius: 8px;
            padding: 16px;
            border: 1px solid rgba(59, 130, 246, 0.1);
        }

        .insights-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .insight-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px;
            border-radius: 8px;
            border-left: 3px solid;
            transition: all 0.3s ease;
        }

        .insight-item.insight-bullish {
            background: rgba(34, 197, 94, 0.1);
            border-left-color: #22c55e;
        }

        .insight-item.insight-bearish {
            background: rgba(239, 68, 68, 0.1);
            border-left-color: #ef4444;
        }

        .insight-item.insight-neutral {
            background: rgba(59, 130, 246, 0.1);
            border-left-color: #3b82f6;
        }

        .insight-item.insight-warning {
            background: rgba(245, 158, 11, 0.1);
            border-left-color: #f59e0b;
        }

        .insight-item:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .insight-icon {
            font-size: 1.25rem;
            min-width: 24px;
            text-align: center;
        }

        .insight-content {
            flex: 1;
        }

        .insight-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #e2e8f0;
            margin-bottom: 4px;
        }

        .insight-description {
            font-size: 0.75rem;
            color: #94a3b8;
            line-height: 1.4;
        }

        /* Loading State */
        .heatmap-loading {
            background: rgba(15, 23, 42, 0.5);
            border-radius: 8px;
            border: 1px solid rgba(59, 130, 246, 0.1);
        }

        /* ===== COINGLASS-STYLE COMPREHENSIVE TABLE ===== */
        
        .coinglass-table-container {
            background: linear-gradient(135deg, 
                rgba(15, 23, 42, 0.95) 0%, 
                rgba(30, 41, 59, 0.95) 100%) !important;
            border-radius: 8px;
            border: 1px solid rgba(59, 130, 246, 0.2);
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .coinglass-table {
            margin: 0;
            background: rgba(15, 23, 42, 0.8) !important;
            font-family: 'Inter', system-ui, sans-serif;
            font-size: 13px;
            color: #e2e8f0 !important;
        }

        .coinglass-table thead th {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: #e2e8f0;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 16px 12px;
            border: none;
            border-bottom: 2px solid rgba(59, 130, 246, 0.3);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .coinglass-table tbody tr {
            border-bottom: 1px solid rgba(59, 130, 246, 0.1) !important;
            transition: all 0.2s ease;
            background: rgba(15, 23, 42, 0.6) !important;
        }

        .coinglass-table tbody tr:hover {
            background: rgba(59, 130, 246, 0.15) !important;
            transform: translateX(2px);
        }

        .coinglass-table td {
            padding: 14px 12px;
            border: none !important;
            vertical-align: middle;
            color: #e2e8f0 !important;
            background: transparent !important;
        }

        /* All Row Special Styling */
        .all-row {
            background: linear-gradient(135deg, 
                rgba(59, 130, 246, 0.1) 0%, 
                rgba(139, 92, 246, 0.05) 100%);
            border-bottom: 2px solid rgba(59, 130, 246, 0.2) !important;
        }

        .all-badge {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
        }

        /* Column Specific Styling */
        .rank-col { width: 60px; text-align: center; }
        .exchange-col { width: 140px; }
        .oi-btc-col, .oi-usd-col { width: 120px; text-align: right; }
        .rate-col { width: 80px; text-align: center; }
        .change-1h-col, .change-4h-col, .change-24h-col { width: 100px; text-align: center; }
        .dominance-col { width: 140px; }

        .rank-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
            border-radius: 50%;
            font-size: 11px;
            font-weight: 700;
        }

        .exchange-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .exchange-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .exchange-name {
            font-weight: 600;
            color: #e2e8f0 !important;
        }

        /* Force Dark Theme Override */
        .table-dark,
        .table-dark > th,
        .table-dark > td {
            background-color: rgba(15, 23, 42, 0.8) !important;
            color: #e2e8f0 !important;
            border-color: rgba(59, 130, 246, 0.1) !important;
        }

        .coinglass-table * {
            color: inherit !important;
        }

        /* Bootstrap Override for Dark Theme */
        .table-dark {
            --bs-table-bg: rgba(15, 23, 42, 0.8) !important;
            --bs-table-color: #e2e8f0 !important;
            --bs-table-border-color: rgba(59, 130, 246, 0.1) !important;
            --bs-table-striped-bg: rgba(30, 41, 59, 0.5) !important;
            --bs-table-hover-bg: rgba(59, 130, 246, 0.15) !important;
        }

        .coinglass-table.table-dark th,
        .coinglass-table.table-dark td {
            background-color: var(--bs-table-bg) !important;
            color: var(--bs-table-color) !important;
            border-bottom-color: var(--bs-table-border-color) !important;
        }

        .coinglass-table.table-dark tbody tr:hover {
            background-color: var(--bs-table-hover-bg) !important;
        }

        .oi-btc-cell, .oi-usd-cell {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #f1f5f9;
        }

        .rate-cell {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            color: #60a5fa;
        }

        .change-cell {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            text-align: center;
        }

        .change-cell.positive {
            color: #22c55e;
        }

        .change-cell.negative {
            color: #ef4444;
        }

        .change-cell.neutral {
            color: #94a3b8;
        }

        /* Dominance Bar */
        .dominance-bar {
            position: relative;
            height: 24px;
            background: rgba(30, 41, 59, 0.6);
            border-radius: 4px;
            overflow: hidden;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .dominance-bar.full {
            background: linear-gradient(90deg, 
                #22c55e 0%, 
                #16a34a 100%);
        }

        .dominance-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 11px;
            font-weight: 700;
            color: #ffffff;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
            z-index: 2;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .coinglass-table {
                font-size: 12px;
            }
            
            .change-1h-col {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .coinglass-table {
                font-size: 11px;
            }
            
            .oi-btc-col,
            .change-4h-col {
                display: none;
            }
            
            .coinglass-table td {
                padding: 10px 8px;
            }
        }

        @media (max-width: 480px) {
            .rate-col {
                display: none;
            }
            
            .exchange-col {
                width: 100px;
            }
            
            .dominance-col {
                width: 100px;
            }
        }

        /* Light Mode Support */
        @media (prefers-color-scheme: light) {
            .coinglass-table-container {
                background: rgba(248, 250, 252, 0.5);
                border: 1px solid rgba(59, 130, 246, 0.15);
            }

            .coinglass-table thead th {
                background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
                color: #1e293b;
                border-bottom: 2px solid rgba(59, 130, 246, 0.2);
            }

            .coinglass-table tbody tr:hover {
                background: rgba(59, 130, 246, 0.03);
            }

            .coinglass-table td {
                color: #1e293b;
            }

            .all-row {
                background: linear-gradient(135deg, 
                    rgba(59, 130, 246, 0.05) 0%, 
                    rgba(139, 92, 246, 0.03) 100%);
                border-bottom: 2px solid rgba(59, 130, 246, 0.15) !important;
            }

            .rank-number {
                background: rgba(59, 130, 246, 0.1);
                color: #2563eb;
            }

            .exchange-name {
                color: #1e293b;
            }

            .oi-btc-cell, .oi-usd-cell {
                color: #0f172a;
            }

            .rate-cell {
                color: #2563eb;
            }

            .dominance-bar {
                background: rgba(226, 232, 240, 0.6);
                border: 1px solid rgba(59, 130, 246, 0.15);
            }
        }

        /* ===== LAEVITAS-STYLE GRID ===== */
        
        .laevitas-grid {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            background: #0f1419;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid rgba(59, 130, 246, 0.2);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .grid-header {
            display: grid;
            grid-template-columns: 120px repeat(8, 1fr);
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border-bottom: 2px solid rgba(59, 130, 246, 0.3);
        }

        .laevitas-grid.optimal .grid-header {
            grid-template-columns: 120px repeat(8, 1fr);
        }

        .header-cell {
            padding: 12px 8px;
            text-align: center;
            font-weight: 700;
            color: #e2e8f0;
            border-right: 1px solid rgba(59, 130, 246, 0.2);
            background: linear-gradient(135deg, 
                rgba(59, 130, 246, 0.1) 0%, 
                rgba(139, 92, 246, 0.05) 100%);
        }

        .exchange-header {
            text-align: left;
            padding-left: 16px;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .date-header {
            font-size: 10px;
            font-weight: 600;
        }

        .total-header {
            background: linear-gradient(135deg, 
                rgba(34, 197, 94, 0.1) 0%, 
                rgba(22, 163, 74, 0.05) 100%);
            color: #22c55e;
            font-weight: 700;
        }

        .grid-row {
            display: grid;
            grid-template-columns: 120px repeat(8, 1fr);
            border-bottom: 1px solid rgba(59, 130, 246, 0.1);
            transition: all 0.2s ease;
        }

        .laevitas-grid.optimal .grid-row {
            grid-template-columns: 120px repeat(8, 1fr);
        }

        .grid-row:hover {
            background: rgba(59, 130, 246, 0.05);
            transform: translateX(2px);
        }

        .grid-cell {
            padding: 10px 8px;
            text-align: center;
            border-right: 1px solid rgba(59, 130, 246, 0.1);
            position: relative;
            transition: all 0.2s ease;
        }

        .exchange-cell {
            text-align: left;
            padding-left: 16px;
            background: rgba(15, 23, 42, 0.8);
        }

        .exchange-name {
            font-weight: 700;
            color: #e2e8f0;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        .date-cell {
            text-align: left;
            padding-left: 16px;
            background: rgba(15, 23, 42, 0.8);
        }

        .date-name {
            font-weight: 700;
            color: #e2e8f0;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        .average-row {
            background: linear-gradient(135deg, 
                rgba(34, 197, 94, 0.1) 0%, 
                rgba(22, 163, 74, 0.05) 100%);
            border-top: 2px solid rgba(34, 197, 94, 0.3);
        }

        .average-label {
            background: linear-gradient(135deg, 
                rgba(34, 197, 94, 0.15) 0%, 
                rgba(22, 163, 74, 0.1) 100%);
        }

        .average-label .date-name {
            color: #22c55e;
            font-weight: 700;
        }

        .data-cell {
            cursor: pointer;
            font-weight: 600;
            position: relative;
            overflow: hidden;
        }

        .data-cell::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, 
                rgba(255, 255, 255, 0.1) 0%, 
                transparent 50%, 
                rgba(255, 255, 255, 0.05) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .data-cell:hover::before {
            opacity: 1;
        }

        .data-cell:hover {
            transform: scale(1.05);
            z-index: 10;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .cell-value {
            color: #ffffff;
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 2;
        }

        .total-cell {
            background: linear-gradient(135deg, 
                rgba(15, 23, 42, 0.9) 0%, 
                rgba(30, 41, 59, 0.9) 100%);
            border-left: 2px solid rgba(34, 197, 94, 0.3);
        }

        .total-value {
            color: #22c55e;
            font-weight: 700;
            font-size: 12px;
        }

        /* Laevitas Tooltip */
        .laevitas-tooltip {
            position: absolute;
            background: linear-gradient(135deg, 
                rgba(15, 23, 42, 0.98) 0%, 
                rgba(30, 41, 59, 0.98) 100%);
            color: #e2e8f0;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-family: 'Inter', system-ui, sans-serif;
            pointer-events: none;
            z-index: 1000;
            border: 1px solid rgba(59, 130, 246, 0.3);
            box-shadow: 
                0 8px 24px rgba(0, 0, 0, 0.4),
                0 4px 8px rgba(59, 130, 246, 0.2);
            backdrop-filter: blur(12px);
            transform: translateX(-50%) translateY(-100%);
            min-width: 200px;
        }

        .tooltip-header {
            font-weight: 700;
            color: #60a5fa;
            margin-bottom: 8px;
            font-size: 13px;
            border-bottom: 1px solid rgba(59, 130, 246, 0.2);
            padding-bottom: 4px;
        }

        .tooltip-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            align-items: center;
        }

        .tooltip-row span:first-child {
            color: #94a3b8;
            font-size: 11px;
        }

        .tooltip-row .highlight {
            color: #22c55e;
            font-weight: 700;
            font-family: 'Courier New', monospace;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .laevitas-grid.optimal .grid-header,
            .laevitas-grid.optimal .grid-row {
                grid-template-columns: 100px repeat(8, minmax(50px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .laevitas-grid {
                font-size: 10px;
                overflow-x: auto;
            }

            .laevitas-grid.optimal .grid-header,
            .laevitas-grid.optimal .grid-row {
                grid-template-columns: 80px repeat(8, minmax(45px, 1fr));
            }

            .header-cell,
            .grid-cell {
                padding: 8px 4px;
            }

            .exchange-header,
            .exchange-cell,
            .date-cell {
                padding-left: 8px;
            }

            .data-cell:hover {
                transform: none;
            }

            .cell-value,
            .total-value {
                font-size: 10px;
            }
        }

        @media (max-width: 480px) {
            .laevitas-grid.optimal .grid-header,
            .laevitas-grid.optimal .grid-row {
                grid-template-columns: 70px repeat(8, minmax(40px, 1fr));
            }

            .header-cell,
            .grid-cell {
                padding: 6px 2px;
            }

            .cell-value,
            .total-value {
                font-size: 9px;
            }

            .exchange-name,
            .date-name {
                font-size: 9px;
            }
        }

        /* Light Mode Support */
        @media (prefers-color-scheme: light) {
            .laevitas-grid {
                background: #f8fafc;
                border: 1px solid rgba(59, 130, 246, 0.15);
            }

            .grid-header {
                background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
            }

            .header-cell {
                color: #1e293b;
                background: linear-gradient(135deg, 
                    rgba(59, 130, 246, 0.05) 0%, 
                    rgba(139, 92, 246, 0.03) 100%);
            }

            .total-header {
                background: linear-gradient(135deg, 
                    rgba(34, 197, 94, 0.05) 0%, 
                    rgba(22, 163, 74, 0.03) 100%);
                color: #16a34a;
            }

            .grid-row:hover {
                background: rgba(59, 130, 246, 0.03);
            }

            .exchange-cell,
            .date-cell {
                background: rgba(248, 250, 252, 0.8);
            }

            .exchange-name,
            .date-name {
                color: #1e293b;
            }

            .laevitas-grid.optimal .grid-header,
            .laevitas-grid.optimal .grid-row {
                grid-template-columns: 120px repeat(8, 1fr);
            }

            .total-cell {
                background: linear-gradient(135deg, 
                    rgba(248, 250, 252, 0.9) 0%, 
                    rgba(241, 245, 249, 0.9) 100%);
                border-left: 2px solid rgba(34, 197, 94, 0.2);
            }

            .total-value {
                color: #16a34a;
            }

            .laevitas-tooltip {
                background: linear-gradient(135deg, 
                    rgba(248, 250, 252, 0.98) 0%, 
                    rgba(241, 245, 249, 0.98) 100%);
                color: #1e293b;
                border: 1px solid rgba(59, 130, 246, 0.2);
            }

            .tooltip-header {
                color: #2563eb;
                border-bottom: 1px solid rgba(59, 130, 246, 0.15);
            }

            .tooltip-row span:first-child {
                color: #64748b;
            }

            .tooltip-row .highlight {
                color: #16a34a;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .heatmap-header {
                padding: 16px;
            }

            .heatmap-header .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 16px;
            }

            .heatmap-body {
                padding: 16px;
            }

            .heatmap-canvas-container {
                padding: 12px;
            }

            .legend-gradient {
                align-items: center;
            }

            .legend-color-bar,
            .legend-labels {
                width: 150px;
            }

            .heatmap-rankings {
                padding: 16px;
            }

            .rankings-table .table-responsive {
                font-size: 0.875rem;
            }

            .market-share-bar {
                min-width: 80px;
            }

            .heatmap-time-selector {
                flex-wrap: wrap;
                justify-content: center;
            }

            .heatmap-time-btn {
                min-width: 35px;
                padding: 0.25rem 0.5rem !important;
            }
        }

        /* Light Mode Support */
        @media (prefers-color-scheme: light) {
            .heatmap-container {
                background: linear-gradient(135deg, 
                    rgba(248, 250, 252, 0.98) 0%, 
                    rgba(241, 245, 249, 0.98) 50%,
                    rgba(248, 250, 252, 0.98) 100%);
                border: 1px solid rgba(59, 130, 246, 0.2);
                box-shadow: 
                    0 10px 40px rgba(0, 0, 0, 0.1),
                    0 4px 16px rgba(59, 130, 246, 0.05),
                    inset 0 1px 0 rgba(255, 255, 255, 0.8);
            }

            .heatmap-header {
                background: linear-gradient(135deg, 
                    rgba(59, 130, 246, 0.05) 0%, 
                    rgba(139, 92, 246, 0.03) 100%);
                border-bottom: 1px solid rgba(59, 130, 246, 0.15);
            }

            .heatmap-header h5 {
                color: #1e293b;
                text-shadow: none;
            }

            .heatmap-body {
                background: linear-gradient(135deg, 
                    rgba(248, 250, 252, 0.9) 0%, 
                    rgba(241, 245, 249, 0.85) 50%,
                    rgba(248, 250, 252, 0.9) 100%);
            }

            .heatmap-canvas-container {
                background: rgba(248, 250, 252, 0.5);
                border: 1px solid rgba(59, 130, 246, 0.1);
            }

            .heatmap-legend {
                background: rgba(241, 245, 249, 0.4);
                border: 1px solid rgba(59, 130, 246, 0.1);
            }

            .legend-label {
                color: #1e293b;
            }

            .legend-labels {
                color: #64748b;
            }

            .heatmap-rankings {
                background: linear-gradient(135deg, 
                    rgba(59, 130, 246, 0.03) 0%, 
                    rgba(139, 92, 246, 0.02) 100%);
                border-top: 1px solid rgba(59, 130, 246, 0.15);
            }

            .rankings-table h6,
            .market-insights h6 {
                color: #1e293b;
            }

            .rankings-table .table {
                background: rgba(248, 250, 252, 0.3);
                border: 1px solid rgba(59, 130, 246, 0.1);
            }

            .rankings-table .table th {
                background: rgba(59, 130, 246, 0.05);
                color: #1e293b;
            }

            .rankings-table .table td {
                color: #1e293b;
                border-bottom: 1px solid rgba(59, 130, 246, 0.1);
            }

            .rankings-table .table tbody tr:hover {
                background: rgba(59, 130, 246, 0.03);
            }

            .share-percentage {
                color: #1e293b;
            }

            .share-bar {
                background: rgba(226, 232, 240, 0.6);
                border: 1px solid rgba(59, 130, 246, 0.15);
            }

            .market-insights {
                background: rgba(248, 250, 252, 0.3);
                border: 1px solid rgba(59, 130, 246, 0.1);
            }

            .insight-title {
                color: #1e293b;
            }

            .insight-description {
                color: #64748b;
            }

            .heatmap-time-selector {
                background: linear-gradient(135deg, 
                    rgba(241, 245, 249, 0.8) 0%, 
                    rgba(226, 232, 240, 0.8) 100%);
                border: 1px solid rgba(59, 130, 246, 0.15);
            }

            .heatmap-time-btn {
                color: #64748b !important;
            }

            .heatmap-time-btn:hover {
                color: #1e293b !important;
            }
        }

        /* Interval Dropdown Styling */
        .interval-dropdown-btn {
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            padding: 0.5rem 0.75rem !important;
            min-width: 70px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            border: 1px solid rgba(59, 130, 246, 0.2) !important;
            background: linear-gradient(135deg, 
                rgba(30, 41, 59, 0.6) 0%, 
                rgba(51, 65, 85, 0.6) 100%) !important;
            color: #94a3b8 !important;
        }

        .interval-dropdown-btn:hover {
            color: #e2e8f0 !important;
            border-color: rgba(59, 130, 246, 0.4) !important;
            background: linear-gradient(135deg, 
                rgba(59, 130, 246, 0.1) 0%, 
                rgba(139, 92, 246, 0.1) 100%) !important;
        }

        .interval-dropdown-btn:focus {
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25) !important;
        }

        /* Light mode interval dropdown */
        @media (prefers-color-scheme: light) {
            .interval-dropdown-btn {
                background: linear-gradient(135deg, 
                    rgba(241, 245, 249, 0.8) 0%, 
                    rgba(226, 232, 240, 0.8) 100%) !important;
                border: 1px solid rgba(59, 130, 246, 0.15) !important;
                color: #64748b !important;
            }

            .interval-dropdown-btn:hover {
                color: #1e293b !important;
                border-color: rgba(59, 130, 246, 0.3) !important;
            }
        }
    </style>
@endsection