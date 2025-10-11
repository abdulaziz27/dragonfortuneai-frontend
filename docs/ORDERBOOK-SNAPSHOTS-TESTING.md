# Orderbook Snapshots - Testing & Verification Guide

## 🧪 Testing Checklist

### Pre-Testing Setup

1. **Verify API Configuration**

```bash
# Check if API_BASE_URL is set in .env
cat .env | grep API_BASE_URL
```

2. **Start Laravel Server**

```bash
php artisan serve
```

3. **Access Dashboard**

```
http://localhost:8000/spot-microstructure/orderbook-snapshots
```

## ✅ Component Testing

### 1. Book Pressure Card

**Expected Behavior**:

-   [ ] Card loads with spinner initially
-   [ ] Shows bid pressure, ask pressure, ratio, sample size
-   [ ] Badge shows direction (BULLISH/BEARISH/NEUTRAL)
-   [ ] Progress bar visualizes bid/ask distribution
-   [ ] Updates when symbol/exchange changed

**Test API**:

```bash
curl -X GET "http://202.155.90.20:8000/api/spot-microstructure/book-pressure?symbol=BTCUSDT&exchange=binance&limit=100"
```

**Expected Response**:

```json
{
    "data": [
        {
            "timestamp": "...",
            "exchange": "binance",
            "symbol": "BTCUSDT",
            "bid_pressure": 71.14,
            "ask_pressure": 44.12,
            "pressure_ratio": 1.61,
            "pressure_direction": "bullish"
        }
    ]
}
```

### 2. Liquidity Imbalance Component

**Expected Behavior**:

-   [ ] Shows total liquidity metrics
-   [ ] Displays bid/ask ratio
-   [ ] Shows imbalance value and percentage
-   [ ] Color coding based on imbalance (green/red)

**Test API**:

```bash
curl -X GET "http://202.155.90.20:8000/api/spot-microstructure/orderbook/liquidity?symbol=BTCUSDT&depth=20"
```

**Expected Response**:

```json
{
    "symbol": "BTCUSDT",
    "timestamp": "...",
    "total_bid_liquidity": 314081.76,
    "total_ask_liquidity": 182539.49,
    "total_liquidity": 496621.25,
    "bid_ask_ratio": 1.72,
    "imbalance": 131542.27,
    "imbalance_pct": 26.49
}
```

### 3. Market Depth Stats

**Expected Behavior**:

-   [ ] Shows depth score (0-100)
-   [ ] Displays bid/ask levels count
-   [ ] Shows total bid/ask volumes
-   [ ] Updates on symbol/exchange change

**Test API**:

```bash
curl -X GET "http://202.155.90.20:8000/api/spot-microstructure/market-depth?symbol=BTCUSDT&exchange=binance&limit=1"
```

**Expected Response**:

```json
{
    "data": [
        {
            "timestamp": "...",
            "exchange": "binance",
            "symbol": "BTCUSDT",
            "bid_levels": 185,
            "ask_levels": 97,
            "total_bid_volume": 471675,
            "total_ask_volume": 659966,
            "depth_score": 63.74
        }
    ]
}
```

### 4. Quick Stats

**Expected Behavior**:

-   [ ] Shows mid price
-   [ ] Displays current spread
-   [ ] Shows spread percentage
-   [ ] Market status indicator (tight/normal/wide spread)

**Test API**:

```bash
curl -X GET "http://202.155.90.20:8000/api/spot-microstructure/orderbook/snapshot?symbol=BTCUSDT&depth=1"
```

### 5. Live Orderbook Snapshot

**Expected Behavior**:

-   [ ] Displays top 10 bids and asks
-   [ ] Shows visual depth bars (background gradient)
-   [ ] Mid price and spread in center
-   [ ] Auto-refreshes every 5 seconds
-   [ ] Price, quantity, and total formatted correctly

**Test API**:

```bash
curl -X GET "http://202.155.90.20:8000/api/spot-microstructure/orderbook/snapshot?symbol=BTCUSDT&depth=15"
```

**Expected Response**:

```json
{
    "asks": [
        { "price": 120538, "quantity": 0.00296 },
        { "price": 120539, "quantity": 0.52502 }
    ],
    "bids": [
        { "price": 120500, "quantity": 0.2451 },
        { "price": 120499, "quantity": 0.3184 }
    ]
}
```

### 6. Book Pressure Chart

**Expected Behavior**:

