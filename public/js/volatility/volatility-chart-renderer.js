/**
 * Volatility Chart Renderer
 * Handles all Chart.js rendering logic for volatility dashboard
 */

class VolatilityChartRenderer {
    constructor() {
        this.charts = {
            volatilityTrend: null,
            candlestick: null,
            volume: null,
            volumeProfile: null,
            heatmap: null
        };
    }

    /**
     * Render Volatility Trend Chart (HV only)
     * @param {string} canvasId - Canvas element ID
     * @param {Array} trendsData - Array of {timestamp, hv}
     */
    renderVolatilityTrendChart(canvasId, trendsData) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.error(`❌ Canvas not found: ${canvasId}`);
            return;
        }

        // Handle empty data
        if (!trendsData || trendsData.length === 0) {
            console.warn('⚠️ No trends data to display');
            if (this.charts.volatilityTrend) {
                this.charts.volatilityTrend.destroy();
                this.charts.volatilityTrend = null;
            }
            this.showEmptyState(canvas, 'No volatility trend data available');
            return;
        }

        // Prepare data with proper timestamp handling
        const labels = trendsData.map(d => {
            const date = new Date(d.timestamp);
            return isNaN(date.getTime()) ? 'Invalid' : date.toLocaleDateString();
        });
        const hvData = trendsData.map(d => d.hv || 0);

        console.log(`📊 Rendering volatility trend chart with ${trendsData.length} HV data points`);

        // Always destroy existing chart first (avoid Alpine.js Proxy circular reference issues)
        if (this.charts.volatilityTrend) {
            try {
                this.charts.volatilityTrend.destroy();
                console.log('🗑️ Destroyed existing volatility trend chart');
            } catch (destroyError) {
                console.warn('⚠️ Error destroying chart:', destroyError);
            }
            this.charts.volatilityTrend = null;
        }

        const ctx = canvas.getContext('2d');

        // Create new chart with clean configuration (HV only)
        const chartConfig = {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Historical Volatility (HV)',
                        data: hvData,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false, // Disable animation to prevent issues
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            boxWidth: 10
                        }
                    },
                    tooltip: {
                        enabled: true,
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Volatility (%)'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                }
            }
        };

        this.charts.volatilityTrend = new Chart(ctx, chartConfig);

        console.log('✅ Volatility trend chart created');
    }

    /**
     * Render Candlestick Chart (using line chart)
     * @param {string} canvasId - Canvas element ID
     * @param {Array} ohlcData - Array of {timestamp, open, high, low, close, volume}
     */
    renderCandlestickChart(canvasId, ohlcData) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.error(`❌ Canvas not found: ${canvasId}`);
            return;
        }

        // Handle empty data
        if (!ohlcData || ohlcData.length === 0) {
            console.warn('⚠️ No OHLC data to display');
            if (this.charts.candlestick) {
                this.charts.candlestick.destroy();
                this.charts.candlestick = null;
            }
            this.showEmptyState(canvas, 'No OHLC data available');
            return;
        }

        // Prepare data with proper timestamp handling
        const labels = ohlcData.map(d => {
            const date = new Date(d.timestamp);
            return isNaN(date.getTime()) ? 'Invalid' : date.toLocaleDateString();
        });
        const closeData = ohlcData.map(d => d.close);
        const colors = ohlcData.map(d => d.close >= d.open ? 'rgba(34, 197, 94, 0.8)' : 'rgba(239, 68, 68, 0.8)');

        console.log(`📊 Rendering candlestick chart with ${ohlcData.length} candles`);

        // Always destroy existing chart first (avoid Alpine.js Proxy circular reference issues)
        if (this.charts.candlestick) {
            try {
                this.charts.candlestick.destroy();
                console.log('🗑️ Destroyed existing candlestick chart');
            } catch (destroyError) {
                console.warn('⚠️ Error destroying chart:', destroyError);
            }
            this.charts.candlestick = null;
        }

        const ctx = canvas.getContext('2d');

        // Store ohlcData reference for tooltip (avoid closure issues)
        const ohlcDataRef = [...ohlcData];

        // Create new chart with clean configuration
        const chartConfig = {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Close Price',
                    data: closeData,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.1,
                    fill: true,
                    pointBackgroundColor: colors,
                    pointBorderColor: colors,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false, // Disable animation
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0
                        }
                    },
                    y: {
                        position: 'right',
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                }
            }
        };

        this.charts.candlestick = new Chart(ctx, chartConfig);

        console.log('✅ Candlestick chart created');
    }

    /**
     * Render Volume Chart (below candlestick)
     * @param {string} canvasId - Canvas element ID
     * @param {Array} ohlcData - Array of {timestamp, open, close, volume}
     */
    renderVolumeChart(canvasId, ohlcData) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.error(`❌ Canvas not found: ${canvasId}`);
            return;
        }

        // Handle empty data
        if (!ohlcData || ohlcData.length === 0) {
            console.warn('⚠️ No volume data to display');
            if (this.charts.volume) {
                this.charts.volume.destroy();
                this.charts.volume = null;
            }
            return;
        }

        // Prepare data with proper timestamp handling
        const volumeData = ohlcData.map(d => d.volume || 0);
        const backgroundColors = ohlcData.map(d =>
            d.close >= d.open
                ? 'rgba(34, 197, 94, 0.5)'
                : 'rgba(239, 68, 68, 0.5)'
        );
        const labels = ohlcData.map(d => {
            const date = new Date(d.timestamp);
            return isNaN(date.getTime()) ? 'Invalid' : date.toLocaleDateString();
        });

        console.log(`📊 Rendering volume chart with ${ohlcData.length} bars`);

        // Always destroy existing chart first (avoid Alpine.js Proxy circular reference issues)
        if (this.charts.volume) {
            try {
                this.charts.volume.destroy();
                console.log('🗑️ Destroyed existing volume chart');
            } catch (destroyError) {
                console.warn('⚠️ Error destroying chart:', destroyError);
            }
            this.charts.volume = null;
        }

        const ctx = canvas.getContext('2d');

        // Create new chart with clean configuration
        const chartConfig = {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Volume',
                    data: volumeData,
                    backgroundColor: backgroundColors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false, // Disable animation
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        display: false,
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        position: 'right',
                        grid: {
                            display: false
                        }
                    }
                }
            }
        };

        this.charts.volume = new Chart(ctx, chartConfig);

        console.log('✅ Volume chart created');
    }

    /**
     * Show empty state message on canvas
     */
    showEmptyState(canvas, message) {
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.font = '14px Arial';
        ctx.fillStyle = '#6b7280';
        ctx.textAlign = 'center';
        ctx.fillText(message, canvas.width / 2, canvas.height / 2);
    }

    /**
     * Destroy specific chart
     */
    destroyChart(chartName) {
        if (this.charts[chartName]) {
            try {
                this.charts[chartName].destroy();
            } catch (e) {
                console.warn(`⚠️ Error destroying ${chartName}:`, e);
            }
            this.charts[chartName] = null;
            console.log(`🗑️ Chart destroyed: ${chartName}`);
        }
    }

    /**
     * Render Intraday Volatility Heatmap
     * @param {string} canvasId - Canvas element ID
     * @param {Array} heatmapData - 7x24 matrix (day x hour)
     */
    renderIntradayHeatmap(canvasId, heatmapData) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.error(`❌ Canvas not found: ${canvasId}`);
            return;
        }

        // Handle empty data
        if (!heatmapData || heatmapData.length === 0) {
            console.warn('⚠️ No heatmap data to display');
            if (this.charts.heatmap) {
                this.charts.heatmap.destroy();
                this.charts.heatmap = null;
            }
            this.showEmptyState(canvas, 'No heatmap data available');
            return;
        }

        console.log(`📊 Rendering intraday heatmap with ${heatmapData.length}x${heatmapData[0].length} data`);

        // Always destroy existing chart first
        if (this.charts.heatmap) {
            try {
                this.charts.heatmap.destroy();
                console.log('🗑️ Destroyed existing heatmap chart');
            } catch (destroyError) {
                console.warn('⚠️ Error destroying chart:', destroyError);
            }
            this.charts.heatmap = null;
        }

        const ctx = canvas.getContext('2d');

        // Prepare data for Chart.js
        const dayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        const hourLabels = Array.from({length: 24}, (_, i) => `${i}:00`);
        
        // Flatten data for bar chart (we'll use grouped bars to simulate heatmap)
        const datasets = dayLabels.map((day, dayIndex) => ({
            label: day,
            data: heatmapData[dayIndex] || Array(24).fill(0),
            backgroundColor: this.getHeatmapColor(dayIndex),
            borderWidth: 0
        }));

        // Create new chart
        const chartConfig = {
            type: 'bar',
            data: {
                labels: hourLabels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            boxWidth: 12,
                            padding: 8,
                            font: {
                                size: 10
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            title: (context) => {
                                return `Hour: ${context[0].label}`;
                            },
                            label: (context) => {
                                return `${context.dataset.label}: ${context.parsed.y.toFixed(2)}%`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: false,
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 9
                            },
                            maxRotation: 45,
                            minRotation: 45
                        }
                    },
                    y: {
                        stacked: false,
                        title: {
                            display: true,
                            text: 'Volatility (%)'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                }
            }
        };

        this.charts.heatmap = new Chart(ctx, chartConfig);
        console.log('✅ Intraday heatmap chart created');
    }

    /**
     * Get color for heatmap based on day index
     */
    getHeatmapColor(dayIndex) {
        const colors = [
            'rgba(239, 68, 68, 0.6)',   // Sunday - Red
            'rgba(59, 130, 246, 0.6)',  // Monday - Blue
            'rgba(34, 197, 94, 0.6)',   // Tuesday - Green
            'rgba(245, 158, 11, 0.6)',  // Wednesday - Orange
            'rgba(139, 92, 246, 0.6)',  // Thursday - Purple
            'rgba(236, 72, 153, 0.6)',  // Friday - Pink
            'rgba(20, 184, 166, 0.6)'   // Saturday - Teal
        ];
        return colors[dayIndex] || 'rgba(100, 100, 100, 0.6)';
    }

    /**
     * Destroy all charts
     */
    destroyAllCharts() {
        Object.keys(this.charts).forEach(chartName => {
            this.destroyChart(chartName);
        });
        console.log('🗑️ All charts destroyed');
    }

    /**
     * Render Volume Profile Chart (Horizontal Bar Chart)
     * @param {string} canvasId - Canvas element ID
     * @param {Object} volumeProfile - Volume profile data {bins, poc, vah, val, currentPrice}
     */
    renderVolumeProfileChart(canvasId, volumeProfile) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.error(`❌ Canvas not found: ${canvasId}`);
            return;
        }

        // Handle empty data
        if (!volumeProfile || !volumeProfile.bins || volumeProfile.bins.length === 0) {
            console.warn('⚠️ No volume profile data to display');
            if (this.charts.volumeProfile) {
                this.charts.volumeProfile.destroy();
                this.charts.volumeProfile = null;
            }
            this.showEmptyState(canvas, 'No volume profile data available');
            return;
        }

        console.log(`📊 Rendering volume profile chart with ${volumeProfile.bins.length} bins`);

        // Always destroy existing chart first
        if (this.charts.volumeProfile) {
            try {
                this.charts.volumeProfile.destroy();
                console.log('🗑️ Destroyed existing volume profile chart');
            } catch (destroyError) {
                console.warn('⚠️ Error destroying chart:', destroyError);
            }
            this.charts.volumeProfile = null;
        }

        const ctx = canvas.getContext('2d');

        // Prepare data
        const labels = volumeProfile.bins.map(b => b.price.toFixed(2));
        const volumes = volumeProfile.bins.map(b => b.volume);
        
        // Color bars based on POC, VAH, VAL
        const backgroundColors = volumeProfile.bins.map(bin => {
            if (Math.abs(bin.price - volumeProfile.poc) < 0.01) {
                return 'rgba(59, 130, 246, 0.8)'; // POC - Blue
            } else if (bin.price >= volumeProfile.val && bin.price <= volumeProfile.vah) {
                return 'rgba(34, 197, 94, 0.6)'; // Value Area - Green
            } else {
                return 'rgba(156, 163, 175, 0.4)'; // Outside VA - Gray
            }
        });

        // Create chart
        const chartConfig = {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Volume',
                    data: volumes,
                    backgroundColor: backgroundColors,
                    borderWidth: 0
                }]
            },
            options: {
                indexAxis: 'y', // Horizontal bars
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            title: (context) => {
                                return `Price: $${context[0].label}`;
                            },
                            label: (context) => {
                                const volume = context.parsed.x;
                                return `Volume: ${volume.toLocaleString()}`;
                            },
                            afterLabel: (context) => {
                                const price = parseFloat(context.label);
                                if (Math.abs(price - volumeProfile.poc) < 0.01) {
                                    return '🎯 POC (Point of Control)';
                                } else if (price >= volumeProfile.val && price <= volumeProfile.vah) {
                                    return '✅ Value Area (70% volume)';
                                }
                                return '';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Volume'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Price Level'
                        },
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 9
                            }
                        }
                    }
                }
            }
        };

        this.charts.volumeProfile = new Chart(ctx, chartConfig);
        console.log('✅ Volume profile chart created');
    }

}
