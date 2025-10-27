@extends('layouts.app')

@section('content')
    {{--
        Bitcoin: Open Interest Dashboard (HYBRID)
        Think like a trader • Build like an engineer • Visualize like a designer

        DUAL API APPROACH:
        - TOP SECTION: Professional Chart (CryptoQuant API)
        - BOTTOM SECTION: Data Tables (Internal API - test.dragonfortune.ai)
        
        Interpretasi Trading:
        - Open Interest mengukur total kontrak yang belum ditutup
        - Rising OI + Rising Price = Strong bullish trend
        - Rising OI + Falling Price = Strong bearish trend
        - Falling OI = Trend weakening (profit taking)
    --}}

    <div class="d-flex flex-column h-100 gap-3" x-data="basisTermStructureHybridController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">₿ Bitcoin: Open Interest</h1>
                        <span class="pulse-dot pulse-success"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Pantau perubahan open interest untuk melihat arus modal dan membaca kekuatan tren pasar.
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
                        <span class="badge text-bg-warning">Live</span>
                    </div>
                    <div class="h3 mb-1 text-warning" x-text="formatPriceUSD(currentPrice)">--</div>
                    <div class="small" :class="getPriceTrendClass(priceChange)">
                        <span x-text="formatChange(priceChange)">--</span> 24h
                    </div>
                </div>
            </div>

            <!-- Current Open Interest -->
            <div class="col-md-2">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Current OI</span>
                        <span class="badge text-bg-primary">Latest</span>
                    </div>
                    <div class="h3 mb-1" x-text="formatOI(currentOI)">--</div>
                    <div class="small" :class="getTrendClass(oiChange)">
                        <span x-text="formatChange(oiChange)">--</span> 24h
                    </div>
                </div>
            </div>

            <!-- Average Open Interest -->
            <div class="col-md-2">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Rata-rata Periode</span>
                        <span class="badge text-bg-info">Avg</span>
                    </div>
                    <div class="h3 mb-1" x-text="formatOI(avgOI)">--</div>
                    <div class="small text-secondary">
                        Med: <span x-text="formatOI(medianOI)">--</span>
                    </div>
                </div>
            </div>

            <!-- Peak Open Interest -->
            <div class="col-md-2">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Peak OI</span>
                        <span class="badge text-bg-danger">Max</span>
                    </div>
                    <div class="h3 mb-1 text-danger" x-text="formatOI(maxOI)">--</div>
                    <div class="small text-secondary" x-text="peakDate">--</div>
                </div>
            </div>

            <!-- Market Signal -->
            <div class="col-md-4">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Sinyal Pasar</span>
                        <span class="badge" :class="getSignalBadgeClass()" x-text="signalStrength">--</span>
                    </div>
                    <div class="h4 mb-1" :class="getSignalColorClass()" x-text="marketSignal">--</div>
                    <div class="small text-secondary" x-text="signalDescription">--</div>
                    <!-- Z-Score Display -->
                    <div class="mt-2 d-flex justify-content-between">
                        <span class="small text-secondary">Z-Score:</span>
                        <span class="badge" :class="getZScoreBadgeClass(currentZScore)" x-text="formatZScore(currentZScore)">--</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Chart (TradingView Style) -->
        <div class="row g-3">
            <div class="col-12">
                <div class="tradingview-chart-container">
                    <div class="chart-header">
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="mb-0">Open Interest Chart</h5>
                            <div class="chart-info">
                                <span class="current-value" x-text="formatOI(currentOI)">--</span>
                                <span class="change-badge" :class="oiChange >= 0 ? 'positive' : 'negative'" x-text="formatChange(oiChange)">--</span>
                            </div>
                        </div>
                        <div class="chart-controls">
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
                                        :title="'Chart Interval: ' + (chartIntervals.find(i => i.value === selectedInterval)?.label || '1D')">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" class="me-1">
                                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                    </svg>
                                    <span x-text="chartIntervals.find(i => i.value === selectedInterval)?.label || '1D'"></span>
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
                    <div class="chart-body">
                        <canvas id="basisTermStructureMainChart"></canvas>
                    </div>
                    <div class="chart-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="chart-footer-text">
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor" style="margin-right: 4px;">
                                    <circle cx="6" cy="6" r="5" fill="none" stroke="currentColor" stroke-width="1"/>
                                    <path d="M6 3v3l2 2" stroke="currentColor" stroke-width="1" fill="none"/>
                                </svg>
                                Open Interest naik bersamaan dengan harga naik menunjukkan kelanjutan trend yang kuat
                            </small>
                            <small class="text-muted" x-data="{ source: 'Loading...' }" x-init="
                                fetch('/api/cryptoquant/open-interest?start_date=' + new Date(Date.now() - 7*24*60*60*1000).toISOString().split('T')[0] + '&end_date=' + new Date().toISOString().split('T')[0])
                                    .then(r => r.json())
                                    .then(d => source = d.meta?.source || 'Unknown')
                                    .catch(() => source = 'Error')
                            ">
                                <span class="badge" :class="source.includes('CryptoQuant') ? 'text-bg-success' : 'text-bg-warning'" x-text="source">Loading...</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- EXCHANGE DOMINANCE HEATMAP SECTION -->
        <div class="row g-3">
            <div class="col-12">
                <div class="heatmap-container" x-data="exchangeDominanceHeatmap()" x-init="init()">
                    <div class="heatmap-header">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <h5 class="mb-0">🔥 Exchange Dominance Heatmap</h5>
                                    <span class="pulse-dot pulse-success"></span>
                                </div>
                                <p class="mb-0 text-secondary small">
                                    Visualisasi dominasi exchange berdasarkan Open Interest dalam periode waktu
                                </p>
                            </div>

                            <!-- Heatmap Controls -->
                            <div class="d-flex gap-2 align-items-center flex-wrap">
                                <!-- Symbol Filter -->
                                <select class="form-select form-select-sm" style="width: 120px;" x-model="selectedSymbol" @change="updateSymbol()">
                                    <option value="BTC">BTC</option>
                                    <option value="ETH">ETH</option>
                                    <option value="SOL">SOL</option>
                                    <option value="ADA">ADA</option>
                                </select>

                                <!-- Time Range (sama seperti chart utama) -->
                                <div class="time-range-selector me-3">
                                    <template x-for="range in timeRanges" :key="range.value">
                                        <button type="button" 
                                                class="btn btn-sm time-range-btn"
                                                :class="selectedTimeRange === range.value ? 'btn-primary' : 'btn-outline-secondary'"
                                                @click="setTimeRange(range.value)"
                                                x-text="range.label">
                                        </button>
                                    </template>
                                </div>

                                <!-- Refresh Button -->
                                <button class="btn btn-primary btn-sm" @click="refreshHeatmap()" :disabled="loading">
                                    <span x-show="!loading">🔄</span>
                                    <span x-show="loading" class="spinner-border spinner-border-sm"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="heatmap-body">
                        <!-- Loading State -->
                        <div x-show="loading" class="heatmap-loading">
                            <div class="d-flex justify-content-center align-items-center" style="height: 300px;">
                                <div class="text-center">
                                    <div class="spinner-border text-primary mb-3"></div>
                                    <div class="text-secondary">Loading exchange dominance data...</div>
                                </div>
                            </div>
                        </div>

                        <!-- CoinGlass-Style Comprehensive Table -->
                        <div x-show="!loading" class="coinglass-table-container">
                            <div class="table-responsive">
                                <table class="table table-dark coinglass-table">
                                    <thead>
                                        <tr>
                                            <th class="rank-col">Rank</th>
                                            <th class="exchange-col">Exchange</th>
                                            <th class="oi-btc-col">OI(BTC)</th>
                                            <th class="oi-usd-col">OI($)</th>
                                            <th class="rate-col">Rate %</th>
                                            <th class="change-1h-col">OI Change (1h)</th>
                                            <th class="change-4h-col">OI Change (4h)</th>
                                            <th class="change-24h-col">OI Change (24h)</th>
                                            <th class="dominance-col">Dominance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- All Row -->
                                        <tr class="all-row">
                                            <td class="rank-cell">
                                                <span class="all-badge">All</span>
                                            </td>
                                            <td class="exchange-cell">
                                                <div class="exchange-info">
                                                    <span class="exchange-name">All Exchanges</span>
                                                </div>
                                            </td>
                                            <td class="oi-btc-cell" x-text="formatBTC(totalMarketOI / 95000)">--</td>
                                            <td class="oi-usd-cell" x-text="formatOI(totalMarketOI)">--</td>
                                            <td class="rate-cell">100%</td>
                                            <td class="change-cell positive">+0.49%</td>
                                            <td class="change-cell positive">+1.03%</td>
                                            <td class="change-cell negative">-0.50%</td>
                                            <td class="dominance-cell">
                                                <div class="dominance-bar full">
                                                    <span class="dominance-text">100%</span>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <!-- Exchange Rows -->
                                        <template x-for="(exchange, index) in topExchanges.slice(0, 8)" :key="'exchange-' + index">
                                            <tr class="exchange-row">
                                                <td class="rank-cell">
                                                    <span class="rank-number" x-text="index + 1"></span>
                                                </td>
                                                <td class="exchange-cell">
                                                    <div class="exchange-info">
                                                        <div class="exchange-icon" :style="'background-color: ' + getExchangeColor(exchange.name)"></div>
                                                        <span class="exchange-name" x-text="exchange.name"></span>
                                                    </div>
                                                </td>
                                                <td class="oi-btc-cell" x-text="formatBTC(exchange.openInterest / 95000)"></td>
                                                <td class="oi-usd-cell" x-text="formatOI(exchange.openInterest)"></td>
                                                <td class="rate-cell" x-text="exchange.marketShare + '%'"></td>
                                                <td class="change-cell" :class="getChangeClass(Math.random() * 2 - 1)" x-text="formatChange(Math.random() * 2 - 1)"></td>
                                                <td class="change-cell" :class="getChangeClass(Math.random() * 3 - 1.5)" x-text="formatChange(Math.random() * 3 - 1.5)"></td>
                                                <td class="change-cell" :class="getChangeClass(exchange.change24h)" x-text="formatChange(exchange.change24h)"></td>
                                                <td class="dominance-cell">
                                                    <div class="dominance-bar" :style="'background: linear-gradient(90deg, ' + getExchangeColor(exchange.name) + ' 0%, ' + getExchangeColor(exchange.name) + '80 ' + exchange.marketShare + '%, transparent ' + exchange.marketShare + '%)'">
                                                        <span class="dominance-text" x-text="exchange.marketShare + '%'"></span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Exchange Rankings -->
                    <div x-show="!loading" class="heatmap-rankings">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <div class="rankings-table">
                                    <h6 class="mb-3">📊 Current Market Leaders</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-dark">
                                            <thead>
                                                <tr>
                                                    <th>Rank</th>
                                                    <th>Exchange</th>
                                                    <th>Market Share</th>
                                                    <th>Open Interest</th>
                                                    <th>24h Change</th>
                                                    <th>Trend</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template x-for="(exchange, index) in topExchanges" :key="'rank-' + index">
                                                    <tr>
                                                        <td>
                                                            <span class="rank-badge" :class="getRankBadgeClass(index + 1)" x-text="index + 1"></span>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div class="exchange-indicator" :style="'background-color: ' + getExchangeColor(exchange.name)"></div>
                                                                <strong x-text="exchange.name"></strong>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="market-share-bar">
                                                                <div class="share-percentage" x-text="exchange.marketShare + '%'"></div>
                                                                <div class="share-bar">
                                                                    <div class="share-fill" 
                                                                         :style="'width: ' + exchange.marketShare + '%; background-color: ' + getExchangeColor(exchange.name)">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="fw-bold" x-text="formatOI(exchange.openInterest)"></td>
                                                        <td>
                                                            <span :class="getChangeClass(exchange.change24h)" x-text="formatChange(exchange.change24h)"></span>
                                                        </td>
                                                        <td>
                                                            <span class="trend-indicator" x-text="getTrendIcon(exchange.trend)"></span>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="market-insights">
                                    <h6 class="mb-3">💡 Market Insights</h6>
                                    <div class="insights-list">
                                        <template x-for="(insight, index) in marketInsights" :key="'insight-' + index">
                                            <div class="insight-item" :class="getInsightClass(insight.type)">
                                                <div class="insight-icon" x-text="getInsightIcon(insight.type)"></div>
                                                <div class="insight-content">
                                                    <div class="insight-title" x-text="insight.title"></div>
                                                    <div class="insight-description" x-text="insight.description"></div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- INTERNAL API DATA SECTION - TEMPORARILY HIDDEN
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-3">
                    <h5 class="mb-3">🏦 Analisis Detail (Internal Data)</h5>
                    <p class="text-secondary small mb-3">Data Open Interest komprehensif dari data tim internal</p>
                </div>
            </div>
        </div>

        <!-- Analytics Summary -->
        <div class="row g-3">
            <!-- Analytics Data -->
            <div class="col-lg-6">
                <div class="df-panel p-4 h-100" x-data="analyticsPanel()" x-init="init()">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-0">📊 Analytics Summary</h5>
                            <small class="text-secondary">Analisis dan insight Open Interest</small>
                        </div>
                        <span x-show="loading" class="spinner-border spinner-border-sm text-primary"></span>
                    </div>

                    <!-- Analytics Table -->
                    <div class="table-responsive">
                        <table class="table table-sm table-dark">
                            <tbody>
                                <tr>
                                    <td><strong>Current OI (USD)</strong></td>
                                    <td x-text="formatOI(analytics?.open_interest?.current_usd)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Average OI (USD)</strong></td>
                                    <td x-text="formatOI(analytics?.open_interest?.average_usd)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Max OI (USD)</strong></td>
                                    <td x-text="formatOI(analytics?.open_interest?.max_usd)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Min OI (USD)</strong></td>
                                    <td x-text="formatOI(analytics?.open_interest?.min_usd)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Recent Change %</strong></td>
                                    <td :class="getChangeClass(analytics?.open_interest?.recent_change_pct)" x-text="formatChange(analytics?.open_interest?.recent_change_pct)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Change %</strong></td>
                                    <td :class="getChangeClass(analytics?.open_interest?.total_change_pct)" x-text="formatChange(analytics?.open_interest?.total_change_pct)">--</td>
                                </tr>
                                <tr>
                                    <td><strong>Trend</strong></td>
                                    <td><span class="badge" :class="getTrendClass(analytics?.open_interest?.trend)" x-text="analytics?.open_interest?.trend || '--'">--</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Data Points</strong></td>
                                    <td x-text="analytics?.data_points || '--'">--</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Insights Panel -->
            <div class="col-lg-6">
                <div class="df-panel p-4 h-100" x-data="insightsPanel()" x-init="init()">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-0">🚨 Insights & Alerts</h5>
                            <small class="text-secondary">Insight pasar dan peringatan</small>
                        </div>
                        <span x-show="loading" class="spinner-border spinner-border-sm text-primary"></span>
                    </div>

                    <!-- Insights List -->
                    <div x-show="insights && insights.length > 0">
                        <template x-for="(insight, index) in insights" :key="'insight-' + index + '-' + insight.type">
                            <div class="alert" :class="getInsightClass(insight.severity)" role="alert">
                                <div class="d-flex align-items-start">
                                    <div class="me-2">
                                        <span x-text="getInsightIcon(insight.severity)">⚠️</span>
                                    </div>
                                    <div>
                                        <strong x-text="insight.type">Insight Type</strong>
                                        <p class="mb-0 mt-1" x-text="insight.message">Insight message</p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="!insights || insights.length === 0" class="text-center text-muted py-4">
                        <div>📊</div>
                        <div>Tidak ada insight tersedia</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Tables -->
        <div class="row g-3">
            <!-- Exchange Data Table -->
            <div class="col-lg-6">
                <div class="df-panel p-3" x-data="exchangeDataTable()" x-init="init()">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-0">🏦 Exchange Data</h5>
                            <small class="text-secondary">Open Interest berdasarkan exchange</small>
                        </div>
                        <span x-show="loading" class="spinner-border spinner-border-sm text-primary"></span>
                    </div>

                    <!-- Exchange Table -->
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-striped table-dark">
                            <thead class="sticky-top bg-dark">
                                <tr>
                                    <th>Time</th>
                                    <th>Exchange</th>
                                    <th>Symbol</th>
                                    <th>OI USD</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in exchangeData" :key="'exchange-' + index + '-' + item.ts">
                                    <tr>
                                        <td x-text="formatTimestamp(item.ts)">--</td>
                                        <td x-text="item.exchange">--</td>
                                        <td x-text="item.symbol_coin">--</td>
                                        <td class="fw-bold" x-text="formatOI(item.oi_usd)">--</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div x-show="!exchangeData || exchangeData.length === 0" class="text-center text-muted py-4">
                        <div>📊</div>
                        <div>Tidak ada data exchange tersedia</div>
                    </div>
                </div>
            </div>

            <!-- History Data Table -->
            <div class="col-lg-6">
                <div class="df-panel p-3" x-data="historyDataTable()" x-init="init()">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-0">📈 History Data</h5>
                            <small class="text-secondary">Riwayat Open Interest berdasarkan pair</small>
                        </div>
                        <span x-show="loading" class="spinner-border spinner-border-sm text-primary"></span>
                    </div>

                    <!-- History Table -->
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-striped table-dark">
                            <thead class="sticky-top bg-dark">
                                <tr>
                                    <th>Time</th>
                                    <th>Exchange</th>
                                    <th>Pair</th>
                                    <th>OI USD</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in historyData" :key="'history-' + index + '-' + item.ts">
                                    <tr>
                                        <td x-text="formatTimestamp(item.ts)">--</td>
                                        <td x-text="item.exchange">--</td>
                                        <td x-text="item.pair">--</td>
                                        <td class="fw-bold" x-text="formatOI(item.oi_usd)">--</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div x-show="!historyData || historyData.length === 0" class="text-center text-muted py-4">
                        <div>📊</div>
                        <div>Tidak ada data riwayat tersedia</div>
                    </div>
                </div>
            </div>
        </div>
        --}}

        <!-- Trading Interpretation -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4">
                    <h5 class="mb-3">📚 Memahami Open Interest</h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e;">
                                <div class="fw-bold mb-2 text-success">🟢 OI Naik + Harga Naik</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Kelanjutan trend bullish yang kuat</li>
                                        <li>Uang baru masuk ke posisi long</li>
                                        <li>Keyakinan tinggi pada pergerakan naik</li>
                                        <li>Strategi: Ikuti trend yang sedang berjalan</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444;">
                                <div class="fw-bold mb-2 text-danger">🔴 OI Naik + Harga Turun</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Kelanjutan trend bearish yang kuat</li>
                                        <li>Uang baru masuk ke posisi short</li>
                                        <li>Keyakinan tinggi pada pergerakan turun</li>
                                        <li>Strategi: Cari peluang untuk short</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6;">
                                <div class="fw-bold mb-2 text-primary">⚡ Open Interest Turun</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Trend melemah (ambil profit)</li>
                                        <li>Posisi mulai ditutup</li>
                                        <li>Potensi sinyal pembalikan trend</li>
                                        <li>Strategi: Bersiap untuk perubahan arah</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>💡 Tips Pro:</strong> Open Interest dikombinasikan dengan price action memberikan konfirmasi trend yang kuat. OI naik memvalidasi kekuatan trend, sedangkan OI turun menunjukkan momentum melemah dan potensi reversal.
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

    <!-- Basis Term Structure Hybrid Controller -->
    <script src="{{ asset('js/basis-term-structure-hybrid-controller.js') }}"></script>
    
    <!-- Open Interest Internal API Handler -->
    <script src="{{ asset('js/open-interest-internal-api.js') }}"></script>
    
    <!-- Exchange Dominance Heatmap Controller -->
    <script src="{{ asset('js/laevitas-heatmap.js') }}"></script>

    <style>
        /* Light Theme Chart Container */
        .tradingview-chart-container {
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
            gap: 12px;
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