# VWAP/TWAP Quick Reference Guide

## 🚀 Quick Start

### Access Dashboard

```
URL: /spot-microstructure/vwap-twap
```

### API Endpoints

```bash
# Historical Data
GET /api/spot-microstructure/vwap?symbol=BTCUSDT&timeframe=5min&exchange=binance&limit=200

# Latest Data
GET /api/spot-microstructure/vwap/latest?symbol=BTCUSDT&timeframe=5min&exchange=binance
```

## 📊 Components

| Component           | File                                        | Purpose                     |
| ------------------- | ------------------------------------------- | --------------------------- |
| **Latest Stats**    | `components/vwap/latest-stats.blade.php`    | Current VWAP values & bands |
| **Market Insights** | `components/vwap/market-insights.blade.php` | Trading signals & bias      |
| **Bands Chart**     | `components/vwap/bands-chart.blade.php`     | VWAP visualization          |
| **History Table**   | `components/vwap/history-table.blade.php`   | Historical data table       |

## 🎯 Trading Signals

### Market Bias

| Signal                | Condition          | Action                        |
| --------------------- | ------------------ | ----------------------------- |
| 🚀 **Strong Bullish** | Price > Upper Band | Take profits or wait pullback |
| 📈 **Bullish**        | Price > VWAP       | Buy dips to VWAP              |
| ⚖️ **Neutral**        | Price ≈ VWAP       | Range trade or wait breakout  |
| 🔻 **Bearish**        | Price < VWAP       | Sell bounces to VWAP          |
| 📉 **Strong Bearish** | Price < Lower Band | Wait capitulation or bounce   |

### Band Width (Volatility)

| Width | Interpretation  | Strategy                              |
| ----- | --------------- | ------------------------------------- |
| < 1%  | Low volatility  | Breakout setup - tight range          |
| 1-2%  | Moderate        | Normal trading conditions             |
| > 2%  | High volatility | Wide range - risky for mean reversion |

## 🔄 Event System

```javascript
// Global Events
"symbol-changed"; // Symbol filter changed
"timeframe-changed"; // Timeframe filter changed
"exchange-changed"; // Exchange filter changed
"vwap-data-ready"; // Data loaded and ready
"refresh-all"; // Manual refresh triggered
```

## 🛠️ Key Functions

### Controller (`vwap-controller.js`)

```javascript
vwapController() {
  // State
  globalSymbol, globalTimeframe, globalExchange

  // Methods
  updateSymbol()      // Update symbol globally
  updateTimeframe()   // Update timeframe globally
  updateExchange()    // Update exchange globally
  loadAllData()       // Fetch all VWAP data
  refreshAll()        // Refresh all components

  // Utilities
  formatPrice()       // Format currency
  formatPercent()     // Format percentage
  formatTimestamp()   // Format date/time
  getTradingSignal()  // Get trading recommendation
}
```

### Component Template

```javascript
function vwapComponent(symbol, timeframe, exchange) {
  return {
    // State
    symbol, timeframe, exchange,
    loading: false,
    error: null,
    data: null,

    // Lifecycle
    init() {
      this.loadData();
      this.setupEventListeners();
      this.setupAutoRefresh();
    },

    // Data
    async loadData() {
      // Fetch from API
    },

    // Event Listeners
    setupEventListeners() {
      window.addEventListener('symbol-changed', ...);
      window.addEventListener('vwap-data-ready', ...);
    }
  }
}
```

## 📝 API Response Format

### Historical VWAP

```json
{
    "data": [
        {
            "exchange": "binance",
            "symbol": "BTCUSDT",
            "timeframe": "5min",
            "timestamp": "Tue, 07 Oct 2025 08:32:48 GMT",
            "vwap": 45258.6,
            "upper_band": 45445.0,
            "lower_band": 45093.9
        }
    ]
}
```

### Latest VWAP

```json
{
    "exchange": "binance",
    "symbol": "BTCUSDT",
    "timeframe": "5min",
    "timestamp": "Tue, 07 Oct 2025 08:32:48 GMT",
    "vwap": 45258.6,
    "upper_band": 45445.0,
    "lower_band": 45093.9
}
```

## 🎨 Styling Classes

```css
/* Panels */
.df-panel              /* Main panel container */

/* Badges */
/* Main panel container */

/* Badges */
/* Main panel container */

/* Badges */
/* Main panel container */

/* Badges */
.text-bg-success       /* Green badge */
.text-bg-danger        /* Red badge */
.text-bg-warning       /* Yellow badge */
.text-bg-secondary     /* Gray badge */

/* Text Colors */
.text-success          /* Green text */
.text-danger           /* Red text */
.text-warning          /* Yellow text */
.text-secondary        /* Gray text */

/* Progress Bars */
.bg-success            /* Green bar */
.bg-danger             /* Red bar */
.bg-warning; /* Yellow bar */
```

