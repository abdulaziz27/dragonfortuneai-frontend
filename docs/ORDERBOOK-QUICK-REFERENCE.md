# Orderbook Snapshots - Quick Reference

## 🚀 Quick Start

### Access URL

```
http://localhost:8000/spot-microstructure/orderbook-snapshots
```

### Navigation

```
Dashboard → Spot Microstructure → Orderbook Snapshots
```

## 📊 Dashboard Components Map

```
╔═══════════════════════════════════════════════════════════════════╗
║  📊 ORDERBOOK SNAPSHOTS - MARKET MICROSTRUCTURE                   ║
║  Controls: [BTCUSDT ▼] [Binance ▼] [🔄 Refresh All]             ║
╚═══════════════════════════════════════════════════════════════════╝

╔═══════════════════════════════════════════════════════════════════╗
║  📊 BOOK PRESSURE ANALYSIS                        [BULLISH]       ║
║  Bid: 71.14  |  Ask: 44.12  |  Ratio: 1.61  |  Samples: 100     ║
║  [████████████████░░░░░░░░] 61% Bid / 39% Ask                    ║
╚═══════════════════════════════════════════════════════════════════╝

╔════════════════════╦════════════════════╦═══════════════════════╗
║ ⚖️ LIQUIDITY      ║ 📏 MARKET DEPTH    ║ ⚡ QUICK STATS      ║
║ IMBALANCE          ║                    ║                       ║
║                    ║                    ║                       ║
║ Total: 496.62K     ║ Depth Score: 63.74 ║ Mid Price: $120,519  ║
║ Ratio: 1.72        ║ Bid Levels: 185    ║ Spread: $19          ║
║ Imbalance: +26.49% ║ Ask Levels: 97     ║ Spread%: 0.0158%     ║
║ Bid: 314.08K       ║ Bid Vol: 471.68K   ║                       ║
║ Ask: 182.54K       ║ Ask Vol: 659.97K   ║ Status: Tight spread ║
╚════════════════════╩════════════════════╩═══════════════════════╝

╔═══════════════════════════════════════════════════════════════════╗
║  📖 LIVE ORDERBOOK SNAPSHOT                    [Auto-refresh: 5s] ║
║                                                                   ║
║  ASKS (SELL)      │    MID PRICE     │    BIDS (BUY)            ║
║  ════════════════ │ ════════════════ │ ════════════════        ║
║  $120,538  0.003  │                  │  $120,500  0.245         ║
║  $120,539  0.525  │   $120,519.50    │  $120,499  0.318         ║
║  $120,606  0.001  │   Spread: $19    │  $120,498  0.520         ║
║       ...         │                  │       ...                ║
╚═══════════════════════════════════════════════════════════════════╝

╔════════════════════════════════╦══════════════════════════════════╗
║  📈 BOOK PRESSURE HISTORY      ║  🔥 LIQUIDITY HEATMAP           ║
║  (Last 100 data points)        ║  (Price Level Distribution)     ║
║                                ║                                  ║
║  [Line Chart]                  ║  [Bar Chart]                    ║
║  - Bid Pressure (Green)        ║  - Bid Liquidity (Green bars)   ║
║  - Ask Pressure (Red)          ║  - Ask Liquidity (Red bars)     ║
║                                ║                                  ║
╚════════════════════════════════╩══════════════════════════════════╝

╔════════════════════════════════╦══════════════════════════════════╗
║  📊 MARKET DEPTH HISTORY       ║  📋 ORDERBOOK DEPTH DETAILS     ║
║                                ║                                  ║
║  Time  Bid/Ask  Volumes  Score ║  Lvl  Bid  Ask  Cumulative      ║
║  ──────────────────────────────║  ─────────────────────────────  ║
║  14:00  185/97  471K/660K 63.7 ║   1   $120,500  $120,538        ║
║  14:05  180/168 342K/751K 64.9 ║   2   $120,499  $120,539        ║
║  14:10  88/162  241K/642K 64.1 ║   3   $120,498  $120,606        ║
║  ...                           ║  ...                            ║
╚════════════════════════════════╩══════════════════════════════════╝

╔═══════════════════════════════════════════════════════════════════╗
║  📚 TRADING INSIGHTS                                              ║
║  ┌─────────────┬─────────────┬──────────────┐                   ║
║  │ 🟩 BULLISH  │ 🟥 BEARISH  │ ⚡ CONCEPTS   │                   ║
║  │ - Bid > Ask │ - Ask > Bid │ - Pressure   │                   ║
║  │ - +Imbal    │ - -Imbal    │ - Depth      │                   ║
║  │ - High bids │ - High asks │ - Imbalance  │                   ║
║  └─────────────┴─────────────┴──────────────┘                   ║
╚═══════════════════════════════════════════════════════════════════╝
```

