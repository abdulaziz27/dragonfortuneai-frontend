/**
 * Basis & Term Structure Main Controller
 * Coordinates data fetching, chart rendering, and metrics calculation
 */

import { BasisAPIService } from './api-service.js';
import { ChartManager } from './chart-manager.js';
import { BasisUtils } from './utils.js';

export function createBasisController() {
    return {
        // Initialization flag
        initialized: false,

        // Services
        apiService: null,
        chartManager: null,
        termStructureChartManager: null,

        // Global state
        globalPeriod: '1d', // Default: 1D (1 day) - better for analytics API
        globalLoading: false,
        selectedExchange: 'Binance',
        selectedSpotPair: 'BTC/USDT',
        selectedFuturesSymbol: 'BTCUSDT', // Default futures symbol
        selectedInterval: '1h',
        termStructureSymbol: 'BTC', // Symbol for term structure API (BTC, ETH)

        // Chart intervals (supported by API: 5m, 15m, 1h, 4h)
        chartIntervals: [
            { label: '5M', value: '5m' },
            { label: '15M', value: '15m' },
            { label: '1H', value: '1h' },
            { label: '4H', value: '4h' }
        ],

        // Time ranges (same pattern as funding-rate and perp-quarterly)
        timeRanges: [
            { label: '1D', value: '1d', days: 1 },
            { label: '7D', value: '7d', days: 7 },
            { label: '1M', value: '1m', days: 30 },
            { label: 'ALL', value: 'all', days: null } // null means use 2 years ago
        ],

        // Auto-refresh state
        refreshInterval: null,
        errorCount: 0,
        maxErrors: 3,

        // Data
        rawData: [],
        dataLoaded: false,

        // Summary metrics (from analytics API)
        currentBasis: null, // From history API (latest data point)
        avgBasis: null, // From analytics API
        basisAnnualized: null, // From analytics API
        basisVolatility: null, // From analytics API
        marketStructure: null, // From analytics API
        trend: null, // From analytics API

        // Analytics data
        analyticsData: null,
        analyticsLoading: false,

        // Term structure data
        termStructureData: null,
        termStructureLoading: false,

        // Chart state
        chartType: 'line', // 'line' or 'bar'

        /**
         * Initialize controller
         */
        init() {
            // Prevent double initialization
            if (this.initialized) {
                console.warn('⚠️ Dashboard already initialized, skipping...');
                return;
            }

            this.initialized = true;
            console.log('🚀 Basis & Term Structure Dashboard initialized');

            // Initialize services
            this.apiService = new BasisAPIService();
            this.chartManager = new ChartManager('basisMainChart');
            this.termStructureChartManager = new ChartManager('basisTermStructureChart');

            // Initial data load
            this.loadData();
            this.loadTermStructure(); // Also load term structure on init
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
         * Load all data (analytics and history in parallel)
         */
        async loadData() {
            // Cancel previous requests
            if (this.apiService) {
                this.apiService.cancelAllRequests();
            }

            this.globalLoading = true;
            this.errorCount = 0;

            try {
                const dateRange = this.getDateRange();
                
                // Use fixed limit 5000 for all cases (same as funding-rate and perp-quarterly)
                // Date range filtering is done client-side after API response
                const limit = 5000;

                console.log('📅 Date Range Request:', {
                    period: this.globalPeriod,
                    startDate: dateRange.startDate.toISOString(),
                    endDate: dateRange.endDate.toISOString(),
                    days: Math.ceil((dateRange.endDate - dateRange.startDate) / (1000 * 60 * 60 * 24))
                });

                // Fetch analytics and history in parallel
                const [historyData, analyticsData] = await Promise.all([
                    this.apiService.fetchHistory({
                        exchange: this.selectedExchange,
                        spotPair: this.selectedSpotPair,
                        futuresSymbol: this.selectedFuturesSymbol,
                        interval: this.selectedInterval,
                        limit: limit,
                        dateRange: dateRange
                    }),
                    this.fetchAnalyticsData()
                ]);

                this.rawData = historyData;
                
                // Map analytics state if received (fetchAnalyticsData already calls mapAnalyticsToState internally)
                // But ensure it's called here as well in case fetchAnalyticsData didn't complete properly
                if (analyticsData) {
                    // Always map to ensure state is updated (fetchAnalyticsData may have been aborted)
                    this.mapAnalyticsToState(analyticsData);
                } else {
                    // If analytics is null, check if we have cached data to maintain
                    if (!this.analyticsData) {
                        console.warn('⚠️ No analytics data available - summary cards will show placeholders');
                    }
                }

                // Calculate current basis from latest history data
                if (this.rawData.length > 0) {
                    this.currentBasis = this.rawData[this.rawData.length - 1].basisAbs;
                }

                // Update chart
                this.chartManager.updateChart(this.rawData, this.chartType);

                this.dataLoaded = true;
                console.log('✅ Data loaded:', {
                    history: historyData.length,
                    analytics: analyticsData ? 'available' : 'null'
                });

            } catch (error) {
                console.error('❌ Error loading data:', error);
                this.errorCount++;
                if (this.errorCount >= this.maxErrors) {
                    console.error('❌ Max errors reached, stopping auto-refresh');
                    this.stopAutoRefresh();
                }
            } finally {
                this.globalLoading = false;
            }
        },

        /**
         * Fetch analytics data
         */
        async fetchAnalyticsData() {
            // Cancel any previous request before starting new one
            if (this.apiService && this.apiService.analyticsAbortController) {
                this.apiService.analyticsAbortController.abort();
            }

            this.analyticsLoading = true;

            try {
                const dateRange = this.getDateRange();
                const days = dateRange.startDate && dateRange.endDate
                    ? Math.ceil((dateRange.endDate - dateRange.startDate) / (1000 * 60 * 60 * 24))
                    : 7;
                
                // Use fixed limit 5000 for analytics
                const limit = 5000;

                console.log('📡 Fetching analytics with:', { 
                    days, 
                    interval: this.selectedInterval, 
                    limit,
                    exchange: this.selectedExchange,
                    spotPair: this.selectedSpotPair,
                    futuresSymbol: this.selectedFuturesSymbol
                });

                const data = await this.apiService.fetchAnalytics({
                    exchange: this.selectedExchange,
                    spotPair: this.selectedSpotPair,
                    futuresSymbol: this.selectedFuturesSymbol,
                    interval: this.selectedInterval,
                    limit: limit
                });

                if (data) {
                    this.analyticsData = data;
                    // Immediately update state when data is received
                    this.mapAnalyticsToState(data);
                    console.log('✅ Analytics data stored and state updated');
                } else {
                    console.warn('⚠️ Analytics API returned null');
                    // Don't clear existing state if API returns null (might be temporary error)
                    // Only clear if explicitly needed
                }

                return data;

            } catch (error) {
                if (error.name === 'AbortError') {
                    console.log('🛑 Analytics request aborted - keeping existing state');
                    // Don't update state if request was aborted
                    // Return null to indicate no new data
                    return null;
                }
                console.error('❌ Error fetching analytics:', error);
                // Don't throw - analytics is optional, chart can still work without it
                return null;
            } finally {
                this.analyticsLoading = false;
            }
        },

        /**
         * Map analytics API response to UI state
         */
        mapAnalyticsToState(analyticsData) {
            if (!analyticsData) {
                console.warn('⚠️ Analytics data is null or empty - preserving existing state');
                // Don't clear existing state - preserve what we have
                // Only clear if this is the first load and we explicitly know there's no data
                if (!this.analyticsData && !this.dataLoaded) {
                    // First load, no cached data - set to null to show placeholders
                    this.marketStructure = null;
                    this.trend = null;
                    this.avgBasis = null;
                    this.basisAnnualized = null;
                    this.basisVolatility = null;
                }
                // Otherwise, keep existing state
                return;
            }

            console.log('📊 Mapping analytics data:', {
                market_structure: analyticsData.market_structure,
                trend: analyticsData.trend,
                basis_abs: analyticsData.basis_abs,
                basis_annualized: analyticsData.basis_annualized,
                basis_volatility: analyticsData.basis_volatility
            });

            // Map all analytics fields - always update when valid data is received
            this.avgBasis = analyticsData.basis_abs !== undefined && analyticsData.basis_abs !== null 
                ? parseFloat(analyticsData.basis_abs) 
                : null;
            this.basisAnnualized = analyticsData.basis_annualized !== undefined && analyticsData.basis_annualized !== null
                ? parseFloat(analyticsData.basis_annualized)
                : null;
            this.basisVolatility = analyticsData.basis_volatility !== undefined && analyticsData.basis_volatility !== null
                ? parseFloat(analyticsData.basis_volatility)
                : null;
            this.marketStructure = analyticsData.market_structure || null;
            this.trend = analyticsData.trend || null;

            console.log('✅ State updated:', {
                marketStructure: this.marketStructure,
                trend: this.trend,
                avgBasis: this.avgBasis,
                basisAnnualized: this.basisAnnualized,
                basisVolatility: this.basisVolatility
            });
        },

        /**
         * Load term structure data
         */
        async loadTermStructure() {
            this.termStructureLoading = true;

            try {
                const data = await this.apiService.fetchTermStructure({
                    symbol: this.termStructureSymbol, // Use termStructureSymbol (BTC or ETH)
                    exchange: this.selectedExchange,
                    limit: 1000
                });

                this.termStructureData = data;

                // Render term structure chart
                if (data && data.basis_curve) {
                    this.termStructureChartManager.renderTermStructureChart(data);
                }

                console.log('✅ Term structure loaded');

            } catch (error) {
                console.error('❌ Error loading term structure:', error);
            } finally {
                this.termStructureLoading = false;
            }
        },

        /**
         * Get date range from selected period
         */
        getDateRange() {
            const now = new Date();
            const range = this.timeRanges.find(r => r.value === this.globalPeriod);
            const days = range ? range.days : 7;

            let startDate;
            let endDate = new Date(now);

            if (this.globalPeriod === 'all') {
                startDate = new Date(now.getFullYear() - 2, 0, 1); // 2 years ago
            } else {
                startDate = new Date(now);
                startDate.setDate(startDate.getDate() - days);
            }

            endDate.setHours(23, 59, 59, 999);

            return { startDate, endDate };
        },

        /**
         * Format basis value
         */
        formatBasis(value) {
            return BasisUtils.formatBasis(value);
        },

        /**
         * Format basis annualized
         */
        formatBasisAnnualized(value) {
            return BasisUtils.formatBasisAnnualized(value);
        },

        /**
         * Format market structure
         */
        formatMarketStructure(value) {
            return BasisUtils.formatMarketStructure(value);
        },

        /**
         * Format trend
         */
        formatTrend(value) {
            return BasisUtils.formatTrend(value);
        },

        /**
         * Get market structure badge class
         */
        getMarketStructureBadgeClass() {
            if (!this.marketStructure) return 'text-bg-secondary';
            
            const structure = this.marketStructure.toLowerCase();
            if (structure.includes('contango')) {
                return 'text-bg-success'; // Green for contango
            } else if (structure.includes('backwardation')) {
                return 'text-bg-danger'; // Red for backwardation
            }
            return 'text-bg-info';
        },

        /**
         * Get trend badge class
         */
        getTrendBadgeClass() {
            if (!this.trend) return 'text-bg-secondary';
            
            const trend = this.trend.toLowerCase();
            if (trend === 'increasing') {
                return 'text-bg-success';
            } else if (trend === 'decreasing') {
                return 'text-bg-danger';
            }
            return 'text-bg-secondary';
        },

        /**
         * Get trend color class
         */
        getTrendColorClass() {
            if (!this.trend) return '';
            
            const trend = this.trend.toLowerCase();
            if (trend === 'increasing') {
                return 'text-success';
            } else if (trend === 'decreasing') {
                return 'text-danger';
            }
            return '';
        },

        /**
         * Refresh all data
         */
        refreshAll() {
            this.loadData();
            this.loadTermStructure();
        },

        /**
         * Set time range
         */
        setTimeRange(range) {
            this.globalPeriod = range;
            this.loadData();
        },

        /**
         * Update exchange
         */
        updateExchange() {
            this.loadData();
            this.loadTermStructure(); // Term structure also depends on exchange
        },

        /**
         * Get available futures symbols based on selected spot pair
         */
        getAvailableFuturesSymbols() {
            if (this.selectedSpotPair === 'BTC/USDT') {
                return [
                    'BTCUSDT',
                    'BTC-USDT-SWAP',
                    'BTC_PERP',
                    'BTC-PERP',
                    'BTCUSDT-PERP',
                    'BTC-PERPETUAL',
                    'BTCPERP',
                    'BTCUSDT_UMCBL',
                    'BTC_USDT',
                    'BTC-31OCT25',
                    'BTCUSDT_251226',
                    'XBTUSD',
                    'tBTCF0:USTF0'
                ];
            } else if (this.selectedSpotPair === 'ETH/USDT') {
                return [
                    'ETHUSDT',
                    'ETH-USDT-SWAP',
                    'ETH_PERP',
                    'ETH-PERP',
                    'ETHUSD-PERP',
                    'ETH-PERPETUAL',
                    'ETHPERP',
                    'ETH_USDT',
                    'ETH-31OCT25',
                    'ETHUSDT_251226'
                ];
            }
            return [];
        },

        /**
         * Update spot pair
         */
        updateSpotPair() {
            // Update futures symbol to first available symbol when spot pair changes
            const availableSymbols = this.getAvailableFuturesSymbols();
            if (availableSymbols.length > 0) {
                this.selectedFuturesSymbol = availableSymbols[0];
            }
            this.loadData();
        },

        /**
         * Update futures symbol
         */
        updateFuturesSymbol() {
            this.loadData();
        },

        /**
         * Update interval
         */
        updateInterval() {
            this.loadData();
        },

        /**
         * Set chart interval
         */
        setChartInterval(interval) {
            this.selectedInterval = interval;
            this.loadData();
        },

        /**
         * Toggle chart type
         */
        toggleChartType(type) {
            this.chartType = type;
            if (this.rawData && this.rawData.length > 0) {
                this.chartManager.updateChart(this.rawData, type);
            }
        },

        /**
         * Start auto-refresh
         */
        startAutoRefresh() {
            this.stopAutoRefresh(); // Clear any existing interval
            
            this.refreshInterval = setInterval(() => {
                if (!document.hidden && !this.globalLoading && this.errorCount < this.maxErrors) {
                    console.log('🔄 Auto-refresh triggered');
                    this.loadData();
                    this.loadTermStructure();
                }
            }, 5000); // 5 seconds interval (same as funding-rate)
        },

        /**
         * Stop auto-refresh
         */
        stopAutoRefresh() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
                this.refreshInterval = null;
            }
        },

        /**
         * Cleanup on component destroy
         */
        cleanup() {
            this.stopAutoRefresh();
            if (this.apiService) {
                this.apiService.cancelAllRequests();
            }
            if (this.chartManager) {
                this.chartManager.destroy();
            }
            if (this.termStructureChartManager) {
                this.termStructureChartManager.destroy();
            }
        }
    };
}

