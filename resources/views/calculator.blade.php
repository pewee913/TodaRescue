@extends('layouts.app')
@section('title', 'Fare Calculator')
@section('content')
<section class="page"><div class="top"><div><h2>Fare Calculator</h2><p>Choose where you are riding from and to, and we will estimate the fare.</p></div></div>
<div class="two"><form id="cf" class="card" style="align-self:start"><h3 style="margin:0;font-size:24px">Trip details</h3>
@foreach(['f' => ['Pick-up point', 'from'], 't' => ['Drop-off point', 'to']] as $name => [$label, $qs])
<label>{{ $label }}</label><select name="{{ $name }}" required><option value="">Select location</option>
@foreach($places as $p)<option value="{{ $p->id }}" data-lat="{{ $p->lat }}" data-lng="{{ $p->lng }}" @selected(request($qs) == $p->id)>{{ $p->name }}</option>@endforeach</select>
@endforeach
<label>Passengers</label><input type="number" name="n" value="1" min="1" max="6" style="width:100%;padding:15px;border:1px solid var(--bd);border-radius:12px;font:16px inherit">
<label>Trip type</label><select name="special"><option value="0">Regular</option><option value="1">Special trip</option></select>
<label>Passenger type</label><select name="type"><option value="regular">Regular</option><option value="student">Student</option><option value="senior">Senior</option><option value="pwd">PWD</option></select>
<button class="btn" style="width:100%;margin-top:18px">Calculate fare</button></form>
<div><div class="res" id="out">Pick two places and tap Calculate fare.</div>
<div class="card" style="margin-top:20px;padding:16px"><b>Route preview</b><span class="sm" id="rp" style="float:right"></span><div id="map2" style="margin-top:12px"></div></div></div></div></section>
@endsection
@push('scripts')<script src="{{ asset('js/calculator.js') }}"></script>@endpush
