<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'DragonFortune AI') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body>
    <div class="df-layout" x-data="{
        sidebarOpen: true,
        sidebarCollapsed: false,
        openSubmenus: {},
        profileDropdownOpen: false,
        toggleSubmenu(menuId) {
            this.openSubmenus[menuId] = !this.openSubmenus[menuId];
        }
    }" @theme-toggle.window="document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');">
        <!-- Sidebar -->
        <aside class="df-sidebar"
               :class="{ 'collapsed': sidebarCollapsed }"
               x-show="sidebarOpen"
               x-transition:enter="slide-in"
               x-transition:leave="transition ease-in-out duration-300"
               x-transition:leave-start="transform translate-x-0"
               x-transition:leave-end="transform -translate-x-full">

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
                                <span class="fw-semibold small">Dragonfortune</span>
                                <span class="small" style="color: var(--muted-foreground);">Pro</span>
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
                            <a href="/" class="df-sidebar-menu-button active">
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
                            <div class="df-submenu" :class="{ 'show': openSubmenus['derivatives'] }">
                                <a href="/derivatives/funding-rate" class="df-submenu-item" style="color: var(--foreground);">Funding Rate</a>
                                <a href="/derivatives/open-interest" class="df-submenu-item" style="color: var(--foreground);">Open Interest</a>
                                <a href="/derivatives/long-short-ratio" class="df-submenu-item" style="color: var(--foreground);">Long/Short Ratio</a>
                                <a href="/derivatives/liquidations" class="df-submenu-item" style="color: var(--foreground);">Liquidations</a>
                                <a href="/derivatives/volume-change" class="df-submenu-item" style="color: var(--foreground);">Volume + Change</a>
                                <a href="/derivatives/delta-long-short" class="df-submenu-item" style="color: var(--foreground);">Delta Long vs Short</a>
                            </div>
                        </li>
                        <li class="df-sidebar-menu-item">
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
                        </li>
                        <li class="df-sidebar-menu-item">
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
                        </li>
                        <li class="df-sidebar-menu-item">
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
                        </li>
                        <li class="df-sidebar-menu-item">
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
                        </li>
                    </ul>
                </div>

                <!-- Watchlist Section -->
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
                </div>
            </div>

            <!-- Sidebar Footer -->
            <div class="df-sidebar-footer">
                <div class="df-sidebar-menu">
                    <div class="df-sidebar-menu-item position-relative">
                        <button class="df-sidebar-menu-button df-sidebar-menu-button-lg" @click="profileDropdownOpen = !profileDropdownOpen">
                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <span class="fw-semibold small text-dark">AA</span>
                            </div>
                            <div class="d-flex flex-column text-start flex-grow-1" x-show="!sidebarCollapsed">
                                <span class="fw-semibold small">Abdul Aziz</span>
                                <span class="small" style="color: var(--muted-foreground);">abdulaziz@dragonfortune.ai</span>
                            </div>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ms-auto" x-show="!sidebarCollapsed">
                                <path d="M7 13l3 3 7-7"/>
                            </svg>
                        </button>

                        <!-- Profile Dropdown -->
                        <div class="df-profile-dropdown" x-show="profileDropdownOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" @click.away="profileDropdownOpen = false">
                            <!-- Profile Header -->
                            <div class="df-profile-dropdown-header">
                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <span class="fw-semibold text-dark">AA</span>
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
                                        <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/>
                                    </svg>
                                    Upgrade to Pro
                                </a>
                                <a href="#" class="df-profile-dropdown-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    Account
                                </a>
                                <a href="#" class="df-profile-dropdown-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <polyline points="14,2 14,8 20,8"/>
                                        <line x1="16" y1="13" x2="8" y2="13"/>
                                        <line x1="16" y1="17" x2="8" y2="17"/>
                                        <polyline points="10,9 9,9 8,9"/>
                                    </svg>
                                    Billing
                                </a>
                                <a href="#" class="df-profile-dropdown-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                                    </svg>
                                    Notifications
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
                    <button class="btn-df-ghost d-md-none" @click="sidebarOpen = !sidebarOpen">
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

                    <button class="btn-df-outline">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                            <path d="M3 6h18"/>
                            <path d="M3 12h18"/>
                            <path d="M3 18h18"/>
                        </svg>
                        Indicators
                    </button>

                    <button class="btn-df-outline">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"/>
                        </svg>
                        Settings
                    </button>

                    <button class="btn-df-primary">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                            <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>
                        </svg>
                        Fullscreen
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
</body>

</html>
