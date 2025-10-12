# Orderbook Snapshots - Market Microstructure Dashboard

## 📋 Overview

Dashboard lengkap untuk analisis mikrostruktur orderbook yang menampilkan pressure analysis, liquidity distribution, market depth, dan live orderbook snapshots.

## 🎯 Fitur Utama

### 1. **Book Pressure Analysis**

-   **API**: `/api/spot-microstructure/book-pressure`
-   **Visualisasi**:
    -   Card dengan metrics bid/ask pressure
    -   Progress bar untuk visualisasi rasio
    -   Historical chart untuk trend analysis
-   **Metrik**:
    -   Bid Pressure
    -   Ask Pressure
    -   Pressure Ratio
    -   Pressure Direction (bullish/bearish/neutral)

### 2. **Liquidity Imbalance**

-   **API**: `/api/spot-microstructure/orderbook/liquidity`
-   **Visualisasi**: Stats panel
-   **Metrik**:
    -   Total Liquidity
    -   Bid/Ask Ratio
    -   Imbalance (absolute)
    -   Imbalance Percentage

### 3. **Market Depth Statistics**

-   **API**: `/api/spot-microstructure/market-depth`
-   **Visualisasi**: Stats panel + table
-   **Metrik**:
    -   Depth Score (stabilitas market)
    -   Bid/Ask Levels
    -   Total Bid/Ask Volume
    -   Historical data table

### 4. **Live Orderbook Snapshot**

-   **API**: `/api/spot-microstructure/orderbook/snapshot`
-   **Visualisasi**: Real-time orderbook display
-   **Features**:
    -   Top 10 bids dan asks
    -   Visual depth bars
    -   Mid price dan spread display
    -   Auto-refresh setiap 5 detik

### 5. **Liquidity Distribution**

-   **API**: `/api/spot-microstructure/liquidity-heatmap`
-   **Visualisasi**: Table dengan progress bars untuk price level distribution
-   **Purpose**: Mendeteksi liquidity walls dan support/resistance levels

### 6. **Orderbook Depth Details**

-   **API**: `/api/spot-microstructure/orderbook-depth`
-   **Visualisasi**: Detailed table dengan level-by-level analysis
-   **Metrik**:
    -   Level (1 = closest to market)
    -   Bid/Ask Price, Quantity, dan Total
    -   Cumulative totals

### 7. **Quick Stats**

-   **API**: `/api/spot-microstructure/orderbook/snapshot` (depth=1)
-   **Metrik**:
    -   Mid Price
    -   Current Spread
    -   Spread Percentage
    -   Market Status indicator

## 🏗️ Struktur File

```
resources/views/
├── spot-microstructure/
│   └── orderbook-snapshots.blade.php (Main dashboard)
└── components/
    └── orderbook/
        ├── pressure-card.blade.php
        ├── liquidity-imbalance.blade.php
        ├── market-depth-stats.blade.php
        ├── quick-stats.blade.php
        ├── live-snapshot.blade.php
        ├── pressure-chart.blade.php
        ├── liquidity-heatmap-chart.blade.php
        ├── market-depth-table.blade.php
        └── orderbook-depth-table.blade.php

public/js/
└── orderbook-controller.js (Alpine.js controllers)
```

## 🎨 Design Pattern

Dashboard mengikuti design pattern yang sama dengan funding-rate dan trades dashboard:

1. **Global Controls**:

    - Symbol selector (BTC, ETH, SOL, etc.)
    - Exchange selector (Binance, OKX, Bybit, Bitget)
    - Refresh All button

2. **Event-Driven Architecture**:

    - `symbol-changed` event
    - `exchange-changed` event
    - `refresh-all` event
    - Auto-refresh untuk real-time components

3. **Modular Components**:

    - Setiap component adalah Alpine.js component terpisah
    - Component listening ke global events
    - Independent loading states

4. **Color Coding**:
    - 🟩 Green (Bullish): Bid pressure, buy orders
    - 🟥 Red (Bearish): Ask pressure, sell orders
    - 🔵 Blue (Neutral/Info): General information

## 📊 API Endpoints Usage

### 1. Book Pressure

```javascript
GET /api/spot-microstructure/book-pressure
Params:
  - symbol: BTCUSDT
  - exchange: binance
  - limit: 100
```

### 2. Liquidity Heatmap

```javascript
GET /api/spot-microstructure/liquidity-heatmap
Params:
  - symbol: BTCUSDT
  - exchange: binance
  - limit: 50
```

### 3. Market Depth

```javascript
GET /api/spot-microstructure/market-depth
Params:
  - symbol: BTCUSDT
  - exchange: binance
  - limit: 20
```

### 4. Orderbook Snapshot

```javascript
GET /api/spot-microstructure/orderbook/snapshot
Params:
  - symbol: BTCUSDT
  - depth: 15
```

