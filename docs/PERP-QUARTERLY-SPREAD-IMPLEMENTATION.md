# Perp-Quarterly Spread Dashboard - Implementation Guide

## 📋 Overview

Implementasi lengkap dashboard **Perp-Quarterly Spread** yang menampilkan analisis spread antara kontrak perpetual dan quarterly futures untuk mengidentifikasi peluang arbitrase dan ekspektasi pasar.

**Status:** ✅ Fully Implemented  
**Tanggal:** 2025-10-11  
**API Base URL:** Configured via `meta[name="api-base-url"]`

---

## 🎯 Features Implemented

### 1. **Main Dashboard View**

**File:** `resources/views/derivatives/perp-quarterly-spread.blade.php`

Features:

-   ✅ Global filters (Symbol, Exchange, Interval)
-   ✅ Real-time data refresh
-   ✅ Comprehensive spread analytics
-   ✅ Historical spread chart
-   ✅ Trading insights panel
-   ✅ Data table with recent spreads
-   ✅ Educational content (Understanding spreads)
-   ✅ API documentation reference

### 2. **JavaScript Controller**

**File:** `public/js/perp-quarterly-controller.js`

Capabilities:

-   ✅ Global state management
-   ✅ Event-driven component coordination
-   ✅ API integration with error handling
-   ✅ Overview data aggregation
-   ✅ URL state management
-   ✅ Utility functions for formatting

### 3. **Component Files**

#### A. Analytics Card

**File:** `resources/views/components/perp-quarterly/analytics-card.blade.php`

Displays:

-   Current spread (absolute & BPS)
-   Average spread
-   Spread range (min/max)
-   Standard deviation
-   Market structure (Contango/Backwardation)
-   Trend direction (Widening/Narrowing)
-   Contract symbols (Perp & Quarterly)
-   API insights

#### B. Spread History Chart

**File:** `resources/views/components/perp-quarterly/spread-history-chart.blade.php`

Features:

-   Chart.js line chart
-   Historical spread timeseries
-   Zero line reference
-   Dynamic color based on spread (green for contango, red for backwardation)
-   Tooltips with interpretation
-   Auto-refresh (60s)

#### C. Spread Data Table

**File:** `resources/views/components/perp-quarterly/spread-table.blade.php`

Displays:

-   Timestamp
-   Perp symbol
-   Quarterly symbol
-   Spread (absolute)
-   Spread (BPS)
-   Market structure badge
-   Scrollable table (max 20 rows visible)

#### D. Insights Panel

**File:** `resources/views/components/perp-quarterly/insights-panel.blade.php`

Provides:

-   Market structure summary
-   Spread trend indicator
-   Arbitrage opportunity score (0-100%)
-   Key metrics (avg, min, max, volatility)
-   Trading strategy suggestions
-   Contract information

---

## 🔌 API Integration

### API Endpoints Used

#### 1. **Analytics Endpoint**

```
GET /api/perp-quarterly/analytics
```

**Parameters:**

-   `exchange` (required): Exchange name (e.g., Binance, Bybit, OKX)
-   `base` (required): Base asset (e.g., BTC, ETH)
-   `quote` (required): Quote asset (e.g., USDT, USD)
-   `interval` (required): Time interval (5m, 15m, 1h, 4h, 1d)
-   `limit` (optional): Max data points (default: 2000)
-   `perp_symbol` (optional): Override perp contract symbol
-   `quarterly_symbol` (optional): Override quarterly contract symbol

**Response Structure:**

```json
{
    "base": "BTC",
    "exchange": "Binance",
    "quote": "USDT",
    "perp_symbol": "BTCUSDT",
    "quarterly_symbol": "BTCUSDT_240628",
    "data_points": 2000,
    "spread_bps": {
        "current": 15.8,
        "average": 12.5,
        "min": -5.2,
        "max": 45.3,
        "std_dev": 8.7
    },
    "trend": {
        "direction": "widening",
        "change_bps": 3.2
    },
    "insights": [
        {
            "type": "arbitrage_opportunity",
            "severity": "medium",
            "message": "Spread widening detected. Consider calendar spread strategy."
        }
    ]
}
```

#### 2. **History Endpoint**

```
GET /api/perp-quarterly/history
```

**Parameters:**

-   `exchange` (required): Exchange name
-   `base` (required): Base asset
-   `quote` (required): Quote asset
-   `interval` (required): Time interval
-   `limit` (optional): Max rows (default: 2000)
-   `start_time` (optional): Start timestamp (ms)
-   `end_time` (optional): End timestamp (ms)
-   `perp_symbol` (optional): Override perp symbol
-   `quarterly_symbol` (optional): Override quarterly symbol

**Response Structure:**

```json
{
    "data": [
        {
            "ts": 1759824600000,
            "exchange": "Binance",
            "perp_symbol": "BTCUSDT",
            "quarterly_symbol": "BTCUSDT_240628",
            "spread_abs": 105.23,
            "spread_bps": 15.8
        }
    ],
    "meta": {
        "base": "BTC",
        "quote": "USDT",
        "exchange": "Binance",
        "interval": "5m",
        "perp_symbol": "BTCUSDT",
        "quarterly_symbol": "BTCUSDT_240628",
        "points": 2000
    }
}
```

---

## 🎨 Design Patterns

### 1. **Component Communication**

-   Global controller dispatches events: `symbol-changed`, `exchange-changed`, `interval-changed`, `refresh-all`
-   Components listen to events and update independently
-   Overview data aggregation via `perp-quarterly-overview-ready` event

### 2. **Data Flow**

```
User Action → Global Controller → API Fetch → Event Dispatch → Components Update
```

