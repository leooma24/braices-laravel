@extends('layouts.layout')

@section('title', 'BienesCorp — Compra, vende y renta propiedades en México')
@section('description', 'Encuentra tu próximo hogar o el inversionista para tu propiedad. Casas, departamentos, terrenos y renta vacacional verificadas.')
@section('og:title', 'BienesCorp — Inmuebles en México')
@section('og:description', 'Encuentra tu próximo hogar. Casas, departamentos, terrenos y renta vacacional verificadas en todo México.')
@section('og:image', asset('BienesCorpLogo.png'))
@section('og:url', url()->current())

@section('content')

    {{-- ========= HERO con buscador prominente ========= --}}
    <section class="hero-search-section">
        <div class="hero-bg" style="background-image: url('{{ asset('JPG-12.jpg') }}');"></div>
        <div class="hero-overlay"></div>

        <div class="hero-content container">
            <div class="text-center text-white mb-4 fade-in-up">
                <h1 class="hero-title mb-3">Encuentra el inmueble <span class="text-accent">perfecto</span></h1>
                <p class="hero-subtitle mb-0">
                    {{ ($stats['properties'] ?? 0) > 0 ? number_format($stats['properties']) . ' propiedades verificadas' : 'Cientos de propiedades verificadas' }}
                    en toda la República Mexicana
                </p>
            </div>

            <form class="hero-search-form fade-in-up" action="{{ route('properties') }}" method="GET">
                <div class="search-grid">
                    <div class="search-field">
                        <label class="search-label"><i class="fas fa-building me-1"></i> Tipo</label>
                        <select name="tipo" class="form-select form-select-lg">
                            <option value="">Todos los tipos</option>
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="search-field">
                        <label class="search-label"><i class="fas fa-tag me-1"></i> Operación</label>
                        <select name="tipo_transaccion" class="form-select form-select-lg">
                            <option value="">Venta o renta</option>
                            @foreach ($transactions as $transaction)
                                <option value="{{ $transaction->id }}">{{ $transaction->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="search-field">
                        <label class="search-label"><i class="fas fa-money-bill-wave me-1"></i> Precio máximo</label>
                        <input type="number" name="precio_maximo" class="form-control form-control-lg" placeholder="$ Sin límite">
                    </div>

                    <button type="submit" class="btn btn-accent btn-lg search-btn">
                        <i class="fas fa-search me-2"></i>Buscar
                    </button>
                </div>
            </form>

            <div class="text-center mt-3">
                <a href="{{ route('reservations') }}" class="hero-secondary-link">
                    <i class="fas fa-bed me-1"></i> ¿Buscas renta por noche? Reserva una estancia →
                </a>
            </div>
        </div>
    </section>

    {{-- ========= Señales de confianza =========
         Antes esto eran cuatro contadores (13 propiedades, 3 ciudades, 4
         agentes) que comunicaban lo contrario de lo que queremos: que el sitio
         esta vacio. Se deja un solo numero, el unico grande y verdadero que
         tenemos, y tres promesas concretas que si podemos sostener. --}}
    <section class="stats-bar">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <div class="stat-number">{{ number_format($stats['views'] ?? 0) }}</div>
                        <div class="stat-label">Visitas a nuestras propiedades</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <div class="stat-number stat-number--word">WhatsApp</div>
                        <div class="stat-label">Directo con el asesor</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <div class="stat-number stat-number--word">En el mapa</div>
                        <div class="stat-label">Ubicación exacta en cada ficha</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <div class="stat-number stat-number--word">Sin plazos</div>
                        <div class="stat-label">Publica y quita cuando quieras</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Flash messages --}}
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-4" role="status" aria-live="polite">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-4" role="alert" aria-live="assertive">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    {{-- ========= Categorías populares con iconos ========= --}}
    @if(!empty($popularCategories))
    <section class="categories-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Explora por categoría</h2>
                <p class="section-subtitle text-muted-2">Encuentra exactamente lo que buscas</p>
            </div>

            <div class="row g-4">
                @foreach($popularCategories as $cat)
                <div class="col-6 col-md-3">
                    <a href="{{ route('properties') }}?categoria={{ $cat['slug'] }}" class="category-card">
                        <div class="category-icon">
                            <i class="fas {{ $cat['icon'] }}"></i>
                        </div>
                        <div class="category-label">{{ $cat['label'] }}</div>
                        <div class="category-count">{{ $cat['count'] }} propiedad{{ $cat['count'] === 1 ? '' : 'es' }}</div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ========= Propiedades: recientes y mas vistas en pestañas ========= --}}
    <x-property-tabs :recent="$newestProperties" :popular="$popularProperties" />

    {{-- ========= Por qué BienesCorp ========= --}}
    <x-characteristics />

    {{-- ========= Bloque de marketing ========= --}}
    <x-background-with-text />

    {{-- ========= Planes / Precios ========= --}}
    <x-prices :packages="$packages" />

@endsection
