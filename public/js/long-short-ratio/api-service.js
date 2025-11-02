/**
 * Long Short Ratio API Service
 * Handles all data fetching from internal and external APIs
 */

export class LongShortRatioAPIService {
    constructor() {
        this.baseUrl = window.APP_CONFIG?.apiBaseUrl || 'https://test.dragonfortune.ai';
        
        // Separate AbortController for each request type to prevent race conditions
        this.overviewAbortController = null;
        this.analyticsAbortController = null;
        this.topAccountsAbortController = null;
        this.topPositionsAbortController = null;
        this.globalAccountAbortController = null;
        this.takerBuySellAbortController = null;
        
        // Cache for external API calls (5 minutes)
        this.dataCache = new Map();
        
        console.log('📡 Long Short Ratio API Service initialized with base URL:', this.baseUrl);
    }

    /**
     * Fetch overview data (internal API)
     */
    async fetchOverview(params) {
        const { symbol, interval, limit } = params;
        
        if (this.overviewAbortController) {
            this.overviewAbortController.abort();
        }
        this.overviewAbortController = new AbortController();

        const url = `${this.baseUrl}/api/long-short-ratio/overview?` +
            `symbol=${symbol}&` +
            `interval=${interval}&` +
            `limit=${limit || 1000}`;

        console.log('📡 Fetching overview:', url);

        try {
            const response = await fetch(url, {
                signal: this.overviewAbortController.signal,
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('✅ Overview data received:', data);
            return data;
        } catch (error) {
            if (error.name === 'AbortError') {
                console.log('🛑 Overview request aborted');
                throw error;
            }
            console.error('❌ Error fetching overview:', error);
            throw error;
        }
    }

    /**
     * Fetch analytics data (internal API)
     */
    async fetchAnalytics(params) {
        const { symbol, exchange, interval, ratio_type, limit } = params;
        
        if (this.analyticsAbortController) {
            this.analyticsAbortController.abort();
        }
        this.analyticsAbortController = new AbortController();

        const url = `${this.baseUrl}/api/long-short-ratio/analytics?` +
            `symbol=${symbol}&` +
            `exchange=${exchange}&` +
            `interval=${interval}&` +
            `ratio_type=${ratio_type || 'accounts'}&` +
            `limit=${limit || 1000}`;

        console.log('📡 Fetching analytics:', url);

        try {
            const response = await fetch(url, {
                signal: this.analyticsAbortController.signal,
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('✅ Analytics data received:', data);
            // API returns array, get first item
            return data && data.length > 0 ? data[0] : null;
        } catch (error) {
            if (error.name === 'AbortError') {
                console.log('🛑 Analytics request aborted');
                throw error;
            }
            console.error('❌ Error fetching analytics:', error);
            throw error;
        }
    }

    /**
     * Fetch top accounts data (internal API)
     */
    async fetchTopAccounts(params) {
        const { symbol, exchange, interval, limit, dateRange } = params;
        
        if (this.topAccountsAbortController) {
            this.topAccountsAbortController.abort();
        }
        this.topAccountsAbortController = new AbortController();

        const url = `${this.baseUrl}/api/long-short-ratio/top-accounts?` +
            `symbol=${symbol}&` +
            `exchange=${exchange}&` +
            `interval=${interval}&` +
            `limit=${limit || 5000}`;

        console.log('📡 Fetching top accounts:', url);

        try {
            const response = await fetch(url, {
                signal: this.topAccountsAbortController.signal,
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            let data = await response.json();
            console.log('✅ Top accounts data received:', data.length, 'records');
            
            // Filter by date range if provided (client-side filtering)
            if (dateRange && dateRange.startDate && dateRange.endDate) {
                const beforeFilter = data.length;
                data = this.filterByDateRange(data, dateRange.startDate, dateRange.endDate);
                console.log(`📅 Date Range Filter: ${beforeFilter} → ${data.length} records`);
            }
            
            // Transform data: convert ts to time (milliseconds)
            return this.transformTopAccountsData(data);
        } catch (error) {
            if (error.name === 'AbortError') {
                console.log('🛑 Top accounts request aborted');
                throw error;
            }
            console.error('❌ Error fetching top accounts:', error);
            throw error;
        }
    }

    /**
     * Fetch top positions data (internal API)
     */
    async fetchTopPositions(params) {
        const { symbol, exchange, interval, limit, dateRange } = params;
        
        if (this.topPositionsAbortController) {
            this.topPositionsAbortController.abort();
        }
        this.topPositionsAbortController = new AbortController();

        const url = `${this.baseUrl}/api/long-short-ratio/top-positions?` +
            `symbol=${symbol}&` +
            `exchange=${exchange}&` +
            `interval=${interval}&` +
            `limit=${limit || 5000}`;

        console.log('📡 Fetching top positions:', url);

        try {
            const response = await fetch(url, {
                signal: this.topPositionsAbortController.signal,
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            let data = await response.json();
            console.log('✅ Top positions data received:', data.length, 'records');
            
            // Filter by date range if provided (client-side filtering)
            if (dateRange && dateRange.startDate && dateRange.endDate) {
                const beforeFilter = data.length;
                data = this.filterByDateRange(data, dateRange.startDate, dateRange.endDate);
                console.log(`📅 Date Range Filter: ${beforeFilter} → ${data.length} records`);
            }
            
            // Transform data: convert ts to time (milliseconds)
            return this.transformTopPositionsData(data);
        } catch (error) {
            if (error.name === 'AbortError') {
                console.log('🛑 Top positions request aborted');
                throw error;
            }
            console.error('❌ Error fetching top positions:', error);
            throw error;
        }
    }

    /**
     * Filter data by date range (client-side filtering)
     * @param {Array} data - Data with ts field (milliseconds)
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
     * Fetch global account ratio (external API - Coinglass)
     * Keep for backward compatibility until migration complete
     */
    async fetchGlobalAccountRatio(params) {
        const { exchange, symbol, interval, startTime, endTime, limit } = params;
        
        if (this.globalAccountAbortController) {
            this.globalAccountAbortController.abort();
        }
        this.globalAccountAbortController = new AbortController();

        const cacheKey = `global-account-${exchange}-${symbol}-${interval}-${startTime}-${endTime}`;
        
        if (this.dataCache.has(cacheKey)) {
            console.log('📦 Using cached Global Account Ratio data');
            return this.dataCache.get(cacheKey);
        }

        const url = `/api/coinglass/global-account-ratio?` +
            `exchange=${exchange}&` +
            `symbol=${symbol}&` +
            `interval=${interval}&` +
            `start_time=${startTime}&` +
            `end_time=${endTime}&` +
            `limit=${limit || 1000}`;

        try {
            const response = await fetch(url, {
                signal: this.globalAccountAbortController.signal,
                headers: { 'Accept': 'application/json' }
            });

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

            console.log('✅ Global Account Ratio data received:', data.data.length, 'records');
            return data.data;
        } catch (error) {
            if (error.name === 'AbortError') {
                console.log('🛑 Global Account Ratio request aborted');
                throw error;
            }
            console.error('❌ Error fetching Global Account Ratio:', error);
            throw error;
        }
    }

    /**
     * Fetch taker buy/sell ratio (external API - Coinglass)
     */
    async fetchTakerBuySellRatio(params) {
        const { symbol, range } = params;
        
        if (this.takerBuySellAbortController) {
            this.takerBuySellAbortController.abort();
        }
        this.takerBuySellAbortController = new AbortController();

        const cacheKey = `taker-buysell-${symbol}-${range}`;
        
        if (this.dataCache.has(cacheKey)) {
            console.log('📦 Using cached Taker Buy/Sell data');
            return this.dataCache.get(cacheKey);
        }

        const url = `/api/coinglass/taker-buy-sell?` +
            `symbol=${symbol}&` +
            `range=${range || '1h'}`;

        try {
            const response = await fetch(url, {
                signal: this.takerBuySellAbortController.signal,
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (!data.success || !data.data) {
                throw new Error('Invalid Taker Buy/Sell data format');
            }

            // Cache the result for 5 minutes
            this.dataCache.set(cacheKey, data.data);
            setTimeout(() => this.dataCache.delete(cacheKey), 5 * 60 * 1000);

            console.log('✅ Taker Buy/Sell data received:', data.data);
            return data.data;
        } catch (error) {
            if (error.name === 'AbortError') {
                console.log('🛑 Taker Buy/Sell request aborted');
                throw error;
            }
            console.error('❌ Error fetching Taker Buy/Sell:', error);
            throw error;
        }
    }

    /**
     * Transform top accounts data from internal API to match Coinglass format
     */
    transformTopAccountsData(data) {
        return data.map(item => ({
            time: item.ts, // Already in milliseconds
            top_account_long_percent: parseFloat(item.long_accounts) || 0,
            top_account_short_percent: parseFloat(item.short_accounts) || 0,
            top_account_long_short_ratio: parseFloat(item.ls_ratio_accounts) || 0,
            // Keep original fields for compatibility
            long_accounts: parseFloat(item.long_accounts) || 0,
            short_accounts: parseFloat(item.short_accounts) || 0,
            ls_ratio_accounts: parseFloat(item.ls_ratio_accounts) || 0,
            ts: item.ts
        }));
    }

    /**
     * Transform top positions data from internal API to match Coinglass format
     */
    transformTopPositionsData(data) {
        return data.map(item => ({
            time: item.ts, // Already in milliseconds
            top_position_long_percent: parseFloat(item.long_positions_percent) || 0,
            top_position_short_percent: parseFloat(item.short_positions_percent) || 0,
            top_position_long_short_ratio: parseFloat(item.ls_ratio_positions) || 0,
            // Keep original fields for compatibility
            long_positions_percent: parseFloat(item.long_positions_percent) || 0,
            short_positions_percent: parseFloat(item.short_positions_percent) || 0,
            ls_ratio_positions: parseFloat(item.ls_ratio_positions) || 0,
            ts: item.ts
        }));
    }

    /**
     * Cancel all pending requests
     */
    cancelAllRequests() {
        const controllers = [
            this.overviewAbortController,
            this.analyticsAbortController,
            this.topAccountsAbortController,
            this.topPositionsAbortController,
            this.globalAccountAbortController,
            this.takerBuySellAbortController
        ];

        controllers.forEach(controller => {
            if (controller) {
                controller.abort();
            }
        });

        console.log('🛑 All API requests cancelled');
    }
}

