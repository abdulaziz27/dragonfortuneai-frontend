# Perp-Quarterly Spread API Integration

## Overview

Implementasi API integration untuk halaman Perp-Quarterly Spread menggunakan endpoint backend yang telah disediakan.

## API Endpoints Used

### 1. Analytics Endpoint

-   **URL**: `/api/perp-quarterly/analytics`
-   **Method**: GET
-   **Purpose**: Mendapatkan spread analytics, trend analysis, dan insights

### 2. History Endpoint

-   **URL**: `/api/perp-quarterly/history`
-   **Method**: GET
-   **Purpose**: Mendapatkan historical spread data untuk chart dan table

## Parameters Used

Berdasarkan dokumentasi backend, parameter yang digunakan:

```javascript
{
    exchange: 'Binance',        // Exchange name (REQUIRED)
    base: 'BTC',               // Base asset symbol (REQUIRED)
    quote: 'USDT',             // Quote asset symbol (REQUIRED)
    interval: '5m',            // Time interval (REQUIRED)
    limit: 2000,               // Max data points
    perp_symbol: 'BTCUSDT'     // Perp contract override (optional but recommended)
}
```

## Components Updated

### 1. Analytics Card (`analytics-card.blade.php`)

-   ✅ Menggunakan `/api/perp-quarterly/analytics`
-   ✅ Menampilkan current spread, average, range, market structure
-   ✅ Menampilkan contract symbols (perp & quarterly)
-   ✅ Menampilkan insights dari API

### 2. Spread History Chart (`spread-history-chart.blade.php`)

-   ✅ Menggunakan `/api/perp-quarterly/history`
-   ✅ Chart.js dengan line chart untuk spread movement
-   ✅ Color coding: green (contango), red (backwardation)
-   ✅ Zero line reference
-   ✅ Performance optimizations (limited data points, no animations)

### 3. Insights Panel (`insights-panel.blade.php`)

-   ✅ Menggunakan `/api/perp-quarterly/analytics`
-   ✅ Menampilkan arbitrage opportunities
-   ✅ Key metrics display
-   ✅ Market structure indicators

### 4. Spread Table (`spread-table.blade.php`)

-   ✅ Menggunakan `/api/perp-quarterly/history`
-   ✅ Tabular display of recent spread data
-   ✅ Contract symbols, spread values, market structure

### 5. Global Controller (`perp-quarterly-controller.js`)

-   ✅ Updated default interval to `5m`
-   ✅ Added `perp_symbol` parameter to API calls
-   ✅ Enhanced logging for debugging

## Key Fixes Applied

### 1. Chart.js Configuration

```javascript
// Removed time scale to avoid date adapter dependency
scales: {
    x: {
        // Simple index-based labels instead of time
        ticks: {
            callback: function(value, index) {
                return index % 10 === 0 ? `#${index}` : '';
            }
        }
    }
}
```

### 2. Performance Optimizations

```javascript
// Limit data points to prevent stack overflow
const limitedData = historyData.slice(-500);

// Disable animations
options: {
    animation: false;
}
```

### 3. Chart Lifecycle Management

```javascript
initChart() {
    // Destroy existing chart to prevent canvas reuse error
    if (this.chart) {
        this.chart.destroy();
        this.chart = null;
    }
    // ... create new chart
}
```

## Testing

Test dengan URL berikut di browser console:

```javascript
// Test Analytics
fetch(
    "/api/perp-quarterly/analytics?exchange=Binance&base=BTC&quote=USDT&interval=5m&perp_symbol=BTCUSDT"
)
    .then((r) => r.json())
    .then(console.log);

// Test History
fetch(
    "/api/perp-quarterly/history?exchange=Binance&base=BTC&quote=USDT&interval=5m&limit=100&perp_symbol=BTCUSDT"
)
    .then((r) => r.json())
    .then(console.log);
```

## Expected Console Output

Setelah refresh halaman, console akan menampilkan:

```
🚀 Perp-Quarterly Spread Dashboard initialized
📊 Symbol: BTC
🏦 Exchange: Binance
⏱️ Interval: 5m
🔄 Loading Perp-Quarterly Overview: {base: "BTC", exchange: "Binance", interval: "5m", perpSymbol: "BTCUSDT"}
📡 Fetching Perp-Quarterly Analytics: exchange=Binance&base=BTC&quote=USDT&interval=5m&limit=2000&perp_symbol=BTCUSDT
📡 Fetching Perp-Quarterly History: exchange=Binance&base=BTC&quote=USDT&interval=5m&limit=2000&perp_symbol=BTCUSDT
✅ Analytics loaded: {spread_bps: {...}, trend: {...}, ...}
✅ Spread history loaded: 500 points
✅ Spread history chart initialized
```

## Files Modified

1. `resources/views/components/perp-quarterly/analytics-card.blade.php`
2. `resources/views/components/perp-quarterly/spread-history-chart.blade.php`
3. `resources/views/components/perp-quarterly/insights-panel.blade.php`
4. `resources/views/components/perp-quarterly/spread-table.blade.php`
5. `resources/views/derivatives/perp-quarterly-spread.blade.php`
6. `public/js/perp-quarterly-controller.js`

## Status

✅ **COMPLETED** - All components now fetch real data from API endpoints with proper error handling and performance optimizations.
