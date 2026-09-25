@extends('layouts.layout')

@section('title', 'BienesCorp - Prospectos')
@section('description', 'Personas que te han contactado por tus propiedades')
@section('og:title', 'BienesCorp - Prospectos')
@section('og:description', 'Personas que te han contactado por tus propiedades')
@section('og:image', asset('BienesCorpLogo.png'))
@section('og:url', url()->current())

@section('content')

<div class="container">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="dashboard-header">
        <div>
            <h1>Prospectos</h1>
            <p>
                @if ($pendientes > 0)
                    Tienes <strong>{{ $pendientes }}</strong> {{ $pendientes === 1 ? 'mensaje sin contestar' : 'mensajes sin contestar' }}.
                @else
                    Todos los mensajes están contestados.
                @endif
            </p>
        </div>
    </div>

    <div class="lead-filters mb-3">
        <a href="{{ route('leads') }}"
           class="btn btn-sm {{ $estadoActual ? 'btn-outline-secondary' : 'btn-accent' }}">Todos</a>
        @foreach ($estados as $clave => $etiqueta)
            <a href="{{ route('leads', ['estado' => $clave]) }}"
               class="btn btn-sm {{ $estadoActual === $clave ? 'btn-accent' : 'btn-outline-secondary' }}">{{ $etiqueta }}</a>
        @endforeach
    </div>

    @if ($leads->isEmpty())
        <div class="dashboard-card">
            <div class="empty-state">
                <div class="empty-state__icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <h3>Todavía no hay prospectos</h3>
                <p>Aquí van a aparecer las personas que te escriban desde la ficha de tus propiedades.</p>
            </div>
        </div>
    @else
        @foreach ($leads as $lead)
            <div class="dashboard-card lead-card {{ $lead->isUrgent() ? 'lead-card--urgente' : '' }}">
                <div class="dashboard-card__body">
                    <div class="lead-card__top">
                        <div>
                            <h3 class="lead-card__name">{{ $lead->name }}</h3>
                            <p class="lead-card__meta">
                                {{ $lead->created_at?->diffForHumans() }}
                                @if ($lead->property)
                                    · <a href="{{ route('property', ['slug' => $lead->property->slug]) }}">{{ $lead->property->title }}</a>
                                @else
                                    · Contacto general del sitio
                                @endif
                            </p>
                        </div>
                        <div class="lead-card__badges">
                            @if ($lead->isUrgent())
                                <span class="badge bg-danger">Sin contestar</span>
                            @endif
                            <span class="badge lead-badge lead-badge--{{ $lead->status }}">{{ $lead->statusLabel() }}</span>
                        </div>
                    </div>

                    <p class="lead-card__message">{{ $lead->message }}</p>

                    <div class="lead-card__contact">
                        @if ($lead->phone)
                            <span><i class="fas fa-phone me-1"></i>{{ $lead->phone }}</span>
                        @endif
                        @if ($lead->email)
                            <span><i class="fas fa-envelope me-1"></i>{{ $lead->email }}</span>
                        @endif
                    </div>

                    @if ($lead->notes)
                        <p class="lead-card__notes"><strong>Nota:</strong> {{ $lead->notes }}</p>
                    @endif

                    <div class="lead-card__actions">
                        @if ($lead->whatsappUrl())
                            <a href="{{ $lead->whatsappUrl() }}" target="_blank" rel="noopener"
                               class="btn btn-success btn-sm">
                                <i class="fab fa-whatsapp me-1"></i>Contestar por WhatsApp
                            </a>
                        @endif
                        @if ($lead->email)
                            <a href="mailto:{{ $lead->email }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-envelope me-1"></i>Correo
                            </a>
                        @endif

                        <form action="{{ route('leads.status', ['id' => $lead->id]) }}" method="POST" class="lead-card__form">
                            @csrf
                            <select name="status" class="form-select form-select-sm" aria-label="Estado del prospecto">
                                @foreach ($estados as $clave => $etiqueta)
                                    <option value="{{ $clave }}" {{ $lead->status === $clave ? 'selected' : '' }}>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="notes" class="form-control form-control-sm"
                                   placeholder="Nota (opcional)" value="{{ $lead->notes }}" maxlength="2000">
                            <button type="submit" class="btn btn-accent btn-sm">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="mt-4">
            {{ $leads->links() }}
        </div>
    @endif
</div>

@endsection
