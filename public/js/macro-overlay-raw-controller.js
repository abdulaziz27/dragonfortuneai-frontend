/**
 * Macro Overlay (Raw) Controller
 * Handles API consumption for all 7 macro overlay endpoints
 * Provides comprehensive data management for raw macro data, analytics, events, and insights
 */

class MacroOverlayRawController {
    constructor() {
        // Get API base URL from meta tag
        const metaTag = document.querySelector('meta[name="api-base-url"]');
        this.baseUrl = metaTag ? metaTag.content : "";

        // Default filters matching API parameters
        this.filters = {
            // Raw data filters
            metric: null, // DXY, YIELD_10Y, FED_FUNDS, M2, RRP, TGA
            source: "FRED",
            start_date: null,
            end_date: null,
            limit: 2000,
            
            // Analytics filters
            days_back: 90,
            metrics: "DXY,FED_FUNDS,YIELD_10Y,M2,RRP,TGA", // comma-separated
            
            // Events filters
            event_type: null, // CPI, CPI_CORE, NFP
            months_back: 6
        };

        // Cache for API responses
        this.cache = {
            rawData: null,
            summary: null,
            analytics: null,
            enhancedAnalytics: null,
            availableMetrics: null,
            events: null,
            eventsSummary: null,
            lastUpdate: null
        };

        // Charts storage
        this.charts = {
            rawDataChart: null,
            analyticsChart: null,
            eventsChart: null,
            correlationChart: null,
            trendsChart: null
        };

        // Available metrics from API
        this.availableMetrics = {
            overlay: ['DXY', 'YIELD_2Y', 'YIELD_10Y', 'YIELD_30Y', 'FED_FUNDS', 'M2', 'RRP', 'TGA'],
            events: ['CPI', 'CPI_CORE', 'NFP']
        };
    }

    /**
     * Build URL with query parameters
     */
    buildUrl(endpoint, params = {}) {
        const url = new URL(`${this.baseUrl}${endpoint}`);
        
        // Add non-null parameters
        Object.entries(params).forEach(([key, value]) => {
            if (value !== null && value !== undefined && value !== '') {
                url.searchParams.append(key, value);
            }
        });
        
        // Add cache-busting parameter to prevent caching issues
        url.searchParams.append('_t', Date.now());
        
        return url.toString();
    }

    /**
     * Generic API fetch with error handling
     */
    async fetchAPI(endpoint, params = {}) {
        try {
            const url = this.buildUrl(endpoint, params);
            console.log(`🌍 Fetching: ${url}`);
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log(`✅ Success: ${endpoint}`, data);
            return data;
            
        } catch (error) {
            console.error(`❌ API Error [${endpoint}]:`, error);
            throw error;
        }
    }

    /**
     * 1. GET /api/macro-overlay/raw
     * Fetch raw macro data with filters
     */
    async fetchRawData(customFilters = {}) {
        const params = {
            metric: customFilters.metric || this.filters.metric,
            source: customFilters.source || this.filters.source,
            start_date: customFilters.start_date || this.filters.start_date,
            end_date: customFilters.end_date || this.filters.end_date,
            limit: customFilters.limit || this.filters.limit
        };

        const data = await this.fetchAPI('/api/macro-overlay/raw', params);
        this.cache.rawData = data;
        this.cache.lastUpdate = new Date();
        return data;
    }

    /**
     * 2. GET /api/macro-overlay/summary
     * Fetch summary statistics
     */
    async fetchSummary(customFilters = {}) {
        const params = {
            metric: customFilters.metric || this.filters.metric,
            source: customFilters.source || this.filters.source,
            days_back: customFilters.days_back || 90
        };

        console.log('🔍 fetchSummary called with params:', params);

        try {
            const data = await this.fetchAPI('/api/macro-overlay/summary', params);
            console.log('✅ Summary API success:', data);
            this.cache.summary = data;
            return data;
        } catch (error) {
            console.error('❌ Summary API failed:', error);
            
            // Return fallback data structure
            const fallbackData = {
                data: {
                    count: 0,
                    avg_value: null,
                    max_value: null,
                    min_value: null,
                    trend: 'neutral',
                    earliest_value: null,
                    latest_value: null
                }
            };
            console.log('🔄 Using fallback data:', fallbackData);
            this.cache.summary = fallbackData;
            return fallbackData;
        }
    }

