# Funding Rate Implementation Guide

## 📋 Overview

Frontend implementation untuk modul **Derivatives Core - Funding Rate** yang mengkonsumsi API backend eksternal dan menampilkan visualisasi komprehensif untuk analisis funding rate pasar derivatif crypto.

## 🎯 Objective

Membangun dashboard funding rate yang:

-   Mengkonsumsi 4 endpoint backend yang tersedia
-   Menampilkan visualisasi real-time untuk trading decisions
-   Memberikan insights otomatis tentang market bias dan positioning
-   Responsive dan performant

## 🔌 Backend API Integration

### Base URL Configuration

```env
API_BASE_URL=http://202.155.90.20:8000
```

Konfigurasi ini dibaca oleh `config/services.php` dan di-expose ke frontend via meta tag di `layouts/app.blade.php`:

```html
<meta name="api-base-url" content="{{ config('services.api.base_url') }}" />
```

### Available Endpoints

#### 1. `/api/funding-rate/analytics`

**Purpose:** Analisis komprehensif dari funding rate history

**Parameters:**

-   `symbol` (required): Trading pair (e.g., BTCUSDT)
-   `exchange` (optional): Exchange name
-   `interval` (optional): Time interval
-   `limit` (optional): Max records (default: 2000)

**Response Structure:**

```json
{
    "summary": {
        "current": 0.00729,
        "average": 0.005068863,
        "median": 0.0053875,
        "min": -0.01277,
        "max": 0.01,
        "std_dev": 0.00381603598457205,
        "volatility": "moderate"
    },
    "bias": {
        "direction": "long",
        "strength": 0.005068863,
        "strength_label": "strong"
    },
    "trend": {
        "direction": "increasing",
        "change": 0.0021510175,
        "older_avg": 0.00347661,
        "recent_avg": 0.0056276275
    },
    "extremes": {
        "count": 75,
        "percentage": 3.75,
        "threshold": 0.0076320719
    },
    "insights": [
        {
            "type": "long_bias",
            "severity": "medium",
            "message": "Strong long bias detected..."
        }
    ]
}
```

**Frontend Usage:**

-   `analytics-insights.blade.php` - Display summary cards, trend, extremes
-   `quick-stats` panel - Populate insights

#### 2. `/api/funding-rate/bias`

**Purpose:** Determine market bias (long/short/neutral)

**Parameters:**

-   `symbol` (required): Trading pair
-   `limit` (optional): Max records (default: 1000)

**Response Structure:**

```json
{
    "bias": "long",
    "strength": 65.5,
    "avg_funding_close": 0.005068863,
    "symbol": "BTCUSDT"
}
```

**Frontend Usage:**

-   `bias-card.blade.php` - Display bias gauge and strength meter
-   `quick-stats` panel - Calculate window average

#### 3. `/api/funding-rate/exchanges`

**Purpose:** Current funding rate snapshot per exchange

**Parameters:**

-   `symbol` (optional): Coin symbol (e.g., BTC)
-   `exchange` (optional): Filter by exchange
-   `margin_type` (optional): isolated/cross
-   `limit` (optional): Max records (default: 1000)

**Response Structure:**

```json
{
    "data": [
        {
            "exchange": "Binance",
            "symbol": "BTC",
            "funding_rate": -0.0052418,
            "funding_rate_interval_hours": 8,
            "next_funding_time": 1760166000000,
            "margin_type": "stablecoin",
            "updated_at": "2025-10-11T13:24:58Z"
        }
    ]
}
```

**Frontend Usage:**

-   `exchange-table.blade.php` - Display snapshot table with next funding time
-   `exchange-comparison.blade.php` - Bar chart comparison
-   `heatmap.blade.php` - Build exchange × time grid
-   `quick-stats` panel - Calculate snapshot average

#### 4. `/api/funding-rate/history`

**Purpose:** Historical funding rate in OHLC format

**Parameters:**

-   `symbol` (optional): Trading pair
-   `exchange` (optional): Filter by exchange
-   `interval` (optional): Time interval (1m, 5m, 1h, 4h, 1d)
-   `start_time` (optional): Start timestamp (ms)
-   `end_time` (optional): End timestamp (ms)
-   `limit` (optional): Max records (default: 2000)

**Response Structure:**

```json
{
    "data": [
        {
            "time": 1745625600000,
            "exchange": "Binance",
            "symbol": "BTCUSDT",
            "open": -0.010085,
            "high": -0.004259,
            "low": -0.010085,
            "close": -0.005419,
            "interval_name": "4h"
        }
    ]
}
```

