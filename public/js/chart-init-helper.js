/**
 * Chart Initialization Helper
 * Provides robust chart initialization with retry logic
 */

window.ChartInitHelper = {
    // Initialize chart with retry logic
    initChartWithRetry(canvasRef, chartConfig, setChartCallback, maxAttempts = 10) {
        let attempts = 0;
        
        const tryInit = () => {
            attempts++;
            
            if (!canvasRef) {
                if (attempts < maxAttempts) {
                    setTimeout(tryInit, 200);
                    return;
                } else {
                    console.warn('Chart canvas not found after', maxAttempts, 'attempts');
                    return;
                }
            }
            
            // Check if canvas is visible and has dimensions
            const rect = canvasRef.getBoundingClientRect();
            if (rect.width === 0 || rect.height === 0) {
                if (attempts < maxAttempts) {
                    setTimeout(tryInit, 200);
                    return;
                } else {
                    console.warn('Chart canvas has no dimensions after', maxAttempts, 'attempts');
                    return;
                }
            }
            
            const ctx = canvasRef.getContext('2d');
            
            // Destroy existing chart if any
            if (canvasRef._chartInstance) {
                canvasRef._chartInstance.destroy();
                canvasRef._chartInstance = null;
            }
            
            // Create chart outside Alpine reactivity
            queueMicrotask(() => {
                try {
                    const chartInstance = new Chart(ctx, chartConfig);
                    setChartCallback(chartInstance);
                    console.log('✅ Chart initialized successfully');
                } catch (error) {
                    console.error('❌ Chart initialization failed:', error);
                }
            });
        };
        
        // Start trying with a small delay
        setTimeout(tryInit, 100);
    },
    
    // Update chart data safely
    updateChartData(chart, labels, datasets) {
        if (!chart) return;
        
        queueMicrotask(() => {
            try {
                chart.data.labels = labels;
                datasets.forEach((data, index) => {
                    if (chart.data.datasets[index]) {
                        chart.data.datasets[index].data = data;
                    }
                });
                chart.update('none');
            } catch (error) {
                console.error('❌ Chart update failed:', error);
            }
        });
    }
};