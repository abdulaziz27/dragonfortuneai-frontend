/**
 * Mining & Price Analytics Controller
 * Handles MPI data and comprehensive price analysis
 */

function onchainMiningPriceController() {
    return {
        // Global state
        loading: false,
        selectedAsset: 'BTC',
        selectedToken: '',
        selectedStablecoin: '',
        selectedWindow: 'day',
        selectedLimit: 200,
        chartType: 'line',
        
        // Component-specific state
        mpiData: [],
        mpiSummary: null,
        priceData: [],
        latestPriceData: null,
        currentPrice: 0,
        currentVolume: 0,
        priceCorrelation: 0,
        
        // Loading states
        loadingStates: {
            mpi: false,
            price: false
        },
        
        // Chart IDs for DOM storage (NO chart instances in Alpine data!)
        mpiChartId: 'mpiChart_' + Math.random().toString(36).substr(2, 9),
        priceChartId: 'priceChart_' + Math.random().toString(36).substr(2, 9),
        
        // Initialize controller
        init() {
            console.log('🚀 Initializing Mining & Price Analytics Controller');
            
            // Wait for Chart.js to be available
            this.waitForChartJS().then(() => {
                this.initializeCharts();
                this.loadAllData();
                
                // Auto refresh every 60 seconds
                setInterval(() => this.refreshAll(), 60000);
            });
        },
        
        // Wait for Chart.js to load
        async waitForChartJS() {
            return new Promise((resolve) => {
                const checkChart = () => {
                    if (typeof Chart !== 'undefined') {
                        resolve();
                    } else {
                        setTimeout(checkChart, 100);
                    }
                };
                checkChart();
            });
        },
        
        // Helper methods for DOM-based chart storage
        getMPIChart() {
            const canvas = this.$refs.mpiChart;
            return canvas ? canvas._chartInstance : null;
        },
        
        setMPIChart(chartInstance) {
            const canvas = this.$refs.mpiChart;
            if (canvas) canvas._chartInstance = chartInstance;
        },
        
        getPriceChart() {
            const canvas = this.$refs.priceChart;
            return canvas ? canvas._chartInstance : null;
        },
        
        setPriceChart(chartInstance) {
            const canvas = this.$refs.priceChart;
            if (canvas) canvas._chartInstance = chartInstance;
        },
        
        // Initialize all charts
        initializeCharts() {
            // Use setTimeout to ensure DOM is ready
            setTimeout(() => {
                this.initMPIChart();
                this.initPriceChart();
            }, 100);
        },
        
        // Initialize MPI chart
        initMPIChart() {
            const canvas = this.$refs.mpiChart;
            if (!canvas) {
                console.warn('MPI chart canvas not found');
                return;
            }
            
            const ctx = canvas.getContext('2d');
            
            // Destroy existing chart if any
            const existingChart = this.getMPIChart();
            if (existingChart) {
                existingChart.destroy();
            }
            
            // Create chart outside Alpine reactivity using queueMicrotask
            queueMicrotask(() => {
                const chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'MPI',
                            data: [],
                            borderColor: '#8b5cf6',
                            backgroundColor: 'rgba(139, 92, 246, 0.1)',
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const value = context.parsed.y;
                                    return `MPI: ${value.toFixed(4)}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            type: 'category',
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'MPI Value'
                            }
                        }
                    }
                }
                });
                
                // Store in DOM, not in Alpine data
                this.setMPIChart(chartInstance);
            });
        },
        
        // Initialize price chart
        initPriceChart() {
            const canvas = this.$refs.priceChart;
            if (!canvas) {
                console.warn('Price chart canvas not found');
                return;
            }
            
            const ctx = canvas.getContext('2d');
            
            // Destroy existing chart if any
            const existingChart = this.getPriceChart();
            if (existingChart) {
                existingChart.destroy();
            }
            
            // Create chart outside Alpine reactivity using queueMicrotask
            queueMicrotask(() => {
                const chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Close Price',
                            data: [],
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            yAxisID: 'y',
                            tension: 0.4
                        },
                        {
                            label: 'Volume',
                            data: [],
                            borderColor: '#22c55e',
                            backgroundColor: 'rgba(34, 197, 94, 0.2)',
                            yAxisID: 'y1',
                            type: 'bar',
                            order: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const label = context.dataset.label;
                                    const value = context.parsed.y;
                                    
                                    if (label.includes('Volume')) {
                                        return `${label}: ${this.formatVolume(value, this.selectedAsset)}`;
                                    } else {
                                        return `${label}: ${this.formatPrice(value, this.selectedAsset)}`;
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            type: 'category',
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: `Price (${this.selectedAsset})`
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Volume'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    }
                }
                });
                
                // Store in DOM, not in Alpine data
                this.setPriceChart(chartInstance);
            });
        },
        
        // Load all data
        async loadAllData() {
            this.loading = true;
            try {
                await Promise.all([
                    this.loadMPIData(),
                    this.loadPriceData()
                ]);
                this.calculateCorrelation();
            } catch (error) {
                console.error('❌ Error loading mining & price data:', error);
            } finally {
                this.loading = false;
            }
        },
        
        // Load MPI data
        async loadMPIData() {
            this.loadingStates.mpi = true;
            try {
                const params = new URLSearchParams({
                    asset: this.selectedAsset,
                    window: this.selectedWindow,
                    limit: this.selectedLimit.toString()
                });
                
                const [mpiResponse, summaryResponse] = await Promise.all([
                    this.fetchAPI(`/api/onchain/miners/mpi?${params}`),
                    this.fetchAPI(`/api/onchain/miners/mpi/summary?${params}`)
                ]);
                
                this.mpiData = mpiResponse.data || [];
                this.mpiSummary = summaryResponse.data || null;
                
                this.updateMPIChart();
                
                console.log('✅ MPI data loaded:', this.mpiData.length, 'records');
            } catch (error) {
                console.error('❌ Error loading MPI data:', error);
                this.mpiData = [];
                this.mpiSummary = null;
            } finally {
                this.loadingStates.mpi = false;
            }
        },
        
        // Load price data
        async loadPriceData() {
            this.loadingStates.price = true;
            try {
                let endpoint = '';
                let params = new URLSearchParams({
                    window: this.selectedWindow,
                    limit: this.selectedLimit.toString()
                });
                
                if (this.selectedStablecoin) {
                    endpoint = '/api/onchain/price/stablecoin';
                    params.append('token', this.selectedStablecoin);
                } else if (this.selectedToken) {
                    endpoint = '/api/onchain/price/erc20';
                    params.append('token', this.selectedToken);
                } else {
                    endpoint = '/api/onchain/price/ohlcv';
                    params.append('asset', this.selectedAsset);
                }
                
                const response = await this.fetchAPI(`${endpoint}?${params}`);
                
                this.priceData = response.data || [];
                
                if (this.priceData.length > 0) {
                    this.latestPriceData = this.priceData[0];
                    this.currentPrice = this.latestPriceData.close || 0;
                    this.currentVolume = this.latestPriceData.volume || 0;
                }
                
                this.updatePriceChart();
                
                console.log('✅ Price data loaded:', this.priceData.length, 'records');
            } catch (error) {
                console.error('❌ Error loading price data:', error);
                this.priceData = [];
                this.latestPriceData = null;
            } finally {
                this.loadingStates.price = false;
            }
        },
        
        // Update MPI chart
        updateMPIChart() {
            const chart = this.getMPIChart();
            if (!chart || !this.mpiData.length) return;
            
            const labels = this.mpiData.map(item => {
                const date = new Date(item.timestamp);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
            const mpiValues = this.mpiData.map(item => item.mpi);
            
            // Update chart data outside Alpine reactivity
            queueMicrotask(() => {
                chart.data.labels = labels;
                chart.data.datasets[0].data = mpiValues;
                
                chart.update('none');
            });
        },
        
        // Update price chart
        updatePriceChart() {
            const chart = this.getPriceChart();
            if (!chart || !this.priceData.length) return;
            
            const labels = this.priceData.map(item => {
                const date = new Date(item.timestamp);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
            const closePrices = this.priceData.map(item => item.close);
            const volumes = this.priceData.map(item => item.volume);
            
            // Update chart data outside Alpine reactivity
            queueMicrotask(() => {
                chart.data.labels = labels;
                chart.data.datasets[0].data = closePrices;
                chart.data.datasets[1].data = volumes;
                
                chart.update('none');
            });
        },
        
        // Calculate MPI-Price correlation
        calculateCorrelation() {
            if (!this.mpiData.length || !this.priceData.length) {
                this.priceCorrelation = 0;
                return;
            }
            
            // Simple correlation calculation (placeholder)
            // In a real implementation, you'd align timestamps and calculate Pearson correlation
            this.priceCorrelation = Math.random() * 2 - 1; // Random between -1 and 1 for demo
        },
        
        // Refresh all data
        async refreshAll() {
            await this.loadAllData();
        },
        
        // Refresh MPI data only
        async refreshMPIData() {
            await this.loadMPIData();
        },
        
        // Refresh price data only
        async refreshPriceData() {
            await this.loadPriceData();
        },
        
        // Fetch API helper
        async fetchAPI(endpoint) {
            try {
                const baseMeta = document.querySelector('meta[name="api-base-url"]');
                const configuredBase = (baseMeta?.content || '').trim();
                
                let url;
                if (configuredBase) {
                    const base = configuredBase.endsWith('/') ? configuredBase.slice(0, -1) : configuredBase;
                    url = `${base}${endpoint}`;
                } else {
                    // Use relative URL as fallback
                    url = endpoint;
                }
                
                console.log(`🔗 Fetching: ${url}`);
                
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                console.log(`✅ Data received:`, data);
                return data;
            } catch (error) {
                console.error(`❌ API Error for ${endpoint}:`, error);
                throw error;
            }
        },
        
        // Formatting helpers
        formatMPI(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            return parseFloat(value).toFixed(4);
        },
        
        formatZScore(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            return parseFloat(value).toFixed(2);
        },
        
        formatPrice(value, asset) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            if (asset === 'BTC' && num >= 1000) return `$${(num / 1000).toFixed(1)}K`;
            return `$${num.toLocaleString('en-US', { maximumFractionDigits: 2 })}`;
        },
        
        formatVolume(value, asset) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            if (num >= 1e9) return `${(num / 1e9).toFixed(2)}B ${asset}`;
            if (num >= 1e6) return `${(num / 1e6).toFixed(2)}M ${asset}`;
            if (num >= 1e3) return `${(num / 1e3).toFixed(2)}K ${asset}`;
            return `${num.toFixed(2)} ${asset}`;
        },
        
        formatPercentage(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            return `${num >= 0 ? '+' : ''}${num.toFixed(2)}%`;
        },
        
        formatCorrelation(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            return parseFloat(value).toFixed(3);
        },
        
        // Style helpers
        getMPIClass() {
            if (!this.mpiSummary?.latest?.mpi) return 'text-secondary';
            const mpi = parseFloat(this.mpiSummary.latest.mpi);
            if (mpi > 2) return 'text-danger';
            if (mpi > 0) return 'text-warning';
            return 'text-success';
        },
        
        getMPIChangeClass() {
            if (!this.mpiSummary?.latest?.change_pct) return 'text-secondary';
            const change = parseFloat(this.mpiSummary.latest.change_pct);
            return change >= 0 ? 'text-danger' : 'text-success';
        },
        
        getZScoreClass() {
            if (!this.mpiSummary?.stats?.z_score) return 'text-secondary';
            const zscore = parseFloat(this.mpiSummary.stats.z_score);
            if (Math.abs(zscore) > 2) return 'text-danger';
            if (Math.abs(zscore) > 1) return 'text-warning';
            return 'text-success';
        },
        
        getZScoreInterpretation() {
            if (!this.mpiSummary?.stats?.z_score) return 'No data';
            const zscore = parseFloat(this.mpiSummary.stats.z_score);
            if (zscore > 2) return 'Extreme high';
            if (zscore > 1) return 'Above average';
            if (zscore > -1) return 'Normal range';
            if (zscore > -2) return 'Below average';
            return 'Extreme low';
        },
        
        getMinerSentimentClass() {
            if (!this.mpiSummary?.latest?.mpi) return 'text-secondary';
            const mpi = parseFloat(this.mpiSummary.latest.mpi);
            if (mpi > 2) return 'text-danger';
            if (mpi > 0) return 'text-warning';
            return 'text-success';
        },
        
        getMinerSentiment() {
            if (!this.mpiSummary?.latest?.mpi) return 'Unknown';
            const mpi = parseFloat(this.mpiSummary.latest.mpi);
            if (mpi > 2) return 'Distributing';
            if (mpi > 0) return 'Neutral';
            return 'Accumulating';
        },
        
        getPriceChangeClass() {
            if (!this.latestPriceData?.open || !this.latestPriceData?.close) return 'text-secondary';
            const change = this.latestPriceData.close - this.latestPriceData.open;
            return change >= 0 ? 'text-success' : 'text-danger';
        },
        
        formatPriceChange() {
            if (!this.latestPriceData?.open || !this.latestPriceData?.close) return 'N/A';
            const change = this.latestPriceData.close - this.latestPriceData.open;
            const changePercent = (change / this.latestPriceData.open) * 100;
            return `${change >= 0 ? '+' : ''}${changePercent.toFixed(2)}%`;
        },
        
        getCorrelationClass() {
            const corr = Math.abs(this.priceCorrelation);
            if (corr > 0.7) return 'text-danger';
            if (corr > 0.3) return 'text-warning';
            return 'text-success';
        },
        
        getCorrelationInterpretation() {
            const corr = Math.abs(this.priceCorrelation);
            if (corr > 0.7) return 'Strong correlation';
            if (corr > 0.3) return 'Moderate correlation';
            return 'Weak correlation';
        },
        
        getSignalStrengthClass() {
            const corr = Math.abs(this.priceCorrelation);
            if (corr > 0.7) return 'text-success';
            if (corr > 0.3) return 'text-warning';
            return 'text-danger';
        },
        
        getSignalStrength() {
            const corr = Math.abs(this.priceCorrelation);
            if (corr > 0.7) return 'Strong';
            if (corr > 0.3) return 'Moderate';
            return 'Weak';
        }
    };
}