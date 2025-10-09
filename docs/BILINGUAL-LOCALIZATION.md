# Bilingual Localization Guide

## Overview

Dashboard ini telah di-refactor untuk memberikan clarity maksimal dengan pendekatan bilingual:

-   **Bahasa Inggris** untuk semua trading terminology, metrics, dan labels (technical accuracy)
-   **Bahasa Indonesia** untuk explanatory text, insights, dan narratives (readability)

---

## Principles

### ✅ KEEP IN ENGLISH

**All Trading & Market Terminology:**

-   Metrics: `Fear & Greed Index`, `Funding Rate`, `Whale Flow`, `DXY`, `Yields`, `M2`, `RRP`
-   Signals: `Bullish`, `Bearish`, `Neutral`, `Extreme Fear`, `Greed`, `Long Squeeze`, `Short Squeeze`
-   Table Headers: `Time`, `Direction`, `Amount`, `Exchange`, `Signal`, `Trend`, `Asset`
-   Chart Labels: `Inflow`, `Outflow`, `Net Flow`, `Sentiment`
-   Timeframes: `1h`, `1d`, `1w`, `1M`, `3M`, `6M`, `1Y`, `Last 24h`, `8h intervals`
-   Dataset Labels: `Twitter`, `Reddit`, `Google Trends`, `mentions`, `posts`
-   Status Terms: `Trending Up`, `Trending Down`, `Stable`, `Beat`, `Miss`, `Inverted`, `Normal`
-   Actions: `Monitor`, `Refresh All`, `Refresh Data`
-   Technical Terms: `USD Strengthening/Weakening`, `High yields`, `Risk-off`, `Risk-on`
-   Correlations: `Correlation with BTC: -0.72`

### ✅ USE BAHASA INDONESIA

**Explanatory & Interpretive Content:**

-   Section subtitles (penjelasan di bawah judul Inggris)
-   Insight panels & tips
-   Warning messages & analysis
-   Helper text yang menjelaskan data
-   Mixed phrasing natural (English terms + Indonesian explanation)

---

## Implementation Examples

### ✅ CORRECT Implementation

**Page Titles & Subtitles:**

```html
<h1>Sentiment & Flow Analysis</h1>
<p>
    Pantau sentimen pasar, tren sosial media, dominasi funding rate & pergerakan
    whale
</p>
```

**Section Headers:**

```html
<h5>Fear & Greed Index</h5>
<small class="text-secondary"
    >Indeks sentimen pasar untuk mengukur kondisi psikologis trader</small
>
```

**Another Example:**

```html
<h5>Funding Rate Dominance</h5>
<small class="text-secondary"
    >Tracking posisi leverage dominan antar exchange</small
>
```

**Table Headers (ALL English):**

```html
<thead>
    <tr>
        <th>Time</th>
        <th>Direction</th>
        <th>Amount</th>
        <th>Asset</th>
        <th>USD Value</th>
        <th>Exchange</th>
        <th>Signal</th>
    </tr>
</thead>
```

**Data Labels (English):**

```html
<div class="small text-secondary">Inflow to Exchanges</div>
<div class="small text-secondary">Last 24h</div>
```

**Insight Panels (Mixed - English terms + Indonesian explanation):**

```html
<div class="small text-secondary">
    <strong>Whale Behavior:</strong> Transfer IN exchange → Potensi sell
    pressure (Bearish). Transfer OUT → Holding/accumulation (Bullish). Monitor
    untuk confirm trend.
</div>
```

**Trading Insights (English signals + Indonesian atau mixed explanation):**

```html
<div class="fw-bold mb-2">Contrarian Buy Signals</div>
<ul>
    <li>Fear & Greed < 20 (Extreme Fear)</li>
    <li>Social mentions bottom out</li>
    <li>Negative funding across exchanges</li>
    <li>Whale net outflow positive</li>
</ul>
```

**Badges & Signals (English):**

```html
<span class="badge">Bullish</span>
<span class="badge">Bearish</span>
<span class="badge">Monitor</span>
<span class="badge">Long Squeeze Risk</span>
```

**Dynamic Text (English units):**

```javascript
x-text="socialBreakdown.twitter.mentions + ' mentions'"
x-text="'$' + whaleFlow.inflow + 'M'"
x-text="'Last 24h'"
```

---

## JavaScript Functions

### Signal Functions (English Returns)

