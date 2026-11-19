<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#ff5f15">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TODA Rescue') - Tricycle Fares & Terminal Finder</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <link rel="stylesheet" href="{{ asset('css/toda.css') }}">
</head>

<body>
    <header>
        <a class="logo" href="{{ route('home') }}">
            <b>T</b>
            <span>TODA Rescue</span>
        </a>

        <!-- Mobile menu toggle -->
        <button class="nav-toggle" id="nav-toggle" aria-label="Toggle menu" aria-expanded="false">
            <span class="nav-toggle-bar"></span>
            <span class="nav-toggle-bar"></span>
            <span class="nav-toggle-bar"></span>
        </button>

        <nav id="main-nav">
            <div class="nav-drawer-header">
                <div class="nav-drawer-title">
                    <b>T</b>
                    <span>TODA Rescue</span>
                </div>
                <button type="button" class="nav-close-btn" id="nav-close-btn" aria-label="Close menu">✕</button>
            </div>
            <div class="nav-links">
                <a href="{{ route('home') }}" @class(['on' => request()->routeIs('home')])>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Home
                </a>
                <a href="{{ route('find') }}" @class(['on' => request()->routeIs('find')])>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    Find a TODA
                </a>
                <a href="{{ route('tables') }}" @class(['on' => request()->routeIs('tables')])>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18M3 15h18M9 3v18"/></svg>
                    Fare Tables
                </a>
                @auth
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.map') }}" @class(['on' => request()->routeIs('admin.*')])>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                            Admin
                        </a>
                    @else
                        <a href="{{ route('admin.login') }}">Admin Login</a>
                    @endif
                @else
                    <a href="{{ route('admin.login') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                        Admin Login
                    </a>
                @endauth
            </div>

            <div class="nav-drawer-footer">
                <div class="drawer-contact-label">Need Assistance?</div>
                <a class="btn mobile-nav-call" href="tel:09000000001">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    Contact Support
                </a>
                <div class="sm" style="margin-top:10px;color:var(--mu-light);font-size:12px;">San Pablo City Tricycle Services</div>
            </div>
        </nav>
        <a class="btn s header-contact-btn" href="tel:09000000001">Contact Us</a>
    </header>

    <!-- Mobile nav overlay -->
    <div class="nav-overlay" id="nav-overlay" onclick="closeMobileNav()"></div>

    <main>
        @yield('content')
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script src="{{ asset('js/leaflet.polylineDecorator.js') }}"></script>
    <script>
        window.TODA = {
            csrf: "{{ csrf_token() }}",
            api: "{{ url('/api') }}",
            center: [14.0683, 121.3256]
        };
    </script>
    <script src="{{ asset('js/common.js') }}"></script>
    @stack('scripts')

    <!-- Mobile nav toggle script -->
    <script>
        (function() {
            const toggle = document.getElementById('nav-toggle');
            const nav = document.getElementById('main-nav');
            const overlay = document.getElementById('nav-overlay');
            const closeBtn = document.getElementById('nav-close-btn');

            if (!toggle || !nav || !overlay) return;

            function openMobileNav() {
                nav.classList.add('open');
                overlay.classList.add('open');
                toggle.classList.add('open');
                toggle.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
            }

            function closeMobileNav() {
                nav.classList.remove('open');
                overlay.classList.remove('open');
                toggle.classList.remove('open');
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }

            window.closeMobileNav = closeMobileNav;

            toggle.onclick = function(e) {
                e.stopPropagation();
                if (nav.classList.contains('open')) closeMobileNav();
                else openMobileNav();
            };

            if (closeBtn) {
                closeBtn.onclick = function(e) {
                    e.stopPropagation();
                    closeMobileNav();
                };
            }

            // Close nav when clicking a link (mobile)
            nav.querySelectorAll('.nav-links a').forEach(function(link) {
                link.onclick = closeMobileNav;
            });
        })();
    </script>
</body>

</html>
