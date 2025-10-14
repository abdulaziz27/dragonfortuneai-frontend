/**
 * Options Metrics Controller
 * 
 * Handles all API calls to options metrics backend endpoints
 * Replaces mock data with real API integration
 */


class OptionsMetricsController {
    constructor() {
        this.baseUrl = document.querySelector('meta[name="api-base-url"]')?.content || 'https://test.dragonfortune.ai';
        this.baseUrl += '/api/options-metrics';
        this.loading = false;
        this.error = null;
        this.data = {
            ivSummary: null,
            ivSmile: null,
            ivTermStructure: null,
            ivTimeseries: null,
            skewSummary: null,
            skewHistory: null,
            skewRegime: null,
            skewHeatmap: null,
            oiSummary: null,
            oiByExpiry: null,
            oiByStrike: null,
            oiTimeseries: null,
            dealerGreeksSummary: null,
            dealerGreeksGex: null,
            dealerGreeksTimeline: null
        };
    }

    /**
     * Generic API call helper
     */
    async apiCall(endpoint, params = {}) {
        try {
            const url = new URL(this.baseUrl + endpoint);
            Object.keys(params).forEach(key => {
                if (params[key] !== null && params[key] !== undefined) {
                    url.searchParams.append(key, params[key]);
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

            return await response.json();
        } catch (error) {
            console.error(`API call failed for ${endpoint}:`, error);
            throw error;
        }
    }

    /**
     * IV (Implied Volatility) API calls
     */
    async fetchIVSummary(exchange = 'Deribit', underlying = 'BTC') {
        try {
            const data = await this.apiCall('/iv/summary', {
                exchange,
                underlying
            });
            this.data.ivSummary = data;
            return data;
        } catch (error) {
            console.error('Failed to fetch IV summary:', error);
            return null;
        }
    }

    async fetchIVSmile(exchange = 'Deribit', underlying = 'BTC', tenor = '30D') {
        try {
            const data = await this.apiCall('/iv/smile', {
                exchange,
                underlying,
                tenor
            });
            // Store with tenor-specific key to avoid overwriting
            this.data[`ivSmile${tenor}`] = data;
            return data;
        } catch (error) {
            console.error('Failed to fetch IV smile:', error);
            return null;
        }
    }

    async fetchIVTermStructure(exchange = 'Deribit', underlying = 'BTC') {
        try {
            const data = await this.apiCall('/iv/term-structure', {
                exchange,
                underlying
            });
            this.data.ivTermStructure = data;
            return data;
        } catch (error) {
            console.error('Failed to fetch IV term structure:', error);
            return null;
        }
    }

    async fetchIVTimeseries(exchange = 'Deribit', underlying = 'BTC', tenor = '30D') {
        try {
            const data = await this.apiCall('/iv/timeseries', {
                exchange,
                underlying,
                tenor
            });
            this.data.ivTimeseries = data;
            return data;
        } catch (error) {
            console.error('Failed to fetch IV timeseries:', error);
            return null;
        }
    }

    /**
     * Skew API calls
     */
    async fetchSkewSummary(exchange = 'Deribit', underlying = 'BTC') {
        try {
            const data = await this.apiCall('/skew/summary', {
                exchange,
                underlying
            });
            this.data.skewSummary = data;
            return data;
        } catch (error) {
            console.error('Failed to fetch skew summary:', error);
            return null;
        }
    }

    async fetchSkewHistory(exchange = 'Deribit', underlying = 'BTC', tenor = '30D') {
        try {
            const data = await this.apiCall('/skew/history', {
                exchange,
                underlying,
                tenor
            });
            // Store with tenor-specific key to avoid overwriting
            this.data[`skewHistory${tenor}`] = data;
            return data;
        } catch (error) {
            console.error('Failed to fetch skew history:', error);
            return null;
        }
    }

    async fetchSkewRegime(exchange = 'Deribit', underlying = 'BTC') {
        try {
            const data = await this.apiCall('/skew/regime', {
                exchange,
                underlying
            });
            this.data.skewRegime = data;
            return data;
        } catch (error) {
            console.error('Failed to fetch skew regime:', error);
            return null;
        }
    }

    async fetchSkewHeatmap(exchange = 'Deribit', underlying = 'BTC') {
        try {
            const data = await this.apiCall('/skew/heatmap', {
                exchange,
                underlying
            });
            this.data.skewHeatmap = data;
            return data;
        } catch (error) {
            console.error('Failed to fetch skew heatmap:', error);
            return null;
        }
    }

    /**
     * OI (Open Interest) API calls
     */
    async fetchOISummary(exchange = 'Deribit', underlying = 'BTC') {
        try {
            const data = await this.apiCall('/oi/summary', {
                exchange,
                underlying
            });
            this.data.oiSummary = data;
            return data;
        } catch (error) {
            console.error('Failed to fetch OI summary:', error);
            return null;
        }
    }

    async fetchOIByExpiry(exchange = 'Deribit', underlying = 'BTC') {
        try {
            const data = await this.apiCall('/oi/expiry', {
                exchange,
                underlying
            });
            this.data.oiByExpiry = data;
            return data;
        } catch (error) {
            console.error('Failed to fetch OI by expiry:', error);
            return null;
        }
    }

    async fetchOIByStrike(exchange = 'Deribit', underlying = 'BTC', expiry = '2024-03-29') {
        try {
            const data = await this.apiCall('/oi/strike', {
                exchange,
                underlying,
                expiry
            });
            this.data.oiByStrike = data;
            return data;
        } catch (error) {
            console.error('Failed to fetch OI by strike:', error);
            return null;
        }
    }

    async fetchOITimeseries(exchange = 'Deribit', underlying = 'BTC', expiry = '2024-03-29') {
        try {
            const data = await this.apiCall('/oi/timeseries', {
                exchange,
                underlying,
                expiry
            });
            this.data.oiTimeseries = data;
            return data;
        } catch (error) {
            console.error('Failed to fetch OI timeseries:', error);
            return null;
        }
    }

    /**
     * Dealer Greeks API calls
     */
    async fetchDealerGreeksSummary(exchange = 'Deribit', underlying = 'BTC') {
        try {
            const data = await this.apiCall('/dealer-greeks/summary', {
                exchange,
                underlying
            });
            this.data.dealerGreeksSummary = data;
            return data;
        } catch (error) {
            console.error('Failed to fetch dealer greeks summary:', error);
            return null;
        }
    }

    async fetchDealerGreeksGex(exchange = 'Deribit', underlying = 'BTC') {
        try {
            const data = await this.apiCall('/dealer-greeks/gex', {
                exchange,
                underlying
            });
            this.data.dealerGreeksGex = data;
            return data;
        } catch (error) {
            console.error('Failed to fetch dealer greeks GEX:', error);
            return null;
        }
    }

    async fetchDealerGreeksTimeline(exchange = 'Deribit', underlying = 'BTC') {
        try {
            const data = await this.apiCall('/dealer-greeks/timeline', {
                exchange,
                underlying
            });
            this.data.dealerGreeksTimeline = data;
            return data;
        } catch (error) {
            console.error('Failed to fetch dealer greeks timeline:', error);
            return null;
        }
    }

    /**
     * Data transformation helpers
     */
    transformIVSmileData(apiData) {
        if (!apiData || !Array.isArray(apiData)) return {};
        
        // Group by tenor and transform for chart
        const grouped = {};
        apiData.forEach(item => {
            const tenor = item.tenor || '30D';
            if (!grouped[tenor]) {
                grouped[tenor] = [];
            }
            // Convert IV to percentage and sort by strike
            grouped[tenor].push({
                strike: item.strike,
                iv: item.iv * 100, // Convert to percentage
                delta: item.delta
            });
        });
        
        // Sort each tenor by strike price
        Object.keys(grouped).forEach(tenor => {
            grouped[tenor].sort((a, b) => a.strike - b.strike);
        });
        
        return grouped;
    }

    transformSkewData(apiData) {
        if (!apiData || !Array.isArray(apiData)) return [];
        
        return apiData.map(item => ({
            timestamp: item.ts,
            tenor: item.tenor,
            rr25: item.rr25,
            bf25: item.bf25
        }));
    }

    transformOIData(apiData) {
        if (!apiData || !Array.isArray(apiData)) return [];
        
        return apiData.map(item => ({
            expiry: item.expiry,
            callOi: item.call_oi || 0,
            putOi: item.put_oi || 0,
            callVol: item.call_vol || 0,
            putVol: item.put_vol || 0,
            totalOi: (item.call_oi || 0) + (item.put_oi || 0),
            totalVol: (item.call_vol || 0) + (item.put_vol || 0)
        }));
    }

    transformGammaData(apiData) {
        if (!apiData || !Array.isArray(apiData)) return [];
        
        return apiData.map(item => ({
            priceLevel: item.price_level,
            gammaExposure: item.gamma_exposure,
            timestamp: item.ts
        }));
    }

    /**
     * Combine IV Smile data from all tenors
     */
    combineIVSmileData() {
        const allIVSmileData = [];
        const tenors = ['7D', '14D', '30D', '90D'];
        
        tenors.forEach(tenor => {
            const tenorData = this.data[`ivSmile${tenor}`];
            if (tenorData && tenorData.data && Array.isArray(tenorData.data)) {
                allIVSmileData.push(...tenorData.data);
            }
        });
        
        this.data.ivSmile = { data: allIVSmileData };
        console.log('🔄 Combined IV Smile data:', allIVSmileData.length, 'records');
    }

    /**
     * Combine Skew History data from all tenors
     */
    combineSkewHistoryData() {
        const allSkewData = [];
        const tenors = ['7D', '14D', '30D', '90D'];
        
        tenors.forEach(tenor => {
            const tenorData = this.data[`skewHistory${tenor}`];
            if (tenorData && tenorData.data && Array.isArray(tenorData.data)) {
                allSkewData.push(...tenorData.data);
            }
        });
        
        this.data.skewHistory = { data: allSkewData };
        console.log('🔄 Combined Skew History data:', allSkewData.length, 'records');
    }

    /**
     * Utility functions
     */
    formatPercent(value) {
        if (value === null || value === undefined) return 'N/A';
        return `${parseFloat(value).toFixed(1)}%`;
    }

    formatNumber(value) {
        if (value === null || value === undefined) return 'N/A';
        return new Intl.NumberFormat('en-US', { 
            notation: 'compact', 
            maximumFractionDigits: 1 
        }).format(value);
    }

    formatPrice(value) {
        if (value === null || value === undefined) return 'N/A';
        if (value >= 1000) {
            return `${(value / 1000).toFixed(1)}k`;
        }
        return value.toLocaleString();
    }

    /**
     * Fetch all dashboard data
     */
    async fetchDashboardData(exchange = 'Deribit', underlying = 'BTC') {
        this.loading = true;
        this.error = null;

        try {
            // Fetch all data in parallel - fetch all tenors for IV Smile and Skew
            const promises = [
                this.fetchIVSummary(exchange, underlying),
                this.fetchSkewSummary(exchange, underlying),
                this.fetchOISummary(exchange, underlying),
                this.fetchDealerGreeksSummary(exchange, underlying),
                // Fetch IV Smile for all tenors
                this.fetchIVSmile(exchange, underlying, '7D'),
                this.fetchIVSmile(exchange, underlying, '14D'),
                this.fetchIVSmile(exchange, underlying, '30D'),
                this.fetchIVSmile(exchange, underlying, '90D'),
                // Fetch Skew History for all tenors
                this.fetchSkewHistory(exchange, underlying, '7D'),
                this.fetchSkewHistory(exchange, underlying, '14D'),
                this.fetchSkewHistory(exchange, underlying, '30D'),
                this.fetchSkewHistory(exchange, underlying, '90D'),
                this.fetchOIByExpiry(exchange, underlying),
                this.fetchDealerGreeksGex(exchange, underlying)
            ];

            await Promise.allSettled(promises);
            
            // Combine IV Smile data from all tenors
            this.combineIVSmileData();
            
            // Combine Skew History data from all tenors
            this.combineSkewHistoryData();
            
            // Log actual API responses to debug data structure
            console.log('📊 API Response Data:', {
                ivSummary: this.data.ivSummary,
                skewSummary: this.data.skewSummary,
                oiSummary: this.data.oiSummary,
                dealerGreeksSummary: this.data.dealerGreeksSummary,
                ivSmile: this.data.ivSmile,
                skewHistory: this.data.skewHistory
            });
            
            console.log('✅ Dashboard data fetched successfully');
            return this.data;
            
        } catch (error) {
            this.error = error.message;
            console.error('❌ Failed to fetch dashboard data:', error);
            return null;
        } finally {
            this.loading = false;
        }
    }
}

// Export for use in Alpine.js components
window.OptionsMetricsController = OptionsMetricsController;
