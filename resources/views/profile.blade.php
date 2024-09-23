@extends('layouts.layout')

@section('title', 'BienesCorp - Perfil')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Perfil')
@section('og:description', 'Somos expertos en asesoría.')
@section('og:image', asset('public/BienesCorpLogo.png'))
@section('og:url', url()->current())

@section('content')

    <x-top-background :image="asset('JPG-12.jpg')">
        Perfil
    </x-top-background>

    <div class="container">
        <div class="row ">
            @if ($errors->any())
            <div class="col-xs-12">
                <div class="alert alert-danger mt-3">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
            @if (session('success'))
            <div class="col-xs-12">
                <div class="alert alert-success my-3">
                    {{ session('success') }}
                </div>
            </div>
            @endif
            @if (session('error'))
            <div class="col-xs-12">
                <div class="alert alert-danger my-3">
                    {{ session('error') }}
                </div>
            </div>
            @endif
            <div class="col-md-4 col-xs-12">
                <div class="card my-3">
                    <div class="card-header">
                        <h3>Perfil</h3>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ asset(auth()->user()->photo) }}" class="img-fluid rounded-circle" width="50%" alt="Logo">

                        <h4 class="mt-3 mb-0">{{ auth()->user()->name }}</h4>
                        <small class="text-secondary">Ejecutivo de ventas</small>
                    </div>

                    <div class="separator"></div>

                    <div class="d-flex">
                        <a href="mailto:{{ auth()->user()->email }}" class="btn w-100 border rounded-0 py-3"><i class="fa fa-envelope"></i> Email </a>
                        <a href="tel:{{ auth()->user()->phone_number }}" class="btn w-100 border rounded-0 py-3"><i class="fa fa-phone"></i> Llamar</a>
                    </div>
                </div>
            </div>

            <div class="col-md-8 col-xs-12">
                <div class="card my-3">
                    <div class="card-header">
                        <h3>Datos generales</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <div class="form-floating mb-3">
                                    <input name="name" type="text" class="form-control
                                        @error('name') {{ 'is-invalid' }} @enderror"
                                        id="name"
                                        aria-describedby="invalidName"
                                        placeholder="Nombre Corto"
                                        value="{{ auth()->user()->name }}">

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
                                        value="{{ auth()->user()->first_name }}">

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
                                        value="{{ auth()->user()->last_name }}">

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
                                        value="{{ auth()->user()->phone_number }}">
                                    <label for="phone_number">Teléfono</label>
                                    @error('phone_number')
                                        <div id="invalidPhone" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <x-form-input name="email" label="Correo Electrónico" type="email" value="{{ auth()->user()->email }}" />
                            </div>

                            <div class="mb-3">
                                <div class="form-floating mb-3">
                                    <input name="facebook" type="text" class="form-control
                                        @error('facebook') {{ 'is-invalid' }} @enderror"
                                        id="facebook"
                                        aria-describedby="invalidFacebook"
                                        placeholder="Facebook"
                                        value="{{ auth()->user()->facebook }}">
                                    <label for="facebook">Facebook</label>
                                    @error('facebook')
                                        <div id="invalidFacebook" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-floating mb-3">
                                    <input name="x" type="text" class="form-control
                                        @error('x') {{ 'is-invalid' }} @enderror"
                                        id="x"
                                        aria-describedby="invalidX"
                                        placeholder="X"
                                        value="{{ auth()->user()->x }}">
                                    <label for="x">X</label>
                                    @error('x')
                                        <div id="invalidX" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-floating mb-3">
                                    <input name="instagram" type="text" class="form-control
                                        @error('instagram') {{ 'is-invalid' }} @enderror"
                                        id="instagram"
                                        aria-describedby="invalidInstagram"
                                        placeholder="Instagram"
                                        value="{{ auth()->user()->instagram }}">
                                    <label for="instagram">Instagram</label>
                                    @error('instagram')
                                        <div id="invalidInstagram" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-floating mb-3">
                                    <input name="tiktok" type="text" class="form-control
                                        @error('tiktok') {{ 'is-invalid' }} @enderror"
                                        id="tiktok"
                                        aria-describedby="invalidTiktok"
                                        placeholder="Tiktok"
                                        value="{{ auth()->user()->tiktok }}">
                                    <label for="tiktok">Tiktok</label>
                                    @error('tiktok')
                                        <div id="invalidTiktok" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Actualizar</button>
                            </div>

                        </form>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h3>Actualizar Imagen</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('profile.update.photo') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <div class="form-floating mb-3">
                                    <input name="photo" type="file" class="form-control
                                        @error('photo') {{ 'is-invalid' }} @enderror"
                                        id="photo"
                                        aria-describedby="invalidPhoto"
                                        placeholder="Imagen">

                                    <label for="photo">Imagen</label>
                                    @error('photo')
                                        <div id="invalidPhoto" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Actualizar</button>
                            </div>

                        </form>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h3>Cambiar Contraseña</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('profile.update.password') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <div class="form-floating mb-3">
                                    <input name="current_password" type="password" class="form-control
                                        @error('current_password') {{ 'is-invalid' }} @enderror"
                                        id="current_password"
                                        aria-describedby="invalidCurrent"
                                        placeholder="Contraseña Actual"
                                        value="{{ old('current_password') }}"
                                        >

                                    <label for="current_password">Contraseña Actual</label>
                                    @error('current_password')
                                        <div id="invalidCurrent" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-floating mb-3">
                                    <input name="password" type="password" class="form-control
                                        @error('password') {{ 'is-invalid' }} @enderror"
                                        id="password"
                                        aria-describedby="invalidPassword"
                                        placeholder="Nueva Contraseña"
                                        value="{{ old('password') }}"
                                        >

                                    <label for="password">Nueva Contraseña</label>
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

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Actualizar</button>
                            </div>

                        </form>
                    </div>
                </div>


            </div>
        </div>
    </div>

@endsection
