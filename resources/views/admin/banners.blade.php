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
        <div class="row">
            <div class="col-xs-12">
                <div class="create-user d-flex justify-content-end">
                    <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">Crear Banner</a>
                </div>
                <div class="table-responsive">
                    <table class="table mt-3">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Titulo</th>
                                <th scope="col">Subtitulo</th>
                                <th scope="col">Posición</th>
                                <th scope="col" width="70px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($banners as $banner)
                                <tr>
                                    <th scope="row">{{ $banner->id }}</th>
                                    <td>{{ $banner->title }}</td>
                                    <td>{{ $banner->subtitle }}</td>
                                    <td>{{ $banner->position }} </td>

                                    <td>
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('admin.banners.edit', $banner->id) }}"
                                                class="btn btn-secondary rounded-circle btn-sm">
                                                <i class="fas fa-edit text-white"></i>
                                            </a>
                                            <button class="btn btn-danger ms-2 rounded-circle btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{ $banner->id }}">
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
