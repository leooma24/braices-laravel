@extends('layouts.layout')

@section('title', 'BienesCorp - Publica tu propiedad')
@section('description', 'Publica tu casa, terreno o local en BienesCorp y mide cuánta gente la ve. Sin contratos forzosos.')
@section('og:title', 'Publica tu propiedad en BienesCorp')
@section('og:description', 'Publica tu casa, terreno o local y mide cuánta gente la ve.')
@section('og:image', asset('BienesCorpLogo.png'))
@section('og:url', url()->current())

@section('content')

    <section class="publish-hero">
        {{-- Mejor una propiedad real del catalogo que una foto de stock: la
             pagina promete justo eso. --}}
        <div class="publish-hero__bg" style="background-image: url('{{ $sample?->coverUrl() ?? asset('roberto-nickson-smJ6XsYy8gA-unsplash.jpg') }}');"></div>
        <div class="publish-hero__overlay"></div>

        <div class="container publish-hero__inner">
            <div class="publish-hero__copy">
                <span class="publish-hero__eyebrow">Para dueños y asesores</span>
                <h1 class="publish-hero__title">Publica tu propiedad y mira quién la está viendo</h1>
                <p class="publish-hero__lead">
                    Tu casa, terreno o local con ficha propia, ubicación en mapa y contacto directo por WhatsApp.
                </p>
                <div class="publish-hero__actions">
                    <a href="{{ Auth::check() ? route('properties.new') : route('register') }}" class="btn btn-accent btn-lg">
                        Publicar ahora
                    </a>
                    <a href="#audiencia" class="btn btn-outline-light btn-lg">Ver los números</a>
                </div>
            </div>
        </div>
    </section>

    @if($audience['total'] > 0 && $audience['average'] > 0 && $audience['days'] >= 3)
        <section class="publish-numbers" id="audiencia">
            <div class="container">
                <h2 class="publish-section-title">Cuánta gente va a ver tu propiedad</h2>
                <p class="publish-section-lead">
                    No es una promesa. Son las visitas que medimos en el sitio durante los últimos
                    {{ $audience['days'] }} {{ $audience['days'] == 1 ? 'día' : 'días' }}.
                </p>

                <div class="publish-numbers__grid">
                    <div class="publish-figure publish-figure--lead">
                        <span class="publish-figure__value">{{ number_format($audience['total']) }}</span>
                        <span class="publish-figure__label">visitas a nuestras propiedades</span>
                    </div>
                    <div class="publish-figure">
                        <span class="publish-figure__value">{{ number_format($audience['average']) }}</span>
                        <span class="publish-figure__label">en promedio por propiedad</span>
                    </div>
                    <div class="publish-figure">
                        <span class="publish-figure__value">{{ number_format($audience['best']) }}</span>
                        <span class="publish-figure__label">la más vista del periodo</span>
                    </div>
                </div>

                <p class="publish-numbers__note">
                    Cada propiedad publicada guarda su propio conteo diario. Vas a poder ver el de la tuya
                    desde tu cuenta, sin pedírselo a nadie.
                </p>
            </div>
        </section>
    @endif

    @if($sample)
        <section class="publish-sample">
            <div class="container">
                <div class="row align-items-center g-4 g-lg-5">
                    <div class="col-12 col-lg-6">
                        <h2 class="publish-section-title">Así se va a ver</h2>
                        <p class="publish-section-lead">
                            Ficha con galería de fotos, medidas, mapa, y una tarjeta lista para que la
                            mandes por WhatsApp. Esta es una propiedad real publicada hoy, no un ejemplo armado.
                        </p>
                        <a href="{{ route('property', $sample->slug) }}" class="btn btn-primary">
                            Abrir esta ficha
                        </a>
                    </div>

                    <div class="col-12 col-lg-6">
                        <article class="property-card card publish-sample__card">
                            <a href="{{ route('property', $sample->slug) }}" class="property-card__media" tabindex="-1" aria-hidden="true">
                                <img src="{{ $sample->coverUrl() }}" alt="{{ $sample->title }}" width="400" height="280" loading="lazy">
                                <div class="types property-card__tags">
                                    @foreach($sample->propertyTypes as $sampleType)
                                        <span class="badge">{{ $sampleType->name }}</span>
                                    @endforeach
                                    <span class="badge">{{ $sample->transaction->name }}</span>
                                </div>
                            </a>
                            <div class="card-body property-card__body">
                                <h3 class="property-card__title">{{ $sample->title }}</h3>
                                <p class="property-card__price mb-0">${{ number_format($sample->price) }}</p>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="publish-steps">
        <div class="container">
            <h2 class="publish-section-title">Publicar toma unos minutos</h2>

            <ol class="publish-steps__list">
                <li>
                    <strong>Crea tu cuenta.</strong>
                    Con correo o con Facebook. El plan gratuito no pide tarjeta.
                </li>
                <li>
                    <strong>Sube las fotos y los datos.</strong>
                    Desde el celular. Entre más fotos, más visitas recibe la ficha.
                </li>
                <li>
                    <strong>Queda publicada.</strong>
                    Con su liga propia para compartirla donde quieras, y el contador de visitas corriendo.
                </li>
            </ol>
        </div>
    </section>

    <x-prices :packages="$packages" />

@endsection