```javascript
getFearGreedLabel() {
    if (this.fearGreedScore <= 25) return 'Extreme Fear';  // ✓ English
    if (this.fearGreedScore <= 45) return 'Fear';
    if (this.fearGreedScore <= 55) return 'Neutral';
    if (this.fearGreedScore <= 75) return 'Greed';
    return 'Extreme Greed';
}

getFearGreedTitle() {
    if (this.fearGreedScore <= 25) return 'Contrarian Buy Opportunity';  // ✓ English
    if (this.fearGreedScore >= 75) return 'Take Profit Zone';
    return 'Neutral Zone';
}

getFundingAction(rate) {
    if (rate > 0.015) return 'Long Squeeze Risk';  // ✓ English
    if (rate > 0.01) return 'Monitor';
    if (rate < 0) return 'Short Squeeze Setup';
    return 'Neutral';
}
```

### Message Functions (Mixed - English terms + Indonesian explanation)

```javascript
getFearGreedMessage() {
    if (this.fearGreedScore <= 25) {
        return `Extreme fear terdeteksi (${this.fearGreedScore}/100). Secara historis titik entry yang baik untuk contrarian traders. Market oversold.`;
    }
    if (this.fearGreedScore >= 75) {
        return `Extreme greed terdeteksi (${this.fearGreedScore}/100). Pertimbangkan take profit. Market berpotensi overheated.`;
    }
    return `Sentimen neutral (${this.fearGreedScore}/100). Market menunjukkan perilaku seimbang. Ikuti tren dan gunakan manajemen risiko yang tepat.`;
}
```

**Notice:** Trading terms (`Extreme fear`, `contrarian traders`, `Market oversold`, `take profit`, `overheated`) tetap English, tapi context/explanation dalam Indonesian.

---

## Files Modified

### Sentiment & Flow Dashboard

**File:** `resources/views/sentiment-flow/dashboard.blade.php`

**Changes:**

1. ✅ Page description: Indonesian (explanatory)
2. ✅ Button text: `Refresh All` (English)
3. ✅ Select options: `All Crypto`, `1h`, `1d` (English)
4. ✅ Section titles: English
5. ✅ Section subtitles: Indonesian (explanatory)
6. ✅ Table headers: ALL English
7. ✅ Data labels: English (`mentions`, `posts`, `Sentiment`, `24h Change`)
8. ✅ Chart labels: English (`Inflow`, `Outflow`, `Last 24h`)
9. ✅ Badges: English (`Bullish`, `Bearish`, `Trending Up`, `Monitor`)
10. ✅ Insight text: Mixed (English terms + Indonesian explanation)
11. ✅ JavaScript functions: English returns for signals, mixed for explanations

### Macro Overlay Dashboard

**File:** `resources/views/macro-overlay/dashboard.blade.php`

**Changes:**

1. ✅ Page description: Indonesian (explanatory)
2. ✅ Timeframe options: English (`1 Month`, `3 Months`, `Year to Date`)
3. ✅ Button: `Refresh Data` (English)
4. ✅ Metric labels: English (`USD Strengthening`, `High yields`, `Liquidity expanding`)
5. ✅ Correlation labels: English (`Correlation with BTC`)
6. ✅ Status labels: English (`Expected`, `Unemployment`, `Beat`, `Miss`)
7. ✅ Signal terms: English (`Recession signal detected`, `Healthy yield curve`)

---

## UI Text Patterns

### Pattern 1: English Title + Indonesian Subtitle

```html
<h5>Fear & Greed Index</h5>
<small class="text-secondary"
    >Indeks sentimen pasar untuk mengukur kondisi psikologis trader</small
>
```

### Pattern 2: English Title + Indonesian Explanatory Note

```html
<h5>Social Platform Breakdown - Last 24h</h5>
<small class="text-secondary"
    >Rincian aktivitas per platform dalam 24 jam terakhir</small
>
```

### Pattern 3: English Labels in Data Display

```html
<div class="small text-secondary">Inflow to Exchanges</div>
<div class="h5 fw-bold" x-text="'$' + whaleFlow.inflow + 'M'"></div>
<div class="small text-secondary">Last 24h</div>
```

### Pattern 4: Mixed Language in Insights (English technical + Indonesian narrative)

```html
<strong>Whale Behavior:</strong> Transfer IN exchange → Potensi sell pressure
(Bearish). Transfer OUT → Holding/accumulation (Bullish). Monitor untuk confirm
trend.
```

