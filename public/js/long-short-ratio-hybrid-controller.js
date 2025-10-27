/**
 * Long-Short Ratio Hybrid Controller
 * 
 * HYBRID API APPROACH:
 * - MAIN DATA: Coinglass API for Long/Short Ratio data
 * - PRICE OVERLAY: CryptoQuant API for Bitcoin price (as reference)
 * 
 * Think like a trader:
 * - Long/Short Ratio shows market sentiment and positioning
 * - Ratio > 2.0: Long crowded → Potential correction/short squeeze
 * - Ratio < 0.5: Short crowded → Potential rally/long squeeze
 * - Ratio 0.8-1.2: Balanced market → Healthy trend continuation
 * 
 * Build like an engineer:
 * - Clean dual API integration (Coinglass + CryptoQuant)
 * - Professional chart rendering with multiple ratio types
 * - Statistical analysis & market sentiment interpretation
 */

function longShortRatioHybridController() {
    return {
        // Global state
        globalPeriod: '1d', // Default to 1 day
        globalLoading: false,
        selectedExchange: 'Binance', // Coinglass uses capitalized exchange names
        selectedSymbol: 'BTCUSDT', // Coinglass format

        // Enhanced chart controls
        timeRanges: [],
        scaleType: 'linear', // 'linear' or 'logarithmic'

        // Chart intervals (Coinglass supported)
        chartIntervals: [
            { label: '5M', value: '5m' },
            { label: '15M', value: '15m' },
            { label: '30M', value: '30m' },
            { label: '1H', value: '1h' },
            { label: '4H', value: '4h' },
            { label: '1D', value: '1d' }
        ],
        selectedInterval: '1h',

        // Data containers
        globalAccountData: [], // Global account ratio data
        topAccountData: [], // Top account ratio data  
        topPositionData: [], // Top position ratio data
        netPositionData: [], // Net position change data
        takerBuySellData: null, // Taker buy/sell ratio
        priceData: [], // Bitcoin price data from CryptoQuant

        // Cache to prevent rate limiting
        dataCache: new Map(),
        priceCache: new Map(),

        // Current metrics for Long/Short Ratios
        currentGlobalRatio: 1.35, // Default fallback from API test
        currentTopAccountRatio: 1.48, // Default fallback from API test
        currentTopPositionRatio: 1.05, // Default fallback from API test


        // Net Position Flow metrics
        currentNetLongChange: 20.44, // Default fallback from API test
        currentNetShortChange: -5.71, // Default fallback from API test

        // Essential variables
        loading: false,
        selectedTimeRange: '1d',
        
        // Time range functions
        setTimeRange: function(range) {
            this.selectedTimeRange = range;
            this.globalPeriod = range;
            this.loadAllData();
        },

        // Ensure getPriceTrendClass exists
        getPriceTrendClass: function(value) {
            if (value > 0) return 'text-success';
            if (value < 0) return 'text-danger';
            return 'text-secondary';
        },

        // Price metrics (from CryptoQuant)
        currentPrice: 111700, // Default fallback price

        // Taker range selector
        selectedTakerRange: '1h',

        // Market sentiment analysis
        marketSentiment: 'Bullish Bias',
        sentimentStrength: 'Moderate',
        sentimentDescription: 'Ratio 1.35: Bias bullish - lebih banyak long daripada short',
        crowdingLevel: 'Long Bias',

        // Chart state
        chartType: 'line',
        mainChart: null,
        comparisonChart: null,
        exchangeChart: null,
        netPositionChart: null,

        // Get YTD days
        getYTDDays() {
            const now = new Date();
            const startOfYear = new Date(now.getFullYear(), 0, 1);
            const diffTime = Math.abs(now - startOfYear);
            return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        },

        // Initialize
        init() {
            console.log('🚀 Long-Short Ratio Hybrid Dashboard initialized');
            console.log('📊 Controller properties:', {
                chartType: this.chartType,
                scaleType: this.scaleType,
                selectedExchange: this.selectedExchange,
                selectedSymbol: this.selectedSymbol,
                selectedInterval: this.selectedInterval
            });

            // Initialize time ranges
            this.timeRanges = [
                { label: '1D', value: '1d', days: 1 },
                { label: '7D', value: '7d', days: 7 },
                { label: '1M', value: '1m', days: 30 },
                { label: 'YTD', value: 'ytd', days: this.getYTDDays() },
                { label: '1Y', value: '1y', days: 365 }
            ];

            // Update market sentiment based on default values
            this.updateMarketSentiment();

            // Wait for Chart.js to be ready
            if (typeof window.chartJsReady !== 'undefined') {
                window.chartJsReady.then(() => {
                    this.loadAllData();
                });
            } else {
                // Fallback: load data after a short delay
                setTimeout(() => this.loadAllData(), 500);
            }

            // Auto refresh every 5 minutes (Coinglass updates every 5 minutes)
            setInterval(() => this.loadAllData(), 5 * 60 * 1000);
        },

        // Update exchange
        updateExchange() {
            console.log('🔄 Updating exchange to:', this.selectedExchange);
            this.loadAllData();
        },

        // Update symbol
        updateSymbol() {
            console.log('🔄 Updating symbol to:', this.selectedSymbol);
            this.loadAllData();
        },

        // Update interval
        updateInterval() {
            console.log('🔄 Updating interval to:', this.selectedInterval);
            this.loadAllData();
        },

        // Set chart interval
        setChartInterval(interval) {
            if (this.selectedInterval === interval) return;
            console.log('🔄 Setting chart interval to:', interval);
            this.selectedInterval = interval;
            this.loadAllData();
        },

        // Toggle scale type (linear/logarithmic)
        async toggleScale(type) {
            if (this.scaleType === type) return;
            console.log('🔄 Toggling scale to:', type);
            this.scaleType = type;
            try {
                await this.renderMainChart(); // Re-render with new scale
                await this.renderComparisonChart();
                await this.renderNetPositionChart();
            } catch (error) {
                console.error('❌ Error re-rendering charts:', error);
            }
        },

        // Toggle chart type (line/bar)
        async toggleChartType(type) {
            if (this.chartType === type) return;
            console.log('🔄 Toggling chart type to:', type);
            this.chartType = type;
            try {
                await this.renderMainChart(); // Re-render with new type
            } catch (error) {
                console.error('❌ Error re-rendering main chart:', error);
            }
        },

        // Reset chart zoom
        resetZoom() {
            if (this.mainChart && this.mainChart.resetZoom) {
                console.log('🔄 Resetting chart zoom');
                this.mainChart.resetZoom();
            }
        },

        // Export chart
        exportChart(format = 'png') {
            if (!this.mainChart) {
                console.warn('⚠️ No chart available for export');
                return;
            }

            try {
                console.log(`📸 Exporting chart as ${format.toUpperCase()}`);
                const timestamp = new Date().toISOString().split('T')[0];
                const filename = `Long_Short_Ratio_Chart_${this.selectedExchange}_${timestamp}`;

                const link = document.createElement('a');
                link.download = `${filename}.png`;
                link.href = this.mainChart.toBase64Image('image/png', 1.0);
                link.click();

                console.log('✅ Chart exported successfully');
            } catch (error) {
                console.error('❌ Error exporting chart:', error);
            }
        },

        // Share chart
        shareChart() {
            if (!this.mainChart) {
                console.warn('⚠️ No chart available for sharing');
                return;
            }

            try {
                const shareData = {
                    title: `Bitcoin Long-Short Ratio - ${this.selectedExchange}`,
                    text: `Current Global Ratio: ${this.formatRatio(this.currentGlobalRatio)} | Sentiment: ${this.marketSentiment}`,
                    url: window.location.href
                };

                if (navigator.share) {
                    navigator.share(shareData).then(() => {
                        console.log('✅ Chart shared successfully');
                    }).catch((error) => {
                        console.log('⚠️ Share cancelled or failed:', error);
                    });
                } else {
                    // Fallback: copy to clipboard
                    navigator.clipboard.writeText(shareData.url).then(() => {
                        console.log('✅ URL copied to clipboard');
                    });
                }
            } catch (error) {
                console.error('❌ Error sharing chart:', error);
            }
        },

        // Refresh all data
        refreshAll() {
            this.globalLoading = true;
            this.loadAllData().finally(() => {
                this.globalLoading = false;
            });
        },

        // Load all data
        async loadAllData() {
            console.log('📊 Loading all Long-Short Ratio data...');
            this.globalLoading = true;

            try {
                // Load data in parallel
                const results = await Promise.allSettled([
                    this.fetchGlobalAccountRatio(),
                    this.fetchTopAccountRatio(),
                    this.fetchTopPositionRatio(),
                    this.fetchNetPositionHistory(),
                    this.fetchTakerBuySellRatio()
                ]);

                // Log results
                results.forEach((result, index) => {
                    const endpoints = ['Global Account', 'Top Account', 'Top Position', 'Net Position', 'Taker Buy/Sell'];
                    if (result.status === 'fulfilled') {
                        console.log(`✅ ${endpoints[index]} data loaded successfully`);
                    } else {
                        console.warn(`⚠️ ${endpoints[index]} failed:`, result.reason);
                    }
                });

                // Update current values (with fallback data if needed)
                this.updateCurrentValues();

                // Render charts after data is loaded
                setTimeout(async () => {
                    try {
                        await this.renderMainChart();
                        await this.renderComparisonChart();
                        await this.renderNetPositionChart();
                        console.log('✅ All charts rendered successfully');
                    } catch (error) {
                        console.error('❌ Error rendering charts:', error);
                    }
                }, 100);

                console.log('✅ All data processing completed');
                console.log('📊 Current values:', {
                    globalRatio: this.currentGlobalRatio,
                    globalRatioChange: this.globalRatioChange,
                    topAccountRatio: this.currentTopAccountRatio,
                    topAccountRatioChange: this.topAccountRatioChange,
                    topPositionRatio: this.currentTopPositionRatio,
                    topPositionRatioChange: this.topPositionRatioChange,
                    netLongChange: this.currentNetLongChange,
                    netShortChange: this.currentNetShortChange,
                    marketSentiment: this.marketSentiment,
                    sentimentStrength: this.sentimentStrength
                });
                
                console.log('📊 Data arrays length:', {
                    globalAccountData: this.globalAccountData.length,
                    topAccountData: this.topAccountData.length,
                    topPositionData: this.topPositionData.length,
                    netPositionData: this.netPositionData.length,
                    takerBuySellData: this.takerBuySellData ? 'Available' : 'Not available'
                });

            } catch (error) {
                console.error('❌ Error loading data:', error);
                this.showError('Failed to load Long-Short Ratio data');
            } finally {
                this.globalLoading = false;
            }
        },

        // Fetch Global Account Ratio data
        async fetchGlobalAccountRatio() {
            try {
                const params = new URLSearchParams({
                    exchange: this.selectedExchange,
                    symbol: this.selectedSymbol,
                    interval: this.selectedInterval,
                    limit: this.getDataLimit()
                });

                const response = await fetch(`/api/coinglass/global-account-ratio?${params}`);
                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const data = await response.json();
                if (data.success && data.data) {
                    this.globalAccountData = data.data;
                    console.log('✅ Global Account Ratio data loaded:', this.globalAccountData.length, 'records');
                    console.log('📊 Sample Global Account data:', this.globalAccountData[0]);
                } else {
                    throw new Error(data.message || 'Invalid response format');
                }
            } catch (error) {
                console.error('❌ Error fetching Global Account Ratio:', error);
                this.globalAccountData = [];
            }
        },

        // Fetch Top Account Ratio data
        async fetchTopAccountRatio() {
            try {
                const params = new URLSearchParams({
                    exchange: this.selectedExchange,
                    symbol: this.selectedSymbol,
                    interval: this.selectedInterval,
                    limit: this.getDataLimit()
                });

                const response = await fetch(`/api/coinglass/top-account-ratio?${params}`);
                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const data = await response.json();
                if (data.success && data.data) {
                    this.topAccountData = data.data;
                    console.log('✅ Top Account Ratio data loaded:', this.topAccountData.length, 'records');
                    console.log('📊 Sample Top Account data:', this.topAccountData[0]);
                } else {
                    throw new Error(data.message || 'Invalid response format');
                }
            } catch (error) {
                console.error('❌ Error fetching Top Account Ratio:', error);
                this.topAccountData = [];
            }
        },

        // Fetch Top Position Ratio data
        async fetchTopPositionRatio() {
            try {
                const params = new URLSearchParams({
                    exchange: this.selectedExchange,
                    symbol: this.selectedSymbol,
                    interval: this.selectedInterval,
                    limit: this.getDataLimit()
                });

                const response = await fetch(`/api/coinglass/top-position-ratio?${params}`);
                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const data = await response.json();
                if (data.success && data.data) {
                    this.topPositionData = data.data;
                    console.log('✅ Top Position Ratio data loaded:', this.topPositionData.length, 'records');
                    console.log('📊 Sample Top Position data:', this.topPositionData[0]);
                } else {
                    throw new Error(data.message || 'Invalid response format');
                }
            } catch (error) {
                console.error('❌ Error fetching Top Position Ratio:', error);
                this.topPositionData = [];
            }
        },

        // Fetch Taker Buy/Sell Ratio data
        async fetchTakerBuySellRatio() {
            try {
                const params = new URLSearchParams({
                    symbol: this.selectedSymbol.replace('USDT', ''), // API expects 'BTC' not 'BTCUSDT'
                    range: this.selectedTakerRange || '1h' // Default to 1h if not set
                });

                const response = await fetch(`/api/coinglass/taker-buy-sell?${params}`);
                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const data = await response.json();
                if (data.success && data.data) {
                    this.takerBuySellData = data.data;
                    console.log('✅ Taker Buy/Sell Ratio data loaded:', this.takerBuySellData);
                } else {
                    throw new Error(data.message || 'Invalid response format');
                }
            } catch (error) {
                console.error('❌ Error fetching Taker Buy/Sell Ratio:', error);
                this.takerBuySellData = null;
            }
        },

        // Fetch Net Position History data
        async fetchNetPositionHistory() {
            try {
                const params = new URLSearchParams({
                    exchange: this.selectedExchange,
                    symbol: this.selectedSymbol,
                    interval: this.selectedInterval,
                    limit: this.getDataLimit()
                });

                const response = await fetch(`/api/coinglass/net-position?${params}`);
                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const data = await response.json();
                if (data.success && data.data) {
                    this.netPositionData = data.data;
                    console.log('✅ Net Position History data loaded:', this.netPositionData.length, 'records');
                    console.log('📊 Sample Net Position data:', this.netPositionData[0]);
                } else {
                    throw new Error(data.message || 'Invalid response format');
                }
            } catch (error) {
                console.error('❌ Error fetching Net Position History:', error);
                this.netPositionData = [];
            }
        },

        // Get data limit based on time range
        getDataLimit() {
            const limitMap = {
                '1d': 48,   // 48 hours of hourly data (untuk memastikan ada cukup data untuk 24h change)
                '7d': 200,  // 7+ days of hourly data
                '1m': 800,  // 30+ days of hourly data
                'ytd': this.getYTDDays() * 24,
                '1y': 8760  // 365 days of hourly data
            };
            return limitMap[this.globalPeriod] || 200;
        },

        // Update current values from latest data
        updateCurrentValues() {
            console.log('🔄 updateCurrentValues() called');
            
            // Global Account Ratio
            if (this.globalAccountData.length > 0) {
                const latest = this.globalAccountData[this.globalAccountData.length - 1];
                this.currentGlobalRatio = parseFloat(latest.global_account_long_short_ratio || 0);
                
                console.log('📊 Global Account processing:', {
                    latest: latest,
                    ratio: this.currentGlobalRatio,
                    fieldValue: latest.global_account_long_short_ratio,
                    dataLength: this.globalAccountData.length
                });
                
                // Calculate 24h change (use data from 24 hours ago if available)
                if (this.globalAccountData.length > 24) {
                    // Use data from 24 hours ago (24 data points back for hourly data)
                    const previous = this.globalAccountData[this.globalAccountData.length - 25];
                    const prevRatio = parseFloat(previous.global_account_long_short_ratio || 0);
                    this.globalRatioChange = prevRatio > 0 ? ((this.currentGlobalRatio - prevRatio) / prevRatio) * 100 : 0;
                    
                    console.log('📊 Global 24h change calculation (24h+ data):', {
                        current: this.currentGlobalRatio,
                        previous: prevRatio,
                        change: this.globalRatioChange,
                        dataPoints: this.globalAccountData.length
                    });
                } else if (this.globalAccountData.length > 1) {
                    // Fallback: use oldest available data
                    const previous = this.globalAccountData[0];
                    const prevRatio = parseFloat(previous.global_account_long_short_ratio || 0);
                    this.globalRatioChange = prevRatio > 0 ? ((this.currentGlobalRatio - prevRatio) / prevRatio) * 100 : 0;
                    
                    console.log('📊 Global 24h change calculation (fallback):', {
                        current: this.currentGlobalRatio,
                        previous: prevRatio,
                        change: this.globalRatioChange,
                        dataPoints: this.globalAccountData.length
                    });
                } else {
                    console.warn('⚠️ Not enough data for Global Ratio change calculation');
                }

                // Update market sentiment
                this.updateMarketSentiment();
            } else {
                console.warn('⚠️ No Global Account data available');
            }

            // Top Account Ratio
            if (this.topAccountData.length > 0) {
                const latest = this.topAccountData[this.topAccountData.length - 1];
                this.currentTopAccountRatio = parseFloat(latest.top_account_long_short_ratio || 0);
                
                console.log('📊 Top Account processing:', {
                    latest: latest,
                    ratio: this.currentTopAccountRatio,
                    fieldValue: latest.top_account_long_short_ratio
                });
                
                // Calculate 24h change for top account
                if (this.topAccountData.length > 24) {
                    const previous = this.topAccountData[this.topAccountData.length - 25];
                    const prevRatio = parseFloat(previous.top_account_long_short_ratio || 0);
                    this.topAccountRatioChange = prevRatio > 0 ? ((this.currentTopAccountRatio - prevRatio) / prevRatio) * 100 : 0;
                    
                    console.log('📊 Top Account 24h change:', {
                        current: this.currentTopAccountRatio,
                        previous: prevRatio,
                        change: this.topAccountRatioChange
                    });
                } else if (this.topAccountData.length > 1) {
                    const previous = this.topAccountData[0];
                    const prevRatio = parseFloat(previous.top_account_long_short_ratio || 0);
                    this.topAccountRatioChange = prevRatio > 0 ? ((this.currentTopAccountRatio - prevRatio) / prevRatio) * 100 : 0;
                    
                    console.log('📊 Top Account 24h change (fallback):', {
                        current: this.currentTopAccountRatio,
                        previous: prevRatio,
                        change: this.topAccountRatioChange
                    });
                }
            } else {
                console.warn('⚠️ No Top Account data available');
            }

            // Top Position Ratio
            if (this.topPositionData.length > 0) {
                const latest = this.topPositionData[this.topPositionData.length - 1];
                this.currentTopPositionRatio = parseFloat(latest.top_position_long_short_ratio || 0);
                
                console.log('📊 Top Position processing:', {
                    latest: latest,
                    ratio: this.currentTopPositionRatio,
                    fieldValue: latest.top_position_long_short_ratio
                });
                
                // Calculate 24h change for top position
                if (this.topPositionData.length > 24) {
                    const previous = this.topPositionData[this.topPositionData.length - 25];
                    const prevRatio = parseFloat(previous.top_position_long_short_ratio || 0);
                    this.topPositionRatioChange = prevRatio > 0 ? ((this.currentTopPositionRatio - prevRatio) / prevRatio) * 100 : 0;
                    
                    console.log('📊 Top Position 24h change:', {
                        current: this.currentTopPositionRatio,
                        previous: prevRatio,
                        change: this.topPositionRatioChange
                    });
                } else if (this.topPositionData.length > 1) {
                    const previous = this.topPositionData[0];
                    const prevRatio = parseFloat(previous.top_position_long_short_ratio || 0);
                    this.topPositionRatioChange = prevRatio > 0 ? ((this.currentTopPositionRatio - prevRatio) / prevRatio) * 100 : 0;
                    
                    console.log('📊 Top Position 24h change (fallback):', {
                        current: this.currentTopPositionRatio,
                        previous: prevRatio,
                        change: this.topPositionRatioChange
                    });
                }
            } else {
                console.warn('⚠️ No Top Position data available');
            }

            // Net Position Data
            if (this.netPositionData && this.netPositionData.length > 0) {
                const latest = this.netPositionData[this.netPositionData.length - 1];
                this.currentNetLongChange = parseFloat(latest.net_long_change || latest.longNetChange || latest.long_net_change || latest.netLong || 0);
                this.currentNetShortChange = parseFloat(latest.net_short_change || latest.shortNetChange || latest.short_net_change || latest.netShort || 0);
                
                console.log('📊 Net Position processing:', {
                    latest: latest,
                    longChange: this.currentNetLongChange,
                    shortChange: this.currentNetShortChange,
                    longField: latest.net_long_change,
                    shortField: latest.net_short_change
                });
            } else {
                console.warn('⚠️ No Net Position data available');
            }

            // Ensure loading is always defined
            this.loading = false;
        },

        // Update market sentiment based on current ratio
        updateMarketSentiment() {
            const ratio = this.currentGlobalRatio;
            
            console.log('🎯 updateMarketSentiment called with ratio:', ratio);
            
            if (ratio > 2.0) {
                this.marketSentiment = 'Long Crowded';
                this.sentimentStrength = 'Strong';
                this.sentimentDescription = `Ratio > 2.0: Long posisi sangat ramai - potensi koreksi atau short squeeze`;
                this.crowdingLevel = 'Extreme Long';
            } else if (ratio > 1.2) {
                // Ratio between 1.2 and 2.0 = Bullish Bias
                this.marketSentiment = 'Bullish Bias';
                this.sentimentStrength = 'Moderate';
                this.sentimentDescription = `Ratio ${ratio.toFixed(2)}: Bias bullish - lebih banyak long daripada short`;
                this.crowdingLevel = 'Long Bias';
            } else if (ratio >= 0.8 && ratio <= 1.2) {
                this.marketSentiment = 'Balanced';
                this.sentimentStrength = 'Normal';
                this.sentimentDescription = `Ratio 0.8-1.2: Pasar seimbang - kelanjutan trend yang sehat`;
                this.crowdingLevel = 'Balanced';
            } else if (ratio < 0.5) {
                this.marketSentiment = 'Short Crowded';
                this.sentimentStrength = 'Strong';
                this.sentimentDescription = `Ratio < 0.5: Short posisi sangat ramai - potensi rally atau long squeeze`;
                this.crowdingLevel = 'Extreme Short';
            } else {
                // Ratio between 0.5 and 0.8 = Bearish Bias
                this.marketSentiment = 'Bearish Bias';
                this.sentimentStrength = 'Moderate';
                this.sentimentDescription = `Ratio ${ratio.toFixed(2)}: Bias bearish - lebih banyak short daripada long`;
                this.crowdingLevel = 'Short Bias';
            }
            
            console.log('📊 Market sentiment updated:', {
                ratio: ratio,
                sentiment: this.marketSentiment,
                strength: this.sentimentStrength,
                description: this.sentimentDescription,
                crowding: this.crowdingLevel
            });
        },

        // Set time range
        setTimeRange(range) {
            if (this.globalPeriod === range) return;

            console.log('🔄 Setting time range to:', range);
            this.globalPeriod = range;
            this.loadAllData();
        },

        // Set chart interval
        setChartInterval(interval) {
            if (this.selectedInterval === interval) return;

            console.log('🔄 Setting chart interval to:', interval);
            this.selectedInterval = interval;
            this.loadAllData();
        },

        // Toggle scale type (linear/logarithmic)
        toggleScale(type) {
            if (this.scaleType === type) return;

            console.log('🔄 Toggling scale to:', type);
            this.scaleType = type;
            this.renderMainChart(); // Re-render with new scale
        },

        // Toggle chart type (line/bar)
        toggleChartType(type) {
            if (this.chartType === type) return;

            console.log('🔄 Toggling chart type to:', type);
            this.chartType = type;
            this.renderMainChart(); // Re-render with new type
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
                const filename = `Long_Short_Ratio_Chart_${this.selectedExchange}_${timestamp}`;

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
                    title: `Bitcoin Long-Short Ratio - ${this.selectedExchange}`,
                    text: `Current Global Ratio: ${this.formatRatio(this.currentGlobalRatio)} | Sentiment: ${this.marketSentiment}`,
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

        // Load all data from Coinglass and CryptoQuant
        async loadAllData() {
            try {
                this.globalLoading = true;
                console.log('📡 Fetching Long-Short Ratio data from Coinglass...');

                // Calculate time range for API calls
                const { startTime, endTime } = this.getTimeRange();
                console.log(`📅 Time range: ${new Date(startTime).toISOString()} to ${new Date(endTime).toISOString()}`);

                // Fetch all Coinglass data in parallel
                const [
                    globalAccountResult,
                    topAccountResult,
                    topPositionResult,
                    netPositionResult,
                    takerBuySellResult,
                    priceResult
                ] = await Promise.allSettled([
                    this.fetchGlobalAccountRatio(startTime, endTime),
                    this.fetchTopAccountRatio(startTime, endTime),
                    this.fetchTopPositionRatio(startTime, endTime),
                    this.fetchNetPositionData(startTime, endTime),
                    this.fetchTakerBuySellRatio(),
                    this.loadPriceData() // CryptoQuant price data
                ]);

                // Process results
                this.globalAccountData = globalAccountResult.status === 'fulfilled' ? globalAccountResult.value : [];
                this.topAccountData = topAccountResult.status === 'fulfilled' ? topAccountResult.value : [];
                this.topPositionData = topPositionResult.status === 'fulfilled' ? topPositionResult.value : [];
                this.netPositionData = netPositionResult.status === 'fulfilled' ? netPositionResult.value : [];
                this.takerBuySellData = takerBuySellResult.status === 'fulfilled' ? takerBuySellResult.value : null;

                // Log any failed requests
                [globalAccountResult, topAccountResult, topPositionResult, netPositionResult, takerBuySellResult, priceResult].forEach((result, index) => {
                    if (result.status === 'rejected') {
                        console.warn(`❌ API request ${index + 1} failed:`, result.reason);
                    }
                });

                // If no data, fall back to dummy data for testing
                if (this.globalAccountData.length === 0) {
                    console.log('⚠️ No real data available, using dummy data for testing');
                    this.loadDummyData();
                }

                // Calculate metrics
                this.calculateMetrics();

                // Render charts
                setTimeout(() => {
                    this.renderMainChart();
                    this.renderComparisonChart();
                    this.renderNetPositionChart();
                    this.renderExchangeChart();
                }, 100);

                console.log('✅ Long-Short Ratio data loaded successfully');

            } catch (error) {
                console.error('❌ Error loading Long-Short Ratio data:', error);
                this.showError(error.message);
            } finally {
                this.globalLoading = false;
            }
        },

        // Load dummy data for testing
        loadDummyData() {
            // Generate dummy global account ratio data
            const now = Date.now();
            this.globalAccountData = Array.from({ length: 24 }, (_, i) => ({
                time: now - (23 - i) * 60 * 60 * 1000, // Last 24 hours
                global_account_long_percent: 55 + Math.random() * 10,
                global_account_short_percent: 45 - Math.random() * 10,
                global_account_long_short_ratio: 1.2 + Math.random() * 0.6
            }));

            // Generate dummy price data
            this.priceData = Array.from({ length: 24 }, (_, i) => ({
                date: new Date(now - (23 - i) * 60 * 60 * 1000).toISOString().split('T')[0],
                price: 95000 + Math.random() * 5000
            }));

            // Calculate current price
            if (this.priceData.length > 0) {
                this.currentPrice = this.priceData[this.priceData.length - 1].price;
                const previousPrice = this.priceData[this.priceData.length - 2]?.price || this.currentPrice;
                this.priceChange = ((this.currentPrice - previousPrice) / previousPrice) * 100;
            }
        },

        // Fetch Global Account Ratio from Coinglass
        async fetchGlobalAccountRatio(startTime, endTime) {
            const cacheKey = `global-account-${this.selectedExchange}-${this.selectedSymbol}-${this.selectedInterval}-${startTime}-${endTime}`;
            
            if (this.dataCache.has(cacheKey)) {
                console.log('📦 Using cached Global Account Ratio data');
                return this.dataCache.get(cacheKey);
            }

            const url = `/api/coinglass/global-account-ratio?exchange=${this.selectedExchange}&symbol=${this.selectedSymbol}&interval=${this.selectedInterval}&start_time=${startTime}&end_time=${endTime}&limit=1000`;
            
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            if (!data.success || !Array.isArray(data.data)) {
                throw new Error('Invalid Global Account Ratio data format');
            }

            // Cache the result for 5 minutes
            this.dataCache.set(cacheKey, data.data);
            setTimeout(() => this.dataCache.delete(cacheKey), 5 * 60 * 1000);

            return data.data;
        },

        // Fetch Top Account Ratio from Coinglass
        async fetchTopAccountRatio(startTime, endTime) {
            const cacheKey = `top-account-${this.selectedExchange}-${this.selectedSymbol}-${this.selectedInterval}-${startTime}-${endTime}`;
            
            if (this.dataCache.has(cacheKey)) {
                console.log('📦 Using cached Top Account Ratio data');
                return this.dataCache.get(cacheKey);
            }

            const url = `/api/coinglass/top-account-ratio?exchange=${this.selectedExchange}&symbol=${this.selectedSymbol}&interval=${this.selectedInterval}&start_time=${startTime}&end_time=${endTime}&limit=1000`;
            
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            if (!data.success || !Array.isArray(data.data)) {
                throw new Error('Invalid Top Account Ratio data format');
            }

            this.dataCache.set(cacheKey, data.data);
            setTimeout(() => this.dataCache.delete(cacheKey), 5 * 60 * 1000);

            return data.data;
        },

        // Fetch Top Position Ratio from Coinglass
        async fetchTopPositionRatio(startTime, endTime) {
            const cacheKey = `top-position-${this.selectedExchange}-${this.selectedSymbol}-${this.selectedInterval}-${startTime}-${endTime}`;
            
            if (this.dataCache.has(cacheKey)) {
                console.log('📦 Using cached Top Position Ratio data');
                return this.dataCache.get(cacheKey);
            }

            const url = `/api/coinglass/top-position-ratio?exchange=${this.selectedExchange}&symbol=${this.selectedSymbol}&interval=${this.selectedInterval}&start_time=${startTime}&end_time=${endTime}&limit=1000`;
            
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            if (!data.success || !Array.isArray(data.data)) {
                throw new Error('Invalid Top Position Ratio data format');
            }

            this.dataCache.set(cacheKey, data.data);
            setTimeout(() => this.dataCache.delete(cacheKey), 5 * 60 * 1000);

            return data.data;
        },

        // Fetch Net Position Data from Coinglass
        async fetchNetPositionData(startTime, endTime) {
            const cacheKey = `net-position-${this.selectedExchange}-${this.selectedSymbol}-${this.selectedInterval}-${startTime}-${endTime}`;
            
            if (this.dataCache.has(cacheKey)) {
                console.log('📦 Using cached Net Position data');
                return this.dataCache.get(cacheKey);
            }

            const url = `/api/coinglass/net-position?exchange=${this.selectedExchange}&symbol=${this.selectedSymbol}&interval=${this.selectedInterval}&start_time=${startTime}&end_time=${endTime}&limit=1000`;
            
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            if (!data.success || !Array.isArray(data.data)) {
                throw new Error('Invalid Net Position data format');
            }

            this.dataCache.set(cacheKey, data.data);
            setTimeout(() => this.dataCache.delete(cacheKey), 5 * 60 * 1000);

            return data.data;
        },

        // Fetch Taker Buy/Sell Ratio from Coinglass
        async fetchTakerBuySellRatio() {
            const symbol = this.selectedSymbol.replace('USDT', ''); // Convert BTCUSDT to BTC
            const range = this.selectedTakerRange || '1h';
            
            const cacheKey = `taker-buysell-${symbol}-${range}`;
            
            if (this.dataCache.has(cacheKey)) {
                console.log('📦 Using cached Taker Buy/Sell data');
                return this.dataCache.get(cacheKey);
            }

            const url = `/api/coinglass/taker-buy-sell?symbol=${symbol}&range=${range}`;
            
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            if (!data.success || !data.data) {
                throw new Error('Invalid Taker Buy/Sell data format');
            }

            // Update the component data directly
            this.takerBuySellData = data.data;

            this.dataCache.set(cacheKey, data.data);
            setTimeout(() => this.dataCache.delete(cacheKey), 5 * 60 * 1000);

            return data.data;
        },

        // Load Bitcoin price data from CryptoQuant API (keep existing implementation)
        async loadPriceData() {
            const { startDate, endDate } = this.getDateRange();
            
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
            }
        },

        // Get Bitcoin price from CryptoQuant API (keep existing implementation)
        async tryMultiplePriceSources(startDate, endDate) {
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
                            price: parseFloat(item.close || item.value)
                        }));

                        // Calculate current price and change
                        const latest = this.priceData[this.priceData.length - 1];
                        const previous = this.priceData[this.priceData.length - 2];

                        this.currentPrice = latest.price;
                        this.priceChange = previous ? ((latest.price - previous.price) / previous.price) * 100 : 0;

                        console.log(`✅ Loaded ${this.priceData.length} REAL Bitcoin price points from CryptoQuant`);
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

        // Get date range for CryptoQuant API (YYYY-MM-DD format)
        getDateRange() {
            const endDate = new Date();
            const startDate = new Date();

            // Handle different period types
            if (this.globalPeriod === 'ytd') {
                startDate.setMonth(0, 1);
            } else if (this.globalPeriod === 'all') {
                startDate.setDate(endDate.getDate() - 365);
            } else {
                const selectedRange = this.timeRanges.find(r => r.value === this.globalPeriod);
                let days = selectedRange ? selectedRange.days : 1;
                startDate.setDate(endDate.getDate() - days);
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

        // Get time range for Coinglass API (milliseconds)
        getTimeRange() {
            const endTime = Date.now();
            const startTime = new Date();

            // Handle different period types
            if (this.globalPeriod === 'ytd') {
                // Year to date
                startTime.setMonth(0, 1); // January 1st of current year
            } else if (this.globalPeriod === 'all') {
                // All available data (1 year max for API stability)
                startTime.setDate(startTime.getDate() - 365);
            } else {
                // Find the selected time range
                const selectedRange = this.timeRanges.find(r => r.value === this.globalPeriod);
                let days = selectedRange ? selectedRange.days : 1;

                // Set start date to X days ago
                startTime.setDate(startTime.getDate() - days);
            }

            return {
                startTime: startTime.getTime(),
                endTime: endTime
            };
        },

        // Calculate all metrics for Long-Short Ratios
        calculateMetrics() {
            console.log('📊 Calculating Long-Short Ratio metrics...');

            // Global Account Ratio metrics already calculated in updateCurrentValues()
            // Just log the current values
            console.log('📊 Current Global Ratio from calculateMetrics:', this.currentGlobalRatio);

            // Calculate market sentiment based on ratios
            this.updateMarketSentiment();

            console.log('📊 Long-Short Ratio metrics calculated:', {
                globalRatio: this.currentGlobalRatio,
                sentiment: this.marketSentiment
            });
        },



        // Render main chart for Long-Short Ratios
        renderMainChart() {
            const canvas = document.getElementById('longShortRatioMainChart');
            if (!canvas) {
                console.warn('⚠️ Canvas element not found: longShortRatioMainChart');
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
            if (!this.globalAccountData || this.globalAccountData.length === 0) {
                console.warn('⚠️ No Global Account data available for chart rendering');
                return;
            }

            // Prepare data
            const sorted = [...this.globalAccountData].sort((a, b) => a.time - b.time);
            const labels = sorted.map(d => new Date(d.time).toLocaleDateString());
            const ratioValues = sorted.map(d => parseFloat(d.global_account_long_short_ratio));

            // Create simple line chart
            try {
                this.mainChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Global Long/Short Ratio',
                            data: ratioValues,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: false,
                                title: {
                                    display: true,
                                    text: 'Long/Short Ratio'
                                }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('❌ Error creating Long-Short Ratio chart:', error);
                this.mainChart = null;
            }
        },

        // Render comparison chart for all three ratios
        async renderComparisonChart() {
            const canvas = document.getElementById('longShortRatioComparisonChart');
            if (!canvas) {
                console.warn('⚠️ Canvas element not found: longShortRatioComparisonChart');
                return;
            }

            // Destroy existing chart safely BEFORE getting context
            if (this.comparisonChart) {
                try {
                    this.comparisonChart.destroy();
                    console.log('🗑️ Previous Comparison chart destroyed');
                } catch (error) {
                    console.warn('⚠️ Error destroying comparison chart:', error);
                }
                this.comparisonChart = null;
            }

            // Wait a bit for cleanup to complete
            await new Promise(resolve => setTimeout(resolve, 50));

            const ctx = canvas.getContext('2d');
            if (!ctx) {
                console.warn('⚠️ Cannot get 2D context from canvas');
                return;
            }

            // Check if we have data for at least one ratio
            if (this.globalAccountData.length === 0 && this.topAccountData.length === 0 && this.topPositionData.length === 0) {
                console.warn('⚠️ No ratio data available for comparison chart');
                return;
            }

            // Prepare data - use global account data as base for timestamps
            const baseData = this.globalAccountData.length > 0 ? this.globalAccountData : 
                            this.topAccountData.length > 0 ? this.topAccountData : this.topPositionData;
            
            const sorted = [...baseData].sort((a, b) => a.time - b.time);
            const labels = sorted.map(d => new Date(d.time).toLocaleDateString());

            // Build datasets
            const datasets = [];

            // Global Account Ratio
            if (this.globalAccountData.length > 0) {
                const globalSorted = [...this.globalAccountData].sort((a, b) => a.time - b.time);
                const globalValues = globalSorted.map(d => parseFloat(d.global_account_long_short_ratio));
                
                datasets.push({
                    label: 'Global Account Ratio',
                    data: globalValues,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4,
                    pointRadius: 2,
                    pointHoverRadius: 5
                });
            }

            // Top Account Ratio
            if (this.topAccountData.length > 0) {
                const topAccountSorted = [...this.topAccountData].sort((a, b) => a.time - b.time);
                const topAccountValues = topAccountSorted.map(d => parseFloat(d.top_account_long_short_ratio));
                
                datasets.push({
                    label: 'Top Account Ratio',
                    data: topAccountValues,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4,
                    pointRadius: 2,
                    pointHoverRadius: 5
                });
            }

            // Top Position Ratio
            if (this.topPositionData.length > 0) {
                const topPositionSorted = [...this.topPositionData].sort((a, b) => a.time - b.time);
                const topPositionValues = topPositionSorted.map(d => parseFloat(d.top_position_long_short_ratio));
                
                datasets.push({
                    label: 'Top Position Ratio',
                    data: topPositionValues,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4,
                    pointRadius: 2,
                    pointHoverRadius: 5
                });
            }

            // Create comparison chart
            try {
                // Validate canvas context before creating chart
                if (!ctx || ctx.canvas.width === 0 || ctx.canvas.height === 0) {
                    console.warn('⚠️ Invalid canvas context for comparison chart');
                    return;
                }

                this.comparisonChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    color: '#64748b',
                                    font: { size: 11, weight: '500' },
                                    boxWidth: 12,
                                    boxHeight: 12,
                                    padding: 15,
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
                                        return `  ${datasetLabel}: ${this.formatRatio(value)}`;
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
                                        const totalLabels = this.chart.data.labels.length;
                                        const showEvery = Math.max(1, Math.ceil(totalLabels / 8));
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
                                beginAtZero: false,
                                title: {
                                    display: true,
                                    text: 'Long/Short Ratio',
                                    color: '#64748b',
                                    font: { size: 11, weight: '600' }
                                },
                                ticks: {
                                    color: '#94a3b8',
                                    font: { size: 11 },
                                    callback: (value) => this.formatRatio(value)
                                },
                                grid: {
                                    color: 'rgba(148, 163, 184, 0.08)',
                                    drawBorder: false
                                }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('❌ Error creating comparison chart:', error);
                this.comparisonChart = null;
            }
        },

        // Render exchange chart (placeholder)
        renderExchangeChart() {
            console.log('📊 Exchange chart rendering - placeholder');
        },

        // Utility: Format Long-Short Ratio value
        formatRatio(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            return num.toFixed(2);
        },

        // Utility: Format change percentage
        formatChange(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const sign = value >= 0 ? '+' : '';
            return `${sign}${value.toFixed(2)}%`;
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

        // Utility: Get trend class for ratios
        getRatioTrendClass(value) {
            if (value > 0) return 'text-success';
            if (value < 0) return 'text-danger';
            return 'text-secondary';
        },

        // Utility: Get sentiment badge class
        getSentimentBadgeClass() {
            const strengthMap = {
                'Strong': 'text-bg-danger',
                'Moderate': 'text-bg-warning',
                'Normal': 'text-bg-secondary'
            };
            return strengthMap[this.sentimentStrength] || 'text-bg-secondary';
        },

        // Utility: Get sentiment color class
        getSentimentColorClass() {
            const colorMap = {
                'Long Crowded': 'text-danger',
                'Bullish Bias': 'text-success',
                'Balanced': 'text-secondary',
                'Bearish Bias': 'text-warning',
                'Short Crowded': 'text-info'
            };
            return colorMap[this.marketSentiment] || 'text-secondary';
        },

        // Taker range selector
        selectedTakerRange: '1h',

        // Update taker range
        updateTakerRange() {
            console.log('🔄 Updating taker range to:', this.selectedTakerRange);
            this.fetchTakerBuySellRatio();
        },

        // Refresh taker data
        refreshTakerData() {
            this.fetchTakerBuySellRatio();
        },

        // Get sorted exchanges by buy ratio (descending)
        getSortedExchanges() {
            if (!this.takerBuySellData?.exchange_list) return [];
            return [...this.takerBuySellData.exchange_list]
                .sort((a, b) => (b.buy_ratio || 0) - (a.buy_ratio || 0));
        },

        // Get most bullish exchanges (top 5 by buy ratio)
        getMostBullishExchanges() {
            return this.getSortedExchanges()
                .filter(ex => ex.buy_ratio > 50)
                .slice(0, 5);
        },

        // Get most bearish exchanges (top 5 by sell ratio)
        getMostBearishExchanges() {
            if (!this.takerBuySellData?.exchange_list) return [];
            return [...this.takerBuySellData.exchange_list]
                .filter(ex => ex.sell_ratio > 50)
                .sort((a, b) => (b.sell_ratio || 0) - (a.sell_ratio || 0))
                .slice(0, 5);
        },

        // Format volume
        formatVolume(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            if (num >= 1e9) return '$' + (num / 1e9).toFixed(2) + 'B';
            if (num >= 1e6) return '$' + (num / 1e6).toFixed(1) + 'M';
            if (num >= 1e3) return '$' + (num / 1e3).toFixed(0) + 'K';
            return '$' + num.toFixed(0);
        },

        // Format net bias
        formatNetBias(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const sign = value >= 0 ? '+' : '';
            return `${sign}${value.toFixed(1)}%`;
        },

        // Get bias class
        getBiasClass(value) {
            if (value > 10) return 'text-success fw-bold'; // Strong bullish
            if (value > 5) return 'text-success'; // Bullish
            if (value < -10) return 'text-danger fw-bold'; // Strong bearish
            if (value < -5) return 'text-danger'; // Bearish
            return 'text-secondary'; // Neutral
        },

        // Get buy ratio class
        getBuyRatioClass(value) {
            if (value > 60) return 'text-success fw-bold';
            if (value > 55) return 'text-success';
            if (value < 40) return 'text-danger';
            if (value < 45) return 'text-warning';
            return 'text-secondary';
        },

        // Get sell ratio class
        getSellRatioClass(value) {
            if (value > 60) return 'text-danger fw-bold';
            if (value > 55) return 'text-danger';
            if (value < 40) return 'text-success';
            if (value < 45) return 'text-warning';
            return 'text-secondary';
        },

        // Calculate dominance level for visual feedback
        getDominanceLevel(longPct, shortPct) {
            const dominance = Math.abs(longPct - shortPct);
            if (dominance > 30) return 'extreme';
            if (dominance > 15) return 'moderate';
            if (dominance > 5) return 'slight';
            return 'balanced';
        },

        // Get dominance color intensity
        getDominanceIntensity(percentage) {
            // Return opacity value based on dominance (0.4 to 1.0)
            return Math.max(0.4, Math.min(1.0, percentage / 80));
        },

        // Get dominance description
        getDominanceDescription(longPct, shortPct) {
            const dominance = Math.abs(longPct - shortPct);
            const isLongDominant = longPct > shortPct;
            
            if (dominance > 30) {
                return isLongDominant ? 
                    '🟢 STRONG LONG DOMINANCE - Pasar sangat bullish' : 
                    '🔴 STRONG SHORT DOMINANCE - Pasar sangat bearish';
            } else if (dominance > 15) {
                return isLongDominant ? 
                    '🟡 Moderate Long Bias - Cenderung bullish' : 
                    '🟡 Moderate Short Bias - Cenderung bearish';
            } else if (dominance > 5) {
                return isLongDominant ? 
                    '🔵 Slight Long Bias - Sedikit bullish' : 
                    '🔵 Slight Short Bias - Sedikit bearish';
            } else {
                return '⚪ Balanced Market - Pasar seimbang';
            }
        },

        // Get exchange color (for visual consistency)
        getExchangeColor(exchangeName) {
            const colors = {
                'Binance': '#f0b90b',
                'OKX': '#0052ff',
                'Bybit': '#f7a600',
                'BitMEX': '#e43e3b',
                'Bitget': '#00d4aa',
                'KuCoin': '#24ae8f',
                'Gate': '#64b5f6',
                'WhiteBIT': '#ffffff',
                'BingX': '#1890ff',
                'MEXC': '#1db584',
                'Bitunix': '#6c5ce7',
                'Crypto.com': '#003cda',
                'Hyperliquid': '#ff6b6b',
                'dYdX': '#6966ff',
                'Deribit': '#fff',
                'Bitmex': '#e43e3b',
                'Bitfinex': '#16a085',
                'CoinEx': '#3498db',
                'Kraken': '#5741d9',
                'Coinbase': '#0052ff',
                'HTX': '#2ecc71'
            };
            return colors[exchangeName] || '#64748b';
        },

        // Render Net Position Flow Chart
        async renderNetPositionChart() {
            console.log('🎯 renderNetPositionChart() called');
            
            // Check if Chart.js is available
            if (typeof Chart === 'undefined') {
                console.warn('⚠️ Chart.js not available, retrying in 500ms...');
                setTimeout(() => this.renderNetPositionChart(), 500);
                return;
            }
            
            const canvas = document.getElementById('netPositionFlowChart');
            if (!canvas) {
                console.warn('⚠️ Canvas element not found: netPositionFlowChart, retrying in 500ms...');
                setTimeout(() => this.renderNetPositionChart(), 500);
                return;
            }
            
            console.log('✅ Canvas element found:', canvas);

            // Destroy existing chart safely BEFORE getting context
            if (this.netPositionChart) {
                try {
                    this.netPositionChart.destroy();
                    console.log('🗑️ Previous Net Position chart destroyed');
                } catch (error) {
                    console.warn('⚠️ Error destroying net position chart:', error);
                }
                this.netPositionChart = null;
            }

            // Wait a bit for cleanup to complete
            await new Promise(resolve => setTimeout(resolve, 50));

            const ctx = canvas.getContext('2d');
            if (!ctx) {
                console.warn('⚠️ Cannot get 2D context from canvas');
                return;
            }

            // Check if we have data
            if (!this.netPositionData || this.netPositionData.length === 0) {
                console.warn('⚠️ No Net Position data available for chart rendering');
                return;
            }
            
            console.log('📊 Net Position data available:', this.netPositionData.length, 'records');

            // Prepare data - handle different possible field names from Coinglass API
            const sorted = [...this.netPositionData].sort((a, b) => a.time - b.time);
            const labels = sorted.map(d => new Date(d.time).toLocaleDateString());
            
            // Try different field names that might be returned by Coinglass API
            const netLongChanges = sorted.map(d => {
                return parseFloat(d.net_long_change || d.longNetChange || d.long_net_change || d.netLong || 0);
            });
            
            const netShortChanges = sorted.map(d => {
                return parseFloat(d.net_short_change || d.shortNetChange || d.short_net_change || d.netShort || 0);
            });

            // Create datasets for Net Position Flow
            const datasets = [];

            // Net Long Change (Green area)
            datasets.push({
                label: 'Net Long Flow',
                data: netLongChanges,
                type: 'line',
                borderColor: 'rgba(34, 197, 94, 1)',
                backgroundColor: 'rgba(34, 197, 94, 0.2)',
                borderWidth: 2,
                fill: 'origin',
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 6,
                pointBackgroundColor: 'rgba(34, 197, 94, 1)',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2
            });

            // Net Short Change (Red area)
            datasets.push({
                label: 'Net Short Flow',
                data: netShortChanges,
                type: 'line',
                borderColor: 'rgba(239, 68, 68, 1)',
                backgroundColor: 'rgba(239, 68, 68, 0.2)',
                borderWidth: 2,
                fill: 'origin',
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 6,
                pointBackgroundColor: 'rgba(239, 68, 68, 1)',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2
            });

            // Zero line reference
            datasets.push({
                label: 'Zero Line',
                data: Array(netLongChanges.length).fill(0),
                type: 'line',
                borderColor: 'rgba(156, 163, 175, 0.5)',
                backgroundColor: 'transparent',
                borderWidth: 1,
                borderDash: [5, 5],
                fill: false,
                pointRadius: 0
            });

            // Create Net Position Flow chart
            try {
                console.log('📊 Creating Net Position Flow chart with data:', {
                    labels: labels.length,
                    longChanges: netLongChanges.length,
                    shortChanges: netShortChanges.length,
                    sampleLongData: netLongChanges.slice(0, 3),
                    sampleShortData: netShortChanges.slice(0, 3)
                });

                // Validate data and canvas context before creating chart
                if (labels.length === 0 || netLongChanges.length === 0 || netShortChanges.length === 0) {
                    console.warn('⚠️ Invalid data for Net Position chart');
                    return;
                }

                if (!ctx || ctx.canvas.width === 0 || ctx.canvas.height === 0) {
                    console.warn('⚠️ Invalid canvas context for Net Position chart');
                    return;
                }
                
                this.netPositionChart = new Chart(ctx, {
                    type: 'line',
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
                                display: true,
                                position: 'top',
                                labels: {
                                    color: '#64748b',
                                    font: { size: 11, weight: '500' },
                                    boxWidth: 12,
                                    boxHeight: 12,
                                    padding: 15,
                                    usePointStyle: true,
                                    filter: function(item, chart) {
                                        return !item.text.includes('Zero Line');
                                    }
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
                                        
                                        if (datasetLabel.includes('Flow')) {
                                            const sign = value >= 0 ? '+' : '';
                                            return `  ${datasetLabel}: ${sign}${value.toFixed(2)}%`;
                                        }
                                        return `  ${datasetLabel}: ${value}`;
                                    },
                                    afterBody: (tooltipItems) => {
                                        const longItem = tooltipItems.find(item => item.dataset.label.includes('Long'));
                                        const shortItem = tooltipItems.find(item => item.dataset.label.includes('Short'));
                                        
                                        let interpretation = '';
                                        
                                        if (longItem && shortItem) {
                                            const longFlow = longItem.parsed.y;
                                            const shortFlow = shortItem.parsed.y;
                                            
                                            // Flow direction analysis
                                            if (longFlow > 2 && shortFlow < -2) {
                                                interpretation = '\n🟢 STRONG BULLISH FLOW - Money flowing into longs, out of shorts';
                                            } else if (longFlow < -2 && shortFlow > 2) {
                                                interpretation = '\n🔴 STRONG BEARISH FLOW - Money flowing into shorts, out of longs';
                                            } else if (longFlow > 0 && shortFlow < 0) {
                                                interpretation = '\n🟡 Bullish Bias - Net flow favoring longs';
                                            } else if (longFlow < 0 && shortFlow > 0) {
                                                interpretation = '\n🟡 Bearish Bias - Net flow favoring shorts';
                                            } else {
                                                interpretation = '\n⚪ Mixed Signals - Conflicting flows';
                                            }
                                        }
                                        
                                        return interpretation;
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
                                title: {
                                    display: true,
                                    text: 'Net Position Change (%)',
                                    color: '#64748b',
                                    font: { size: 11, weight: '600' }
                                },
                                ticks: {
                                    color: '#64748b',
                                    font: { size: 11 },
                                    callback: (value) => {
                                        const sign = value >= 0 ? '+' : '';
                                        return `${sign}${value.toFixed(1)}%`;
                                    }
                                },
                                grid: {
                                    color: 'rgba(148, 163, 184, 0.08)',
                                    drawBorder: false
                                }
                            }
                        }
                    }
                });
                
                console.log('✅ Net Position Flow chart created successfully:', this.netPositionChart);
            } catch (error) {
                console.error('❌ Error creating Net Position Flow chart:', error);
                this.netPositionChart = null;
            }
        },

        // Render main chart with background bar chart (like CoinGlass example)
        async renderMainChart() {
            const canvas = document.getElementById('longShortRatioMainChart');
            if (!canvas) {
                console.warn('⚠️ Canvas element not found: longShortRatioMainChart');
                return;
            }

            // Destroy existing chart safely BEFORE getting context
            if (this.mainChart) {
                try {
                    this.mainChart.destroy();
                    console.log('🗑️ Previous Main chart destroyed');
                } catch (error) {
                    console.warn('⚠️ Error destroying main chart:', error);
                }
                this.mainChart = null;
            }

            // Wait a bit for cleanup to complete
            await new Promise(resolve => setTimeout(resolve, 50));

            const ctx = canvas.getContext('2d');
            if (!ctx) {
                console.warn('⚠️ Cannot get 2D context from canvas');
                return;
            }

            // Check if we have data
            if (!this.globalAccountData || this.globalAccountData.length === 0) {
                console.warn('⚠️ No Global Account data available for chart rendering');
                return;
            }

            // Prepare data with safety checks
            const sorted = [...this.globalAccountData].sort((a, b) => a.time - b.time);
            const labels = sorted.map(d => new Date(d.time).toLocaleDateString());
            const ratioValues = sorted.map(d => parseFloat(d.global_account_long_short_ratio || 0));
            const longPercent = sorted.map(d => parseFloat(d.global_account_long_percent || 0));
            const shortPercent = sorted.map(d => parseFloat(d.global_account_short_percent || 0));

            // Validate data
            if (ratioValues.length === 0 || longPercent.length === 0 || shortPercent.length === 0) {
                console.warn('⚠️ Invalid data structure for chart rendering');
                return;
            }

            // Create proportional bar chart data - tinggi bar sesuai dominance
            const datasets = [];

            // Calculate dynamic bar heights based on dominance
            const longBarHeights = longPercent.map(longPct => {
                // Bar height proportional to long percentage (0-100 scale)
                return longPct; // Langsung gunakan persentase sebagai tinggi
            });

            const shortBarHeights = shortPercent.map(shortPct => {
                // Bar height proportional to short percentage (0-100 scale)
                return shortPct; // Langsung gunakan persentase sebagai tinggi
            });

            // Long bars - tinggi dan intensitas sesuai dominance (Green)
            datasets.push({
                label: 'Long Dominance',
                data: longBarHeights,
                type: 'bar',
                backgroundColor: longPercent.map((longPct, index) => {
                    const shortPct = shortPercent[index];
                    const dominance = Math.abs(longPct - shortPct);
                    
                    // Intensitas warna berdasarkan dominance
                    let intensity;
                    if (longPct > shortPct) {
                        // Long dominan - warna lebih terang
                        intensity = Math.max(0.6, Math.min(1.0, longPct / 80));
                    } else {
                        // Short dominan - long bar lebih transparan
                        intensity = Math.max(0.2, Math.min(0.5, longPct / 100));
                    }
                    
                    return `rgba(34, 197, 94, ${intensity})`;
                }),
                borderColor: longPercent.map((longPct, index) => {
                    const shortPct = shortPercent[index];
                    return longPct > shortPct ? 'rgba(34, 197, 94, 1)' : 'rgba(34, 197, 94, 0.5)';
                }),
                borderWidth: longPercent.map((longPct, index) => {
                    const shortPct = shortPercent[index];
                    return longPct > shortPct ? 2 : 1; // Border lebih tebal jika dominan
                }),
                yAxisID: 'y1',
                order: 3,
                barPercentage: 0.7,
                categoryPercentage: 0.9
            });

            // Short bars - tinggi dan intensitas sesuai dominance (Red)  
            datasets.push({
                label: 'Short Dominance',
                data: shortBarHeights,
                type: 'bar',
                backgroundColor: shortPercent.map((shortPct, index) => {
                    const longPct = longPercent[index];
                    const dominance = Math.abs(longPct - shortPct);
                    
                    // Intensitas warna berdasarkan dominance
                    let intensity;
                    if (shortPct > longPct) {
                        // Short dominan - warna lebih terang
                        intensity = Math.max(0.6, Math.min(1.0, shortPct / 80));
                    } else {
                        // Long dominan - short bar lebih transparan
                        intensity = Math.max(0.2, Math.min(0.5, shortPct / 100));
                    }
                    
                    return `rgba(239, 68, 68, ${intensity})`;
                }),
                borderColor: shortPercent.map((shortPct, index) => {
                    const longPct = longPercent[index];
                    return shortPct > longPct ? 'rgba(239, 68, 68, 1)' : 'rgba(239, 68, 68, 0.5)';
                }),
                borderWidth: shortPercent.map((shortPct, index) => {
                    const longPct = longPercent[index];
                    return shortPct > longPct ? 2 : 1; // Border lebih tebal jika dominan
                }),
                yAxisID: 'y1',
                order: 3,
                barPercentage: 0.7,
                categoryPercentage: 0.9
            });

            // Main line chart for Long/Short Ratio (White line with dots like example)
            datasets.push({
                label: 'Long/Short Ratio',
                data: ratioValues,
                type: 'line',
                borderColor: '#ffffff',
                backgroundColor: 'transparent',
                borderWidth: 2,
                fill: false,
                tension: 0.3,
                pointRadius: 4,
                pointHoverRadius: 7,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#1f2937',
                pointBorderWidth: 2,
                yAxisID: 'y',
                order: 1
            });

            // Add reference line at ratio = 1.0 (balanced)
            datasets.push({
                label: 'Balanced Line (1.0)',
                data: Array(ratioValues.length).fill(1.0),
                type: 'line',
                borderColor: 'rgba(156, 163, 175, 0.8)',
                backgroundColor: 'transparent',
                borderWidth: 1,
                borderDash: [5, 5],
                fill: false,
                pointRadius: 0,
                yAxisID: 'y',
                order: 2
            });

            // Create dual-axis chart with background bars like CoinGlass
            try {
                // Validate canvas context before creating chart
                if (!ctx || ctx.canvas.width === 0 || ctx.canvas.height === 0) {
                    console.warn('⚠️ Invalid canvas context for main chart');
                    return;
                }

                this.mainChart = new Chart(ctx, {
                    type: 'line',
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
                                display: true,
                                position: 'top',
                                labels: {
                                    color: '#64748b',
                                    font: { size: 11, weight: '500' },
                                    boxWidth: 12,
                                    boxHeight: 12,
                                    padding: 15,
                                    usePointStyle: true,
                                    filter: function(item, chart) {
                                        // Hide the balanced line from legend
                                        return !item.text.includes('Balanced Line');
                                    }
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

                                        if (datasetLabel.includes('Dominance')) {
                                            return `  ${datasetLabel}: ${value.toFixed(1)}%`;
                                        } else if (datasetLabel.includes('Ratio')) {
                                            return `  ${datasetLabel}: ${value.toFixed(2)}`;
                                        }
                                        return `  ${datasetLabel}: ${value}`;
                                    },
                                    afterBody: (tooltipItems) => {
                                        // Add dominance interpretation
                                        const longItem = tooltipItems.find(item => item.dataset.label.includes('Long Dominance'));
                                        const shortItem = tooltipItems.find(item => item.dataset.label.includes('Short Dominance'));
                                        const ratioItem = tooltipItems.find(item => item.dataset.label.includes('Ratio'));
                                        
                                        let interpretation = '';
                                        
                                        // Dominance analysis
                                        if (longItem && shortItem) {
                                            const longPct = longItem.parsed.y;
                                            const shortPct = shortItem.parsed.y;
                                            const dominance = Math.abs(longPct - shortPct);
                                            
                                            if (dominance > 30) {
                                                interpretation += longPct > shortPct ? 
                                                    '\n🟢 STRONG LONG DOMINANCE' : 
                                                    '\n🔴 STRONG SHORT DOMINANCE';
                                            } else if (dominance > 15) {
                                                interpretation += longPct > shortPct ? 
                                                    '\n🟡 Moderate Long Bias' : 
                                                    '\n🟡 Moderate Short Bias';
                                            } else {
                                                interpretation += '\n⚪ Balanced Market';
                                            }
                                        }
                                        
                                        // Ratio interpretation
                                        if (ratioItem) {
                                            const ratio = ratioItem.parsed.y;
                                            if (ratio > 2.0) interpretation += '\n⚠️ Extreme Long Crowding';
                                            else if (ratio < 0.5) interpretation += '\n⚠️ Extreme Short Crowding';
                                        }
                                        
                                        return interpretation;
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
                                    text: 'Long/Short Ratio',
                                    color: '#ffffff',
                                    font: { size: 11, weight: '600' }
                                },
                                ticks: {
                                    color: '#ffffff',
                                    font: { size: 11 },
                                    callback: (value) => value.toFixed(2)
                                },
                                grid: {
                                    color: 'rgba(148, 163, 184, 0.08)',
                                    drawBorder: false
                                }
                            },
                            y1: {
                                type: 'linear',
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Long/Short %',
                                    color: '#64748b',
                                    font: { size: 11, weight: '600' }
                                },
                                min: 0,
                                max: 100,
                                ticks: {
                                    color: '#64748b',
                                    font: { size: 11 },
                                    callback: (value) => value + '%'
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
                console.error('❌ Error creating Long-Short Ratio chart:', error);
                this.mainChart = null;
            }
        },

        // Format ratio values
        formatRatio(value) {
            if (value === null || value === undefined || isNaN(value)) return '--';
            return parseFloat(value).toFixed(2);
        },

        // Format change percentage
        formatChange(value) {
            if (value === null || value === undefined || isNaN(value)) return '--';
            const sign = value >= 0 ? '+' : '';
            return `${sign}${parseFloat(value).toFixed(2)}%`;
        },

        // Format price in USD
        formatPriceUSD(value) {
            if (value === null || value === undefined || isNaN(value)) return '--';
            const num = parseFloat(value);
            if (num >= 1000000) {
                return '$' + (num / 1000000).toFixed(2) + 'M';
            } else if (num >= 1000) {
                return '$' + (num / 1000).toFixed(1) + 'K';
            } else {
                return '$' + num.toLocaleString('en-US', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                });
            }
        },

        // Get price trend class
        getPriceTrendClass(value) {
            if (value > 0) return 'text-success';
            if (value < 0) return 'text-danger';
            return 'text-secondary';
        },



        // Additional utility functions that might be called from template
        refreshAll() {
            this.globalLoading = true;
            this.loadAllData().finally(() => {
                this.globalLoading = false;
            });
        },

        updateExchange() {
            console.log('🔄 Updating exchange to:', this.selectedExchange);
            this.loadAllData();
        },

        updateSymbol() {
            console.log('🔄 Updating symbol to:', this.selectedSymbol);
            this.loadAllData();
        },

        // Utility: Show error
        showError(message) {
            console.error('Error:', message);
            // Could add toast notification here
        }
    };
}



console.log('✅ Long-Short Ratio Hybrid Controller loaded');

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    // Make sure Alpine.js can access the controller
    if (typeof window.Alpine !== 'undefined') {
        console.log('🔗 Registering Long-Short Ratio Hybrid controller with Alpine.js');
    }
});

// Make controller available globally for Alpine.js
window.longShortRatioHybridController = longShortRatioHybridController;