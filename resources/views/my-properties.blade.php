@extends('layouts.layout')

@section('title', 'BienesCorp - Mis Propiedades')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Mis Propiedades')
@section('og:description', 'Administración de Bienes Inmuebles')
@section('og:image', asset('BienesCorpLogo.png'))
@section('og:url', url()->current())

@section('content')

<div class="container">
    <div class="row">

    @if (session('success'))
    <div class="col-xs-12">
        <div class="alert alert-success my-3">
            {{ session('success') }}
        </div>
    </div>
    @endif
    @if (session('error'))
        <div class="col-xs-12">
            <div class="alert alert-danger my-3">
                {{ session('error') }}
            </div>
        </div>
    @endif
    <div class="col-xs-12">
        <div class="card my-3">
            <div class="card-header d-flex justify-content-between p-3">
                <h3 class="card-title">Mis Propiedades</h3>
                <div class="card-tools">
                    <a href="{{ route('properties.new') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Propiedad
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table mt-3 mc-table">
                      <thead class="table-dark">
                        <tr>
                          <th scope="col">ID</th>
                          <th scope="col">Título</th>
                          <th scope="col" width="40%">Descripción</th>
                          <th scope="col">Precio</th>
                          <th scope="col" width="130px">Acciones</th>
                        </tr>
                      </thead>
                        <tbody>
                        @foreach ($list as $property)
                            <tr>
                                <th scope="row">{{ $property->id }}</th>
                                <td>{{ $property->title }}</td>
                                <td>{{ $property->description }}</td>
                                <td>${{ number_format($property->price) }}</td>
                                <td>
                                    <div class="d-flex justify-content-between gap-1">
                                        <a href="{{ route('property', $property->slug) }}"
                                        class="btn btn-primary rounded-circle btn-sm" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($property->isFeaturedNow())
                                            <form method="POST" action="{{ route('properties.feature', $property->slug) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-warning rounded-circle btn-sm" title="Quitar destacado">
                                                    <i class="fas fa-star"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('properties.feature', $property->slug) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-warning rounded-circle btn-sm" title="Destacar 30 días">
                                                    <i class="far fa-star"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($property->is_reservable)
                                            <a href="{{ route('host.calendar', $property->slug) }}"
                                            class="btn btn-info rounded-circle btn-sm text-white" title="Calendario">
                                                <i class="fas fa-calendar-alt"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('properties.edit', $property->slug) }}"
                                        class="btn btn-secondary rounded-circle btn-sm" title="Editar">
                                            <i class="fas fa-edit text-white"></i>
                                        </a>
                                        <button class="btn btn-danger rounded-circle btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{ $property->id }}" title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach

                            @if ($list->isEmpty())
                                <tr>
                                    <td colspan="5" class="text-center">No hay propiedades registradas
                                        <a href="{{ route('properties.new') }}" class="btn btn-primary btn-sm">Crear
                                        Propiedad</a>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                  </div>
            </div>
        </div>

    </div>
    <div class="col-12">
    <div class="d-flex justify-content-center">
    {{ $list->links() }}
    </div>
    </div>
    </div>
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
