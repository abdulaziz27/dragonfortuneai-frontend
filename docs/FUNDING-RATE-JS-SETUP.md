# 🔧 Funding Rate JavaScript Setup Guide

## 📂 JavaScript File Location Options

Ada dua cara untuk meletakkan file `funding-rate-controller.js`:

### Option 1: Public Directory (✅ Recommended untuk Quick Setup)

**Location:** `public/js/funding-rate-controller.js`

**Pros:**

-   ✅ Tidak perlu compile
-   ✅ Langsung accessible via `{{ asset('js/funding-rate-controller.js') }}`
-   ✅ Quick development iteration
-   ✅ No build step required

**Cons:**

-   ❌ Tidak ter-minify
-   ❌ Tidak ter-bundle dengan dependencies
-   ❌ Tidak ada tree-shaking

**Usage:**

```blade
@section('scripts')
    <script src="{{ asset('js/funding-rate-controller.js') }}"></script>
@endsection
```

---

### Option 2: Resources Directory (⚡ Recommended untuk Production)

**Location:** `resources/js/funding-rate-controller.js`

**Pros:**

-   ✅ Ter-minify otomatis
-   ✅ Bundled dengan Vite
-   ✅ Better caching
-   ✅ Tree-shaking untuk smaller bundle

**Cons:**

-   ❌ Perlu run `npm run build` atau `npm run dev`
-   ❌ Extra build step

**Setup Steps:**

1. **Move file ke resources/js:**

```bash
mv public/js/funding-rate-controller.js resources/js/
```

2. **Import di resources/js/app.js:**

```javascript
import "./bootstrap";
import "./funding-rate-controller";
```

3. **Run Vite:**

```bash
# Development mode (hot reload)
npm run dev

# Production build
npm run build
```

4. **Update Blade Template:**

```blade
@section('scripts')
    {{-- Already loaded via @vite in layout --}}
    {{-- No need to include separately --}}
@endsection
```

---

## 🔍 Current Implementation

Saat ini menggunakan **Option 1 (Public Directory)** untuk kemudahan development.

**File:** `public/js/funding-rate-controller.js`

**Loaded in:** `resources/views/derivatives/funding-rate.blade.php`

```blade
@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    <script src="{{ asset('js/funding-rate-controller.js') }}"></script>
@endsection
```

---

## 🐛 Troubleshooting

### Error: "globalLoading is not defined"

**Cause:** Layout tidak punya `@yield('scripts')` section

**Fix:**

```blade
<!-- In resources/views/layouts/app.blade.php -->
@livewireScripts

{{-- Add this --}}
@yield('scripts')
</body>
```

---

### Error: "Chart is not defined"

**Cause:** Chart.js belum loaded saat Alpine.js init components

**Fix:**

1. Load Chart.js dengan specific version:

```html
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
```

2. Add Promise untuk wait:

```javascript
window.chartJsReady = new Promise((resolve) => {
    if (typeof Chart !== "undefined") {
        resolve();
    } else {
        setTimeout(() => resolve(), 100);
    }
});
```

3. Await di component init:

```javascript
async init() {
    if (typeof Chart === 'undefined') {
        await window.chartJsReady;
    }
    this.initChart();
}
```

---

### Error: "$forceUpdate is not a function"

**Cause:** Alpine.js 3 tidak punya `$forceUpdate()` method

**Fix:**

```javascript
// ❌ Old way (Alpine v2)
setInterval(() => this.$forceUpdate(), 1000);

// ✅ New way (Alpine v3)
setInterval(() => {
    this.exchanges = [...this.exchanges];
}, 1000);
```

---

### Error: "funding-rate-controller.js 404 Not Found"

**Cause:** File tidak ada di public directory

**Check:**

```bash
ls -la public/js/funding-rate-controller.js
```

**If missing, restore:**

```bash
# File should be in public/js/ directory
# Make sure it exists and is readable
```

---

## 📝 Load Order Best Practices

**Correct load order:**

1. **Alpine.js** (via Vite in layout)
2. **Chart.js** (CDN in @section('scripts'))
3. **Chart.js Adapter** (date-fns)
4. **Funding Rate Controller** (custom JS)
5. **Alpine Init** (happens automatically)

**Example:**

```blade
@section('scripts')
    <!-- 1. Chart.js MUST load first -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

    <!-- 2. Wait helper -->
    <script>
        window.chartJsReady = new Promise((resolve) => {
            if (typeof Chart !== 'undefined') resolve();
            else setTimeout(() => resolve(), 100);
        });
    </script>

    <!-- 3. Custom controller -->
    <script src="{{ asset('js/funding-rate-controller.js') }}"></script>

    <!-- 4. Alpine.js already loaded via layout -->
@endsection
```

---

## ✅ Verification Checklist

Before deploying, verify:

-   [ ] `@yield('scripts')` ada di layout
-   [ ] Chart.js loaded dengan version spesifik
-   [ ] `window.chartJsReady` Promise exists
-   [ ] All components menggunakan `async init()` dengan await
-   [ ] No `$forceUpdate()` calls (use spread operator)
-   [ ] funding-rate-controller.js accessible
-   [ ] Browser console shows no errors
-   [ ] All charts rendering correctly
-   [ ] Data loading from API
-   [ ] No Alpine.js warnings

---

## 🚀 Production Deployment

**Recommended steps for production:**

1. **Move to Vite bundling:**

```bash
mv public/js/funding-rate-controller.js resources/js/
```

2. **Update app.js:**

```javascript
// resources/js/app.js
import "./funding-rate-controller";
```

3. **Build for production:**

```bash
npm run build
```

4. **Update blade to remove script tag:**

```blade
@section('scripts')
    <!-- Chart.js still via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- funding-rate-controller.js now bundled in app.js -->
@endsection
```

5. **Clear cache:**

```bash
php artisan optimize:clear
```

---

## 📞 Support

Jika masih ada error, check:

1. Browser console untuk error messages
2. Network tab untuk 404 errors
3. Laravel logs untuk server-side errors

**Version:** 1.0.0  
**Last Updated:** October 2025
