<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#ff5f15">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'TODA Rescue Admin')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <link rel="stylesheet" href="{{ asset('css/toda.css') }}">
    @stack('initial-styles')
</head>
<body>
    <!-- Admin top bar -->
    <header class="admin-header">
        <div class="admin-logo">
            <span class="logo-mark">T</span>
            <div>
                <div class="logo-text">TODA Rescue</div>
                <div class="logo-sub">Admin Panel</div>
            </div>
        </div>
        <div class="admin-header-actions">
            <a href="{{ route('home') }}" class="admin-site-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span>Site</span>
            </a>
            <span class="admin-badge">Admin</span>
            <a href="{{ route('admin.logout') }}" class="admin-logout-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2h-4"/><polyline points="9 12 11 14 15 10"/></svg>
                Logout
            </a>
            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display:none">@csrf @method('POST')</form>
        </div>
    </header>

    <main class="admin-main">
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
</body>
</html>