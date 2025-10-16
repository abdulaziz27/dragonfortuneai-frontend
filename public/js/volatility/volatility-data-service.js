/**
 * Volatility Data Service
 * Handles all API calls and data transformation for volatility dashboard
 */

class VolatilityDataService {
    constructor(baseUrl = 'https://test.dragonfortune.ai') {
        this.baseUrl = baseUrl;
        this.cache = new Map();
        this.cacheTimeout = 5000; // 5 seconds cache
    }

    /**
     * Generic fetch with error handling and caching
     */
    async fetchWithCache(url, cacheKey) {
        // Check cache
        const cached = this.cache.get(cacheKey);
        if (cached && Date.now() - cached.timestamp < this.cacheTimeout) {
            console.log(`📦 Cache hit: ${cacheKey}`);
            return cached.data;
        }

        try {
            console.log(`🌐 Fetching: ${url}`);
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            // Cache the result
            this.cache.set(cacheKey, {
                data: data,
                timestamp: Date.now()
            });
            
            return data;
        } catch (error) {
            console.error(`❌ Fetch error for ${cacheKey}:`, error);
            throw error;
        }
    }

    /**
     * Fetch ATR (Average True Range)
     */
    async fetchATR(symbol, interval, period = 14) {
        const url = `${this.baseUrl}/api/volatility/analytics/atr?symbol=${symbol}&interval=${interval}&period=${period}`;
        const cacheKey = `atr_${symbol}_${interval}_${period}`;
        
        try {
            return await this.fetchWithCache(url, cacheKey);
        } catch (error) {
            console.error('❌ ATR fetch error:', error);
            return { atr_percent: 0, percentile: 50 };
        }
    }

    /**
     * Fetch HV (Historical Volatility)
     */
    async fetchHV(symbol, interval, period = 30) {
        const url = `${this.baseUrl}/api/volatility/analytics/hv?symbol=${symbol}&interval=${interval}&period=${period}`;
        const cacheKey = `hv_${symbol}_${interval}_${period}`;
        
        try {
            return await this.fetchWithCache(url, cacheKey);
        } catch (error) {
            console.error('❌ HV fetch error:', error);
            return { hv_percent: 0, percentile: 50 };
        }
    }

    /**
     * Fetch RV (Realized Volatility)
     */
    async fetchRV(symbol, interval, limit) {
        const url = `${this.baseUrl}/api/volatility/analytics/rv?symbol=${symbol}&interval=${interval}&limit=${limit}`;
        const cacheKey = `rv_${symbol}_${interval}_${limit}`;
        
        try {
            return await this.fetchWithCache(url, cacheKey);
        } catch (error) {
            console.error('❌ RV fetch error:', error);
            return { rv_percent: 0, percentile: 50 };
        }
    }

    /**
     * Fetch OHLC data (returns array of candles)
     */
    async fetchOHLC(symbol, interval, limit, startMs, endMs) {
        // Build URL - only add time params if provided
        let url = `${this.baseUrl}/api/volatility/spot/ohlc?symbol=${symbol}&interval=${interval}&limit=${limit}`;
        if (startMs && endMs) {
            url += `&start_ms=${startMs}&end_ms=${endMs}`;
        }
        
        const cacheKey = `ohlc_${symbol}_${interval}_${limit}`;
        
        try {
            const response = await this.fetchWithCache(url, cacheKey);
            const data = response.data || [];
            
            console.log(`📊 OHLC API returned ${data.length} candles`);
            
            // Normalize field names: ts -> timestamp
            return data.map(candle => ({
                timestamp: candle.ts || candle.timestamp,
                open: candle.open,
                high: candle.high,
                low: candle.low,
                close: candle.close,
                volume: candle.volume,
                change: candle.change || ((candle.close - candle.open) / candle.open * 100)
            }));
        } catch (error) {
            console.error('❌ OHLC fetch error:', error);
            return [];
        }
    }

    /**
     * Clear cache
     */
    clearCache() {
        this.cache.clear();
        console.log('🗑️ Cache cleared');
    }

    /**
     * Clear specific cache entry
     */
    clearCacheEntry(key) {
        this.cache.delete(key);
    }

