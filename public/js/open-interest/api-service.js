/**
 * Open Interest API Service
 * Handles all data fetching from internal API
 */

export class OpenInterestAPIService {
    constructor() {
        // Align with funding-rate behavior: use configured base URL (can be external)
        this.baseUrl = window.APP_CONFIG?.apiBaseUrl || document.querySelector('meta[name="api-base-url"]')?.getAttribute('content') || 'https://test.dragonfortune.ai';
        this.abortController = null; // For history
        this.analyticsAbortController = null; // For analytics
        
        console.log('📡 Open Interest API Service initialized with base URL:', this.baseUrl);
    }

    /**
     * Fetch historical Open Interest data
     * @param {Object} params - Fetch parameters
     * @param {Boolean} isPrefetch - If true, use separate abort controller (don't cancel main requests)
     */
    // Fetch historical Open Interest data
    // isPrefetch: true when called from background prefetch
    // noTimeout: true to disable request timeout (for main, user-facing requests)
    async fetchHistory(params, isPrefetch = false, noTimeout = false) {
        const { symbol, exchange, interval, limit, with_price = false } = params;
        
        // Use separate abort controller for prefetch to avoid cancelling main requests
        let abortController;
        if (isPrefetch) {
            // Prefetch uses its own controller (not shared)
            abortController = new AbortController();
        } else {
            // Main request: cancel previous main request if exists
            if (this.abortController) {
                this.abortController.abort();
            }
            this.abortController = new AbortController();
            abortController = this.abortController;
        }

        // Use limit directly (limit-based approach, no dateRange)
        const requestLimit = limit || 100;

        const url = `${this.baseUrl}/api/open-interest/history?` +
            `symbol=${symbol}&` +
            `exchange=${exchange}&` +
            `interval=${interval}&` +
            `limit=${requestLimit}&` +
            `with_price=${with_price}`;

        console.log(`[HIST:START] interval=${interval} limit=${requestLimit} exchange=${exchange}`);
        
        const startTime = Date.now();

        let timeoutId = null;
        let didTimeout = false;
        try {
            // Add timeout (30 seconds) unless explicitly disabled
            if (!noTimeout) {
                const timeoutDuration = 30000; // 30 seconds
                timeoutId = setTimeout(() => {
                    if (abortController) {
                        console.warn('⏱️ Request timeout after', timeoutDuration / 1000, 'seconds');
                        didTimeout = true;
                        abortController.abort();
                    }
                }, timeoutDuration);
            }

            const response = await fetch(url, {
                signal: abortController.signal,
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
            
            console.log(`[HIST:OK] records=${data.length}`);

            // Sort data by timestamp (oldest first) before transform
            const sortedData = [...data].sort((a, b) => a.ts - b.ts);
            
            // Transform data
            const transformed = this.transformHistoryData(sortedData);
            
            const fetchTime = Date.now() - startTime;
            console.log(`[HIST:OK] records=${transformed.length} fetch_ms=${fetchTime}`);
            
            const totalTime = Date.now() - startTime;
            console.log(`[HIST:TOTAL] ms=${totalTime}`);
            
            return transformed;

        } catch (error) {
            // Clear timeout in case of error
            if (timeoutId) {
                clearTimeout(timeoutId);
                timeoutId = null;
            }
            
            if (didTimeout) {
                const timeoutError = new Error('RequestTimeout');
                timeoutError.code = 'TIMEOUT';
                console.warn('[HIST:TIMEOUT]');
                throw timeoutError;
            }
            
            if (error.name === 'AbortError') {
                console.log('[HIST:CANCEL] reason=abort');
                return null; // Return null for cancelled requests
            }
            console.error('[HIST:ERROR]', error);
            throw error;
        } finally {
            this.abortController = null;
        }
    }

    /**
     * Cancel only history request (do not cancel analytics)
     */
    cancelHistoryOnly() {
        if (this.abortController) {
            try {
                this.abortController.abort();
            } catch (_) {}
            this.abortController = null;
        }
    }
    
    /**
     * Transform internal API format to controller format
     * 
     * FROM (Internal API - ACTUAL RESPONSE):
     * [{
     *   "ts": 1762208100000,
     *   "exchange": "Binance",
     *   "pair": "BTCUSDT",
     *   "oi_usd": "8230487962.44420000",
     *   "price": "0E-8" or actual price
     * }]
     * 
     * TO (Controller format):
     * [{
     *   "date": "2024-01-01T08:00:00.000Z",
     *   "value": 8230487962.4442,
     *   "price": null or actual price,
     *   "exchange": "binance"
     * }]
     */
    transformHistoryData(data) {
        if (!Array.isArray(data)) {
            throw new Error('Invalid data format: expected array');
        }

        return data.map(item => {
            const price = item.price && item.price !== '0E-8' && parseFloat(item.price) > 0 
                ? parseFloat(item.price) 
                : null;

            return {
                date: new Date(item.ts).toISOString(),
                value: parseFloat(item.oi_usd),
                price: price,
                exchange: item.exchange.toLowerCase()
            };
        });
    }

    /**
     * Fetch analytics Open Interest data
     */
    // Fetch analytics Open Interest data
    // isPrefetch: use isolated controller
    // noTimeout: when true, do not set a timeout (allow long-running request)
    async fetchAnalytics(symbol, exchange, interval, limit = 1000, isPrefetch = false, noTimeout = false) {
        // Controller policy: never abort a running main analytics request
        let controller;
        if (isPrefetch) {
            controller = new AbortController();
        } else {
            if (!this.analyticsAbortController) {
                this.analyticsAbortController = new AbortController();
            }
            controller = this.analyticsAbortController;
        }

        const url = `${this.baseUrl}/api/open-interest/analytics?` +
            `symbol=${symbol}&` +
            `exchange=${exchange}&` +
            `interval=${interval}&` +
            `limit=${limit}`;

        console.log('📡 Fetching OI analytics:', url);
        
        const startTime = Date.now();
        
        let timeoutId = null;
        try {
            // Optional timeout for analytics, disabled when noTimeout=true
            if (!noTimeout) {
                const timeoutDuration = 15000; // 15 seconds
                timeoutId = setTimeout(() => {
                    console.warn('⏱️ Analytics request timeout after', timeoutDuration / 1000, 'seconds');
                    // Do NOT abort main analytics; just log (prefetch will be abandoned by abort)
                    if (isPrefetch) {
                        controller.abort();
                    }
                }, timeoutDuration);
            }

            const response = await fetch(url, {
                signal: controller.signal,
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
            console.log('✅ OI analytics data received:', data, `(${fetchTime}ms)`);

            // Handle array response (API returns array with single object)
            const analyticsData = Array.isArray(data) ? data[0] : data;

            return {
                // Trend from API
                trend: analyticsData.trend || null,
                // Current OI from API
                currentOI: analyticsData.open_interest ? parseFloat(analyticsData.open_interest) : null,
                // Insights from API
                insights: analyticsData.insights || {},
                // Direct mapping for summary cards
                dataPoints: analyticsData.insights?.data_points || null,
                maxOI: analyticsData.insights?.max_oi ? parseFloat(analyticsData.insights.max_oi) : null,
                minOI: analyticsData.insights?.min_oi ? parseFloat(analyticsData.insights.min_oi) : null,
                volatilityLevel: analyticsData.insights?.volatility_level || null
            };
        } catch (error) {
            // Clear timeout in case of error
            if (timeoutId) {
                clearTimeout(timeoutId);
                timeoutId = null;
            }
            
            if (error.name === 'AbortError') {
                console.log('⏭️ OI analytics request cancelled');
                return null;
            }
            console.error('❌ Error fetching analytics data:', error);
            throw error;
        } finally {
            // Keep controller for main analytics; prefetch controller is GC'd
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
        // Do not abort analytics here by default (we avoid cancelling long-running analytics)
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
