@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100 gap-3" x-data="dashboardData()">
        <!-- Market Snapshot -->
        <section class="df-panel p-3">
            <div class="row g-3 align-items-end">
                <div class="col-6 col-lg-3">
                    <div class="small" style="color: var(--muted-foreground);">BTCUSDT · Last</div>
                    <div class="h3 fw-bold mb-0" x-text="formatPrice(btc.last)">$65,420.00</div>
                    <div class="small" :class="btc.chgPct >= 0 ? 'text-success' : 'text-danger'"
                         x-text="signed(btc.chg) + ' (' + signed(btc.chgPct) + '%)'">
                        +1,250.00 (+1.95%)
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="small" style="color: var(--muted-foreground);">24h Range</div>
                    <div class="fw-semibold">
                        <span x-text="formatPrice(btc.low)">$64,200.00</span>
                        <span class="text-secondary"> → </span>
                        <span x-text="formatPrice(btc.high)">$66,800.00</span>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="small" style="color: var(--muted-foreground);">24h Volume</div>
                    <div class="fw-semibold" x-text="formatVolume(btc.volume)">28.5B</div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="small" style="color: var(--muted-foreground);">Dominance · Fear & Greed</div>
                    <div class="fw-semibold">
                        <span x-text="btc.dominance.toFixed(1) + '%'">54.2%</span>
                        <span class="text-secondary"> · </span>
                        <span x-text="fgIndex">Neutral (53)</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Derivatives + Options + On‑Chain + ETF Tiles -->
        <section class="row g-3">
            <div class="col-lg-3 col-md-6">
                <a href="/derivatives/funding-rate" class="text-decoration-none" style="color: inherit;">
                    <div class="df-panel p-3 h-100">
                        <div class="small" style="color: var(--muted-foreground);">Funding Rate (perp)</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <div class="h4 mb-0" :class="funding >= 0 ? 'text-success' : 'text-danger'" x-text="signed(funding) + '%'">+0.02%</div>
                            <small class="text-secondary">24h avg</small>
                        </div>
                        <div class="mt-2 small" :class="fundingTrend >= 0 ? 'text-success' : 'text-danger'">
                            <span x-text="trendText(fundingTrend)">Rising</span> last 4h
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6">
                <a href="/derivatives/open-interest" class="text-decoration-none" style="color: inherit;">
                    <div class="df-panel p-3 h-100">
                        <div class="small" style="color: var(--muted-foreground);">Open Interest Δ 24h</div>
                        <div class="h4 mb-0" :class="oiChange >= 0 ? 'text-success' : 'text-danger'" x-text="signed(oiChange) + 'B'">+0.8B</div>
                        <div class="small text-secondary">Across major venues</div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6">
                <a href="/derivatives/liquidations" class="text-decoration-none" style="color: inherit;">
                    <div class="df-panel p-3 h-100">
                        <div class="small" style="color: var(--muted-foreground);">Liquidations (24h)</div>
                        <div class="d-flex align-items-baseline gap-3">
                            <div>
                                <div class="small text-secondary">Long</div>
                                <div class="fw-semibold" x-text="'$' + formatCompact(liq.long)">$120M</div>
                            </div>
                            <div>
                                <div class="small text-secondary">Short</div>
                                <div class="fw-semibold" x-text="'$' + formatCompact(liq.short)">$98M</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6">
                <a href="/etf-basis/perp-basis" class="text-decoration-none" style="color: inherit;">
                    <div class="df-panel p-3 h-100">
                        <div class="small" style="color: var(--muted-foreground);">Perp Basis vs Spot</div>
                        <div class="h4 mb-0" :class="basis >= 0 ? 'text-success' : 'text-danger'" x-text="signed(basis) + '%'">+0.35%</div>
                        <div class="small text-secondary">Indicative carry</div>
                    </div>
                </a>
            </div>
        </section>

        <!-- Watchlist Heat (Top movers) -->
        <section class="df-panel p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Watchlist · Top Movers</h6>
                <a href="#" class="small" style="color: var(--muted-foreground);">Manage</a>
            </div>
            <div class="row g-3">
                <template x-for="asset in watchlist" :key="asset.symbol">
                    <div class="col-6 col-lg-3">
                        <div class="p-3 rounded-3 border" :class="asset.chgPct >= 0 ? 'border-success-subtle' : 'border-danger-subtle'">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="fw-semibold" x-text="asset.symbol">BTC</div>
                                <div class="small" :class="asset.chgPct >= 0 ? 'text-success' : 'text-danger'" x-text="signed(asset.chgPct) + '%'">+2.1%</div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div class="small text-secondary">Last</div>
                                <div class="small" x-text="formatPrice(asset.last)">$65,420</div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </section>

        <!-- Options Snapshot + On‑Chain + ETF Netflow + Risk -->
        <section class="row g-3">
            <div class="col-lg-3 col-md-6">
                <a href="/options-metrics/implied-volatility" class="text-decoration-none" style="color: inherit;">
                    <div class="df-panel p-3 h-100">
                        <div class="small" style="color: var(--muted-foreground);">30d IV (BTC)</div>
                        <div class="h4 mb-0" x-text="iv30.toFixed(1) + '%'">52.3%</div>
                        <div class="small" :class="ivTrend >= 0 ? 'text-success' : 'text-danger'" x-text="trendText(ivTrend) + ' this week'">Rising this week</div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6">
                <a href="/options-metrics/options-skew" class="text-decoration-none" style="color: inherit;">
                    <div class="df-panel p-3 h-100">
                        <div class="small" style="color: var(--muted-foreground);">25d RR (BTC)</div>
                        <div class="h4 mb-0" :class="rr25 >= 0 ? 'text-success' : 'text-danger'" x-text="signed(rr25) + '%'">-3.1%</div>
                        <div class="small text-secondary">Skew toward puts/calls</div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6">
                <a href="/onchain-metrics/exchange-netflow" class="text-decoration-none" style="color: inherit;">
                    <div class="df-panel p-3 h-100">
                        <div class="small" style="color: var(--muted-foreground);">Exchange Netflow (BTC)</div>
                        <div class="h4 mb-0" :class="netflow >= 0 ? 'text-danger' : 'text-success'" x-text="signed(netflow) + ' BTC'">-3.2k BTC</div>
                        <div class="small text-secondary">Outflow suggests accumulation</div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6">
                <a href="/etf-basis/spot-etf-netflow" class="text-decoration-none" style="color: inherit;">
                    <div class="df-panel p-3 h-100">
                        <div class="small" style="color: var(--muted-foreground);">Spot BTC ETF Netflow (today)</div>
                        <div class="h4 mb-0" :class="etfNetflow >= 0 ? 'text-success' : 'text-danger'" x-text="'$' + formatCompact(etfNetflow)">$185M</div>
                        <div class="small text-secondary">US ETFs aggregated</div>
                    </div>
                </a>
            </div>
        </section>

        <!-- Risk + Positioning -->
        <section class="row g-3">
            <div class="col-lg-6">
                <a href="/volatility-regime/detector" class="text-decoration-none" style="color: inherit;">
                    <div class="df-panel p-3 h-100 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small" style="color: var(--muted-foreground);">Volatility Regime</div>
                            <div class="h4 mb-0" x-text="volRegime">Expanding</div>
                            <div class="small text-secondary">σ pendek vs σ panjang</div>
                        </div>
                        <div>
                            <span class="badge text-bg-primary" x-text="regimeBadge">Risk‑on</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-6">
                <a href="/atr/detector" class="text-decoration-none" style="color: inherit;">
                    <div class="df-panel p-3 h-100">
                        <div class="small" style="color: var(--muted-foreground);">ATR‑based Sizing Hint (BTC)</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <div class="h4 mb-0" x-text="(atrMultiple).toFixed(2) + 'x ATR'">1.75x ATR</div>
                            <small class="text-secondary">use as stop distance</small>
                        </div>
                        <div class="small" x-text="'Suggested risk per trade: ' + riskPerTrade + '%'">Suggested risk per trade: 0.75%</div>
                    </div>
                </a>
            </div>
        </section>
    </div>

    <script type="text/javascript">
        function dashboardData() {
            const state = {
                btc: { last: 65420, chg: 1250, chgPct: 1.95, low: 64200, high: 66800, volume: 28.5e9, dominance: 54.2 },
                fgIndex: 'Neutral (53)',
                funding: 0.02,
                fundingTrend: 1,
                oiChange: 0.8,
                liq: { long: 120e6, short: 98e6 },
                basis: 0.35,
                iv30: 52.3,
                ivTrend: 1,
                rr25: -3.1,
                netflow: -3200,
                etfNetflow: 185e6,
                volRegime: 'Expanding',
                regimeBadge: 'Risk‑on',
                atrMultiple: 1.75,
                riskPerTrade: 0.75,
                watchlist: [
                    { symbol: 'BTC', last: 65420, chgPct: 2.1 },
                    { symbol: 'ETH', last: 3420, chgPct: -1.3 },
                    { symbol: 'SOL', last: 185.2, chgPct: 3.8 },
                    { symbol: 'DOGE', last: 0.18, chgPct: -4.2 }
                ]
            };

            // Helpers
            state.formatPrice = (v) => '$' + Number(v).toLocaleString('en-US', { maximumFractionDigits: 2 });
            state.formatVolume = (v) => {
                if (v >= 1e9) return (v / 1e9).toFixed(1) + 'B';
                if (v >= 1e6) return (v / 1e6).toFixed(1) + 'M';
                return Number(v).toLocaleString('en-US');
            };
            state.formatCompact = (v) => {
                const abs = Math.abs(v);
                if (abs >= 1e9) return (v / 1e9).toFixed(0) + 'B';
                if (abs >= 1e6) return (v / 1e6).toFixed(0) + 'M';
                if (abs >= 1e3) return (v / 1e3).toFixed(0) + 'K';
                return String(v);
            };
            state.signed = (v) => (v >= 0 ? '+' : '') + Number(v).toFixed(typeof v === 'number' && Math.abs(v) < 10 ? 2 : 2);
            state.trendText = (v) => v > 0 ? 'Rising' : v < 0 ? 'Falling' : 'Flat';

            // Simulate light real‑time updates
            setInterval(() => {
                const drift = (Math.random() - 0.5) * 150;
                state.btc.last = Math.max(1000, state.btc.last + drift);
                state.btc.chg = drift;
                state.btc.chgPct = (drift / (state.btc.last - drift)) * 100;
            }, 3000);

            return state;
        }
    </script>
@endsection
