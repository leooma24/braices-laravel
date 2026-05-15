@extends('layouts.layout')

@section('title', 'BienesCorp - Nosotros')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Administración de Bienes Inmuebles')
@section('og:description', 'Administración de Bienes Inmuebles')
@section('og:image', asset('BienesCorpLogo.png') )
@section('og:url', url()->current())

@section('content')

    <x-top-background
        :image="asset('JPG-11.jpg')"
        eyebrow="Conócenos"
        subtitle="Más de 25 años desarrollando soluciones digitales para el sector inmobiliario.">
        Sobre Nosotros
    </x-top-background>

    <div class="container">
        <p class="page-intro">
            Somos una plataforma desarrollada por <strong>Medios Corp. Agencia Digital</strong>, con
            más de 25 años de experiencia en desarrollo web. Nuestro objetivo es brindarle a los agentes
            y empresas del ramo inmobiliario un servicio de administración de propiedades en la nube,
            en tiempo real, que reduce tiempos al hacer difusión en sitios web, redes sociales y
            mensajeros.
        </p>

        <div class="feature-grid mb-5">
            <div class="feature-card">
                <div class="feature-card__icon">
                    <img src="{{ asset('SVG-26.svg') }}" alt="Misión">
                </div>
                <h3 class="feature-card__title">Misión</h3>
                <p>
                    Brindar soluciones integrales y confiables en la administración de bienes
                    inmuebles, maximizando el valor de las propiedades de nuestros clientes a través
                    de un servicio personalizado y eficiente.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-card__icon">
                    <img src="{{ asset('SVG-27.svg') }}" alt="Visión">
                </div>
                <h3 class="feature-card__title">Visión</h3>
                <p>
                    Ser la empresa líder en la administración de bienes inmuebles a nivel nacional,
                    reconocida por nuestra excelencia, innovación y compromiso con la satisfacción
                    de nuestros clientes.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-card__icon">
                    <img src="{{ asset('SVG-28.svg') }}" alt="Valores">
                </div>
                <h3 class="feature-card__title">Valores</h3>
                <ul>
                    <li>Integridad</li>
                    <li>Compromiso</li>
                    <li>Innovación</li>
                    <li>Profesionalismo</li>
                    <li>Trabajo en equipo</li>
                </ul>
            </div>
        </div>
    </div>

@endsection
