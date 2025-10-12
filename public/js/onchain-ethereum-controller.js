/**
 * Ethereum On-Chain Metrics Controller
 * Handles network gas metrics and ETH 2.0 staking data
 */

function onchainEthereumController() {
    return {
        // Global state
        loading: false,
        selectedWindow: 'day',
        selectedLimit: 200,
        
        // Component-specific state
        gasData: [],
        gasSummary: null,
        stakingData: [],
        stakingSummary: null,
        
        // Loading states
        loadingStates: {
            gas: false,
            staking: false
        },
        
        // Chart IDs for DOM storage (NO chart instances in Alpine data!)
        gasChartId: 'gasChart_' + Math.random().toString(36).substr(2, 9),
        stakingChartId: 'stakingChart_' + Math.random().toString(36).substr(2, 9),
        
        // Initialize controller
        init() {
            console.log('🚀 Initializing Ethereum On-Chain Metrics Controller');
            
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
                    if (typeof Chart !== 'undefined' && Chart.registry && Chart.registry.getScale('time')) {
                        resolve();
                    } else {
                        setTimeout(checkChart, 100);
                    }
                };
                checkChart();
            });
        },
        
        // Helper methods for DOM-based chart storage
        getGasChart() {
            const canvas = this.$refs.gasChart;
            return canvas ? canvas._chartInstance : null;
        },
        
        setGasChart(chartInstance) {
            const canvas = this.$refs.gasChart;
            if (canvas) canvas._chartInstance = chartInstance;
        },
        
        getStakingChart() {
            const canvas = this.$refs.stakingChart;
            return canvas ? canvas._chartInstance : null;
        },
        
        setStakingChart(chartInstance) {
            const canvas = this.$refs.stakingChart;
            if (canvas) canvas._chartInstance = chartInstance;
        },
        
        // Initialize all charts
        initializeCharts() {
            // Use setTimeout to ensure DOM is ready
            setTimeout(() => {
                this.initGasChart();
                this.initStakingChart();
            }, 100);
        },
        
        // Initialize gas metrics chart
        initGasChart() {
            const canvas = this.$refs.gasChart;
            if (!canvas) {
                console.warn('Gas chart canvas not found');
                return;
            }
            
            const ctx = canvas.getContext('2d');
            
            // Destroy existing chart if any
            const existingChart = this.getGasChart();
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
                            label: 'Gas Price (Gwei)',
                            data: [],
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            yAxisID: 'y',
                            tension: 0.4
                        },
                        {
                            label: 'Gas Used %',
                            data: [],
                            borderColor: '#22c55e',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            yAxisID: 'y1',
                            tension: 0.4
                        },
                        {
                            label: 'Gas Limit (M)',
                            data: [],
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            yAxisID: 'y2',
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
                                    
                                    if (label.includes('Gas Price')) {
                                        return `${label}: ${value.toFixed(2)} Gwei`;
                                    } else if (label.includes('Gas Used')) {
                                        return `${label}: ${value.toFixed(1)}%`;
                                    } else if (label.includes('Gas Limit')) {
                                        return `${label}: ${(value / 1000000).toFixed(2)}M`;
                                    }
                                    return `${label}: ${value}`;
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
                                text: 'Gas Price (Gwei)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Utilization %'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        },
                        y2: {
                            type: 'linear',
                            display: false,
                            position: 'right',
                        }
                    }
                }
                });
                
                // Store in DOM, not in Alpine data
                this.setGasChart(chartInstance);
            });
        },
        
        // Initialize staking deposits chart
        initStakingChart() {
            const canvas = this.$refs.stakingChart;
            if (!canvas) {
                console.warn('Staking chart canvas not found');
                return;
            }
            
            const ctx = canvas.getContext('2d');
            
            // Destroy existing chart if any
            const existingChart = this.getStakingChart();
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
                            label: 'Staking Inflow (ETH)',
                            data: [],
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
                                    return `Staking Inflow: ${this.formatETH(value)}`;
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
                                text: 'ETH Staked'
                            },
                            ticks: {
                                callback: (value) => this.formatETH(value)
                            }
                        }
                    }
                }
                });
                
                // Store in DOM, not in Alpine data
                this.setStakingChart(chartInstance);
            });
        },
        
        // Load all data
        async loadAllData() {
            this.loading = true;
            try {
                await Promise.all([
                    this.loadGasData(),
                    this.loadStakingData()
                ]);
            } catch (error) {
                console.error('❌ Error loading Ethereum data:', error);
            } finally {
                this.loading = false;
            }
        },
        
        // Load network gas data
        async loadGasData() {
            this.loadingStates.gas = true;
            try {
                const [gasResponse, summaryResponse] = await Promise.all([
                    this.fetchAPI(`/api/onchain/eth/network-gas?window=${this.selectedWindow}&limit=${this.selectedLimit}`),
                    this.fetchAPI(`/api/onchain/eth/network-gas/summary?window=${this.selectedWindow}&limit=${this.selectedLimit}`)
                ]);
                
                this.gasData = gasResponse.data || [];
                this.gasSummary = summaryResponse.data || null;
                
                this.updateGasChart();
                
                console.log('✅ Gas data loaded:', this.gasData.length, 'records');
            } catch (error) {
                console.error('❌ Error loading gas data:', error);
                this.gasData = [];
                this.gasSummary = null;
            } finally {
                this.loadingStates.gas = false;
            }
        },
        
        // Load staking deposits data
        async loadStakingData() {
            this.loadingStates.staking = true;
            try {
                const [stakingResponse, summaryResponse] = await Promise.all([
                    this.fetchAPI(`/api/onchain/eth/staking-deposits?window=${this.selectedWindow}&limit=${this.selectedLimit}`),
                    this.fetchAPI(`/api/onchain/eth/staking-deposits/summary?window=${this.selectedWindow}&limit=${this.selectedLimit}`)
                ]);
                
                this.stakingData = stakingResponse.data || [];
                this.stakingSummary = summaryResponse.data || null;
                
                this.updateStakingChart();
                
                console.log('✅ Staking data loaded:', this.stakingData.length, 'records');
            } catch (error) {
                console.error('❌ Error loading staking data:', error);
                this.stakingData = [];
                this.stakingSummary = null;
            } finally {
                this.loadingStates.staking = false;
            }
        },
        
        // Update gas chart with new data
        updateGasChart() {
            const chart = this.getGasChart();
            if (!chart || !this.gasData.length) return;
            
            const labels = this.gasData.map(item => {
                const date = new Date(item.timestamp);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
            const gasPrices = this.gasData.map(item => item.gas_price_mean);
            const gasUsedPercent = this.gasData.map(item => 
                (item.gas_used_mean / item.gas_limit_mean) * 100
            );
            const gasLimits = this.gasData.map(item => item.gas_limit_mean);
            
            // Update chart data outside Alpine reactivity
            queueMicrotask(() => {
                chart.data.labels = labels;
                chart.data.datasets[0].data = gasPrices;
                chart.data.datasets[1].data = gasUsedPercent;
                chart.data.datasets[2].data = gasLimits;
                
                chart.update('none');
            });
        },
        
        // Update staking chart with new data
        updateStakingChart() {
            const chart = this.getStakingChart();
            if (!chart || !this.stakingData.length) return;
            
            const labels = this.stakingData.map(item => {
                const date = new Date(item.timestamp);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
            const stakingInflows = this.stakingData.map(item => item.staking_inflow_total);
            
            // Update chart data outside Alpine reactivity
            queueMicrotask(() => {
                chart.data.labels = labels;
                chart.data.datasets[0].data = stakingInflows;
                
                chart.update('none');
            });
        },
        
        // Refresh all data
        async refreshAll() {
            await this.loadAllData();
        },
        
        // Refresh gas data only
        async refreshGasData() {
            await this.loadGasData();
        },
        
        // Refresh staking data only
        async refreshStakingData() {
            await this.loadStakingData();
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
        formatGasPrice(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            return `${parseFloat(value).toFixed(2)} Gwei`;
        },
        
        formatETH(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            if (num >= 1000000) return `${(num / 1000000).toFixed(2)}M ETH`;
            if (num >= 1000) return `${(num / 1000).toFixed(2)}K ETH`;
            return `${num.toFixed(2)} ETH`;
        },
        
        formatUtilization(gasUsed, gasLimit) {
            if (!gasUsed || !gasLimit || isNaN(gasUsed) || isNaN(gasLimit)) return 'N/A';
            const percent = (parseFloat(gasUsed) / parseFloat(gasLimit)) * 100;
            return `${percent.toFixed(1)}%`;
        },
        
        formatPercentage(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            return `${num >= 0 ? '+' : ''}${num.toFixed(2)}%`;
        },
        
        formatGasUsage(value) {
            if (value === null || value === undefined || isNaN(value)) return 'N/A';
            const num = parseFloat(value);
            if (num >= 1e12) return `${(num / 1e12).toFixed(2)}T`;
            if (num >= 1e9) return `${(num / 1e9).toFixed(2)}B`;
            if (num >= 1e6) return `${(num / 1e6).toFixed(2)}M`;
            return num.toLocaleString();
        },
        
        // Style helpers
        getGasPriceClass() {
            if (!this.gasSummary?.latest?.gas_price_mean) return 'text-secondary';
            const price = parseFloat(this.gasSummary.latest.gas_price_mean);
            if (price > 50) return 'text-danger';
            if (price > 20) return 'text-warning';
            return 'text-success';
        },
        
        getGasPriceChangeClass() {
            if (!this.gasSummary?.change_pct?.gas_price_mean) return 'text-secondary';
            const change = parseFloat(this.gasSummary.change_pct.gas_price_mean);
            return change >= 0 ? 'text-danger' : 'text-success';
        },
        
        getUtilizationClass() {
            if (!this.gasSummary?.latest) return 'text-secondary';
            const utilization = (this.gasSummary.latest.gas_used_mean / this.gasSummary.latest.gas_limit_mean) * 100;
            if (utilization > 90) return 'text-danger';
            if (utilization > 70) return 'text-warning';
            return 'text-success';
        },
        
        getGasUsageChangeClass() {
            if (!this.gasSummary?.change_pct?.gas_used_total) return 'text-secondary';
            const change = parseFloat(this.gasSummary.change_pct.gas_used_total);
            return change >= 0 ? 'text-success' : 'text-danger';
        },
        
        getStakingInflowClass() {
            if (!this.stakingSummary?.latest?.staking_inflow_total) return 'text-secondary';
            const inflow = parseFloat(this.stakingSummary.latest.staking_inflow_total);
            if (inflow > 100000) return 'text-success';
            if (inflow > 50000) return 'text-warning';
            return 'text-secondary';
        },
        
        getStakingChangeClass() {
            if (!this.stakingSummary?.latest?.change_pct) return 'text-secondary';
            const change = parseFloat(this.stakingSummary.latest.change_pct);
            return change >= 0 ? 'text-success' : 'text-danger';
        },
        
        getMomentumClass() {
            if (!this.stakingSummary?.momentum_pct) return 'text-secondary';
            const momentum = parseFloat(this.stakingSummary.momentum_pct);
            if (momentum > 100) return 'text-success';
            if (momentum > 0) return 'text-warning';
            return 'text-danger';
        },
        
        getMomentumLabel() {
            if (!this.stakingSummary?.momentum_pct) return 'No data';
            const momentum = parseFloat(this.stakingSummary.momentum_pct);
            if (momentum > 100) return 'Strong acceleration';
            if (momentum > 50) return 'Moderate acceleration';
            if (momentum > 0) return 'Slight acceleration';
            if (momentum > -50) return 'Slight deceleration';
            return 'Strong deceleration';
        }
    };
}