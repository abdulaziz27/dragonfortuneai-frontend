/**
 * Long Short Ratio Chart Manager
 * Handles Chart.js operations for multiple charts
 */

import { LongShortRatioUtils } from './utils.js';

export class ChartManager {
    constructor(canvasId) {
        this.canvasId = canvasId;
        this.chart = null;
    }

    /**
     * Destroy existing chart
     */
    destroy() {
        if (this.chart) {
            try {
                this.chart.stop(); // Stop animations
                this.chart.destroy();
                console.log('🗑️ Chart destroyed:', this.canvasId);
            } catch (error) {
                console.warn('⚠️ Error destroying chart:', error);
            }
            this.chart = null;
        }
    }

    /**
     * Render main chart (Long/Short Ratio)
     */
    renderMainChart(data, chartType = 'line', priceData = []) {
        this.destroy();

        const canvas = document.getElementById(this.canvasId);
        if (!canvas) {
            console.warn('⚠️ Canvas element not found:', this.canvasId);
            return;
        }

        const ctx = canvas.getContext('2d');
        if (!ctx) {
            console.warn('⚠️ Cannot get 2D context');
            return;
        }

        if (!data || data.length === 0) {
            console.warn('⚠️ No data available for main chart');
            return;
        }

        // Small delay to ensure destroy is complete
        setTimeout(() => {
            try {
                const sorted = [...data].sort((a, b) => (a.time || a.ts) - (b.time || b.ts));
                // Keep full timestamp (milliseconds) for accurate time display in tooltip
                const labels = sorted.map(d => d.time || d.ts);
                
                // FASE 1: Extract ratio value from Internal API (top-accounts endpoint)
                const ratioValues = sorted.map(d => {
                    return parseFloat(d.ls_ratio_accounts || 0);
                });
                
                console.log('📊 Main Chart Rendering:', {
                    dataPoints: ratioValues.length,
                    first: ratioValues[0],
                    last: ratioValues[ratioValues.length - 1]
                });

                // Prepare dataset based on chart type
                let datasets;
                
                if (chartType === 'bar') {
                    // Bar chart with dynamic colors (green for > 1, red for < 1)
                    const barColors = ratioValues.map(value => 
                        value >= 1 ? 'rgba(16, 185, 129, 0.8)' : 'rgba(239, 68, 68, 0.8)'
                    );
                    const borderColors = ratioValues.map(value => 
                        value >= 1 ? '#10b981' : '#ef4444'
                    );
                    
                    datasets = [{
                        label: 'Long/Short Ratio',
                        data: ratioValues,
                        backgroundColor: barColors,
                        borderColor: borderColors,
                        borderWidth: 1.5,
                        borderRadius: 4
                    }];
                } else if (chartType === 'area') {
                    // Area chart with fill
                    datasets = [{
                        label: 'Long/Short Ratio',
                        data: ratioValues,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 4
                    }];
                } else {
                    // Line chart (default)
                    datasets = [{
                        label: 'Long/Short Ratio',
                        data: ratioValues,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 4
                    }];
                }

                // Add price overlay if available
                if (priceData.length > 0) {
                    const priceMap = new Map(priceData.map(p => [p.date, p.price]));
                    const alignedPrices = labels.map(label => {
                        // label is now timestamp in milliseconds
                        const date = new Date(label);
                        const dateStr = date.toISOString().split('T')[0];
                        return priceMap.get(dateStr) || null;
                    });

                    datasets.push({
                        label: 'BTC Price',
                        data: alignedPrices,
                        borderColor: '#f59e0b',
                        backgroundColor: 'transparent',
                        borderWidth: 1.5,
                        fill: false,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 3,
                        yAxisID: 'y1',
                        order: 2
                    });
                }

                const chartOptions = this.getMainChartOptions(priceData.length > 0);

                this.chart = new Chart(ctx, {
                    type: chartType === 'bar' ? 'bar' : 'line',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: chartOptions
                });

                console.log('✅ Main chart rendered:', this.canvasId);
            } catch (error) {
                console.error('❌ Error rendering main chart:', error);
                this.chart = null;
            }
        }, 50);
    }

