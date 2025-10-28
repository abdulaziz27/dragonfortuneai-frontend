/**
 * Funding Rate Controller (Exact Copy from CDD)
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
        // Global state
        globalPeriod: '1m', // Changed from '30d' to match new time ranges
        globalLoading: false,
        selectedExchange: 'binance',

        // Enhanced chart controls with YTD (initialized in init method)
        timeRanges: [],
        scaleType: 'linear', // 'linear' or 'logarithmic'

        // Chart intervals
        chartIntervals: [
            { label: '1H', value: '1h' },
            { label: '4H', value: '4h' },
            { label: '1D', value: '1d' },
            { label: '1W', value: '1w' }
        ],
        selectedInterval: '1d',

        // Get YTD days
        getYTDDays() {
            const now = new Date();
            const startOfYear = new Date(now.getFullYear(), 0, 1);
            const diffTime = Math.abs(now - startOfYear);
            return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        },

        // Data
        rawData: [],
        priceData: [], // Bitcoin price data for overlay

        // Data loading state
        dataLoaded: false,
        summaryDataLoaded: false,

        // Summary metrics (changed from CDD to Funding Rate)
        currentFundingRate: null,
        fundingChange: null,
        avgFundingRate: null,
        medianFundingRate: null,
        maxFundingRate: null,
        minFundingRate: null,
        peakDate: '--',

        // Price metrics
        currentPrice: null,
        priceChange: null,

        // Analysis metrics (adapted for funding rates)
        ma7: 0,
        ma30: 0,
        highFundingEvents: 0,
        extremeFundingEvents: 0,

        // Market signal
        marketSignal: 'Neutral',
        signalStrength: 'Normal',
        signalDescription: 'Loading...',

        // Chart state
        chartType: 'line',
        mainChart: null,
        distributionChart: null,
        maChart: null,

        // Initialize
        init() {
            console.log('🚀 Funding Rate Dashboard initialized');
            console.log('📊 Controller properties:', {
                chartType: this.chartType,
                scaleType: this.scaleType,
                chartIntervals: this.chartIntervals,
                selectedInterval: this.selectedInterval
            });

            // Initialize time ranges (removed 3M and 6M)
            this.timeRanges = [
                { label: '1D', value: '1d', days: 1 },
                { label: '7D', value: '7d', days: 7 },
                { label: '1M', value: '1m', days: 30 },
                { label: 'YTD', value: 'ytd', days: this.getYTDDays() },
                { label: '1Y', value: '1y', days: 365 },
                { label: 'ALL', value: 'all', days: 365 } // 3 years
            ];

            // Register Chart.js zoom plugin
            if (typeof Chart !== 'undefined' && Chart.register) {
                try {
                    // The zoom plugin should be automatically registered when loaded via CDN
                    console.log('✅ Chart.js zoom plugin should be available');
                } catch (error) {
                    console.warn('⚠️ Error with Chart.js zoom plugin:', error);
                }
            }

            // Wait for Chart.js to be ready
            if (typeof window.chartJsReady !== 'undefined') {
                window.chartJsReady.then(() => {
                    this.loadData();
                });
            } else {
                // Fallback: load data after a short delay
                setTimeout(() => this.loadData(), 500);
            }

            // Auto refresh every 5 minutes
            setInterval(() => this.loadData(), 5 * 60 * 1000);
        },

        // Update period filter
        updatePeriod() {
            console.log('🔄 Updating period to:', this.globalPeriod);
            this.loadData();
        },

        // Update exchange
        updateExchange() {
            console.log('🔄 Updating exchange to:', this.selectedExchange);
            this.loadData();
        },

        // Update interval
        updateInterval() {
            console.log('🔄 Updating interval to:', this.selectedInterval);
            this.loadData();
        },

        // Refresh all data
        refreshAll() {
            this.globalLoading = true;
            this.loadData().finally(() => {
                this.globalLoading = false;
            });
        },

        // Set time range
        setTimeRange(range) {
            if (this.globalPeriod === range) return;

            console.log('🔄 Setting time range to:', range);
            this.globalPeriod = range;
            this.loadData();
        },

        // Set chart interval (renamed to avoid conflict with native setInterval)
        setChartInterval(interval) {
            if (this.selectedInterval === interval) return;

            console.log('🔄 Setting chart interval to:', interval);
            this.selectedInterval = interval;
            this.loadData(); // Reload data with new interval
        },

        // Set chart interval (renamed to avoid conflict with native setInterval)
        setChartInterval(interval) {
            if (this.selectedInterval === interval) return;

            console.log('🔄 Setting chart interval to:', interval);
            this.selectedInterval = interval;
            this.loadData(); // Reload data with new interval
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

        // Reset chart zoom
        resetZoom() {
            if (this.mainChart && this.mainChart.resetZoom) {
                console.log('🔄 Resetting chart zoom');
                this.mainChart.resetZoom();
            }
        },

        // Export chart with enhanced options
        exportChart(format = 'png') {
            if (!this.mainChart) {
                console.warn('⚠️ No chart available for export');
                return;
            }

            try {
                console.log(`📸 Exporting chart as ${format.toUpperCase()}`);

                const timestamp = new Date().toISOString().split('T')[0];
                const filename = `Funding_Rate_Chart_${this.selectedExchange}_${timestamp}`;

                if (format === 'png') {
                    const link = document.createElement('a');
                    link.download = `${filename}.png`;
                    link.href = this.mainChart.toBase64Image('image/png', 1.0);
                    link.click();
                } else if (format === 'svg') {
                    // For SVG export, we'd need additional library
                    console.warn('⚠️ SVG export requires additional implementation');
                    // Fallback to PNG
                    this.exportChart('png');
                }

                // Show success notification (could be enhanced with toast)
                console.log('✅ Chart exported successfully');

            } catch (error) {
                console.error('❌ Error exporting chart:', error);
            }
        },

        // Share chart functionality
        shareChart() {
            if (!this.mainChart) {
                console.warn('⚠️ No chart available for sharing');
                return;
            }

            try {
                const dataUrl = this.mainChart.toBase64Image('image/png', 0.8);

                // Create shareable content
                const shareData = {
                    title: `Bitcoin Funding Rate - ${this.selectedExchange}`,
                    text: `Current Funding Rate: ${this.formatFundingRate(this.currentFundingRate)} | Signal: ${this.marketSignal}`,
                    url: window.location.href
                };

                // Use Web Share API if available
                if (navigator.share) {
                    navigator.share(shareData).then(() => {
                        console.log('✅ Chart shared successfully');
                    }).catch((error) => {
                        console.log('⚠️ Share cancelled or failed:', error);
                        this.fallbackShare(shareData);
                    });
                } else {
                    this.fallbackShare(shareData);
                }

            } catch (error) {
                console.error('❌ Error sharing chart:', error);
            }
        },

        // Fallback share method
        fallbackShare(shareData) {
            // Copy URL to clipboard
            navigator.clipboard.writeText(shareData.url).then(() => {
                console.log('✅ Chart URL copied to clipboard');
                // Could show toast notification here
            }).catch(() => {
                console.warn('⚠️ Could not copy to clipboard');
            });
        },

        // Load data from API with optimization
        async loadData() {
            try {
                this.globalLoading = true;
                console.log('📡 Fetching Funding Rate data...');

                // Calculate date range based on period
                const { startDate, endDate } = this.getDateRange();
                console.log(`📅 Date range: ${startDate} to ${endDate}`);

                // Fetch Funding Rate data and price data in parallel for better performance
                const [fundingData, priceData] = await Promise.allSettled([
                    this.fetchFundingRateData(startDate, endDate),
                    this.loadPriceData(startDate, endDate)
                ]);

                // Handle Funding Rate data
                if (fundingData.status === 'fulfilled') {
                    this.rawData = fundingData.value;
                    console.log(`✅ Loaded ${this.rawData.length} Funding Rate data points`);
                } else {
                    console.error('❌ Error loading Funding Rate data:', fundingData.reason);
                    throw fundingData.reason;
                }

                // Calculate metrics
                this.calculateMetrics();
                
                // Mark summary data as loaded
                this.summaryDataLoaded = true;
                this.dataLoaded = true;

                // Render charts with small delay to ensure DOM is ready
                setTimeout(() => {
                    try {
                        this.renderChart();
                        this.renderDistributionChart();
                        this.renderMAChart();
                    } catch (error) {
                        console.error('❌ Error rendering charts:', error);
                    }
                }, 150); // Increased delay slightly

            } catch (error) {
                console.error('❌ Error loading data:', error);
                this.showError(error.message);
            } finally {
                this.globalLoading = false;
            }
        },

        // Separate Funding Rate data fetching for better error handling
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
        // Load Bitcoin price data from CryptoQuant API only (NO DUMMY DATA)
        async loadPriceData(startDate, endDate) {
            try {
                console.log('📡 Fetching REAL Bitcoin price data from CryptoQuant...');
                await this.tryMultiplePriceSources(startDate, endDate);

                // Verify we have valid price data
                if (this.currentPrice > 0 && this.priceData.length > 0) {
                    console.log(`✅ CryptoQuant Bitcoin price loaded successfully: ${this.currentPrice.toLocaleString()}`);
                    console.log(`📊 Price data points: ${this.priceData.length}, 24h change: ${this.priceChange.toFixed(2)}%`);
                } else {
                    throw new Error('No valid CryptoQuant price data received');
                }

            } catch (error) {
                console.error('❌ Error loading CryptoQuant price data:', error);

                // NO DUMMY DATA - disable price overlay if CryptoQuant fails
                this.priceData = [];
                this.currentPrice = 0;
                this.priceChange = 0;

                console.warn('⚠️ Price overlay disabled - CryptoQuant API unavailable');
                console.warn('⚠️ Please check CryptoQuant API configuration and endpoints');
            }
        },

        // Get Bitcoin price from CryptoQuant API (REAL DATA - NO DUMMY)
        async tryMultiplePriceSources(startDate, endDate) {
            // Use the new CryptoQuant Bitcoin price endpoint
            try {
                const cryptoquantPrice = `${window.location.origin}/api/cryptoquant/btc-market-price?start_date=${startDate}&end_date=${endDate}`;
                console.log('📡 Fetching Bitcoin price from:', cryptoquantPrice);

                const response = await fetch(cryptoquantPrice);

                if (response.ok) {
                    const data = await response.json();
                    console.log('📊 CryptoQuant Bitcoin price response:', data);

                    if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                        // Transform price data
                        this.priceData = data.data.map(item => ({
                            date: item.date,
                            price: parseFloat(item.close || item.value) // Use close price or value
                        }));

                        // Calculate current price and change
                        const latest = this.priceData[this.priceData.length - 1];
                        const previous = this.priceData[this.priceData.length - 2];

                        this.currentPrice = latest.price;
                        this.priceChange = previous ? ((latest.price - previous.price) / previous.price) * 100 : 0;

                        console.log(`✅ Loaded ${this.priceData.length} REAL Bitcoin price points from CryptoQuant`);
                        console.log(`📊 Current BTC Price: ${this.currentPrice.toLocaleString()}, Change: ${this.priceChange.toFixed(2)}%`);
                        return;
                    } else {
                        console.warn('⚠️ CryptoQuant returned empty or invalid data:', data);
                    }
                } else {
                    console.warn('⚠️ CryptoQuant API response not OK:', response.status, response.statusText);
                }
            } catch (error) {
                console.warn('⚠️ CryptoQuant Bitcoin price endpoint failed:', error);
            }

            // If CryptoQuant endpoint fails, show error (NO DUMMY DATA)
            console.error('❌ CryptoQuant Bitcoin price endpoint failed. Please check API configuration.');
            throw new Error('No CryptoQuant Bitcoin price data available');
        },

        // Calculate price metrics
        calculatePriceMetrics() {
            if (this.priceData.length > 0) {
                this.currentPrice = this.priceData[this.priceData.length - 1].price;
                const yesterdayPrice = this.priceData[this.priceData.length - 2]?.price || this.currentPrice;
                this.priceChange = ((this.currentPrice - yesterdayPrice) / yesterdayPrice) * 100;
            }
        },

        // Get date range in days
        getDateRangeDays() {
            const selectedRange = this.timeRanges.find(r => r.value === this.globalPeriod);
            return selectedRange ? selectedRange.days : 30;
        },

        // Calculate all metrics (with safety checks for small datasets) - ADAPTED FOR FUNDING RATES
        calculateMetrics() {
            if (this.rawData.length === 0) {
                console.warn('⚠️ No data available for metrics calculation');
                return;
            }

            // Sort by date
            const sorted = [...this.rawData].sort((a, b) =>
                new Date(a.date) - new Date(b.date)
            );

            // Extract Funding Rate values
            const fundingValues = sorted.map(d => parseFloat(d.value));

            // Current metrics
            this.currentFundingRate = fundingValues[fundingValues.length - 1] || 0;
            const previousFundingRate = fundingValues[fundingValues.length - 2] || this.currentFundingRate;

            // Calculate absolute change for funding rate (in basis points)
            this.fundingChange = (this.currentFundingRate - previousFundingRate) * 10000; // Convert to basis points

            // Statistical metrics
            this.avgFundingRate = fundingValues.length > 0 ? fundingValues.reduce((a, b) => a + b, 0) / fundingValues.length : 0;
            this.medianFundingRate = fundingValues.length > 0 ? this.calculateMedian(fundingValues) : 0;
            this.maxFundingRate = fundingValues.length > 0 ? Math.max(...fundingValues) : 0;
            this.minFundingRate = fundingValues.length > 0 ? Math.min(...fundingValues) : 0;

            // Peak date (with safety check)
            if (fundingValues.length > 0) {
                const peakIndex = fundingValues.indexOf(this.maxFundingRate);
                this.peakDate = this.formatDate(sorted[peakIndex]?.date || sorted[0].date);
            } else {
                this.peakDate = '--';
            }

            // Moving averages (flexible - use available data)
            this.ma7 = this.calculateMA(fundingValues, 7);
            this.ma30 = this.calculateMA(fundingValues, 30);

            // Outlier detection (flexible approach) - ADAPTED FOR FUNDING RATES
            if (fundingValues.length >= 2) {
                const stdDev = this.calculateStdDev(fundingValues);
                
                // Count outliers using Z-score (correct statistical approach)
                this.highFundingEvents = fundingValues.filter(v => {
                    const zScore = Math.abs((v - this.avgFundingRate) / stdDev);
                    return zScore > 2; // |Z-score| > 2σ
                }).length;
                
                this.extremeFundingEvents = fundingValues.filter(v => {
                    const zScore = Math.abs((v - this.avgFundingRate) / stdDev);
                    return zScore > 3; // |Z-score| > 3σ
                }).length;

                // Market signal
                this.calculateMarketSignal(stdDev);
            } else if (fundingValues.length === 1) {
                // Single data point
                this.highFundingEvents = 0;
                this.extremeFundingEvents = 0;
                this.marketSignal = 'Single Data Point';
                this.signalStrength = 'Normal';
                this.signalDescription = 'Current Funding Rate: ' + this.formatFundingRate(this.currentFundingRate);
            } else {
                // Not enough data for statistical analysis
                this.highFundingEvents = 0;
                this.extremeFundingEvents = 0;
                this.marketSignal = 'Insufficient Data';
                this.signalStrength = 'N/A';
                this.signalDescription = 'Need more data points for analysis';
            }

            // Calculate Z-Score
            this.calculateCurrentZScore();

            console.log('📊 Metrics calculated:', {
                current: this.currentFundingRate,
                avg: this.avgFundingRate,
                stdDev: fundingValues.length >= 2 ? this.calculateStdDev(fundingValues) : 'N/A',
                max: this.maxFundingRate,
                highEvents: this.highFundingEvents,
                extremeEvents: this.extremeFundingEvents,
                signal: this.marketSignal,
                zScore: this.currentZScore,
                dataPoints: fundingValues.length
            });
        },

        // Calculate market signal - ADAPTED FOR FUNDING RATES
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

        // Render main chart (CryptoQuant style with price overlay) - ADAPTED FOR FUNDING RATES
        renderChart() {
            try {
                // Check if Chart.js is available
                if (typeof Chart === 'undefined') {
                    console.warn('⚠️ Chart.js not loaded yet, retrying...');
                    setTimeout(() => this.renderChart(), 100);
                    return;
                }

                const canvas = document.getElementById('fundingRateMainChart');
                if (!canvas) {
                    console.warn('⚠️ Canvas element not found: fundingRateMainChart');
                    return;
                }

                // Check if canvas is still in DOM
                if (!canvas.isConnected) {
                    console.warn('⚠️ Canvas element is not connected to DOM');
                    return;
                }

                const ctx = canvas.getContext('2d');
                if (!ctx) {
                    console.warn('⚠️ Cannot get 2D context from canvas');
                    return;
                }

                // Destroy existing chart safely
                if (this.mainChart) {
                    try {
                        this.mainChart.destroy();
                    } catch (error) {
                        console.warn('⚠️ Error destroying chart:', error);
                    }
                    this.mainChart = null;
                }

                // Check if we have data
                if (!this.rawData || this.rawData.length === 0) {
                    console.warn('⚠️ No data available for chart rendering');
                    return;
            }

            // Prepare Funding Rate data
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

            // Dataset 1: Funding Rate (main data)
            if (this.chartType === 'bar') {
                // Bar chart for Funding Rate with color coding (positive = green, negative = red)
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
                // Line chart for Funding Rate
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

            // Create chart with dual Y-axis (CryptoQuant style)
            try {
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
                        // Enhanced plugins with zoom and pan
                        plugins: {
                            zoom: {
                                enabled: true,
                                mode: 'xy',
                                limits: {
                                    x: { min: 'original', max: 'original' },
                                    y: { min: 'original', max: 'original' },
                                    y1: { min: 'original', max: 'original' }
                                },
                                pan: {
                                    enabled: true,
                                    mode: 'xy',
                                    threshold: 10,
                                    modifierKey: null
                                },
                                zoom: {
                                    wheel: {
                                        enabled: true,
                                        speed: 0.1
                                    },
                                    pinch: {
                                        enabled: true
                                    },
                                    mode: 'xy',
                                    drag: {
                                        enabled: true,
                                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                        borderColor: 'rgba(59, 130, 246, 0.5)',
                                        borderWidth: 1
                                    }
                                }
                            },
                            legend: {
                                display: this.priceData.length > 0,
                                position: 'top',
                                align: 'end',
                                labels: {
                                    color: '#64748b',
                                    font: { size: 11, weight: '500' },
                                    boxWidth: 12,
                                    boxHeight: 12,
                                    padding: 10,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(17, 24, 39, 0.95)',
                                titleColor: '#f3f4f6',
                                bodyColor: '#f3f4f6',
                                borderColor: 'rgba(59, 130, 246, 0.5)',
                                borderWidth: 1,
                                padding: 12,
                                displayColors: true,
                                boxWidth: 8,
                                boxHeight: 8,
                                callbacks: {
                                    title: (items) => {
                                        const date = new Date(items[0].label);
                                        return date.toLocaleDateString('en-US', {
                                            weekday: 'short',
                                            year: 'numeric',
                                            month: 'short',
                                            day: 'numeric'
                                        });
                                    },
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
                                    font: { size: 11 },
                                    maxRotation: 0,
                                    minRotation: 0,
                                    callback: function (value, index) {
                                        // Show every Nth label to avoid crowding
                                        const totalLabels = this.chart.data.labels.length;
                                        const showEvery = Math.max(1, Math.ceil(totalLabels / 12));
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
                                grid: {
                                    display: true,
                                    color: 'rgba(148, 163, 184, 0.08)',
                                    drawBorder: false
                                }
                            },
                            y: {
                                type: 'linear',
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Funding Rate (%)',
                                    color: '#3b82f6',
                                    font: { size: 11, weight: '600' }
                                },
                                ticks: {
                                    color: '#3b82f6',
                                    font: { size: 11 },
                                    callback: (value) => this.formatFundingRate(value)
                                },
                                grid: {
                                    color: 'rgba(148, 163, 184, 0.08)',
                                    drawBorder: false
                                }
                            },
                            y1: {
                                type: this.scaleType, // Dynamic scale type
                                position: 'right',
                                display: this.priceData.length > 0,
                                title: {
                                    display: true,
                                    text: 'BTC Price (USD)',
                                    color: '#f59e0b',
                                    font: { size: 11, weight: '600' }
                                },
                                ticks: {
                                    color: '#f59e0b',
                                    font: { size: 11 },
                                    callback: (value) => '$' + value.toLocaleString('en-US', { maximumFractionDigits: 0 })
                                },
                                grid: {
                                    display: false,
                                    drawBorder: false
                                }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('❌ Error creating chart:', error);
                this.mainChart = null;
            }
        } catch (error) {
            console.error('❌ Error in renderChart function:', error);
            this.mainChart = null;
        }
        },

        // Render distribution chart (histogram) - ADAPTED FOR FUNDING RATES
        renderDistributionChart() {
            const canvas = document.getElementById('fundingRateDistributionChart');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');

            if (this.distributionChart) {
                this.distributionChart.destroy();
            }

            // Create histogram bins with safety checks
            const values = this.rawData.map(d => parseFloat(d.value));

            // Always create histogram, adjust bin count based on data
            let binCount = Math.min(20, Math.max(1, values.length));
            if (values.length === 1) binCount = 1;
            else if (values.length === 2) binCount = 2;

            const bins = this.createHistogramBins(values, binCount);

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

        // Render moving average chart - ADAPTED FOR FUNDING RATES
        renderMAChart() {
            const canvas = document.getElementById('fundingRateMAChart');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');

            if (this.maChart) {
                this.maChart.destroy();
            }

            // Prepare data with safety checks
            const sorted = [...this.rawData].sort((a, b) =>
                new Date(a.date) - new Date(b.date)
            );

            // Always render MA chart, but adapt based on available data
            const labels = sorted.map(d => d.date);
            const values = sorted.map(d => parseFloat(d.value));

            // Calculate MA data (will return null for insufficient periods)
            const ma7Data = this.calculateMAArray(values, Math.min(7, values.length));
            const ma30Data = this.calculateMAArray(values, Math.min(30, values.length));

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

        // Utility: Get date range based on period (exact copy from CDD)
        getDateRange() {
            const endDate = new Date();
            const startDate = new Date();

            // Handle different period types
            if (this.globalPeriod === 'ytd') {
                // Year to date
                startDate.setMonth(0, 1); // January 1st of current year
            } else if (this.globalPeriod === 'all') {
                // All available data (1 year max for API stability)
                startDate.setDate(endDate.getDate() - 365);
            } else {
                // Find the selected time range
                const selectedRange = this.timeRanges.find(r => r.value === this.globalPeriod);
                let days = selectedRange ? selectedRange.days : 30;

                // Handle special cases
                if (this.globalPeriod === 'all') {
                    days = 365; // 1 year max for API stability
                }

                // Set start date to X days ago
                startDate.setDate(endDate.getDate() - days);
            }

            // Ensure we don't go too far back (API limits)
            const maxDaysBack = 365; // 1 year max for stability
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

        // Utility: Get time unit for chart
        getTimeUnit() {
            const unitMap = {
                '7d': 'day',
                '30d': 'day',
                '90d': 'week',
                '180d': 'week',
                '1y': 'month'
            };
            return unitMap[this.globalPeriod] || 'day';
        },

        // Utility: Calculate median
        calculateMedian(values) {
            const sorted = [...values].sort((a, b) => a - b);
            const mid = Math.floor(sorted.length / 2);
            return sorted.length % 2 === 0
                ? (sorted[mid - 1] + sorted[mid]) / 2
                : sorted[mid];
        },

        // Utility: Calculate standard deviation (safe for small datasets)
        calculateStdDev(values) {
            if (values.length === 0) return 0;
            if (values.length === 1) return 0; // No deviation with single value

            const avg = values.reduce((a, b) => a + b, 0) / values.length;
            const squareDiffs = values.map(v => Math.pow(v - avg, 2));
            const avgSquareDiff = squareDiffs.reduce((a, b) => a + b, 0) / squareDiffs.length;
            return Math.sqrt(avgSquareDiff);
        },

        // Utility: Calculate moving average (last N values, flexible)
        calculateMA(values, period) {
            if (values.length === 0) return 0;

            // Use available data if less than period
            const effectivePeriod = Math.min(period, values.length);
            const slice = values.slice(-effectivePeriod);
            return slice.reduce((a, b) => a + b, 0) / slice.length;
        },

        // Utility: Calculate MA array for all points (flexible for small datasets)
        calculateMAArray(values, period) {
            if (values.length === 0) return [];

            // For very small datasets, use available data
            const effectivePeriod = Math.min(period, values.length);

            return values.map((_, i) => {
                if (i < effectivePeriod - 1) {
                    // For early points, use available data (expanding window)
                    const slice = values.slice(0, i + 1);
                    return slice.reduce((a, b) => a + b, 0) / slice.length;
                }
                const slice = values.slice(i - effectivePeriod + 1, i + 1);
                return slice.reduce((a, b) => a + b, 0) / slice.length;
            });
        },

        // Utility: Create histogram bins (with safety checks)
        createHistogramBins(values, binCount) {
            if (!values || values.length === 0) {
                console.warn('⚠️ No values provided for histogram bins');
                return [];
            }

            const min = Math.min(...values);
            const max = Math.max(...values);

            // Handle case where all values are the same (min = max)
            if (min === max) {
                console.warn('⚠️ All values are the same, creating single bin');
                return [{
                    min: min,
                    max: max,
                    count: values.length,
                    label: this.formatFundingRate(min)
                }];
            }

            const binSize = (max - min) / binCount;

            // Safety check for binSize
            if (binSize <= 0) {
                console.warn('⚠️ Invalid bin size, using fallback');
                return [{
                    min: min,
                    max: max,
                    count: values.length,
                    label: this.formatFundingRate(min)
                }];
            }

            const bins = Array.from({ length: binCount }, (_, i) => ({
                min: min + (i * binSize),
                max: min + ((i + 1) * binSize),
                count: 0,
                label: ''
            }));

            values.forEach(v => {
                const binIndex = Math.min(
                    Math.floor((v - min) / binSize),
                    binCount - 1
                );
                if (bins[binIndex]) {
                    bins[binIndex].count++;
                }
            });

            bins.forEach(bin => {
                if (bin) {
                    bin.label = this.formatFundingRate(bin.min);
                }
            });

            return bins;
        },

        // Utility: Create gradient
        createGradient(ctx, color) {
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, color.replace(')', ', 0.3)').replace('rgb', 'rgba'));
            gradient.addColorStop(1, color.replace(')', ', 0)').replace('rgb', 'rgba'));
            return gradient;
        },

        // Utility: Format Funding Rate value (ADAPTED FOR FUNDING RATES)
        formatFundingRate(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            // Data already in percentage format from backend
            return num.toFixed(4) + '%';
        },

        // Utility: Format price
        formatPrice(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            return '$' + num.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        },

        // Utility: Format price with USD label
        formatPriceUSD(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            return '$' + num.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        },

        // Utility: Format change (basis points for funding rate)
        formatChange(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const sign = value >= 0 ? '+' : '';
            return `${sign}${value.toFixed(1)} bps`;
        },

        // Utility: Format date
        formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        },

        // Utility: Get trend class (for Funding Rate - higher positive is bullish, negative is bearish)
        getTrendClass(value) {
            if (value > 0) return 'text-success'; // Positive funding = bullish
            if (value < 0) return 'text-danger';  // Negative funding = bearish
            return 'text-secondary';
        },

        // Utility: Get price trend class (for price - higher is bullish)
        getPriceTrendClass(value) {
            if (value > 0) return 'text-success';
            if (value < 0) return 'text-danger';
            return 'text-secondary';
        },

        // Utility: Get signal badge class
        getSignalBadgeClass() {
            const strengthMap = {
                'Strong': 'text-bg-danger',
                'Moderate': 'text-bg-warning',
                'Weak': 'text-bg-info',
                'Normal': 'text-bg-secondary'
            };
            return strengthMap[this.signalStrength] || 'text-bg-secondary';
        },

        // Z-Score calculation and display
        currentZScore: 0,

        // Calculate current Z-Score
        calculateCurrentZScore() {
            if (this.rawData.length < 2) {
                this.currentZScore = 0;
                return;
            }

            const values = this.rawData.map(d => parseFloat(d.value));
            const mean = values.reduce((a, b) => a + b, 0) / values.length;
            const stdDev = this.calculateStdDev(values);
            
            if (stdDev === 0) {
                this.currentZScore = 0;
                return;
            }

            this.currentZScore = (this.currentFundingRate - mean) / stdDev;
        },

        // Format Z-Score for display
        formatZScore(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            const sign = num >= 0 ? '+' : '';
            return `${sign}${num.toFixed(2)}σ`;
        },

        // Get Z-Score badge class based on value
        getZScoreBadgeClass(value) {
            if (value === null || value === undefined || isNaN(value)) return 'text-bg-secondary';
            
            const absValue = Math.abs(value);
            if (absValue >= 3) return 'text-bg-danger';      // Extreme (>3σ)
            if (absValue >= 2) return 'text-bg-warning';     // High (>2σ)
            if (absValue >= 1) return 'text-bg-info';        // Moderate (>1σ)
            return 'text-bg-success';                         // Normal (<1σ)
        },

        // Utility: Get signal color class
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

        // Utility: Show error
        showError(message) {
            console.error('Error:', message);
            // Could add toast notification here
        }
    };
}

console.log('✅ Funding Rate Controller loaded');

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    // Make sure Alpine.js can access the controller
    if (typeof window.Alpine !== 'undefined') {
        console.log('🔗 Registering Funding Rate controller with Alpine.js');
    }
});

// Make controller available globally for Alpine.js
window.fundingRateController = fundingRateController;