/**
 * Options Chart Renderer
 * 
 * Handles chart rendering with race condition prevention
 * Follows proven patterns from Volatility and ETF dashboards
 */

class OptionsChartRenderer {
    constructor() {
        this.charts = {
            ivSmile: null,
            skew: null,
            oiVolume: null,
            gamma: null
        };
        
        // Chart color palettes
        this.smilePalette = {
            '7D': '#3b82f6',
            '14D': '#10b981',
            '30D': '#f59e0b',
            '90D': '#8b5cf6'
        };
        
        this.skewColors = ['#38bdf8', '#10b981', '#f59e0b', '#8b5cf6'];
    }

    /**
     * Centralized chart destruction with error handling
     */
    destroyAllCharts() {
        console.log('🗑️ Destroying all charts');
        
        const chartList = [
            { name: 'IV Smile', instance: this.charts.ivSmile, key: 'ivSmile' },
            { name: 'Skew', instance: this.charts.skew, key: 'skew' },
            { name: 'OI Volume', instance: this.charts.oiVolume, key: 'oiVolume' },
            { name: 'Gamma', instance: this.charts.gamma, key: 'gamma' }
        ];
        
        chartList.forEach(chart => {
            if (chart.instance) {
                try {
                    if (typeof chart.instance.stop === 'function') {
                        chart.instance.stop();
                    }
                    chart.instance.destroy();
                } catch (error) {
                    console.warn(`⚠️ Error destroying ${chart.name} chart:`, error);
                }
                this.charts[chart.key] = null;
            }
        });
    }

