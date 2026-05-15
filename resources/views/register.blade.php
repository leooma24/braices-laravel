@extends('layouts.layout')

@section('title', 'BienesCorp - Registrarse')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Registrarse')
@section('og:description', 'Administración de Bienes Inmuebles')
@section('og:image', asset('BienesCorpLogo.png'))
@section('og:url', url()->current())

@section('content')

<div class="auth-shell">
    <div class="container">
        <div class="auth-card auth-card--wide">
            <div class="auth-card__brand">
                <img src="{{ asset('BienesCorpLogo.png') }}" alt="BienesCorp">
            </div>
            <h1 class="auth-card__title">Crea tu cuenta</h1>
            <p class="auth-card__subtitle">Es gratis. Publica tu primera propiedad en minutos.</p>

            @if (session('error'))
                <div class="alert alert-danger mb-3">{{ session('error') }}</div>
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

            <form method="POST" action="{{ route('register.post') }}">
                @csrf

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input id="first_name" type="text"
                                class="form-control @error('first_name') is-invalid @enderror"
                                name="first_name" placeholder="Nombre(s)"
                                value="{{ old('first_name') }}" required autofocus>
                            <label for="first_name">Nombre(s)</label>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input id="last_name" type="text"
                                class="form-control @error('last_name') is-invalid @enderror"
                                name="last_name" placeholder="Apellido(s)"
                                value="{{ old('last_name') }}" required>
                            <label for="last_name">Apellido(s)</label>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-floating mb-3">
                    <input id="email" type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        name="email" placeholder="Correo electrónico"
                        value="{{ old('email') }}" required>
                    <label for="email">Correo electrónico</label>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input id="password" type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                name="password" placeholder="Contraseña" required>
                            <label for="password">Contraseña</label>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input id="password-confirm" type="password" class="form-control"
                                name="password_confirmation" placeholder="Confirmar" required>
                            <label for="password-confirm">Confirmar contraseña</label>
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Crear cuenta
                    </button>
                </div>
            </form>

            <div class="auth-footer">
                ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
            </div>
        </div>
    </div>
</div>

@endsection