**Frontend Usage:**

-   `history-chart.blade.php` - Candlestick chart
-   `heatmap.blade.php` - Time-series data for grid
-   Overview aggregation - 8h resampling

## 🎨 Frontend Components

### Core Components

#### 1. **Bias Card** (`components/funding/bias-card.blade.php`)

**Purpose:** Display current market bias with visual indicators

**Features:**

-   Large bias indicator (LONG/SHORT/NEUTRAL) with gradient background
-   Strength meter (0-100%) with color coding
-   Average funding rate display
-   Trading insight message based on bias + strength
-   Auto-refresh every 30s

**Data Source:** `/api/funding-rate/bias`

**Visual Elements:**

-   Bias display: Green (long), Red (short), Gray (neutral)
-   Strength meter: Green (low), Yellow (moderate), Red (extreme)
-   Alert box: Info/Warning/Danger based on strength

#### 2. **Exchange Comparison** (`components/funding/exchange-comparison.blade.php`)

**Purpose:** Compare funding rates across exchanges

**Features:**

-   Horizontal bar chart showing top 15 exchanges
-   Color-coded bars (green = positive, red = negative)
-   Spread alert when difference > 0.5%
-   Auto-refresh on filter changes

**Data Source:** `/api/funding-rate/exchanges`

**Visual Elements:**

-   Bar chart (Chart.js)
-   Alert box for arbitrage opportunities

#### 3. **Analytics Insights** (`components/funding/analytics-insights.blade.php`)

**Purpose:** Display comprehensive analytics summary

**Features:**

-   Current funding rate + average/median
-   Volatility indicator + std dev/range
-   Trend card (increasing/decreasing/stable)
-   Extreme events alert (when > 3% of data is extreme)
-   API-provided insights with severity levels

**Data Source:** `/api/funding-rate/analytics`

**Visual Elements:**

-   Summary cards (2-column grid)
-   Trend alert (success/danger/secondary)
-   Extremes alert (danger)
-   Insight cards (info/warning/danger)

#### 4. **History Chart** (`components/funding/history-chart.blade.php`)

**Purpose:** OHLC candlestick chart of funding rate over time

**Features:**

-   Candlestick visualization
-   Color-coded (green = positive close, red = negative)
-   Hoverable tooltips with OHLC values
-   Time range selection

**Data Source:** `/api/funding-rate/history`

**Visual Elements:**

-   Candlestick chart (Chart.js with Financial plugin)

#### 5. **Exchange Table** (`components/funding/exchange-table.blade.php`)

**Purpose:** Detailed table view of all exchanges

**Features:**

-   Sortable columns
-   Color-coded funding rates
-   Next funding countdown
-   Interval display
-   Pagination support

**Data Source:** `/api/funding-rate/exchanges`

**Visual Elements:**

-   Bootstrap table with custom styling

#### 6. **Heatmap** (`components/funding/heatmap.blade.php`)

**Purpose:** Exchange × Time grid visualization

**Features:**

-   Matrix view of funding rates
-   Color intensity based on rate magnitude
-   Hover tooltips with exact values
-   Auto-updates from overview event

**Data Source:** Aggregated from `/api/funding-rate/history`

**Visual Elements:**

-   HTML table with background color gradients

### Supporting Components

#### Quick Stats Panel

**Location:** Inline in `derivatives/funding-rate.blade.php`

**Features:**

-   Funding trend (window average)
-   Snapshot average across exchanges
-   Market sentiment bar (long % vs short %)
-   Next funding countdown
-   Trading insight message

**Data Sources:**

-   `/api/funding-rate/bias` (window average)
-   `/api/funding-rate/exchanges` (snapshot average, next funding)
-   Overview event (aggregated analytics)

## 🔄 Data Flow Architecture

### 1. Initialization Flow

```
Page Load
    ↓
fundingRateController.init()
    ↓
loadOverview() [parallel fetch]
    ├── /analytics
    ├── /exchanges
    └── /history
    ↓
resampleToEightHours() [client-side]
    ↓
Emit 'funding-overview-ready' event
    ↓
All components listen & update
```

### 2. Filter Change Flow

```
User changes symbol/interval
    ↓
fundingRateController.updateSymbol()
    ↓
Emit 'symbol-changed' event
    ↓
Components listen & reload data
    ↓
loadOverview() [reload composite]
```

### 3. Component Refresh Flow