### Pattern 5: English Signal Lists

```html
<div class="fw-bold">Contrarian Buy Signals</div>
<ul>
    <li>Fear & Greed < 20 (Extreme Fear)</li>
    <li>Social mentions bottom out</li>
    <li>Negative funding across exchanges</li>
</ul>
```

---

## Benefits

### For Technical Accuracy:

✅ Semua trading terminology tetap standard & universal  
✅ Tidak ada ambiguitas dalam signal interpretation  
✅ Mudah cross-reference dengan sumber data global  
✅ Professional appearance untuk trading dashboard

### For User Understanding:

✅ Explanatory text dalam bahasa yang familiar  
✅ Context & insights mudah dipahami  
✅ Natural mixing of technical English + Indonesian explanation  
✅ Clear guidance untuk decision making

### For Maintainability:

✅ Consistent pattern across all dashboards  
✅ Easy to update trading terms (tetap English)  
✅ Clear separation: technical labels vs explanatory text  
✅ Future-proof untuk integrasi API/data sources

---

## Guidelines for Future Development

### When Adding New Features:

**1. New Metrics/Indicators:**

-   Keep name in English: `VWAP`, `CVD`, `Order Flow`, `Delta`
-   Add Indonesian subtitle if needed for clarity

**2. New Tables:**

-   Column headers: Always English
-   Data labels: English
-   Tooltips/explanations: Can be Indonesian

**3. New Charts:**

-   Title: English
-   Legend items: English
-   Axis labels: English
-   Tooltip descriptions: Can use mixed language

**4. New Alert/Insight Panels:**

-   Signal terms: English (`Bullish`, `Overbought`, `Divergence`)
-   Explanation/context: Indonesian atau mixed
-   Action items: English (`Buy`, `Sell`, `Monitor`, `Wait`)

**5. JavaScript Functions:**

-   Function names: English
-   Return values for signals/labels: English
-   Return values for explanatory messages: Mixed OK

---

## Testing Checklist

### Visual Consistency:

-   [ ] All metric names in English
-   [ ] All table headers in English
-   [ ] All badge/signal texts in English
-   [ ] Timeframes & intervals in English
-   [ ] Subtitles provide Indonesian context where helpful

### Technical Accuracy:

-   [ ] Trading terms match industry standard
-   [ ] No translation errors in technical terms
-   [ ] Signals are clear and unambiguous
-   [ ] Data labels consistent across dashboards

### User Experience:

-   [ ] Explanations clear and helpful
-   [ ] Mixed language feels natural, not forced
-   [ ] Indonesian text adds value, not redundancy
-   [ ] Overall flow is professional

---

## Quick Reference

### English-Only Terms (Never Translate):

**Market Signals:**
`Bullish`, `Bearish`, `Neutral`, `Overbought`, `Oversold`, `Extreme Fear`, `Extreme Greed`, `Long`, `Short`, `Squeeze`

**Time Labels:**
`1h`, `4h`, `1d`, `1w`, `1M`, `3M`, `6M`, `1Y`, `YTD`, `Last 24h`, `8h intervals`, `Real-time`, `Live Feed`

**Action Terms:**
`Buy`, `Sell`, `Monitor`, `Hold`, `Wait`, `Refresh`, `Update`, `Alert`

**Metrics:**
`Fear & Greed Index`, `Funding Rate`, `Open Interest`, `Volume`, `CVD`, `Delta`, `VWAP`, `Price`, `Market Cap`

**Technical Indicators:**
`DXY`, `Yields`, `Fed Funds`, `CPI`, `NFP`, `M2`, `RRP`, `TGA`

**Table Headers:**
`Time`, `Date`, `Price`, `Amount`, `Volume`, `Exchange`, `Direction`, `Signal`, `Status`, `Trend`, `Change`

**Data Units:**
`mentions`, `posts`, `tweets`, `searches`, `followers`, `likes`, `shares`

---

## Summary

Dashboard ini menggunakan **bilingual approach** yang optimal:

-   **Technical precision** with English trading terminology
-   **User clarity** dengan Indonesian explanatory text
-   **Professional standard** yang acceptable globally
-   **Local context** yang familiar untuk Indonesian users

Pendekatan ini memberikan **best of both worlds**: akurasi teknis + kemudahan pemahaman.

---

**Status:** ✅ **COMPLETED**  
**Version:** 3.0 - Bilingual Clarity Release  
**Date:** December 2024
