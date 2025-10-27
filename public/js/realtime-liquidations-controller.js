/**
 * Real-Time Liquidations Table Controller
 * Menggunakan WebSocket Coinglass untuk data liquidation real-time
 * 
 * WebSocket Endpoint: wss://open-ws.coinglass.com/ws-api?cg-api-key={api_key}
 * Channel: liquidationOrders
 */

function realtimeLiquidationsTable() {
    return {
        // Connection State
        isConnected: false,
        isPaused: false,
        soundEnabled: true,
        ws: null,
        reconnectAttempts: 0,
        maxReconnectAttempts: 5,
        
        // Data State
        liquidations: [],
        filteredLiquidations: [],
        maxLiquidations: 100, // Keep last 100 liquidations
        
        // Filters
        selectedFilter: 'all',
        selectedExchange: 'all',
        selectedSymbol: 'all',
        sortBy: 'time',
        
        // Available options for filters
        availableSymbols: [],
        availableExchanges: [],
        
        // Statistics
        totalLiquidations: 0,
        longCount: 0,
        shortCount: 0,
        totalVolume: 0,
        lastUpdateTime: '--',
        
        // WebSocket Configuration
        wsUrl: 'wss://open-ws.coinglass.com/ws-api?cg-api-key=f78a531eb0ef4d06ba9559ec16a6b0c2',
        
        init() {
            console.log('🚀 Initializing Real-Time Liquidations Table');
            
            // Initialize with common exchanges as fallback
            this.availableExchanges = ['Binance', 'OKX', 'Bybit', 'BitMEX', 'Bitfinex'];
            
            this.connectWebSocket();
            this.setupAudio();
            
            // Update time every second
            setInterval(() => {
                this.updateLastUpdateTime();
            }, 1000);
        },
        
        connectWebSocket() {
            try {
                console.log('🔌 Connecting to Coinglass WebSocket...');
                this.ws = new WebSocket(this.wsUrl);
                
                this.ws.onopen = () => {
                    console.log('✅ WebSocket connected');
                    this.isConnected = true;
                    this.reconnectAttempts = 0;
                    
                    // Subscribe to liquidation orders
                    this.subscribeToLiquidations();
                };
                
                this.ws.onmessage = (event) => {
                    this.handleWebSocketMessage(event);
                };
                
                this.ws.onclose = (event) => {
                    console.log('❌ WebSocket disconnected:', event.code, event.reason);
                    this.isConnected = false;
                    this.handleReconnect();
                };
                
                this.ws.onerror = (error) => {
                    console.error('🚨 WebSocket error:', error);
                    this.isConnected = false;
                };
                
            } catch (error) {
                console.error('🚨 Failed to connect WebSocket:', error);
                this.handleReconnect();
            }
        },
        
        subscribeToLiquidations() {
            if (this.ws && this.ws.readyState === WebSocket.OPEN) {
                const subscribeMessage = {
                    method: 'subscribe',
                    channels: ['liquidationOrders']
                };
                
                this.ws.send(JSON.stringify(subscribeMessage));
                console.log('📡 Subscribed to liquidationOrders channel');
            }
        },
        
        handleWebSocketMessage(event) {
            try {
                const data = JSON.parse(event.data);
                
                if (data.channel === 'liquidationOrders' && data.data) {
                    this.processLiquidationData(data.data);
                }
            } catch (error) {
                console.error('🚨 Error parsing WebSocket message:', error);
            }
        },
        
        processLiquidationData(liquidationArray) {
            if (!Array.isArray(liquidationArray) || this.isPaused) {
                return;
            }
            
            liquidationArray.forEach(liquidation => {
                // Add unique ID and timestamp
                const processedLiquidation = {
                    ...liquidation,
                    id: Date.now() + Math.random(),
                    receivedAt: Date.now()
                };
                
                // Add to beginning of array (newest first)
                this.liquidations.unshift(processedLiquidation);
                
                // Update available symbols and exchanges
                this.updateAvailableOptions(processedLiquidation);
                
                // Play sound for large liquidations
                if (this.soundEnabled && liquidation.volUsd > 50000) {
                    this.playLiquidationSound(liquidation.side);
                }
            });
            
            // Keep only last N liquidations
            if (this.liquidations.length > this.maxLiquidations) {
                this.liquidations = this.liquidations.slice(0, this.maxLiquidations);
            }
            
            // Update statistics
            this.updateStatistics();
            
            // Apply current filters
            this.applyFilter();
            
            // Update last update time
            this.lastUpdateTime = new Date().toLocaleTimeString();
        },
        
        updateAvailableOptions(liquidation) {
            // Update available symbols
            if (liquidation.symbol && !this.availableSymbols.includes(liquidation.symbol)) {
                this.availableSymbols.push(liquidation.symbol);
                this.availableSymbols.sort();
            }
            
            // Update available exchanges
            if (liquidation.exName && !this.availableExchanges.includes(liquidation.exName)) {
                this.availableExchanges.push(liquidation.exName);
                this.availableExchanges.sort();
            }
        },
        
        updateStatistics() {
            this.totalLiquidations = this.liquidations.length;
            this.longCount = this.liquidations.filter(l => l.side === 1).length;
            this.shortCount = this.liquidations.filter(l => l.side === 2).length;
            this.totalVolume = this.liquidations.reduce((sum, l) => sum + (l.volUsd || 0), 0);
        },
        
        applyFilter() {
            let filtered = [...this.liquidations];
            
            // Filter by symbol
            if (this.selectedSymbol !== 'all') {
                filtered = filtered.filter(l => l.symbol === this.selectedSymbol);
            }
            
            // Filter by side
            if (this.selectedFilter === 'long') {
                filtered = filtered.filter(l => l.side === 1);
            } else if (this.selectedFilter === 'short') {
                filtered = filtered.filter(l => l.side === 2);
            } else if (this.selectedFilter === 'large') {
                filtered = filtered.filter(l => l.volUsd > 10000);
            }
            
            // Filter by exchange
            if (this.selectedExchange !== 'all') {
                filtered = filtered.filter(l => l.exName === this.selectedExchange);
            }
            
            // Apply sorting
            this.applySortingToArray(filtered);
            
            this.filteredLiquidations = filtered;
        },
        
        applySorting() {
            this.applyFilter();
        },
        
        applySortingToArray(array) {
            switch (this.sortBy) {
                case 'time':
                    array.sort((a, b) => b.time - a.time);
                    break;
                case 'value':
                    array.sort((a, b) => b.volUsd - a.volUsd);
                    break;
                case 'price':
                    array.sort((a, b) => b.price - a.price);
                    break;
            }
        },
        
        resetFilters() {
            this.selectedFilter = 'all';
            this.selectedExchange = 'all';
            this.selectedSymbol = 'all';
            this.sortBy = 'time';
            this.applyFilter();
        },
        
        clearTable() {
            this.liquidations = [];
            this.filteredLiquidations = [];
            this.updateStatistics();
        },
        
        togglePause() {
            this.isPaused = !this.isPaused;
            console.log(this.isPaused ? '⏸️ Paused' : '▶️ Resumed');
        },
        
        toggleSound() {
            this.soundEnabled = !this.soundEnabled;
            console.log(this.soundEnabled ? '🔊 Sound enabled' : '🔇 Sound disabled');
        },
        
        reconnect() {
            if (this.ws) {
                this.ws.close();
            }
            this.connectWebSocket();
        },
        
        handleReconnect() {
            if (this.reconnectAttempts < this.maxReconnectAttempts) {
                this.reconnectAttempts++;
                const delay = Math.min(1000 * Math.pow(2, this.reconnectAttempts), 30000);
                
                console.log(`🔄 Reconnecting in ${delay/1000}s (attempt ${this.reconnectAttempts}/${this.maxReconnectAttempts})`);
                
                setTimeout(() => {
                    this.connectWebSocket();
                }, delay);
            } else {
                console.error('🚨 Max reconnection attempts reached');
            }
        },
        
        setupAudio() {
            // Create audio context for liquidation sounds
            try {
                this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            } catch (error) {
                console.warn('Audio not supported:', error);
                this.soundEnabled = false;
            }
        },
        
        playLiquidationSound(side) {
            if (!this.audioContext || !this.soundEnabled) return;
            
            try {
                const oscillator = this.audioContext.createOscillator();
                const gainNode = this.audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(this.audioContext.destination);
                
                // Different frequencies for long vs short
                oscillator.frequency.setValueAtTime(side === 1 ? 800 : 400, this.audioContext.currentTime);
                oscillator.type = 'sine';
                
                gainNode.gain.setValueAtTime(0.1, this.audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 0.3);
                
                oscillator.start(this.audioContext.currentTime);
                oscillator.stop(this.audioContext.currentTime + 0.3);
            } catch (error) {
                console.warn('Failed to play sound:', error);
            }
        },
        
        updateLastUpdateTime() {
            if (this.liquidations.length > 0) {
                const lastLiquidation = this.liquidations[0];
                const timeDiff = Date.now() - lastLiquidation.receivedAt;
                
                if (timeDiff < 60000) {
                    this.lastUpdateTime = `${Math.floor(timeDiff / 1000)}s ago`;
                } else {
                    this.lastUpdateTime = `${Math.floor(timeDiff / 60000)}m ago`;
                }
            }
        },
        
        // UI Helper Methods
        getLiquidationRowClass(liquidation) {
            const classes = [];
            
            // Add animation class for new liquidations
            if (Date.now() - liquidation.receivedAt < 2000) {
                classes.push('new-liquidation');
            }
            
            // Add size-based classes
            if (liquidation.volUsd > 100000) {
                classes.push('huge-liquidation');
            } else if (liquidation.volUsd > 50000) {
                classes.push('large-liquidation');
            }
            
            return classes.join(' ');
        },
        
        getSideBadgeClass(side) {
            return side === 1 ? 'long' : 'short';
        },
        
        getSideText(side) {
            return side === 1 ? 'Long' : 'Short';
        },
        
        getValueClass(value) {
            if (value > 100000) return 'huge';
            if (value > 50000) return 'large';
            if (value > 10000) return 'medium';
            return 'small';
        },
        
        getSymbolColor(baseAsset) {
            const colors = {
                'BTC': '#f7931a',
                'ETH': '#627eea',
                'BNB': '#f3ba2f',
                'SOL': '#9945ff',
                'ADA': '#0033ad',
                'DOT': '#e6007a',
                'MATIC': '#8247e5',
                'AVAX': '#e84142'
            };
            return colors[baseAsset] || '#6b7280';
        },
        
        getExchangeColor(exchange) {
            const colors = {
                'Binance': '#f0b90b',
                'OKX': '#000000',
                'Bybit': '#f7a600',
                'BitMEX': '#e43e3b',
                'Bitfinex': '#16a085',
                'Huobi': '#2ebd85',
                'KuCoin': '#20d4a6'
            };
            return colors[exchange] || '#6b7280';
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
        
        formatValue(value) {
            if (value >= 1000000) {
                return '$' + (value / 1000000).toFixed(2) + 'M';
            } else if (value >= 1000) {
                return '$' + (value / 1000).toFixed(1) + 'K';
            } else {
                return '$' + value.toFixed(0);
            }
        },
        
        formatTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleTimeString('en-US', { 
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        },
        
        showLiquidationDetails(liquidation) {
            // Show detailed modal or tooltip
            console.log('Liquidation details:', liquidation);
            
            // You can implement a modal here to show more details
            alert(`
Liquidation Details:
Symbol: ${liquidation.symbol}
Exchange: ${liquidation.exName}
Side: ${this.getSideText(liquidation.side)}
Price: ${this.formatPrice(liquidation.price)}
Value: ${this.formatValue(liquidation.volUsd)}
Time: ${this.formatTime(liquidation.time)}
            `);
        },
        
        // Cleanup on destroy
        destroy() {
            if (this.ws) {
                this.ws.close();
            }
            if (this.audioContext) {
                this.audioContext.close();
            }
        }
    };
}