```
User clicks refresh button
    ↓
Component.refresh()
    ↓
Component.loadData()
    ↓
Fetch from backend
    ↓
Update local state
    ↓
Re-render visualization
```

## 📊 Client-Side Data Processing

### Overview Aggregation

The `fundingRateController` provides a `loadOverview()` method that:

1. Fetches `analytics`, `exchanges`, `history` in parallel
2. Normalizes numeric strings to floats
3. Resamples history to 8h buckets (aligned to 00:00, 08:00, 16:00 UTC)
4. Groups data by exchange
5. Emits `funding-overview-ready` event

### 8-Hour Resampling Algorithm

```javascript
resampleToEightHours(rows) {
    // Group raw data points into 8h buckets per exchange
    // Buckets aligned to 00:00, 08:00, 16:00 UTC
    // Compute OHLC for each bucket
    // Sort by timestamp
    return resampled;
}
```

**Purpose:** Convert arbitrary interval data (1h, 4h, etc.) into consistent 8h funding periods for comparison.

## 🎛️ Global Controls

### Symbol Selector

-   Default: BTC
-   Options: BTC, ETH, SOL, BNB, XRP, ADA, DOGE, MATIC, DOT, AVAX
-   Propagates to all components via `symbol-changed` event

### Margin Type Filter

-   Default: All
-   Options: All, Stablecoin, Token
-   Filters `/exchanges` endpoint results

### Interval Selector

-   Default: 1h
-   Currently only 1h is active (4h, 8h, 1d are disabled pending backend support)
-   Propagates to history-based components

### Refresh All Button

-   Manually triggers reload across all components
-   Emits `refresh-all` event

## 🔔 Event System

### Custom Events

| Event Name               | Payload                                                            | Purpose                    |
| ------------------------ | ------------------------------------------------------------------ | -------------------------- |
| `symbol-changed`         | `{symbol, marginType, interval}`                                   | Symbol filter changed      |
| `margin-type-changed`    | `{symbol, marginType, interval}`                                   | Margin type filter changed |
| `interval-changed`       | `{symbol, marginType, interval}`                                   | Interval filter changed    |
| `refresh-all`            | `{symbol, marginType}`                                             | Manual refresh triggered   |
| `funding-overview-ready` | `{meta, analytics, exchanges, timeseries, timeseries_by_exchange}` | Composite data ready       |

### Event Flow

```
Controller emits event
    ↓
Components listen via addEventListener
    ↓
Components extract data from event.detail
    ↓
Components update state & re-render
```

## 🎨 Visual Design Patterns

### Color Coding

