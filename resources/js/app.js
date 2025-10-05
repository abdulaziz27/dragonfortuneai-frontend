import "./bootstrap";
import "bootstrap/dist/js/bootstrap.bundle.min.js";

import jQuery from "jquery";
window.$ = window.jQuery = jQuery;

import Alpine from "alpinejs";
window.Alpine = Alpine;

// Alpine.js data and components
document.addEventListener("alpine:init", () => {
    Alpine.data("sidebar", () => ({
        open: true,
        collapsed: false,

        toggle() {
            this.open = !this.open;
        },

        toggleCollapse() {
            this.collapsed = !this.collapsed;
        },
    }));

    Alpine.data("theme", () => ({
        dark: true,

        init() {
            // Check for saved theme preference or default to dark
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
        },

        applyTheme() {
            if (this.dark) {
                document.documentElement.classList.add("dark");
            } else {
                document.documentElement.classList.remove("dark");
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

Alpine.start();

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
