# 🔧 Alpine.js + Chart.js Integration Guide

## 🚨 Critical Problem: Infinite Loop

### The Issue

When integrating Chart.js with Alpine.js, you'll encounter this error:

```javascript
❌ RangeError: Maximum call stack size exceeded
    at track (alpinejs.js:1785:15)
    at color.esm.js:241:3
    at index.umd.ts:50:18
```

### Root Cause Analysis

**Alpine.js Reactivity System:**

-   Alpine automatically tracks all property access in reactive data
-   When you assign `this.chart = new Chart(...)`, Alpine starts tracking the entire Chart.js object
-   Chart.js internally accesses properties for color parsing, gradient creation, etc.
-   Each property access triggers Alpine's reactivity system
-   This creates an infinite loop: **Access → Track → Access → Track → ...**

**Visual Representation:**

```
Alpine Data: { chart: ChartInstance }
    ↓
Alpine tracks: chart.data.datasets[0].backgroundColor
    ↓
Chart.js accesses: gradient.colorStops[0].color
    ↓
Alpine tracks: gradient.colorStops[0].color
    ↓
Chart.js accesses: color.rgba()
    ↓
Alpine tracks: color.rgba()
    ↓
INFINITE LOOP! 💥
```

## ✅ Solution: DOM-Based Storage

### The Fix

Store Chart.js instances in DOM elements, completely outside Alpine's reactivity system:

```javascript
// ❌ WRONG - Alpine tracks this
this.chart = new Chart(ctx, {...});

// ✅ CORRECT - Store in DOM
const chartInstance = new Chart(ctx, {...});
this.setChart(chartInstance);
```

### Implementation Pattern

```javascript
function chartComponent() {
    return {
        // Data properties
        chartData: [],
        loading: false,

        // DO NOT store chart in Alpine data
        // chart: null,  ← REMOVE THIS!

        // Unique chart ID
        chartId: "chart_" + Math.random().toString(36).substr(2, 9),

        // Helper methods for DOM storage
        getChart() {
            const canvas = document.getElementById(this.chartId);
            return canvas ? canvas._chartInstance : null;
        },

        setChart(chartInstance) {
            const canvas = document.getElementById(this.chartId);
            if (canvas) canvas._chartInstance = chartInstance;
        },

        initChart() {
            const canvas = document.getElementById(this.chartId);
            const ctx = canvas.getContext("2d");

            // Create chart OUTSIDE Alpine context
            queueMicrotask(() => {
                const chartInstance = new Chart(ctx, {
                    type: "line",
                    data: {
                        labels: [],
                        datasets: [
                            {
                                label: "Data",
                                data: [],
                                backgroundColor: "rgba(59, 130, 246, 0.1)",
                                borderColor: "rgba(59, 130, 246, 1)",
                                borderWidth: 2,
                                fill: true,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        // ... other options
                    },
                });

                // Store in DOM, NOT in Alpine
                this.setChart(chartInstance);
            });
        },

        updateChart() {
            const chart = this.getChart();
            if (!chart) return;

            // Update chart data
            chart.data.labels = this.labels;
            chart.data.datasets[0].data = this.values;

            // Update with queueMicrotask to break reactivity
            queueMicrotask(() => {
                chart.update("none");
            });
        },
    };
}
```

## 🛠️ Advanced Techniques

### 1. Breaking Reactivity with queueMicrotask()

```javascript
// Wrap Chart.js operations in queueMicrotask
queueMicrotask(() => {
    const chartInstance = new Chart(ctx, config);
    this.setChart(chartInstance);
});

// This schedules the operation after current execution
// Breaks Alpine's reactivity tracking
```

### 2. Deep Cloning Data

```javascript
// Store raw data without Alpine tracking
chart.data.datasets[0]._rawData = JSON.parse(JSON.stringify(this.chartData));

// Access in tooltips
tooltip: {
    callbacks: {
        afterLabel: function(context) {
            const rawData = context.chart.data.datasets[0]._rawData;
            return `Raw: ${rawData[context.dataIndex]?.value}`;
        }
    }
}
```

