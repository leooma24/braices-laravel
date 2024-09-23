@extends('layouts.layout')

@section('title', 'BienesCorp - Administración de Bienes Inmuebles')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Administración de Bienes Inmuebles')
@section('og:description', 'Administración de Bienes Inmuebles')
@section('og:image', asset('BienesCorpLogo.png') )
@section('og:url', url()->current())

@section('content')

    <x-carrusel :images="$banners" />


    <div class="container">
        <!-- Success message -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <x-popular-properties :properties="$newestProperties">
        Propiedades Recientes
    </x-popular-properties>

    <x-characteristics />

    <x-prices :packages="$packages"  />

    <x-background-with-text />



@endsection
