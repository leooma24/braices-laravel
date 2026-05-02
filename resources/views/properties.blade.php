@extends('layouts.layout')

@section('title', 'BienesCorp - Propiedades')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Propiedades')
@section('og:description', 'Administración de Bienes Inmuebles')
@section('og:image', asset('public/BienesCorpLogo.png'))
@section('og:url', url()->current())

@section('content')

    <x-top-background :image="asset('JPG-12.jpg')">
        Propiedades
    </x-top-background>

    <div class="container">
        <div class="filters">
            <form action="{{ route('properties') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label small text-muted-2 mb-1">Tipo de propiedad</label>
                        <select id="tipo" name="tipo" class="form-select">
                            <option value="">Todos los tipos</option>
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}" {{ isset($data['tipo']) && $type->id == $data['tipo'] ? 'selected' : ''}}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label small text-muted-2 mb-1">Transacción</label>
                        <select id="tipo_transaccion" name="tipo_transaccion" class="form-select">
                            <option value="">Cualquiera</option>
                            @foreach ($transactions as $transaction)
                                <option value="{{ $transaction->id }}" {{ isset($data['tipo_transaccion']) && $transaction->id == $data['tipo_transaccion'] ? 'selected' : ''}}>{{ $transaction->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-lg-2">
                        <label class="form-label small text-muted-2 mb-1">Precio mínimo</label>
                        <input id="precio_minimo" name="precio_minimo" value="{{ $data['precio_minimo'] ?? '' }}" type="number" class="form-control" placeholder="$0">
                    </div>

                    <div class="col-6 col-lg-2">
                        <label class="form-label small text-muted-2 mb-1">Precio máximo</label>
                        <input id="precio_maximo" name="precio_maximo" value="{{ $data['precio_maximo'] ?? '' }}" type="number" class="form-control" placeholder="Sin límite">
                    </div>

                    <div class="col-12 col-lg-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="row g-4 mt-2 mb-2">
            @foreach ($list as $property)
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="card h-100 position-relative">
                        <a href="{{ route('property', $property->slug) }}" class="d-block">
                            <img src="{{ $property->photo_main }}" class="card-img-top" alt="{{ $property->title }}" loading="lazy">
                        </a>

                        <div class="types position-absolute top-0 start-0 m-3 d-flex flex-wrap gap-2">
                            @foreach($property->propertyTypes as $propertyType)
                                <span class="badge">{{ $propertyType->name }}</span>
                            @endforeach
                            <span class="badge">{{ $property->transaction->name }}</span>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-1 text-truncate">{{ $property->title }}</h5>
                            <p class="card-text mb-3 text-muted-2 small text-truncate">
                                <i class="fas fa-map-marker-alt me-1 text-primary"></i>{{ $property->address }}
                            </p>

                            <div class="d-flex flex-wrap gap-3 small text-muted-2 mb-3">
                                <span><i class="fas fa-bed me-1 text-primary"></i>{{ $property->bedrooms }} rec.</span>
                                <span><i class="fas fa-bath me-1 text-primary"></i>{{ $property->bathrooms }} baños</span>
                                <span><i class="fas fa-ruler-combined me-1 text-primary"></i>{{ number_format($property->square_feet) }} m²</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                                <span class="mc-price">${{ number_format($property->price) }}</span>
                                <a href="{{ route('property', $property->slug) }}" class="btn btn-primary btn-sm">
                                    Ver detalles <i class="fas fa-arrow-right ms-1"></i>
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
