@extends('layouts.app')

@section('title', 'Perp-Quarterly Spread | DragonFortune')

@push('head')
    <!-- Resource Hints for Faster API Loading (Critical for Hard Refresh) -->
    <link rel="dns-prefetch" href="{{ config('app.api_urls.internal') }}">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="{{ config('app.api_urls.internal') }}" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    
    <!-- Preload critical resources for faster initial load -->
    <link rel="preload" href="{{ asset('js/perp-quarterly-spread-controller.js') }}" as="script" type="module">
    
    <!-- Prefetch API endpoints (will fetch in background during hard refresh) -->
    <link rel="prefetch" href="{{ config('app.api_urls.internal') }}/api/perp-quarterly/history?symbol=BTC&exchange=Bybit&interval=1h&limit=100" as="fetch" crossorigin="anonymous">
    <link rel="prefetch" href="{{ config('app.api_urls.internal') }}/api/perp-quarterly/analytics?symbol=BTC&exchange=Bybit&interval=1h&limit=100" as="fetch" crossorigin="anonymous">
@endpush

@section('content')
    {{--
        Perpetual-Quarterly Spread Dashboard
        Think like a trader • Build like an engineer • Visualize like a designer
        
        Interpretasi Trading:
        - Spread mengukur perbedaan harga antara perpetual dan quarterly futures
        - Spread widening = increased divergence, potential arbitrage opportunity
        - Spread narrowing = convergence, reduced arbitrage opportunity
        - Extreme spreads may indicate market stress or liquidation events
    --}}

    <div class="d-flex flex-column h-100 gap-3" x-data="perpQuarterlySpreadController()" x-init="init()" x-cloak>
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">Perp-Quarterly Spread</h1>
                        <span class="pulse-dot pulse-success" x-show="rawData.length > 0"></span>
                        <span class="spinner-border spinner-border-sm text-primary" style="width: 16px; height: 16px;" x-show="rawData.length === 0" x-cloak></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Pantau spread antara kontrak perpetual dan quarterly futures untuk mengidentifikasi peluang arbitrase dan memahami dinamika pasar futures.
                    </p>
                </div>

                <!-- Global Controls -->
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <!-- Symbol Selector -->
                    <select class="form-select" style="width: 140px;" x-model="selectedSymbol" @change="updateSymbol()">
                        <option value="BTC">BTC</option>
                        <option value="ETH">ETH</option>
                    </select>

                    <!-- Exchange Selector -->
                    <select class="form-select" style="width: 160px;" x-model="selectedExchange" @change="updateExchange()">
                        <option value="Bybit">Bybit</option>
                        <option value="Deribit">Deribit</option>
                    </select>

                    <!-- Interval Selector -->
                    <select class="form-select" style="width: 120px;" x-model="selectedInterval" @change="updateInterval()">
                        <option value="5m">5 Minute</option>
                        <option value="15m">15 Minutes</option>
                        <option value="1h">1 Hour</option>
                        <option value="4h">4 Hours</option>
                    </select>

                    <button class="btn btn-primary" @click="refreshAll()" :disabled="globalLoading" x-show="false">
                        <span x-show="!globalLoading">🔄 Refresh</span>
                        <span x-show="globalLoading" class="spinner-border spinner-border-sm"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Cards Row - Data from Analytics API -->
        <div class="row g-3">
            <!-- Current Spread (from History API - latest data point) -->
            <div class="col-md-2">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Current Spread</span>
                        <span class="badge text-bg-primary" x-show="currentSpread !== null && currentSpread !== undefined">Latest</span>
                        <span class="badge text-bg-secondary" x-show="currentSpread === null || currentSpread === undefined">Loading...</span>
                    </div>
                    <div class="h3 mb-0" 
                         :class="currentSpread !== null && currentSpread !== undefined && currentSpread >= 0 ? 'text-success' : (currentSpread !== null && currentSpread !== undefined ? 'text-danger' : '')"
                         x-text="currentSpread !== null && currentSpread !== undefined ? formatSpread(currentSpread) : '--'"></div>
                </div>
            </div>

            <!-- Average Spread (from Analytics API) -->
            <div class="col-md-2">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Avg Spread</span>
                        <span class="badge text-bg-info" x-show="avgSpread !== null && avgSpread !== undefined">Avg</span>
                        <span class="badge text-bg-secondary" x-show="avgSpread === null || avgSpread === undefined">Loading...</span>
                    </div>
                    <div>
                        <div class="h3 mb-1" x-text="avgSpread !== null && avgSpread !== undefined ? formatSpread(avgSpread) : '--'"></div>
                        <div class="small text-secondary" x-show="avgSpreadBps !== null && avgSpreadBps !== undefined" 
                             x-text="'(' + formatSpreadBPS(avgSpreadBps) + ')'"></div>
                    </div>
                </div>
            </div>

            <!-- Max Spread (from Analytics API) -->
            <div class="col-md-2">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Max Spread</span>
                        <span class="badge text-bg-success" x-show="maxSpread !== null && maxSpread !== undefined">Max</span>
                        <span class="badge text-bg-secondary" x-show="maxSpread === null || maxSpread === undefined">Loading...</span>
                    </div>
                    <div class="h3 mb-0 text-success" x-text="maxSpread !== null && maxSpread !== undefined ? formatSpread(maxSpread) : '--'"></div>
                    </div>
            </div>

            <!-- Min Spread (from Analytics API) -->
            <div class="col-md-2">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Min Spread</span>
                        <span class="badge text-bg-danger" x-show="minSpread !== null && minSpread !== undefined">Min</span>
                        <span class="badge text-bg-secondary" x-show="minSpread === null || minSpread === undefined">Loading...</span>
                    </div>
                    <div class="h3 mb-0 text-danger" x-text="minSpread !== null && minSpread !== undefined ? formatSpread(minSpread) : '--'"></div>
                </div>
            </div>

            <!-- Volatility (from Analytics API) -->
            <div class="col-md-2">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Volatility</span>
                        <span class="badge text-bg-warning" x-show="spreadVolatility !== null && spreadVolatility !== undefined">Vol</span>
                        <span class="badge text-bg-secondary" x-show="spreadVolatility === null || spreadVolatility === undefined">Loading...</span>
                    </div>
                    <div class="h3 mb-0" x-text="spreadVolatility !== null && spreadVolatility !== undefined ? formatSpread(spreadVolatility) : '--'"></div>
                </div>
            </div>

            <!-- Trend/Market Signal (from Analytics API) -->
            <div class="col-md-2">
                <div class="df-panel p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary">Trend</span>
                        <span class="badge" :class="getSignalBadgeClass()" x-show="signalStrength !== null && signalStrength !== undefined" x-text="signalStrength"></span>
                        <span class="badge text-bg-secondary" x-show="signalStrength === null || signalStrength === undefined">Loading...</span>
                    </div>
                    <div>
                        <div class="h4 mb-1" :class="getSignalColorClass()" x-text="marketSignal !== null && marketSignal !== undefined ? marketSignal : '--'"></div>
                        <div class="small text-secondary" x-text="signalDescription || 'Loading market signal...'"></div>
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
                            <h5 class="mb-0">Spread & Price</h5>
                            <div class="chart-info">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="current-value" x-text="currentSpread !== null && currentSpread !== undefined ? formatSpread(currentSpread) : '--'"></span>
                                </div>
                            </div>
                        </div>
                        <div class="chart-controls">
                            <div class="d-flex flex-wrap align-items-center gap-3">
                            <!-- Time Range Buttons -->
                                <div class="time-range-selector">
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
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" 
                                            class="btn" 
                                            :class="chartType === 'line' ? 'btn-primary' : 'btn-outline-secondary'" 
                                            @click="toggleChartType('line')"
                                            title="Line Chart - Mudah Dibaca">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="margin-right: 4px;">
                                        <path d="M2 12l3-3 3 3 6-6"/>
                                    </svg>
                                        Line
                                </button>
                                    <button type="button" 
                                            class="btn" 
                                            :class="chartType === 'bar' ? 'btn-primary' : 'btn-outline-secondary'" 
                                            @click="toggleChartType('bar')"
                                            title="Bar Chart - Clear Positif/Negatif">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="margin-right: 4px;">
                                            <path d="M2 2h2v12H2V2zm4 0h2v12H6V2zm4 0h2v12h-2V2zm4 0h2v12h-2V2z"/>
                                    </svg>
                                        Bar
                                </button>
                            </div>

                            <!-- Interval Dropdown -->
                                <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle interval-dropdown-btn" 
                                        type="button" 
                                        data-bs-toggle="dropdown" 
                                            :title="'Chart Interval: ' + (chartIntervals.find(i => i.value === selectedInterval)?.label || '1H')">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" class="me-1">
                                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                    </svg>
                                        <span x-text="chartIntervals.find(i => i.value === selectedInterval)?.label || '1H'"></span>
                                </button>
                                    <ul class="dropdown-menu">
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
                            </div>
            </div>
        </div>

                                
                    <!-- Chart Canvas -->
                    <div class="chart-body">
                        <canvas id="perpQuarterlyMainChart"></canvas>
                                </div>

                    <!-- Chart Footer Legend -->
                    <div class="chart-footer">
                        <div class="d-flex flex-wrap gap-3 justify-content-center small text-secondary">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width: 16px; height: 3px; background: linear-gradient(to right, rgba(239, 68, 68, 1), rgba(34, 197, 94, 1)); border-radius: 2px;"></div>
                                <span>Spread (USD) - Green (positive), Red (negative)</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width: 16px; height: 2px; background: #f59e0b;"></div>
                                <span>Perp Price</span>
                        </div>
                                                            <div class="d-flex align-items-center gap-2">
                                <div style="width: 16px; height: 2px; background: #3b82f6;"></div>
                                <span>Quarterly Price</span>
                                                            </div>
                                                                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Chart.js with Date Adapter and Plugins - Load async for faster initial render -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js" defer></script>

    <!-- Initialize Chart.js ready promise immediately (non-blocking) -->
    <script>
        // Create promise immediately (non-blocking)
        window.chartJsReady = new Promise((resolve) => {
            // Check if Chart.js already loaded (from cache or previous load)
            if (typeof Chart !== 'undefined') {
                console.log('✅ Chart.js already loaded');
                resolve();
                return;
            }
            
            // Wait for Chart.js to load (with fallback timeout)
            let checkCount = 0;
            const checkInterval = setInterval(() => {
                checkCount++;
                if (typeof Chart !== 'undefined') {
                    console.log('✅ Chart.js loaded (after', checkCount * 50, 'ms)');
                    clearInterval(checkInterval);
                    resolve();
                } else if (checkCount > 40) {
                    // Timeout after 2 seconds - resolve anyway
                    console.warn('⚠️ Chart.js load timeout, resolving anyway');
                    clearInterval(checkInterval);
                    resolve();
                }
            }, 50);
        });
    </script>

    <!-- Perp-Quarterly Spread Controller - Load with defer for non-blocking -->
    <script type="module" src="{{ asset('js/perp-quarterly-spread-controller.js') }}" defer></script>

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
        
        /* Use default .derivatives-header styles from app.css (matching funding-rate, open-interest, liquidations) */
        /* No override needed - using global styles for consistency */
        
        /* Light Theme Chart Container */
        .tradingview-chart-container {
            background: #ffffff; /* Pure white background - brighter */
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(226, 232, 240, 0.8); /* Light gray border */
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

        /* Chart Controls - Responsive Layout */
        .chart-controls {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1rem;
            padding: 12px 20px;
        }

        .chart-controls > div {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chart-controls .btn-group {
            background: rgba(241, 245, 249, 0.8);
            border-radius: 6px;
            padding: 2px;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .chart-controls .btn {
            border: none;
            padding: 6px 12px;
            color: #64748b;
            background: transparent;
            transition: all 0.2s;
        }

        .chart-controls .btn:hover {
            color: #1e293b;
            background: rgba(241, 245, 249, 1);
        }

        .chart-controls .btn-primary,
        .chart-controls .btn.btn-primary {
            background: #3b82f6;
            color: #fff;
        }

        .chart-controls .btn-outline-secondary {
            color: #64748b;
            border-color: rgba(226, 232, 240, 0.8);
        }

        .chart-controls .btn-outline-secondary:hover {
            background: rgba(241, 245, 249, 1);
            color: #1e293b;
        }

        .chart-body {
            padding: 20px;
            height: 500px;
            position: relative;
            background: #ffffff; /* Pure white background */
        }

        .chart-footer {
            padding: 12px 20px;
            border-top: 1px solid rgba(0, 0, 0, 0.08);
            background: rgba(59, 130, 246, 0.02);
        }

        .chart-footer small {
            color: #64748b !important; /* Light theme text color */
            display: flex;
            align-items: center;
        }

        .chart-footer-text {
            color: #64748b !important; /* Light theme text color */
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

        /* Time Range Selector */
        .time-range-selector {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .time-range-btn {
            padding: 6px 14px;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 6px;
            transition: all 0.2s;
            white-space: nowrap;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .chart-controls {
                padding: 10px 16px;
                gap: 0.75rem;
            }

            .chart-controls > div {
                width: 100%;
                justify-content: flex-start;
            }

            .time-range-selector {
                width: 100%;
                justify-content: flex-start;
            }

            .form-select.form-select-sm {
                width: 100% !important;
                min-width: unset !important;
            }
        }

        @media (max-width: 576px) {
            .chart-controls {
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
            }

            .chart-controls > div {
                width: 100%;
            }

            .time-range-selector {
                width: 100%;
                justify-content: space-between;
            }

            .time-range-btn {
                flex: 1;
                min-width: 0;
            }
        }

        /* Dropdown Menu Styling */
        .dropdown-menu {
            background: #ffffff !important;
            border: 1px solid rgba(226, 232, 240, 0.8) !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1) !important;
        }

        .dropdown-menu .dropdown-item {
            color: #1e293b !important;
            transition: all 0.2s ease !important;
            border-radius: 4px !important;
            margin: 0.125rem !important;
        }

        .dropdown-menu .dropdown-item:hover {
            background: rgba(59, 130, 246, 0.1) !important;
            color: #3b82f6 !important;
        }

        .dropdown-menu .dropdown-item.active {
            background: #3b82f6 !important;
            color: #fff !important;
        }

        /* Interval Dropdown Styling */
        .interval-dropdown-btn {
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            padding: 0.5rem 0.75rem !important;
            min-width: 70px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            border: 1px solid rgba(59, 130, 246, 0.15) !important;
            background: rgba(241, 245, 249, 0.8) !important;
            color: #64748b !important;
        }

        .interval-dropdown-btn:hover {
            color: #1e293b !important;
            border-color: rgba(59, 130, 246, 0.3) !important;
            background: rgba(241, 245, 249, 1) !important;
        }

        .interval-dropdown-btn:focus {
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25) !important;
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

        /* Light theme enforced - media queries removed to prevent dark mode override */
    </style>
@endsection
