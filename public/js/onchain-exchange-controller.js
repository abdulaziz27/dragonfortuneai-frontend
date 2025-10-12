/**
 * Exchange Reserves & Market Indicators Controller
 * Handles exchange reserves and market leverage data
 */

function onchainExchangeController() {
    return {
        // Global state
        loading: false,
        selectedAsset: 'BTC',
        selectedExchange: '',
        selectedWindow: 'day',
        selectedLimit: 200,

        // Component-specific state
        reservesData: [],
        reserveSummary: null,
        indicatorsData: [],
        exchangeList: [],
        currentLeverageRatio: 0,

        // Loading states
        loadingStates: {
            reserves: false,
            indicators: false
        },

        // Chart instances (stored in Alpine data like working controller)
        reservesChart: null,
        indicatorsChart: null,

        // Initialize controller
        init() {
            console.log('🚀 Initializing Exchange Metrics Controller');
            
            // Load shared state
            this.loadSharedState();
            
            // Subscribe to shared state changes
            this.subscribeToSharedState();
            
            this.loadAllData();

            // Auto refresh every 60 seconds
            setInterval(() => this.refreshAll(), 60000);
        },
        
        // Load shared state
        loadSharedState() {
            if (window.OnChainSharedState) {
                const sharedFilters = window.OnChainSharedState.getAllFilters();
                this.selectedAsset = sharedFilters.selectedAsset;
                this.selectedWindow = sharedFilters.selectedWindow;
                this.selectedLimit = sharedFilters.selectedLimit;
                this.selectedExchange = sharedFilters.selectedExchange;
            }
        },
        
        // Subscribe to shared state changes
        subscribeToSharedState() {
            if (window.OnChainSharedState) {
                // Subscribe to asset changes
                window.OnChainSharedState.subscribe('selectedAsset', (value) => {
                    if (this.selectedAsset !== value) {
                        this.selectedAsset = value;
                        this.refreshAll();
                    }
                });
                
                // Subscribe to window changes
                window.OnChainSharedState.subscribe('selectedWindow', (value) => {
                    if (this.selectedWindow !== value) {
                        this.selectedWindow = value;
                        this.refreshAll();
                    }
                });
                
                // Subscribe to limit changes
                window.OnChainSharedState.subscribe('selectedLimit', (value) => {
                    if (this.selectedLimit !== value) {
                        this.selectedLimit = value;
                        this.refreshAll();
                    }
                });
                
                // Subscribe to exchange changes
                window.OnChainSharedState.subscribe('selectedExchange', (value) => {
                    if (this.selectedExchange !== value) {
                        this.selectedExchange = value;
                        this.refreshAll();
                    }
                });
            }
        },
        
        // Update shared state when local state changes
        updateSharedState() {
            if (window.OnChainSharedState) {
                window.OnChainSharedState.setFilter('selectedAsset', this.selectedAsset);
                window.OnChainSharedState.setFilter('selectedWindow', this.selectedWindow);
                window.OnChainSharedState.setFilter('selectedLimit', this.selectedLimit);
                window.OnChainSharedState.setFilter('selectedExchange', this.selectedExchange);
            }
        },

        // Render reserves chart
        renderReservesChart() {
            console.log('🎨 Rendering reserves chart...');
            const canvas = this.$refs.reservesChart;
            if (!canvas) {
                console.warn('❌ Reserves chart canvas not found');
                return;
            }
            
            // Destroy existing chart
            if (this.reservesChart) {
                this.reservesChart.destroy();
                this.reservesChart = null;
            }
            
            if (!this.reservesData.length) {
                console.warn('❌ No reserves data to render');
                return;
            }
            
            const labels = this.reservesData.map(item => {
                const date = new Date(item.timestamp);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
            const reserves = this.reservesData.map(item => item.reserve);
            const usdValues = this.reservesData.map(item => item.reserve_usd);
            
            const ctx = canvas.getContext('2d');
            this.reservesChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Reserve Amount',
                                data: reserves,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                yAxisID: 'y',
                                tension: 0.4
                            },
                            {
                                label: 'USD Value',
                                data: usdValues,
                                borderColor: '#22c55e',
                                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                yAxisID: 'y1',
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
                                        const label = context.dataset.label;
                                        const value = context.parsed.y;

                                        if (label.includes('USD')) {
                                            return `${label}: ${this.formatUSD(value)}`;
                                        } else {
                                            return `${label}: ${this.formatReserve(value, this.selectedAsset)}`;
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
                                    text: `Reserve (${this.selectedAsset})`
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'USD Value'
                                },
                                grid: {
                                    drawOnChartArea: false,
                                },
                            }
                        }
                    }
            });
            
            console.log('✅ Reserves chart created successfully');
        },

        // Render indicators chart
        renderIndicatorsChart() {
            console.log('🎨 Rendering indicators chart...');
            const canvas = this.$refs.indicatorsChart;
            if (!canvas) {
                console.warn('❌ Indicators chart canvas not found');
                return;
            }
            
            // Destroy existing chart
            if (this.indicatorsChart) {
                this.indicatorsChart.destroy();
                this.indicatorsChart = null;
            }
            
            if (!this.indicatorsData.length) {
                console.warn('❌ No indicators data to render');
                return;
            }
            
            const labels = this.indicatorsData.map(item => {
                const date = new Date(item.timestamp);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
            const leverageRatios = this.indicatorsData.map(item => item.estimated_leverage_ratio);
            
            const ctx = canvas.getContext('2d');
            this.indicatorsChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Leverage Ratio',
                                data: leverageRatios,
                                borderColor: '#8b5cf6',
                                backgroundColor: 'rgba(139, 92, 246, 0.2)',
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
                                        return `Leverage Ratio: ${value.toFixed(4)}`;
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
                                    text: 'Leverage Ratio'
                                },
                                min: 0,
                                max: 1
                            }
                        }
                    }
            });
            
            console.log('✅ Indicators chart created successfully');
        },

        // Load all data
        async loadAllData() {
            this.loading = true;
            try {
                await Promise.all([
                    this.loadReservesData(),
                    this.loadIndicatorsData()
                ]);
            } catch (error) {
                console.error('❌ Error loading exchange data:', error);
            } finally {
                this.loading = false;
            }
        },

        // Load reserves data
        async loadReservesData() {
            this.loadingStates.reserves = true;
            try {
                const params = new URLSearchParams({
                    asset: this.selectedAsset,
                    window: this.selectedWindow,
                    limit: this.selectedLimit.toString()
                });

                if (this.selectedExchange) {
                    params.append('exchange', this.selectedExchange);
                }

                console.log('🔍 Loading reserves with params:', {
                    asset: this.selectedAsset,
                    window: this.selectedWindow,
                    limit: this.selectedLimit,
                    exchange: this.selectedExchange
                });

                const [reservesResponse, summaryResponse] = await Promise.all([
                    this.fetchAPI(`/api/onchain/exchange/reserves?${params}`),
                    this.fetchAPI(`/api/onchain/exchange/reserves/summary?${params}`)
                ]);

                this.reservesData = reservesResponse.data || [];
                this.reserveSummary = summaryResponse.data || null;
                this.exchangeList = this.reserveSummary?.exchanges || [];

                console.log('📊 Reserves data structure:', {
                    dataLength: this.reservesData.length,
                    sampleData: this.reservesData.slice(0, 2),
                    summary: this.reserveSummary
                });

                this.renderReservesChart();

                console.log('✅ Reserves data loaded:', this.reservesData.length, 'records');
            } catch (error) {
                console.error('❌ Error loading reserves data:', error);
                this.reservesData = [];
                this.reserveSummary = null;
            } finally {
                this.loadingStates.reserves = false;
            }
        },

        // Load market indicators data
        async loadIndicatorsData() {
            this.loadingStates.indicators = true;
            try {
                // Use XRP as default for market indicators as per API documentation
                const params = new URLSearchParams({
                    asset: 'XRP', // API default for market indicators
                    window: this.selectedWindow,
                    limit: this.selectedLimit.toString()
                });

                if (this.selectedExchange) {
                    params.append('exchange', this.selectedExchange);
                }

                console.log('🔍 Loading indicators with params:', {
                    asset: 'XRP',
                    window: this.selectedWindow,
                    limit: this.selectedLimit,
                    exchange: this.selectedExchange
                });

                const response = await this.fetchAPI(`/api/onchain/market/indicators?${params}`);

                this.indicatorsData = response.data || [];

                if (this.indicatorsData.length > 0) {
                    this.currentLeverageRatio = this.indicatorsData[0].estimated_leverage_ratio || 0;
                }

                console.log('📊 Indicators data structure:', {
                    dataLength: this.indicatorsData.length,
                    sampleData: this.indicatorsData.slice(0, 2),
                    currentLeverageRatio: this.currentLeverageRatio
                });

                this.renderIndicatorsChart();

                console.log('✅ Indicators data loaded:', this.indicatorsData.length, 'records');
            } catch (error) {
                console.error('❌ Error loading indicators data:', error);
                this.indicatorsData = [];
                this.currentLeverageRatio = 0;
            } finally {
                this.loadingStates.indicators = false;
            }
        },



        // Refresh all data
        async refreshAll() {
            this.updateSharedState();
            await this.loadAllData();
        },

        // Refresh reserves data only
        async refreshReservesData() {
            await this.loadReservesData();
        },

        // Refresh indicators data only
        async refreshIndicatorsData() {
            await this.loadIndicatorsData();
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
        formatReserve(value, asset) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            if (num >= 1000000) return `${(num / 1000000).toFixed(2)}M ${asset}`;
            if (num >= 1000) return `${(num / 1000).toFixed(2)}K ${asset}`;
            return `${num.toFixed(2)} ${asset}`;
        },

        formatReserveChange(value, asset) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            const sign = num >= 0 ? '+' : '';
            if (Math.abs(num) >= 1000000) return `${sign}${(num / 1000000).toFixed(2)}M ${asset}`;
            if (Math.abs(num) >= 1000) return `${sign}${(num / 1000).toFixed(2)}K ${asset}`;
            return `${sign}${num.toFixed(2)} ${asset}`;
        },

        formatUSD(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            if (Math.abs(num) >= 1e12) return `$${(num / 1e12).toFixed(2)}T`;
            if (Math.abs(num) >= 1e9) return `$${(num / 1e9).toFixed(2)}B`;
            if (Math.abs(num) >= 1e6) return `$${(num / 1e6).toFixed(2)}M`;
            if (Math.abs(num) >= 1e3) return `$${(num / 1e3).toFixed(2)}K`;
            return `$${num.toFixed(2)}`;
        },

        formatLeverage(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            return parseFloat(value).toFixed(4);
        },

        formatPercentage(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            return `${num >= 0 ? '+' : ''}${num.toFixed(2)}%`;
        },

        // Style helpers
        getReserveChangeClass() {
            if (!this.reserveSummary?.totals?.change) return 'text-secondary';
            const change = parseFloat(this.reserveSummary.totals.change);
            return change >= 0 ? 'text-success' : 'text-danger';
        },

        getFlowDirectionClass() {
            if (!this.reserveSummary?.totals?.change) return 'text-secondary';
            const change = parseFloat(this.reserveSummary.totals.change);
            if (change > 0) return 'text-danger'; // Inflow = bearish
            if (change < 0) return 'text-success'; // Outflow = bullish
            return 'text-secondary';
        },

        getFlowDirection() {
            if (!this.reserveSummary?.totals?.change) return 'Neutral';
            const change = parseFloat(this.reserveSummary.totals.change);
            if (change > 0) return 'Inflow';
            if (change < 0) return 'Outflow';
            return 'Neutral';
        },

        getLeverageRiskClass() {
            if (!this.currentLeverageRatio) return 'text-secondary';
            const ratio = parseFloat(this.currentLeverageRatio);
            if (ratio > 0.5) return 'text-danger';
            if (ratio > 0.3) return 'text-warning';
            return 'text-success';
        },

        getLeverageRiskLabel() {
            if (!this.currentLeverageRatio) return 'No data';
            const ratio = parseFloat(this.currentLeverageRatio);
            if (ratio > 0.5) return 'High Risk';
            if (ratio > 0.3) return 'Medium Risk';
            return 'Low Risk';
        },

        getRiskLevelClass() {
            return this.getLeverageRiskClass();
        },

        getRiskLevel() {
            return this.getLeverageRiskLabel();
        },

        getMarketHealthClass() {
            if (!this.currentLeverageRatio) return 'text-secondary';
            const ratio = parseFloat(this.currentLeverageRatio);
            if (ratio > 0.5) return 'text-danger';
            if (ratio > 0.3) return 'text-warning';
            return 'text-success';
        },

        getMarketHealth() {
            if (!this.currentLeverageRatio) return 'Unknown';
            const ratio = parseFloat(this.currentLeverageRatio);
            if (ratio > 0.5) return 'Unhealthy';
            if (ratio > 0.3) return 'Moderate';
            return 'Healthy';
        }
    };
}