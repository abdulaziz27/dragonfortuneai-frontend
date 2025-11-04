/**
 * Open Interest Utilities
 * Helper functions for date handling, formatting, calculations
 */

export const OpenInterestUtils = {

    /**
     * Capitalize exchange name (binance -> Binance)
     */
    capitalizeExchange(exchange) {
        if (exchange === 'all_exchange') return 'all_exchange';
        return exchange.charAt(0).toUpperCase() + exchange.slice(1);
    },

    /**
     * Calculate median from array of values
     */
    calculateMedian(values) {
        if (values.length === 0) return 0;
        const sorted = [...values].sort((a, b) => a - b);
        const mid = Math.floor(sorted.length / 2);
        return sorted.length % 2 === 0
            ? (sorted[mid - 1] + sorted[mid]) / 2
            : sorted[mid];
    },

    /**
     * Calculate standard deviation
     */
    calculateStdDev(values) {
        if (values.length === 0) return 0;
        if (values.length === 1) return 0;
        
        const avg = values.reduce((a, b) => a + b, 0) / values.length;
        const squareDiffs = values.map(v => Math.pow(v - avg, 2));
        const avgSquareDiff = squareDiffs.reduce((a, b) => a + b, 0) / squareDiffs.length;
        return Math.sqrt(avgSquareDiff);
    },

    /**
     * Calculate moving average
     */
    calculateMA(values, period) {
        if (values.length === 0) return 0;
        const effectivePeriod = Math.min(period, values.length);
        const slice = values.slice(-effectivePeriod);
        return slice.reduce((a, b) => a + b, 0) / slice.length;
    },

    /**
     * Calculate MA array for all points
     */
    calculateMAArray(values, period) {
        if (values.length === 0) return [];
        
        const effectivePeriod = Math.min(period, values.length);
        
        return values.map((_, i) => {
            if (i < effectivePeriod - 1) {
                // For early points, use available data (expanding window)
                const slice = values.slice(0, i + 1);
                return slice.reduce((a, b) => a + b, 0) / slice.length;
            }
            const slice = values.slice(i - effectivePeriod + 1, i + 1);
            return slice.reduce((a, b) => a + b, 0) / slice.length;
        });
    },

    /**
     * Format Open Interest value for display
     */
    formatOI(value) {
        if (value === null || value === undefined || isNaN(value)) return 'N/A';
        const num = parseFloat(value);
        if (num >= 1e9) {
            return '$' + (num / 1e9).toFixed(2) + 'B';
        } else if (num >= 1e6) {
            return '$' + (num / 1e6).toFixed(2) + 'M';
        } else if (num >= 1e3) {
            return '$' + (num / 1e3).toFixed(2) + 'K';
        }
        return '$' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    /**
     * Format price value
     */
    formatPrice(value) {
        if (value === null || value === undefined || isNaN(value)) return 'N/A';
        const num = parseFloat(value);
        return '$' + num.toLocaleString('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    },

    /**
     * Format change (percentage for Open Interest)
     */
    formatChange(value) {
        if (value === null || value === undefined || isNaN(value)) return 'N/A';
        const sign = value >= 0 ? '+' : '';
        return `${sign}${value.toFixed(2)}%`;
    },

    /**
     * Format date string
     */
    formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    },

};

