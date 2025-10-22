/**
 * Open Interest Internal API Handler
 * 
 * Handles data from internal DragonFortune API (test.dragonfortune.ai)
 * for the bottom section of the hybrid dashboard
 */

// Analytics Panel Controller
function analyticsPanel() {
    return {
        loading: false,
        analytics: null,

        async init() {
            console.log('📊 Analytics Panel initialized');
            await this.loadAnalytics();
        },

        async loadAnalytics() {
            try {
                this.loading = true;
                console.log('📡 Fetching analytics from internal API...');

                // Use the same pattern as the working open-interest page
                const baseMeta = document.querySelector('meta[name="api-base-url"]');
                const configuredBase = (baseMeta?.content || "").trim();
                
                // Use relative URL as default (same pattern as working page)
                let url = '/api/open-interest/analytics?symbol=BTCUSDT&interval=5m&limit=2000&with_price=true';
                if (configuredBase) {
                    const normalizedBase = configuredBase.endsWith("/")
                        ? configuredBase.slice(0, -1)
                        : configuredBase;
                    url = `${normalizedBase}/api/open-interest/analytics?symbol=BTCUSDT&interval=5m&limit=2000&with_price=true`;
                }

                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                this.analytics = data;

                console.log('✅ Analytics berhasil dimuat:', this.analytics);

            } catch (error) {
                console.error('❌ Error memuat analytics:', error);
                this.analytics = null;
            } finally {
                this.loading = false;
            }
        },

        formatOI(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            if (num >= 1e9) return (num / 1e9).toFixed(2) + 'B';
            if (num >= 1e6) return (num / 1e6).toFixed(2) + 'M';
            if (num >= 1e3) return (num / 1e3).toFixed(2) + 'K';
            return num.toFixed(2);
        },

        formatChange(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const sign = value >= 0 ? '+' : '';
            return `${sign}${value.toFixed(2)}%`;
        },

        getChangeClass(value) {
            if (value > 0) return 'text-success';
            if (value < 0) return 'text-danger';
            return 'text-secondary';
        },

        getTrendClass(trend) {
            const trendMap = {
                'bullish': 'text-bg-success',
                'bearish': 'text-bg-danger',
                'neutral': 'text-bg-secondary',
                'sideways': 'text-bg-info'
            };
            return trendMap[trend?.toLowerCase()] || 'text-bg-secondary';
        }
    };
}

// Insights Panel Controller
function insightsPanel() {
    return {
        loading: false,
        insights: [],

        async init() {
            console.log('🚨 Insights Panel initialized');
            await this.loadInsights();
        },

        async loadInsights() {
            try {
                this.loading = true;
                console.log('📡 Fetching insights from internal API...');

                // Use the same pattern as the working open-interest page
                const baseMeta = document.querySelector('meta[name="api-base-url"]');
                const configuredBase = (baseMeta?.content || "").trim();
                
                // Use relative URL as default (same pattern as working page)
                let url = '/api/open-interest/analytics?symbol=BTCUSDT&interval=5m&limit=2000&with_price=true';
                if (configuredBase) {
                    const normalizedBase = configuredBase.endsWith("/")
                        ? configuredBase.slice(0, -1)
                        : configuredBase;
                    url = `${normalizedBase}/api/open-interest/analytics?symbol=BTCUSDT&interval=5m&limit=2000&with_price=true`;
                }

                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                // Extract insights from analytics data
                this.insights = data.insights || [];

                console.log('✅ Insights loaded:', this.insights);

            } catch (error) {
                console.error('❌ Error loading insights:', error);
                this.insights = [];
            } finally {
                this.loading = false;
            }
        },

        getInsightClass(severity) {
            const severityMap = {
                'high': 'alert-danger',
                'medium': 'alert-warning',
                'low': 'alert-info',
                'info': 'alert-primary'
            };
            return severityMap[severity?.toLowerCase()] || 'alert-secondary';
        },

        getInsightIcon(severity) {
            const iconMap = {
                'high': '🚨',
                'medium': '⚠️',
                'low': 'ℹ️',
                'info': '💡'
            };
            return iconMap[severity?.toLowerCase()] || '📊';
        }
    };
}

