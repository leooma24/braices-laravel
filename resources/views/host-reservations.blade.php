@extends('layouts.layout')

@section('title', 'Reservas recibidas')
@section('description', 'Reservas recibidas')

@section('content')
    <div class="container py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="dashboard-header">
            <div>
                <h1>Reservas Recibidas</h1>
                <p>Huéspedes que han reservado tus propiedades.</p>
            </div>
        </div>

        @forelse($reservations as $reservation)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5 class="card-title mb-1">{{ $reservation->property->title }}</h5>
                            <p class="text-muted small mb-0">
                                Huésped: <strong>{{ $reservation->user->name }}</strong>
                                @if($reservation->user->phone_number)
                                    · {{ $reservation->user->phone_number }}
                                @endif
                            </p>
                        </div>
                        <span class="badge {{ $reservation->status->value === 'confirmada' ? 'bg-success' : ($reservation->status->value === 'pendiente' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                            {{ $reservation->status->label() }}
                        </span>
                    </div>
                    <p class="text-muted small mb-2">
                        {{ $reservation->check_in_date->format('d/m/Y') }} → {{ $reservation->check_out_date->format('d/m/Y') }}
                        · {{ $reservation->nights }} {{ $reservation->nights === 1 ? 'noche' : 'noches' }}
                        · {{ $reservation->guests }} {{ $reservation->guests === 1 ? 'huésped' : 'huéspedes' }}
                    </p>
                    <p class="mb-0"><strong>Monto:</strong> ${{ number_format((float) $reservation->total_price, 2) }} MXN</p>
                </div>
            </div>
        @empty
            <div class="dashboard-card">
                <div class="empty-state">
                    <div class="empty-state__icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h5>Aún no recibes reservaciones</h5>
                    <p>Cuando un huésped reserve una de tus propiedades, aparecerá aquí.</p>
                </div>
            </div>
        @endforelse

        {{ $reservations->links() }}
    </div>
@endsection
