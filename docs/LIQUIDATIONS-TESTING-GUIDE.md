# 🧪 Liquidations Module - Testing Guide

## Pre-Testing Checklist

### Environment Setup

-   [ ] Laravel application running (`php artisan serve`)
-   [ ] NPM assets compiled (`npm run dev` or `npm run build`)
-   [ ] API base URL configured in `.env`:
    ```env
    API_BASE_URL=http://202.155.90.20:8000
    ```
-   [ ] Browser console open (F12) for debugging

---

## 🚀 Quick Smoke Test (5 minutes)

### 1. Basic Navigation

```bash
✓ Navigate to: http://localhost:8000/derivatives/liquidations
✓ Page loads without errors
✓ All components visible
✓ No console errors (check F12)
```

**Expected Result:**

-   Dashboard loads completely
-   6 components visible: Analytics, Historical Chart, Stream, Heatmap, Exchange Comparison, Coin List
-   Loading spinners appear briefly then disappear
-   Data populates in all components

### 2. Global Filters Test

```bash
✓ Change Symbol from BTC to ETH
✓ All components update with new data
✓ Change Exchange filter to "Binance"
✓ Data filters appropriately
✓ Change Interval to "5m"
✓ Charts update with new interval
✓ Click "Refresh All" button
✓ Loading spinner appears, data refreshes
```

**Expected Result:**

-   Each filter change triggers data update
-   Loading indicators appear during fetch
-   New data displays correctly
-   No errors in console

### 3. Quick Visual Check

```bash
✓ Analytics Summary shows numbers (not N/A)
✓ Historical Chart displays line/bar chart
✓ Liquidation Stream shows recent orders
✓ Heatmap shows stacked bars
✓ Exchange Comparison shows bar chart
✓ Coin List Table has data rows
```

---

## 🔍 Detailed Component Testing

### Component 1: Analytics Summary

**Test Cases:**

1. **Stats Display**

    - [ ] Total Liquidations shows USD value
    - [ ] Long Liquidations shows USD value (red)
    - [ ] Short Liquidations shows USD value (green)
    - [ ] Percentages add up to 100%

2. **Long/Short Ratio**

    - [ ] Ratio number displays (e.g., "2.23x")
    - [ ] Visual progress bar shows proportions
    - [ ] Colors correct (red for long, green for short)
    - [ ] Badge color changes based on ratio:
        - Red if ratio > 2
        - Orange if ratio > 1.5
        - Green if ratio < 0.5

3. **Cascade Detection**

    - [ ] Cascade count displays
    - [ ] Icon changes based on severity:
        - ✅ if 0 cascades
        - ⚡ if 1-20 cascades
        - ⚠️ if 21-50 cascades
        - 🚨 if 50+ cascades
    - [ ] Alert color matches severity
    - [ ] Message describes cascade situation

4. **Top Events**

    - [ ] Shows up to 3 largest liquidations
    - [ ] Displays timestamp and amount
    - [ ] Sorted by size (largest first)

5. **AI Insights**
    - [ ] At least one insight displayed
    - [ ] Icon matches insight type
    - [ ] Severity color appropriate
    - [ ] Message is readable and actionable

**Manual Test:**

```javascript
// In browser console
let summary = document.querySelector(
    '[x-data*="liquidationsAnalyticsSummary"]'
);
console.log("Analytics Summary Component:", summary.__x.$data);
```

---

### Component 2: Historical Chart

**Test Cases:**

1. **Chart Rendering**

    - [ ] Canvas element exists
    - [ ] Chart displays data
    - [ ] X-axis shows time labels
    - [ ] Y-axis shows USD values
    - [ ] Two lines/bars: Long (red) and Short (green)

2. **Chart Type Switcher**

    - [ ] Select "Line Chart" → shows lines
    - [ ] Select "Bar Chart" → shows bars
    - [ ] Select "Area Chart" → shows filled areas
    - [ ] No errors during switch

