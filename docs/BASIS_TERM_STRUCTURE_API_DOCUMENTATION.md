# Dokumentasi API Basis & Term Structure - Backend API v2

## 1. Analytics Basis & Term Structure

### Endpoint URL
```
GET https://test.dragonfortune.ai/api/basis/analytics
```

### Deskripsi Singkat
Mengambil data analisis komprehensif basis dan struktur pasar futures termasuk deteksi contango/backwardation, volatilitas basis, dan market structure insights.

### Parameter Request
| Parameter | Tipe | Required | Default | Deskripsi |
|-----------|------|----------|---------|-----------|
| `exchange` | string | Ya | Binance | Nama exchange (Binance, Bybit, OKX, Deribit, dll.) |
| `spot_pair` | string | Ya | BTC/USDT | Pasangan spot trading (BTC/USDT, ETH/USDT) |
| `futures_symbol` | string | Ya | BTCUSDT | Simbol kontrak futures (BTCUSDT, BTC-PERP, dll.) |
| `interval` | string | Tidak | 5m | Interval waktu (5m, 15m, 1h, 4h) |
| `limit` | integer | Tidak | 2000 | Jumlah maksimal record yang dikembalikan |

### Contoh cURL Request
```bash
curl -X GET "https://test.dragonfortune.ai/api/basis/analytics?exchange=Binance&spot_pair=BTC/USDT&futures_symbol=BTCUSDT&interval=15m&limit=1000"
```

### Contoh Response Body
```json
[
  {
    "market_structure": "contango",
    "basis_abs": 125.50,
    "basis_annualized": 0.0285,
    "trend": "increasing",
    "exchange": "Binance",
    "spot_pair": "BTC/USDT",
    "futures_symbol": "BTCUSDT",
    "basis_volatility": 0.0045,
    "data_points": 1000
  }
]
```

### Kegunaan / Tujuan Endpoint
Endpoint ini digunakan untuk menampilkan panel analytics basis pada dashboard. Data ini memberikan insights tentang struktur pasar futures, kondisi contango/backwardation, dan volatilitas basis untuk strategi calendar spreads dan arbitrase.

---

## 2. Historical Basis Data

### Endpoint URL
```
GET https://test.dragonfortune.ai/api/basis/history
```

### Deskripsi Singkat
Mengambil data historis basis antara spot dan futures untuk analisis tren dan pembuatan chart time series dengan overlay harga.

### Parameter Request
| Parameter | Tipe | Required | Default | Deskripsi |
|-----------|------|----------|---------|-----------|
| `exchange` | string | Ya | Binance | Nama exchange (Binance, Bybit, OKX, Deribit, dll.) |
| `spot_pair` | string | Ya | BTC/USDT | Pasangan spot trading (BTC/USDT, ETH/USDT) |
| `futures_symbol` | string | Ya | BTCUSDT | Simbol kontrak futures (BTCUSDT, BTC-PERP, dll.) |
| `interval` | string | Tidak | 5m | Interval waktu (5m, 15m, 1h, 4h) |
| `limit` | integer | Tidak | 2000 | Jumlah maksimal record yang dikembalikan |

### Contoh cURL Request
```bash
curl -X GET "https://test.dragonfortune.ai/api/basis/history?exchange=Binance&spot_pair=BTC/USDT&futures_symbol=BTCUSDT&interval=1h&limit=500"
```

### Contoh Response Body
```json
[
  {
    "ts": 1704067200000,
    "basis_abs": 125.50,
    "basis_annualized": 0.0285,
    "spot_price": 45000.0,
    "futures_price": 45125.50
  },
  {
    "ts": 1704063600000,
    "basis_abs": 118.20,
    "basis_annualized": 0.0268,
    "spot_price": 44950.0,
    "futures_price": 45068.20
  }
]
```

### Kegunaan / Tujuan Endpoint
Endpoint ini digunakan untuk menampilkan chart historis basis pada dashboard. Data time series ini memungkinkan trader melihat evolusi basis dari waktu ke waktu dan mengidentifikasi pola contango/backwardation untuk timing entry/exit.

---

## 3. Term Structure Analysis

### Endpoint URL
```
GET https://test.dragonfortune.ai/api/basis/term-structure
```

### Deskripsi Singkat
Mengambil data struktur term (curve) basis across multiple expiries untuk analisis yield curve dan calendar spread opportunities.

### Parameter Request
| Parameter | Tipe | Required | Default | Deskripsi |
|-----------|------|----------|---------|-----------|
| `symbol` | string | Ya | BTC | Simbol base (BTC, ETH) |
| `exchange` | string | Ya | Binance | Nama exchange (Binance, Bybit, OKX, Deribit, dll.) |
| `limit` | integer | Tidak | 1000 | Jumlah maksimal record yang dikembalikan |

### Contoh cURL Request
```bash
curl -X GET "https://test.dragonfortune.ai/api/basis/term-structure?symbol=BTC&exchange=Binance&limit=500"
```

### Contoh Response Body
```json
{
  "expiries": ["perpetual", "weekly", "monthly", "quarterly"],
  "basis_curve": [
    {
      "expiry": "perpetual",
      "basis": 0.0,
      "basis_annualized": 0.0,
      "volatility": 0.0025
    },
    {
      "expiry": "weekly",
      "basis": 25.50,
      "basis_annualized": 0.0285,
      "volatility": 0.0045
    },
    {
      "expiry": "monthly",
      "basis": 85.20,
      "basis_annualized": 0.0195,
      "volatility": 0.0035
    },
    {
      "expiry": "quarterly",
      "basis": 125.80,
      "basis_annualized": 0.0115,
      "volatility": 0.0028
    }
  ],
  "term_structure": {
    "perpetual": {
      "basis": 0.0,
      "basis_annualized": 0.0,
      "data_points": 100
    },
    "weekly": {
      "basis": 25.50,
      "basis_annualized": 0.0285,
      "data_points": 95
    },
    "monthly": {
      "basis": 85.20,
      "basis_annualized": 0.0195,
      "data_points": 88
    },
    "quarterly": {
      "basis": 125.80,
      "basis_annualized": 0.0115,
      "data_points": 75
    }
  }
}
```

### Kegunaan / Tujuan Endpoint
Endpoint ini digunakan untuk menampilkan term structure curve pada dashboard basis. Data ini memberikan gambaran lengkap yield curve futures dan membantu trader mengidentifikasi mispricing antar expiries untuk calendar spread strategies.