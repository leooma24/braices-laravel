@extends('layouts.layout')

@section('title', 'Mis reservaciones')
@section('description', 'Mis reservaciones')

@section('content')
    <div class="container py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="dashboard-header">
            <div>
                <h1>Mis Reservaciones</h1>
                <p>Revisa tus estancias confirmadas, pendientes y completadas.</p>
            </div>
            <a href="{{ route('reservations') }}" class="btn btn-accent">
                <i class="fas fa-search me-2"></i>Explorar estancias
            </a>
        </div>

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

                            <div class="d-flex gap-2 align-items-center flex-wrap">
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

                                @if($reservation->status->value === 'completada' && !$reservation->review)
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="collapse" data-bs-target="#review-form-{{ $reservation->id }}">
                                        Dejar reseña
                                    </button>
                                @elseif($reservation->status->value === 'completada' && $reservation->review)
                                    <span class="badge bg-info">Reseña enviada</span>
                                @endif
                            </div>

                            @if($reservation->status->value === 'completada' && !$reservation->review)
                                <div class="collapse mt-3" id="review-form-{{ $reservation->id }}">
                                    <form method="POST" action="{{ route('reservation.review.store', $reservation) }}">
                                        @csrf
                                        <div class="mb-2">
                                            <label class="form-label small mb-1">Calificación</label>
                                            <select name="rating" class="form-select form-select-sm" required>
                                                @for($i = 5; $i >= 1; $i--)
                                                    <option value="{{ $i }}">{{ str_repeat('★', $i) }}{{ str_repeat('☆', 5 - $i) }} — {{ $i }}/5</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small mb-1">Comentario</label>
                                            <textarea name="comment" rows="3" class="form-control form-control-sm" minlength="10" maxlength="1000" required placeholder="Cuéntale a otros huéspedes cómo fue tu estadía..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-primary">Enviar reseña</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="dashboard-card">
                <div class="empty-state">
                    <div class="empty-state__icon">
                        <i class="fas fa-suitcase-rolling"></i>
                    </div>
                    <h5>Aún no tienes reservaciones</h5>
                    <p class="mb-4">Explora propiedades disponibles y reserva tu próxima escapada.</p>
                    <a href="{{ route('reservations') }}" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Buscar estancias
                    </a>
                </div>
            </div>
        @endforelse

        {{ $reservations->links() }}
    </div>
@endsection
