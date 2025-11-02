/**
 * Long Short Ratio Main Controller
 * Coordinates data fetching, chart rendering, and metrics calculation
 */

import { LongShortRatioAPIService } from './api-service.js';
import { ChartManager } from './chart-manager.js';
import { LongShortRatioUtils } from './utils.js';

export function createLongShortRatioController() {
    return {
        // Initialization flag
        initialized: false,

        // Services
        apiService: null,
        mainChartManager: null,
        comparisonChartManager: null,

        // Global state
        globalPeriod: '1d',
        globalLoading: false,
        selectedExchange: 'Binance',
        selectedSymbol: 'BTCUSDT',
        selectedInterval: '1h',
        selectedTakerRange: '1h',
        scaleType: 'linear',
        chartType: 'line',

        // Time ranges
        timeRanges: [],

        // Chart intervals
        chartIntervals: [
            { label: '30M', value: '30m' },
            { label: '1H', value: '1h' },
            { label: '4H', value: '4h' }
        ],

        // Auto-refresh state
        refreshInterval: null,
        errorCount: 0,
        maxErrors: 3,

        // Data containers (from Internal API)
        topAccountData: [],      // Top trader accounts ratio (/api/long-short-ratio/top-accounts)
        topPositionData: [],     // Top trader positions ratio (/api/long-short-ratio/top-positions)

        // Analytics data (from internal API)
        overviewData: null,
        analyticsData: null,
        analyticsLoading: false,

        // Current metrics
        currentGlobalRatio: null,
        currentTopAccountRatio: null,
        currentTopPositionRatio: null,
        globalRatioChange: 0,
        topAccountRatioChange: 0,
        topPositionRatioChange: 0,

        // Market sentiment
        marketSentiment: 'Balanced',
        sentimentStrength: 'Normal',
        sentimentDescription: 'Loading...',
        crowdingLevel: 'Balanced',

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
            console.log('🚀 Long Short Ratio Dashboard initialized');

            // Initialize services
            this.apiService = new LongShortRatioAPIService();
            this.mainChartManager = new ChartManager('longShortRatioMainChart');
            this.comparisonChartManager = new ChartManager('longShortRatioComparisonChart');

            // Initialize time ranges (simplified: 1D, 7D, 1M, ALL)
            this.timeRanges = [
                { label: '1D', value: '1d', days: 1 },
                { label: '7D', value: '7d', days: 7 },
                { label: '1M', value: '1m', days: 30 },
                { label: 'ALL', value: 'all', days: 730 }
                // Note: YTD and 1Y commented out for future use
                // { label: 'YTD', value: 'ytd', days: LongShortRatioUtils.getYTDDays() },
                // { label: '1Y', value: '1y', days: 365 }
            ];

            // Wait for Chart.js to be ready
            if (typeof window.chartJsReady !== 'undefined') {
                window.chartJsReady.then(() => {
                    this.loadAllData();
                });
            } else {
                setTimeout(() => this.loadAllData(), 500);
            }

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
         * Start auto-refresh
         */
        startAutoRefresh() {
            this.stopAutoRefresh();

            const intervalMs = 5000; // 5 seconds

            this.refreshInterval = setInterval(() => {
                if (document.hidden) return;
                if (this.globalLoading) return;
                if (this.errorCount >= this.maxErrors) {
                    console.error('❌ Too many errors, stopping auto refresh');
                    this.stopAutoRefresh();
                    return;
                }

                console.log('🔄 Auto-refresh triggered');
                this.loadAllData();
            }, intervalMs);

            console.log('✅ Auto-refresh started (5 second interval)');
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
         * Load all data (FASE 1: Prioritize Internal API)
         * Optimized: Load critical data first, non-critical in background
         */
        async loadAllData() {
            // ⚡ GUARD: Skip if already loading (prevent overlapping requests)
            if (this.globalLoading) {
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
                const timeRange = LongShortRatioUtils.getTimeRange(this.globalPeriod, this.timeRanges);
                const limit = LongShortRatioUtils.calculateLimit(
                    this.timeRanges.find(r => r.value === this.globalPeriod)?.days || 1,
                    this.selectedInterval
                );

                console.log('📡 Loading Long Short Ratio data (FASE 1: Internal API Priority)...', {
                    period: this.globalPeriod,
                    exchange: this.selectedExchange,
                    symbol: this.selectedSymbol,
                    interval: this.selectedInterval,
                    limit: limit
                });

                // Calculate date range for filtering
                const dateRange = this.getDateRange();
                console.log('📅 Date Range Filter:', {
                    period: this.globalPeriod,
                    from: dateRange.startDate.toLocaleString(),
                    to: dateRange.endDate.toLocaleString(),
                    days: this.getDateRangeDays()
                });

                // Fetch data in parallel - FASE 1: Prioritize Internal API
                const [
                    overviewResult,
                    analyticsResult,
                    topAccountsResult,
                    topPositionsResult
                ] = await Promise.allSettled([
                    // ✅ 100% Internal API calls (FASE 1 Complete)
                    this.apiService.fetchOverview({
                        symbol: this.selectedSymbol,
                        interval: this.selectedInterval,
                        limit: limit
                    }),
                    this.apiService.fetchAnalytics({
                        symbol: this.selectedSymbol,
                        exchange: this.selectedExchange,
                        interval: this.selectedInterval,
                        ratio_type: 'accounts',
                        limit: limit
                    }),
                    this.apiService.fetchTopAccounts({
                        symbol: this.selectedSymbol,
                        exchange: this.selectedExchange,
                        interval: this.selectedInterval,
                        limit: 5000,
                        dateRange: dateRange  // Pass date range for client-side filtering
                    }),
                    this.apiService.fetchTopPositions({
                        symbol: this.selectedSymbol,
                        exchange: this.selectedExchange,
                        interval: this.selectedInterval,
                        limit: 5000,
                        dateRange: dateRange  // Pass date range for client-side filtering
                    })
                ]);

                // Process results - FASE 1: Use Internal API as primary source
                let hasCriticalData = false;

                if (overviewResult.status === 'fulfilled' && overviewResult.value) {
                    this.overviewData = overviewResult.value;
                    console.log('✅ Overview data loaded from Internal API');
                }
                
                if (analyticsResult.status === 'fulfilled' && analyticsResult.value) {
                    this.analyticsData = analyticsResult.value;
                    console.log('✅ Analytics data loaded from Internal API');
                }
                
                // Critical data for main chart and summary cards
                if (topAccountsResult.status === 'fulfilled' && topAccountsResult.value) {
                    this.topAccountData = topAccountsResult.value;
                    console.log('✅ Top Accounts data loaded:', this.topAccountData.length, 'records');
                    hasCriticalData = true;
                }
                
                if (topPositionsResult.status === 'fulfilled' && topPositionsResult.value) {
                    this.topPositionData = topPositionsResult.value;
                    console.log('✅ Top Positions data loaded:', this.topPositionData.length, 'records');
                }

                // FASE 1: Map analytics data to state (for summary cards)
                this.mapAnalyticsToState();

                // Update current values from data
                this.updateCurrentValues();

                // ⚡ OPTIMIZATION: Hide skeleton ASAP if critical data is ready
                if (hasCriticalData) {
                    this.globalLoading = false;
                    console.log('⚡ Critical data ready, hiding skeleton');
                }

                // Taker Buy/Sell data removed (Exchange Rankings section is hidden)

                // ⚡ OPTIMIZATION: Render all charts ONCE with complete data
                setTimeout(() => {
                    // Render main chart (Top Account Ratio)
                    if (this.topAccountData.length > 0) {
                        this.mainChartManager.renderMainChart(
                            this.topAccountData,  // Direct use, no duplicate variable
                            this.chartType, 
                            [] // No price overlay
                        );
                    }

                    // Render comparison chart (only Top Account vs Top Position)
                    if (this.topAccountData.length > 0 || this.topPositionData.length > 0) {
                        this.comparisonChartManager.renderComparisonChart(
                            [], // No global account data (redundant with top accounts)
                            this.topAccountData,
                            this.topPositionData
                        );
                    }
                }, 100); // Single batch render

                console.log('✅ All data loaded successfully (FASE 1: Internal API Priority)');

            } catch (error) {
                console.error('❌ Error loading data:', error);
                this.errorCount++;
            } finally {
                // Ensure skeleton is hidden even if some data fails
                this.globalLoading = false;
            }
        },

        /**
         * Map analytics data to state
         */
        mapAnalyticsToState() {
            // First, try to use overview data if available
            if (this.overviewData) {
                this.mapOverviewToState();
            }

            // Then override with analytics if available (more detailed)
            if (!this.analyticsData) return;

            // Map positioning and trend from analytics
            if (this.analyticsData.positioning) {
                const positioning = this.analyticsData.positioning.toLowerCase();
                if (positioning.includes('extreme_bullish') || positioning.includes('extreme_bull')) {
                    this.marketSentiment = 'Long Crowded';
                    this.sentimentStrength = 'Strong';
                } else if (positioning.includes('bullish') || positioning.includes('bull')) {
                    this.marketSentiment = 'Bullish Bias';
                    this.sentimentStrength = 'Moderate';
                } else if (positioning.includes('extreme_bearish') || positioning.includes('extreme_bear')) {
                    this.marketSentiment = 'Short Crowded';
                    this.sentimentStrength = 'Strong';
                } else if (positioning.includes('bearish') || positioning.includes('bear')) {
                    this.marketSentiment = 'Bearish Bias';
                    this.sentimentStrength = 'Moderate';
                }
            }

            // Update description based on trend
            if (this.analyticsData.trend) {
                const trend = this.analyticsData.trend.toLowerCase();
                if (trend === 'increasing') {
                    this.sentimentDescription = 'Trend meningkat - sentimen bullish menguat';
                } else if (trend === 'decreasing') {
                    this.sentimentDescription = 'Trend menurun - sentimen bearish menguat';
                } else if (trend === 'stable') {
                    this.sentimentDescription = 'Trend stabil - sentimen tidak berubah signifikan';
                }
            }
        },

        /**
         * Map overview data to state (from /overview endpoint)
         */
        mapOverviewToState() {
            if (!this.overviewData) return;

            // Map signals
            if (this.overviewData.signals) {
                const accountsSignal = this.overviewData.signals.accounts_signal?.toLowerCase();
                const positionsSignal = this.overviewData.signals.positions_signal?.toLowerCase();

                // Determine overall sentiment from signals
                if (accountsSignal === 'bullish' || positionsSignal === 'bullish') {
                    this.marketSentiment = 'Bullish Bias';
                    this.sentimentStrength = accountsSignal === 'bullish' && positionsSignal === 'bullish' ? 'Strong' : 'Moderate';
                } else if (accountsSignal === 'bearish' || positionsSignal === 'bearish') {
                    this.marketSentiment = 'Bearish Bias';
                    this.sentimentStrength = accountsSignal === 'bearish' && positionsSignal === 'bearish' ? 'Strong' : 'Moderate';
                }
            }

            // Map summary stats (for reference, actual values come from current data)
            // These can be used for summary cards if needed
            if (this.overviewData.accounts_summary) {
                const avgRatio = parseFloat(this.overviewData.accounts_summary.avg_ratio);
                if (avgRatio > 2.0) {
                    this.marketSentiment = 'Long Crowded';
                    this.sentimentStrength = 'Strong';
                } else if (avgRatio > 1.2) {
                    this.marketSentiment = 'Bullish Bias';
                    this.sentimentStrength = 'Moderate';
                }
            }
        },

        /**
         * Update current values from data arrays (FASE 1: Use Internal API)
         */
        updateCurrentValues() {
            // FASE 1: Update from top accounts data (Internal API)
            if (this.topAccountData.length > 0) {
                const latest = this.topAccountData[this.topAccountData.length - 1];
                
                // Use internal API field names
                this.currentTopAccountRatio = parseFloat(latest.ls_ratio_accounts || 0);
                
                // Calculate 24h change
                if (this.topAccountData.length > 24) {
                    const previous = this.topAccountData[this.topAccountData.length - 25];
                    const prevRatio = parseFloat(previous.ls_ratio_accounts || 0);
                    this.topAccountRatioChange = prevRatio > 0 
                        ? ((this.currentTopAccountRatio - prevRatio) / prevRatio) * 100 
                        : 0;
                }
                
                console.log('✅ Top Account Ratio:', {
                    current: this.currentTopAccountRatio,
                    change24h: this.topAccountRatioChange,
                    long: latest.long_accounts,
                    short: latest.short_accounts
                });
            }

            // FASE 1: Update from top positions data (Internal API)
            if (this.topPositionData.length > 0) {
                const latest = this.topPositionData[this.topPositionData.length - 1];
                
                // Use internal API field names
                this.currentTopPositionRatio = parseFloat(latest.ls_ratio_positions || 0);
                
                // Calculate 24h change
                if (this.topPositionData.length > 24) {
                    const previous = this.topPositionData[this.topPositionData.length - 25];
                    const prevRatio = parseFloat(previous.ls_ratio_positions || 0);
                    this.topPositionRatioChange = prevRatio > 0 
                        ? ((this.currentTopPositionRatio - prevRatio) / prevRatio) * 100 
                        : 0;
                }
                
                console.log('✅ Top Position Ratio:', {
                    current: this.currentTopPositionRatio,
                    change24h: this.topPositionRatioChange,
                    long: latest.long_positions_percent,
                    short: latest.short_positions_percent
                });
            }

            // FASE 1: Use top account ratio as global ratio (since we now use internal API)
            this.currentGlobalRatio = this.currentTopAccountRatio;
            this.globalRatioChange = this.topAccountRatioChange;
            
            console.log('✅ Global Ratio (from Top Accounts):', {
                current: this.currentGlobalRatio,
                change24h: this.globalRatioChange
            });

            // Update market sentiment based on current ratio
            this.updateMarketSentiment();
        },

        /**
         * Update market sentiment based on current ratio
         */
        updateMarketSentiment() {
            const ratio = this.currentGlobalRatio;
            if (!ratio) return;

            // Override with analytics if available
            if (this.analyticsData && this.analyticsData.positioning) {
                // Already mapped in mapAnalyticsToState
                return;
            }

            // Fallback calculation
            if (ratio > 2.0) {
                this.marketSentiment = 'Long Crowded';
                this.sentimentStrength = 'Strong';
                this.sentimentDescription = `Ratio > 2.0: Long posisi sangat ramai - potensi koreksi`;
                this.crowdingLevel = 'Extreme Long';
            } else if (ratio > 1.2) {
                this.marketSentiment = 'Bullish Bias';
                this.sentimentStrength = 'Moderate';
                this.sentimentDescription = `Ratio ${ratio.toFixed(2)}: Bias bullish - lebih banyak long`;
                this.crowdingLevel = 'Long Bias';
            } else if (ratio >= 0.8 && ratio <= 1.2) {
                this.marketSentiment = 'Balanced';
                this.sentimentStrength = 'Normal';
                this.sentimentDescription = `Ratio 0.8-1.2: Pasar seimbang`;
                this.crowdingLevel = 'Balanced';
            } else if (ratio < 0.5) {
                this.marketSentiment = 'Short Crowded';
                this.sentimentStrength = 'Strong';
                this.sentimentDescription = `Ratio < 0.5: Short posisi sangat ramai - potensi rally`;
                this.crowdingLevel = 'Extreme Short';
            } else {
                this.marketSentiment = 'Bearish Bias';
                this.sentimentStrength = 'Moderate';
                this.sentimentDescription = `Ratio ${ratio.toFixed(2)}: Bias bearish - lebih banyak short`;
                this.crowdingLevel = 'Short Bias';
            }
        },


        /**
         * Filter handlers
         */
        setTimeRange(range) {
            if (this.globalPeriod === range) return;
            console.log('🔄 Setting time range to:', range);
            this.globalPeriod = range;
            this.loadAllData();
        },

        setChartInterval(interval) {
            if (this.selectedInterval === interval) return;
            console.log('🔄 Setting chart interval to:', interval);
            this.selectedInterval = interval;
            this.loadAllData();
        },

        /**
         * Get date range days from globalPeriod
         */
        getDateRangeDays() {
            const periodMap = {
                '1d': 1, 
                '7d': 7, 
                '1m': 30,
                'all': 730  // 2 years
            };
            return periodMap[this.globalPeriod] || 1;
        },


        /**
         * Get date range for filtering (similar to Funding Rate)
         * @returns {{startDate: Date, endDate: Date}}
         */
        getDateRange() {
            const now = new Date();
            const days = this.getDateRangeDays();
            
            let startDate;
            let endDate = new Date(now); // End date is always "now"
            
            if (this.globalPeriod === 'all') {
                startDate = new Date(now.getFullYear() - 2, 0, 1); // 2 years ago
            } else {
                startDate = new Date(now);
                startDate.setDate(startDate.getDate() - days);
            }
            
            // Set end of day for endDate
            endDate.setHours(23, 59, 59, 999);
            
            return { startDate, endDate };
        },

        updateExchange() {
            console.log('🔄 Updating exchange to:', this.selectedExchange);
            this.loadAllData();
        },

        updateSymbol() {
            console.log('🔄 Updating symbol to:', this.selectedSymbol);
            this.loadAllData();
        },

        updateTakerRange() {
            console.log('🔄 Updating taker range to:', this.selectedTakerRange);
            this.loadAllData();
        },

        toggleChartType(type) {
            if (this.chartType === type) return;
            console.log('🔄 Toggling chart type to:', type);
            this.chartType = type;
            // Re-render main chart with new type
            if (this.globalAccountData.length > 0) {
                this.mainChartManager.renderMainChart(
                    this.globalAccountData, 
                    this.chartType, 
                    [] // No price overlay
                );
            }
        },

        toggleScale(type) {
            if (this.scaleType === type) return;
            console.log('🔄 Toggling scale to:', type);
            this.scaleType = type;
            // Note: Scale toggle needs chart options update - implement if needed
        },

        refreshAll() {
            this.globalLoading = true;
            this.loadAllData().finally(() => {
                this.globalLoading = false;
            });
        },


        /**
         * Format functions (delegate to utils)
         */
        formatRatio(value) {
            return LongShortRatioUtils.formatRatio(value);
        },

        formatChange(value) {
            return LongShortRatioUtils.formatChange(value);
        },

        formatPriceUSD(value) {
            return LongShortRatioUtils.formatPriceUSD(value);
        },

        formatVolume(value) {
            return LongShortRatioUtils.formatVolume(value);
        },

        formatNetBias(value) {
            return LongShortRatioUtils.formatNetBias(value);
        },

        getRatioTrendClass(value) {
            return LongShortRatioUtils.getRatioTrendClass(value);
        },

        getSentimentBadgeClass() {
            return LongShortRatioUtils.getSentimentBadgeClass(this.sentimentStrength);
        },

        getSentimentColorClass() {
            return LongShortRatioUtils.getSentimentColorClass(this.marketSentiment);
        },

        getPriceTrendClass(value) {
            return LongShortRatioUtils.getRatioTrendClass(value); // Reuse same logic
        },

        getExchangeColor(exchangeName) {
            return LongShortRatioUtils.getExchangeColor(exchangeName);
        },

        getBiasClass(value) {
            return LongShortRatioUtils.getBiasClass(value);
        },

        getBuyRatioClass(value) {
            return LongShortRatioUtils.getBuyRatioClass(value);
        },

        getSellRatioClass(value) {
            return LongShortRatioUtils.getSellRatioClass(value);
        },

        /**
         * Helper functions for taker buy/sell data
         */
        getSortedExchanges() {
            if (!this.takerBuySellData?.exchange_list) return [];
            return [...this.takerBuySellData.exchange_list]
                .sort((a, b) => (b.buy_ratio || 0) - (a.buy_ratio || 0));
        },

        getMostBullishExchanges() {
            return this.getSortedExchanges()
                .filter(ex => ex.buy_ratio > 50)
                .slice(0, 5);
        },

        getMostBearishExchanges() {
            if (!this.takerBuySellData?.exchange_list) return [];
            return [...this.takerBuySellData.exchange_list]
                .filter(ex => ex.sell_ratio > 50)
                .sort((a, b) => (b.sell_ratio || 0) - (a.sell_ratio || 0))
                .slice(0, 5);
        },

        /**
         * Cleanup
         */
        cleanup() {
            this.stopAutoRefresh();
            if (this.mainChartManager) this.mainChartManager.destroy();
            if (this.comparisonChartManager) this.comparisonChartManager.destroy();
        }
    };
}

