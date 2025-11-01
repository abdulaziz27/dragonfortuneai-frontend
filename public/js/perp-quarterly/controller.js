/**
 * Perp-Quarterly Spread Main Controller
 * Coordinates data fetching, chart rendering, and metrics calculation
 */

import { PerpQuarterlyAPIService } from './api-service.js';
import { ChartManager } from './chart-manager.js';
import { PerpQuarterlyUtils } from './utils.js';

export function createPerpQuarterlyController() {
    return {
        // Services
        apiService: null,
        chartManager: null,
        
        // Global state
        globalPeriod: 'all', // Start with 'all' to show all available data
        globalLoading: false,
        selectedSymbol: 'BTC',
        selectedExchange: 'Bybit',
        scaleType: 'linear',
        
        // Chart intervals
        chartIntervals: [
            { label: '5M', value: '5m' },
            { label: '15M', value: '15m' },
            { label: '1H', value: '1h' },
            { label: '4H', value: '4h' }
        ],
        selectedInterval: '1h',
        
        // Time ranges (same pattern as funding-rate)
        timeRanges: [
            { label: '1D', value: '1d', days: 1 },
            { label: '7D', value: '7d', days: 7 },
            { label: '1M', value: '1m', days: 30 },
            { label: 'ALL', value: 'all', days: null } // null means use 2 years ago in getDateRange()
        ],
        
        // Auto-refresh state
        refreshInterval: null,
        errorCount: 0,
        maxErrors: 3,
        
        // Data
        rawData: [],
        dataLoaded: false,
        
        // Summary metrics
        currentSpread: null, // From history API (latest data point)
        avgSpread: null, // From analytics API
        maxSpread: null, // From analytics API
        minSpread: null, // From analytics API
        spreadVolatility: null, // From analytics API
        avgSpreadBps: null, // From analytics API (avg_spread_bps)
        spreadTrend: 'neutral', // From analytics API (widening, narrowing, neutral)
        peakDate: '--',
        
        // Market signal
        marketSignal: 'Neutral',
        signalStrength: 'Normal',
        signalDescription: 'Loading...',
        analyticsData: null,
        analyticsLoading: false,
        
        // Chart state
        chartType: 'bar', // 'line' or 'bar' (default to bar for better visibility of positive/negative spreads)
        
        /**
         * Initialize controller
         */
        init() {
            console.log('🚀 Perp-Quarterly Spread Dashboard initialized');
            
            // Initialize services
            this.apiService = new PerpQuarterlyAPIService();
            this.chartManager = new ChartManager('perpQuarterlyMainChart');
            
            // Initial data load
            this.loadData();
            
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
            
            this.refreshInterval = setInterval(() => {
                if (document.hidden || this.globalLoading || this.errorCount >= this.maxErrors) {
                    return;
                }
                
                console.log('🔄 Auto-refresh triggered');
                this.loadData();
            }, 5000); // 5 seconds
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
         * Load all data
         */
        async loadData() {
            if (this.globalLoading) return;
            
            this.globalLoading = true;
            
            try {
                // Cancel previous requests
                this.apiService.cancelRequest();
                
                // Get date range
                const dateRange = this.getDateRange();
                
                // Use fixed limit 5000 for all cases (same as funding-rate)
                // Date range filtering is done client-side after API response
                // This ensures we get enough data for any date range
                const limit = 5000;
                
                console.log('📅 Date Range Request:', {
                    period: this.globalPeriod,
                    startDate: dateRange.startDate.toISOString(),
                    endDate: dateRange.endDate.toISOString(),
                    days: Math.ceil((dateRange.endDate - dateRange.startDate) / (1000 * 60 * 60 * 24))
                });
                
                // Fetch history and analytics in parallel
                const [historyData, analyticsData] = await Promise.all([
                    this.apiService.fetchHistory({
                        symbol: this.selectedSymbol,
                        exchange: this.selectedExchange,
                        interval: this.selectedInterval,
                        limit: limit,
                        dateRange: dateRange
                    }),
                    this.fetchAnalyticsData()
                ]);
                
                if (!historyData) {
                    console.warn('⚠️ No history data received');
                    return;
                }
                
                this.rawData = historyData;
                this.dataLoaded = true;
                
                console.log('📊 Data loaded:', historyData.length, 'records');
                if (historyData.length > 0) {
                    console.log('📊 First data point:', {
                        date: historyData[0].date,
                        spread: historyData[0].spread,
                        perpPrice: historyData[0].perpPrice,
                        quarterlyPrice: historyData[0].quarterlyPrice
                    });
                }
                
                // Calculate metrics
                this.calculateMetrics();
                
                // Update chart (same pattern as funding-rate)
                if (historyData.length > 0) {
                    setTimeout(() => {
                        try {
                            console.log('📊 Updating chart with', historyData.length, 'data points');
                            this.chartManager.updateChart(historyData, this.chartType);
                        } catch (error) {
                            console.error('❌ Error updating chart:', error);
                        }
                    }, 150);
                } else {
                    console.warn('⚠️ No data to render chart');
                }
                
            } catch (error) {
                console.error('❌ Error loading data:', error);
                this.errorCount++;
                
                if (this.errorCount >= this.maxErrors) {
                    this.stopAutoRefresh();
                    console.error('❌ Too many errors, auto-refresh stopped');
                }
            } finally {
                this.globalLoading = false;
            }
        },
        
        /**
         * Fetch analytics data
         */
        async fetchAnalyticsData() {
            if (this.analyticsLoading) return;
            
            this.analyticsLoading = true;
            
            try {
                const dateRange = this.getDateRange();
                
                // Calculate days from date range (same pattern as funding-rate)
                const days = dateRange.startDate && dateRange.endDate
                    ? Math.ceil((dateRange.endDate - dateRange.startDate) / (1000 * 60 * 60 * 24))
                    : 7; // Default to 7 days if date range not available
                
                // Use fixed limit 5000 for analytics (same as history)
                // Analytics API can handle this internally
                const limit = 5000;
                
                console.log('📡 Fetching analytics with:', { days, interval: this.selectedInterval, limit });
                
                const data = await this.apiService.fetchAnalytics(
                    this.selectedSymbol,
                    this.selectedExchange,
                    this.selectedInterval,
                    limit
                );
                
                this.analyticsData = data;
                this.mapAnalyticsToState(data);
                
            } catch (error) {
                console.error('❌ Error fetching analytics:', error);
                // Don't throw - analytics is optional, chart can still work without it
            } finally {
                this.analyticsLoading = false;
            }
        },
        
        /**
         * Map analytics API response to UI state
         */
        mapAnalyticsToState(analyticsData) {
            if (!analyticsData) {
                this.marketSignal = 'Neutral';
                this.signalStrength = 'Normal';
                this.signalDescription = 'No analytics data available';
                return;
            }
            
            // Map spread_analysis (all data from analytics API)
            if (analyticsData.spread_analysis) {
                const analysis = analyticsData.spread_analysis;
                this.avgSpread = analysis.avg_spread || null;
                this.maxSpread = analysis.max_spread || null;
                this.minSpread = analysis.min_spread || null;
                this.spreadVolatility = analysis.spread_volatility || null;
                this.avgSpreadBps = analysis.avg_spread_bps || null; // Added: Avg spread in basis points
            }
            
            // Map trend (use API value directly, format for display)
            if (analyticsData.trend) {
                this.spreadTrend = analyticsData.trend; // widening, narrowing, stable
                
                // Map trend to market signal (use actual API values)
                const trend = analyticsData.trend.toLowerCase();
                if (trend === 'widening') {
                    this.marketSignal = 'Widening';
                    this.signalDescription = 'Spread widening - increased divergence';
                } else if (trend === 'narrowing') {
                    this.marketSignal = 'Narrowing';
                    this.signalDescription = 'Spread narrowing - convergence';
                } else if (trend === 'stable') {
                    this.marketSignal = 'Stable';
                    this.signalDescription = 'Spread stable';
                } else {
                    // Use API value as-is, capitalize first letter
                    this.marketSignal = trend.charAt(0).toUpperCase() + trend.slice(1);
                    this.signalDescription = `Spread ${trend}`;
                }
            }
            
            // Signal strength from spread_level (use API value directly, format for display)
            // API returns: "tight_spread", "moderate", "wide_spread", etc.
            if (analyticsData.spread_analysis?.spread_level) {
                const level = analyticsData.spread_analysis.spread_level;
                // Format: "tight_spread" → "Tight Spread", "wide_spread" → "Wide Spread"
                this.signalStrength = level
                    .split('_')
                    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                    .join(' ');
            }
        },
        
        /**
         * Calculate metrics from raw data
         */
        calculateMetrics() {
            if (!this.rawData || this.rawData.length === 0) return;
            
            const spreads = this.rawData.map(d => parseFloat(d.spread || 0));
            
            // Current spread from latest data point (always update from history)
            if (spreads.length > 0) {
                this.currentSpread = spreads[spreads.length - 1];
            }
            
            // Fallback calculations if analytics not available
            if (this.avgSpread === null || this.avgSpread === undefined) {
                this.avgSpread = spreads.reduce((a, b) => a + b, 0) / spreads.length;
            }
            
            if (this.maxSpread === null || this.maxSpread === undefined) {
                this.maxSpread = Math.max(...spreads);
            }
            
            if (this.minSpread === null || this.minSpread === undefined) {
                this.minSpread = Math.min(...spreads);
            }
            
            if (this.spreadVolatility === null || this.spreadVolatility === undefined) {
                this.spreadVolatility = PerpQuarterlyUtils.calculateStdDev(spreads);
            }
            
            // Find peak date
            const maxIndex = spreads.indexOf(this.maxSpread);
            if (maxIndex >= 0 && this.rawData[maxIndex]) {
                const peakTs = this.rawData[maxIndex].ts || this.rawData[maxIndex].date;
                this.peakDate = new Date(peakTs).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric'
                });
            }
        },
        
        /**
         * Get date range from globalPeriod
         * Same pattern as funding-rate controller.js
         */
        getDateRange() {
            const now = new Date();
            const range = this.timeRanges.find(r => r.value === this.globalPeriod);
            const days = range ? range.days : 7;
            
            let startDate;
            let endDate = new Date(now); // End date is always "now" (latest available)
            
            if (this.globalPeriod === 'all') {
                // All data: from a very old date (e.g., 2 years ago) to now
                startDate = new Date(now.getFullYear() - 2, 0, 1);
            } else if (days === null) {
                // Fallback: if days is null, use 2 years ago
                startDate = new Date(now.getFullYear() - 2, 0, 1);
            } else {
                // Standard periods: days ago from now
                startDate = new Date(now);
                startDate.setDate(startDate.getDate() - days);
            }
            
            // Set end date to end of today (23:59:59) for inclusive range
            endDate.setHours(23, 59, 59, 999);
            
            return { startDate, endDate };
        },
        
        /**
         * Format spread for display
         */
        formatSpread(value) {
            return PerpQuarterlyUtils.formatSpread(value);
        },
        
        /**
         * Format spread BPS
         */
        formatSpreadBPS(value) {
            return PerpQuarterlyUtils.formatSpreadBPS(value);
        },
        
        /**
         * Refresh all data
         */
        refreshAll() {
            this.errorCount = 0;
            this.loadData();
        },
        
        /**
         * Set time range
         */
        setTimeRange(range) {
            if (this.globalPeriod === range) return;
            this.globalPeriod = range;
            this.loadData();
        },
        
        /**
         * Update exchange
         */
        updateExchange() {
            this.loadData();
        },
        
        /**
         * Update interval (from header selector)
         */
        updateInterval() {
            this.loadData();
        },
        
        /**
         * Set chart interval (from chart header dropdown)
         */
        setChartInterval(interval) {
            if (this.selectedInterval === interval) return;
            this.selectedInterval = interval;
            this.loadData();
        },
        
        /**
         * Update symbol
         */
        updateSymbol() {
            this.loadData();
        },
        
        /**
         * Toggle chart type
         */
        toggleChartType(type) {
            if (this.chartType === type) return;
            this.chartType = type;
            
            if (this.rawData && this.rawData.length > 0) {
                setTimeout(() => {
                    this.chartManager.updateChart(this.rawData, this.chartType);
                }, 100);
            }
        },
        
        /**
         * Get signal badge class
         */
        getSignalBadgeClass() {
            const strengthMap = {
                'High': 'text-bg-danger',
                'Moderate': 'text-bg-warning',
                'Low': 'text-bg-info',
                'Normal': 'text-bg-secondary'
            };
            return strengthMap[this.signalStrength] || 'text-bg-secondary';
        },
        
        /**
         * Get signal color class
         */
        getSignalColorClass() {
            const colorMap = {
                'Widening': 'text-warning',
                'Narrowing': 'text-success',
                'Neutral': 'text-secondary'
            };
            return colorMap[this.marketSignal] || 'text-secondary';
        },
        
        /**
         * Cleanup
         */
        cleanup() {
            this.stopAutoRefresh();
            this.apiService.cancelRequest();
            this.chartManager.destroy();
        }
    };
}

