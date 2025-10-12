# Long/Short Ratio - Testing Guide

## 🚀 Quick Test After Bug Fixes

### Prerequisites

1. **Check Environment Variable**

```bash
# Verify API URL is set
grep API_BASE_URL .env
```

Should show:

```
API_BASE_URL=https://test.dragonfortune.ai
```

2. **Clear Caches**

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

3. **Rebuild Frontend Assets** (if modified)

```bash
npm run build
# or for development
npm run dev
```

---

## ✅ Testing Steps

### 1. Load the Dashboard

```
http://your-domain/derivatives/long-short-ratio
```

**Expected Result:**

-   ✅ Page loads without errors
-   ✅ No Alpine.js errors in console
-   ✅ Loading spinner appears briefly

---

### 2. Check Browser Console (F12)

**Look for these success messages:**

```javascript
✅ Long/Short Ratio Controller loaded
🚀 Initializing Long/Short Ratio dashboard...
✅ Chart.js loaded
Filter updated: symbol = BTCUSDT
Filter updated: interval = 1h
Filter updated: ratioType = accounts
📡 Fetching analytics from: https://test.dragonfortune.ai/api/long-short-ratio/analytics...
📡 Fetching top accounts from: https://test.dragonfortune.ai/api/long-short-ratio/top-accounts...
📊 Creating charts with X data points
✅ Charts created successfully
📊 Exchange data prepared: {Binance: {...}, Bybit: {...}, OKX: {...}}
```

**Should NOT see:**

-   ❌ `toFixed is not a function`
-   ❌ `insight is not defined`
-   ❌ `Canvas not found`
-   ❌ Any Alpine Expression Errors

---

### 3. Verify Visual Elements

#### A. Statistics Cards (Top Section)

**Check all 4 cards display:**

1. **Current Ratio** - Shows number with trend arrow (↑↓)
2. **Average Ratio** - Shows number with Min-Max
3. **Market Sentiment** - Shows Bullish/Bearish/Neutral with color
4. **Risk Level** - Shows Low/Medium/High with color

**Test:**

```javascript
// In console, check data is present
Alpine.$data(document.querySelector("[x-data]")).analytics;
```

#### B. Insights Panel

**If insights exist**, should show:

-   Alert boxes with colored backgrounds
-   Type badges (CONTRARIAN, TREND, etc)
-   Severity badges (high, medium, low)
-   Messages explaining the insight

**Test:**

```javascript
// Check insights data
Alpine.$data(document.querySelector("[x-data]")).analytics?.insights;
```

#### C. Main Ratio Chart

**Should display:**

-   Blue line showing L/S ratio over time
-   Gray dashed line at 1.0 (neutral)
-   Hover tooltips working
-   X-axis: Time labels
-   Y-axis: Ratio values

**Test:**

```javascript
// Check chart exists
Alpine.$data(document.querySelector("[x-data]")).controller.charts.mainChart;
```

#### D. Distribution Chart

**Should display:**

-   Green area for Long %
-   Red area for Short %
-   Stacked or overlapping areas
-   Hover tooltips working

**Test:**

```javascript
// Check chart exists
Alpine.$data(document.querySelector("[x-data]")).controller.charts.areaChart;
```

#### E. Exchange Comparison Table

**Should display:**

-   3 rows (Binance, Bybit, OKX)
-   Columns: Exchange, Pair, Ratio, Long %, Short %, Sentiment
-   Numbers properly formatted (e.g., "1.450", "58.10%")
-   Color-coded sentiment badges
-   No "NaN" or "undefined"

**Test:**

```javascript
// Check exchange data
Alpine.$data(document.querySelector("[x-data]")).exchangeData;
```

---

### 4. Test Filters

#### A. Symbol Filter

1. Click symbol dropdown
2. Select "ETHUSDT"
3. **Expected:**
    - Page reloads data
    - Charts update
    - Table updates
    - Console shows new fetch requests

#### B. Ratio Type Toggle

1. Click "Ratio Type" dropdown
2. Switch between "Accounts" and "Positions"
3. **Expected:**
    - Data reloads
    - Charts show different values
    - Table updates

#### C. Exchange Filter

1. Click "Exchange" dropdown
2. Select "Binance"
3. **Expected:**

    - Data filtered to Binance only
    - Exchange comparison table disappears (no multi-exchange when filtered)
    - Charts show Binance data only

4. Switch back to "All Exchanges"
5. **Expected:**
    - Table reappears with 3 exchanges

#### D. Interval Switcher

1. Click interval buttons: 15m, 1H, 4H, 1D
2. **Expected:**
    - Charts update to show different timeframes
    - Data reloads for selected interval

---

### 5. Test Auto-Refresh

1. Wait 60 seconds
2. **Expected:**
    - Console shows new fetch requests
    - Data updates silently (no loading spinner)
    - Charts refresh

**Test:**

```javascript
// Check last update time
const data = Alpine.$data(document.querySelector("[x-data]"));
console.log("Last update:", new Date(data.lastUpdate));
```

---

### 6. Test Manual Refresh

