@extends('layouts.app')
@section('title', 'Find a TODA')
@section('content')
<section class="page">
    <div class="top">
        <div>
            <h2>Find a TODA</h2>
            <p>Look up the nearest terminal, the areas it serves, and what you will pay to get there.</p>
        </div>
        <div class="search-bar">
            <input type="text" id="q" placeholder="Search barangay, landmark, or TODA...">
            <button class="btn" id="go">Search</button>
        </div>
    </div>

    <div class="two find-layout">
        <div class="find-sidebar">
            <div class="dest">
                <label for="dst">
                    <b>Where are you going?</b>
                    <span class="label-hint">(or click anywhere on the map)</span>
                </label>
                <select id="dst">
                    <option value="">Select destination</option>
                    @foreach($places as $p)
                        <option value="{{ $p->id }}" data-lat="{{ $p->lat }}" data-lng="{{ $p->lng }}">{{ $p->name }}</option>
                    @endforeach
                </select>
                <div id="info" class="dest-info">Pick a destination to see fares from each TODA.</div>
            </div>

            <div class="chips-row">
                <span class="chip on" data-f="all">All Terminals</span>
                <span class="chip" data-f="near">Near me</span>
                <span class="chip" data-f="open">Open now</span>
            </div>

            <div class="list-meta">
                <span class="sm" id="cnt">Loading terminals...</span>
            </div>

            <div id="list" class="toda-list"></div>
        </div>

        <div class="find-map-wrap">
            <div id="map"></div>
        </div>
    </div>
</section>
@endsection
@push('scripts')
<script src="{{ asset('js/find.js') }}"></script>
@endpush