3. **Stats Cards**

    - [ ] Data Points count displays
    - [ ] Avg Long value shows
    - [ ] Avg Short value shows
    - [ ] Peak Total value shows

4. **Hover Interactions**
    - [ ] Tooltip appears on hover
    - [ ] Shows timestamp and values
    - [ ] Follows cursor

**Manual Test:**

```javascript
// Check if chart exists
console.log("Chart instance:", Chart.getChart("historicalCanvas"));
```

---

### Component 3: Liquidation Stream

**Test Cases:**

1. **Stream Display**

    - [ ] Shows recent liquidation orders
    - [ ] Each order has: badge, exchange, time, pair, amount, price
    - [ ] Color coding: Red for Long, Green for Short
    - [ ] Orders sorted by timestamp (newest first)

2. **Filters**

    - [ ] "All Sides" shows long and short
    - [ ] "Long Only" filters to long orders
    - [ ] "Short Only" filters to short orders
    - [ ] Exchange filter works correctly

3. **Stats Bar**

    - [ ] Total Orders count displays
    - [ ] Avg Size calculated correctly
    - [ ] Largest order shows highest value

4. **Auto-Refresh**
    - [ ] Stream updates every 10 seconds
    - [ ] Pulse dot animating
    - [ ] New orders appear smoothly

**Manual Test:**

```javascript
// Check stream data
let stream = document.querySelector('[x-data*="liquidationsStream"]');
console.log("Stream orders:", stream.__x.$data.orders.length);
```

---

### Component 4: Heatmap Chart

**Test Cases:**

1. **Heatmap Rendering**

    - [ ] Stacked bar chart displays
    - [ ] Multiple exchanges visible
    - [ ] Time buckets on x-axis
    - [ ] USD values on y-axis

2. **Legend**

    - [ ] Red square = Long Liquidations
    - [ ] Green square = Short Liquidations
    - [ ] Legend matches chart colors

3. **Interactions**
    - [ ] Hover shows tooltip with exchange and values
    - [ ] No overlapping labels

**Manual Test:**

```javascript
// Check heatmap chart
console.log("Heatmap Chart:", Chart.getChart("heatmapCanvas"));
```

---

### Component 5: Exchange Comparison

**Test Cases:**

1. **Tab Switching**

    - [ ] Click "1H" tab → shows 1h data
    - [ ] Click "4H" tab → shows 4h data
    - [ ] Click "12H" tab → shows 12h data
    - [ ] Click "24H" tab → shows 24h data
    - [ ] Active tab highlighted

2. **Chart Display**

    - [ ] Bar chart shows exchanges
    - [ ] Two bars per exchange (Long/Short)
    - [ ] Legend displays correctly
    - [ ] Values formatted as USD

