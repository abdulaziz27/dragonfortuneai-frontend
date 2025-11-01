/**
 * Funding Rate Utilities
 * Helper functions for date handling, formatting, calculations
 */

export const FundingRateUtils = {
    /**
     * Calculate optimal limit based on date range and interval
     * 
     * Account for client-side filter loss (backend returns mixed margin_type data)
     * Buffer: 50% extra to ensure we get enough data after filtering
     * 
     * @param {number} days - Date range in days
     * @param {string} interval - Interval (1m, 1h, 8h)
     * @returns {number} - Limit for API request
     */
    calculateLimit(days, interval) {
        const intervalHours = {
            '1m': 1 / 60,  // 1 minute = 1/60 hours
            '1h': 1,
            '8h': 8
            // Commented for future use:
            // '4h': 4,
            // '1d': 24,
            // '1w': 168
        };
        
        const hours = intervalHours[interval] || 8;
        
        // Calculate exact records needed
        const exactRecordsNeeded = Math.ceil((days * 24) / hours);
        
        // Dynamic buffer based on interval to account for client-side filter loss
        // Backend returns mixed margin_type (e.g., 8h + 1h), filter removes non-matching
        // Larger intervals (8h, 1d, 1w) have worse ratio because 1h data dominates
        let bufferMultiplier;
        if (hours >= 24) {
            // Daily or weekly intervals - backend has very low ratio (~5-10% matching)
            // Need 10-20x buffer to get enough data
            bufferMultiplier = 20;
        } else if (hours >= 8) {
            // 8h interval - backend has low ratio (~10-15% matching)
            // Need 8-10x buffer
            bufferMultiplier = 10;
        } else if (hours >= 4) {
            // 4h interval - moderate ratio (~20-30% matching)
            // Need 4-5x buffer
            bufferMultiplier = 5;
        } else {
            // 1h interval - good ratio (~70-80% matching)
            // Need 1.5x buffer
            bufferMultiplier = 1.5;
        }
        
        const limitWithBuffer = Math.ceil(exactRecordsNeeded * bufferMultiplier);
        
        // Cap based on reasonable limits per use case
        let maxLimit;
        if (days <= 7) {
            // Small ranges (1D, 7D) - allow up to 2000
            maxLimit = 2000;
        } else if (days <= 90) {
            // Medium ranges (1M) - allow up to 3000
            maxLimit = 3000;
        } else {
            // Large ranges (YTD, 1Y, ALL) - allow up to 5000
            maxLimit = 5000;
        }
        
        const finalLimit = Math.min(limitWithBuffer, maxLimit);
        
        console.log(`📊 Limit Calculation:`, {
            days,
            interval,
            exactNeeded: exactRecordsNeeded,
            bufferMultiplier: `${bufferMultiplier}x`,
            withBuffer: limitWithBuffer,
            finalLimit: finalLimit,
            reason: hours >= 8 ? 'Large interval - backend has low matching ratio' : 'Small interval - buffer for filter loss'
        });
        
        return finalLimit;
    },

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
     * Format funding rate for display
     */
    formatFundingRate(value) {
        if (value === null || value === undefined || isNaN(value)) return 'N/A';
        const num = parseFloat(value);
        return num.toFixed(4) + '%';
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
     * Format change (basis points for funding rate)
     */
    formatChange(value) {
        if (value === null || value === undefined || isNaN(value)) return 'N/A';
        const sign = value >= 0 ? '+' : '';
        return `${sign}${value.toFixed(1)} bps`;
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

    /**
     * Format Z-Score for display
     */
    formatZScore(value) {
        if (value === null || value === undefined || isNaN(value)) return 'N/A';
        const num = parseFloat(value);
        const sign = num >= 0 ? '+' : '';
        return `${sign}${num.toFixed(2)}σ`;
    },

    /**
     * Create histogram bins
     */
    createHistogramBins(values, binCount) {
        if (!values || values.length === 0) {
            console.warn('No values provided for histogram bins');
            return [];
        }

        const min = Math.min(...values);
        const max = Math.max(...values);

        // Handle case where all values are the same
        if (min === max) {
            return [{
                min: min,
                max: max,
                count: values.length,
                label: this.formatFundingRate(min)
            }];
        }

        const binSize = (max - min) / binCount;

        if (binSize <= 0) {
            return [{
                min: min,
                max: max,
                count: values.length,
                label: this.formatFundingRate(min)
            }];
        }

        const bins = Array.from({ length: binCount }, (_, i) => ({
            min: min + (i * binSize),
            max: min + ((i + 1) * binSize),
            count: 0,
            label: ''
        }));

        values.forEach(v => {
            const binIndex = Math.min(
                Math.floor((v - min) / binSize),
                binCount - 1
            );
            if (bins[binIndex]) {
                bins[binIndex].count++;
            }
        });

        bins.forEach(bin => {
            if (bin) {
                bin.label = this.formatFundingRate(bin.min);
            }
        });

        return bins;
    }
};