## 🐛 Common Issues

### Components Not Loading

```javascript
// Check Chart.js is loaded
if (typeof Chart === "undefined") {
    console.error("Chart.js not loaded");
}

// Check Alpine.js components
console.log("Alpine version:", Alpine.version);
```

### API Not Responding

```javascript
// Check API base URL
const baseMeta = document.querySelector('meta[name="api-base-url"]');
console.log("API Base:", baseMeta?.content);

// Test endpoint manually
fetch("/api/spot-microstructure/vwap/latest?symbol=BTCUSDT&timeframe=5min")
    .then((r) => r.json())
    .then(console.log)
    .catch(console.error);
```

### Data Not Updating

```javascript
// Force manual refresh
window.dispatchEvent(new CustomEvent("refresh-all"));

// Check auto-refresh interval
console.log(
    "Component intervals:",
    Object.keys(window).filter((k) => k.includes("interval"))
);
```

## 📊 Chart Configuration

```javascript
// Chart.js options template
{
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      callbacks: {
        label: (ctx) => formatCurrency(ctx.parsed.y)
      }
    }
  },
  scales: {
    x: {
      type: 'time',
      time: { unit: 'minute' }
    },
    y: {
      ticks: {
        callback: (val) => '$' + val.toLocaleString()
      }
    }
  }
}
```

## 🔍 Debugging Tips

### Enable Verbose Logging

```javascript
// In vwap-controller.js
console.log("📡 Fetching:", endpoint, params);
console.log("✅ Received:", data);
console.log("❌ Error:", error);
```

### Inspect Component State

```javascript
// In browser console
$el.__x.$data; // Access Alpine.js component data
```

### Monitor Events

```javascript
// Listen to all events
[
    "symbol-changed",
    "timeframe-changed",
    "exchange-changed",
    "vwap-data-ready",
].forEach((event) => {
    window.addEventListener(event, (e) => {
        console.log("Event:", event, e.detail);
    });
});
```

## 🚦 Testing Commands

### Browser Console Tests

```javascript
// Test API endpoint
fetch("/api/spot-microstructure/vwap/latest?symbol=BTCUSDT&timeframe=5min")
    .then((r) => r.json())
    .then((data) => console.log("VWAP:", data.vwap))
    .catch((err) => console.error("Error:", err));

// Trigger global refresh
window.dispatchEvent(new CustomEvent("refresh-all"));

// Change symbol programmatically
window.dispatchEvent(
    new CustomEvent("symbol-changed", {
        detail: { symbol: "ETHUSDT", timeframe: "5min", exchange: "binance" },
    })
);
```

### cURL Tests

```bash
# Test historical endpoint
curl -X GET "http://202.155.90.20:8000/api/spot-microstructure/vwap?symbol=BTCUSDT&timeframe=5min&limit=10" \
  -H "accept: application/json"

# Test latest endpoint
curl -X GET "http://202.155.90.20:8000/api/spot-microstructure/vwap/latest?symbol=BTCUSDT&timeframe=5min" \
  -H "accept: application/json"
```

## 📦 Files Checklist

```bash
✅ public/js/vwap-controller.js
✅ resources/views/spot-microstructure/vwap-twap.blade.php
✅ resources/views/components/vwap/latest-stats.blade.php
✅ resources/views/components/vwap/bands-chart.blade.php
✅ resources/views/components/vwap/market-insights.blade.php
✅ resources/views/components/vwap/history-table.blade.php
✅ routes/web.php (route already exists)
✅ docs/VWAP-TWAP-IMPLEMENTATION.md
✅ docs/VWAP-QUICK-REFERENCE.md
```

## 🎓 Trading Tips

### Intraday Trading

1. Use 5min or 15min timeframe
2. Buy pullbacks to VWAP when price is above
3. Sell bounces to VWAP when price is below
4. Take profits at opposite band

### Position Trading

1. Use 1h or 4h timeframe
2. VWAP as major support/resistance
3. Band breakouts indicate trend strength
4. Combine with volume analysis

### Risk Management

1. Stop loss below/above VWAP (opposite to position)
2. Scale out at bands
3. Avoid trading in low volatility (tight bands)
4. Wait for confirmation volume on breakouts

## 📞 Quick Help

| Issue                | Solution                              |
| -------------------- | ------------------------------------- |
| No data showing      | Check API base URL in `.env`          |
| Charts not rendering | Verify Chart.js is loaded             |
| Filters not working  | Check browser console for JS errors   |
| Slow loading         | Reduce `limit` parameter in API calls |
| Stale data           | Check auto-refresh interval (30s)     |

---

**For detailed documentation, see:** [VWAP-TWAP-IMPLEMENTATION.md](./VWAP-TWAP-IMPLEMENTATION.md)
