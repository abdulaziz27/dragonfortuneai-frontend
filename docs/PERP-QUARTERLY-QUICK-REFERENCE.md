# Perp-Quarterly Spread - Quick Reference

## 🚀 Quick Start

### Access Dashboard

```
URL: /derivatives/perp-quarterly-spread
```

### Key Controls

-   **Symbol:** BTC, ETH, SOL, BNB, XRP, etc.
-   **Exchange:** Binance, Bybit, OKX, Bitget, Gate.io, Deribit
-   **Interval:** 5m, 15m, 1h, 4h, 1d
-   **Refresh:** Manual refresh button (auto-refresh: 30-60s)

---

## 📊 Reading the Dashboard

### Analytics Card (Top)

| Metric               | Meaning                 | Action                   |
| -------------------- | ----------------------- | ------------------------ |
| **Current Spread**   | Real-time spread in BPS | >50 = Strong opportunity |
| **Average Spread**   | Historical average      | Benchmark for current    |
| **Spread Range**     | Min to Max range        | Volatility indicator     |
| **Market Structure** | Contango/Backwardation  | Trading direction        |
| **Trend**            | Widening/Narrowing      | Entry/exit timing        |

### Spread History Chart (Left)

-   **Green area:** Contango (Perp > Quarterly)
-   **Red area:** Backwardation (Quarterly > Perp)
-   **Zero line:** Neutral spread
-   **Slope:** Trend direction

### Insights Panel (Right)

-   **Market Structure:** Current state + interpretation
-   **Spread Trend:** Direction + change amount
-   **Arbitrage Score:** 0-100% opportunity gauge
-   **Key Metrics:** Quick stats
-   **Trading Strategy:** Actionable suggestion

### Data Table (Bottom)

-   Recent spread measurements
-   Timestamp, symbols, spreads
-   Structure classification

---

## 💰 Trading Signals

### Strong Buy Signals (Calendar Spread)

✅ Spread > 50 BPS + Widening  
✅ Strong Contango + High funding  
✅ Near expiry + Spread > 30 BPS  
✅ Arbitrage score > 75%

### Strong Sell Signals (Exit Calendar)

🔴 Spread < 10 BPS  
🔴 Narrowing rapidly  
🔴 Approaching expiry (< 7 days)  
🔴 Arbitrage score < 25%

### Neutral (Wait)

⚪ Spread 0-10 BPS  
⚪ No clear trend  
⚪ Low volatility  
⚪ Arbitrage score 10-30%

---

## 📈 Market Structures Cheat Sheet

### Contango (Positive Spread)

```
Perp Price > Quarterly Price
```

**Means:**

-   Market expects higher prices
-   Longs paying high funding
-   Normal in bull markets

**Strategy:**

-   Short Perp / Long Quarterly
-   Collect funding + convergence
-   Exit near expiry

**Risk:**

-   Spread may widen further
-   Funding rate spikes
-   Execution slippage

---

### Backwardation (Negative Spread)

```
Quarterly Price > Perp Price
```

**Means:**

-   Supply shortage
-   High spot demand
-   Unusual in crypto

**Strategy:**

-   Long Perp / Short Quarterly
-   Negative funding benefit
-   Wait for normalization

**Risk:**

-   Spread may widen (negative)
-   Settlement complications
-   Liquidity issues

---

## 🎯 Trading Strategies

### 1. **Calendar Spread (Contango)**

```
Position: Short Perp + Long Quarterly
Entry: Spread > 30 BPS + Widening
Exit: Spread < 15 BPS or near expiry
P&L: (Spread entry - Spread exit) × Position size
```

### 2. **Calendar Spread (Backwardation)**

```
Position: Long Perp + Short Quarterly
Entry: Spread < -30 BPS
Exit: Spread > -10 BPS
P&L: (Spread exit - Spread entry) × Position size
```

### 3. **Convergence Trade**

```
Position: Depends on structure
Entry: 7-14 days before expiry + Spread > 20 BPS
Exit: 1-3 days before expiry
P&L: Nearly guaranteed convergence profit
```

---

## 🔍 Key Metrics Explained

### Spread (BPS)

```
Spread BPS = ((Quarterly Price - Perp Price) / Perp Price) × 10,000
```

-   **Positive:** Contango
-   **Negative:** Backwardation
-   **Zero:** Neutral

### Arbitrage Score (0-100%)

