<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
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
        <nav>
            <a href="{{ route('home') }}" @class(['on' => request()->routeIs('home')])>Home</a>
            <a href="{{ route('calculator') }}" @class(['on' => request()->routeIs('calculator')])>Fare Calculator</a>
            <a href="{{ route('find') }}" @class(['on' => request()->routeIs('find')])>Find a TODA</a>
            <a href="{{ route('tables') }}" @class(['on' => request()->routeIs('tables')])>Fare Tables</a>
            @auth
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.map') }}" @class(['on' => request()->routeIs('admin.*')])>Admin</a>
                @else
                    <a href="{{ route('admin.login') }}">Admin Login</a>
                @endif
            @else
                <a href="{{ route('admin.login') }}">Admin Login</a>
            @endauth
        </nav>
        <a class="btn s" href="tel:09000000001">Contact Us</a>
    </header>

    <main>
        @yield('content')
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
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
