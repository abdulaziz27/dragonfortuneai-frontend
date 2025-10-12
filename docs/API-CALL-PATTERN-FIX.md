# API Call Pattern Fix - Mixed Content Security Issue

## Problem Description

**Error di VPS:** Mixed Content Security Policy
```
Mixed Content: The page at '<URL>' was loaded over HTTPS, but requested an insecure resource '<URL>'. This request has been blocked; the content must be served over HTTPS.
```

**Root Cause:** Open Interest menggunakan hardcode HTTP URL sebagai fallback, sedangkan Funding Rate menggunakan relative URL.

## Perbedaan Implementation

### ✅ Funding Rate (BENAR)
```javascript
// public/js/funding-rate-controller.js
async fetchAPI(endpoint, params = {}) {
    let url = `/api/funding-rate/${endpoint}?${queryString}`; // default relative
    if (configuredBase) {
        url = `${normalizedBase}/api/funding-rate/${endpoint}?${queryString}`;
    }
}
```

### ❌ Open Interest (SALAH - sebelum fix)
```javascript
// resources/views/derivatives/open-interest.blade.php
async fetchAPI(endpoint, params = {}) {
    if (configuredBase) {
        url = `${normalizedBase}/api/open-interest/${endpoint}?${queryString}`;
    } else {
        // ❌ Hardcode HTTP fallback
        url = `https://test.dragonfortune.ai/api/open-interest/${endpoint}?${queryString}`;
    }
}
```

## Solusi yang Diterapkan

### 1. Fix fetchAPI Method
```javascript
// ✅ FIXED - Mengikuti pattern funding rate
async fetchAPI(endpoint, params = {}) {
    let url = `/api/open-interest/${endpoint}?${queryString}`; // default relative
    if (configuredBase) {
        const normalizedBase = configuredBase.endsWith("/")
            ? configuredBase.slice(0, -1)
            : configuredBase;
        url = `${normalizedBase}/api/open-interest/${endpoint}?${queryString}`;
    }
}
```

### 2. Fix loadHistoryData Method
```javascript
// ✅ FIXED - Remove hardcode HTTP fallback
let url = `/api/open-interest/history?${params.toString()}`;
if (configuredBase) {
    const normalizedBase = configuredBase.endsWith("/")
        ? configuredBase.slice(0, -1)
        : configuredBase;
    url = `${normalizedBase}/api/open-interest/history?${params.toString()}`;
}
```

## Mengapa Ini Bekerja?

### Relative URL Behavior:
- **Lokal (HTTP):** `/api/open-interest/analytics` → `http://localhost:8000/api/open-interest/analytics`
- **VPS (HTTPS):** `/api/open-interest/analytics` → `https://yourdomain.com/api/open-interest/analytics`

### Configuration Flow:
1. **Meta Tag:** `<meta name="api-base-url" content="{{ config('services.api.base_url') }}">`
2. **Config:** `config/services.php` → `'base_url' => env('API_BASE_URL', 'https://test.dragonfortune.ai')`
3. **Environment:** 
   - **Lokal:** `API_BASE_URL` tidak set → menggunakan relative URL
   - **VPS:** `API_BASE_URL=https://yourdomain.com` → menggunakan configured URL

## Testing

### Local Development:
```bash
# Should work with both patterns
curl http://localhost:8000/api/open-interest/analytics?symbol=BTCUSDT
```

### VPS Production:
```bash
# Should now work with HTTPS
curl https://yourdomain.com/api/open-interest/analytics?symbol=BTCUSDT
```

## Best Practices

1. **Always use relative URL as default** untuk menghindari mixed content issues
2. **Only use absolute URL** ketika ada explicit configuration
3. **Follow consistent pattern** across all modules (funding rate, open interest, dll)
4. **Test both local and production** environments

## Files Modified

- `resources/views/derivatives/open-interest.blade.php`
  - Fixed `fetchAPI()` method
  - Fixed `loadHistoryData()` method
  - Removed hardcode HTTP fallback URLs

## Impact

- ✅ **Local development:** No change (still works)
- ✅ **VPS production:** Fixed mixed content error
- ✅ **Consistency:** Now follows same pattern as funding rate
- ✅ **Security:** No more HTTP requests from HTTPS pages
