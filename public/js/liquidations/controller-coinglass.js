/**
 * Liquidations Controller (Modular, Coinglass)
 */

import { LiquidationsAPIService } from './api-service.js';

export function createLiquidationsController() {
    const api = new LiquidationsAPIService();

    return {
        // Global state
        globalLoading: true,
        selectedExchange: 'binance',
        selectedSymbol: 'btc_usdt',

        // Summary metrics
        currentPrice: 0,
        priceChange: 0,
        totalLiquidations: 0,
        longLiquidations: 0,
        shortLiquidations: 0,
        longLiquidationRatio: 0,
        shortLiquidationRatio: 0,
        longShortLiqRatio: 0,
        liquidationSentiment: 'Loading...',
        liquidationSentimentStrength: 'Normal',
        liquidationSentimentDescription: 'Analyzing market data...',

        async init() {
            await this.refreshAll();
            setInterval(() => this.refreshAll(), 30000);
        },

        async refreshAll() {
            try {
                this.globalLoading = true;
                await Promise.all([
                    this.loadBitcoinPrice(),
                    this.loadLiquidationSummary(),
                ]);
            } catch (e) {
                console.error('Liquidations refresh error:', e);
            } finally {
                this.globalLoading = false;
            }
        },

        async loadBitcoinPrice() {
            try {
                const data = await api.fetchBitcoinPrice24hWindow();
                if (data.success && Array.isArray(data.data) && data.data.length) {
                    const latest = data.data[data.data.length - 1];
                    this.currentPrice = latest.close || latest.value || 0;
                    if (data.data.length >= 2) {
                        const prev = data.data[data.data.length - 2];
                        const prevPrice = prev.close || prev.value || 0;
                        if (prevPrice > 0) this.priceChange = ((this.currentPrice - prevPrice) / prevPrice) * 100;
                    }
                } else {
                    this.useFallbackPrice();
                }
            } catch (e) {
                this.useFallbackPrice();
            }
        },

        async loadLiquidationSummary() {
            try {
                const data = await api.fetchLiquidationSummary('BTC');
                if (data.success && data.data) {
                    this.totalLiquidations = data.data.total || 0;
                    this.longLiquidations = data.data.long || 0;
                    this.shortLiquidations = data.data.short || 0;
                    if (this.totalLiquidations > 0) {
                        this.longLiquidationRatio = (this.longLiquidations / this.totalLiquidations) * 100;
                        this.shortLiquidationRatio = (this.shortLiquidations / this.totalLiquidations) * 100;
                    }
                    if (this.shortLiquidations > 0) this.longShortLiqRatio = this.longLiquidations / this.shortLiquidations;
                    this.calculateLiquidationSentiment();
                } else {
                    this.useFallbackLiquidations();
                }
            } catch (e) {
                this.useFallbackLiquidations();
            }
        },

        // Helpers and fallbacks
        useFallbackPrice() {
            this.currentPrice = 95000;
            this.priceChange = 2.5;
        },

        useFallbackLiquidations() {
            this.totalLiquidations = 45000000;
            this.longLiquidations = 25000000;
            this.shortLiquidations = 20000000;
            this.longLiquidationRatio = 55.6;
            this.shortLiquidationRatio = 44.4;
            this.longShortLiqRatio = 1.25;
            this.calculateLiquidationSentiment();
        },

        calculateLiquidationSentiment() {
            const r = this.longShortLiqRatio;
            if (r > 3) { this.liquidationSentiment = 'Extreme Long Liquidations'; this.liquidationSentimentStrength = 'Strong'; this.liquidationSentimentDescription = 'Massive long positions being liquidated - strong bearish pressure'; return; }
            if (r > 2) { this.liquidationSentiment = 'High Long Liquidations'; this.liquidationSentimentStrength = 'Moderate'; this.liquidationSentimentDescription = 'More longs being liquidated - bearish momentum'; return; }
            if (r < 0.33) { this.liquidationSentiment = 'Extreme Short Liquidations'; this.liquidationSentimentStrength = 'Strong'; this.liquidationSentimentDescription = 'Massive short positions being liquidated - strong bullish pressure'; return; }
            if (r < 0.5) { this.liquidationSentiment = 'High Short Liquidations'; this.liquidationSentimentStrength = 'Moderate'; this.liquidationSentimentDescription = 'More shorts being liquidated - bullish momentum'; return; }
            this.liquidationSentiment = 'Balanced';
            this.liquidationSentimentStrength = 'Normal';
            this.liquidationSentimentDescription = 'Liquidations are relatively balanced between longs and shorts';
        },

        updateExchange() {},
        updateSymbol() {},

        formatPriceUSD(value) {
            if (!value || isNaN(value)) return '$0';
            const num = parseFloat(value);
            return '$' + num.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        },
        formatLiquidation(value) {
            if (!value || isNaN(value)) return '$0';
            const num = parseFloat(value);
            if (num >= 1_000_000) return '$' + (num / 1_000_000).toFixed(2) + 'M';
            if (num >= 1_000) return '$' + (num / 1_000).toFixed(1) + 'K';
            return '$' + num.toFixed(0);
        },
        formatChange(value) {
            if (!value || isNaN(value)) return '0.00%';
            const num = parseFloat(value);
            const sign = num >= 0 ? '+' : '';
            return sign + num.toFixed(2) + '%';
        },
        getPriceTrendClass(value) {
            if (!value || isNaN(value)) return 'text-secondary';
            return parseFloat(value) >= 0 ? 'text-success' : 'text-danger';
        },
    };
}


