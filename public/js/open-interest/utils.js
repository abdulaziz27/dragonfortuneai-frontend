/**
 * Open Interest Utilities
 * Helper functions for formatting, calculations, and date handling
 */

export const OpenInterestUtils = {
    /**
     * Format Open Interest value (1.5B, 800M, etc)
     */
    formatOI(value) {
        if (value === null || value === undefined || isNaN(value)) return 'N/A';
        const num = parseFloat(value);
        
        if (num >= 1e9) {
            return `$${(num / 1e9).toFixed(2)}B`;
        } else if (num >= 1e6) {
            return `$${(num / 1e6).toFixed(2)}M`;
        } else if (num >= 1e3) {
            return `$${(num / 1e3).toFixed(2)}K`;
        }
        return `$${num.toFixed(2)}`;
    },

    /**
     * Format price value in USD
     */
    formatPrice(value) {
        if (value === null || value === undefined || isNaN(value)) return 'N/A';
        const num = parseFloat(value);
        
        if (num >= 1e6) {
            return `$${(num / 1e6).toFixed(2)}M`;
        } else if (num >= 1e3) {
            return `$${(num / 1e3).toFixed(2)}K`;
        }
        return `$${num.toFixed(2)}`;
    },

    /**
     * Format change percentage
     */
    formatChange(value) {
        if (value === null || value === undefined || isNaN(value)) return 'N/A';
        const num = parseFloat(value);
        const sign = num >= 0 ? '+' : '';
        return `${sign}${num.toFixed(2)}%`;
    },

    /**
     * Format volume numbers
     */
    formatVolume(value) {
        if (value === null || value === undefined || isNaN(value)) return 'N/A';
        const num = parseFloat(value);
        
        if (num >= 1e9) {
            return `${(num / 1e9).toFixed(2)}B`;
        } else if (num >= 1e6) {
            return `${(num / 1e6).toFixed(2)}M`;
        } else if (num >= 1e3) {
            return `${(num / 1e3).toFixed(2)}K`;
        }
        return num.toFixed(2);
    },

    /**
     * Get badge class for trend
     */
    getTrendBadgeClass(trend) {
        if (!trend) return 'text-bg-secondary';
        const trendLower = trend.toLowerCase();
        
        if (trendLower === 'increasing' || trendLower === 'bullish') {
            return 'text-bg-success';
        } else if (trendLower === 'decreasing' || trendLower === 'bearish') {
            return 'text-bg-danger';
        }
        return 'text-bg-secondary';
    },

    /**
     * Get color class for trend
     */
    getTrendColorClass(trend) {
        if (!trend) return 'text-secondary';
        const trendLower = trend.toLowerCase();
        
        if (trendLower === 'increasing' || trendLower === 'bullish') {
            return 'text-success';
        } else if (trendLower === 'decreasing' || trendLower === 'bearish') {
            return 'text-danger';
        }
        return 'text-secondary';
    },

    /**
     * Get badge class for volatility level
     */
    getVolatilityBadgeClass(level) {
        if (!level) return 'text-bg-secondary';
        const levelLower = level.toLowerCase();
        
        if (levelLower === 'high') {
            return 'text-bg-danger';
        } else if (levelLower === 'moderate') {
            return 'text-bg-warning';
        } else if (levelLower === 'low') {
            return 'text-bg-success';
        }
        return 'text-bg-secondary';
    },

    /**
     * Calculate API limit based on date range and interval
     */
    calculateLimit(days, interval) {
        // Fixed limit for date range filtering
        return 5000;
    },

    /**
     * Get date range object (startDate, endDate) from period
     */
    getDateRange(period, timeRanges) {
        const now = new Date();
        let startDate, endDate;

        if (period === 'all') {
            // All time: 2 years ago
            startDate = new Date(now.getTime() - (730 * 24 * 60 * 60 * 1000));
            endDate = now;
        } else {
            const range = timeRanges.find(r => r.value === period);
            const days = range ? range.days : 1;
            startDate = new Date(now.getTime() - (days * 24 * 60 * 60 * 1000));
            endDate = now;
        }

        return { startDate, endDate };
    },

    /**
     * Get time range in milliseconds
     */
    getTimeRange(period, timeRanges) {
        const range = timeRanges.find(r => r.value === period);
        if (!range) return 24 * 60 * 60 * 1000; // Default 1 day
        
        if (period === 'all') {
            return 730 * 24 * 60 * 60 * 1000; // 2 years
        }
        
        return range.days * 24 * 60 * 60 * 1000;
    }
};

