# On-Chain Metrics - Quick Reference

## 🚀 Quick Start

### Access Dashboard

```
URL: http://your-domain/onchain-metrics
Route: onchain-metrics.index
```

### API Endpoints Summary

| Endpoint                               | Purpose                       | Key Parameters               |
| -------------------------------------- | ----------------------------- | ---------------------------- |
| `/api/onchain/valuation/mvrv`          | MVRV Z-Score & Realized Price | `limit`, `metric`            |
| `/api/onchain/exchange/flows`          | Exchange netflows             | `limit`, `asset`, `exchange` |
| `/api/onchain/exchange/summary`        | Aggregated flows              | `limit`, `asset`, `exchange` |
| `/api/onchain/supply/distribution`     | LTH vs STH supply             | `limit`, `cohort`            |
| `/api/onchain/supply/hodl-waves`       | Age-based distribution        | `limit`, `cohort`            |
| `/api/onchain/behavioral/chain-health` | Reserve Risk, SOPR, etc.      | `limit`, `metric`            |
| `/api/onchain/miners/metrics`          | Miner activity                | `limit`                      |
| `/api/onchain/whales/holdings`         | Whale positions               | `limit`, `cohort`            |
| `/api/onchain/whales/summary`          | Whale statistics              | `limit`                      |
| `/api/onchain/valuation/realized-cap`  | Realized Cap & Thermocap      | `limit`, `metric`            |

## 📊 Dashboard Sections

### 1. Quick Stats (Top Row)

-   **MVRV Z-Score**: Market valuation indicator
-   **Exchange Netflow**: 24h flow direction
-   **Puell Multiple**: Miner selling pressure
-   **LTH/STH Ratio**: Holder conviction

### 2. MVRV & Valuation

-   Left: MVRV Z-Score + Realized Price chart
-   Right: Valuation gauge + interpretation guide

### 3. Exchange Flows

-   Left: Multi-exchange netflow chart
-   Right: Exchange summary table

### 4. Supply Distribution

-   Left: LTH vs STH supply chart
-   Right: HODL Waves (age cohorts)

### 5. Chain Health

-   Full-width: Selectable metrics (Reserve Risk, SOPR, etc.)

### 6. Miner Metrics

-   Left: Miner reserves + Puell Multiple
-   Right: Current metrics cards

### 7. Whale Activity

-   Left: Whale holdings by cohort
-   Right: Whale summary table

### 8. Realized Cap

-   Full-width: Realized Cap + Thermocap

## 🎛️ Filters

### Asset Filter

```javascript
filters.asset = ""; // All Assets
filters.asset = "BTC"; // Bitcoin only
filters.asset = "USDT"; // Stablecoins only
```

### Exchange Filter

```javascript
filters.exchange = ""; // All Exchanges
filters.exchange = "binance"; // Binance only
filters.exchange = "coinbase"; // Coinbase only
filters.exchange = "okx"; // OKX only
```

### Date Range

```javascript
filters.limit = 30; // Last 30 days
filters.limit = 90; // Last 90 days
filters.limit = 180; // Last 6 months
filters.limit = 365; // Last 1 year
```

## 🔄 Data Refresh

### Manual Refresh

-   Click **"🔄 Refresh All"** button in header
-   Or click individual refresh buttons on each panel

### Programmatic Refresh

```javascript
// Refresh all metrics
refreshAll();

// Refresh specific metrics
loadMVRVData();
loadExchangeFlows();
loadSupplyDistribution();
loadHodlWaves();
loadChainHealth();
loadMinerMetrics();
loadWhaleHoldings();
loadRealizedCap();
```

## 🎨 Color Coding

| Color     | Meaning          | Example                            |
| --------- | ---------------- | ---------------------------------- |
| 🟢 Green  | Bullish/Positive | Outflow, Accumulation, Undervalued |
| 🔴 Red    | Bearish/Negative | Inflow, Distribution, Overvalued   |
| 🔵 Blue   | Neutral/Info     | Normal range, Informational        |
| 🟡 Yellow | Warning/Moderate | Moderate risk, Caution             |

