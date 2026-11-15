@extends('layouts.app')
@section('content')
<section class="hero">
    <div class="hero-content">
        <span class="pill">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-1px;margin-right:2px"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            Tricycle fares made simple
        </span>
        <h1>Know your fare.<br>Find your TODA.</h1>
        <p>Estimate your tricycle fare, locate the nearest TODA terminal on a live map, and check the fare tables before you ride.</p>
        <div class="hero-actions">
            <a class="btn" href="{{ route('calculator') }}">Calculate fare</a>
            <a class="btn w" href="{{ route('find') }}">Find a TODA</a>
        </div>
    </div>

    <form class="card hero-card" method="GET" action="{{ route('calculator') }}">
        <h3>Quick fare estimate</h3>
        <p class="card-subtitle">Get instant calculation between any two stops</p>
        
        <label for="from_select">From</label>
        <select id="from_select" name="from" required>
            <option value="">Select pick-up point</option>
            @foreach($places as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
        </select>

        <label for="to_select">To</label>
        <select id="to_select" name="to" required>
            <option value="">Select drop-off point</option>
            @foreach($places as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
        </select>

        <button type="submit" class="btn btn-full">Calculate fare</button>
    </form>
</section>
@endsection