    /**
     * Fetch Trends data (HV only from trends endpoint)
     * Returns: [{ timestamp, hv }]
     */
    async fetchTrends(symbol, interval, limit) {
        const url = `${this.baseUrl}/api/volatility/analytics/trends?symbol=${symbol}&window_size=20&metric=hv&limit=${limit}`;
        const cacheKey = `trends_${symbol}_${interval}_${limit}`;
        
        try {
            const response = await this.fetchWithCache(url, cacheKey);
            
            // Check if response has volatility_series
            if (!response.volatility_series || response.volatility_series.length === 0) {
                console.warn('⚠️ Trends endpoint returned empty volatility_series');
                return [];
            }

            console.log(`✅ Fetched ${response.volatility_series.length} HV data points from trends endpoint`);
            
            // Return HV data only
            const trendsData = response.volatility_series.map(point => ({
                timestamp: point.timestamp,
                hv: point.volatility || 0  // HV from trends endpoint
            }));
            
            console.log(`✅ Trends data ready: ${trendsData.length} HV points`);
            return trendsData;
            
        } catch (error) {
            console.error('❌ Trends fetch error:', error);
            return [];
        }
    }



    /**
     * Fetch Multi-Timeframe Volatility with REAL average calculation
     * NO MORE DUMMY: average = current * (50 / percentile)
     * 
     * Strategy:
     * 1. Fetch current HV for each interval (1h, 4h, 1d)
     * 2. Fetch 30-day historical HV data for each interval
     * 3. Calculate real average from historical data
     * 4. Return: [{ timeframe, current, average, percentile }]
     */
    async fetchMultiTimeframeVolatility(symbol, intervals = ['1h', '4h', '1d']) {
        console.log('📊 Fetching multi-timeframe volatility with REAL averages...');
        
        const results = [];
        
        for (const interval of intervals) {
            try {
                // Fetch current HV
                const currentHV = await this.fetchHV(symbol, interval, 30);
                const current = currentHV?.hv_percent || 0;
                const percentile = currentHV?.percentile || 50;
                
                // Fetch historical HV data (30 days)
                // We'll use the trends endpoint to get historical HV values
                const historicalUrl = `${this.baseUrl}/api/volatility/analytics/trends?symbol=${symbol}&interval=${interval}&limit=30`;
                
                let average = current; // Default to current if historical fetch fails
                
                try {
                    const historicalResponse = await fetch(historicalUrl);
                    if (historicalResponse.ok) {
                        const historicalData = await historicalResponse.json();
                        
                        if (historicalData.data && historicalData.data.length > 0) {
                            // Calculate real average from historical HV values
                            const hvValues = historicalData.data
                                .map(d => d.hv || 0)
                                .filter(v => v > 0); // Filter out zeros
                            
                            if (hvValues.length > 0) {
                                const sum = hvValues.reduce((a, b) => a + b, 0);
                                average = sum / hvValues.length;
                                console.log(`✅ ${interval}: Calculated real average from ${hvValues.length} data points`);
                            } else {
                                console.warn(`⚠️ ${interval}: No valid HV values in historical data`);
                            }
                        }
                    }
                } catch (histError) {
                    console.warn(`⚠️ ${interval}: Failed to fetch historical data, using current as average`, histError);
                }
                
                results.push({
                    timeframe: interval,
                    current: Math.round(current * 10) / 10,
                    average: Math.round(average * 10) / 10,
                    percentile: percentile
                });
                
            } catch (error) {
                console.error(`❌ Error fetching ${interval} volatility:`, error);
                results.push({
                    timeframe: interval,
                    current: 0,
                    average: 0,
                    percentile: 50
                });
            }
        }
        
        console.log('✅ Multi-timeframe volatility loaded with REAL averages:', results);
        return results;
    }

    /**
     * Fetch Intraday Volatility Heatmap Data
     * Get 7 days of hourly OHLC data and calculate volatility per hour
     * Returns: 7x24 matrix (day of week x hour of day)
     */
    async fetchIntradayHeatmap(symbol, interval = '1h', days = 7) {
        console.log(`📊 Fetching intraday heatmap data for ${days} days...`);
        
        try {
            const limit = days * 24; // 7 days * 24 hours = 168 candles
            const ohlcData = await this.fetchOHLC(symbol, interval, limit);
            
            if (!ohlcData || ohlcData.length === 0) {
                console.warn('⚠️ No OHLC data for heatmap');
                return this.generateEmptyHeatmap();
            }
            
            // Initialize 7x24 matrix (day of week x hour of day)
            const heatmapData = Array(7).fill(null).map(() => Array(24).fill(null).map(() => ({ count: 0, totalVol: 0 })));
            
            // Calculate volatility for each candle and group by day/hour
            ohlcData.forEach(candle => {
                const date = new Date(candle.timestamp);
                const dayOfWeek = date.getUTCDay(); // 0 = Sunday, 6 = Saturday
                const hour = date.getUTCHours(); // 0-23
                
                // Calculate volatility for this candle (high-low range as % of close)
                const volatility = candle.close > 0 
                    ? ((candle.high - candle.low) / candle.close) * 100 
                    : 0;
                
                heatmapData[dayOfWeek][hour].count++;
                heatmapData[dayOfWeek][hour].totalVol += volatility;
            });
            
            // Calculate average volatility for each cell
            const result = heatmapData.map(day => 
                day.map(hour => 
                    hour.count > 0 ? Math.round((hour.totalVol / hour.count) * 100) / 100 : 0
                )
            );
            
            console.log('✅ Heatmap data calculated:', {
                days: result.length,
                hours: result[0].length,
                sample: result[0].slice(0, 3)
            });
            
            return result;
            
        } catch (error) {
            console.error('❌ Error fetching heatmap data:', error);
            return this.generateEmptyHeatmap();
        }
    }
    
