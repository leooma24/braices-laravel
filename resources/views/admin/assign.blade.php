@extends('layouts.layout')

@section('title', 'BienesCorp - Asignar Paquete')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Asignar Paquete')
@section('og:description', 'Somos expertos en asesoría.')
@section('og:image', asset('public/BienesCorpLogo.png'))
@section('og:url', url()->current())

@section('content')

    <x-admin-header />

    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1>Asignar Paquete</h1>
                <p>Asigna un paquete a <strong>{{ $user->name }}</strong> ({{ $user->email }}).</p>
            </div>
            <a href="{{ route('admin') }}" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-2"></i>Usuarios
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="dashboard-card">
                    <div class="dashboard-card__header">
                        <h2><i class="fas fa-box text-primary me-2"></i>Selecciona el paquete</h2>
                    </div>
                    <div class="dashboard-card__body">
                        <form action="{{ route('admin.users.assign.add',  $user->id ) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <x-form-select name="package_id" id="package_id" label="Paquete" :options="$packages" value="{{ old('package_id') }}" />
                            </div>

                            <div class="form-action-bar">
                                <a href="{{ route('admin') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-times me-1"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-check me-2"></i>Asignar paquete
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