## 🔌 API Endpoints Usage

| Component                  | API Endpoint           | Params                      | Purpose                   |
| -------------------------- | ---------------------- | --------------------------- | ------------------------- |
| **Pressure Card**          | `/book-pressure`       | symbol, exchange, limit=100 | Bid/Ask pressure analysis |
| **Pressure Chart**         | `/book-pressure`       | symbol, exchange, limit=100 | Historical pressure trend |
| **Liquidity Imbalance**    | `/orderbook/liquidity` | symbol, depth=20            | Imbalance metrics         |
| **Market Depth Stats**     | `/market-depth`        | symbol, exchange, limit=1   | Depth score & levels      |
| **Market Depth Table**     | `/market-depth`        | symbol, exchange, limit=20  | Historical depth data     |
| **Quick Stats**            | `/orderbook/snapshot`  | symbol, depth=1             | Spread & mid price        |
| **Live Snapshot**          | `/orderbook/snapshot`  | symbol, depth=15            | Real-time orderbook       |
| **Liquidity Distribution** | `/liquidity-heatmap`   | symbol, exchange, limit=20  | Price level liquidity     |
| **Depth Table**            | `/orderbook-depth`     | symbol, exchange, limit=20  | Level-by-level details    |

## 🎯 Component Functions

### Alpine.js Components

```javascript
// Main Controller
orderbookController(); // Global state & event handling

// Data Components
bookPressureCard(); // Book pressure metrics
liquidityImbalance(); // Liquidity imbalance stats
marketDepthStats(); // Market depth metrics
quickStats(); // Quick market stats

// Visualization Components
liveOrderbookSnapshot(); // Real-time orderbook display
bookPressureChart(); // Pressure history chart
liquidityDistributionTable(); // Liquidity distribution table
marketDepthTable(); // Market depth table
orderbookDepthTable(); // Orderbook depth table
```

## 🎨 Color Scheme

