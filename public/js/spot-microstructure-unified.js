/**
 * Spot Microstructure Unified Controller - FIXED VERSION
 * Handles all spot microstructure data in one page
 * Raw data from providers - no processing, no filtering
 */

class SpotMicrostructureUnified {
    constructor() {
        this.apiBaseUrl = document.querySelector('meta[name="api-base-url"]')?.getAttribute('content') || '';
        this.currentSymbol = 'BTCUSDT';
        this.currentExchange = 'binance';
        this.refreshInterval = 5000; // 5 seconds
        this.intervals = [];
        
        // Chart instances
        this.cvdChart = null;
        this.vwapChart = null;
        this.volumeChart = null;
        
        // Data storage
        this.tradesData = [];
        this.cvdData = [];
        this.vwapData = [];
        this.volumeData = [];
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeCharts();
        this.loadAllData();
        this.startAutoRefresh();
    }

    setupEventListeners() {
        // Symbol and exchange selectors
        document.getElementById('symbolSelect')?.addEventListener('change', (e) => {
            this.currentSymbol = e.target.value;
            this.loadAllData();
        });

        document.getElementById('exchangeSelect')?.addEventListener('change', (e) => {
            this.currentExchange = e.target.value;
            this.loadAllData();
        });
    }

    initializeCharts() {
        // CVD Chart
        const cvdLayout = {
            title: 'Cumulative Volume Delta (CVD)',
            xaxis: { title: 'Time' },
            yaxis: { title: 'CVD' },
            showlegend: true,
            height: 300
        };
        this.cvdChart = Plotly.newPlot('cvdChart', [], cvdLayout);

        // VWAP Chart
        const vwapLayout = {
            title: 'VWAP vs TWAP vs Market Price',
            xaxis: { title: 'Time' },
            yaxis: { title: 'Price (USD)' },
            showlegend: true,
            height: 300
        };
        this.vwapChart = Plotly.newPlot('vwapChart', [], vwapLayout);

        // Volume Chart
        const volumeLayout = {
            title: 'Volume & Trade Statistics',
            xaxis: { title: 'Time' },
            yaxis: { title: 'Volume' },
            showlegend: true,
            height: 300
        };
        this.volumeChart = Plotly.newPlot('volumeChart', [], volumeLayout);
    }

    async loadAllData() {
        try {
            await this.loadUnifiedData();
        } catch (error) {
            console.error('Error loading unified data:', error);
        }
    }

    async loadUnifiedData() {
        try {
            const url = `${this.apiBaseUrl}/api/spot-microstructure/unified?symbol=${this.currentSymbol}&exchange=${this.currentExchange}&limit=100`;
            console.log('Fetching unified data from:', url);
            
            const response = await fetch(url);
            const data = await response.json();

            console.log('Unified data response:', {
                success: data.success,
                trades_count: data.data?.trades?.length || 0,
                cvd_count: data.data?.cvd?.length || 0,
                vwap_count: data.data?.vwap?.length || 0,
                volume_count: data.data?.volume_stats?.length || 0,
                large_orders_count: data.data?.large_orders?.length || 0
            });

            if (data.success && data.data) {
                this.tradesData = data.data.trades || [];
                this.cvdData = data.data.cvd || [];
                this.vwapData = data.data.vwap || [];
                this.volumeData = data.data.volume_stats || [];
                
                this.updateTradesTable();
                this.updateBuySellRatio();
                this.updateCVDChart();
                this.updateCVDDelta();
                this.updateVWAPChart();
                this.updateVWAPMetrics();
                this.updateVolumeChart();
                this.updateVolumeMetrics();
                this.updateLargeOrdersTable(data.data.large_orders || []);
                
                console.log('All UI components updated successfully');
            }
        } catch (error) {
            console.error('Error loading unified data:', error);
            throw error;
        }
    }

