# Volume & Trade Stats - Quick Reference

## 🚀 Quick Access

**URL**: `/spot-microstructure/volume-trade-stats`

## 📊 Dashboard Overview

### Key Metrics (Top Cards)

1. **Total Trades**: Buy + Sell breakdown
2. **Buy/Sell Ratio**: Market sentiment indicator
3. **Total Volume**: Current trading volume
4. **Avg Trade Size**: Institutional activity gauge

### Main Visualizations

1. **Trade Activity Chart**: Line chart showing buy/sell/total trades over time
2. **Buy/Sell Distribution**: Doughnut chart showing trade balance
3. **Volume Flow Chart**: Stacked bars showing buy/sell volume
4. **Volume Profile**: Horizontal bars showing volume by price level (POC)
5. **Trade Size Evolution**: Line chart tracking avg and max trade sizes

## 🔌 API Endpoints Used

| Endpoint                                           | Purpose             | Key Data                                |
| -------------------------------------------------- | ------------------- | --------------------------------------- |
| `/api/spot-microstructure/trade-stats`             | Trade frequency     | buy_trades, sell_trades, avg_trade_size |
| `/api/spot-microstructure/volume-profile`          | Aggregated analysis | buy_sell_ratio, total_trades            |
| `/api/spot-microstructure/volume-profile-detailed` | Price level volume  | price_level, volume, poc                |
| `/api/spot-microstructure/volume-stats`            | Time-series volume  | buy_volume, sell_volume, volume_std     |

## 🎯 Trading Insights

### Buy/Sell Ratio Interpretation

-   **> 1.5**: 🟢 Strong buying pressure (Bullish)
-   **1.1 - 1.5**: 🔵 Moderate buying (Cautiously bullish)
-   **0.9 - 1.1**: ⚪ Balanced market (Neutral)
-   **0.6 - 0.9**: 🟠 Moderate selling (Cautiously bearish)
-   **< 0.6**: 🔴 Strong selling pressure (Bearish)

### Volume Spike Detection

-   **> 1.5x avg**: ⚡ High volume spike (Watch for volatility)
-   **> 1.2x avg**: 📈 Above average (Increased interest)
-   **< 0.7x avg**: 📉 Low volume (Reduced activity)

### POC (Point of Control)

-   **Definition**: Price level with highest volume
-   **Use Case**: Key support/resistance level
-   **Strategy**: Price tends to return to POC

### Trade Size Analysis

-   **Large max trades**: Whale/institutional activity
-   **Rising avg size**: Accumulation phase
-   **Falling avg size**: Distribution phase

## 🎛️ Controls

### Symbol Selection

-   BTCUSDT (Bitcoin)
-   ETHUSDT (Ethereum)
-   SOLUSDT (Solana)
-   BNBUSDT (BNB)
-   XRPUSDT (XRP)

### Timeframe Selection

-   1m (1 Minute)
-   5m (5 Minutes) - **Default**
-   15m (15 Minutes)
-   1h (1 Hour)

### Refresh

-   **Auto**: Every 60 seconds
-   **Manual**: Click refresh button

## 📋 Data Tables

### Trade Statistics Table

Shows last 20 records with:

-   Timestamp
-   Exchange & Pair
-   Buy/Sell/Total trades
-   Avg/Max trade size
-   B/S ratio badge

### Volume Statistics Table

Shows last 20 records with:

-   Timestamp
-   Exchange & Timeframe
-   Buy/Sell/Total volume
-   Average volume
-   Volume standard deviation
-   Dominance indicator

## 🔍 What to Look For

### Bullish Signals

✅ Buy/Sell ratio > 1.2
✅ Increasing buy volume
✅ Rising average trade size
✅ Volume spike on green candles

### Bearish Signals

❌ Buy/Sell ratio < 0.8
❌ Increasing sell volume
❌ Falling average trade size
❌ Volume spike on red candles

### Consolidation Signals

🔄 Buy/Sell ratio near 1.0
🔄 Decreasing volume
🔄 Stable trade sizes
🔄 Price near POC

## ⚡ Performance Tips

