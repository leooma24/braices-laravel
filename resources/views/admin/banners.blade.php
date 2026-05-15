@extends('layouts.layout')

@section('title', 'BienesCorp - Administrador')

@section('description', 'Administración de Bienes Inmuebles')

@section('og:title', 'BienesCorp - Administrador')

@section('og:description', 'Somos expertos en asesoría.')

@section('og:image', asset('public/BienesCorpLogo.png'))

@section('og:url', url()->current())

@section('content')

    <x-admin-header />

    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1>Banners</h1>
                <p>Banners que aparecen en la portada del sitio.</p>
            </div>
            <a href="{{ route('admin.banners.create') }}" class="btn btn-accent btn-lg">
                <i class="fas fa-plus me-2"></i>Crear banner
            </a>
        </div>

        <div class="dashboard-card">
            <div class="dashboard-card__body p-0">
                <div class="table-responsive">
                    <table class="table mc-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th>Título</th>
                                <th>Subtítulo</th>
                                <th style="width: 100px;">Posición</th>
                                <th class="text-end" style="width: 130px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($banners as $banner)
                                <tr>
                                    <th scope="row" class="text-muted-2">#{{ $banner->id }}</th>
                                    <td class="fw-semibold">{{ $banner->title }}</td>
                                    <td class="text-muted-2">{{ $banner->subtitle }}</td>
                                    <td><span class="badge" style="background: var(--color-primary-50); color: var(--color-primary-dark);">{{ $banner->position }}</span></td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="{{ route('admin.banners.edit', $banner->id) }}"
                                               class="btn btn-outline-primary btn-sm" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-outline-danger btn-sm"
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                    data-id="{{ $banner->id }}" title="Eliminar">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            @if($banners->isEmpty())
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state mb-0">
                                            <div class="empty-state__icon">
                                                <i class="fas fa-image"></i>
                                            </div>
                                            <h5>Aún no hay banners</h5>
                                            <a href="{{ route('admin.banners.create') }}" class="btn btn-primary mt-2">
                                                <i class="fas fa-plus me-2"></i>Crear banner
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-delete-modal>
        ¿Estás seguro de que deseas eliminar este Banner?
    </x-delete-modal>

        <script>
            var deleteModal = document.getElementById('deleteModal')
            deleteModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget
                var id = button.getAttribute('data-id')
                var form = document.getElementById('deleteForm')
                form.action = '/administrador/banner/eliminar/' + id
            })
        </script>
@endsection
