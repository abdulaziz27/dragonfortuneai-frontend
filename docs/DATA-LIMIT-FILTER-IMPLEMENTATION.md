# Data Limit Filter Implementation

## Overview

Implementasi filter data limit untuk mengontrol jumlah data yang diambil dari API pada halaman Perp-Quarterly Spread dan Basis & Term Structure.

## Filter Options

### Available Limit Values:

-   **100**: Untuk testing cepat dan preview data
-   **500**: Untuk analisis ringan
-   **1,000**: Untuk analisis standar
-   **2,000**: Default - untuk analisis lengkap
-   **5,000**: Untuk analisis mendalam
-   **10,000**: Untuk analisis komprehensif

## Implementation Details

### 1. Perp-Quarterly Spread

#### Filter UI

```html
<!-- Data Limit -->
<select
    class="form-select"
    style="width: 120px;"
    x-model="globalLimit"
    @change="updateLimit()"
>
    <option value="100">100</option>
    <option value="500">500</option>
    <option value="1000">1,000</option>
    <option value="2000">2,000</option>
    <option value="5000">5,000</option>
    <option value="10000">10,000</option>
</select>
```

#### Controller Updates

-   Added `globalLimit: "2000"` state
-   Added `updateLimit()` function
-   Updated all event dispatchers to include `limit` parameter
-   Updated `loadOverview()` to use dynamic limit

#### Component Updates

All components now support dynamic limit:

-   `analytics-card.blade.php`
-   `spread-history-chart.blade.php`
-   `insights-panel.blade.php`
-   `spread-table.blade.php`

### 2. Basis & Term Structure

#### Filter UI

```html
<!-- Data Limit -->
<select
    class="form-select"
    style="width: 120px;"
    x-model="globalLimit"
    @change="updateLimit()"
>
    <option value="100">100</option>
    <option value="500">500</option>
    <option value="1000">1,000</option>
    <option value="2000">2,000</option>
    <option value="5000">5,000</option>
    <option value="10000">10,000</option>
</select>
```

#### Controller Updates

-   Added `globalLimit: "2000"` state
-   Added `updateLimit()` function
-   Updated all event dispatchers to include `limit` parameter
-   Updated `loadOverview()` to use dynamic limit

#### Component Updates

All components now support dynamic limit:

-   `marketStructureCard()` - inline component
-   `quickStatsPanel()` - inline component
-   `analyticsTable()` - inline component
-   `basisHistoryChart()` - separate component
-   `termStructureChart()` - separate component

## API Parameter Usage

### Perp-Quarterly API

```javascript
const params = new URLSearchParams({
    exchange: this.exchange,
    base: this.symbol,
    quote: this.quote,
    interval: this.interval,
    limit: this.limit, // Dynamic limit
    perp_symbol: actualPerpSymbol,
});
```

### Basis API

```javascript
const params = new URLSearchParams({
    exchange: this.exchange,
    spot_pair: `${this.symbol}USDT`,
    futures_symbol: `${this.symbol}USDT`,
    interval: "5m",
    limit: this.limit, // Dynamic limit
});
```

## Event System

### Event Broadcasting

All filter changes now broadcast `limit` parameter:

```javascript
window.dispatchEvent(
    new CustomEvent("limit-changed", {
        detail: {
            symbol: this.globalSymbol,
            exchange: this.globalExchange,
            interval: this.globalInterval,
            limit: this.globalLimit,
        },
    })
);
```

### Event Listening

All components listen for limit changes:

```javascript
window.addEventListener("limit-changed", (e) => {
    this.limit = e.detail?.limit || this.limit;
    this.loadData();
});
```

## Performance Considerations

### Chart Performance

-   Limited data points prevent stack overflow errors
-   Disabled animations for large datasets
-   Chart destruction before re-initialization

### API Performance

-   Smaller limits = faster API responses
-   Larger limits = more comprehensive data
-   Default 2000 provides good balance

## Usage Examples

### 1. Quick Preview (100 points)

-   Use for testing and quick data preview
-   Fastest loading time
-   Limited historical context

### 2. Standard Analysis (2,000 points)

-   Default setting
-   Good balance of speed and data depth
-   Suitable for most trading analysis

### 3. Deep Analysis (10,000 points)

-   Maximum data depth
-   Slower loading but comprehensive
-   Best for detailed historical analysis

## Testing

### Console Output

After implementing limit filter, console will show:

```
🚀 Perp-Quarterly Spread Dashboard initialized
📊 Base: BTC
💰 Quote: USDT
🏦 Exchange: Binance
⏱️ Interval: 5m
🔧 Perp Symbol Override: auto-generated
📈 Data Limit: 2000
🔄 Updating data limit to: 5000
```

### API Calls

API calls will include dynamic limit:

```
📡 Fetching Perp-Quarterly Analytics: /api/perp-quarterly/analytics?exchange=Binance&base=BTC&quote=USDT&interval=5m&limit=5000&perp_symbol=BTCUSDT
```

## Files Modified

### Perp-Quarterly Spread

1. `resources/views/derivatives/perp-quarterly-spread.blade.php` - Added limit filter UI
2. `public/js/perp-quarterly-controller.js` - Added limit state and functions
3. `resources/views/components/perp-quarterly/analytics-card.blade.php` - Added limit support
4. `resources/views/components/perp-quarterly/spread-history-chart.blade.php` - Added limit support
5. `resources/views/components/perp-quarterly/insights-panel.blade.php` - Added limit support
6. `resources/views/components/perp-quarterly/spread-table.blade.php` - Added limit support

### Basis & Term Structure

1. `resources/views/derivatives/basis-term-structure.blade.php` - Added limit filter UI
2. `public/js/basis-term-structure-controller.js` - Added limit state and functions
3. `resources/views/components/basis/history-chart.blade.php` - Added limit support
4. `resources/views/components/basis/term-structure-chart.blade.php` - Added limit support

## Status

✅ **COMPLETED** - Data limit filter successfully implemented on both Perp-Quarterly Spread and Basis & Term Structure pages. All components now support dynamic data limits with proper event handling and API integration.
