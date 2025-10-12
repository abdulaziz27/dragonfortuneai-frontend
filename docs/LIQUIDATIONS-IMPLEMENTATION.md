# Liquidations Module Implementation Guide

## 📋 Overview

Modul **Liquidations** adalah sistem monitoring dan analisis komprehensif untuk melacak forced liquidations di berbagai exchange cryptocurrency. Modul ini menggunakan 6 API endpoints berbeda untuk memberikan gambaran lengkap tentang kondisi liquidation market secara real-time.

## 🎯 Fitur Utama

### 1. **Analytics Summary**

-   Total liquidation volume (Long/Short/Total)
-   Long/Short Ratio dengan visual meter
-   Cascade event detection
-   AI-powered insights dan warnings
-   Top liquidation events

### 2. **Historical Chart**

-   Time series visualization dengan 3 mode tampilan:
    -   Line Chart
    -   Bar Chart
    -   Area Chart
-   Price overlay untuk korelasi
-   Statistical summary (avg long, avg short, peak total)

### 3. **Live Liquidation Stream**

-   Real-time order feed
-   Filter by side (Long/Short) dan exchange
-   Auto-refresh setiap 10 detik
-   Color-coded berdasarkan side

### 4. **Liquidation Heatmap**

-   Visualisasi intensitas liquidation
-   Grouped by exchange dan time
-   Stacked bar chart untuk perbandingan

### 5. **Exchange Comparison**

-   Volume breakdown per exchange
-   Multi-timeframe view (1h, 4h, 12h, 24h)
-   Top exchange ranking

### 6. **Coin List Table**

-   Multi-range snapshot per exchange
-   Interactive time range selector
-   Long/short ratio per exchange
-   Sortable table

## 🔌 API Endpoints Used

### 1. `/api/liquidations/analytics`

**Purpose:** Comprehensive analytics dengan cascade detection

**Parameters:**

-   `symbol` (required): Trading pair (e.g., BTCUSDT)
-   `interval` (required): Time resolution (1m, 5m, 15m, 1h, 4h)
-   `exchange` (optional): Filter by exchange
-   `limit`: Data points (default: 2000)

**Response Highlights:**

```json
{
  "liquidation_summary": {
    "total_usd": 20043527.96,
    "total_long_usd": 13837529.12,
    "total_short_usd": 6205998.84,
    "long_short_ratio": 2.23
  },
  "cascade_detection": {
    "cascade_count": 36,
    "threshold_usd": 123220.35
  },
  "insights": [...],
  "top_events": [...]
}
```

### 2. `/api/liquidations/coin-list`

**Purpose:** Multi-range snapshot per coin across exchanges

**Parameters:**

-   `symbol` (required): Coin symbol (e.g., BTC)
-   `exchange` (optional): Filter by exchange
-   `limit`: Max rows (default: 1000)

**Data Fields:**

-   `liquidation_usd_1h`, `_4h`, `_12h`, `_24h`
-   `long_liquidation_usd_1h`, `_4h`, `_12h`, `_24h`
-   `short_liquidation_usd_1h`, `_4h`, `_12h`, `_24h`

### 3. `/api/liquidations/exchange-list`

**Purpose:** Aggregated total per exchange for specific time range

**Parameters:**

-   `symbol` (required): Coin symbol
-   `range_str` (required): Time range (1h, 4h, 12h, 24h)
-   `exchange` (optional): Filter by exchange
-   `limit`: Max rows (default: 1000)

### 4. `/api/liquidations/orders`

**Purpose:** Real-time liquidation orders stream

**Parameters:**

-   `symbol` (optional): Trading pair
-   `exchange` (optional): Filter by exchange
-   `start_time`, `end_time` (optional): Time range
-   `limit`: Max orders (default: 2000)

**Response Fields:**

```json
{
    "data": [
        {
            "ts": 1759824519782,
            "exchange": "OKX",
            "pair": "XRP-USDT-SWAP",
            "side": 1,
            "side_label": "long",
            "qty_usd": "1441.139",
            "price": "2.9411"
        }
    ]
}
```

### 5. `/api/liquidations/pair-history`

**Purpose:** Bucketed time series data

**Parameters:**

-   `symbol` (optional): Trading pair
-   `exchange` (optional): Filter by exchange
-   `interval` (optional): Bucket size
-   `with_price`: Include price data (true/false)
-   `start_time`, `end_time`, `limit`

**Response Fields:**

```json
{
    "data": [
        {
            "ts": 1745625600000,
            "exchange": "Binance",
            "pair": "BTCUSDT",
            "interval_name": "4h",
            "liq_usd": "881487.676",
            "long_liquidation_usd": "124561.275",
            "short_liquidation_usd": "756926.401",
            "bucket_price": 45000.0 // if with_price=true
        }
    ]
}
```

## 🏗️ Architecture

### File Structure

