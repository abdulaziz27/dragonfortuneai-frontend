/**
 * Long/Short Ratio Controller
 * Handles API consumption and data management for Long/Short Ratio dashboard
 */

class LongShortRatioController {
    constructor() {
        // Get API base URL from meta tag
        const metaTag = document.querySelector('meta[name="api-base-url"]');
        this.baseUrl = metaTag ? metaTag.content : "";

        // Default filters
        this.filters = {
            symbol: "BTCUSDT",
            exchange: "Binance", // Default to Binance
            interval: "1h",
            ratioType: "accounts",
            limit: 2000,
            startTime: null,
            endTime: null,
        };

        // Cache
        this.cache = {
            analytics: null,
            topAccounts: null,
            topPositions: null,
            lastUpdate: null,
        };

        // Charts
        this.charts = {
            mainChart: null,
            areaChart: null,
        };
    }

    /**
     * Build URL with query parameters
     */
    buildUrl(endpoint, params = {}) {
        const url = new URL(`${this.baseUrl}${endpoint}`);

        Object.keys(params).forEach((key) => {
            if (params[key] !== null && params[key] !== undefined) {
                url.searchParams.append(key, params[key]);
            }
        });

        return url.toString();
    }

    /**
     * Fetch analytics data
     */
    async fetchAnalytics() {
        try {
            const url = this.buildUrl("/api/long-short-ratio/analytics", {
                symbol: this.filters.symbol,
                exchange: this.filters.exchange,
                interval: this.filters.interval,
                ratio_type: this.filters.ratioType,
                limit: this.filters.limit,
                start_time: this.filters.startTime,
                end_time: this.filters.endTime,
            });

            console.log("Fetching analytics from:", url);
            const response = await fetch(url);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            this.cache.analytics = data;
            this.cache.lastUpdate = Date.now();

            return data;
        } catch (error) {
            console.error("Error fetching analytics:", error);
            throw error;
        }
    }

    /**
     * Fetch top accounts data
     */
    async fetchTopAccounts() {
        try {
            const url = this.buildUrl("/api/long-short-ratio/top-accounts", {
                symbol: this.filters.symbol,
                exchange: this.filters.exchange,
                interval: this.filters.interval,
                limit: this.filters.limit,
                start_time: this.filters.startTime,
                end_time: this.filters.endTime,
            });

            console.log("Fetching top accounts from:", url);
            const response = await fetch(url);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            this.cache.topAccounts = result.data || [];

            return result.data || [];
        } catch (error) {
            console.error("Error fetching top accounts:", error);
            throw error;
        }
    }

    /**
     * Fetch top positions data
     */
    async fetchTopPositions() {
        try {
            const url = this.buildUrl("/api/long-short-ratio/top-positions", {
                symbol: this.filters.symbol,
                exchange: this.filters.exchange,
                interval: this.filters.interval,
                limit: this.filters.limit,
                start_time: this.filters.startTime,
                end_time: this.filters.endTime,
            });

            console.log("Fetching top positions from:", url);
            const response = await fetch(url);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            this.cache.topPositions = result.data || [];

            return result.data || [];
        } catch (error) {
            console.error("Error fetching top positions:", error);
            throw error;
        }
    }

    /**
     * Fetch all data in parallel
     */
    async fetchAllData() {
        try {
            const [analytics, timeSeriesData] = await Promise.all([
                this.fetchAnalytics(),
                this.filters.ratioType === "accounts"
                    ? this.fetchTopAccounts()
                    : this.fetchTopPositions(),
            ]);

            return {
                analytics,
                timeseries: timeSeriesData,
            };
        } catch (error) {
            console.error("Error fetching all data:", error);
            throw error;
        }
    }

    /**
     * Update filter and refetch data
     */
    updateFilter(key, value) {
        this.filters[key] = value;
        console.log("Filter updated:", key, "=", value);
    }

