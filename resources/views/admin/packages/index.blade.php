@extends('layouts.layout')


@section('content')
    <x-top-background>
        Paquetes
    </x-top-background>

    <x-admin-header />


    <div class="container">
        <div class="row ">
            <div class="col-xs-12">
                <div class="create-user d-flex justify-content-end">
                    <a href="{{ route('admin.packages.create') }}" class="btn btn-primary">Crear Paquete</a>
                </div>
                <div class="table-responsive">
                    <table class="table mt-3">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col" width="60%">Nombre</th>
                                <th scope="col" >Precio</th>
                                <th scope="col">Cantidad asignada</th>
                                <th scope="col" width="70px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($packages as $package)
                                <tr>
                                    <th scope="row">{{ $package->id }}</th>
                                    <td>{{ $package->name }}</td>
                                    <td>{{ $package->price }}</td>
                                    <td>{{ $package->max_listings }}</td>
                                    <td>
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('admin.packages.edit', $package->id) }}"
                                                class="btn btn-secondary rounded-circle btn-sm">
                                                <i class="fas fa-edit text-white"></i>
                                            </a>
                                            <button class="btn btn-danger ms-2 rounded-circle btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{ $package->id }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
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
