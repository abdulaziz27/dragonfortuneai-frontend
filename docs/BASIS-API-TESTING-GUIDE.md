# Basis & Term Structure - Testing Guide

## 🧪 Quick Testing Guide

### Pre-requisites

1. Backend API harus running di `https://test.dragonfortune.ai`
2. Set `API_BASE_URL` di `.env`:
    ```
    API_BASE_URL=https://test.dragonfortune.ai
    ```
3. Laravel development server running: `php artisan serve`

---

## ✅ Testing Checklist

### 1. Basic Page Load

**Steps:**

1. Buka browser ke `/derivatives/basis-term-structure`
2. Buka Console (F12)
3. Cek log initialization

**Expected Output:**

```
🚀 Basis & Term Structure Dashboard initialized
📊 Symbol: BTC
🏢 Exchange: Binance
✅ Basis & Term Structure Controller loaded
```

**Expected Behavior:**

-   ✅ Page load tanpa error
-   ✅ Semua komponen terlihat (tidak ada yang broken)
-   ✅ Charts terlihat (meskipun mungkin masih loading)

---

### 2. API Fetching

**Steps:**

1. Monitor console untuk API calls
2. Tunggu beberapa detik untuk loading selesai

**Expected Output:**

```
📡 Fetching: analytics {exchange: "Binance", spot_pair: "BTCUSDT", ...}
✅ Received: analytics summary
📡 Fetching: history {exchange: "Binance", spot_pair: "BTCUSDT", ...}
✅ Received: history 2000 items
📡 Fetching: term-structure {exchange: "Binance", spot_pair: "BTCUSDT"}
✅ Received: term-structure 5
```

**Expected Behavior:**

-   ✅ No 404 errors
-   ✅ No CORS errors
-   ✅ Data received successfully

---

### 3. Chart Display

**Steps:**

1. Scroll ke Basis History Chart
2. Verify chart shows data (bukan kosong)
3. Hover over data points

**Expected Behavior:**

-   ✅ Line chart dengan data points
-   ✅ Blue line untuk basis values
-   ✅ Gray dashed line untuk zero reference
-   ✅ Tooltip shows values saat hover
-   ✅ X-axis menampilkan timestamps
-   ✅ Y-axis menampilkan currency format ($)

**Visual Check:**

```
[Chart menampilkan line chart dengan nilai basis yang berfluktuasi]
[Ada zero line sebagai reference]
[Tooltip muncul saat hover dengan format: "Basis (Absolute): $150.25"]
```

---

### 4. Term Structure Chart

**Steps:**

1. Scroll ke Term Structure Chart
2. Verify bar chart shows multiple contracts
3. Check color coding

**Expected Behavior:**

-   ✅ Bar chart dengan multiple bars (satu per expiry)
-   ✅ Color coding:
    -   Hijau = Positive basis (Contango)
    -   Merah = Negative basis (Backwardation)
    -   Abu-abu = Neutral
-   ✅ X-axis menampilkan expiry dates
-   ✅ Y-axis menampilkan basis values
-   ✅ Tooltip shows basis dan annualized basis

**Visual Check:**

```
[Bar chart dengan 3-5 bars]
[Warna bar sesuai dengan nilai basis]
[Label menampilkan "Jun 2024", "Sep 2024", etc]
```

---

### 5. Market Structure Card

**Steps:**

1. Check Market Structure Overview card
2. Verify badges and values

**Expected Behavior:**

-   ✅ Market structure badge (Contango/Backwardation)
-   ✅ Trend badge (Widening/Narrowing/Stable)
-   ✅ Current Basis value (dengan warna sesuai)
-   ✅ Annualized Basis (dengan %)
-   ✅ Basis Range
-   ✅ Basis Volatility
-   ✅ Market insight alert dengan message relevan

**Visual Check:**

```
Market Structure Overview
[Contango] [Widening]

Current Basis    Annualized Basis    Basis Range    Volatility
$150.25          +3.65%              $500.00        $75.50

📈 Contango Market
Futures trading above spot ($150.25). Market expects higher prices...
```

