@extends('layouts.app')

@section('content')
    {{--
        Bitcoin: Funding Rate Dashboard
        Think like a trader • Build like an engineer • Visualize like a designer

        Interpretasi Trading:
        - Funding Rate mengukur premium/discount perpetual futures
        - Positive funding = longs pay shorts (bullish sentiment)
        - Negative funding = shorts pay longs (bearish sentiment)
        - Extreme funding rates often signal market tops/bottoms
    --}}

    <div class="d-flex flex-column h-100 gap-3" x-data="fundingRateController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">₿ Bitcoin: Funding Rate</h1>
                        <span class="pulse-dot pulse-success"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Pantau funding rate dari kontrak perpetual (perpetual futures) untuk melihat arah sentimen pasar dan mengidentifikasi potensi pembalikan tren.
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
                        <option value="deribit">Deribit</option>
                        <option value="all_exchange">All Exchanges</option>
                    </select>

                    <!-- Interval Selector -->
                    <select class="form-select" style="width: 120px;" x-model="selectedInterval" @change="updateInterval()">
                        <option value="1h">1 Hour</option>
                        <option value="4h">4 Hours</option>
                        <option value="1d">1 Day</option>
                        <option value="1w">1 Week</option>
                    </select>

                    <button class="btn btn-primary" @click="refreshAll()" :disabled="globalLoading" x-show="false">
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
                    <template x-if="globalLoading">
                        <div>
                            <div class="h3 mb-2 skeleton skeleton-text" style="width: 80%; height: 28px;"></div>
                            <div class="small">
                                <span class="skeleton skeleton-text" style="width: 60px; height: 16px;"></span>
                                <span class="text-secondary ms-1">24h</span>
                            </div>
                        </div>
                    </template>
                    <template x-if="!globalLoading">
                        <div>
                            <div class="h3 mb-1 text-warning" x-text="formatPriceUSD(currentPrice)"></div>
                            <div class="small" :class="getPriceTrendClass(priceChange)">
                                <span x-text="formatChange(priceChange)"></span> 24h
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Current Funding Rate -->
            <div class="col-md-2">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Current Rate</span>
                        <span class="badge text-bg-primary">Latest</span>
                    </div>
                    <template x-if="globalLoading">
                        <div>
                            <div class="h3 mb-2 skeleton skeleton-text" style="width: 70%; height: 28px;"></div>
                            <div class="small">
                                <span class="skeleton skeleton-text" style="width: 60px; height: 16px;"></span>
                                <span class="text-secondary ms-1">24h</span>
                            </div>
                        </div>
                    </template>
                    <template x-if="!globalLoading">
                        <div>
                            <div class="h3 mb-1" x-text="formatFundingRate(currentFundingRate)"></div>
                            <div class="small" :class="getTrendClass(fundingChange)">
                                <span x-text="formatChange(fundingChange)"></span> 24h
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Average Funding Rate -->
            <div class="col-md-2">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Period Avg</span>
                        <span class="badge text-bg-info">Avg</span>
                    </div>
                    <template x-if="globalLoading">
                        <div>
                            <div class="h3 mb-2 skeleton skeleton-text" style="width: 65%; height: 28px;"></div>
                            <div class="small text-secondary d-flex align-items-center gap-1">
                                <span>Med:</span>
                                <span class="skeleton skeleton-text" style="width: 60px; height: 16px;"></span>
                            </div>
                        </div>
                    </template>
                    <template x-if="!globalLoading">
                        <div>
                            <div class="h3 mb-1" x-text="formatFundingRate(avgFundingRate)"></div>
                            <div class="small text-secondary">
                                Med: <span x-text="formatFundingRate(medianFundingRate)"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Peak Funding Rate -->
            <div class="col-md-2">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Peak Rate</span>
                        <span class="badge text-bg-danger">Max</span>
                    </div>
                    <template x-if="globalLoading">
                        <div>
                            <div class="h3 mb-2 skeleton skeleton-text" style="width: 65%; height: 28px;"></div>
                            <div class="small text-secondary skeleton skeleton-text" style="width: 80px; height: 16px;"></div>
                        </div>
                    </template>
                    <template x-if="!globalLoading">
                        <div>
                            <div class="h3 mb-1 text-danger" x-text="formatFundingRate(maxFundingRate)"></div>
                            <div class="small text-secondary" x-text="peakDate"></div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Market Signal -->
            <div class="col-md-4">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Market Signal</span>
                        <template x-if="globalLoading">
                            <span class="badge skeleton skeleton-badge" style="width: 80px; height: 22px;"></span>
                        </template>
                        <template x-if="!globalLoading">
                            <span class="badge" :class="getSignalBadgeClass()" x-text="signalStrength"></span>
                        </template>
                    </div>
                    <template x-if="globalLoading">
                        <div>
                            <div class="h4 mb-2 skeleton skeleton-text" style="width: 60%; height: 22px;"></div>
                            <div class="small text-secondary skeleton skeleton-text" style="width: 90%; height: 16px;"></div>
                        </div>
                    </template>
                    <template x-if="!globalLoading">
                        <div>
                            <div class="h4 mb-1" :class="getSignalColorClass()" x-text="marketSignal"></div>
                            <div class="small text-secondary" x-text="signalDescription"></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Main Chart (TradingView Style) -->
        <div class="row g-3">
            <div class="col-12">
                <div class="tradingview-chart-container">
                    <div class="chart-header">
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="mb-0">Funding Rate</h5>
                            <div class="chart-info">
                                <template x-if="globalLoading">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="current-value skeleton skeleton-text" style="width: 120px; height: 22px;"></span>
                                        <span class="change-badge skeleton skeleton-pill" style="width: 80px; height: 24px;"></span>
                                    </div>
                                </template>
                                <template x-if="!globalLoading">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="current-value" x-text="formatFundingRate(currentFundingRate)"></span>
                                        <span class="change-badge" :class="fundingChange >= 0 ? 'positive' : 'negative'" x-text="formatChange(fundingChange)"></span>
                                    </div>
                                </template>
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

                            <!-- Chart Type Toggle (hidden) -->
                            <div class="btn-group btn-group-sm me-3" role="group" style="display: none;">
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

                            <!-- Scale Toggle (hidden) -->
                            <div class="btn-group btn-group-sm me-3" role="group" style="display: none;">
                                <button type="button" 
                                        class="btn scale-toggle-btn"
                                        :class="scaleType === 'linear' ? 'btn-primary' : 'btn-outline-secondary'"
                                        @click="toggleScale('linear')"
                                        title="Linear Scale - Equal intervals, good for absolute changes">
                                    Linear
                                </button>
                                <button type="button" 
                                        class="btn scale-toggle-btn"
                                        :class="scaleType === 'logarithmic' ? 'btn-primary' : 'btn-outline-secondary'"
                                        @click="toggleScale('logarithmic')"
                                        title="Logarithmic Scale - Exponential intervals, good for percentage changes">
                                    Log
                                </button>
                            </div>

                            <!-- Chart Tools (hidden) -->
                            <div class="btn-group btn-group-sm chart-tools" role="group" style="display: none;">
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
                                            Export as PNG
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" @click.prevent="exportChart('svg')">
                                            <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" class="me-2">
                                                <path d="M8.5 2a.5.5 0 0 0-1 0v5.793L5.354 5.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 7.793V2z"/>
                                                <path d="M3 9.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5z"/>
                                            </svg>
                                            Export as SVG
                                        </a></li>
                                    </ul>
                                </div>
                                
                                <button type="button" class="btn btn-outline-secondary chart-tool-btn" @click="shareChart()" title="Share Chart">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                        <path d="M11 2.5a2.5 2.5 0 1 1 .603 1.628l-6.718 3.12a2.499 2.499 0 0 1 0 1.504l6.718 3.12a2.5 2.5 0 1 1-.488.876l-6.718-3.12a2.5 2.5 0 1 1 0-3.256l6.718-3.12A2.5 2.5 0 0 1 11 2.5z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="chart-body">
                        <canvas id="fundingRateMainChart"></canvas>
                    </div>
                    <div class="chart-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="chart-footer-text">
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor" style="margin-right: 4px;">
                                    <circle cx="6" cy="6" r="5" fill="none" stroke="currentColor" stroke-width="1"/>
                                    <path d="M6 3v3l2 2" stroke="currentColor" stroke-width="1" fill="none"/>
                                </svg>
                                Positive funding rates menandakan sentimen bullish (longs pay shorts)
                            </small>
                            <small class="text-muted" x-data="{ source: 'Loading...' }" x-init="
                                fetch('/api/cryptoquant/funding-rate?start_date=' + new Date(Date.now() - 7*24*60*60*1000).toISOString().split('T')[0] + '&end_date=' + new Date().toISOString().split('T')[0])
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

        <!-- Analysis Row -->
        <div class="row g-3">
            <!-- Distribution Analysis -->
            <div class="col-lg-6">
                <div class="df-panel p-3 h-100">
                    <h5 class="mb-3">📈 Analisis Distribusi</h5>
                    <div style="height: 300px; position: relative;">
                        <canvas id="fundingRateDistributionChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <!-- Z-Score Display -->
                        <template x-if="globalLoading">
                            <div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small text-secondary">Z-Score Saat Ini</span>
                                    <span class="badge skeleton skeleton-badge" style="width: 70px; height: 22px;"></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small text-secondary">Event Funding Tinggi (>2σ)</span>
                                    <span class="badge skeleton skeleton-badge" style="width: 36px; height: 22px;"></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="small text-secondary">Event Ekstrem (>3σ)</span>
                                    <span class="badge skeleton skeleton-badge" style="width: 36px; height: 22px;"></span>
                                </div>
                            </div>
                        </template>
                        <template x-if="!globalLoading">
                            <div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small text-secondary">Z-Score Saat Ini</span>
                                    <span class="badge" :class="getZScoreBadgeClass(currentZScore)" x-text="formatZScore(currentZScore)"></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small text-secondary">Event Funding Tinggi (>2σ)</span>
                                    <span class="badge text-bg-warning" x-text="highFundingEvents"></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="small text-secondary">Event Ekstrem (>3σ)</span>
                                    <span class="badge text-bg-danger" x-text="extremeFundingEvents"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Moving Averages -->
            <div class="col-lg-6">
                <div class="df-panel p-3 h-100">
                    <h5 class="mb-3">📉 Rata-rata Bergerak (Moving Averages)</h5>
                    <div style="height: 300px; position: relative;">
                        <canvas id="fundingRateMAChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <template x-if="globalLoading">
                            <div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small">Rata-rata 7 Hari:</span>
                                    <span class="fw-bold skeleton skeleton-text" style="width: 80px; height: 18px;"></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="small">Rata-rata 30 Hari:</span>
                                    <span class="fw-bold skeleton skeleton-text" style="width: 80px; height: 18px;"></span>
                                </div>
                            </div>
                        </template>
                        <template x-if="!globalLoading">
                            <div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small">Rata-rata 7 Hari:</span>
                                    <span class="fw-bold" x-text="formatFundingRate(ma7)"></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="small">Rata-rata 30 Hari:</span>
                                    <span class="fw-bold" x-text="formatFundingRate(ma30)"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trading Interpretation -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4">
                    <h5 class="mb-3">📚 Memahami Funding Rate</h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e;">
                                <div class="fw-bold mb-2 text-success">🟢 Positive Funding (Bullish)</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Longs pay shorts (bullish sentiment)</li>
                                        <li>Perpetual futures diperdagangkan di harga premium</li>
                                        <li>Demand tinggi untuk posisi long</li>
                                        <li>Strategi: Waspadai kondisi pasar yang overheated</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444;">
                                <div class="fw-bold mb-2 text-danger">🔴 Negative Funding (Bearish)</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Shorts pay longs (bearish sentiment)</li>
                                        <li>Perpetual futures diperdagangkan di bawah harga spot</li>
                                        <li>Demand tinggi untuk posisi short</li>
                                        <li>Strategi: Cari peluang pantulan saat kondisi oversold</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6;">
                                <div class="fw-bold mb-2 text-primary">⚡ Extreme Funding</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Funding rate sangat tinggi atau sangat rendah</li>
                                        <li>Sering kali menandakan tops/bottoms pasar</li>
                                        <li>Indikator kontrarian untuk potensi pembalikan arah</li>
                                        <li>Strategi: Bersiap untuk perubahan tren</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>💡 Pro Tip:</strong> Funding rate ekstrem (>0.1% atau <-0.1%) sering bertepatan dengan puncak atau dasar pasar. Gunakan sebagai indikator kontrarian bersama analisis pergerakan harga.
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

    <!-- Funding Rate Controller -->
    <script src="{{ asset('js/funding-rate-exact-controller.js') }}"></script>

    <style>
        /* Skeleton placeholders */
        [x-cloak] { display: none !important; }
        .skeleton {
            position: relative;
            overflow: hidden;
            background: rgba(148, 163, 184, 0.15);
            border-radius: 6px;
        }
        .skeleton::after {
            content: '';
            position: absolute;
            inset: 0;
            transform: translateX(-100%);
            background: linear-gradient(90deg,
                rgba(255,255,255,0) 0%,
                rgba(255,255,255,0.4) 50%,
                rgba(255,255,255,0) 100%);
            animation: skeleton-shimmer 1.2s infinite;
        }
        .skeleton-text { display: inline-block; }
        .skeleton-badge { display: inline-block; border-radius: 999px; }
        .skeleton-pill { display: inline-block; border-radius: 999px; }
        @keyframes skeleton-shimmer {
            100% { transform: translateX(100%); }
        }
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