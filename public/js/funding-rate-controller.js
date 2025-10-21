/**
 * Funding Rate Controller
 * 
 * Manages Bitcoin Funding Rate dashboard
 * Data source: CryptoQuant API
 * 
 * Think like a trader:
 * - Funding Rate measures perpetual futures premium/discount
 * - Positive funding = longs pay shorts (bullish sentiment)
 * - Negative funding = shorts pay longs (bearish sentiment)
 * 
 * Build like an engineer:
 * - Clean data fetching with error handling
 * - Efficient chart rendering
 * - Statistical analysis (MA, std dev, outliers)
 */

function fundingRateController() {
    return {
        // UI state
        globalLoading: false,
        selectedExchange: 'binance',
        selectedInterval: '1d',
        globalPeriod: '1m',
        chartType: 'line',
        scaleType: 'linear',

        // Time ranges with days for calculation (exact copy from CDD)
        timeRanges: [
            { label: '1D', value: '1d', days: 1 },
            { label: '7D', value: '7d', days: 7 },
            { label: '1M', value: '1m', days: 30 },
            { label: 'YTD', value: 'ytd', days: this.getYTDDays() },
            { label: '1Y', value: '1y', days: 365 },
            { label: 'ALL', value: 'all', days: 1095 }
        ],

        // Data
        rawData: [],
        priceData: [],

        // Summary metrics
        currentFundingRate: 0,
        fundingChange: 0,
        avgFundingRate: 0,
        medianFundingRate: 0,
        maxFundingRate: 0,
        minFundingRate: 0,
        peakDate: '--',

        // Price metrics
        currentPrice: 0,
        priceChange: 0,

        // Analysis metrics
        ma7: 0,
        ma30: 0,
        highFundingEvents: 0,
        extremeFundingEvents: 0,

        // Market signal
        marketSignal: 'Neutral',
        signalStrength: 'Normal',
        signalDescription: 'Loading...',

        // Chart state
        mainChart: null,

        // Initialize
        init() {
            console.log('🚀 Funding Rate Dashboard initialized');
            this.loadData();
            
            // Auto refresh every 5 minutes
            setInterval(() => this.loadData(), 5 * 60 * 1000);
        },

        // Get YTD days (exact copy from CDD)
        getYTDDays() {
            const now = new Date();
            const startOfYear = new Date(now.getFullYear(), 0, 1);
            const diffTime = Math.abs(now - startOfYear);
            return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        },

        // Update exchange
        updateExchange() {
            console.log('🔄 Exchange changed to:', this.selectedExchange);
            this.loadData();
        },
        
        // Update interval
        updateInterval() {
            console.log('🔄 Interval changed to:', this.selectedInterval);
            this.loadData();
        },

        // Set time range
        setTimeRange(range) {
            if (this.globalPeriod === range) return;
            console.log('🔄 Setting time range to:', range);
            this.globalPeriod = range;
            this.loadData();
        },

        // Toggle scale type (linear/logarithmic)
        toggleScale(type) {
            if (this.scaleType === type) return;

            console.log('🔄 Toggling scale to:', type);
            this.scaleType = type;
            this.renderChart(); // Re-render with new scale
        },

        // Toggle chart type (line/bar)
        toggleChartType(type) {
            if (this.chartType === type) return;

            console.log('🔄 Toggling chart type to:', type);
            this.chartType = type;
            this.renderChart(); // Re-render with new type
        },

        // Get date range based on period (exact copy from CDD)
        getDateRange() {
            const endDate = new Date();
            const startDate = new Date();

            // Handle different period types
            if (this.globalPeriod === 'ytd') {
                // Year to date
                startDate.setMonth(0, 1); // January 1st of current year
            } else if (this.globalPeriod === 'all') {
                // All available data (3 years max for API limits)
                startDate.setDate(endDate.getDate() - 1095);
            } else {
                // Find the selected time range
                const selectedRange = this.timeRanges.find(r => r.value === this.globalPeriod);
                let days = selectedRange ? selectedRange.days : 30;

                // Handle special cases
                if (this.globalPeriod === 'all') {
                    days = 1095; // 3 years max for API limits
                }

                // Set start date to X days ago
                startDate.setDate(endDate.getDate() - days);
            }

            // Ensure we don't go too far back (API limits)
            const maxDaysBack = 1095; // 3 years
            const minStartDate = new Date();
            minStartDate.setDate(endDate.getDate() - maxDaysBack);

            if (startDate < minStartDate) {
                startDate.setTime(minStartDate.getTime());
            }

            // Format dates properly (YYYY-MM-DD)
            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };

            return {
                startDate: formatDate(startDate),
                endDate: formatDate(endDate)
            };
        },

        // Load data from API
        async loadData() {
            try {
                this.globalLoading = true;
                console.log('📡 Fetching Funding Rate data...');

                // Calculate date range based on period
                const { startDate, endDate } = this.getDateRange();
                console.log(`📅 Date range: ${startDate} to ${endDate}`);

                // Fetch funding rate data and price data in parallel
                const [fundingData, priceData] = await Promise.allSettled([
                    this.fetchFundingRateData(startDate, endDate),
                    this.loadPriceData(startDate, endDate)
                ]);

                // Handle funding rate data
                if (fundingData.status === 'fulfilled') {
                    this.rawData = fundingData.value;
                    console.log(`✅ Loaded ${this.rawData.length} Funding Rate data points`);
                } else {
                    console.error('❌ Error loading Funding Rate data:', fundingData.reason);
                    throw fundingData.reason;
                }

                // Calculate metrics
                this.calculateMetrics();

                // Render chart
                this.renderChart();

            } catch (error) {
                console.error('❌ Error loading data:', error);
                this.showError(error.message);
            } finally {
                this.globalLoading = false;
            }
        },

        // Fetch funding rate data
        async fetchFundingRateData(startDate, endDate) {
            const url = `${window.location.origin}/api/cryptoquant/funding-rate?start_date=${startDate}&end_date=${endDate}&exchange=${this.selectedExchange}&interval=${this.selectedInterval}`;

            const response = await fetch(url);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (!data.success || !Array.isArray(data.data)) {
                throw new Error('Invalid data format');
            }

            return data.data;
        },

        // Load Bitcoin price data
        async loadPriceData(startDate, endDate) {
            try {
                console.log('📡 Fetching Bitcoin price data...');
                const url = `${window.location.origin}/api/cryptoquant/btc-market-price?start_date=${startDate}&end_date=${endDate}`;
                
                const response = await fetch(url);
                
                if (response.ok) {
                    const data = await response.json();
                    
                    if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                        this.priceData = data.data.map(item => ({
                            date: item.date,
                            price: parseFloat(item.close || item.value)
                        }));

                        const latest = this.priceData[this.priceData.length - 1];
                        const previous = this.priceData[this.priceData.length - 2];

                        this.currentPrice = latest.price;
                        this.priceChange = previous ? ((latest.price - previous.price) / previous.price) * 100 : 0;

                        console.log(`✅ Loaded ${this.priceData.length} Bitcoin price points`);
                    }
                }
            } catch (error) {
                console.error('❌ Error loading price data:', error);
                this.priceData = [];
                this.currentPrice = 0;
                this.priceChange = 0;
            }
        },

        // Calculate metrics
        calculateMetrics() {
            if (this.rawData.length === 0) {
                console.warn('⚠️ No data available for metrics calculation');
                return;
            }

            // Sort by date
            const sorted = [...this.rawData].sort((a, b) =>
                new Date(a.date) - new Date(b.date)
            );

            // Extract funding rate values
            const fundingValues = sorted.map(d => parseFloat(d.value));

            // Current metrics
            this.currentFundingRate = fundingValues[fundingValues.length - 1] || 0;
            const previousFundingRate = fundingValues[fundingValues.length - 2] || this.currentFundingRate;
            
            // Calculate percentage change for funding rate
            this.fundingChange = previousFundingRate !== 0 ? 
                ((this.currentFundingRate - previousFundingRate) / Math.abs(previousFundingRate)) * 100 :
                0;

            // Statistical metrics
            this.avgFundingRate = fundingValues.length > 0 ? fundingValues.reduce((a, b) => a + b, 0) / fundingValues.length : 0;
            
            // Calculate median
            const sortedValues = [...fundingValues].sort((a, b) => a - b);
            const mid = Math.floor(sortedValues.length / 2);
            this.medianFundingRate = sortedValues.length % 2 === 0 ?
                (sortedValues[mid - 1] + sortedValues[mid]) / 2 :
                sortedValues[mid];
                
            this.maxFundingRate = fundingValues.length > 0 ? Math.max(...fundingValues) : 0;
            this.minFundingRate = fundingValues.length > 0 ? Math.min(...fundingValues) : 0;

            // Peak date
            if (fundingValues.length > 0) {
                const peakIndex = fundingValues.indexOf(this.maxFundingRate);
                this.peakDate = this.formatDate(sorted[peakIndex]?.date || sorted[0].date);
            } else {
                this.peakDate = '--';
            }

            // Moving averages
            this.ma7 = this.calculateMA(fundingValues, 7);
            this.ma30 = this.calculateMA(fundingValues, 30);

            // Market signal analysis
            if (fundingValues.length >= 2) {
                const stdDev = this.calculateStdDev(fundingValues);
                const threshold2Sigma = this.avgFundingRate + (2 * stdDev);
                const threshold3Sigma = this.avgFundingRate + (3 * stdDev);

                this.highFundingEvents = fundingValues.filter(v => Math.abs(v) > Math.abs(threshold2Sigma)).length;
                this.extremeFundingEvents = fundingValues.filter(v => Math.abs(v) > Math.abs(threshold3Sigma)).length;

                this.calculateMarketSignal(stdDev);
            }

            console.log('📊 Metrics calculated:', {
                current: this.currentFundingRate,
                avg: this.avgFundingRate,
                max: this.maxFundingRate,
                signal: this.marketSignal
            });
        },

        // Calculate market signal
        calculateMarketSignal(stdDev) {
            const zScore = (this.currentFundingRate - this.avgFundingRate) / stdDev;

            if (zScore > 2) {
                this.marketSignal = 'Extreme Bullish';
                this.signalStrength = 'Strong';
                this.signalDescription = 'Very high funding rate - longs paying shorts';
            } else if (zScore > 1) {
                this.marketSignal = 'Bullish';
                this.signalStrength = 'Moderate';
                this.signalDescription = 'Elevated funding rate detected';
            } else if (zScore < -2) {
                this.marketSignal = 'Extreme Bearish';
                this.signalStrength = 'Strong';
                this.signalDescription = 'Very negative funding rate - shorts paying longs';
            } else if (zScore < -1) {
                this.marketSignal = 'Bearish';
                this.signalStrength = 'Moderate';
                this.signalDescription = 'Negative funding rate detected';
            } else {
                this.marketSignal = 'Neutral';
                this.signalStrength = 'Normal';
                this.signalDescription = 'Normal funding rate conditions';
            }
        },

        // Render chart
        renderChart() {
            const canvas = document.getElementById('fundingRateMainChart');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');

            // Destroy existing chart
            if (this.mainChart) {
                this.mainChart.destroy();
            }

            // Prepare funding rate data
            const sorted = [...this.rawData].sort((a, b) =>
                new Date(a.date) - new Date(b.date)
            );

            const labels = sorted.map(d => d.date);
            const fundingValues = sorted.map(d => parseFloat(d.value));

            // Prepare price data (align with funding rate dates)
            const priceMap = new Map(this.priceData.map(p => [p.date, p.price]));
            const alignedPrices = labels.map(date => priceMap.get(date) || null);

            // Create gradient
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
            gradient.addColorStop(1, 'rgba(59, 130, 246, 0.05)');

            // Build datasets
            const datasets = [];

            // Dataset 1: Funding Rate
            if (this.chartType === 'bar') {
                datasets.push({
                    label: 'Funding Rate',
                    data: fundingValues,
                    backgroundColor: fundingValues.map(value => {
                        return value >= 0 ? 'rgba(34, 197, 94, 0.7)' : 'rgba(239, 68, 68, 0.7)';
                    }),
                    borderColor: fundingValues.map(value => {
                        return value >= 0 ? '#22c55e' : '#ef4444';
                    }),
                    borderWidth: 1,
                    yAxisID: 'y',
                    order: 2
                });
            } else {
                datasets.push({
                    label: 'Funding Rate',
                    data: fundingValues,
                    borderColor: '#3b82f6',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#3b82f6',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                    yAxisID: 'y',
                    order: 2
                });
            }

            // Dataset 2: Bitcoin Price overlay (if available)
            if (this.priceData.length > 0) {
                datasets.push({
                    label: 'BTC Price',
                    data: alignedPrices,
                    borderColor: '#f59e0b',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    type: 'line',
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#f59e0b',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                    yAxisID: 'y1',
                    order: 1
                });
            }

            // Create chart with dual Y-axis
            this.mainChart = new Chart(ctx, {
                type: this.chartType,
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: this.priceData.length > 0,
                            position: 'top',
                            align: 'end'
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleColor: '#f3f4f6',
                            bodyColor: '#f3f4f6',
                            borderColor: 'rgba(59, 130, 246, 0.5)',
                            borderWidth: 1,
                            callbacks: {
                                label: (context) => {
                                    const datasetLabel = context.dataset.label;
                                    const value = context.parsed.y;

                                    if (datasetLabel === 'BTC Price') {
                                        return `  ${datasetLabel}: $${value.toLocaleString('en-US', { maximumFractionDigits: 0 })}`;
                                    } else {
                                        const sentiment = value >= 0 ? '🟢 Bullish' : '🔴 Bearish';
                                        return [
                                            `  ${datasetLabel}: ${this.formatFundingRate(value)}`,
                                            `  ${sentiment} Sentiment`
                                        ];
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 11 }
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.08)'
                            }
                        },
                        y: {
                            type: 'linear',
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Funding Rate (%)',
                                color: '#3b82f6'
                            },
                            ticks: {
                                color: '#3b82f6',
                                callback: (value) => this.formatFundingRate(value)
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.08)'
                            }
                        },
                        y1: {
                            type: this.scaleType,
                            position: 'right',
                            display: this.priceData.length > 0,
                            title: {
                                display: true,
                                text: 'BTC Price (USD)',
                                color: '#f59e0b'
                            },
                            ticks: {
                                color: '#f59e0b',
                                callback: (value) => '$' + value.toLocaleString('en-US', { maximumFractionDigits: 0 })
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        },

        // Utility functions
        calculateMA(values, period) {
            if (values.length === 0) return 0;
            const effectivePeriod = Math.min(period, values.length);
            const slice = values.slice(-effectivePeriod);
            return slice.reduce((a, b) => a + b, 0) / slice.length;
        },

        calculateStdDev(values) {
            if (values.length === 0) return 0;
            if (values.length === 1) return 0;

            const avg = values.reduce((a, b) => a + b, 0) / values.length;
            const squareDiffs = values.map(v => Math.pow(v - avg, 2));
            const avgSquareDiff = squareDiffs.reduce((a, b) => a + b, 0) / squareDiffs.length;
            return Math.sqrt(avgSquareDiff);
        },

        formatFundingRate(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            return (num * 100).toFixed(4) + '%';
        },

        formatPrice(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            return '$' + num.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        },

        formatPriceUSD(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            return '$' + num.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        },

        formatChange(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const sign = value >= 0 ? '+' : '';
            return `${sign}${value.toFixed(2)}%`;
        },

        formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        },

        getTrendClass(value) {
            if (value > 0) return 'text-success';
            if (value < 0) return 'text-danger';
            return 'text-secondary';
        },

        getPriceTrendClass(value) {
            if (value > 0) return 'text-success';
            if (value < 0) return 'text-danger';
            return 'text-secondary';
        },

        getSignalBadgeClass() {
            const strengthMap = {
                'Strong': 'text-bg-danger',
                'Moderate': 'text-bg-warning',
                'Weak': 'text-bg-info',
                'Normal': 'text-bg-secondary'
            };
            return strengthMap[this.signalStrength] || 'text-bg-secondary';
        },

        getSignalColorClass() {
            const colorMap = {
                'Extreme Bullish': 'text-success',
                'Bullish': 'text-success',
                'Extreme Bearish': 'text-danger',
                'Bearish': 'text-danger',
                'Neutral': 'text-secondary'
            };
            return colorMap[this.marketSignal] || 'text-secondary';
        },

        showError(message) {
            console.error('Error:', message);
        }
    };
}

console.log('✅ Funding Rate Controller loaded');

// Make controller available globally for Alpine.js
window.fundingRateController = fundingRateController;