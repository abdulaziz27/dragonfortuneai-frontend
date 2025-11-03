/**
 * Funding Rate API Service
 * Handles all data fetching from internal API
 */

export class FundingRateAPIService {
    constructor() {
        this.baseUrl = window.APP_CONFIG?.apiBaseUrl || 'https://test.dragonfortune.ai';
        this.abortController = null; // For history
        this.analyticsAbortController = null; // For analytics
        this.exchangesAbortController = null; // For exchanges
        
        console.log('📡 API Service initialized with base URL:', this.baseUrl);
    }

    /**
     * Fetch historical funding rate data
     */
    async fetchHistory(params) {
        const { symbol, exchange, interval, limit, dateRange } = params;
        
        // Abort previous request if exists
        if (this.abortController) {
            this.abortController.abort();
        }
        this.abortController = new AbortController();

        // Calculate limit: use dateRange if provided, otherwise use limit param
        // For date range approach: request large limit to ensure we get all data in range
        // Then filter by timestamp in frontend
        let requestLimit = limit;
        if (dateRange && dateRange.startDate && dateRange.endDate) {
            // Request large limit to ensure we cover the entire date range
            // Better to request too much than too little
            requestLimit = 5000; // Large enough to cover any reasonable date range
        }

        const url = `${this.baseUrl}/api/funding-rate/history?` +
            `symbol=${symbol}&` +
            `exchange=${exchange}&` +
            `interval=${interval}&` +
            `limit=${requestLimit}`;

        console.log('📡 Fetching funding rate data:', url);
        
        const startTime = Date.now();
        if (dateRange) {
            console.log('📅 Date Range Filter:', {
                startDate: dateRange.startDate.toISOString(),
                endDate: dateRange.endDate.toISOString()
            });
        }

        let timeoutId = null;
        try {
            // Add timeout (30 seconds) to prevent hanging requests
            // API can be slow, so we need longer timeout
            const timeoutDuration = 30000; // 30 seconds
            timeoutId = setTimeout(() => {
                if (this.abortController) {
                    console.warn('⏱️ Request timeout after', timeoutDuration / 1000, 'seconds');
                    this.abortController.abort();
                }
            }, timeoutDuration);

            const response = await fetch(url, {
                signal: this.abortController.signal,
                headers: {
                    'Accept': 'application/json'
                }
            });

            // Clear timeout if request succeeds
            if (timeoutId) {
                clearTimeout(timeoutId);
                timeoutId = null;
            }

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            console.log('✅ Funding rate data received:', data.length, 'records');
            
            // ⚠️ WORKAROUND: Backend doesn't filter by interval parameter
            // Filter data client-side based on margin_type matching requested interval
            const filterStartTime = Date.now();
            let filteredData = this.filterByInterval(data, interval);
            const filterTime = Date.now() - filterStartTime;
            
            // Only log if filtering actually reduced data (optimize logging for performance)
            if (filteredData.length < data.length && filterTime > 10) {
                console.log(`📊 Filtered ${data.length} → ${filteredData.length} records for interval=${interval} (${filterTime}ms)`);
            }
            
            // Filter by date range if provided (optimized - only log if significant reduction)
            if (dateRange && dateRange.startDate && dateRange.endDate) {
                const beforeDateFilter = filteredData.length;
                filteredData = this.filterByDateRange(filteredData, dateRange.startDate, dateRange.endDate);
                // Only log if significant reduction (optimize logging)
                if (filteredData.length < beforeDateFilter && (beforeDateFilter - filteredData.length) > 10) {
                    console.log(`📅 Date Range Filter: ${beforeDateFilter} → ${filteredData.length} records`);
                }
            }
            
            // Sort filtered data by timestamp (oldest first) before transform
            // Backend might return data in descending order, need ascending for chart
            const sortedFilteredData = [...filteredData].sort((a, b) => a.ts - b.ts);
            
            // Transform data (optimized - minimal logging for performance)
            const transformed = this.transformHistoryData(sortedFilteredData);
            
            const fetchTime = Date.now() - startTime;
            console.log('✅ Funding rate data received:', transformed.length, 'records', `(${fetchTime}ms)`);
            
            // Only log samples if we have data and in debug mode (reduce logging overhead)
            if (transformed.length > 0 && transformed.length <= 200) {
                // Only show detailed logs for small datasets (initial load)
                console.log('📊 Transformed Sample:', {
                    date: transformed[0].date,
                    value: transformed[0].value,
                    count: transformed.length
                });
            }
            
            const totalTime = Date.now() - startTime;
            console.log('⏱️ Total history fetch time:', totalTime + 'ms');
            
            return transformed;

        } catch (error) {
            // Clear timeout in case of error
            if (timeoutId) {
                clearTimeout(timeoutId);
                timeoutId = null;
            }
            
            if (error.name === 'AbortError') {
                console.log('🚫 Request cancelled (newer request started)');
                return null; // Return null for cancelled requests
            }
            console.error('❌ API Error:', error);
            throw error;
        } finally {
            this.abortController = null;
        }
    }

