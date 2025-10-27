/**
 * Exchange Liquidations Table Controller
 * Menampilkan breakdown liquidation per exchange
 */

function exchangeLiquidationsTable() {
    return {
        // State
        loading: false,
        exchangeData: [],
        
        // Filters
        selectedSymbol: 'BTC',
        selectedTimeRange: '1h',
        sortBy: 'liquidation_usd',
        sortDirection: 'desc',
        
        // Available options
        availableSymbols: ['ALL', 'BTC', 'ETH', 'SOL', 'XRP', 'ADA'],
        availableTimeRanges: [
            { value: '1h', label: '1 Hour' },
            { value: '4h', label: '4 Hours' },
            { value: '12h', label: '12 Hours' },
            { value: '24h', label: '24 Hours' }
        ],
        
        // API Configuration
        baseUrl: '/api/coinglass',
        
        init() {
            console.log('🚀 Initializing Exchange Liquidations Table');
            this.loadData();
            
            // Auto refresh every 2 minutes
            setInterval(() => {
                this.loadData();
            }, 120000);
        },
        
        async loadData() {
            this.loading = true;
            
            try {
                const params = new URLSearchParams({
                    symbol: this.selectedSymbol === 'ALL' ? 'BTC' : this.selectedSymbol,
                    range: this.selectedTimeRange
                });
                
                const response = await fetch(`${this.baseUrl}/liquidation-exchange-list?${params}`, {
                    headers: {
                        'accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success && data.data) {
                    this.processExchangeData(data.data);
                } else {
                    console.error('API Error:', data);
                    this.useFallbackData();
                }
                
            } catch (error) {
                console.error('🚨 Failed to load exchange data:', error);
                this.useFallbackData();
            } finally {
                this.loading = false;
            }
        },
        
        processExchangeData(data) {
            this.exchangeData = data.map(item => ({
                ...item,
                longShortRatio: item.shortLiquidation_usd > 0 ? 
                    (item.longLiquidation_usd / item.shortLiquidation_usd).toFixed(2) : 0,
                marketShare: 0 // Will be calculated after processing
            }));
            
            // Calculate market share
            const totalLiquidation = this.exchangeData.reduce((sum, item) => 
                sum + (parseFloat(item.liquidation_usd) || 0), 0
            );
            
            this.exchangeData = this.exchangeData.map(item => ({
                ...item,
                marketShare: totalLiquidation > 0 ? 
                    ((parseFloat(item.liquidation_usd) || 0) / totalLiquidation * 100).toFixed(2) : 0
            }));
            
            this.sortTable(this.sortBy);
        },
        
        useFallbackData() {
            // Demo data for testing
            this.exchangeData = [
                {
                    exchange: 'All',
                    liquidation_usd: 3950000,
                    longLiquidation_usd: 2080000,
                    shortLiquidation_usd: 1870000,
                    marketShare: 100,
                    longShortRatio: 1.11
                },
                {
                    exchange: 'Binance',
                    liquidation_usd: 1960000,
                    longLiquidation_usd: 988770,
                    shortLiquidation_usd: 972070,
                    marketShare: 49.68,
                    longShortRatio: 1.02
                },
                {
                    exchange: 'Bybit',
                    liquidation_usd: 876170,
                    longLiquidation_usd: 505570,
                    shortLiquidation_usd: 370600,
                    marketShare: 22.2,
                    longShortRatio: 1.36
                },
                {
                    exchange: 'OKX',
                    liquidation_usd: 417700,
                    longLiquidation_usd: 230890,
                    shortLiquidation_usd: 186800,
                    marketShare: 10.58,
                    longShortRatio: 1.24
                }
            ];
        },
        
        // Filter Methods
        setSymbol(symbol) {
            this.selectedSymbol = symbol;
            this.loadData();
        },
        
        setTimeRange(range) {
            this.selectedTimeRange = range;
            this.loadData();
        },
        
        sortTable(column) {
            if (this.sortBy === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = column;
                this.sortDirection = 'desc';
            }
            
            this.exchangeData.sort((a, b) => {
                let aVal = parseFloat(a[column]) || 0;
                let bVal = parseFloat(b[column]) || 0;
                
                if (column === 'exchange') {
                    aVal = a[column].toLowerCase();
                    bVal = b[column].toLowerCase();
                    return this.sortDirection === 'asc' ? 
                        aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
                }
                
                return this.sortDirection === 'asc' ? aVal - bVal : bVal - aVal;
            });
        },
        
        refreshData() {
            this.loadData();
        },
        
        // UI Helper Methods
        getExchangeColor(exchange) {
            const colors = {
                'All': '#6b7280',
                'Binance': '#f0b90b',
                'OKX': '#000000',
                'Bybit': '#f7a600',
                'BitMEX': '#e43e3b',
                'Bitfinex': '#16a085',
                'Huobi': '#2ebd85',
                'HTX': '#2ebd85',
                'Gate': '#64748b',
                'Hyperliquid': '#ff6b6b',
                'CoinEx': '#4da6ff',
                'Bitmex': '#e43e3b'
            };
            return colors[exchange] || '#6b7280';
        },
        
        getRateClass(exchange) {
            if (exchange === 'All') return 'all-rate';
            
            const rate = parseFloat(this.exchangeData.find(e => e.exchange === exchange)?.marketShare || 0);
            if (rate > 30) return 'high-rate';
            if (rate > 10) return 'medium-rate';
            return 'low-rate';
        },
        
        getLongShortRatioClass(ratio) {
            const numRatio = parseFloat(ratio);
            if (numRatio > 1.5) return 'long-heavy';
            if (numRatio > 0.67) return 'balanced';
            return 'short-heavy';
        },
        
        formatLiquidation(value) {
            const num = parseFloat(value) || 0;
            if (num >= 1000000) {
                return '$' + (num / 1000000).toFixed(2) + 'M';
            } else if (num >= 1000) {
                return '$' + (num / 1000).toFixed(1) + 'K';
            } else {
                return '$' + num.toFixed(0);
            }
        },
        
        formatPercentage(value) {
            return parseFloat(value).toFixed(2) + '%';
        },
        
        showExchangeDetails(exchange) {
            console.log('Exchange details:', exchange);
            
            alert(`
${exchange.exchange} Liquidation Details:
Total Liquidations: ${this.formatLiquidation(exchange.liquidation_usd)}
Long Liquidations: ${this.formatLiquidation(exchange.longLiquidation_usd)}
Short Liquidations: ${this.formatLiquidation(exchange.shortLiquidation_usd)}
Market Share: ${exchange.marketShare}%
Long/Short Ratio: ${exchange.longShortRatio}
Time Range: ${this.selectedTimeRange}
            `);
        },
        
        getSortIcon(column) {
            if (this.sortBy !== column) return '↕️';
            return this.sortDirection === 'asc' ? '↑' : '↓';
        }
    };
}