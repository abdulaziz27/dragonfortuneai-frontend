@extends('layouts.app')

@section('content')
    {{--
        ETF & Institutional Dashboard
        Think like a trader • Build like an engineer • Visualize like a designer

        Interpretasi Trading:
        - Positive ETF Flow → Institutional accumulation → Bullish medium-term
        - Premium > 50bps → Overvaluation risk → Take profit consideration
        - COT Long/Short ratio → Track smart money positioning
        - High creations/low redemptions → Strong demand signal
    --}}

    <div class="d-flex flex-column h-100 gap-3" x-data="etfInstitutionalController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">ETF & Institutional Dashboard</h1>
                        <span class="pulse-dot pulse-success"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Monitor arus institusional, ETF flow, premium/discount, dan posisi COT untuk Bitcoin
                    </p>
                </div>

                <!-- Global Controls -->
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <select class="form-select" style="width: 140px;" x-model="selectedAsset" @change="refreshAll()">
                        <option value="BTC">Bitcoin</option>
                        <option value="ETH">Ethereum</option>
                    </select>

                    <button class="btn btn-primary" @click="refreshAll()" :disabled="loading">
                        <span x-show="!loading">Refresh All</span>
                        <span x-show="loading" class="spinner-border spinner-border-sm"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 1. ETF Flow & Institutional Overview -->
        <div class="row g-3">
            <!-- ETF Flow Meter Gauge -->
            <div class="col-lg-4">
                <div class="df-panel p-4 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="mb-1">ETF Flow Meter</h5>
                        <small class="text-secondary">Total arus masuk/keluar harian ETF spot</small>
                    </div>

                    <!-- Gauge Display -->
                    <div class="text-center mb-3 flex-shrink-0">
                        <div class="position-relative d-inline-block" style="width: 200px; height: 200px;">
                            <!-- Circular Gauge Background -->
                            <svg viewBox="0 0 200 200" class="w-100 h-100">
                                <!-- Background Arc -->
                                <path d="M 20 100 A 80 80 0 0 1 180 100"
                                      fill="none"
                                      stroke="#e5e7eb"
                                      stroke-width="20"
                                      stroke-linecap="round"/>

                                <!-- Colored Segments: Outflow → Neutral → Inflow -->
                                <path d="M 20 100 A 80 80 0 0 1 60 38"
                                      fill="none"
                                      stroke="#ef4444"
                                      stroke-width="20"
                                      stroke-linecap="round"/>
                                <path d="M 60 38 A 80 80 0 0 1 100 20"
                                      fill="none"
                                      stroke="#f59e0b"
                                      stroke-width="20"
                                      stroke-linecap="round"/>
                                <path d="M 100 20 A 80 80 0 0 1 140 38"
                                      fill="none"
                                      stroke="#22c55e"
                                      stroke-width="20"
                                      stroke-linecap="round"/>
                                <path d="M 140 38 A 80 80 0 0 1 180 100"
                                      fill="none"
                                      stroke="#10b981"
                                      stroke-width="20"
                                      stroke-linecap="round"/>

                                <!-- Indicator Needle -->
                                <!-- Map flow -500M to +500M to 180°-360° arc -->
                                <line :x1="100" :y1="100"
                                      :x2="100 + 70 * Math.cos((180 + getFlowAngle()) * Math.PI / 180)"
                                      :y2="100 + 70 * Math.sin((180 + getFlowAngle()) * Math.PI / 180)"
                                      stroke="#1f2937"
                                      stroke-width="3"
                                      stroke-linecap="round"/>
                                <circle cx="100" cy="100" r="8" fill="#1f2937"/>
                            </svg>
                        </div>

                        <div class="mt-3">
                            <div class="h1 mb-1 fw-bold" :class="flowMeter.daily_flow >= 0 ? 'text-success' : 'text-danger'" x-text="formatFlowValue(flowMeter.daily_flow)">--</div>
                            <div class="badge fs-6" :class="getFlowBadge()" x-text="getFlowLabel()">--</div>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <div class="p-2 rounded mb-3" :class="getFlowAlert()">
                            <div class="small fw-semibold mb-1" x-text="getFlowTitle()">Analysis</div>
                            <div class="small" x-text="getFlowMessage()">Loading...</div>
                        </div>

                        <div class="d-flex justify-content-between small text-secondary">
                            <span>Outflow</span>
                            <span>Inflow</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Institutional Overview Cards -->
            <div class="col-lg-8">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="mb-1">Institutional Overview</h5>
                        <small class="text-secondary">Snapshot metrics kunci dari aktivitas institusional</small>
                    </div>

                    <div class="row g-3 flex-grow-1">
                        <div class="col-md-6">
                            <div class="p-3 rounded h-100" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="small text-secondary mb-1">Net Inflow 24h</div>
                                        <div class="h3 mb-0 fw-bold text-success" x-text="formatCurrency(overview.net_inflow_24h)">--</div>
                                    </div>
                                    <div class="badge text-bg-success" x-text="formatChange(overview.change_24h) + '%'">--</div>
                                </div>
                                <div class="small text-secondary mt-2">
                                    Arus bersih positif mengindikasikan akumulasi institusional
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 rounded h-100" style="background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="small text-secondary mb-1">Total AUM</div>
                                        <div class="h3 mb-0 fw-bold text-primary" x-text="formatCurrency(overview.total_aum)">--</div>
                                    </div>
                                    <div class="badge text-bg-info">All ETFs</div>
                                </div>
                                <div class="small text-secondary mt-2">
                                    Asset Under Management dari semua ETF spot Bitcoin
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 rounded h-100" style="background: rgba(245, 158, 11, 0.1); border-left: 4px solid #f59e0b;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="small text-secondary mb-1">Top Issuer by Flow</div>
                                        <div class="h4 mb-0 fw-bold text-warning" x-text="overview.top_issuer">--</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small text-secondary">Flow</div>
                                        <div class="fw-semibold" x-text="formatCurrency(overview.top_issuer_flow)">--</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 rounded h-100" style="background: rgba(139, 92, 246, 0.1); border-left: 4px solid #8b5cf6;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="small text-secondary mb-1">Total Shares Outstanding</div>
                                        <div class="h4 mb-0 fw-bold text-purple" x-text="formatNumber(overview.total_shares)">--</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small text-secondary">BTC Equivalent</div>
                                        <div class="fw-semibold" x-text="formatNumber(overview.btc_equivalent) + ' BTC'">--</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Spot ETF Details -->
        <div class="row g-3">
            <!-- ETF Flow Table -->
            <div class="col-lg-6">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="mb-0">Recent ETF Flows by Issuer</h5>
                        <small class="text-secondary">Arus ETF harian dari institusi utama</small>
                    </div>

                    <div class="table-responsive flex-grow-1">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Issuer</th>
                                    <th>Ticker</th>
                                    <th class="text-end">Flow (USD)</th>
                                    <th class="text-end">AUM (USD)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="flow in etfFlows" :key="flow.id">
                                    <tr>
                                        <td class="small text-secondary" x-text="flow.date">--</td>
                                        <td class="fw-semibold" x-text="flow.issuer">--</td>
                                        <td x-text="flow.ticker">--</td>
                                        <td class="text-end">
                                            <span :class="flow.flow_usd >= 0 ? 'text-success fw-semibold' : 'text-danger fw-semibold'" x-text="formatFlowValue(flow.flow_usd)">--</span>
                                        </td>
                                        <td class="text-end" x-text="formatCurrency(flow.aum_usd)">--</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 p-2 rounded" style="background: rgba(59, 130, 246, 0.1);">
                        <div class="small text-secondary">
                            <strong>Flow Insight:</strong> Positive flow mengindikasikan akumulasi institusi, negative flow menandakan net redemption.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily ETF Inflows/Outflows Chart -->
            <div class="col-lg-6">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="mb-0">Daily ETF Inflows/Outflows (30 Days)</h5>
                        <small class="text-secondary">Tren arus ETF harian per issuer</small>
                    </div>
                    <div class="flex-grow-1" style="min-height: 280px;">
                        <canvas id="etfFlowChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Premium/Discount & COT Insights -->
        <div class="row g-3">
            <!-- Premium vs NAV Chart -->
            <div class="col-lg-8">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="mb-0">Premium vs NAV (Basis Points)</h5>
                        <small class="text-secondary">ETF diperdagangkan di atas NAV mengindikasikan potensi overbought</small>
                    </div>
                    <div class="flex-grow-1" style="min-height: 300px;">
                        <canvas id="premiumDiscountChart"></canvas>
                    </div>
                    <div class="mt-3 p-2 rounded" style="background: rgba(139, 92, 246, 0.1);">
                        <div class="small text-secondary">
                            <strong>Premium/Discount Insight:</strong> Premium > 50bps = Overvaluation risk. Discount > -50bps = Potential buy opportunity.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Creations vs Redemptions -->
            <div class="col-lg-4">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="mb-0">Creations vs Redemptions</h5>
                        <small class="text-secondary">Aktivitas creation/redemption mingguan</small>
                    </div>

                    <div class="mb-3 flex-grow-1">
                        <template x-for="item in creationsRedemptions" :key="item.id">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div>
                                        <span class="fw-semibold" x-text="item.issuer">--</span>
                                        <div class="small text-secondary" x-text="item.date">--</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="badge" :class="item.net_creation >= 0 ? 'text-bg-success' : 'text-bg-danger'" x-text="item.net_creation >= 0 ? 'Net Creation' : 'Net Redemption'">--</div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 small">
                                    <div class="flex-fill p-2 rounded text-center" style="background: rgba(34, 197, 94, 0.1);">
                                        <div class="text-secondary">Creations</div>
                                        <div class="fw-bold text-success" x-text="formatNumber(item.creations_shares)">--</div>
                                    </div>
                                    <div class="flex-fill p-2 rounded text-center" style="background: rgba(239, 68, 68, 0.1);">
                                        <div class="text-secondary">Redemptions</div>
                                        <div class="fw-bold text-danger" x-text="formatNumber(item.redemptions_shares)">--</div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-auto p-2 rounded" style="background: rgba(34, 197, 94, 0.1);">
                        <div class="small text-secondary">
                            <strong>Creation Insight:</strong> High creations + low redemptions = strong institutional demand.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CME Futures & COT Analysis -->
        <div class="row g-3">
            <!-- CME Open Interest Trend -->
            <div class="col-lg-8">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="mb-0">CME Futures Open Interest Trend</h5>
                        <small class="text-secondary">Tracking institutional exposure melalui CME Bitcoin Futures</small>
                    </div>
                    <div class="flex-grow-1" style="min-height: 280px;">
                        <canvas id="cmeOiChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- COT Breakdown -->
            <div class="col-lg-4">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">COT Breakdown</h5>
                                <small class="text-secondary">Commitment of Traders - Weekly</small>
                            </div>
                            <span class="badge text-bg-info">Weekly</span>
                        </div>
                    </div>

                    <div class="table-responsive flex-grow-1">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Group</th>
                                    <th class="text-end">Long</th>
                                    <th class="text-end">Short</th>
                                    <th class="text-end">Net</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="cot in cotData" :key="cot.id">
                                    <tr>
                                        <td class="fw-semibold" x-text="cot.report_group">--</td>
                                        <td class="text-end text-success" x-text="formatNumber(cot.long_contracts)">--</td>
                                        <td class="text-end text-danger" x-text="formatNumber(cot.short_contracts)">--</td>
                                        <td class="text-end">
                                            <span :class="cot.net >= 0 ? 'text-success fw-semibold' : 'text-danger fw-semibold'" x-text="formatSigned(cot.net)">--</td>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 p-2 rounded" style="background: rgba(245, 158, 11, 0.1);">
                        <div class="small text-secondary">
                            <strong>COT Analysis:</strong> Net long Funds > Dealers = Bullish institutional positioning.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- COT Long vs Short Comparison -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-3">
                    <div class="mb-3">
                        <h5 class="mb-0">COT Long vs Short Positioning</h5>
                        <small class="text-secondary">Perbandingan posisi long dan short per report group</small>
                    </div>
                    <div style="min-height: 300px;">
                        <canvas id="cotComparisonChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trading Insights -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4">
                    <div class="mb-3">
                        <h5 class="mb-1">Trading Insights - ETF & Institutional</h5>
                        <small class="text-secondary">Panduan interpretasi signal untuk institutional flow analysis</small>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e;">
                                <div class="fw-bold mb-2 text-success">Bullish Institutional Signals</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Positive ETF flow > $200M daily</li>
                                        <li>High creations / low redemptions</li>
                                        <li>Premium to NAV < 50bps (fair value)</li>
                                        <li>COT Funds net long increasing</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444;">
                                <div class="fw-bold mb-2 text-danger">Bearish Institutional Signals</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Negative ETF flow > -$200M daily</li>
                                        <li>Low creations / high redemptions</li>
                                        <li>Premium > 100bps (overvalued)</li>
                                        <li>COT Funds net short increasing</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6;">
                                <div class="fw-bold mb-2 text-primary">Neutral / Monitor Zone</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>ETF flow -$100M to +$100M</li>
                                        <li>Balanced creations/redemptions</li>
                                        <li>Premium -30bps to +30bps</li>
                                        <li>COT positioning unchanged</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

    <script>
        function etfInstitutionalController() {
            return {
                selectedAsset: 'BTC',
                loading: false,

                // ETF Flow Meter
                flowMeter: {
                    daily_flow: 243.5, // in millions USD
                },

                // Institutional Overview
                overview: {
                    net_inflow_24h: 243.5, // millions
                    change_24h: 12.8,
                    total_aum: 58.2, // billions
                    top_issuer: 'BlackRock',
                    top_issuer_flow: 156.2, // millions
                    total_shares: 892458000,
                    btc_equivalent: 485200
                },

                // ETF Flows (Recent)
                etfFlows: [
                    { id: 1, date: 'Oct 9', issuer: 'BlackRock', ticker: 'IBIT', flow_usd: 156.2, aum_usd: 24.8 },
                    { id: 2, date: 'Oct 9', issuer: 'Fidelity', ticker: 'FBTC', flow_usd: 87.3, aum_usd: 12.4 },
                    { id: 3, date: 'Oct 9', issuer: 'Grayscale', ticker: 'GBTC', flow_usd: -42.8, aum_usd: 18.6 },
                    { id: 4, date: 'Oct 8', issuer: 'BlackRock', ticker: 'IBIT', flow_usd: 198.5, aum_usd: 24.6 },
                    { id: 5, date: 'Oct 8', issuer: 'Fidelity', ticker: 'FBTC', flow_usd: 112.4, aum_usd: 12.3 },
                    { id: 6, date: 'Oct 8', issuer: 'VanEck', ticker: 'HODL', flow_usd: 28.7, aum_usd: 1.8 }
                ],

                // Creations & Redemptions
                creationsRedemptions: [
                    { id: 1, issuer: 'BlackRock IBIT', date: 'Oct 9', creations_shares: 12500000, redemptions_shares: 2800000, net_creation: 9700000 },
                    { id: 2, issuer: 'Fidelity FBTC', date: 'Oct 9', creations_shares: 8200000, redemptions_shares: 1500000, net_creation: 6700000 },
                    { id: 3, issuer: 'Grayscale GBTC', date: 'Oct 9', creations_shares: 1200000, redemptions_shares: 5800000, net_creation: -4600000 }
                ],

                // COT Data (Weekly)
                cotData: [
                    { id: 1, report_group: 'Asset Managers', long_contracts: 12850, short_contracts: 3240, net: 9610 },
                    { id: 2, report_group: 'Leveraged Funds', long_contracts: 8420, short_contracts: 5680, net: 2740 },
                    { id: 3, report_group: 'Dealers', long_contracts: 4250, short_contracts: 7820, net: -3570 },
                    { id: 4, report_group: 'Other Reportables', long_contracts: 2180, short_contracts: 1950, net: 230 }
                ],

                // Charts
                etfFlowChart: null,
                premiumDiscountChart: null,
                cmeOiChart: null,
                cotComparisonChart: null,

                init() {
                    if (typeof Chart !== 'undefined') {
                        this.initCharts();
                    } else {
                        setTimeout(() => this.initCharts(), 100);
                    }
                },

                initCharts() {
                    // Daily ETF Flow Chart (Stacked Bar)
                    const etfFlowCtx = document.getElementById('etfFlowChart');
                    if (etfFlowCtx) {
                        this.etfFlowChart = new Chart(etfFlowCtx, {
                            type: 'bar',
                            data: {
                                labels: this.generateDateLabels(30),
                                datasets: [
                                    {
                                        label: 'BlackRock',
                                        data: this.generateFlowData(30, 80, 180),
                                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                                        borderColor: 'rgb(59, 130, 246)',
                                        borderWidth: 1
                                    },
                                    {
                                        label: 'Fidelity',
                                        data: this.generateFlowData(30, 50, 120),
                                        backgroundColor: 'rgba(245, 158, 11, 0.8)',
                                        borderColor: 'rgb(245, 158, 11)',
                                        borderWidth: 1
                                    },
                                    {
                                        label: 'Grayscale',
                                        data: this.generateFlowData(30, -60, 40),
                                        backgroundColor: 'rgba(139, 92, 246, 0.8)',
                                        borderColor: 'rgb(139, 92, 246)',
                                        borderWidth: 1
                                    },
                                    {
                                        label: 'VanEck',
                                        data: this.generateFlowData(30, 10, 40),
                                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                                        borderColor: 'rgb(34, 197, 94)',
                                        borderWidth: 1
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: true, position: 'top' },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return context.dataset.label + ': $' + context.parsed.y.toFixed(1) + 'M';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: { stacked: true },
                                    y: {
                                        stacked: true,
                                        position: 'right',
                                        title: { display: true, text: 'Flow (USD Millions)' },
                                        ticks: {
                                            callback: function(value) {
                                                return '$' + value + 'M';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // Premium/Discount Chart
                    const premiumCtx = document.getElementById('premiumDiscountChart');
                    if (premiumCtx) {
                        this.premiumDiscountChart = new Chart(premiumCtx, {
                            type: 'line',
                            data: {
                                labels: this.generateDateLabels(30),
                                datasets: [
                                    {
                                        label: 'IBIT Premium/Discount',
                                        data: this.generatePremiumData(30, -20, 80),
                                        borderColor: 'rgb(59, 130, 246)',
                                        backgroundColor: function(context) {
                                            const chart = context.chart;
                                            const {ctx, chartArea} = chart;
                                            if (!chartArea) return;
                                            const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                                            gradient.addColorStop(0, 'rgba(239, 68, 68, 0.1)');
                                            gradient.addColorStop(0.5, 'rgba(156, 163, 175, 0.1)');
                                            gradient.addColorStop(1, 'rgba(34, 197, 94, 0.1)');
                                            return gradient;
                                        },
                                        tension: 0.4,
                                        fill: true,
                                        borderWidth: 2
                                    },
                                    {
                                        label: 'FBTC Premium/Discount',
                                        data: this.generatePremiumData(30, -30, 70),
                                        borderColor: 'rgb(245, 158, 11)',
                                        backgroundColor: 'transparent',
                                        tension: 0.4,
                                        borderWidth: 2
                                    },
                                    {
                                        label: 'GBTC Premium/Discount',
                                        data: this.generatePremiumData(30, -40, 60),
                                        borderColor: 'rgb(139, 92, 246)',
                                        backgroundColor: 'transparent',
                                        tension: 0.4,
                                        borderWidth: 2
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: { mode: 'index', intersect: false },
                                plugins: {
                                    legend: { display: true, position: 'top' },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + ' bps';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        position: 'right',
                                        title: { display: true, text: 'Premium/Discount (bps)' },
                                        ticks: {
                                            callback: function(value) {
                                                return value + ' bps';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // CME OI Chart
                    const cmeOiCtx = document.getElementById('cmeOiChart');
                    if (cmeOiCtx) {
                        this.cmeOiChart = new Chart(cmeOiCtx, {
                            type: 'line',
                            data: {
                                labels: this.generateDateLabels(60),
                                datasets: [{
                                    label: 'CME Futures Open Interest',
                                    data: this.generateOiData(60, 8000, 12000),
                                    borderColor: 'rgb(59, 130, 246)',
                                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                    tension: 0.4,
                                    fill: true,
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: true, position: 'top' },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return 'OI: $' + context.parsed.y.toLocaleString() + 'M';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        position: 'right',
                                        title: { display: true, text: 'Open Interest (USD Millions)' },
                                        ticks: {
                                            callback: function(value) {
                                                return '$' + (value / 1000).toFixed(1) + 'B';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // COT Comparison Chart
                    const cotCtx = document.getElementById('cotComparisonChart');
                    if (cotCtx) {
                        this.cotComparisonChart = new Chart(cotCtx, {
                            type: 'bar',
                            data: {
                                labels: this.cotData.map(c => c.report_group),
                                datasets: [
                                    {
                                        label: 'Long Contracts',
                                        data: this.cotData.map(c => c.long_contracts),
                                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                                        borderColor: 'rgb(34, 197, 94)',
                                        borderWidth: 1
                                    },
                                    {
                                        label: 'Short Contracts',
                                        data: this.cotData.map(c => c.short_contracts),
                                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                                        borderColor: 'rgb(239, 68, 68)',
                                        borderWidth: 1
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: true, position: 'top' }
                                },
                                scales: {
                                    y: {
                                        position: 'right',
                                        title: { display: true, text: 'Contracts' },
                                        ticks: {
                                            callback: function(value) {
                                                return (value / 1000).toFixed(1) + 'K';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                },

                generateDateLabels(days) {
                    const labels = [];
                    const today = new Date();
                    for (let i = days - 1; i >= 0; i--) {
                        const date = new Date(today);
                        date.setDate(date.getDate() - i);
                        labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
                    }
                    return labels;
                },

                generateFlowData(days, min, max) {
                    const data = [];
                    for (let i = 0; i < days; i++) {
                        const value = Math.random() * (max - min) + min;
                        data.push(parseFloat(value.toFixed(1)));
                    }
                    return data;
                },

                generatePremiumData(days, min, max) {
                    const data = [];
                    let value = (min + max) / 2;
                    for (let i = 0; i < days; i++) {
                        value += (Math.random() - 0.5) * 20;
                        value = Math.max(min, Math.min(max, value));
                        data.push(parseFloat(value.toFixed(1)));
                    }
                    return data;
                },

                generateOiData(days, min, max) {
                    const data = [];
                    let value = (min + max) / 2;
                    for (let i = 0; i < days; i++) {
                        value += (Math.random() - 0.5) * 500;
                        value = Math.max(min, Math.min(max, value));
                        data.push(Math.round(value));
                    }
                    return data;
                },

                // Flow Meter Calculations
                getFlowAngle() {
                    // Map flow from -500M to +500M to 0-180 degrees
                    const normalizedFlow = Math.max(-500, Math.min(500, this.flowMeter.daily_flow));
                    return ((normalizedFlow + 500) / 1000) * 180;
                },

                getFlowBadge() {
                    const flow = this.flowMeter.daily_flow;
                    if (flow >= 200) return 'text-bg-success';
                    if (flow >= 50) return 'text-bg-info';
                    if (flow >= -50) return 'text-bg-warning';
                    return 'text-bg-danger';
                },

                getFlowLabel() {
                    const flow = this.flowMeter.daily_flow;
                    if (flow >= 200) return 'Strong Inflow';
                    if (flow >= 50) return 'Bullish Inflow';
                    if (flow >= -50) return 'Neutral';
                    if (flow >= -200) return 'Bearish Outflow';
                    return 'Strong Outflow';
                },

                getFlowAlert() {
                    const flow = this.flowMeter.daily_flow;
                    if (flow >= 200) return 'bg-success bg-opacity-10';
                    if (flow >= -200) return 'bg-info bg-opacity-10';
                    return 'bg-danger bg-opacity-10';
                },

                getFlowTitle() {
                    const flow = this.flowMeter.daily_flow;
                    if (flow >= 200) return 'Institutional Accumulation';
                    if (flow >= -200) return 'Monitor Zone';
                    return 'Institutional Distribution';
                },

                getFlowMessage() {
                    const flow = this.flowMeter.daily_flow;
                    if (flow >= 200) {
                        return `Strong inflow detected ($${flow.toFixed(1)}M). Institusi aktif mengakumulasi Bitcoin melalui ETF. Signal bullish medium-term.`;
                    }
                    if (flow >= -200) {
                        return `Flow moderate ($${flow >= 0 ? '+' : ''}${flow.toFixed(1)}M). Market institusional dalam kondisi balanced. Monitor untuk konfirmasi arah.`;
                    }
                    return `Strong outflow detected ($${flow.toFixed(1)}M). Institusi melakukan net redemption. Signal bearish, risk-off sentiment.`;
                },

                // Formatting Helpers
                formatFlowValue(value) {
                    const sign = value >= 0 ? '+$' : '-$';
                    return sign + Math.abs(value).toFixed(1) + 'M';
                },

                formatCurrency(value) {
                    if (value >= 1000) {
                        return '$' + (value / 1000).toFixed(1) + 'B';
                    }
                    return '$' + value.toFixed(1) + 'M';
                },

                formatNumber(value) {
                    return value.toLocaleString();
                },

                formatChange(value) {
                    return (value >= 0 ? '+' : '') + value.toFixed(1);
                },

                formatSigned(value) {
                    return (value >= 0 ? '+' : '') + value.toLocaleString();
                },

                refreshAll() {
                    this.loading = true;
                    setTimeout(() => {
                        // Simulate data refresh
                        this.flowMeter.daily_flow = (Math.random() - 0.3) * 400;
                        this.overview.net_inflow_24h = this.flowMeter.daily_flow;
                        this.loading = false;
                    }, 1000);
                }
            };
        }
    </script>

    <style>
        .pulse-success {
            background-color: #22c55e;
            box-shadow: 0 0 0 rgba(34, 197, 94, 0.7);
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            }
            50% {
                box-shadow: 0 0 0 8px rgba(34, 197, 94, 0);
            }
        }

        .text-purple {
            color: #8b5cf6 !important;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
    </style>
@endsection

