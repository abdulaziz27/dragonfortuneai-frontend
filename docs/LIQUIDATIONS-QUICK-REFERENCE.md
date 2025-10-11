# Liquidations Module - Quick Reference

## 🚀 Quick Start

```bash
# Navigate to liquidations dashboard
http://localhost/derivatives/liquidations

# With filters (optional)
?symbol=BTC&exchange=Binance&interval=1m
```

## 📊 Dashboard Components

| Component               | Description               | Key Features                               |
| ----------------------- | ------------------------- | ------------------------------------------ |
| **Analytics Summary**   | Overall stats & insights  | Total volume, ratio, cascades, AI insights |
| **Historical Chart**    | Time series visualization | 3 chart types, statistical summary         |
| **Liquidation Stream**  | Real-time order feed      | Auto-refresh, filters, color-coded         |
| **Heatmap**             | Intensity visualization   | Multi-exchange, stacked view               |
| **Exchange Comparison** | Volume breakdown          | Multi-timeframe tabs                       |
| **Coin List Table**     | Multi-range snapshot      | 1h/4h/12h/24h views                        |

## 🔌 API Endpoints at a Glance

```javascript
// 1. Analytics - Comprehensive metrics
GET /api/liquidations/analytics?symbol=BTCUSDT&interval=1m&limit=2000

// 2. Coin List - Multi-range per exchange
GET /api/liquidations/coin-list?symbol=BTC&limit=1000

// 3. Exchange List - Time range aggregation
GET /api/liquidations/exchange-list?symbol=BTC&range_str=1h

// 4. Orders - Real-time stream
GET /api/liquidations/orders?limit=500

// 5. Pair History - Bucketed time series
GET /api/liquidations/pair-history?symbol=BTCUSDT&interval=1m&with_price=true
```

## 💡 Trading Signals

### 🔴 Long Liquidations (Red)

-   **What:** Forced selling from long positions
-   **Impact:** Immediate sell pressure
-   **Signal:** Potential oversold bounce opportunity
-   **Risk:** Cascade events can extend downside

### 🟢 Short Liquidations (Green)

-   **What:** Forced buying from short positions
-   **Impact:** Immediate buy pressure
-   **Signal:** Short squeeze momentum
-   **Risk:** Can reverse quickly once exhausted

### ⚡ Cascade Events

-   **Threshold:** Multiple liquidations in quick succession
-   **Severity Levels:**
    -   🟢 Low (1-20): Minor chains
    -   🟠 Medium (21-50): Watch for volatility
    -   🔴 High (50+): Extreme danger

### 🎯 Long/Short Ratio

```
Ratio > 2.0   → Heavy long bias (squeeze risk high)
Ratio 1.5-2.0 → Moderate long bias
Ratio 0.5-1.5 → Balanced
Ratio < 0.5   → Heavy short bias (squeeze risk high)
```

## 🎛️ Controls & Filters

```javascript
// Global filters
Symbol:    BTC, ETH, SOL, etc.
Exchange:  All, Binance, Bybit, OKX, etc.
Interval:  1m, 5m, 15m, 1h, 4h

// Component-specific filters
Stream:    Side (Long/Short), Exchange
Heatmap:   Top 5 exchanges, last 20 buckets
Table:     Time range (1h/4h/12h/24h)
```

## 📈 Key Metrics Explained

| Metric               | Formula            | Interpretation             |
| -------------------- | ------------------ | -------------------------- |
| **Total USD**        | Long + Short       | Overall liquidation volume |
| **Long/Short Ratio** | Long ÷ Short       | Positioning bias           |
| **Cascade Count**    | Events > threshold | Volatility indicator       |
| **Peak Total**       | Max(total_usd)     | Largest liquidation spike  |
| **Avg Long/Short**   | Sum ÷ Count        | Average liquidation size   |

## 🔄 Data Refresh

| Component              | Method      | Interval            |
| ---------------------- | ----------- | ------------------- |
| **Global Overview**    | Auto        | 30 seconds          |
| **Liquidation Stream** | Auto        | 10 seconds          |
| **Charts**             | Manual      | Click "Refresh All" |
| **Tables**             | Event-based | On filter change    |

## 🎨 Color Coding

```css
#ef4444  Red      → Long liquidations (sell pressure)
#22c55e  Green    → Short liquidations (buy pressure)
#3b82f6  Blue     → Total / neutral
#f59e0b  Orange   → Warnings / cascades
#6b7280  Gray     → No data / neutral
```

## 🏗️ File Structure (Quick Nav)

