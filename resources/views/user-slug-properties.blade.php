@extends('layouts.layout')

@section('title', 'BienesCorp - ' . $user->name)
@section('description', 'Propiedades de ' . $user->name)
@section('og:title', 'BienesCorp - ' . $user->name)
@section('og:description', 'Propiedades de ' . $user->name)
@section('og:image', $user->photo)
@section('og:url', url()->current())

@section('content')

<div class="container">
    <nav aria-label="breadcrumb" class="my-4">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/">Inicio</a></li>
          <li class="breadcrumb-item"><a href="/propiedades">Usuario</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ $user->name }} </li>
        </ol>
      </nav>


    <div class="row mt-5">
        <div class="col-12">
            <div class="filters mt-5">
                <form action="{{ route('my.properties', ['slug' => $user->slug]) }}" method="GET">
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
        </div>
        <div class="col-12 col-md-8">
            <div class="row">
                @foreach ($list as $property)
                    <div class="col-6 col-xs-12">
                        <div class="card mb-5 border-1 bg-white">
                            <a href="{{ route('userProperty', ['slugUser' => $user->slug, 'slug' => $property->slug]) }}">
                                <img src="{{ $property->photo_main }}" class="card-img-top" alt="...">
                            </a>
                            <div class="types position-absolute top-0 start-0 m-3">
                                <div class="badge p-2 bg-danger">{{ $property->type->name }}</div>
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

        </div>


        <div class="col-xs-12 col-md-4">
            <div class="card mb-3 border-1 bg-white">
                <div class="card-body d-flex flex-column text-center">
                    <div class="agent">
                        <img src="{{ $user->photo }}" class="rounded-circle" alt="Logo" width="150" height="150">
                        <div class="ms-3">
                            <h5 class="card-title mb-0">{{ $user->name }}</h5>
                            <p class="text-secondary">Ejecutivo de ventas</p>
                            <p class="card-text"><i class="fas fa-envelope"></i> {{ $user->email }}</p>
                        </div>

                        <div class="ms-auto">
                            <a href="https://wa.me/{{ str_replace([' '],'',$user->phone_number) }}?text={{ urlencode('Hola, estoy interesado en algunas de sus propiedades, me gustaria contactarlo para mas detalles.') }}" target="_blank" class="btn"><i class="fab fa-whatsapp"></i> {{ $user->phone_number }}</a>
                        </div>


                        <hr />

                        <div class="social-media d-flex justify-content-center">
                            <a target="_blank" href="{{ $user->facebook }}" class="btn btn-white rounded-circle shadow"><i class="fab fa-facebook"></i></a>
                            <a target="_blank" href="{{ $user->x }}" class="btn btn-white rounded-circle ms-2 shadow"><i class="fas fa-times"></i></a>
                            <a target="_blank" href="{{ $user->instagram }}" class="btn btn-white rounded-circle ms-2 shadow"><i class="fab fa-instagram"></i></a>
                            <a target="_blank" href="{{ $user->tiktok }}" class="btn btn-white rounded-circle ms-2 shadow"><i class="fab fa-tiktok"></i></a>
                        </div>

                        <hr />
                    </div>

                    <div class="send-message">
                        @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>

                        @endif
                        <h4 class="mt-3"><strong>Enviar Mensaje</strong></h4>
                        <form action="{{ route('contact.me') }}" method="POST">
                            @csrf
                            <div class="form-floating mb-3">
                                <input type="text" name="name" class="form-control" id="name" placeholder="Nombre">
                                <label for="name">Nombre</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="phone" name="phone_number" class="form-control" id="phone_number" placeholder="Teléfono">
                                <label for="phohe_number">Teléfono</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input name="email" type="email" class="form-control" id="email" placeholder="Correo">
                                <label for="email">Correo</label>
                            </div>

                            <div class="form-floating mb-3">
                                <textarea name="message" class="form-control" style="height: 100px"  id="message" placeholder="Mensaje">Hola, estoy interesado en saber mas de sus propiedades</textarea>
                                <label for="message">Mensaje</label>
                            </div>

                            <div class="text-center mt-2">
                                {!! NoCaptcha::display() !!}
                            </div>

                            <button type="submit" class="btn btn-primary">Enviar</button>

                            @if ($errors->has('g-recaptcha-response'))
                            <div class="text-center mt-2">
                                <span class="text-danger">{{ $errors->first('g-recaptcha-response') }}</span>
                            </div>
                            @endif
                        </form>
                    </div>

                    <hr />
                    <div class="qrCode">
                        <h4 class="mt-3"><strong>QR Code</strong></h4>
                        {{ $qrCode }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{!! NoCaptcha::renderJs() !!}


@endsection
