# 💰 Funding Rate Analytics Dashboard

**Think like a trader • Build like an engineer • Visualize like a designer**

## 📋 Overview

Dashboard analitik funding rate untuk cryptocurrency futures markets. Menampilkan funding rate lintas exchange, bias pasar, dan insight trading untuk membantu trader mendeteksi leverage positioning dan potential squeeze setups.

---

## 🏗️ Struktur Komponen

### 1. **Bias Card** (`components/funding/bias-card.blade.php`)

**Tujuan:** Menampilkan market bias (Long/Short/Neutral) dengan visual yang jelas

**API Endpoint:**

```
GET /api/funding-rate/bias?symbol=BTC&limit=1000
```

**Features:**

-   ✅ Dynamic color coding (🟩 Long, 🟥 Short, 🟨 Neutral)
-   ✅ Strength meter dengan interpretasi
-   ✅ Average funding rate display
-   ✅ Trading insights berdasarkan bias

**Trading Interpretasi:**

-   **Bias: Long** → Market overleveraged long → Risk: Long squeeze
-   **Bias: Short** → Market overleveraged short → Risk: Short squeeze
-   **Strength > 70%** → Extreme positioning → High squeeze risk

---

### 2. **Exchange Table** (`components/funding/exchange-table.blade.php`)

**Tujuan:** Tabel sortable funding rate per exchange dengan countdown

**API Endpoint:**

```
GET /api/funding-rate/exchanges?limit=1000&symbol=BTC
```

**Features:**

-   ✅ Sortable columns (exchange, funding rate)
-   ✅ Color-coded rates (green positive, red negative)
-   ✅ Next funding countdown timer
-   ✅ APR calculation from funding rate
-   ✅ Highlight highest & lowest rates

**Trading Interpretasi:**

-   **Large spread** → Arbitrage opportunity
-   **Negative on one exchange** → Check for local factors
-   **Consistent high funding** → Sustained directional bias

---

### 3. **Aggregate Chart** (`components/funding/aggregate-chart.blade.php`)

**Tujuan:** Bar chart perbandingan funding rate antar exchange

**API Endpoint:**

```
GET /api/funding-rate/aggregate?limit=2000&symbol=BTC&range_str=7d
```

**Features:**

-   ✅ Bar chart dengan color coding
-   ✅ Exchange comparison
-   ✅ Spread alert jika > 50%
-   ✅ Highest/lowest/spread stats

**Trading Interpretasi:**

-   **Green bars** → Long dominance on that exchange
-   **Red bars** → Short dominance on that exchange
-   **Large spread** → Arbitrage atau risk exchange-specific

---

### 4. **Weighted Chart** (`components/funding/weighted-chart.blade.php`)

**Tujuan:** Line chart OI-weighted funding rate

**API Endpoint:**

```
GET /api/funding-rate/weighted?symbol=BTC&interval=4h&limit=100
```

**Features:**

-   ✅ Line chart dengan gradient fill
-   ✅ OI-weighted untuk akurasi positioning
-   ✅ Current/24h avg/trend stats
-   ✅ Interval selector (1h, 4h, 1d)

**Trading Interpretasi:**

-   **OI-weighted lebih akurat** → Exchange besar punya pengaruh lebih
-   **Trend naik** → Long positioning increasing
-   **Trend turun** → Short positioning increasing

---

### 5. **History Chart** (`components/funding/history-chart.blade.php`)

**Tujuan:** Line chart historical funding rate dengan OHLC

**API Endpoint:**

```
GET /api/funding-rate/history?symbol=BTC&interval=4h&limit=100
```

**Features:**

-   ✅ Line chart dengan gradient
-   ✅ OHLC stats display
-   ✅ Tooltip dengan detail O/H/L/C
-   ✅ Interval selector (1h, 4h, 8h, 1d)

**Trading Interpretasi:**

-   **Trend naik konsisten** → Long bias strengthening
-   **Spike tiba-tiba** → Extreme positioning / squeeze risk
-   **High-low range besar** → Volatility tinggi dalam periode

---

## 🎨 Desain & Style Guide

### Color System

```css
/* Funding Rate Colors */
--funding-extreme-positive: #16a34a; /* > +0.1% */
--funding-high-positive: #22c55e; /* 0 to +0.1% */
--funding-low-negative: #f87171; /* -0.1 to 0 */
--funding-extreme-negative: #dc2626; /* < -0.1% */
```

### Bias Colors

