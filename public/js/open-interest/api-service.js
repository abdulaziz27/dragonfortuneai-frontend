/**
 * Open Interest API Service
 * Handles all API requests for Open Interest data
 */

export class OpenInterestAPIService {
    constructor() {
        this.baseUrl = window.APP_CONFIG?.apiBaseUrl || '';
        
        // Separate AbortController for each request type
        this.historyAbortController = null;
        this.analyticsAbortController = null;
        this.exchangeAbortController = null;
    }

    /**
     * Fetch Open Interest history data
     */
    async fetchHistory(params) {
        const { symbol, exchange, interval, limit, with_price = true } = params;

        // Cancel previous request
        if (this.historyAbortController) {
            this.historyAbortController.abort();
        }
        this.historyAbortController = new AbortController();

        const url = `${this.baseUrl}/api/open-interest/history?symbol=${symbol}&exchange=${exchange}&interval=${interval}&limit=${limit}&with_price=${with_price}`;

        console.log('📡 Fetching OI history:', url);
        
        const startTime = Date.now();

        try {
            // Add timeout (5 seconds) to prevent hanging requests
            const timeoutId = setTimeout(() => {
                if (this.historyAbortController) {
                    this.historyAbortController.abort();
                }
            }, 5000);

            const response = await fetch(url, {
                signal: this.historyAbortController.signal
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            const fetchTime = Date.now() - startTime;
            console.log('✅ OI history data received:', data?.length || 0, 'records', `(${fetchTime}ms)`);

            // Transform data efficiently
            const transformed = this.transformHistoryData(data);
            const totalTime = Date.now() - startTime;
            console.log('⏱️ Total history fetch time:', totalTime + 'ms');

            return transformed;
        } catch (error) {
            if (error.name === 'AbortError') {
                console.log('⏭️ OI history request cancelled');
                return null;
            }
            console.error('❌ Error fetching OI history:', error);
            throw error;
        }
    }

    /**
     * Fetch Open Interest analytics data
     */
    async fetchAnalytics(params) {
        const { symbol, exchange, interval, limit } = params;

        // Cancel previous request
        if (this.analyticsAbortController) {
            this.analyticsAbortController.abort();
        }
        this.analyticsAbortController = new AbortController();

        const url = `${this.baseUrl}/api/open-interest/analytics?symbol=${symbol}&exchange=${exchange}&interval=${interval}&limit=${limit}`;

        console.log('📡 Fetching OI analytics:', url);
        
        const startTime = Date.now();

        try {
            // Add timeout (5 seconds) to prevent hanging requests
            const timeoutId = setTimeout(() => {
                if (this.analyticsAbortController) {
                    this.analyticsAbortController.abort();
                }
            }, 5000);

            const response = await fetch(url, {
                signal: this.analyticsAbortController.signal
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            const fetchTime = Date.now() - startTime;
            console.log('✅ OI analytics data received:', data, `(${fetchTime}ms)`);

            // Return first item if array, otherwise return as-is
            return Array.isArray(data) ? (data[0] || null) : data;
        } catch (error) {
            if (error.name === 'AbortError') {
                console.log('⏭️ OI analytics request cancelled');
                return null;
            }
            console.error('❌ Error fetching OI analytics:', error);
            return null; // Return null instead of throwing for analytics
        }
    }

    /**
     * Fetch Open Interest per exchange (for FASE 2)
     */
    async fetchExchange(params) {
        const { symbol, exchange, limit, pivot = false } = params;

        // Cancel previous request
        if (this.exchangeAbortController) {
            this.exchangeAbortController.abort();
        }
        this.exchangeAbortController = new AbortController();

        const url = `${this.baseUrl}/api/open-interest/exchange?symbol=${symbol}&exchange=${exchange}&limit=${limit}&pivot=${pivot}`;

        console.log('📡 Fetching OI exchange data:', url);

        try {
            const response = await fetch(url, {
                signal: this.exchangeAbortController.signal
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('✅ OI exchange data received:', data?.length || 0, 'records');

            return data;
        } catch (error) {
            if (error.name === 'AbortError') {
                console.log('⏭️ OI exchange request cancelled');
                return null;
            }
            console.error('❌ Error fetching OI exchange:', error);
            return null;
        }
    }

    /**
     * Cancel all pending requests
     */
    cancelAllRequests() {
        if (this.historyAbortController) {
            this.historyAbortController.abort();
        }
        if (this.analyticsAbortController) {
            this.analyticsAbortController.abort();
        }
        if (this.exchangeAbortController) {
            this.exchangeAbortController.abort();
        }
    }

    /**
     * Transform history data from API format to chart format
     */
    transformHistoryData(data) {
        if (!Array.isArray(data)) {
            console.warn('⚠️ History data is not an array');
            return [];
        }

        // Transform and ensure timestamps are in milliseconds
        const transformed = data.map(item => {
            const ts = item.ts || item.time || 0;
            
            return {
                ts: ts < 1e12 ? ts * 1000 : ts, // Convert to milliseconds if needed
                oi_usd: parseFloat(item.oi_usd || item.open_interest || 0),
                price: item.price ? parseFloat(item.price) : null,
                exchange: item.exchange
            };
        });

        // Sort by timestamp ascending
        transformed.sort((a, b) => a.ts - b.ts);

        console.log('📊 Transformed history data:', {
            count: transformed.length,
            first: transformed[0],
            last: transformed[transformed.length - 1]
        });

        return transformed;
    }

    /**
     * Filter data by date range (client-side) - Optimized for large datasets
     */
    filterByDateRange(data, startDate, endDate) {
        if (!Array.isArray(data) || data.length === 0) return data;

        const startTs = startDate.getTime();
        const endTs = endDate.getTime();

        // Optimized filter - single pass, early exit conditions
        const filtered = [];
        for (let i = 0; i < data.length; i++) {
            const item = data[i];
            const ts = item.ts || item.time || 0;
            
            // Early exit if we're past the end date (data should be sorted by timestamp)
            if (ts > endTs) break;
            
            if (ts >= startTs && ts <= endTs) {
                filtered.push(item);
            }
        }

        console.log('📅 Date Range Filter:', {
            startDate: startDate.toISOString(),
            endDate: endDate.toISOString(),
            beforeFilter: data.length,
            afterFilter: filtered.length,
            filteredPercent: ((filtered.length / data.length) * 100).toFixed(1) + '%'
        });

        return filtered;
    }
}

