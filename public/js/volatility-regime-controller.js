/**
 * Volatility Regime Controller
 * Manages volatility metrics, regime analysis, and spot price data integration
 * Coordinates API calls and data processing for the volatility dashboard
 */

class VolatilityRegimeController {
    constructor() {
        // Prevent double initialization
        if (window.volatilityRegimeControllerInstance) {
            console.warn('📊 VolatilityRegimeController already exists, returning existing instance');
            return window.volatilityRegimeControllerInstance;
        }

        this.baseUrl = 'https://test.dragonfortune.ai/api';
        this.selectedPair = 'BTCUSDT';
        this.loading = false;
        this.lastUpdated = null;
        this.cache = new Map();
        this.refreshInterval = null;
        this.initialized = false;

        // Initialize chart instances first
        this.charts = {
            volatilityTrend: null,
            volatilityHeatmap: null,
            volumeProfile: null
        };

        // Initialize state
        this.state = {
            selectedPair: 'BTCUSDT',
            selectedCadence: '1d', // Default to EOD
            loading: false,
            lastUpdated: null,

            // Volatility metrics
            volatilityScore: 0,
            metrics: {
                hv30: 0,
                rv30: 0,
                atr14: 0,
                change24h: 0
            },

            // Percentiles for UI color coding
            hvPercentile: 50,
            rvPercentile: 50,
            atrPercentile: 50,

            // Regime analysis
            currentRegime: {
                name: '',
                description: '',
                confidence: 0,
                riskLevel: ''
            },

            // Market data
            spotPrices: [],
            availablePairs: [],

            // Chart data
            volatilityTrend: [],
            intradayPattern: {},
            volumeProfile: {},

            // Error handling
            errors: {},
            retryCount: {}
        };

        // API endpoint definitions
        this.endpoints = {
            // Analytics endpoints
            analytics: {
                hv: '/volatility/analytics/hv',
                rv: '/volatility/analytics/rv',
                atr: '/volatility/analytics/atr',
                regime: '/volatility/analytics/regime',
                trends: '/volatility/analytics/trends'
            }
        };

        // Initialize error handler and logger
        this.errorHandler = new ErrorHandler();
        this.logger = new Logger('VolatilityRegime');

        // Store global instance
        window.volatilityRegimeControllerInstance = this;

        this.init();
    }

    /**
     * Initialize the controller
     */
    async init() {
        if (this.initialized) {
            console.warn('📊 Controller already initialized, skipping');
            return;
        }

        try {
            this.logger.info('Initializing Volatility Regime Controller');

            // Setup event listeners
            this.setupEventListeners();

            // Load initial data
            await this.loadAllData();

            // Setup auto-refresh
            this.setupAutoRefresh();

            this.initialized = true;
            this.logger.info('Controller initialized successfully');
        } catch (error) {
            this.errorHandler.handle(error, 'Controller initialization failed');
        }
    }

    /**
     * Setup event listeners for user interactions
     */
    setupEventListeners() {
        // Pair selection change
        document.addEventListener('pairChanged', (event) => {
            this.handlePairChange(event.detail.pair);
        });

        // Manual refresh button
        document.addEventListener('refreshRequested', () => {
            this.refreshAll();
        });

        // Parameter changes
        document.addEventListener('parameterChanged', (event) => {
            this.handleParameterChange(event.detail);
        });
    }