// Exchange Data Table Controller
function exchangeDataTable() {
    return {
        loading: false,
        exchangeData: [],

        async init() {
            console.log('🏦 Exchange Data Table initialized');
            await this.loadExchangeData();
        },

        async loadExchangeData() {
            try {
                this.loading = true;
                console.log('📡 Fetching exchange data from internal API...');

                // Use the same pattern as the working open-interest page
                const baseMeta = document.querySelector('meta[name="api-base-url"]');
                const configuredBase = (baseMeta?.content || "").trim();
                const apiBase = configuredBase ? configuredBase : 'https://test.dragonfortune.ai';
                
                const params = new URLSearchParams();
                params.append('exchange', 'Binance');
                params.append('symbol', 'BTC');
                params.append('limit', '1000');
                params.append('pivot', 'true');

                const url = `${apiBase}/api/open-interest/exchange?${params.toString()}`;

                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                
                // Filter and sort data like in the working page
                let filteredData = data.data || [];
                
                // Filter by symbol BTC
                filteredData = filteredData.filter(item => 
                    item.symbol_coin === 'BTC' || 
                    item.symbol === 'BTC' ||
                    (item.symbol_coin && item.symbol_coin.toUpperCase() === 'BTC')
                );
                
                // Filter by exchange Binance
                filteredData = filteredData.filter(item => 
                    item.exchange === 'Binance' ||
                    (item.exchange && item.exchange.toLowerCase() === 'binance')
                );
                
                // Sort by timestamp descending (newest first)
                this.exchangeData = filteredData.sort((a, b) => {
                    if (!a.ts) return 1;
                    if (!b.ts) return -1;
                    return new Date(b.ts) - new Date(a.ts);
                });

                console.log('✅ Exchange data loaded:', this.exchangeData.length, 'records');

            } catch (error) {
                console.error('❌ Error loading exchange data:', error);
                this.exchangeData = [];
            } finally {
                this.loading = false;
            }
        },

        formatTimestamp(ts) {
            if (!ts) return '--';
            const date = new Date(ts * 1000); // Convert Unix timestamp to milliseconds
            return date.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        formatOI(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            if (num >= 1e9) return '$' + (num / 1e9).toFixed(2) + 'B';
            if (num >= 1e6) return '$' + (num / 1e6).toFixed(2) + 'M';
            if (num >= 1e3) return '$' + (num / 1e3).toFixed(2) + 'K';
            return '$' + num.toFixed(2);
        }
    };
}

// History Data Table Controller
function historyDataTable() {
    return {
        loading: false,
        historyData: [],

        async init() {
            console.log('📈 History Data Table initialized');
            await this.loadHistoryData();
        },

        async loadHistoryData() {
            try {
                this.loading = true;
                console.log('📡 Fetching history data from internal API...');

                // Use the same pattern as the working open-interest page
                const baseMeta = document.querySelector('meta[name="api-base-url"]');
                const configuredBase = (baseMeta?.content || "").trim();
                
                const params = new URLSearchParams();
                params.append('interval', '5m');
                params.append('limit', '1000');
                params.append('pivot', 'true');
                params.append('with_price', 'true');
                params.append('symbol', 'BTCUSDT');

                // Use relative URL as default (same pattern as working page)
                let url = `/api/open-interest/history?${params.toString()}`;
                if (configuredBase) {
                    const normalizedBase = configuredBase.endsWith("/")
                        ? configuredBase.slice(0, -1)
                        : configuredBase;
                    url = `${normalizedBase}/api/open-interest/history?${params.toString()}`;
                }

                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                
                // Filter and sort data like in the working page
                let filteredData = data.data || [];
                
                // Filter by pair BTCUSDT
                filteredData = filteredData.filter(item => 
                    item.pair === 'BTCUSDT' ||
                    item.symbol === 'BTCUSDT' ||
                    (item.pair && item.pair.toUpperCase() === 'BTCUSDT')
                );
                
                // Sort by timestamp descending (newest first)
                this.historyData = filteredData.sort((a, b) => {
                    if (!a.ts) return 1;
                    if (!b.ts) return -1;
                    return new Date(b.ts) - new Date(a.ts);
                });

                console.log('✅ History data loaded:', this.historyData.length, 'records');

            } catch (error) {
                console.error('❌ Error loading history data:', error);
                this.historyData = [];
            } finally {
                this.loading = false;
            }
        },

        formatTimestamp(ts) {
            if (!ts) return '--';
            const date = new Date(ts * 1000); // Convert Unix timestamp to milliseconds
            return date.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        formatOI(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            if (num >= 1e9) return '$' + (num / 1e9).toFixed(2) + 'B';
            if (num >= 1e6) return '$' + (num / 1e6).toFixed(2) + 'M';
            if (num >= 1e3) return '$' + (num / 1e3).toFixed(2) + 'K';
            return '$' + num.toFixed(2);
        }
    };
}

// Make controllers available globally for Alpine.js
window.analyticsPanel = analyticsPanel;
window.insightsPanel = insightsPanel;
window.exchangeDataTable = exchangeDataTable;
window.historyDataTable = historyDataTable;

console.log('✅ Open Interest Internal API handlers loaded');