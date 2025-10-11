# Long/Short Ratio - Quick Reference Card

## 🚀 Quick Start (5 Minutes)

### 1. Set Environment Variable

```bash
# Edit .env file
API_BASE_URL=http://202.155.90.20:8000
```

### 2. Clear Cache

```bash
php artisan config:clear && php artisan config:cache
```

### 3. Access Dashboard

```
http://your-domain/derivatives/long-short-ratio
```

---

## 📍 File Locations

| File              | Path                                                     |
| ----------------- | -------------------------------------------------------- |
| **Frontend View** | `resources/views/derivatives/long-short-ratio.blade.php` |
| **JS Controller** | `public/js/long-short-ratio-controller.js`               |
| **Route**         | `routes/web.php` (line 18)                               |
| **Config**        | `config/services.php` (line 39-41)                       |
| **Documentation** | `docs/LONG-SHORT-RATIO-*.md`                             |

---

## 🎯 Features at a Glance

### Statistics Cards (4)

```
┌──────────────┬──────────────┬──────────────┬──────────────┐
│ Current      │ Average      │ Sentiment    │ Risk Level   │
│ Ratio        │ Ratio        │              │              │
└──────────────┴──────────────┴──────────────┴──────────────┘
```

### Charts (2)

```
┌──────────────────────┬──────────────┐
│ L/S Ratio Trend      │ Distribution │
│ (Line Chart)         │ (Area Chart) │
└──────────────────────┴──────────────┘
```

### Interactive Filters (4)

-   🎯 Symbol (BTCUSDT, ETHUSDT, etc)
-   🏢 Exchange (All, Binance, Bybit, OKX)
-   ⏰ Interval (15m, 1h, 4h, 1d)
-   📊 Ratio Type (Accounts, Positions)

### Data Table

-   📋 Exchange Comparison (real-time)

### Alerts

-   💡 Actionable Insights Panel

---

## 🔧 API Endpoints Used

| Endpoint                              | Purpose              | Parameters                             |
| ------------------------------------- | -------------------- | -------------------------------------- |
| `/api/long-short-ratio/analytics`     | High-level metrics   | symbol, exchange, interval, ratio_type |
| `/api/long-short-ratio/top-accounts`  | Accounts timeseries  | symbol, exchange, interval, limit      |
| `/api/long-short-ratio/top-positions` | Positions timeseries | symbol, exchange, interval, limit      |

---

## 📊 Data Interpretation

### Ratio Values

-   **> 1.0** = More Longs (Bullish)
-   **= 1.0** = Balanced (Neutral)
-   **< 1.0** = More Shorts (Bearish)

### Risk Levels

-   **Low**: Normal positioning
-   **Medium**: Starting to crowd
-   **High**: Extremely crowded (reversal risk!)

### Sentiment

-   🟢 **Bullish**: Long bias dominates
-   🔴 **Bearish**: Short bias dominates
-   ⚪ **Neutral**: Balanced positioning

### Insights Severity

-   🔴 **High**: Urgent attention needed
-   🟡 **Medium**: Notable condition
-   🔵 **Low**: Informational

---

## 🎨 UI Components Map

```
Dashboard Structure:
│
├── Header
│   ├── Title & Description
│   └── Filters (Symbol, Type, Exchange, Refresh)
│
├── Statistics Grid (4 cards)
│   ├── Current Ratio (with trend)
│   ├── Average Ratio (with min/max)
│   ├── Market Sentiment (with %)
│   └── Risk Level (with std dev)
│
├── Insights Panel
│   └── Alerts (if any)
│
├── Charts Row
│   ├── Main Ratio Chart (line)
│   │   └── Interval Buttons (15m/1h/4h/1d)
│   └── Distribution Chart (area)
│
└── Exchange Comparison Table
    └── Binance / Bybit / OKX data
```

---

## ⚡ Performance Features

-   ✅ **Auto-refresh**: Every 60 seconds
-   ✅ **Parallel Fetching**: All endpoints simultaneously
-   ✅ **Silent Updates**: Background refresh without spinner
-   ✅ **Caching**: Prevents duplicate requests
-   ✅ **Lazy Rendering**: Charts only render when data exists
-   ✅ **Debouncing Ready**: For rapid filter changes

---

## 🐛 Common Issues & Quick Fixes

### "Failed to load data"

