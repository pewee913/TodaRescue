@extends('layouts.app')
@section('title', 'Find a TODA')
@section('content')
<section class="page find-page">
    <div class="top find-top">
        <div class="find-title-wrap">
            <h2>Find a TODA</h2>
            <p class="find-subtitle">Locate terminals & live walking directions</p>
        </div>
        <div class="search-bar">
            <input type="search" id="q" placeholder="Search barangay, landmark, or TODA..." autocomplete="off">
            <button class="btn" id="go">Search</button>
        </div>
    </div>

    <!-- Destination Bar (Compact & Clean) -->
    <div class="dest-bar">
        <div class="dest-bar-inner">
            <svg class="dest-bar-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            <select id="dst" aria-label="Select destination to check fares">
                <option value="">Where are you heading? (Optional: checks fares)</option>
                @foreach($places as $p)
                    <option value="{{ $p->id }}" data-lat="{{ $p->lat }}" data-lng="{{ $p->lng }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="two find-layout">
        <div class="find-map-wrap">
            <div id="map"></div>
        </div>

        <div class="find-sidebar" id="find-sidebar">
            <!-- User Location Status -->
            <div id="location-status-banner" class="location-banner">
                <span id="location-text">📍 Detecting your location...</span>
                <button class="btn s w" id="btn-detect-gps" type="button" style="padding:4px 10px;font-size:12px">Locate Me</button>
            </div>

            <!-- Empty state: shown on desktop, hidden on mobile to prevent clutter -->
            <div id="empty-state" class="card empty-state desktop-only">
                <div class="empty-icon">📍</div>
                <h3 style="margin:0 0 4px;font-size:16px">Select a TODA</h3>
                <p class="sm" style="margin:0">Tap any terminal pin on the map to see its directions and fare.</p>
            </div>

            <!-- TODA info card: shown only when a terminal is selected -->
            <div id="nav-card" class="card nav-card" style="display:none;">
                <div class="nav-header">
                    <div>
                        <b id="nav-title" style="font-size:16px">Directions to TODA</b>
                        <div class="sm" id="nav-subtitle">Live walking guidance to terminal</div>
                    </div>
                    <button class="nav-close" onclick="hideNavCard()" aria-label="Close info card">✕</button>
                </div>

                <!-- Walking Direction Instruction Banner -->
                <div id="nav-instruction-banner" class="nav-instruction-banner">
                    <div class="nav-arrow-icon" id="nav-arrow-icon">🚶</div>
                    <div class="nav-instruction-content">
                        <div class="nav-step-main" id="nav-step-main">Head toward terminal</div>
                        <div class="nav-step-sub" id="nav-step-sub">Follow the blue walking route on the map</div>
                    </div>
                </div>

                <!-- Live Walk Distance & ETA Stats -->
                <div id="nav-stats" class="track-stats-grid">
                    <div>
                        <div class="track-stat-val" id="nav-dist">0 m</div>
                        <div class="track-stat-lbl">Walk Distance</div>
                    </div>
                    <div>
                        <div class="track-stat-val" id="nav-eta">~0 min</div>
                        <div class="track-stat-lbl">Walk Time</div>
                    </div>
                    <div>
                        <div class="track-stat-val" id="nav-bearing">North</div>
                        <div class="track-stat-lbl">Direction</div>
                    </div>
                </div>

                <!-- Quick Navigation Action Buttons -->
                <div style="display:flex;gap:8px;margin-bottom:12px;">
                    <button type="button" class="btn s w" onclick="focusWalkRoute()" style="flex:1;display:inline-flex;align-items:center;justify-content:center;gap:6px;font-size:12px;padding:7px 10px;">
                        <span>🚶</span> View Walk Route
                    </button>
                    <button type="button" class="btn s outline" onclick="focusTodaTerminal()" style="flex:1;display:inline-flex;align-items:center;justify-content:center;gap:6px;font-size:12px;padding:7px 10px;">
                        <span>📍</span> View Terminal
                    </button>
                </div>

                <!-- Terminal Details & Barangay Cards -->
                <div id="nav-toda-info" class="dest-info" style="margin-top:8px"></div>
            </div>
        </div>
    </div>

    <!-- Floating Toast Container -->
    <div id="toda-toast-container" class="toda-toast-container"></div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/find.js') }}"></script>
@endpush

@push('scripts')
<script>
(function(){
    const wrap = document.querySelector('.find-layout');
    const btn = document.getElementById('toggle-find-sidebar');
    if (!wrap || !btn) return;
    const closed = localStorage.getItem('find-sidebar') === 'closed';
    if (closed){ wrap.classList.add('sidebar-off'); btn.classList.add('collapsed'); }
    btn.onclick = function(){
        const now = btn.classList.toggle('collapsed');
        wrap.classList.toggle('sidebar-off');
        localStorage.setItem('find-sidebar', now ? 'closed' : 'open');
        if (!now && window.map) setTimeout(() => window.map.invalidateSize(), 350);
    };
})();
</script>
@endpush