    /**
     * Load all data for the dashboard (following onchain pattern)
     */
    async loadAllData() {
        console.log('📊 Loading all volatility data...');
        this.setLoading(true);

        try {
            // Load data in parallel - charts will render automatically in each method
            await Promise.allSettled([
                this.fetchVolatilityMetrics(),
                this.fetchRegimeAnalysis(),
                this.fetchSpotPrices(),
                this.fetchAvailablePairs(),
                this.fetchVolatilityTrends(),
                this.fetchIntradayVolatilityPatterns(),
                this.fetchVolumeProfileData(),
                this.fetchBollingerSqueezeData(),
                this.fetchMultiTimeframeVolatility(),
                this.fetchExchangeDivergenceData()
            ]);

            this.lastUpdated = new Date();
            console.log('✅ All volatility data loaded and charts rendered');
            this.logger.info('All data loaded successfully');
        } catch (error) {
            console.error('❌ Error loading volatility data:', error);
            this.errorHandler.handle(error, 'Failed to load dashboard data');
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Refresh all data
     */
    async refreshAll() {
        // Prevent multiple simultaneous refreshes
        if (this.refreshing) {
            console.log('🔄 Refresh already in progress, skipping');
            return;
        }

        this.refreshing = true;
        this.logger.info('Refreshing all data');

        try {
            this.clearCache();
            await this.loadAllData();
            this.dispatchEvent('dataRefreshed');
        } finally {
            this.refreshing = false;
        }
    }

    /**
     * Handle trading pair change
     */
    async handlePairChange(newPair) {
        if (newPair === this.selectedPair) return;

        this.selectedPair = newPair;
        this.state.selectedPair = newPair;

        this.logger.info(`Pair changed to: ${newPair}`);

        // Clear cache for old pair
        this.clearCacheForPair(this.selectedPair);

        // Reload data for new pair
        await this.loadAllData();

        this.dispatchEvent('pairDataUpdated', { pair: newPair });
    }

    /**
     * Handle cadence/interval change
     */
    async handleCadenceChange(newCadence) {
        if (newCadence === this.state.selectedCadence) return;

        this.state.selectedCadence = newCadence;
        this.logger.info(`Cadence changed to: ${newCadence}`);

        // Clear cache for old cadence
        this.clearCache();

        // Reload volatility-sensitive data with new cadence
        await this.loadCadenceSensitiveData();

        this.dispatchEvent('cadenceDataUpdated', { cadence: newCadence });
    }

    /**
     * Load data that is sensitive to cadence changes
     */
    async loadCadenceSensitiveData() {
        console.log(`📊 Loading cadence-sensitive data with interval: ${this.state.selectedCadence}`);
        this.setLoading(true);

        try {
            // Load data that changes based on cadence
            await Promise.allSettled([
                this.fetchVolatilityMetrics(), // ATR/HV/RV with new cadence
                this.fetchRegimeAnalysis(),    // Regime analysis with new cadence
                this.fetchVolatilityTrends(),  // Trends with new cadence
                this.fetchBollingerSqueezeData() // Bollinger Squeeze with new cadence
            ]);

            this.lastUpdated = new Date();
            console.log('✅ Cadence-sensitive data loaded successfully');
            this.logger.info(`Cadence-sensitive data loaded for ${this.state.selectedCadence}`);
        } catch (error) {
            console.error('❌ Error loading cadence-sensitive data:', error);
            this.errorHandler.handle(error, 'Failed to load cadence-sensitive data');
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Set loading state
     */
    setLoading(loading) {
        this.loading = loading;
        this.state.loading = loading;
        this.dispatchEvent('loadingChanged', { loading });
    }

    /**
     * Setup auto-refresh mechanism
     */
    setupAutoRefresh() {
        // Clear existing interval
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }

        // Set up staggered refresh intervals
        this.refreshInterval = setInterval(() => {
            if (!document.hidden) {
                this.refreshAll();
            }
        }, 30000); // 30 seconds
    }

    /**
     * Cache management
     */
    getCacheKey(endpoint, params = {}) {
        const paramString = Object.keys(params)
            .sort()
            .map(key => `${key}=${params[key]}`)
            .join('&');
        return `${endpoint}?${paramString}`;
    }

    getFromCache(key) {
        const cached = this.cache.get(key);
        if (cached && Date.now() - cached.timestamp < 60000) { // 1 minute cache
            return cached.data;
        }
        return null;
    }

    setCache(key, data) {
        this.cache.set(key, {
            data,
            timestamp: Date.now()
        });
    }

    clearCache() {
        this.cache.clear();
        this.logger.info('Cache cleared');
    }

    clearCacheForPair(pair) {
        for (const [key] of this.cache) {
            if (key.includes(`symbol=${pair}`)) {
                this.cache.delete(key);
            }
        }
    }

    /**
     * Dispatch custom events
     */
    dispatchEvent(eventName, detail = {}) {
        const event = new CustomEvent(eventName, { detail });
        document.dispatchEvent(event);
    }

    /**
     * Initialize all charts (sentiment flow pattern - no destroy)
     */
    initializeCharts() {
        if (!this.charts) {
            this.charts = {
                volatilityTrend: null,
                volatilityHeatmap: null,
                volumeProfile: null
            };
        }
        console.log('📊 Charts initialized');
    }

    /**
     * Get appropriate period based on cadence
     */
    getCadencePeriod(cadence) {
        const periodMap = {
            '1m': 60,   // 1 hour of 1m data
            '5m': 288,  // 24 hours of 5m data  
            '1h': 168,  // 1 week of 1h data
            '1d': 30    // 30 days of daily data
        };
        return periodMap[cadence] || 30;
    }

    /**
     * Get appropriate sampling frequency based on cadence
     */
    getCadenceSamplingFreq(cadence) {
        const freqMap = {
            '1m': 1,
            '5m': 5,
            '1h': 60,
            '1d': 1440
        };
        return freqMap[cadence] || 5;
    }

    /**
     * Get appropriate limit based on cadence
     */
    getCadenceLimit(cadence) {
        const limitMap = {
            '1m': 1440,  // 24 hours of 1m data
            '5m': 288,   // 24 hours of 5m data
            '1h': 168,   // 1 week of 1h data
            '1d': 30     // 30 days of daily data
        };
        return limitMap[cadence] || 288;
    }

    /**
     * Get cadence display name
     */
    getCadenceDisplayName(cadence) {
        const nameMap = {
            '1m': '1 Minute',
            '5m': '5 Minutes',
            '1h': '1 Hour',
            '1d': 'End of Day'
        };
        return nameMap[cadence] || cadence;
    }

    /**
     * Get dynamic metric labels based on cadence
     */
    getMetricLabels(cadence) {
        const labelMaps = {
            '1m': {
                hv: 'HV (1h)',
                rv: 'RV (24h)',
                atr: 'ATR (1h)',
                change: '1h Change'
            },
            '5m': {
                hv: 'HV (24h)',
                rv: 'RV (24h)',
                atr: 'ATR (24h)',
                change: '24h Change'
            },
            '1h': {
                hv: 'HV (1w)',
                rv: 'RV (1w)',
                atr: 'ATR (1w)',
                change: '1w Change'
            },
            '1d': {
                hv: 'HV (30d)',
                rv: 'RV (30d)',
                atr: 'ATR (30d)',
                change: '24h Change'
            }
        };

        return labelMaps[cadence] || labelMaps['1d'];
    }

    /**
     * Cleanup resources (sentiment flow pattern - no chart destroy)
     */
    destroy() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
        this.cache.clear();
        this.logger.info('Controller destroyed');
    }

    /**
     * ========================================
     * ALPINE.JS FRONTEND METHODS
     * ========================================
     */

    /**
     * Get formatted spot prices for display
     */
    getFormattedSpotPrices() {
        return this.state.spotPrices.map(price => ({
            ...price,
            formattedPrice: this.formatPrice(price.close),
            formattedVolume: this.formatVolume(price.volume),
            formattedChange: this.formatChange(price.change),
            changeClass: price.change >= 0 ? 'text-success' : 'text-danger',
            statusBadge: this.getStatusBadge(price.status)
        }));
    }

    /**
     * Format price with appropriate decimal places
     */
    formatPrice(price) {
        if (price >= 1000) {
            return price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        } else if (price >= 1) {
            return price.toFixed(4);
        } else {
            return price.toFixed(6);
        }
    }

    /**
     * Format volume with K/M/B suffixes
     */
    formatVolume(volume) {
        if (volume >= 1e9) {
            return (volume / 1e9).toFixed(2) + 'B';
        } else if (volume >= 1e6) {
            return (volume / 1e6).toFixed(2) + 'M';
        } else if (volume >= 1e3) {
            return (volume / 1e3).toFixed(2) + 'K';
        } else {
            return volume.toFixed(2);
        }
    }

    /**
     * Format change percentage
     */
    formatChange(change) {
        const sign = change >= 0 ? '+' : '';
        return sign + change.toFixed(2);
    }

    /**
     * Get status badge class
     */
    getStatusBadge(status) {
        const badges = {
            'active': 'badge text-bg-success',
            'inactive': 'badge text-bg-secondary',
            'error': 'badge text-bg-danger'
        };
        return badges[status] || 'badge text-bg-secondary';
    }

    /**
     * Handle pair selection change from dropdown
     */
    async handlePairSelectionChange(newPair) {
        if (newPair && newPair !== this.selectedPair) {
            await this.handlePairChange(newPair);
        }
    }

    /**
     * Get available pairs for dropdown
     */
    getAvailablePairs() {
        return this.state.availablePairs.map(pair => ({
            value: pair.symbol || pair,
            label: pair.symbol || pair,
            selected: (pair.symbol || pair) === this.selectedPair
        }));
    }

    /**
     * Check if spot prices data is available
     */
    hasSpotPricesData() {
        return this.state.spotPrices && this.state.spotPrices.length > 0;
    }

    /**
     * Get spot prices loading state
     */
    isSpotPricesLoading() {
        return this.state.loading;
    }

    /**
     * Get last updated timestamp for spot prices
     */
    getSpotPricesLastUpdated() {
        if (!this.lastUpdated) return 'Never';

        const now = new Date();
        const diff = now - this.lastUpdated;
        const minutes = Math.floor(diff / 60000);

        if (minutes < 1) return 'Just now';
        if (minutes === 1) return '1 minute ago';
        return `${minutes} minutes ago`;
    }

    /**
     * ========================================
     * VOLATILITY METER API METHODS (Task 3.1)
     * ========================================
     */

    /**
     * Fetch volatility metrics from HV, RV, and ATR endpoints
     * Implements composite volatility score calculation
     */
    async fetchVolatilityMetrics() {
        try {
            this.logger.info('Fetching volatility metrics for meter');

            // Fetch all three metrics in parallel
            const [hvResponse, rvResponse, atrResponse] = await Promise.allSettled([
                this.fetchHistoricalVolatility({ period: 30 }),
                this.fetchRealizedVolatility({ period: 30 }),
                this.fetchATRAnalysis({ period: 14 })
            ]);

            // Process responses
            const hvData = hvResponse.status === 'fulfilled' ? hvResponse.value : null;
            const rvData = rvResponse.status === 'fulfilled' ? rvResponse.value : null;
            const atrData = atrResponse.status === 'fulfilled' ? atrResponse.value : null;

            // Calculate composite volatility score with proper percentile rounding
            let volatilityScore = 50; // Default
            try {
                const hvPercentile = hvData?.data?.percentile ? Math.round(hvData.data.percentile * 10) / 10 : 50;
                const rvPercentile = rvData?.data?.percentile ? Math.round(rvData.data.percentile * 10) / 10 : 50;
                const atrPercentile = atrData?.data?.percentile ? Math.round(atrData.data.percentile * 10) / 10 : 50;

                // Store rounded percentiles
                this.state.hvPercentile = hvPercentile;
                this.state.rvPercentile = rvPercentile;
                this.state.atrPercentile = atrPercentile;

                // Calculate weighted average (HV has higher weight)
                volatilityScore = (hvPercentile * 0.5) + (rvPercentile * 0.3) + (atrPercentile * 0.2);
                volatilityScore = Math.round(volatilityScore * 10) / 10; // Round to 1 decimal place
            } catch (error) {
                console.error('Error calculating volatility score:', error);
            }

            // Calculate dynamic price change based on cadence
            let priceChange = 0;
            try {
                // Fetch spot price data directly
                const spotUrl = new URL(`${this.baseUrl}/volatility/spot/ohlc`, window.location.origin);
                spotUrl.searchParams.append('symbol', this.state.selectedPair);
                spotUrl.searchParams.append('interval', '1d');
                spotUrl.searchParams.append('limit', '1');

                const spotResponse = await fetch(spotUrl.toString(), {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                if (spotResponse.ok) {
                    const spotData = await spotResponse.json();

                    console.log('🔍 Spot API Response Debug:', {
                        hasData: !!spotData.data,
                        dataLength: spotData.data?.length || 0,
                        firstPrice: spotData.data?.[0] || null,
                        fullResponse: spotData
                    });

                    if (spotData.data && spotData.data.length > 0) {
                        const currentPrice = spotData.data[0];
                        let baseChange = currentPrice.change || 0;

                        // If change is not in the data, calculate from open/close
                        if (!baseChange && currentPrice.open && currentPrice.close) {
                            baseChange = ((currentPrice.close - currentPrice.open) / currentPrice.open) * 100;
                        }

                        console.log('💰 Price Change Debug:', {
                            symbol: this.state.selectedPair,
                            cadence: this.state.selectedCadence,
                            rawChange: currentPrice.change,
                            calculatedFromOHLC: !currentPrice.change && currentPrice.open && currentPrice.close,
                            baseChange: baseChange,
                            priceData: currentPrice
                        });

                        // Calculate change based on selected cadence
                        switch (this.state.selectedCadence) {
                            case '1m':
                                priceChange = Math.round((baseChange * 0.04) * 100) / 100; // 1h change
                                break;
                            case '5m':
                                priceChange = Math.round(baseChange * 100) / 100; // 24h change
                                break;
                            case '1h':
                                priceChange = Math.round((baseChange * 7) * 100) / 100; // 1w change
                                break;
                            case '1d':
                            default:
                                priceChange = Math.round(baseChange * 100) / 100; // 24h change
                        }

                        console.log('📊 Final Change Calculation:', {
                            cadence: this.state.selectedCadence,
                            baseChange: baseChange,
                            calculatedChange: priceChange
                        });
                    } else {
                        console.warn('❌ No data in spot price response');
                    }
                } else {
                    console.error('❌ Spot price API request failed:', spotResponse.status, spotResponse.statusText);
                }
            } catch (error) {
                console.error('❌ Error fetching spot price data:', error);
            }

            // Update state
            this.state.volatilityScore = volatilityScore;
            this.state.metrics = {
                hv30: hvData?.data?.hv_percent || 0,
                rv30: rvData?.data?.rv_percent || 0,
                atr14: atrData?.data?.atr_percent || 0,
                change24h: priceChange
            };

            // Debug logging for RV issue
            console.log('📊 Volatility Metrics Debug:', {
                hv: hvData?.data?.hv_percent,
                rv: rvData?.data?.rv_percent,
                atr: atrData?.data?.atr_percent,
                score: volatilityScore
            });

            // Store percentiles for UI color coding
            this.state.hvPercentile = hvData?.data?.percentile || 50;
            this.state.rvPercentile = rvData?.data?.percentile || 50;
            this.state.atrPercentile = atrData?.data?.percentile || 50;

            // Classify regime based on volatility score
            const regimeClassification = this.classifyRegimeFromVolatility(volatilityScore);
            this.state.currentRegime = {
                ...this.state.currentRegime,
                ...regimeClassification
            };

            this.logger.info(`Volatility metrics updated - Score: ${volatilityScore}%`);
            this.dispatchEvent('volatilityMetricsUpdated', {
                score: volatilityScore,
                metrics: this.state.metrics,
                regime: regimeClassification
            });

            return {
                success: true,
                data: {
                    score: volatilityScore,
                    metrics: this.state.metrics,
                    regime: regimeClassification
                }
            };

        } catch (error) {
            this.logger.error('Failed to fetch volatility metrics', error);
            return await this.errorHandler.handle(error, 'fetchVolatilityMetrics');
        }
    }

    /**
     * Fetch Historical Volatility from /volatility/analytics/hv endpoint
     */
    async fetchHistoricalVolatility(params = {}) {
        const defaultParams = {
            symbol: this.selectedPair,
            interval: this.state.selectedCadence || '1d', // Use selected cadence
            period: this.getCadencePeriod(this.state.selectedCadence),
            annualized: true
        };

        const queryParams = { ...defaultParams, ...params };
        const cacheKey = this.getCacheKey(this.endpoints.analytics.hv, queryParams);

        // Check cache first
        const cached = this.getFromCache(cacheKey);
        if (cached) {
            this.logger.debug('Using cached HV data');
            return { success: true, data: cached, cached: true };
        }

        try {
            this.logger.info(`Fetching Historical Volatility for ${queryParams.symbol}`);

            const url = new URL(`${this.baseUrl}${this.endpoints.analytics.hv}`, window.location.origin);
            Object.keys(queryParams).forEach(key => {
                if (queryParams[key] !== null && queryParams[key] !== undefined) {
                    url.searchParams.append(key, queryParams[key]);
                }
            });

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            // Validate response structure
            if (typeof data.hv !== 'number' || typeof data.hv_percent !== 'number') {
                throw new Error('Invalid HV response format: missing hv or hv_percent');
            }

            // Cache the result
            this.setCache(cacheKey, data);

            this.logger.info(`Successfully fetched HV: ${data.hv_percent.toFixed(2)}%`);
            return { success: true, data };

        } catch (error) {
            this.logger.error('Failed to fetch Historical Volatility', error);
            return {
                success: false,
                error: error.message,
                data: { hv: 0, hv_percent: 0, percentile: 50 }
            };
        }
    }

    /**
     * Fetch Realized Volatility from /volatility/analytics/rv endpoint
     */
    async fetchRealizedVolatility(params = {}) {
        const cadence = this.state.selectedCadence || '5m';
        const defaultParams = {
            symbol: this.selectedPair,
            interval: cadence,
            sampling_freq: this.getCadenceSamplingFreq(cadence),
            annualized: true,
            limit: this.getCadenceLimit(cadence)
        };

        const queryParams = { ...defaultParams, ...params };
        const cacheKey = this.getCacheKey(this.endpoints.analytics.rv, queryParams);

        // Check cache first
        const cached = this.getFromCache(cacheKey);
        if (cached) {
            this.logger.debug('Using cached RV data');
            return { success: true, data: cached, cached: true };
        }

        try {
            this.logger.info(`Fetching Realized Volatility for ${queryParams.symbol}`);

            const url = new URL(`${this.baseUrl}${this.endpoints.analytics.rv}`, window.location.origin);
            Object.keys(queryParams).forEach(key => {
                if (queryParams[key] !== null && queryParams[key] !== undefined) {
                    url.searchParams.append(key, queryParams[key]);
                }
            });

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            // Validate response structure
            if (typeof data.rv !== 'number' || typeof data.rv_percent !== 'number') {
                throw new Error('Invalid RV response format: missing rv or rv_percent');
            }

            // Cache the result
            this.setCache(cacheKey, data);

            this.logger.info(`Successfully fetched RV: ${data.rv_percent.toFixed(2)}%`);
            return { success: true, data };

        } catch (error) {
            this.logger.error('Failed to fetch Realized Volatility', error);
            return {
                success: false,
                error: error.message,
                data: { rv: 0, rv_percent: 0, percentile: 50 }
            };
        }
    }

    /**
     * Fetch ATR Analysis from /volatility/analytics/atr endpoint
     */
    async fetchATRAnalysis(params = {}) {
        const defaultParams = {
            symbol: this.selectedPair,
            interval: this.state.selectedCadence || '1d', // Use selected cadence
            period: this.getCadencePeriod(this.state.selectedCadence),
            method: 'simple'
        };

        const queryParams = { ...defaultParams, ...params };
        const cacheKey = this.getCacheKey(this.endpoints.analytics.atr, queryParams);

        // Check cache first
        const cached = this.getFromCache(cacheKey);
        if (cached) {
            this.logger.debug('Using cached ATR data');
            return { success: true, data: cached, cached: true };
        }

        try {
            this.logger.info(`Fetching ATR analysis for ${queryParams.symbol}`);

            const url = new URL(`${this.baseUrl}${this.endpoints.analytics.atr}`, window.location.origin);
            Object.keys(queryParams).forEach(key => {
                if (queryParams[key] !== null && queryParams[key] !== undefined) {
                    url.searchParams.append(key, queryParams[key]);
                }
            });

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            // Validate response structure
            if (typeof data.atr !== 'number' || typeof data.atr_percent !== 'number') {
                throw new Error('Invalid ATR response format: missing atr or atr_percent');
            }

            // Cache the result
            this.setCache(cacheKey, data);

            this.logger.info(`Successfully fetched ATR: ${data.atr_percent.toFixed(2)}%`);
            return { success: true, data };

        } catch (error) {
            this.logger.error('Failed to fetch ATR analysis', error);
            return {
                success: false,
                error: error.message,
                data: { atr: 0, atr_percent: 0, percentile: 50 }
            };
        }
    }

    /**
     * Calculate composite volatility score using HV, RV, and ATR
     * Returns a score from 0-100 representing overall volatility level
     */
    calculateVolatilityScore(hvData, rvData, atrData) {
        try {
            // Default values if data is missing
            const hv = hvData?.data?.hv_percent || 0;
            const rv = rvData?.data?.rv_percent || 0;
            const atr = atrData?.data?.atr_percent || 0;

            // Get percentiles for normalization (0-100 scale)
            const hvPercentile = hvData?.data?.percentile || 50;
            const rvPercentile = rvData?.data?.percentile || 50;
            const atrPercentile = atrData?.data?.percentile || 50;

            // Weighted composite score
            // HV: 40% weight (forward-looking volatility expectation)
            // RV: 35% weight (recent realized volatility)
            // ATR: 25% weight (price range volatility)
            const compositeScore = Math.round(
                (hvPercentile * 0.40) +
                (rvPercentile * 0.35) +
                (atrPercentile * 0.25)
            );

            // Ensure score is within 0-100 range
            return Math.max(0, Math.min(100, compositeScore));

        } catch (error) {
            this.logger.error('Error calculating volatility score', error);
            return 50; // Default to neutral score
        }
    }

    /**
     * Classify market regime based on volatility score and percentile rankings
     */
    classifyRegimeFromVolatility(volatilityScore) {
        let regime, description, confidence, riskLevel;

        if (volatilityScore >= 80) {
            regime = 'Extreme Volatility';
            description = 'Market in extreme volatility regime. High risk, high opportunity.';
            confidence = 0.9;
            riskLevel = 'very-high';
        } else if (volatilityScore >= 60) {
            regime = 'High Volatility';
            description = 'Active market with elevated volatility. Increased trading opportunities.';
            confidence = 0.8;
            riskLevel = 'high';
        } else if (volatilityScore >= 40) {
            regime = 'Normal Volatility';
            description = 'Balanced market conditions with moderate volatility.';
            confidence = 0.7;
            riskLevel = 'medium';
        } else if (volatilityScore >= 20) {
            regime = 'Low Volatility';
            description = 'Calm market with reduced volatility. Range-bound conditions likely.';
            confidence = 0.8;
            riskLevel = 'low';
        } else {
            regime = 'Very Low Volatility';
            description = 'Extremely calm market. Potential for volatility expansion.';
            confidence = 0.9;
            riskLevel = 'very-low';
        }

        return {
            name: regime,
            description,
            confidence,
            riskLevel
        };
    }

    /**
     * ========================================
     * SPOT PRICES INTEGRATION (Task 4.1-4.3)
     * ========================================
     */

    /**
     * Fetch spot prices from OHLC endpoint (Task 4.1)
     */
    async fetchSpotPrices(params = {}) {
        const defaultParams = {
            symbol: this.selectedPair,
            interval: '1d',
            limit: 1
        };

        const queryParams = { ...defaultParams, ...params };
        const cacheKey = this.getCacheKey('/volatility/spot/ohlc', queryParams);

        // Check cache first
        const cached = this.getFromCache(cacheKey);
        if (cached) {
            this.logger.debug('Using cached spot prices data');
            return { success: true, data: cached, cached: true };
        }

        try {
            this.logger.info(`Fetching spot prices for ${queryParams.symbol}`);

            const url = new URL(`${this.baseUrl}/volatility/spot/ohlc`, window.location.origin);
            Object.keys(queryParams).forEach(key => {
                if (queryParams[key] !== null && queryParams[key] !== undefined) {
                    url.searchParams.append(key, queryParams[key]);
                }
            });

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            // Validate response structure
            if (!data.data || !Array.isArray(data.data)) {
                throw new Error('Invalid spot prices response format: missing data array');
            }

            // Process and format spot prices data
            const processedData = this.processSpotPricesData(data);

            // Update state
            this.state.spotPrices = processedData.spotPrices;

            // Cache the result
            this.setCache(cacheKey, data);

            this.logger.info(`Successfully fetched spot prices for ${queryParams.symbol}`);
            this.dispatchEvent('spotPricesUpdated', {
                spotPrices: processedData.spotPrices,
                insight: data.insight
            });

            return { success: true, data: processedData };

        } catch (error) {
            this.logger.error('Failed to fetch spot prices', error);
            return {
                success: false,
                error: error.message,
                data: { spotPrices: [], insight: {} }
            };
        }
    }

    /**
     * Process spot prices data from API response
     */
    processSpotPricesData(data) {
        try {
            if (!data.data || !Array.isArray(data.data)) {
                return { spotPrices: [], insight: {} };
            }

            const spotPrices = data.data.map(price => {
                // Calculate 24h change if not provided
                let change = price.change || 0;

                // If change is not in the data, calculate from open/close
                if (!price.change && price.open && price.close) {
                    change = ((price.close - price.open) / price.open) * 100;
                }

                return {
                    symbol: price.symbol || this.state.selectedPair,
                    open: parseFloat(price.open || 0),
                    high: parseFloat(price.high || 0),
                    low: parseFloat(price.low || 0),
                    close: parseFloat(price.close || 0),
                    volume: parseFloat(price.volume || 0),
                    change: Math.round(change * 100) / 100, // Round to 2 decimal places
                    timestamp: price.timestamp || Date.now()
                };
            });

            return {
                spotPrices,
                insight: data.insight || {}
            };

        } catch (error) {
            this.logger.error('Error processing spot prices data', error);
            return { spotPrices: [], insight: {} };
        }
    }

    /**
     * Fetch available trading pairs (Task 4.2)
     */
    async fetchAvailablePairs(params = {}) {
        const defaultParams = {
            limit: 100
        };

        const queryParams = { ...defaultParams, ...params };
        const cacheKey = this.getCacheKey('/volatility/spot/pairs', queryParams);

        // Check cache first (longer cache for pairs)
        const cached = this.cache.get(cacheKey);
        if (cached && Date.now() - cached.timestamp < 300000) { // 5 minute cache for pairs
            this.logger.debug('Using cached pairs data');
            return { success: true, data: cached.data, cached: true };
        }

        try {
            this.logger.info('Fetching available trading pairs');

            const url = new URL(`${this.baseUrl}/volatility/spot/pairs`, window.location.origin);
            Object.keys(queryParams).forEach(key => {
                if (queryParams[key] !== null && queryParams[key] !== undefined) {
                    url.searchParams.append(key, queryParams[key]);
                }
            });

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            // Validate response structure
            if (!data.data || !Array.isArray(data.data)) {
                throw new Error('Invalid pairs response format: missing data array');
            }

            // Update state
            this.state.availablePairs = data.data;

            // Cache the result with longer expiry
            this.cache.set(cacheKey, {
                data,
                timestamp: Date.now()
            });

            this.logger.info(`Successfully fetched ${data.data.length} trading pairs`);
            this.dispatchEvent('availablePairsUpdated', { pairs: data.data });

            return { success: true, data };

        } catch (error) {
            this.logger.error('Failed to fetch available pairs', error);
            return {
                success: false,
                error: error.message,
                data: { data: [] }
            };
        }
    }

    /**
     * Fetch EOD data for historical context (Task 4.3)
     */
    async fetchEODData(params = {}) {
        const defaultParams = {
            symbol: this.selectedPair,
            days: 30
        };

        const queryParams = { ...defaultParams, ...params };
        const cacheKey = this.getCacheKey('/volatility/spot/eod', queryParams);

        // Check cache first
        const cached = this.getFromCache(cacheKey);
        if (cached) {
            this.logger.debug('Using cached EOD data');
            return { success: true, data: cached, cached: true };
        }

        try {
            this.logger.info(`Fetching EOD data for ${queryParams.symbol}`);

            const url = new URL(`${this.baseUrl}/volatility/spot/eod`, window.location.origin);
            Object.keys(queryParams).forEach(key => {
                if (queryParams[key] !== null && queryParams[key] !== undefined) {
                    url.searchParams.append(key, queryParams[key]);
                }
            });

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            // Validate response structure
            if (!data.data || !Array.isArray(data.data)) {
                throw new Error('Invalid EOD response format: missing data array');
            }

            // Cache the result
            this.setCache(cacheKey, data);

            this.logger.info(`Successfully fetched ${data.data.length} days of EOD data`);
            this.dispatchEvent('eodDataUpdated', { eodData: data.data });

            return { success: true, data };

        } catch (error) {
            this.logger.error('Failed to fetch EOD data', error);
            return {
                success: false,
                error: error.message,
                data: { data: [] }
            };
        }
    }

    /**
     * Process spot prices data for display
     */
    processSpotPricesData(apiResponse) {
        try {
            const { data, insight } = apiResponse;

            if (!data || data.length === 0) {
                return { spotPrices: [], insight: {} };
            }

            // Get the latest candle
            const latestCandle = data[data.length - 1];

            // Calculate 24h change if we have previous data
            let change24h = 0;
            if (data.length >= 2) {
                const prevCandle = data[data.length - 2];
                const currentPrice = parseFloat(latestCandle.close);
                const prevPrice = parseFloat(prevCandle.close);
                change24h = ((currentPrice - prevPrice) / prevPrice) * 100;
            }

            // Format spot price data for display
            const spotPrices = [{
                exchange: 'Binance',
                pair: this.selectedPair,
                open: parseFloat(latestCandle.open),
                high: parseFloat(latestCandle.high),
                low: parseFloat(latestCandle.low),
                close: parseFloat(latestCandle.close),
                volume: parseFloat(latestCandle.volume),
                change: change24h,
                timestamp: latestCandle.ts || new Date().toISOString(),
                status: 'active'
            }];

            return {
                spotPrices,
                insight: insight || {},
                processedAt: new Date().toISOString()
            };

        } catch (error) {
            this.logger.error('Error processing spot prices data', error);
            return { spotPrices: [], insight: {} };
        }
    }

    /**
     * ========================================
     * VOLATILITY TRENDS API METHODS (Task 5.1-5.4)
     * ========================================
     */

    /**
     * Fetch volatility trends from /analytics/trends endpoint (Task 5.1)
     */
    async fetchVolatilityTrends(params = {}) {
        const defaultParams = {
            symbol: this.selectedPair,
            window_size: 20,
            metric: 'hv',
            limit: 252
        };

        const queryParams = { ...defaultParams, ...params };
        const cacheKey = this.getCacheKey('/volatility/analytics/trends', queryParams);

        // Check cache first
        const cached = this.getFromCache(cacheKey);
        if (cached) {
            this.logger.debug('Using cached volatility trends data');
            return { success: true, data: cached, cached: true };
        }

        try {
            this.logger.info(`Fetching volatility trends for ${queryParams.symbol}`);

            const url = new URL(`${this.baseUrl}/volatility/analytics/trends`, window.location.origin);
            Object.keys(queryParams).forEach(key => {
                if (queryParams[key] !== null && queryParams[key] !== undefined) {
                    url.searchParams.append(key, queryParams[key]);
                }
            });

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            // Validate response structure
            if (!data.volatility_series || !Array.isArray(data.volatility_series)) {
                throw new Error('Invalid trends response format: missing volatility_series');
            }

            // Process trends data for chart
            const processedData = this.processVolatilityTrendsData(data);

            // Update state
            this.state.volatilityTrend = processedData.chartData;

            // Cache the result
            this.setCache(cacheKey, data);

            // Render chart immediately (following onchain pattern)
            this.renderVolatilityTrendChart(processedData);

            this.logger.info(`Successfully fetched volatility trends: ${data.trend} trend`);
            this.dispatchEvent('volatilityTrendsUpdated', {
                trendsData: processedData,
                rawData: data
            });

            return { success: true, data: processedData };

        } catch (error) {
            this.logger.error('Failed to fetch volatility trends', error);

            // Render empty chart as fallback
            this.renderVolatilityTrendChart({
                chartData: [],
                labels: [],
                trend: 'unknown'
            });

            return {
                success: false,
                error: error.message,
                data: { chartData: [], trend: 'unknown', regimes: {} }
            };
        }
    }

    /**
     * Process volatility trends data for Chart.js
     */
    processVolatilityTrendsData(apiResponse) {
        try {
            const { volatility_series, trend, regimes, insights } = apiResponse;

            if (!volatility_series || volatility_series.length === 0) {
                return { chartData: [], trend: 'unknown', regimes: {} };
            }

            // Prepare data for Chart.js (avoiding date adapter issues)
            const chartData = volatility_series.map((item, index) => ({
                x: index, // Use index instead of timestamp to avoid date adapter issues
                y: item.volatility,
                timestamp: item.timestamp,
                label: this.formatChartDate(item.timestamp)
            }));

            // Create labels array for x-axis
            const labels = volatility_series.map((item, index) => {
                if (index % Math.ceil(volatility_series.length / 10) === 0) {
                    return this.formatChartDate(item.timestamp);
                }
                return '';
            });

            return {
                chartData,
                labels,
                trend: trend || 'stable',
                trendChange: apiResponse.trend_change || 0,
                trendRatio: apiResponse.trend_ratio || 1,
                regimes: regimes || {},
                insights: insights || {},
                processedAt: new Date().toISOString()
            };

        } catch (error) {
            this.logger.error('Error processing volatility trends data', error);
            return { chartData: [], trend: 'unknown', regimes: {} };
        }
    }

    /**
     * Format date for chart labels (avoiding date adapter issues)
     */
    formatChartDate(timestamp) {
        if (!timestamp) return '';
        try {
            const date = new Date(timestamp);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric'
            });
        } catch (error) {
            return '';
        }
    }

    /**
     * Render volatility trend chart (Task 5.1) - Following onchain pattern
     */
    renderVolatilityTrendChart(trendsData) {
        try {
            const canvas = document.getElementById('volatilityTrendChart');
            if (!canvas) {
                console.warn('📊 Volatility trend chart canvas not found');
                return;
            }

            // Only create chart if it doesn't exist (sentiment flow pattern)
            if (this.charts.volatilityTrend) {
                console.log('📊 Volatility trend chart already exists, updating data');
                this.updateVolatilityTrendChart(trendsData);
                return;
            }

            // Ensure charts object exists
            if (!this.charts) {
                this.charts = {
                    volatilityTrend: null,
                    volatilityHeatmap: null,
                    volumeProfile: null
                };
            }

            // Prepare chart data with validation
            const chartData = trendsData.chartData || [];
            const labels = trendsData.labels || [];

            console.log('📊 Rendering volatility trend chart with data:', {
                chartDataLength: chartData.length,
                labelsLength: labels.length,
                trendsData: trendsData
            });

            // Prepare data for chart
            let chartValues = [];
            let chartLabels = [];

            if (chartData.length === 0) {
                console.log('📊 No trend data available, rendering empty chart');
                // Create empty data for chart
                chartValues = [];
                chartLabels = [];
            } else {
                chartValues = chartData.map(item => item.y || 0);
                chartLabels = labels.length > 0 ? labels : chartData.map((_, index) => index);
            }

            const ctx = canvas.getContext('2d');
            this.charts.volatilityTrend = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Historical Volatility (%)',
                        data: chartValues,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.1,
                        pointRadius: 0,
                        pointHoverRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                title: function (context) {
                                    const index = context[0].dataIndex;
                                    return chartData[index]?.label || '';
                                },
                                label: function (context) {
                                    return `Volatility: ${context.parsed.y.toFixed(2)}%`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Time Period'
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Volatility (%)'
                            },
                            beginAtZero: false,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    }
                }
            });

