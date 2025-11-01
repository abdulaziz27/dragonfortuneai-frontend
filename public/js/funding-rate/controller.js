/**
 * Funding Rate Main Controller
 * Coordinates data fetching, chart rendering, and metrics calculation
 */

import { FundingRateAPIService } from './api-service.js';
import { ChartManager } from './chart-manager.js';
import { FundingRateUtils } from './utils.js';

export function createFundingRateController() {
    return {
        // Services
        apiService: null,
        chartManager: null,
        
        // Global state
        globalPeriod: '1m',
        globalLoading: false,
        selectedSymbol: 'BTCUSDT',
        selectedExchange: 'binance',
        scaleType: 'linear',
        
        // Chart intervals
        chartIntervals: [
            // { label: '1M', value: '1m' },
            { label: '1H', value: '1h' },
            { label: '8H', value: '8h' }
        ],
        selectedInterval: '1h',
        
        // Time ranges (initialized in init)
        timeRanges: [],
        
        // Auto-refresh state
        refreshInterval: null,
        errorCount: 0,
        maxErrors: 3,
        lastUpdateTime: null,
        
        // Data
        rawData: [],
        priceData: [],
        dataLoaded: false,
        summaryDataLoaded: false,
        
        // Summary metrics
        currentFundingRate: null,
        fundingChange: null,
        avgFundingRate: null,  // Set from analytics API
        medianFundingRate: null,
        maxFundingRate: null,  // Set from analytics API
        minFundingRate: null,  // Set from analytics API
        fundingVolatility: null,  // Set from analytics API
        peakDate: '--',
        
        // Price metrics
        currentPrice: null,
        priceChange: null,
        priceDataAvailable: false,
        
        // Analysis metrics
        ma7: 0,
        ma30: 0,
        highFundingEvents: 0,
        extremeFundingEvents: 0,
        currentZScore: 0,
        
        // Market signal (from analytics API)
        marketSignal: 'Neutral',
        signalStrength: 'Normal',
        signalDescription: 'Loading...',
        analyticsData: null,
        analyticsLoading: false,
        
        // Chart state
        chartType: 'line',
        distributionChart: null,
        maChart: null,
        
        // Exchange comparison data
        exchangesData: [],
        exchangesLoading: false,
        
        /**
         * Initialize controller
         */
        init() {
            console.log('🚀 Funding Rate Dashboard initialized (Modular)');
            
            // Initialize services
            this.apiService = new FundingRateAPIService();
            this.chartManager = new ChartManager('fundingRateMainChart');
            
            // Initialize time ranges
            this.timeRanges = [
                { label: '1D', value: '1d', days: 1 },
                { label: '7D', value: '7d', days: 7 },
                { label: '1M', value: '1m', days: 30 },
                { label: 'ALL', value: 'all', days: 365 }
                // Commented for future use:
                // { label: 'YTD', value: 'ytd', days: this.getYTDDays() },
                // { label: '1Y', value: '1y', days: 365 },
            ];
            
            // Initial data load (will also trigger fetchAnalyticsData and fetchExchangesData)
            this.loadData();
            
            // Start auto-refresh
            this.startAutoRefresh();
            
            console.log('✅ Dashboard initialized - Analytics and Exchanges data will load in parallel');
            
            // Setup cleanup listeners
            window.addEventListener('beforeunload', () => this.cleanup());
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.stopAutoRefresh();
                    console.log('⏸️ Auto-refresh paused (tab hidden)');
                } else {
                    this.startAutoRefresh();
                    console.log('▶️ Auto-refresh resumed (tab visible)');
                }
            });
        },
        
        /**
         * Start auto-refresh with safety checks
         */
        startAutoRefresh() {
            this.stopAutoRefresh(); // Clear any existing interval
            
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
                this.loadData(); // This will also trigger fetchAnalyticsData()
                
                // Also refresh analytics data independently (handles its own errors)
                if (!this.analyticsLoading) {
                    this.fetchAnalyticsData().catch(err => {
                        console.warn('⚠️ Analytics refresh failed:', err);
                    });
                }

                // Also refresh exchanges data independently
                if (!this.exchangesLoading) {
                    this.fetchExchangesData().catch(err => {
                        console.warn('⚠️ Exchanges refresh failed:', err);
                    });
                }
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
         * Get Year-to-Date days
         */
        getYTDDays() {
            const now = new Date();
            const startOfYear = new Date(now.getFullYear(), 0, 1);
            const diffTime = Math.abs(now - startOfYear);
            return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        },
        
        /**
         * Get date range in days
         */
        getDateRangeDays() {
            const selectedRange = this.timeRanges.find(r => r.value === this.globalPeriod);
            return selectedRange ? selectedRange.days : 30;
        },
        
        /**
         * Calculate start and end date based on selected period
         * @returns {{startDate: Date, endDate: Date}}
         */
        getDateRange() {
            const now = new Date();
            const days = this.getDateRangeDays();
            
            let startDate;
            let endDate = new Date(now); // End date is always "now" (latest available)
            
            if (this.globalPeriod === 'ytd') {
                // Year to date: from start of year to now
                startDate = new Date(now.getFullYear(), 0, 1);
            } else if (this.globalPeriod === 'all') {
                // All data: from a very old date (e.g., 2 years ago) to now
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
         * Load data from API
         */
        async loadData() {
            if (this.globalLoading) {
                console.log('⏭️ Skip load (already loading)');
                return;
            }
            
            this.globalLoading = true;
            
            try {
                console.log('📡 Loading funding rate data...');
                
                const exchange = FundingRateUtils.capitalizeExchange(this.selectedExchange);
                
                // Calculate date range (startDate to endDate)
                const dateRange = this.getDateRange();
                
                // Calculate expected records for verification
                const intervalHours = {
                    '1m': 1 / 60,  // 1 minute = 1/60 hours
                    '1h': 1,
                    '8h': 8
                    // Commented for future use:
                    // '4h': 4,
                    // '1d': 24,
                    // '1w': 168
                };
                const hours = intervalHours[this.selectedInterval] || 8;
                const days = this.getDateRangeDays();
                const expectedRecords = Math.ceil((days * 24) / hours);
                
                // Fetch data from internal API with date range
                const data = await this.apiService.fetchHistory({
                    symbol: this.selectedSymbol,
                    exchange: exchange,
                    interval: this.selectedInterval,
                    dateRange: dateRange // Pass date range instead of limit
                });
                
                // Handle cancelled requests
                if (data === null) {
                    console.log('🚫 Request was cancelled');
                    return;
                }
                
                this.rawData = data;
                this.errorCount = 0; // Reset on success
                this.lastUpdateTime = new Date();
                
                console.log('✅ Data loaded:', this.rawData.length, 'records');
                
                // Verify data completeness after filtering
                const actualCount = this.rawData.length;
                const coveragePercent = (actualCount / expectedRecords) * 100;
                
                if (actualCount < expectedRecords * 0.8) {
                    // Less than 80% of expected data - might be incomplete
                    console.warn(`⚠️ Data completeness: ${coveragePercent.toFixed(1)}% (${actualCount}/${expectedRecords} records)`);
                    console.warn(`⚠️ Consider increasing limit or checking backend filter`);
                } else {
                    console.log(`✅ Data completeness: ${coveragePercent.toFixed(1)}% (${actualCount}/${expectedRecords} records)`);
                }
                
                // Extract price data from funding rate data (bonus: it's included!)
                this.priceData = data
                    .filter(d => d.price !== null && d.price !== undefined)
                    .map(d => ({ date: d.date, price: d.price }));
                
                // Debug price data extraction
                console.log('💰 Price data extracted:', this.priceData.length, 'points');
                if (this.priceData.length === 0) {
                    console.warn('⚠️ No price data available! symbol_price might be null in API response');
                } else {
                    console.log('💰 Price range:', {
                        min: Math.min(...this.priceData.map(d => d.price)),
                        max: Math.max(...this.priceData.map(d => d.price)),
                        latest: this.priceData[this.priceData.length - 1]?.price
                    });
                }
                
                // Calculate metrics
                this.calculateMetrics();
                this.summaryDataLoaded = true;
                this.dataLoaded = true;
                
                // Fetch analytics data in parallel (includes bias + summary stats)
                // Important: This sets marketSignal, signalStrength, and fundingVolatility
                this.fetchAnalyticsData().catch(err => {
                    console.error('❌ Analytics fetch failed in loadData:', err);
                    console.error('❌ This will cause market signal and volatility to remain at default values');
                });

                // Fetch exchanges comparison data in parallel
                this.fetchExchangesData().catch(err => {
                    console.warn('⚠️ Exchanges fetch failed:', err);
                });
                
                // Update charts with delay to ensure cleanup is complete
                setTimeout(() => {
                    try {
                        this.chartManager.updateChart(this.rawData, this.priceData);
                        // Distribution and MA charts removed - not industry standard for funding rate
                    } catch (error) {
                        console.error('❌ Error updating charts:', error);
                    }
                }, 150); // Increased delay for safer cleanup
                
            } catch (error) {
                console.error('❌ Error loading data:', error);
                this.errorCount++;
                
                if (this.errorCount >= this.maxErrors) {
                    this.stopAutoRefresh();
                    this.showError('Auto-refresh disabled due to repeated errors');
                }
            } finally {
                this.globalLoading = false;
            }
        },
        
        /**
         * Calculate all metrics
         */
        calculateMetrics() {
            if (this.rawData.length === 0) {
                console.warn('⚠️ No data for metrics calculation');
                return;
            }
            
            const sorted = [...this.rawData].sort((a, b) => 
                new Date(a.date) - new Date(b.date)
            );
            
            const fundingValues = sorted.map(d => parseFloat(d.value));
            
            // Current metrics
            this.currentFundingRate = fundingValues[fundingValues.length - 1] || 0;
            const previousFundingRate = fundingValues[fundingValues.length - 2] || this.currentFundingRate;
            this.fundingChange = (this.currentFundingRate - previousFundingRate) * 10000; // Basis points
            
            // Statistical metrics
            // Note: avgFundingRate, maxFundingRate, minFundingRate are now set from analytics API
            // Only calculate if not already set by analytics (fallback)
            if (this.avgFundingRate === null || this.avgFundingRate === undefined) {
                this.avgFundingRate = fundingValues.reduce((a, b) => a + b, 0) / fundingValues.length;
            }
            // Median still calculated from raw data (not in analytics API)
            this.medianFundingRate = FundingRateUtils.calculateMedian(fundingValues);
            // Max and min from analytics if available, otherwise calculate from raw data
            if (this.maxFundingRate === null || this.maxFundingRate === undefined) {
                this.maxFundingRate = Math.max(...fundingValues);
            }
            if (this.minFundingRate === null || this.minFundingRate === undefined) {
                this.minFundingRate = Math.min(...fundingValues);
            }
            
            // Peak date
            const peakIndex = fundingValues.indexOf(this.maxFundingRate);
            this.peakDate = FundingRateUtils.formatDate(sorted[peakIndex]?.date || sorted[0].date);
            
            // Moving averages
            this.ma7 = FundingRateUtils.calculateMA(fundingValues, 7);
            this.ma30 = FundingRateUtils.calculateMA(fundingValues, 30);
            
            // Price metrics
            if (this.priceData.length > 0) {
                this.currentPrice = this.priceData[this.priceData.length - 1].price;
                const yesterdayPrice = this.priceData[this.priceData.length - 2]?.price || this.currentPrice;
                this.priceChange = ((this.currentPrice - yesterdayPrice) / yesterdayPrice) * 100;
                this.priceDataAvailable = true;
            } else {
                this.currentPrice = null;
                this.priceChange = null;
                this.priceDataAvailable = false;
            }
            
            // Outlier detection
            if (fundingValues.length >= 2) {
                const stdDev = FundingRateUtils.calculateStdDev(fundingValues);
                
                // Calculate volatility as fallback if API doesn't provide it
                // Only set if not already set by analytics API (to avoid overwriting API values)
                if (this.fundingVolatility === null || this.fundingVolatility === undefined) {
                    this.fundingVolatility = stdDev; // Use stdDev as volatility fallback
                }
                
                this.highFundingEvents = fundingValues.filter(v => {
                    const zScore = Math.abs((v - this.avgFundingRate) / stdDev);
                    return zScore > 2;
                }).length;
                
                this.extremeFundingEvents = fundingValues.filter(v => {
                    const zScore = Math.abs((v - this.avgFundingRate) / stdDev);
                    return zScore > 3;
                }).length;
                
                // Market signal is now fetched from /api/funding-rate/analytics endpoint
                // Removed calculateMarketSignal() call - see fetchAnalyticsData()
            }
            
            // Calculate Z-Score
            this.calculateCurrentZScore();
            
            console.log('📊 Metrics calculated:', {
                current: this.currentFundingRate,
                avg: this.avgFundingRate,
                max: this.maxFundingRate,
                signal: this.marketSignal
            });
        },
        
        /**
         * Map analytics API response to UI state
         * Uses direct values from API response without thresholds
         */
        mapAnalyticsToState(analyticsData) {
            console.log('🔄 Mapping analytics data:', analyticsData);
            
            if (!analyticsData) {
                console.warn('⚠️ No analytics data provided, setting defaults');
                this.marketSignal = 'Neutral';
                this.signalStrength = 'Normal';
                this.signalDescription = 'No analytics data available';
                return;
            }

            // Map bias direction to marketSignal (direct from API - use long/short terminology)
            // Analytics format: bias.direction = "long_pays_short" or "short_pays_long"
            console.log('🔍 Bias value:', analyticsData.bias, 'Type:', typeof analyticsData.bias);
            
            if (analyticsData.bias === 'long_pays_short') {
                this.marketSignal = 'Long';
                this.signalDescription = 'Long bias - longs paying shorts';
                console.log('✅ Mapped to Long signal');
            } else if (analyticsData.bias === 'short_pays_long') {
                this.marketSignal = 'Short';
                this.signalDescription = 'Short bias - shorts paying longs';
                console.log('✅ Mapped to Short signal');
            } else {
                console.warn('⚠️ Unknown bias value, setting to Neutral:', analyticsData.bias);
                this.marketSignal = 'Neutral';
                this.signalDescription = 'Neutral market conditions';
            }

            // Use strength value directly from API (format for display)
            if (analyticsData.biasStrength !== null && analyticsData.biasStrength !== undefined) {
                const strengthPercent = (analyticsData.biasStrength * 100).toFixed(2);
                this.signalStrength = `${strengthPercent}%`;
                console.log('✅ Signal strength set:', this.signalStrength);
            } else {
                console.warn('⚠️ No biasStrength, using default');
                this.signalStrength = 'Normal';
            }

            // Update summary stats from API (replace frontend calculations)
            if (analyticsData.average !== null && analyticsData.average !== undefined) {
                this.avgFundingRate = analyticsData.average;
            }
            if (analyticsData.max !== null && analyticsData.max !== undefined) {
                this.maxFundingRate = analyticsData.max;
                // Note: peakDate might not be available from analytics, keep existing logic if needed
            }
            if (analyticsData.min !== null && analyticsData.min !== undefined) {
                this.minFundingRate = analyticsData.min;
            }
            // Update volatility from API (will override fallback from calculateMetrics if available)
            if (analyticsData.volatility !== null && analyticsData.volatility !== undefined) {
                this.fundingVolatility = analyticsData.volatility;
                console.log('✅ Volatility set from API:', analyticsData.volatility);
            } else {
                console.warn('⚠️ Volatility not available from API, will use fallback from calculateMetrics');
            }
        },

        /**
         * Fetch analytics data from API (includes bias + summary stats)
         */
        async fetchAnalyticsData() {
            if (this.analyticsLoading) {
                console.log('⏭️ Skip analytics fetch (already loading)');
                return;
            }

            this.analyticsLoading = true;

            try {
                console.log('📡 Fetching analytics data...');

                const exchange = FundingRateUtils.capitalizeExchange(this.selectedExchange);
                const limit = 1000; // Large limit to ensure comprehensive stats

                const analyticsData = await this.apiService.fetchAnalytics(
                    this.selectedSymbol,
                    exchange,
                    this.selectedInterval,
                    limit
                );

                // Handle cancelled requests
                if (analyticsData === null) {
                    console.warn('🚫 Analytics request was cancelled - market signal will not update');
                    return;
                }

                if (!analyticsData) {
                    console.error('❌ Analytics data is null/undefined after fetch');
                    return;
                }

                this.analyticsData = analyticsData;
                
                console.log('📊 Analytics data before mapping:', {
                    hasBias: !!analyticsData.bias,
                    hasStrength: !!analyticsData.biasStrength,
                    hasVolatility: !!analyticsData.volatility,
                    biasValue: analyticsData.bias
                });
                
                // Map analytics data to UI state (includes bias + summary stats)
                this.mapAnalyticsToState(analyticsData);

                console.log('✅ Analytics data loaded:', {
                    bias: analyticsData.bias,
                    strength: analyticsData.biasStrength,
                    average: analyticsData.average,
                    max: analyticsData.max,
                    volatility: analyticsData.volatility,
                    signal: this.marketSignal,
                    signalStrength: this.signalStrength,
                    volatilitySet: this.fundingVolatility
                });

            } catch (error) {
                console.error('❌ Error loading analytics data:', error);
                console.error('❌ Error details:', {
                    symbol: this.selectedSymbol,
                    exchange: FundingRateUtils.capitalizeExchange(this.selectedExchange),
                    interval: this.selectedInterval,
                    errorMessage: error.message
                });
                // Don't update errorCount or stop auto-refresh for analytics errors
                // Only reset if values are still at default (to avoid overwriting existing good data)
                if (this.marketSignal === 'Neutral' && this.signalStrength === 'Normal') {
                    this.marketSignal = 'Neutral';
                    this.signalStrength = 'Normal';
                    this.signalDescription = 'Error loading analytics data';
                }
                // Don't reset volatility if it's already set from calculateMetrics fallback
            } finally {
                this.analyticsLoading = false;
            }
        },

        /**
         * Fetch exchanges comparison data
         */
        async fetchExchangesData() {
            if (this.exchangesLoading) {
                console.log('⏭️ Skip exchanges fetch (already loading)');
                return;
            }

            this.exchangesLoading = true;

            try {
                console.log('📡 Fetching exchanges comparison data...');

                const data = await this.apiService.fetchExchanges(this.selectedSymbol, 50);

                // Handle cancelled requests
                if (data === null) {
                    console.log('🚫 Exchanges request was cancelled');
                    return;
                }

                // Map selectedInterval to margin_type format
                const intervalMap = {
                    '1m': '1m',
                    '1h': '1h',
                    '8h': '8h'
                };
                const targetMarginType = intervalMap[this.selectedInterval] || this.selectedInterval;

                // Filter by margin_type matching selectedInterval
                const filteredByInterval = data.filter(item => item.margin_type === targetMarginType);

                // Group by exchange, take the one with latest (highest) next_funding_time
                const exchangeMap = new Map();
                
                filteredByInterval.forEach(item => {
                    const exchangeKey = item.exchange;
                    const existing = exchangeMap.get(exchangeKey);
                    
                    // Take the one with latest (highest) next_funding_time
                    if (!existing || item.next_funding_time > existing.next_funding_time) {
                        exchangeMap.set(exchangeKey, {
                            exchange: item.exchange,
                            funding_rate: parseFloat(item.funding_rate),
                            next_funding_time: item.next_funding_time,
                            margin_type: item.margin_type,
                            pair: item.pair
                        });
                    }
                });

                // Convert to array and sort by exchange name
                const filtered = Array.from(exchangeMap.values())
                    .sort((a, b) => a.exchange.localeCompare(b.exchange));

                this.exchangesData = filtered;

                console.log('✅ Exchanges data processed:', {
                    total: filtered.length,
                    exchanges: filtered.map(e => e.exchange)
                });

            } catch (error) {
                console.error('❌ Error loading exchanges data:', error);
                this.exchangesData = [];
            } finally {
                this.exchangesLoading = false;
            }
        },

        /**
         * Format next funding time for display
         */
        formatNextFundingTime(timestamp) {
            if (!timestamp) return '--';
            const date = new Date(timestamp);
            const now = new Date();
            const diffMs = date - now;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMins / 60);
            
            if (diffMins < 0) {
                return 'Lalu';
            } else if (diffMins < 60) {
                return `${diffMins} menit lagi`;
            } else if (diffHours < 24) {
                return `${diffHours} jam lagi`;
            } else {
                return date.toLocaleDateString('id-ID', { 
                    month: 'short', 
                    day: 'numeric', 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
            }
        },

        /**
         * Calculate arbitrage opportunity (funding rate difference)
         */
        calculateArbitrage() {
            if (this.exchangesData.length < 2) return null;
            
            const rates = this.exchangesData.map(e => e.funding_rate);
            const maxRate = Math.max(...rates);
            const minRate = Math.min(...rates);
            const spread = maxRate - minRate;
            
            if (spread <= 0) return null;
            
            const maxExchange = this.exchangesData.find(e => e.funding_rate === maxRate);
            const minExchange = this.exchangesData.find(e => e.funding_rate === minRate);
            
            return {
                spread: spread,
                maxExchange: maxExchange.exchange,
                minExchange: minExchange.exchange,
                maxRate: maxRate,
                minRate: minRate
            };
        },
        
        /**
         * Calculate current Z-Score
         */
        calculateCurrentZScore() {
            if (this.rawData.length < 2) {
                this.currentZScore = 0;
                return;
            }
            
            const values = this.rawData.map(d => parseFloat(d.value));
            const mean = values.reduce((a, b) => a + b, 0) / values.length;
            const stdDev = FundingRateUtils.calculateStdDev(values);
            
            if (stdDev === 0) {
                this.currentZScore = 0;
                return;
            }
            
            this.currentZScore = (this.currentFundingRate - mean) / stdDev;
        },
        
        /**
         * Render distribution chart
         */
        renderDistributionChart() {
            const canvas = document.getElementById('fundingRateDistributionChart');
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            
            // Proper cleanup
            if (this.distributionChart) {
                try {
                    this.distributionChart.stop();
                    this.distributionChart.destroy();
                } catch (e) {
                    console.warn('⚠️ Distribution chart cleanup error:', e);
                }
                this.distributionChart = null;
            }
            
            const values = this.rawData.map(d => parseFloat(d.value));
            let binCount = Math.min(20, Math.max(1, values.length));
            if (values.length === 1) binCount = 1;
            else if (values.length === 2) binCount = 2;
            
            const bins = FundingRateUtils.createHistogramBins(values, binCount);
            
            this.distributionChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: bins.map(b => b.label),
                    datasets: [{
                        label: 'Frequency',
                        data: bins.map(b => b.count),
                        backgroundColor: 'rgba(139, 92, 246, 0.6)',
                        borderColor: '#8b5cf6',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false, // Disable animation for stability during auto-refresh
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            ticks: { color: '#94a3b8', maxRotation: 45 },
                            grid: { display: false }
                        },
                        y: {
                            ticks: { color: '#94a3b8' },
                            grid: { color: 'rgba(148, 163, 184, 0.1)' }
                        }
                    }
                }
            });
        },
        
        /**
         * Render moving average chart
         */
        renderMAChart() {
            const canvas = document.getElementById('fundingRateMAChart');
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            
            // Proper cleanup
            if (this.maChart) {
                try {
                    this.maChart.stop();
                    this.maChart.destroy();
                } catch (e) {
                    console.warn('⚠️ MA chart cleanup error:', e);
                }
                this.maChart = null;
            }
            
            const sorted = [...this.rawData].sort((a, b) => 
                new Date(a.date) - new Date(b.date)
            );
            
            const labels = sorted.map(d => d.date);
            const values = sorted.map(d => parseFloat(d.value));
            
            const ma7Data = FundingRateUtils.calculateMAArray(values, Math.min(7, values.length));
            const ma30Data = FundingRateUtils.calculateMAArray(values, Math.min(30, values.length));
            
            this.maChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Funding Rate',
                            data: values,
                            borderColor: '#94a3b8',
                            backgroundColor: 'transparent',
                            borderWidth: 1,
                            tension: 0.4
                        },
                        {
                            label: '7-Day MA',
                            data: ma7Data,
                            borderColor: '#22c55e',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.4
                        },
                        {
                            label: '30-Day MA',
                            data: ma30Data,
                            borderColor: '#ef4444',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false, // Disable animation for stability during auto-refresh
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: { color: '#94a3b8', boxWidth: 20 }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#94a3b8',
                                maxRotation: 45,
                                minRotation: 45,
                                callback: function (value, index) {
                                    const totalLabels = this.chart.data.labels.length;
                                    const showEvery = Math.ceil(totalLabels / 8);
                                    if (index % showEvery === 0) {
                                        const date = this.chart.data.labels[index];
                                        return new Date(date).toLocaleDateString('en-US', {
                                            month: 'short',
                                            day: 'numeric'
                                        });
                                    }
                                    return '';
                                }
                            },
                            grid: { display: false }
                        },
                        y: {
                            ticks: { color: '#94a3b8' },
                            grid: { color: 'rgba(148, 163, 184, 0.1)' }
                        }
                    }
                }
            });
        },
        
        /**
         * Set time range
         */
        setTimeRange(range) {
            if (this.globalPeriod === range) return;
            console.log('🔄 Setting time range to:', range);
            this.globalPeriod = range;
            this.loadData();
        },
        
        /**
         * Set chart interval
         */
        setChartInterval(interval) {
            if (this.selectedInterval === interval) return;
            console.log('🔄 Setting chart interval to:', interval);
            this.selectedInterval = interval;
            this.loadData();
        },
        
        /**
         * Update symbol
         */
        updateSymbol() {
            console.log('🔄 Updating symbol to:', this.selectedSymbol);
            this.loadData();
        },
        
        /**
         * Update exchange
         */
        updateExchange() {
            console.log('🔄 Updating exchange to:', this.selectedExchange);
            this.loadData();
        },
        
        /**
         * Update interval
         */
        updateInterval() {
            console.log('🔄 Updating interval to:', this.selectedInterval);
            this.loadData();
        },
        
        /**
         * Refresh all data
         */
        refreshAll() {
            this.loadData();
        },
        
        /**
         * Cleanup on destroy
         */
        cleanup() {
            console.log('🧹 Cleaning up...');
            this.stopAutoRefresh();
            
            if (this.chartManager) {
                this.chartManager.destroy();
            }
            
            if (this.distributionChart) {
                try {
                    this.distributionChart.destroy();
                } catch (e) {
                    console.warn('⚠️ Distribution chart cleanup error:', e);
                }
            }
            
            if (this.maChart) {
                try {
                    this.maChart.destroy();
                } catch (e) {
                    console.warn('⚠️ MA chart cleanup error:', e);
                }
            }
            
            if (this.apiService) {
                this.apiService.cancelRequest();
            }
        },
        
        /**
         * Format funding rate
         */
        formatFundingRate(value) {
            return FundingRateUtils.formatFundingRate(value);
        },
        
        /**
         * Format price
         */
        formatPrice(value) {
            return FundingRateUtils.formatPrice(value);
        },
        
        /**
         * Format price with USD label
         */
        formatPriceUSD(value) {
            return FundingRateUtils.formatPrice(value);
        },
        
        /**
         * Format change
         */
        formatChange(value) {
            return FundingRateUtils.formatChange(value);
        },
        
        /**
         * Format Z-Score
         */
        formatZScore(value) {
            return FundingRateUtils.formatZScore(value);
        },
        
        /**
         * Get trend class
         */
        getTrendClass(value) {
            if (value > 0) return 'text-success';
            if (value < 0) return 'text-danger';
            return 'text-secondary';
        },
        
        /**
         * Get price trend class
         */
        getPriceTrendClass(value) {
            if (value > 0) return 'text-success';
            if (value < 0) return 'text-danger';
            return 'text-secondary';
        },
        
        /**
         * Get signal badge class
         */
        getSignalBadgeClass() {
            // signalStrength is now a percentage string (e.g., "51.75%")
            // For legacy compatibility, check if it's old format first
            const strengthMap = {
                'Strong': 'text-bg-danger',
                'Moderate': 'text-bg-warning',
                'Weak': 'text-bg-info',
                'Normal': 'text-bg-secondary'
            };
            
            // If it's old format, use the map
            if (strengthMap[this.signalStrength]) {
                return strengthMap[this.signalStrength];
            }
            
            // New format: percentage string - parse and assign color based on strength value
            // Extract numeric value from percentage string (e.g., "51.75%" -> 51.75)
            const strengthMatch = this.signalStrength.match(/(\d+\.?\d*)%/);
            if (strengthMatch) {
                const strengthValue = parseFloat(strengthMatch[1]);
                // Map percentage to badge color
                if (strengthValue >= 50) return 'text-bg-danger';    // Strong (red)
                if (strengthValue >= 20) return 'text-bg-warning';  // Moderate (yellow)
                if (strengthValue >= 5) return 'text-bg-info';      // Weak (blue)
                return 'text-bg-secondary';                          // Normal (gray)
            }
            
            return 'text-bg-secondary';
        },
        
        /**
         * Get signal color class
         */
        getSignalColorClass() {
            const colorMap = {
                'Long': 'text-success',
                'Short': 'text-danger',
                'Neutral': 'text-secondary'
            };
            return colorMap[this.marketSignal] || 'text-secondary';
        },
        
        /**
         * Get Z-Score badge class
         */
        getZScoreBadgeClass(value) {
            if (value === null || value === undefined || isNaN(value)) return 'text-bg-secondary';
            
            const absValue = Math.abs(value);
            if (absValue >= 3) return 'text-bg-danger';
            if (absValue >= 2) return 'text-bg-warning';
            if (absValue >= 1) return 'text-bg-info';
            return 'text-bg-success';
        },
        
        /**
         * Show error message
         */
        showError(message) {
            console.error('Error:', message);
            // Could add toast notification here
        }
    };
}