    /**
     * 3. GET /api/macro-overlay/analytics
     * Fetch comprehensive analytics and insights
     */
    async fetchAnalytics(customFilters = {}) {
        const params = {
            metric: customFilters.metric || this.filters.metric,
            source: customFilters.source || this.filters.source,
            start_date: customFilters.start_date || this.filters.start_date,
            end_date: customFilters.end_date || this.filters.end_date,
            limit: customFilters.limit || this.filters.limit
        };

        const data = await this.fetchAPI('/api/macro-overlay/analytics', params);
        this.cache.analytics = data;
        return data;
    }

    /**
     * 4. GET /api/macro-overlay/enhanced-analytics
     * Fetch enhanced analytics with correlations and volatility
     */
    async fetchEnhancedAnalytics(customFilters = {}) {
        const params = {
            metrics: customFilters.metrics || this.filters.metrics,
            days_back: customFilters.days_back || this.filters.days_back
        };

        const data = await this.fetchAPI('/api/macro-overlay/enhanced-analytics', params);
        this.cache.enhancedAnalytics = data;
        return data;
    }

    /**
     * 5. GET /api/macro-overlay/available-metrics
     * Fetch available metrics information
     */
    async fetchAvailableMetrics() {
        const data = await this.fetchAPI('/api/macro-overlay/available-metrics');
        this.cache.availableMetrics = data;
        return data;
    }

    /**
     * 6. GET /api/macro-overlay/events
     * Fetch macro economic events (CPI, NFP, etc.)
     */
    async fetchEvents(customFilters = {}) {
        const params = {
            event_type: customFilters.event_type || this.filters.event_type,
            source: customFilters.source || this.filters.source,
            start_date: customFilters.start_date || this.filters.start_date,
            end_date: customFilters.end_date || this.filters.end_date,
            limit: customFilters.limit || this.filters.limit
        };

        const data = await this.fetchAPI('/api/macro-overlay/events', params);
        this.cache.events = data;
        return data;
    }

    /**
     * 7. GET /api/macro-overlay/events-summary
     * Fetch events summary statistics
     */
    async fetchEventsSummary(customFilters = {}) {
        const params = {
            event_type: customFilters.event_type || this.filters.event_type,
            source: customFilters.source || this.filters.source,
            months_back: customFilters.months_back || this.filters.months_back
        };

        const data = await this.fetchAPI('/api/macro-overlay/events-summary', params);
        this.cache.eventsSummary = data;
        return data;
    }

