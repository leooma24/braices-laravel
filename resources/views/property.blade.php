@extends('layouts.layout')

@section('title', 'BienesCorp - ' . $property->title)
@section('description', Str::limit(strip_tags($property->description), 155))
@section('og:title', 'BienesCorp - ' . $property->title)
@section('og:description', Str::limit(strip_tags($property->description), 155))
@section('og:image', $property->photo_main)
@section('og:url', url()->current())

@push('head')
    @php
        $isReservable = (bool) $property->is_reservable;
        $type = $isReservable ? 'Accommodation' : 'RealEstateListing';
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => $type,
            'name' => $property->title,
            'description' => strip_tags($property->description),
            'url' => url()->current(),
            'image' => $property->photo_main,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $property->address,
                'addressLocality' => $property->city,
                'addressRegion' => $property->stateName?->nombre,
                'postalCode' => $property->zip,
                'addressCountry' => $property->countryName?->nombre ?? 'MX',
            ],
        ];
        if ($property->lat && $property->long) {
            $jsonLd['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $property->lat,
                'longitude' => (float) $property->long,
            ];
        }
        if (!$isReservable) {
            $jsonLd['offers'] = [
                '@type' => 'Offer',
                'price' => (float) $property->price,
                'priceCurrency' => 'MXN',
                'availability' => 'https://schema.org/InStock',
                'url' => url()->current(),
            ];
        } else {
            $jsonLd['priceRange'] = '$' . number_format((float)($property->price_per_night ?? 0));
            $jsonLd['numberOfRooms'] = $property->bedrooms;
            $jsonLd['occupancy'] = [
                '@type' => 'QuantitativeValue',
                'maxValue' => $property->max_guests,
            ];
        }
        if (!$property->isLand()) {
            $jsonLd['numberOfBedrooms'] = $property->bedrooms;
            $jsonLd['numberOfBathroomsTotal'] = $property->bathrooms;
        }
        $jsonLd['floorSize'] = [
            '@type' => 'QuantitativeValue',
            'value' => (float) $property->square_feet,
            'unitCode' => 'MTK',
        ];
        $avg = $property->averageRating();
        $reviewsCount = $property->reviews->count();
        if ($avg && $reviewsCount > 0) {
            $jsonLd['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $avg,
                'reviewCount' => $reviewsCount,
                'bestRating' => 5,
                'worstRating' => 1,
            ];
        }
    @endphp
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

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

    <div class="row mt-4 g-4">
        <div class="col-12 col-md-8">
            <div class="detail-card">
                <div class="detail-card__body">
                    <div class="detail-badges">
                        @foreach ($property->propertyTypes as $propertyType)
                            <span class="badge">{{ $propertyType->name }}</span>
                        @endforeach
                        <span class="badge">{{ $property->status->name }}</span>
                        <span class="badge badge-views"><i class="fas fa-eye me-1"></i>{{ $property->views }} vistas</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-2">
                        <h2 class="card-title mb-0" style="font-family: var(--font-display); font-weight: 800; letter-spacing: -0.01em;">{{ $property->title }}</h2>
                        <span class="detail-price">${{ number_format($property->price) }}</span>
                    </div>
                    <p class="text-muted-2 mb-4">
                        <i class="fas fa-map-marker-alt text-primary me-1"></i>{{ $property->address }}
                    </p>

                    <h4 class="detail-section-title"><i class="fas fa-align-left"></i>Descripción</h4>
                    <p class="card-text" style="line-height: 1.75;">{{ $property->description }}</p>
                </div>
            </div>

            <div class="detail-card">
                <div class="detail-card__body">
                    <h4 class="detail-section-title"><i class="fas fa-clipboard-list"></i>Datos generales</h4>
                    <div class="spec-grid">
                        <div class="spec-item">
                            <img src="{{ asset('key.svg') }}" alt="ID">
                            <div>
                                <div class="spec-item__value">#{{ $property->id }}</div>
                                <div class="spec-item__label">ID</div>
                            </div>
                        </div>
                        <div class="spec-item">
                            <img src="{{ asset('market.svg') }}" alt="Tipo">
                            <div>
                                <div class="spec-item__value">
                                    @foreach ($property->propertyTypes as $propertyType){{ $propertyType->name }}@endforeach
                                </div>
                                <div class="spec-item__label">Tipo</div>
                            </div>
                        </div>
                        @if(!$property->isLand())
                            <div class="spec-item">
                                <img src="{{ asset('bed.svg') }}" alt="Recámaras">
                                <div>
                                    <div class="spec-item__value">{{ $property->bedrooms }}</div>
                                    <div class="spec-item__label">Recámaras</div>
                                </div>
                            </div>
                            <div class="spec-item">
                                <img src="{{ asset('baths.svg') }}" alt="Baños">
                                <div>
                                    <div class="spec-item__value">{{ $property->bathrooms }}</div>
                                    <div class="spec-item__label">Baños</div>
                                </div>
                            </div>
                        @endif
                        <div class="spec-item">
                            <img src="{{ asset('sizes.svg') }}" alt="m²">
                            <div>
                                <div class="spec-item__value">{{ number_format($property->square_feet) }} m²</div>
                                <div class="spec-item__label">Superficie</div>
                            </div>
                        </div>
                        @if(!$property->isLand())
                            <div class="spec-item">
                                <img src="{{ asset('calendar.svg') }}" alt="Año">
                                <div>
                                    <div class="spec-item__value">{{ $property->year_built }}</div>
                                    <div class="spec-item__label">Año</div>
                                </div>
                            </div>
                        @endif
                        @if($property->front)
                            <div class="spec-item">
                                <img src="{{ asset('vertical-rule.svg') }}" alt="Frente">
                                <div>
                                    <div class="spec-item__value">{{ $property->front }} m</div>
                                    <div class="spec-item__label">Frente</div>
                                </div>
                            </div>
                        @endif
                        @if($property->depth)
                            <div class="spec-item">
                                <img src="{{ asset('vertical-rule.svg') }}" alt="Fondo">
                                <div>
                                    <div class="spec-item__value">{{ $property->depth }} m</div>
                                    <div class="spec-item__label">Fondo</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <div class="detail-card__body">
                    <h4 class="detail-section-title"><i class="fas fa-map-marker-alt"></i>Dirección</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="address-row">
                                <span class="address-row__label">Calle</span>
                                <span class="address-row__value">{{ $property->address }}</span>
                            </div>
                            <div class="address-row">
                                <span class="address-row__label">País</span>
                                <span class="address-row__value">{{ $property->countryName?->nombre }}</span>
                            </div>
                            <div class="address-row">
                                <span class="address-row__label">Estado</span>
                                <span class="address-row__value">{{ $property->stateName?->nombre }}</span>
                            </div>
                            <div class="address-row">
                                <span class="address-row__label">Municipio</span>
                                <span class="address-row__value">{{ $property->townshipName?->nombre }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="address-row">
                                <span class="address-row__label">Ciudad</span>
                                <span class="address-row__value">{{ $property->city }}</span>
                            </div>
                            <div class="address-row">
                                <span class="address-row__label">Colonia</span>
                                <span class="address-row__value">{{ $property->suburbName?->nombre }}</span>
                            </div>
                            <div class="address-row">
                                <span class="address-row__label">Código Postal</span>
                                <span class="address-row__value">{{ $property->zip }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <div class="detail-card__body">
                    <h4 class="detail-section-title"><i class="fas fa-map"></i>Ubicación</h4>
                    <div id="map" style="height: 420px; width: 100%; border-radius: var(--radius-md); overflow: hidden;"></div>
                </div>
            </div>

            @if($property->youtube)
                <div class="detail-card">
                    <div class="detail-card__body">
                        <h4 class="detail-section-title"><i class="fab fa-youtube"></i>Video</h4>
                        <div class="ratio ratio-16x9" style="border-radius: var(--radius-md); overflow: hidden;">
                            <iframe src="https://www.youtube.com/embed/{{ $property->youtube }}" title="YouTube video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>
            @endif

            @php $avg = $property->averageRating(); @endphp
            @if($property->reviews->isNotEmpty())
                <div class="detail-card">
                    <div class="detail-card__body">
                        <div class="d-flex justify-content-between align-items-baseline mb-3">
                            <h4 class="detail-section-title mb-0"><i class="fas fa-comment-dots"></i>Reseñas</h4>
                            @if($avg)
                                <span class="fs-5"><i class="fas fa-star text-warning"></i> <strong>{{ $avg }}</strong> <span class="text-muted-2 small">({{ $property->reviews->count() }})</span></span>
                            @endif
                        </div>
                        @foreach($property->reviews->sortByDesc('created_at')->take(10) as $review)
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <strong>{{ $review->user->name }}</strong>
                                    <span class="text-warning">{{ str_repeat('★', $review->rating) }}<span class="text-muted">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                                </div>
                                <small class="text-muted">{{ $review->created_at->format('d/m/Y') }}</small>
                                <p class="mb-0 mt-1">{{ $review->comment }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="detail-card">
                <div class="detail-card__body">
                    <h4 class="detail-section-title"><i class="fas fa-share-alt"></i>Compartir</h4>
                    <div class="sharethis-inline-share-buttons"></div>
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-md-4">
            <div class="agent-card position-sticky" style="top: 90px;">
                <div class="card-body d-flex flex-column text-center">
                    <div class="agent">
                        <img src="{{ $property->user->photo }}" class="agent-card__avatar" alt="{{ $property->user->name }}">
                        <h5 class="agent-card__name">{{ $property->user->name }}</h5>
                        <p class="agent-card__role">Ejecutivo de ventas</p>

                        <div class="d-grid gap-2 mb-3">
                            @if($property->user->phone_number)
                                <a href="https://wa.me/{{ preg_replace('/\s+/', '', $property->user->phone_number) }}?text={{ urlencode('Hola, me interesa la propiedad: ' . $property->title) }}"
                                   target="_blank" rel="noopener"
                                   class="btn btn-success btn-lg" style="background-color: #25D366; border-color: #25D366;">
                                    <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                </a>
                            @endif
                            <a href="mailto:{{ $property->user->email }}" class="btn btn-outline-primary">
                                <i class="fas fa-envelope me-2"></i>{{ $property->user->email }}
                            </a>
                            <a href="{{ route('my.properties', ['slug' => $property->user->slug]) }}" class="btn btn-link">
                                Ver más propiedades del agente
                            </a>
                        </div>

                        <div class="social-media d-flex justify-content-center gap-2 mb-3">
                            @if($property->user->facebook)
                                <a target="_blank" rel="noopener" href="{{ $property->user->facebook }}" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px;" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                            @endif
                            @if($property->user->x)
                                <a target="_blank" rel="noopener" href="{{ $property->user->x }}" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px;" aria-label="Twitter/X"><i class="fab fa-x-twitter"></i></a>
                            @endif
                            @if($property->user->instagram)
                                <a target="_blank" rel="noopener" href="{{ $property->user->instagram }}" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px;" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            @endif
                            @if($property->user->tiktok)
                                <a target="_blank" rel="noopener" href="{{ $property->user->tiktok }}" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px;" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                            @endif
                        </div>
                    </div>

                    <hr />

                    <div class="send-message text-start">
                        @if (session('success'))
                            <div class="alert alert-success" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif
                        <h5 class="mt-3 mb-3"><strong>Envíale un mensaje</strong></h5>
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
                    <h4 class="mt-5">Compartela en tus estados de WhatsApp</h4>
                    <div class="d-flex justify-content-center">
                        <div class="imageEstado">
                            <img src="/propiedad/imagen/{{ $property->id  }}" alt="Estado" />
                        </div>
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
