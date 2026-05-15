@extends('layouts.layout')

@section('title', 'BienesCorp - Perfil')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Perfil')
@section('og:description', 'Somos expertos en asesoría.')
@section('og:image', asset('public/BienesCorpLogo.png'))
@section('og:url', url()->current())

@section('content')

    <x-admin-header />

    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1>{{ isset($user->id) ? 'Editar Usuario' : 'Nuevo Usuario' }}</h1>
                <p>{{ isset($user->id) ? 'Actualiza la información de la cuenta.' : 'Crea una cuenta nueva con su rol asignado.' }}</p>
            </div>
            <a href="{{ route('admin') }}" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-2"></i>Usuarios
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <div class="d-flex align-items-start gap-2 mb-2">
                    <i class="fas fa-exclamation-triangle mt-1"></i>
                    <strong>Revisa los siguientes campos:</strong>
                </div>
                <ul class="mb-0 ps-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="dashboard-card">
                    <div class="dashboard-card__header">
                        <h2><i class="fas fa-user-circle text-primary me-2"></i>Datos del usuario</h2>
                    </div>
                    <div class="dashboard-card__body">
                        <form action="{{ route('users.' . (isset($user->id) ? 'save' : 'new'),  $user->id ?? null ) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <div class="form-floating mb-3">
                                    <input name="email" type="email" class="form-control
                                        @error('email') {{ 'is-invalid' }} @enderror"
                                        id="email"
                                        aria-describedby="invalidEmail"
                                        placeholder="Correo Electrónico"
                                        value="{{ $user->email ?? old('email') }}">

                                    <label for="email">Correo Electrónico</label>
                                    @error('email')
                                        <div id="invalidEmail" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-floating mb-3">
                                    <input name="name" type="text" class="form-control
                                        @error('name') {{ 'is-invalid' }} @enderror"
                                        id="name"
                                        aria-describedby="invalidName"
                                        placeholder="Nombre Corto"
                                        value="{{ $user->name ?? old('name') }}">

                                    <label for="email">Nombre Corto</label>
                                    @error('name')
                                        <div id="invalidName" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-floating mb-3">
                                    <input name="first_name" type="text" class="form-control
                                        @error('fisrt_name') {{ 'is-invalid' }} @enderror"
                                        id="fisrt_name"
                                        aria-describedby="invalidFirst"
                                        placeholder="Nombre"
                                        value="{{ $user->first_name ?? old('first_name') }}">

                                    <label for="title">Nombre(s)</label>
                                    @error('fist_name')
                                        <div id="invalidFirst" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-floating mb-3">
                                    <input name="last_name" type="text" class="form-control
                                        @error('last_name') {{ 'is-invalid' }} @enderror"
                                        id="last_name"
                                        aria-describedby="invalidLast"
                                        placeholder="Apellidos"
                                        value="{{ $user->last_name ?? old('last_name') }}">

                                    <label for="last_name">Apellidos</label>
                                    @error('last_name')
                                        <div id="invalidLast" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-floating mb-3">
                                    <input name="phone_number" type="text" class="form-control
                                        @error('phone_number') {{ 'is-invalid' }} @enderror"
                                        id="phone_number"
                                        aria-describedby="invalidPhone"
                                        placeholder="Teléfono"
                                        value="{{ $user->phone_number ?? old('phone_number') }}">
                                    <label for="phone_number">Teléfono</label>
                                    @error('phone_number')
                                        <div id="invalidPhone" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <select name="role" class="form-select p-3  @error('roles') {{ 'is-invalid' }} @enderror"
                                    aria-label="Large select example"
                                    aria-describedby="invalidStatus"
                                    >
                                    <option value="">Selecciona un Rol</option>
                                    @foreach ($roles as $rol)
                                        <option value="{{ $rol->name }}" {{ isset($user) && $user->hasRole($rol->name) ? 'selected' : ($rol->name == old('role') ? 'selected' : '' ) }}>
                                            {{ $rol->name == 'admin' ? 'Administrador' : 'Usuario' }}</option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <div id="invalidStatus" class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-floating mb-3">
                                    <input name="password" type="password" class="form-control
                                        @error('password') {{ 'is-invalid' }} @enderror"
                                        id="password"
                                        aria-describedby="invalidPassword"
                                        placeholder="Contraseña"
                                        value="{{ old('password') }}"
                                        >

                                    <label for="password">Contraseña</label>
                                    @error('password')
                                        <div id="invalidPassword" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-floating mb-3">
                                    <input name="password_confirmation" type="password" class="form-control
                                        @error('password_confirmation') {{ 'is-invalid' }} @enderror"
                                        id="password_confirmation"
                                        aria-describedby="invalidPasswordConfirmation"
                                        placeholder="Confirmar Contraseña"
                                        value="{{ old('password_confirmation') }}"
                                        >

                                    <label for="password_confirmation">Confirmar Contraseña</label>
                                    @error('password_confirmation')
                                        <div id="invalidPasswordConfirmation" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-action-bar">
                                <a href="{{ route('admin') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-times me-1"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>{{ isset($user->id) ? 'Guardar cambios' : 'Crear usuario' }}
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
