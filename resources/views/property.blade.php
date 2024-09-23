@extends('layouts.layout')

@section('title', 'BienesCorp - ' . $property->title)
@section('description', $property->description)
@section('og:title', 'BienesCorp - ' . $property->title)
@section('og:description', $property->description)
@section('og:image', $property->photo_main )
@section('og:url', url()->current())

<script type="text/javascript" src="https://platform-api.sharethis.com/js/sharethis.js#property=66983fa2a88bfa0019b9391d&product=inline-share-buttons&source=platform" async="async"></script>

@section('content')

<div class="container">
    <nav aria-label="breadcrumb" class="my-4">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/">Inicio</a></li>
          <li class="breadcrumb-item"><a href="/propiedades">Propiedades</a></li>
          @if(isset($slugUser))
            <li class="breadcrumb-item"><a href="{{ route('my.properties', ['slug' => $slugUser]) }}">{{ $property->user->name }}</a></li>
        @endif
          <li class="breadcrumb-item active" aria-current="page">{{ $property->title }} </li>
        </ol>
      </nav>
    <x-carrusel :image="$property->photo_main" :images="$property->images" />

    <div class="row mt-5">
        <div class="col-12 col-md-8">
            <div class="card mb-3 border-1 bg-white">
                <div class="card-body">
                    <div>
                        <div class="badge text-bg-primary">{{ $property->type->name }}</div>
                        <div class="badge text-bg-primary">{{ $property->status->name }}</div>
                        <div class="badge text-bg-secondary"> <i class="fas fa-eye"></i> {{ $property->views }} Vistas</div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="card-title "><strong>{{ $property->title }}</strong></h2>
                        <p class="h4 m-0"><strong>${{ number_format($property->price) }}</strong></p>
                    </div>
                    <p class="card-text mb-2 text-secondary text-dots">{{ $property->address }}</p>

                    <h4 class="mt-3"><strong>Descripción</strong></h4>
                    <p class="card-text">{{ $property->description }}</p>

                </div>
            </div>

            <div class="card mb-3 border-1 bg-white">
                <div class="card-body">

                    <h4 class="mt-1"><strong>Datos Generales</strong></h4>
                    <div class="row">
                        <div class="col-xs-6 col-md-3 p-2">
                            <span> <img src="{{ asset('key.svg') }}" alt="bed" width="20" height="20" /> {{ $property->id }}</span>
                        </div>
                        <div class="col-xs-6 col-md-3 p-2">
                            <span> <img src="{{ asset('market.svg') }}" alt="bed" width="20" height="20" /> {{ $property->type->name }}</span>
                        </div>
                        <div class="col-xs-6 col-md-3 p-2">
                            <span> <img src="{{ asset('bed.svg') }}" alt="bed" width="20" height="20" /> {{ $property->bedrooms }} Recámaras</span>
                        </div>
                        <div class="col-xs-6 col-md-3 p-2">
                            <span> <img src="{{ asset('baths.svg') }}" alt="bed" width="20" height="20" /> {{ $property->bathrooms }} Baños</span>
                        </div>
                        <div class="col-xs-6 col-md-3 p-2">
                            <span> <img src="{{ asset('sizes.svg') }}" alt="bed" width="20" height="20" /> {{ number_format($property->square_feet) }} m²</span>
                        </div>
                        <div class="col-xs-6 col-md-3 p-2">
                            <span> <img src="{{ asset('calendar.svg') }}" alt="bed" width="20" height="20" /> {{ ($property->year_built) }}</span>
                        </div>
                        <div class="col-xs-6 col-md-3 p-2">
                            <span> <img src="{{ asset('vertical-rule.svg') }}" alt="bed" width="20" height="20" /> {{ ($property->front) }} de frente</span>
                        </div>
                        <div class="col-xs-6 col-md-3 p-2">
                            <span> <img src="{{ asset('vertical-rule.svg') }}" alt="bed" width="20" height="20" /> {{ ($property->depth) }} de fondo</span>
                        </div>
                    </div>
                </div>

            </div>

            <div class="card mb-3 border-1 bg-white">
                <div class="card-body">

                    <h4 class="mt-1"><strong>Dirección</strong></h4>
                    <div class="row">
                        <div class="col-xs-6 col-md-3 p-2">
                            <h6 class="mb-0">Calle</h6>
                        </div>
                        <div class="col-xs-6 col-md-9 p-2">
                            {{ $property->address }}
                        </div>

                        <div class="col-xs-6 col-md-3 p-2">
                            <h6 class="mb-0">País</h6>
                        </div>
                        <div class="col-xs-6 col-md-3 p-2">
                            {{ $property->countryName?->nombre }}
                        </div>

                        <div class="col-xs-6 col-md-3 p-2">
                            <h6 class="mb-0">Estado</h6>
                        </div>
                        <div class="col-xs-6 col-md-3 p-2">
                            {{ $property->stateName?->nombre }}
                        </div>

                        <div class="col-xs-6 col-md-3 p-2">
                            <h6 class="mb-0">Municipio</h6>
                        </div>
                        <div class="col-xs-6 col-md-3 p-2">
                            {{ $property->townshipName?->nombre }}
                        </div>

                        <div class="col-xs-6 col-md-3 p-2">
                            <h6 class="mb-0">Ciudad</h6>
                        </div>
                        <div class="col-xs-6 col-md-3 p-2">
                            {{ $property->city }}
                        </div>

                        <div class="col-xs-6 col-md-3 p-2">
                            <h6 class="mb-0">Colonia</h6>
                        </div>
                        <div class="col-xs-6 col-md-3 p-2">
                            {{ $property->suburbName?->nombre }}
                        </div>

                        <div class="col-xs-6 col-md-3 p-2">
                            <h6 class="mb-0">Código Postal</h6>
                        </div>
                        <div class="col-xs-6 col-md-3 p-2">
                            {{ $property->zip }}
                        </div>

                    </div>
                </div>

            </div>

            <div class="card mb-3 border-1 bg-white">
                <div class="card-body">

                    <h4 class="mt-1"><strong>Ubicación</strong></h4>

                    <div id="map" style="height: 500px; width: 100%;"></div>

                </div>
            </div>

            @if($property->youtube)
            <div class="card mb-3 border-1 bg-white">
                <div class="card-body">

                    <h4 class="mt-1"><strong>Video</strong></h4>

                    <iframe width="100%" height="315" src="https://www.youtube.com/embed/{{ $property->youtube }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
            </div>
            @endif

            <div class="card mb-3 border-1 bg-white">
                <div class="card-body">
                    <h4 class="mt-1"><strong>Compartir</strong></h4>
                    <div class="sharethis-inline-share-buttons"></div>
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-md-4">
            <div class="card mb-3 border-1 bg-white">
                <div class="card-body d-flex flex-column text-center">
                    <div class="agent">
                        <img src="{{ $property->user->photo }}" class="rounded-circle" alt="Logo" width="150" height="150">
                        <div class="ms-3">
                            <h5 class="card-title mb-0">{{ $property->user->name }}</h5>
                            <p class="text-secondary">Ejecutivo de ventas</p>
                            <p class="card-text"><i class="fas fa-envelope"></i> {{ $property->user->email }}</p>
                        </div>

                        <div class="ms-auto">
                            <a href="https://wa.me/{{ str_replace([' '],'',$property->user->phone_number) }}?text={{ urlencode('Hola, estoy interesado en' . $property->title) }}" target="_blank" class="btn"><i class="fab fa-whatsapp"></i> {{ $property->user->phone_number }}</a>
                        </div>

                        <div class="ms-auto">
                            <a href="{{ route('my.properties', ['slug' => $property->user->slug]) }}" class="btn btn-primary">Ver Propiedades</a>
                        </div>

                        <hr />

                        <div class="social-media d-flex justify-content-center">
                            <a target="_blank" href="{{ $property->user->facebook }}" class="btn btn-white rounded-circle shadow"><i class="fab fa-facebook"></i></a>
                            <a target="_blank" href="{{ $property->user->x }}" class="btn btn-white rounded-circle ms-2 shadow"><i class="fas fa-times"></i></a>
                            <a target="_blank" href="{{ $property->user->instagram }}" class="btn btn-white rounded-circle ms-2 shadow"><i class="fab fa-instagram"></i></a>
                            <a target="_blank" href="{{ $property->user->tiktok }}" class="btn btn-white rounded-circle ms-2 shadow"><i class="fab fa-tiktok"></i></a>
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
                            <input type="hidden" name="property_id" value="{{ $property->id }}">
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
                                <textarea name="message" class="form-control" style="height: 100px"  id="message" placeholder="Mensaje">Hola, estoy interesado en {{ $property->title }}</textarea>
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

<script async defer
src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&callback=initMap">
</script>

<script>
    function initMap() {
        // Configuración inicial del mapa
        var location = { lat: {{ $property->lat }}, lng: {{ $property->long }} };
        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 14,
            center: location
        });

        // Opcional: agregar un marcador
        var marker = new google.maps.Marker({
            position: location,
            map: map
        });
    }
</script>



@endsection
