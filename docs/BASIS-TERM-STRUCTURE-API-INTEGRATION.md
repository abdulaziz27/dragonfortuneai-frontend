# Basis & Term Structure - API Integration

## 📋 Ringkasan Perubahan

Dokumentasi ini menjelaskan integrasi lengkap API untuk modul **Basis & Term Structure** pada Dashboard Trading.

### ✅ Yang Telah Dilakukan

1. **Menghapus Data Dummy**

    - ❌ Dihapus: Fungsi `generateBasisData()` dan `generateDateLabels()`
    - ❌ Dihapus: Inisialisasi chart dengan data hardcoded
    - ✅ Diganti: Semua chart sekarang menggunakan data real dari API

2. **Implementasi Chart dengan Data Real**

    - ✅ **Basis History Chart**: Mengambil data dari `/api/basis/history`
    - ✅ **Term Structure Chart**: Mengambil data dari `/api/basis/term-structure`

3. **Komponen Alpine.js yang Dibuat**
    - `basisHistoryChart()`: Mengelola chart history basis
    - `termStructureChart()`: Mengelola chart term structure
    - `marketStructureCard()`: Menampilkan overview market structure (sudah ada sebelumnya)
    - `quickStatsPanel()`: Menampilkan statistik cepat (sudah ada sebelumnya)
    - `analyticsTable()`: Menampilkan tabel analytics (sudah ada sebelumnya)

---

## 🔌 Endpoint API yang Digunakan

### 1. `/api/basis/analytics`

**Digunakan oleh:** `marketStructureCard()`, `quickStatsPanel()`, `analyticsTable()`

**Parameter:**

-   `exchange`: Nama exchange (contoh: Binance, Bybit, OKX)
-   `spot_pair`: Symbol spot pair (contoh: BTCUSDT)
-   `futures_symbol`: Symbol futures (contoh: BTCUSDT_240628)
-   `interval`: Interval waktu (contoh: 1h, 4h, 1d)
-   `limit`: Jumlah data maksimal (default: 2000)

**Response:**

```json
{
    "basis_abs": {
        "average": 0,
        "current": 0,
        "max": 0,
        "median": 0,
        "min": 0,
        "range": 0,
        "std_dev": 0
    },
    "basis_distribution": {
        "negative_pct": 0,
        "negative_periods": 0,
        "neutral_periods": 0,
        "positive_pct": 0,
        "positive_periods": 0
    },
    "market_structure": "contango",
    "trend": {
        "direction": "widening",
        "magnitude": 0
    },
    "insights": []
}
```

### 2. `/api/basis/history`

**Digunakan oleh:** `basisHistoryChart()`, `quickStatsPanel()`

**Parameter:**

-   `exchange`: Nama exchange
-   `spot_pair`: Symbol spot pair
-   `futures_symbol`: Symbol futures
-   `interval`: Interval waktu
-   `limit`: Jumlah data maksimal (default: 2000)

**Response:**

```json
{
    "data": [
        {
            "ts": 1234567890000,
            "exchange": "Binance",
            "spot_pair": "BTCUSDT",
            "futures_symbol": "BTCUSDT_240628",
            "price_spot": 50000,
            "price_futures": 50150,
            "basis_abs": 150,
            "basis_annualized": 0.0365,
            "expiry": 1234567890000
        }
    ]
}
```

### 3. `/api/basis/term-structure`

**Digunakan oleh:** `termStructureChart()`

**Parameter:**

-   `exchange`: Nama exchange (REQUIRED)
-   `spot_pair`: Symbol spot pair (REQUIRED)
-   `max_contracts`: Jumlah kontrak maksimal (default: 20)

**Response:**

```json
{
    "data": [
        {
            "exchange": "Binance",
            "spot_pair": "BTCUSDT",
            "futures_symbol": "BTCUSDT_240628",
            "instrument_id": "BTCUSDT_240628",
            "expiry": 1234567890000,
            "price_spot": 50000,
            "price_futures": 50150,
            "basis_abs": 150,
            "basis_annualized": 0.0365
        }
    ]
}
```

---

## 🎨 Komponen Chart

### Basis History Chart