```
├── public/js/
│   └── liquidations-controller.js          # Global controller & API integration
├── resources/views/
│   ├── derivatives/
│   │   └── liquidations.blade.php          # Main dashboard page
│   └── components/liquidations/
│       ├── analytics-summary.blade.php     # Summary stats & insights
│       ├── coin-list-table.blade.php       # Exchange breakdown table
│       ├── liquidation-stream.blade.php    # Real-time order feed
│       ├── heatmap-chart.blade.php         # Intensity heatmap
│       ├── exchange-comparison.blade.php   # Exchange volume comparison
│       └── historical-chart.blade.php      # Time series chart
└── routes/web.php                          # Route definition
```

### Controller Pattern

**Global State Management:**

```javascript
function liquidationsController() {
    return {
        globalSymbol: "BTC",
        globalExchange: "",
        globalInterval: "1m",
        globalLimit: 2000,
        globalLoading: false,
        overview: null,
        cache: {},

        // Methods: init(), loadOverview(), updateSymbol(), etc.
    };
}
```

**Component Pattern:**

```javascript
function componentName() {
    return {
        // Local state
        data: [],
        loading: false,

        init() {
            // Listen for global events
            window.addEventListener("liquidations-overview-ready", (e) => {
                this.applyOverview(e.detail);
            });

            // Listen for filter changes
            window.addEventListener("symbol-changed", () => {
                this.loadData();
            });
        },

        applyOverview(overview) {
            // Process overview data
        },

        async loadData() {
            // Fetch component-specific data
        },
    };
}
```

### Event System

**Global Events:**

1. `liquidations-overview-ready` - Triggered when all API data loaded
2. `symbol-changed` - When user changes coin symbol
3. `exchange-changed` - When user changes exchange filter
4. `interval-changed` - When user changes time interval
5. `refresh-all` - Manual refresh triggered

**Data Flow:**

```
User Action → Global Controller → API Calls → Overview Built → Event Dispatched → Components Update
```

## 🎨 Component Details

### 1. Analytics Summary

**Location:** `components/liquidations/analytics-summary.blade.php`

**Features:**

-   Total/Long/Short liquidation stats
-   Long/Short ratio meter
-   Cascade event detection with severity levels
-   Top 3 largest liquidations
-   AI insights with severity badges

**Key Methods:**

-   `getLongPercentage()` - Calculate long ratio
-   `getCascadeBadgeClass()` - Style cascade alerts
-   `getInsightIcon()` - Icon for insight type

### 2. Historical Chart

**Location:** `components/liquidations/historical-chart.blade.php`

**Features:**

-   Chart.js powered visualization
-   3 chart types (line, bar, area)
-   Statistical summary cards
-   Responsive design

**Key Methods:**

-   `renderChart()` - Create/update chart
-   `calculateStats()` - Compute averages and peaks
-   `formatTimestamp()` - Format x-axis labels

### 3. Liquidation Stream

**Location:** `components/liquidations/liquidation-stream.blade.php`

**Features:**

-   Real-time order blotter
-   Side and exchange filters
-   Auto-refresh every 10s
-   Animated entries
-   Color-coded by side

**Key Methods:**

-   `applyFilters()` - Filter orders
-   `getAverageSize()` - Calculate avg order size
-   `getSideClass()` - Style by side

### 4. Heatmap Chart

**Location:** `components/liquidations/heatmap-chart.blade.php`

**Features:**

-   Stacked bar chart
-   Multi-exchange visualization
-   Time-based intensity
-   Top 5 exchanges shown

**Key Methods:**

-   `renderChart()` - Build stacked chart
-   `getExchangeColor()` - Color coding
-   Group data by exchange and time

### 5. Exchange Comparison

**Location:** `components/liquidations/exchange-comparison.blade.php`

**Features:**

-   Tab-based time range selector
-   Top 8 exchanges ranked
-   Percentage breakdown
-   Bar chart comparison

**Key Methods:**

-   `renderChart()` - Create comparison chart
-   Watch `selectedRange` for updates

### 6. Coin List Table

**Location:** `components/liquidations/coin-list-table.blade.php`

**Features:**

-   Multi-timeframe view (1h/4h/12h/24h)
-   Sortable by volume
-   Long/short ratio per exchange
-   Summary totals

**Key Methods:**

-   `updateDisplayedData()` - Filter by time range
-   `getRatioBadgeClass()` - Style ratio badges

## 🔄 Data Flow Example

### Initial Load

1. User navigates to `/derivatives/liquidations`
2. `liquidationsController()` initializes
3. `loadOverview()` called:
    ```javascript
    const [analytics, coinList, exchangeList, orders, pairHistory] =
      await Promise.all([...]) // 8 parallel API calls
    ```
4. Overview object built with all data
5. `liquidations-overview-ready` event dispatched
6. All components receive overview and render

### Symbol Change

1. User selects "ETH" in dropdown
2. `updateSymbol()` called
3. `symbol-changed` event dispatched
4. All components call their `loadData()`
5. Controller's `loadOverview()` re-fetches with new symbol
6. Components update with new data

### Auto-refresh

1. Timer triggers every 30s (configurable)
2. `loadOverview()` called in background
3. No cache clearing (smooth updates)
4. Components update silently