| Color              | Usage                   | Meaning              |
| ------------------ | ----------------------- | -------------------- |
| 🟩 Green (#22c55e) | Bids, Bullish, Positive | Buy pressure, Long   |
| 🟥 Red (#ef4444)   | Asks, Bearish, Negative | Sell pressure, Short |
| 🔵 Blue (#3b82f6)  | Info, Primary           | Neutral, General     |
| ⚪ Gray (#6b7280)  | Secondary, Disabled     | Inactive, Loading    |

## 📏 Data Formatting

### Numbers

-   `< 1,000`: 123.45
-   `≥ 1,000`: 1.23K
-   `≥ 1,000,000`: 1.23M

### Prices

-   Format: `$120,519.50`
-   Decimals: 2 (configurable)

### Percentages

-   Format: `+26.49%` or `-15.32%`
-   Always show sign (+/-)

### Time

-   Format: `14:05` (24h format)
-   Timezone: Based on API response

## ⚙️ Settings & Configuration

### Default Values

```javascript
globalSymbol: "BTCUSDT";
globalExchange: "binance";
autoRefreshInterval: 5000; // 5 seconds (live snapshot only)
```

### API Base URL

```env
API_BASE_URL=http://202.155.90.20:8000
```

### Symbols Available

-   BTCUSDT (Bitcoin)
-   ETHUSDT (Ethereum)
-   SOLUSDT (Solana)
-   BNBUSDT (BNB)
-   XRPUSDT (XRP)
-   ADAUSDT (Cardano)

### Exchanges Available

-   binance
-   okx
-   bybit
-   bitget

## 🔄 Event System

### Events Dispatched

```javascript
"symbol-changed"; // When symbol selector changes
"exchange-changed"; // When exchange selector changes
"refresh-all"; // When Refresh All button clicked
```

### Event Listeners

All components listen to:

-   Global filter changes (symbol/exchange)
-   Refresh all event
-   Component-specific watchers

## 📊 Metrics Explained

### Book Pressure

-   **Bid Pressure**: Sum of bid depth/liquidity
-   **Ask Pressure**: Sum of ask depth/liquidity
-   **Ratio**: bid_pressure / ask_pressure
-   **Direction**: bullish (>1), bearish (<1), neutral (≈1)

### Liquidity Imbalance

-   **Total Liquidity**: bid + ask liquidity
-   **Imbalance**: bid - ask liquidity
-   **Imbalance %**: (imbalance / total) × 100

### Market Depth

-   **Depth Score**: 0-100, higher = more stable
-   **Bid/Ask Levels**: Count of price levels
-   **Total Volume**: Aggregate volume at all levels

### Spread

-   **Current Spread**: best_ask - best_bid
-   **Spread %**: (spread / mid_price) × 100
-   **Mid Price**: (best_ask + best_bid) / 2

## 🚨 Trading Signals

### Strong Bullish

-   Pressure ratio > 2.0
-   Imbalance > +30%
-   Pressure direction: bullish
-   High bid liquidity walls

### Moderate Bullish

-   Pressure ratio: 1.2 - 2.0
-   Imbalance: +10% to +30%
-   Increasing bid pressure trend

### Neutral

-   Pressure ratio: 0.8 - 1.2
-   Imbalance: -10% to +10%
-   Balanced liquidity

### Moderate Bearish

-   Pressure ratio: 0.5 - 0.8
-   Imbalance: -10% to -30%
-   Increasing ask pressure trend

### Strong Bearish

-   Pressure ratio < 0.5
-   Imbalance < -30%
-   Pressure direction: bearish
-   High ask liquidity walls

## 🛠️ Troubleshooting

### No Data Showing

1. Check API base URL in meta tag
2. Open console (F12) and check for errors
3. Verify API endpoint is accessible
4. Check symbol/exchange selection

### Charts Not Rendering

1. Verify Chart.js is loaded
2. Check console for JavaScript errors
3. Ensure canvas elements have IDs
4. Check data format from API

### Auto-Refresh Not Working

1. Component only: Live Orderbook Snapshot
2. Check interval: 5000ms (5 seconds)
3. Verify component initialized
4. Check console logs

### Slow Loading

1. Reduce limit parameter in API calls
2. Check network speed
3. Verify API response time
4. Consider caching strategies

## 📞 Support

### Files to Check

-   Main: `resources/views/spot-microstructure/orderbook-snapshots.blade.php`
-   Controller: `public/js/orderbook-controller.js`
-   Components: `resources/views/components/orderbook/*.blade.php`

### Console Commands

```javascript
// Check global state
console.log($root.globalSymbol);
console.log($root.globalExchange);

// Manually trigger refresh
window.dispatchEvent(new CustomEvent("refresh-all"));

// Check component state
console.log($component);
```

## 📚 Documentation Files

1. **Implementation Guide**: `ORDERBOOK-SNAPSHOTS-IMPLEMENTATION.md`
2. **Summary**: `ORDERBOOK-SNAPSHOTS-SUMMARY.md`
3. **Testing Guide**: `ORDERBOOK-SNAPSHOTS-TESTING.md`
4. **Quick Reference**: `ORDERBOOK-QUICK-REFERENCE.md` (this file)

---

**Last Updated**: October 2025  
**Version**: 1.0  
**Status**: ✅ Production Ready