1. Click the refresh button (🔄)
2. **Expected:**
    - Loading spinner appears on button
    - Data reloads
    - Charts update
    - Spinner disappears

---

### 7. Test Error Handling

#### Simulate API Error:

1. Open DevTools > Network tab
2. Right-click any request > Block request URL
3. Click refresh
4. **Expected:**
    - Error message appears at top
    - "Failed to load data. Please check your connection..."
    - Console shows error logs
    - Dashboard doesn't crash

---

### 8. Test Responsive Design

#### Desktop (> 768px):

-   Charts side by side
-   Table visible
-   All filters in one row

#### Mobile (< 768px):

-   Charts stacked vertically
-   Table scrolls horizontally
-   Filters stack/wrap
-   Hamburger menu works

**Test in DevTools:**

```
Toggle device toolbar (Ctrl+Shift+M)
Try different screen sizes
```

---

## 🐛 Common Issues & Solutions

### Issue 1: Charts Don't Appear

**Debug:**

```javascript
// 1. Check Chart.js loaded
console.log("Chart.js:", typeof Chart);
// Should show: "function"

// 2. Check canvas elements
console.log("Main canvas:", document.getElementById("mainRatioChart"));
console.log("Area canvas:", document.getElementById("distributionChart"));
// Should NOT be null

// 3. Check data available
const data = Alpine.$data(document.querySelector("[x-data]"));
console.log("Timeseries:", data.timeseries?.length);
// Should show number > 0
```

**Solutions:**

-   Clear browser cache (Ctrl+F5)
-   Check network tab for failed requests
-   Verify API_BASE_URL in .env

---

### Issue 2: Table Shows "NaN" or "-"

**Debug:**

```javascript
const data = Alpine.$data(document.querySelector("[x-data]"));
console.log("Exchange data:", data.exchangeData);
// Check if values are numbers
```

**Solutions:**

-   API might be returning no data for that symbol/exchange
-   Try different symbol (BTCUSDT usually has data)
-   Check API directly: `curl https://test.dragonfortune.ai/api/long-short-ratio/top-accounts?symbol=BTCUSDT&limit=10`

---

### Issue 3: Filters Don't Work

**Debug:**

```javascript
const data = Alpine.$data(document.querySelector("[x-data]"));
console.log("Current filters:", {
    symbol: data.symbol,
    exchange: data.exchange,
    interval: data.interval,
    ratioType: data.ratioType,
});
```

**Solutions:**

-   Check if `loadData()` is being called
-   Look for errors in console
-   Verify network requests are triggered

---

## 📊 Expected Console Output (Success)

```
✅ Long/Short Ratio Controller loaded
🚀 Initializing Long/Short Ratio dashboard...
✅ Chart.js loaded
Filter updated: symbol = BTCUSDT
Filter updated: interval = 1h
Filter updated: ratioType = accounts
Filter updated: symbol = BTCUSDT
Filter updated: exchange = null
Filter updated: interval = 1h
Filter updated: ratioType = accounts
📡 Fetching analytics from: https://test.dragonfortune.ai/api/long-short-ratio/analytics?symbol=BTCUSDT&interval=1h&ratio_type=accounts&limit=2000
📡 Fetching top accounts from: https://test.dragonfortune.ai/api/long-short-ratio/top-accounts?symbol=BTCUSDT&interval=1h&limit=2000
Data loaded successfully: {analytics: {...}, timeseries: Array(100)}
📊 Creating charts with 100 data points
✅ Charts created successfully
📊 Exchange data prepared: {Binance: {...}, Bybit: {...}, OKX: {...}}
```

---

## 🎯 Success Criteria

Dashboard is working correctly if:

-   [x] ✅ No errors in console
-   [x] ✅ All 4 statistics cards show data
-   [x] ✅ Both charts render and display data
-   [x] ✅ Exchange comparison table has 3 rows with numbers
-   [x] ✅ All filters trigger data reload
-   [x] ✅ Charts update when filters change
-   [x] ✅ Auto-refresh works after 60s
-   [x] ✅ Manual refresh button works
-   [x] ✅ Insights panel shows when data available
-   [x] ✅ Responsive design works on mobile
-   [x] ✅ Error handling shows user-friendly messages
-   [x] ✅ Loading states appear appropriately

---

## 📞 Support

### Get Alpine.js State:

```javascript
Alpine.$data(document.querySelector("[x-data]"));
```

### Get Controller Instance:

```javascript
Alpine.$data(document.querySelector("[x-data]")).controller;
```

### Force Reload:

```javascript
Alpine.$data(document.querySelector("[x-data]")).loadData();
```

### Check Charts:

```javascript
const ctrl = Alpine.$data(document.querySelector("[x-data]")).controller;
console.log("Main chart:", ctrl.charts.mainChart);
console.log("Area chart:", ctrl.charts.areaChart);
```

---

**Testing Date**: October 11, 2025  
**Bug Fixes Applied**: All critical issues resolved  
**Status**: ✅ **READY FOR TESTING**

Happy Testing! 🚀📈