    /**
     * Filter data by interval (workaround for backend not filtering)
     * Maps frontend interval format to backend margin_type format
     * 
     * @param {Array} data - Raw API response data
     * @param {string} interval - Requested interval (1m, 1h, 8h)
     * @returns {Array} - Filtered data matching requested interval
     */
    filterByInterval(data, interval) {
        if (!Array.isArray(data) || !interval) {
            return data;
        }
        
        // Map frontend interval format to backend margin_type format
        // Backend uses margin_type field (e.g., "1m", "1h", "8h") which should match interval
        const intervalMap = {
            '1m': '1m',
            '5m': '5m',
            '15m': '15m',
            '1h': '1h',
            '4h': '4h',
            '8h': '8h',
            '1w': '1w'
        };
        
        const targetMarginType = intervalMap[interval.toLowerCase()] || interval.toLowerCase();
        
        // Filter data where margin_type matches requested interval
        const filtered = data.filter(item => {
            // margin_type is the field name in API response
            const itemMarginType = item.margin_type || item.interval;
            return itemMarginType && itemMarginType.toLowerCase() === targetMarginType;
        });
        
        return filtered;
    }
    
    /**
     * Filter data by date range (timestamp-based)
     * 
     * @param {Array} data - Filtered data (after interval filter)
     * @param {Date} startDate - Start date (inclusive)
     * @param {Date} endDate - End date (inclusive)
     * @returns {Array} - Filtered data within date range
     */
    filterByDateRange(data, startDate, endDate) {
        if (!Array.isArray(data) || !startDate || !endDate) {
            return data;
        }
        
        const startTs = startDate.getTime();
        const endTs = endDate.getTime();
        
        // Include records where timestamp is within range [startTs, endTs]
        const filtered = data.filter(item => {
            const itemTs = item.ts;
            return itemTs >= startTs && itemTs <= endTs;
        });
        
        return filtered;
    }
    
    /**
     * Transform internal API format to controller format
     * 
     * FROM (Internal API - ACTUAL RESPONSE):
     * [{
     *   "ts": 1704067200000,
     *   "exchange": "Binance",
     *   "pair": "BTCUSDT",
     *   "funding_rate": "0.01000000",
     *   "funding_high": "0.01000000",
     *   "funding_low": "0.01000000",
     *   "funding_open": "0.01000000",
     *   "margin_type": "8h"
     * }]
     * 
     * NOTE: symbol_price is NOT available in API response!
     * 
     * TO (Controller format):
     * [{
     *   "date": "2024-01-01T08:00:00.000Z",  // Full ISO timestamp for hourly support
     *   "value": 0.01,
     *   "price": null,
     *   "exchange": "binance"
     * }]
     */
    transformHistoryData(data) {
        if (!Array.isArray(data)) {
            throw new Error('Invalid data format: expected array');
        }

        return data.map(item => ({
            // Keep full ISO timestamp to support hourly/minute-level data
            date: new Date(item.ts).toISOString(),
            value: parseFloat(item.funding_rate),  // Close value (for backward compatibility)
            // OHLC data for candlestick chart
            open: parseFloat(item.funding_open),
            high: parseFloat(item.funding_high),
            low: parseFloat(item.funding_low),
            close: parseFloat(item.funding_rate),  // funding_rate is the close value
            price: null,  // ⚠️ Price data not available from this endpoint
            exchange: item.exchange.toLowerCase()
        }));
    }