| Element        | Positive (Long)     | Negative (Short)                | Neutral          |
| -------------- | ------------------- | ------------------------------- | ---------------- |
| Funding Rate   | Green (#22c55e)     | Red (#ef4444)                   | Gray (#6b7280)   |
| Bias Indicator | Green gradient      | Red gradient                    | Gray gradient    |
| Chart Bars     | Green (80% opacity) | Red (80% opacity)               | N/A              |
| Alert Severity | Info (blue)         | Warning (yellow) / Danger (red) | Secondary (gray) |

### Typography

-   **Headers:** H5, semi-bold
-   **Values:** H4 (large metrics), H5 (medium), small (labels)
-   **Descriptions:** Small, secondary color (#6b7280)

### Spacing

-   **Panel padding:** 1rem (p-3)
-   **Gap between elements:** 0.75rem (gap-3)
-   **Chart height:** 280-380px depending on component

## 📱 Responsive Behavior

### Breakpoints

-   **Desktop (≥992px):** 2-column charts, full sidebar
-   **Tablet (768-991px):** 2-column charts, collapsible sidebar
-   **Mobile (<768px):** 1-column stacked, overlay sidebar

### Mobile Optimizations

-   Horizontal scrolling for tables
-   Touch-friendly buttons (min 44px)
-   Simplified tooltips
-   Reduced chart heights

## ⚡ Performance Optimizations

### Caching Strategy

-   **Endpoint caching:** Browser HTTP cache respects API headers
-   **Component state:** Cached in Alpine.js reactive data
-   **Chart instances:** Reused and updated, not recreated

### Lazy Loading

-   Charts initialize after `window.chartJsReady` promise resolves
-   Components delay init by 500ms to stabilize layout

### Debouncing

-   Filter changes debounced by Alpine.js reactivity
-   No manual debounce needed for simple changes

### Data Limits

-   History: 2000 records max
-   Exchanges: 1000 records max, displayed top 15
-   Heatmap: Last 15 time buckets per exchange

## 🧪 Testing Checklist

### Functional Tests

-   [ ] Symbol change updates all components
-   [ ] Margin type filter applies to exchanges
-   [ ] Interval change updates history-based charts
-   [ ] Refresh all button reloads data
-   [ ] Bias card shows correct long/short/neutral
-   [ ] Exchange comparison displays top exchanges
-   [ ] Analytics insights show summary/trend/extremes
-   [ ] History chart renders candlesticks
-   [ ] Exchange table sorts correctly
-   [ ] Heatmap displays exchange × time grid
-   [ ] Quick stats calculate correctly
-   [ ] Alerts show for extreme conditions

### Visual Tests

-   [ ] Colors match design (green/red/gray)
-   [ ] Charts render without overlap
-   [ ] Text is readable on all backgrounds
-   [ ] Tooltips appear on hover
-   [ ] Loading spinners display during fetch
-   [ ] Responsive layout works on mobile

### Error Handling

-   [ ] API errors display gracefully
-   [ ] Empty data shows "no data" message
-   [ ] 404 endpoints don't crash page
-   [ ] Network timeout shows error
-   [ ] Invalid JSON handled

## 🔧 Configuration

### Environment Variables

```env
# Required
API_BASE_URL=http://202.155.90.20:8000

# Optional (Laravel defaults)
APP_NAME="DragonFortune AI"
APP_ENV=production
APP_DEBUG=false
```

### Services Config

`config/services.php`:

```php
'api' => [
    'base_url' => env('API_BASE_URL', ''),
],
```

## 📝 File Structure

```
resources/
├── views/
│   ├── derivatives/
│   │   └── funding-rate.blade.php          # Main dashboard
│   ├── components/
│   │   └── funding/
│   │       ├── bias-card.blade.php         # Bias indicator
│   │       ├── exchange-comparison.blade.php # Bar chart
│   │       ├── analytics-insights.blade.php  # Summary cards
│   │       ├── history-chart.blade.php      # Candlestick
│   │       ├── exchange-table.blade.php     # Table view
│   │       └── heatmap.blade.php            # Matrix grid
│   └── layouts/
│       └── app.blade.php                    # Layout with meta tag
├── js/
│   └── app.js                               # Alpine.js bootstrap
public/
└── js/
    └── funding-rate-controller.js           # Global controller
routes/
└── web.php                                  # Routes (view only)
config/
└── services.php                             # API config
```

## 🚀 Deployment Notes

### Production Checklist

-   [ ] Set `API_BASE_URL` in `.env`
-   [ ] Run `npm run build` to compile assets
-   [ ] Clear Laravel cache: `php artisan config:clear`
-   [ ] Test all endpoints are reachable
-   [ ] Verify CORS is configured on backend
-   [ ] Check SSL certificate for API

### Common Issues

**Issue:** API calls fail with CORS error
**Solution:** Backend must allow `Access-Control-Allow-Origin` for frontend domain

**Issue:** Charts don't render
**Solution:** Ensure Chart.js loads before Alpine initializes (check `window.chartJsReady`)

**Issue:** Data shows N/A
**Solution:** Check API response structure matches expected format

## 📚 References

-   [Chart.js Documentation](https://www.chartjs.org/docs/latest/)
-   [Alpine.js Documentation](https://alpinejs.dev/)
-   [Laravel Blade](https://laravel.com/docs/blade)
-   [Bootstrap 5](https://getbootstrap.com/docs/5.3/)

## 🤝 Contributing

When adding new visualizations:

1. Create component in `resources/views/components/funding/`
2. Add Alpine.js component function in `<script>` tag
3. Listen to global events (`symbol-changed`, `funding-overview-ready`)
4. Include in `derivatives/funding-rate.blade.php`
5. Update this documentation

## 📊 Future Enhancements

### Planned Features

-   [ ] Price correlation scatter plot (pending price endpoint)
-   [ ] Distribution histogram (client-side from history)
-   [ ] Multi-pair comparison view
-   [ ] Export data to CSV
-   [ ] Custom alerts configuration
-   [ ] Historical playback mode

### Backend Dependencies

-   [ ] Price history endpoint for correlation
-   [ ] Aggregated endpoints for better performance
-   [ ] WebSocket for real-time updates
-   [ ] User preferences storage

---

**Last Updated:** October 11, 2025
**Version:** 1.0.0
**Status:** ✅ Production Ready
