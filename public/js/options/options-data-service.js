/**
 * Options Data Service
 * 
 * Handles API calls, caching, and data transformation for Options Metrics Dashboard
 * Follows proven patterns from Volatility and ETF dashboards
 */

class OptionsDataService {
    constructor() {
        this.baseUrl = 'https://test.dragonfortune.ai/api/options-metrics';
        this.cache = new Map();
        this.cacheTimeout = 5000; // 5 seconds
    }

    /**
     * Generic API call helper with caching
     */
    async apiCall(endpoint, params = {}) {
        const cacheKey = this.getCacheKey(endpoint, params);
        
        // Special longer cache for GEX data to reduce chart flickering
        const isGexEndpoint = endpoint.includes('/dealer-greeks/gex');
        const cacheTimeout = isGexEndpoint ? 30000 : this.cacheTimeout; // 30s for GEX, 5s for others
        
        // Check cache first
        if (this.cache.has(cacheKey)) {
            const cacheEntry = this.cache.get(cacheKey);
            if (this.isCacheValidWithTimeout(cacheEntry, cacheTimeout)) {
                console.log(`📦 Cache hit for ${endpoint} (${isGexEndpoint ? '30s' : '5s'} cache)`);
                return cacheEntry.data;
            }
        }

        try {
            const url = new URL(this.baseUrl + endpoint);
            Object.keys(params).forEach(key => {
                if (params[key] !== null && params[key] !== undefined) {
                    url.searchParams.append(key, params[key]);
                }
            });

            console.log(`🌐 API call: ${url.toString()}`);
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
            
            // Cache the result with appropriate timeout
            this.cache.set(cacheKey, {
                data: data,
                timestamp: Date.now(),
                ttl: cacheTimeout
            });

            return data;
        } catch (error) {
            console.error(`❌ API call failed for ${endpoint}:`, error);
            return null;
        }
    }

    /**
     * Fetch all dashboard data in parallel
     */
    async fetchAllData(filters = {}) {
        const { exchange = 'Deribit', underlying = 'BTC', tenor = 'all' } = filters;
        
        console.log(`📊 Loading all data for ${underlying} on ${exchange}, tenor: ${tenor}`);

        try {
            // First, get OI by Expiry to find nearest expiry date
            const oiByExpiry = await this.fetchOIByExpiry({ exchange, underlying });
            const nearestExpiry = oiByExpiry?.data?.[0]?.expiry || '2024-12-27';
            
            console.log(`📅 Using nearest expiry: ${nearestExpiry}`);
            
            // Prepare API calls
            const apiCalls = [
                this.fetchIVSummary({ exchange, underlying }),
                this.fetchSkewSummary({ exchange, underlying }),
                this.fetchOISummary({ exchange, underlying }),
                this.fetchDealerGreeksSummary({ exchange, underlying }),
                Promise.resolve(oiByExpiry), // Already fetched
                this.fetchOIByStrike({ exchange, underlying, expiry: nearestExpiry }),
                this.fetchDealerGreeksGex({ exchange, underlying })
            ];

            // Add tenor-specific calls if tenor is specified
            if (tenor === 'all') {
                // Fetch all tenors
                const tenors = ['7D', '14D', '30D', '90D'];
                tenors.forEach(t => {
                    apiCalls.push(this.fetchIVSmile({ exchange, underlying, tenor: t }));
                    apiCalls.push(this.fetchSkewHistory({ exchange, underlying, tenor: t }));
                });
            } else {
                // Fetch specific tenor
                apiCalls.push(this.fetchIVSmile({ exchange, underlying, tenor }));
                apiCalls.push(this.fetchSkewHistory({ exchange, underlying, tenor }));
            }

            // Execute all API calls in parallel
            const results = await Promise.allSettled(apiCalls);
            
            // Process results
            const data = {
                ivSummary: results[0].status === 'fulfilled' ? results[0].value : null,
                skewSummary: results[1].status === 'fulfilled' ? results[1].value : null,
                oiSummary: results[2].status === 'fulfilled' ? results[2].value : null,
                dealerGreeksSummary: results[3].status === 'fulfilled' ? results[3].value : null,
                oiByExpiry: results[4].status === 'fulfilled' ? results[4].value : null,
                oiByStrike: results[5].status === 'fulfilled' ? results[5].value : null,
                dealerGreeksGex: results[6].status === 'fulfilled' ? results[6].value : null,
                ivSmile: { data: [] },
                skewHistory: { data: [] }
            };

            // Combine IV Smile and Skew History data from multiple tenors
            let ivSmileData = [];
            let skewHistoryData = [];
            
            for (let i = 7; i < results.length; i++) {
                if (results[i].status === 'fulfilled' && results[i].value && results[i].value.data) {
                    const resultData = results[i].value.data;
                    if (Array.isArray(resultData) && resultData.length > 0) {
                        // Check if this is IV Smile data (has 'strike' field) or Skew History (has 'rr25' field)
                        if (resultData[0].strike !== undefined) {
                            ivSmileData.push(...resultData);
                        } else if (resultData[0].rr25 !== undefined) {
                            skewHistoryData.push(...resultData);
                        }
                    }
                }
            }

            data.ivSmile.data = ivSmileData;
            data.skewHistory.data = skewHistoryData;

            console.log('✅ All data loaded successfully');
            return data;
            
        } catch (error) {
            console.error('❌ Failed to fetch all data:', error);
            return null;
        }
    }

    /**
     * Individual API methods
     */
    async fetchIVSummary(params) {
        return await this.apiCall('/iv/summary', params);
    }

    async fetchIVSmile(params) {
        return await this.apiCall('/iv/smile', params);
    }

    async fetchSkewSummary(params) {
        return await this.apiCall('/skew/summary', params);
    }

    async fetchSkewHistory(params) {
        return await this.apiCall('/skew/history', params);
    }

    async fetchOISummary(params) {
        return await this.apiCall('/oi/summary', params);
    }

    async fetchOIByExpiry(params) {
        return await this.apiCall('/oi/expiry', params);
    }

    async fetchOIByStrike(params) {
        return await this.apiCall('/oi/strike', params);
    }

    async fetchDealerGreeksSummary(params) {
        return await this.apiCall('/dealer-greeks/summary', params);
    }

    async fetchDealerGreeksGex(params) {
        return await this.apiCall('/dealer-greeks/gex', params);
    }

    /**
     * Cache management
     */
    clearCache() {
        console.log('🗑️ Clearing cache');
        this.cache.clear();
    }

    getCacheKey(endpoint, params) {
        const sortedParams = Object.keys(params)
            .sort()
            .reduce((result, key) => {
                result[key] = params[key];
                return result;
            }, {});
        return `${endpoint}:${JSON.stringify(sortedParams)}`;
    }

    isCacheValid(cacheEntry) {
        return (Date.now() - cacheEntry.timestamp) < cacheEntry.ttl;
    }

    isCacheValidWithTimeout(cacheEntry, timeout) {
        return (Date.now() - cacheEntry.timestamp) < timeout;
    }

    /**
     * Data transformation methods
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
}

// Export for use in Alpine.js components
window.OptionsDataService = OptionsDataService;