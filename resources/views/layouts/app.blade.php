<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'DragonFortune AI') }}</title>

    <meta name="api-base-url" content="{{ config('services.api.base_url') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body>
    <div class="df-layout" x-data="{
        sidebarOpen: window.innerWidth >= 768,
        sidebarCollapsed: false,
        openSubmenus: {},
        profileDropdownOpen: false,
        isMobile: window.innerWidth < 768,

        init() {
            // Handle window resize
            window.addEventListener('resize', () => {
                this.isMobile = window.innerWidth < 768;
                if (!this.isMobile) {
                    this.sidebarOpen = true;
                    document.body.classList.remove('sidebar-open');
                } else {
                    this.sidebarOpen = false;
                    document.body.classList.remove('sidebar-open');
                }
            });

            // Watch for sidebar state changes
            this.$watch('sidebarOpen', (value) => {
                if (this.isMobile) {
                    if (value) {
                        document.body.classList.add('sidebar-open');
                    } else {
                        document.body.classList.remove('sidebar-open');
                    }
                }
            });
        },

        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
        },

        closeSidebar() {
            if (this.isMobile) {
                this.sidebarOpen = false;
            }
        },

        toggleSubmenu(menuId) {
            this.openSubmenus[menuId] = !this.openSubmenus[menuId];
        }
    }" @theme-toggle.window="document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');">

        <!-- Mobile Overlay -->
        <div class="mobile-overlay d-md-none"
             :class="{ 'show': sidebarOpen && isMobile }"
             @click="closeSidebar()">
        </div>

        <!-- Sidebar -->
        <aside class="df-sidebar"
               :class="{
                   'collapsed': sidebarCollapsed && !isMobile,
                   'mobile-open': sidebarOpen && isMobile
               }"
               x-show="sidebarOpen || isMobile">

            <!-- Sidebar Header -->
            <div class="df-sidebar-header">
                <div class="df-sidebar-menu">
                    <div class="df-sidebar-menu-item">
                        <button class="df-sidebar-menu-button df-sidebar-menu-button-lg">
                            <div class="bg-primary rounded d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 3v18h18"/>
                                    <path d="M7 12l3-3 3 3 5-5"/>
                                </svg>
                            </div>
                            <div class="d-flex flex-column text-start flex-grow-1" x-show="!sidebarCollapsed">
                                <span class="fw-semibold" style="font-size: 1rem;">Dragon Fortune</span>
                            </div>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ms-auto" x-show="!sidebarCollapsed">
                                <path d="M7 13l3 3 7-7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sidebar Content -->
            <div class="df-sidebar-content df-scrollbar">
                <!-- Navigation Section -->
                <div class="df-sidebar-group">
                    <div class="df-sidebar-group-label" x-show="!sidebarCollapsed">Navigation</div>
                    <ul class="df-sidebar-menu">
                        <li class="df-sidebar-menu-item">
                            <a href="/" class="df-sidebar-menu-button {{ request()->routeIs('workspace') ? 'active' : '' }}" @click="closeSidebar()">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="7" height="7"/>
                                    <rect x="14" y="3" width="7" height="7"/>
                                    <rect x="14" y="14" width="7" height="7"/>
                                    <rect x="3" y="14" width="7" height="7"/>
                                </svg>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="df-sidebar-menu-item">
                            <button class="df-sidebar-menu-button" @click="toggleSubmenu('derivatives')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    <path d="M8 12h8"/>
                                    <path d="M12 8v8"/>
                                </svg>
                                <span>Derivatives Core</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ms-auto" :class="{ 'rotate-90': openSubmenus['derivatives'] }">
                                    <path d="M9 18l6-6-6-6"/>
                                </svg>
                            </button>
                            <div class="df-submenu{{ request()->routeIs('derivatives.*') ? ' show' : '' }}" :class="{ 'show': openSubmenus['derivatives'] }">
                                <a href="/derivatives/funding-rate" class="df-submenu-item {{ request()->routeIs('derivatives.funding-rate') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">Funding Rate</a>
                                <a href="/derivatives/open-interest" class="df-submenu-item {{ request()->routeIs('derivatives.open-interest') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">Open Interest</a>
                                <a href="/derivatives/long-short-ratio" class="df-submenu-item {{ request()->routeIs('derivatives.long-short-ratio') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">Long/Short Ratio</a>
                                <a href="/derivatives/liquidations" class="df-submenu-item {{ request()->routeIs('derivatives.liquidations') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">Liquidations</a>
                                <a href="/derivatives/volume-change" class="df-submenu-item {{ request()->routeIs('derivatives.volume-change') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">Volume + Change</a>
                                <a href="/derivatives/delta-long-short" class="df-submenu-item {{ request()->routeIs('derivatives.delta-long-short') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">Delta Long vs Short</a>
                            </div>
                        </li>
                        <li class="df-sidebar-menu-item">
                            <button class="df-sidebar-menu-button" @click="toggleSubmenu('spot-microstructure')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 3v18h18"/>
                                    <path d="M7 12l3-3 3 3 5-5"/>
                                    <circle cx="12" cy="12" r="3"/>
                                    <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"/>
                                </svg>
                                <span>Spot Microstructure</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ms-auto" :class="{ 'rotate-90': openSubmenus['spot-microstructure'] }">
                                    <path d="M9 18l6-6-6-6"/>
                                </svg>
                            </button>
                            <div class="df-submenu{{ request()->routeIs('spot-microstructure.*') ? ' show' : '' }}" :class="{ 'show': openSubmenus['spot-microstructure'] }">
                                <a href="/spot-microstructure/cvd" class="df-submenu-item {{ request()->routeIs('spot-microstructure.cvd') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">CVD Analysis</a>
                                <a href="/spot-microstructure/orderbook-depth" class="df-submenu-item {{ request()->routeIs('spot-microstructure.orderbook-depth') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">Orderbook Depth</a>
                                <a href="/spot-microstructure/absorption" class="df-submenu-item {{ request()->routeIs('spot-microstructure.absorption') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">Absorption</a>
                                <a href="/spot-microstructure/spoofing" class="df-submenu-item {{ request()->routeIs('spot-microstructure.spoofing') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">Spoofing Detection</a>
                                <a href="/spot-microstructure/vwap" class="df-submenu-item {{ request()->routeIs('spot-microstructure.vwap') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">VWAP + Bands</a>
                                <a href="/spot-microstructure/liquidity-cluster" class="df-submenu-item {{ request()->routeIs('spot-microstructure.liquidity-cluster') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">Liquidity Cluster</a>
                            </div>
                        </li>
                        <li class="df-sidebar-menu-item">
                            <button class="df-sidebar-menu-button" @click="toggleSubmenu('onchain-metrics')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    <path d="M8 12h8"/>
                                    <path d="M12 8v8"/>
                                </svg>
                                <span>On‑Chain Metrics</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ms-auto" :class="{ 'rotate-90': openSubmenus['onchain-metrics'] }">
                                    <path d="M9 18l6-6-6-6"/>
                                </svg>
                            </button>
                            <div class="df-submenu{{ request()->routeIs('onchain-metrics.*') ? ' show' : '' }}" :class="{ 'show': openSubmenus['onchain-metrics'] }">
                                <a href="/onchain-metrics/exchange-netflow" class="df-submenu-item {{ request()->routeIs('onchain-metrics.exchange-netflow') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">Exchange Netflow (BTC in‑out)</a>
                                <a href="/onchain-metrics/whale-activity" class="df-submenu-item {{ request()->routeIs('onchain-metrics.whale-activity') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">Whale Wallet Activity</a>
                                <a href="/onchain-metrics/stablecoin-supply" class="df-submenu-item {{ request()->routeIs('onchain-metrics.stablecoin-supply') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">Stablecoin Supply / Netflow</a>
                                <a href="/onchain-metrics/miner-flow" class="df-submenu-item {{ request()->routeIs('onchain-metrics.miner-flow') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">Miner Flow</a>
                            </div>
                        </li>
                        <li class="df-sidebar-menu-item">
                            <button class="df-sidebar-menu-button" @click="toggleSubmenu('options-metrics')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    <path d="M8 12h8"/>
                                    <path d="M12 8v8"/>
                                </svg>
                                <span>Options Metrics</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ms-auto" :class="{ 'rotate-90': openSubmenus['options-metrics'] }">
                                    <path d="M9 18l6-6-6-6"/>
                                </svg>
                            </button>
                            <div class="df-submenu{{ request()->routeIs('options-metrics.*') ? ' show' : '' }}" :class="{ 'show': openSubmenus['options-metrics'] }">
                                <a href="/options-metrics/implied-volatility" class="df-submenu-item {{ request()->routeIs('options-metrics.implied-volatility') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">Implied Volatility (IV)</a>
                                <a href="/options-metrics/put-call-ratio" class="df-submenu-item {{ request()->routeIs('options-metrics.put-call-ratio') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">Put/Call Ratio</a>
                                <a href="/options-metrics/options-skew" class="df-submenu-item {{ request()->routeIs('options-metrics.options-skew') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">Options Skew (25d RR)</a>
                                <a href="/options-metrics/gamma-exposure" class="df-submenu-item {{ request()->routeIs('options-metrics.gamma-exposure') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">Gamma Exposure (GEX)</a>
                            </div>
                        </li>
                        <li class="df-sidebar-menu-item">
                            <button class="df-sidebar-menu-button" @click="toggleSubmenu('etf-basis')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    <path d="M8 12h8"/>
                                    <path d="M12 8v8"/>
                                </svg>
                                <span>ETF & Basis</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ms-auto" :class="{ 'rotate-90': openSubmenus['etf-basis'] }">
                                    <path d="M9 18l6-6-6-6"/>
                                </svg>
                            </button>
                            <div class="df-submenu{{ request()->routeIs('etf-basis.*') ? ' show' : '' }}" :class="{ 'show': openSubmenus['etf-basis'] }">
                                <a href="/etf-basis/spot-etf-netflow" class="df-submenu-item {{ request()->routeIs('etf-basis.spot-etf-netflow') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">Spot BTC ETF Netflow (daily)</a>
                                <a href="/etf-basis/perp-basis" class="df-submenu-item {{ request()->routeIs('etf-basis.perp-basis') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">Perp Basis vs Spot Index</a>
                            </div>
                        </li>
                        <li class="df-sidebar-menu-item">
                            <button class="df-sidebar-menu-button" @click="toggleSubmenu('volatility-regime')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    <path d="M8 12h8"/>
                                    <path d="M12 8v8"/>
                                </svg>
                                <span>Volatility & Regime</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ms-auto" :class="{ 'rotate-90': openSubmenus['volatility-regime'] }">
                                    <path d="M9 18l6-6-6-6"/>
                                </svg>
                            </button>
                            <div class="df-submenu{{ request()->routeIs('volatility-regime.*') ? ' show' : '' }}" :class="{ 'show': openSubmenus['volatility-regime'] }">
                                <a href="/volatility-regime/detector" class="df-submenu-item {{ request()->routeIs('volatility-regime.detector') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">σ pendek vs σ panjang</a>
                            </div>
                        </li>
                        <li class="df-sidebar-menu-item">
                            <button class="df-sidebar-menu-button" @click="toggleSubmenu('macro-overlay')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M2 12h20"/>
                                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                                </svg>
                                <span>Macro Overlay</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ms-auto" :class="{ 'rotate-90': openSubmenus['macro-overlay'] }">
                                    <path d="M9 18l6-6-6-6"/>
                                </svg>
                            </button>
                            <div class="df-submenu{{ request()->routeIs('macro-overlay.*') ? ' show' : '' }}" :class="{ 'show': openSubmenus['macro-overlay'] }">
                                <a href="/macro-overlay/dashboard" class="df-submenu-item {{ request()->routeIs('macro-overlay.dashboard') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">DXY, Yields, Fed & Liquidity</a>
                            </div>
                        </li>
                        <li class="df-sidebar-menu-item">
                            <button class="df-sidebar-menu-button" @click="toggleSubmenu('sentiment-flow')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                    <path d="M8 10h.01M12 10h.01M16 10h.01"/>
                                </svg>
                                <span>Sentiment & Flow</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ms-auto" :class="{ 'rotate-90': openSubmenus['sentiment-flow'] }">
                                    <path d="M9 18l6-6-6-6"/>
                                </svg>
                            </button>
                            <div class="df-submenu{{ request()->routeIs('sentiment-flow.*') ? ' show' : '' }}" :class="{ 'show': openSubmenus['sentiment-flow'] }">
                                <a href="/sentiment-flow/dashboard" class="df-submenu-item {{ request()->routeIs('sentiment-flow.dashboard') ? 'active' : '' }}" style="color: var(--foreground);" @click="closeSidebar()">Fear & Greed, Social & Whales</a>
                            </div>
                        </li>
                        {{-- <li class="df-sidebar-menu-item">
                            <button class="df-sidebar-menu-button" @click="toggleSubmenu('watchlists')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="7" height="7"/>
                                    <rect x="14" y="3" width="7" height="7"/>
                                    <rect x="14" y="14" width="7" height="7"/>
                                    <rect x="3" y="14" width="7" height="7"/>
                                </svg>
                                <span>Watchlists</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ms-auto" :class="{ 'rotate-90': openSubmenus['watchlists'] }">
                                    <path d="M9 18l6-6-6-6"/>
                                </svg>
                            </button>
                            <div class="df-submenu" :class="{ 'show': openSubmenus['watchlists'] }">
                                <a href="#" class="df-submenu-item" style="color: var(--foreground);">Crypto</a>
                                <a href="#" class="df-submenu-item" style="color: var(--foreground);">Stocks</a>
                                <a href="#" class="df-submenu-item" style="color: var(--foreground);">Forex</a>
                            </div>
                        </li> --}}
                        {{-- <li class="df-sidebar-menu-item">
                            <button class="df-sidebar-menu-button" @click="toggleSubmenu('screeners')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 6h18"/>
                                    <path d="M3 12h18"/>
                                    <path d="M3 18h18"/>
                                </svg>
                                <span>Screeners</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ms-auto" :class="{ 'rotate-90': openSubmenus['screeners'] }">
                                    <path d="M9 18l6-6-6-6"/>
                                </svg>
                            </button>
                            <div class="df-submenu" :class="{ 'show': openSubmenus['screeners'] }">
                                <a href="#" class="df-submenu-item" style="color: var(--foreground);">Crypto Screener</a>
                                <a href="#" class="df-submenu-item" style="color: var(--foreground);">Stock Screener</a>
                                <a href="#" class="df-submenu-item" style="color: var(--foreground);">Strategy Builder</a>
                            </div>
                        </li> --}}
                        {{-- <li class="df-sidebar-menu-item">
                            <button class="df-sidebar-menu-button" @click="toggleSubmenu('activity')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                                </svg>
                                <span>Activity</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ms-auto" :class="{ 'rotate-90': openSubmenus['activity'] }">
                                    <path d="M9 18l6-6-6-6"/>
                                </svg>
                            </button>
                            <div class="df-submenu" :class="{ 'show': openSubmenus['activity'] }">
                                <a href="#" class="df-submenu-item" style="color: var(--foreground);">Alerts</a>
                                <a href="#" class="df-submenu-item" style="color: var(--foreground);">Orders</a>
                            </div>
                        </li> --}}
                        {{-- <li class="df-sidebar-menu-item">
                            <button class="df-sidebar-menu-button" @click="toggleSubmenu('settings')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="3"/>
                                    <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"/>
                                </svg>
                                <span>Settings</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ms-auto" :class="{ 'rotate-90': openSubmenus['settings'] }">
                                    <path d="M9 18l6-6-6-6"/>
                                </svg>
                            </button>
                            <div class="df-submenu" :class="{ 'show': openSubmenus['settings'] }">
                                <a href="#" class="df-submenu-item" style="color: var(--foreground);">Account</a>
                                <a href="#" class="df-submenu-item" style="color: var(--foreground);">Appearance</a>
                                <a href="#" class="df-submenu-item" style="color: var(--foreground);">Billing</a>
                            </div>
                        </li> --}}
                    </ul>
                </div>

                {{-- <!-- Watchlist Section -->
                <div class="df-sidebar-group">
                    <div class="df-sidebar-group-label" x-show="!sidebarCollapsed">Watchlist</div>
                    <ul class="df-sidebar-menu">
                        <li class="df-sidebar-menu-item">
                            <a href="#" class="df-sidebar-menu-button">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 3v18h18"/>
                                    <path d="M7 12l3-3 3 3 5-5"/>
                                </svg>
                                <span>BTC · Binance</span>
                            </a>
                        </li>
                        <li class="df-sidebar-menu-item">
                            <a href="#" class="df-sidebar-menu-button">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 3v18h18"/>
                                    <path d="M7 12l3-3 3 3 5-5"/>
                                </svg>
                                <span>ETH · Coinbase</span>
                            </a>
                        </li>
                        <li class="df-sidebar-menu-item">
                            <a href="#" class="df-sidebar-menu-button">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 3v18h18"/>
                                    <path d="M7 12l3-3 3 3 5-5"/>
                                </svg>
                                <span>NASDAQ Futures</span>
                            </a>
                        </li>
                        <li class="df-sidebar-menu-item">
                            <button class="df-sidebar-menu-button text-muted">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="1"/>
                                    <circle cx="19" cy="12" r="1"/>
                                    <circle cx="5" cy="12" r="1"/>
                                </svg>
                                <span>Manage lists</span>
                            </button>
                        </li>
                    </ul>
                </div> --}}
            </div>

            <!-- Sidebar Footer -->
            <div class="df-sidebar-footer">
                <div class="df-sidebar-menu">
                    <div class="df-sidebar-menu-item position-relative">
                        <button class="df-sidebar-menu-button df-sidebar-menu-button-lg" @click="profileDropdownOpen = !profileDropdownOpen">
                            <div class="bg-secondary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 50%; overflow: hidden;">
                                <span class="fw-semibold small text-dark" style="line-height: 1;">AA</span>
                            </div>
                            <div class="d-flex flex-column text-start flex-grow-1" x-show="!sidebarCollapsed">
                                <span class="fw-semibold small">Abdul Aziz</span>
                                <span class="small" style="color: var(--muted-foreground);">abdulaziz@dragonfortune.ai</span>
                            </div>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ms-auto" x-show="!sidebarCollapsed">
                                <circle cx="12" cy="12" r="1"/>
                                <circle cx="19" cy="12" r="1"/>
                                <circle cx="5" cy="12" r="1"/>
                            </svg>
                        </button>

                        <!-- Profile Dropdown -->
                        <div class="df-profile-dropdown" x-show="profileDropdownOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" @click.away="profileDropdownOpen = false">
                            <!-- Profile Header -->
                            <div class="df-profile-dropdown-header">
                                <div class="bg-secondary d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden;">
                                    <span class="fw-semibold text-dark" style="line-height: 1;">AA</span>
                                </div>
                                <div>
                                    <div class="fw-semibold">Abdul Aziz</div>
                                    <div class="small" style="color: var(--muted-foreground);">abdulaziz@dragonfortune.ai</div>
                                </div>
                            </div>

                            <!-- Dropdown Menu -->
                            <div class="df-profile-dropdown-menu">
                                <a href="#" class="df-profile-dropdown-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    Account
                                </a>
                                <a href="#" class="df-profile-dropdown-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                        <polyline points="16,17 21,12 16,7"/>
                                        <line x1="21" y1="12" x2="9" y2="12"/>
                                    </svg>
                                    Log out
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="df-sidebar-inset">
            <!-- Toolbar -->
            <header class="df-toolbar">
                <div class="d-flex align-items-center gap-3">
                    <!-- Mobile Sidebar Toggle -->
                    <button class="btn-df-ghost d-md-none" @click="toggleSidebar()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="6" width="18" height="2"/>
                            <rect x="3" y="11" width="18" height="2"/>
                            <rect x="3" y="16" width="18" height="2"/>
                        </svg>
                    </button>

        <!-- Desktop Sidebar Toggle -->
        <button class="btn-df-ghost d-none d-md-block" @click="sidebarCollapsed = !sidebarCollapsed; openSubmenus = {}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="6" width="18" height="2"/>
                            <rect x="3" y="11" width="18" height="2"/>
                            <rect x="3" y="16" width="18" height="2"/>
                        </svg>
                    </button>

                    <div class="d-flex flex-column">
                        <h1 class="h6 mb-0 fw-semibold">Dashboard</h1>
                        {{-- <p class="small mb-0" style="color: var(--muted-foreground);">BTCUSD · 1D · Bitstamp</p> --}}
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <!-- Theme Toggle -->
                    <button class="btn-df-ghost" @click="$dispatch('theme-toggle')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="5"/>
                            <path d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <div class="flex-grow-1 p-4 fade-in">
        @yield('content')
            </div>
        </main>
    </div>

    @livewireScripts

    {{-- Additional Scripts from Views --}}
    @yield('scripts')
</body>

</html>