-   [ ] Line chart with 2 datasets (bid/ask pressure)
-   [ ] Green line for bid pressure
-   [ ] Red line for ask pressure
-   [ ] Time-based x-axis
-   [ ] Tooltip shows values on hover
-   [ ] Updates on symbol/exchange change

**Visual Check**:

-   [ ] Chart renders without errors
-   [ ] Both lines visible
-   [ ] Legend shows "Bid Pressure" and "Ask Pressure"

### 7. Liquidity Heatmap Chart

**Expected Behavior**:

-   [ ] Bar chart showing price levels
-   [ ] Green bars for bid liquidity
-   [ ] Red bars for ask liquidity
-   [ ] X-axis shows price levels
-   [ ] Y-axis shows liquidity amounts

**Test API**:

```bash
curl -X GET "http://202.155.90.20:8000/api/spot-microstructure/liquidity-heatmap?symbol=BTCUSDT&exchange=binance&limit=50"
```

### 8. Market Depth Table

**Expected Behavior**:

-   [ ] Table with historical market depth data
-   [ ] Columns: Time, Bid Levels, Ask Levels, Volumes, Depth Score
-   [ ] Scrollable table (max-height: 400px)
-   [ ] Sticky header
-   [ ] Formatted values (K/M for large numbers)

### 9. Orderbook Depth Table

**Expected Behavior**:

-   [ ] Table showing level-by-level orderbook details
-   [ ] Columns: Level, Bid Price, Bid Qty, Bid Total, Ask Price, Ask Qty, Ask Total
-   [ ] Level 1 = closest to market
-   [ ] Cumulative totals shown
-   [ ] Proper formatting

**Test API**:

```bash
curl -X GET "http://202.155.90.20:8000/api/spot-microstructure/orderbook-depth?symbol=BTCUSDT&exchange=binance&limit=20"
```

## 🎯 Functional Testing

### Global Controls

1. **Symbol Selector**

    - [ ] Change symbol to ETH → All components update
    - [ ] Change to SOL → All components update
    - [ ] Change to ADA → All components update

2. **Exchange Selector**

    - [ ] Change exchange to OKX → Applicable components update
    - [ ] Change to Bybit → Applicable components update
    - [ ] Change to Bitget → Applicable components update

3. **Refresh All Button**
    - [ ] Click "Refresh All"
    - [ ] Loading spinner shows on button
    - [ ] All components reload data
    - [ ] Button returns to normal after ~2s

### Event System

**Test Event Propagation**:

1. Open browser console (F12)
2. Change symbol → Check console logs:
    ```
    📊 Symbol changed to: ETHUSDT
    ✅ Book pressure loaded: bullish
    ✅ Liquidity imbalance loaded
    ✅ Market depth stats loaded
    ... (all components log successful load)
    ```

### Auto-Refresh

**Test Live Orderbook**:

1. Watch live orderbook snapshot
2. Wait 5 seconds
3. [ ] Component automatically refreshes
4. [ ] New data loads without manual action
5. [ ] Console shows: `✅ Orderbook snapshot loaded`

## 🔍 Error Handling Testing

### 1. Test API Unavailable

```bash
# Stop backend API temporarily or change API_BASE_URL to invalid
```

**Expected**:

-   [ ] Components show "No data available" message
-   [ ] Console shows error logs
-   [ ] No JavaScript errors
-   [ ] Loading states resolve

### 2. Test Invalid Symbol

**Steps**:

1. Manually edit globalSymbol in console: `$root.globalSymbol = 'INVALID'`
2. Click refresh

**Expected**:

-   [ ] API returns empty data or error
-   [ ] Components handle gracefully
-   [ ] Shows "No data available"

### 3. Test Network Error

**Steps**:

1. Open DevTools → Network tab
2. Enable "Offline" mode
3. Click Refresh All

**Expected**:

-   [ ] Console shows network errors
-   [ ] Components show empty states
-   [ ] No crashes

## 📊 Visual Testing

### Layout Checks

-   [ ] Header aligns properly
-   [ ] Controls in header are responsive
-   [ ] Cards have proper spacing (gap-3)
-   [ ] All components visible without scrolling (except tables)
-   [ ] Mobile view: sidebar collapses, layout stacks vertically

### Color Coding

-   [ ] Green = Bullish/Bid (success)
-   [ ] Red = Bearish/Ask (danger)
-   [ ] Blue = Info/Neutral (primary)
-   [ ] Gray = Secondary/Loading

### Animations

-   [ ] Pulse dot animates on header
-   [ ] Loading spinners rotate smoothly
-   [ ] Progress bars fill smoothly
-   [ ] Hover effects on stat items work
-   [ ] Chart animations smooth