    /**
     * Render comparison chart (3 ratios: Global, Top Account, Top Position)
     */
    renderComparisonChart(globalData = [], topAccountData = [], topPositionData = []) {
        this.destroy();

        const canvas = document.getElementById(this.canvasId);
        if (!canvas) {
            console.warn('⚠️ Canvas element not found:', this.canvasId);
            return;
        }

        const ctx = canvas.getContext('2d');
        if (!ctx) {
            console.warn('⚠️ Cannot get 2D context');
            return;
        }

        if (globalData.length === 0 && topAccountData.length === 0 && topPositionData.length === 0) {
            console.warn('⚠️ No data available for comparison chart');
            return;
        }

        // Small delay to ensure destroy is complete
        setTimeout(() => {
            try {
                // Use first available dataset as base for timestamps
                const baseData = globalData.length > 0 ? globalData : 
                                topAccountData.length > 0 ? topAccountData : 
                                topPositionData;

                const sorted = [...baseData].sort((a, b) => (a.time || a.ts) - (b.time || b.ts));
                // Keep full timestamp (milliseconds) for accurate time display in tooltip
                const labels = sorted.map(d => d.time || d.ts);

                const datasets = [];

                // Top Account Ratio (from Internal API: /top-accounts)
                if (topAccountData.length > 0) {
                    const topAccountSorted = [...topAccountData].sort((a, b) => (a.time || a.ts) - (b.time || b.ts));
                    const topAccountValues = topAccountSorted.map(d => 
                        parseFloat(d.top_account_long_short_ratio || d.ls_ratio_accounts || 0)
                    );

                    datasets.push({
                        label: 'Top Account Ratio',
                        data: topAccountValues,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointRadius: 2,
                        pointHoverRadius: 5
                    });
                }

                // Top Position Ratio
                if (topPositionData.length > 0) {
                    const topPositionSorted = [...topPositionData].sort((a, b) => (a.time || a.ts) - (b.time || b.ts));
                    const topPositionValues = topPositionSorted.map(d => 
                        parseFloat(d.top_position_long_short_ratio || d.ls_ratio_positions || 0)
                    );

                    datasets.push({
                        label: 'Top Position Ratio',
                        data: topPositionValues,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointRadius: 2,
                        pointHoverRadius: 5
                    });
                }

                const chartOptions = this.getComparisonChartOptions();

                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: chartOptions
                });

                console.log('✅ Comparison chart rendered:', this.canvasId);
            } catch (error) {
                console.error('❌ Error rendering comparison chart:', error);
                this.chart = null;
            }
        }, 50);
    }

    /**
     * Render net position chart
     */
    renderNetPositionChart(netPositionData) {
        this.destroy();

        const canvas = document.getElementById(this.canvasId);
        if (!canvas) {
            console.warn('⚠️ Canvas element not found:', this.canvasId);
            return;
        }

        const ctx = canvas.getContext('2d');
        if (!ctx) {
            console.warn('⚠️ Cannot get 2D context');
            return;
        }

        if (!netPositionData || netPositionData.length === 0) {
            console.warn('⚠️ No net position data available');
            return;
        }

        // Small delay to ensure destroy is complete
        setTimeout(() => {
            try {
                const sorted = [...netPositionData].sort((a, b) => (a.time || a.ts) - (b.time || b.ts));
                // Keep full timestamp (milliseconds) for accurate time display in tooltip
                const labels = sorted.map(d => d.time || d.ts);

                // Extract net long/short changes (support multiple field names)
                const netLongChanges = sorted.map(d => 
                    parseFloat(d.net_long_change || d.longNetChange || d.long_net_change || d.netLong || 0)
                );
                
                const netShortChanges = sorted.map(d => 
                    parseFloat(d.net_short_change || d.shortNetChange || d.short_net_change || d.netShort || 0)
                );

                const datasets = [
                    {
                        label: 'Net Long Flow',
                        data: netLongChanges,
                        borderColor: 'rgba(34, 197, 94, 1)',
                        backgroundColor: 'rgba(34, 197, 94, 0.2)',
                        borderWidth: 2,
                        fill: 'origin',
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Net Short Flow',
                        data: netShortChanges,
                        borderColor: 'rgba(239, 68, 68, 1)',
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        borderWidth: 2,
                        fill: 'origin',
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Zero Line',
                        data: Array(netLongChanges.length).fill(0),
                        borderColor: 'rgba(156, 163, 175, 0.5)',
                        backgroundColor: 'transparent',
                        borderWidth: 1,
                        borderDash: [5, 5],
                        fill: false,
                        pointRadius: 0
                    }
                ];

                const chartOptions = this.getNetPositionChartOptions();

                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: chartOptions
                });

                console.log('✅ Net position chart rendered:', this.canvasId);
            } catch (error) {
                console.error('❌ Error rendering net position chart:', error);
                this.chart = null;
            }
        }, 50);
    }

    /**
     * Get chart options for main chart
     */
    getMainChartOptions(hasPriceOverlay = false) {
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
                    display: false  // Hide legend for cleaner look
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.98)',
                    titleColor: '#1e293b',
                    bodyColor: '#334155',
                    borderColor: 'rgba(59, 130, 246, 0.3)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        title: (items) => {
                            try {
                                // Get raw label value (should be timestamp in milliseconds)
                                const rawLabel = items[0].label;
                                console.log('📅 [Main Chart Tooltip] Raw label:', rawLabel, 'Type:', typeof rawLabel);
                                
                                // Parse as timestamp (handle both number and string)
                                const timestamp = typeof rawLabel === 'number' ? rawLabel : parseInt(rawLabel, 10);
                                
                                if (isNaN(timestamp)) {
                                    console.error('❌ Invalid timestamp:', rawLabel);
                                    return 'Invalid Date';
                                }
                                
                                const date = new Date(timestamp);
                                console.log('📅 Parsed date:', date.toISOString());
                                
                                // Format date and time in Jakarta timezone (UTC+7)
                                const dateStr = date.toLocaleDateString('en-US', {
                                    weekday: 'short',
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric',
                                    timeZone: 'Asia/Jakarta'
                                });
                                const timeStr = date.toLocaleTimeString('en-US', {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    hour12: false,
                                    timeZone: 'Asia/Jakarta'
                                });
                                return `${dateStr}, ${timeStr} WIB`;
                            } catch (error) {
                                console.error('❌ Error formatting tooltip title:', error);
                                return 'Error';
                            }
                        },
                        label: (context) => {
                            const datasetLabel = context.dataset.label;
                            const value = context.parsed.y;
                            if (datasetLabel.includes('Price')) {
                                return `  ${datasetLabel}: ${LongShortRatioUtils.formatPriceUSD(value)}`;
                            }
                            return `  ${datasetLabel}: ${LongShortRatioUtils.formatRatio(value)}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#64748b',
                        font: { size: 10 },
                        maxRotation: 45,
                        minRotation: 45,
                        callback: function (value, index) {
                            const totalLabels = this.chart.data.labels.length;
                            const showEvery = Math.max(1, Math.ceil(totalLabels / 10));
                            if (index % showEvery === 0) {
                                const date = new Date(this.chart.data.labels[index]);
                                return date.toLocaleDateString('en-US', {
                                    month: 'short',
                                    day: 'numeric'
                                });
                            }
                            return '';
                        }
                    },
                    grid: {
                        display: true,
                        color: 'rgba(148, 163, 184, 0.15)',
                        drawBorder: false
                    }
                },
                y: {
                    beginAtZero: false,
                    title: {
                        display: true,
                        text: 'Long/Short Ratio',
                        color: '#475569',
                        font: { size: 11, weight: '500' }
                    },
                    ticks: {
                        color: '#64748b',
                        font: { size: 11 },
                        callback: (value) => LongShortRatioUtils.formatRatio(value)
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.15)',
                        drawBorder: false
                    }
                },
                ...(hasPriceOverlay && {
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'BTC Price (USD)',
                            color: '#475569',
                            font: { size: 11, weight: '500' }
                        },
                        ticks: {
                            color: '#f59e0b',
                            font: { size: 11 },
                            callback: (value) => LongShortRatioUtils.formatPriceUSD(value)
                        },
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                })
            }
        };
    }

    /**
     * Get chart options for comparison chart
     */
    getComparisonChartOptions() {
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
                    display: true,  // Keep legend for comparison chart to distinguish lines
                    position: 'top',
                    labels: {
                        color: '#64748b',
                        font: { size: 11, weight: '500' },
                        boxWidth: 12,
                        boxHeight: 12,
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.98)',
                    titleColor: '#1e293b',
                    bodyColor: '#334155',
                    borderColor: 'rgba(59, 130, 246, 0.3)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        title: (items) => {
                            try {
                                // Get raw label value (should be timestamp in milliseconds)
                                const rawLabel = items[0].label;
                                console.log('📅 [Comparison Chart Tooltip] Raw label:', rawLabel, 'Type:', typeof rawLabel);
                                
                                // Parse as timestamp (handle both number and string)
                                const timestamp = typeof rawLabel === 'number' ? rawLabel : parseInt(rawLabel, 10);
                                
                                if (isNaN(timestamp)) {
                                    console.error('❌ Invalid timestamp:', rawLabel);
                                    return 'Invalid Date';
                                }
                                
                                const date = new Date(timestamp);
                                console.log('📅 Parsed date:', date.toISOString());
                                
                                // Format date and time in Jakarta timezone (UTC+7)
                                const dateStr = date.toLocaleDateString('en-US', {
                                    weekday: 'short',
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric',
                                    timeZone: 'Asia/Jakarta'
                                });
                                const timeStr = date.toLocaleTimeString('en-US', {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    hour12: false,
                                    timeZone: 'Asia/Jakarta'
                                });
                                return `${dateStr}, ${timeStr} WIB`;
                            } catch (error) {
                                console.error('❌ Error formatting tooltip title:', error);
                                return 'Error';
                            }
                        },
                        label: (context) => {
                            const datasetLabel = context.dataset.label;
                            const value = context.parsed.y;
                            return `  ${datasetLabel}: ${LongShortRatioUtils.formatRatio(value)}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#64748b',
                        font: { size: 11 },
                        maxRotation: 0,
                        minRotation: 0,
                        callback: function (value, index) {
                            const totalLabels = this.chart.data.labels.length;
                            const showEvery = Math.max(1, Math.ceil(totalLabels / 8));
                            if (index % showEvery === 0) {
                                const date = new Date(this.chart.data.labels[index]);
                                return date.toLocaleDateString('en-US', {
                                    month: 'short',
                                    day: 'numeric'
                                });
                            }
                            return '';
                        }
                    },
                    grid: {
                        display: true,
                        color: 'rgba(148, 163, 184, 0.15)',
                        drawBorder: false
                    }
                },
                y: {
                    beginAtZero: false,
                    title: {
                        display: true,
                        text: 'Long/Short Ratio',
                        color: '#475569',
                        font: { size: 11, weight: '500' }
                    },
                    ticks: {
                        color: '#64748b',
                        font: { size: 11 },
                        callback: (value) => LongShortRatioUtils.formatRatio(value)
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.15)',
                        drawBorder: false
                    }
                }
            }
        };
    }

    /**
     * Get chart options for net position chart
     */
    getNetPositionChartOptions() {
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
                    display: true,
                    position: 'top',
                    labels: {
                        color: '#64748b',
                        font: { size: 11, weight: '500' },
                        boxWidth: 12,
                        boxHeight: 12,
                        padding: 15,
                        usePointStyle: true,
                        filter: function(item) {
                            return !item.text.includes('Zero Line');
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.98)',
                    titleColor: '#1e293b',
                    bodyColor: '#334155',
                    borderColor: 'rgba(59, 130, 246, 0.3)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
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
                            if (datasetLabel.includes('Flow')) {
                                const sign = value >= 0 ? '+' : '';
                                return `  ${datasetLabel}: ${sign}${value.toFixed(2)}%`;
                            }
                            return `  ${datasetLabel}: ${value}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#64748b',
                        font: { size: 11 },
                        maxRotation: 0,
                        minRotation: 0,
                        callback: function (value, index) {
                            const totalLabels = this.chart.data.labels.length;
                            const showEvery = Math.max(1, Math.ceil(totalLabels / 12));
                            if (index % showEvery === 0) {
                                const date = new Date(this.chart.data.labels[index]);
                                return date.toLocaleDateString('en-US', {
                                    month: 'short',
                                    day: 'numeric'
                                });
                            }
                            return '';
                        }
                    },
                    grid: {
                        display: true,
                        color: 'rgba(148, 163, 184, 0.15)',
                        drawBorder: false
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Net Position Change (%)',
                        color: '#475569',
                        font: { size: 11, weight: '500' }
                    },
                    ticks: {
                        color: '#64748b',
                        font: { size: 11 },
                        callback: (value) => {
                            const sign = value >= 0 ? '+' : '';
                            return `${sign}${value.toFixed(1)}%`;
                        }
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.15)',
                        drawBorder: false
                    }
                }
            }
        };
    }
}