### 5. Orderbook Depth

```javascript
GET /api/spot-microstructure/orderbook-depth
Params:
  - symbol: BTCUSDT
  - exchange: binance
  - limit: 20
```

### 6. Orderbook Liquidity

```javascript
GET /api/spot-microstructure/orderbook/liquidity
Params:
  - symbol: BTCUSDT
  - depth: 20
```

### 7. Orderbook (Granular)

```javascript
GET /api/spot-microstructure/orderbook
Params:
  - symbol: BTCUSDT
  - side: bid/ask (optional)
  - limit: 2000
```

## 🔧 Technical Implementation

### Alpine.js Components

1. **orderbookController()** - Main dashboard controller
2. **bookPressureCard()** - Book pressure analysis
3. **liquidityImbalance()** - Liquidity imbalance metrics
4. **marketDepthStats()** - Market depth statistics
5. **quickStats()** - Quick market stats
6. **liveOrderbookSnapshot()** - Real-time orderbook
7. **bookPressureChart()** - Pressure history chart
8. **liquidityHeatmapChart()** - Liquidity distribution chart
9. **marketDepthTable()** - Market depth table
10. **orderbookDepthTable()** - Orderbook depth table

### Chart.js Integration

-   Book Pressure Chart: Line chart dengan dual datasets (bid/ask)
-   Liquidity Distribution: Table dengan progress bars untuk price level distribution
-   Auto-scaling dan responsive design
-   Time-based x-axis untuk historical data

### API Base URL Configuration

API base URL diambil dari meta tag:

```html
<meta name="api-base-url" content="{{ config('services.api.base_url') }}" />
```

Default: `https://test.dragonfortune.ai`

## 📱 Features

### Auto-Refresh

-   Live Orderbook Snapshot: auto-refresh setiap 5 detik
-   Manual refresh via "Refresh All" button
-   Event-driven updates untuk semua components

### Error Handling

-   Try-catch pada semua API calls
-   Fallback ke empty state jika API error
-   Loading indicators untuk setiap component
-   Console logging untuk debugging

### Responsive Design

-   Mobile-friendly layout
-   Sticky table headers
-   Scrollable tables dengan max-height
-   Flexbox-based grid system

## 🎯 Trading Interpretasi

### Bullish Signals (🟩)

-   Bid pressure > Ask pressure (ratio > 1)
-   Positive liquidity imbalance
-   High bid depth at key levels
-   Pressure direction: "bullish"
-   Strong bid liquidity walls

### Bearish Signals (🟥)

-   Ask pressure > Bid pressure (ratio < 1)
-   Negative liquidity imbalance
-   High ask depth at resistance
-   Pressure direction: "bearish"
-   Strong ask liquidity walls

### Neutral Market

-   Balanced bid/ask pressure (ratio ≈ 1)
-   Low imbalance percentage
-   Even liquidity distribution
-   Pressure direction: "neutral"

## 🔍 Key Concepts

1. **Book Pressure**: Rasio kekuatan bid vs ask untuk mengidentifikasi arah momentum
2. **Depth Score**: Ukuran stabilitas dan keseimbangan market (0-100)
3. **Imbalance**: Ketidakseimbangan likuiditas yang dapat mengindikasikan pergerakan harga
4. **Liquidity Walls**: Level harga dengan volume besar yang bertindak sebagai support/resistance

## 🚀 Usage

### Route

```
/spot-microstructure/orderbook-snapshots
```

### Navigation

Dashboard → Spot Microstructure → Orderbook Snapshots

### Default Settings

-   Symbol: BTCUSDT
-   Exchange: Binance
-   Auto-refresh: 5s (untuk live snapshot)

## ⚙️ Configuration

### Environment Variables

```env
API_BASE_URL=https://test.dragonfortune.ai
```

### Component Customization

Setiap component dapat di-customize melalui Alpine.js x-data attributes di blade files.

## 📝 Notes

-   Semua API menggunakan base URL dari backend: `https://test.dragonfortune.ai`
-   Dashboard fully reactive dengan Alpine.js
-   No page refresh required untuk updates
-   Console logs untuk debugging (dapat di-disable di production)
-   Error handling dengan graceful fallbacks

## 🔮 Future Enhancements

1. WebSocket integration untuk real-time updates
2. Historical comparison features
3. Export data functionality
4. Alert system untuk imbalance extremes
5. Multi-exchange comparison view
6. Custom depth level selection
7. Price alerts pada liquidity walls

## 📚 References

-   [Funding Rate Dashboard](./FUNDING-RATE-IMPLEMENTATION.md)
-   [Trades CVD Dashboard](./TRADES-CVD-IMPLEMENTATION.md)
-   [API Documentation](../dokumentasi_backend.md)