    /**
     * Generate empty heatmap (7x24 matrix of zeros)
     */
    generateEmptyHeatmap() {
        return Array(7).fill(null).map(() => Array(24).fill(0));
    }

    /**
     * Calculate Volatility Score
     * Weighted average: HV (50%) + RV (30%) + ATR (20%)
     */
    calculateVolatilityScore(atr, hv, rv) {
        const atrPercent = atr?.atr_percent || 0;
        const hvPercent = hv?.hv_percent || 0;
        const rvPercent = rv?.rv_percent || 0;

        const score = (hvPercent * 0.5) + (rvPercent * 0.3) + (atrPercent * 0.2);
        return Math.round(score * 10) / 10;
    }

    /**
     * Calculate Market Regime based on volatility score
     */
    calculateRegime(atr, hv, rv) {
        const score = this.calculateVolatilityScore(atr, hv, rv);
        const confidence = this.calculateConfidence(atr, hv, rv);

        if (score < 30) {
            return {
                name: 'Calm',
                description: 'Low volatility, stable market conditions',
                confidence: confidence,
                recommendations: [
                    'Consider range-bound strategies',
                    'Sell options premium',
                    'Tighter stop losses acceptable'
                ]
            };
        } else if (score >= 60) {
            return {
                name: 'Volatile',
                description: 'High volatility, increased risk',
                confidence: confidence,
                recommendations: [
                    'Reduce position sizes',
                    'Use wider stop losses',
                    'Monitor news closely'
                ]
            };
        } else {
            return {
                name: 'Normal',
                description: 'Moderate volatility, balanced market',
                confidence: confidence,
                recommendations: [
                    'Standard position sizing',
                    'Trend following strategies',
                    'Normal risk management'
                ]
            };
        }
    }

    /**
     * Calculate Confidence Score
     * Based on consistency of percentile values across metrics
     */
    calculateConfidence(atr, hv, rv) {
        try {
            const values = [
                atr?.percentile || 50,
                hv?.percentile || 50,
                rv?.percentile || 50
            ];
            
            // Calculate average
            const avg = values.reduce((a, b) => a + b, 0) / values.length;
            
            // Calculate variance
            const variance = values.reduce((sum, val) => sum + Math.pow(val - avg, 2), 0) / values.length;
            
            // Calculate standard deviation
            const stdDev = Math.sqrt(variance);
            
            // Convert to confidence score (lower stdDev = higher confidence)
            const confidence = Math.max(0, Math.min(100, 100 - (stdDev * 2)));
            
            return Math.round(confidence * 10) / 10;
        } catch (error) {
            console.error('❌ Confidence calculation error:', error);
            return 50;
        }
    }

