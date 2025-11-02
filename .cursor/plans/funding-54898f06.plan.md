<!-- 54898f06-fe09-41cc-9af4-8115d53df872 8ecf1329-8b81-466f-b4bb-22bb4fa1fb71 -->
# Modularize Long Short Ratio Controller

## Objective

Refactor `public/js/long-short-ratio-hybrid-controller.js` (2372 lines) into modular ES6 structure matching the pattern used in funding-rate, perp-quarterly, and basis dashboards.

## File Structure to Create

### 1. Entry Point: `public/js/long-short-ratio-controller.js`

- Thin wrapper that imports and exposes controller to Alpine.js
- Similar to `funding-rate-exact-controller.js`
- Pattern: Import `createLongShortRatioController` from `./long-short-ratio/controller.js`
- Expose as `window.longShortRatioController`

### 2. Main Controller: `public/js/long-short-ratio/controller.js`

- Core Alpine.js controller logic
- State management (globalPeriod, selectedExchange, selectedSymbol, etc.)
- Orchestrates API calls, chart rendering, and UI updates
- Imports: `LongShortRatioAPIService`, `ChartManager`, `LongShortRatioUtils`
- Methods: `init()`, `loadAllData()`, `updateExchange()`, `updateSymbol()`, `refreshAll()`, etc.
- State variables: All current state from hybrid controller (data arrays, current values, sentiment, etc.)

### 3. API Service: `public/js/long-short-ratio/api-service.js`

- All API fetching logic (both internal and external)
- Export class `LongShortRatioAPIService`
- Methods:
- `fetchOverview()` - `/api/long-short-ratio/overview` (internal)
- `fetchAnalytics()` - `/api/long-short-ratio/analytics` (internal)
- `fetchTopAccounts()` - `/api/long-short-ratio/top-accounts` (internal)
- `fetchTopPositions()` - `/api/long-short-ratio/top-positions` (internal)
- `fetchGlobalAccountRatio()` - `/api/coinglass/global-account-ratio` (external - keep for now)
- `fetchNetPositionData()` - `/api/coinglass/net-position` (external - keep for now)
- `fetchTakerBuySellRatio()` - `/api/coinglass/taker-buy-sell` (external - keep for now)
- `fetchPriceData()` - `/api/cryptoquant/btc-market-price` (external - keep for now)
- AbortController for each request type (separate instances)
- Data transformation/formatting methods
- Error handling

### 4. Chart Manager: `public/js/long-short-ratio/chart-manager.js`

- All Chart.js rendering logic
- Export class `ChartManager`
- Methods:
- `renderMainChart(data, chartType)` - Main Long/Short Ratio chart
- `renderComparisonChart(globalData, topAccountData, topPositionData)` - Comparison chart
- `renderNetPositionChart(netPositionData)` - Net Position Flow chart
- `updateChart(chartId, data, chartType)` - Generic update method
- `destroy()` - Cleanup method
- `getChartOptions()` - Chart configuration
- Separate chart instances for: mainChart, comparisonChart, netPositionChart
- Light theme styling (consistent with other dashboards)
- Proper cleanup and memory management

### 5. Utils: `public/js/long-short-ratio/utils.js`

- Helper functions
- Export object `LongShortRatioUtils`
- Functions:
- `formatRatio(value)` - Format ratio with 2 decimals
- `formatChange(value)` - Format percentage change
- `formatPriceUSD(value)` - Format price in USD
- `formatVolume(value)` - Format volume numbers
- `formatNetBias(value)` - Format net bias
- `getRatioTrendClass(value)` - Get CSS class for ratio trend
- `getSentimentBadgeClass(sentiment)` - Get badge class for sentiment
- `getSentimentColorClass(sentiment)` - Get color class for sentiment
- `getDateRange(period, timeRanges)` - Calculate date range
- `getTimeRange(period, timeRanges)` - Calculate time range (milliseconds)
- `getYTDDays()` - Calculate year-to-date days
- `calculateLimit(days, interval)` - Calculate API limit based on range
- `getExchangeColor(exchangeName)` - Get color for exchange
- All other utility functions from current controller

## Implementation Steps

1. **Create directory structure**

- Create `public/js/long-short-ratio/` directory

2. **Extract Utils** (`utils.js`)

- Move all formatting and utility functions
- No dependencies on controller state
- Pure functions only

3. **Extract API Service** (`api-service.js`)

- Move all `fetch*` methods
- Implement separate AbortController instances
- Add data transformation methods
- Keep external API calls (Coinglass, CryptoQuant) for now - will migrate later

4. **Extract Chart Manager** (`chart-manager.js`)

- Move all `render*` chart methods
- Implement proper chart lifecycle (create, update, destroy)
- Apply light theme styling
- Fix chart cleanup issues (similar to funding-rate fix)

5. **Refactor Controller** (`controller.js`)

- Keep state management and UI orchestration
- Import services and use them
- Remove duplicated code
- Implement `init()`, `loadAllData()`, filter handlers
- Auto-refresh logic (5 seconds like other dashboards)

6. **Create Entry Point** (`long-short-ratio-controller.js`)

- Thin wrapper for Alpine.js
- Import and expose controller

7. **Update Blade Template**

- Update script tag to use new entry point
- Ensure Alpine.js directive uses correct function name

## Key Considerations

- **Backward Compatibility**: Keep all current functionality working
- **Error Handling**: Maintain existing error handling patterns
- **Caching**: Preserve dataCache and priceCache logic
- **Auto-refresh**: Implement same pattern as other dashboards (5 seconds)
- **Chart Cleanup**: Apply fixes from funding-rate (destroy and recreate strategy)
- **State Preservation**: Ensure state updates correctly on data load
- **Hybrid API**: Support both internal and external APIs during transition

## Files to Modify

1. Create: `public/js/long-short-ratio/utils.js`
2. Create: `public/js/long-short-ratio/api-service.js`
3. Create: `public/js/long-short-ratio/chart-manager.js`
4. Create: `public/js/long-short-ratio/controller.js`
5. Create: `public/js/long-short-ratio-controller.js`
6. Update: `resources/views/derivatives/long-short-ratio.blade.php` (script tag)
7. Keep: `public/js/long-short-ratio-hybrid-controller.js` (backup, can be deleted after verification)

## Testing Checklist

- [ ] All summary cards display correctly
- [ ] Main chart renders with data
- [ ] Comparison chart shows all three ratios
- [ ] Net Position chart displays (if data available)
- [ ] Taker Buy/Sell section works
- [ ] Exchange ranking table displays
- [ ] Filters (exchange, symbol, interval) update correctly
- [ ] Auto-refresh works (5 seconds)
- [ ] No console errors
- [ ] Chart cleanup works (no memory leaks)
- [ ] Error handling works (failed API calls)

### To-dos

- [ ] Uncomment menu Basis & Term Structure di sidebar (app.blade.php line 171)
- [ ] Buat public/js/basis/utils.js dengan format functions dan calculations
- [ ] Buat public/js/basis/api-service.js untuk fetch analytics, history, term-structure dengan separate AbortControllers
- [ ] Buat public/js/basis/chart-manager.js dengan history chart (dual-axis) dan term structure chart (bar)
- [ ] Buat public/js/basis/controller.js dengan state management, mapAnalyticsToState, auto-refresh, dan filter controls
- [ ] Buat public/js/basis-term-structure-controller.js sebagai entry point
- [ ] Replace seluruh konten basis-term-structure.blade.php dengan struktur baru (header, summary cards, charts)
- [ ] Test dan verify semua API calls dan field mapping dari analytics ke summary cards