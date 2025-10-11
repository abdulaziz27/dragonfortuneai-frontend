# BTC Market Analytics Dashboard – API Playbook

## Overview
The backend delivers real-time and historical Bitcoin market intelligence across derivatives, spot microstructure, options, ETF & institutional flows, volatility regimes, macro indicators, and on-chain endpoints.

## Implementation Snapshot
| Feature | Blueprint / Module | Status | Notes |
|---------|-------------------|--------|-------|
| Derivatives Core | `api.derivatives_core.*` | live | Funding, open interest, long/short, liquidations, basis, perp-vs-quarterly |
| Spot Microstructure | `api.spot_microstructure` | live | Raw trades, orderbook depth, VWAP/TWAP, flow analytics |
| On-Chain Metrics | `api.onchain_metrics` | live* | MVRV, supply distribution, exchange flows (**data pipeline pending**) |
| Options Metrics | `api.options_metrics` | live | IV surfaces, skew, OI by strike & expiry, dealer Greeks |
| ETF & Institutional | `api.etf_institutional` | live | Spot ETF flows, creations/redemptions, premium/discount, CME OI & COT |
| Volatility & Regime | `api.volatility` | live | Spot OHLC, ATR/HV/RV analytics, regime classification |
| Macro Overlay | `api.macro_overlay` | live | Macro series, event releases, summary & analytics |

*On-chain endpoints operate on the seeded dataset until the production pipeline is connected.*

## Data Architecture
### Data Domains (current state)
1. **Derivatives Core** - operational, powered by Coinglass-derived tables (`cg_fut_*`) with optional Binance price joins.
2. **Spot Microstructure** - operational, Binance spot trade and orderbook data with enrichment analytics.
3. **On-Chain Metrics** - blueprint/queries live; data served from the seeded dataset while production ingestion is pending.
4. **Options Metrics** - operational, option surfaces pre-seeded while awaiting direct vendor integration.
5. **ETF & Institutional** - operational, populated from Arkam datasets (`etf_spot_*`, `cme_*` tables).
6. **Volatility & Regime** – operational, relies on spot OHLC and derived analytics.
7. **Macro Overlay** – operational, macro series/events stored in `macro_overlay` and `macro_events`.

### Storage Model
- Primary datastore: MySQL (SQLAlchemy engine configured in `db/connection.py` with env-driven DSN).
- Each feature reads from cohesive, domain-specific tables (e.g., `cg_fut_fr_history`, `binance_trades`, `options_iv_surface`, `macro_overlay`).
- The previously proposed universal `metrics_raw` table is not in production yet; instead, endpoints query purpose-built tables. Keep this in mind when extending analytics or building exports.
- Query helpers live in `db/queries.py`. Every helper guards against missing tables with `_table_exists` checks to keep empty schemas safe.

## Feature Guides

### 1. Derivatives Core (`api/derivatives_core/*`)
**Funding Rate — Live**
- Fields: `ts`, `exchange`, `pair`, `funding_rate`, `interval`, `symbol_price`
- Cadence: Every funding interval (8h) with optional 1h snapshots
- Providers: Coinglass, Binance
- Tables: `cg_fut_fr_history`, `cg_fut_fr_exchange_list`
- **Endpoints**: `/api/funding-rate/history`, `/api/funding-rate/exchanges`, `/api/funding-rate/bias`, `/api/funding-rate/analytics`, `/api/funding-rate/overview`

**Open Interest — Live**
- Fields: `ts`, `exchange`, `pair`, `oi_usd`, `oi_contract`, `oi_coin`, `symbol_price`
- Cadence: 1–5m (minimum 15m)
- Providers: Coinglass, Binance
- Tables: `cg_fut_oi_history`, `cg_fut_oi_exchange_split`, `cg_fut_oi_exchange_list`
- **Endpoints**: `/api/open-interest/history`, `/api/open-interest/exchange`, `/api/open-interest/exchange-list`, `/api/open-interest/analytics`, `/api/open-interest/overview`