### 3. **Error Handling**

-   Try-catch blocks in all API calls
-   Fallback to empty data on errors
-   Console logging for debugging
-   User-friendly error messages

### 4. **State Management**

-   Global state in main controller
-   Local state in each component
-   URL state for bookmarking
-   LocalStorage for preferences (via utility)

---

## 📊 Trading Interpretations

### Market Structures

#### **Contango (Spread > 0)**

-   Perpetual trading above quarterly
-   Market expects higher future prices
-   Normal in bull markets
-   **Strategy:** Short perp / Long quarterly for convergence

#### **Backwardation (Spread < 0)**

-   Quarterly trading above perpetual
-   Supply shortage or high spot demand
-   Unusual in crypto (arbitrage opportunity)
-   **Strategy:** Long perp / Short quarterly

#### **Convergence**

-   Spread narrows as expiry approaches
-   At expiry, both contracts converge to spot price
-   Guaranteed convergence = low-risk arbitrage

### Trend Analysis

-   **Widening:** Divergence increasing (enter calendar spread)
-   **Narrowing:** Convergence approaching (exit positions)

---

## 🚀 Usage Guide

### 1. **Access Dashboard**

Navigate to: `/derivatives/perp-quarterly-spread`

### 2. **Select Filters**

-   Choose asset (BTC, ETH, etc.)
-   Choose exchange (Binance, Bybit, OKX, etc.)
-   Choose interval (5m, 15m, 1h, 4h, 1d)

### 3. **Interpret Data**

**Spread Analytics Card:**

-   Current spread shows real-time market structure
-   Trend badge indicates widening or narrowing
-   Insights provide actionable alerts

**History Chart:**

-   Green area = Contango
-   Red area = Backwardation
-   Watch for convergence patterns

**Insights Panel:**

-   Arbitrage score (0-100%)
-   Trading strategy suggestions
-   Risk considerations

**Data Table:**

-   Recent spread measurements
-   Timestamp, symbols, spreads
-   Sortable and scrollable

### 4. **Trading Actions**

Based on insights:

-   High arbitrage score (>75%) → Strong opportunity
-   Widening contango → Consider calendar spread
-   Approaching expiry → Guaranteed convergence play

---

## 🔧 Configuration

### API Base URL

Set in `.env`:

```env
API_BASE_URL=https://test.dragonfortune.ai
```

Or in `config/services.php`:

```php
'api' => [
    'base_url' => env('API_BASE_URL', 'https://test.dragonfortune.ai'),
],
```

### Auto-Refresh Intervals

-   Analytics Card: 30s
-   History Chart: 60s
-   Data Table: 60s
-   Insights Panel: 30s

---

## 🐛 Troubleshooting

### Issue: No Data Displayed

**Solution:**

1. Check API base URL in browser console
2. Verify network requests in DevTools
3. Check API endpoint availability
4. Ensure correct parameters (exchange, base, quote)

### Issue: Chart Not Rendering

**Solution:**

1. Verify Chart.js is loaded (check console)
2. Wait for `chartJsReady` promise
3. Check canvas element ID is unique
4. Clear browser cache

### Issue: Components Not Updating

**Solution:**

1. Check event listeners are registered
2. Verify Alpine.js is initialized
3. Check console for JavaScript errors
4. Refresh page to reinitialize

---

## 📁 File Structure

```
resources/views/
├── derivatives/
│   └── perp-quarterly-spread.blade.php (Main view)
└── components/
    └── perp-quarterly/
        ├── analytics-card.blade.php
        ├── spread-history-chart.blade.php
        ├── spread-table.blade.php
        └── insights-panel.blade.php

public/js/
└── perp-quarterly-controller.js

routes/
└── web.php (Route: /derivatives/perp-quarterly-spread)
```

---

## ✅ Testing Checklist

-   [x] Dashboard loads without errors
-   [x] Global filters work correctly
-   [x] Analytics card displays data
-   [x] History chart renders
-   [x] Data table populates
-   [x] Insights panel shows strategies
-   [x] Refresh button works
-   [x] Auto-refresh functions
-   [x] Event communication works
-   [x] Mobile responsive
-   [x] API error handling
-   [x] Educational content readable

---

## 🎓 Key Learnings

### Spread Trading Basics

1. **Contango = Perp > Quarterly** → Bullish market expectations
2. **Backwardation = Quarterly > Perp** → Supply shortage or spot strength
3. **Convergence** → Guaranteed at expiry
4. **Wide spreads** → Arbitrage opportunities
5. **Funding rates** → Drive perpetual spread behavior

### Risk Management

-   Consider execution costs (fees + slippage)
-   Monitor liquidity on both contracts
-   Set stop-losses on calendar spreads
-   Watch for funding rate changes
-   Plan for expiry and rollover

---

## 🔗 Related Documentation

-   [Funding Rate Implementation](./FUNDING-RATE-IMPLEMENTATION.md)
-   [Liquidations Implementation](./LIQUIDATIONS-IMPLEMENTATION.md)
-   [Long-Short Ratio Implementation](./LONG-SHORT-RATIO-IMPLEMENTATION-GUIDE.md)

---

## 📝 Notes

-   All timestamps are in milliseconds (epoch)
-   BPS = Basis Points (1 bps = 0.01%)
-   Auto-discovery of contracts by backend
-   Manual override available via params
-   Cadence: 5-15 minutes (configurable)

---

**Developed by:** AI Assistant  
**Framework:** Laravel 11 + Alpine.js + Chart.js  
**Design Philosophy:** Think like a trader • Build like an engineer • Visualize like a designer
