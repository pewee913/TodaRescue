@extends('layouts.app')
@section('title', 'Fare Calculator')
@section('content')
<section class="page">
    <div class="top">
        <div>
            <h2>Fare Calculator</h2>
            <p>Choose where you are riding from and to, and we will estimate the fare.</p>
        </div>
    </div>

    <div class="two">
        <form id="cf" class="card calculator-form">
            <h3>Trip details</h3>

            @foreach(['f' => ['Pick-up point', 'from'], 't' => ['Drop-off point', 'to']] as $name => [$label, $qs])
                <label for="select_{{ $name }}">{{ $label }}</label>
                <select id="select_{{ $name }}" name="{{ $name }}" required>
                    <option value="">Select location</option>
                    @foreach($places as $p)
                        <option value="{{ $p->id }}" data-lat="{{ $p->lat }}" data-lng="{{ $p->lng }}" @selected(request($qs) == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            @endforeach

            <div class="form-grid">
                <div>
                    <label for="passengers_input">Passengers</label>
                    <input type="number" id="passengers_input" name="n" value="1" min="1" max="6" required>
                </div>
                <div>
                    <label for="trip_type">Trip type</label>
                    <select id="trip_type" name="special">
                        <option value="0">Regular</option>
                        <option value="1">Special trip</option>
                    </select>
                </div>
            </div>

            <label for="passenger_type">Passenger type</label>
            <select id="passenger_type" name="type">
                <option value="regular">Regular</option>
                <option value="student">Student</option>
                <option value="senior">Senior</option>
                <option value="pwd">PWD</option>
            </select>

            <button type="submit" class="btn btn-full">Calculate fare</button>
        </form>

        <div class="calculator-results">
            <div class="res" id="out">
                <div class="res-empty">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.8;margin-bottom:12px"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    <div>Pick two places and tap <b>Calculate fare</b> to see route details and official pricing.</div>
                </div>
            </div>

            <div class="card map-preview-card">
                <div class="card-title-row">
                    <b>Route preview</b>
                    <span class="sm" id="rp"></span>
                </div>
                <div id="map2"></div>
            </div>
        </div>
    </div>
</section>
@endsection
@push('scripts')
<script src="{{ asset('js/calculator.js') }}"></script>
@endpush