**Lokasi:** `resources/views/derivatives/basis-term-structure.blade.php` (line ~1070)

**Fitur:**

-   Menampilkan historical basis movement over time
-   Line chart dengan zero line reference
-   Auto-refresh setiap 30 detik
-   Responsif terhadap perubahan filter (symbol, exchange, interval)
-   Tooltip interaktif dengan format currency

**Implementasi:**

```javascript
function basisHistoryChart() {
    return {
        symbol: "BTC",
        exchange: "Binance",
        interval: "1h",
        chart: null,

        init() {
            this.initChart();
            this.loadData();
        },

        async loadData() {
            // Fetch dari /api/basis/history
        },

        updateChart(historyData) {
            // Update chart dengan data real
        },
    };
}
```

### Term Structure Chart

**Lokasi:** `resources/views/derivatives/basis-term-structure.blade.php` (line ~1285)

**Fitur:**

-   Menampilkan basis across different contract expiries
-   Bar chart dengan color coding:
    -   🟢 Hijau: Contango (basis positif)
    -   🔴 Merah: Backwardation (basis negatif)
    -   ⚪ Abu-abu: Neutral
-   Auto-refresh setiap 30 detik
-   Responsif terhadap perubahan filter
-   Tooltip dengan annualized basis

**Implementasi:**

```javascript
function termStructureChart() {
    return {
        symbol: "BTC",
        exchange: "Binance",
        chart: null,

        init() {
            this.initChart();
            this.loadData();
        },

        async loadData() {
            // Fetch dari /api/basis/term-structure
        },

        updateChart(termStructureData) {
            // Update chart dengan data real
            // Color coding berdasarkan nilai basis
        },
    };
}
```

---

## 🔄 Event Communication

Dashboard menggunakan sistem event untuk komunikasi antar komponen:

### Events yang Di-dispatch:

1. **`symbol-changed`**: Ketika user mengubah symbol (BTC, ETH, dll)
2. **`exchange-changed`**: Ketika user mengubah exchange (Binance, Bybit, dll)
3. **`interval-changed`**: Ketika user mengubah interval (5m, 1h, dll)
4. **`refresh-all`**: Ketika user klik tombol Refresh All
5. **`basis-overview-ready`**: Ketika data overview siap (dari controller)

### Event Listeners:

Setiap komponen mendengarkan event-event di atas dan akan:

-   Update data internal
-   Fetch data baru dari API
-   Update display/chart

**Contoh:**

```javascript
window.addEventListener("symbol-changed", (e) => {
    this.symbol = e.detail?.symbol || this.symbol;
    this.loadData(); // Fetch data baru
});
```

---

## 🔧 Catatan Teknis

### 1. Futures Symbol Hardcoded

Saat ini, `futures_symbol` masih menggunakan hardcoded value:

```javascript
const futuresSymbol = `${symbol}USDT_240628`;
```

**Alasan:**

-   Blueprint API tidak menyediakan endpoint untuk auto-discovery futures symbols
-   Endpoint `/api/basis/term-structure` bisa auto-discover, tapi endpoint lain perlu explicit symbol

**Solusi Future:**

-   Backend bisa menambahkan endpoint `/api/basis/available-futures` untuk list available futures
-   Atau gunakan endpoint term-structure untuk auto-discover, lalu gunakan hasil tersebut

### 2. API Base URL

Sistem mendukung flexible API base URL melalui meta tag:

```html
<meta name="api-base-url" content="https://test.dragonfortune.ai" />
```

Jika tidak ada, akan menggunakan relative URL (untuk development dengan proxy).

### 3. Error Handling

Semua fetch dilengkapi dengan try-catch:

```javascript
try {
    const response = await fetch(url);
    const data = await response.json();
    // Process data
} catch (error) {
    console.error("❌ Error:", error);
    // Continue with empty data
}
```

### 4. Auto-refresh

Semua komponen di-set untuk auto-refresh setiap 30 detik:

```javascript
setInterval(() => this.loadData(), 30000);
```

### 5. Chart.js Time Scale

Basis History Chart menggunakan time scale:

```javascript
scales: {
    x: {
        type: 'time',
        time: {
            unit: 'hour',
            displayFormats: {
                hour: 'MMM dd HH:mm'
            }
        }
    }
}
```

Memerlukan library: `chartjs-adapter-date-fns`

---

## 🧪 Testing

### Manual Testing Checklist:

1. **Load Page**

    - ✅ Semua komponen load tanpa error
    - ✅ Charts menampilkan data (bukan dummy)
    - ✅ Market structure card menampilkan data analytics

2. **Change Symbol**

    - ✅ Pilih symbol berbeda (ETH, SOL, dll)
    - ✅ Semua chart dan stats update
    - ✅ Data sesuai dengan symbol yang dipilih

3. **Change Exchange**

    - ✅ Pilih exchange berbeda (Bybit, OKX, dll)
    - ✅ Semua chart dan stats update
    - ✅ Data sesuai dengan exchange yang dipilih

4. **Change Interval**

    - ✅ Pilih interval berbeda (5m, 1h, 4h, dll)
    - ✅ Basis History Chart update dengan interval baru
    - ✅ Analytics update dengan interval baru

5. **Refresh All**

    - ✅ Klik tombol "Refresh All"
    - ✅ Semua komponen reload data
    - ✅ Loading indicator muncul

6. **Auto-refresh**
    - ✅ Tunggu 30 detik
    - ✅ Data update otomatis tanpa page reload

### Console Debugging:

Buka browser console untuk melihat:

```
🚀 Basis & Term Structure Dashboard initialized
📊 Symbol: BTC
🏢 Exchange: Binance
📡 Fetching: analytics {...}
✅ Received: analytics summary
📡 Fetching: history {...}
✅ Received: history 2000 items
📡 Fetching: term-structure {...}
✅ Received: term-structure 5 items
```

---

## 📝 Files Modified

1. **resources/views/derivatives/basis-term-structure.blade.php**

    - Dihapus: Fungsi dummy data generator
    - Ditambah: `basisHistoryChart()` component
    - Ditambah: `termStructureChart()` component
    - Modified: HTML untuk menambahkan `x-data` attributes

2. **public/js/basis-term-structure-controller.js**
    - Tidak ada perubahan (sudah bagus dari awal)
    - Controller sudah memiliki fungsi `fetchAPI()` yang reusable
    - Event system sudah berjalan dengan baik

---

## 🎯 Trading Interpretation

Dashboard sekarang menampilkan data real yang membantu trader memahami:

1. **Contango vs Backwardation**

    - Positive basis (Contango) → Futures > Spot → Market expects higher prices
    - Negative basis (Backwardation) → Spot > Futures → Supply shortage

2. **Basis Trend**

    - Widening → Opportunity expanding
    - Narrowing → Convergence approaching

3. **Term Structure**

    - Steep curve → Strong expectations
    - Flat curve → Neutral market
    - Inverted curve → Supply constraints

4. **Arbitrage Opportunities**
    - Large basis spreads → Potential profit
    - Cross-exchange differences → Inter-exchange arbitrage

---

## 🚀 Next Steps (Optional Improvements)

1. **Dynamic Futures Discovery**

    - Buat endpoint atau helper untuk auto-discover available futures
    - Dropdown untuk select specific futures contract

2. **Multiple Contracts Comparison**

    - Compare basis dari multiple contracts dalam satu chart
    - Switch antara different expiry dates

3. **Historical Comparison**

    - Compare current basis dengan historical averages
    - Percentile bands untuk context

4. **Alert System**

    - Set alerts ketika basis melewati threshold tertentu
    - Notification untuk arbitrage opportunities

5. **Export Data**
    - Download data sebagai CSV/JSON
    - Share chart as image

---

## 📚 References

-   **API Documentation**: Lihat query user untuk full API specs
-   **Chart.js**: https://www.chartjs.org/
-   **Alpine.js**: https://alpinejs.dev/
-   **Basis Trading**: https://www.investopedia.com/terms/b/basis-trading.asp

---

**Dibuat:** 2025-10-11  
**Status:** ✅ Complete - All dummy data removed, using real API data  
**Author:** AI Assistant