**Long/Short Ratio — Live**
- Fields: Accounts (`long_accounts`, `short_accounts`, `ls_ratio_accounts`); Positions (`long_notional_usd`, `short_notional_usd`, `ls_ratio_positions`)
- Cadence: 15–60m
- Providers: Coinglass, Hyblock
- Tables: `cg_fut_lsr_top_accounts`, `cg_fut_lsr_top_positions`
- **Endpoints**: `/api/long-short-ratio/top-accounts`, `/api/long-short-ratio/top-positions`, `/api/long-short-ratio/analytics`, `/api/long-short-ratio/overview`

**Liquidations (Stream & Bucket) — Live**
- Fields: Stream (`ts`, `exchange`, `pair`, `side`, `qty_usd`, `price`); Bucket (`ts`, `exchange`, `pair`, `bucket_price`, `liq_usd`)
- Cadence: Stream real-time/1m, bucketed 15m
- Providers: Coinglass, Hyblock, Binance
- Tables: `cg_fut_liq_orders`, `cg_fut_liq_pair_history`, `cg_fut_liq_exchange_list`, `cg_fut_liq_coin_list`
- **Endpoints**: `/api/liquidations/orders`, `/api/liquidations/pair-history`, `/api/liquidations/exchange-list`, `/api/liquidations/coin-list`, `/api/liquidations/analytics`, `/api/liquidations/overview`

**Basis & Term Structure — Live**
- Fields: `ts`, `exchange`, `spot_pair`, `futures_symbol`, `expiry`, `price_spot`, `price_futures`, `basis_abs`, `basis_annualized`
- Cadence: 5–15m
- Providers: Coinglass, Binance
- Tables: `cg_fut_basis_history`, `cg_fut_basis_term_structure`
- **Endpoints**: `/api/basis/history`, `/api/basis/term-structure`, `/api/basis/analytics`

**Perp–Quarterly Spread — Live**
- Fields: `ts`, `exchange`, `perp_symbol`, `quarterly_symbol`, `spread_abs`, `spread_bps`
- Cadence: 5–15m
- Providers: Coinglass, Binance
- Tables: `cg_fut_perp_basis_history`
- **Endpoints**: `/api/perp-quarterly/history`, `/api/perp-quarterly/analytics`

### 2. Spot Microstructure (`api/spot_microstructure.py`)
**Trades for CVD & Buy/Sell — Live**
- Fields: `ts`, `exchange`, `pair`, `side`, `qty`, `price`
- Cadence: Real-time (1m buckets available)
- Providers: Binance, TensorChart
- Tables: `binance_trades`, `binance_trades_agg`
- **Endpoints**: `/api/spot-microstructure/trades`, `/api/spot-microstructure/trades/summary`, `/api/spot-microstructure/cvd`, `/api/spot-microstructure/buyer-seller-ratio`, `/api/spot-microstructure/trade-bias`, `/api/spot-microstructure/trade-flow`, `/api/spot-microstructure/volume-delta`

**Orderbook Snapshots — Live**
- Fields: `ts`, `exchange`, `pair`, `bids[{price,size}]`, `asks[{price,size}]`
- Cadence: 1–5m
- Providers: Binance, TensorChart
- Tables: `binance_orderbook_levels`
- **Endpoints**: `/api/spot-microstructure/orderbook`, `/api/spot-microstructure/orderbook/snapshot`, `/api/spot-microstructure/orderbook/liquidity`, `/api/spot-microstructure/orderbook-depth`, `/api/spot-microstructure/market-depth`, `/api/spot-microstructure/book-pressure`

**VWAP/TWAP — Live (derived)**
- Source: Computed from trades on request, not stored persistently
- **Endpoints**: `/api/spot-microstructure/vwap`, `/api/spot-microstructure/vwap/latest`, `/api/spot-microstructure/twap`

