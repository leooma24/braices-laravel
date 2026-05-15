@extends('layouts.layout')

@section('title', 'BienesCorp - Reservaciones')
@section('description', 'Hospédate en propiedades únicas')
@section('og:title', 'BienesCorp - Reservaciones')
@section('og:description', 'Hospédate en propiedades únicas')
@section('og:image', asset('BienesCorpLogo.png'))
@section('og:url', url()->current())

@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
@endpush

@section('content')
    <div class="container py-4">
        <div class="reservations-hero">
            <span class="badge mb-3" style="background: rgba(255,255,255,0.18); color:#fff; padding: 0.4rem 1rem; border-radius: 999px; font-weight: 600; letter-spacing: 0.05em;">
                <i class="fas fa-bed me-1"></i> Estancias verificadas
            </span>
            <h1>Encuentra tu próxima escapada</h1>
            <p>Hospédate en propiedades únicas, listas para recibirte.</p>
        </div>

        <div class="row g-4">
            @forelse($properties as $property)
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="card h-100 position-relative">
                        <a href="{{ route('reservation.show', $property->slug) }}" class="d-block">
                            <img src="{{ $property->photo_main }}" class="card-img-top" alt="{{ $property->title }}" loading="lazy">
                        </a>

                        <button type="button" class="btn btn-light position-absolute top-0 end-0 m-3 rounded-circle p-2 shadow-sm" aria-label="Guardar como favorito" style="width: 40px; height: 40px;">
                            <i class="far fa-heart text-danger"></i>
                        </button>

                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0 me-2 text-truncate">{{ $property->title }}</h5>
                                @if($property->reviews_count ?? $property->reviews()->count())
                                    @php $avg = $property->averageRating(); @endphp
                                    @if($avg)
                                        <span class="small text-nowrap">
                                            <i class="fas fa-star text-warning"></i> <strong>{{ $avg }}</strong>
                                        </span>
                                    @endif
                                @endif
                            </div>

                            <p class="card-text small text-muted-2 mb-3">{{ Str::words($property->description, 16, '...') }}</p>

                            <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                                <div>
                                    <span class="mc-price">${{ number_format($property->price_per_night ?? 0, 0) }}</span>
                                    <small class="text-muted-2"> /noche</small>
                                </div>
                                <a href="{{ route('reservation.show', $property->slug) }}" class="btn btn-primary btn-sm">
                                    Reservar
                                </a>
                            </div>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center py-5">
                        <i class="fas fa-bed fs-2 mb-3 d-block text-primary"></i>
                        <h5 class="mb-2">Aún no hay propiedades para reservar</h5>
                        <p class="mb-0 text-muted-2">Vuelve pronto, estamos sumando opciones nuevas.</p>
                    </div>
                </div>
            @endforelse
        </div>

        @if(method_exists($properties, 'links'))
            <div class="d-flex justify-content-center mt-4">
                {{ $properties->links() }}
            </div>
        @endif
    </div>
@endsection
