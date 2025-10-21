import "./bootstrap";
import "bootstrap/dist/js/bootstrap.bundle.min.js";

import jQuery from "jquery";
window.$ = window.jQuery = jQuery;

import Alpine from "alpinejs";
import { 
    Chart, 
    CategoryScale,
    LinearScale,
    LogarithmicScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    Filler,
    registerables 
} from "chart.js";
import { MatrixController, MatrixElement } from "chartjs-chart-matrix";

// Register Chart.js components
Chart.register(
    ...registerables, 
    MatrixController, 
    MatrixElement,
    CategoryScale,
    LinearScale,
    LogarithmicScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

// Make Chart.js available globally
window.Chart = Chart;
window.Alpine = Alpine;

// Alpine.js data and components
// Note: Alpine.js is already loaded by Livewire
document.addEventListener("alpine:init", () => {
    const themePalette = {
        bullish: "#22c55e",
        bearish: "#ef4444",
        neutral: "#3b82f6",
        background: "#f9fafb",
        slate: "#1f2937",
    };

    const assetProfiles = {
        BTC: {
            label: "Bitcoin",
            totalSupply: 19.6,
            mvrv: { base: 2.15, amplitude: 0.55, noise: 0.25, min: 0.7 },
            zScore: { base: 1.05, amplitude: 1.4, noise: 0.55, min: -1.8 },
            reserveRisk: {
                base: 0.42,
                amplitude: 0.22,
                noise: 0.08,
                min: 0.04,
            },
            sopr: { base: 1.02, amplitude: 0.14, noise: 0.05, min: 0.82 },
            dormancy: { base: 86, amplitude: 24, noise: 12, min: 35 },
            cdd: { base: 145, amplitude: 48, noise: 24, min: 60 },
            lthBase: 0.68,
            realizedCap: { base: 465, amplitude: 34, noise: 12 },
            hodlBands: [14, 16, 17, 18, 20, 15],
            minerReserve: { base: 1.82, amplitude: 0.17, noise: 0.08 },
            puell: { base: 1.55, amplitude: 0.75, noise: 0.3, min: 0.4 },
            whaleHoldings: {
                "1k-10k": 3.4,
                "10k+": 2.1,
                Exchanges: 2.8,
            },
            flowIntensity: { inflow: 48, outflow: 52, volatility: 7 },
        },
        ETH: {
            label: "Ethereum",
            totalSupply: 120,
            mvrv: { base: 1.72, amplitude: 0.45, noise: 0.22, min: 0.6 },
            zScore: { base: 0.68, amplitude: 1.05, noise: 0.45, min: -2.1 },
            reserveRisk: {
                base: 0.36,
                amplitude: 0.18,
                noise: 0.07,
                min: 0.05,
            },
            sopr: { base: 1.01, amplitude: 0.11, noise: 0.05, min: 0.82 },
            dormancy: { base: 72, amplitude: 22, noise: 10, min: 28 },
            cdd: { base: 112, amplitude: 42, noise: 20, min: 45 },
            lthBase: 0.58,
            realizedCap: { base: 220, amplitude: 24, noise: 9 },
            hodlBands: [18, 21, 19, 17, 15, 10],
            minerReserve: { base: 2.95, amplitude: 0.22, noise: 0.09 },
            puell: { base: 1.32, amplitude: 0.58, noise: 0.26, min: 0.35 },
            whaleHoldings: {
                "1k-10k": 6.2,
                "10k+": 4.1,
                Exchanges: 10.4,
            },
            flowIntensity: { inflow: 54, outflow: 46, volatility: 9 },
        },
        SOL: {
            label: "Solana",
            totalSupply: 440,
            mvrv: { base: 1.95, amplitude: 0.72, noise: 0.32, min: 0.5 },
            zScore: { base: 0.85, amplitude: 1.65, noise: 0.55, min: -2.6 },
            reserveRisk: { base: 0.51, amplitude: 0.28, noise: 0.1, min: 0.08 },
            sopr: { base: 1.05, amplitude: 0.18, noise: 0.07, min: 0.75 },
            dormancy: { base: 58, amplitude: 28, noise: 12, min: 18 },
            cdd: { base: 96, amplitude: 54, noise: 25, min: 30 },
            lthBase: 0.44,
            realizedCap: { base: 92, amplitude: 18, noise: 7 },
            hodlBands: [26, 22, 18, 14, 12, 8],
            minerReserve: { base: 0.42, amplitude: 0.08, noise: 0.04 },
            puell: { base: 1.95, amplitude: 0.85, noise: 0.33, min: 0.5 },
            whaleHoldings: {
                "1k-10k": 11.6,
                "10k+": 5.4,
                Exchanges: 48,
            },
            flowIntensity: { inflow: 58, outflow: 42, volatility: 15 },
        },
        STABLECOINS: {
            label: "Stablecoins",
            totalSupply: 1400,
            mvrv: { base: 1, amplitude: 0.06, noise: 0.02, min: 0.92 },
            zScore: { base: 0.2, amplitude: 0.35, noise: 0.12, min: -0.6 },
            reserveRisk: {
                base: 0.26,
                amplitude: 0.12,
                noise: 0.05,
                min: 0.02,
            },
            sopr: { base: 1, amplitude: 0.04, noise: 0.01, min: 0.94 },
            dormancy: { base: 44, amplitude: 11, noise: 5, min: 20 },
            cdd: { base: 62, amplitude: 18, noise: 8, min: 25 },
            lthBase: 0.38,
            realizedCap: { base: 140, amplitude: 12, noise: 5 },
            hodlBands: [34, 22, 18, 12, 8, 6],
            minerReserve: { base: 0.18, amplitude: 0.03, noise: 0.01 },
            puell: { base: 0.85, amplitude: 0.22, noise: 0.08, min: 0.5 },
            whaleHoldings: {
                "1k-10k": 40,
                "10k+": 220,
                Exchanges: 320,
            },
            flowIntensity: { inflow: 61, outflow: 39, volatility: 12 },
        },
    };

    const cohortBands = ["< 1M", "1-3M", "3-6M", "6-12M", "1-2Y", "2Y+"];
    const hodlPalette = [
        "#0f172a",
        "#1e3a8a",
        "#2563eb",
        "#3b82f6",
        "#60a5fa",
        "#93c5fd",
    ];
    const whalePalette = ["#2563eb", "#9333ea", "#f97316"];
    const exchangeVenues = [
        "Binance",
        "Coinbase",
        "Kraken",
        "OKX",
        "Bybit",
        "Bitfinex",
    ];

    const rangeToDays = {
        "7D": 7,
        "30D": 30,
        "90D": 90,
        "180D": 180,
    };

    const clamp = (value, min = -Infinity, max = Infinity) =>
        Math.min(Math.max(value, min), max);

    const generateDateRange = (range) => {
        const days = rangeToDays[range] ?? 30;
        const today = new Date();
        const dates = [];
        for (let offset = days - 1; offset >= 0; offset -= 1) {
            const point = new Date(today);
            point.setDate(today.getDate() - offset);
            dates.push(point.toISOString().split("T")[0]);
        }
        return dates;
    };

    const generateSeries = (
        length,
        base,
        amplitude,
        noise = amplitude / 3,
        minimum = null
    ) => {
        return Array.from({ length }, (_, idx) => {
            const wave = Math.sin(
                (idx / Math.max(1, length - 1)) * Math.PI * 2
            );
            const variance = (Math.random() - 0.5) * noise * 2;
            const next = base + wave * amplitude + variance;
            return Number(
                clamp(next, minimum ?? -Infinity, Infinity).toFixed(2)
            );
        });
    };

    const createDistributionSeries = (length, baseDistribution) => {
        return Array.from({ length }, () => {
            const adjusted = baseDistribution.map((value) => {
                const variation = (Math.random() - 0.5) * 2.4;
                return Math.max(0.5, value + variation);
            });
            const sum = adjusted.reduce((acc, value) => acc + value, 0);
            return adjusted.map((value) =>
                Number(((value / sum) * 100).toFixed(2))
            );
        });
    };

    const buildHeatmapDataset = (dates, assetProfile) => {
        const data = [];
        dates.forEach((date, xIndex) => {
            exchangeVenues.forEach((venue, yIndex) => {
                const base =
                    (assetProfile.flowIntensity.volatility / 2) *
                    Math.sin((xIndex + yIndex) / 2);
                const directionalBias =
                    yIndex % 2 === 0
                        ? assetProfile.flowIntensity.outflow
                        : assetProfile.flowIntensity.inflow;
                const net =
                    (Math.random() - 0.5) *
                        assetProfile.flowIntensity.volatility +
                    (directionalBias - 50) * 1.1 +
                    base;
                data.push({
                    x: date,
                    y: venue,
                    v: Number(net.toFixed(2)),
                });
            });
        });
        return data;
    };

    const formatCompact = (value, decimals = 1) => {
        if (Math.abs(value) >= 1_000_000_000) {
            return `${(value / 1_000_000_000).toFixed(decimals)}B`;
        }
        if (Math.abs(value) >= 1_000_000) {
            return `${(value / 1_000_000).toFixed(decimals)}M`;
        }
        if (Math.abs(value) >= 1_000) {
            return `${(value / 1_000).toFixed(decimals)}K`;
        }
        return value.toFixed(decimals);
    };

    const hexToRgba = (hex, alpha = 1) => {
        let sanitized = hex.replace("#", "");
        if (sanitized.length === 3) {
            sanitized = sanitized
                .split("")
                .map((char) => char + char)
                .join("");
        }
        const bigint = parseInt(sanitized, 16);
        const r = (bigint >> 16) & 255;
        const g = (bigint >> 8) & 255;
        const b = bigint & 255;
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    };

    const createGradientFill = (ctx, color, alpha = 0.18) => {
        const gradient = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height);
        gradient.addColorStop(0, hexToRgba(color, alpha));
        gradient.addColorStop(1, hexToRgba(color, 0));
        return gradient;
    };

    Alpine.store("onchainMetrics", {
        assets: ["BTC", "ETH", "SOL", "STABLECOINS"],
        ranges: ["7D", "30D", "90D", "180D"],
        theme: themePalette,
        selectedAsset: "BTC",
        selectedRange: "30D",
        loading: false,
        refreshTick: 0,

        setAsset(asset) {
            if (this.selectedAsset === asset || !assetProfiles[asset]) {
                return;
            }
            this.selectedAsset = asset;
            this.triggerRefresh();
        },

        setRange(range) {
            if (this.selectedRange === range || !rangeToDays[range]) {
                return;
            }
            this.selectedRange = range;
            this.triggerRefresh();
        },

        triggerRefresh() {
            this.refreshTick += 1;
        },

        refresh() {
            if (this.loading) {
                return;
            }
            this.loading = true;
            setTimeout(() => {
                this.loading = false;
                this.triggerRefresh();
            }, 420);
        },

        assetProfile() {
            return assetProfiles[this.selectedAsset];
        },

        assetLabel() {
            return assetProfiles[this.selectedAsset].label;
        },

        generateValuationData(
            asset = this.selectedAsset,
            range = this.selectedRange
        ) {
            const profile = assetProfiles[asset] ?? assetProfiles.BTC;
            const dates = generateDateRange(range);
            const length = dates.length;
            const mvrv = generateSeries(
                length,
                profile.mvrv.base,
                profile.mvrv.amplitude,
                profile.mvrv.noise,
                profile.mvrv.min
            );
            const zScore = generateSeries(
                length,
                profile.zScore.base,
                profile.zScore.amplitude,
                profile.zScore.noise,
                profile.zScore.min
            );
            const reserveRisk = generateSeries(
                length,
                profile.reserveRisk.base,
                profile.reserveRisk.amplitude,
                profile.reserveRisk.noise,
                profile.reserveRisk.min
            );
            const sopr = generateSeries(
                length,
                profile.sopr.base,
                profile.sopr.amplitude,
                profile.sopr.noise,
                profile.sopr.min
            );
            const dormancy = generateSeries(
                length,
                profile.dormancy.base,
                profile.dormancy.amplitude,
                profile.dormancy.noise,
                profile.dormancy.min
            );
            const cdd = generateSeries(
                length,
                profile.cdd.base,
                profile.cdd.amplitude,
                profile.cdd.noise,
                profile.cdd.min
            );

            return {
                dates,
                mvrv,
                zScore,
                reserveRisk,
                sopr,
                dormancy,
                cdd,
            };
        },

        generateSupplyData(
            asset = this.selectedAsset,
            range = this.selectedRange
        ) {
            const profile = assetProfiles[asset] ?? assetProfiles.BTC;
            const dates = generateDateRange(range);
            const length = dates.length;

            const lthPercent = generateSeries(
                length,
                profile.lthBase * 100,
                6,
                3,
                20
            ).map((val) => clamp(val, 20, 92));
            const sthPercent = lthPercent.map((val) =>
                Number((100 - val).toFixed(2))
            );

            const totalSupplyUnits =
                profile.totalSupply *
                (asset === "STABLECOINS" ? 1_000_000_000 : 1_000_000);
            const lthSupply = lthPercent.map((val) =>
                Number(((val / 100) * totalSupplyUnits).toFixed(0))
            );
            const sthSupply = sthPercent.map((val) =>
                Number(((val / 100) * totalSupplyUnits).toFixed(0))
            );

            const realizedCap = generateSeries(
                length,
                profile.realizedCap.base,
                profile.realizedCap.amplitude,
                profile.realizedCap.noise,
                profile.realizedCap.base * 0.6
            ).map((val) => Number(val.toFixed(2)));

            const hodlDistributions = createDistributionSeries(
                length,
                profile.hodlBands
            );

            return {
                dates,
                lthPercent,
                sthPercent,
                lthSupply,
                sthSupply,
                realizedCap,
                hodlDistributions,
            };
        },

        generateFlowsData(
            asset = this.selectedAsset,
            range = this.selectedRange
        ) {
            const profile = assetProfiles[asset] ?? assetProfiles.BTC;
            const dates = generateDateRange(range);
            const length = dates.length;
            const baseNetflow = profile.flowIntensity;

            const inflow = generateSeries(
                length,
                baseNetflow.inflow,
                baseNetflow.volatility,
                baseNetflow.volatility / 2
            );
            const outflow = generateSeries(
                length,
                baseNetflow.outflow,
                baseNetflow.volatility,
                baseNetflow.volatility / 2
            );
            const netflow = inflow.map((value, idx) =>
                Number((outflow[idx] - value).toFixed(2))
            );

            const stablecoinInflow = generateSeries(
                length,
                baseNetflow.inflow * 1.28,
                baseNetflow.volatility * 1.1
            );
            const stablecoinOutflow = generateSeries(
                length,
                baseNetflow.outflow * 0.92,
                baseNetflow.volatility * 0.9
            );
            const stablecoinNetflow = stablecoinInflow.map((value, idx) =>
                Number((value - stablecoinOutflow[idx]).toFixed(2))
            );

            const heatmapData = buildHeatmapDataset(dates, profile);

            const exchangeBreakdown = exchangeVenues.map((venue, idx) => {
                const bias =
                    idx % 2 === 0
                        ? -(profile.flowIntensity.volatility * 0.6)
                        : profile.flowIntensity.volatility * 0.6;
                const venueNet =
                    bias +
                    (Math.random() - 0.5) *
                        profile.flowIntensity.volatility *
                        1.4;
                return {
                    venue,
                    netflow: Number(venueNet.toFixed(2)),
                    balance: Number(
                        (Math.random() * 150 + 50 + idx * 35).toFixed(2)
                    ),
                };
            });

            return {
                dates,
                inflow,
                outflow,
                netflow,
                stablecoinInflow,
                stablecoinOutflow,
                stablecoinNetflow,
                heatmapData,
                exchangeBreakdown,
            };
        },

        generateMinerData(
            asset = this.selectedAsset,
            range = this.selectedRange
        ) {
            const profile = assetProfiles[asset] ?? assetProfiles.BTC;
            const dates = generateDateRange(range);
            const length = dates.length;

            const minerReserve = generateSeries(
                length,
                profile.minerReserve.base,
                profile.minerReserve.amplitude,
                profile.minerReserve.noise,
                profile.minerReserve.base * 0.4
            );
            const puellMultiple = generateSeries(
                length,
                profile.puell.base,
                profile.puell.amplitude,
                profile.puell.noise,
                profile.puell.min
            );

            const whaleCohorts = {};
            Object.entries(profile.whaleHoldings).forEach(([cohort, base]) => {
                whaleCohorts[cohort] = generateSeries(
                    length,
                    base,
                    base * 0.18,
                    base * 0.08,
                    base * 0.3
                );
            });

            return {
                dates,
                minerReserve,
                puellMultiple,
                whaleCohorts,
            };
        },

        cohortBands() {
            return cohortBands;
        },

        exchangeVenues() {
            return exchangeVenues;
        },

        formatNumber(value, decimals = 2) {
            return Number(value).toLocaleString("en-US", {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            });
        },

        formatPercent(value, decimals = 2) {
            return `${value.toFixed(decimals)}%`;
        },

        formatCompact,
    });
    Alpine.data("valuationModule", () => ({
        store: Alpine.store("onchainMetrics"),
        charts: {
            mvrv: null,
            reserve: null,
            cdd: null,
        },
        insights: {
            mvrv: "",
            reserve: "",
            cdd: "",
        },
        metrics: {
            mvrv: "--",
            mvrvDelta: "",
            zScore: "--",
            zScoreDelta: "",
            sopr: "--",
            soprNarrative: "",
        },
        init() {
            queueMicrotask(() => {
                this.renderCharts();
            });
            this.$watch(
                () => [this.store.selectedAsset, this.store.selectedRange],
                () => this.updateCharts()
            );
            this.$watch(
                () => this.store.refreshTick,
                () => this.updateCharts()
            );
        },
        renderCharts() {
            const data = this.store.generateValuationData();
            this.buildMvrvChart(data);
            this.buildReserveChart(data);
            this.buildCddChart(data);
            this.updateInsights(data);
        },
        updateCharts() {
            if (!this.charts.mvrv || !this.charts.reserve || !this.charts.cdd) {
                this.renderCharts();
                return;
            }
            const data = this.store.generateValuationData();
            this.applyMvrvData(data);
            this.applyReserveData(data);
            this.applyCddData(data);
            this.updateInsights(data);
        },
        buildMvrvChart(data) {
            const ctx = this.$refs.mvrvChart.getContext("2d");
            const zonePlugin = {
                id: "mvrvZones",
                beforeDraw: (chart) => {
                    const { ctx, chartArea, scales } = chart;
                    const axis = scales.mvrv;
                    if (!axis) {
                        return;
                    }
                    const sections = [
                        {
                            limit: Math.min(1, axis.max),
                            color: hexToRgba(themePalette.bullish, 0.12),
                        },
                        {
                            limit: Math.min(3, axis.max),
                            color: hexToRgba(themePalette.neutral, 0.08),
                        },
                        {
                            limit: axis.max,
                            color: hexToRgba(themePalette.bearish, 0.08),
                        },
                    ];
                    let start = axis.getPixelForValue(axis.min);
                    sections.forEach((section) => {
                        const top = axis.getPixelForValue(section.limit);
                        ctx.save();
                        ctx.fillStyle = section.color;
                        ctx.fillRect(
                            chartArea.left,
                            Math.min(start, top),
                            chartArea.right - chartArea.left,
                            Math.abs(start - top)
                        );
                        ctx.restore();
                        start = top;
                    });
                },
            };
            this.charts.mvrv = new Chart(ctx, {
                type: "line",
                data: {
                    labels: data.dates,
                    datasets: [
                        {
                            label: "MVRV Ratio",
                            data: data.mvrv,
                            borderColor: themePalette.bullish,
                            backgroundColor: createGradientFill(
                                ctx,
                                themePalette.bullish,
                                0.22
                            ),
                            borderWidth: 2,
                            tension: 0.35,
                            fill: true,
                            pointRadius: 0,
                            yAxisID: "mvrv",
                        },
                        {
                            label: "Z-Score",
                            data: data.zScore,
                            borderColor: "#f97316",
                            borderWidth: 2,
                            borderDash: [5, 4],
                            tension: 0.35,
                            fill: false,
                            pointRadius: 0,
                            yAxisID: "zscore",
                        },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    interaction: { mode: "index", intersect: false },
                    animation: { duration: 520, easing: "easeOutQuart" },
                    plugins: {
                        legend: {
                            display: true,
                            align: "start",
                            labels: { usePointStyle: true, boxWidth: 10 },
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const label = context.dataset.label ?? "";
                                    return `${label}: ${context.parsed.y.toFixed(
                                        2
                                    )}`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                        },
                        mvrv: {
                            position: "left",
                            title: { display: true, text: "MVRV" },
                            grid: {
                                color: hexToRgba(themePalette.slate, 0.08),
                            },
                        },
                        zscore: {
                            position: "right",
                            title: { display: true, text: "Z-Score" },
                            grid: { drawOnChartArea: false },
                        },
                    },
                },
                plugins: [zonePlugin],
            });
            this.applyMvrvData(data);
        },
        applyMvrvData(data) {
            const chart = this.charts.mvrv;
            if (!chart) {
                return;
            }
            chart.data.labels = data.dates;
            chart.data.datasets[0].data = data.mvrv;
            chart.data.datasets[1].data = data.zScore;
            const mvrvMin = Math.min(...data.mvrv);
            const mvrvMax = Math.max(...data.mvrv);
            chart.options.scales.mvrv.suggestedMin = Math.min(0, mvrvMin - 0.4);
            chart.options.scales.mvrv.suggestedMax = mvrvMax + 0.4;
            const zMin = Math.min(...data.zScore);
            const zMax = Math.max(...data.zScore);
            chart.options.scales.zscore.suggestedMin = zMin - 0.6;
            chart.options.scales.zscore.suggestedMax = zMax + 0.6;
            chart.update();
        },
        buildReserveChart(data) {
            const ctx = this.$refs.reserveChart.getContext("2d");
            this.charts.reserve = new Chart(ctx, {
                type: "line",
                data: {
                    labels: data.dates,
                    datasets: [
                        {
                            label: "Reserve Risk",
                            data: data.reserveRisk,
                            borderColor: "#0ea5e9",
                            backgroundColor: createGradientFill(
                                ctx,
                                "#0ea5e9",
                                0.18
                            ),
                            borderWidth: 2,
                            tension: 0.35,
                            fill: true,
                            pointRadius: 0,
                            yAxisID: "confidence",
                        },
                        {
                            label: "SOPR",
                            data: data.sopr,
                            borderColor: themePalette.bearish,
                            borderWidth: 2,
                            borderDash: [4, 3],
                            tension: 0.3,
                            fill: false,
                            pointRadius: 0,
                            yAxisID: "sopr",
                        },
                        {
                            label: "Dormancy",
                            data: data.dormancy,
                            borderColor: "#14b8a6",
                            borderWidth: 2,
                            tension: 0.3,
                            fill: false,
                            pointRadius: 0,
                            yAxisID: "confidence",
                        },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    interaction: { mode: "index", intersect: false },
                    animation: { duration: 520, easing: "easeOutQuart" },
                    plugins: {
                        legend: {
                            display: true,
                            align: "start",
                            labels: { usePointStyle: true, boxWidth: 10 },
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const label = context.dataset.label ?? "";
                                    return `${label}: ${context.parsed.y.toFixed(
                                        2
                                    )}`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                        },
                        confidence: {
                            position: "left",
                            title: {
                                display: true,
                                text: "Confidence Indicators",
                            },
                            grid: {
                                color: hexToRgba(themePalette.slate, 0.08),
                            },
                        },
                        sopr: {
                            position: "right",
                            title: { display: true, text: "SOPR" },
                            grid: { drawOnChartArea: false },
                        },
                    },
                },
            });
            this.applyReserveData(data);
        },
        applyReserveData(data) {
            const chart = this.charts.reserve;
            if (!chart) {
                return;
            }
            chart.data.labels = data.dates;
            chart.data.datasets[0].data = data.reserveRisk;
            chart.data.datasets[1].data = data.sopr;
            chart.data.datasets[2].data = data.dormancy;
            const reserveMin = Math.min(...data.reserveRisk, ...data.dormancy);
            const reserveMax = Math.max(...data.reserveRisk, ...data.dormancy);
            chart.options.scales.confidence.suggestedMin = Math.max(
                0,
                reserveMin - 0.2
            );
            chart.options.scales.confidence.suggestedMax = reserveMax + 0.2;
            const soprMin = Math.min(...data.sopr);
            const soprMax = Math.max(...data.sopr);
            chart.options.scales.sopr.suggestedMin = soprMin - 0.1;
            chart.options.scales.sopr.suggestedMax = soprMax + 0.1;
            chart.update();
        },
        buildCddChart(data) {
            const ctx = this.$refs.cddChart.getContext("2d");
            this.charts.cdd = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: data.dates,
                    datasets: [
                        {
                            label: "CDD (Coin Days Destroyed)",
                            data: data.cdd,
                            backgroundColor: hexToRgba("#f59e0b", 0.6),
                            borderColor: "#f59e0b",
                            borderWidth: 1,
                            borderRadius: 4,
                            yAxisID: "cdd",
                        },
                        {
                            label: "Dormancy",
                            data: data.dormancy,
                            borderColor: "#14b8a6",
                            backgroundColor: createGradientFill(
                                ctx,
                                "#14b8a6",
                                0.18
                            ),
                            borderWidth: 2,
                            tension: 0.32,
                            fill: true,
                            pointRadius: 0,
                            type: "line",
                            yAxisID: "dormancy",
                        },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    interaction: { mode: "index", intersect: false },
                    animation: { duration: 520, easing: "easeOutQuart" },
                    plugins: {
                        legend: {
                            display: true,
                            align: "start",
                            labels: { usePointStyle: true, boxWidth: 10 },
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const label = context.dataset.label ?? "";
                                    return `${label}: ${context.parsed.y.toFixed(
                                        2
                                    )}`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                        },
                        cdd: {
                            position: "left",
                            title: {
                                display: true,
                                text: "Coin Days Destroyed",
                            },
                            grid: {
                                color: hexToRgba(themePalette.slate, 0.08),
                            },
                        },
                        dormancy: {
                            position: "right",
                            title: { display: true, text: "Dormancy" },
                            grid: { drawOnChartArea: false },
                        },
                    },
                },
            });
            this.applyCddData(data);
        },
        applyCddData(data) {
            const chart = this.charts.cdd;
            if (!chart) {
                return;
            }
            chart.data.labels = data.dates;
            chart.data.datasets[0].data = data.cdd;
            chart.data.datasets[1].data = data.dormancy;
            const cddMin = Math.min(...data.cdd);
            const cddMax = Math.max(...data.cdd);
            chart.options.scales.cdd.suggestedMin = Math.max(
                0,
                cddMin - cddMin * 0.2
            );
            chart.options.scales.cdd.suggestedMax = cddMax + cddMax * 0.2;
            const dormancyMin = Math.min(...data.dormancy);
            const dormancyMax = Math.max(...data.dormancy);
            chart.options.scales.dormancy.suggestedMin = Math.max(
                0,
                dormancyMin - dormancyMin * 0.2
            );
            chart.options.scales.dormancy.suggestedMax =
                dormancyMax + dormancyMax * 0.2;
            chart.update();
        },
        updateInsights(data) {
            const asset = this.store.assetLabel();
            const mvrvLatest = data.mvrv.at(-1) ?? 0;
            const mvrvPrev = data.mvrv.at(-2) ?? mvrvLatest;
            const zLatest = data.zScore.at(-1) ?? 0;
            const reserveLatest = data.reserveRisk.at(-1) ?? 0;
            const soprLatest = data.sopr.at(-1) ?? 1;
            const cddLatest = data.cdd.at(-1) ?? 0;
            const cddPrev = data.cdd.at(-2) ?? cddLatest;
            const dormancyLatest = data.dormancy.at(-1) ?? 0;

            let zTone;
            if (zLatest > 1.4) {
                zTone = `Z-Score ${asset} melonjak ke ${zLatest.toFixed(
                    2
                )} sehingga risiko pasar overheating meningkat.`;
            } else if (zLatest < 0) {
                zTone = `Z-Score ${asset} berada di ${zLatest.toFixed(
                    2
                )}, mencerminkan valuasi relatif dingin dan akumulatif.`;
            } else {
                zTone = `Z-Score ${asset} stabil di ${zLatest.toFixed(
                    2
                )}, menandakan valuasi berada di kisaran wajar.`;
            }
            let valuationTone;
            if (mvrvLatest < 1) {
                valuationTone = `MVRV ${asset} di ${mvrvLatest.toFixed(
                    2
                )} menegaskan kondisi undervaluation dan peluang akumulasi.`;
            } else if (mvrvLatest < 2) {
                valuationTone = `MVRV ${asset} di ${mvrvLatest.toFixed(
                    2
                )} masih netral; monitor konfirmasi tren sebelum agresif.`;
            } else {
                valuationTone = `MVRV ${asset} mencapai ${mvrvLatest.toFixed(
                    2
                )} sehingga potensi distribusi jangka pendek meningkat.`;
            }
            this.insights.mvrv = `${zTone} ${valuationTone}`;
            const soprTone =
                soprLatest > 1
                    ? `SOPR ${asset} di ${soprLatest.toFixed(
                          2
                      )} menunjukkan profit-taking pelaku pasar masih dominan.`
                    : `SOPR ${asset} di ${soprLatest.toFixed(
                          2
                      )} menandakan tekanan jual dapat diserap oleh demand on-chain.`;
            const reserveTone =
                reserveLatest < 0.4
                    ? `Reserve Risk ${asset} rendah (${reserveLatest.toFixed(
                          2
                      )}) sehingga kepercayaan holder jangka panjang tetap kuat.`
                    : `Reserve Risk ${asset} naik ke ${reserveLatest.toFixed(
                          2
                      )}; perhatikan risiko distribusi saat momentum melemah.`;
            this.insights.reserve = `${soprTone} ${reserveTone}`;

            const cddDirection = cddLatest >= cddPrev ? "meningkat" : "menurun";
            const cddAvg =
                data.cdd.reduce((a, b) => a + b, 0) / data.cdd.length;
            const cddTone =
                cddLatest > cddAvg
                    ? `Lonjakan CDD ke ${cddLatest.toFixed(
                          0
                      )} menandakan pergerakan supply lama — potensi distribusi pada fase puncak pasar.`
                    : `CDD ${asset} ${cddDirection} ke ${cddLatest.toFixed(
                          0
                      )}, mengindikasikan supply lama masih tertahan.`;
            const dormancyTone =
                dormancyLatest > 70
                    ? `Dormancy tinggi (${dormancyLatest.toFixed(
                          0
                      )}) menunjukkan coin tertahan lama; holder tidak tergoda jual.`
                    : `Dormancy rendah (${dormancyLatest.toFixed(
                          0
                      )}) mengindikasikan pergerakan supply aktif meningkat.`;
            this.insights.cdd = `${cddTone} ${dormancyTone}`;

            const direction = mvrvLatest >= mvrvPrev ? "Higher" : "Lower";
            this.metrics.mvrv = mvrvLatest.toFixed(2);
            this.metrics.mvrvDelta = `Prev: ${mvrvPrev.toFixed(
                2
            )} (${direction})`;
            this.metrics.zScore = zLatest.toFixed(2);
            this.metrics.zScoreDelta =
                zLatest > 1.4
                    ? "Hot zone watchlist"
                    : zLatest < 0
                    ? "Accumulation comfort"
                    : "Neutral regime";
            this.metrics.sopr = soprLatest.toFixed(2);
            this.metrics.soprNarrative =
                soprLatest > 1
                    ? "Profit pressure building"
                    : "Healthy absorption";
        },
    }));
    Alpine.data("supplyModule", () => ({
        store: Alpine.store("onchainMetrics"),
        charts: {
            supply: null,
            realizedCap: null,
            hodl: null,
        },
        insights: {
            supply: "",
            realizedCap: "",
            hodl: "",
        },
        metrics: {
            lthShare: "--",
            lthTrend: "",
            sthShare: "--",
            sthTrend: "",
            realizedCap: "--",
            realizedCapTrend: "",
        },
        init() {
            queueMicrotask(() => {
                this.renderCharts();
            });
            this.$watch(
                () => [this.store.selectedAsset, this.store.selectedRange],
                () => this.updateCharts()
            );
            this.$watch(
                () => this.store.refreshTick,
                () => this.updateCharts()
            );
        },
        renderCharts() {
            const data = this.store.generateSupplyData();
            this.buildSupplyChart(data);
            this.buildRealizedCapChart(data);
            this.buildHodlChart(data);
            this.updateInsights(data);
        },
        updateCharts() {
            if (
                !this.charts.supply ||
                !this.charts.realizedCap ||
                !this.charts.hodl
            ) {
                this.renderCharts();
                return;
            }
            const data = this.store.generateSupplyData();
            this.applySupplyData(data);
            this.applyRealizedCapData(data);
            this.applyHodlData(data);
            this.updateInsights(data);
        },
        buildSupplyChart(data) {
            const ctx = this.$refs.supplyChart.getContext("2d");
            this.charts.supply = new Chart(ctx, {
                type: "line",
                data: {
                    labels: data.dates,
                    datasets: [
                        {
                            label: "Long-Term Holders",
                            data: data.lthPercent,
                            borderColor: themePalette.bullish,
                            backgroundColor: createGradientFill(
                                ctx,
                                themePalette.bullish,
                                0.32
                            ),
                            borderWidth: 2,
                            tension: 0.35,
                            fill: true,
                            pointRadius: 0,
                            yAxisID: "supply",
                            stack: "holders",
                        },
                        {
                            label: "Short-Term Holders",
                            data: data.sthPercent,
                            borderColor: themePalette.neutral,
                            backgroundColor: createGradientFill(
                                ctx,
                                themePalette.neutral,
                                0.24
                            ),
                            borderWidth: 2,
                            tension: 0.35,
                            fill: true,
                            pointRadius: 0,
                            yAxisID: "supply",
                            stack: "holders",
                        },
                        {
                            label: "Realized Cap (USD Bn)",
                            data: data.realizedCap,
                            borderColor: "#8b5cf6",
                            borderWidth: 2,
                            tension: 0.3,
                            fill: false,
                            pointRadius: 0,
                            yAxisID: "cap",
                        },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    interaction: { mode: "index", intersect: false },
                    animation: { duration: 520, easing: "easeOutQuart" },
                    plugins: {
                        legend: {
                            display: true,
                            align: "start",
                            labels: { usePointStyle: true, boxWidth: 10 },
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const label = context.dataset.label ?? "";
                                    const value = context.parsed.y;
                                    if (context.dataset.yAxisID === "cap") {
                                        return `${label}: $${value.toFixed(
                                            1
                                        )}B`;
                                    }
                                    return `${label}: ${value.toFixed(1)}%`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                        },
                        supply: {
                            min: 0,
                            max: 100,
                            position: "left",
                            stacked: true,
                            title: { display: true, text: "Supply Share (%)" },
                            ticks: {
                                callback: (value) => `${value}%`,
                            },
                            grid: {
                                color: hexToRgba(themePalette.slate, 0.08),
                            },
                        },
                        cap: {
                            position: "right",
                            title: {
                                display: true,
                                text: "Realized Cap (USD Bn)",
                            },
                            grid: { drawOnChartArea: false },
                        },
                    },
                },
            });
            this.applySupplyData(data);
        },
        applySupplyData(data) {
            const chart = this.charts.supply;
            if (!chart) {
                return;
            }
            chart.data.labels = data.dates;
            chart.data.datasets[0].data = data.lthPercent;
            chart.data.datasets[1].data = data.sthPercent;
            chart.data.datasets[2].data = data.realizedCap;
            chart.update();
        },
        buildRealizedCapChart(data) {
            const ctx = this.$refs.realizedCapChart.getContext("2d");
            this.charts.realizedCap = new Chart(ctx, {
                type: "line",
                data: {
                    labels: data.dates,
                    datasets: [
                        {
                            label: "Realized Cap (USD Bn)",
                            data: data.realizedCap,
                            borderColor: "#8b5cf6",
                            backgroundColor: createGradientFill(
                                ctx,
                                "#8b5cf6",
                                0.22
                            ),
                            borderWidth: 2,
                            tension: 0.35,
                            fill: true,
                            pointRadius: 0,
                        },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    interaction: { mode: "index", intersect: false },
                    animation: { duration: 520, easing: "easeOutQuart" },
                    plugins: {
                        legend: {
                            display: true,
                            align: "start",
                            labels: { usePointStyle: true, boxWidth: 10 },
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) =>
                                    `Realized Cap: $${context.parsed.y.toFixed(
                                        1
                                    )}B`,
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                        },
                        y: {
                            title: {
                                display: true,
                                text: "Realized Cap (USD Bn)",
                            },
                            grid: {
                                color: hexToRgba(themePalette.slate, 0.08),
                            },
                            ticks: {
                                callback: (value) => `$${value}B`,
                            },
                        },
                    },
                },
            });
            this.applyRealizedCapData(data);
        },
        applyRealizedCapData(data) {
            const chart = this.charts.realizedCap;
            if (!chart) {
                return;
            }
            chart.data.labels = data.dates;
            chart.data.datasets[0].data = data.realizedCap;
            const capMin = Math.min(...data.realizedCap);
            const capMax = Math.max(...data.realizedCap);
            chart.options.scales.y.suggestedMin = Math.max(
                0,
                capMin - capMin * 0.1
            );
            chart.options.scales.y.suggestedMax = capMax + capMax * 0.1;
            chart.update();
        },
        buildHodlChart(data) {
            const ctx = this.$refs.hodlChart.getContext("2d");
            const gradients = hodlPalette.map((color) =>
                createGradientFill(ctx, color, 0.28)
            );
            const datasets = this.store.cohortBands().map((cohort, index) => ({
                label: cohort,
                data: data.hodlDistributions.map((entry) => entry[index]),
                borderColor: hodlPalette[index],
                backgroundColor: gradients[index],
                fill: true,
                tension: 0.32,
                pointRadius: 0,
                stack: "hodl",
                yAxisID: "hodl",
            }));
            this.charts.hodl = new Chart(ctx, {
                type: "line",
                data: {
                    labels: data.dates,
                    datasets,
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    interaction: { mode: "index", intersect: false },
                    animation: { duration: 520, easing: "easeOutQuart" },
                    plugins: {
                        legend: {
                            display: true,
                            position: "top",
                            align: "start",
                            labels: { usePointStyle: true, boxWidth: 8 },
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) =>
                                    `${
                                        context.dataset.label
                                    }: ${context.parsed.y.toFixed(1)}%`,
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                        },
                        hodl: {
                            stacked: true,
                            position: "left",
                            min: 0,
                            max: 100,
                            title: {
                                display: true,
                                text: "Supply Cohorts (%)",
                            },
                            ticks: {
                                callback: (value) => `${value}%`,
                            },
                            grid: {
                                color: hexToRgba(themePalette.slate, 0.08),
                            },
                        },
                    },
                },
            });
            this.applyHodlData(data);
        },
        applyHodlData(data) {
            const chart = this.charts.hodl;
            if (!chart) {
                return;
            }
            chart.data.labels = data.dates;
            chart.data.datasets.forEach((dataset, index) => {
                dataset.data = data.hodlDistributions.map(
                    (entry) => entry[index]
                );
            });
            chart.update();
        },
        updateInsights(data) {
            const asset = this.store.assetLabel();
            const lthLatest = data.lthPercent.at(-1) ?? 0;
            const lthPrev = data.lthPercent.at(-2) ?? lthLatest;
            const sthLatest = data.sthPercent.at(-1) ?? 0;
            const realizedLatest = data.realizedCap.at(-1) ?? 0;
            const realizedPrev = data.realizedCap.at(-2) ?? realizedLatest;
            const hodlLatest = data.hodlDistributions.at(-1) ?? [];
            const stickyCohort = hodlLatest
                .slice(3)
                .reduce((acc, value) => acc + value, 0);
            const lthTrend = lthLatest >= lthPrev ? "akumulasi" : "distribusi";
            this.insights.supply = `Dominasi LTH ${asset} berada di ${lthLatest.toFixed(
                1
            )}% yang menandakan fase ${lthTrend}. STH tersisa ${sthLatest.toFixed(
                1
            )}% sehingga supply berputar tetap terkendali.`;

            const realizedGrowth = (
                ((realizedLatest - realizedPrev) / realizedPrev) *
                100
            ).toFixed(1);
            const realizedTone =
                realizedLatest >= realizedPrev
                    ? `Kenaikan realized cap ${asset} sebesar ${realizedGrowth}% ke $${realizedLatest.toFixed(
                          1
                      )}B menunjukkan aliran modal baru dan meningkatnya valuasi fundamental.`
                    : `Realized cap ${asset} turun ${Math.abs(
                          parseFloat(realizedGrowth)
                      ).toFixed(1)}% ke $${realizedLatest.toFixed(
                          1
                      )}B; perhatikan potensi realisasi laba oleh holder.`;
            this.insights.realizedCap = realizedTone;

            const stickyTone =
                stickyCohort >= 55
                    ? "Gelombang supply berumur >6 bulan mendominasi dan menekan tekanan jual."
                    : "Proporsi supply berumur panjang menurun; awasi potensi distribusi baru.";
            this.insights.hodl = `Peningkatan supply berumur >1 tahun ${asset} mencapai ${stickyCohort.toFixed(
                1
            )}% - ${stickyTone} Hal ini menunjukkan berkurangnya tekanan jual dan keengganan melepas posisi.`;
            this.metrics.lthShare = `${lthLatest.toFixed(1)}%`;
            this.metrics.lthTrend =
                lthLatest >= lthPrev
                    ? "Bias: accumulation"
                    : "Bias: distribution";
            this.metrics.sthShare = `${sthLatest.toFixed(1)}%`;
            this.metrics.sthTrend =
                sthLatest <= 40 ? "Float tightening" : "Float expanding";
            this.metrics.realizedCap = `$${realizedLatest.toFixed(1)}B`;
            this.metrics.realizedCapTrend = `Prev: $${realizedPrev.toFixed(
                1
            )}B`;
        },
    }));
    Alpine.data("flowsModule", () => ({
        store: Alpine.store("onchainMetrics"),
        charts: {
            netflow: null,
            stablecoin: null,
            heatmap: null,
        },
        insights: {
            netflow: "",
            liquidity: "",
            heatmap: "",
        },
        metrics: {
            netflow: "--",
            netflowTone: "",
            stablecoinNet: "--",
            stablecoinTone: "",
            dominantVenue: "",
        },
        exchangeRows: [],
        init() {
            queueMicrotask(() => {
                this.renderCharts();
            });
            this.$watch(
                () => [this.store.selectedAsset, this.store.selectedRange],
                () => this.updateCharts()
            );
            this.$watch(
                () => this.store.refreshTick,
                () => this.updateCharts()
            );
        },
        renderCharts() {
            const data = this.store.generateFlowsData();
            this.buildNetflowChart(data);
            this.buildStablecoinChart(data);
            this.buildHeatmapChart(data);
            this.updateInsights(data);
        },
        updateCharts() {
            if (
                !this.charts.netflow ||
                !this.charts.stablecoin ||
                !this.charts.heatmap
            ) {
                this.renderCharts();
                return;
            }
            const data = this.store.generateFlowsData();
            this.applyNetflowData(data);
            this.applyStablecoinData(data);
            this.applyHeatmapData(data);
            this.updateInsights(data);
        },
        buildNetflowChart(data) {
            const ctx = this.$refs.netflowChart.getContext("2d");
            this.charts.netflow = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: data.dates,
                    datasets: [
                        {
                            label: "Exchange Netflow",
                            data: data.netflow,
                            backgroundColor: (context) => {
                                const value = context.parsed.y ?? 0;
                                const intensity = Math.min(
                                    1,
                                    Math.abs(value) / 12
                                );
                                return value >= 0
                                    ? hexToRgba(
                                          themePalette.bearish,
                                          0.3 + intensity * 0.6
                                      )
                                    : hexToRgba(
                                          themePalette.bullish,
                                          0.3 + intensity * 0.6
                                      );
                            },
                            borderRadius: 6,
                            borderSkipped: false,
                        },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    interaction: { mode: "index", intersect: false },
                    animation: { duration: 480, easing: "easeOutQuart" },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) =>
                                    `Netflow: ${context.parsed.y.toFixed(2)}%`,
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                        },
                        y: {
                            title: {
                                display: true,
                                text: "Netflow (Relative %)",
                            },
                            grid: {
                                color: hexToRgba(themePalette.slate, 0.08),
                            },
                            ticks: {
                                callback: (value) => `${value}%`,
                            },
                        },
                    },
                },
            });
            this.applyNetflowData(data);
        },
        applyNetflowData(data) {
            const chart = this.charts.netflow;
            if (!chart) {
                return;
            }
            chart.data.labels = data.dates;
            chart.data.datasets[0].data = data.netflow;
            chart.update();
        },
        buildStablecoinChart(data) {
            const ctx = this.$refs.stablecoinChart.getContext("2d");
            this.charts.stablecoin = new Chart(ctx, {
                type: "line",
                data: {
                    labels: data.dates,
                    datasets: [
                        {
                            label: "Stablecoin Netflow",
                            data: data.stablecoinNetflow,
                            borderColor: "#f59e0b",
                            backgroundColor: createGradientFill(
                                ctx,
                                "#f59e0b",
                                0.22
                            ),
                            borderWidth: 2,
                            tension: 0.32,
                            fill: true,
                            pointRadius: 0,
                        },
                        {
                            label: "Stablecoin Inflow",
                            data: data.stablecoinInflow,
                            borderColor: themePalette.neutral,
                            borderWidth: 1.5,
                            borderDash: [6, 4],
                            tension: 0.3,
                            fill: false,
                            pointRadius: 0,
                        },
                        {
                            label: "Stablecoin Outflow",
                            data: data.stablecoinOutflow,
                            borderColor: themePalette.bearish,
                            borderWidth: 1.5,
                            borderDash: [4, 4],
                            tension: 0.3,
                            fill: false,
                            pointRadius: 0,
                        },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    interaction: { mode: "index", intersect: false },
                    animation: { duration: 480, easing: "easeOutQuart" },
                    plugins: {
                        legend: {
                            display: true,
                            position: "top",
                            align: "start",
                            labels: { usePointStyle: true, boxWidth: 10 },
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) =>
                                    `${
                                        context.dataset.label
                                    }: ${context.parsed.y.toFixed(2)}%`,
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                        },
                        y: {
                            title: {
                                display: true,
                                text: "Netflow (Relative %)",
                            },
                            grid: {
                                color: hexToRgba(themePalette.slate, 0.08),
                            },
                            ticks: {
                                callback: (value) => `${value}%`,
                            },
                        },
                    },
                },
            });
            this.applyStablecoinData(data);
        },
        applyStablecoinData(data) {
            const chart = this.charts.stablecoin;
            if (!chart) {
                return;
            }
            chart.data.labels = data.dates;
            chart.data.datasets[0].data = data.stablecoinNetflow;
            chart.data.datasets[1].data = data.stablecoinInflow;
            chart.data.datasets[2].data = data.stablecoinOutflow;
            chart.update();
        },
        buildHeatmapChart(data) {
            const ctx = this.$refs.heatmapChart.getContext("2d");
            this.charts.heatmap = new Chart(ctx, {
                type: "matrix",
                data: {
                    datasets: [
                        {
                            label: "Exchange Comparison",
                            data: data.heatmapData,
                            backgroundColor: (context) => {
                                const value = context.raw.v ?? 0;
                                const intensity = Math.min(
                                    1,
                                    Math.abs(value) / 12
                                );
                                return value >= 0
                                    ? hexToRgba(
                                          themePalette.bearish,
                                          0.25 + intensity * 0.6
                                      )
                                    : hexToRgba(
                                          themePalette.bullish,
                                          0.25 + intensity * 0.6
                                      );
                            },
                            borderColor: hexToRgba(themePalette.slate, 0.08),
                            borderWidth: 1,
                            width: (ctx) => {
                                const chartArea = ctx.chart.chartArea;
                                const count = ctx.chart.scales.x.ticks.length;
                                return count ? chartArea.width / count - 4 : 0;
                            },
                            height: (ctx) => {
                                const chartArea = ctx.chart.chartArea;
                                const count = ctx.chart.scales.y.ticks.length;
                                return count ? chartArea.height / count - 4 : 0;
                            },
                        },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    interaction: { mode: "nearest", intersect: true },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: (items) => {
                                    const item = items[0];
                                    return `${item.raw.y} • ${item.raw.x}`;
                                },
                                label: (context) =>
                                    `Netflow: ${context.raw.v.toFixed(2)}%`,
                            },
                        },
                    },
                    scales: {
                        x: {
                            type: "category",
                            offset: true,
                            ticks: { maxRotation: 0 },
                            grid: { display: false },
                        },
                        y: {
                            type: "category",
                            offset: true,
                            grid: { display: false },
                        },
                    },
                },
            });
            this.applyHeatmapData(data);
        },
        applyHeatmapData(data) {
            const chart = this.charts.heatmap;
            if (!chart) {
                return;
            }
            chart.data.datasets[0].data = data.heatmapData;
            chart.update();
        },
        updateInsights(data) {
            const asset = this.store.assetLabel();
            const netLatest = data.netflow.at(-1) ?? 0;
            const netPrev = data.netflow.at(-2) ?? netLatest;
            const stableNet = data.stablecoinNetflow.at(-1) ?? 0;
            const stablePrev = data.stablecoinNetflow.at(-2) ?? stableNet;
            const leadVenue = data.exchangeBreakdown.reduce(
                (top, item) =>
                    Math.abs(item.netflow) > Math.abs(top.netflow) ? item : top,
                data.exchangeBreakdown[0] ?? {
                    venue: "-",
                    netflow: 0,
                    balance: 0,
                }
            );
            const pressureTone =
                netLatest >= 0
                    ? `Net inflow ${asset} mencapai ${netLatest.toFixed(
                          2
                      )}% sehingga tekanan jual jangka pendek meningkat.`
                    : `Net outflow ${asset} sebesar ${Math.abs(
                          netLatest
                      ).toFixed(2)}% menandakan akumulasi dari exchange.`;
            const deltaTone =
                Math.abs(netLatest) >= Math.abs(netPrev)
                    ? "Momentum aliran semakin kuat."
                    : "Momentum aliran melemah dibanding periode sebelumnya.";
            this.insights.netflow = `${pressureTone} ${deltaTone}`;
            const liquidityTone =
                stableNet >= 0
                    ? `Stablecoin netflow positif ${stableNet.toFixed(
                          2
                      )}% menunjukkan likuiditas siap mendukung kenaikan ${asset}.`
                    : `Stablecoin netflow negatif ${Math.abs(stableNet).toFixed(
                          2
                      )}% menandakan likuiditas berpindah keluar dari pasar ${asset}.`;
            const stableDelta =
                Math.abs(stableNet) >= Math.abs(stablePrev)
                    ? "Perubahan volume memperkuat sinyal likuiditas."
                    : "Perubahan netflow melambat dibanding periode sebelumnya.";
            this.insights.liquidity = `${liquidityTone} ${stableDelta}`;
            this.insights.heatmap = `Exchange ${
                leadVenue.venue
            } mendominasi arus ${asset} dengan netflow ${leadVenue.netflow.toFixed(
                2
            )}% - amati peluang arbitrase lintas venue.`;
            this.metrics.netflow = `${netLatest.toFixed(2)}%`;
            this.metrics.netflowTone =
                netLatest >= 0 ? "Bias: sell pressure" : "Bias: accumulation";
            this.metrics.stablecoinNet = `${stableNet.toFixed(2)}%`;
            this.metrics.stablecoinTone =
                stableNet >= 0 ? "Liquidity building" : "Liquidity draining";
            this.metrics.dominantVenue = `${
                leadVenue.venue
            } (${leadVenue.netflow.toFixed(2)}%)`;
            this.exchangeRows = data.exchangeBreakdown.map((item) => ({
                ...item,
                netflow: item.netflow,
                balance: item.balance,
            }));
        },
    }));
    Alpine.data("minersWhalesModule", () => ({
        store: Alpine.store("onchainMetrics"),
        charts: {
            miner: null,
            whale: null,
            whaleChange: null,
        },
        insights: {
            miner: "",
            whale: "",
            whaleChange: "",
        },
        metrics: {
            minerReserve: "--",
            minerTrend: "",
            puell: "--",
            puellTone: "",
            whaleLeader: "",
        },
        init() {
            queueMicrotask(() => {
                this.renderCharts();
            });
            this.$watch(
                () => [this.store.selectedAsset, this.store.selectedRange],
                () => this.updateCharts()
            );
            this.$watch(
                () => this.store.refreshTick,
                () => this.updateCharts()
            );
        },
        renderCharts() {
            const data = this.store.generateMinerData();
            this.buildMinerChart(data);
            this.buildWhaleChart(data);
            this.buildWhaleChangeChart(data);
            this.updateInsights(data);
        },
        updateCharts() {
            if (
                !this.charts.miner ||
                !this.charts.whale ||
                !this.charts.whaleChange
            ) {
                this.renderCharts();
                return;
            }
            const data = this.store.generateMinerData();
            this.applyMinerData(data);
            this.applyWhaleData(data);
            this.applyWhaleChangeData(data);
            this.updateInsights(data);
        },
        buildMinerChart(data) {
            const ctx = this.$refs.minerChart.getContext("2d");
            this.charts.miner = new Chart(ctx, {
                type: "line",
                data: {
                    labels: data.dates,
                    datasets: [
                        {
                            label: "Miner Reserve (M units)",
                            data: data.minerReserve,
                            borderColor: themePalette.neutral,
                            backgroundColor: createGradientFill(
                                ctx,
                                themePalette.neutral,
                                0.24
                            ),
                            borderWidth: 2,
                            tension: 0.32,
                            fill: true,
                            pointRadius: 0,
                            yAxisID: "reserve",
                        },
                        {
                            label: "Puell Multiple",
                            data: data.puellMultiple,
                            borderColor: themePalette.bearish,
                            borderWidth: 2,
                            borderDash: [5, 4],
                            tension: 0.3,
                            fill: false,
                            pointRadius: 0,
                            yAxisID: "puell",
                        },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    interaction: { mode: "index", intersect: false },
                    animation: { duration: 500, easing: "easeOutQuart" },
                    plugins: {
                        legend: {
                            display: true,
                            position: "top",
                            align: "start",
                            labels: { usePointStyle: true, boxWidth: 10 },
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) =>
                                    `${
                                        context.dataset.label
                                    }: ${context.parsed.y.toFixed(2)}`,
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                        },
                        reserve: {
                            position: "left",
                            title: {
                                display: true,
                                text: "Miner Reserve (M units)",
                            },
                            grid: {
                                color: hexToRgba(themePalette.slate, 0.08),
                            },
                        },
                        puell: {
                            position: "right",
                            title: { display: true, text: "Puell Multiple" },
                            grid: { drawOnChartArea: false },
                        },
                    },
                },
            });
            this.applyMinerData(data);
        },
        applyMinerData(data) {
            const chart = this.charts.miner;
            if (!chart) {
                return;
            }
            chart.data.labels = data.dates;
            chart.data.datasets[0].data = data.minerReserve;
            chart.data.datasets[1].data = data.puellMultiple;
            const reserveUnit =
                this.store.selectedAsset === "STABLECOINS" ? "B" : "M";
            chart.data.datasets[0].label = `Miner Reserve (${reserveUnit} units)`;
            chart.options.scales.reserve.title.text = `Miner Reserve (${reserveUnit} units)`;
            const reserveMin = Math.min(...data.minerReserve);
            const reserveMax = Math.max(...data.minerReserve);
            chart.options.scales.reserve.suggestedMin = Math.max(
                0,
                reserveMin - reserveMin * 0.2
            );
            chart.options.scales.reserve.suggestedMax =
                reserveMax + reserveMax * 0.2;
            const puellMin = Math.min(...data.puellMultiple);
            const puellMax = Math.max(...data.puellMultiple);
            chart.options.scales.puell.suggestedMin = Math.max(
                0,
                puellMin - 0.4
            );
            chart.options.scales.puell.suggestedMax = puellMax + 0.4;
            chart.update();
        },
        buildWhaleChart(data) {
            const ctx = this.$refs.whaleChart.getContext("2d");
            const cohorts = Object.keys(data.whaleCohorts);
            const datasets = cohorts.map((cohort, index) => ({
                label: cohort,
                data: data.whaleCohorts[cohort],
                borderColor: whalePalette[index % whalePalette.length],
                borderWidth: 2,
                tension: 0.32,
                fill: false,
                pointRadius: 0,
            }));
            this.charts.whale = new Chart(ctx, {
                type: "line",
                data: {
                    labels: data.dates,
                    datasets,
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    interaction: { mode: "index", intersect: false },
                    animation: { duration: 500, easing: "easeOutQuart" },
                    plugins: {
                        legend: {
                            display: true,
                            position: "top",
                            align: "start",
                            labels: { usePointStyle: true, boxWidth: 10 },
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) =>
                                    `${
                                        context.dataset.label
                                    }: ${context.parsed.y.toFixed(2)} ${
                                        this.store.selectedAsset ===
                                        "STABLECOINS"
                                            ? "B"
                                            : "M"
                                    }`,
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                        },
                        y: {
                            title: {
                                display: true,
                                text:
                                    this.store.selectedAsset === "STABLECOINS"
                                        ? "Holdings (B units)"
                                        : "Holdings (M units)",
                            },
                            grid: {
                                color: hexToRgba(themePalette.slate, 0.08),
                            },
                        },
                    },
                },
            });
            this.applyWhaleData(data);
        },
        applyWhaleData(data) {
            const chart = this.charts.whale;
            if (!chart) {
                return;
            }
            chart.data.labels = data.dates;
            chart.data.datasets.forEach((dataset) => {
                dataset.data = data.whaleCohorts[dataset.label];
            });
            chart.options.scales.y.title.text =
                this.store.selectedAsset === "STABLECOINS"
                    ? "Holdings (B units)"
                    : "Holdings (M units)";
            chart.update();
        },
        buildWhaleChangeChart(data) {
            const ctx = this.$refs.whaleChangeChart.getContext("2d");
            const cohorts = Object.keys(data.whaleCohorts);
            const firstCohort = cohorts[0];

            // Calculate daily changes
            const dailyChanges = data.whaleCohorts[firstCohort].map(
                (val, idx, arr) => {
                    if (idx === 0) return 0;
                    return Number((val - arr[idx - 1]).toFixed(2));
                }
            );

            // Calculate cumulative changes
            const cumulativeChanges = dailyChanges.reduce((acc, val) => {
                const prev = acc.length > 0 ? acc[acc.length - 1] : 0;
                acc.push(Number((prev + val).toFixed(2)));
                return acc;
            }, []);

            this.charts.whaleChange = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: data.dates,
                    datasets: [
                        {
                            label: "Daily Change",
                            data: dailyChanges,
                            backgroundColor: (context) => {
                                const value = context.parsed.y ?? 0;
                                return value >= 0
                                    ? hexToRgba(themePalette.bullish, 0.7)
                                    : hexToRgba(themePalette.bearish, 0.7);
                            },
                            borderColor: (context) => {
                                const value = context.parsed.y ?? 0;
                                return value >= 0
                                    ? themePalette.bullish
                                    : themePalette.bearish;
                            },
                            borderWidth: 1,
                            borderRadius: 4,
                            yAxisID: "change",
                        },
                        {
                            label: "Cumulative Change",
                            data: cumulativeChanges,
                            type: "line",
                            borderColor: "#8b5cf6",
                            backgroundColor: createGradientFill(
                                ctx,
                                "#8b5cf6",
                                0.15
                            ),
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true,
                            pointRadius: 0,
                            yAxisID: "cumulative",
                        },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    interaction: { mode: "index", intersect: false },
                    animation: { duration: 500, easing: "easeOutQuart" },
                    plugins: {
                        legend: {
                            display: true,
                            position: "top",
                            align: "start",
                            labels: { usePointStyle: true, boxWidth: 10 },
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const unit =
                                        this.store.selectedAsset ===
                                        "STABLECOINS"
                                            ? "B"
                                            : "M";
                                    return `${context.dataset.label}: ${
                                        context.parsed.y >= 0 ? "+" : ""
                                    }${context.parsed.y.toFixed(2)} ${unit}`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                        },
                        change: {
                            position: "left",
                            title: {
                                display: true,
                                text:
                                    this.store.selectedAsset === "STABLECOINS"
                                        ? "Daily Change (B units)"
                                        : "Daily Change (M units)",
                            },
                            grid: {
                                color: hexToRgba(themePalette.slate, 0.08),
                            },
                        },
                        cumulative: {
                            position: "right",
                            title: {
                                display: true,
                                text:
                                    this.store.selectedAsset === "STABLECOINS"
                                        ? "Cumulative (B units)"
                                        : "Cumulative (M units)",
                            },
                            grid: { drawOnChartArea: false },
                        },
                    },
                },
            });
            this.applyWhaleChangeData(data);
        },
        applyWhaleChangeData(data) {
            const chart = this.charts.whaleChange;
            if (!chart) {
                return;
            }

            const cohorts = Object.keys(data.whaleCohorts);
            const firstCohort = cohorts[0];

            const dailyChanges = data.whaleCohorts[firstCohort].map(
                (val, idx, arr) => {
                    if (idx === 0) return 0;
                    return Number((val - arr[idx - 1]).toFixed(2));
                }
            );

            const cumulativeChanges = dailyChanges.reduce((acc, val) => {
                const prev = acc.length > 0 ? acc[acc.length - 1] : 0;
                acc.push(Number((prev + val).toFixed(2)));
                return acc;
            }, []);

            chart.data.labels = data.dates;
            chart.data.datasets[0].data = dailyChanges;
            chart.data.datasets[1].data = cumulativeChanges;

            const unit =
                this.store.selectedAsset === "STABLECOINS"
                    ? "B units"
                    : "M units";
            chart.options.scales.change.title.text = `Daily Change (${unit})`;
            chart.options.scales.cumulative.title.text = `Cumulative (${unit})`;

            chart.update();
        },
        updateInsights(data) {
            const asset = this.store.assetLabel();
            const minerReserveLatest = data.minerReserve.at(-1) ?? 0;
            const minerReservePrev =
                data.minerReserve.at(-2) ?? minerReserveLatest;
            const puellLatest = data.puellMultiple.at(-1) ?? 0;
            const puellPrev = data.puellMultiple.at(-2) ?? puellLatest;
            const reserveDirection =
                minerReserveLatest >= minerReservePrev
                    ? "meningkat"
                    : "menurun";
            const puellTone =
                puellLatest > 1.5
                    ? "Puell tinggi mengindikasikan margin penambang subur dan potensi distribusi."
                    : puellLatest < 1
                    ? "Puell rendah menandakan insentif jual penambang menurun."
                    : "Puell berada di zona seimbang; tekanan distribusi terkontrol.";
            this.insights.miner = `Cadangan penambang ${asset} ${reserveDirection} ke ${minerReserveLatest.toFixed(
                2
            )}M, sementara Puell ${asset} berada di ${puellLatest.toFixed(
                2
            )}. ${puellTone}`;
            const cohortDeltas = Object.entries(data.whaleCohorts).map(
                ([cohort, series]) => ({
                    cohort,
                    delta:
                        (series.at(-1) ?? 0) -
                        (series.at(-2) ?? series.at(-1) ?? 0),
                    latest: series.at(-1) ?? 0,
                })
            );
            const leader = cohortDeltas.reduce(
                (top, item) =>
                    Math.abs(item.delta) > Math.abs(top.delta) ? item : top,
                cohortDeltas[0] ?? { cohort: "-", delta: 0, latest: 0 }
            );
            const whaleTone =
                leader.delta >= 0
                    ? `Cohort ${leader.cohort} menambah ${Math.abs(
                          leader.delta
                      ).toFixed(2)} ${
                          this.store.selectedAsset === "STABLECOINS" ? "B" : "M"
                      } ${asset}, mengonfirmasi akumulasi smart money.`
                    : `Cohort ${leader.cohort} melepas ${Math.abs(
                          leader.delta
                      ).toFixed(2)} ${
                          this.store.selectedAsset === "STABLECOINS" ? "B" : "M"
                      } ${asset}, sinyal distribusi strategis.`;
            this.insights.whale = `${whaleTone} Total kepemilikan cohort tetap di ${leader.latest.toFixed(
                2
            )} ${
                this.store.selectedAsset === "STABLECOINS" ? "B" : "M"
            } ${asset}.`;

            const cohorts = Object.keys(data.whaleCohorts);
            const firstCohort = cohorts[0];
            const dailyChanges = data.whaleCohorts[firstCohort].map(
                (val, idx, arr) => {
                    if (idx === 0) return 0;
                    return Number((val - arr[idx - 1]).toFixed(2));
                }
            );
            const latestChange = dailyChanges.at(-1) ?? 0;
            const cumulativeChange = dailyChanges.reduce(
                (acc, val) => acc + val,
                0
            );

            const changeTrend = latestChange >= 0 ? "akumulasi" : "distribusi";
            const changeDirection =
                cumulativeChange >= 0 ? "positif" : "negatif";
            const whaleChangeTone = `Arah perubahan posisi whale membantu membaca fase ${changeTrend} jangka panjang. Perubahan harian terakhir ${
                latestChange >= 0 ? "+" : ""
            }${latestChange.toFixed(2)} ${
                this.store.selectedAsset === "STABLECOINS" ? "B" : "M"
            } ${asset} dengan kumulatif ${changeDirection} ${Math.abs(
                cumulativeChange
            ).toFixed(2)} ${
                this.store.selectedAsset === "STABLECOINS" ? "B" : "M"
            }.`;
            this.insights.whaleChange = whaleChangeTone;

            const unit = this.store.selectedAsset === "STABLECOINS" ? "B" : "M";
            this.metrics.minerReserve = `${minerReserveLatest.toFixed(
                2
            )}${unit}`;
            this.metrics.minerTrend =
                minerReserveLatest >= minerReservePrev
                    ? "Reserves rising"
                    : "Reserves declining";
            this.metrics.puell = puellLatest.toFixed(2);
            this.metrics.puellTone =
                puellLatest >= puellPrev
                    ? "Revenue improving"
                    : "Revenue cooling";
            this.metrics.whaleLeader = `${leader.cohort}: ${
                leader.delta >= 0 ? "+" : ""
            }${leader.delta.toFixed(2)} ${unit}`;
        },
    }));
    Alpine.data("sidebar", () => ({
        open: true,
        collapsed: false,
        openSubmenus: {},
        profileDropdownOpen: false,

        toggle() {
            this.open = !this.open;
        },

        toggleCollapse() {
            this.collapsed = !this.collapsed;
            // Close all submenus when collapsing
            this.openSubmenus = {};
            // Close profile dropdown when collapsing
            this.profileDropdownOpen = false;
        },

        toggleSubmenu(menuId) {
            this.openSubmenus[menuId] = !this.openSubmenus[menuId];
        },

        toggleProfileDropdown() {
            this.profileDropdownOpen = !this.profileDropdownOpen;
        },

        closeProfileDropdown() {
            this.profileDropdownOpen = false;
        },
    }));

    Alpine.data("theme", () => ({
        dark: false,

        init() {
            // Check for saved theme preference or default to light
            this.dark =
                localStorage.getItem("theme") === "dark" ||
                (!localStorage.getItem("theme") &&
                    window.matchMedia("(prefers-color-scheme: dark)").matches);
            this.applyTheme();
        },

        toggle() {
            this.dark = !this.dark;
            this.applyTheme();
            localStorage.setItem("theme", this.dark ? "dark" : "light");

            // Update TradingView theme if widget exists
            this.updateTradingViewTheme();
        },

        applyTheme() {
            if (this.dark) {
                document.documentElement.classList.add("dark");
            } else {
                document.documentElement.classList.remove("dark");
            }
        },

        updateTradingViewTheme() {
            // Check if TradingView widget exists and update its theme
            if (window.TradingView && document.getElementById("tradingChart")) {
                // Remove existing widget
                const container = document.getElementById("tradingChart");
                container.innerHTML = "";

                // Create new widget with updated theme
                new TradingView.widget({
                    autosize: true,
                    symbol: "BINANCE:BTCUSDT",
                    interval: "D",
                    timezone: "Etc/UTC",
                    theme: this.dark ? "dark" : "light",
                    style: "1",
                    locale: "en",
                    toolbar_bg: this.dark ? "#1e293b" : "#ffffff",
                    enable_publishing: false,
                    withdateranges: true,
                    range: "1M",
                    hide_side_toolbar: false,
                    allow_symbol_change: true,
                    details: true,
                    hotlist: true,
                    calendar: false,
                    studies: ["RSI@tv-basicstudies", "MACD@tv-basicstudies"],
                    container_id: "tradingChart",
                });
            }
        },
    }));

    Alpine.data("tradingChart", () => ({
        symbol: "BTCUSDT",
        price: 65420.0,
        change: 1250.0,
        changePercent: 1.95,
        volume: 28500000000,
        high24h: 66800.0,
        low24h: 64200.0,

        init() {
            this.startPriceUpdates();
        },

        startPriceUpdates() {
            setInterval(() => {
                this.updatePrice();
            }, 2000);
        },

        updatePrice() {
            const basePrice = 65420;
            const change = (Math.random() - 0.5) * 2000;
            this.price = basePrice + change;
            this.change = change;
            this.changePercent = (change / basePrice) * 100;

            // Update volume randomly
            this.volume = Math.floor(Math.random() * 10000000000) + 20000000000;
        },

        formatPrice(price) {
            return (
                "$" +
                price.toLocaleString("en-US", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                })
            );
        },

        formatVolume(volume) {
            if (volume >= 1000000000) {
                return (volume / 1000000000).toFixed(1) + "B BTC";
            } else if (volume >= 1000000) {
                return (volume / 1000000).toFixed(1) + "M BTC";
            } else {
                return volume.toLocaleString() + " BTC";
            }
        },
    }));
});