```
100% = Spread > 100 BPS (Very high opportunity)
75%  = Spread > 50 BPS  (Strong opportunity)
50%  = Spread > 20 BPS  (Moderate opportunity)
25%  = Spread > 10 BPS  (Small opportunity)
10%  = Spread < 10 BPS  (Minimal opportunity)
```

### Market Structure

| Spread       | Structure            |
| ------------ | -------------------- |
| > +50 BPS    | Strong Contango      |
| 0 to +50 BPS | Contango             |
| 0 BPS        | Neutral              |
| 0 to -50 BPS | Backwardation        |
| < -50 BPS    | Strong Backwardation |

---

## ⚠️ Risk Checklist

Before entering calendar spread:

-   [ ] Check liquidity on both contracts
-   [ ] Calculate total fees (entry + exit)
-   [ ] Verify spread is wide enough to cover costs
-   [ ] Check days to expiry
-   [ ] Monitor funding rates
-   [ ] Set stop-loss (spread widening limit)
-   [ ] Plan exit strategy
-   [ ] Consider position sizing

---

## 🧮 P&L Calculation

### Example: Contango Calendar Spread

**Entry:**

-   Spread: +45 BPS
-   Position: 1 BTC
-   Perp: Short at $50,000
-   Quarterly: Long at $50,225

**Exit:**

-   Spread: +10 BPS
-   Perp: Cover at $51,000
-   Quarterly: Close at $51,051

**P&L Calculation:**

```
Perp P&L:  ($50,000 - $51,000) = -$1,000 (loss on short)
Quarterly P&L: ($51,051 - $50,225) = +$826 (profit on long)
Spread P&L: (45 BPS - 10 BPS) × $50,000 / 10,000 = +$175
Net P&L: +$1 (approximately breakeven)
```

**Note:** In practice, spread narrowing = profit on calendar spread

---

## 🛠️ API Quick Reference

### Analytics

```
GET /api/perp-quarterly/analytics?exchange=Binance&base=BTC&quote=USDT&interval=1h&limit=2000
```

**Key Fields:**

-   `spread_bps.current` - Current spread
-   `spread_bps.average` - Average spread
-   `trend.direction` - Widening/Narrowing
-   `insights[]` - Trading alerts

### History

```
GET /api/perp-quarterly/history?exchange=Binance&base=BTC&quote=USDT&interval=5m&limit=2000
```

**Key Fields:**

-   `ts` - Timestamp
-   `spread_abs` - Absolute spread ($)
-   `spread_bps` - Relative spread (BPS)
-   `perp_symbol` - Perp contract
-   `quarterly_symbol` - Quarterly contract

---

## 💡 Pro Tips

1. **Best times to trade:**

    - High volatility periods
    - 7-30 days before expiry
    - After major news events

2. **Avoid trading:**

    - < 3 days to expiry (execution risk)
    - Very low liquidity
    - Spread < 15 BPS (fees may exceed profit)

3. **Monitor continuously:**

    - Funding rates (affects perp spread)
    - Liquidity depth
    - OI changes
    - Market volatility

4. **Position sizing:**

    - Start small (10-20% of capital)
    - Scale based on conviction
    - Diversify across exchanges

5. **Risk management:**
    - Set max loss per trade (2-5%)
    - Use stop-loss on spread widening
    - Plan multiple exit points
    - Monitor 24/7 (crypto never sleeps)

---

## 📱 Mobile View

Dashboard is fully responsive:

-   Filters stack vertically
-   Charts adapt to screen size
-   Tables scrollable horizontally
-   All features accessible

---

## 🆘 Common Issues

### "No data available"

→ Check API connection, verify exchange supports both contracts

### Chart not showing

→ Refresh page, check Chart.js loaded, clear cache

### Wrong spread values

→ Verify correct exchange selected, check symbol override

### Insights not updating

→ Wait for auto-refresh (30s), or click manual refresh

---

## 📚 Further Reading

-   [Full Implementation Guide](./PERP-QUARTERLY-SPREAD-IMPLEMENTATION.md)
-   [Trading Strategies Deep Dive](#)
-   [Risk Management Guide](#)
-   [API Documentation](#)

---

**Remember:** Always do your own research. This dashboard provides data and insights, but trading decisions are your responsibility.

**Risk Disclaimer:** Calendar spreads can lose money if spread widens further or execution costs exceed profit potential.
