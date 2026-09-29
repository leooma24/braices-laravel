@extends('layouts.layout')

@section('title', 'BienesCorp - Propiedades')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Propiedades')
@section('og:description', 'Administración de Bienes Inmuebles')
@section('og:image', asset('public/BienesCorpLogo.png'))
@section('og:url', url()->current())

@section('content')

    <x-top-background
        :image="asset('JPG-12.jpg')"
        eyebrow="Inventario verificado"
        subtitle="Casas, departamentos, terrenos y locales — usa los filtros para encontrar el tuyo.">
        Propiedades
    </x-top-background>

    <div class="container">
        <div class="filters filters--floating">
            <form action="{{ route('properties') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-3">
                        <label for="ubicacion" class="form-label small text-muted-2 mb-1">Ubicación</label>
                        <input id="ubicacion" name="ubicacion" type="search" class="form-control"
                            value="{{ $data['ubicacion'] ?? '' }}"
                            placeholder="Ciudad, colonia o calle…">
                    </div>

                    <div class="col-12 col-md-6 col-lg-2">
                        <label for="tipo" class="form-label small text-muted-2 mb-1">Tipo de propiedad</label>
                        <select id="tipo" name="tipo" class="form-select">
                            <option value="">Todos los tipos</option>
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}" {{ isset($data['tipo']) && $type->id == $data['tipo'] ? 'selected' : ''}}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-lg-2">
                        <label for="tipo_transaccion" class="form-label small text-muted-2 mb-1">Transacción</label>
                        <select id="tipo_transaccion" name="tipo_transaccion" class="form-select">
                            <option value="">Cualquiera</option>
                            @foreach ($transactions as $transaction)
                                <option value="{{ $transaction->id }}" {{ isset($data['tipo_transaccion']) && $transaction->id == $data['tipo_transaccion'] ? 'selected' : ''}}>{{ $transaction->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-lg-2">
                        <label for="precio_minimo" class="form-label small text-muted-2 mb-1">Desde</label>
                        <input id="precio_minimo" name="precio_minimo" value="{{ $data['precio_minimo'] ?? '' }}" type="number" inputmode="numeric" class="form-control" placeholder="$0">
                    </div>

                    <div class="col-6 col-lg-2">
                        <label for="precio_maximo" class="form-label small text-muted-2 mb-1">Hasta</label>
                        <input id="precio_maximo" name="precio_maximo" value="{{ $data['precio_maximo'] ?? '' }}" type="number" inputmode="numeric" class="form-control" placeholder="Sin tope">
                    </div>

                    <div class="col-12 col-lg-1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <span class="d-lg-none ms-2">Buscar</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Resumen de la busqueda: cuantos resultados hay, que filtros estan
             puestos (y como quitarlos de a uno) y con que criterio se ordena. --}}
        @php
            $activeFilters = [];
            if (!empty($data['ubicacion'])) {
                $activeFilters[] = ['label' => $data['ubicacion'], 'key' => 'ubicacion'];
            }
            if (!empty($data['tipo'])) {
                $activeFilters[] = ['label' => optional($types->firstWhere('id', (int) $data['tipo']))->name, 'key' => 'tipo'];
            }
            if (!empty($data['tipo_transaccion'])) {
                $activeFilters[] = ['label' => optional($transactions->firstWhere('id', (int) $data['tipo_transaccion']))->name, 'key' => 'tipo_transaccion'];
            }
            if (!empty($data['precio_minimo'])) {
                $activeFilters[] = ['label' => 'Desde $' . number_format((float) $data['precio_minimo']), 'key' => 'precio_minimo'];
            }
            if (!empty($data['precio_maximo'])) {
                $activeFilters[] = ['label' => 'Hasta $' . number_format((float) $data['precio_maximo']), 'key' => 'precio_maximo'];
            }
            $orden = $data['orden'] ?? 'recientes';
        @endphp

        <div class="results-bar">
            <p class="results-bar__count" aria-live="polite">
                <strong>{{ number_format($list->total()) }}</strong>
                {{ $list->total() === 1 ? 'propiedad encontrada' : 'propiedades encontradas' }}
            </p>

            <form action="{{ route('properties') }}" method="GET" class="results-bar__sort">
                @foreach (['ubicacion', 'tipo', 'tipo_transaccion', 'precio_minimo', 'precio_maximo'] as $keep)
                    @if (!empty($data[$keep]))
                        <input type="hidden" name="{{ $keep }}" value="{{ $data[$keep] }}">
                    @endif
                @endforeach
                <label for="orden" class="form-label small text-muted-2 mb-0">Ordenar por</label>
                <select id="orden" name="orden" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="recientes" {{ $orden === 'recientes' ? 'selected' : '' }}>Más recientes</option>
                    <option value="precio_asc" {{ $orden === 'precio_asc' ? 'selected' : '' }}>Precio: menor a mayor</option>
                    <option value="precio_desc" {{ $orden === 'precio_desc' ? 'selected' : '' }}>Precio: mayor a menor</option>
                </select>
                <noscript><button type="submit" class="btn btn-sm btn-outline-primary">Aplicar</button></noscript>
            </form>
        </div>

        @if (count($activeFilters))
            <div class="active-filters">
                @foreach ($activeFilters as $filter)
                    @if ($filter['label'])
                        <a class="active-filters__chip"
                           href="{{ route('properties', collect($data)->except($filter['key'], 'page')->filter()->all()) }}">
                            {{ $filter['label'] }}
                            <span aria-hidden="true">&times;</span>
                            <span class="visually-hidden">Quitar filtro</span>
                        </a>
                    @endif
                @endforeach
                <a class="active-filters__clear" href="{{ route('properties') }}">Limpiar todo</a>
            </div>
        @endif

        <div class="row g-4 mt-0 mb-2">
            @foreach ($list as $property)
                <div class="col-12 col-md-6 col-lg-4">
                    @php($soldOut = $property->soldOutLabel())
                    <article class="property-card card h-100 {{ $property->isFeaturedNow() ? 'card-featured' : '' }} {{ $soldOut ? 'card-sold-out' : '' }}">
                        {{-- La media va en un contenedor de alto fijo: si falta la
                             foto o el archivo se perdio, la tarjeta conserva su
                             tamano en vez de encogerse y desacomodar la fila. --}}
                        <a href="{{ route('property', $property->slug) }}" class="property-card__media" tabindex="-1" aria-hidden="true">
                            @if($property->hasPhoto())
                                <img src="{{ $property->photo_main }}" alt="" width="400" height="280" loading="lazy"
                                     onerror="this.closest('.property-card__media').classList.add('is-empty'); this.remove();">
                            @endif
                            <span class="property-card__placeholder" aria-hidden="true">
                                <i class="fas fa-camera"></i>
                                <small>Sin fotografía</small>
                            </span>

                            @if($soldOut)
                                <span class="sold-out-ribbon">{{ $soldOut }}</span>
                            @endif

                            @if($property->isFeaturedNow())
                                <span class="badge featured-badge property-card__featured">
                                    <i class="fas fa-star me-1" aria-hidden="true"></i>Destacada
                                </span>
                            @endif

                            <div class="types property-card__tags">
                                @foreach($property->propertyTypes as $propertyType)
                                    <span class="badge">{{ $propertyType->name }}</span>
                                @endforeach
                                <span class="badge">{{ $property->transaction->name }}</span>
                            </div>
                        </a>

                        <div class="card-body property-card__body">
                            <h3 class="property-card__title">
                                <a href="{{ route('property', $property->slug) }}">{{ $property->title }}</a>
                            </h3>
                            <p class="property-card__address">
                                <i class="fas fa-map-marker-alt" aria-hidden="true"></i>{{ $property->address }}
                            </p>

                            {{-- Un terreno mostraba "0 rec. 0 banos": ahora cada dato
                                 aparece solo si tiene valor. --}}
                            <ul class="property-card__specs">
                                @if($property->bedrooms > 0)
                                    <li><i class="fas fa-bed" aria-hidden="true"></i>{{ $property->bedrooms }} {{ $property->bedrooms == 1 ? 'rec.' : 'recs.' }}</li>
                                @endif
                                @if($property->bathrooms > 0)
                                    <li><i class="fas fa-bath" aria-hidden="true"></i>{{ $property->bathrooms }} {{ $property->bathrooms == 1 ? 'baño' : 'baños' }}</li>
                                @endif
                                @if($property->square_feet > 0)
                                    <li><i class="fas fa-ruler-combined" aria-hidden="true"></i>{{ number_format($property->square_feet) }} m²</li>
                                @endif
                            </ul>

                            <div class="property-card__footer">
                                <span class="property-card__price">
                                    <span class="mc-price">${{ number_format($property->price) }}</span>
                                    @if($property->priceSuffix())
                                        <small>{{ $property->priceSuffix() }}</small>
                                    @endif
                                </span>
                                <a href="{{ route('property', $property->slug) }}" class="btn btn-primary btn-sm">
                                    Ver detalles <i class="fas fa-arrow-right ms-1" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach

            @if ( $list->isEmpty() )
                <div class="col-12">
                    <div class="alert alert-info text-center py-5">
                        <i class="fas fa-search fs-2 mb-3 d-block text-primary"></i>
                        <h5 class="mb-2">No encontramos propiedades</h5>
                        <p class="mb-3 text-muted-2">Intenta ajustar los filtros o explorar otras opciones.</p>
                        <a href="{{ route('properties') }}" class="btn btn-outline-primary btn-sm">Limpiar filtros</a>
                    </div>
                </div>
            @endif
        </div>

        <div class="d-flex justify-content-center mt-4 mb-5">
            {{ $list->links() }}
        </div>
    </div>
@endsection
