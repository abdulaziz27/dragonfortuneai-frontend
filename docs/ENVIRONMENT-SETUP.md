# 🔧 Environment Setup Guide

## 📋 Environment Variables

### API Base URL Configuration

Untuk mengatur base URL API, tambahkan variabel berikut di file `.env`:

```bash
# Base URL untuk API backend
# Kosongkan untuk menggunakan path relatif /api
API_BASE_URL=http://202.155.90.20:8000
```

### Complete .env Example

```bash
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=sqlite

CACHE_STORE=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

QUEUE_CONNECTION=sync

# Base URL untuk API backend
# Kosongkan untuk menggunakan path relatif /api
API_BASE_URL=http://202.155.90.20:8000
```

## 🚀 Deployment Configuration

### Production Environment

```bash
# Production .env
APP_NAME="DragonFortune AI"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# API Configuration
API_BASE_URL=https://api.yourdomain.com
```

### Staging Environment

```bash
# Staging .env
APP_NAME="DragonFortune AI"
APP_ENV=staging
APP_DEBUG=true
APP_URL=https://staging.yourdomain.com

# API Configuration
API_BASE_URL=https://staging-api.yourdomain.com
```

### Local Development

```bash
# Local .env
APP_NAME="DragonFortune AI"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# API Configuration
API_BASE_URL=http://202.155.90.20:8000
```

## 🔄 How It Works

1. **Environment Variable**: `API_BASE_URL` dibaca dari `.env`
2. **Config Service**: Disimpan di `config/services.php`
3. **Meta Tag**: Di-inject ke layout sebagai `<meta name="api-base-url">`
4. **JavaScript**: `funding-rate-controller.js` membaca meta tag dan menggunakan base URL

### Code Flow

```php
// config/services.php
'api' => [
    'base_url' => env('API_BASE_URL', ''),
],
```

```blade
{{-- resources/views/layouts/app.blade.php --}}
<meta name="api-base-url" content="{{ config('services.api.base_url') }}">
```

```javascript
// public/js/funding-rate-controller.js
const baseMeta = document.querySelector('meta[name="api-base-url"]');
const configuredBase = (baseMeta?.content || '').trim();

let url = `/api/funding-rate/${endpoint}?${queryString}`; // default relative
if (configuredBase) {
    const normalizedBase = configuredBase.endsWith('/')
        ? configuredBase.slice(0, -1)
        : configuredBase;
    url = `${normalizedBase}/api/funding-rate/${endpoint}?${queryString}`;
}
```

## 🛠️ Setup Instructions

### 1. Copy Environment File

```bash
cp .env.example .env
```

### 2. Set API Base URL

Edit file `.env` dan tambahkan:

```bash
API_BASE_URL=http://202.155.90.20:8000
```

### 3. Clear Config Cache

```bash
php artisan config:clear
php artisan config:cache
```

### 4. Verify Configuration

Check di browser console:

```javascript
// Should show: API Base: http://202.155.90.20:8000
console.log(document.querySelector('meta[name="api-base-url"]').content);
```

## 🔍 Troubleshooting

### API Base URL Not Working

1. **Check .env file**:
   ```bash
   grep API_BASE_URL .env
   ```

2. **Clear config cache**:
   ```bash
   php artisan config:clear
   ```

3. **Check meta tag**:
   ```html
   <meta name="api-base-url" content="http://202.155.90.20:8000">
   ```

4. **Check JavaScript console**:
   ```javascript
   // Should show the configured base URL
   console.log(document.querySelector('meta[name="api-base-url"]').content);
   ```

### Fallback to Relative Paths

Jika `API_BASE_URL` kosong atau tidak ada, aplikasi akan menggunakan path relatif:

```javascript
// Fallback behavior
let url = `/api/funding-rate/${endpoint}?${queryString}`;
```

Ini berguna untuk:
- Reverse proxy setup
- Same-origin API calls
- Development dengan Laravel serve

## 📝 Notes

- **Default Value**: Jika `API_BASE_URL` tidak diset, akan menggunakan path relatif
- **Trailing Slash**: Otomatis di-normalize (menghilangkan trailing slash)
- **Security**: Pastikan base URL yang digunakan aman dan terpercaya
- **CORS**: Pastikan API server mengizinkan CORS dari domain frontend

---

**Last Updated**: December 2024  
**Maintainer**: Development Team