3. **Stats Grid**
    - [ ] Shows top exchanges
    - [ ] Displays rank badge (#1, #2, etc.)
    - [ ] Shows percentage of total
    - [ ] Sorted by volume

**Manual Test:**

```javascript
// Check comparison data
let comparison = document.querySelector(
    '[x-data*="liquidationsExchangeComparison"]'
);
console.log("Selected Range:", comparison.__x.$data.selectedRange);
console.log("Top Exchanges:", comparison.__x.$data.topExchanges);
```

---

### Component 6: Coin List Table

**Test Cases:**

1. **Time Range Buttons**

    - [ ] "1 Hour" button → shows 1h data
    - [ ] "4 Hours" button → shows 4h data
    - [ ] "12 Hours" button → shows 12h data
    - [ ] "24 Hours" button → shows 24h data
    - [ ] Active button highlighted (primary color)

2. **Table Display**

    - [ ] Headers: Exchange, Total, Long, Short, Ratio
    - [ ] Data rows populated
    - [ ] Long column in red
    - [ ] Short column in green
    - [ ] Ratio badge colored based on value

3. **Summary Stats**

    - [ ] Total Across Exchanges calculated
    - [ ] Long total in red
    - [ ] Short total in green

4. **Sorting**
    - [ ] Rows sorted by Total descending
    - [ ] Largest exchange at top

**Manual Test:**

```javascript
// Check table data
let table = document.querySelector('[x-data*="liquidationsCoinListTable"]');
console.log("Selected Range:", table.__x.$data.selectedRange);
console.log("Displayed Data:", table.__x.$data.displayedData);
```

---

## 🌐 API Integration Testing

### Test All Endpoints

Open browser console and run:

```javascript
// Test analytics endpoint
fetch(
    "http://202.155.90.20:8000/api/liquidations/analytics?symbol=BTCUSDT&interval=1m&limit=2000"
)
    .then((r) => r.json())
    .then((d) => console.log("✓ Analytics:", d));

// Test coin-list endpoint
fetch(
    "http://202.155.90.20:8000/api/liquidations/coin-list?symbol=BTC&limit=1000"
)
    .then((r) => r.json())
    .then((d) => console.log("✓ Coin List:", d));

// Test exchange-list endpoint
fetch(
    "http://202.155.90.20:8000/api/liquidations/exchange-list?symbol=BTC&range_str=1h"
)
    .then((r) => r.json())
    .then((d) => console.log("✓ Exchange List:", d));

// Test orders endpoint
fetch("http://202.155.90.20:8000/api/liquidations/orders?limit=500")
    .then((r) => r.json())
    .then((d) => console.log("✓ Orders:", d));

// Test pair-history endpoint
fetch(
    "http://202.155.90.20:8000/api/liquidations/pair-history?symbol=BTCUSDT&interval=1m&limit=2000&with_price=true"
)
    .then((r) => r.json())
    .then((d) => console.log("✓ Pair History:", d));
```

**Expected:**

-   All 5 console logs appear
-   Each shows data object
-   No 404 or 500 errors

---

## 📱 Responsive Testing

### Desktop (>= 992px)

-   [ ] Full layout displays
-   [ ] 3-column grid visible
-   [ ] All charts readable
-   [ ] Filters in single row

### Tablet (768-991px)

-   [ ] 2-column layout
-   [ ] Charts resize appropriately
-   [ ] Filters wrap to two rows
-   [ ] Touch targets adequate

### Mobile (< 768px)

-   [ ] Single column stack
-   [ ] Filters stack vertically
-   [ ] Charts resize to fit
-   [ ] Text remains readable
-   [ ] No horizontal scroll

**Test Method:**

```bash
1. Open DevTools (F12)
2. Toggle device toolbar (Ctrl+Shift+M)
3. Test these widths:
   - 1920px (Desktop)
   - 1024px (Tablet)
   - 768px (Tablet)
   - 375px (Mobile)
```

---

## ⚡ Performance Testing

### Load Time

```bash
1. Open DevTools → Network tab
2. Hard refresh (Ctrl+Shift+R)
3. Check metrics:
   - [ ] DOM Content Loaded < 1s
   - [ ] Load Event < 2s
   - [ ] All API calls < 3s total
```

### Memory Usage

```bash
1. Open DevTools → Performance
2. Record session for 60 seconds
3. Check:
   - [ ] No memory leaks
   - [ ] Heap size stable
   - [ ] No excessive GC
```

### Chart Performance

```bash
1. Switch chart types rapidly
2. Change filters multiple times
3. Check:
   - [ ] No lag or freeze
   - [ ] Smooth transitions
   - [ ] No visual glitches
```

---

## 🔄 Event System Testing

### Global Events

```javascript
// Listen to all liquidations events
[
    "liquidations-overview-ready",
    "symbol-changed",
    "exchange-changed",
    "interval-changed",
    "refresh-all",
].forEach((event) => {
    window.addEventListener(event, (e) => {
        console.log(`✓ Event: ${event}`, e.detail);
    });
});

// Now trigger filter changes and watch console
```

**Expected:**

-   Each filter change triggers appropriate event
-   Event detail contains correct data
-   Components respond to events

---

## 🐛 Error Handling Testing

### API Failure Scenarios

1. **Network Offline**

    ```bash
    1. Open DevTools → Network tab
    2. Set "Throttling" to "Offline"
    3. Refresh page
    4. Check: Error handled gracefully, no crash
    ```

2. **API Timeout**

    ```bash
    1. Set "Throttling" to "Slow 3G"
    2. Refresh page
    3. Check: Loading states visible, eventual load
    ```

3. **Invalid Symbol**
    ```javascript
    // In console
    let controller = document.querySelector(
        '[x-data*="liquidationsController"]'
    );
    controller.__x.$data.globalSymbol = "INVALID";
    controller.__x.$data.updateSymbol();
    // Check: No crash, components show "No data" state
    ```

---

## ✅ Final Acceptance Criteria

### Must Pass

-   [ ] All 6 components render
-   [ ] All 6 API endpoints returning data
-   [ ] Filters work (symbol, exchange, interval)
-   [ ] Charts display correctly
-   [ ] Real-time updates functioning
-   [ ] No console errors
-   [ ] Responsive on mobile
-   [ ] Load time < 2 seconds
-   [ ] Documentation accessible

### Nice to Have

-   [ ] Smooth animations
-   [ ] Hover states working
-   [ ] Tooltips readable
-   [ ] Colors consistent
-   [ ] Auto-refresh seamless

---

## 📋 Test Report Template

```markdown
## Liquidations Module Test Report

**Date:** [Date]
**Tester:** [Name]
**Browser:** [Chrome/Firefox/Safari/Edge]
**Version:** [Browser version]

### Test Results

#### Smoke Test

-   Basic Navigation: ✅ / ❌
-   Global Filters: ✅ / ❌
-   Visual Check: ✅ / ❌

#### Component Tests

-   Analytics Summary: ✅ / ❌
-   Historical Chart: ✅ / ❌
-   Liquidation Stream: ✅ / ❌
-   Heatmap Chart: ✅ / ❌
-   Exchange Comparison: ✅ / ❌
-   Coin List Table: ✅ / ❌

#### Integration Tests

-   API Endpoints: ✅ / ❌
-   Event System: ✅ / ❌
-   Error Handling: ✅ / ❌

#### Performance

-   Load Time: [X]s
-   Memory Usage: [X]MB
-   No Memory Leaks: ✅ / ❌

#### Responsive

-   Desktop: ✅ / ❌
-   Tablet: ✅ / ❌
-   Mobile: ✅ / ❌

### Issues Found

1. [Issue description]
2. [Issue description]

### Overall Status: ✅ PASS / ❌ FAIL

### Notes

[Any additional observations]
```

---

## 🔧 Debug Commands

```javascript
// Check if controller loaded
typeof liquidationsController;

// Get controller instance
let ctrl = document.querySelector('[x-data*="liquidationsController"]').__x
    .$data;

// View overview data
console.table(ctrl.overview?.meta);

// Check specific component
let component = document.querySelector(
    '[x-data*="liquidationsAnalyticsSummary"]'
);
console.log(component.__x.$data);

// Monitor API calls
performance
    .getEntriesByType("resource")
    .filter((r) => r.name.includes("liquidations"))
    .forEach((r) => console.log(`${r.name}: ${r.duration}ms`));

// Check Chart.js version
console.log("Chart.js version:", Chart.version);
```

---

## 📞 Support

If any tests fail:

1. Check browser console for errors
2. Verify `.env` has correct `API_BASE_URL`
3. Confirm Laravel server running
4. Clear browser cache and retry
5. Check documentation for troubleshooting

---

**Testing Checklist Version:** 1.0
**Last Updated:** October 11, 2025
