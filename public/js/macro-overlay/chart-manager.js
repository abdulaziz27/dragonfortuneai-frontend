/**
 * Macro Overlay Chart Manager
 * Manages Chart.js instances for macro indicators
 * Pattern: Open Interest blueprint (race condition protection)
 */

export class MacroChartManager {
    constructor() {
        this.charts = new Map();
        this.renderingFlags = new Map(); // Track rendering state per chart
    }

    /**
     * Render FRED time series chart
     */
    renderFredChart(canvasId, data, options = {}) {
        const {
            seriesId = '',
            label = '',
            color = '#3b82f6',
            yAxisLabel = 'Value'
        } = options;

        // ⚡ FIXED: Prevent concurrent renders
        if (this.renderingFlags.get(canvasId)) {
            console.warn(`⚠️ Chart ${canvasId} render already in progress, skipping`);
            return;
        }

        this.renderingFlags.set(canvasId, true);

        console.log(`📊 Rendering FRED chart for ${seriesId}:`, {
            dataPoints: data.length,
            sample: data[0]
        });

        try {
            // Verify Chart.js loaded
            if (typeof Chart === 'undefined') {
                console.warn('⚠️ Chart.js not loaded, aborting render');
                this.renderingFlags.set(canvasId, false);
                return;
            }

            // Destroy existing chart
            if (this.charts.has(canvasId)) {
                try {
                    this.charts.get(canvasId).destroy();
                    console.log(`🗑️ Destroyed existing chart: ${canvasId}`);
                } catch (e) {
                    console.warn(`⚠️ Error destroying chart ${canvasId}:`, e);
                }
                this.charts.delete(canvasId);
            }

            const canvas = document.getElementById(canvasId);
            if (!canvas || !canvas.isConnected) {
                console.error(`Canvas not found or not connected: ${canvasId}`);
                this.renderingFlags.set(canvasId, false);
                return;
            }

            const ctx = canvas.getContext('2d');
            if (!ctx) {
                console.error(`Cannot get context for canvas: ${canvasId}`);
                this.renderingFlags.set(canvasId, false);
                return;
            }

            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(d => d.date),
                    datasets: [{
                        label: label || seriesId,
                        data: data.map(d => d.value),
                        borderColor: color,
                        backgroundColor: color + '20',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: color,
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false, // ⚡ FIXED: Disable animations for instant updates (Open Interest pattern)
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: color,
                            borderWidth: 1,
                            displayColors: false,
                            callbacks: {
                                title: (context) => {
                                    return context[0].label;
                                },
                                label: (context) => {
                                    return `${label}: ${context.parsed.y.toFixed(2)}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                unit: 'month',
                                displayFormats: {
                                    month: 'MMM yy'
                                }
                            },
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 8
                            }
                        },
                        y: {
                            beginAtZero: false,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toFixed(2);
                                }
                            },
                            title: {
                                display: true,
                                text: yAxisLabel
                            }
                        }
                    }
                }
            });

            this.charts.set(canvasId, chart);
            console.log(`✅ Chart rendered: ${canvasId}`);

        } catch (error) {
            console.error(`Chart render error for ${canvasId}:`, error);
        } finally {
            // ⚡ FIXED: Always reset rendering flag
            this.renderingFlags.set(canvasId, false);
        }
    }

    /**
     * Render Bitcoin vs M2 comparison chart
     */
    renderBitcoinM2Chart(canvasId, data, options = {}) {
        // ⚡ FIXED: Prevent concurrent renders
        if (this.renderingFlags.get(canvasId)) {
            console.warn(`⚠️ Chart ${canvasId} render already in progress, skipping`);
            return;
        }

        this.renderingFlags.set(canvasId, true);

        console.log('📊 Rendering Bitcoin vs M2 chart:', {
            dataPoints: data.length,
            sample: data[0]
        });

        try {
            // Verify Chart.js loaded
            if (typeof Chart === 'undefined') {
                console.warn('⚠️ Chart.js not loaded, aborting render');
                this.renderingFlags.set(canvasId, false);
                return;
            }

            // Destroy existing chart
            if (this.charts.has(canvasId)) {
                try {
                    this.charts.get(canvasId).destroy();
                    console.log(`🗑️ Destroyed existing chart: ${canvasId}`);
                } catch (e) {
                    console.warn(`⚠️ Error destroying chart ${canvasId}:`, e);
                }
                this.charts.delete(canvasId);
            }

            const canvas = document.getElementById(canvasId);
            if (!canvas || !canvas.isConnected) {
                console.error(`Canvas not found or not connected: ${canvasId}`);
                this.renderingFlags.set(canvasId, false);
                return;
            }

            const ctx = canvas.getContext('2d');
            if (!ctx) {
                console.error(`Cannot get context for canvas: ${canvasId}`);
                this.renderingFlags.set(canvasId, false);
                return;
            }

            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(d => d.date),
                    datasets: [
                        {
                            label: 'BTC Price',
                            data: data.map(d => d.price),
                            borderColor: '#f59e0b',
                            backgroundColor: '#f59e0b20',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0,
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: '#f59e0b',
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 2,
                            yAxisID: 'y'
                        },
                        {
                            label: 'M2 YoY Growth %',
                            data: data.map(d => d.global_m2_yoy_growth),
                            borderColor: '#3b82f6',
                            backgroundColor: '#3b82f620',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.4,
                            pointRadius: 0,
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: '#3b82f6',
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 2,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false, // ⚡ FIXED: Disable animations for instant updates
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#f59e0b',
                            borderWidth: 1,
                            displayColors: true,
                            callbacks: {
                                title: (context) => {
                                    return context[0].label;
                                },
                                label: (context) => {
                                    const item = data[context.dataIndex];
                                    if (context.datasetIndex === 0) {
                                        return `BTC Price: $${item.price.toLocaleString()}`;
                                    } else {
                                        return `M2 YoY Growth: ${item.global_m2_yoy_growth.toFixed(2)}%`;
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                unit: 'month',
                                displayFormats: {
                                    month: 'MMM yy'
                                }
                            },
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 10
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            beginAtZero: false,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            },
                            title: {
                                display: true,
                                text: 'BTC Price (USD)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toFixed(1) + '%';
                                }
                            },
                            title: {
                                display: true,
                                text: 'M2 YoY Growth (%)'
                            }
                        }
                    }
                }
            });

            this.charts.set(canvasId, chart);
            console.log('✅ Bitcoin vs M2 chart rendered');

        } catch (error) {
            console.error('Chart render error:', error);
        } finally {
            // ⚡ FIXED: Always reset rendering flag
            this.renderingFlags.set(canvasId, false);
        }
    }

    /**
     * Destroy a specific chart safely
     */
    destroyChart(canvasId) {
        if (this.charts.has(canvasId)) {
            try {
                const chart = this.charts.get(canvasId);
                
                // ⚡ Stop animations before destroying (Open Interest pattern)
                if (chart.options && chart.options.animation) {
                    chart.options.animation = false;
                }
                
                // Stop chart updates
                if (chart.stop) chart.stop();
                
                // Destroy chart
                chart.destroy();
                this.charts.delete(canvasId);
                console.log(`🗑️ Chart destroyed: ${canvasId}`);
            } catch (e) {
                console.warn(`⚠️ Error destroying chart ${canvasId}:`, e);
                this.charts.delete(canvasId);
            }
        }
    }

    /**
     * Destroy all charts safely
     */
    destroyAllCharts() {
        this.charts.forEach((chart, id) => {
            try {
                if (chart.options && chart.options.animation) {
                    chart.options.animation = false;
                }
                if (chart.stop) chart.stop();
                chart.destroy();
                console.log(`🗑️ Chart destroyed: ${id}`);
            } catch (e) {
                console.warn(`⚠️ Error destroying chart ${id}:`, e);
            }
        });
        this.charts.clear();
    }
}