    /**
     * Fetch analytics data (for future use)
     */
    async fetchAnalytics(params) {
        const { symbol, exchange, interval, limit } = params;
        
        const url = `${this.baseUrl}/api/funding-rate/analytics?` +
            `symbol=${symbol}&` +
            `exchange=${exchange}&` +
            `interval=${interval}&` +
            `limit=${limit}`;

        console.log('📊 Fetching analytics data:', url);

        const response = await fetch(url, {
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return response.json();
    }

    /**
     * Fetch bias data (for future use)
     */
    async fetchBias(params) {
        const { symbol, limit } = params;
        
        const url = `${this.baseUrl}/api/funding-rate/bias?` +
            `symbol=${symbol}&` +
            `limit=${limit}`;

        console.log('🎯 Fetching bias data:', url);

        const response = await fetch(url, {
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return response.json();
    }

    /**
     * Fetch aggregated funding rate data (for future use)
     */
    async fetchAggregate(params) {
        const { symbol, interval, limit } = params;
        
        const url = `${this.baseUrl}/api/funding-rate/aggregate?` +
            `symbol=${symbol}&` +
            `interval=${interval}&` +
            `limit=${limit}`;

        console.log('📈 Fetching aggregated data:', url);

        const response = await fetch(url, {
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return response.json();
    }

    /**
     * Fetch analytics funding rate data (includes bias + summary stats)
     */
    async fetchAnalytics(symbol, exchange, interval, limit = 1000) {
        // Abort previous analytics request if exists (separate from history)
        if (this.analyticsAbortController) {
            this.analyticsAbortController.abort();
        }
        this.analyticsAbortController = new AbortController();

        const url = `${this.baseUrl}/api/funding-rate/analytics?` +
            `symbol=${symbol}&` +
            `exchange=${exchange}&` +
            `interval=${interval}&` +
            `limit=${limit}`;

        console.log('📡 Fetching analytics data:', url);
        
        const startTime = Date.now();
        
        let timeoutId = null;
        try {
            // Add timeout (15 seconds) to prevent hanging requests
            const timeoutDuration = 15000; // 15 seconds
            timeoutId = setTimeout(() => {
                if (this.analyticsAbortController) {
                    console.warn('⏱️ Analytics request timeout after', timeoutDuration / 1000, 'seconds');
                    this.analyticsAbortController.abort();
                }
            }, timeoutDuration);

            const response = await fetch(url, {
                signal: this.analyticsAbortController.signal,
                headers: {
                    'Accept': 'application/json'
                }
            });

            // Clear timeout if request succeeds
            if (timeoutId) {
                clearTimeout(timeoutId);
                timeoutId = null;
            }

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            const fetchTime = Date.now() - startTime;
            console.log('✅ Analytics data received:', data, `(${fetchTime}ms)`);

            return {
                // Bias data
                bias: data.bias?.direction || null,
                biasStrength: data.bias?.strength || null,
                // Summary stats from API
                average: data.summary?.average || null,
                max: data.summary?.max || null,
                min: data.summary?.min || null,
                volatility: data.summary?.volatility || null,
                dataPoints: data.summary?.data_points || null,
                marginType: data.summary?.margin_type || null,
                latestTimestamp: data.latest_timestamp || null
            };
        } catch (error) {
            // Clear timeout in case of error
            if (timeoutId) {
                clearTimeout(timeoutId);
                timeoutId = null;
            }
            
            if (error.name === 'AbortError') {
                console.log('🚫 Analytics request was cancelled');
                return null;
            }
            console.error('❌ Error fetching analytics data:', error);
            throw error;
        } finally {
            // Don't set to null here, keep it for potential cancellation
        }
    }

    /**
     * Fetch exchanges comparison data
     */
    async fetchExchanges(symbol, interval, limit = 50) {
        // Abort previous exchanges request if exists (separate from history and analytics)
        if (this.exchangesAbortController) {
            this.exchangesAbortController.abort();
        }
        this.exchangesAbortController = new AbortController();

        const url = `${this.baseUrl}/api/funding-rate/exchanges?` +
            `symbol=${symbol}&` +
            `interval=${interval}&` +
            `limit=${limit}`;

        console.log('📡 Fetching exchanges data:', url);
        
        let timeoutId = null;
        try {
            // Add timeout (15 seconds)
            const timeoutDuration = 15000;
            timeoutId = setTimeout(() => {
                if (this.exchangesAbortController) {
                    this.exchangesAbortController.abort();
                }
            }, timeoutDuration);

            const response = await fetch(url, {
                signal: this.exchangesAbortController.signal,
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            // Clear timeout if request succeeds
            if (timeoutId) {
                clearTimeout(timeoutId);
                timeoutId = null;
            }

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            console.log('✅ Exchanges data received:', data.length, 'records');

            return data;
        } catch (error) {
            // Clear timeout in case of error
            if (timeoutId) {
                clearTimeout(timeoutId);
                timeoutId = null;
            }
            
            if (error.name === 'AbortError') {
                console.log('🚫 Exchanges request was cancelled');
                return null;
            }
            console.error('❌ Error fetching exchanges data:', error);
            throw error;
        }
    }

    /**
     * Cancel ongoing requests
     */
    cancelRequest() {
        if (this.abortController) {
            this.abortController.abort();
            this.abortController = null;
        }
        if (this.analyticsAbortController) {
            this.analyticsAbortController.abort();
            this.analyticsAbortController = null;
        }
        if (this.exchangesAbortController) {
            this.exchangesAbortController.abort();
            this.exchangesAbortController = null;
        }
    }

    /**
     * Cancel all pending requests (alias for cancelRequest for consistency)
     */
    cancelAllRequests() {
        try {
            this.cancelRequest();
        } catch (error) {
            // Ignore errors from abort (expected behavior)
            if (error.name !== 'AbortError') {
                console.warn('⚠️ Error canceling requests:', error);
            }
        }
    }
}