**Volume & Trade Stats — Live**
- Fields: `ts`, `exchange`, `pair`, `trades_count`, `volume_base`, `volume_quote`, `avg_trade_size`
- Cadence: 1–5m
- Tables: `binance_volume_stats`, `binance_volume_profile`
- **Endpoints**: `/api/spot-microstructure/trade-stats`, `/api/spot-microstructure/volume-profile`, `/api/spot-microstructure/volume-profile-detailed`, `/api/spot-microstructure/volume-stats`, `/api/spot-microstructure/analytics`, `/api/spot-microstructure/large-trades`, `/api/spot-microstructure/liquidity-heatmap`

### 3. On-Chain Metrics (`api/onchain_metrics.py`)
Status: Blueprint live; development data already seeded pending production ingestion.

**MVRV & Z-Score — Live (synthetic feed)**
- Fields: `date`, `metric`, `value`
- Cadence: Daily (EOD)
- Tables: `onchain_mvrv`
- **Endpoints**: `/api/onchain/valuation/mvrv`

**LTH vs STH Supply — Live (synthetic feed)**
- Fields: `date`, `lth_supply_btc`, `sth_supply_btc`, optional exchange/illiquid/miner/whale balances
- Cadence: Daily (EOD)
- Tables: `onchain_supply_distribution`
- **Endpoints**: `/api/onchain/supply/distribution`

**Exchange Netflow (BTC & Stablecoin) — Live (synthetic feed)**
- Fields: `date`, `asset`, `exchange`, `inflow`, `outflow`, `netflow`
- Cadence: Daily (EOD)
- Tables: `onchain_exchange_flows`
- **Endpoints**: `/api/onchain/exchange/flows`, `/api/onchain/exchange/summary`

**Realized Cap & HODL Waves — Live (synthetic feed)**
- Fields: `date`, `metric`, `value`; `date`, `cohort_age_band`, `percent_supply`
- Cadence: Daily (EOD)
- Tables: `onchain_realized_cap`, `onchain_hodl_waves`
- **Endpoints**: `/api/onchain/valuation/realized-cap`, `/api/onchain/supply/hodl-waves`

**Reserve Risk / SOPR / Dormancy / CDD — Live (synthetic feed)**
- Fields: `date`, `metric`, `value`
- Cadence: Daily (EOD)
- Table: `onchain_chain_health`
- **Endpoints**: `/api/onchain/behavioral/chain-health`

**Miner Metrics — Live (synthetic feed)**
- Fields: `date`, `miner_reserve_btc`, `puell_multiple`, `hash_rate`, `revenue_btc`
- Cadence: Daily (EOD)
- Table: `onchain_miner_metrics`
- **Endpoints**: `/api/onchain/miners/metrics`

**Whale Holdings — Live (synthetic feed)**
- Fields: `date`, `cohort`, `balance_btc`, `balance_change_btc`
- Cadence: Daily (EOD)
- Table: `onchain_whale_holdings`
- **Endpoints**: `/api/onchain/whales/holdings`, `/api/onchain/whales/summary`

### 4. Options Metrics (`api/options_metrics.py`)
**IV Smile & Surface — Live**
- Fields: `ts`, `exchange`, `underlying`, `tenor`, `strike`, `iv`
- Cadence: 5–15m
- Tables: `options_iv_smile`, `options_iv_surface`, `options_iv_term`
- **Endpoints**: `/api/options-metrics/iv/smile`, `/api/options-metrics/iv/surface`, `/api/options-metrics/iv/term-structure`, `/api/options-metrics/iv/timeseries`, `/api/options-metrics/iv/summary`

**25D Skew — Live**
- Fields: `ts`, `exchange`, `tenor`, `rr25`, `bf25`
- Cadence: 5–15m
- Tables: `options_skew_rr25`, `options_skew_heatmap`
- **Endpoints**: `/api/options-metrics/skew/history`, `/api/options-metrics/skew/summary`, `/api/options-metrics/skew/heatmap`, `/api/options-metrics/skew/regime`

**OI & Volume by Strike/Expiry — Live**
- Fields: `ts`, `exchange`, `expiry`, `strike`, `call_oi`, `put_oi`, `call_vol`, `put_vol`
- Cadence: 15–60m
- Tables: `options_oi_strike`, `options_oi_expiry`, `options_oi_timeseries`
- **Endpoints**: `/api/options-metrics/oi/strike`, `/api/options-metrics/oi/expiry`, `/api/options-metrics/oi/timeseries`, `/api/options-metrics/oi/summary`

