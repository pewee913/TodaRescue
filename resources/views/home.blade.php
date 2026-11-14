@extends('layouts.app')
@section('content')
<section class="hero"><div><span class="pill">Tricycle fares made simple</span><h1>Know your fare.<br>Find your TODA.</h1>
<p>Estimate your tricycle fare, locate the nearest TODA terminal on a live map, and check the fare tables before you ride.</p>
<a class="btn" href="{{ route('calculator') }}" style="text-decoration:none;display:inline-block">Calculate fare</a>
<a class="btn w" href="{{ route('find') }}" style="text-decoration:none;display:inline-block">Find a TODA</a></div>
<form class="card" method="GET" action="{{ route('calculator') }}"><h3 style="font-size:24px;margin:0">Quick fare estimate</h3>
<label>From</label><select name="from" required><option value="">Select pick-up point</option>@foreach($places as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select>
<label>To</label><select name="to" required><option value="">Select drop-off point</option>@foreach($places as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select>
<button class="btn" style="width:100%;margin-top:22px">Calculate fare</button></form></section>
@endsection
