/**
 * Open Interest Controller
 * Main Alpine.js controller for Open Interest dashboard
 */

import { OpenInterestUtils } from './utils.js';
import { OpenInterestAPIService } from './api-service.js';
import { ChartManager } from './chart-manager.js';

export function createOpenInterestController() {
    return {
        // Initialization flag
        initialized: false,

        // Loading states
        globalLoading: false, // Start false - will show skeleton only if no cache
        analyticsLoading: false,
        isLoading: false, // Flag to prevent multiple simultaneous loads
        errorCount: 0,
        maxErrors: 3,

        // Auto-refresh
        refreshInterval: null,

        // Data containers
        historyData: [],
        analyticsData: null,
        priceData: [],

        // Current metrics
        currentOI: null,
        oiChange: 0,
        currentPrice: null,
        priceChange: 0,

        // Analytics fields
        trend: 'stable',
        volatilityLevel: 'moderate',
        minOI: null,
        maxOI: null,
        dataPoints: 0,

        // Filters
        selectedSymbol: 'BTCUSDT',
        selectedExchange: 'Binance',
        selectedInterval: '5m',
        globalPeriod: '1d',
        chartType: 'line',

        // Available options
        symbols: ['BTCUSDT'],
        exchanges: ['OKX','Binance','HTX','Bitmex','Bitfinex','Bybit','Deribit','Gate','Kraken','KuCoin','CME','Bitget','dYdX','CoinEx','BingX','Coinbase','Gemini','Crypto.com','Hyperliquid','Bitunix','MEXC','WhiteBIT','Aster','Lighter','EdgeX','Drift','Paradex','Extended','ApeX Omni'],
        intervals: [
            { label: '1 Minute', value: '1m' },
            { label: '5 Minutes', value: '5m' },
            { label: '15 Minutes', value: '15m' },
            { label: '1 Hour', value: '1h' },
            { label: '4 Hours', value: '4h' },
            { label: '8 Hours', value: '8h' },
            { label: '1 Week', value: '1w' }
        ],
        timeRanges: [
            { label: '1D', value: '1d', days: 1 },
            { label: '7D', value: '7d', days: 7 },
            { label: '1M', value: '1m', days: 30 },
            { label: 'ALL', value: 'all', days: 730 }
        ],

        // Services
        apiService: null,
        chartManager: null,

        /**
         * Initialize controller
         */
        async init() {
            // Prevent double initialization
            if (this.initialized) {
                console.log('⏭️ Controller already initialized');
                return;
            }

            this.initialized = true;
            console.log('🚀 Open Interest Dashboard initialized');

            // Initialize services IMMEDIATELY (non-blocking)
            this.apiService = new OpenInterestAPIService();
            this.chartManager = new ChartManager('openInterestMainChart');

            // Set globalLoading = false initially (will show skeleton only if no cache)
            this.globalLoading = false;
            this.analyticsLoading = false;

            // STEP 1: Load cache data INSTANT (no loading skeleton)
            const cacheLoaded = this.loadFromCache();
            if (cacheLoaded) {
                console.log('✅ Cache data loaded instantly - showing cached data');
                // Render chart immediately with cached data (don't wait Chart.js)
                // Chart will render when Chart.js is ready
                if (this.chartManager && this.historyData.length > 0) {
                    // Wait for Chart.js to be ready (but don't block other operations)
                    (window.chartJsReady || Promise.resolve()).then(() => {
                        setTimeout(() => {
                            this.chartManager.renderChart(this.historyData, this.priceData, this.chartType);
                        }, 10);
                    });
                }
                // globalLoading already false from loadFromCache (no skeleton shown)
                
                // STEP 2: Fetch fresh data from endpoints (background, no skeleton)
                // Don't await - let it run in background while showing cache
                this.loadData(true).catch(err => {
                    console.warn('⚠️ Background fetch failed:', err);
                });
            } else {
                // No cache available - optimistic UI (no skeleton, show placeholder values)
                console.log('⚠️ No cache available - loading data with optimistic UI (no skeleton)');
                // Don't set globalLoading = true - show layout immediately with placeholder values
                // Data will appear seamlessly after fetch completes
                
                // IMPORTANT: Start fetch IMMEDIATELY (don't wait for Chart.js)
                // This makes hard refresh faster - API fetch starts ASAP
                const fetchPromise = this.loadData(false).catch(err => {
                    console.warn('⚠️ Initial load failed:', err);
                });
                
                // Wait for fetch to complete before starting auto-refresh
                // But don't block on Chart.js - chart will render when ready
                await fetchPromise;
            }

            // Start auto-refresh ONLY after initial load completes
            this.startAutoRefresh();

            // Setup cleanup listeners
            window.addEventListener('beforeunload', () => this.cleanup());
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.stopAutoRefresh();
                } else {
                    this.startAutoRefresh();
                }
            });
        },

        /**
         * Get cache key based on current filters
         */
        getCacheKey() {
            return `oi_dashboard_v2_${this.selectedSymbol}_${this.selectedExchange}_${this.selectedInterval}_${this.globalPeriod}`;
        },

        /**
         * Load data from cache (INSTANT display)
         */
        loadFromCache() {
            try {
                const cacheKey = this.getCacheKey();
                const cached = localStorage.getItem(cacheKey);
                
                if (cached) {
                    const data = JSON.parse(cached);
                    
                    // Check if cache is still valid (not older than 10 minutes)
                    const now = Date.now();
                    const cacheAge = now - (data.timestamp || 0);
                    const maxAge = 10 * 60 * 1000; // 10 minutes
                    
                    if (cacheAge < maxAge && data.historyData && data.historyData.length > 0) {
                        this.historyData = data.historyData;
                        this.priceData = data.priceData || [];
                        this.analyticsData = data.analyticsData || null;
                        
                        // Update state from cached analytics
                        if (this.analyticsData) {
                            this.mapAnalyticsToState();
                        }
                        
                        // Update current values
                        this.updateCurrentValues();
                        
                        // IMPORTANT: Hide loading skeletons immediately after cache loaded
                        this.globalLoading = false;
                        this.analyticsLoading = false;
                        
                        console.log('✅ Loaded from cache:', {
                            records: this.historyData.length,
                            age: Math.round(cacheAge / 1000) + 's'
                        });
                        
                        return true;
                    } else {
                        console.log('⚠️ Cache expired or invalid');
                        localStorage.removeItem(cacheKey);
                    }
                }
            } catch (error) {
                console.warn('⚠️ Error loading cache:', error);
            }
            
            return false;
        },

        /**
         * Save data to cache
         */
        saveToCache() {
            try {
                const cacheKey = this.getCacheKey();
                const cacheData = {
                    timestamp: Date.now(),
                    historyData: this.historyData,
                    priceData: this.priceData,
                    analyticsData: this.analyticsData,
                    filters: {
                        symbol: this.selectedSymbol,
                        exchange: this.selectedExchange,
                        interval: this.selectedInterval,
                        period: this.globalPeriod
                    }
                };
                
                localStorage.setItem(cacheKey, JSON.stringify(cacheData));
                console.log('💾 Data saved to cache:', cacheKey);
            } catch (error) {
                console.warn('⚠️ Error saving cache:', error);
            }
        },

        /**
         * Load all data (OPTIMISTIC LOADING: history first, analytics in background)
         * @param {boolean} isAutoRefresh - If true, don't show loading skeleton
         */
        async loadData(isAutoRefresh = false) {
            // Guard: Skip if already loading (prevent race condition)
            if (this.isLoading) {
                console.log('⏭️ Skip load (already loading)');
                return;
            }

            // Set loading flag to prevent multiple simultaneous loads
            this.isLoading = true;

            // Only show loading skeleton on initial load (hard refresh)
            // Auto-refresh should be silent (no skeleton) since data already exists
            const isInitialLoad = this.historyData.length === 0;
            const shouldShowLoading = isInitialLoad && !isAutoRefresh;

            // IMPORTANT: Don't cancel previous requests on initial load
            // Initial load needs to complete, and auto-refresh will skip if isLoading = true
            // Only cancel on subsequent loads (auto-refresh) to prevent stale data
            if (this.apiService && !isInitialLoad) {
                this.apiService.cancelAllRequests();
            }
            
            if (shouldShowLoading) {
                this.globalLoading = true; // Show skeleton only on first load
                console.log('🔄 Initial load - showing skeleton');
            } else {
                console.log('🔄 Auto-refresh - silent update (no skeleton)');
            }

            this.errorCount = 0;

            // Performance monitoring
            const loadStartTime = Date.now();
            console.log('⏱️ loadData() started at:', new Date().toISOString());

            try {
                const dateRange = this.getDateRange();
                const calculatedLimit = OpenInterestUtils.calculateLimit(
                    this.timeRanges.find(r => r.value === this.globalPeriod)?.days || 1,
                    this.selectedInterval
                );

                // For initial load, use ULTRA small limit (100) for INSTANT response
                // Then load full data in background after first render
                // This provides instant feedback to user - chart appears in <500ms
                const limit = isInitialLoad ? Math.min(100, calculatedLimit) : calculatedLimit;
                
                // Per new API spec, always include price overlay
                const withPrice = true;

                console.log('📡 Loading Open Interest data...', {
                    symbol: this.selectedSymbol,
                    exchange: this.selectedExchange,
                    interval: this.selectedInterval,
                    period: this.globalPeriod,
                    limit: limit,
                    withPrice: withPrice,
                    isInitialLoad: isInitialLoad,
                    calculatedLimit: calculatedLimit
                });

                // OPTIMISTIC LOADING: Fetch history first (main data)
                // Use ultra small limit + skip price for instant feedback
                const historyData = await this.apiService.fetchHistory({
                    symbol: this.selectedSymbol,
                    exchange: this.selectedExchange,
                    interval: this.selectedInterval,
                    limit: limit,
                    with_price: withPrice
                });

                // Handle cancelled requests
                if (historyData === null) {
                    console.log('🚫 Request was cancelled');
                    return;
                }

                // Apply client-side date range filtering (anchor to latest data if backend window is stale)
                const filterStartTime = Date.now();
                let filteredData = historyData;
                if (Array.isArray(historyData) && historyData.length > 0) {
                    const latestTs = historyData[historyData.length - 1].ts;
                    if (this.globalPeriod !== 'all') {
                        const days = this.timeRanges.find(r => r.value === this.globalPeriod)?.days || 1;
                        const desiredEndTs = Math.max(dateRange.endDate.getTime(), latestTs);
                        const desiredStartTs = desiredEndTs - (days * 24 * 60 * 60 * 1000);
                        filteredData = this.apiService.filterByDateRange(
                            historyData,
                            new Date(desiredStartTs),
                            new Date(desiredEndTs)
                        );
                    }
                }
                const filterTime = Date.now() - filterStartTime;
                console.log('⏱️ Client-side filter time:', filterTime + 'ms');

                // Coverage check: ensure we cover desired days; if not, re-fetch with larger limit (silent)
                if (Array.isArray(filteredData) && filteredData.length > 0 && this.globalPeriod !== 'all') {
                    const haveSpanMs = filteredData[filteredData.length - 1].ts - filteredData[0].ts;
                    const wantDays = this.timeRanges.find(r => r.value === this.globalPeriod)?.days || 1;
                    const wantMs = wantDays * 24 * 60 * 60 * 1000;
                    if (haveSpanMs + 1 < wantMs * 0.95 && limit < 20000) {
                        const upscaleLimit = Math.min(20000, Math.ceil(calculatedLimit * 1.5));
                        console.log('🔁 Coverage insufficient, refetching with larger limit:', upscaleLimit);
                        const retryData = await this.apiService.fetchHistory({
                            symbol: this.selectedSymbol,
                            exchange: this.selectedExchange,
                            interval: this.selectedInterval,
                            limit: upscaleLimit,
                            with_price: true
                        });
                        if (Array.isArray(retryData) && retryData.length > 0) {
                            const latestTs2 = retryData[retryData.length - 1].ts;
                            const desiredEndTs2 = Math.max(dateRange.endDate.getTime(), latestTs2);
                            const desiredStartTs2 = desiredEndTs2 - wantMs;
                            filteredData = this.apiService.filterByDateRange(
                                retryData,
                                new Date(desiredStartTs2),
                                new Date(desiredEndTs2)
                            );
                        }
                    }
                }

                // Transform price data (optimized)
                const transformStartTime = Date.now();
                this.historyData = filteredData;
                this.priceData = filteredData.map(d => ({ ts: d.ts, price: d.price }));
                const transformTime = Date.now() - transformStartTime;
                console.log('⏱️ Data transform time:', transformTime + 'ms');

                this.errorCount = 0; // Reset on success

                console.log('✅ History data loaded:', this.historyData.length, 'records');

                // Update current values from history (immediate)
                this.updateCurrentValues();

                // Render chart IMMEDIATELY (before analytics fetch for faster perceived performance)
                // Chart is the most important visual element - show it ASAP
                // Don't wait for Chart.js - it will render when ready (non-blocking)
                const chartRenderStart = Date.now();
                const renderChart = () => {
                    try {
                        if (this.chartManager && this.historyData.length > 0) {
                            this.chartManager.renderChart(this.historyData, this.priceData, this.chartType);
                            const chartRenderTime = Date.now() - chartRenderStart;
                            console.log('⏱️ Chart render time:', chartRenderTime + 'ms');
                        }
                    } catch (error) {
                        console.error('❌ Error rendering chart:', error);
                        // Fallback: try with small delay if immediate render fails
                        setTimeout(() => {
                            if (this.chartManager && this.historyData.length > 0) {
                                this.chartManager.renderChart(this.historyData, this.priceData, this.chartType);
                            }
                        }, 50);
                    }
                };

                // Try immediate render (Chart.js might already be loaded)
                if (typeof Chart !== 'undefined') {
                    renderChart();
                } else {
                    // Chart.js not ready yet - wait for it (non-blocking)
                    (window.chartJsReady || Promise.resolve()).then(() => {
                        renderChart();
                    }).catch(() => {
                        // Fallback if Chart.js fails to load
                        console.warn('⚠️ Chart.js not available, will retry later');
                        setTimeout(renderChart, 100);
                    });
                }

                // Fetch analytics AFTER chart render (non-blocking, fire-and-forget)
                // This allows chart to appear instantly, analytics updates summary cards later
                // Pass isAutoRefresh to prevent skeleton during auto-refresh
                // Skip analytics on initial load for even faster performance - load it in background
                if (!isInitialLoad || !isAutoRefresh) {
                    this.fetchAnalyticsData(isAutoRefresh).then(() => {
                        // Save to cache after analytics loaded
                        this.saveToCache();
                    }).catch(err => {
                        console.warn('⚠️ Analytics fetch failed (will use defaults):', err);
                        // Set defaults if analytics fails
                        this.trend = 'stable';
                        this.volatilityLevel = 'moderate';
                        // Save cache even if analytics failed
                        this.saveToCache();
                    });
                } else {
                    // Initial load: load analytics in background after chart render
                    // This provides instant chart, analytics updates later
                    setTimeout(() => {
                        this.fetchAnalyticsData(true).then(() => {
                            this.saveToCache();
                        }).catch(err => {
                            console.warn('⚠️ Background analytics fetch failed:', err);
                            this.trend = 'stable';
                            this.volatilityLevel = 'moderate';
                            this.saveToCache();
                        });
                    }, 100); // Small delay to let chart render first
                }

                // Log total load time
                const totalLoadTime = Date.now() - loadStartTime;
                console.log('⏱️ Total loadData() time:', totalLoadTime + 'ms');

                // If this was initial load with reduced limit, load full data in background
                // Start immediately (no delay) for faster full data load
                if (isInitialLoad && limit < calculatedLimit && this.historyData.length > 0) {
                    console.log('🔄 Initial load complete, loading full dataset in background...', {
                        currentLimit: limit,
                        fullLimit: calculatedLimit
                    });
                    
                    // Capture dateRange for use in async function
                    const capturedDateRange = dateRange;
                    
                    // Load full data in background IMMEDIATELY (no delay)
                    // Reset isLoading flag first so auto-refresh can work
                    this.isLoading = false;
                    
                    // Start full data load immediately (non-blocking)
                    // Use requestIdleCallback for better performance (falls back to setTimeout)
                    const scheduleFullDataLoad = (callback) => {
                        if (window.requestIdleCallback) {
                            window.requestIdleCallback(callback, { timeout: 50 });
                        } else {
                            setTimeout(callback, 0); // Start immediately
                        }
                    };

                    scheduleFullDataLoad(async () => {
                        try {
                            const fullHistoryData = await this.apiService.fetchHistory({
                                symbol: this.selectedSymbol,
                                exchange: this.selectedExchange,
                                interval: this.selectedInterval,
                                limit: calculatedLimit,
                                with_price: true
                            });

                            if (fullHistoryData && fullHistoryData.length > 0) {
                                // Merge with current data to preserve newer points if backend returned older window
                                const currentLatest = this.historyData[this.historyData.length - 1]?.ts || 0;
                                const fullLatest = fullHistoryData[fullHistoryData.length - 1]?.ts || 0;
                                let merged = fullHistoryData;
                                if (fullLatest < currentLatest) {
                                    const map = new Map();
                                    for (const d of fullHistoryData) map.set(d.ts, d);
                                    for (const d of this.historyData) map.set(d.ts, d);
                                    merged = Array.from(map.values()).sort((a,b) => a.ts - b.ts);
                                }

                                // Apply client-side date range filtering (anchor to latest available)
                                let filteredData = merged;
                                if (this.globalPeriod !== 'all') {
                                    const days = this.timeRanges.find(r => r.value === this.globalPeriod)?.days || 1;
                                    const latestTs = merged[merged.length - 1].ts;
                                    const desiredEndTs = Math.max(capturedDateRange.endDate.getTime(), latestTs);
                                    const desiredStartTs = desiredEndTs - (days * 24 * 60 * 60 * 1000);
                                    filteredData = this.apiService.filterByDateRange(
                                        merged,
                                        new Date(desiredStartTs),
                                        new Date(desiredEndTs)
                                    );
                                }

                                // Update with full dataset (filtered)
                                this.historyData = filteredData;
                                this.priceData = filteredData.map(d => ({ ts: d.ts, price: d.price }));
                                this.updateCurrentValues();

                                // Update chart with full data (smooth update)
                                if (this.chartManager) {
                                    this.chartManager.renderChart(this.historyData, this.priceData, this.chartType);
                                }

                                // Save updated cache
                                this.saveToCache();

                                console.log('✅ Full dataset loaded and chart updated:', {
                                    records: this.historyData.length,
                                    previousRecords: limit
                                });
                            }
                        } catch (err) {
                            console.warn('⚠️ Background full data load failed (using initial data):', err);
                        }
                    });
                } else {
                    // Normal load complete, reset isLoading flag
                    this.isLoading = false;
                }

            } catch (error) {
                // Handle AbortError gracefully (don't log as error)
                if (error.name === 'AbortError') {
                    console.log('⏭️ Request was cancelled (expected during auto-refresh)');
                    return; // Exit early, don't increment error count
                }

                console.error('❌ Error loading data:', error);
                this.errorCount++;

                if (this.errorCount >= this.maxErrors) {
                    console.error('❌ Max errors reached, stopping auto-refresh');
                    this.stopAutoRefresh();
                }
            } finally {
                // Always reset loading flag
                this.isLoading = false;

                // Hide skeleton only if it was shown (initial load)
                // Auto-refresh doesn't show skeleton, so don't set it here
                if (shouldShowLoading) {
                    this.globalLoading = false;
                    console.log('✅ Initial load complete - skeleton hidden');
                }
            }
        },

        /**
         * Fetch analytics data in background (independent from main load)
         * @param {boolean} isAutoRefresh - If true, don't show loading skeleton
         */
        async fetchAnalyticsData(isAutoRefresh = false) {
            // Never show analytics loading skeleton during auto-refresh
            // Auto-refresh should be silent (data already visible)
            if (isAutoRefresh) {
                // Auto-refresh: Keep analyticsLoading = false (silent update)
                this.analyticsLoading = false;
            } else if (this.historyData.length === 0) {
                // Initial load without data: Show skeleton
                this.analyticsLoading = true;
            } else {
                // Background refresh when data exists: Keep false (silent)
                this.analyticsLoading = false;
            }

            try {
                const limit = OpenInterestUtils.calculateLimit(
                    this.timeRanges.find(r => r.value === this.globalPeriod)?.days || 1,
                    this.selectedInterval
                );

                const analyticsData = await this.apiService.fetchAnalytics({
                    symbol: this.selectedSymbol,
                    exchange: this.selectedExchange,
                    interval: this.selectedInterval,
                    limit: limit
                });

                if (analyticsData) {
                    this.analyticsData = analyticsData;
                    this.mapAnalyticsToState();
                    console.log('✅ Analytics data loaded in background');
                }

            } catch (error) {
                console.warn('⚠️ Analytics fetch error:', error);
                // Don't throw - let main flow continue
            } finally {
                this.analyticsLoading = false;
            }
        },

        /**
         * Map analytics data to state
         */
        mapAnalyticsToState() {
            if (!this.analyticsData) {
                console.warn('⚠️ Analytics data is null or empty');
                return;
            }

            // Extract analytics fields
            this.trend = this.analyticsData.trend || 'stable';
            
            // Handle insights object or direct properties
            const insights = this.analyticsData.insights || {};
            this.volatilityLevel = insights.volatility_level || this.analyticsData.volatility_level || 'moderate';
            this.minOI = insights.min_oi || this.analyticsData.min_oi || null;
            this.maxOI = insights.max_oi || this.analyticsData.max_oi || null;
            this.dataPoints = insights.data_points || this.analyticsData.data_points || 0;

            console.log('✅ Analytics mapped to state:', {
                trend: this.trend,
                volatilityLevel: this.volatilityLevel,
                minOI: this.minOI,
                maxOI: this.maxOI
            });
        },

        /**
         * Update current values from history data
         */
        updateCurrentValues() {
            if (this.historyData.length === 0) return;

            // Sort by timestamp to get truly latest value
            const sorted = [...this.historyData].sort((a, b) => a.ts - b.ts);
            const latest = sorted[sorted.length - 1];

            // Update current OI
            this.currentOI = latest.oi_usd ? parseFloat(latest.oi_usd) : null;

            // Update current price
            this.currentPrice = latest.price ? parseFloat(latest.price) : null;

            // Calculate 24h change (compare with data from 24h ago)
            const oneDayAgo = latest.ts - (24 * 60 * 60 * 1000);
            const previous = sorted.find(d => d.ts <= oneDayAgo) || sorted[0];

            if (previous && previous.oi_usd) {
                const prevOI = parseFloat(previous.oi_usd);
                this.oiChange = prevOI > 0 
                    ? ((this.currentOI - prevOI) / prevOI) * 100 
                    : 0;
            }

            if (previous && previous.price) {
                const prevPrice = parseFloat(previous.price);
                this.priceChange = prevPrice > 0 
                    ? ((this.currentPrice - prevPrice) / prevPrice) * 100 
                    : 0;
            }

            console.log('✅ Current values updated:', {
                currentOI: this.currentOI,
                oiChange: this.oiChange,
                currentPrice: this.currentPrice,
                priceChange: this.priceChange
            });
        },

        /**
         * Start auto-refresh (5 seconds)
         */
        startAutoRefresh() {
            this.stopAutoRefresh();

            const intervalMs = 5000; // 5 seconds

            this.refreshInterval = setInterval(() => {
                // Safety checks
                if (document.hidden) return; // Don't refresh hidden tabs
                if (this.globalLoading) return; // Skip if showing skeleton
                if (this.isLoading) return; // Skip if already loading (prevent race condition)
                if (this.errorCount >= this.maxErrors) {
                    console.error('❌ Too many errors, stopping auto refresh');
                    this.stopAutoRefresh();
                    return;
                }

                console.log('🔄 Auto-refresh triggered');
                // Pass isAutoRefresh=true to prevent loading skeleton during auto-refresh
                this.loadData(true).catch(err => {
                    // Handle errors gracefully (AbortError expected during rapid refreshes)
                    if (err.name !== 'AbortError') {
                        console.warn('⚠️ Auto-refresh error:', err);
                    }
                }); // Silent update - no skeleton shown

                // Also refresh analytics independently (non-blocking)
                // Pass isAutoRefresh=true to prevent analytics skeleton during auto-refresh
                if (!this.analyticsLoading) {
                    this.fetchAnalyticsData(true).catch(err => {
                        console.warn('⚠️ Analytics refresh failed:', err);
                    });
                }
            }, intervalMs);

            console.log('✅ Auto-refresh started (5s interval)');
        },

        /**
         * Stop auto-refresh
         */
        stopAutoRefresh() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
                this.refreshInterval = null;
                console.log('⏸️ Auto-refresh stopped');
            }
        },

        /**
         * Cleanup
         */
        cleanup() {
            this.stopAutoRefresh();
            if (this.chartManager) this.chartManager.destroy();
            if (this.apiService) this.apiService.cancelAllRequests();
        },

        /**
         * Handle filter change with cache support
         */
        async handleFilterChange() {
            // Set loading states to false initially (will show skeleton only if no cache)
            this.globalLoading = false;
            this.analyticsLoading = false;
            
            // Try to load cache for new filter combination
            const cacheLoaded = this.loadFromCache();
            
            if (cacheLoaded) {
                console.log('✅ Cache loaded for new filter - showing cached data');
                // Re-filter cached data to the new date range before render (instant visual)
                const { startDate, endDate } = this.getDateRange();
                let filtered = this.historyData;
                if (this.globalPeriod !== 'all' && Array.isArray(filtered) && filtered.length > 0) {
                    filtered = this.apiService.filterByDateRange(filtered, startDate, endDate);
                }
                if (this.chartManager && filtered.length > 0) {
                    setTimeout(() => {
                        const filteredPrice = filtered.map(d => ({ ts: d.ts, price: d.price }));
                        this.chartManager.renderChart(filtered, filteredPrice, this.chartType);
                    }, 10);
                }
                // Fetch fresh data in background (no skeleton)
                this.loadData(true).catch(err => {
                    console.warn('⚠️ Background fetch failed:', err);
                });
            } else {
                // No cache - load data normally (will show skeleton if needed)
                await this.loadData();
            }
        },

        /**
         * Filter handlers
         */
        setTimeRange(range) {
            if (this.globalPeriod === range) return;
            this.globalPeriod = range;
            console.log('📅 Time range changed:', range);
            this.handleFilterChange();
        },

        updateSymbol(symbol) {
            const allowed = new Set(['BTCUSDT']);
            const finalSymbol = allowed.has(symbol) ? symbol : 'BTCUSDT';
            if (this.selectedSymbol === finalSymbol) return;
            this.selectedSymbol = finalSymbol;
            console.log('💱 Symbol changed:', symbol);
            this.handleFilterChange();
        },

        updateExchange(exchange) {
            const allowed = new Set(['OKX','Binance','HTX','Bitmex','Bitfinex','Bybit','Deribit','Gate','Kraken','KuCoin','CME','Bitget','dYdX','CoinEx','BingX','Coinbase','Gemini','Crypto.com','Hyperliquid','Bitunix','MEXC','WhiteBIT','Aster','Lighter','EdgeX','Drift','Paradex','Extended','ApeX Omni']);
            const finalExchange = allowed.has(exchange) ? exchange : 'Binance';
            if (this.selectedExchange === finalExchange) return;
            this.selectedExchange = finalExchange;
            console.log('🏦 Exchange changed:', exchange);
            this.handleFilterChange();
        },

        updateInterval(interval) {
            const allowed = new Set(['1m','5m','15m','1h','4h','8h','1w']);
            const finalInterval = allowed.has(interval) ? interval : '5m';
            if (this.selectedInterval === finalInterval) return;
            this.selectedInterval = finalInterval;
            console.log('⏱️ Interval changed:', interval);
            this.handleFilterChange();
        },

        toggleChartType() {
            this.chartType = this.chartType === 'line' ? 'bar' : 'line';
            console.log('📊 Chart type toggled:', this.chartType);
            // Render chart with new type
            if (this.chartManager && this.historyData.length > 0) {
                this.chartManager.renderChart(this.historyData, this.priceData, this.chartType);
            }
        },

        /**
         * Helper methods
         */
        getDateRange() {
            return OpenInterestUtils.getDateRange(this.globalPeriod, this.timeRanges);
        },

        formatOI(value) {
            return OpenInterestUtils.formatOI(value);
        },

        formatPrice(value) {
            return OpenInterestUtils.formatPrice(value);
        },

        formatChange(value) {
            return OpenInterestUtils.formatChange(value);
        },

        getTrendBadgeClass(trend) {
            return OpenInterestUtils.getTrendBadgeClass(trend);
        },

        getTrendColorClass(trend) {
            return OpenInterestUtils.getTrendColorClass(trend);
        },

        getVolatilityBadgeClass(level) {
            return OpenInterestUtils.getVolatilityBadgeClass(level);
        }
    };
}