### 3. Gradient Creation Outside Callbacks

```javascript
// ❌ WRONG - Alpine tracks gradient creation
datasets: [{
    backgroundColor: function(context) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.05)');
        return gradient;
    }
}]

// ✅ CORRECT - Create gradient outside
initChart() {
    const canvas = document.getElementById(this.chartId);
    const ctx = canvas.getContext('2d');

    // Create gradient once, outside reactive context
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0.05)');

    queueMicrotask(() => {
        const chartInstance = new Chart(ctx, {
            data: {
                datasets: [{
                    backgroundColor: gradient, // Use pre-created gradient
                    // ...
                }]
            }
        });

        this.setChart(chartInstance);
    });
}
```

## 🔍 Debugging Guide

### 1. Check for Reactivity Issues

```javascript
// Add this to your component
debugReactivity() {
    console.log('Alpine data:', this.$data);
    console.log('Chart instance:', this.getChart());
    console.log('Chart data:', this.getChart()?.data);
}
```

### 2. Monitor Property Access

```javascript
// In browser console
const originalTrack = Alpine.reactive;
Alpine.reactive = function (obj) {
    console.log("Tracking:", obj);
    return originalTrack(obj);
};
```

### 3. Verify DOM Storage

```javascript
// Check if chart is stored in DOM
const canvas = document.getElementById("yourChartId");
console.log("Chart in DOM:", canvas._chartInstance);
```

## 🚫 Common Mistakes

### 1. Storing Chart in Alpine Data

```javascript
// ❌ DON'T DO THIS
return {
    chart: null, // Alpine will track this!
    // ...
};
```

### 2. Accessing Chart from Alpine Context

```javascript
// ❌ DON'T DO THIS
this.chart.update(); // Alpine tracks this access

// ✅ DO THIS
const chart = this.getChart();
chart.update();
```

### 3. Creating Gradients in Callbacks

```javascript
// ❌ DON'T DO THIS
backgroundColor: function(context) {
    return ctx.createLinearGradient(...); // Alpine tracks this
}

// ✅ DO THIS
// Create gradient outside, use reference
```

### 4. Not Using queueMicrotask

```javascript
// ❌ DON'T DO THIS
this.chart = new Chart(ctx, config); // Alpine tracks assignment

// ✅ DO THIS
queueMicrotask(() => {
    const chartInstance = new Chart(ctx, config);
    this.setChart(chartInstance);
});
```

## 🎯 Best Practices

### 1. Always Use Helper Methods

```javascript
// Centralized chart access
getChart() { /* ... */ }
setChart(instance) { /* ... */ }
```

### 2. Initialize with Retry Logic

```javascript
initChartWithRetry() {
    let attempts = 0;
    const maxAttempts = 5;

    const tryInit = () => {
        const canvas = document.getElementById(this.chartId);
        const parentWidth = canvas.parentElement.offsetWidth;

        if (parentWidth < 100 && attempts < maxAttempts) {
            setTimeout(tryInit, 500);
            return;
        }

        this.initChart();
    };

    setTimeout(tryInit, 500);
}
```

### 3. Handle Resize Events

```javascript
setupVisibilityObserver() {
    const canvas = document.getElementById(this.chartId);
    const observer = new ResizeObserver(() => {
        const chart = this.getChart();
        if (chart && canvas.offsetParent !== null) {
            chart.resize();
        }
    });

    observer.observe(canvas.parentElement);
}
```

### 4. Clean Up Resources

```javascript
destroy() {
    const chart = this.getChart();
    if (chart) {
        chart.destroy();
    }
}
```

## 📚 Additional Resources

-   [Alpine.js Reactivity Documentation](https://alpinejs.dev/advanced/reactivity)
-   [Chart.js Configuration Guide](https://www.chartjs.org/docs/latest/configuration/)
-   [MDN queueMicrotask](https://developer.mozilla.org/en-US/docs/Web/API/queueMicrotask)

---

**Remember:** The key is to keep Chart.js instances completely separate from Alpine's reactive data system. Store them in DOM elements and access them through helper methods! 🎯
