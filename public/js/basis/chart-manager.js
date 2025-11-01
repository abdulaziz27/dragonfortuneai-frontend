/**
 * Basis Chart Manager
 * Handles Chart.js rendering and updates for Basis dashboard
 */

import { BasisUtils } from './utils.js';

export class ChartManager {
    constructor(canvasId) {
        this.canvasId = canvasId;
        this.chart = null;
    }

    /**
     * Create or update chart smoothly
     * 
     * Note: Always destroys and recreates chart to avoid Chart.js
     * internal stack overflow issues during updates.
     */
    updateChart(data, chartType = 'line') {
        if (!data || data.length === 0) {
            console.warn('⚠️ No data provided to chart');
            return;
        }

        // Always destroy and recreate for stability
        // Chart.js incremental updates can cause stack overflow
        // with complex configurations (dual-axis, multiple datasets).
        // Performance impact is minimal with 5 second refresh interval.
        this.renderChart(data, chartType);
    }

    /**
     * Full chart render with cleanup
     */
    renderChart(data, chartType = 'line') {
        // Cleanup old chart
        this.destroy();

        // Verify Chart.js loaded
        if (typeof Chart === 'undefined') {
            console.warn('⚠️ Chart.js not loaded, retrying...');
            setTimeout(() => this.renderChart(data, chartType), 100);
            return;
        }

        // Get canvas
        const canvas = document.getElementById(this.canvasId);
        if (!canvas || !canvas.isConnected) {
            console.warn('⚠️ Canvas not available:', this.canvasId);
            return;
        }

        // Clear canvas to prevent memory leaks
        const ctx = canvas.getContext('2d');
        if (!ctx) {
            console.warn('⚠️ Cannot get 2D context');
            return;
        }

        // Clear canvas before rendering
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Small delay to ensure Chart.js internal state is cleared
        setTimeout(() => {
            const sorted = [...data].sort((a, b) => a.ts - b.ts);
            const labels = sorted.map(d => d.date || new Date(d.ts).toISOString());
            const basisValues = sorted.map(d => parseFloat(d.basisAbs || 0));

            if (chartType === 'line') {
                this.renderHistoryChart(sorted, labels, basisValues);
            } else {
                this.renderHistoryChart(sorted, labels, basisValues); // Default to line chart
            }
        }, 50); // Small delay to ensure destroy is complete
    }

