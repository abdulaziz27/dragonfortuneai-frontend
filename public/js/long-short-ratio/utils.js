/**
 * Long Short Ratio Utilities
 * Helper functions for formatting, calculations, and date handling
 */

export const LongShortRatioUtils = {
    /**
     * Format Long-Short Ratio value
     */
    formatRatio(value) {
        if (value === null || value === undefined || isNaN(value)) return 'N/A';
        const num = parseFloat(value);
        return num.toFixed(2);
    },

    /**
     * Format change percentage
     */
    formatChange(value) {
        if (value === null || value === undefined || isNaN(value)) return 'N/A';
        const sign = value >= 0 ? '+' : '';
        return `${sign}${value.toFixed(2)}%`;
    },

    /**
     * Format price with USD label
     */
    formatPriceUSD(value) {
        if (value === null || value === undefined || isNaN(value)) return 'N/A';
        const num = parseFloat(value);
        return '$' + num.toLocaleString('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    },

    /**
     * Format volume numbers
     */
    formatVolume(value) {
        if (value === null || value === undefined || isNaN(value)) return 'N/A';
        const num = parseFloat(value);
        if (num >= 1e9) return '$' + (num / 1e9).toFixed(2) + 'B';
        if (num >= 1e6) return '$' + (num / 1e6).toFixed(1) + 'M';
        if (num >= 1e3) return '$' + (num / 1e3).toFixed(0) + 'K';
        return '$' + num.toFixed(0);
    },

    /**
     * Format net bias
     */
    formatNetBias(value) {
        if (value === null || value === undefined || isNaN(value)) return 'N/A';
        const sign = value >= 0 ? '+' : '';
        return `${sign}${value.toFixed(1)}%`;
    },

    /**
     * Get trend class for ratios
     */
    getRatioTrendClass(value) {
        if (value > 0) return 'text-success';
        if (value < 0) return 'text-danger';
        return 'text-secondary';
    },

    /**
     * Get sentiment badge class
     */
    getSentimentBadgeClass(sentimentStrength) {
        const strengthMap = {
            'Strong': 'text-bg-danger',
            'Moderate': 'text-bg-warning',
            'Normal': 'text-bg-secondary'
        };
        return strengthMap[sentimentStrength] || 'text-bg-secondary';
    },

    /**
     * Get sentiment color class
     */
    getSentimentColorClass(marketSentiment) {
        const colorMap = {
            'Long Crowded': 'text-danger',
            'Bullish Bias': 'text-success',
            'Balanced': 'text-secondary',
            'Bearish Bias': 'text-warning',
            'Short Crowded': 'text-info'
        };
        return colorMap[marketSentiment] || 'text-secondary';
    },

    /**
     * Get date range for CryptoQuant API (YYYY-MM-DD format)
     */
    getDateRange(globalPeriod, timeRanges) {
        const endDate = new Date();
        const startDate = new Date();

        // Handle different period types
        if (globalPeriod === 'ytd') {
            startDate.setMonth(0, 1);
        } else if (globalPeriod === 'all') {
            startDate.setDate(endDate.getDate() - 365);
        } else {
            const selectedRange = timeRanges.find(r => r.value === globalPeriod);
            let days = selectedRange ? selectedRange.days : 1;
            startDate.setDate(endDate.getDate() - days);
        }

        // Format dates properly (YYYY-MM-DD)
        const formatDate = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        return {
            startDate: formatDate(startDate),
            endDate: formatDate(endDate)
        };
    },

    /**
     * Get time range for Coinglass API (milliseconds)
     */
    getTimeRange(globalPeriod, timeRanges) {
        const endTime = Date.now();
        const startTime = new Date();

        // Handle different period types
        if (globalPeriod === 'ytd') {
            // Year to date
            startTime.setMonth(0, 1); // January 1st of current year
        } else if (globalPeriod === 'all') {
            // All available data (1 year max for API stability)
            startTime.setDate(startTime.getDate() - 365);
        } else {
            // Find the selected time range
            const selectedRange = timeRanges.find(r => r.value === globalPeriod);
            let days = selectedRange ? selectedRange.days : 1;

            // Set start date to X days ago
            startTime.setDate(startTime.getDate() - days);
        }

        return {
            startTime: startTime.getTime(),
            endTime: endTime
        };
    },

    /**
     * Get YTD days
     */
    getYTDDays() {
        const now = new Date();
        const startOfYear = new Date(now.getFullYear(), 0, 1);
        const diffTime = Math.abs(now - startOfYear);
        return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    },

    /**
     * Calculate API limit based on date range and interval
     */
    calculateLimit(days, interval) {
        const intervalHours = {
            '5m': 5 / 60,
            '15m': 15 / 60,
            '30m': 30 / 60,
            '1h': 1,
            '4h': 4,
            '1d': 24
        };
        
        const hours = intervalHours[interval] || 1;
        const exactRecordsNeeded = Math.ceil((days * 24) / hours);
        
        // Buffer multiplier to ensure we get enough data
        const bufferMultiplier = 1.5;
        let calculatedLimit = Math.ceil(exactRecordsNeeded * bufferMultiplier);
        const maxLimit = 5000;
        return Math.min(calculatedLimit, maxLimit);
    },

    /**
     * Get exchange color (for visual consistency)
     */
    getExchangeColor(exchangeName) {
        const colors = {
            'Binance': '#f0b90b',
            'OKX': '#0052ff',
            'Bybit': '#f7a600',
            'BitMEX': '#e43e3b',
            'Bitget': '#00d4aa',
            'KuCoin': '#24ae8f',
            'Gate': '#64b5f6',
            'WhiteBIT': '#ffffff',
            'BingX': '#1890ff',
            'MEXC': '#1db584',
            'Bitunix': '#6c5ce7',
            'Crypto.com': '#003cda',
            'Hyperliquid': '#ff6b6b',
            'dYdX': '#6966ff',
            'Deribit': '#fff',
            'Bitmex': '#e43e3b',
            'Bitfinex': '#16a085',
            'CoinEx': '#3498db',
            'Kraken': '#5741d9',
            'Coinbase': '#0052ff',
            'HTX': '#01d3a1'
        };
        return colors[exchangeName] || '#64748b';
    },

    /**
     * Get bias class
     */
    getBiasClass(value) {
        if (value > 10) return 'text-success fw-bold'; // Strong bullish
        if (value > 5) return 'text-success'; // Bullish
        if (value < -10) return 'text-danger fw-bold'; // Strong bearish
        if (value < -5) return 'text-danger'; // Bearish
        return 'text-secondary'; // Neutral
    },

    /**
     * Get buy ratio class
     */
    getBuyRatioClass(value) {
        if (value > 60) return 'text-success fw-bold';
        if (value > 55) return 'text-success';
        if (value < 40) return 'text-danger';
        if (value < 45) return 'text-warning';
        return 'text-secondary';
    },

    /**
     * Get sell ratio class
     */
            getSellRatioClass(value) {
            if (value > 60) return 'text-danger fw-bold';
            if (value > 55) return 'text-danger';
            if (value < 40) return 'text-success';
            if (value < 45) return 'text-warning';
            return 'text-secondary';
        }
};