## 📈 Trading Signals

### MVRV Z-Score

-   **Z > 7**: 🔴 Extreme overvaluation → Sell zone
-   **Z 2-7**: 🟡 Overvalued → Take profits
-   **Z 0-2**: 🔵 Normal range → Hold
-   **Z < 0**: 🟢 Undervalued → Buy zone

### Exchange Netflow

-   **Negative (Outflow)**: 🟢 Bullish → Accumulation
-   **Positive (Inflow)**: 🔴 Bearish → Distribution

### Puell Multiple

-   **> 4**: 🔴 High selling pressure
-   **1-4**: 🟡 Moderate pressure
-   **< 0.5**: 🟢 Low pressure (capitulation)

### LTH/STH Ratio

-   **> 5**: 🟢 Strong conviction
-   **3-5**: 🔵 Moderate conviction
-   **< 2**: 🔴 Weak conviction

## 🐛 Troubleshooting

### Charts Not Loading

1. Check browser console for errors
2. Verify API base URL in meta tag
3. Check network tab for failed requests
4. Ensure backend API is running

### Empty Data

1. Try different date range (increase limit)
2. Remove filters (asset, exchange)
3. Check API response in network tab
4. Verify endpoint returns data

### Performance Issues

1. Reduce date range (use 30d instead of 365d)
2. Apply filters to reduce data volume
3. Clear browser cache
4. Refresh individual panels instead of all

## 💻 Developer Reference

### Alpine.js Data Structure

```javascript
{
    apiBaseUrl: string,
    loading: boolean,
    loadingStates: {
        mvrv: boolean,
        flows: boolean,
        supply: boolean,
        hodl: boolean,
        chainHealth: boolean,
        miners: boolean,
        whales: boolean,
        realizedCap: boolean,
    },
    filters: {
        asset: string,
        exchange: string,
        limit: number,
    },
    stats: {
        mvrvZ: string,
        exchangeNetflow: string,
        puellMultiple: string,
        lthSthRatio: string,
    },
    charts: {
        mvrv: Chart,
        exchangeFlow: Chart,
        supply: Chart,
        hodl: Chart,
        chainHealth: Chart,
        miner: Chart,
        whale: Chart,
        realizedCap: Chart,
    }
}
```

### Chart Configuration

```javascript
{
    type: 'line',
    data: {
        datasets: [...]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            x: { type: 'time' },
            y: { ... }
        }
    }
}
```

### API Request Format

```javascript
const params = new URLSearchParams({
    limit: this.filters.limit,
    asset: this.filters.asset || undefined,
    exchange: this.filters.exchange || undefined,
});

const response = await fetch(`${this.apiBaseUrl}/api/onchain/xxx?${params}`);
const data = await response.json();
```

## 📦 Dependencies

### Frontend

-   **Alpine.js**: v3.x (reactive framework)
-   **Chart.js**: v4.4.0 (charts)
-   **chartjs-adapter-date-fns**: v3.0.0 (time scale)
-   **Bootstrap**: v5.x (UI framework)

### Backend

-   **API Base URL**: `http://202.155.90.20:8000`
-   **Response Format**: JSON
-   **Date Format**: GMT strings

## 🎯 Best Practices

1. **Always destroy old charts** before creating new ones
2. **Use loading states** for better UX
3. **Handle API errors gracefully**
4. **Sort time series data** before rendering
5. **Test with different date ranges**
6. **Check console for errors**
7. **Verify API responses**

## 📞 Support

For questions or issues:

-   Review console logs for errors
-   Check API endpoint responses
-   Verify backend connectivity
-   Refer to full documentation

---

**Quick Reference Version**: 1.0.0  
**Last Updated**: October 11, 2025