```
public/js/liquidations-controller.js          ← Main logic
resources/views/derivatives/liquidations.blade.php  ← Dashboard
resources/views/components/liquidations/
  ├── analytics-summary.blade.php             ← Stats & insights
  ├── historical-chart.blade.php              ← Time series
  ├── liquidation-stream.blade.php            ← Real-time feed
  ├── heatmap-chart.blade.php                 ← Intensity map
  ├── exchange-comparison.blade.php           ← Volume comparison
  └── coin-list-table.blade.php               ← Multi-range table
```

## ⚡ Quick Fixes

### Charts not showing?

```javascript
// Check Chart.js loaded
console.log(typeof Chart); // should be "function"

// Wait for ready
await window.chartJsReady;
```

### No data appearing?

```javascript
// Check API base URL
console.log(document.querySelector('meta[name="api-base-url"]').content);

// Check console for errors
// Verify response in Network tab
```

### Filters not working?

```javascript
// Check global state
console.log(this.globalSymbol, this.globalExchange, this.globalInterval);

// Verify event dispatch
window.dispatchEvent(new CustomEvent('symbol-changed', { detail: {...} }));
```

## 📱 Responsive Breakpoints

```css
Desktop:  >= 992px  → Full layout, all components visible
Tablet:   768-991px → Stacked columns, condensed filters
Mobile:   < 768px   → Single column, compact mode
```

## 🧪 Testing Quick Checklist

```bash
✓ Navigate to /derivatives/liquidations
✓ Check all 6 components render
✓ Change symbol → data updates
✓ Change exchange filter → data filters
✓ Change interval → charts update
✓ Click "Refresh All" → loading spinner → updates
✓ Stream auto-refreshes every 10s
✓ No console errors
✓ Mobile responsive
```

## 💾 Local Storage (Optional)

```javascript
// Save preferences
localStorage.setItem("liquidations_symbol", "BTC");
localStorage.setItem("liquidations_interval", "1m");

// Retrieve
const symbol = localStorage.getItem("liquidations_symbol") || "BTC";
```

## 🔐 API Configuration

```bash
# .env file
API_BASE_URL=http://202.155.90.20:8000

# Access in blade
<meta name="api-base-url" content="{{ config('services.api.base_url') }}">

# Access in JS
const baseUrl = document.querySelector('meta[name="api-base-url"]').content;
```

## 🎯 Trading Strategy Examples

### Strategy 1: Cascade Reversal

```
1. Monitor cascade count
2. Wait for count > 30
3. Wait for cascade to subside (count drops)
4. Enter position in reversal direction
5. Set tight stops
```

### Strategy 2: Ratio Extremes

```
1. Monitor long/short ratio
2. Ratio > 2.5 → Expect long squeeze
3. Wait for first sign of weakness
4. Short with tight stop above resistance
5. Exit on ratio normalization
```

### Strategy 3: Stop Hunt

```
1. Identify liquidation clusters in heatmap
2. Note price levels with high volume
3. Expect price to test these levels
4. Enter after "wick" through cluster
5. Stop beyond cluster zone
```

## 📊 Performance Tips

```javascript
// Reduce data load
globalLimit: 1000 (instead of 2000)

// Increase refresh interval
setInterval(..., 60000) // 60s instead of 30s

// Limit stream display
filteredOrders.slice(0, 50) // Show 50 instead of 100

// Disable auto-refresh on specific components
this.isStreaming = false
```

## 🚨 Warning Signs

| Sign                     | Meaning            | Action                 |
| ------------------------ | ------------------ | ---------------------- |
| Cascade count > 50       | Extreme volatility | Reduce position size   |
| Ratio > 3.0              | Extreme long bias  | Watch for squeeze      |
| Ratio < 0.33             | Extreme short bias | Watch for pump         |
| Large single liq > $10M  | Whale liquidated   | Expect volatility      |
| Multiple exchanges spike | Coordinated move   | High conviction signal |

## 🔗 Related Modules

-   **Funding Rate:** Complement liquidations with funding analysis
-   **Long/Short Ratio:** Position sentiment overview
-   **Open Interest:** Total leverage in market

## 📞 Support & Debug

```javascript
// Enable debug mode
localStorage.setItem("liquidations_debug", "true");

// View global state
console.log(this.$root);

// Check overview data
console.log(this.$root.overview);

// Monitor events
window.addEventListener("liquidations-overview-ready", console.log);
```

---

**Quick Links:**

-   [Full Documentation](./LIQUIDATIONS-IMPLEMENTATION.md)
-   [API Documentation](./API-ENDPOINTS.md)
-   [GitHub Issues](https://github.com/your-repo/issues)

**Version:** 1.0.0 | **Last Updated:** October 11, 2025