// Note: Alpine.start() is already called by Livewire

// Legacy functions for backward compatibility
window.initTradingWidget = (element, options = {}) => {
    const $el = $(element);
    $el.addClass("position-relative bg-black rounded-4 overflow-hidden");
    $el.attr("data-widget-options", JSON.stringify(options));

    const info = $("<div/>", {
        class: "position-absolute top-0 end-0 m-3 text-end text-light",
    }).appendTo($el);

    const canvas = $("<div/>", {
        class: "w-100 h-100",
        css: {
            minHeight: "480px",
            background:
                "radial-gradient(circle at top, rgba(59,130,246,.2), rgba(15,23,42,1))",
        },
    }).appendTo($el);

    $el.data("df-info", info);
    $el.data("df-canvas", canvas);

    window.updateTradingWidget(options);
};

window.updateTradingWidget = (payload = {}) => {
    const { symbol = "BTCUSD", price = 0, changePercent = 0 } = payload;
    const target = $("[data-widget-options]");

    if (!target.length) {
        return;
    }

    target.each((_, element) => {
        const $el = $(element);
        const info = $el.data("df-info");
        if (!info) {
            return;
        }
        info.html(`
            <div class="fw-semibold">${symbol}</div>
            <div class="display-6 fw-bold">${Number(price).toLocaleString(
                undefined,
                { minimumFractionDigits: 2 }
            )}</div>
            <div class="small ${
                changePercent >= 0 ? "text-success" : "text-danger"
            }">
                ${changePercent >= 0 ? "+" : ""}${changePercent.toFixed(2)}%
            </div>
        `);
    });
};

// Utility functions
window.DFUtils = {
    formatCurrency: (amount, currency = "USD") => {
        return new Intl.NumberFormat("en-US", {
            style: "currency",
            currency: currency,
        }).format(amount);
    },

    formatNumber: (number, decimals = 2) => {
        return new Intl.NumberFormat("en-US", {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        }).format(number);
    },

    formatPercentage: (number, decimals = 2) => {
        return (number >= 0 ? "+" : "") + number.toFixed(decimals) + "%";
    },
};