---

### 6. Quick Stats Panel

**Steps:**

1. Check Quick Stats panel di sebelah chart
2. Verify distribution bar

**Expected Behavior:**

-   ✅ Basis Distribution bar (Green/Red split)
-   ✅ Percentage values yang masuk akal
-   ✅ Average Basis value
-   ✅ Data Points count
-   ✅ Time Range duration

**Visual Check:**

```
📈 Quick Stats

Basis Distribution
[Positive 65%][Negative 35%]

Average Basis
$125.50

Data Points
2000
83h duration
```

---

### 7. Analytics Table

**Steps:**

1. Scroll ke Basis Analytics Summary table
2. Check all metrics rows

**Expected Behavior:**

-   ✅ Table dengan multiple rows
-   ✅ Metrics: Current Basis, Average Basis, Range, Std Dev, etc
-   ✅ Values dengan format yang benar
-   ✅ Signal badges (Contango, Backwardation, Volatility, etc)
-   ✅ Color coding sesuai dengan nilai

**Visual Check:**

```
Basis Analytics Summary

Metric              Value      Description                    Signal
Current Basis       $150.25    Current absolute basis value   Contango
Average Basis       $125.50    Historical average basis       Contango
Basis Range         $500.00    Range between min and max      Volatility
...
```

---

### 8. Filter Changes

#### 8.1 Change Symbol

**Steps:**

1. Select "Ethereum" dari dropdown symbol
2. Wait for data refresh
3. Check console logs

**Expected Output:**

```
🔄 Updating symbol to: ETH
📡 Fetching: analytics {spot_pair: "ETHUSDT", ...}
✅ Received: analytics summary
...
```

**Expected Behavior:**

-   ✅ All components reload with ETH data
-   ✅ Charts update dengan data baru
-   ✅ Stats panel update
-   ✅ URL update to include `?symbol=ETH`

#### 8.2 Change Exchange

**Steps:**

1. Select "Bybit" dari dropdown exchange
2. Wait for data refresh

**Expected Behavior:**

-   ✅ All components reload with Bybit data
-   ✅ Data mungkin berbeda dari Binance
-   ✅ URL update to include `?exchange=Bybit`

#### 8.3 Change Interval

**Steps:**

1. Select "4 Hours" dari dropdown interval
2. Wait for data refresh

**Expected Behavior:**

-   ✅ Basis History Chart update dengan interval 4h
-   ✅ Less data points tapi range yang lebih panjang
-   ✅ URL update to include `?interval=4h`

---

### 9. Refresh All Button

**Steps:**

1. Click "🔄 Refresh All" button
2. Watch loading spinner
3. Verify data refresh

**Expected Behavior:**

-   ✅ Button shows loading spinner
-   ✅ All components reload data
-   ✅ Console shows multiple fetch requests
-   ✅ Button returns to normal after ~2 seconds

---

### 10. Auto-refresh

**Steps:**

1. Wait 30 seconds tanpa interaksi
2. Monitor console logs

\*\*Expected Output (after 30s):

```
📡 Fetching: analytics ...
📡 Fetching: history ...
📡 Fetching: term-structure ...
```

**Expected Behavior:**

-   ✅ Data auto-refresh setiap 30 detik
-   ✅ Charts smoothly update
-   ✅ No page reload
-   ✅ No visual glitches

---

## 🐛 Common Issues & Solutions

### Issue 1: Charts Empty / No Data

**Symptoms:**

-   Charts display but no data
-   Console shows "❌ Error loading..."

**Solutions:**

1. Check API_BASE_URL di `.env`
2. Verify backend API is running
3. Check network tab for failed requests
4. Verify API returns valid JSON

### Issue 2: CORS Error

**Symptoms:**

```
Access to fetch at 'https://test.dragonfortune.ai/api/basis/...'
from origin 'http://localhost:8000' has been blocked by CORS policy
```

**Solutions:**

1. Backend needs to allow CORS from localhost
2. Add these headers di backend:
    ```
    Access-Control-Allow-Origin: *
    Access-Control-Allow-Methods: GET, POST, OPTIONS
    Access-Control-Allow-Headers: Content-Type
    ```