    updateTradesTable() {
        const tbody = document.getElementById('tradesTableBody');
        if (!tbody || !this.tradesData.length) {
            this.showError('tradesTableBody', 'No trades data available');
            return;
        }

        tbody.innerHTML = this.tradesData.slice(0, 20).map(trade => {
            const timestamp = trade.timestamp || trade.ts || trade.ts_ms;
            const exchange = trade.exchange || this.currentExchange;
            const symbol = trade.symbol || trade.pair || this.currentSymbol;
            
            // Determine side based on buy/sell volume
            let side = trade.side;
            if (!side || side === 'unknown') {
                const buyVol = trade.buy_volume_quote || trade.buy_volume || 0;
                const sellVol = trade.sell_volume_quote || trade.sell_volume || 0;
                side = buyVol > sellVol ? 'buy' : 'sell';
            }
            
            const qty = trade.quantity || trade.qty || trade.volume_base || 0;
            const price = trade.price || trade.avg_price || 0;
            const notional = trade.quote_quantity || trade.volume_quote || (qty * price);
            
            return `
                <tr>
                    <td>${this.formatTimestamp(timestamp)}</td>
                    <td>${exchange}</td>
                    <td>${symbol}</td>
                    <td><span class="badge ${side === 'buy' ? 'bg-success' : 'bg-danger'}">${side.toUpperCase()}</span></td>
                    <td>${this.formatNumber(qty)}</td>
                    <td>$${this.formatPrice(price)}</td>
                    <td>$${this.formatNumber(notional)}</td>
                </tr>
            `;
        }).join('');
    }

    updateBuySellRatio() {
        if (!this.tradesData.length) return;

        const buyVolume = this.tradesData.reduce((sum, trade) => {
            return sum + (trade.buy_volume_quote || trade.buy_volume || 0);
        }, 0);

        const sellVolume = this.tradesData.reduce((sum, trade) => {
            return sum + (trade.sell_volume_quote || trade.sell_volume || 0);
        }, 0);

        const ratio = sellVolume > 0 ? (buyVolume / sellVolume).toFixed(2) : 'N/A';
        
        const element = document.getElementById('buySellRatio');
        if (element) {
            element.textContent = ratio;
            element.className = parseFloat(ratio) > 1 ? 'text-success' : 'text-danger';
        }
    }

    updateCVDChart() {
        if (!this.cvdData.length) return;

        const timestamps = this.cvdData.map(d => new Date(d.timestamp || d.ts || d.ts_ms));
        const cvdValues = this.cvdData.map(d => d.cvd || d.value || 0);

        const trace = {
            x: timestamps,
            y: cvdValues,
            type: 'scatter',
            mode: 'lines',
            name: 'CVD',
            line: { color: '#007bff', width: 2 }
        };

        const layout = {
            title: 'Cumulative Volume Delta (CVD)',
            xaxis: { title: 'Time' },
            yaxis: { title: 'CVD (USD)' },
            showlegend: true,
            height: 300,
            margin: { l: 60, r: 50, t: 50, b: 50 }
        };

        Plotly.react('cvdChart', [trace], layout);
    }

    updateCVDDelta() {
        if (!this.cvdData.length) return;

        const latest = this.cvdData[0];
        const previous = this.cvdData[1];
        
        if (latest && previous) {
            const latestCvd = latest.cvd || latest.value || 0;
            const previousCvd = previous.cvd || previous.value || 0;
            const delta = latestCvd - previousCvd;
            
            const element = document.getElementById('cvdDelta');
            if (element) {
                element.textContent = this.formatNumber(delta);
                element.className = delta > 0 ? 'text-success' : 'text-danger';
            }
        } else if (latest) {
            const element = document.getElementById('cvdDelta');
            if (element) {
                const cvdValue = latest.cvd || latest.value || 0;
                element.textContent = this.formatNumber(cvdValue);
                element.className = cvdValue > 0 ? 'text-success' : 'text-danger';
            }
        }
    }

