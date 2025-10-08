# 📚 Documentation Index

## 🎯 Main Documentation

### [FUNDING-RATE-ANALYTICS.md](./FUNDING-RATE-ANALYTICS.md)
**Complete system documentation** - Overview, architecture, components, API integration, and troubleshooting guide.

### [funding-rate-components.md](./funding-rate-components.md)
**Component reference** - Detailed documentation of each funding rate component with trading insights.

### [ALPINE-CHARTJS-INTEGRATION.md](./ALPINE-CHARTJS-INTEGRATION.md)
**Technical deep-dive** - Critical guide for integrating Alpine.js with Chart.js without infinite loops.

## 📊 Additional Resources

### [additional-insights.md](./additional-insights.md)
Trading insights and market analysis patterns.

### [open-interest.md](./open-interest.md)
Open Interest metrics documentation.

## 🚨 Critical Issues Solved

### Alpine.js + Chart.js Infinite Loop
- **Problem:** `RangeError: Maximum call stack size exceeded`
- **Solution:** DOM-based chart storage
- **Files:** All chart components
- **Status:** ✅ RESOLVED

### Chart Visibility Issues
- **Problem:** Charts not visible on first load
- **Solution:** Retry logic with width detection
- **Files:** All chart components
- **Status:** ✅ RESOLVED

## 🛠️ Quick Reference

### Chart Component Pattern
```javascript
// ✅ CORRECT - Store chart in DOM
getChart() {
    const canvas = document.getElementById(this.chartId);
    return canvas ? canvas._chartInstance : null;
}

setChart(chartInstance) {
    const canvas = document.getElementById(this.chartId);
    if (canvas) canvas._chartInstance = chartInstance;
}
```

### API Endpoints
- `/api/funding-rate/bias` - Market bias data
- `/api/funding-rate/exchanges` - Exchange data
- `/api/funding-rate/aggregate` - Aggregate data
- `/api/funding-rate/history` - Historical data
- `/api/funding-rate/weighted` - Weighted data

## 📝 Maintenance

**Last Updated:** December 2024  
**Maintainer:** Development Team  
**Status:** Production Ready ✅

---

**Need Help?** Check the troubleshooting section in [FUNDING-RATE-ANALYTICS.md](./FUNDING-RATE-ANALYTICS.md) or refer to [ALPINE-CHARTJS-INTEGRATION.md](./ALPINE-CHARTJS-INTEGRATION.md) for technical issues.
