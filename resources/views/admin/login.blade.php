@extends('layouts.admin')
@section('title', 'Admin Login')
@section('content')
<section class="page admin-login-page">
    <div class="admin-login-card">
        <h2>TODA Rescue Admin</h2>
        <p>Sign in to manage TODA terminal pinpoints on the map.</p>

        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.authenticate') }}">
            @csrf

            <div class="field">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" required value="{{ old('email') }}" placeholder="admin@example.com">
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-full">Sign in</button>
        </form>

        <p class="sm" style="margin-top:12px;text-align:center;">
            <a href="{{ route('home') }}">← Back to site</a>
        </p>
    </div>
</section>
@endsection