    updateVWAPChart() {
        if (!this.vwapData.length) {
            console.warn('No VWAP data available for chart');
            return;
        }

        const timestamps = this.vwapData.map(d => new Date(d.timestamp || d.ts || d.ts_ms));
        const vwapValues = this.vwapData.map(d => d.vwap || 0);
        const twapValues = this.vwapData.map(d => d.twap || 0);
        const priceValues = this.vwapData.map(d => d.price || 0);

        console.log('VWAP Chart Data:', {
            timestamps: timestamps.length,
            vwap_sample: vwapValues.slice(0, 3),
            twap_sample: twapValues.slice(0, 3),
            price_sample: priceValues.slice(0, 3)
        });

        // Check if all values are the same (which would make the chart look flat)
        const allSame = vwapValues.every(v => v === vwapValues[0]) && 
                       twapValues.every(v => v === twapValues[0]) && 
                       priceValues.every(v => v === priceValues[0]);

        if (allSame) {
            console.warn('All VWAP/TWAP/Price values are identical - this is expected for spot flow data with constant price');
        }

        const vwapTrace = {
            x: timestamps,
            y: vwapValues,
            type: 'scatter',
            mode: 'lines',
            name: 'VWAP',
            line: { color: '#007bff', width: 2 }
        };

        const twapTrace = {
            x: timestamps,
            y: twapValues,
            type: 'scatter',
            mode: 'lines',
            name: 'TWAP',
            line: { color: '#17a2b8', width: 2 }
        };

        const priceTrace = {
            x: timestamps,
            y: priceValues,
            type: 'scatter',
            mode: 'lines',
            name: 'Market Price',
            line: { color: '#28a745', width: 2 }
        };

        const layout = {
            title: 'VWAP vs TWAP vs Market Price',
            xaxis: { title: 'Time' },
            yaxis: { title: 'Price (USD)' },
            showlegend: true,
            height: 300,
            margin: { l: 60, r: 50, t: 50, b: 50 }
        };

        Plotly.react('vwapChart', [vwapTrace, twapTrace, priceTrace], layout);
    }

    updateVWAPMetrics() {
        if (!this.vwapData.length) return;

        const latest = this.vwapData[0];
        
        const vwap = latest.vwap || 0;
        const twap = latest.twap || 0;
        const price = latest.price || 0;
        
        const vwapEl = document.getElementById('currentVWAP');
        const twapEl = document.getElementById('currentTWAP');
        const priceEl = document.getElementById('marketPrice');
        const deviationEl = document.getElementById('vwapDeviation');
        
        if (vwapEl) vwapEl.textContent = `$${this.formatNumber(vwap)}`;
        if (twapEl) twapEl.textContent = `$${this.formatNumber(twap)}`;
        if (priceEl) priceEl.textContent = `$${this.formatNumber(price)}`;
        
        if (deviationEl && vwap > 0) {
            const deviation = ((price - vwap) / vwap * 100).toFixed(2);
            deviationEl.textContent = `${deviation}%`;
            deviationEl.className = parseFloat(deviation) > 0 ? 'text-success' : 'text-danger';
        }
    }

    updateVolumeChart() {
        if (!this.volumeData.length) return;

        const timestamps = this.volumeData.map(d => new Date(d.timestamp || d.ts || d.ts_ms));
        const volumeValues = this.volumeData.map(d => d.volume_quote || d.volume || 0);
        const tradesCountValues = this.volumeData.map(d => d.trades_count || d.count || 1);

        const volumeTrace = {
            x: timestamps,
            y: volumeValues,
            type: 'bar',
            name: 'Volume (Quote)',
            marker: { color: '#007bff' },
            yaxis: 'y'
        };

        const tradesTrace = {
            x: timestamps,
            y: tradesCountValues,
            type: 'scatter',
            mode: 'lines+markers',
            name: 'Trades Count',
            yaxis: 'y2',
            line: { color: '#dc3545', width: 2 },
            marker: { size: 4 }
        };

        const layout = {
            title: 'Volume & Trade Statistics',
            xaxis: { title: 'Time' },
            yaxis: { 
                title: 'Volume (USD)', 
                side: 'left',
                showgrid: true
            },
            yaxis2: { 
                title: 'Trades Count', 
                side: 'right', 
                overlaying: 'y',
                showgrid: false
            },
            showlegend: true,
            height: 300,
            margin: { l: 60, r: 60, t: 50, b: 50 }
        };

        Plotly.react('volumeChart', [volumeTrace, tradesTrace], layout);
    }

    updateVolumeMetrics() {
        if (!this.volumeData.length) return;

        const latest = this.volumeData[0];
        
        const tradesCount = latest.trades_count || latest.count || 0;
        const volumeBase = latest.volume_base || 0;
        const volumeQuote = latest.volume_quote || latest.volume || 0;
        const avgTradeSize = latest.avg_trade_size || latest.average_size || 0;
        
        const tradesCountEl = document.getElementById('tradesCount');
        const volumeBaseEl = document.getElementById('volumeBase');
        const volumeQuoteEl = document.getElementById('volumeQuote');
        const avgTradeSizeEl = document.getElementById('avgTradeSize');
        
        if (tradesCountEl) tradesCountEl.textContent = this.formatNumber(tradesCount);
        if (volumeBaseEl) volumeBaseEl.textContent = this.formatNumber(volumeBase);
        if (volumeQuoteEl) volumeQuoteEl.textContent = `$${this.formatNumber(volumeQuote)}`;
        if (avgTradeSizeEl) avgTradeSizeEl.textContent = `$${this.formatNumber(avgTradeSize)}`;
    }

