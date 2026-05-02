@extends('layouts.layout')

@section('title', 'Mis reservaciones')
@section('description', 'Mis reservaciones')

@section('content')
    <div class="container py-4">
        <h1 class="h3 mb-4">Mis reservaciones</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @forelse($reservations as $reservation)
            <div class="card mb-3">
                <div class="row g-0">
                    <div class="col-md-3">
                        @if($reservation->property->photo_main)
                            <img src="{{ $reservation->property->photo_main }}" class="img-fluid rounded-start h-100" style="object-fit: cover;" alt="{{ $reservation->property->title }}">
                        @endif
                    </div>
                    <div class="col-md-9">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">
                                    <a href="{{ route('property', $reservation->property->slug) }}" class="text-decoration-none">
                                        {{ $reservation->property->title }}
                                    </a>
                                </h5>
                                <span class="badge {{ $reservation->status->value === 'confirmada' ? 'bg-success' : ($reservation->status->value === 'pendiente' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                    {{ $reservation->status->label() }}
                                </span>
                            </div>
                            <p class="text-muted small mb-2">
                                {{ $reservation->check_in_date->format('d/m/Y') }} → {{ $reservation->check_out_date->format('d/m/Y') }}
                                · {{ $reservation->nights }} {{ $reservation->nights === 1 ? 'noche' : 'noches' }}
                                · {{ $reservation->guests }} {{ $reservation->guests === 1 ? 'huésped' : 'huéspedes' }}
                            </p>
                            <p class="mb-2"><strong>Total:</strong> ${{ number_format((float) $reservation->total_price, 2) }} MXN</p>

                            <div class="d-flex gap-2">
                                @if($reservation->status->value === 'pendiente' && !$reservation->isExpired())
                                    <a href="{{ route('reservation.checkout', $reservation) }}" class="btn btn-sm btn-primary">
                                        Completar pago
                                    </a>
                                @endif

                                @if($reservation->isCancellable())
                                    <form method="POST" action="{{ route('reservation.cancel', $reservation) }}"
                                          onsubmit="return confirm('¿Cancelar esta reservación?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Cancelar</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info">
                Aún no tienes reservaciones. <a href="{{ route('reservations') }}">Explora propiedades disponibles</a>.
            </div>
        @endforelse

        {{ $reservations->links() }}
    </div>
@endsection