    /**
     * Fetch all data sources in parallel with fallbacks
     * Supports both start_date/end_date and days_back parameters
     */
    async fetchAllData(customFilters = {}) {
        try {
            console.log('🚀 Fetching all macro overlay data...');
            console.log('📅 Filters:', {
                start_date: customFilters.start_date,
                end_date: customFilters.end_date,
                days_back: customFilters.days_back,
                metric: customFilters.metric
            });
            
            // Calculate months_back from days_back for events-summary
            const monthsBack = customFilters.days_back ? Math.ceil(customFilters.days_back / 30) : 6;
            
            const promises = [
                // Raw data - uses start_date & end_date
                this.fetchRawData({
                    metric: customFilters.metric,
                    start_date: customFilters.start_date,
                    end_date: customFilters.end_date,
                    limit: 2000
                }),
                // Summary - uses days_back
                this.fetchSummary({
                    metric: customFilters.metric,
                    days_back: customFilters.days_back || 90
                }),
                // Analytics - uses start_date & end_date
                this.fetchAnalytics({
                    metric: customFilters.metric,
                    start_date: customFilters.start_date,
                    end_date: customFilters.end_date,
                    limit: 2000
                }),
                // Enhanced Analytics - uses days_back
                this.fetchEnhancedAnalytics({
                    days_back: customFilters.days_back || 90
                }),
                // Available Metrics - no parameters
                this.fetchAvailableMetrics(),
                // Events - uses start_date & end_date (NOT affected by metric filter)
                this.fetchEvents({
                    event_type: null, // Events are independent of metric selection
                    start_date: customFilters.start_date,
                    end_date: customFilters.end_date,
                    limit: 100
                }),
                // Events Summary - uses months_back (NOT affected by metric filter)
                this.fetchEventsSummary({
                    event_type: null, // Events are independent of metric selection
                    months_back: monthsBack
                })
            ];

            const results = await Promise.allSettled(promises);
            
            // Log results
            results.forEach((result, index) => {
                const endpoints = ['raw', 'summary', 'analytics', 'enhanced-analytics', 'available-metrics', 'events', 'events-summary'];
                if (result.status === 'fulfilled') {
                    console.log(`✅ ${endpoints[index]}: Success`);
                } else {
                    console.warn(`⚠️ ${endpoints[index]}: ${result.reason.message}`);
                }
            });

            // Return results with fallbacks for failed requests
            return {
                rawData: results[0].status === 'fulfilled' ? results[0].value : { data: [] },
                summary: results[1].status === 'fulfilled' ? results[1].value : { 
                    data: { count: 0, avg_value: null, max_value: null, min_value: null, trend: 'neutral' }
                },
                analytics: results[2].status === 'fulfilled' ? results[2].value : {
                    market_sentiment: { risk_appetite: 'N/A', dollar_strengthening: false, inflation_pressure: 'N/A' },
                    monetary_policy: { fed_stance: 'N/A', liquidity_conditions: 'N/A', yield_curve: 'N/A' },
                    trends: { dollar_trend: 'N/A', yield_trend: 'N/A', liquidity_trend: 'N/A' },
                    summary: { total_records: 0, date_range: { earliest: null, latest: null } }
                },
                enhancedAnalytics: results[3].status === 'fulfilled' ? results[3].value : {
                    data: { individual_analytics: {}, correlation_matrix: {} }
                },
                availableMetrics: results[4].status === 'fulfilled' ? results[4].value : {
                    overlay_metrics: [],
                    event_metrics: [],
                    metadata: { total_overlay_metrics: 0, total_event_metrics: 0, use_cases: [] }
                },
                events: results[5].status === 'fulfilled' ? results[5].value : { data: [] },
                eventsSummary: results[6].status === 'fulfilled' ? results[6].value : {
                    data: { total_events: 0, events_with_forecast: 0, avg_surprise_pct: null, latest_release_date: null }
                }
            };
            
        } catch (error) {
            console.error('❌ Error fetching all data:', error);
            // Return empty fallback data
            return {
                rawData: { data: [] },
                summary: { data: { count: 0, avg_value: null, max_value: null, min_value: null, trend: 'neutral' } },
                analytics: { market_sentiment: { risk_appetite: 'N/A' }, monetary_policy: { fed_stance: 'N/A' }, trends: { dollar_trend: 'N/A' }, summary: { total_records: 0 } },
                enhancedAnalytics: { data: { individual_analytics: {}, correlation_matrix: {} } },
                availableMetrics: { overlay_metrics: [], metadata: { total_overlay_metrics: 0, use_cases: [] } },
                events: { data: [] },
                eventsSummary: { data: { total_events: 0, avg_surprise_pct: null } }
            };
        }
    }

    /**
     * Update filters and refetch data
     */
    async updateFilters(newFilters) {
        Object.assign(this.filters, newFilters);
        return await this.fetchAllData();
    }

    /**
     * Get cached data
     */
    getCachedData() {
        return {
            ...this.cache,
            isStale: this.cache.lastUpdate ? 
                (Date.now() - this.cache.lastUpdate.getTime()) > 300000 : true // 5 minutes
        };
    }

    /**
     * Format data for charts
     * Charts need data sorted ascending (oldest to newest) for proper timeline visualization
     */
    formatForChart(data, xField = 'date', yField = 'value') {
        if (!data?.data || !Array.isArray(data.data) || data.data.length === 0) {
            return { labels: [], values: [] };
        }
        
        try {
            // Sort data ascending (oldest first) for timeline chart
            const sortedData = [...data.data].sort((a, b) => {
                return new Date(a[xField]) - new Date(b[xField]);
            });
            
            return {
                labels: sortedData.map(item => {
                    if (!item[xField]) return 'N/A';
                    const date = new Date(item[xField]);
                    return isNaN(date.getTime()) ? 'N/A' : date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }),
                values: sortedData.map(item => {
                    const value = parseFloat(item[yField]);
                    return isNaN(value) ? 0 : value;
                })
            };
        } catch (error) {
            console.warn('Error formatting chart data:', error);
            return { labels: [], values: [] };
        }
    }