    /**
     * Render history chart with dual-axis (basis + prices)
     */
    renderHistoryChart(sorted, labels, basisValues) {
        // Extract price data
        const spotPrices = sorted.map(d => parseFloat(d.spotPrice || 0));
        const futuresPrices = sorted.map(d => parseFloat(d.futuresPrice || 0));

        // Determine basis colors (green for positive, red for negative)
        const basisColors = basisValues.map(value => 
            value >= 0 ? 'rgba(34, 197, 94, 1)' : 'rgba(239, 68, 68, 1)'
        );

        // Create datasets for dual-axis
        const datasets = [
            {
                label: 'Basis (USD)',
                data: basisValues,
                borderColor: '#3b82f6',
                backgroundColor: (ctx) => {
                    const index = ctx.dataIndex;
                    const value = basisValues[index];
                    if (value >= 0) {
                        return 'rgba(34, 197, 94, 0.2)'; // Green fill for positive
                    } else {
                        return 'rgba(239, 68, 68, 0.2)'; // Red fill for negative
                    }
                },
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 4,
                yAxisID: 'y', // Left axis for basis
                segment: {
                    borderColor: (ctx) => {
                        const value = ctx.p1.raw;
                        return value >= 0 ? 'rgba(34, 197, 94, 1)' : 'rgba(239, 68, 68, 1)';
                    }
                }
            },
            {
                label: 'Spot Price',
                data: spotPrices,
                borderColor: '#f59e0b', // Yellow/gold
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                borderWidth: 1.5,
                fill: false,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 3,
                yAxisID: 'y1', // Right axis for price
                order: 2
            },
            {
                label: 'Futures Price',
                data: futuresPrices,
                borderColor: '#3b82f6', // Blue
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 1.5,
                fill: false,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 3,
                yAxisID: 'y1', // Right axis for price
                order: 3
            },
            // Zero reference line
            {
                label: 'Zero Reference',
                data: Array(basisValues.length).fill(0),
                borderColor: 'rgba(255, 255, 255, 0.6)',
                backgroundColor: 'transparent',
                borderWidth: 1,
                borderDash: [5, 5],
                fill: false,
                pointRadius: 0,
                pointHoverRadius: 0,
                yAxisID: 'y',
                order: 4,
                tension: 0
            }
        ];

        console.log('📊 History chart data prepared:', {
            basis: basisValues.length,
            spotPrice: spotPrices.length,
            futuresPrice: futuresPrices.length
        });

        const chartOptions = this.getChartOptions(true, basisValues); // Enable dual-axis, pass basis values for padding

        // Update tooltip (light theme)
        const originalTooltipFilter = chartOptions.plugins.tooltip?.filter;
        chartOptions.plugins.tooltip = {
            ...chartOptions.plugins.tooltip,
            backgroundColor: 'rgba(255, 255, 255, 0.98)',
            titleColor: '#1e293b',
            bodyColor: '#334155',
            borderColor: 'rgba(59, 130, 246, 0.3)',
            boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
            filter: (tooltipItem) => {
                // Hide zero reference line from tooltip
                if (tooltipItem.datasetIndex === 3) return false;
                if (originalTooltipFilter) {
                    return originalTooltipFilter(tooltipItem);
                }
                return true;
            },
            callbacks: {
                ...chartOptions.plugins.tooltip.callbacks,
                title: (items) => {
                    const date = new Date(items[0].label);
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');
                    return `${year}-${month}-${day} ${hours}:${minutes}`;
                },
                label: (context) => {
                    // Skip zero reference line
                    if (context.datasetIndex === 3) return [];

                    const index = context.dataIndex;
                    const item = sorted[index];

                    if (context.datasetIndex === 0) {
                        // Basis dataset
                        const basis = context.parsed.y;
                        const basisAnnualized = item?.basisAnnualized || 0;
                        return [
                            `Basis: ${BasisUtils.formatBasis(basis)} USD`,
                            `Annualized: ${BasisUtils.formatBasisAnnualized(basisAnnualized)}`
                        ];
                    } else if (context.datasetIndex === 1) {
                        // Spot Price dataset
                        const price = context.parsed.y;
                        return `Spot Price: ${BasisUtils.formatPrice(price)}`;
                    } else {
                        // Futures Price dataset
                        const price = context.parsed.y;
                        return `Futures Price: ${BasisUtils.formatPrice(price)}`;
                    }
                }
            }
        };

        // Enable legend
        chartOptions.plugins.legend = {
            display: true,
            position: 'top',
            labels: {
                usePointStyle: true,
                padding: 15,
                font: {
                    size: 11
                },
                generateLabels: (chart) => {
                    return [
                        {
                            text: 'Basis (USD)',
                            fillStyle: basisValues[basisValues.length - 1] >= 0 
                                ? 'rgba(34, 197, 94, 1)' 
                                : 'rgba(239, 68, 68, 1)',
                            strokeStyle: basisValues[basisValues.length - 1] >= 0 
                                ? 'rgba(34, 197, 94, 1)' 
                                : 'rgba(239, 68, 68, 1)',
                            datasetIndex: 0
                        },
                        {
                            text: 'Spot Price',
                            fillStyle: '#f59e0b',
                            strokeStyle: '#f59e0b',
                            datasetIndex: 1
                        },
                        {
                            text: 'Futures Price',
                            fillStyle: '#3b82f6',
                            strokeStyle: '#3b82f6',
                            datasetIndex: 2
                        }
                    ];
                },
                filter: (legendItem) => {
                    // Hide zero reference line from legend
                    return legendItem.datasetIndex !== 3;
                }
            }
        };

        const canvas = document.getElementById(this.canvasId);
        const ctx = canvas.getContext('2d');

        try {
            // Ensure chart is destroyed before creating new one
            if (this.chart) {
                this.destroy();
            }

            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: chartOptions,
                plugins: []
            });