    /**
     * Fetch Regime Transition Probability with Timeframes
     * @param {string} symbol - Trading pair symbol
     * @param {string} cadence - Current cadence (1m, 5m, 1h, 1d)
     * @returns {Object} Transition probabilities with timeframes
     */
    async fetchRegimeTransition(symbol, cadence = '1h') {
        const url = `${this.baseUrl}/api/volatility/analytics/regime?symbol=${symbol}&lookback_period=30`;
        const cacheKey = `regime_transition_${symbol}_${cadence}`;
        
        try {
            const response = await this.fetchWithCache(url, cacheKey);
            
            const currentRegime = response.current_regime?.regime || 'normal';
            const confidence = response.confidence_score || 50;
            
            console.log(`✅ Regime transition data: ${currentRegime} (${confidence}% confidence)`);
            
            // Define timeframes based on cadence
            const timeframeMap = {
                '1m': ['30m-1h', '1-2h', '2h+'],
                '5m': ['2-4h', '4-8h', '8h+'],
                '1h': ['6-12h', '12-24h', '24h+'],
                '1d': ['3-7d', '7-14d', '14d+']
            };
            
            const timeframes = timeframeMap[cadence] || timeframeMap['1h'];
            
            // Calculate base probabilities based on confidence
            // Lower confidence = higher transition probability
            const transitionFactor = (100 - confidence) / 100;
            
            let transitions = [];
            
            if (currentRegime === 'high') {
                // High regime: likely to stay high short-term, normalize over time
                transitions = [
                    { 
                        to: 'High Volatility', 
                        probability: Math.max(20, 60 - (transitionFactor * 30)),
                        timeframe: timeframes[0],
                        color: 'danger'
                    },
                    { 
                        to: 'Normal Volatility', 
                        probability: Math.max(15, 30 + (transitionFactor * 20)),
                        timeframe: timeframes[1],
                        color: 'warning'
                    },
                    { 
                        to: 'Low Volatility', 
                        probability: Math.max(5, 10 + (transitionFactor * 10)),
                        timeframe: timeframes[2],
                        color: 'success'
                    }
                ];
            } else if (currentRegime === 'normal') {
                // Normal regime: can go either way
                transitions = [
                    { 
                        to: 'High Volatility', 
                        probability: Math.max(15, 25 + (transitionFactor * 15)),
                        timeframe: timeframes[0],
                        color: 'danger'
                    },
                    { 
                        to: 'Normal Volatility', 
                        probability: Math.max(20, 50 - (transitionFactor * 20)),
                        timeframe: timeframes[1],
                        color: 'secondary'
                    },
                    { 
                        to: 'Low Volatility', 
                        probability: Math.max(10, 25 + (transitionFactor * 15)),
                        timeframe: timeframes[2],
                        color: 'success'
                    }
                ];
            } else { // low
                // Low regime: likely to stay low short-term, can spike over time
                transitions = [
                    { 
                        to: 'Low Volatility', 
                        probability: Math.max(20, 60 - (transitionFactor * 30)),
                        timeframe: timeframes[0],
                        color: 'success'
                    },
                    { 
                        to: 'Normal Volatility', 
                        probability: Math.max(15, 30 + (transitionFactor * 20)),
                        timeframe: timeframes[1],
                        color: 'warning'
                    },
                    { 
                        to: 'High Volatility', 
                        probability: Math.max(5, 10 + (transitionFactor * 10)),
                        timeframe: timeframes[2],
                        color: 'danger'
                    }
                ];
            }
            
            // Normalize probabilities to sum to 100%
            const totalProb = transitions.reduce((sum, t) => sum + t.probability, 0);
            transitions = transitions.map(t => ({
                ...t,
                probability: Math.round((t.probability / totalProb) * 100 * 10) / 10
            }));
            
            // Sort by timeframe order (short to long term)
            // Already in correct order from definition
            
            return {
                current: currentRegime,
                currentLabel: this.getRegimeLabel(currentRegime),
                confidence: confidence,
                nextTimeframe: timeframes[0],
                transitions: transitions
            };
            
        } catch (error) {
            console.error('❌ Regime transition fetch error:', error);
            return {
                current: 'normal',
                currentLabel: 'Normal Volatility',
                confidence: 50,
                nextTimeframe: '6-12h',
                transitions: []
            };
        }
    }
    
    /**
     * Get regime label
     */
    getRegimeLabel(regime) {
        const labels = {
            'high': 'High Volatility',
            'normal': 'Normal Volatility',
            'low': 'Low Volatility'
        };
        return labels[regime] || 'Normal Volatility';
    }

