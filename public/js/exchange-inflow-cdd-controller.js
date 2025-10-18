/**
 * Exchange Inflow CDD Controller
 * 
 * Manages Bitcoin Exchange Inflow CDD (Coin Days Destroyed) dashboard
 * Data source: CryptoQuant API
 * 
 * Think like a trader:
 * - CDD measures the "age" of coins moving
 * - High CDD = old coins (long-term holders) moving to exchanges
 * - Spike in CDD often precedes selling pressure
 * 
 * Build like an engineer:
 * - Clean data fetching with error handling
 * - Efficient chart rendering
 * - Statistical analysis (MA, std dev, outliers)
 */

function exchangeInflowCDDController() {
    return {
        // Global state
        globalPeriod: '30d',
        globalLoading: false,
        selectedExchange: 'binance',
        
        // Data
        rawData: [],
        priceData: [], // Bitcoin price data for overlay
        
        // Summary metrics
        currentCDD: 0,
        cddChange: 0,
        avgCDD: 0,
        medianCDD: 0,
        maxCDD: 0,
        peakDate: '--',
        
        // Price metrics
        currentPrice: 0,
        priceChange: 0,
        
        // Analysis metrics
        ma7: 0,
        ma30: 0,
        highCDDEvents: 0,
        extremeCDDEvents: 0,
        
        // Market signal
        marketSignal: 'Neutral',
        signalStrength: 'Normal',
        signalDescription: 'Loading...',
        
        // Chart state
        chartType: 'line',
        mainChart: null,
        distributionChart: null,
        maChart: null,
        
        // Initialize
        init() {
            console.log('🚀 Exchange Inflow CDD Dashboard initialized');
            
            // Load initial data
            this.loadData();
            
            // Auto refresh every 5 minutes
            setInterval(() => this.loadData(), 5 * 60 * 1000);
        },
        
        // Update period filter
        updatePeriod() {
            console.log('🔄 Updating period to:', this.globalPeriod);
            this.loadData();
        },
        
        // Update exchange
        updateExchange() {
            console.log('🔄 Updating exchange to:', this.selectedExchange);
            this.loadData();
        },
        
        // Refresh all data
        refreshAll() {
            this.globalLoading = true;
            this.loadData().finally(() => {
                this.globalLoading = false;
            });
        },
        
        // Load data from API
        async loadData() {
            try {
                console.log('📡 Fetching Exchange Inflow CDD data...');
                
                // Calculate date range based on period
                const { startDate, endDate } = this.getDateRange();
                
                // Fetch CDD data via Laravel backend proxy (to avoid CORS)
                const url = `/api/cryptoquant/exchange-inflow-cdd?start_date=${startDate}&end_date=${endDate}&exchange=${this.selectedExchange}`;
                
                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                
                if (!data.success || !Array.isArray(data.data)) {
                    throw new Error('Invalid data format');
                }
                
                this.rawData = data.data;
                console.log(`✅ Loaded ${this.rawData.length} CDD data points`);
                
                // Fetch Bitcoin price data for overlay
                await this.loadPriceData(startDate, endDate);
                
                // Calculate metrics
                this.calculateMetrics();
                
                // Render charts
                this.renderChart();
                this.renderDistributionChart();
                this.renderMAChart();
                
            } catch (error) {
                console.error('❌ Error loading CDD data:', error);
                this.showError(error.message);
            }
        },
        
        // Load Bitcoin price data
        async loadPriceData(startDate, endDate) {
            try {
                // Use CoinGecko API for BTC price (free, no API key needed)
                const start = Math.floor(new Date(startDate).getTime() / 1000);
                const end = Math.floor(new Date(endDate).getTime() / 1000);
                
                const url = `https://api.coingecko.com/api/v3/coins/bitcoin/market_chart/range?vs_currency=usd&from=${start}&to=${end}`;
                
                const response = await fetch(url);
                
                if (!response.ok) {
                    console.warn('⚠️ Failed to fetch price data, chart will show CDD only');
                    this.priceData = [];
                    return;
                }
                
                const data = await response.json();
                
                if (data.prices && Array.isArray(data.prices)) {
                    // Transform to our format
                    this.priceData = data.prices.map(([timestamp, price]) => ({
                        date: new Date(timestamp).toISOString().split('T')[0],
                        price: price
                    }));
                    
                    console.log(`✅ Loaded ${this.priceData.length} price data points`);
                    
                    // Calculate current price and change
                    if (this.priceData.length > 0) {
                        this.currentPrice = this.priceData[this.priceData.length - 1].price;
                        const yesterdayPrice = this.priceData[this.priceData.length - 2]?.price || this.currentPrice;
                        this.priceChange = ((this.currentPrice - yesterdayPrice) / yesterdayPrice) * 100;
                    }
                } else {
                    this.priceData = [];
                }
                
            } catch (error) {
                console.warn('⚠️ Error loading price data:', error);
                this.priceData = [];
            }
        },
        
        // Calculate all metrics
        calculateMetrics() {
            if (this.rawData.length === 0) return;
            
            // Sort by date
            const sorted = [...this.rawData].sort((a, b) => 
                new Date(a.date) - new Date(b.date)
            );
            
            // Extract CDD values
            const cddValues = sorted.map(d => parseFloat(d.value));
            
            // Current metrics
            this.currentCDD = cddValues[cddValues.length - 1];
            const yesterdayCDD = cddValues[cddValues.length - 2] || this.currentCDD;
            this.cddChange = ((this.currentCDD - yesterdayCDD) / yesterdayCDD) * 100;
            
            // Statistical metrics
            this.avgCDD = cddValues.reduce((a, b) => a + b, 0) / cddValues.length;
            this.medianCDD = this.calculateMedian(cddValues);
            this.maxCDD = Math.max(...cddValues);
            
            // Peak date
            const peakIndex = cddValues.indexOf(this.maxCDD);
            this.peakDate = this.formatDate(sorted[peakIndex].date);
            
            // Moving averages
            this.ma7 = this.calculateMA(cddValues, 7);
            this.ma30 = this.calculateMA(cddValues, 30);
            
            // Outlier detection (using standard deviation)
            const stdDev = this.calculateStdDev(cddValues);
            const threshold2Sigma = this.avgCDD + (2 * stdDev);
            const threshold3Sigma = this.avgCDD + (3 * stdDev);
            
            this.highCDDEvents = cddValues.filter(v => v > threshold2Sigma).length;
            this.extremeCDDEvents = cddValues.filter(v => v > threshold3Sigma).length;
            
            // Market signal
            this.calculateMarketSignal(stdDev);
            
            console.log('📊 Metrics calculated:', {
                current: this.currentCDD,
                avg: this.avgCDD,
                max: this.maxCDD,
                signal: this.marketSignal
            });
        },
        
        // Calculate market signal
        calculateMarketSignal(stdDev) {
            const zScore = (this.currentCDD - this.avgCDD) / stdDev;
            
            if (zScore > 2) {
                this.marketSignal = 'Distribution';
                this.signalStrength = 'Strong';
                this.signalDescription = 'Old coins moving to exchanges';
            } else if (zScore > 1) {
                this.marketSignal = 'Caution';
                this.signalStrength = 'Moderate';
                this.signalDescription = 'Elevated CDD levels detected';
            } else if (zScore < -1) {
                this.marketSignal = 'Accumulation';
                this.signalStrength = 'Weak';
                this.signalDescription = 'Low distribution activity';
            } else {
                this.marketSignal = 'Neutral';
                this.signalStrength = 'Normal';
                this.signalDescription = 'Normal market conditions';
            }
        },
        
        // Render main chart (CryptoQuant style with price overlay)
        renderChart() {
            const canvas = document.getElementById('cddMainChart');
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            
            // Destroy existing chart
            if (this.mainChart) {
                this.mainChart.destroy();
            }
            
            // Prepare CDD data
            const sorted = [...this.rawData].sort((a, b) => 
                new Date(a.date) - new Date(b.date)
            );
            
            const labels = sorted.map(d => d.date);
            const values = sorted.map(d => parseFloat(d.value));
            
            // Calculate threshold for coloring (above/below average)
            const avgValue = this.avgCDD;
            
            // Prepare price data (align with CDD dates)
            const priceMap = new Map(this.priceData.map(p => [p.date, p.price]));
            const alignedPrices = labels.map(date => priceMap.get(date) || null);
            
            // Determine bar colors (green if below avg, red if above avg - like CryptoQuant)
            const barColors = values.map(v => 
                v > avgValue ? 'rgba(239, 68, 68, 0.7)' : 'rgba(34, 197, 94, 0.7)'
            );
            
            // Build datasets
            const datasets = [];
            
            // Dataset 1: CDD bars
            if (this.chartType === 'bar') {
                datasets.push({
                    label: 'Exchange Inflow CDD',
                    data: values,
                    backgroundColor: barColors,
                    borderColor: barColors.map(c => c.replace('0.7', '1')),
                    borderWidth: 1,
                    yAxisID: 'y',
                    order: 2
                });
            } else {
                // Line chart with area fill
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
                gradient.addColorStop(1, 'rgba(59, 130, 246, 0.05)');
                
                datasets.push({
                    label: 'Exchange Inflow CDD',
                    data: values,
                    borderColor: '#3b82f6',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.1,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#3b82f6',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                    yAxisID: 'y',
                    order: 2
                });
            }
            
            // Dataset 2: Bitcoin Price overlay (if available)
            if (this.priceData.length > 0) {
                datasets.push({
                    label: 'BTC Price',
                    data: alignedPrices,
                    borderColor: '#f59e0b',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    type: 'line',
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#f59e0b',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                    yAxisID: 'y1',
                    order: 1
                });
            }
            
            // Create chart with dual Y-axis (CryptoQuant style)
            this.mainChart = new Chart(ctx, {
                type: this.chartType,
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: this.priceData.length > 0,
                            position: 'top',
                            align: 'end',
                            labels: {
                                color: '#64748b',
                                font: { size: 11, weight: '500' },
                                boxWidth: 12,
                                boxHeight: 12,
                                padding: 10,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleColor: '#f3f4f6',
                            bodyColor: '#f3f4f6',
                            borderColor: 'rgba(59, 130, 246, 0.5)',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                            boxWidth: 8,
                            boxHeight: 8,
                            callbacks: {
                                title: (items) => {
                                    const date = new Date(items[0].label);
                                    return date.toLocaleDateString('en-US', { 
                                        weekday: 'short',
                                        year: 'numeric', 
                                        month: 'short', 
                                        day: 'numeric' 
                                    });
                                },
                                label: (context) => {
                                    const datasetLabel = context.dataset.label;
                                    const value = context.parsed.y;
                                    
                                    if (datasetLabel === 'BTC Price') {
                                        return `  ${datasetLabel}: $${value.toLocaleString('en-US', { maximumFractionDigits: 0 })}`;
                                    } else {
                                        const vsAvg = ((value - avgValue) / avgValue * 100).toFixed(1);
                                        const trend = value > avgValue ? '🔴 Above Avg' : '🟢 Below Avg';
                                        return [
                                            `  ${datasetLabel}: ${this.formatCDD(value)}`,
                                            `  ${trend} (${vsAvg > 0 ? '+' : ''}${vsAvg}%)`
                                        ];
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { 
                                color: '#94a3b8',
                                font: { size: 11 },
                                maxRotation: 0,
                                minRotation: 0,
                                callback: function(value, index) {
                                    // Show every Nth label to avoid crowding
                                    const totalLabels = this.chart.data.labels.length;
                                    const showEvery = Math.max(1, Math.ceil(totalLabels / 12));
                                    if (index % showEvery === 0) {
                                        const date = this.chart.data.labels[index];
                                        return new Date(date).toLocaleDateString('en-US', { 
                                            month: 'short', 
                                            day: 'numeric' 
                                        });
                                    }
                                    return '';
                                }
                            },
                            grid: { 
                                display: true,
                                color: 'rgba(148, 163, 184, 0.08)',
                                drawBorder: false
                            }
                        },
                        y: {
                            type: 'linear',
                            position: 'left',
                            title: {
                                display: true,
                                text: 'CDD',
                                color: '#3b82f6',
                                font: { size: 11, weight: '600' }
                            },
                            ticks: {
                                color: '#3b82f6',
                                font: { size: 11 },
                                callback: (value) => this.formatCDD(value)
                            },
                            grid: { 
                                color: 'rgba(148, 163, 184, 0.08)',
                                drawBorder: false
                            }
                        },
                        y1: {
                            type: 'linear',
                            position: 'right',
                            display: this.priceData.length > 0,
                            title: {
                                display: true,
                                text: 'BTC Price (USD)',
                                color: '#f59e0b',
                                font: { size: 11, weight: '600' }
                            },
                            ticks: {
                                color: '#f59e0b',
                                font: { size: 11 },
                                callback: (value) => '$' + value.toLocaleString('en-US', { maximumFractionDigits: 0 })
                            },
                            grid: { 
                                display: false,
                                drawBorder: false
                            }
                        }
                    }
                }
            });
        },
        
        // Render distribution chart (histogram)
        renderDistributionChart() {
            const canvas = document.getElementById('cddDistributionChart');
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            
            if (this.distributionChart) {
                this.distributionChart.destroy();
            }
            
            // Create histogram bins
            const values = this.rawData.map(d => parseFloat(d.value));
            const bins = this.createHistogramBins(values, 20);
            
            this.distributionChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: bins.map(b => b.label),
                    datasets: [{
                        label: 'Frequency',
                        data: bins.map(b => b.count),
                        backgroundColor: 'rgba(139, 92, 246, 0.6)',
                        borderColor: '#8b5cf6',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            ticks: { color: '#94a3b8', maxRotation: 45 },
                            grid: { display: false }
                        },
                        y: {
                            ticks: { color: '#94a3b8' },
                            grid: { color: 'rgba(148, 163, 184, 0.1)' }
                        }
                    }
                }
            });
        },
        
        // Render moving average chart
        renderMAChart() {
            const canvas = document.getElementById('cddMAChart');
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            
            if (this.maChart) {
                this.maChart.destroy();
            }
            
            // Prepare data
            const sorted = [...this.rawData].sort((a, b) => 
                new Date(a.date) - new Date(b.date)
            );
            
            const labels = sorted.map(d => d.date);
            const values = sorted.map(d => parseFloat(d.value));
            const ma7Data = this.calculateMAArray(values, 7);
            const ma30Data = this.calculateMAArray(values, 30);
            
            this.maChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'CDD',
                            data: values,
                            borderColor: '#94a3b8',
                            backgroundColor: 'transparent',
                            borderWidth: 1,
                            tension: 0.4
                        },
                        {
                            label: '7-Day MA',
                            data: ma7Data,
                            borderColor: '#22c55e',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.4
                        },
                        {
                            label: '30-Day MA',
                            data: ma30Data,
                            borderColor: '#ef4444',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: { color: '#94a3b8', boxWidth: 20 }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { 
                                color: '#94a3b8',
                                maxRotation: 45,
                                minRotation: 45,
                                callback: function(value, index) {
                                    const totalLabels = this.chart.data.labels.length;
                                    const showEvery = Math.ceil(totalLabels / 8);
                                    if (index % showEvery === 0) {
                                        const date = this.chart.data.labels[index];
                                        return new Date(date).toLocaleDateString('en-US', { 
                                            month: 'short', 
                                            day: 'numeric' 
                                        });
                                    }
                                    return '';
                                }
                            },
                            grid: { display: false }
                        },
                        y: {
                            ticks: { color: '#94a3b8' },
                            grid: { color: 'rgba(148, 163, 184, 0.1)' }
                        }
                    }
                }
            });
        },
        
        // Utility: Get date range based on period
        getDateRange() {
            const endDate = new Date();
            const startDate = new Date();
            
            const periodMap = {
                '7d': 7,
                '30d': 30,
                '90d': 90,
                '180d': 180,
                '1y': 365
            };
            
            const days = periodMap[this.globalPeriod] || 30;
            
            // Set start date to X days ago
            startDate.setDate(endDate.getDate() - days);
            
            // Format dates properly (YYYY-MM-DD)
            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };
            
            return {
                startDate: formatDate(startDate),
                endDate: formatDate(endDate)
            };
        },
        
        // Utility: Get time unit for chart
        getTimeUnit() {
            const unitMap = {
                '7d': 'day',
                '30d': 'day',
                '90d': 'week',
                '180d': 'week',
                '1y': 'month'
            };
            return unitMap[this.globalPeriod] || 'day';
        },
        
        // Utility: Calculate median
        calculateMedian(values) {
            const sorted = [...values].sort((a, b) => a - b);
            const mid = Math.floor(sorted.length / 2);
            return sorted.length % 2 === 0
                ? (sorted[mid - 1] + sorted[mid]) / 2
                : sorted[mid];
        },
        
        // Utility: Calculate standard deviation
        calculateStdDev(values) {
            const avg = values.reduce((a, b) => a + b, 0) / values.length;
            const squareDiffs = values.map(v => Math.pow(v - avg, 2));
            const avgSquareDiff = squareDiffs.reduce((a, b) => a + b, 0) / squareDiffs.length;
            return Math.sqrt(avgSquareDiff);
        },
        
        // Utility: Calculate moving average (last N values)
        calculateMA(values, period) {
            if (values.length < period) return 0;
            const slice = values.slice(-period);
            return slice.reduce((a, b) => a + b, 0) / slice.length;
        },
        
        // Utility: Calculate MA array for all points
        calculateMAArray(values, period) {
            return values.map((_, i) => {
                if (i < period - 1) return null;
                const slice = values.slice(i - period + 1, i + 1);
                return slice.reduce((a, b) => a + b, 0) / slice.length;
            });
        },
        
        // Utility: Create histogram bins
        createHistogramBins(values, binCount) {
            const min = Math.min(...values);
            const max = Math.max(...values);
            const binSize = (max - min) / binCount;
            
            const bins = Array.from({ length: binCount }, (_, i) => ({
                min: min + (i * binSize),
                max: min + ((i + 1) * binSize),
                count: 0,
                label: ''
            }));
            
            values.forEach(v => {
                const binIndex = Math.min(
                    Math.floor((v - min) / binSize),
                    binCount - 1
                );
                bins[binIndex].count++;
            });
            
            bins.forEach(bin => {
                bin.label = this.formatCDD(bin.min);
            });
            
            return bins;
        },
        
        // Utility: Create gradient
        createGradient(ctx, color) {
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, color.replace(')', ', 0.3)').replace('rgb', 'rgba'));
            gradient.addColorStop(1, color.replace(')', ', 0)').replace('rgb', 'rgba'));
            return gradient;
        },
        
        // Utility: Format CDD value
        formatCDD(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            if (num >= 1e9) return (num / 1e9).toFixed(2) + 'B';
            if (num >= 1e6) return (num / 1e6).toFixed(2) + 'M';
            if (num >= 1e3) return (num / 1e3).toFixed(2) + 'K';
            return num.toFixed(2);
        },
        
        // Utility: Format price
        formatPrice(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            return '$' + num.toLocaleString('en-US', { 
                minimumFractionDigits: 0,
                maximumFractionDigits: 0 
            });
        },
        
        // Utility: Format change percentage
        formatChange(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const sign = value >= 0 ? '+' : '';
            return `${sign}${value.toFixed(2)}%`;
        },
        
        // Utility: Format date
        formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric',
                year: 'numeric'
            });
        },
        
        // Utility: Get trend class (for CDD - higher is bearish)
        getTrendClass(value) {
            if (value > 0) return 'text-danger';
            if (value < 0) return 'text-success';
            return 'text-secondary';
        },
        
        // Utility: Get price trend class (for price - higher is bullish)
        getPriceTrendClass(value) {
            if (value > 0) return 'text-success';
            if (value < 0) return 'text-danger';
            return 'text-secondary';
        },
        
        // Utility: Get signal badge class
        getSignalBadgeClass() {
            const strengthMap = {
                'Strong': 'text-bg-danger',
                'Moderate': 'text-bg-warning',
                'Weak': 'text-bg-info',
                'Normal': 'text-bg-secondary'
            };
            return strengthMap[this.signalStrength] || 'text-bg-secondary';
        },
        
        // Utility: Get signal color class
        getSignalColorClass() {
            const colorMap = {
                'Distribution': 'text-danger',
                'Caution': 'text-warning',
                'Accumulation': 'text-success',
                'Neutral': 'text-secondary'
            };
            return colorMap[this.marketSignal] || 'text-secondary';
        },
        
        // Utility: Show error
        showError(message) {
            console.error('Error:', message);
            // Could add toast notification here
        }
    };
}

console.log('✅ Exchange Inflow CDD Controller loaded');
