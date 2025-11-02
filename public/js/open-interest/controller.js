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
         * Load all data
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

                // Fetch analytics and history in parallel
                const [analyticsResult, historyResult] = await Promise.allSettled([
                    this.apiService.fetchAnalytics({
                        symbol: this.selectedSymbol,
                        exchange: this.selectedExchange,
                        interval: this.selectedInterval,
                        limit: limit
                    }),
                    this.apiService.fetchHistory({
                        symbol: this.selectedSymbol,
                        exchange: this.selectedExchange,
                        interval: this.selectedInterval,
                        limit: limit,
                        with_price: true
                    })
                ]);

                // Process analytics data
                if (analyticsResult.status === 'fulfilled' && analyticsResult.value) {
                    this.analyticsData = analyticsResult.value;
                    this.mapAnalyticsToState();
                    console.log('✅ Analytics data processed');
                }

                // Process history data
                if (historyResult.status === 'fulfilled' && historyResult.value) {
                    let historyData = historyResult.value;

                    // Apply client-side date range filtering
                    if (this.globalPeriod !== 'all') {
                        historyData = this.apiService.filterByDateRange(
                            historyData,
                            dateRange.startDate,
                            dateRange.endDate
                        );
                    }

                    this.historyData = historyData;
                    this.priceData = historyData.map(d => ({ ts: d.ts, price: d.price }));

                    console.log('✅ History data loaded:', this.historyData.length, 'records');

                    // Update current values
                    this.updateCurrentValues();

                    // Render chart
                    if (this.chartManager && this.historyData.length > 0) {
                        this.chartManager.renderChart(this.historyData, this.priceData);
                    }
                }

                // Hide loading skeleton
                this.globalLoading = false;

                console.log('✅ All data loaded successfully');

            } catch (error) {
                console.error('❌ Error loading data:', error);
                this.errorCount++;

                if (this.errorCount >= this.maxErrors) {
                    console.error('❌ Max errors reached, stopping auto-refresh');
                    this.stopAutoRefresh();
                }
            } finally {
                // Ensure skeleton is hidden even if some data fails
                if (this.historyData.length > 0 || this.analyticsData) {
                    this.globalLoading = false;
                }
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
                if (document.hidden) return;
                if (this.globalLoading) return;

                console.log('🔄 Auto-refresh triggered');
                this.loadData();
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
            this.chartType = this.chartType === 'line' ? 'area' : 'line';
            console.log('📊 Chart type toggled:', this.chartType);
            // Render chart with new type
            if (this.chartManager && this.historyData.length > 0) {
                this.chartManager.renderChart(this.historyData, this.priceData);
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