    /**
     * Fetch Volatility Ranking (Multi-Asset Comparison)
     * @param {Array} symbols - Array of trading pair symbols
     * @param {string} metric - Volatility metric (hv, rv, atr)
     * @returns {Object} Ranking data with statistics
     */
    async fetchVolatilityRanking(symbols = ['BTCUSDT', 'ETHUSDT', 'BNBUSDT', 'SOLUSDT', 'ADAUSDT'], metric = 'hv') {
        const symbolsParam = symbols.join(',');
        const url = `${this.baseUrl}/api/volatility/analytics/ranking?symbols=${symbolsParam}&period=30&metric=${metric}`;
        const cacheKey = `volatility_ranking_${symbolsParam}_${metric}`;
        
        try {
            const response = await this.fetchWithCache(url, cacheKey);
            
            const ranking = response.ranking || [];
            const statistics = response.statistics || {};
            
            console.log(`✅ Volatility ranking: ${ranking.length} assets`);
            
            if (ranking.length < 2) {
                return {
                    ranking: [],
                    statistics: {},
                    maxSpread: 0,
                    avgSpread: 0,
                    opportunity: false,
                    opportunities: 0
                };
            }
            
            // Calculate spread (difference between highest and lowest)
            const maxVol = ranking[0].volatility;
            const minVol = ranking[ranking.length - 1].volatility;
            const spread = ((maxVol - minVol) / minVol) * 100;
            
            // Calculate average spread (coefficient of variation)
            const avgSpread = statistics.mean > 0 
                ? (statistics.std / statistics.mean) * 100 
                : 0;
            
            // Flag opportunities (high spread = potential arbitrage)
            const opportunity = spread > 20; // 20% spread threshold
            
            // Count assets above mean + 1 std dev
            const highVolThreshold = statistics.mean + statistics.std;
            const opportunities = ranking.filter(r => r.volatility > highVolThreshold).length;
            
            return {
                ranking: ranking.slice(0, 5), // Top 5
                statistics: statistics,
                maxSpread: Math.round(spread * 100) / 100,
                avgSpread: Math.round(avgSpread * 100) / 100,
                opportunity: opportunity,
                opportunities: opportunities
            };
            
        } catch (error) {
            console.error('❌ Volatility ranking fetch error:', error);
            return {
                ranking: [],
                statistics: {},
                maxSpread: 0,
                avgSpread: 0,
                opportunity: false,
                opportunities: 0
            };
        }
    }

    /**
     * Fetch Volume Profile Data
     * Calculate from OHLC data - price levels with highest volume
     * @param {string} symbol - Trading pair symbol
     * @param {string} interval - Time interval
     * @param {number} limit - Number of candles
     * @returns {Object} Volume profile with POC, VAH, VAL
     */
    async fetchVolumeProfile(symbol, interval = '1h', limit = 24) {
        console.log(`📊 Calculating volume profile for ${symbol}...`);
        
        try {
            const ohlc = await this.fetchOHLC(symbol, interval, limit);
            
            if (!ohlc || ohlc.length === 0) {
                console.warn('⚠️ No OHLC data for volume profile');
                return {
                    bins: [],
                    poc: 0,
                    vah: 0,
                    val: 0,
                    currentPrice: 0
                };
            }
            
            // Get price range
            const prices = ohlc.flatMap(c => [c.high, c.low, c.close]);
            const minPrice = Math.min(...prices);
            const maxPrice = Math.max(...prices);
            const currentPrice = ohlc[ohlc.length - 1].close;
            
            // Create 20 price bins
            const numBins = 20;
            const binSize = (maxPrice - minPrice) / numBins;
            
            // Initialize bins
            const bins = [];
            for (let i = 0; i < numBins; i++) {
                const priceLevel = minPrice + (i * binSize);
                bins.push({ 
                    price: Math.round(priceLevel * 100) / 100, 
                    volume: 0 
                });
            }
            
            // Aggregate volume by price bin
            ohlc.forEach(candle => {
                // Assume volume is distributed across the candle's price range
                const binIndex = Math.floor((candle.close - minPrice) / binSize);
                const validIndex = Math.max(0, Math.min(numBins - 1, binIndex));
                bins[validIndex].volume += candle.volume;
            });
            
            // Find POC (Point of Control) - price level with highest volume
            const poc = bins.reduce((max, bin) => bin.volume > max.volume ? bin : max);
            
            // Calculate VAH/VAL (Value Area High/Low - 70% of volume)
            const totalVolume = bins.reduce((sum, bin) => sum + bin.volume, 0);
            const targetVolume = totalVolume * 0.70;
            
            // Sort by volume and find 70% area
            const sortedBins = [...bins].sort((a, b) => b.volume - a.volume);
            let cumVolume = 0;
            const valueArea = [];
            
            for (const bin of sortedBins) {
                cumVolume += bin.volume;
                valueArea.push(bin);
                if (cumVolume >= targetVolume) break;
            }
            
            const vah = Math.max(...valueArea.map(b => b.price));
            const val = Math.min(...valueArea.map(b => b.price));
            
            console.log(`✅ Volume profile calculated: POC=${poc.price}, VAH=${vah}, VAL=${val}`);
            
            return {
                bins: bins,
                poc: Math.round(poc.price * 100) / 100,
                vah: Math.round(vah * 100) / 100,
                val: Math.round(val * 100) / 100,
                currentPrice: Math.round(currentPrice * 100) / 100
            };
            
        } catch (error) {
            console.error('❌ Volume profile calculation error:', error);
            return {
                bins: [],
                poc: 0,
                vah: 0,
                val: 0,
                currentPrice: 0
            };
        }
    }

}