    /**
     * Format timestamp to readable date
     */
    formatTimestamp(ts, format = "datetime") {
        const date = new Date(ts);

        if (format === "time") {
            return date.toLocaleTimeString("en-US", {
                hour: "2-digit",
                minute: "2-digit",
            });
        } else if (format === "date") {
            return date.toLocaleDateString("en-US", {
                month: "short",
                day: "numeric",
            });
        } else {
            return date.toLocaleString("en-US", {
                month: "short",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        }
    }

    /**
     * Calculate risk level from analytics
     */
    calculateRiskLevel(analytics) {
        if (!analytics || !analytics.ratio_stats) {
            return { level: "Unknown", class: "secondary" };
        }

        const { current, average, std_dev } = analytics.ratio_stats;
        const deviation = Math.abs(current - average) / std_dev;

        if (deviation > 2) {
            return { level: "High", class: "danger" };
        } else if (deviation > 1) {
            return { level: "Medium", class: "warning" };
        } else {
            return { level: "Low", class: "success" };
        }
    }

    /**
     * Get sentiment badge info
     */
    getSentimentBadge(sentiment) {
        const badges = {
            bullish: { text: "Bullish", class: "success", icon: "↑" },
            bearish: { text: "Bearish", class: "danger", icon: "↓" },
            neutral: { text: "Neutral", class: "secondary", icon: "→" },
        };

        return badges[sentiment?.toLowerCase()] || badges["neutral"];
    }

    /**
     * Get trend badge info
     */
    getTrendBadge(direction, change) {
        if (direction === "increasing") {
            return {
                icon: "↑",
                class: "success",
                text: `+${change?.toFixed(2)}%`,
            };
        } else if (direction === "decreasing") {
            return {
                icon: "↓",
                class: "danger",
                text: `${change?.toFixed(2)}%`,
            };
        } else {
            return {
                icon: "→",
                class: "secondary",
                text: "0.00%",
            };
        }
    }

    /**
     * Create or update main ratio chart
     */
    createMainChart(canvasId, data) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.error("Canvas not found:", canvasId);
            return;
        }

        const ctx = canvas.getContext("2d");

        // Destroy existing chart
        if (this.charts.mainChart) {
            this.charts.mainChart.destroy();
        }

        // Prepare data
        const labels = data.map((d) => this.formatTimestamp(d.ts, "time"));
        const ratios = data.map((d) =>
            this.filters.ratioType === "accounts"
                ? d.ls_ratio_accounts
                : d.ls_ratio_positions
        );

        // Neutral line data
        const neutralLine = new Array(data.length).fill(1.0);

        // Create chart
        this.charts.mainChart = new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: `${this.filters.symbol} L/S Ratio (${this.filters.ratioType})`,
                        data: ratios,
                        borderColor: "rgb(59, 130, 246)",
                        backgroundColor: "rgba(59, 130, 246, 0.1)",
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6,
                    },
                    {
                        label: "Neutral Line (1.0)",
                        data: neutralLine,
                        borderColor: "rgba(156, 163, 175, 0.5)",
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: false,
                        pointRadius: 0,
                        pointHoverRadius: 0,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: "index",
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: "top",
                        labels: {
                            color: getComputedStyle(
                                document.documentElement
                            ).getPropertyValue("--foreground"),
                            usePointStyle: true,
                            padding: 15,
                        },
                    },
                    tooltip: {
                        backgroundColor: "rgba(0, 0, 0, 0.8)",
                        titleColor: "#fff",
                        bodyColor: "#fff",
                        borderColor: "rgba(255, 255, 255, 0.1)",
                        borderWidth: 1,
                        padding: 12,
                        displayColors: true,
                        callbacks: {
                            label: function (context) {
                                let label = context.dataset.label || "";
                                if (label) {
                                    label += ": ";
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y.toFixed(3);
                                }
                                return label;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        ticks: {
                            color: getComputedStyle(
                                document.documentElement
                            ).getPropertyValue("--muted-foreground"),
                            maxTicksLimit: 12,
                        },
                        grid: {
                            color: getComputedStyle(
                                document.documentElement
                            ).getPropertyValue("--border"),
                            display: false,
                        },
                    },
                    y: {
                        ticks: {
                            color: getComputedStyle(
                                document.documentElement
                            ).getPropertyValue("--muted-foreground"),
                            callback: function (value) {
                                return value.toFixed(2);
                            },
                        },
                        grid: {
                            color: getComputedStyle(
                                document.documentElement
                            ).getPropertyValue("--border"),
                        },
                    },
                },
            },
        });
    }

    /**
     * Create or update area chart (Long/Short distribution)
     */
    createAreaChart(canvasId, data) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.error("Canvas not found:", canvasId);
            return;
        }

        const ctx = canvas.getContext("2d");

        // Destroy existing chart
        if (this.charts.areaChart) {
            this.charts.areaChart.destroy();
        }

        // Prepare data
        const labels = data.map((d) => this.formatTimestamp(d.ts, "time"));
        const longData = data.map((d) =>
            this.filters.ratioType === "accounts"
                ? d.long_accounts
                : d.long_positions_percent
        );
        const shortData = data.map((d) =>
            this.filters.ratioType === "accounts"
                ? d.short_accounts
                : d.short_positions_percent
        );

        // Create chart
        this.charts.areaChart = new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Long %",
                        data: longData,
                        borderColor: "rgb(34, 197, 94)",
                        backgroundColor: "rgba(34, 197, 94, 0.3)",
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6,
                    },
                    {
                        label: "Short %",
                        data: shortData,
                        borderColor: "rgb(239, 68, 68)",
                        backgroundColor: "rgba(239, 68, 68, 0.3)",
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: "index",
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: "top",
                        labels: {
                            color: getComputedStyle(
                                document.documentElement
                            ).getPropertyValue("--foreground"),
                            usePointStyle: true,
                            padding: 15,
                        },
                    },
                    tooltip: {
                        backgroundColor: "rgba(0, 0, 0, 0.8)",
                        callbacks: {
                            label: function (context) {
                                let label = context.dataset.label || "";
                                if (label) {
                                    label += ": ";
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y.toFixed(2) + "%";
                                }
                                return label;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        ticks: {
                            color: getComputedStyle(
                                document.documentElement
                            ).getPropertyValue("--muted-foreground"),
                            maxTicksLimit: 12,
                        },
                        grid: {
                            display: false,
                        },
                    },
                    y: {
                        min: 0,
                        max: 100,
                        ticks: {
                            color: getComputedStyle(
                                document.documentElement
                            ).getPropertyValue("--muted-foreground"),
                            callback: function (value) {
                                return value + "%";
                            },
                        },
                        grid: {
                            color: getComputedStyle(
                                document.documentElement
                            ).getPropertyValue("--border"),
                        },
                    },
                },
            },
        });
    }

    /**
     * Destroy all charts
     */
    destroyCharts() {
        if (this.charts.mainChart) {
            this.charts.mainChart.destroy();
            this.charts.mainChart = null;
        }
        if (this.charts.areaChart) {
            this.charts.areaChart.destroy();
            this.charts.areaChart = null;
        }
    }
}

// Export for global use
window.LongShortRatioController = LongShortRatioController;

// Log when controller is loaded
console.log("✅ Long/Short Ratio Controller loaded");
