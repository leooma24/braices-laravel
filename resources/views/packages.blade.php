@extends('layouts.layout')

@section('title', 'BienesCorp - Planes')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Administración de Bienes Inmuebles')
@section('og:description', 'Administración de Bienes Inmuebles')
@section('og:image', asset('BienesCorpLogo.png') )
@section('og:url', url()->current())

@section('content')

    <x-top-background
        :image="asset('JPG-11.jpg')"
        eyebrow="Planes y precios"
        subtitle="Elige el paquete que mejor se ajuste a tu volumen de propiedades. Sin contratos forzosos.">
        Nuestros Planes
    </x-top-background>

    <x-prices :packages="$packages"  />

@endsection