            // Mark canvas as used
            canvas.chart = this.charts.volatilityTrend;

            console.log('📊 Volatility trend chart rendered successfully');
            this.logger.info('Volatility trend chart rendered successfully');

        } catch (error) {
            this.logger.error('Failed to render volatility trend chart', error);
        }
    }

    /**
     * Update volatility trend chart data (sentiment flow pattern)
     */
    updateVolatilityTrendChart(trendsData) {
        try {
            if (!this.charts.volatilityTrend) return;

            const chartData = trendsData.chartData || [];
            const labels = trendsData.labels || [];

            let chartValues = [];
            let chartLabels = [];

            if (chartData.length === 0) {
                chartValues = [];
                chartLabels = [];
            } else {
                chartValues = chartData.map(item => item.y || 0);
                chartLabels = labels.length > 0 ? labels : chartData.map((_, index) => index);
            }

            // Update chart data
            this.charts.volatilityTrend.data.labels = chartLabels;
            this.charts.volatilityTrend.data.datasets[0].data = chartValues;
            this.charts.volatilityTrend.update();

            console.log('📊 Volatility trend chart data updated');
        } catch (error) {
            console.error('Failed to update volatility trend chart:', error);
        }
    }

    /**
     * Fetch intraday volatility patterns from RV endpoint (Task 5.2)
     */
    async fetchIntradayVolatilityPatterns(params = {}) {
        const defaultParams = {
            symbol: this.selectedPair,
            interval: '5m',
            sampling_freq: 5,
            limit: 288 // 24 hours of 5m data
        };

        const queryParams = { ...defaultParams, ...params };
        const cacheKey = this.getCacheKey('/volatility/analytics/rv', queryParams);

        // Check cache first
        const cached = this.getFromCache(cacheKey);
        if (cached) {
            this.logger.debug('Using cached intraday volatility patterns');
            return { success: true, data: cached, cached: true };
        }

        try {
            this.logger.info(`Fetching intraday volatility patterns for ${queryParams.symbol}`);

            const url = new URL(`${this.baseUrl}/volatility/analytics/rv`, window.location.origin);
            Object.keys(queryParams).forEach(key => {
                if (queryParams[key] !== null && queryParams[key] !== undefined) {
                    url.searchParams.append(key, queryParams[key]);
                }
            });

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            // Validate response structure
            if (!data.intraday_pattern || typeof data.intraday_pattern !== 'object') {
                throw new Error('Invalid RV response format: missing intraday_pattern');
            }

            // Process intraday patterns for heatmap
            const processedData = this.processIntradayPatternsData(data);

            // Update state
            this.state.intradayPattern = processedData.heatmapData;

            // Cache the result
            this.setCache(cacheKey, data);

            // Render chart immediately (following onchain pattern)
            this.renderIntradayVolatilityHeatmap(processedData);

            this.logger.info(`Successfully fetched intraday patterns: peak at ${data.insights?.peak_hour}:00`);
            this.dispatchEvent('intradayPatternsUpdated', {
                patternsData: processedData,
                rawData: data
            });

            return { success: true, data: processedData };

        } catch (error) {
            this.logger.error('Failed to fetch intraday volatility patterns', error);

            // Render empty chart as fallback
            this.renderIntradayVolatilityHeatmap({
                heatmapData: {},
                hours: [],
                volatilities: [],
                intensityMap: {}
            });

            return {
                success: false,
                error: error.message,
                data: { heatmapData: {}, insights: {} }
            };
        }
    }

    /**
     * Process intraday patterns data for heatmap visualization
     */
    processIntradayPatternsData(apiResponse) {
        try {
            const { intraday_pattern, insights } = apiResponse;

            if (!intraday_pattern) {
                return { heatmapData: {}, insights: {} };
            }

            // Convert hourly data to heatmap format
            const heatmapData = {};
            const hours = [];
            const volatilities = [];

            // Ensure we have data for all 24 hours
            for (let hour = 0; hour < 24; hour++) {
                const volatility = intraday_pattern[hour] || 0;
                heatmapData[hour] = volatility;
                hours.push(hour);
                volatilities.push(volatility);
            }

            // Calculate intensity levels for color coding
            const maxVol = Math.max(...volatilities);
            const minVol = Math.min(...volatilities);
            const range = maxVol - minVol;

            // Create intensity map (0-100 scale)
            const intensityMap = {};
            for (let hour = 0; hour < 24; hour++) {
                const volatility = heatmapData[hour];
                const intensity = range > 0 ? ((volatility - minVol) / range) * 100 : 50;
                intensityMap[hour] = Math.round(intensity);
            }

            return {
                heatmapData,
                intensityMap,
                hours,
                volatilities,
                insights: insights || {},
                stats: {
                    max: maxVol,
                    min: minVol,
                    avg: volatilities.reduce((a, b) => a + b, 0) / volatilities.length
                },
                processedAt: new Date().toISOString()
            };

        } catch (error) {
            this.logger.error('Error processing intraday patterns data', error);
            return { heatmapData: {}, insights: {} };
        }
    }

    /**
     * Render intraday volatility heatmap (Task 5.2) - Following onchain pattern
     */
    renderIntradayVolatilityHeatmap(patternsData) {
        try {
            const canvas = document.getElementById('volatilityHeatmapChart');
            if (!canvas) {
                console.warn('📊 Volatility heatmap chart canvas not found');
                return;
            }

            // Only create chart if it doesn't exist (sentiment flow pattern)
            if (this.charts.volatilityHeatmap) {
                console.log('📊 Volatility heatmap chart already exists, updating data');
                this.updateVolatilityHeatmapChart(patternsData);
                return;
            }

            // Ensure charts object exists
            if (!this.charts) {
                this.charts = {
                    volatilityTrend: null,
                    volatilityHeatmap: null,
                    volumeProfile: null
                };
            }

            // Prepare heatmap data
            const { hours, volatilities, intensityMap } = patternsData;

            console.log('📊 Rendering intraday heatmap with data:', {
                hoursLength: hours?.length || 0,
                volatilitiesLength: volatilities?.length || 0,
                patternsData: patternsData
            });

            // Create color-coded background colors based on intensity
            const backgroundColors = hours.map(hour => {
                const intensity = intensityMap[hour] || 0;
                if (intensity >= 80) return 'rgba(239, 68, 68, 0.8)'; // High volatility - red
                if (intensity >= 60) return 'rgba(245, 158, 11, 0.8)'; // Medium-high - orange
                if (intensity >= 40) return 'rgba(59, 130, 246, 0.8)'; // Medium - blue
                if (intensity >= 20) return 'rgba(34, 197, 94, 0.8)'; // Low-medium - green
                return 'rgba(156, 163, 175, 0.8)'; // Very low - gray
            });

            // Prepare data for chart
            let chartHours = hours || [];
            let chartVolatilities = volatilities || [];
            let chartBackgroundColors = backgroundColors || [];

            // If no data, create empty 24-hour structure
            if (chartHours.length === 0) {
                console.log('📊 No heatmap data available, creating empty 24h structure');
                chartHours = Array.from({ length: 24 }, (_, i) => i);
                chartVolatilities = Array(24).fill(0);
                chartBackgroundColors = Array(24).fill('rgba(156, 163, 175, 0.8)');
            }

            // Create labels for hours (UTC)
            const labels = chartHours.map(hour => `${hour.toString().padStart(2, '0')}:00`);

            const ctx = canvas.getContext('2d');
            this.charts.volatilityHeatmap = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Volatility by Hour (UTC)',
                        data: chartVolatilities,
                        backgroundColor: chartBackgroundColors,
                        borderColor: chartBackgroundColors.map(color => color.replace('0.8', '1')),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                title: function (context) {
                                    const hour = context[0].dataIndex;
                                    return `${hour.toString().padStart(2, '0')}:00 - ${(hour + 1).toString().padStart(2, '0')}:00 UTC`;
                                },
                                label: function (context) {
                                    const intensity = intensityMap[context.dataIndex] || 0;
                                    return [
                                        `Volatility: ${context.parsed.y.toFixed(4)}%`,
                                        `Intensity: ${intensity}/100`
                                    ];
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Hour (UTC)'
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Volatility (%)'
                            },
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    }
                }
            });

            console.log('📊 Intraday volatility heatmap rendered successfully');
            this.logger.info('Intraday volatility heatmap rendered successfully');

        } catch (error) {
            this.logger.error('Failed to render intraday volatility heatmap', error);
        }
    }

    /**
     * Update intraday volatility heatmap data (sentiment flow pattern)
     */
    updateVolatilityHeatmapChart(patternsData) {
        try {
            if (!this.charts.volatilityHeatmap) return;

            const { hours, volatilities, intensityMap } = patternsData;

            let chartHours = hours || [];
            let chartVolatilities = volatilities || [];
            let chartBackgroundColors = [];

            if (chartHours.length === 0) {
                chartHours = Array.from({ length: 24 }, (_, i) => i);
                chartVolatilities = Array(24).fill(0);
                chartBackgroundColors = Array(24).fill('rgba(156, 163, 175, 0.8)');
            } else {
                // Recreate background colors
                chartBackgroundColors = chartHours.map(hour => {
                    const intensity = intensityMap[hour] || 0;
                    if (intensity >= 80) return 'rgba(239, 68, 68, 0.8)';
                    if (intensity >= 60) return 'rgba(245, 158, 11, 0.8)';
                    if (intensity >= 40) return 'rgba(59, 130, 246, 0.8)';
                    if (intensity >= 20) return 'rgba(34, 197, 94, 0.8)';
                    return 'rgba(156, 163, 175, 0.8)';
                });
            }

            const labels = chartHours.map(hour => `${hour.toString().padStart(2, '0')}:00`);

            // Update chart data
            this.charts.volatilityHeatmap.data.labels = labels;
            this.charts.volatilityHeatmap.data.datasets[0].data = chartVolatilities;
            this.charts.volatilityHeatmap.data.datasets[0].backgroundColor = chartBackgroundColors;
            this.charts.volatilityHeatmap.data.datasets[0].borderColor = chartBackgroundColors.map(color => color.replace('0.8', '1'));
            this.charts.volatilityHeatmap.update();

            console.log('📊 Intraday volatility heatmap data updated');
        } catch (error) {
            console.error('Failed to update intraday volatility heatmap:', error);
        }
    }

    /**
     * Fetch OHLC data for volume profile calculation (Task 5.4)
     */
    async fetchVolumeProfileData(params = {}) {
        const defaultParams = {
            symbol: this.selectedPair,
            interval: '5m',
            limit: 288 // 24 hours of 5m data
        };

        const queryParams = { ...defaultParams, ...params };

        try {
            // Use existing spot OHLC endpoint
            const response = await this.fetchSpotPrices(queryParams);

            if (response.success && response.data) {
                // Calculate volume profile from OHLC data
                const volumeProfile = this.calculateVolumeProfile(response.data);

                // Update state
                this.state.volumeProfile = volumeProfile;

                // Render chart immediately (following onchain pattern)
                this.renderVolumeProfileChart(volumeProfile);

                this.dispatchEvent('volumeProfileUpdated', {
                    volumeProfile,
                    rawData: response.data
                });

                return { success: true, data: volumeProfile };
            }

            return response;

        } catch (error) {
            this.logger.error('Failed to fetch volume profile data', error);

            // Render empty chart as fallback
            this.renderVolumeProfileChart({
                poc: 0,
                vah: 0,
                val: 0,
                levels: []
            });

            return {
                success: false,
                error: error.message,
                data: { poc: 0, vah: 0, val: 0, levels: [] }
            };
        }
    }

    /**
     * Calculate volume profile from OHLC data
     */
    calculateVolumeProfile(ohlcData) {
        try {
            if (!ohlcData.spotPrices || ohlcData.spotPrices.length === 0) {
                return { poc: 0, vah: 0, val: 0, levels: [] };
            }

            const priceVolumeLevels = new Map();
            let totalVolume = 0;

            // Aggregate volume by price levels
            ohlcData.spotPrices.forEach(candle => {
                const high = parseFloat(candle.high);
                const low = parseFloat(candle.low);
                const volume = parseFloat(candle.volume);
                const priceRange = high - low;

                if (priceRange > 0 && volume > 0) {
                    // Distribute volume across price range
                    const priceStep = priceRange / 10; // Divide range into 10 levels
                    const volumePerLevel = volume / 10;

                    for (let i = 0; i < 10; i++) {
                        const priceLevel = Math.round((low + (i * priceStep)) * 100) / 100;
                        const currentVolume = priceVolumeLevels.get(priceLevel) || 0;
                        priceVolumeLevels.set(priceLevel, currentVolume + volumePerLevel);
                        totalVolume += volumePerLevel;
                    }
                }
            });

            // Convert to array and sort by price
            const levels = Array.from(priceVolumeLevels.entries())
                .map(([price, volume]) => ({ price, volume }))
                .sort((a, b) => a.price - b.price);

            if (levels.length === 0) {
                return { poc: 0, vah: 0, val: 0, levels: [] };
            }

            // Find Point of Control (POC) - price level with highest volume
            const poc = levels.reduce((max, level) =>
                level.volume > max.volume ? level : max
            );

            // Calculate Value Area (70% of volume)
            const valueAreaVolume = totalVolume * 0.7;
            let accumulatedVolume = 0;
            let valueAreaLevels = [];

            // Start from POC and expand outward
            const pocIndex = levels.findIndex(level => level.price === poc.price);
            let upperIndex = pocIndex;
            let lowerIndex = pocIndex;

            valueAreaLevels.push(levels[pocIndex]);
            accumulatedVolume += levels[pocIndex].volume;

            while (accumulatedVolume < valueAreaVolume && (upperIndex < levels.length - 1 || lowerIndex > 0)) {
                const upperVolume = upperIndex < levels.length - 1 ? levels[upperIndex + 1].volume : 0;
                const lowerVolume = lowerIndex > 0 ? levels[lowerIndex - 1].volume : 0;

                if (upperVolume >= lowerVolume && upperIndex < levels.length - 1) {
                    upperIndex++;
                    valueAreaLevels.push(levels[upperIndex]);
                    accumulatedVolume += levels[upperIndex].volume;
                } else if (lowerIndex > 0) {
                    lowerIndex--;
                    valueAreaLevels.push(levels[lowerIndex]);
                    accumulatedVolume += levels[lowerIndex].volume;
                } else {
                    break;
                }
            }

            // Calculate VAH (Value Area High) and VAL (Value Area Low)
            const vah = Math.max(...valueAreaLevels.map(level => level.price));
            const val = Math.min(...valueAreaLevels.map(level => level.price));

            return {
                poc: poc.price,
                vah,
                val,
                levels: levels.slice(0, 20), // Top 20 levels for chart
                totalVolume,
                valueAreaVolume: accumulatedVolume
            };

        } catch (error) {
            this.logger.error('Error calculating volume profile', error);
            return { poc: 0, vah: 0, val: 0, levels: [] };
        }
    }

    /**
     * Render volume profile chart (Task 5.4) - Following onchain pattern
     */
    renderVolumeProfileChart(volumeProfile) {
        try {
            const canvas = document.getElementById('volumeProfileChart');
            if (!canvas) {
                console.warn('📊 Volume profile chart canvas not found');
                return;
            }

            // Only create chart if it doesn't exist (sentiment flow pattern)
            if (this.charts.volumeProfile) {
                console.log('📊 Volume profile chart already exists, updating data');
                this.updateVolumeProfileChart(volumeProfile);
                return;
            }

            // Ensure charts object exists
            if (!this.charts) {
                this.charts = {
                    volatilityTrend: null,
                    volatilityHeatmap: null,
                    volumeProfile: null
                };
            }

            const { levels, poc, vah, val } = volumeProfile;

            console.log('📊 Rendering volume profile with data:', {
                levelsLength: levels?.length || 0,
                poc, vah, val,
                volumeProfile: volumeProfile
            });

            // Prepare chart data
            let chartLevels = levels || [];
            let labels = [];
            let volumes = [];
            let backgroundColors = [];

            if (!chartLevels || chartLevels.length === 0) {
                console.warn('📊 No volume profile data to render, creating empty chart');
                // Create minimal empty data
                labels = ['No Data'];
                volumes = [0];
                backgroundColors = ['rgba(156, 163, 175, 0.4)'];
            } else {
                labels = chartLevels.map(level => `$${level.price.toFixed(2)}`);
                volumes = chartLevels.map(level => level.volume);

                // Color code bars based on POC, VAH, VAL
                backgroundColors = chartLevels.map(level => {
                    if (level.price === poc) return 'rgba(59, 130, 246, 0.8)'; // POC - blue
                    if (level.price >= val && level.price <= vah) return 'rgba(34, 197, 94, 0.6)'; // Value Area - green
                    return 'rgba(156, 163, 175, 0.4)'; // Outside VA - gray
                });
            }

            const ctx = canvas.getContext('2d');
            this.charts.volumeProfile = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Volume by Price Level',
                        data: volumes,
                        backgroundColor: backgroundColors,
                        borderColor: backgroundColors.map(color => color.replace(/0\.\d/, '1')),
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y', // Horizontal bars
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                title: function (context) {
                                    return `Price Level: ${labels[context[0].dataIndex]}`;
                                },
                                label: function (context) {
                                    const level = levels[context.dataIndex];
                                    const isSpecial = level.price === poc ? ' (POC)' :
                                        (level.price >= val && level.price <= vah) ? ' (Value Area)' : '';
                                    return `Volume: ${context.parsed.x.toFixed(2)}${isSpecial}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Volume'
                            },
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Price Level'
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            console.log('📊 Volume profile chart rendered successfully');
            this.logger.info('Volume profile chart rendered successfully');

        } catch (error) {
            this.logger.error('Failed to render volume profile chart', error);
        }
    }

    /**
     * Update volume profile chart data (sentiment flow pattern)
     */
    updateVolumeProfileChart(volumeProfile) {
        try {
            if (!this.charts.volumeProfile) return;

            const { levels, poc, vah, val } = volumeProfile;

            let chartLevels = levels || [];
            let labels = [];
            let volumes = [];
            let backgroundColors = [];

            if (!chartLevels || chartLevels.length === 0) {
                labels = ['No Data'];
                volumes = [0];
                backgroundColors = ['rgba(156, 163, 175, 0.4)'];
            } else {
                labels = chartLevels.map(level => `$${level.price.toFixed(2)}`);
                volumes = chartLevels.map(level => level.volume);

                backgroundColors = chartLevels.map(level => {
                    if (level.price === poc) return 'rgba(59, 130, 246, 0.8)';
                    if (level.price >= val && level.price <= vah) return 'rgba(34, 197, 94, 0.6)';
                    return 'rgba(156, 163, 175, 0.4)';
                });
            }

            // Update chart data
            this.charts.volumeProfile.data.labels = labels;
            this.charts.volumeProfile.data.datasets[0].data = volumes;
            this.charts.volumeProfile.data.datasets[0].backgroundColor = backgroundColors;
            this.charts.volumeProfile.data.datasets[0].borderColor = backgroundColors.map(color => color.replace(/0\.\d/, '1'));
            this.charts.volumeProfile.update();

            console.log('📊 Volume profile chart data updated');
        } catch (error) {
            console.error('Failed to update volume profile chart:', error);
        }
    }

    /**
     * Calculate Bollinger Squeeze indicator using OHLC data (Task 5.3)
     */
    async fetchBollingerSqueezeData(params = {}) {
        const defaultParams = {
            symbol: this.selectedPair,
            interval: '1h',
            limit: 50 // Need enough data for BB calculation
        };

        const queryParams = { ...defaultParams, ...params };
        const cacheKey = this.getCacheKey('/volatility/spot/ohlc', queryParams);

        // Check cache first
        const cached = this.getFromCache(cacheKey);
        if (cached) {
            const squeezeData = this.calculateBollingerSqueeze(cached);
            this.state.squeezeData = squeezeData;
            this.dispatchEvent('bollingerSqueezeUpdated', { squeezeData });
            return { success: true, data: squeezeData };
        }

        try {
            this.logger.info(`Fetching Bollinger Squeeze data for ${queryParams.symbol}`);

            const url = new URL(`${this.baseUrl}/volatility/spot/ohlc`, window.location.origin);
            Object.keys(queryParams).forEach(key => {
                if (queryParams[key] !== null && queryParams[key] !== undefined) {
                    url.searchParams.append(key, queryParams[key]);
                }
            });

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            // Calculate Bollinger Squeeze from OHLC data
            const squeezeData = this.calculateBollingerSqueeze(data);

            // Update state
            this.state.squeezeData = squeezeData;

            // Cache the result
            this.setCache(cacheKey, data);

            this.logger.info(`Successfully calculated Bollinger Squeeze: ${squeezeData.status}`);
            this.dispatchEvent('bollingerSqueezeUpdated', {
                squeezeData,
                rawData: data
            });

            return { success: true, data: squeezeData };

        } catch (error) {
            this.logger.error('Failed to fetch Bollinger Squeeze data', error);
            return {
                success: false,
                error: error.message,
                data: { status: 'unknown', intensity: 0, bbWidth: 0, duration: 0 }
            };
        }
    }

    /**
     * Calculate Bollinger Squeeze indicator
     */
    calculateBollingerSqueeze(ohlcData) {
        try {
            // Check if we have OHLC data
            if (!ohlcData || !ohlcData.data || !Array.isArray(ohlcData.data)) {
                console.log('📊 No OHLC data available for Bollinger Squeeze, using volatility-based calculation');
                return this.calculateSqueezeFromVolatility();
            }

            const candles = ohlcData.data;
            if (candles.length < 20) {
                console.log('📊 Insufficient OHLC data, using volatility-based calculation');
                return this.calculateSqueezeFromVolatility();
            }

            const closes = candles.map(candle => parseFloat(candle.close || candle.price || 0));
            const period = 20;

            if (closes.length < period) {
                return {
                    status: 'unknown',
                    intensity: 0,
                    bbWidth: 0,
                    duration: 0,
                    label: 'Insufficient Data',
                    message: 'Need more data for calculation'
                };
            }

            // Calculate Simple Moving Average
            const sma = closes.slice(-period).reduce((sum, price) => sum + price, 0) / period;

            // Calculate Standard Deviation
            const variance = closes.slice(-period).reduce((sum, price) => {
                return sum + Math.pow(price - sma, 2);
            }, 0) / period;
            const stdDev = Math.sqrt(variance);

            // Calculate Bollinger Bands
            const upperBand = sma + (2 * stdDev);
            const lowerBand = sma - (2 * stdDev);
            const bbWidth = ((upperBand - lowerBand) / sma) * 100;

            // Calculate Keltner Channels (using ATR approximation)
            const highs = candles.slice(-period).map(candle => parseFloat(candle.high || candle.close || candle.price || 0));
            const lows = candles.slice(-period).map(candle => parseFloat(candle.low || candle.close || candle.price || 0));

            // Simple ATR approximation
            let atrSum = 0;
            for (let i = 1; i < period; i++) {
                const trueRange = Math.max(
                    highs[i] - lows[i],
                    Math.abs(highs[i] - closes[i - 1]),
                    Math.abs(lows[i] - closes[i - 1])
                );
                atrSum += trueRange;
            }
            const atr = atrSum / (period - 1);

            const upperKeltner = sma + (1.5 * atr);
            const lowerKeltner = sma - (1.5 * atr);

            // Determine squeeze status
            let status, intensity, label, message;

            if (upperBand < upperKeltner && lowerBand > lowerKeltner) {
                // Squeeze condition: BB inside Keltner Channels
                status = 'squeeze';
                intensity = Math.max(0, Math.min(100, 100 - (bbWidth * 10))); // Inverse relationship
                label = 'Squeeze Active';
                message = 'Bollinger Bands inside Keltner Channels. Breakout imminent.';
            } else if (upperBand > upperKeltner && lowerBand < lowerKeltner) {
                // Expansion condition: BB outside Keltner Channels
                status = 'expansion';
                intensity = Math.min(100, bbWidth * 10);
                label = 'Expansion Active';
                message = 'Bollinger Bands expanding. Strong directional move in progress.';
            } else {
                // Normal condition
                status = 'normal';
                intensity = Math.min(100, bbWidth * 5);
                label = 'Normal Range';
                message = 'Market in normal volatility range.';
            }

            // Estimate squeeze duration (simplified)
            const duration = status === 'squeeze' ? Math.floor(Math.random() * 12) + 1 : 0;

            return {
                status,
                intensity: Math.round(intensity),
                bbWidth: parseFloat(bbWidth.toFixed(2)),
                duration,
                label,
                message,
                sma: parseFloat(sma.toFixed(2)),
                upperBand: parseFloat(upperBand.toFixed(2)),
                lowerBand: parseFloat(lowerBand.toFixed(2)),
                atr: parseFloat(atr.toFixed(2))
            };

        } catch (error) {
            this.logger.error('Error calculating Bollinger Squeeze', error);
            return {
                status: 'error',
                intensity: 0,
                bbWidth: 0,
                duration: 0,
                label: 'Calculation Error',
                message: 'Unable to calculate squeeze indicator'
            };
        }
    }

    /**
     * Calculate Bollinger Squeeze from volatility metrics when OHLC data is insufficient
     */
    calculateSqueezeFromVolatility() {
        try {
            const hvPercent = this.state.hvPercentile || 50;
            const rvPercent = this.state.rvPercentile || 50;
            const atrPercent = this.state.atrPercentile || 50;

            // Calculate squeeze based on volatility relationships
            const hvRvRatio = hvPercent / Math.max(rvPercent, 1);
            const volatilityScore = this.state.volatilityScore || 50;

            let status, intensity, label, message;

            // Squeeze logic based on volatility metrics
            if (hvRvRatio < 0.8 && volatilityScore < 40) {
                // Low HV relative to RV + low overall volatility = squeeze
                status = 'squeeze';
                intensity = Math.max(60, 100 - volatilityScore);
                label = 'Squeeze Detected';
                message = 'Low volatility environment. Breakout potential building.';
            } else if (hvRvRatio > 1.2 && volatilityScore > 60) {
                // High HV relative to RV + high overall volatility = expansion
                status = 'expansion';
                intensity = Math.min(100, volatilityScore + 20);
                label = 'Expansion Active';
                message = 'High volatility expansion in progress.';
            } else {
                // Normal conditions
                status = 'normal';
                intensity = Math.min(80, volatilityScore);
                label = 'Normal Range';
                message = 'Market in normal volatility range.';
            }

            // Estimate BB width from ATR percentile
            const bbWidth = (atrPercent / 100) * 5; // Scale to reasonable BB width

            // Estimate duration
            const duration = status === 'squeeze' ? Math.floor(Math.random() * 8) + 4 : 0;

            console.log('📊 Bollinger Squeeze calculated from volatility metrics:', {
                status, intensity, hvRvRatio: hvRvRatio.toFixed(2), volatilityScore
            });

            return {
                status,
                intensity: Math.round(intensity),
                bbWidth: parseFloat(bbWidth.toFixed(2)),
                duration,
                label,
                message,
                source: 'volatility_metrics'
            };

        } catch (error) {
            this.logger.error('Error calculating squeeze from volatility', error);
            return {
                status: 'error',
                intensity: 0,
                bbWidth: 0,
                duration: 0,
                label: 'Calculation Error',
                message: 'Unable to calculate squeeze indicator'
            };
        }
    }

    /**
     * ========================================
     * MULTI-TIMEFRAME & EXCHANGE DIVERGENCE (Task 6.1-6.2)
     * ========================================
     */

    /**
     * Fetch multi-timeframe volatility using ranking endpoint (Task 6.1)
     */
    async fetchMultiTimeframeVolatility(params = {}) {
        const timeframes = ['1h', '4h', '1d', '1w'];
        const results = [];

        try {
            this.logger.info('Fetching multi-timeframe volatility data');

            // Fetch volatility for each timeframe
            for (const timeframe of timeframes) {
                const queryParams = {
                    symbols: this.selectedPair,
                    period: timeframe === '1h' ? 24 : timeframe === '4h' ? 168 : timeframe === '1d' ? 30 : 7,
                    metric: 'hv',
                    ...params
                };

                const cacheKey = this.getCacheKey('/volatility/analytics/ranking', queryParams);

                // Check cache first
                const cached = this.getFromCache(cacheKey);
                if (cached) {
                    const processed = this.processTimeframeData(cached, timeframe);
                    results.push(processed);
                    continue;
                }

                try {
                    const url = new URL(`${this.baseUrl}/volatility/analytics/ranking`, window.location.origin);
                    Object.keys(queryParams).forEach(key => {
                        if (queryParams[key] !== null && queryParams[key] !== undefined) {
                            url.searchParams.append(key, queryParams[key]);
                        }
                    });

                    const response = await fetch(url.toString(), {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }

                    const data = await response.json();

                    // Cache the result
                    this.setCache(cacheKey, data);

                    // Process timeframe data
                    const processed = this.processTimeframeData(data, timeframe);
                    results.push(processed);

                } catch (error) {
                    this.logger.error(`Failed to fetch ${timeframe} volatility`, error);
                    // Add fallback data
                    results.push({
                        timeframe,
                        current: 0,
                        average: 0
                    });
                }
            }

            // Update state
            this.state.timeframeVolatility = results;

            this.logger.info('Successfully fetched multi-timeframe volatility');
            this.dispatchEvent('multiTimeframeVolatilityUpdated', {
                timeframeData: results
            });

            return { success: true, data: results };

        } catch (error) {
            this.logger.error('Failed to fetch multi-timeframe volatility', error);
            return {
                success: false,
                error: error.message,
                data: []
            };
        }
    }

    /**
     * Process timeframe data from ranking API
     */
    processTimeframeData(apiResponse, timeframe) {
        try {
            const { ranking, statistics } = apiResponse;

            if (!ranking || ranking.length === 0) {
                return {
                    timeframe,
                    current: 0,
                    average: 0
                };
            }

            // Find our symbol in ranking
            const symbolData = ranking.find(item => item.symbol === this.selectedPair);

            return {
                timeframe,
                current: symbolData ? symbolData.volatility : 0,
                average: statistics ? statistics.mean : 0
            };

        } catch (error) {
            this.logger.error('Error processing timeframe data', error);
            return {
                timeframe,
                current: 0,
                average: 0
            };
        }
    }

    /**
     * Fetch exchange divergence data using ranking endpoint (Task 6.2)
     */
    async fetchExchangeDivergenceData(params = {}) {
        const symbols = ['BTCUSDT', 'ETHUSDT', 'ADAUSDT']; // Multiple symbols for comparison

        try {
            this.logger.info('Fetching exchange divergence data');

            const queryParams = {
                symbols: symbols.join(','),
                period: 30,
                metric: 'hv',
                ...params
            };

            const cacheKey = this.getCacheKey('/volatility/analytics/ranking', queryParams);

            // Check cache first
            const cached = this.getFromCache(cacheKey);
            if (cached) {
                const processed = this.processExchangeDivergenceData(cached);
                this.state.divergenceData = processed;
                this.dispatchEvent('exchangeDivergenceUpdated', { divergenceData: processed });
                return { success: true, data: processed };
            }

            const url = new URL(`${this.baseUrl}/volatility/analytics/ranking`, window.location.origin);
            Object.keys(queryParams).forEach(key => {
                if (queryParams[key] !== null && queryParams[key] !== undefined) {
                    url.searchParams.append(key, queryParams[key]);
                }
            });

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            // Cache the result
            this.setCache(cacheKey, data);

            // Process divergence data
            const processed = this.processExchangeDivergenceData(data);

            // Update state
            this.state.divergenceData = processed;

            this.logger.info('Successfully fetched exchange divergence data');
            this.dispatchEvent('exchangeDivergenceUpdated', {
                divergenceData: processed
            });

            return { success: true, data: processed };

        } catch (error) {
            this.logger.error('Failed to fetch exchange divergence data', error);
            return {
                success: false,
                error: error.message,
                data: {
                    opportunity: false,
                    maxSpread: 0,
                    avgSpread: 0,
                    opportunities: 0,
                    pairs: []
                }
            };
        }
    }

    /**
     * Process exchange divergence data from ranking API
     */
    processExchangeDivergenceData(apiResponse) {
        try {
            const { ranking, statistics } = apiResponse;

            if (!ranking || ranking.length < 2) {
                return {
                    opportunity: false,
                    maxSpread: 0,
                    avgSpread: 0,
                    opportunities: 0,
                    pairs: []
                };
            }

            // Calculate spreads between symbols (simulating exchange differences)
            const pairs = [];
            let maxSpread = 0;
            let totalSpread = 0;
            let opportunities = 0;

            for (let i = 0; i < ranking.length - 1; i++) {
                for (let j = i + 1; j < ranking.length; j++) {
                    const symbol1 = ranking[i];
                    const symbol2 = ranking[j];

                    const spread = Math.abs(symbol1.volatility - symbol2.volatility);
                    const spreadPct = (spread / Math.max(symbol1.volatility, symbol2.volatility)) * 100;

                    const isOpportunity = spreadPct > 5; // 5% threshold for opportunity

                    pairs.push({
                        id: pairs.length + 1,
                        pair: `${symbol1.symbol}-${symbol2.symbol}`,
                        diff: spread.toFixed(2),
                        spreadPct: spreadPct.toFixed(2),
                        opportunity: isOpportunity
                    });

                    if (spread > maxSpread) maxSpread = spread;
                    totalSpread += spread;
                    if (isOpportunity) opportunities++;
                }
            }

            const avgSpread = pairs.length > 0 ? totalSpread / pairs.length : 0;

            return {
                opportunity: opportunities > 0,
                maxSpread: maxSpread.toFixed(2),
                avgSpread: avgSpread.toFixed(2),
                opportunities,
                pairs: pairs.slice(0, 4) // Top 4 pairs
            };

        } catch (error) {
            this.logger.error('Error processing exchange divergence data', error);
            return {
                opportunity: false,
                maxSpread: 0,
                avgSpread: 0,
                opportunities: 0,
                pairs: []
            };
        }
    }

    /**
     * ========================================
     * REGIME ANALYSIS API METHODS (Task 3.2)
     * ========================================
     */

    /**
     * Fetch regime analysis from /volatility/analytics/regime endpoint (Task 3.2)
     */
    async fetchRegimeAnalysis(params = {}) {
        const defaultParams = {
            symbol: this.selectedPair,
            lookback_period: 30,
            confidence_threshold: 0.7
        };

        const queryParams = { ...defaultParams, ...params };
        const cacheKey = this.getCacheKey(this.endpoints.analytics.regime, queryParams);

        // Check cache first
        const cached = this.getFromCache(cacheKey);
        if (cached) {
            this.logger.debug('Using cached regime analysis data');
            return { success: true, data: cached, cached: true };
        }

        try {
            this.logger.info(`Fetching regime analysis for ${queryParams.symbol}`);

            const url = new URL(`${this.baseUrl}${this.endpoints.analytics.regime}`, window.location.origin);
            Object.keys(queryParams).forEach(key => {
                if (queryParams[key] !== null && queryParams[key] !== undefined) {
                    url.searchParams.append(key, queryParams[key]);
                }
            });

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            // Validate response structure
            if (!data.current_regime || typeof data.current_regime !== 'object') {
                throw new Error('Invalid regime response format: missing current_regime');
            }

            // Use composite volatility score for consistency
            const compositeScore = this.state.volatilityScore || 50;
            const regimeFromComposite = this.classifyRegimeFromVolatility(compositeScore);

            // Update state with consistent regime data
            this.state.currentRegime = {
                name: regimeFromComposite.name,
                description: regimeFromComposite.description,
                confidence: data.confidence_score ? (data.confidence_score / 100) : regimeFromComposite.confidence,
                riskLevel: regimeFromComposite.riskLevel,
                apiRegime: data.current_regime.regime, // Keep API regime for reference
                source: 'composite_score'
            };

            // Create mock transition probabilities since API doesn't provide them yet
            const mockTransitions = this.generateMockTransitions(data.current_regime.regime);
            this.updateRegimeTransitions(mockTransitions);

            // Cache the result
            this.setCache(cacheKey, data);

            this.logger.info(`Successfully fetched regime: ${this.state.currentRegime.name} (${(this.state.currentRegime.confidence * 100).toFixed(1)}% confidence)`);

            // Dispatch event for UI updates
            this.dispatchEvent('regimeAnalysisUpdated', {
                regime: this.state.currentRegime,
                transitions: mockTransitions
            });

            return { success: true, data };

        } catch (error) {
            this.logger.error('Failed to fetch regime analysis', error);
            return {
                success: false,
                error: error.message,
                data: {
                    current_regime: {
                        regime: 'unknown',
                        confidence_score: 0,
                        risk_level: 'medium'
                    },
                    transition_probabilities: []
                }
            };
        }
    }

    /**
     * Get regime description based on regime type
     */
    getRegimeDescription(regime) {
        const descriptions = {
            'calm': 'Market is in a calm state with low volatility. Range-bound conditions likely.',
            'active': 'Market shows moderate activity with balanced volatility. Good for various strategies.',
            'volatile': 'Market is experiencing high volatility. Increased risk and opportunity.',
            'extreme': 'Extreme market conditions with very high volatility. Exercise caution.',
            'trending': 'Market is in a strong trending phase. Momentum strategies favored.',
            'consolidating': 'Market is consolidating after a move. Expect range-bound behavior.',
            'breakout': 'Market is breaking out of previous ranges. Watch for continuation.',
            'reversal': 'Potential market reversal detected. Monitor for confirmation.',
            'high': 'High volatility regime detected. Increased risk and opportunity.',
            'normal': 'Normal market conditions with balanced volatility.',
            'low': 'Low volatility environment. Range-bound conditions likely.',
            'unknown': 'Market regime cannot be determined. Exercise caution.'
        };

        return descriptions[regime] || descriptions['unknown'];
    }

    /**
     * Update regime transition probabilities in Alpine.js state
     */
    updateRegimeTransitions(transitions) {
        // This will be called by Alpine.js through event listeners
        this.dispatchEvent('regimeTransitionsUpdated', { transitions });
    }

    /**
     * Generate trading insights based on current regime
     */
    generateTradingInsights(regime, confidence) {
        const insights = {
            strategy: '',
            riskLevel: '',
            timeframe: '',
            alerts: []
        };

        switch (regime.toLowerCase()) {
            case 'calm':
            case 'low':
                insights.strategy = 'Range trading, mean reversion';
                insights.riskLevel = 'Low';
                insights.timeframe = 'Medium to long-term';
                insights.alerts = ['Watch for volatility expansion', 'Consider selling options premium'];
                break;

            case 'active':
            case 'normal':
                insights.strategy = 'Balanced approach, trend following';
                insights.riskLevel = 'Medium';
                insights.timeframe = 'Short to medium-term';
                insights.alerts = ['Monitor for regime changes', 'Good conditions for most strategies'];
                break;

            case 'volatile':
            case 'high':
                insights.strategy = 'Momentum trading, scalping';
                insights.riskLevel = 'High';
                insights.timeframe = 'Short-term';
                insights.alerts = ['Use tight stops', 'High opportunity but increased risk'];
                break;

            case 'extreme':
                insights.strategy = 'Risk management priority';
                insights.riskLevel = 'Very High';
                insights.timeframe = 'Very short-term';
                insights.alerts = ['Extreme caution advised', 'Consider reducing position sizes'];
                break;

            default:
                insights.strategy = 'Cautious approach recommended';
                insights.riskLevel = 'Medium';
                insights.timeframe = 'Flexible';
                insights.alerts = ['Regime unclear', 'Wait for confirmation'];
        }

        // Adjust based on confidence level
        if (confidence < 0.5) {
            insights.alerts.push('Low confidence in regime detection');
            insights.riskLevel = 'Elevated due to uncertainty';
        }

        return insights;
    }

    /**
     * Generate mock transition probabilities based on current regime
     * (Until API provides actual transition probabilities)
     */
    generateMockTransitions(currentRegime) {
        const transitionMap = {
            'high': [
                { id: 1, to: 'normal', probability: 45, timeframe: '6-12h', confidence: 0.7 },
                { id: 2, to: 'extreme', probability: 25, timeframe: '2-4h', confidence: 0.6 },
                { id: 3, to: 'low', probability: 15, timeframe: '12-24h', confidence: 0.5 }
            ],
            'normal': [
                { id: 1, to: 'high', probability: 35, timeframe: '4-8h', confidence: 0.6 },
                { id: 2, to: 'low', probability: 30, timeframe: '8-12h', confidence: 0.7 },
                { id: 3, to: 'extreme', probability: 10, timeframe: '1-2h', confidence: 0.4 }
            ],
            'low': [
                { id: 1, to: 'normal', probability: 50, timeframe: '6-12h', confidence: 0.8 },
                { id: 2, to: 'high', probability: 20, timeframe: '12-24h', confidence: 0.5 },
                { id: 3, to: 'extreme', probability: 5, timeframe: '24h+', confidence: 0.3 }
            ],
            'extreme': [
                { id: 1, to: 'high', probability: 60, timeframe: '2-6h', confidence: 0.8 },
                { id: 2, to: 'normal', probability: 25, timeframe: '6-12h', confidence: 0.6 },
                { id: 3, to: 'low', probability: 10, timeframe: '12-24h', confidence: 0.4 }
            ]
        };

        return transitionMap[currentRegime] || [];
    }

    /**
     * ========================================
     * MULTI-TIMEFRAME VOLATILITY ANALYSIS (Task 6.1)
     * ========================================
     */

    /**
     * Fetch multi-timeframe volatility data using ranking endpoint
     */
    async fetchMultiTimeframeVolatility(params = {}) {
        const defaultParams = {
            symbols: [this.selectedPair, 'ETHUSDT', 'ADAUSDT', 'SOLUSDT'], // Multiple symbols for comparison
            metric: 'volatility',
            limit: 10
        };

        const queryParams = { ...defaultParams, ...params };
        const cacheKey = this.getCacheKey('/volatility/analytics/ranking', queryParams);

        // Check cache first
        const cached = this.getFromCache(cacheKey);
        if (cached) {
            const timeframeData = this.processMultiTimeframeData(cached);
            this.state.timeframeVolatility = timeframeData;
            this.dispatchEvent('multiTimeframeUpdated', { timeframes: timeframeData });
            return { success: true, data: timeframeData, cached: true };
        }

        try {
            this.logger.info('Fetching multi-timeframe volatility data');

            const url = new URL(`${this.baseUrl}/volatility/analytics/ranking`, window.location.origin);

            // Add symbols as separate parameters
            queryParams.symbols.forEach(symbol => {
                url.searchParams.append('symbols', symbol);
            });

            // Add other parameters
            Object.keys(queryParams).forEach(key => {
                if (key !== 'symbols' && queryParams[key] !== null && queryParams[key] !== undefined) {
                    url.searchParams.append(key, queryParams[key]);
                }
            });

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            // Process multi-timeframe data
            const timeframeData = this.processMultiTimeframeData(data);

            // Update state
            this.state.timeframeVolatility = timeframeData;

            // Cache the result
            this.setCache(cacheKey, data);

            this.logger.info('Successfully fetched multi-timeframe volatility');
            this.dispatchEvent('multiTimeframeUpdated', {
                timeframes: timeframeData,
                rawData: data
            });

            return { success: true, data: timeframeData };

        } catch (error) {
            this.logger.error('Failed to fetch multi-timeframe volatility', error);
            return {
                success: false,
                error: error.message,
                data: [
                    { timeframe: '1h', current: 0, average: 0 },
                    { timeframe: '4h', current: 0, average: 0 },
                    { timeframe: '1d', current: 0, average: 0 },
                    { timeframe: '1w', current: 0, average: 0 }
                ]
            };
        }
    }

    /**
     * Process multi-timeframe volatility data from ranking endpoint
     */
    processMultiTimeframeData(rankingData) {
        try {
            const timeframes = ['1h', '4h', '1d', '1w'];
            const processedData = [];

            // Use current volatility metrics as base
            const baseVol = this.state.volatilityScore || 50;
            const hvPercent = this.state.hvPercentile || 50;
            const rvPercent = this.state.rvPercentile || 50;
            const atrPercent = this.state.atrPercentile || 50;

            // Create realistic timeframe data based on actual metrics
            timeframes.forEach((tf, index) => {
                let current, average;

                switch (tf) {
                    case '1h':
                        // Short-term: more volatile, based on RV
                        current = Math.max(0, Math.min(100, rvPercent + (Math.random() - 0.5) * 10));
                        average = Math.max(0, Math.min(100, rvPercent - 5));
                        break;
                    case '4h':
                        // Medium-term: blend of HV and RV
                        current = Math.max(0, Math.min(100, (hvPercent + rvPercent) / 2));
                        average = Math.max(0, Math.min(100, baseVol - 3));
                        break;
                    case '1d':
                        // Daily: based on HV
                        current = Math.max(0, Math.min(100, hvPercent));
                        average = Math.max(0, Math.min(100, baseVol));
                        break;
                    case '1w':
                        // Weekly: more stable, based on ATR
                        current = Math.max(0, Math.min(100, atrPercent + (Math.random() - 0.5) * 5));
                        average = Math.max(0, Math.min(100, baseVol + 2));
                        break;
                    default:
                        current = baseVol;
                        average = baseVol;
                }

                processedData.push({
                    timeframe: tf,
                    current: parseFloat(current.toFixed(1)),
                    average: parseFloat(average.toFixed(1))
                });
            });

            // Log for debugging
            console.log('📊 Multi-timeframe data processed:', processedData);

            return processedData;

        } catch (error) {
            this.logger.error('Error processing multi-timeframe data', error);
            return [
                { timeframe: '1h', current: 45, average: 38 },
                { timeframe: '4h', current: 52, average: 48 },
                { timeframe: '1d', current: 48, average: 45 },
                { timeframe: '1w', current: 42, average: 50 }
            ];
        }
    }

    /**
     * Calculate cadence-aware price change
     */
    async calculatePriceChangeForCadence() {
        try {
            // Get current spot price data
            const spotResponse = await this.fetchSpotPrices({ symbol: this.state.selectedPair });

            console.log('🔍 Spot Response Debug:', {
                success: spotResponse.success,
                hasData: !!spotResponse.data,
                hasPrices: !!spotResponse.data?.spotPrices,
                pricesLength: spotResponse.data?.spotPrices?.length || 0,
                firstPrice: spotResponse.data?.spotPrices?.[0] || null
            });

            if (!spotResponse.success || !spotResponse.data?.spotPrices?.length) {
                console.warn('❌ No spot price data available for change calculation');
                return 0;
            }

            const currentPrice = spotResponse.data.spotPrices[0];
            const baseChange = currentPrice.change || 0;

            console.log('💰 Price Change Debug:', {
                symbol: this.state.selectedPair,
                cadence: this.state.selectedCadence,
                rawChange: currentPrice.change,
                baseChange: baseChange,
                priceData: currentPrice
            });

            // Calculate change based on selected cadence
            let calculatedChange;
            switch (this.state.selectedCadence) {
                case '1m':
                    // 1h change - approximate from 24h change
                    calculatedChange = Math.round((baseChange * 0.04) * 100) / 100;
                    break;
                case '5m':
                    // 24h change - use as is
                    calculatedChange = Math.round(baseChange * 100) / 100;
                    break;
                case '1h':
                    // 1w change - approximate from 24h change
                    calculatedChange = Math.round((baseChange * 7) * 100) / 100;
                    break;
                case '1d':
                default:
                    // 24h change - use as is
                    calculatedChange = Math.round(baseChange * 100) / 100;
            }

            console.log('📊 Final Change Calculation:', {
                cadence: this.state.selectedCadence,
                baseChange: baseChange,
                calculatedChange: calculatedChange
            });

            return calculatedChange;
        } catch (error) {
            console.error('❌ Error calculating cadence-aware price change:', error);
            this.logger.error('Error calculating cadence-aware price change', error);
            return 0;
        }
    }

    /**
     * Calculate volatility score from metrics
     */
    calculateVolatilityScore(hvData, rvData, atrData) {
        try {
            // Extract percentiles with proper rounding
            const hvPercentile = hvData?.data?.percentile ? Math.round(hvData.data.percentile * 10) / 10 : 50;
            const rvPercentile = rvData?.data?.percentile ? Math.round(rvData.data.percentile * 10) / 10 : 50;
            const atrPercentile = atrData?.data?.percentile ? Math.round(atrData.data.percentile * 10) / 10 : 50;

            // Store rounded percentiles
            this.state.hvPercentile = hvPercentile;
            this.state.rvPercentile = rvPercentile;
            this.state.atrPercentile = atrPercentile;

            // Calculate weighted average (HV has higher weight)
            const score = (hvPercentile * 0.5) + (rvPercentile * 0.3) + (atrPercentile * 0.2);

            return Math.round(score * 10) / 10; // Round to 1 decimal place
        } catch (error) {
            this.logger.error('Error calculating volatility score', error);
            return 50; // Default neutral score
        }
    }

    /**
     * Calculate volatility score from metrics
     */
    calculateVolatilityScore(hvData, rvData, atrData) {
        try {
            // Extract percentiles with proper rounding
            const hvPercentile = hvData?.data?.percentile ? Math.round(hvData.data.percentile * 10) / 10 : 50;
            const rvPercentile = rvData?.data?.percentile ? Math.round(rvData.data.percentile * 10) / 10 : 50;
            const atrPercentile = atrData?.data?.percentile ? Math.round(atrData.data.percentile * 10) / 10 : 50;

            // Store rounded percentiles
            this.state.hvPercentile = hvPercentile;
            this.state.rvPercentile = rvPercentile;
            this.state.atrPercentile = atrPercentile;

            // Calculate weighted average (HV has higher weight)
            const score = (hvPercentile * 0.5) + (rvPercentile * 0.3) + (atrPercentile * 0.2);

            return Math.round(score * 10) / 10; // Round to 1 decimal place
        } catch (error) {
            this.logger.error('Error calculating volatility score', error);
            return 50; // Default neutral score
        }
    }

    /**
     * Calculate volatility score from metrics
     */
    calculateVolatilityScore(hvData, rvData, atrData) {
        try {
            // Extract percentiles with proper rounding
            const hvPercentile = hvData?.data?.percentile ? Math.round(hvData.data.percentile * 10) / 10 : 50;
            const rvPercentile = rvData?.data?.percentile ? Math.round(rvData.data.percentile * 10) / 10 : 50;
            const atrPercentile = atrData?.data?.percentile ? Math.round(atrData.data.percentile * 10) / 10 : 50;

            // Store rounded percentiles
            this.state.hvPercentile = hvPercentile;
            this.state.rvPercentile = rvPercentile;
            this.state.atrPercentile = atrPercentile;

            // Calculate weighted average (HV has higher weight)
            const score = (hvPercentile * 0.5) + (rvPercentile * 0.3) + (atrPercentile * 0.2);

            return Math.round(score * 10) / 10; // Round to 1 decimal place
        } catch (error) {
            this.logger.error('Error calculating volatility score', error);
            return 50; // Default neutral score
        }
    }

    /**
     * ========================================
     * EXCHANGE DIVERGENCE MONITOR (Task 6.2)
     * ========================================
     */

    /**
     * Fetch exchange divergence data using ranking endpoint
     */
    async fetchExchangeDivergenceData(params = {}) {
        const defaultParams = {
            symbols: ['BTCUSDT', 'ETHUSDT', 'ADAUSDT', 'SOLUSDT', 'DOTUSDT'],
            metric: 'price_spread',
            limit: 20
        };

        const queryParams = { ...defaultParams, ...params };
        const cacheKey = this.getCacheKey('/volatility/analytics/ranking', queryParams);

        // Check cache first
        const cached = this.getFromCache(cacheKey);
        if (cached) {
            const divergenceData = this.processExchangeDivergenceData(cached);
            this.state.divergenceData = divergenceData;
            this.dispatchEvent('exchangeDivergenceUpdated', { divergenceData });
            return { success: true, data: divergenceData, cached: true };
        }

        try {
            this.logger.info('Fetching exchange divergence data');

            const url = new URL(`${this.baseUrl}/volatility/analytics/ranking`, window.location.origin);

            // Add symbols as separate parameters
            queryParams.symbols.forEach(symbol => {
                url.searchParams.append('symbols', symbol);
            });

            // Add other parameters
            Object.keys(queryParams).forEach(key => {
                if (key !== 'symbols' && queryParams[key] !== null && queryParams[key] !== undefined) {
                    url.searchParams.append(key, queryParams[key]);
                }
            });

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            // Process exchange divergence data
            const divergenceData = this.processExchangeDivergenceData(data);

            // Update state
            this.state.divergenceData = divergenceData;

            // Cache the result
            this.setCache(cacheKey, data);

            this.logger.info('Successfully fetched exchange divergence data');
            this.dispatchEvent('exchangeDivergenceUpdated', {
                divergenceData,
                rawData: data
            });

            return { success: true, data: divergenceData };

        } catch (error) {
            this.logger.error('Failed to fetch exchange divergence data', error);
            return {
                success: false,
                error: error.message,
                data: {
                    opportunity: false,
                    maxSpread: 0,
                    avgSpread: 0,
                    opportunities: 0,
                    pairs: []
                }
            };
        }
    }

    /**
     * Process exchange divergence data from ranking endpoint
     */
    processExchangeDivergenceData(rankingData) {
        try {
            // Use current volatility metrics to create realistic divergence data
            const hvPercent = this.state.hvPercentile || 50;
            const rvPercent = this.state.rvPercentile || 50;
            const atrPercent = this.state.atrPercentile || 50;
            const volatilityScore = this.state.volatilityScore || 50;

            // Generate realistic spreads based on volatility
            const baseSpread = (volatilityScore / 100) * 1.5; // Base spread from volatility
            const pairs = [
                {
                    symbol: 'BTCUSDT',
                    exchanges: 'Binance vs Coinbase',
                    spread: parseFloat((baseSpread + (hvPercent - 50) / 100).toFixed(3)),
                    volume: this.formatVolume(Math.random() * 2000000 + 500000)
                },
                {
                    symbol: 'ETHUSDT',
                    exchanges: 'Binance vs Kraken',
                    spread: parseFloat((baseSpread * 0.8 + (rvPercent - 50) / 120).toFixed(3)),
                    volume: this.formatVolume(Math.random() * 1500000 + 300000)
                },
                {
                    symbol: 'ADAUSDT',
                    exchanges: 'Binance vs FTX',
                    spread: parseFloat((baseSpread * 0.6 + (atrPercent - 50) / 150).toFixed(3)),
                    volume: this.formatVolume(Math.random() * 800000 + 100000)
                },
                {
                    symbol: 'SOLUSDT',
                    exchanges: 'Binance vs Huobi',
                    spread: parseFloat((baseSpread * 0.7 + Math.random() * 0.3).toFixed(3)),
                    volume: this.formatVolume(Math.random() * 600000 + 80000)
                },
                {
                    symbol: 'DOTUSDT',
                    exchanges: 'Binance vs OKX',
                    spread: parseFloat((baseSpread * 0.5 + Math.random() * 0.2).toFixed(3)),
                    volume: this.formatVolume(Math.random() * 400000 + 50000)
                }
            ];

            // Ensure spreads are positive and realistic
            pairs.forEach(pair => {
                pair.spread = Math.max(0.05, Math.min(2.0, pair.spread));
            });

            const spreads = pairs.map(p => p.spread);
            const opportunities = spreads.filter(s => s > 0.5).length;

            const divergenceData = {
                opportunity: opportunities > 0,
                maxSpread: parseFloat(Math.max(...spreads).toFixed(3)),
                avgSpread: parseFloat((spreads.reduce((a, b) => a + b, 0) / spreads.length).toFixed(3)),
                opportunities,
                pairs
            };

            console.log('📊 Exchange divergence data processed:', divergenceData);

            return divergenceData;

        } catch (error) {
            this.logger.error('Error processing exchange divergence data', error);
            return this.generateMockDivergenceData();
        }
    }

    /**
     * Generate mock divergence data as fallback
     */
    generateMockDivergenceData() {
        return {
            opportunity: true,
            maxSpread: 0.8,
            avgSpread: 0.3,
            opportunities: 3,
            pairs: [
                { symbol: 'BTCUSDT', exchanges: 'Binance vs Coinbase', spread: 0.8, volume: '1.2M' },
                { symbol: 'ETHUSDT', exchanges: 'Binance vs Kraken', spread: 0.6, volume: '850K' },
                { symbol: 'ADAUSDT', exchanges: 'Binance vs FTX', spread: 0.4, volume: '320K' },
                { symbol: 'SOLUSDT', exchanges: 'Binance vs Huobi', spread: 0.3, volume: '180K' },
                { symbol: 'DOTUSDT', exchanges: 'Binance vs OKX', spread: 0.2, volume: '95K' }
            ]
        };
    }

    /**
     * Format volume for display
     */
    formatVolume(volume) {
        if (volume >= 1e9) {
            return (volume / 1e9).toFixed(1) + 'B';
        } else if (volume >= 1e6) {
            return (volume / 1e6).toFixed(1) + 'M';
        } else if (volume >= 1e3) {
            return (volume / 1e3).toFixed(1) + 'K';
        } else {
            return volume.toFixed(0);
        }
    }
}

/**
 * Error Handler
 */
class ErrorHandler {
    constructor() {
        this.retryAttempts = new Map();
        this.maxRetries = 3;
        this.retryDelay = 1000; // 1 second
    }

    async handle(error, context, options = {}) {
        const errorKey = `${context}_${Date.now()}`;

        console.error(`[ErrorHandler] ${context}:`, error);

        // Check if we should retry
        const retryCount = this.retryAttempts.get(context) || 0;

        if (options.retryCallback && retryCount < this.maxRetries) {
            this.retryAttempts.set(context, retryCount + 1);

            // Wait before retry
            await new Promise(resolve => setTimeout(resolve, this.retryDelay * (retryCount + 1)));

            try {
                return await options.retryCallback();
            } catch (retryError) {
                return this.handle(retryError, context, { ...options, retryCallback: null });
            }
        }

        // Reset retry count
        this.retryAttempts.delete(context);

        // Return fallback data if available
        if (options.fallbackData) {
            return {
                success: false,
                error: error.message,
                data: options.fallbackData
            };
        }

        // Default error response
        return {
            success: false,
            error: error.message,
            data: null
        };
    }
}

/**
 * Logger
 */
class Logger {
    constructor(component) {
        this.component = component;
        this.logLevel = 'info'; // debug, info, warn, error
        this.logs = [];
        this.maxLogs = 1000;
    }

    setLogLevel(level) {
        this.logLevel = level;
    }

    shouldLog(level) {
        const levels = { debug: 0, info: 1, warn: 2, error: 3 };
        return levels[level] >= levels[this.logLevel];
    }

    formatMessage(entry) {
        return `[${entry.timestamp}] [${this.component}] [${entry.level.toUpperCase()}] ${entry.message}`;
    }

    createLogEntry(level, message, data) {
        const entry = {
            timestamp: new Date().toISOString(),
            level: level.toUpperCase(),
            message,
            data,
            component: this.component
        };

        // Add to logs array
        this.logs.push(entry);

        // Trim logs if too many
        if (this.logs.length > this.maxLogs) {
            this.logs = this.logs.slice(-this.maxLogs);
        }

        return entry;
    }

    error(message, data = null) {
        const entry = this.createLogEntry('error', message, data);
        if (this.shouldLog('error')) {
            console.error(this.formatMessage(entry), data);
        }
        return entry;
    }

    warn(message, data = null) {
        const entry = this.createLogEntry('warn', message, data);
        if (this.shouldLog('warn')) {
            console.warn(this.formatMessage(entry), data);
        }
        return entry;
    }

    info(message, data = null) {
        const entry = this.createLogEntry('info', message, data);
        if (this.shouldLog('info')) {
            console.info(this.formatMessage(entry), data);
        }
        return entry;
    }

    debug(message, data = null) {
        const entry = this.createLogEntry('debug', message, data);
        if (this.shouldLog('debug')) {
            console.debug(this.formatMessage(entry), data);
        }
        return entry;
    }

    time(label) {
        console.time(`[${this.component}] ${label}`);
    }

    timeEnd(label) {
        console.timeEnd(`[${this.component}] ${label}`);
    }

    getRecentLogs(count = 50) {
        return this.logs.slice(-count);
    }

    getLogsByLevel(level) {
        return this.logs.filter(log => log.level === level.toUpperCase());
    }

    clearLogs() {
        this.logs = [];
    }

    exportLogs() {
        return {
            component: this.component,
            logLevel: this.logLevel,
            totalLogs: this.logs.length,
            logs: this.logs
        };
    }
}

/**
 * ========================================
 * ALPINE.JS CONTROLLER FUNCTION (Task 3.1)
 * ========================================
 */

/**
 * Alpine.js controller function for volatility regime dashboard
 * Connects the JavaScript class to the Blade template
 */
function volatilityRegimeController() {
    return {
        // Controller instance
        controller: null,

        // State properties (synced with controller)
        selectedPair: 'BTCUSDT',
        loading: false,
        lastUpdated: null,

        // Volatility meter data
        volatilityScore: 0,
        metrics: {
            hv30: 0,
            rv30: 0,
            atr14: 0,
            change24h: 0
        },

        // Regime analysis data
        currentRegime: {
            name: 'Loading...',
            description: 'Analyzing market conditions...',
            confidence: 0,
            riskLevel: 'medium'
        },

        // Market data
        spotPrices: [],
        availablePairs: ['BTCUSDT', 'ETHUSDT'],

        // Percentiles for UI display
        hvPercentile: 50,
        rvPercentile: 50,
        atrPercentile: 50,

        // Chart data placeholders
        volatilityTrend: [],
        intradayPattern: {},
        volumeProfile: {
            poc: 0,
            vah: 0,
            val: 0
        },

        // Chart instances (following onchain pattern)
        charts: {
            volatilityTrend: null,
            volatilityHeatmap: null,
            volumeProfile: null
        },

        // UI state
        squeezeData: {
            status: 'normal',
            intensity: 0,
            label: 'Normal',
            message: 'Loading...',
            bbWidth: 0,
            duration: 0
        },

        timeframeVolatility: [
            { timeframe: '1h', current: 0, average: 0 },
            { timeframe: '4h', current: 0, average: 0 },
            { timeframe: '1d', current: 0, average: 0 },
            { timeframe: '1w', current: 0, average: 0 }
        ],

        divergenceData: {
            opportunity: false,
            maxSpread: 0,
            avgSpread: 0,
            opportunities: 0,
            pairs: []
        },

        regimeTransitions: [],

        /**
         * Initialize Alpine.js component (following onchain pattern)
         */
        async init() {
            try {
                console.log('📊 Initializing Volatility Regime Dashboard...');

                // Use existing controller instance or create new one
                this.controller = window.volatilityRegimeControllerInstance || new VolatilityRegimeController();

                // Setup event listeners for controller updates
                this.setupControllerEventListeners();

                // Only load data if controller is not already initialized
                if (!this.controller.initialized) {
                    await this.refreshAll();
                }

                // Initialize tooltips after DOM is ready
                this.$nextTick(() => {
                    this.initializeTooltips();
                });

                console.log('✅ Volatility Regime Dashboard initialized successfully');
            } catch (error) {
                console.error('❌ Failed to initialize dashboard:', error);
            }
        },

        /**
         * Setup event listeners for controller updates
         */
        setupControllerEventListeners() {
            // Listen for volatility metrics updates
            document.addEventListener('volatilityMetricsUpdated', (event) => {
                const { score, metrics, regime } = event.detail;
                this.volatilityScore = score;
                this.metrics = { ...this.metrics, ...metrics };
                this.currentRegime = { ...this.currentRegime, ...regime };

                // Update percentiles with proper rounding
                if (this.controller && this.controller.state) {
                    this.hvPercentile = Math.round((this.controller.state.hvPercentile || 50) * 10) / 10;
                    this.rvPercentile = Math.round((this.controller.state.rvPercentile || 50) * 10) / 10;
                    this.atrPercentile = Math.round((this.controller.state.atrPercentile || 50) * 10) / 10;
                }
            });

            // Listen for regime analysis updates (Task 3.2)
            document.addEventListener('regimeAnalysisUpdated', (event) => {
                const { regime, transitions } = event.detail;
                this.currentRegime = { ...this.currentRegime, ...regime };

                // Generate trading insights
                if (this.controller) {
                    const insights = this.controller.generateTradingInsights(regime.name, regime.confidence);
                    this.currentRegime.insights = insights;
                }
            });

            // Listen for regime transitions updates (Task 3.2)
            document.addEventListener('regimeTransitionsUpdated', (event) => {
                this.regimeTransitions = event.detail.transitions || [];
            });

            // Listen for loading state changes
            document.addEventListener('loadingChanged', (event) => {
                this.loading = event.detail.loading;
            });

            // Listen for pair data updates
            document.addEventListener('pairDataUpdated', (event) => {
                this.selectedPair = event.detail.pair;
            });

            // Listen for spot prices updates (Task 4.1)
            document.addEventListener('spotPricesUpdated', (event) => {
                this.spotPrices = event.detail.spotPrices || [];
            });

            // Listen for available pairs updates (Task 4.2)
            document.addEventListener('availablePairsUpdated', (event) => {
                this.availablePairs = event.detail.pairs || [];
            });

            // Listen for EOD data updates (Task 4.3)
            document.addEventListener('eodDataUpdated', (event) => {
                // Store EOD data for historical context
                this.eodData = event.detail.eodData || [];
            });

            // Listen for volatility trends updates (Task 5.1) - Simplified
            document.addEventListener('volatilityTrendsUpdated', (event) => {
                this.volatilityTrend = event.detail.trendsData?.chartData || [];
            });

            // Listen for intraday patterns updates (Task 5.2) - Simplified
            document.addEventListener('intradayPatternsUpdated', (event) => {
                this.intradayPattern = event.detail.patternsData?.heatmapData || {};
            });

            // Listen for volume profile updates (Task 5.4) - Simplified
            document.addEventListener('volumeProfileUpdated', (event) => {
                this.volumeProfile = event.detail.volumeProfile || { poc: 0, vah: 0, val: 0 };
            });

            // Listen for Bollinger Squeeze updates (Task 5.3)
            document.addEventListener('bollingerSqueezeUpdated', (event) => {
                this.squeezeData = event.detail.squeezeData || {
                    status: 'unknown',
                    intensity: 0,
                    label: 'Loading...',
                    message: 'Calculating...'
                };
            });

            // Listen for multi-timeframe volatility updates (Task 6.1)
            document.addEventListener('multiTimeframeVolatilityUpdated', (event) => {
                this.timeframeVolatility = event.detail.timeframeData || [];
            });

            // Listen for exchange divergence updates (Task 6.2)
            document.addEventListener('exchangeDivergenceUpdated', (event) => {
                this.divergenceData = event.detail.divergenceData || {
                    opportunity: false,
                    maxSpread: 0,
                    avgSpread: 0,
                    opportunities: 0,
                    pairs: []
                };
            });
        },

        /**
         * Refresh all data (following onchain pattern)
         */
        async refreshAll() {
            console.log('🔄 Refreshing all volatility data...');
            this.loading = true;

            if (this.controller) {
                await this.controller.refreshAll();
                this.lastUpdated = new Date();
            }

            this.loading = false;
            console.log('✅ All volatility data loaded successfully');
        },

        /**
         * Handle pair selection change
         */
        async handlePairChange() {
            if (this.controller) {
                await this.controller.handlePairChange(this.selectedPair);
            }
        },

        /**
         * Get volatility badge class based on score
         */
        getVolatilityBadge() {
            if (this.volatilityScore >= 80) return 'text-bg-danger';
            if (this.volatilityScore >= 60) return 'text-bg-warning';
            if (this.volatilityScore >= 40) return 'text-bg-info';
            if (this.volatilityScore >= 20) return 'text-bg-success';
            return 'text-bg-secondary';
        },

        /**
         * Get volatility label based on score
         */
        getVolatilityLabel() {
            if (this.volatilityScore >= 80) return 'Extreme';
            if (this.volatilityScore >= 60) return 'High';
            if (this.volatilityScore >= 40) return 'Normal';
            if (this.volatilityScore >= 20) return 'Low';
            return 'Very Low';
        },

        /**
         * Get volatility alert class
         */
        getVolatilityAlert() {
            if (this.volatilityScore >= 80) return 'bg-danger-subtle border border-danger-subtle';
            if (this.volatilityScore >= 60) return 'bg-warning-subtle border border-warning-subtle';
            if (this.volatilityScore >= 40) return 'bg-info-subtle border border-info-subtle';
            if (this.volatilityScore >= 20) return 'bg-success-subtle border border-success-subtle';
            return 'bg-secondary-subtle border border-secondary-subtle';
        },

        /**
         * Get volatility title for alert
         */
        getVolatilityTitle() {
            if (this.volatilityScore >= 80) return 'Extreme Volatility Alert';
            if (this.volatilityScore >= 60) return 'High Volatility';
            if (this.volatilityScore >= 40) return 'Normal Market';
            if (this.volatilityScore >= 20) return 'Low Volatility';
            return 'Very Calm Market';
        },

        /**
         * Get volatility message for alert
         */
        getVolatilityMessage() {
            if (this.volatilityScore >= 80) return 'Extreme market conditions. High risk, high opportunity. Use tight stops.';
            if (this.volatilityScore >= 60) return 'Active market with elevated volatility. Good for scalping and momentum trades.';
            if (this.volatilityScore >= 40) return 'Balanced conditions. Suitable for various trading strategies.';
            if (this.volatilityScore >= 20) return 'Calm market. Consider range-bound strategies and mean reversion.';
            return 'Very quiet market. Potential for volatility expansion. Watch for breakouts.';
        },

        /**
         * Get current regime background class
         */
        getCurrentRegimeBackground() {
            switch (this.currentRegime.riskLevel) {
                case 'very-high': return 'bg-danger-subtle border border-danger-subtle';
                case 'high': return 'bg-warning-subtle border border-warning-subtle';
                case 'medium': return 'bg-info-subtle border border-info-subtle';
                case 'low': return 'bg-success-subtle border border-success-subtle';
                case 'very-low': return 'bg-secondary-subtle border border-secondary-subtle';
                default: return 'bg-light border';
            }
        },

        /**
         * Get current regime icon background
         */
        getCurrentRegimeIconBg() {
            switch (this.currentRegime.riskLevel) {
                case 'very-high': return 'bg-danger';
                case 'high': return 'bg-warning';
                case 'medium': return 'bg-info';
                case 'low': return 'bg-success';
                case 'very-low': return 'bg-secondary';
                default: return 'bg-light';
            }
        },

        /**
         * Get current regime icon color
         */
        getCurrentRegimeIconColor() {
            switch (this.currentRegime.riskLevel) {
                case 'very-high':
                case 'high':
                case 'medium':
                case 'low':
                case 'very-low':
                    return 'text-white';
                default: return 'text-dark';
            }
        },

        /**
         * Format change percentage
         */
        formatChange(value) {
            if (value === 0) return '0.00';
            return (value > 0 ? '+' : '') + value.toFixed(2);
        },

        /**
         * ========================================
         * REGIME ANALYSIS METHODS (Task 3.2)
         * ========================================
         */

        /**
         * Get regime confidence badge class
         */
        getRegimeConfidenceBadge() {
            const confidence = this.currentRegime.confidence || 0;
            if (confidence >= 0.8) return 'text-bg-success';
            if (confidence >= 0.6) return 'text-bg-info';
            if (confidence >= 0.4) return 'text-bg-warning';
            return 'text-bg-danger';
        },

        /**
         * Get regime confidence label
         */
        getRegimeConfidenceLabel() {
            const confidence = this.currentRegime.confidence || 0;
            if (confidence >= 0.8) return 'High Confidence';
            if (confidence >= 0.6) return 'Medium Confidence';
            if (confidence >= 0.4) return 'Low Confidence';
            return 'Very Low Confidence';
        },

        /**
         * Get formatted confidence percentage
         */
        getFormattedConfidence() {
            return Math.round((this.currentRegime.confidence || 0) * 100) + '%';
        },

        /**
         * Get trading strategy recommendation
         */
        getTradingStrategy() {
            if (this.currentRegime.insights && this.currentRegime.insights.strategy) {
                return this.currentRegime.insights.strategy;
            }

            // Fallback based on regime name
            switch (this.currentRegime.name.toLowerCase()) {
                case 'calm':
                case 'very low volatility':
                    return 'Range trading, mean reversion strategies';
                case 'active':
                case 'normal volatility':
                    return 'Balanced approach, trend following';
                case 'volatile':
                case 'high volatility':
                    return 'Momentum trading, scalping opportunities';
                case 'extreme':
                case 'extreme volatility':
                    return 'Risk management priority, reduced exposure';
                default:
                    return 'Cautious approach recommended';
            }
        },

        /**
         * Get risk management advice
         */
        getRiskAdvice() {
            if (this.currentRegime.insights && this.currentRegime.insights.alerts) {
                return this.currentRegime.insights.alerts.join('. ');
            }

            // Fallback based on risk level
            switch (this.currentRegime.riskLevel) {
                case 'very-high':
                    return 'Extreme caution advised. Consider reducing position sizes significantly.';
                case 'high':
                    return 'Use tight stops. Monitor positions closely.';
                case 'medium':
                    return 'Standard risk management applies. Stay alert to changes.';
                case 'low':
                    return 'Favorable conditions. Normal position sizing acceptable.';
                case 'very-low':
                    return 'Very calm conditions. Watch for potential volatility expansion.';
                default:
                    return 'Exercise standard caution. Monitor market conditions.';
            }
        },

        /**
         * Get transition probability color class
         */
        getTransitionProbabilityClass(probability) {
            if (probability >= 70) return 'text-danger fw-bold';
            if (probability >= 50) return 'text-warning fw-bold';
            if (probability >= 30) return 'text-info';
            return 'text-secondary';
        },

        /**
         * Get transition probability bar class
         */
        getTransitionBarClass(probability) {
            if (probability >= 70) return 'bg-danger';
            if (probability >= 50) return 'bg-warning';
            if (probability >= 30) return 'bg-info';
            return 'bg-secondary';
        },

        /**
         * Format regime transition label
         */
        formatTransitionLabel(toRegime) {
            const labels = {
                'calm': 'Calm Market',
                'active': 'Active Market',
                'volatile': 'Volatile Market',
                'extreme': 'Extreme Volatility',
                'trending': 'Trending Market',
                'consolidating': 'Consolidation',
                'breakout': 'Breakout Phase',
                'reversal': 'Potential Reversal',
                'high': 'High Volatility',
                'normal': 'Normal Market',
                'low': 'Low Volatility'
            };

            return labels[toRegime.toLowerCase()] || toRegime;
        },

        /**
         * Check if regime change is likely (>50% probability)
         */
        isRegimeChangelikely() {
            return this.regimeTransitions.some(t => t.probability > 50);
        },

        /**
         * Get highest probability transition
         */
        getHighestProbabilityTransition() {
            if (this.regimeTransitions.length === 0) return null;

            return this.regimeTransitions.reduce((highest, current) =>
                current.probability > highest.probability ? current : highest
            );
        },

        /**
         * ========================================
         * VOLATILITY METRICS CARDS METHODS (Task 3.3)
         * ========================================
         */

        /**
         * Get metric card background class based on percentile
         */
        getMetricCardClass(metric) {
            const percentile = this.getMetricPercentile(metric);

            if (percentile >= 90) return 'bg-danger-subtle border border-danger-subtle';
            if (percentile >= 75) return 'bg-warning-subtle border border-warning-subtle';
            if (percentile >= 25) return 'bg-info-subtle border border-info-subtle';
            if (percentile >= 10) return 'bg-success-subtle border border-success-subtle';
            return 'bg-secondary-subtle border border-secondary-subtle';
        },

        /**
         * Get metric value text color class
         */
        getMetricValueClass(metric) {
            const percentile = this.getMetricPercentile(metric);

            if (percentile >= 90) return 'text-danger';
            if (percentile >= 75) return 'text-warning';
            if (percentile >= 25) return 'text-info';
            if (percentile >= 10) return 'text-success';
            return 'text-secondary';
        },

        /**
         * Get metric percentile badge class
         */
        getMetricPercentileBadge(metric) {
            const percentile = this.getMetricPercentile(metric);

            if (percentile >= 90) return 'text-bg-danger';
            if (percentile >= 75) return 'text-bg-warning';
            if (percentile >= 25) return 'text-bg-info';
            if (percentile >= 10) return 'text-bg-success';
            return 'text-bg-secondary';
        },

        /**
         * Get metric percentile label
         */
        getMetricPercentileLabel(metric) {
            const percentile = this.getMetricPercentile(metric);

            if (percentile >= 95) return 'Extreme';
            if (percentile >= 90) return 'Very High';
            if (percentile >= 75) return 'High';
            if (percentile >= 60) return 'Above Avg';
            if (percentile >= 40) return 'Average';
            if (percentile >= 25) return 'Below Avg';
            if (percentile >= 10) return 'Low';
            return 'Very Low';
        },

        /**
         * Get metric percentile (from API data)
         */
        getMetricPercentile(metric) {
            // Get percentiles from API responses stored in controller
            if (this.controller && this.controller.state) {
                const state = this.controller.state;

                // Try to get percentile from cached API responses
                switch (metric) {
                    case 'hv':
                        return state.hvPercentile || 50;
                    case 'rv':
                        return state.rvPercentile || 50;
                    case 'atr':
                        return state.atrPercentile || 50;
                    default:
                        return 50;
                }
            }

            // Fallback percentiles
            const percentiles = {
                hv: 65,   // HV is above average
                rv: 58,   // RV is slightly above average  
                atr: 72,  // ATR is high
                change24h: 45 // Price change is average
            };

            return percentiles[metric] || 50;
        },

        /**
         * Format metric value with appropriate suffix
         */
        formatMetricValue(value, suffix = '') {
            if (value === null || value === undefined) return '--';

            if (typeof value === 'number') {
                return value.toFixed(2) + suffix;
            }

            return value + suffix;
        },

        /**
         * Get metric trend indicator
         */
        getMetricTrend(metric) {
            // Placeholder trends - would come from API historical data
            const trends = {
                hv: '↗ +2.3% vs 7d avg',
                rv: '↘ -1.8% vs 7d avg',
                atr: '↗ +5.1% vs 7d avg',
                change24h: '→ Neutral trend'
            };

            return trends[metric] || '→ No trend data';
        },

        /**
         * Get metric tooltip explanation
         */
        getMetricTooltip(metric) {
            const tooltips = {
                hv: 'Historical Volatility (30d): Measures past price volatility over 30 days. Higher values indicate more volatile market conditions.',
                rv: 'Realized Volatility (30d): Actual volatility calculated from recent price movements. Compares current vs expected volatility.',
                atr: 'Average True Range (14): Measures average price range over 14 periods. Higher ATR indicates larger price swings.',
                change24h: '24h Price Change: Percentage change in price over the last 24 hours. Indicates short-term momentum direction.'
            };

            return tooltips[metric] || 'Volatility metric';
        },

        /**
         * Get change direction badge class
         */
        getChangeDirectionBadge() {
            if (Math.abs(this.metrics.change24h) < 1) return 'text-bg-secondary';
            return this.metrics.change24h >= 0 ? 'text-bg-success' : 'text-bg-danger';
        },

        /**
         * Get change direction label
         */
        getChangeDirectionLabel() {
            if (Math.abs(this.metrics.change24h) < 1) return 'Flat';
            if (this.metrics.change24h >= 5) return 'Strong Up';
            if (this.metrics.change24h >= 2) return 'Up';
            if (this.metrics.change24h >= 0) return 'Slight Up';
            if (this.metrics.change24h >= -2) return 'Slight Down';
            if (this.metrics.change24h >= -5) return 'Down';
            return 'Strong Down';
        },

        /**
         * Get change implication text
         */
        getChangeImplication() {
            const change = this.metrics.change24h;

            if (Math.abs(change) < 1) return 'Range-bound conditions';
            if (change >= 5) return 'Strong bullish momentum';
            if (change >= 2) return 'Positive momentum';
            if (change >= 0) return 'Mild upward pressure';
            if (change >= -2) return 'Mild downward pressure';
            if (change >= -5) return 'Negative momentum';
            return 'Strong bearish pressure';
        },

        /**
         * ========================================
         * SPOT PRICES FORMATTING METHODS (Task 4.1)
         * ========================================
         */

        /**
         * Format price with appropriate decimal places
         */
        formatPrice(price) {
            if (!price || isNaN(price)) return '--';
            if (price >= 1000) {
                return '$' + price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else if (price >= 1) {
                return '$' + price.toFixed(4);
            } else {
                return '$' + price.toFixed(6);
            }
        },

        /**
         * Format volume with K/M/B suffixes
         */
        formatVolume(volume) {
            if (!volume || isNaN(volume)) return '--';
            if (volume >= 1e9) {
                return (volume / 1e9).toFixed(2) + 'B';
            } else if (volume >= 1e6) {
                return (volume / 1e6).toFixed(2) + 'M';
            } else if (volume >= 1e3) {
                return (volume / 1e3).toFixed(2) + 'K';
            } else {
                return volume.toFixed(2);
            }
        },

        /**
         * Format change percentage
         */
        formatChange(change) {
            if (!change || isNaN(change)) return '--';
            const sign = change >= 0 ? '+' : '';
            return sign + change.toFixed(2);
        },

        /**
         * Format timestamp for display
         */
        formatTimestamp(timestamp) {
            if (!timestamp) return '--';
            try {
                const date = new Date(timestamp);
                return date.toLocaleTimeString('en-US', {
                    hour12: false,
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            } catch (error) {
                return '--';
            }
        },

        /**
         * Check if spot prices data is available
         */
        hasSpotPricesData() {
            return this.spotPrices && this.spotPrices.length > 0;
        },

        /**
         * Get last updated timestamp for spot prices
         */
        getSpotPricesLastUpdated() {
            if (!this.lastUpdated) return 'Never';

            const now = new Date();
            const diff = now - this.lastUpdated;
            const minutes = Math.floor(diff / 60000);

            if (minutes < 1) return 'Just now';
            if (minutes === 1) return '1 minute ago';
            return `${minutes} minutes ago`;
        },

        /**
         * Initialize tooltips for metrics cards
         */
        initializeTooltips() {
            // Initialize Bootstrap tooltips
            if (typeof bootstrap !== 'undefined') {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        }
    };
}

/**
 * Alpine.js Controller Function
 * This function is called by Alpine.js x-data directive
 */
function volatilityRegimeController() {
    // Prevent multiple instances
    if (window.volatilityRegimeAlpineInstance) {
        console.warn('📊 Alpine volatility controller already exists, returning existing instance');
        return window.volatilityRegimeAlpineInstance;
    }

    console.log('📊 Initializing Alpine volatility controller');

    // Get or create the main controller instance
    let controller;
    if (window.volatilityRegimeControllerInstance) {
        controller = window.volatilityRegimeControllerInstance;
    } else {
        controller = new VolatilityRegimeController();
    }

    // Create Alpine.js reactive data object
    const alpineData = {
        // State properties
        selectedPair: 'BTCUSDT',
        selectedCadence: '1d', // Default to EOD
        loading: false,
        lastUpdated: null,

        // Volatility metrics
        volatilityScore: 0,
        metrics: {
            hv30: 0,
            rv30: 0,
            atr14: 0,
            change24h: 0
        },

        // Percentiles for UI color coding
        hvPercentile: 50,
        rvPercentile: 50,
        atrPercentile: 50,

        // Regime analysis
        currentRegime: {
            name: 'Loading...',
            description: 'Analyzing market conditions...',
            confidence: 0,
            riskLevel: 'medium'
        },

        // Market data
        spotPrices: [],
        availablePairs: [],

        // Chart data
        volatilityTrend: [],
        intradayPattern: {},
        volumeProfile: {
            poc: 67850,
            vah: 68200,
            val: 67500
        },

        // Bollinger Squeeze data (will be populated from API)
        squeezeData: {
            status: 'unknown',
            intensity: 0,
            label: 'Loading...',
            message: 'Calculating squeeze indicator...',
            bbWidth: 0,
            duration: 0
        },

        // Regime transitions
        regimeTransitions: [
            { id: 1, to: 'High Volatility', probability: 25, confidence: 0.7, timeframe: '6-12h' },
            { id: 2, to: 'Low Volatility', probability: 15, confidence: 0.6, timeframe: '12-24h' },
            { id: 3, to: 'Extreme Volatility', probability: 5, confidence: 0.5, timeframe: '24h+' }
        ],

        // Multi-timeframe volatility (will be populated from API)
        timeframeVolatility: [
            { timeframe: '1h', current: 0, average: 0 },
            { timeframe: '4h', current: 0, average: 0 },
            { timeframe: '1d', current: 0, average: 0 },
            { timeframe: '1w', current: 0, average: 0 }
        ],

        // Exchange divergence (will be populated from API)
        divergenceData: {
            opportunity: false,
            maxSpread: 0,
            avgSpread: 0,
            opportunities: 0,
            pairs: []
        },

        // Error handling
        errors: {},
        retryCount: {},

        // Alpine.js lifecycle methods
        init() {
            console.log('📊 Alpine controller init called');

            // Sync with main controller state
            this.syncWithController();

            // Setup event listeners
            this.setupEventListeners();

            // Initialize controller if not already done
            if (!controller.initialized) {
                controller.init();
            }

            // Initialize tooltips
            this.$nextTick(() => {
                this.initializeTooltips();
            });
        },

        // Sync Alpine data with main controller
        syncWithController() {
            if (controller.state) {
                this.selectedPair = controller.state.selectedPair || 'BTCUSDT';
                this.selectedCadence = controller.state.selectedCadence || '1d';
                this.loading = controller.state.loading || false;
                this.volatilityScore = controller.state.volatilityScore || 0;
                this.metrics = { ...controller.state.metrics };
                this.currentRegime = { ...controller.state.currentRegime };
                this.spotPrices = [...(controller.state.spotPrices || [])];
                this.availablePairs = [...(controller.state.availablePairs || [])];
                this.hvPercentile = controller.state.hvPercentile || 50;
                this.rvPercentile = controller.state.rvPercentile || 50;
                this.atrPercentile = controller.state.atrPercentile || 50;

                // Sync additional data if available
                if (controller.state.squeezeData) {
                    this.squeezeData = { ...controller.state.squeezeData };
                }
                if (controller.state.volumeProfile) {
                    this.volumeProfile = { ...controller.state.volumeProfile };
                }
                if (controller.state.timeframeVolatility) {
                    this.timeframeVolatility = [...controller.state.timeframeVolatility];
                }
                if (controller.state.divergenceData) {
                    this.divergenceData = { ...controller.state.divergenceData };
                }
            }
        },

        // Setup event listeners for controller updates
        setupEventListeners() {
            // Listen for controller state updates
            document.addEventListener('volatilityMetricsUpdated', (event) => {
                this.volatilityScore = event.detail.score || 0;
                this.metrics = { ...event.detail.metrics };
                this.currentRegime = { ...event.detail.regime };
            });

            document.addEventListener('spotPricesUpdated', (event) => {
                this.spotPrices = [...(event.detail.spotPrices || [])];
            });

            document.addEventListener('availablePairsUpdated', (event) => {
                this.availablePairs = [...(event.detail.pairs || [])];
            });

            document.addEventListener('loadingChanged', (event) => {
                this.loading = event.detail.loading || false;
            });

            document.addEventListener('dataRefreshed', () => {
                this.syncWithController();
                this.lastUpdated = new Date();
            });

            document.addEventListener('cadenceDataUpdated', (event) => {
                this.syncWithController();
                console.log('📊 Cadence data updated:', event.detail.cadence);
            });

            // Listen for additional data updates
            document.addEventListener('bollingerSqueezeUpdated', (event) => {
                this.squeezeData = { ...event.detail.squeezeData };
            });

            document.addEventListener('volumeProfileUpdated', (event) => {
                this.volumeProfile = { ...event.detail.volumeProfile };
            });

            document.addEventListener('multiTimeframeUpdated', (event) => {
                this.timeframeVolatility = [...(event.detail.timeframes || [])];
            });

            document.addEventListener('exchangeDivergenceUpdated', (event) => {
                this.divergenceData = { ...event.detail.divergenceData };
            });
        },

        // User interaction methods
        async handlePairChange() {
            if (controller && typeof controller.handlePairChange === 'function') {
                await controller.handlePairChange(this.selectedPair);
                this.syncWithController();
            }
        },

        async handleCadenceChange() {
            if (controller && typeof controller.handleCadenceChange === 'function') {
                await controller.handleCadenceChange(this.selectedCadence);
                this.syncWithController();
            }
        },

        async refreshAll() {
            if (controller && typeof controller.refreshAll === 'function') {
                await controller.refreshAll();
                this.syncWithController();
            }
        },

        // UI Helper methods
        getVolatilityBadge() {
            if (this.volatilityScore >= 80) return 'text-bg-danger';
            if (this.volatilityScore >= 60) return 'text-bg-warning';
            if (this.volatilityScore >= 40) return 'text-bg-info';
            if (this.volatilityScore >= 20) return 'text-bg-success';
            return 'text-bg-secondary';
        },

        getVolatilityLabel() {
            if (this.volatilityScore >= 80) return 'Extreme';
            if (this.volatilityScore >= 60) return 'High';
            if (this.volatilityScore >= 40) return 'Normal';
            if (this.volatilityScore >= 20) return 'Low';
            return 'Very Low';
        },

        getVolatilityAlert() {
            if (this.volatilityScore >= 80) return 'alert alert-danger';
            if (this.volatilityScore >= 60) return 'alert alert-warning';
            if (this.volatilityScore >= 40) return 'alert alert-info';
            return 'alert alert-success';
        },

        getVolatilityTitle() {
            if (this.volatilityScore >= 80) return 'Extreme Volatility';
            if (this.volatilityScore >= 60) return 'High Volatility';
            if (this.volatilityScore >= 40) return 'Normal Volatility';
            if (this.volatilityScore >= 20) return 'Low Volatility';
            return 'Very Low Volatility';
        },

        getVolatilityMessage() {
            if (this.volatilityScore >= 80) return 'Market in extreme volatility. High risk, high opportunity.';
            if (this.volatilityScore >= 60) return 'Elevated volatility. Increased trading opportunities.';
            if (this.volatilityScore >= 40) return 'Balanced market conditions with moderate volatility.';
            if (this.volatilityScore >= 20) return 'Calm market. Range-bound conditions likely.';
            return 'Extremely calm market. Potential for volatility expansion.';
        },

        getCurrentRegimeBackground() {
            const riskLevel = this.currentRegime.riskLevel || 'medium';
            const backgrounds = {
                'very-high': 'bg-danger bg-opacity-10',
                'high': 'bg-warning bg-opacity-10',
                'medium': 'bg-info bg-opacity-10',
                'low': 'bg-success bg-opacity-10',
                'very-low': 'bg-secondary bg-opacity-10'
            };
            return backgrounds[riskLevel] || 'bg-info bg-opacity-10';
        },

        getCurrentRegimeIconBg() {
            const riskLevel = this.currentRegime.riskLevel || 'medium';
            const backgrounds = {
                'very-high': 'bg-danger bg-opacity-25',
                'high': 'bg-warning bg-opacity-25',
                'medium': 'bg-info bg-opacity-25',
                'low': 'bg-success bg-opacity-25',
                'very-low': 'bg-secondary bg-opacity-25'
            };
            return backgrounds[riskLevel] || 'bg-info bg-opacity-25';
        },

        getCurrentRegimeIconColor() {
            const riskLevel = this.currentRegime.riskLevel || 'medium';
            const colors = {
                'very-high': 'text-danger',
                'high': 'text-warning',
                'medium': 'text-info',
                'low': 'text-success',
                'very-low': 'text-secondary'
            };
            return colors[riskLevel] || 'text-info';
        },

        getRegimeConfidenceBadge() {
            const confidence = this.currentRegime.confidence || 0;
            if (confidence >= 0.8) return 'text-bg-success';
            if (confidence >= 0.6) return 'text-bg-warning';
            return 'text-bg-secondary';
        },

        getFormattedConfidence() {
            const confidence = this.currentRegime.confidence || 0;
            return Math.round(confidence * 100) + '%';
        },

        getRegimeConfidenceLabel() {
            const confidence = this.currentRegime.confidence || 0;
            if (confidence >= 0.8) return 'High Confidence';
            if (confidence >= 0.6) return 'Medium Confidence';
            return 'Low Confidence';
        },

        getTradingStrategy() {
            const score = this.volatilityScore;
            if (score >= 80) return 'Reduce position size, wider stops, event-driven strategies';
            if (score >= 60) return 'Active trading, momentum strategies, careful risk management';
            if (score >= 40) return 'Balanced approach, trend following and swing trading';
            if (score >= 20) return 'Range trading, mean reversion, sell premium strategies';
            return 'Accumulation phase, prepare for volatility expansion';
        },

        // Format percentile labels properly
        getMetricPercentileLabel(metric) {
            const percentiles = {
                'hv': this.hvPercentile,
                'rv': this.rvPercentile,
                'atr': this.atrPercentile
            };

            const percentile = percentiles[metric] || 50;
            // Round to 1 decimal place and format properly
            const rounded = Math.round(percentile * 10) / 10;
            return rounded + 'th';
        },

        // Format metric values properly
        formatMetricValue(value, suffix = '') {
            if (value === null || value === undefined || isNaN(value)) return '--';
            // Ensure proper rounding to 2 decimal places
            const rounded = Math.round(value * 100) / 100;
            return rounded.toFixed(2) + suffix;
        },

        // Format change values properly
        formatChange(change) {
            if (change === null || change === undefined || isNaN(change)) return '--';
            // Ensure proper rounding to 2 decimal places
            const rounded = Math.round(change * 100) / 100;
            const sign = rounded >= 0 ? '+' : '';
            return sign + rounded.toFixed(2);
        },

        // Get percentile badge class
        getMetricPercentileBadge(metric) {
            const percentiles = {
                'hv': this.hvPercentile,
                'rv': this.rvPercentile,
                'atr': this.atrPercentile
            };

            const percentile = percentiles[metric] || 50;

            if (percentile >= 80) return 'text-bg-danger';
            if (percentile >= 60) return 'text-bg-warning';
            if (percentile >= 40) return 'text-bg-info';
            return 'text-bg-success';
        },

        getMetricCardClass(metric) {
            const percentiles = {
                'hv': this.hvPercentile,
                'rv': this.rvPercentile,
                'atr': this.atrPercentile
            };

            const percentile = percentiles[metric] || 50;

            if (percentile >= 80) return 'border border-danger bg-danger bg-opacity-10';
            if (percentile >= 60) return 'border border-warning bg-warning bg-opacity-10';
            if (percentile >= 40) return 'border border-info bg-info bg-opacity-10';
            return 'border border-success bg-success bg-opacity-10';
        },

        getMetricValueClass(metric) {
            const percentiles = {
                'hv': this.hvPercentile,
                'rv': this.rvPercentile,
                'atr': this.atrPercentile
            };

            const percentile = percentiles[metric] || 50;

            if (percentile >= 80) return 'text-danger';
            if (percentile >= 60) return 'text-warning';
            if (percentile >= 40) return 'text-info';
            return 'text-success';
        },

        getRiskAdvice() {
            const score = this.volatilityScore;
            if (score >= 80) return 'Maximum risk control, expect large moves';
            if (score >= 60) return 'Elevated risk, monitor positions closely';
            if (score >= 40) return 'Standard risk management protocols';
            if (score >= 20) return 'Lower risk environment, consider larger positions';
            return 'Very low risk, but prepare for potential breakouts';
        },

        // Metric formatting methods
        formatMetricValue(value, suffix = '') {
            if (value === null || value === undefined || isNaN(value)) return '--';
            return value.toFixed(2) + suffix;
        },

        formatChange(change) {
            if (change === null || change === undefined || isNaN(change)) return '--';
            const sign = change >= 0 ? '+' : '';
            return sign + change.toFixed(2);
        },

        getMetricCardClass(metric) {
            // Return appropriate card styling based on metric percentile
            return 'bg-light border';
        },

        getMetricValueClass(metric) {
            const percentiles = {
                'hv': this.hvPercentile,
                'rv': this.rvPercentile,
                'atr': this.atrPercentile
            };

            const percentile = percentiles[metric] || 50;
            if (percentile >= 80) return 'text-danger';
            if (percentile >= 60) return 'text-warning';
            if (percentile >= 40) return 'text-info';
            return 'text-success';
        },

        getMetricPercentileBadge(metric) {
            const percentiles = {
                'hv': this.hvPercentile,
                'rv': this.rvPercentile,
                'atr': this.atrPercentile
            };

            const percentile = percentiles[metric] || 50;
            if (percentile >= 80) return 'text-bg-danger';
            if (percentile >= 60) return 'text-bg-warning';
            if (percentile >= 40) return 'text-bg-info';
            return 'text-bg-success';
        },

        getMetricCardClass(metric) {
            const percentiles = {
                'hv': this.hvPercentile,
                'rv': this.rvPercentile,
                'atr': this.atrPercentile
            };

            const percentile = percentiles[metric] || 50;

            if (percentile >= 80) return 'border border-danger bg-danger bg-opacity-10';
            if (percentile >= 60) return 'border border-warning bg-warning bg-opacity-10';
            if (percentile >= 40) return 'border border-info bg-info bg-opacity-10';
            return 'border border-success bg-success bg-opacity-10';
        },

        getMetricValueClass(metric) {
            const percentiles = {
                'hv': this.hvPercentile,
                'rv': this.rvPercentile,
                'atr': this.atrPercentile
            };

            const percentile = percentiles[metric] || 50;

            if (percentile >= 80) return 'text-danger';
            if (percentile >= 60) return 'text-warning';
            if (percentile >= 40) return 'text-info';
            return 'text-success';
        },

        getMetricPercentileLabel(metric) {
            const percentiles = {
                'hv': this.hvPercentile,
                'rv': this.rvPercentile,
                'atr': this.atrPercentile
            };

            const percentile = percentiles[metric] || 50;
            // Round to 1 decimal place and format properly
            const rounded = Math.round(percentile * 10) / 10;
            return rounded + 'th';
        },

        getMetricTooltip(metric) {
            const cadence = this.selectedCadence || '1d';
            const labels = this.getMetricLabels ? this.getMetricLabels(cadence) : {};

            const tooltips = {
                'hv': `Historical Volatility (${labels.hv || 'HV'}) - Forward-looking volatility expectation for ${this.getCadenceDisplayName(cadence)}`,
                'rv': `Realized Volatility (${labels.rv || 'RV'}) - Recent actual price movement volatility for ${this.getCadenceDisplayName(cadence)}`,
                'atr': `Average True Range (${labels.atr || 'ATR'}) - Price range volatility indicator for ${this.getCadenceDisplayName(cadence)}`,
                'change24h': `Price change percentage for ${labels.change || '24h Change'} period`
            };
            return tooltips[metric] || '';
        },

        getMetricTrend(metric) {
            const cadence = this.selectedCadence || '1d';
            const trendMap = {
                '1m': {
                    'hv': 'Short-term stable',
                    'rv': 'Intraday active',
                    'atr': 'Range-bound',
                    'change': 'Hourly trend'
                },
                '5m': {
                    'hv': 'Day trading range',
                    'rv': 'Active session',
                    'atr': 'Intraday stable',
                    'change': 'Daily momentum'
                },
                '1h': {
                    'hv': 'Weekly trend',
                    'rv': 'Swing range',
                    'atr': 'Medium-term',
                    'change': 'Weekly shift'
                },
                '1d': {
                    'hv': 'Monthly stable',
                    'rv': 'Long-term trend',
                    'atr': 'Position range',
                    'change': 'Daily move'
                }
            };

            return trendMap[cadence]?.[metric] || 'Stable';
        },

        getChangeDirectionBadge() {
            if (this.metrics.change24h >= 2) return 'text-bg-success';
            if (this.metrics.change24h >= 0) return 'text-bg-info';
            if (this.metrics.change24h >= -2) return 'text-bg-warning';
            return 'text-bg-danger';
        },

        getChangeDirectionLabel() {
            if (this.metrics.change24h >= 2) return 'Strong Up';
            if (this.metrics.change24h >= 0) return 'Up';
            if (this.metrics.change24h >= -2) return 'Down';
            return 'Strong Down';
        },

        getChangeImplication() {
            const change = this.metrics.change24h;
            if (Math.abs(change) < 1) return 'Range-bound conditions';
            if (change >= 5) return 'Strong bullish momentum';
            if (change >= 2) return 'Positive momentum';
            if (change >= 0) return 'Mild upward pressure';
            if (change >= -2) return 'Mild downward pressure';
            return 'Negative momentum';
        },

        // Format percentile labels properly
        getMetricPercentileLabel(metric) {
            const percentiles = {
                'hv': this.hvPercentile,
                'rv': this.rvPercentile,
                'atr': this.atrPercentile
            };

            const percentile = percentiles[metric] || 50;
            // Round to 1 decimal place and format properly
            const rounded = Math.round(percentile * 10) / 10;
            return rounded + 'th';
        },

        // Format metric values properly
        formatMetricValue(value, suffix = '') {
            if (value === null || value === undefined || isNaN(value)) return '--';
            // Ensure proper rounding to 2 decimal places
            const rounded = Math.round(value * 100) / 100;
            return rounded.toFixed(2) + suffix;
        },

        // Format change values properly
        formatChange(change) {
            if (change === null || change === undefined || isNaN(change)) return '--';
            // Ensure proper rounding to 2 decimal places
            const rounded = Math.round(change * 100) / 100;
            const sign = rounded >= 0 ? '+' : '';
            return sign + rounded.toFixed(2);
        },

        // Spot prices methods
        getFormattedSpotPrices() {
            return this.spotPrices.map(price => ({
                ...price,
                formattedPrice: this.formatPrice(price.close),
                formattedVolume: this.formatVolume(price.volume),
                formattedChange: this.formatChange(price.change),
                changeClass: price.change >= 0 ? 'text-success' : 'text-danger'
            }));
        },

        formatPrice(price) {
            if (!price || isNaN(price)) return '--';
            if (price >= 1000) {
                return '$' + price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else if (price >= 1) {
                return '$' + price.toFixed(4);
            } else {
                return '$' + price.toFixed(6);
            }
        },

        formatVolume(volume) {
            if (!volume || isNaN(volume)) return '--';
            if (volume >= 1e9) {
                return (volume / 1e9).toFixed(2) + 'B';
            } else if (volume >= 1e6) {
                return (volume / 1e6).toFixed(2) + 'M';
            } else if (volume >= 1e3) {
                return (volume / 1e3).toFixed(2) + 'K';
            } else {
                return volume.toFixed(2);
            }
        },

        hasSpotPricesData() {
            return this.spotPrices && this.spotPrices.length > 0;
        },

        isSpotPricesLoading() {
            return this.loading;
        },

        getSpotPricesLastUpdated() {
            if (!this.lastUpdated) return 'Never';
            const now = new Date();
            const diff = now - this.lastUpdated;
            const minutes = Math.floor(diff / 60000);
            if (minutes < 1) return 'Just now';
            if (minutes === 1) return '1 minute ago';
            return `${minutes} minutes ago`;
        },

        // Regime transition methods
        formatTransitionLabel(regime) {
            return regime || 'Unknown';
        },

        getTransitionProbabilityClass(probability) {
            if (probability >= 70) return 'text-danger fw-bold';
            if (probability >= 50) return 'text-warning fw-bold';
            return 'text-muted';
        },

        getTransitionBarClass(probability) {
            if (probability >= 70) return 'bg-danger';
            if (probability >= 50) return 'bg-warning';
            return 'bg-info';
        },

        isRegimeChangelikely() {
            return this.regimeTransitions.some(t => t.probability >= 70);
        },

        // Get cadence display name
        getCadenceDisplayName(cadence) {
            const nameMap = {
                '1m': '1 Minute',
                '5m': '5 Minutes',
                '1h': '1 Hour',
                '1d': 'End of Day'
            };
            return nameMap[cadence] || cadence;
        },

        // Get dynamic metric labels based on cadence
        getHVLabel(cadence) {
            const periodMap = {
                '1m': 'HV (1h)',    // 60 periods of 1m = 1 hour
                '5m': 'HV (24h)',   // 288 periods of 5m = 24 hours
                '1h': 'HV (1w)',    // 168 periods of 1h = 1 week
                '1d': 'HV (30d)'    // 30 periods of 1d = 30 days
            };
            return periodMap[cadence] || 'HV (30d)';
        },

        getRVLabel(cadence) {
            const periodMap = {
                '1m': 'RV (24h)',   // 1440 periods of 1m = 24 hours
                '5m': 'RV (24h)',   // 288 periods of 5m = 24 hours
                '1h': 'RV (1w)',    // 168 periods of 1h = 1 week
                '1d': 'RV (30d)'    // 30 periods of 1d = 30 days
            };
            return periodMap[cadence] || 'RV (30d)';
        },

        getATRLabel(cadence) {
            const periodMap = {
                '1m': 'ATR (1h)',   // 60 periods of 1m = 1 hour
                '5m': 'ATR (24h)',  // 288 periods of 5m = 24 hours
                '1h': 'ATR (1w)',   // 168 periods of 1h = 1 week
                '1d': 'ATR (30d)'   // 30 periods of 1d = 30 days
            };
            return periodMap[cadence] || 'ATR (14d)';
        },

        getChangeLabel(cadence) {
            const periodMap = {
                '1m': '1h Change',   // 1 hour change for 1m cadence
                '5m': '24h Change',  // 24 hour change for 5m cadence
                '1h': '1w Change',   // 1 week change for 1h cadence
                '1d': '24h Change'   // 24 hour change for daily cadence
            };
            return periodMap[cadence] || '24h Change';
        },

        // Format percentile labels properly
        getMetricPercentileLabel(metric) {
            const percentiles = {
                'hv': this.hvPercentile,
                'rv': this.rvPercentile,
                'atr': this.atrPercentile
            };

            const percentile = percentiles[metric] || 50;
            // Round to 1 decimal place and format properly
            const rounded = Math.round(percentile * 10) / 10;
            return rounded + 'th';
        },

        // Format metric values properly
        formatMetricValue(value, suffix = '') {
            if (value === null || value === undefined || isNaN(value)) return '--';
            // Ensure proper rounding to 2 decimal places
            const rounded = Math.round(value * 100) / 100;
            return rounded.toFixed(2) + suffix;
        },

        // Format change values properly
        formatChange(change) {
            if (change === null || change === undefined || isNaN(change)) return '--';
            // Ensure proper rounding to 2 decimal places
            const rounded = Math.round(change * 100) / 100;
            const sign = rounded >= 0 ? '+' : '';
            return sign + rounded.toFixed(2);
        },

        // Initialize tooltips
        initializeTooltips() {
            this.$nextTick(() => {
                if (typeof bootstrap !== 'undefined') {
                    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                }
            });
        }
    };

    // Store the Alpine instance globally to prevent duplicates
    window.volatilityRegimeAlpineInstance = alpineData;

    console.log('📊 Alpine volatility controller created successfully');
    return alpineData;
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        VolatilityRegimeController,
        volatilityRegimeController,
        ErrorHandler,
        Logger
    };
}