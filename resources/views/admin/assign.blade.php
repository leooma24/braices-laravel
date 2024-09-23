@extends('layouts.layout')

@section('title', 'BienesCorp - Asignar Paquete')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Asignar Paquete')
@section('og:description', 'Somos expertos en asesoría.')
@section('og:image', asset('public/BienesCorpLogo.png'))
@section('og:url', url()->current())

@section('content')

    <x-top-background>
        Asignar Paquete
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

            </div>

            <div class="col-md-8 col-xs-12">
                <div class="card my-3">
                    <div class="card-header">
                        <h3>Asignar Paquete</h3>
                    </div>
                    <div class="card-body">

                        <form action="{{ route('admin.users.assign.add',  $user->id ) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <x-form-select name="package_id" id="package_id" label="Paquete" :options="$packages" value="{{ $banner->package_id ?? old('package_id') }}" />
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Asignar</button>
                            </div>

                        </form>
                    </div>
                </div>


            </div>
        </div>
    </div>

@endsection
