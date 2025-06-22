@extends('front.layouts.app')

@section('content')
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="/"><b>Appointment</b>System</a>
        
        <div class="ml-auto">
            @if (Route::has('login'))
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-light">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-light mr-2">Login</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-light">Register</a>
                    @endif
                @endauth
            @endif
        </div>
    </div>
</nav>
<div class="hero-section py-5 bg-light">
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <div class="col-lg-6">
                <h1 class="display-4 font-weight-bold mb-4">Welcome to Our Appointment System</h1>
                <p class="lead mb-4">Streamline your scheduling process with our efficient and user-friendly appointment management system.</p>
                <div class="mt-4">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg mr-3">Get Started</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .hero-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    .navbar {
        box-shadow: 0 2px 4px rgba(0,0,0,.1);
    }
</style>
@endsection