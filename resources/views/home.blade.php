@extends('layouts.app')
@section('content')
<section class="hero">
    <div class="hero-content">
        <span class="pill">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-1px;margin-right:2px"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            Tricycle fares made simple
        </span>
        <h1>Know your fare.<br>Find your TODA.</h1>
        <p>Estimate your tricycle fare, locate the nearest TODA terminal on a live map, and check official fare tables before you ride.</p>
        <div class="hero-actions">
            <a class="btn" href="{{ route('find') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                Find a TODA
            </a>
            <a class="btn w" href="{{ route('tables') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18M3 15h18M9 3v18"/></svg>
                View Fare Tables
            </a>
        </div>
    </div>
</section>

<section class="page home-quick-section">
    <div class="home-quick-grid">
        <a href="{{ route('find') }}" class="home-quick-card">
            <div class="quick-card-icon" style="background:#fff2ea;color:var(--primary);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div class="quick-card-body">
                <h3>Live TODA Terminal Map</h3>
                <p>Find nearby terminals, see walking distance, and get turn-by-turn guidance.</p>
            </div>
            <span class="quick-card-arrow">→</span>
        </a>

        <a href="{{ route('tables') }}" class="home-quick-card">
            <div class="quick-card-icon" style="background:#e6f6f5;color:var(--t);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18M3 15h18M9 3v18"/></svg>
            </div>
            <div class="quick-card-body">
                <h3>Official Fare Tables</h3>
                <p>Browse official San Pablo City rates (Ordinance 72) with 20% discounts.</p>
            </div>
            <span class="quick-card-arrow">→</span>
        </a>
    </div>

    <div class="home-hotline-note">
        <span>Need assistance? Contact SPCTMO: <a href="tel:0495032200">(049) 503-2200</a></span>
    </div>
</section>
@endsection
