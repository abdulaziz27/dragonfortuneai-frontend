/**
 * Basis & Term Structure Utilities
 * Helper functions for formatting and calculations
 */

export const BasisUtils = {
    /**
     * Format basis absolute value
     */
    formatBasis(value) {
        if (value === null || value === undefined || isNaN(value)) return '--';
        return parseFloat(value).toFixed(2);
    },

    /**
     * Format basis annualized (percentage)
     */
    formatBasisAnnualized(value) {
        if (value === null || value === undefined || isNaN(value)) return '--';
        const percentage = parseFloat(value);
        return percentage.toFixed(2) + '%';
    },

    /**
     * Format price in USD
     */
    formatPrice(value) {
        if (value === null || value === undefined || isNaN(value)) return '--';
        return '$' + parseFloat(value).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    },

    /**
     * Calculate standard deviation
     */
    calculateStdDev(values) {
        if (!values || values.length === 0) return 0;
        const mean = values.reduce((a, b) => a + b, 0) / values.length;
        const squaredDiffs = values.map(v => Math.pow(v - mean, 2));
        const avgSquaredDiff = squaredDiffs.reduce((a, b) => a + b, 0) / values.length;
        return Math.sqrt(avgSquaredDiff);
    },

    /**
     * Calculate limit from date range and interval
     * Same pattern as funding-rate and perp-quarterly
     */
    calculateLimit(days, interval) {
        if (!days || days === null) {
            // For 'ALL' period, use maximum limit
            return 5000;
        }

        const intervalHours = {
            '5m': 5 / 60,
            '15m': 15 / 60,
            '1h': 1,
            '4h': 4
        };

        const hours = intervalHours[interval] || 1;
        const exactRecordsNeeded = Math.ceil((days * 24) / hours);
        
        // Dynamic buffer multiplier based on interval
        let bufferMultiplier = 1.5;
        if (interval === '5m') bufferMultiplier = 2.0;
        else if (interval === '15m') bufferMultiplier = 1.8;
        
        let calculatedLimit = Math.ceil(exactRecordsNeeded * bufferMultiplier);
        const maxLimit = 5000;
        return Math.min(calculatedLimit, maxLimit);
    },

    /**
     * Format market structure (contango, backwardation, etc.)
     */
    formatMarketStructure(value) {
        if (!value) return '--';
        // Format: "strong_contango" → "Strong Contango"
        return value
            .split('_')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    },

    /**
     * Format trend (increasing, decreasing, stable)
     */
    formatTrend(value) {
        if (!value) return '--';
        return value.charAt(0).toUpperCase() + value.slice(1);
    }
};