1. Use default timeframe (5m) for best balance
2. Limit parameter is optimized at 1000 records
3. Auto-refresh pauses when loading
4. Charts update smoothly with new data

## 🐛 Troubleshooting

| Issue        | Solution                                  |
| ------------ | ----------------------------------------- |
| No data      | Check API_BASE_URL in config              |
| Charts blank | Check browser console for errors          |
| Slow loading | Reduce limit or increase refresh interval |
| Wrong data   | Verify symbol and timeframe selection     |

## 🔧 Configuration

### Environment Variable

```env
API_BASE_URL=https://test.dragonfortune.ai
```

### Services Config

```php
// config/services.php
'api' => [
    'base_url' => env('API_BASE_URL', ''),
],
```

## 📱 Mobile Support

-   Fully responsive design
-   Touch-friendly controls
-   Scrollable tables
-   Adaptive chart sizes

## 🎨 Color Coding

-   **Green** 🟢: Bullish/Buy signals
-   **Red** 🔴: Bearish/Sell signals
-   **Blue** 🔵: Neutral/Info
-   **Purple** 💜: POC/Important levels
-   **Orange** 🟠: Warning/Caution

## 📚 Educational Content

The dashboard includes three educational cards explaining:

1. **Buy/Sell Ratio**: How to interpret different ratio levels
2. **Volume Profile (POC)**: What it means and how to use it
3. **Trade Size Analysis**: Detecting institutional activity

## 🔄 Data Refresh Cycle

```
Init → Load All (4 APIs) → Calculate Metrics → Render Charts
  ↓
Wait 60s
  ↓
Repeat if not loading
```

## 📊 Chart Types

| Chart                 | Type           | Purpose               |
| --------------------- | -------------- | --------------------- |
| Trade Activity        | Line           | Frequency trends      |
| Buy/Sell Distribution | Doughnut       | Balance visualization |
| Volume Flow           | Stacked Bar    | Buy vs Sell volume    |
| Volume Profile        | Horizontal Bar | Volume by price       |
| Trade Size            | Line           | Size evolution        |

## 🎯 Key Files

-   **View**: `resources/views/spot-microstructure/volume-trade-stats.blade.php`
-   **Controller**: `public/js/volume-trade-stats-controller.js`
-   **Route**: `/spot-microstructure/volume-trade-stats` (already configured)

## ✅ Features

✅ Real-time updates (60s)
✅ 4 API endpoints integrated
✅ 5 chart visualizations
✅ 2 detailed data tables
✅ Intelligent insights
✅ Educational content
✅ Responsive design
✅ Error handling
✅ Loading states
✅ Symbol/timeframe filters

## 🚦 Status Indicators

-   🟢 **Pulse dot**: Live data active
-   **Spinner**: Loading in progress
-   **Badges**: Quick status indicators
-   **Color bars**: Volume/trade dominance

## 💡 Pro Tips

1. **Compare POC with current price** - Distance indicates potential move
2. **Watch volume spikes** - Often precede price movements
3. **Monitor B/S ratio trends** - Changing sentiment indicator
4. **Large trade sizes** - Follow the smart money
5. **Volume profile clusters** - Strong support/resistance zones

## 📞 Quick Support

**Issue with API?**

-   Verify backend is running at configured URL
-   Check CORS settings
-   Test endpoint directly in browser

**Issue with charts?**

-   Clear browser cache
-   Check Chart.js loaded (Network tab)
-   Verify JavaScript errors (Console)

**Issue with data?**

-   Check symbol format (must include USDT)
-   Verify timeframe is supported
-   Try manual refresh

---

## 🔧 Known Issues & Solutions

### Volume Profile API

**Issue**: Volume Profile endpoint does NOT support `timeframe` parameter  
**Status**: Fixed in v1.0.1  
**Solution**: Parameter removed from API call

### Empty Data

**Issue**: Some endpoints may return empty data arrays  
**Status**: Handled gracefully  
**Solution**: Dashboard shows "Waiting for Data" messages

---

**Last Updated**: October 11, 2025  
**Version**: 1.0.1  
**Status**: Production Ready ✅
