/**
 * Basis & Term Structure API Service
 * Handles all API interactions for Basis dashboard
 */

import { BasisUtils } from './utils.js';

export class BasisAPIService {
    constructor() {
        this.baseUrl = window.APP_CONFIG?.apiBaseUrl || '';
        this.analyticsAbortController = null;
        this.historyAbortController = null;
        this.termStructureAbortController = null;
        console.log('📡 Basis API Service initialized with base URL:', this.baseUrl);
    }

    /**
     * Fetch analytics data
     */
    async fetchAnalytics(params) {
        const { exchange, spotPair, futuresSymbol, interval, limit } = params;

        // Abort previous request if exists
        if (this.analyticsAbortController) {
            this.analyticsAbortController.abort();
        }
        this.analyticsAbortController = new AbortController();

        const url = `${this.baseUrl}/api/basis/analytics?` +
            `exchange=${encodeURIComponent(exchange)}&` +
            `spot_pair=${encodeURIComponent(spotPair)}&` +
            `futures_symbol=${encodeURIComponent(futuresSymbol)}&` +
            `interval=${interval}&` +
            `limit=${limit}`;

        console.log('📡 Fetching basis analytics:', url);
        
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
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            const fetchTime = Date.now() - startTime;
            console.log('✅ Basis analytics data received:', data, `(${fetchTime}ms)`);

            // API returns array, get first item
            if (!data || !Array.isArray(data) || data.length === 0) {
                console.warn('⚠️ Analytics API returned empty array or invalid data');
                return null;
            }
            
            const analyticsResult = data[0];
            console.log('📊 Analytics result:', analyticsResult);
            return analyticsResult;
        } catch (error) {
            // Clear timeout in case of error
            if (timeoutId) {
                clearTimeout(timeoutId);
                timeoutId = null;
            }
            
            if (error.name === 'AbortError') {
                console.log('🛑 Analytics request aborted');
                return null;
            }
            console.error('❌ Error fetching analytics:', error);
            throw error;
        }
    }

    /**
     * Fetch history data
     */
    async fetchHistory(params) {
        const { exchange, spotPair, futuresSymbol, interval, limit, dateRange } = params;

        // Abort previous request if exists
        if (this.historyAbortController) {
            this.historyAbortController.abort();
        }
        this.historyAbortController = new AbortController();

        // Use fixed limit 5000 (same as funding-rate and perp-quarterly)
        const requestLimit = limit || 5000;

        const url = `${this.baseUrl}/api/basis/history?` +
            `exchange=${encodeURIComponent(exchange)}&` +
            `spot_pair=${encodeURIComponent(spotPair)}&` +
            `futures_symbol=${encodeURIComponent(futuresSymbol)}&` +
            `interval=${interval}&` +
            `limit=${requestLimit}`;

        console.log('📡 Fetching basis history:', url);
        
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
            const timeoutDuration = 30000; // 30 seconds
            timeoutId = setTimeout(() => {
                if (this.historyAbortController) {
                    console.warn('⏱️ History request timeout after', timeoutDuration / 1000, 'seconds');
                    this.historyAbortController.abort();
                }
            }, timeoutDuration);

            const response = await fetch(url, {
                signal: this.historyAbortController.signal,
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
            const fetchTime = Date.now() - startTime;
            console.log('✅ Basis history data received:', data.length, 'records', `(${fetchTime}ms)`);

            // Transform data: verify timestamp format (milliseconds or seconds?)
            const transformed = this.transformHistoryData(data);
            
            // Filter by date range if provided
            let filteredData = transformed;
            if (dateRange && dateRange.startDate && dateRange.endDate) {
                filteredData = this.filterByDateRange(transformed, dateRange.startDate, dateRange.endDate);
                console.log(`📅 Date Range Filter Result: ${transformed.length} → ${filteredData.length} records`);
            }

            // Sort by timestamp (ascending)
            const sorted = [...filteredData].sort((a, b) => a.ts - b.ts);
            
            const totalTime = Date.now() - startTime;
            console.log('⏱️ Total history fetch time:', totalTime + 'ms');
            
            return sorted;
        } catch (error) {
            // Clear timeout in case of error
            if (timeoutId) {
                clearTimeout(timeoutId);
                timeoutId = null;
            }
            
            if (error.name === 'AbortError') {
                console.log('🛑 History request aborted');
                return null;
            }
            console.error('❌ Error fetching history:', error);
            throw error;
        }
    }

    /**
     * Fetch term structure data
     */
    async fetchTermStructure(params) {
        const { symbol, exchange, limit } = params;

        // Abort previous request if exists
        if (this.termStructureAbortController) {
            this.termStructureAbortController.abort();
        }
        this.termStructureAbortController = new AbortController();

        const url = `${this.baseUrl}/api/basis/term-structure?` +
            `symbol=${encodeURIComponent(symbol)}&` +
            `exchange=${encodeURIComponent(exchange)}&` +
            `limit=${limit || 1000}`;

        console.log('📡 Fetching term structure:', url);
        
        const startTime = Date.now();
        let timeoutId = null;

        try {
            // Add timeout (15 seconds) to prevent hanging requests
            const timeoutDuration = 15000; // 15 seconds
            timeoutId = setTimeout(() => {
                if (this.termStructureAbortController) {
                    console.warn('⏱️ Term structure request timeout after', timeoutDuration / 1000, 'seconds');
                    this.termStructureAbortController.abort();
                }
            }, timeoutDuration);

            const response = await fetch(url, {
                signal: this.termStructureAbortController.signal,
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
            const fetchTime = Date.now() - startTime;
            console.log('✅ Term structure data received:', data, `(${fetchTime}ms)`);
            return data;
        } catch (error) {
            // Clear timeout in case of error
            if (timeoutId) {
                clearTimeout(timeoutId);
                timeoutId = null;
            }
            
            if (error.name === 'AbortError') {
                console.log('🛑 Term structure request aborted');
                return null;
            }
            console.error('❌ Error fetching term structure:', error);
            throw error;
        }
    }

    /**
     * Transform history data from API format
     * Verify timestamp format and convert to milliseconds if needed
     */
    transformHistoryData(data) {
        return data.map(item => {
            // Verify timestamp format - check if it's in seconds or milliseconds
            let ts = parseInt(item.ts) || 0;
            
            // If timestamp is less than year 2000 in milliseconds, assume it's in seconds
            // Year 2000 in milliseconds: 946684800000
            if (ts < 946684800000) {
                ts = ts * 1000; // Convert seconds to milliseconds
            }

            return {
                ts: ts,
                date: new Date(ts).toISOString(),
                basisAbs: parseFloat(item.basis_abs) || 0,
                basisAnnualized: parseFloat(item.basis_annualized) || 0,
                spotPrice: parseFloat(item.spot_price) || 0,
                futuresPrice: parseFloat(item.futures_price) || 0
            };
        });
    }

    /**
     * Filter data by date range
     */
    filterByDateRange(data, startDate, endDate) {
        const startTs = startDate.getTime();
        const endTs = endDate.getTime();
        return data.filter(item => {
            const itemTs = item.ts;
            return itemTs >= startTs && itemTs <= endTs;
        });
    }

    /**
     * Cancel all pending requests
     */
    cancelAllRequests() {
        if (this.analyticsAbortController) {
            this.analyticsAbortController.abort();
        }
        if (this.historyAbortController) {
            this.historyAbortController.abort();
        }
        if (this.termStructureAbortController) {
            this.termStructureAbortController.abort();
        }
    }
}

