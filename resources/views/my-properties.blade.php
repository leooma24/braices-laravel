@extends('layouts.layout')

@section('title', 'BienesCorp - Mis Propiedades')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Mis Propiedades')
@section('og:description', 'Administración de Bienes Inmuebles')
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
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="dashboard-header">
        <div>
            <h1>Mis Propiedades</h1>
            <p>Administra tu inventario, destácalas y gestiona reservas.</p>
        </div>
        <a href="{{ route('properties.new') }}" class="btn btn-accent btn-lg">
            <i class="fas fa-plus me-2"></i>Nueva propiedad
        </a>
    </div>

    @if ($list->isEmpty())
        <div class="dashboard-card">
            <div class="empty-state">
                <div class="empty-state__icon">
                    <i class="fas fa-home"></i>
                </div>
                <h5>Aún no tienes propiedades publicadas</h5>
                <p class="mb-4">Publica tu primera propiedad y empieza a recibir contactos hoy.</p>
                <a href="{{ route('properties.new') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Crear propiedad
                </a>
            </div>
        </div>
    @else
        <div class="row g-4 mb-4">
            @foreach ($list as $property)
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="card h-100 position-relative {{ $property->isFeaturedNow() ? 'card-featured' : '' }}">
                        <a href="{{ route('property', $property->slug) }}" class="d-block">
                            <img src="{{ $property->photo_main }}" class="card-img-top" alt="{{ $property->title }}" loading="lazy" style="height: 200px; object-fit: cover;">
                        </a>

                        @if($property->isFeaturedNow())
                            <span class="badge featured-badge position-absolute top-0 end-0 m-3">
                                <i class="fas fa-star me-1"></i>Destacada
                            </span>
                        @endif

                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h5 class="card-title mb-0 text-truncate me-2">{{ $property->title }}</h5>
                                <small class="text-muted-2 text-nowrap">#{{ $property->id }}</small>
                            </div>
                            <p class="small text-muted-2 mb-3 text-truncate">
                                {{ Str::limit($property->description, 90) }}
                            </p>

                            <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                                <span class="mc-price">${{ number_format($property->price) }}</span>

                                <div class="action-buttons">
                                    <a href="{{ route('property', $property->slug) }}"
                                       class="btn btn-outline-primary btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form method="POST" action="{{ route('properties.feature', $property->slug) }}" class="d-inline">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-sm {{ $property->isFeaturedNow() ? 'btn-warning' : 'btn-outline-warning' }}"
                                                title="{{ $property->isFeaturedNow() ? 'Quitar destacado' : 'Destacar 30 días' }}">
                                            <i class="{{ $property->isFeaturedNow() ? 'fas' : 'far' }} fa-star"></i>
                                        </button>
                                    </form>
                                    @if($property->is_reservable)
                                        <a href="{{ route('host.calendar', $property->slug) }}"
                                           class="btn btn-info btn-sm text-white" title="Calendario">
                                            <i class="fas fa-calendar-alt"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('properties.edit', $property->slug) }}"
                                       class="btn btn-secondary btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-danger btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                                            data-id="{{ $property->id }}" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mb-5">
            {{ $list->links() }}
        </div>
    @endif
</div>

<x-delete-modal>
    ¿Estás seguro de que deseas eliminar esta propiedad?
</x-delete-modal>

<script>
    var deleteModal = document.getElementById('deleteModal')
    deleteModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget
        var id = button.getAttribute('data-id')
        var form = document.getElementById('deleteForm')
        form.action = `/propiedad/${id}/eliminar`
    })
</script>

@endsection
