<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light only">
    <title>@yield('title', 'Sistem Costing Manufaktur') - Dharma Electrindo Mfg</title>
    {{-- Load Google Fonts asynchronously so it never blocks page rendering --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"></noscript>
    {{-- Critical CSS inlined for instant first paint --}}
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;background:#f1f5f9;color:#1e293b;min-height:100vh;line-height:1.6}
        #page-loading-overlay{position:fixed;inset:0;background:rgba(15,23,42,.32);backdrop-filter:blur(1.5px);z-index:9999;display:flex;align-items:center;justify-content:center;padding:1rem;transition:opacity .3s ease}
        #page-loading-overlay.hidden{opacity:0;pointer-events:none}
        .loading-card{min-width:210px;max-width:min(88vw,320px);border-radius:14px;background:rgba(255,255,255,.96);border:1px solid #dbe4f2;box-shadow:0 18px 45px rgba(15,23,42,.22);padding:1rem 1.1rem;text-align:center;animation:lcPopIn .2s ease}
        .loading-spinner{width:42px;height:42px;margin:0 auto .65rem;border-radius:999px;border:3px solid #dbeafe;border-top-color:#2563eb;border-right-color:#60a5fa;animation:sr .8s linear infinite}
        .loading-text{margin:0;color:#1e293b;font-size:.9rem;font-weight:600;letter-spacing:.01em}
        @keyframes sr{to{transform:rotate(360deg)}}
        @keyframes lcPopIn{from{opacity:0;transform:translateY(6px) scale(.98)}to{opacity:1;transform:translateY(0) scale(1)}}
    </style>
    <link rel="stylesheet" href="{{ asset('css/app-layout.css') }}">
</head>

<body>
    <!-- Page Loading Overlay -->
    <div id="page-loading-overlay">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <p class="loading-text">Memuat halaman...</p>
        </div>
    </div>

    <div class="app-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <div class="sidebar-logo-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z" />
                            <path d="M2 17l10 5 10-5" />
                            <path d="M2 12l10 5 10-5" />
                        </svg>
                    </div>
                    <div class="sidebar-logo-text">
                        <span class="sidebar-logo-title">Costing System</span>
                        <span class="sidebar-logo-subtitle">Dharma Electrindo Mfg</span>
                    </div>
                </div>
            </div>
            <nav class="sidebar-nav">
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-title">Menu Utama</div>
                        <a href="{{ route('dashboard', absolute: false) }}"
                        class="sidebar-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7" rx="1" />
                            <rect x="14" y="3" width="7" height="7" rx="1" />
                            <rect x="14" y="14" width="7" height="7" rx="1" />
                            <rect x="3" y="14" width="7" height="7" rx="1" />
                        </svg>
                        <span>Dashboard</span>
                    </a>
                        <a href="{{ route('compare.costing', absolute: false) }}"
                            class="sidebar-nav-item {{ request()->routeIs('compare.costing') ? 'active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 6h6" />
                                <path d="M15 6h6" />
                                <path d="M9 6a3 3 0 0 1 6 0" />
                                <path d="M3 18h6" />
                                <path d="M15 18h6" />
                                <path d="M9 18a3 3 0 0 1 6 0" />
                                <path d="M12 6v12" />
                            </svg>
                            <span>Compare Costing</span>
                        </a>
                    <a href="{{ route('resume-cogm', absolute: false) }}"
                        class="sidebar-nav-item {{ request()->routeIs('resume-cogm') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                        <span>Resume COGM</span>
                    </a>
                    <a href="{{ route('analisis-tren', absolute: false) }}"
                        class="sidebar-nav-item {{ request()->routeIs('analisis-tren') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                        <span>Analisis Tren</span>
                    </a>
                    <a href="{{ route('cogm-submissions', absolute: false) }}"
                        class="sidebar-nav-item {{ request()->routeIs('cogm-submissions') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4"/>
                            <path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9c1.5 0 2.91.37 4.15 1.02"/>
                        </svg>
                        <span>COGM Submission</span>
                    </a>
                    <div class="sidebar-dropdown">
                        <button class="sidebar-nav-item sidebar-dropdown-toggle" onclick="toggleDropdown(this)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3" />
                                <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5" />
                                <ellipse cx="12" cy="5" rx="9" ry="3" />
                            </svg>
                            <span>Database</span>
                            <svg class="dropdown-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2"
                                style="width: 16px; height: 16px; margin-left: auto; transition: transform 0.2s;">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </button>
                        <div class="sidebar-submenu">
                            <a href="{{ route('database.parts', absolute: false) }}"
                                class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('database.parts') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path
                                        d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                                </svg>
                                <span>Part</span>
                            </a>
                            @if(Route::has('database.wires'))
                                <a href="{{ route('database.wires', absolute: false) }}"
                                    class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('database.wires*') ? 'active' : '' }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 3v6" />
                                        <path d="M18 3v6" />
                                        <path d="M6 21v-6" />
                                        <path d="M18 21v-6" />
                                        <path d="M8 9h8" />
                                        <path d="M8 15h8" />
                                    </svg>
                                    <span>Wire</span>
                                </a>
                            @endif
                            <a href="{{ route('database.costing', absolute: false) }}"
                                class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('database.costing') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="1" x2="12" y2="23" />
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                                </svg>
                                <span>Costing</span>
                            </a>
                            <a href="{{ route('database.material-cost', absolute: false) }}"
                                class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('database.material-cost') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 7h16" />
                                    <path d="M4 12h16" />
                                    <path d="M4 17h16" />
                                    <circle cx="8" cy="7" r="1" fill="currentColor" stroke="none" />
                                    <circle cx="12" cy="12" r="1" fill="currentColor" stroke="none" />
                                    <circle cx="16" cy="17" r="1" fill="currentColor" stroke="none" />
                                </svg>
                                <span>Material Cost</span>
                            </a>
                            <a href="{{ route('database.customers', absolute: false) }}"
                                class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('database.customers') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                                <span>Customer</span>
                            </a>
                            <a href="{{ route('database.business-categories', absolute: false) }}"
                                class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('database.business-categories*') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 7h16" />
                                    <path d="M4 12h16" />
                                    <path d="M4 17h10" />
                                </svg>
                                <span>Business Categories</span>
                            </a>
                            <a href="{{ route('database.plants', absolute: false) }}"
                                class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('database.plants*') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 21h18" />
                                    <path d="M5 21V8l7-5 7 5v13" />
                                    <path d="M9 21v-6h6v6" />
                                </svg>
                                <span>Plant</span>
                            </a>
                            <a href="{{ route('database.pics', absolute: false) }}"
                                class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('database.pics*') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                    <circle cx="8.5" cy="7" r="4" />
                                    <path d="M20 8v6" />
                                    <path d="M23 11h-6" />
                                </svg>
                                <span>PIC</span>
                            </a>
                            <a href="{{ route('database.cycle-time-templates', absolute: false) }}"
                                class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('database.cycle-time-templates*') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                                <span>Cycle Time</span>
                            </a>
                            <a href="{{ route('database.project-documents', absolute: false) }}"
                                class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('database.project-documents*') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                    <path d="M9 15l2 2 4-4" />
                                </svg>
                                <span>Dokumen Project</span>
                            </a>
                            <a href="{{ route('products.index', absolute: false) }}"
                                class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                </svg>
                                <span>Product</span>
                            </a>
                            <a href="{{ route('material-breakdown', absolute: false) }}"
                                class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('material-breakdown') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="3" y1="9" x2="21" y2="9"/>
                                    <line x1="3" y1="15" x2="21" y2="15"/>
                                    <line x1="9" y1="3" x2="9" y2="21"/>
                                </svg>
                                <span>Material Breakdown</span>
                            </a>
                            <a href="{{ route('rate-kurs', absolute: false) }}"
                                class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('rate-kurs*') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="1" x2="12" y2="23"/>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                </svg>
                                <span>Rate & Kurs</span>
                            </a>
                            <a href="{{ route('unpriced-parts', absolute: false) }}"
                                class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('unpriced-parts') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                                <span>Unpriced Parts</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-title">Input Data</div>
                    <a href="{{ route('form', absolute: false) }}"
                        class="sidebar-nav-item {{ request()->routeIs('form') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                            <polyline points="10 9 9 9 8 9" />
                        </svg>
                        <span>Form Costing</span>
                    </a>
                    <a href="{{ route('tracking-documents.index', absolute: false) }}"
                        class="sidebar-nav-item {{ request()->routeIs('tracking-documents.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 3v18h18" />
                            <path d="M7 14l3-3 3 2 4-5" />
                        </svg>
                        <span>Project</span>
                    </a>
                </div>
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-title">Laporan</div>
                    <a href="{{ route('laporan', absolute: false) }}"
                        class="sidebar-nav-item {{ request()->routeIs('laporan') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
                            <line x1="4" y1="22" x2="4" y2="15"/>
                        </svg>
                        <span>Laporan & Export</span>
                    </a>
                    <a href="{{ route('audit-trail', absolute: false) }}"
                        class="sidebar-nav-item {{ request()->routeIs('audit-trail') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        <span>Audit Trail</span>
                    </a>
                </div>
                @if(auth()->check() && auth()->user()->role === 'admin')
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-title">Administrasi</div>
                    <a href="{{ route('permissions', absolute: false) }}"
                        class="sidebar-nav-item {{ request()->routeIs('permissions') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="8.5" cy="7" r="4"/>
                            <path d="M17 11l2 2 4-4"/>
                        </svg>
                        <span>Permission</span>
                    </a>
                </div>
                @endif
            </nav>
            <div class="sidebar-footer" style="padding: 0.75rem 1rem; border-top: 1px solid rgba(255,255,255,0.08);">
                @auth
                <div style="display: flex; align-items: center; gap: 0.625rem; margin-bottom: 0.5rem;">
                    <div style="width: 32px; height: 32px; background: rgba(255,255,255,0.15); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; color: rgba(255,255,255,0.7);"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div style="overflow: hidden;">
                        <div style="font-size: 0.75rem; font-weight: 600; color: rgba(255,255,255,0.9); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ auth()->user()->name }}</div>
                        <div style="font-size: 0.625rem; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 0.05em;">{{ auth()->user()->role ?? 'user' }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" style="width: 100%; padding: 0.375rem; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; color: rgba(255,255,255,0.7); font-size: 0.6875rem; font-family: inherit; font-weight: 500; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.375rem; transition: all 0.15s;" onmouseenter="this.style.background='rgba(255,255,255,0.15)'" onmouseleave="this.style.background='rgba(255,255,255,0.08)'">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Logout
                    </button>
                </form>
                @endauth
                <p class="sidebar-footer-text" style="margin-top: 0.5rem;">© {{ date('Y') }} Dharma Electrindo Mfg</p>
            </div>
        </aside>

        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        <!-- Main Content Wrapper -->
        <div class="main-wrapper">
            <!-- Header -->
            <header class="header">
                <div class="header-content">
                    <div class="header-left">
                        <button class="mobile-menu-toggle" onclick="toggleSidebar()">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <line x1="3" y1="12" x2="21" y2="12" />
                                <line x1="3" y1="6" x2="21" y2="6" />
                                <line x1="3" y1="18" x2="21" y2="18" />
                            </svg>
                        </button>
                        <div>
                            <h1 class="header-title">@yield('page-title', 'Costing Per Product Dashboard')</h1>
                            <span class="header-subtitle">Dharma Electrindo Mfg</span>
                        </div>
                    </div>
                    <div class="header-right">
                        @yield('header-filters')
                        <nav class="nav-tabs">
                            <a href="{{ route('dashboard', absolute: false) }}"
                                class="nav-tab {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="7" height="7" rx="1" />
                                    <rect x="14" y="3" width="7" height="7" rx="1" />
                                    <rect x="14" y="14" width="7" height="7" rx="1" />
                                    <rect x="3" y="14" width="7" height="7" rx="1" />
                                </svg>
                                Dashboard
                            </a>
                            <a href="{{ route('database', absolute: false) }}"
                                class="nav-tab {{ request()->routeIs('database') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3" />
                                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5" />
                                    <ellipse cx="12" cy="5" rx="9" ry="3" />
                                </svg>
                                Database
                            </a>
                            <a href="{{ route('form', absolute: false) }}"
                                class="nav-tab {{ request()->routeIs('form') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                    <line x1="16" y1="13" x2="8" y2="13" />
                                    <line x1="16" y1="17" x2="8" y2="17" />
                                    <polyline points="10 9 9 9 8 9" />
                                </svg>
                                Form Costing
                            </a>
                            <a href="{{ route('compare.costing', absolute: false) }}"
                                class="nav-tab {{ request()->routeIs('compare.costing') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 6h6" />
                                    <path d="M15 6h6" />
                                    <path d="M9 6a3 3 0 0 1 6 0" />
                                    <path d="M3 18h6" />
                                    <path d="M15 18h6" />
                                    <path d="M9 18a3 3 0 0 1 6 0" />
                                    <path d="M12 6v12" />
                                </svg>
                                Compare
                            </a>
                        </nav>
                    </div>
                </div>
            </header>

            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <div class="breadcrumb-content">
                    <a href="{{ route('dashboard', absolute: false) }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                            <polyline points="9 22 9 12 15 12 15 22" />
                        </svg>
                    </a>
                    <span class="breadcrumb-separator">/</span>
                    @yield('breadcrumb')
                </div>
            </div>

            <!-- Main Content -->
            <main class="main-content">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="footer">
                <div class="footer-content">
                    <span>&copy; 2025 Dharma Electrindo Mfg. All rights reserved.</span>
                    <span>Sistem Costing Manufaktur v1.0</span>
                </div>
            </footer>
        </div>
    </div>

    <div id="app-confirm-modal" class="app-confirm-modal is-hidden" role="dialog" aria-modal="true" aria-labelledby="app-confirm-title" onclick="closeAppConfirmOnOverlay(event)">
        <div class="app-confirm-card">
            <div class="app-confirm-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 9v4" />
                    <path d="M12 17h.01" />
                    <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                </svg>
            </div>
            <h3 id="app-confirm-title" class="app-confirm-title">Konfirmasi Aksi</h3>
            <p id="app-confirm-message" class="app-confirm-message">Apakah Anda yakin?</p>
            <div class="app-confirm-actions">
                <button type="button" class="app-confirm-btn app-confirm-btn-secondary" onclick="closeAppConfirm()">Batal</button>
                <button type="button" id="app-confirm-ok" class="app-confirm-btn app-confirm-btn-danger" onclick="executeAppConfirm()">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>

    <div id="app-notify-modal" class="app-confirm-modal is-hidden" role="dialog" aria-modal="true" aria-labelledby="app-notify-title" onclick="closeAppNotifyOnOverlay(event)">
        <div class="app-confirm-card app-notify-card">
            <div class="app-confirm-icon app-notify-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M12 9v4" />
                    <path d="M12 16h.01" />
                </svg>
            </div>
            <h3 id="app-notify-title" class="app-confirm-title">Informasi</h3>
            <p id="app-notify-message" class="app-confirm-message">Ada informasi untuk Anda.</p>
            <div class="app-confirm-actions app-notify-actions">
                <button type="button" id="app-notify-ok" class="app-confirm-btn app-confirm-btn-primary">OK</button>
            </div>
        </div>
    </div>

    <div id="app-loading-overlay" class="app-loading-overlay is-hidden" role="status" aria-live="polite" aria-label="Memuat data">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <p id="app-loading-text" class="loading-text">Memuat halaman...</p>
        </div>
    </div>

    

    <script>
        let appConfirmCurrentOnConfirm = null;
        let appNotifyCurrentOnClose = null;
        let appLoadingVisible = false;

        function showAppLoading(message) {
            const overlay = document.getElementById('app-loading-overlay');
            const textNode = document.getElementById('app-loading-text');

            if (!overlay) {
                return;
            }

            if (textNode) {
                textNode.textContent = message || 'Memuat halaman...';
            }

            if (appLoadingVisible) {
                return;
            }

            appLoadingVisible = true;
            overlay.classList.remove('is-hidden');
        }

        function hideAppLoading() {
            const overlay = document.getElementById('app-loading-overlay');
            if (!overlay) {
                return;
            }

            appLoadingVisible = false;
            overlay.classList.add('is-hidden');
        }

        function openAppConfirm(message, onConfirm) {
            const modal = document.getElementById('app-confirm-modal');
            const messageNode = document.getElementById('app-confirm-message');
            const okButton = document.getElementById('app-confirm-ok');

            messageNode.textContent = message || 'Apakah Anda yakin ingin melanjutkan?';
            appConfirmCurrentOnConfirm = onConfirm;
            modal.classList.remove('is-hidden');
            document.body.style.overflow = 'hidden';
            okButton.focus();
        }

        function closeAppConfirm() {
            const modal = document.getElementById('app-confirm-modal');
            appConfirmCurrentOnConfirm = null;
            modal.classList.add('is-hidden');
            document.body.style.overflow = '';
        }

        function executeAppConfirm() {
            if (typeof appConfirmCurrentOnConfirm === 'function') {
                const callback = appConfirmCurrentOnConfirm;
                closeAppConfirm();
                callback();
            } else {
                closeAppConfirm();
            }
        }

        function openAppNotify(message, onClose) {
            const modal = document.getElementById('app-notify-modal');
            const messageNode = document.getElementById('app-notify-message');
            const okButton = document.getElementById('app-notify-ok');

            if (!modal || !messageNode || !okButton) {
                window.alert(message || 'Ada informasi untuk Anda.');
                if (typeof onClose === 'function') {
                    onClose();
                }
                return;
            }

            messageNode.textContent = message || 'Ada informasi untuk Anda.';
            appNotifyCurrentOnClose = onClose;
            modal.classList.remove('is-hidden');
            document.body.style.overflow = 'hidden';
            okButton.focus();
        }

        function closeAppNotify() {
            const modal = document.getElementById('app-notify-modal');
            if (!modal) {
                return;
            }

            const onClose = appNotifyCurrentOnClose;
            appNotifyCurrentOnClose = null;
            modal.classList.add('is-hidden');
            document.body.style.overflow = '';

            if (typeof onClose === 'function') {
                onClose();
            }
        }

        function closeAppNotifyOnOverlay(event) {
            if (event.target && event.target.id === 'app-notify-modal') {
                closeAppNotify();
            }
        }

        function closeAppConfirmOnOverlay(event) {
            if (event.target && event.target.id === 'app-confirm-modal') {
                closeAppConfirm();
            }
        }

        function shouldShowLoadingForLink(link, event) {
            if (!(link instanceof HTMLAnchorElement)) {
                return false;
            }

            if (link.dataset.skipLoadingOverlay === 'true' || event.defaultPrevented) {
                return false;
            }

            if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return false;
            }

            if (link.target && link.target !== '_self') {
                return false;
            }

            if (link.hasAttribute('download')) {
                return false;
            }

            const hrefValue = link.getAttribute('href') || '';
            const lowerHref = hrefValue.trim().toLowerCase();
            if (!lowerHref || lowerHref === '#' || lowerHref.startsWith('javascript:') || lowerHref.startsWith('mailto:') || lowerHref.startsWith('tel:')) {
                return false;
            }

            try {
                const destination = new URL(link.href, window.location.href);
                if (destination.origin !== window.location.origin) {
                    return false;
                }

                const isSamePageAnchor = destination.pathname === window.location.pathname
                    && destination.search === window.location.search
                    && destination.hash;
                if (isSamePageAnchor) {
                    return false;
                }

                return true;
            } catch (_) {
                return false;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const okButton = document.getElementById('app-confirm-ok');
            const notifyOkButton = document.getElementById('app-notify-ok');

            okButton.addEventListener('click', function () {
                if (typeof appConfirmCurrentOnConfirm === 'function') {
                    const callback = appConfirmCurrentOnConfirm;
                    closeAppConfirm();
                    callback();
                    return;
                }

                closeAppConfirm();
            });

            if (notifyOkButton) {
                notifyOkButton.addEventListener('click', function () {
                    closeAppNotify();
                });
            }

            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (!form.classList || !form.classList.contains('js-confirm-form')) {
                    return;
                }

                if (form.dataset.confirmed === 'true') {
                    return;
                }

                event.preventDefault();
                const message = form.dataset.confirmMessage || 'Apakah Anda yakin ingin melanjutkan?';
                openAppConfirm(message, function () {
                    form.dataset.confirmed = 'true';
                    showAppLoading();
                    form.submit();
                });
            });

            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) {
                    return;
                }

                if (event.defaultPrevented) {
                    return;
                }

                if (form.dataset.skipLoadingOverlay === 'true') {
                    return;
                }

                showAppLoading();
            });

            document.addEventListener('click', function (event) {
                const link = event.target.closest('a');
                if (!shouldShowLoadingForLink(link, event)) {
                    return;
                }

                showAppLoading('Memuat halaman...');
            }, true);

            window.addEventListener('beforeunload', function () {
                showAppLoading('Memuat halaman...');
            });

            window.addEventListener('pageshow', function () {
                hideAppLoading();
            });

            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Escape') {
                    return;
                }

                const modal = document.getElementById('app-confirm-modal');
                if (!modal.classList.contains('is-hidden')) {
                    closeAppConfirm();
                    return;
                }

                const notifyModal = document.getElementById('app-notify-modal');
                if (notifyModal && !notifyModal.classList.contains('is-hidden')) {
                    closeAppNotify();
                }
            });
        });

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }

        function toggleDropdown(button) {
            const dropdown = button.closest('.sidebar-dropdown');
            dropdown.classList.toggle('open');
        }
    </script>

    <script>
        // Hide loading overlay once the page is fully loaded
        (function () {
            function hideOverlay() {
                var overlay = document.getElementById('page-loading-overlay');
                if (overlay) {
                    overlay.classList.add('hidden');
                    setTimeout(function () { overlay.style.display = 'none'; }, 320);
                }
            }

            if (document.readyState === 'complete') {
                hideOverlay();
            } else {
                window.addEventListener('load', hideOverlay);
            }

            // Show overlay when navigating away (clicking links / submitting forms)
            document.addEventListener('click', function (e) {
                var target = e.target.closest('a[href]');
                if (!target) return;
                var href = target.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript') || target.getAttribute('target') === '_blank') return;
                // Only show for same-origin navigation
                try {
                    var url = new URL(href, window.location.origin);
                    if (url.origin !== window.location.origin) return;
                } catch (_) { return; }
                var overlay = document.getElementById('page-loading-overlay');
                if (overlay) {
                    overlay.style.display = 'flex';
                    overlay.classList.remove('hidden');
                }
            });

            document.addEventListener('submit', function (e) {
                if (e.defaultPrevented) return;

                var form = e.target;
                if (form && form.dataset && form.dataset.skipLoadingOverlay === 'true') return;

                var overlay = document.getElementById('page-loading-overlay');
                if (overlay) {
                    overlay.style.display = 'flex';
                    overlay.classList.remove('hidden');
                }
            });
        })();
    </script>

    @yield('scripts')
</body>

</html></html>