    /**
     * Render all charts with requestAnimationFrame for stability
     */
    renderAllCharts(data) {
        console.log('🎨 Rendering all charts');
        
        // Destroy existing charts first
        this.destroyAllCharts();
        
        // Use double requestAnimationFrame for stable rendering
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                this.renderIVSmileChart(data);
                this.renderSkewChart(data);
                this.renderOIVolumeChart(data);
                this.renderGammaChart(data);
            });
        });
    }

    /**
     * Render IV Smile Chart
     */
    renderIVSmileChart(data) {
        const ctx = document.getElementById('ivSmileChart');
        if (!ctx) {
            console.warn('❌ IV Smile chart canvas not found');
            return;
        }

        if (!data.ivSmile || !data.ivSmile.data || data.ivSmile.data.length === 0) {
            console.warn('❌ No IV Smile data available');
            return;
        }

        console.log('🎯 Rendering IV Smile chart');
        
        // Transform data for chart
        const smileDatasets = this.transformIVSmileForChart(data.ivSmile.data);
        const tenors = ['7D', '14D', '30D', '90D'];
        
        const datasets = tenors.map((tenor) => {
            const tenorData = smileDatasets[tenor] || [];
            return {
                label: tenor,
                data: tenorData.map(item => item.iv),
                borderColor: this.smilePalette[tenor],
                backgroundColor: this.smilePalette[tenor] + '33',
                tension: 0.35,
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5,
                fill: false
            };
        });

        // Get strike labels from first available tenor
        const firstTenor = tenors.find(tenor => smileDatasets[tenor] && smileDatasets[tenor].length > 0);
        const strikeLabels = firstTenor ? smileDatasets[firstTenor].map(item => `${Math.round(item.strike)}`) : [];

        this.charts.ivSmile = new Chart(ctx, {
            type: 'line',
            data: {
                labels: strikeLabels,
                datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 0  // ← CRITICAL: Disable animations to prevent race conditions
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: {
                            color: 'rgba(148, 163, 184, 0.2)'
                        },
                        ticks: {
                            callback: (value) => `${value}%`
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(148, 163, 184, 0.15)',
                            borderDash: [4, 4]
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end'
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        callbacks: {
                            label: (context) => `${context.dataset.label}: ${context.parsed.y}%`
                        }
                    }
                }
            }
        });
    }

    /**
     * Render Skew Chart
     */
    renderSkewChart(data) {
        const ctx = document.getElementById('skewChart');
        if (!ctx) {
            console.warn('❌ Skew chart canvas not found');
            return;
        }

        if (!data.skewHistory || !data.skewHistory.data || data.skewHistory.data.length === 0) {
            console.warn('❌ No Skew History data available');
            return;
        }

        console.log('🎯 Rendering Skew chart');
        
        const skewData = data.skewHistory.data;
        const tenors = ['7D', '14D', '30D', '90D'];
        
        const datasets = tenors.map((tenor, idx) => {
            const tenorData = skewData.filter(item => item.tenor === tenor);
            return {
                label: tenor,
                data: tenorData.map(item => item.rr25 * 100), // Convert to percentage
                borderColor: this.skewColors[idx % this.skewColors.length],
                backgroundColor: this.skewColors[idx % this.skewColors.length] + '33',
                tension: 0.35,
                borderWidth: 2,
                fill: false
            };
        });

        // Generate time labels from data
        const timeLabels = skewData.slice(0, 12).map(item => {
            const date = new Date(item.ts);
            return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        });

        this.charts.skew = new Chart(ctx, {
            type: 'line',
            data: {
                labels: timeLabels,
                datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 0  // ← CRITICAL: Disable animations
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: {
                            color: 'rgba(148, 163, 184, 0.2)'
                        },
                        ticks: {
                            callback: (value) => `${value}%`
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(148, 163, 184, 0.15)',
                            borderDash: [4, 4]
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end'
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        callbacks: {
                            label: (context) => `${context.dataset.label}: ${context.parsed.y}%`
                        }
                    }
                }
            }
        });
    }

    /**
     * Render OI & Volume Chart
     * Enhanced to use OI by Strike data for more detailed view
     */
    renderOIVolumeChart(data) {
        const ctx = document.getElementById('oiVolumeChart');
        if (!ctx) {
            console.warn('❌ OI Volume chart canvas not found');
            return;
        }

        // Prioritize OI by Strike data for more detailed view (5-10 bars vs 3 bars)
        let oiData, labels, chartTitle;
        
        if (data.oiByStrike && data.oiByStrike.data && data.oiByStrike.data.length > 0) {
            console.log('🎯 Rendering OI Volume chart with Strike data (enhanced view)');
            oiData = data.oiByStrike.data;
            labels = oiData.map(item => `$${(item.strike / 1000).toFixed(0)}k`);
            chartTitle = 'OI & Volume by Strike (Nearest Expiry)';
        } else if (data.oiByExpiry && data.oiByExpiry.data && data.oiByExpiry.data.length > 0) {
            console.log('🎯 Rendering OI Volume chart with Expiry data (fallback view)');
            oiData = data.oiByExpiry.data;
            labels = oiData.map(item => item.expiry);
            chartTitle = 'OI & Volume by Expiry';
        } else {
            console.warn('❌ No OI data available (neither Strike nor Expiry)');
            return;
        }
        
        this.charts.oiVolume = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Call OI',
                        data: oiData.map(item => item.call_oi || 0),
                        backgroundColor: '#10b981',
                        yAxisID: 'y'
                    },
                    {
                        label: 'Put OI',
                        data: oiData.map(item => item.put_oi || 0),
                        backgroundColor: '#ef4444',
                        yAxisID: 'y'
                    },
                    {
                        label: 'Total Volume',
                        data: oiData.map(item => (item.call_vol || 0) + (item.put_vol || 0)),
                        type: 'line',
                        borderColor: '#f59e0b',
                        backgroundColor: 'transparent',
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 0  // ← CRITICAL: Disable animations
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        position: 'left',
                        grid: {
                            color: 'rgba(148, 163, 184, 0.2)'
                        },
                        ticks: {
                            callback: (value) => this.formatCompact(value)
                        },
                        title: {
                            display: true,
                            text: 'Open Interest'
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: {
                            display: false
                        },
                        ticks: {
                            callback: (value) => this.formatCompact(value)
                        },
                        title: {
                            display: true,
                            text: 'Volume'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(148, 163, 184, 0.15)',
                            borderDash: [4, 4]
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: chartTitle,
                        font: {
                            size: 12
                        },
                        color: '#64748b'
                    },
                    legend: {
                        position: 'top',
                        align: 'end'
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        callbacks: {
                            label: (context) => {
                                const value = context.parsed.y ?? context.parsed;
                                return `${context.dataset.label}: ${this.formatCompact(value)}`;
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Render Gamma Exposure Chart
     */
    renderGammaChart(data) {
        const ctx = document.getElementById('gammaChart');
        if (!ctx) {
            console.warn('❌ Gamma chart canvas not found');
            return;
        }

        if (!data.dealerGreeksGex || !data.dealerGreeksGex.data || !Array.isArray(data.dealerGreeksGex.data)) {
            console.warn('❌ No Dealer Greeks GEX data available');
            return;
        }

        console.log('🎯 Rendering Gamma chart');
        
        // STABILIZATION: Sort and filter data for consistent chart display
        const rawData = data.dealerGreeksGex.data.slice(); // Create copy
        
        // Filter out extreme outliers (optional - can be removed if all data is valid)
        const filteredData = rawData.filter(item => {
            return item.price_level > 0 && 
                   Math.abs(item.gamma_exposure) < 1000000; // Filter extreme values
        });
        
        // Sort by price_level for consistent ordering
        const gammaData = filteredData.sort((a, b) => a.price_level - b.price_level);
        
        console.log('📊 Stabilized gamma data:', {
            original: rawData.length,
            filtered: filteredData.length,
            final: gammaData.length
        });
        
        const labels = gammaData.map(item => this.formatPriceLevel(item.price_level));
        const exposures = gammaData.map(item => item.gamma_exposure / 1000); // Convert to k

        this.charts.gamma = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Gamma Exposure (k)',
                    data: exposures,
                    backgroundColor: exposures.map(value => value >= 0 ? '#10b981' : '#ef4444'),
                    borderColor: exposures.map(value => value >= 0 ? '#059669' : '#dc2626'),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 0  // ← CRITICAL: Disable animations
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(148, 163, 184, 0.2)'
                        },
                        ticks: {
                            callback: (value) => `${value}k`
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(148, 163, 184, 0.15)',
                            borderDash: [4, 4]
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        callbacks: {
                            label: (context) => `Gamma: ${context.parsed.y}k`
                        }
                    }
                }
            }
        });
    }

    /**
     * Helper methods
     */
    transformIVSmileForChart(apiData) {
        if (!apiData || !Array.isArray(apiData)) return {};
        
        const grouped = {};
        apiData.forEach(item => {
            const tenor = item.tenor || '30D';
            if (!grouped[tenor]) {
                grouped[tenor] = [];
            }
            grouped[tenor].push({
                strike: item.strike,
                iv: item.iv * 100, // Convert to percentage
                delta: item.delta
            });
        });
        
        // Sort each tenor by strike price
        Object.keys(grouped).forEach(tenor => {
            grouped[tenor].sort((a, b) => a.strike - b.strike);
        });
        
        return grouped;
    }

    formatCompact(value) {
        if (value === null || value === undefined) return 'N/A';
        return new Intl.NumberFormat('en-US', { 
            notation: 'compact', 
            maximumFractionDigits: 1 
        }).format(value);
    }

    formatPriceLevel(value) {
        if (value === null || value === undefined) return 'N/A';
        if (value >= 1000) {
            return `${(value / 1000).toFixed(1)}k`;
        }
        return value.toLocaleString();
    }
}

// Export for use in Alpine.js components
window.OptionsChartRenderer = OptionsChartRenderer;