## 🎨 Styling & UX

### Color Scheme

```javascript
colors: {
  long: '#ef4444',      // Red - danger
  short: '#22c55e',     // Green - success
  total: '#3b82f6',     // Blue - primary
  cascade: '#f59e0b',   // Orange - warning
  neutral: '#6b7280'    // Gray
}
```

### Responsive Design

-   Mobile-first approach
-   Stacked layout on small screens
-   Condensed filters on mobile
-   Touch-friendly controls

### Loading States

-   Spinner on data fetch
-   Skeleton screens optional
-   "No data" placeholders
-   Error state handling

### Animations

-   Fade-in transitions
-   Smooth chart updates
-   Hover effects on cards
-   Pulse dot for live status

## 🚀 Usage

### Basic Usage

1. Navigate to `/derivatives/liquidations`
2. Select coin symbol (default: BTC)
3. Optionally filter by exchange
4. Choose time interval (1m, 5m, 15m, 1h, 4h)
5. Click "Refresh All" for manual update

### Trading Interpretation

**Long Liquidations (Red):**

-   Forced selling from overleveraged longs
-   Creates sell pressure → price drops
-   Watch for cascade events
-   Strategy: Look for oversold bounces

**Short Liquidations (Green):**

-   Forced buying from overleveraged shorts
-   Creates buy pressure → price pumps
-   Can trigger "short squeeze"
-   Strategy: Ride momentum, watch exhaustion

**Cascade Events:**

-   Chain reaction liquidations
-   Extreme volatility warning
-   High count = dangerous market
-   Wait for calm before entering

**Stop Hunt Zones:**

-   Price levels with liquidation clusters
-   Market makers may target these
-   Place stops beyond major clusters

## 🧪 Testing Checklist

-   [ ] All 6 API endpoints returning data
-   [ ] Charts rendering correctly
-   [ ] Filters working (symbol, exchange, interval)
-   [ ] Real-time updates functioning
-   [ ] No console errors
-   [ ] Responsive on mobile
-   [ ] Tooltips and hover states
-   [ ] Loading states visible
-   [ ] Error handling graceful
-   [ ] Performance acceptable (<2s load)

## 🔧 Configuration

### API Base URL

Set in `.env`:

```
API_BASE_URL=https://test.dragonfortune.ai
```

Accessed via:

```javascript
const baseMeta = document.querySelector('meta[name="api-base-url"]');
const baseUrl = baseMeta?.content || "";
```

### Refresh Intervals

```javascript
// Auto-refresh every 30s
setInterval(() => {
    if (!this.globalLoading) {
        this.loadOverview();
    }
}, 30000);

// Stream refresh every 10s
setInterval(() => {
    if (!this.loading && this.isStreaming) {
        this.loadData();
    }
}, 10000);
```

### Data Limits

```javascript
globalLimit: 2000,  // Main analytics
orders: 500,        // Stream orders
coinList: 1000,     // Exchange list
```

## 📊 Performance Considerations

### Optimization Strategies

1. **Parallel API Calls:** All endpoints fetched simultaneously
2. **Caching:** Store overview data to reduce calls
3. **Chart Reuse:** Destroy and recreate charts efficiently
4. **Data Limiting:** Configurable limits per endpoint
5. **Lazy Loading:** Components render only when visible
6. **Debouncing:** Filter changes debounced 300ms

### Memory Management

-   Destroy charts before recreating
-   Clear cache on manual refresh
-   Limit stream orders displayed (100 max)
-   Slice historical data to recent periods

## 🐛 Troubleshooting

### Common Issues

**1. Charts not rendering**

-   Check if Chart.js loaded: `typeof Chart !== 'undefined'`
-   Wait for `window.chartJsReady` promise
-   Verify canvas ref exists

**2. No data appearing**

-   Check API base URL in meta tag
-   Verify endpoint responses in Network tab
-   Check console for API errors
-   Confirm symbol format (BTCUSDT not BTC-USDT)

**3. Filters not working**

-   Check event listeners registered
-   Verify global state updates
-   Console log event dispatches

**4. Performance issues**

-   Reduce `globalLimit` value
-   Increase refresh intervals
-   Limit visible orders in stream

## 🔐 Security Notes

-   All API calls client-side (no backend proxy)
-   No authentication required for public endpoints
-   CORS must be enabled on API server
-   Rate limiting handled by API

## 📝 Future Enhancements

-   [ ] WebSocket support for true real-time
-   [ ] Export data to CSV
-   [ ] Custom alerts/notifications
-   [ ] More advanced cascade algorithms
-   [ ] Historical comparison tools
-   [ ] Mobile app integration
-   [ ] Save custom layouts
-   [ ] Dark/light theme toggle

## 📚 References

-   Funding Rate module (similar pattern)
-   Long Short Ratio module (event system reference)
-   Chart.js documentation
-   Alpine.js documentation

---

**Last Updated:** October 11, 2025
**Version:** 1.0.0
**Author:** DragonFortune AI Team