**GEX / Dealer Greeks — Live**
- Fields: `ts`, `price_level`, `gamma_exposure`
- Cadence: 15–60m
- Tables: `options_dealer_greeks_gex`, `options_dealer_greeks_summary`, `options_dealer_greeks_timeline`
- **Endpoints**: `/api/options-metrics/dealer-greeks/gex`, `/api/options-metrics/dealer-greeks/summary`, `/api/options-metrics/dealer-greeks/timeline`

### 5. ETF & Institutional (`api/etf_institutional.py`)
**Spot ETF Flow — Live**
- Fields: `date`, `issuer`, `ticker`, `flow_usd`, `aum_usd`, `shares_outstanding`
- Cadence: Daily
- Table: `etf_spot_flows`
- **Endpoints**: `/api/etf-institutional/spot/daily-flows`

**Creations & Redemptions — Live**
- Fields: `date`, `issuer`, `creations_shares`, `redemptions_shares`
- Table: `etf_spot_creations_redemptions`
- **Endpoints**: `/api/etf-institutional/spot/creations-redemptions`

**Premium/Discount vs NAV — Live**
- Fields: `date`, `ticker`, `nav`, `market_price`, `premium_discount_bps`
- Table: `etf_spot_premium_discount`
- **Endpoints**: `/api/etf-institutional/spot/premium-discount`

**CME Futures OI & COT — Live**
- Fields: Daily `oi_usd`, `oi_contracts`; Weekly `report_group`, `long_contracts`, `short_contracts`
- Tables: `cme_futures_oi`, `cme_cot_reports`
- **Endpoints**: `/api/etf-institutional/cme/oi`, `/api/etf-institutional/cme/cot`, `/api/etf-institutional/cme/summary`

### 6. Volatility & Regime (`api/volatility.py`)
**Spot Prices — Live**
- Fields: `ts`, `exchange`, `pair`, `open`, `high`, `low`, `close`, `volume`
- Cadence: 1m/5m + EOD
- Tables: `spot_price_ohlc`, `spot_price_eod`, `spot_price_pairs`
- **Endpoints**: `/api/volatility/spot/ohlc`, `/api/volatility/spot/pairs`, `/api/volatility/spot/eod`

**Regime Analytics — Live**
- Metrics: ATR, historical volatility, realised volatility, regime classification, trend tracking
- Tables: analytics materialized views
- **Endpoints**: `/api/volatility/analytics/atr`, `/api/volatility/analytics/hv`, `/api/volatility/analytics/rv`, `/api/volatility/analytics/ranking`, `/api/volatility/analytics/trends`, `/api/volatility/analytics/regime`

### 7. Macro Overlay (`api/macro_overlay.py`)
**Macro Series — Live**
- Metrics: DXY, treasury yields, Fed funds, M2, RRP, TGA
- Fields: `date/ts`, `metric`, `value`
- Cadence: Daily or per publication
- Tables: `macro_overlay`
- **Endpoints**: `/api/macro-overlay/raw`, `/api/macro-overlay/summary`, `/api/macro-overlay/analytics`, `/api/macro-overlay/enhanced-analytics`, `/api/macro-overlay/available-metrics`

**Event Releases — Live**
- Events: CPI, CPI Core, NFP, other scheduled prints
- Fields: `event_type`, `release_date`, `actual_value`, `forecast_value`, `previous_value`
- Table: `macro_events`
- **Endpoints**: `/api/macro-overlay/events`, `/api/macro-overlay/events-summary`


## Provider Coverage (current ingestion)
| Provider | Derivatives | Spot | On-Chain | Options | ETF / Institutional | Macro |
|----------|-------------|------|----------|---------|----------------------|-------|
| Coinglass | Funding, OI, L/S, liquidations, basis | — | — | — | — | — |
| Binance | Price reference joins in derivatives | Trades, orderbook, VWAP/TWAP, volume | — | — | — | — |
| Hyblock | Supplemental liquidation & positioning analytics (via ingested tables) | — | — | — | — | — |
| TensorChart | — | Advanced spot analytics integration | — | IV surfaces, skew, Greeks | — | — |
| Arkam | - | - | MVRV, supply distribution, exchange flows *(synthetic placeholder; real feed pending)* | - | Spot ETF flows, CME futures | Macro indicators |

