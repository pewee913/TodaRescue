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
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div class="quick-card-body">
                <h3>Live TODA Terminal Map</h3>
                <p>Pinpoint the nearest tricycle terminal, view unit capacity, and get live GPS walking directions.</p>
            </div>
            <span class="quick-card-arrow">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </span>
        </a>

        <a href="{{ route('tables') }}" class="home-quick-card">
            <div class="quick-card-icon" style="background:#e6f6f5;color:var(--t);">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18M3 15h18M9 3v18"/></svg>
            </div>
            <div class="quick-card-body">
                <h3>Official Fare Tables</h3>
                <p>San Pablo City Ordinance 72 rates with regular fares and 20% discount calculations.</p>
            </div>
            <span class="quick-card-arrow">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </span>
        </a>

        <div class="home-quick-card hotline-card">
            <div class="quick-card-icon" style="background:#fef3c7;color:#b45309;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </div>
            <div class="quick-card-body">
                <h3>Passenger Concerns & Hotline</h3>
                <p>San Pablo City Traffic Management (SPCTMO): <a href="tel:0495032200" style="color:var(--primary);font-weight:700">(049) 503-2200</a></p>
            </div>
        </div>
    </div>
</section>
@endsection
