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
        globalLoading: true,
        analyticsLoading: false,
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
        symbols: ['BTCUSDT', 'ETHUSDT', 'SOLUSDT', 'BNBUSDT'],
        exchanges: ['Binance', 'Bybit'],
        intervals: [
            { label: '1 Minute', value: '1m' },
            { label: '5 Minutes', value: '5m' },
            { label: '15 Minutes', value: '15m' },
            { label: '1 Hour', value: '1h' }
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

            // Initialize services
            this.apiService = new OpenInterestAPIService();
            this.chartManager = new ChartManager('openInterestMainChart');

            // Load initial data
            await this.loadData();

            // Start auto-refresh
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
         * Load all data (OPTIMISTIC LOADING: history first, analytics in background)
         */
        async loadData() {
            // Guard: Skip if already loading
            if (this.globalLoading && this.historyData.length > 0) {
                console.log('⏭️ Skip load (already loading)');
                return;
            }

            // Cancel previous requests
            if (this.apiService) {
                this.apiService.cancelAllRequests();
            }

            this.globalLoading = true;
            this.errorCount = 0;

            try {
                const dateRange = this.getDateRange();
                const limit = OpenInterestUtils.calculateLimit(
                    this.timeRanges.find(r => r.value === this.globalPeriod)?.days || 1,
                    this.selectedInterval
                );

                console.log('📡 Loading Open Interest data...', {
                    symbol: this.selectedSymbol,
                    exchange: this.selectedExchange,
                    interval: this.selectedInterval,
                    period: this.globalPeriod,
                    limit: limit
                });

                // OPTIMISTIC LOADING: Fetch history first (main data)
                const historyData = await this.apiService.fetchHistory({
                    symbol: this.selectedSymbol,
                    exchange: this.selectedExchange,
                    interval: this.selectedInterval,
                    limit: limit,
                    with_price: true
                });

                // Handle cancelled requests
                if (historyData === null) {
                    console.log('🚫 Request was cancelled');
                    return;
                }

                // Apply client-side date range filtering
                let filteredData = historyData;
                if (this.globalPeriod !== 'all') {
                    filteredData = this.apiService.filterByDateRange(
                        historyData,
                        dateRange.startDate,
                        dateRange.endDate
                    );
                }

                this.historyData = filteredData;
                this.priceData = filteredData.map(d => ({ ts: d.ts, price: d.price }));
                this.errorCount = 0; // Reset on success

                console.log('✅ History data loaded:', this.historyData.length, 'records');

                // Update current values from history
                this.updateCurrentValues();

                // Fetch analytics in background (non-blocking, fire-and-forget)
                // This will update: trend, volatilityLevel, minOI, maxOI
                this.fetchAnalyticsData().catch(err => {
                    console.warn('⚠️ Analytics fetch failed (will use defaults):', err);
                    // Set defaults if analytics fails
                    this.trend = 'stable';
                    this.volatilityLevel = 'moderate';
                });

                // Render chart with delay for safer cleanup
                setTimeout(() => {
                    try {
                        if (this.chartManager && this.historyData.length > 0) {
                            this.chartManager.renderChart(this.historyData, this.priceData, this.chartType);
                        }
                    } catch (error) {
                        console.error('❌ Error rendering chart:', error);
                    }
                }, 100);

            } catch (error) {
                console.error('❌ Error loading data:', error);
                this.errorCount++;

                if (this.errorCount >= this.maxErrors) {
                    console.error('❌ Max errors reached, stopping auto-refresh');
                    this.stopAutoRefresh();
                }
            } finally {
                // Hide skeleton immediately after history data is loaded
                this.globalLoading = false;
            }
        },

        /**
         * Fetch analytics data in background (independent from main load)
         */
        async fetchAnalyticsData() {
            this.analyticsLoading = true;

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
                if (this.globalLoading) return; // Skip if loading
                if (this.errorCount >= this.maxErrors) {
                    console.error('❌ Too many errors, stopping auto refresh');
                    this.stopAutoRefresh();
                    return;
                }

                console.log('🔄 Auto-refresh triggered');
                this.loadData(); // This will load history and trigger analytics in background

                // Also refresh analytics independently (non-blocking)
                if (!this.analyticsLoading) {
                    this.fetchAnalyticsData().catch(err => {
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
         * Filter handlers
         */
        setTimeRange(range) {
            if (this.globalPeriod === range) return;
            this.globalPeriod = range;
            console.log('📅 Time range changed:', range);
            this.loadData();
        },

        updateSymbol(symbol) {
            if (this.selectedSymbol === symbol) return;
            this.selectedSymbol = symbol;
            console.log('💱 Symbol changed:', symbol);
            this.loadData();
        },

        updateExchange(exchange) {
            if (this.selectedExchange === exchange) return;
            this.selectedExchange = exchange;
            console.log('🏦 Exchange changed:', exchange);
            this.loadData();
        },

        updateInterval(interval) {
            if (this.selectedInterval === interval) return;
            this.selectedInterval = interval;
            console.log('⏱️ Interval changed:', interval);
            this.loadData();
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