```bash
# Check API URL in .env
echo $API_BASE_URL

# Clear cache
php artisan config:clear

# Test API directly
curl http://202.155.90.20:8000/api/long-short-ratio/analytics?symbol=BTCUSDT&limit=100
```

### Charts not rendering

```javascript
// Check browser console (F12)
// Verify Chart.js loaded
console.log(typeof Chart); // Should be "function"

// Check canvas elements
document.getElementById("mainRatioChart");
document.getElementById("distributionChart");
```

### No exchange data

```
This is normal if:
1. API has no data for that symbol/exchange
2. Exchange filter is set (clear it for comparison view)
3. Network issue (check console)
```

---

## 🔍 Debug Checklist

```bash
# 1. Check route exists
php artisan route:list | grep long-short

# 2. Check config
php artisan config:show services.api.base_url

# 3. Check view exists
ls -la resources/views/derivatives/long-short-ratio.blade.php

# 4. Check JS file
ls -la public/js/long-short-ratio-controller.js

# 5. Check browser console (F12)
# Look for:
# - "Initializing Long/Short Ratio dashboard..."
# - "Fetching analytics from: ..."
# - "Data loaded successfully: ..."
```

---

## 📝 Customization Quick Tips

### Change Auto-refresh Interval

```javascript
// In long-short-ratio.blade.php, find:
setInterval(() => { ... }, 60000);
// Change 60000 to desired milliseconds
```

### Add New Symbol

```html
<!-- In blade file, add to select: -->
<option value="XRPUSDT">XRPUSDT</option>
```

### Modify Chart Colors

```javascript
// In long-short-ratio-controller.js:
borderColor: 'rgb(59, 130, 246)',  // Change this
backgroundColor: 'rgba(59, 130, 246, 0.1)',  // And this
```

### Change Data Limit

```javascript
// In controller:
this.filters = {
    limit: 2000, // Change to 1000, 3000, etc
};
```

---

## 📚 Related Documentation

| Document                                   | Purpose                            |
| ------------------------------------------ | ---------------------------------- |
| `LONG-SHORT-RATIO-ANALYSIS.md`             | Detailed blueprint vs API analysis |
| `LONG-SHORT-RATIO-IMPLEMENTATION-GUIDE.md` | Technical implementation details   |
| `LONG-SHORT-RATIO-FINAL-SUMMARY.md`        | Complete project summary           |
| This file                                  | Quick reference & troubleshooting  |

---

## 🎓 Understanding L/S Ratio

### What it tells you:

-   **Positioning**: Who's on which side?
-   **Sentiment**: Bullish or bearish bias?
-   **Risk**: How crowded is the trade?
-   **Opportunity**: Contrarian signals

### Accounts vs Positions:

-   **Accounts**: Number of traders (retail bias)
-   **Positions**: Dollar value (institutional bias)

### Contrarian Strategy:

-   Very high ratio (>2.0) → Too many longs → Consider shorts
-   Very low ratio (<0.5) → Too many shorts → Consider longs

### Confirmation:

Always combine with:

-   Price action
-   Volume
-   Other indicators
-   Fundamental analysis

---

## ✨ Pro Tips

1. **Compare Accounts vs Positions** to spot retail/institutional divergence
2. **Watch for extremes** (ratio > 2.0 or < 0.5) for reversal signals
3. **Cross-reference exchanges** to detect arbitrage opportunities
4. **Use multiple timeframes** for better context
5. **Read insights panel** for backend-generated alerts
6. **Monitor risk level** to gauge trade crowding
7. **Track trend direction** for momentum confirmation

---

## 📞 Support

### Browser Console

Press `F12` → Console tab

### Check Logs

```bash
tail -f storage/logs/laravel.log
```

### API Health

```bash
curl http://202.155.90.20:8000/api/long-short-ratio/analytics?symbol=BTCUSDT&limit=10
```

---

## ✅ Daily Usage Checklist

-   [ ] Dashboard loads without errors
-   [ ] All 4 stat cards show data
-   [ ] Both charts render
-   [ ] Filters work when changed
-   [ ] Exchange table populates
-   [ ] Manual refresh button works
-   [ ] Data updates after 60s
-   [ ] No console errors

---

**Version**: 1.0.0  
**Last Updated**: October 11, 2025  
**Status**: Production Ready ✅

---

## 🚀 You're Ready!

Dashboard ini siap digunakan untuk analisis profesional positioning trader.

**Happy Trading!** 📈
