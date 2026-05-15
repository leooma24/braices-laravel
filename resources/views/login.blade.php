@extends('layouts.layout')

@section('title', 'BienesCorp - Iniciar sesión')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Iniciar sesión')
@section('og:description', 'Administración de Bienes Inmuebles')
@section('og:image', asset('BienesCorpLogo.png'))
@section('og:url', url()->current())

@section('content')

<div class="auth-shell">
    <div class="container">
        <div class="auth-card">
            <div class="auth-card__brand">
                <img src="{{ asset('BienesCorpLogo.png') }}" alt="BienesCorp">
            </div>
            <h1 class="auth-card__title">Bienvenido de vuelta</h1>
            <p class="auth-card__subtitle">Inicia sesión para administrar tus propiedades.</p>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger mb-3">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-floating mb-3">
                    <input id="email" type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        name="email" placeholder="Correo electrónico"
                        value="{{ old('email') }}" required autocomplete="email" autofocus>
                    <label for="email">Correo electrónico</label>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-floating mb-3">
                    <input id="password" type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        name="password" placeholder="Contraseña"
                        required autocomplete="current-password">
                    <label for="password">Contraseña</label>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid mb-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Entrar
                    </button>
                </div>
            </form>

            <div class="auth-divider">o continúa con</div>

            <a href="{{ route('login.facebook') }}" class="btn w-100 d-flex align-items-center justify-content-center gap-2"
               style="background: #1877F2; color: #fff; padding: 0.75rem;">
                <i class="fab fa-facebook"></i> Facebook
            </a>

            <div class="auth-footer">
                ¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate gratis</a>
            </div>
        </div>
    </div>
</div>

@endsection
