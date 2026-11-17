@extends('layouts.app')
@section('title', 'Find a TODA')
@section('content')
<section class="page">
    <div class="top">
        <div>
            <h2>Find a TODA</h2>
            <p>Locate the nearest terminal, see live walking directions, and check fare estimates.</p>
            <button class="toggle-btn" id="toggle-find-sidebar" aria-label="Toggle sidebar"><svg class="t-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><polyline points="6 9 12 15 18 9"/></svg></button>
        </div>
        <div class="search-bar">
            <input type="text" id="q" placeholder="Search barangay, landmark, or TODA...">
            <button class="btn" id="go">Search</button>
        </div>
    </div>

    <div class="two find-layout">
        <div class="find-sidebar" id="find-sidebar">
            <!-- User Location Status Banner -->
            <div id="location-status-banner" class="location-banner">
                <span id="location-text">📍 Detecting your location...</span>
                <button class="btn s w" id="btn-detect-gps" type="button" style="padding:4px 10px;font-size:12px">Locate Me</button>
            </div>

            <!-- Destination Selection (Optional: pick where you're ultimately riding to) -->
            <div class="dest">
                <label for="dst">
                    <b>Where are you heading?</b>
                </label>
                <select id="dst">
                    <option value="">Select destination (to check fares)</option>
                    @foreach($places as $p)
                        <option value="{{ $p->id }}" data-lat="{{ $p->lat }}" data-lng="{{ $p->lng }}">{{ $p->name }}</option>
                    @endforeach
                </select>
                <div id="dst-info" class="dest-info">Select a destination or tap any TODA terminal below to get live directions.</div>
            </div>

            <!-- Empty state: shown when no TODA is selected -->
            <div id="empty-state" class="card empty-state">
                <div class="empty-icon">📍</div>
                <h3 style="margin:0 0 6px;font-size:17px">Select a TODA</h3>
                <p class="sm" style="margin:0">Tap any terminal pin on the map to see its information and get directions.</p>
            </div>

            <!-- TODA info card: shown only when a terminal is selected -->
            <div id="nav-card" class="card nav-card" style="display:none;">
                <div class="nav-header">
                    <div>
                        <b id="nav-title" style="font-size:17px">Directions to TODA</b>
                        <div class="sm" id="nav-subtitle">Live walking guidance & route</div>
                    </div>
                    <button class="nav-close" onclick="hideNavCard()" aria-label="Close info card">✕</button>
                    <span id="nav-badge" class="live-badge" style="display:none;">
                        <span class="live-dot"></span>
                        <span id="nav-badge-text">GUIDANCE ACTIVE</span>
                    </span>
                </div>

                <!-- Turn / Direction Banner -->
                <div id="nav-instruction-banner" class="nav-instruction-banner">
                    <div class="nav-arrow-icon" id="nav-arrow-icon">↗</div>
                    <div class="nav-instruction-content">
                        <div class="nav-step-main" id="nav-step-main">Head toward terminal</div>
                        <div class="nav-step-sub" id="nav-step-sub">Follow the highlighted route on the map</div>
                    </div>
                </div>

                <!-- Live Navigation Stats -->
                <div id="nav-stats" class="track-stats-grid">
                    <div>
                        <div class="track-stat-val" id="nav-dist">0 m</div>
                        <div class="track-stat-lbl">Distance</div>
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

                <!-- Terminal Details -->
                <div id="nav-toda-info" class="dest-info" style="border-top:1px dashed var(--bd);padding-top:10px;margin-top:10px"></div>

                <div style="display:flex;gap:10px;margin-top:14px">
                    <button class="btn btn-full" id="btn-start-nav">Start Live Guidance</button>
                    <button class="btn w btn-full" id="btn-stop-nav" style="display:none;">Stop Guidance</button>
                </div>
            </div>
        </div>

        <div class="find-map-wrap">
            <div id="map"></div>
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
