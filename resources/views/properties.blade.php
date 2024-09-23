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
                <div class="row">
                    <div class="col-xs-12 col-md-3">
                        <select id="tipo" name="tipo" class="bg-primary-light form-select p-3 mb-2" aria-label="Large select example">
                            <option value="">Tipo de Propiedad</option>
                            @foreach ($types as $type)
                            <option value="{{ $type->id }}" {{ isset($data['tipo']) && $type->id == $data['tipo'] ? 'selected' : ''}}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xs-12 col-md-3 d-flex justify-content-center align-items-center">
                        <select id="tipo_transaccion" name="tipo_transaccion" class="bg-primary text-white form-select p-3 mb-2" aria-label="Large select example">
                            <option value="">Tipo de Transacción</option>
                            @foreach ($transactions as $transaction)
                            <option value="{{ $transaction->id }}" {{ isset($data['tipo_transaccion']) && $transaction->id == $data['tipo_transaccion'] ? 'selected="selected"' : ''}}>{{ $transaction->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xs-12 col-md-2 d-flex justify-content-center align-items-center">
                        <div class="form-floating mb-2 precio" style="width: 100%;">
                            <input id="precio_minimo" name="precio_minimo" value="{{ $data['precio_minimo'] ?? '' }}" type="number" class="form-control bg-primary-dark text-white" id="min_price" placeholder="Precio Mínimo">
                            <label for="min_price text-white">Precio Mínimo</label>
                        </div>
                    </div>

                    <div class="col-xs-12 col-md-2 d-flex justify-content-center align-items-center">
                        <div class="form-floating mb-2 precio" style="width: 100%;">
                            <input id="precio_maximo" name="precio_maximo" value="{{ $data['precio_maximo'] ?? '' }}" type="number" class="form-control bg-primary-light text-white" id="min_price" placeholder="Precio Máximo">
                            <label for="min_price text-white">Precio Máximo</label>
                        </div>
                    </div>

                    <div class="col-xs-12 col-md-2 d-flex justify-content-center align-items-start">
                        <button type="submit" class="btn btn-dark w-100 py-3">Buscar</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="row mt-5 mb-2 row-cols-lg-3">
            @foreach ($list as $property)
                <div class="col">
                    <div class="card mb-5 border-1 bg-white">
                        <a href="{{ route('property', $property->slug) }}">
                            <img src="{{ $property->photo_main }}" class="card-img-top" alt="...">
                        </a>
                        <div class="types position-absolute top-0 start-0 m-3">
                            @foreach($property->propertyTypes as $propertyType)
                                <div class="badge p-2 bg-danger">{{ $propertyType->name }}</div>
                            @endforeach
                            <div class="badge p-2 bg-warning">{{ $property->transaction->name }}</div>
                        </div>

                        <div class="card-body">
                            <h4 class="card-title mb-1 text-primary-emphasis"><strong>{{ $property->title }}</strong></h4>
                            <p class="card-text mb-1 text-secondary text-truncate">{{ $property->address }}</p>

                            <hr />
                            <div class="d-flex justify-content-between">
                                <span class="me-3">
                                    <img src="{{ asset('bed.svg') }}" alt="bed" width="20" height="20">
                                     {{ $property->bedrooms }}
                                    Recámaras</span>
                                <span class="me-3">
                                    <img src="{{ asset('baths.svg') }}" alt="bed" width="20" height="20">
                                     {{ $property->bathrooms }}
                                    Baños</span>
                                <span>
                                    <img src="{{ asset('sizes.svg') }}" alt="bed" width="20" height="20">
                                     {{ number_format($property->square_feet) }}
                                    m²</span>
                            </div>


                            <hr />
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="h4 m-0"><strong>${{ number_format($property->price) }}</strong></p>
                                <a href="{{ route('property', $property->slug) }}" class="btn btn-primary"><i
                                        class="fas fa-arrow-right"></i></a>
                            </div>


                        </div>
                    </div>
                </div>
            @endforeach

            @if ( $list->isEmpty() )
                <div class="col-12">
                    <div class="alert alert-warning" role="alert">
                        No se encontraron propiedades
                    </div>
                </div>
            @endif
        </div>

        <div class="col-xs-12">
            <div class="d-flex justify-content-center">
                {{ $list->links() }}
            </div>
        </div>
    </div>
@endsection
