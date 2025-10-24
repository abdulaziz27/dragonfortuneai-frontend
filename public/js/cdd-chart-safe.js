/**
 * Safe CDD Chart Renderer
 * Fixes Chart.js errors by providing safer configuration
 */

function renderSafeCDDChart(controller) {
    const canvas = document.getElementById('cddMainChart');
    if (!canvas) {
        console.warn('⚠️ Chart canvas not found');
        return;
    }

    const ctx = canvas.getContext('2d');

    // Destroy existing chart
    if (controller.mainChart) {
        controller.mainChart.destroy();
    }

    // Prepare data safely
    const sorted = [...(controller.rawData || [])].sort((a, b) =>
        new Date(a.date) - new Date(b.date)
    );

    if (sorted.length === 0) {
        console.warn('⚠️ No data available for chart');
        return;
    }

    const labels = sorted.map(d => d.date);
    const values = sorted.map(d => parseFloat(d.value) || 0);

    // Calculate average safely
    const avgValue = controller.avgCDD || (values.reduce((a, b) => a + b, 0) / values.length);

    // Prepare datasets
    const datasets = [];

    // CDD Dataset
    if (controller.chartType === 'bar') {
        const barColors = values.map(v =>
            v > avgValue ? 'rgba(239, 68, 68, 0.7)' : 'rgba(34, 197, 94, 0.7)'
        );

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
        // Line chart
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

    // Price overlay (if available)
    if (controller.priceData && controller.priceData.length > 0) {
        const priceMap = new Map(controller.priceData.map(p => [p.date, p.price]));
        const alignedPrices = labels.map(date => priceMap.get(date) || null);

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

    // Safe chart configuration
    const config = {
        type: controller.chartType || 'line',
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
                    display: controller.priceData && controller.priceData.length > 0,
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
                                const formattedValue = controller.formatCDD ? controller.formatCDD(value) : value.toFixed(2);
                                
                                // Safe comparison with average
                                if (avgValue > 0) {
                                    const vsAvg = ((value - avgValue) / avgValue * 100).toFixed(1);
                                    const trend = value > avgValue ? '🔴 Above Avg' : '🟢 Below Avg';
                                    return [
                                        `  ${datasetLabel}: ${formattedValue}`,
                                        `  ${trend} (${vsAvg > 0 ? '+' : ''}${vsAvg}%)`
                                    ];
                                } else {
                                    return `  ${datasetLabel}: ${formattedValue}`;
                                }
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
                        callback: function (value, index) {
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
                        callback: (value) => controller.formatCDD ? controller.formatCDD(value) : value.toFixed(2)
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.08)',
                        drawBorder: false
                    }
                },
                y1: {
                    type: controller.scaleType || 'linear',
                    position: 'right',
                    display: controller.priceData && controller.priceData.length > 0,
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
    };

    // Create chart with error handling
    try {
        controller.mainChart = new Chart(ctx, config);
        console.log('✅ Safe CDD chart rendered successfully');
    } catch (error) {
        console.error('❌ Error creating chart:', error);
        
        // Fallback: simple chart without advanced features
        const fallbackConfig = {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Exchange Inflow CDD',
                    data: values,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        };
        
        try {
            controller.mainChart = new Chart(ctx, fallbackConfig);
            console.log('✅ Fallback chart rendered');
        } catch (fallbackError) {
            console.error('❌ Fallback chart also failed:', fallbackError);
        }
    }
}

// Make available globally
if (typeof window !== 'undefined') {
    window.renderSafeCDDChart = renderSafeCDDChart;
}

console.log('✅ Safe CDD chart renderer loaded');