    updateLargeOrdersTable(orders) {
        const tbody = document.getElementById('largeOrdersTableBody');
        if (!tbody) return;
        
        if (!orders || !orders.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No large orders data available from provider</td></tr>';
            return;
        }

        tbody.innerHTML = orders.slice(0, 20).map(order => {
            const timestamp = order.timestamp || order.ts || order.ts_ms || order.time;
            const exchange = order.exchange || 'Unknown';
            const symbol = order.symbol || order.pair || this.currentSymbol;
            
            // Determine side for large orders
            let side = order.side;
            if (!side || side === 'unknown') {
                const buyVol = order.buy_volume_quote || order.buy_volume || 0;
                const sellVol = order.sell_volume_quote || order.sell_volume || 0;
                side = buyVol > sellVol ? 'buy' : 'sell';
            }
            
            const size = order.size || order.quantity || order.qty || order.volume_base || 0;
            const price = order.price || order.avg_price || 0;
            const notional = order.notional_usd || order.notional || order.quote_quantity || order.volume_quote || (size * price);
            const source = order.source || order.data_source || 'CoinGlass';
            
            return `
                <tr>
                    <td>${this.formatTimestamp(timestamp)}</td>
                    <td>${exchange}</td>
                    <td>${symbol}</td>
                    <td><span class="badge ${side === 'buy' ? 'bg-success' : 'bg-danger'}">${side.toUpperCase()}</span></td>
                    <td>${this.formatNumber(size)}</td>
                    <td>$${this.formatPrice(price)}</td>
                    <td>$${this.formatNumber(notional)}</td>
                    <td><span class="badge bg-secondary">${source}</span></td>
                </tr>
            `;
        }).join('');
    }

    startAutoRefresh() {
        this.intervals.forEach(interval => clearInterval(interval));
        this.intervals = [];

        this.intervals.push(
            setInterval(() => this.loadUnifiedData().catch(err => console.error('Auto-refresh failed:', err)), this.refreshInterval)
        );
    }

    showError(elementId, message) {
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML = `<tr><td colspan="100%" class="text-center text-danger">${message}</td></tr>`;
        }
    }

    formatNumber(num) {
        if (num === null || num === undefined) return 'N/A';
        
        const number = parseFloat(num);
        if (isNaN(number)) return 'N/A';
        
        if (number >= 1e9) return (number / 1e9).toFixed(2) + 'B';
        if (number >= 1e6) return (number / 1e6).toFixed(2) + 'M';
        if (number >= 1e3) return (number / 1e3).toFixed(2) + 'K';
        
        return number.toLocaleString(undefined, { 
            minimumFractionDigits: 2, 
            maximumFractionDigits: 8 
        });
    }

    formatPrice(num) {
        if (num === null || num === undefined) return 'N/A';
        
        const number = parseFloat(num);
        if (isNaN(number)) return 'N/A';
        
        // For prices, always show full number with 2 decimals
        return number.toLocaleString(undefined, { 
            minimumFractionDigits: 2, 
            maximumFractionDigits: 2 
        });
    }

    formatTimestamp(timestamp) {
        if (!timestamp) return 'N/A';
        
        const ts = timestamp > 10000000000 ? timestamp : timestamp * 1000;
        const date = new Date(ts);
        
        if (isNaN(date.getTime())) return 'N/A';
        
        // Format as YYYY-MM-DD HH:MM:SS
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const seconds = String(date.getSeconds()).padStart(2, '0');
        
        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    }

    destroy() {
        this.intervals.forEach(interval => clearInterval(interval));
        this.intervals = [];
        
        if (this.cvdChart) Plotly.purge('cvdChart');
        if (this.vwapChart) Plotly.purge('vwapChart');
        if (this.volumeChart) Plotly.purge('volumeChart');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.spotMicrostructureUnified = new SpotMicrostructureUnified();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.spotMicrostructureUnified) {
        window.spotMicrostructureUnified.destroy();
    }
});
