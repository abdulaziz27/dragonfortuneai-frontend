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
    <div class="df-layout" x-data="{ sidebarOpen: true, sidebarCollapsed: false }">
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
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary rounded d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 3v18h18"/>
                            <path d="M7 12l3-3 3 3 5-5"/>
                        </svg>
                    </div>
                    <div x-show="!sidebarCollapsed">
                        <div class="fw-semibold">Dragonfortune</div>
                        <div class="small text-muted">Pro</div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Content -->
            <div class="df-sidebar-content df-scrollbar">
                <!-- Navigation Menu -->
                <ul class="df-sidebar-menu">
                    <li class="df-sidebar-menu-item">
                        <a href="/workspace" class="df-sidebar-menu-button active">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 3v18h18"/>
                                <path d="M7 12l3-3 3 3 5-5"/>
                            </svg>
                            <span>Workspace</span>
                        </a>
                    </li>
                    <li class="df-sidebar-menu-item">
                        <a href="#" class="df-sidebar-menu-button">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7"/>
                                <rect x="14" y="3" width="7" height="7"/>
                                <rect x="14" y="14" width="7" height="7"/>
                                <rect x="3" y="14" width="7" height="7"/>
                            </svg>
                            <span>Watchlists</span>
                        </a>
                    </li>
                    <li class="df-sidebar-menu-item">
                        <a href="#" class="df-sidebar-menu-button">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 6h18"/>
                                <path d="M3 12h18"/>
                                <path d="M3 18h18"/>
                            </svg>
                            <span>Screeners</span>
                        </a>
                    </li>
                    <li class="df-sidebar-menu-item">
                        <a href="#" class="df-sidebar-menu-button">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                            </svg>
                            <span>Activity</span>
                        </a>
                    </li>
                    <li class="df-sidebar-menu-item">
                        <a href="#" class="df-sidebar-menu-button">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"/>
                            </svg>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>

                <!-- Projects Section -->
                <div class="mt-4" x-show="!sidebarCollapsed">
                    <div class="small text-muted mb-2 px-2">Projects</div>
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
                    </ul>
                </div>
            </div>

            <!-- Sidebar Footer -->
            <div class="df-sidebar-footer">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div x-show="!sidebarCollapsed">
                        <div class="fw-semibold small">Abdul Aziz</div>
                        <div class="small text-muted">abdulaziz@dragonfortune.ai</div>
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
                    <button class="btn-df-ghost d-none d-md-block" @click="sidebarCollapsed = !sidebarCollapsed">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="6" width="18" height="2"/>
                            <rect x="3" y="11" width="18" height="2"/>
                            <rect x="3" y="16" width="18" height="2"/>
                        </svg>
                    </button>

                    <div class="d-flex flex-column">
                        <h1 class="h6 mb-0 fw-semibold">Workspace</h1>
                        <p class="small text-muted mb-0">BTCUSD · 1D · Bitstamp</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <!-- Theme Toggle -->
                    <button class="btn-df-ghost" @click="document.documentElement.classList.toggle('dark')">
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