> _Note_: Provider labels reflect upstream data feeds stored in the database; individual endpoints may join multiple sources.

## Key Principles
- **Raw data first** – endpoints expose un-sanitised, high-frequency records; analytics layers sit on top.
- **Blueprint isolation** – each domain keeps its own blueprint and query helpers for maintainability.
- **Graceful degradation** – queries guard for missing tables and return empty arrays rather than raising errors.
- **Config-driven** – shared constants live in `config/constants.py` (limits, Swagger tags, parameter descriptions).

## API Endpoint Reference

### Derivatives Core
- `/api/funding-rate/history`
- `/api/funding-rate/exchanges`
- `/api/funding-rate/bias`
- `/api/funding-rate/analytics`
- `/api/funding-rate/overview`
- `/api/open-interest/history`
- `/api/open-interest/exchange`
- `/api/open-interest/exchange-list`
- `/api/open-interest/analytics`
- `/api/open-interest/overview`
- `/api/long-short-ratio/top-accounts`
- `/api/long-short-ratio/top-positions`
- `/api/long-short-ratio/analytics`
- `/api/long-short-ratio/overview`
- `/api/liquidations/orders`
- `/api/liquidations/pair-history`
- `/api/liquidations/exchange-list`
- `/api/liquidations/coin-list`
- `/api/liquidations/analytics`
- `/api/liquidations/overview`
- `/api/basis/history`
- `/api/basis/term-structure`
- `/api/basis/analytics`
- `/api/perp-quarterly/history`
- `/api/perp-quarterly/analytics`

### On-Chain Metrics
- `/api/onchain/valuation/mvrv`
- `/api/onchain/supply/distribution`
- `/api/onchain/exchange/flows`
- `/api/onchain/exchange/summary`
- `/api/onchain/valuation/realized-cap`
- `/api/onchain/supply/hodl-waves`
- `/api/onchain/behavioral/chain-health`
- `/api/onchain/miners/metrics`
- `/api/onchain/whales/holdings`
- `/api/onchain/whales/summary`

### Spot Microstructure
- `/api/spot-microstructure/trades`
- `/api/spot-microstructure/trades/summary`
- `/api/spot-microstructure/cvd`
- `/api/spot-microstructure/buyer-seller-ratio`
- `/api/spot-microstructure/trade-bias`
- `/api/spot-microstructure/orderbook`
- `/api/spot-microstructure/orderbook/snapshot`
- `/api/spot-microstructure/orderbook/liquidity`
- `/api/spot-microstructure/vwap`
- `/api/spot-microstructure/vwap/latest`
- `/api/spot-microstructure/twap`
- `/api/spot-microstructure/trade-stats`
- `/api/spot-microstructure/volume-profile`
- `/api/spot-microstructure/volume-profile-detailed`
- `/api/spot-microstructure/large-trades`
- `/api/spot-microstructure/trade-flow`
- `/api/spot-microstructure/volume-delta`
- `/api/spot-microstructure/book-pressure`
- `/api/spot-microstructure/orderbook-depth`
- `/api/spot-microstructure/market-depth`
- `/api/spot-microstructure/volume-stats`
- `/api/spot-microstructure/liquidity-heatmap`
- `/api/spot-microstructure/analytics`

### Options Metrics
- `/api/options-metrics/iv/smile`
- `/api/options-metrics/iv/surface`
- `/api/options-metrics/iv/term-structure`
- `/api/options-metrics/iv/timeseries`
- `/api/options-metrics/iv/summary`
- `/api/options-metrics/skew/history`
- `/api/options-metrics/skew/summary`
- `/api/options-metrics/skew/heatmap`
- `/api/options-metrics/skew/regime`
- `/api/options-metrics/oi/strike`
- `/api/options-metrics/oi/expiry`
- `/api/options-metrics/oi/timeseries`
- `/api/options-metrics/oi/summary`
- `/api/options-metrics/dealer-greeks/gex`
- `/api/options-metrics/dealer-greeks/summary`
- `/api/options-metrics/dealer-greeks/timeline`

