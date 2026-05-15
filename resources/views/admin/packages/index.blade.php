@extends('layouts.layout')


@section('content')
    <x-admin-header />

    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1>Paquetes</h1>
                <p>Planes que los usuarios pueden contratar.</p>
            </div>
            <a href="{{ route('admin.packages.create') }}" class="btn btn-accent btn-lg">
                <i class="fas fa-plus me-2"></i>Crear paquete
            </a>
        </div>

        <div class="dashboard-card">
            <div class="dashboard-card__body p-0">
                <div class="table-responsive">
                    <table class="table mc-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th>Nombre</th>
                                <th style="width: 140px;">Precio</th>
                                <th style="width: 180px;">Publicaciones</th>
                                <th class="text-end" style="width: 130px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($packages as $package)
                                <tr>
                                    <th scope="row" class="text-muted-2">#{{ $package->id }}</th>
                                    <td class="fw-semibold">{{ $package->name }}</td>
                                    <td><span class="mc-price" style="font-size: 1rem;">${{ number_format($package->price, 2) }}</span></td>
                                    <td>
                                        <span class="badge" style="background: var(--color-accent); color: #fff;">
                                            <i class="fas fa-box me-1"></i>{{ $package->max_listings }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="{{ route('admin.packages.edit', $package->id) }}"
                                               class="btn btn-outline-primary btn-sm" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-outline-danger btn-sm"
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                    data-id="{{ $package->id }}" title="Eliminar">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            @if($packages->isEmpty())
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state mb-0">
                                            <div class="empty-state__icon">
                                                <i class="fas fa-box-open"></i>
                                            </div>
                                            <h5>Aún no hay paquetes</h5>
                                            <a href="{{ route('admin.packages.create') }}" class="btn btn-primary mt-2">
                                                <i class="fas fa-plus me-2"></i>Crear paquete
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
        ¿Estás seguro de que deseas eliminar este paquete?
    </x-delete-modal>

    <script>
        var deleteModal = document.getElementById('deleteModal')
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget
            var id = button.getAttribute('data-id')
            var form = document.getElementById('deleteForm')
            form.action = `/administrador/paquetes/eliminar/${id}`
        })
    </script>
@endsection