            console.log('✅ History chart rendered successfully');
        } catch (error) {
            console.error('❌ Error rendering history chart:', error);
            this.chart = null;
        }
    }

    /**
     * Render term structure chart (bar chart)
     */
    renderTermStructureChart(termStructureData) {
        this.destroy();

        const canvas = document.getElementById('basisTermStructureChart');
        if (!canvas) {
            console.error('❌ Term structure canvas not found');
            return;
        }

        const ctx = canvas.getContext('2d');

        const basisCurve = termStructureData.basis_curve || [];
        const labels = basisCurve.map(item => item.expiry || 'Unknown');
        const basisValues = basisCurve.map(item => parseFloat(item.basis || 0));
        const basisAnnualizedValues = basisCurve.map(item => parseFloat(item.basis_annualized || 0));

        // Determine colors based on basis value
        const backgroundColors = basisValues.map(value => 
            value >= 0 ? 'rgba(34, 197, 94, 0.8)' : 'rgba(239, 68, 68, 0.8)'
        );
        const borderColors = basisValues.map(value => 
            value >= 0 ? 'rgba(34, 197, 94, 1)' : 'rgba(239, 68, 68, 1)'
        );

        const datasets = [
            {
                label: 'Basis (USD)',
                data: basisValues,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1,
                borderRadius: 4,
                yAxisID: 'y'
            },
            {
                label: 'Basis Annualized (%)',
                data: basisAnnualizedValues,
                type: 'line',
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: false,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6,
                yAxisID: 'y1',
                order: 1
            }
        ];

        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
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
                    backgroundColor: 'rgba(255, 255, 255, 0.98)',
                    titleColor: '#1e293b',
                    bodyColor: '#334155',
                    borderColor: 'rgba(59, 130, 246, 0.3)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
                    callbacks: {
                        title: (items) => {
                            return `Expiry: ${items[0].label}`;
                        },
                        label: (context) => {
                            if (context.datasetIndex === 0) {
                                const basis = context.parsed.y;
                                const annualized = basisAnnualizedValues[context.dataIndex];
                                return [
                                    `Basis: ${BasisUtils.formatBasis(basis)} USD`,
                                    `Annualized: ${BasisUtils.formatBasisAnnualized(annualized)}`
                                ];
                            } else {
                                const annualized = context.parsed.y;
                                return `Annualized: ${BasisUtils.formatBasisAnnualized(annualized)}`;
                            }
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#64748b',
                        font: { size: 11 }
                    },
                    grid: {
                        display: true,
                        color: 'rgba(148, 163, 184, 0.15)'
                    }
                },
                y: {
                    type: 'linear',
                    position: 'left',
                    ticks: {
                        color: '#64748b',
                        font: { size: 11 },
                        callback: (value) => BasisUtils.formatBasis(value) + ' USD'
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.15)'
                    },
                    title: {
                        display: true,
                        text: 'Basis (USD)',
                        color: '#475569',
                        font: { size: 11, weight: '500' }
                    }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    ticks: {
                        color: '#64748b',
                        font: { size: 11 },
                        callback: (value) => BasisUtils.formatBasisAnnualized(value)
                    },
                    grid: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Basis Annualized (%)',
                        color: '#475569',
                        font: { size: 11, weight: '500' }
                    }
                }
            }
        };

        try {
            // Ensure chart is destroyed before creating new one
            if (this.chart) {
                this.destroy();
            }

            this.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: chartOptions
            });

            console.log('✅ Term structure chart rendered successfully');
        } catch (error) {
            console.error('❌ Error rendering term structure chart:', error);
            this.chart = null;
        }
    }

    /**
     * Get chart configuration options
     */
    getChartOptions(hasPriceOverlay = false, dataValues = []) {
        // Calculate min/max from data to add padding/spacing
        let suggestedMin = undefined;
        let suggestedMax = undefined;

        if (dataValues && dataValues.length > 0) {
            const minValue = Math.min(...dataValues);
            const maxValue = Math.max(...dataValues);
            const dataRange = maxValue - minValue;

            // Calculate padding based on user's requirement:
            // - Negative data: use ~25% space below 0 (total height for negative = 25%)
            // - Positive data: use ~75% space above 0, but highest point at ~55% (20% space above highest point)

            if (minValue < 0 && maxValue > 0) {
                // Data spans both positive and negative
                const negativeRange = Math.abs(minValue);
                const positiveRange = maxValue;

                const negativeSpace = negativeRange / 0.25; // 25% of total for negative
                const positiveSpaceWithPadding = positiveRange / 0.55 * 1.20; // 75% of total, maxValue at 55%, +20% padding

                suggestedMin = -negativeSpace;
                suggestedMax = positiveSpaceWithPadding;

            } else if (minValue >= 0) {
                // Only positive data
                const totalPositiveSpace = maxValue / 0.55; // Space where maxValue is at 55%
                suggestedMax = totalPositiveSpace * 1.20; // Add 20% space above

                suggestedMin = Math.max(0, minValue - (dataRange * 0.05));
            } else {
                // Only negative data
                const totalNegativeSpace = Math.abs(minValue) / 0.25;
                suggestedMin = -totalNegativeSpace;
                suggestedMax = 0; // Cap at 0
            }

            console.log('📊 Y-axis padding calculation:', {
                minValue,
                maxValue,
                dataRange,
                suggestedMin,
                suggestedMax,
                topPadding: suggestedMax ? suggestedMax - maxValue : 0,
                topPaddingPercent: suggestedMax ? ((suggestedMax - maxValue) / (suggestedMax - (suggestedMin || minValue))) * 100 : 0
            });
        }

        const scales = {
            x: {
                ticks: {
                    color: '#64748b',
                    font: { size: 10 },
                    maxRotation: 45,
                    minRotation: 45,
                    callback: function (value, index) {
                        const labels = this.chart.data.labels;
                        if (!labels || labels.length === 0) return '';

                        const dates = labels.map(label => new Date(label));
                        const firstDate = dates[0];
                        const lastDate = dates[dates.length - 1];

                        let isHourlyData = false;
                        if (dates.length > 1) {
                            const timeSpanHours = (lastDate - firstDate) / (1000 * 60 * 60);
                            const avgIntervalHours = timeSpanHours / (dates.length - 1);
                            isHourlyData = avgIntervalHours <= 12;
                        }

                        const totalLabels = labels.length;
                        let showEvery;

                        if (isHourlyData) {
                            if (totalLabels <= 48) {
                                showEvery = 1;
                            } else if (totalLabels <= 96) {
                                showEvery = 2;
                            } else if (totalLabels <= 200) {
                                showEvery = 3;
                            } else {
                                showEvery = Math.ceil(totalLabels / 40);
                            }
                        } else {
                            if (totalLabels <= 24) {
                                showEvery = 1;
                            } else if (totalLabels <= 100) {
                                showEvery = Math.ceil(totalLabels / 20);
                            } else {
                                showEvery = Math.ceil(totalLabels / 25);
                            }
                        }

                        if (index === 0 || index === totalLabels - 1 || index % showEvery === 0) {
                            const currentDate = new Date(labels[index]);

                            if (isHourlyData) {
                                const year = currentDate.getFullYear();
                                const month = String(currentDate.getMonth() + 1).padStart(2, '0');
                                const day = String(currentDate.getDate());
                                const hours = String(currentDate.getHours()).padStart(2, '0');
                                const minutes = String(currentDate.getMinutes()).padStart(2, '0');
                                return `${year}-${month}-${day} ${hours}:${minutes}`;
                            } else {
                                return currentDate.toLocaleDateString('en-US', {
                                    month: 'short',
                                    day: 'numeric'
                                });
                            }
                        }

                        return '';
                    }
                },
                grid: {
                    display: true,
                    color: 'rgba(148, 163, 184, 0.15)'
                }
            },
            y: {
                type: 'linear',
                position: 'left',
                suggestedMin: suggestedMin,
                suggestedMax: suggestedMax,
                ticks: {
                    color: '#64748b',
                    font: { size: 11 },
                    callback: (value) => BasisUtils.formatBasis(value) + ' USD'
                },
                grid: {
                    color: 'rgba(148, 163, 184, 0.15)'
                },
                title: {
                    display: true,
                    text: 'Basis (USD)',
                    color: '#475569',
                    font: { size: 11, weight: '500' }
                }
            }
        };

        // Add right Y-axis for price if overlay enabled
        if (hasPriceOverlay) {
            scales.y1 = {
                type: 'linear',
                position: 'right',
                ticks: {
                    color: '#64748b',
                    font: { size: 11 },
                    callback: (value) => BasisUtils.formatPrice(value)
                },
                grid: {
                    display: false // Don't show grid for right axis to avoid clutter
                },
                title: {
                    display: true,
                    text: 'Price (USD)',
                    color: '#475569',
                    font: { size: 11, weight: '500' }
                }
            };
        }

        return {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: false // Will be set in renderHistoryChart
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.98)',
                    titleColor: '#1e293b',
                    bodyColor: '#334155',
                    borderColor: 'rgba(59, 130, 246, 0.3)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    filter: (tooltipItem) => {
                        // Hide zero reference line from tooltip (will be overridden in renderHistoryChart if needed)
                        return true; // Default: show all
                    }
                }
            },
            scales: scales
        };
    }

    /**
     * Destroy chart instance
     */
    destroy() {
        if (this.chart) {
            try {
                // Stop any running animations first
                this.chart.stop();
                
                // Destroy the chart
                this.chart.destroy();
                
                console.log('🗑️ Chart destroyed:', this.canvasId);
            } catch (error) {
                console.warn('⚠️ Error destroying chart:', error);
            }
            this.chart = null;
        }
    }
}