-   🟩 **Long Bias:** Green gradient (#22c55e to #16a34a)
-   🟥 **Short Bias:** Red gradient (#ef4444 to #dc2626)
-   🟨 **Neutral:** Gray gradient (#6b7280 to #4b5563)

### Icons

-   🎯 Market Bias
-   🏢 Exchange Overview
-   📊 Aggregate Chart
-   ⚖️ OI-Weighted
-   📉 History
-   💡 Insights
-   🚨 Alerts
-   📈 Long trend
-   📉 Short trend
-   → Neutral trend

---

## 🔧 Technical Stack

-   **Frontend:** Laravel Blade + Bootstrap 5
-   **JS Framework:** Alpine.js (reactive state)
-   **Charts:** Chart.js v4 (DOM-based storage)
-   **HTTP:** Fetch API
-   **Styling:** Bootstrap 5 + Custom CSS
-   **State Management:** Event-driven communication
-   **Chart Storage:** DOM elements (non-reactive)

---

## 📡 API Endpoints Summary

| Endpoint                      | Method | Purpose                         | Key Fields                                |
| ----------------------------- | ------ | ------------------------------- | ----------------------------------------- |
| `/api/funding-rate/bias`      | GET    | Market bias classification      | bias, strength, avg_funding_close         |
| `/api/funding-rate/exchanges` | GET    | Exchange metadata               | exchange, funding_rate, next_funding_time |
| `/api/funding-rate/aggregate` | GET    | Accumulated funding by exchange | exchange, funding_rate, range_str         |
| `/api/funding-rate/weighted`  | GET    | OI-weighted funding             | open, high, low, close, time              |
| `/api/funding-rate/history`   | GET    | Historical OHLC                 | open, high, low, close, time              |

---

## 🚀 Usage

### Include Components in Blade View

```blade
{{-- Bias Card --}}
@include('components.funding.bias-card', ['symbol' => 'BTC'])

{{-- Exchange Table --}}
@include('components.funding.exchange-table', ['symbol' => 'BTC', 'limit' => 20])

{{-- Aggregate Chart --}}
@include('components.funding.aggregate-chart', ['symbol' => 'BTC', 'rangeStr' => '7d'])

{{-- Weighted Chart --}}
@include('components.funding.weighted-chart', ['symbol' => 'BTC', 'interval' => '4h'])

{{-- History Chart --}}
@include('components.funding.history-chart', ['symbol' => 'BTC', 'interval' => '4h'])
```

### Required Scripts

```blade
@section('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>

    <!-- Funding Rate Controller -->
    <script src="{{ asset('js/funding-rate-controller.js') }}"></script>
@endsection
```

---

## 🧠 Trading Insights

### Positive Funding Rate (🟩 Long Dominance)

```
Interpretasi:
- Longs membayar shorts untuk memegang posisi
- Market terlalu bullish / overleveraged long
- Risk: Long squeeze jika price gagal break resistance

Strategy:
- Consider shorting pada resistance
- Take profit untuk long positions
- Wait for correction sebelum re-enter long
```

### Negative Funding Rate (🟥 Short Dominance)

```
Interpretasi:
- Shorts membayar longs untuk memegang posisi
- Market terlalu bearish / overleveraged short
- Risk: Short squeeze pada positive catalyst

Strategy:
- Look for bounce setups
- Tight stops untuk short positions
- Wait for flush sebelum shorting lagi
```

### High Strength (⚠️ Extreme Positioning)

```
Interpretasi:
- Posisi sangat crowded di satu sisi
- High risk of forced liquidations / squeeze
- Volatility kemungkinan meningkat

Strategy:
- Reduce leverage
- Consider hedging positions
- Monitor closely untuk signs of reversal
```

### Exchange Spreads (⚡ Arbitrage)

```
Interpretasi:
- Large spread (> 50%) → Arbitrage opportunity atau exchange risk
- Consistent spread → Structural difference antar exchange
- Converging spread → Market normalizing

Strategy:
- Check volume dan liquidity sebelum arbitrage
- Consider transaction costs dan withdrawal times
- Monitor for exchange-specific issues
```

---

## 🔍 Component Architecture

```
funding-rate.blade.php (Main View)
├── bias-card.blade.php
│   └── Alpine.js: fundingBiasCard()
├── exchange-table.blade.php
│   └── Alpine.js: exchangeFundingTable()
├── aggregate-chart.blade.php
│   └── Alpine.js: aggregateFundingChart()
├── weighted-chart.blade.php
│   └── Alpine.js: weightedFundingChart()
└── history-chart.blade.php
    └── Alpine.js: historyFundingChart()

Global Controller: funding-rate-controller.js
└── fundingRateController()
    ├── Global state management
    ├── Event coordination
    └── Utility functions
```

---

## 📝 Best Practices

1. **Modular Components**

    - Setiap komponen self-contained
    - Props untuk configurability
    - Alpine.js untuk reactive state
    - **Chart storage di DOM elements (bukan Alpine data)**

2. **API Calls**

    - Error handling dengan try-catch
    - Loading states untuk UX
    - Auto-refresh setiap 30 detik
    - Proper parameter validation

3. **Performance**

    - Chart updates menggunakan `update('none')` untuk smooth animation
    - Debouncing untuk frequent updates
    - LocalStorage untuk caching preferences
    - **queueMicrotask() untuk break Alpine reactivity**

4. **Alpine + Chart.js Integration**

    - **NEVER store Chart instances in Alpine data**
    - Use `getChart()` dan `setChart()` helpers
    - Create gradients outside reactive callbacks
    - Deep clone data dengan `JSON.parse(JSON.stringify())`

5. **Trading Insights**
    - Color coding untuk quick decisions
    - Tooltips dengan detailed info
    - Alert panels untuk critical conditions

---

## 🎯 Future Enhancements

-   [ ] Add alert system untuk funding rate thresholds
-   [ ] Export data ke CSV
-   [ ] Compare multiple symbols side-by-side
-   [ ] Historical correlation dengan price movements
-   [ ] Push notifications untuk extreme funding rates
-   [ ] Mobile-optimized responsive layout
-   [ ] Dark mode toggle
-   [ ] Customizable dashboard layout (drag & drop)

---

## 📞 Support

Untuk pertanyaan atau issues, silakan hubungi tim development.

**Version:** 1.0.0  
**Last Updated:** October 2025  
**Tech Stack:** Laravel 11 + Bootstrap 5 + Alpine.js + Chart.js
