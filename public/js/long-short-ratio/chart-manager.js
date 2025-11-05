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
     * Render chart with ratio line + long/short percentages stacked bar
     * Used for both Accounts and Positions charts
     */
    renderRatioDistributionChart(data, chartType = 'line', isPositions = false) {
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
            console.warn('⚠️ No data available for chart');
            return;
        }

        try {
                const sorted = [...data].sort((a, b) => (a.time || a.ts) - (b.time || b.ts));
                const labels = sorted.map(d => d.time || d.ts);
                
                // Extract ratio and percentages (support both accounts and positions)
                const ratioField = isPositions ? 'ls_ratio_positions' : 'ls_ratio_accounts';
                const longPercentField = isPositions ? 'long_positions_percent' : 'long_accounts';
                const shortPercentField = isPositions ? 'short_positions_percent' : 'short_accounts';
                
                const ratioValues = sorted.map(d => {
                    return parseFloat(d[ratioField] || 0);
                });
                
                const longPercentages = sorted.map(d => {
                    return parseFloat(d[longPercentField] || 0);
                });
                
                const shortPercentages = sorted.map(d => {
                    return parseFloat(d[shortPercentField] || 0);
                });
                
                const chartLabel = isPositions ? 'Top Positions' : 'Top Accounts';
                
                console.log(`📊 ${chartLabel} Chart Rendering:`, {
                    dataPoints: ratioValues.length,
                    first: ratioValues[0],
                    last: ratioValues[ratioValues.length - 1],
                    longAvg: longPercentages.reduce((a, b) => a + b, 0) / longPercentages.length,
                    shortAvg: shortPercentages.reduce((a, b) => a + b, 0) / shortPercentages.length
                });

                // Prepare datasets: Ratio line (Y1) + Long/Short percentages stacked bar (Y2)
                let datasets = [];
                
                // 1. Ratio Line (primary Y-axis - left)
                if (chartType === 'line') {
                    datasets.push({
                        label: `${chartLabel} Ratio`,
                        data: ratioValues,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        yAxisID: 'y'
                    });
                } else if (chartType === 'area') {
                    datasets.push({
                        label: `${chartLabel} Ratio`,
                        data: ratioValues,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        yAxisID: 'y'
                    });
                } else {
                    const barColors = ratioValues.map(value => 
                        value >= 1 ? 'rgba(16, 185, 129, 0.8)' : 'rgba(239, 68, 68, 0.8)'
                    );
                    const borderColors = ratioValues.map(value => 
                        value >= 1 ? '#10b981' : '#ef4444'
                    );
                    
                    datasets.push({
                        label: `${chartLabel} Ratio`,
                        data: ratioValues,
                        backgroundColor: barColors,
                        borderColor: borderColors,
                        borderWidth: 1.5,
                        borderRadius: 4,
                        yAxisID: 'y'
                    });
                }
                
                // 2. Long/Short Percentages Stacked Bar (secondary Y-axis - right)
                const longLabel = isPositions ? 'Long Positions' : 'Long Accounts';
                const shortLabel = isPositions ? 'Short Positions' : 'Short Accounts';
                
                datasets.push({
                    label: longLabel,
                    type: 'bar',
                    data: longPercentages,
                    backgroundColor: 'rgba(16, 185, 129, 0.3)',
                    borderColor: 'rgba(16, 185, 129, 0.5)',
                    borderWidth: 0,
                    yAxisID: 'y1',
                    order: 2,
                    stack: 'percentages'
                });
                
                datasets.push({
                    label: shortLabel,
                    type: 'bar',
                    data: shortPercentages,
                    backgroundColor: 'rgba(239, 68, 68, 0.3)',
                    borderColor: 'rgba(239, 68, 68, 0.5)',
                    borderWidth: 0,
                    yAxisID: 'y1',
                    order: 2,
                    stack: 'percentages'
                });

                const chartOptions = this.getMainChartOptions(true); // Enable dual-axis for percentages

                // Defensive cleanup: if Chart.js still tracks a chart on this canvas, destroy it first
                try {
                    const existing = Chart.getChart ? Chart.getChart(canvas) : null;
                    if (existing && typeof existing.destroy === 'function') {
                        existing.destroy();
                    }
                } catch (e) {
                    console.warn('⚠️ Pre-destroy existing chart failed (safe to ignore):', e);
                }

                this.chart = new Chart(ctx, {
                    type: chartType === 'bar' ? 'bar' : 'line',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: chartOptions
                });

                console.log(`✅ ${chartLabel} chart rendered:`, this.canvasId);
            } catch (error) {
                console.error('❌ Error rendering chart:', error);
                this.chart = null;
            }
    }

    /**
     * Update existing ratio + distribution chart data in-place (no re-render)
     * Applies to main (accounts) and positions charts. Avoids flicker during auto-refresh.
     */
    updateRatioDistributionData(data, isPositions = false) {
        if (!this.chart || !this.chart.data || !Array.isArray(this.chart.data.datasets)) {
            return;
        }

        if (!data || data.length === 0) {
            return;
        }

        try {
            // Data is already cloned in controller before being passed here
            const sorted = [...data].sort((a, b) => (a.time || a.ts) - (b.time || b.ts));
            
            const ratioField = isPositions ? 'ls_ratio_positions' : 'ls_ratio_accounts';
            const longPercentField = isPositions ? 'long_positions_percent' : 'long_accounts';
            const shortPercentField = isPositions ? 'short_positions_percent' : 'short_accounts';

            // Extract primitive values (numbers/timestamps)
            const labels = sorted.map(d => d.time || d.ts);
            const ratioValues = sorted.map(d => parseFloat(d[ratioField] || 0));
            const longPercentages = sorted.map(d => parseFloat(d[longPercentField] || 0));
            const shortPercentages = sorted.map(d => parseFloat(d[shortPercentField] || 0));

            // Update labels (plain array of primitives)
            this.chart.data.labels = labels;

            // Expected dataset order: [ratio line/bar, long %, short %]
            if (this.chart.data.datasets[0]) {
                this.chart.data.datasets[0].data = ratioValues;
            }
            if (this.chart.data.datasets[1]) {
                this.chart.data.datasets[1].data = longPercentages;
            }
            if (this.chart.data.datasets[2]) {
                this.chart.data.datasets[2].data = shortPercentages;
            }

            // Smooth update without animation
            this.chart.update('none');
            
            const chartLabel = isPositions ? 'Positions' : 'Accounts';
            console.log(`✅ ${chartLabel} chart data updated after refresh (in-place):`, {
                canvas: this.canvasId,
                points: labels.length,
                lastRatio: ratioValues[ratioValues.length - 1]
            });
        } catch (error) {
            console.warn('⚠️ Failed to update ratio distribution chart, falling back to re-render:', error);
            // If in-place update fails, fall back to full render to keep chart consistent
            this.renderRatioDistributionChart(data, 'line', isPositions);
        }
    }

    /**
     * Render main chart (Long/Short Ratio) - Accounts
     * @deprecated Use renderRatioDistributionChart(data, chartType, false) instead
     */
    renderMainChart(data, chartType = 'line', priceData = []) {
        // Forward to new method for backward compatibility
        this.renderRatioDistributionChart(data, chartType, false);
    }

    /**
     * Render positions chart (Long/Short Ratio) - Positions
     */
    renderPositionsChart(data, chartType = 'line') {
        this.renderRatioDistributionChart(data, chartType, true);
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

            // Defensive cleanup: if Chart.js still tracks a chart on this canvas, destroy it first
            try {
                const existing = Chart.getChart ? Chart.getChart(canvas) : null;
                if (existing && typeof existing.destroy === 'function') {
                    existing.destroy();
                }
            } catch (e) {
                console.warn('⚠️ Pre-destroy existing chart failed (safe to ignore):', e);
            }

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
    }

    /**
     * Update existing comparison chart data in-place (no re-render)
     */
    updateComparisonChartData(globalData = [], topAccountData = [], topPositionData = []) {
        if (!this.chart || !this.chart.data || !Array.isArray(this.chart.data.datasets)) {
            return;
        }

        if (globalData.length === 0 && topAccountData.length === 0 && topPositionData.length === 0) {
            return;
        }

        try {
            // Data is already cloned in controller before being passed here
            // Use first available dataset as base for timestamps
            const baseData = topAccountData.length > 0 ? topAccountData : topPositionData;
            const sorted = [...baseData].sort((a, b) => (a.time || a.ts) - (b.time || b.ts));
            const labels = sorted.map(d => d.time || d.ts);

            // Update labels
            this.chart.data.labels = labels;

            // Expected dataset order: [Top Account Ratio, Top Position Ratio]
            let datasetIndex = 0;

            if (topAccountData.length > 0) {
                const topAccountSorted = [...topAccountData].sort((a, b) => (a.time || a.ts) - (b.time || b.ts));
                const topAccountValues = topAccountSorted.map(d => 
                    parseFloat(d.top_account_long_short_ratio || d.ls_ratio_accounts || 0)
                );
                if (this.chart.data.datasets[datasetIndex]) {
                    this.chart.data.datasets[datasetIndex].data = topAccountValues;
                }
                datasetIndex++;
            }

            if (topPositionData.length > 0) {
                const topPositionSorted = [...topPositionData].sort((a, b) => (a.time || a.ts) - (b.time || b.ts));
                const topPositionValues = topPositionSorted.map(d => 
                    parseFloat(d.top_position_long_short_ratio || d.ls_ratio_positions || 0)
                );
                if (this.chart.data.datasets[datasetIndex]) {
                    this.chart.data.datasets[datasetIndex].data = topPositionValues;
                }
            }

            // Smooth update without animation
            this.chart.update('none');
            
            console.log('✅ Comparison chart data updated after refresh (in-place):', {
                canvas: this.canvasId,
                points: labels.length
            });
        } catch (error) {
            console.warn('⚠️ Failed to update comparison chart, falling back to re-render:', error);
            this.renderComparisonChart(globalData, topAccountData, topPositionData);
        }
    }



    /**
     * Get chart options for main chart
     */
    getMainChartOptions(hasPercentages = false) {
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
                            
                            // Format percentages for Long/Short (both Accounts and Positions)
                            if (datasetLabel.includes('Long Accounts') || datasetLabel.includes('Short Accounts') ||
                                datasetLabel.includes('Long Positions') || datasetLabel.includes('Short Positions')) {
                                return `  ${datasetLabel}: ${value.toFixed(2)}%`;
                            }
                            
                            // Format ratio for Long/Short Ratio line
                            if (datasetLabel.includes('Ratio')) {
                                return `  ${datasetLabel}: ${LongShortRatioUtils.formatRatio(value)}`;
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
                        font: { size: 10 },
                        maxRotation: 45,
                        minRotation: 45,
                        maxTicksLimit: undefined, // Show all ticks
                        callback: function (value, index) {
                            const labels = this.chart.data.labels;
                            const totalLabels = labels.length;
                            
                            if (totalLabels === 0 || index >= totalLabels) return '';
                            
                            const date = new Date(labels[index]);
                            
                            // Detect if hourly data (check time span between first and last)
                            const firstDate = new Date(labels[0]);
                            const lastDate = new Date(labels[totalLabels - 1]);
                            const timeSpanDays = (lastDate - firstDate) / (1000 * 60 * 60 * 24);
                            const isHourlyData = timeSpanDays < 7; // Less than 7 days = show time
                            
                            if (isHourlyData) {
                                // Hourly view - show date + time: yyyy-mm-dd HH:mm
                                const year = date.getFullYear();
                                const month = String(date.getMonth() + 1).padStart(2, '0');
                                const day = String(date.getDate());
                                const hours = String(date.getHours()).padStart(2, '0');
                                const minutes = String(date.getMinutes()).padStart(2, '0');
                                return `${year}-${month}-${day} ${hours}:${minutes}`;
                            } else {
                                // Daily view - show full date: MMM dd, yyyy
                                return date.toLocaleDateString('en-US', {
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric'
                                });
                            }
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
                    grace: '15%', // Add 15% padding to top and bottom
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
                ...(hasPercentages && {
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Long/Short %',
                            color: '#475569',
                            font: { size: 11, weight: '500' }
                        },
                        ticks: {
                            color: '#64748b',
                            font: { size: 11 },
                            callback: (value) => `${value.toFixed(1)}%`,
                            max: 100,
                            min: 0
                        },
                        grid: {
                            display: false, // Hide grid for cleaner look
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
                        maxRotation: 45,
                        minRotation: 45,
                        maxTicksLimit: undefined, // Show all ticks
                        callback: function (value, index) {
                            const labels = this.chart.data.labels;
                            const totalLabels = labels.length;
                            
                            if (totalLabels === 0 || index >= totalLabels) return '';
                            
                            const date = new Date(labels[index]);
                            
                            // Detect if hourly data (check time span between first and last)
                            const firstDate = new Date(labels[0]);
                            const lastDate = new Date(labels[totalLabels - 1]);
                            const timeSpanDays = (lastDate - firstDate) / (1000 * 60 * 60 * 24);
                            const isHourlyData = timeSpanDays < 7; // Less than 7 days = show time
                            
                            if (isHourlyData) {
                                // Hourly view - show date + time: yyyy-mm-dd HH:mm
                                const year = date.getFullYear();
                                const month = String(date.getMonth() + 1).padStart(2, '0');
                                const day = String(date.getDate());
                                const hours = String(date.getHours()).padStart(2, '0');
                                const minutes = String(date.getMinutes()).padStart(2, '0');
                                return `${year}-${month}-${day} ${hours}:${minutes}`;
                            } else {
                                // Daily view - show full date: MMM dd, yyyy
                                return date.toLocaleDateString('en-US', {
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric'
                                });
                            }
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
                    grace: '15%', // Add 15% padding to top and bottom
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


}

