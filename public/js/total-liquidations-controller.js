/**
 * Total Liquidations Table Controller
 * Menggunakan Coinglass API endpoint: /api/futures/liquidation/coin-list
 */

function totalLiquidationsTable() {
    return {
        // State
        loading: false,
        liquidationsData: [],
        selectedExchange: 'Binance',
        lastUpdateTime: '--',
        
        // API Configuration - Using Laravel backend as proxy
        baseUrl: '/api/coinglass',
        
        init() {
            console.log('🚀 Initializing Total Liquidations Table');
            this.loadData();
            
            // Auto refresh every 30 seconds
            setInterval(() => {
                this.loadData();
            }, 30000);
        },
        
        async loadData() {
            this.loading = true;
            
            try {
                const response = await fetch(`${this.baseUrl}/liquidation-coin-list?exchange=${this.selectedExchange}`, {
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
                    this.processLiquidationData(data.data);
                    this.lastUpdateTime = new Date().toLocaleTimeString();
                } else {
                    console.error('API Error:', data);
                    throw new Error(data.message || 'Invalid API response');
                }
                
            } catch (error) {
                console.error('🚨 Failed to load liquidation data:', error);
                // Use fallback data for demo
                this.useFallbackData();
            } finally {
                this.loading = false;
            }
        },  
      
        processLiquidationData(data) {
            // Sort by total 24h liquidation volume (descending)
            this.liquidationsData = data
                .map(coin => ({
                    ...coin,
                    totalLiquidation24h: (coin.long_liquidation_usd_24h || 0) + (coin.short_liquidation_usd_24h || 0),
                    // Add mock price data (in real implementation, get from another API)
                    price: this.getMockPrice(coin.symbol),
                    priceChange: this.getMockPriceChange(coin.symbol)
                }))
                .sort((a, b) => b.totalLiquidation24h - a.totalLiquidation24h)
                .slice(0, 20); // Top 20 coins
        },
        
        // Mock price data - replace with real price API
        getMockPrice(symbol) {
            const prices = {
                'BTC': 114259.5,
                'ETH': 4126.2,
                'SOL': 198.92,
                'XRP': 2.6312,
                'HYPE': 47.778,
                'DOGE': 0.20381,
                'BNB': 1136.11,
                'SUI': 2.6512
            };
            return prices[symbol] || Math.random() * 1000;
        },
        
        getMockPriceChange(symbol) {
            const changes = {
                'BTC': -2.55,
                'ETH': 4.38,
                'SOL': 2.46,
                'XRP': 1.36,
                'HYPE': 8.51,
                'DOGE': 3.69,
                'BNB': 1.77,
                'SUI': 4.64
            };
            return changes[symbol] || (Math.random() - 0.5) * 10;
        },
        
        refreshData() {
            this.loadData();
        },
        
        useFallbackData() {
            // Fallback data for demo purposes
            const fallbackData = [
                {
                    symbol: 'BTC',
                    liquidation_usd_24h: 269597.27934,
                    long_liquidation_usd_24h: 92212.0849876,
                    short_liquidation_usd_24h: 177385.1943524,
                    liquidation_usd_12h: 113075.1808608,
                    long_liquidation_usd_12h: 61019.2107374,
                    short_liquidation_usd_12h: 52055.9701234,
                    liquidation_usd_4h: 44160.416653,
                    long_liquidation_usd_4h: 23177.0921527,
                    short_liquidation_usd_4h: 20983.3245003,
                    liquidation_usd_1h: 302.1349,
                    long_liquidation_usd_1h: 302.1349,
                    short_liquidation_usd_1h: 0
                },
                {
                    symbol: 'ETH',
                    liquidation_usd_24h: 140285.13768358,
                    long_liquidation_usd_24h: 65827.7857128,
                    short_liquidation_usd_24h: 74457.35197078,
                    liquidation_usd_12h: 89953.44924214,
                    long_liquidation_usd_12h: 38680.03749136,
                    short_liquidation_usd_12h: 51273.41175078,
                    liquidation_usd_4h: 31913.65493134,
                    long_liquidation_usd_4h: 15045.69059168,
                    short_liquidation_usd_4h: 16867.96433966,
                    liquidation_usd_1h: 4812.55363334,
                    long_liquidation_usd_1h: 2558.54265168,
                    short_liquidation_usd_1h: 2254.01098166
                },
                {
                    symbol: 'SOL',
                    liquidation_usd_24h: 98765.43210,
                    long_liquidation_usd_24h: 45432.10987,
                    short_liquidation_usd_24h: 53333.32223,
                    liquidation_usd_12h: 56789.12345,
                    long_liquidation_usd_12h: 28394.56789,
                    short_liquidation_usd_12h: 28394.55556,
                    liquidation_usd_4h: 23456.78901,
                    long_liquidation_usd_4h: 12345.67890,
                    short_liquidation_usd_4h: 11111.11011,
                    liquidation_usd_1h: 1234.56789,
                    long_liquidation_usd_1h: 678.90123,
                    short_liquidation_usd_1h: 555.66666
                }
            ];
            
            this.processLiquidationData(fallbackData);
            this.lastUpdateTime = new Date().toLocaleTimeString() + ' (Demo)';
        },
        
        getTotalLiquidations() {
            return this.liquidationsData.reduce((sum, coin) => 
                sum + (coin.totalLiquidation24h || 0), 0
            );
        },       
 
        // UI Helper Methods
        getRankingClass(rank) {
            if (rank <= 3) return 'top-rank';
            if (rank <= 10) return 'high-rank';
            return 'normal-rank';
        },
        
        getCoinColor(symbol) {
            const colors = {
                'BTC': '#f7931a',
                'ETH': '#627eea',
                'BNB': '#f3ba2f',
                'SOL': '#9945ff',
                'XRP': '#23292f',
                'ADA': '#0033ad',
                'DOT': '#e6007a',
                'MATIC': '#8247e5',
                'AVAX': '#e84142',
                'DOGE': '#c2a633',
                'HYPE': '#ff6b6b',
                'SUI': '#4da6ff'
            };
            return colors[symbol] || '#6b7280';
        },
        
        getCoinName(symbol) {
            const names = {
                'BTC': 'Bitcoin',
                'ETH': 'Ethereum',
                'BNB': 'BNB',
                'SOL': 'Solana',
                'XRP': 'XRP',
                'ADA': 'Cardano',
                'DOT': 'Polkadot',
                'MATIC': 'Polygon',
                'AVAX': 'Avalanche',
                'DOGE': 'Dogecoin',
                'HYPE': 'Hyperliquid',
                'SUI': 'Sui'
            };
            return names[symbol] || symbol;
        },
        
        getChangeClass(change) {
            return change >= 0 ? 'positive' : 'negative';
        },
        
        formatPrice(price) {
            if (price >= 1000) {
                return '$' + price.toLocaleString('en-US', { 
                    minimumFractionDigits: 2, 
                    maximumFractionDigits: 2 
                });
            } else {
                return '$' + price.toFixed(4);
            }
        },        

        formatPercentage(value) {
            const sign = value >= 0 ? '+' : '';
            return sign + value.toFixed(2) + '%';
        },
        
        formatLiquidation(value) {
            if (!value || value === 0) return '$0';
            
            if (value >= 1000000) {
                return '$' + (value / 1000000).toFixed(2) + 'M';
            } else if (value >= 1000) {
                return '$' + (value / 1000).toFixed(1) + 'K';
            } else {
                return '$' + value.toFixed(0);
            }
        },
        
        showCoinDetails(coin) {
            // Show detailed modal or navigate to coin detail page
            console.log('Coin details:', coin);
            
            alert(`
${coin.symbol} Liquidation Details:
Price: ${this.formatPrice(coin.price)}
24h Change: ${this.formatPercentage(coin.priceChange)}

24h Liquidations:
Long: ${this.formatLiquidation(coin.long_liquidation_usd_24h)}
Short: ${this.formatLiquidation(coin.short_liquidation_usd_24h)}
Total: ${this.formatLiquidation(coin.totalLiquidation24h)}

4h Liquidations:
Long: ${this.formatLiquidation(coin.long_liquidation_usd_4h)}
Short: ${this.formatLiquidation(coin.short_liquidation_usd_4h)}

1h Liquidations:
Long: ${this.formatLiquidation(coin.long_liquidation_usd_1h)}
Short: ${this.formatLiquidation(coin.short_liquidation_usd_1h)}
            `);
        }
    };
}