    /**
     * Get metric description and significance
     */
    getMetricInfo(metric) {
        const metricMap = {
            'DXY': {
                name: 'US Dollar Index',
                description: 'Measures strength of USD against basket of currencies',
                significance: 'Risk-on/risk-off indicator, impacts all asset classes',
                correlation: 'Inverse correlation with BTC (-0.72)'
            },
            'YIELD_10Y': {
                name: '10-Year Treasury Yield',
                description: 'Long-term US government bond yield',
                significance: 'Risk appetite gauge, monetary policy expectations',
                correlation: 'Inverse correlation with BTC (-0.65)'
            },
            'YIELD_2Y': {
                name: '2-Year Treasury Yield',
                description: 'Short-term US government bond yield',
                significance: 'Monetary policy expectations, short-term funding costs',
                correlation: 'Fed policy sensitivity indicator'
            },
            'FED_FUNDS': {
                name: 'Federal Funds Rate',
                description: 'Federal Reserve benchmark interest rate',
                significance: 'Cost of capital, liquidity conditions',
                correlation: 'Inverse correlation with risk assets'
            },
            'M2': {
                name: 'M2 Money Supply',
                description: 'Broad measure of money supply including cash, deposits, and near money',
                significance: 'Liquidity indicator, inflation pressure gauge',
                correlation: 'Strong positive correlation with BTC (+0.81)'
            },
            'RRP': {
                name: 'Reverse Repo Operations',
                description: 'Money parked at Federal Reserve overnight',
                significance: 'Liquidity drain indicator, money market conditions',
                correlation: 'Inverse correlation with risk assets'
            },
            'TGA': {
                name: 'Treasury General Account',
                description: 'US Treasury cash balance at Federal Reserve',
                significance: 'Government liquidity operations, market impact',
                correlation: 'Inverse correlation with market liquidity'
            }
        };
        
        return metricMap[metric] || {
            name: metric,
            description: 'Macro economic indicator',
            significance: 'Market impact indicator',
            correlation: 'Various correlations with risk assets'
        };
    }

    /**
     * Calculate trend from data array
     */
    calculateTrend(values) {
        if (!values || values.length < 2) return 'neutral';
        
        const recent = values.slice(-5); // Last 5 values
        const older = values.slice(-10, -5); // Previous 5 values
        
        const recentAvg = recent.reduce((a, b) => a + b, 0) / recent.length;
        const olderAvg = older.reduce((a, b) => a + b, 0) / older.length;
        
        const change = ((recentAvg - olderAvg) / olderAvg) * 100;
        
        if (change > 2) return 'rising';
        if (change < -2) return 'falling';
        return 'neutral';
    }

    /**
     * Format numbers for display
     */
    formatNumber(value, decimals = 2) {
        if (value === null || value === undefined) return 'N/A';
        const num = parseFloat(value);
        if (isNaN(num)) return 'N/A';
        return num.toFixed(decimals);
    }

    /**
     * Format percentage
     */
    formatPercentage(value, decimals = 2) {
        if (value === null || value === undefined) return 'N/A';
        const num = parseFloat(value);
        if (isNaN(num)) return 'N/A';
        return (num >= 0 ? '+' : '') + num.toFixed(decimals) + '%';
    }

    /**
     * Format currency
     */
    formatCurrency(value, currency = 'USD', decimals = 2) {
        if (value === null || value === undefined) return 'N/A';
        const num = parseFloat(value);
        if (isNaN(num)) return 'N/A';
        
        if (num >= 1e12) return '$' + (num / 1e12).toFixed(1) + 'T';
        if (num >= 1e9) return '$' + (num / 1e9).toFixed(1) + 'B';
        if (num >= 1e6) return '$' + (num / 1e6).toFixed(1) + 'M';
        
        return '$' + num.toFixed(decimals);
    }

    /**
     * Get trend class for styling
     */
    getTrendClass(trend) {
        switch (trend) {
            case 'rising': return 'text-success';
            case 'falling': return 'text-danger';
            default: return 'text-secondary';
        }
    }

    /**
     * Get trend icon
     */
    getTrendIcon(trend) {
        switch (trend) {
            case 'rising': return '↗️';
            case 'falling': return '↘️';
            default: return '➡️';
        }
    }
}

// Export for use in dashboard
window.MacroOverlayRawController = MacroOverlayRawController;
