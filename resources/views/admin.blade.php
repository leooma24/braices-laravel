@extends('layouts.layout')

@section('title', 'BienesCorp - Administrador')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Administrador')
@section('og:description', 'Somos expertos en asesoría.')
@section('og:image', asset('public/BienesCorpLogo.png'))
@section('og:url', url()->current())

@section('content')

    <x-top-background>
        Administrador
    </x-top-background>

    <x-admin-header />

    <div class="container">
        <div class="row ">
            <div class="col-xs-12">
                <div class="create-user d-flex justify-content-end">
                    <a href="{{ route('users.create') }}" class="btn btn-primary">Crear Usuario</a>
                </div>
                <div class="table-responsive">
                    <table class="table mt-3">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Correo</th>
                                <th scope="col">Rol</th>
                                <th scope="col" width="70px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <th scope="row">{{ $user->id }}</th>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if ($user->hasRole('admin'))
                                            <span class="badge bg-danger">Administrador</span>
                                        @endif
                                        @if ($user->hasRole('user'))
                                            <span class="badge bg-primary">Usuario</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('users.edit', $user->id) }}"
                                                class="btn btn-secondary rounded-circle btn-sm">
                                                <i class="fas fa-edit text-white"></i>
                                            </a>
                                            <a href="{{ route('admin.users.assign', $user->id) }}"
                                                class="btn btn-primary ms-2 rounded-circle btn-sm">
                                                <i class="fas fa-cart-plus"></i>
                                            </a>
                                            <button class="btn btn-danger ms-2 rounded-circle btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{ $user->id }}">
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
    ¿Estás seguro de que deseas eliminar esta propiedad?
</x-delete-modal>

    <script>
        var deleteModal = document.getElementById('deleteModal')
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget
            var id = button.getAttribute('data-id')
            var form = document.getElementById('deleteForm')
            form.action = '/administrador/usuario/eliminar/' + id
        })
    </script>


@endsection
