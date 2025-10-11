# Perp-Quarterly Spread API Integration - ENHANCED

## Overview

Implementasi API integration untuk halaman Perp-Quarterly Spread menggunakan endpoint backend yang telah disediakan dengan filter yang sesuai dokumentasi API.

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

## 🆕 ENHANCED Filter UI

Filter sekarang menggunakan parameter yang sesuai dengan dokumentasi API:

### Filter Components:

1. **Base Asset**: BTC, ETH, SOL, BNB, XRP, ADA, DOGE, MATIC, DOT, AVAX
2. **Quote Asset**: USDT, USD, BUSD
3. **Exchange**: Binance, Bybit, OKX, Bitget, Gate.io, Deribit
4. **Interval**: 5m, 15m, 1h, 4h, 1d
5. **Perp Symbol Override**: Optional input field (auto-generated if empty)

### Filter Logic:

-   **Auto-generation**: Jika `perp_symbol` kosong, otomatis generate `${base}${quote}` (contoh: BTCUSDT)
-   **Manual Override**: User dapat input manual perp symbol jika diperlukan
-   **Real-time Update**: Setiap perubahan filter langsung trigger API call

## Components Updated

### 1. Analytics Card (`analytics-card.blade.php`)

-   ✅ Menggunakan `/api/perp-quarterly/analytics`
-   ✅ Menampilkan current spread, average, range, market structure
-   ✅ Menampilkan contract symbols (perp & quarterly)
-   ✅ Menampilkan insights dari API
-   ✅ **NEW**: Support untuk quote parameter dan perp_symbol override

### 2. Spread History Chart (`spread-history-chart.blade.php`)

-   ✅ Menggunakan `/api/perp-quarterly/history`
-   ✅ Chart.js dengan line chart untuk spread movement
-   ✅ Color coding: green (contango), red (backwardation)
-   ✅ Zero line reference
-   ✅ Performance optimizations (limited data points, no animations)
-   ✅ **NEW**: Dynamic parameter handling dengan quote dan perp_symbol

### 3. Insights Panel (`insights-panel.blade.php`)

-   ✅ Menggunakan `/api/perp-quarterly/analytics`
-   ✅ Menampilkan arbitrage opportunities
-   ✅ Key metrics display
-   ✅ Market structure indicators
-   ✅ **NEW**: Enhanced filter support

### 4. Spread Table (`spread-table.blade.php`)

-   ✅ Menggunakan `/api/perp-quarterly/history`
-   ✅ Tabular display of recent spread data
-   ✅ Contract symbols, spread values, market structure
-   ✅ **NEW**: Flexible parameter configuration

### 5. Global Controller (`perp-quarterly-controller.js`)

-   ✅ Updated default interval to `5m`
-   ✅ Added `perp_symbol` parameter to API calls
-   ✅ Enhanced logging for debugging
-   ✅ **NEW**: Added `globalQuote` and `globalPerpSymbol` state
-   ✅ **NEW**: New event handlers: `updateQuote()`, `updatePerpSymbol()`
-   ✅ **NEW**: Enhanced event broadcasting with all parameters

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

### 4. Dynamic Parameter Handling

```javascript
// Auto-generate perp symbol if not provided
const actualPerpSymbol = this.perpSymbol || `${this.symbol}${this.quote}`;

const params = new URLSearchParams({
    exchange: this.exchange,
    base: this.symbol,
    quote: this.quote,
    interval: this.interval,
    limit: "2000",
    perp_symbol: actualPerpSymbol,
});
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
📊 Base: BTC
💰 Quote: USDT
🏦 Exchange: Binance
⏱️ Interval: 5m
🔧 Perp Symbol Override: auto-generated
🔄 Loading Perp-Quarterly Overview: {base: "BTC", quote: "USDT", exchange: "Binance", interval: "5m", perpSymbol: "BTCUSDT"}
📡 Fetching Perp-Quarterly Analytics: /api/perp-quarterly/analytics?exchange=Binance&base=BTC&quote=USDT&interval=5m&limit=2000&perp_symbol=BTCUSDT
📡 Fetching Perp-Quarterly History: /api/perp-quarterly/history?exchange=Binance&base=BTC&quote=USDT&interval=5m&limit=2000&perp_symbol=BTCUSDT
✅ Analytics loaded: {spread_bps: {...}, trend: {...}, ...}
✅ Spread history loaded: 500 points
✅ Spread history chart initialized
```

## Filter Usage Examples

### 1. Basic Usage (Auto-generated perp symbol)

-   Base: BTC
-   Quote: USDT
-   Exchange: Binance
-   Interval: 5m
-   Perp Symbol: (empty - auto generates BTCUSDT)

### 2. Manual Override

-   Base: BTC
-   Quote: USDT
-   Exchange: Binance
-   Interval: 5m
-   Perp Symbol: BTCUSDT_PERP (manual override)

### 3. Different Quote Asset

-   Base: ETH
-   Quote: USD
-   Exchange: Bybit
-   Interval: 1h
-   Perp Symbol: (empty - auto generates ETHUSD)

## Files Modified

1. `resources/views/derivatives/perp-quarterly-spread.blade.php` - **NEW**: Enhanced filter UI
2. `public/js/perp-quarterly-controller.js` - **NEW**: Added quote & perp symbol support
3. `resources/views/components/perp-quarterly/analytics-card.blade.php` - **NEW**: Dynamic parameters
4. `resources/views/components/perp-quarterly/spread-history-chart.blade.php` - **NEW**: Enhanced filtering
5. `resources/views/components/perp-quarterly/insights-panel.blade.php` - **NEW**: Parameter updates
6. `resources/views/components/perp-quarterly/spread-table.blade.php` - **NEW**: Filter integration

## Status

✅ **COMPLETED** - All components now use proper API parameters with dynamic filtering according to API documentation. Filter UI updated to match API requirements: exchange, base, quote, interval, perp_symbol.
