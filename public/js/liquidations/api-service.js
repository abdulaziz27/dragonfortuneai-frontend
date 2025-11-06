/**
 * Liquidations API Service (Coinglass + CryptoQuant wrappers)
 */

export class LiquidationsAPIService {
    constructor() {}

    async fetchBitcoinPrice24hWindow() {
        const endDate = new Date().toISOString().split('T')[0];
        const startDate = new Date(Date.now() - 2 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        const url = `/api/cryptoquant/btc-price?start_date=${startDate}&end_date=${endDate}`;
        const res = await fetch(url);
        if (!res.ok) throw new Error(`BTC price HTTP ${res.status}`);
        return res.json();
    }

    async fetchLiquidationSummary(symbol = 'BTC') {
        const res = await fetch(`/api/coinglass/liquidation-summary?symbol=${encodeURIComponent(symbol)}`);
        if (!res.ok) throw new Error(`Liq summary HTTP ${res.status}`);
        return res.json();
    }
}


