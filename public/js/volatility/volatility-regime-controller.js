/**
 * Volatility Regime Controller (Refactored)
 * Main Alpine.js controller using modular data service and chart renderer
 */

function volatilityRegimeController() {
    return {
        // Dependencies
        dataService: null,
        chartRenderer: null,

        // State
        selectedPair: 'BTCUSDT',
        selectedCadence: '1h',
        selectedPeriod: '7D',
        loading: false,
        lastUpdated: '',
        autoRefreshEnabled: true,
        autoRefreshInterval: null,

        // Loading states per section
        loadingStates: {
            metrics: false,
            trends: false,
            ohlc: false,
            multiTimeframe: false,
            heatmap: false,
            regimeTransition: false,
            volatilityRanking: false,
            volumeProfile: false
        },

        // Error states per section
        errors: {
            metrics: null,
            trends: null,
            ohlc: null,
            multiTimeframe: null,
            heatmap: null,
            regimeTransition: null,
            volatilityRanking: null,
            volumeProfile: null
        },

        // Data
        volatilityScore: 0,
        metrics: {
            hv30: 0,
            rv30: 0,
            atr14: 0,
            change24h: 0
        },
        currentRegime: {
            name: 'Normal',
            description: 'Moderate volatility, balanced market',
            confidence: 50,
            recommendations: []
        },
        ohlcData: [],  // Changed from object to array for candlestick
        volatilityTrends: [],
        timeframeVolatility: [],
        heatmapData: [],  // 7x24 matrix for intraday heatmap
        
        // New sections data
        regimeTransitions: [],  // Regime transition probabilities
        regimeTransitionData: {}, // Full transition data with current regime
        volatilityRanking: {    // Multi-asset volatility comparison
            ranking: [],
            statistics: {},
            maxSpread: 0,
            avgSpread: 0,
            opportunity: false,
            opportunities: 0
        },
        volumeProfile: {        // Volume profile data
            bins: [],
            poc: 0,
            vah: 0,
            val: 0,
            currentPrice: 0
        },

        // Period options (cadence-aware)
        periodOptions: {
            '1m': [
                { value: '1H', label: '1 Hour', points: 60 },
                { value: '4H', label: '4 Hours', points: 240 },
                { value: '1D', label: '1 Day', points: 1440 },
                { value: '3D', label: '3 Days', points: 4320 },
                { value: '7D', label: '7 Days', points: 10080 }
            ],
            '5m': [
                { value: '4H', label: '4 Hours', points: 48 },
                { value: '1D', label: '1 Day', points: 288 },
                { value: '3D', label: '3 Days', points: 864 },
                { value: '7D', label: '7 Days', points: 2016 },
                { value: '14D', label: '14 Days', points: 4032 }
            ],
            '1h': [
                { value: '1D', label: '1 Day', points: 24 },
                { value: '3D', label: '3 Days', points: 72 },
                { value: '7D', label: '7 Days', points: 168 },
                { value: '14D', label: '14 Days', points: 336 },
                { value: '30D', label: '30 Days', points: 720 }
            ],
            '1d': [
                { value: '7D', label: '7 Days', points: 7 },
                { value: '14D', label: '14 Days', points: 14 },
                { value: '30D', label: '30 Days', points: 30 },
                { value: '60D', label: '60 Days', points: 60 },
                { value: '90D', label: '90 Days', points: 90 }
            ]
        },

        /**
         * Initialize controller
         */
        init() {
            console.log('📊 Volatility Regime Controller (Refactored) initialized');
            
            // Initialize dependencies
            this.dataService = new VolatilityDataService();
            this.chartRenderer = new VolatilityChartRenderer();
            
            console.log('✅ Dependencies initialized:', {
                dataService: !!this.dataService,
                chartRenderer: !!this.chartRenderer
            });
            
            // Load initial data
            this.loadAllData();
            
            // Start auto-refresh
            this.startAutoRefresh();
        },

        /**
         * Get current period options based on selected cadence
         */
        get currentPeriodOptions() {
            return this.periodOptions[this.selectedCadence] || this.periodOptions['1h'];
        },

        /**
         * Calculate limit parameter
         */
        calculateLimit() {
            const option = this.currentPeriodOptions.find(opt => opt.value === this.selectedPeriod);
            return option ? option.points : 168;
        },

        /**
         * Calculate time range for OHLC
         * Returns null to let backend use default range (more reliable)
         */
        calculateTimeRange() {
            // Don't calculate time range - let backend handle it
            // Backend will return most recent data based on limit
            return {
                start_ms: null,
                end_ms: null
            };
        },

        /**
         * Handle cadence change
         */
        async handleCadenceChange() {
            console.log(`📊 Cadence changed to: ${this.selectedCadence}`);

            // Reset period to default
            const defaults = { '1m': '1H', '5m': '1D', '1h': '7D', '1d': '30D' };
            this.selectedPeriod = defaults[this.selectedCadence] || '7D';

            // Destroy existing charts first
            this.chartRenderer.destroyAllCharts();
            
            // Clear cache to force fresh data
            this.dataService.clearCache();
            
            // Small delay to ensure charts are fully destroyed
            await new Promise(resolve => setTimeout(resolve, 100));

            await this.loadAllData();
        },

        /**
         * Handle period change
         */
        async handlePeriodChange() {
            console.log(`📊 Period changed to: ${this.selectedPeriod}`);
            
            // Destroy existing charts first
            this.chartRenderer.destroyAllCharts();
            
            // Clear cache to force fresh data
            this.dataService.clearCache();
            
            // Small delay to ensure charts are fully destroyed
            await new Promise(resolve => setTimeout(resolve, 100));
            
            await this.loadAllData();
        },

        /**
         * Handle pair change
         */
        async handlePairChange() {
            console.log(`📊 Pair changed to: ${this.selectedPair}`);
            
            // Destroy existing charts first
            this.chartRenderer.destroyAllCharts();
            
            // Clear cache to force fresh data
            this.dataService.clearCache();
            
            // Small delay to ensure charts are fully destroyed
            await new Promise(resolve => setTimeout(resolve, 100));
            
            await this.loadAllData();
        },

        /**
         * Cleanup on destroy
         */
        destroy() {
            this.stopAutoRefresh();
            this.chartRenderer.destroyAllCharts();
            console.log('📊 Controller destroyed');
        },

        /**
         * Load all data using data service
         */
        async loadAllData() {
            if (this.loading) return;

            this.loading = true;
            console.log('📊 Loading volatility data with new architecture...');

            // Clear errors
            this.errors = {
                metrics: null,
                trends: null,
                ohlc: null,
                multiTimeframe: null,
                heatmap: null,
                regimeTransition: null,
                volatilityRanking: null,
                volumeProfile: null
            };

            try {
                const limit = this.calculateLimit();
                const { start_ms, end_ms } = this.calculateTimeRange();

                // Fetch core metrics
                await this.loadMetrics(limit);

                // Fetch trends data (HV + RV)
                await this.loadTrends(limit);

                // Fetch OHLC data (array of candles)
                await this.loadOHLC(limit, start_ms, end_ms);

                // Fetch multi-timeframe volatility (with real averages)
                await this.loadMultiTimeframeVolatility();

                // Fetch intraday heatmap data
                await this.loadIntradayHeatmap();

                // Fetch new sections data
                await this.loadRegimeTransition();
                await this.loadVolatilityRanking();
                await this.loadVolumeProfile(limit);

                // Update last updated timestamp
                this.lastUpdated = new Date().toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                });

                console.log('✅ All data loaded successfully');
            } catch (error) {
                console.error('❌ Error loading data:', error);
            } finally {
                this.loading = false;
            }
        },

        /**
         * Load core metrics (ATR, HV, RV)
         */
        async loadMetrics(limit) {
            this.loadingStates.metrics = true;
            
            try {
                // Fetch all metrics in parallel
                const [atr, hv, rv] = await Promise.all([
                    this.dataService.fetchATR(this.selectedPair, this.selectedCadence, 14),
                    this.dataService.fetchHV(this.selectedPair, this.selectedCadence, 30),
                    this.dataService.fetchRV(this.selectedPair, this.selectedCadence, limit)
                ]);

                // Update metrics
                this.metrics = {
                    atr14: atr?.atr_percent || 0,
                    hv30: hv?.hv_percent || 0,
                    rv30: rv?.rv_percent || 0,
                    change24h: 0  // Will be updated from OHLC
                };

                // Calculate volatility score
                this.volatilityScore = this.dataService.calculateVolatilityScore(atr, hv, rv);

                // Calculate regime
                this.currentRegime = this.dataService.calculateRegime(atr, hv, rv);

                console.log('✅ Metrics loaded:', this.metrics);
                console.log('✅ Volatility score:', this.volatilityScore);
                console.log('✅ Current regime:', this.currentRegime.name);
            } catch (error) {
                console.error('❌ Error loading metrics:', error);
                this.errors.metrics = 'Failed to load volatility metrics';
            } finally {
                this.loadingStates.metrics = false;
            }
        },

        /**
         * Load trends data (HV + RV combined)
         */
        async loadTrends(limit) {
            this.loadingStates.trends = true;
            
            try {
                const trends = await this.dataService.fetchTrends(
                    this.selectedPair,
                    this.selectedCadence,
                    limit
                );

                this.volatilityTrends = trends;
                console.log(`✅ Trends loaded: ${trends.length} data points`);

                // Render chart immediately (chart renderer handles update vs create)
                try {
                    this.chartRenderer.renderVolatilityTrendChart('volatilityTrendChart', this.volatilityTrends);
                } catch (chartError) {
                    console.warn('⚠️ Chart render error (will retry next refresh):', chartError);
                    // Don't set error state - chart will retry on next refresh
                }
            } catch (error) {
                console.error('❌ Error loading trends:', error);
                this.errors.trends = 'Failed to load volatility trends';
            } finally {
                this.loadingStates.trends = false;
            }
        },

        /**
         * Load OHLC data (array of candles)
         */
        async loadOHLC(limit, startMs, endMs) {
            this.loadingStates.ohlc = true;
            
            try {
                const ohlcArray = await this.dataService.fetchOHLC(
                    this.selectedPair,
                    this.selectedCadence,
                    limit,
                    startMs,
                    endMs
                );

                this.ohlcData = ohlcArray;
                console.log(`✅ OHLC loaded: ${ohlcArray.length} candles`);

                // Update change24h from latest candle
                if (ohlcArray.length > 0) {
                    const latestCandle = ohlcArray[0];
                    this.metrics.change24h = latestCandle.change || 0;
                }

                // Render charts immediately (chart renderer handles update vs create)
                try {
                    this.chartRenderer.renderCandlestickChart('candlestickChart', this.ohlcData);
                    this.chartRenderer.renderVolumeChart('volumeChart', this.ohlcData);
                } catch (chartError) {
                    console.warn('⚠️ Chart render error (will retry next refresh):', chartError);
                    // Don't set error state - chart will retry on next refresh
                }
            } catch (error) {
                console.error('❌ Error loading OHLC:', error);
                this.errors.ohlc = 'Failed to load OHLC data';
            } finally {
                this.loadingStates.ohlc = false;
            }
        },

        /**
         * Load multi-timeframe volatility (with REAL averages)
         */
        async loadMultiTimeframeVolatility() {
            this.loadingStates.multiTimeframe = true;
            
            try {
                const multiTimeframe = await this.dataService.fetchMultiTimeframeVolatility(
                    this.selectedPair,
                    ['1h', '4h', '1d']
                );

                this.timeframeVolatility = multiTimeframe;
                console.log('✅ Multi-timeframe volatility loaded with REAL averages:', multiTimeframe);
            } catch (error) {
                console.error('❌ Error loading multi-timeframe:', error);
                this.errors.multiTimeframe = 'Failed to load multi-timeframe data';
            } finally {
                this.loadingStates.multiTimeframe = false;
            }
        },

        /**
         * Load intraday volatility heatmap
         */
        async loadIntradayHeatmap() {
            this.loadingStates.heatmap = true;
            
            try {
                const heatmapData = await this.dataService.fetchIntradayHeatmap(
                    this.selectedPair,
                    '1h',
                    7 // 7 days
                );

                this.heatmapData = heatmapData;
                console.log('✅ Heatmap data loaded:', heatmapData.length + 'x' + heatmapData[0].length);
                
                // Render heatmap chart
                try {
                    this.chartRenderer.renderIntradayHeatmap('intradayHeatmapChart', heatmapData);
                } catch (chartError) {
                    console.warn('⚠️ Heatmap chart render error:', chartError);
                }
            } catch (error) {
                console.error('❌ Error loading heatmap:', error);
                this.errors.heatmap = 'Failed to load heatmap data';
            } finally {
                this.loadingStates.heatmap = false;
            }
        },

        /**
         * Load Regime Transition Probability
         */
        async loadRegimeTransition() {
            this.loadingStates.regimeTransition = true;
            
            try {
                const transitionData = await this.dataService.fetchRegimeTransition(
                    this.selectedPair,
                    this.selectedCadence
                );
                this.regimeTransitions = transitionData.transitions || [];
                this.regimeTransitionData = transitionData; // Store full data
                console.log('✅ Regime transition data loaded:', this.regimeTransitions.length + ' transitions');
            } catch (error) {
                console.error('❌ Error loading regime transition:', error);
                this.errors.regimeTransition = 'Failed to load regime transition data';
            } finally {
                this.loadingStates.regimeTransition = false;
            }
        },

        /**
         * Load Volatility Ranking (Multi-Asset Comparison)
         */
        async loadVolatilityRanking() {
            this.loadingStates.volatilityRanking = true;
            
            try {
                const rankingData = await this.dataService.fetchVolatilityRanking(
                    ['BTCUSDT', 'ETHUSDT', 'BNBUSDT', 'SOLUSDT', 'ADAUSDT'],
                    'hv'
                );
                this.volatilityRanking = rankingData;
                console.log('✅ Volatility ranking loaded:', rankingData.ranking.length + ' assets');
            } catch (error) {
                console.error('❌ Error loading volatility ranking:', error);
                this.errors.volatilityRanking = 'Failed to load volatility ranking';
            } finally {
                this.loadingStates.volatilityRanking = false;
            }
        },

        /**
         * Load Volume Profile
         */
        async loadVolumeProfile(limit = 24) {
            this.loadingStates.volumeProfile = true;
            
            try {
                const profileData = await this.dataService.fetchVolumeProfile(
                    this.selectedPair,
                    this.selectedCadence,
                    limit
                );
                this.volumeProfile = profileData;
                console.log('✅ Volume profile loaded: POC=' + profileData.poc);
                
                // Render volume profile chart
                try {
                    this.chartRenderer.renderVolumeProfileChart('volumeProfileChart', profileData);
                } catch (chartError) {
                    console.warn('⚠️ Volume profile chart render error:', chartError);
                }
            } catch (error) {
                console.error('❌ Error loading volume profile:', error);
                this.errors.volumeProfile = 'Failed to load volume profile';
            } finally {
                this.loadingStates.volumeProfile = false;
            }
        },

        /**
         * Refresh all data manually
         */
        async refreshAll() {
            // Clear cache for fresh data
            this.dataService.clearCache();
            await this.loadAllData();
        },

        /**
         * Toggle auto-refresh
         */
        toggleAutoRefresh() {
            this.autoRefreshEnabled = !this.autoRefreshEnabled;
            if (this.autoRefreshEnabled) {
                this.startAutoRefresh();
                console.log('✅ Auto-refresh enabled');
            } else {
                this.stopAutoRefresh();
                console.log('⏸ Auto-refresh disabled');
            }
        },

        /**
         * Start auto-refresh
         */
        startAutoRefresh() {
            this.stopAutoRefresh();
            this.autoRefreshInterval = setInterval(async () => {
                if (this.autoRefreshEnabled && !this.loading) {
                    console.log('🔄 Auto-refresh triggered');
                    await this.loadAllData();
                }
            }, 5000);
        },

        /**
         * Stop auto-refresh
         */
        stopAutoRefresh() {
            if (this.autoRefreshInterval) {
                clearInterval(this.autoRefreshInterval);
                this.autoRefreshInterval = null;
            }
        },

        /**
         * Get cadence display name
         */
        getCadenceDisplayName(cadence) {
            const names = {
                '1m': '1 Minute',
                '5m': '5 Minutes',
                '1h': '1 Hour',
                '1d': 'End of Day'
            };
            return names[cadence] || cadence;
        },

        /**
         * Format number
         */
        formatNumber(num, decimals = 2) {
            if (!num) return '0.00';
            return Number(num).toFixed(decimals);
        },

        /**
         * UI Helper methods for volatility display
         */
        getVolatilityBadge() {
            if (this.volatilityScore < 30) return 'badge-success';
            if (this.volatilityScore < 60) return 'badge-warning';
            return 'badge-danger';
        },

        getVolatilityLabel() {
            if (this.volatilityScore < 30) return 'Calm';
            if (this.volatilityScore < 60) return 'Normal';
            return 'Volatile';
        },

        getVolatilityAlert() {
            if (this.volatilityScore < 30) return 'bg-success bg-opacity-10';
            if (this.volatilityScore < 60) return 'bg-warning bg-opacity-10';
            return 'bg-danger bg-opacity-10';
        },

        getVolatilityTitle() {
            if (this.volatilityScore < 30) return 'Low Volatility';
            if (this.volatilityScore < 60) return 'Moderate Volatility';
            return 'High Volatility';
        },

        getVolatilityMessage() {
            if (this.volatilityScore < 30) return 'Market is stable with low price fluctuations';
            if (this.volatilityScore < 60) return 'Market showing normal volatility levels';
            return 'Market experiencing high volatility - exercise caution';
        },

        /**
         * UI Helper methods for regime display
         */
        getCurrentRegimeBackground() {
            const regime = this.currentRegime.name;
            if (regime === 'Calm') return 'bg-success bg-opacity-10';
            if (regime === 'Volatile') return 'bg-danger bg-opacity-10';
            return 'bg-warning bg-opacity-10';
        },

        getCurrentRegimeIconBg() {
            const regime = this.currentRegime.name;
            if (regime === 'Calm') return 'bg-success bg-opacity-25';
            if (regime === 'Volatile') return 'bg-danger bg-opacity-25';
            return 'bg-warning bg-opacity-25';
        },

        getCurrentRegimeIconColor() {
            const regime = this.currentRegime.name;
            if (regime === 'Calm') return 'text-success';
            if (regime === 'Volatile') return 'text-danger';
            return 'text-warning';
        },

        getRegimeConfidenceBadge() {
            const conf = this.currentRegime.confidence;
            if (conf >= 70) return 'badge-success';
            if (conf >= 40) return 'badge-warning';
            return 'badge-danger';
        },

        getFormattedConfidence() {
            return this.formatNumber(this.currentRegime.confidence, 1) + '%';
        },

        getRegimeConfidenceLabel() {
            const conf = this.currentRegime.confidence;
            if (conf >= 70) return 'High Confidence';
            if (conf >= 40) return 'Medium Confidence';
            return 'Low Confidence';
        },

        getTradingStrategy() {
            return this.currentRegime.recommendations[0] || 'Standard trading approach';
        },

        getRiskAdvice() {
            return this.currentRegime.recommendations[1] || 'Normal risk management';
        },

        /**
         * UI Helper methods for metrics display
         */
        getMetricCardClass() {
            return 'bg-light';
        },

        getMetricTooltip(metric) {
            const tooltips = {
                'hv': 'Historical Volatility - measures past price fluctuations',
                'rv': 'Realized Volatility - actual volatility observed',
                'atr': 'Average True Range - measures market volatility',
                'change24h': 'Price change percentage'
            };
            return tooltips[metric] || '';
        },

        getMetricPercentileBadge() {
            return 'badge-secondary';
        },

        getMetricPercentileLabel() {
            return 'P50';
        },

        getMetricValueClass() {
            return 'text-primary';
        },

        formatMetricValue(value, suffix = '') {
            return this.formatNumber(value, 2) + suffix;
        },

        getMetricTrend() {
            return 'Stable';
        },

        getHVLabel(cadence) {
            const labels = {
                '1m': 'HV (1h)',
                '5m': 'HV (24h)',
                '1h': 'HV (1w)',
                '1d': 'HV (30d)'
            };
            return labels[cadence] || 'HV (30d)';
        },

        getRVLabel(cadence) {
            const labels = {
                '1m': 'RV (1h)',
                '5m': 'RV (24h)',
                '1h': 'RV (1w)',
                '1d': 'RV (30d)'
            };
            return labels[cadence] || 'RV (30d)';
        },

        getATRLabel(cadence) {
            const labels = {
                '1m': 'ATR (1h)',
                '5m': 'ATR (24h)',
                '1h': 'ATR (1w)',
                '1d': 'ATR (14d)'
            };
            return labels[cadence] || 'ATR (14)';
        },

        getChangeLabel(cadence) {
            const labels = {
                '1m': '1h Change',
                '5m': '24h Change',
                '1h': '1w Change',
                '1d': '24h Change'
            };
            return labels[cadence] || '24h Change';
        },

        getChangeDirectionBadge() {
            return this.metrics.change24h >= 0 ? 'badge-success' : 'badge-danger';
        },

        getChangeDirectionLabel() {
            return this.metrics.change24h >= 0 ? 'Up' : 'Down';
        },

        getChangeImplication() {
            if (Math.abs(this.metrics.change24h) < 1) return 'Minimal movement';
            if (Math.abs(this.metrics.change24h) < 3) return 'Moderate movement';
            return 'Significant movement';
        },

        formatChange(value) {
            const sign = value >= 0 ? '+' : '';
            return sign + this.formatNumber(value, 2);
        }
    };
}