### Issue 3: Chart.js Not Defined

**Symptoms:**

```
Uncaught ReferenceError: Chart is not defined
```

**Solutions:**

1. Check Chart.js loaded di page source
2. Verify CDN URLs accessible
3. Clear browser cache
4. Check internet connection

### Issue 4: Alpine.js Component Not Initializing

**Symptoms:**

-   Components don't load
-   `x-data` not working

**Solutions:**

1. Check Alpine.js loaded
2. Verify no JavaScript errors before Alpine loads
3. Check console for Alpine errors
4. Try refresh page

### Issue 5: Data Mismatch Between Components

**Symptoms:**

-   Chart shows different data than stats
-   Inconsistent values

**Solutions:**

1. Check if all components using same filters
2. Verify event listeners working
3. Check `basis-overview-ready` event dispatched
4. Verify data transformation logic

---

## 📊 Sample Test Results

### Success Case:

```
✅ Page Load: OK
✅ API Analytics: 200 OK (1.2s)
✅ API History: 200 OK (2.5s) - 2000 records
✅ API Term Structure: 200 OK (0.8s) - 5 contracts
✅ Basis History Chart: Displayed with 2000 points
✅ Term Structure Chart: 5 bars with correct colors
✅ Market Structure: Contango, Widening
✅ Quick Stats: 65% Positive, 35% Negative
✅ Analytics Table: 10 metrics displayed
✅ Symbol Change: OK (ETH loaded in 2.1s)
✅ Exchange Change: OK (Bybit loaded in 2.3s)
✅ Interval Change: OK (4h loaded in 1.8s)
✅ Refresh All: OK
✅ Auto-refresh: OK (after 30s)

Overall: PASS ✅
```

### Failure Case:

```
✅ Page Load: OK
❌ API Analytics: 404 Not Found
❌ API History: 500 Internal Server Error
❌ API Term Structure: Timeout
⚠️  Basis History Chart: Empty (no data)
⚠️  Term Structure Chart: Empty (no data)
⚠️  Market Structure: Loading...
❌ Quick Stats: N/A values

Overall: FAIL ❌
Action: Check backend API status
```

---

## 🔍 Advanced Testing

### Performance Testing

```javascript
// Measure chart rendering time
console.time("chartRender");
// ... chart update ...
console.timeEnd("chartRender");
// Expected: < 100ms for 2000 points
```

### Memory Leak Testing

1. Open page
2. Change filters 50x rapidly
3. Check Chrome DevTools Memory Profile
4. Expected: No significant memory increase

### Stress Testing

1. Set limit=10000 in API calls
2. Verify chart still renders
3. Check performance

---

## 📝 Manual Test Report Template

```
Test Date: __________
Tester: __________
Environment: Development / Staging / Production

[ ] 1. Basic Page Load
[ ] 2. API Fetching
[ ] 3. Basis History Chart
[ ] 4. Term Structure Chart
[ ] 5. Market Structure Card
[ ] 6. Quick Stats Panel
[ ] 7. Analytics Table
[ ] 8. Symbol Filter
[ ] 9. Exchange Filter
[ ] 10. Interval Filter
[ ] 11. Refresh Button
[ ] 12. Auto-refresh

Issues Found:
1. _______________
2. _______________

Screenshots:
- [ ] Attached

Overall Status: PASS / FAIL
Notes: _______________
```

---

## 🎯 Acceptance Criteria

Dashboard dianggap **READY FOR PRODUCTION** jika:

✅ All API calls return 200 OK  
✅ All charts display real data (no dummy data)  
✅ All filters work correctly  
✅ No console errors  
✅ Auto-refresh works  
✅ Responsive on mobile/tablet  
✅ Performance acceptable (< 3s initial load)  
✅ Data accuracy verified against API responses  
✅ Color coding correct (contango/backwardation)  
✅ Tooltips informative and accurate

---

**Last Updated:** 2025-10-11  
**Version:** 1.0  
**Status:** Ready for Testing
