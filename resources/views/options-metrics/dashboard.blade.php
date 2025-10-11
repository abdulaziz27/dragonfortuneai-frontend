@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100 gap-3" x-data="optionsMetricsController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">Options Intelligence Dashboard</h1>
                        <span class="pulse-dot pulse-success"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Menyajikan struktur volatilitas, skew 25D, distribusi open interest, dan positioning dealer untuk membaca rezim pasar derivatif.
                    </p>
                </div>

                <!-- Global Controls -->
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <select class="form-select" style="width: 120px;" x-model="selectedAsset">
                        <option value="BTC">BTC</option>
                        <option value="ETH">ETH</option>
                    </select>

                    <select class="form-select" style="width: 140px;" x-model="selectedExchange">
                        <option value="Deribit">Deribit</option>
                        <option value="OKX">OKX</option>
                    </select>

                    <button class="btn btn-primary" @click="applyProfile(); refreshAll();">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                            <path d="M21 12a9 9 0 1 1-9-9c2.5 0 4.8 1 6.4 2.6M21 3v6h-6"/>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- A. Volatility Overview -->
        <div class="row g-3">
            <div class="col-sm-6 col-xl-3">
                <div class="df-panel p-4 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary">ATM IV (30D)</div>
                            <div class="h2 mb-1" x-text="formatPercent(metrics.atmIv)"></div>
                        </div>
                        <div class="badge rounded-pill"
                             :class="metrics.ivChange >= 0 ? 'text-bg-success' : 'text-bg-danger'"
                             x-text="formatDelta(metrics.ivChange, 'pts')"></div>
                    </div>
                    <div class="small text-secondary mt-3" x-text="metrics.ivNarrative"></div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="df-panel p-4 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary">25D Risk Reversal</div>
                            <div class="h2 mb-1" x-text="formatDelta(metrics.skew, '%')"></div>
                        </div>
                        <div class="badge rounded-pill"
                             :class="metrics.skewChange <= 0 ? 'text-bg-warning' : 'text-bg-success'"
                             x-text="formatDelta(metrics.skewChange, 'bps')"></div>
                    </div>
                    <div class="small text-secondary mt-3" x-text="metrics.skewNarrative"></div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="df-panel p-4 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary">Call/Put Imbalance</div>
                            <div class="h2 mb-1" x-text="formatMultiplier(metrics.callPutRatio)"></div>
                        </div>
                        <div class="badge rounded-pill"
                             :class="metrics.callPutDelta >= 0 ? 'text-bg-info' : 'text-bg-danger'"
                             x-text="formatDelta(metrics.callPutDelta, '')"></div>
                    </div>
                    <div class="small text-secondary mt-3" x-text="metrics.cpNarrative"></div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="df-panel p-4 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary">Gamma Hotspot</div>
                            <div class="h2 mb-1" x-text="formatPrice(metrics.gammaHotspot)"></div>
                        </div>
                        <div class="badge rounded-pill"
                             :class="metrics.gammaTag === 'Short Gamma' ? 'text-bg-danger' : 'text-bg-success'"
                             x-text="metrics.gammaTag"></div>
                    </div>
                    <div class="small text-secondary mt-3" x-text="metrics.gammaNarrative"></div>
                </div>
            </div>
        </div>

        <!-- B. IV Smile -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h5 class="mb-1">IV Smile by Strike</h5>
                            <small class="text-secondary">Kurva volatilitas antar strike untuk tenor utama; smile menggambarkan premi volatilitas antar strike, bentuk U menandakan keseimbangan demand call dan put.</small>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <template x-for="tenor in smileTenors" :key="tenor">
                                <span class="badge" :style="`background-color:${smilePalette[tenor]}20;color:${smilePalette[tenor]};`" x-text="tenor"></span>
                            </template>
                        </div>
                    </div>
                    <div class="flex-grow-1 position-relative" style="z-index: 1201;">
                        <canvas id="ivSmileChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- C & D -->
        <div class="row g-3">
            <div class="col-xl-8">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">25D Skew Monitor</h5>
                            <small class="text-secondary">RR25 negatif dapat menandakan permintaan proteksi downside meningkat.</small>
                        </div>
                        <span class="badge text-bg-secondary">Last 24h</span>
                    </div>
                    <div class="flex-grow-1 position-relative" style="z-index: 1201;">
                        <canvas id="skewChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="mb-1">Term Structure Snapshot</h5>
                        <small class="text-secondary">Kurva IV tetap steep; tenor pendek memimpin perubahan volatilitas.</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Tenor</th>
                                    <th class="text-end">ATM IV</th>
                                    <th class="text-end">RR25</th>
                                    <th class="text-end">Flow Bias</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="row in termStructure" :key="row.tenor">
                                    <tr>
                                        <td x-text="row.tenor"></td>
                                        <td class="text-end" x-text="formatPercent(row.atmIv)"></td>
                                        <td class="text-end" x-text="formatDelta(row.rr25, '%')"></td>
                                        <td class="text-end">
                                            <span class="badge rounded-pill"
                                                  :class="row.flow.includes('call') ? 'text-bg-primary' : 'text-bg-warning'"
                                                  x-text="row.flow"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- E & G -->
        <div class="row g-3">
            <div class="col-xl-8">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">OI & Volume by Expiry</h5>
                            <small class="text-secondary">Distribusi posisi antar expiry utama.</small>
                        </div>
                        <span class="badge text-bg-info" x-text="`Spot ${currentProfile().spotLabel}`"></span>
                    </div>
                    <div class="flex-grow-1 position-relative" style="z-index: 1201;">
                        <canvas id="oiVolumeChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="mb-1">Top Strike Flows</h5>
                        <small class="text-secondary">Level strike aktif menggambarkan bias positioning.</small>
                    </div>
                    <div class="d-flex flex-column gap-3">
                        <template x-for="strike in topStrikes" :key="strike.label">
                            <div class="p-3 rounded border" style="border: 1px solid var(--df-border-muted, rgba(148, 163, 184, 0.25));">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-semibold" x-text="strike.label"></div>
                                        <div class="small text-secondary mt-1" x-text="strike.insight"></div>
                                    </div>
                                    <div class="text-end">
                                        <div class="badge text-bg-dark mb-1" x-text="strike.oi"></div>
                                        <div class="badge"
                                             :class="strike.flow.startsWith('+') ? 'text-bg-success' : 'text-bg-danger'"
                                             x-text="strike.flow"></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- F & H -->
        <div class="row g-3">
            <div class="col-xl-8">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">Gamma Exposure Ladder</h5>
                            <small class="text-secondary">Dealer short gamma di sekitar spot, potensi pergerakan harga cepat.</small>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge rounded-pill text-bg-danger" x-text="formatGamma(gammaSummary.netGamma)"></span>
                            <span class="badge rounded-pill text-bg-secondary" x-text="`Pivot ${formatPrice(gammaSummary.pivot)}`"></span>
                        </div>
                    </div>
                    <div class="flex-grow-1 position-relative" style="z-index: 1201;">
                        <canvas id="gammaChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="mb-1">Dealer Positioning</h5>
                        <small class="text-secondary">Ringkasan narasi dealer terhadap pergerakan volatilitas.</small>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <template x-for="note in gammaNarratives" :key="note">
                            <div class="d-flex align-items-start gap-2">
                                <span class="badge rounded-pill text-bg-primary mt-1">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M5 12l5 5L20 7"/>
                                    </svg>
                                </span>
                                <p class="small text-secondary mb-0" x-text="note"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script>
        function optionsMetricsController() {
            return {
                selectedAsset: 'BTC',
                selectedExchange: 'Deribit',

                smileChart: null,
                skewChart: null,
                oiVolumeChart: null,
                gammaChart: null,

                metrics: {},
                termStructure: [],
                topStrikes: [],
                gammaNarratives: [],
                gammaSummary: {},

                smileTenors: ['7D', '14D', '30D', '90D'],
                smilePalette: {
                    '7D': '#3b82f6',
                    '14D': '#10b981',
                    '30D': '#f59e0b',
                    '90D': '#8b5cf6'
                },
                relativeStrikes: [-40, -30, -20, -10, 0, 10, 20, 30, 40],
                rrTenors: ['7D', '14D', '30D', '90D'],
                intradayLabels: [],

                smileDatasets: [],
                skewDatasets: [],
                oiSeries: [],
                gammaData: { labels: [], exposures: [], netGamma: 0 },

                percentFormatter: new Intl.NumberFormat('en-US', { minimumFractionDigits: 1, maximumFractionDigits: 1 }),
                compactFormatter: new Intl.NumberFormat('en-US', { notation: 'compact', maximumFractionDigits: 1 }),

                baseProfiles: {
                    BTC: {
                        Deribit: {
                            spot: 48500,
                            spotLabel: '48.5k',
                            metrics: {
                                atmIv: 68.4,
                                ivChange: 3.1,
                                ivNarrative: 'Volatilitas ATM 30D menanjak karena demand call weekly melonjak.',
                                skew: -4.2,
                                skewChange: -35,
                                skewNarrative: 'RR25 semakin negatif; pasar membayar perlindungan downside.',
                                callPutRatio: 1.32,
                                callPutDelta: 0.14,
                                cpNarrative: 'Delta call lebih dominan; dealer menjaga posisi long delta untuk hedging.',
                                gammaHotspot: 48500,
                                gammaTag: 'Short Gamma',
                                gammaNarrative: 'Zona short gamma di dekat spot membuat dealer sensitif terhadap pergerakan cepat.'
                            },
                            smile: {
                                baseOffset: { '7D': 7.6, '14D': 5.1, '30D': 2.4, '90D': -0.6 },
                                curvature: { '7D': 2.1, '14D': 1.8, '30D': 1.4, '90D': 1.0 },
                                callTilt: 1.8,
                                putTilt: 2.5
                            },
                            rr25: {
                                base: -4.0,
                                amplitude: { '7D': 0.7, '14D': 0.6, '30D': 0.45, '90D': 0.3 },
                                drift: { '7D': 0.3, '14D': 0.2, '30D': 0.1, '90D': 0.05 }
                            },
                            termTemplate: [
                                { tenor: '7D', atmOffset: 11.2, rrOffset: -0.8, flow: '+2.1k call' },
                                { tenor: '14D', atmOffset: 8.4, rrOffset: -0.6, flow: '+1.3k call' },
                                { tenor: '30D', atmOffset: 6.0, rrOffset: -0.4, flow: '+0.6k put' },
                                { tenor: '60D', atmOffset: 3.4, rrOffset: -0.2, flow: '+0.5k put' },
                                { tenor: '90D', atmOffset: 1.6, rrOffset: 0.0, flow: '+0.3k put' }
                            ],
                            topStrikes: [
                                { label: '50k Call', oi: '12.7k OI', flow: '+1.5k', insight: 'Permintaan tinggi menjaga gamma area resistance.' },
                                { label: '48k Put', oi: '10.2k OI', flow: '+1.1k', insight: 'Proteksi downside bertambah pasca sesi Asia.' },
                                { label: '55k Call', oi: '8.4k OI', flow: '+0.8k', insight: 'Wing call jauh mencerminkan ekspektasi breakout.' }
                            ],
                            gamma: {
                                pivot: 49200,
                                offsets: [-4, -3, -2, -1, 0, 1, 2, 3, 4],
                                step: 500,
                                baseMagnitude: 120,
                                flipIndex: 1,
                                decay: 0.85
                            },
                            oiTemplate: [
                                { expiry: '29 Mar 24', callOi: 18600, putOi: 14800, callVol: 5200, putVol: 3900 },
                                { expiry: '26 Apr 24', callOi: 21400, putOi: 17600, callVol: 4800, putVol: 3600 },
                                { expiry: '31 May 24', callOi: 19100, putOi: 20600, callVol: 3500, putVol: 4100 },
                                { expiry: '28 Jun 24', callOi: 16700, putOi: 22100, callVol: 3000, putVol: 4300 }
                            ],
                            gammaNarratives: [
                                'Zona short gamma 47k-49k; dealer agresif mengejar delta saat spot bergerak cepat.',
                                'Bertahan di atas pivot 49.2k meredam penjualan gamma dan mendorong stabilisasi.',
                                'Breakout di atas 50k memicu hedging buy tambahan dari dealer short gamma.'
                            ]
                        },
                        OKX: {
                            spot: 47800,
                            spotLabel: '47.8k',
                            metrics: {
                                atmIv: 64.9,
                                ivChange: 2.1,
                                ivNarrative: 'IV 30D stabil; desk Asia memilih strategi carry short vol.',
                                skew: -3.3,
                                skewChange: -18,
                                skewNarrative: 'RR25 negatif tetapi lebih landai dari Deribit.',
                                callPutRatio: 1.18,
                                callPutDelta: 0.07,
                                cpNarrative: 'Flow cenderung seimbang; kalender spread mendominasi.',
                                gammaHotspot: 47200,
                                gammaTag: 'Short Gamma',
                                gammaNarrative: 'Gamma negatif tipis; dealer masih responsif terhadap range trading.'
                            },
                            smile: {
                                baseOffset: { '7D': 6.8, '14D': 4.4, '30D': 2.0, '90D': -0.8 },
                                curvature: { '7D': 1.9, '14D': 1.6, '30D': 1.2, '90D': 0.9 },
                                callTilt: 1.4,
                                putTilt: 2.1
                            },
                            rr25: {
                                base: -3.0,
                                amplitude: { '7D': 0.5, '14D': 0.45, '30D': 0.35, '90D': 0.25 },
                                drift: { '7D': 0.2, '14D': 0.15, '30D': 0.1, '90D': 0.05 }
                            },
                            termTemplate: [
                                { tenor: '7D', atmOffset: 9.6, rrOffset: -0.5, flow: '+1.6k call' },
                                { tenor: '14D', atmOffset: 7.0, rrOffset: -0.4, flow: '+0.9k call' },
                                { tenor: '30D', atmOffset: 4.8, rrOffset: -0.2, flow: '+0.4k put' },
                                { tenor: '60D', atmOffset: 2.6, rrOffset: -0.1, flow: '+0.3k put' },
                                { tenor: '90D', atmOffset: 1.0, rrOffset: 0.0, flow: '+0.2k put' }
                            ],
                            topStrikes: [
                                { label: '48k Call', oi: '9.9k OI', flow: '+1.2k', insight: 'Call dekat spot ramai, mencerminkan chase upside.' },
                                { label: '45k Put', oi: '7.1k OI', flow: '+0.7k', insight: 'Put proteksi ringan untuk treasury desk.' },
                                { label: '52k Call', oi: '5.8k OI', flow: '+0.4k', insight: 'Wing jauh hanya dibeli tipis, fokus pada strike dekat.' }
                            ],
                            gamma: {
                                pivot: 48600,
                                offsets: [-4, -3, -2, -1, 0, 1, 2, 3, 4],
                                step: 500,
                                baseMagnitude: 100,
                                flipIndex: 1,
                                decay: 0.8
                            },
                            oiTemplate: [
                                { expiry: '29 Mar 24', callOi: 14800, putOi: 12100, callVol: 4300, putVol: 3200 },
                                { expiry: '26 Apr 24', callOi: 17000, putOi: 15100, callVol: 3900, putVol: 3100 },
                                { expiry: '31 May 24', callOi: 15400, putOi: 16300, callVol: 3100, putVol: 3400 },
                                { expiry: '28 Jun 24', callOi: 14100, putOi: 17900, callVol: 2700, putVol: 3600 }
                            ],
                            gammaNarratives: [
                                'Gamma negatif lebih tipis; dealer Asia leluasa menjaga delta netral.',
                                'Pivot 48.6k menjadi garis demarkasi arah hedging.',
                                'Break di bawah 46k dapat memicu penjualan tambahan dari dealer.'
                            ]
                        }
                    },
                    ETH: {
                        Deribit: {
                            spot: 3200,
                            spotLabel: '3.2k',
                            metrics: {
                                atmIv: 72.8,
                                ivChange: 2.6,
                                ivNarrative: 'IV ETH naik seiring narasi ETF institutional flow.',
                                skew: -2.9,
                                skewChange: -24,
                                skewNarrative: 'RR25 melebar negatif; demand put melindungi level 3k.',
                                callPutRatio: 1.22,
                                callPutDelta: 0.09,
                                cpNarrative: 'Call tenor dekat aktif; dealer menjaga net long delta.',
                                gammaHotspot: 3200,
                                gammaTag: 'Short Gamma',
                                gammaNarrative: 'Dealer short gamma di area spot; breakout memicu chase beli.'
                            },
                            smile: {
                                baseOffset: { '7D': 8.4, '14D': 5.8, '30D': 3.0, '90D': -0.4 },
                                curvature: { '7D': 2.4, '14D': 2.0, '30D': 1.5, '90D': 1.1 },
                                callTilt: 2.0,
                                putTilt: 2.8
                            },
                            rr25: {
                                base: -2.6,
                                amplitude: { '7D': 0.6, '14D': 0.55, '30D': 0.4, '90D': 0.25 },
                                drift: { '7D': 0.25, '14D': 0.18, '30D': 0.12, '90D': 0.06 }
                            },
                            termTemplate: [
                                { tenor: '7D', atmOffset: 12.0, rrOffset: -0.6, flow: '+1.8k call' },
                                { tenor: '14D', atmOffset: 8.8, rrOffset: -0.4, flow: '+1.1k call' },
                                { tenor: '30D', atmOffset: 6.2, rrOffset: -0.3, flow: '+0.5k put' },
                                { tenor: '60D', atmOffset: 3.7, rrOffset: -0.1, flow: '+0.3k put' },
                                { tenor: '90D', atmOffset: 1.8, rrOffset: 0.0, flow: '+0.2k put' }
                            ],
                            topStrikes: [
                                { label: '3.4k Call', oi: '9.8k OI', flow: '+1.0k', insight: 'Call dekat spot diburu untuk mengejar narasi ETF.' },
                                { label: '3.0k Put', oi: '8.2k OI', flow: '+0.9k', insight: 'Put proteksi menjaga area psikologis 3k.' },
                                { label: '3.6k Call', oi: '6.6k OI', flow: '+0.6k', insight: 'Wing jauh aktif, mengantisipasi squeeze bullish.' }
                            ],
                            gamma: {
                                pivot: 3270,
                                offsets: [-4, -3, -2, -1, 0, 1, 2, 3, 4],
                                step: 100,
                                baseMagnitude: 95,
                                flipIndex: 1,
                                decay: 0.75
                            },
                            oiTemplate: [
                                { expiry: '29 Mar 24', callOi: 15800, putOi: 13200, callVol: 4700, putVol: 3600 },
                                { expiry: '26 Apr 24', callOi: 19400, putOi: 16900, callVol: 4400, putVol: 3300 },
                                { expiry: '31 May 24', callOi: 17800, putOi: 19100, callVol: 3200, putVol: 3700 },
                                { expiry: '28 Jun 24', callOi: 16400, putOi: 21200, callVol: 2800, putVol: 4000 }
                            ],
                            gammaNarratives: [
                                'Dealer short gamma kuat di 3.1k-3.3k; volatilitas mudah meningkat.',
                                'Pivot gamma 3.27k menjadi trigger hedging beli jika ditembus.',
                                'Wing positif baru muncul di atas 3.4k, membuka ruang squeeze lebih jauh.'
                            ]
                        },
                        OKX: {
                            spot: 3120,
                            spotLabel: '3.12k',
                            metrics: {
                                atmIv: 69.8,
                                ivChange: 1.8,
                                ivNarrative: 'IV ETH di OKX naik moderat; spread terhadap Deribit menyempit.',
                                skew: -2.1,
                                skewChange: -16,
                                skewNarrative: 'RR25 lebih landai; demand proteksi relatif terukur.',
                                callPutRatio: 1.08,
                                callPutDelta: 0.04,
                                cpNarrative: 'Call-put hampir seimbang, strategi relative value dominan.',
                                gammaHotspot: 3120,
                                gammaTag: 'Short Gamma',
                                gammaNarrative: 'Gamma negatif ringan; dealer nyaman menjaga range.'
                            },
                            smile: {
                                baseOffset: { '7D': 7.4, '14D': 4.9, '30D': 2.4, '90D': -0.6 },
                                curvature: { '7D': 2.0, '14D': 1.7, '30D': 1.3, '90D': 1.0 },
                                callTilt: 1.7,
                                putTilt: 2.4
                            },
                            rr25: {
                                base: -1.8,
                                amplitude: { '7D': 0.45, '14D': 0.4, '30D': 0.3, '90D': 0.22 },
                                drift: { '7D': 0.2, '14D': 0.15, '30D': 0.1, '90D': 0.05 }
                            },
                            termTemplate: [
                                { tenor: '7D', atmOffset: 9.8, rrOffset: -0.4, flow: '+1.3k call' },
                                { tenor: '14D', atmOffset: 7.2, rrOffset: -0.3, flow: '+0.7k call' },
                                { tenor: '30D', atmOffset: 5.0, rrOffset: -0.1, flow: '+0.3k put' },
                                { tenor: '60D', atmOffset: 3.1, rrOffset: -0.1, flow: '+0.2k put' },
                                { tenor: '90D', atmOffset: 1.5, rrOffset: 0.0, flow: '+0.1k put' }
                            ],
                            topStrikes: [
                                { label: '3.1k Call', oi: '8.4k OI', flow: '+0.8k', insight: 'Call dekat spot aktif mengikuti momentum.' },
                                { label: '2.9k Put', oi: '6.9k OI', flow: '+0.6k', insight: 'Put proteksi ringan untuk menjaga downside.' },
                                { label: '3.4k Call', oi: '5.4k OI', flow: '+0.4k', insight: 'Wing jauh tetap hidup namun dengan ukuran kecil.' }
                            ],
                            gamma: {
                                pivot: 3210,
                                offsets: [-4, -3, -2, -1, 0, 1, 2, 3, 4],
                                step: 90,
                                baseMagnitude: 80,
                                flipIndex: 1,
                                decay: 0.7
                            },
                            oiTemplate: [
                                { expiry: '29 Mar 24', callOi: 13400, putOi: 11200, callVol: 3600, putVol: 2800 },
                                { expiry: '26 Apr 24', callOi: 15100, putOi: 13900, callVol: 3300, putVol: 2900 },
                                { expiry: '31 May 24', callOi: 13900, putOi: 15200, callVol: 2700, putVol: 3000 },
                                { expiry: '28 Jun 24', callOi: 13100, putOi: 17300, callVol: 2500, putVol: 3300 }
                            ],
                            gammaNarratives: [
                                'Gamma negatif ringan membuat pasar lebih tenang, cocok range trading.',
                                'Pivot 3.21k menjadi patokan arah hedging dealer.',
                                'Lonjakan di atas 3.3k memaksa dealer mengejar delta ke sisi beli.'
                            ]
                        }
                    }
                },

                init() {
                    this.generateIntradayLabels();
                    this.applyProfile();
                    this.$watch('selectedAsset', () => {
                        this.applyProfile();
                        this.refreshAll();
                    });
                    this.$watch('selectedExchange', () => {
                        this.applyProfile();
                        this.refreshAll();
                    });
                    this.waitForChart(() => this.renderAllCharts());
                },

                generateIntradayLabels() {
                    const points = 12;
                    const now = new Date();
                    const labels = [];
                    for (let i = points - 1; i >= 0; i--) {
                        const stamp = new Date(now.getTime() - i * 60 * 60 * 1000);
                        labels.push(stamp.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }));
                    }
                    this.intradayLabels = labels;
                },

                applyProfile() {
                    const profile = this.currentProfile();
                    this.metrics = profile.metrics;
                    this.termStructure = this.generateTermStructure(profile);
                    this.topStrikes = profile.topStrikes;
                    this.gammaNarratives = profile.gammaNarratives;
                    this.smileDatasets = this.generateSmileSeries(profile);
                    this.skewDatasets = this.generateRR25Series(profile);
                    this.oiSeries = this.generateOiByExpiry(profile);
                    this.gammaData = this.generateGamma(profile);
                    this.gammaSummary = {
                        netGamma: this.gammaData.netGamma,
                        pivot: profile.gamma.pivot
                    };
                },

                currentProfile() {
                    return this.baseProfiles[this.selectedAsset][this.selectedExchange];
                },

                refreshAll() {
                    this.destroyCharts();
                    this.waitForChart(() => this.renderAllCharts());
                },

                waitForChart(callback) {
                    if (typeof Chart !== 'undefined') {
                        callback();
                    } else {
                        setTimeout(() => this.waitForChart(callback), 80);
                    }
                },

                destroyCharts() {
                    if (this.smileChart) {
                        this.smileChart.destroy();
                        this.smileChart = null;
                    }
                    if (this.skewChart) {
                        this.skewChart.destroy();
                        this.skewChart = null;
                    }
                    if (this.oiVolumeChart) {
                        this.oiVolumeChart.destroy();
                        this.oiVolumeChart = null;
                    }
                    if (this.gammaChart) {
                        this.gammaChart.destroy();
                        this.gammaChart = null;
                    }
                },

                renderAllCharts() {
                    this.renderSmileChart();
                    this.renderSkewChart();
                    this.renderOiVolumeChart();
                    this.renderGammaChart();
                },

                renderSmileChart() {
                    const ctx = document.getElementById('ivSmileChart');
                    if (!ctx) return;

                    const datasets = this.smileTenors.map((tenor) => ({
                        label: tenor,
                        data: this.smileDatasets[tenor],
                        borderColor: this.smilePalette[tenor],
                        backgroundColor: this.smilePalette[tenor] + '33',
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        fill: false
                    }));

                    this.smileChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: this.relativeStrikes.map((strike) => `${strike}%`),
                            datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.2)'
                                    },
                                    ticks: {
                                        callback: (value) => `${value}%`
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.15)',
                                        borderDash: [4, 4]
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    align: 'end'
                                },
                                tooltip: {
                                    backgroundColor: '#0f172a',
                                    callbacks: {
                                        label: (context) => `${context.dataset.label}: ${context.parsed.y}%`
                                    }
                                }
                            }
                        }
                    });
                },

                renderSkewChart() {
                    const ctx = document.getElementById('skewChart');
                    if (!ctx) return;

                    const colors = ['#38bdf8', '#10b981', '#f59e0b', '#8b5cf6'];
                    const datasets = this.rrTenors.map((tenor, idx) => ({
                        label: tenor,
                        data: this.skewDatasets[tenor],
                        borderColor: colors[idx % colors.length],
                        backgroundColor: colors[idx % colors.length] + '33',
                        tension: 0.35,
                        borderWidth: 2,
                        fill: false
                    }));

                    this.skewChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: this.intradayLabels,
                            datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.2)'
                                    },
                                    ticks: {
                                        callback: (value) => `${value}%`
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.15)',
                                        borderDash: [4, 4]
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    align: 'end'
                                },
                                tooltip: {
                                    backgroundColor: '#0f172a',
                                    callbacks: {
                                        label: (context) => `${context.dataset.label}: ${context.parsed.y}%`
                                    }
                                }
                            }
                        }
                    });
                },

                renderOiVolumeChart() {
                    const ctx = document.getElementById('oiVolumeChart');
                    if (!ctx) return;

                    this.oiVolumeChart = new Chart(ctx, {
                        data: {
                            labels: this.oiSeries.map(item => item.expiry),
                            datasets: [
                                {
                                    type: 'bar',
                                    label: 'Call OI',
                                    data: this.oiSeries.map(item => item.callOi),
                                    backgroundColor: 'rgba(59, 130, 246, 0.75)',
                                    borderRadius: 4,
                                    stack: 'oi'
                                },
                                {
                                    type: 'bar',
                                    label: 'Put OI',
                                    data: this.oiSeries.map(item => item.putOi),
                                    backgroundColor: 'rgba(239, 68, 68, 0.75)',
                                    borderRadius: 4,
                                    stack: 'oi'
                                },
                                {
                                    type: 'line',
                                    label: 'Total Volume',
                                    data: this.oiSeries.map(item => item.totalVol),
                                    borderColor: '#22c55e',
                                    backgroundColor: '#22c55e33',
                                    tension: 0.3,
                                    yAxisID: 'y1',
                                    fill: false,
                                    pointRadius: 3,
                                    pointHoverRadius: 5
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    stacked: true,
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.2)'
                                    },
                                    ticks: {
                                        callback: (value) => this.compactFormatter.format(value)
                                    },
                                    title: {
                                        display: true,
                                        text: 'Open Interest'
                                    }
                                },
                                y1: {
                                    beginAtZero: true,
                                    position: 'right',
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        callback: (value) => this.compactFormatter.format(value)
                                    },
                                    title: {
                                        display: true,
                                        text: 'Volume'
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.15)',
                                        borderDash: [4, 4]
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    align: 'end'
                                },
                                tooltip: {
                                    backgroundColor: '#0f172a',
                                    callbacks: {
                                        label: (context) => {
                                            const value = context.parsed.y ?? context.parsed;
                                            return `${context.dataset.label}: ${this.compactFormatter.format(value)}`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                },

                renderGammaChart() {
                    const ctx = document.getElementById('gammaChart');
                    if (!ctx) return;

                    this.gammaChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: this.gammaData.labels,
                            datasets: [
                                {
                                    label: 'Gamma Exposure',
                                    data: this.gammaData.exposures,
                                    backgroundColor: this.gammaData.exposures.map(value =>
                                        value >= 0 ? 'rgba(34, 197, 94, 0.8)' : 'rgba(239, 68, 68, 0.8)'
                                    ),
                                    borderRadius: 4
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            scales: {
                                x: {
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.2)'
                                    },
                                    ticks: {
                                        callback: (value) => `${value}k`
                                    },
                                    title: {
                                        display: true,
                                        text: 'Gamma (k)'
                                    }
                                },
                                y: {
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.15)',
                                        borderDash: [4, 4]
                                    }
                                }
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#0f172a',
                                    callbacks: {
                                        label: (context) => `Gamma: ${context.parsed.x}k`
                                    }
                                }
                            }
                        }
                    });
                },

                generateTermStructure(profile) {
                    return profile.termTemplate.map((item) => ({
                        tenor: item.tenor,
                        atmIv: profile.metrics.atmIv + item.atmOffset,
                        rr25: profile.rr25.base + item.rrOffset,
                        flow: item.flow
                    }));
                },

                generateSmileSeries(profile) {
                    const result = {};
                    this.smileTenors.forEach((tenor) => {
                        const baseAtm = profile.metrics.atmIv + profile.smile.baseOffset[tenor];
                        const curvature = profile.smile.curvature[tenor];
                        result[tenor] = this.relativeStrikes.map((strike) => {
                            if (strike === 0) {
                                return parseFloat(baseAtm.toFixed(1));
                            }
                            const absStrike = Math.abs(strike);
                            const normalized = Math.pow(absStrike / 10, 1.08);
                            const tilt = strike > 0
                                ? profile.smile.callTilt * (absStrike / 40)
                                : profile.smile.putTilt * (absStrike / 40);
                            const value = baseAtm + normalized * curvature + tilt;
                            return parseFloat(value.toFixed(1));
                        });
                    });
                    return result;
                },

                generateRR25Series(profile) {
                    const result = {};
                    this.rrTenors.forEach((tenor, idx) => {
                        const base = profile.rr25.base;
                        const amplitude = profile.rr25.amplitude[tenor];
                        const drift = profile.rr25.drift[tenor];
                        result[tenor] = this.intradayLabels.map((_, index) => {
                            const wave = Math.sin((index / (this.intradayLabels.length - 1)) * Math.PI * 1.4 + idx * 0.35);
                            const value = base + amplitude * wave + drift * (index / this.intradayLabels.length);
                            return parseFloat(value.toFixed(2));
                        });
                    });
                    return result;
                },

                generateOiByExpiry(profile) {
                    return profile.oiTemplate.map((item, index) => {
                        const modulation = 1 + 0.03 * Math.sin(index * 0.8);
                        const callOi = Math.round(item.callOi * modulation);
                        const putOi = Math.round(item.putOi * (2 - modulation));
                        const callVol = Math.round(item.callVol * modulation);
                        const putVol = Math.round(item.putVol * (1.8 - modulation));
                        return {
                            expiry: item.expiry,
                            callOi,
                            putOi,
                            callVol,
                            putVol,
                            totalVol: callVol + putVol
                        };
                    });
                },

                generateGamma(profile) {
                    const labels = [];
                    const exposures = [];
                    const { offsets, step, baseMagnitude, flipIndex, decay, pivot } = profile.gamma;
                    offsets.forEach((offset) => {
                        const priceLevel = pivot + offset * step;
                        labels.push(this.formatPriceLevel(priceLevel));
                        const distance = Math.abs(offset);
                        const magnitude = baseMagnitude * Math.pow(1.25, distance) * Math.exp(-distance * decay * 0.1);
                        const sign = offset <= flipIndex ? -1 : 1;
                        const value = Math.round(magnitude * sign);
                        exposures.push(value);
                    });
                    const netGamma = exposures.reduce((acc, value) => acc + value, 0);
                    return { labels, exposures, netGamma };
                },

                formatPercent(value) {
                    return `${this.percentFormatter.format(value)}%`;
                },

                formatDelta(value, suffix = '') {
                    let formatted;
                    if (suffix === 'bps') {
                        formatted = Math.round(value);
                    } else if (Math.abs(value) < 1) {
                        formatted = value.toFixed(2);
                    } else {
                        formatted = value.toFixed(1);
                    }
                    const sign = value > 0 ? '+' : '';
                    return `${sign}${formatted}${suffix ? ` ${suffix}` : ''}`;
                },

                formatMultiplier(value) {
                    return `${value.toFixed(2)}x`;
                },

                formatPrice(value) {
                    if (value >= 1000) {
                        return `${(value / 1000).toFixed(1)}k`;
                    }
                    return value.toLocaleString();
                },

                formatGamma(value) {
                    const sign = value > 0 ? '+' : '';
                    return `${sign}${value}k gamma`;
                },

                formatPriceLevel(value) {
                    if (value >= 1000) {
                        return `${(value / 1000).toFixed(1)}k`;
                    }
                    return value.toLocaleString();
                }
            };
        }
    </script>
@endsection