## 🚀 Performance Testing

### Load Time

-   [ ] Initial page load < 2s
-   [ ] All API calls complete < 3s
-   [ ] Charts render < 1s after data received

### Memory

-   [ ] No memory leaks on symbol/exchange changes
-   [ ] Charts properly destroyed before re-render
-   [ ] Event listeners cleaned up

### Console

-   [ ] No JavaScript errors
-   [ ] No warning messages
-   [ ] API logs show successful fetches
-   [ ] Component initialization logs present

## 📱 Responsive Testing

### Desktop (1920x1080)

-   [ ] All components visible
-   [ ] 3-column layout for stats
-   [ ] 2-column layout for charts/tables
-   [ ] Sidebar open by default

### Tablet (768x1024)

-   [ ] Layout adjusts to smaller screen
-   [ ] Stats stack vertically or 2-column
-   [ ] Charts/tables maintain aspect ratio
-   [ ] Sidebar toggleable

### Mobile (375x667)

-   [ ] Single column layout
-   [ ] Sidebar hidden by default
-   [ ] Controls stack vertically
-   [ ] Tables scrollable horizontally
-   [ ] Font sizes readable

## 🔧 Browser Compatibility

Test in:

-   [ ] Chrome (latest)
-   [ ] Firefox (latest)
-   [ ] Safari (latest)
-   [ ] Edge (latest)

## ✅ Final Verification

**All 7 API Endpoints Used**:

-   [x] `/api/spot-microstructure/book-pressure`
-   [x] `/api/spot-microstructure/liquidity-heatmap`
-   [x] `/api/spot-microstructure/market-depth`
-   [x] `/api/spot-microstructure/orderbook/snapshot`
-   [x] `/api/spot-microstructure/orderbook-depth`
-   [x] `/api/spot-microstructure/orderbook/liquidity`
-   [x] `/api/spot-microstructure/orderbook` (available for future use)

**All Components Working**:

-   [x] Book Pressure Card
-   [x] Liquidity Imbalance
-   [x] Market Depth Stats
-   [x] Quick Stats
-   [x] Live Orderbook Snapshot
-   [x] Book Pressure Chart
-   [x] Liquidity Heatmap Chart
-   [x] Market Depth Table
-   [x] Orderbook Depth Table

**Core Features**:

-   [x] Global symbol selector
-   [x] Global exchange selector
-   [x] Refresh all functionality
-   [x] Auto-refresh (live snapshot)
-   [x] Event-driven updates
-   [x] Error handling
-   [x] Loading states
-   [x] Responsive design
-   [x] Trading insights section

## 📝 Test Results Template

```
Date: ___________
Tester: ___________
Browser: ___________
Screen Size: ___________

Component Tests:
- Book Pressure Card: ☐ Pass ☐ Fail
- Liquidity Imbalance: ☐ Pass ☐ Fail
- Market Depth Stats: ☐ Pass ☐ Fail
- Quick Stats: ☐ Pass ☐ Fail
- Live Snapshot: ☐ Pass ☐ Fail
- Pressure Chart: ☐ Pass ☐ Fail
- Heatmap Chart: ☐ Pass ☐ Fail
- Market Depth Table: ☐ Pass ☐ Fail
- Orderbook Depth Table: ☐ Pass ☐ Fail

Functional Tests:
- Symbol Changes: ☐ Pass ☐ Fail
- Exchange Changes: ☐ Pass ☐ Fail
- Refresh All: ☐ Pass ☐ Fail
- Auto-Refresh: ☐ Pass ☐ Fail

Error Handling:
- API Errors: ☐ Pass ☐ Fail
- Network Errors: ☐ Pass ☐ Fail
- Invalid Data: ☐ Pass ☐ Fail

Performance:
- Load Time: ☐ Pass ☐ Fail
- Memory: ☐ Pass ☐ Fail
- Responsiveness: ☐ Pass ☐ Fail

Notes:
_______________________________________________
_______________________________________________
```

## 🎉 Success Criteria

Dashboard is considered **ready for production** when:

-   ✅ All API endpoints return data successfully
-   ✅ All components render without errors
-   ✅ Global controls update all components
-   ✅ Charts display correctly
-   ✅ Tables show formatted data
-   ✅ Auto-refresh works
-   ✅ Error handling graceful
-   ✅ Responsive on all screen sizes
-   ✅ No console errors
-   ✅ Trading insights section displays