### ETF & Institutional
- `/api/etf-institutional/spot/daily-flows`
- `/api/etf-institutional/spot/creations-redemptions`
- `/api/etf-institutional/spot/premium-discount`
- `/api/etf-institutional/spot/summary`
- `/api/etf-institutional/cme/oi`
- `/api/etf-institutional/cme/cot`
- `/api/etf-institutional/cme/summary`

### Volatility & Regime
- `/api/volatility/spot/ohlc`
- `/api/volatility/spot/pairs`
- `/api/volatility/spot/eod`
- `/api/volatility/analytics/atr`
- `/api/volatility/analytics/hv`
- `/api/volatility/analytics/rv`
- `/api/volatility/analytics/ranking`
- `/api/volatility/analytics/trends`
- `/api/volatility/analytics/regime`

### Macro Overlay
- `/api/macro-overlay/raw`
- `/api/macro-overlay/events`
- `/api/macro-overlay/summary`
- `/api/macro-overlay/analytics`
- `/api/macro-overlay/enhanced-analytics`
- `/api/macro-overlay/events-summary`
- `/api/macro-overlay/available-metrics`

## Feature Endpoint Matrix

| Feature / Subfeature | Primary Endpoints | Notes |
|----------------------|-------------------|-------|
| Derivatives – Funding Rate | `/api/funding-rate/history`, `/api/funding-rate/exchanges`, `/api/funding-rate/bias`, `/api/funding-rate/analytics`, `/api/funding-rate/overview` | OHLC + snapshot + bias analytics |
| Derivatives – Open Interest | `/api/open-interest/history`, `/api/open-interest/exchange`, `/api/open-interest/exchange-list`, `/api/open-interest/analytics`, `/api/open-interest/overview` | Supports unit filters & trend insights |
| Derivatives – Long/Short Ratio | `/api/long-short-ratio/top-accounts`, `/api/long-short-ratio/top-positions`, `/api/long-short-ratio/analytics`, `/api/long-short-ratio/overview` | Accounts vs positions views |
| Derivatives – Liquidations | `/api/liquidations/orders`, `/api/liquidations/pair-history`, `/api/liquidations/exchange-list`, `/api/liquidations/coin-list`, `/api/liquidations/analytics`, `/api/liquidations/overview` | Stream, buckets, exchange/coin snapshots |
| Derivatives – Basis & Term Structure | `/api/basis/history`, `/api/basis/term-structure`, `/api/basis/analytics` | Perp-dated basis ladder |
| Derivatives – Perp vs Quarterly | `/api/perp-quarterly/history`, `/api/perp-quarterly/analytics` | Spread monitor |
| Spot Microstructure – Trades & Flow | `/api/spot-microstructure/trades`, `/api/spot-microstructure/trades/summary`, `/api/spot-microstructure/cvd`, `/api/spot-microstructure/buyer-seller-ratio`, `/api/spot-microstructure/trade-bias`, `/api/spot-microstructure/trade-flow`, `/api/spot-microstructure/volume-delta` | Reused across CVD, flow dashboards |
| Spot Microstructure – Orderbook & Depth | `/api/spot-microstructure/orderbook`, `/api/spot-microstructure/orderbook/snapshot`, `/api/spot-microstructure/orderbook/liquidity`, `/api/spot-microstructure/orderbook-depth`, `/api/spot-microstructure/market-depth`, `/api/spot-microstructure/book-pressure` | Depth heatmaps & liquidity |
| Spot Microstructure – VWAP/TWAP | `/api/spot-microstructure/vwap`, `/api/spot-microstructure/vwap/latest`, `/api/spot-microstructure/twap` | Computed on demand |
| Spot Microstructure – Volume & Stats | `/api/spot-microstructure/trade-stats`, `/api/spot-microstructure/volume-profile`, `/api/spot-microstructure/volume-profile-detailed`, `/api/spot-microstructure/volume-stats`, `/api/spot-microstructure/analytics`, `/api/spot-microstructure/large-trades`, `/api/spot-microstructure/liquidity-heatmap` | Aggregated stats & whale flow |
| On-Chain – Valuation | `/api/onchain/valuation/mvrv`, `/api/onchain/valuation/realized-cap` | Synthetic until Arkam feed |
| On-Chain – Supply Distribution | `/api/onchain/supply/distribution`, `/api/onchain/supply/hodl-waves` | Includes cohort & age-band balances |
| On-Chain – Exchange Flows | `/api/onchain/exchange/flows`, `/api/onchain/exchange/summary` | Asset/exchange netflows |
| On-Chain – Behavioural | `/api/onchain/behavioral/chain-health` | Reserve risk, SOPR, dormancy metrics |
| On-Chain – Miner Metrics | `/api/onchain/miners/metrics` | Reserve, puell multiple, hash rate |
| On-Chain – Whale Cohorts | `/api/onchain/whales/holdings`, `/api/onchain/whales/summary` | Cohort balance tracking |
| Options – IV Surface | `/api/options-metrics/iv/smile`, `/api/options-metrics/iv/surface`, `/api/options-metrics/iv/term-structure`, `/api/options-metrics/iv/timeseries`, `/api/options-metrics/iv/summary` | Tenor/strike slices |
| Options – Skew | `/api/options-metrics/skew/history`, `/api/options-metrics/skew/summary`, `/api/options-metrics/skew/heatmap`, `/api/options-metrics/skew/regime` | RR25/BF25 analytics |
| Options – OI & Volume | `/api/options-metrics/oi/strike`, `/api/options-metrics/oi/expiry`, `/api/options-metrics/oi/timeseries`, `/api/options-metrics/oi/summary` | Strike & expiry distributions |
| Options – Dealer Greeks | `/api/options-metrics/dealer-greeks/gex`, `/api/options-metrics/dealer-greeks/summary`, `/api/options-metrics/dealer-greeks/timeline` | Gamma exposure dashboards |
| ETF & Institutional – Spot ETFs | `/api/etf-institutional/spot/daily-flows`, `/api/etf-institutional/spot/creations-redemptions`, `/api/etf-institutional/spot/premium-discount`, `/api/etf-institutional/spot/summary` | Daily flow + NAV metrics |
| ETF & Institutional – CME | `/api/etf-institutional/cme/oi`, `/api/etf-institutional/cme/cot`, `/api/etf-institutional/cme/summary` | Futures OI/COT |
| Volatility & Regime – Spot Prices | `/api/volatility/spot/ohlc`, `/api/volatility/spot/pairs`, `/api/volatility/spot/eod` | OHLC feeds |
| Volatility & Regime – Analytics | `/api/volatility/analytics/atr`, `/api/volatility/analytics/hv`, `/api/volatility/analytics/rv`, `/api/volatility/analytics/ranking`, `/api/volatility/analytics/trends`, `/api/volatility/analytics/regime` | Derived signals |
| Macro Overlay – Macro Series | `/api/macro-overlay/raw`, `/api/macro-overlay/summary`, `/api/macro-overlay/analytics`, `/api/macro-overlay/enhanced-analytics`, `/api/macro-overlay/available-metrics` | DXY, yields, liquidity |
| Macro Overlay – Events | `/api/macro-overlay/events`, `/api/macro-overlay/events-summary` | CPI/NFP calendar |

## Next Steps & TODOs
1. Replace the synthetic on-chain bootstrap with the real Arkam ingestion pipeline and deliver the remaining on-chain sub-features (realized cap, HODL waves, reserve risk, miner & whale cohorts).
2. Decide whether a universal `metrics_raw` table is still required; if so, model migrations and ETL.
3. Add automated integration smoke tests for high-traffic endpoints once database fixtures are available.

*Last reviewed: 11 Oct 2025*
