@extends('layouts.app')

@section('content')
    {{--
        Sentiment & Flow Dashboard
        Think like a trader • Build like an engineer • Visualize like a designer

        Interpretasi Trading:
        - Fear & Greed Index < 20 → Extreme Fear → Contrarian buy opportunity
        - Fear & Greed Index > 80 → Extreme Greed → Take profit zone
        - Social Mentions spike → FOMO building → Potential top near
        - Funding Dominance → Track leverage positioning across exchanges
        - Whale Alerts → Large wallet movements = Smart money positioning
    --}}

    <div class="d-flex flex-column h-100 gap-3" x-data="sentimentFlowController()">
        <!-- Page Header -->
        <div class="derivatives-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h1 class="mb-0">Sentiment & Flow Analysis BTC</h1>
                        <span class="pulse-dot pulse-info"></span>
                    </div>
                    <p class="mb-0 text-secondary">
                        Pantau sentimen pasar, tren sosial media, dominasi funding rate & pergerakan whale
                    </p>
                </div>

                <!-- Global Controls -->
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <!-- <select class="form-select" style="width: 120px;" x-model="selectedAsset" @change="refreshAll()">
                        <option value="BTC">Bitcoin</option>
                        <option value="ETH">Ethereum</option>
                        <option value="CRYPTO">All Crypto</option>
                    </select>

                    <button class="btn btn-primary" @click="refreshAll()" :disabled="loading">
                        <span x-show="!loading">Refresh All</span>
                        <span x-show="loading" class="spinner-border spinner-border-sm"></span>
                    </button> -->
                </div>
            </div>
        </div>

        <!-- Fear & Greed Index + Sentiment Overview -->
        <div class="row g-3">
            <!-- Fear & Greed Gauge -->
            <div class="col-lg-4">
                <div class="df-panel p-4 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="mb-1">Fear & Greed Index</h5>
                        <small class="text-secondary">Indeks Ketakutan & Keserakahan Pasar</small>
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

                                <!-- Colored Segments -->
                                <path d="M 20 100 A 80 80 0 0 1 52 42"
                                      fill="none"
                                      stroke="#ef4444"
                                      stroke-width="20"
                                      stroke-linecap="round"/>
                                <path d="M 52 42 A 80 80 0 0 1 100 20"
                                      fill="none"
                                      stroke="#f59e0b"
                                      stroke-width="20"
                                      stroke-linecap="round"/>
                                <path d="M 100 20 A 80 80 0 0 1 148 42"
                                      fill="none"
                                      stroke="#22c55e"
                                      stroke-width="20"
                                      stroke-linecap="round"/>
                                <path d="M 148 42 A 80 80 0 0 1 180 100"
                                      fill="none"
                                      stroke="#ef4444"
                                      stroke-width="20"
                                      stroke-linecap="round"/>

                                <!-- Indicator Needle -->
                                <!-- Arc is top semicircle: left (180°) → top (270°/-90°) → right (360°/0°) -->
                                <!-- Map value 0-100 to 180°-360° for correct positioning on top arc -->
                                <line :x1="100" :y1="100"
                                      :x2="100 + 70 * Math.cos((180 + fearGreedScore * 1.8) * Math.PI / 180)"
                                      :y2="100 + 70 * Math.sin((180 + fearGreedScore * 1.8) * Math.PI / 180)"
                                      stroke="#1f2937"
                                      stroke-width="3"
                                      stroke-linecap="round"/>
                                <circle cx="100" cy="100" r="8" fill="#1f2937"/>
                            </svg>
                        </div>

                        <div class="mt-3">
                            <div class="h1 mb-1 fw-bold" x-text="fearGreedScore">--</div>
                            <div class="badge fs-6" :class="getFearGreedBadge()" x-text="getFearGreedLabel()">--</div>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <div class="p-2 rounded mb-3" :class="getFearGreedAlert()">
                            <div class="small fw-semibold mb-1" x-text="getFearGreedTitle()">Analysis</div>
                            <div class="small" x-text="getFearGreedMessage()">Loading...</div>
                        </div>

                        <div class="d-flex justify-content-between small text-secondary">
                            <span>Fear</span>
                            <span>Greed</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Sentiment Breakdown -->
            <div class="col-lg-8">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="mb-3 flex-shrink-0">
                        <h5 class="mb-1">Social Media Sentiment - Daily Mentions</h5>
                        <small class="text-secondary">Analisis sentimen dari penyebutan harian di media sosial</small>
                    </div>
                    <div class="flex-grow-1" style="min-height: 280px;">
                        <canvas id="socialChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Platform Breakdown -->
        <div class="row g-3">
            <div class="col-lg-12">
                <div class="df-panel p-3">
                    <div class="mb-3">
                        <h5 class="mb-1">Social Platform Breakdown - Last 24h</h5>
                        <small class="text-secondary">Rincian aktivitas per platform dalam 24 jam terakhir</small>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <div class="fw-bold">Twitter / X</div>
                                        <div class="h4 mb-0 mt-1" x-text="socialBreakdown.twitter.mentions + ' mentions'">--</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="badge" :class="socialBreakdown.twitter.sentiment >= 0 ? 'text-bg-success' : 'text-bg-danger'" x-text="socialBreakdown.twitter.sentiment + '%'">--</div>
                                        <div class="small text-secondary">Sentiment</div>
                                    </div>
                                </div>
                                <div class="small text-secondary">
                                    Top keywords: <span class="fw-semibold" x-text="socialBreakdown.twitter.keywords">--</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <div class="fw-bold">Reddit</div>
                                        <div class="h4 mb-0 mt-1" x-text="socialBreakdown.reddit.mentions + ' posts'">--</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="badge" :class="socialBreakdown.reddit.sentiment >= 0 ? 'text-bg-success' : 'text-bg-danger'" x-text="socialBreakdown.reddit.sentiment + '%'">--</div>
                                        <div class="small text-secondary">Sentiment</div>
                                    </div>
                                </div>
                                <div class="small text-secondary">
                                    Top subreddits: <span class="fw-semibold" x-text="socialBreakdown.reddit.keywords">--</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <div class="fw-bold">Google Trends</div>
                                        <div class="h4 mb-0 mt-1" x-text="socialBreakdown.google.score + ' / 100'">--</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="badge" :class="socialBreakdown.google.change >= 0 ? 'text-bg-success' : 'text-bg-danger'" x-text="formatChange(socialBreakdown.google.change) + '%'">--</div>
                                        <div class="small text-secondary">24h Change</div>
                                    </div>
                                </div>
                                <div class="small text-secondary">
                                    Search interest: <span class="fw-semibold" x-text="socialBreakdown.google.region">--</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Funding Dominance & Whale Flow -->
        <div class="row g-3">
            <!-- Funding Rate Heatmap -->
            <div class="col-lg-6">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">Funding Rate Dominance</h5>
                                <small class="text-secondary">Tracking posisi leverage dominan antar exchange</small>
                            </div>
                            <span class="badge text-bg-info">8h intervals</span>
                        </div>
                    </div>

                    <div class="table-responsive flex-shrink-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Exchange</th>
                                    <th>Funding Rate</th>
                                    <th>Trend</th>
                                    <th>Signal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="item in fundingDominance" :key="item.exchange">
                                    <tr>
                                        <td class="fw-semibold" x-text="item.exchange">--</td>
                                        <td>
                                            <span :class="item.rate >= 0 ? 'text-success' : 'text-danger'" x-text="formatFundingRate(item.rate)">--</span>
                                        </td>
                                        <td>
                                            <span x-show="item.trend === 'up'">Trending Up</span>
                                            <span x-show="item.trend === 'down'">Trending Down</span>
                                            <span x-show="item.trend === 'stable'">Stable</span>
                                        </td>
                                        <td>
                                            <span class="badge" :class="getFundingActionBadge(item.rate)" x-text="getFundingAction(item.rate)">--</span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Funding Rate Heatmap Visual -->
                    <div class="mt-3 flex-shrink-0">
                        <div class="small text-secondary mb-2 fw-semibold">Visual Heatmap</div>
                        <div style="height: 140px;">
                            <canvas id="fundingHeatmap"></canvas>
                        </div>
                    </div>

                    <div class="mt-auto pt-3">
                        <div class="p-2 rounded" style="background: rgba(59, 130, 246, 0.1);">
                            <div class="small text-secondary">
                                <strong>Tip:</strong> Funding rate positif tinggi → Longs crowded → Potensi long squeeze.
                                Bandingkan antar exchange untuk cari arbitrage opportunity.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Whale Flow Balance -->
            <div class="col-lg-6">
                <div class="df-panel p-3 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">Whale Flow Balance</h5>
                                <small class="text-secondary">Memantau posisi smart money melalui whale movements</small>
                            </div>
                            <span class="badge text-bg-warning">Real-time</span>
                        </div>
                    </div>

                    <!-- Flow Balance Chart -->
                    <div class="flex-grow-1" style="min-height: 240px;">
                        <canvas id="whaleFlowChart"></canvas>
                    </div>

                    <div class="mt-3 flex-shrink-0">
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="p-2 rounded" style="background: rgba(239, 68, 68, 0.1);">
                                    <div class="small text-secondary">Inflow to Exchanges</div>
                                    <div class="h5 mb-0 fw-bold text-danger" x-text="'$' + whaleFlow.inflow + 'M'">--</div>
                                    <div class="small text-secondary">Last 24h</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded" style="background: rgba(34, 197, 94, 0.1);">
                                    <div class="small text-secondary">Outflow from Exchanges</div>
                                    <div class="h5 mb-0 fw-bold text-success" x-text="'$' + whaleFlow.outflow + 'M'">--</div>
                                    <div class="small text-secondary">Last 24h</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 flex-shrink-0">
                        <div class="d-flex justify-content-between align-items-center p-2 rounded" :class="whaleFlow.netFlow >= 0 ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10'">
                            <div class="small fw-semibold">Net Flow:</div>
                            <div class="h5 mb-0 fw-bold" :class="whaleFlow.netFlow >= 0 ? 'text-success' : 'text-danger'" x-text="(whaleFlow.netFlow >= 0 ? '+$' : '-$') + Math.abs(whaleFlow.netFlow) + 'M'">--</div>
                        </div>
                        <div class="mt-2 small text-secondary">
                            <span x-show="whaleFlow.netFlow >= 0"><strong>Bullish:</strong> More whale money leaving exchanges (accumulation)</span>
                            <span x-show="whaleFlow.netFlow < 0"><strong>Bearish:</strong> More whale money entering exchanges (distribution)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Whale Alerts -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-3">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">Recent Whale Alerts</h5>
                                <small class="text-secondary">Pelacakan transaksi whale besar secara real-time</small>
                            </div>
                            <span class="badge text-bg-warning">Live Feed</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
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
                            <tbody>
                                <template x-for="whale in whaleAlerts" :key="whale.id">
                                    <tr :class="getWhaleAlertRowClass(whale.type)">
                                        <td class="small text-secondary" x-text="whale.time">--</td>
                                        <td>
                                            <span class="badge" :class="whale.type === 'in' ? 'text-bg-danger' : 'text-bg-success'" x-text="whale.type === 'in' ? 'IN' : 'OUT'">--</span>
                                        </td>
                                        <td class="fw-semibold" x-text="whale.amount">--</td>
                                        <td x-text="whale.asset">--</td>
                                        <td class="fw-semibold" x-text="'$' + whale.usd_value">--</td>
                                        <td x-text="whale.exchange">--</td>
                                        <td>
                                            <span class="badge" :class="whale.type === 'in' ? 'text-bg-danger' : 'text-bg-success'" x-text="whale.type === 'in' ? 'Bearish' : 'Bullish'">--</span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-2 p-2 rounded" style="background: rgba(245, 158, 11, 0.1);">
                        <div class="small text-secondary">
                            <strong>Whale Behavior:</strong> Transfer IN exchange → Potensi sell pressure (Bearish). Transfer OUT → Holding/accumulation (Bullish). Monitor untuk confirm trend.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Mentions Trend -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-3">
                    <div class="mb-3">
                        <h5 class="mb-1">Social Mentions Trend (Twitter, Reddit, Google)</h5>
                        <small class="text-secondary">Tracking volume penyebutan untuk detect FOMO atau kapitulasi</small>
                    </div>
                    <div style="height: 300px;">
                        <canvas id="mentionsChart"></canvas>
                    </div>
                    <div class="mt-3 p-2 rounded" style="background: rgba(139, 92, 246, 0.1);">
                        <div class="small text-secondary">
                            <strong>Social Volume Insight:</strong> Spike mendadak di social mentions = FOMO building. Sering terjadi near local top. Volume turun drastis = Kapitulasi atau kehilangan interest.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sentiment Insights -->
        <div class="row g-3">
            <div class="col-12">
                <div class="df-panel p-4">
                    <div class="mb-3">
                        <h5 class="mb-1">Trading Insights dari Sentiment</h5>
                        <small class="text-secondary">Panduan interpretasi signal untuk entry & exit timing</small>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e;">
                                <div class="fw-bold mb-2 text-success">Contrarian Buy Signals</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Fear & Greed < 20 (Extreme Fear)</li>
                                        <li>Social mentions bottom out</li>
                                        <li>Negative funding across exchanges</li>
                                        <li>Whale net outflow positive</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444;">
                                <div class="fw-bold mb-2 text-danger">Warning Sell Signals</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Fear & Greed > 80 (Extreme Greed)</li>
                                        <li>Social mentions spike dramatically</li>
                                        <li>High positive funding (longs crowded)</li>
                                        <li>Whale net inflow negative</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6;">
                                <div class="fw-bold mb-2 text-primary">Momentum Confirmation</div>
                                <div class="small text-secondary">
                                    <ul class="mb-0 ps-3">
                                        <li>Fear & Greed 40-60 (Neutral → Greed)</li>
                                        <li>Gradual increase social volume</li>
                                        <li>Balanced funding across exchanges</li>
                                        <li>Whale activity aligned with trend</li>
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
        function sentimentFlowController() {
            return {
                selectedAsset: 'BTC',
                loading: false,
                fearGreedScore: 42,
                socialChart: null,
                mentionsChart: null,
                fundingHeatmap: null,
                whaleFlowChart: null,
                socialBreakdown: {
                    twitter: { mentions: 12847, sentiment: 42, keywords: 'halving, rally, breakout' },
                    reddit: { mentions: 3521, sentiment: 38, keywords: 'r/Bitcoin, r/CryptoCurrency' },
                    google: { score: 67, change: 5.2, region: 'Worldwide' }
                },
                fundingDominance: [
                    { exchange: 'Binance', rate: 0.0125, trend: 'up' },
                    { exchange: 'Bybit', rate: 0.0098, trend: 'stable' },
                    { exchange: 'OKX', rate: 0.0156, trend: 'up' },
                    { exchange: 'Bitget', rate: -0.0023, trend: 'down' },
                    { exchange: 'Gate.io', rate: 0.0087, trend: 'stable' },
                    { exchange: 'Deribit', rate: 0.0112, trend: 'up' }
                ],
                whaleFlow: {
                    inflow: 342.5,
                    outflow: 487.2,
                    netFlow: 144.7
                },
                whaleAlerts: [
                    { id: 1, type: 'out', amount: '1,284', asset: 'BTC', usd_value: '55.2M', exchange: 'Binance', time: '2 mins ago' },
                    { id: 2, type: 'in', amount: '3,450', asset: 'ETH', usd_value: '7.8M', exchange: 'Coinbase', time: '15 mins ago' },
                    { id: 3, type: 'out', amount: '842', asset: 'BTC', usd_value: '36.1M', exchange: 'Kraken', time: '28 mins ago' },
                    { id: 4, type: 'in', amount: '5,200', asset: 'ETH', usd_value: '11.7M', exchange: 'Binance', time: '45 mins ago' },
                    { id: 5, type: 'out', amount: '2,150', asset: 'BTC', usd_value: '92.3M', exchange: 'Gemini', time: '1 hour ago' }
                ],

                init() {
                    // Wait for Chart.js to be ready
                    if (typeof Chart !== 'undefined') {
                        this.initCharts();
                        this.startWhaleSimulation();
                    } else {
                        setTimeout(() => {
                            this.initCharts();
                            this.startWhaleSimulation();
                        }, 100);
                    }
                },

                initCharts() {
                    // Social Sentiment Chart
                    const socialCtx = document.getElementById('socialChart');
                    if (socialCtx) {
                        this.socialChart = new Chart(socialCtx, {
                            type: 'bar',
                            data: {
                                labels: this.generateDateLabels(30),
                                datasets: [
                                    {
                                        label: 'Twitter',
                                        data: this.generateSocialData(30, 5000, 2000),
                                        backgroundColor: 'rgba(59, 130, 246, 0.7)'
                                    },
                                    {
                                        label: 'Reddit',
                                        data: this.generateSocialData(30, 3000, 1500),
                                        backgroundColor: 'rgba(239, 68, 68, 0.7)'
                                    },
                                    {
                                        label: 'Google Trends',
                                        data: this.generateSocialData(30, 2000, 1000),
                                        backgroundColor: 'rgba(34, 197, 94, 0.7)'
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
                                    x: { stacked: true },
                                    y: {
                                        stacked: true,
                                        position: 'right',
                                        title: { display: true, text: 'Mentions' }
                                    }
                                }
                            }
                        });
                    }

                    // Funding Heatmap
                    const fundingCtx = document.getElementById('fundingHeatmap');
                    if (fundingCtx) {
                        this.fundingHeatmap = new Chart(fundingCtx, {
                            type: 'bar',
                            data: {
                                labels: this.fundingDominance.map(f => f.exchange),
                                datasets: [{
                                    label: 'Funding Rate (%)',
                                    data: this.fundingDominance.map(f => (f.rate * 100).toFixed(4)),
                                    backgroundColor: this.fundingDominance.map(f =>
                                        f.rate > 0.015 ? 'rgba(239, 68, 68, 0.8)' :
                                        f.rate > 0.01 ? 'rgba(245, 158, 11, 0.8)' :
                                        f.rate < 0 ? 'rgba(34, 197, 94, 0.8)' :
                                        'rgba(156, 163, 175, 0.6)'
                                    ),
                                    borderColor: this.fundingDominance.map(f =>
                                        f.rate > 0.015 ? 'rgb(239, 68, 68)' :
                                        f.rate > 0.01 ? 'rgb(245, 158, 11)' :
                                        f.rate < 0 ? 'rgb(34, 197, 94)' :
                                        'rgb(156, 163, 175)'
                                    ),
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false }
                                },
                                scales: {
                                    y: {
                                        position: 'right',
                                        title: { display: true, text: 'Rate (%)' }
                                    }
                                }
                            }
                        });
                    }

                    // Whale Flow Chart
                    const whaleFlowCtx = document.getElementById('whaleFlowChart');
                    if (whaleFlowCtx) {
                        this.whaleFlowChart = new Chart(whaleFlowCtx, {
                            type: 'line',
                            data: {
                                labels: this.generateDateLabels(7),
                                datasets: [
                                    {
                                        label: 'Inflow',
                                        data: this.generateWhaleFlowData(7, 300, 500),
                                        borderColor: 'rgb(239, 68, 68)',
                                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                        tension: 0.4,
                                        fill: true
                                    },
                                    {
                                        label: 'Outflow',
                                        data: this.generateWhaleFlowData(7, 400, 600),
                                        borderColor: 'rgb(34, 197, 94)',
                                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                        tension: 0.4,
                                        fill: true
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
                                        title: { display: true, text: 'USD ($M)' }
                                    }
                                }
                            }
                        });
                    }

                    // Mentions Trend Chart
                    const mentionsCtx = document.getElementById('mentionsChart');
                    if (mentionsCtx) {
                        this.mentionsChart = new Chart(mentionsCtx, {
                            type: 'line',
                            data: {
                                labels: this.generateDateLabels(90),
                                datasets: [
                                    {
                                        label: 'Total Social Volume',
                                        data: this.generateMentionsTrend(90),
                                        borderColor: 'rgb(139, 92, 246)',
                                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                                        tension: 0.4,
                                        fill: true
                                    },
                                    {
                                        label: 'Fear & Greed Index',
                                        data: this.generateFearGreedTrend(90),
                                        borderColor: 'rgb(245, 158, 11)',
                                        tension: 0.4,
                                        yAxisID: 'y1'
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: { mode: 'index', intersect: false },
                                plugins: {
                                    legend: { display: true, position: 'top' }
                                },
                                scales: {
                                    y: {
                                        type: 'linear',
                                        display: true,
                                        position: 'left',
                                        title: { display: true, text: 'Social Volume' }
                                    },
                                    y1: {
                                        type: 'linear',
                                        display: true,
                                        position: 'right',
                                        title: { display: true, text: 'F&G Index' },
                                        min: 0,
                                        max: 100,
                                        grid: { drawOnChartArea: false }
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

                generateSocialData(days, base, volatility) {
                    const data = [];
                    for (let i = 0; i < days; i++) {
                        data.push(Math.floor(base + (Math.random() - 0.5) * volatility));
                    }
                    return data;
                },

                generateWhaleFlowData(days, min, max) {
                    const data = [];
                    for (let i = 0; i < days; i++) {
                        data.push(Math.floor(Math.random() * (max - min) + min));
                    }
                    return data;
                },

                generateMentionsTrend(days) {
                    const data = [];
                    let value = 8000;
                    for (let i = 0; i < days; i++) {
                        value += (Math.random() - 0.48) * 1000;
                        data.push(Math.floor(Math.max(3000, value)));
                    }
                    return data;
                },

                generateFearGreedTrend(days) {
                    const data = [];
                    let value = 50;
                    for (let i = 0; i < days; i++) {
                        value += (Math.random() - 0.5) * 15;
                        data.push(Math.floor(Math.max(10, Math.min(90, value))));
                    }
                    return data;
                },

                startWhaleSimulation() {
                    setInterval(() => {
                        const types = ['in', 'out'];
                        const assets = ['BTC', 'ETH'];
                        const exchanges = ['Binance', 'Coinbase', 'Kraken', 'Gemini', 'Bybit'];

                        const newWhale = {
                            id: Date.now(),
                            type: types[Math.floor(Math.random() * types.length)],
                            amount: (Math.random() * 3000 + 500).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ','),
                            asset: assets[Math.floor(Math.random() * assets.length)],
                            usd_value: (Math.random() * 100 + 10).toFixed(1) + 'M',
                            exchange: exchanges[Math.floor(Math.random() * exchanges.length)],
                            time: 'Just now'
                        };

                        this.whaleAlerts.unshift(newWhale);
                        if (this.whaleAlerts.length > 10) {
                            this.whaleAlerts.pop();
                        }

                        // Update whale flow
                        if (newWhale.type === 'in') {
                            this.whaleFlow.inflow += 5;
                        } else {
                            this.whaleFlow.outflow += 5;
                        }
                        this.whaleFlow.netFlow = this.whaleFlow.outflow - this.whaleFlow.inflow;
                    }, 15000); // New whale every 15 seconds
                },

                getFearGreedBadge() {
                    if (this.fearGreedScore <= 25) return 'text-bg-danger';
                    if (this.fearGreedScore <= 45) return 'text-bg-warning';
                    if (this.fearGreedScore <= 55) return 'text-bg-secondary';
                    if (this.fearGreedScore <= 75) return 'text-bg-info';
                    return 'text-bg-success';
                },

                getFearGreedLabel() {
                    if (this.fearGreedScore <= 25) return 'Extreme Fear';
                    if (this.fearGreedScore <= 45) return 'Fear';
                    if (this.fearGreedScore <= 55) return 'Neutral';
                    if (this.fearGreedScore <= 75) return 'Greed';
                    return 'Extreme Greed';
                },

                getFearGreedAlert() {
                    if (this.fearGreedScore <= 25) return 'bg-success bg-opacity-10';
                    if (this.fearGreedScore >= 75) return 'bg-danger bg-opacity-10';
                    return 'bg-info bg-opacity-10';
                },

                getFearGreedTitle() {
                    if (this.fearGreedScore <= 25) return 'Contrarian Buy Opportunity';
                    if (this.fearGreedScore >= 75) return 'Take Profit Zone';
                    return 'Neutral Zone';
                },

                getFearGreedMessage() {
                    if (this.fearGreedScore <= 25) {
                        return `Extreme fear terdeteksi (${this.fearGreedScore}/100). Secara historis titik entry yang baik untuk contrarian traders. Market oversold.`;
                    }
                    if (this.fearGreedScore >= 75) {
                        return `Extreme greed terdeteksi (${this.fearGreedScore}/100). Pertimbangkan take profit. Market berpotensi overheated.`;
                    }
                    return `Sentimen neutral (${this.fearGreedScore}/100). Market menunjukkan perilaku seimbang. Ikuti tren dan gunakan manajemen risiko yang tepat.`;
                },

                formatChange(value) {
                    return (value >= 0 ? '+' : '') + value.toFixed(1);
                },

                formatFundingRate(rate) {
                    const percent = (rate * 100).toFixed(4);
                    return (rate >= 0 ? '+' : '') + percent + '%';
                },

                getFundingActionBadge(rate) {
                    if (rate > 0.015) return 'text-bg-danger';
                    if (rate > 0.01) return 'text-bg-warning';
                    if (rate < 0) return 'text-bg-success';
                    return 'text-bg-secondary';
                },

                getFundingAction(rate) {
                    if (rate > 0.015) return 'Long Squeeze Risk';
                    if (rate > 0.01) return 'Monitor';
                    if (rate < 0) return 'Short Squeeze Setup';
                    return 'Neutral';
                },

                getWhaleAlertRowClass(type) {
                    return type === 'in' ? 'table-danger' : 'table-success';
                },

                refreshAll() {
                    this.loading = true;
                    setTimeout(() => {
                        this.fearGreedScore = Math.floor(Math.random() * 60 + 20);
                        this.loading = false;
                    }, 1000);
                }
            };
        }
    </script>

    <style>
        .pulse-info {
            background-color: #3b82f6;
            box-shadow: 0 0 0 rgba(59, 130, 246, 0.7);
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
            }
            50% {
                box-shadow: 0 0 0 8px rgba(59, 130, 246, 0);
            }
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
    </style>
@endsection
