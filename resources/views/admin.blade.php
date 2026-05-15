@extends('layouts.layout')

@section('title', 'BienesCorp - Administrador')
@section('description', 'Panel de Administración')
@section('og:title', 'BienesCorp - Administrador')
@section('og:description', 'Panel de Administración')
@section('og:image', asset('BienesCorpLogo.png'))
@section('og:url', url()->current())

@section('content')

    <x-admin-header />

    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1>Usuarios</h1>
                <p>Administra cuentas, roles y paquetes contratados.</p>
            </div>
            <a href="{{ route('users.create') }}" class="btn btn-accent btn-lg">
                <i class="fas fa-user-plus me-2"></i>Crear usuario
            </a>
        </div>

        <div class="dashboard-card">
            <div class="dashboard-card__body p-0">
                <div class="table-responsive">
                    <table class="table mc-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 60px;">ID</th>
                                <th scope="col">Usuario</th>
                                <th scope="col">Correo</th>
                                <th scope="col">Rol</th>
                                <th scope="col">Plan</th>
                                <th scope="col" class="text-end" style="width: 200px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                @php
                                    $activePkg = $user->userPackages
                                        ->filter(fn($p) => $p->remaining_listings > 0)
                                        ->sortByDesc('id')
                                        ->first()
                                        ?? $user->userPackages->sortByDesc('id')->first();
                                @endphp
                                <tr>
                                    <th scope="row" class="text-muted-2">#{{ $user->id }}</th>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $user->photo }}" alt="{{ $user->name }}"
                                                 class="rounded-circle" width="36" height="36"
                                                 style="object-fit: cover; border: 2px solid var(--color-border);">
                                            <div>
                                                <div class="fw-semibold">{{ $user->name }}</div>
                                                @if($user->phone_number)
                                                    <small class="text-muted-2">{{ $user->phone_number }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="text-muted-2 small">{{ $user->email }}</span></td>
                                    <td>
                                        @if ($user->hasRole('admin'))
                                            <span class="badge" style="background: var(--color-danger); color: #fff;">
                                                <i class="fas fa-shield-alt me-1"></i>Admin
                                            </span>
                                        @endif
                                        @if ($user->hasRole('user'))
                                            <span class="badge" style="background: var(--color-primary-50); color: var(--color-primary-dark);">
                                                <i class="fas fa-user me-1"></i>Usuario
                                            </span>
                                        @endif
                                        @if (!$user->hasRole('admin') && !$user->hasRole('user'))
                                            <span class="text-muted-2 small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($activePkg && $activePkg->package)
                                            <div>
                                                <span class="badge"
                                                      style="background: var(--color-accent); color: #fff;">
                                                    <i class="fas fa-box me-1"></i>{{ $activePkg->package->name }}
                                                </span>
                                            </div>
                                            <small class="text-muted-2 d-block mt-1">
                                                {{ $activePkg->remaining_listings }} publicaciones disponibles
                                                @if($activePkg->expires_at)
                                                    · vence {{ \Carbon\Carbon::parse($activePkg->expires_at)->format('d/m/Y') }}
                                                @endif
                                            </small>
                                        @else
                                            <span class="text-muted-2 small">
                                                <i class="fas fa-minus-circle me-1"></i>Sin plan
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="{{ route('users.edit', $user->id) }}"
                                               class="btn btn-outline-primary btn-sm" title="Editar usuario">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('admin.users.assign', $user->id) }}"
                                               class="btn btn-outline-warning btn-sm" title="Asignar paquete">
                                                <i class="fas fa-box"></i>
                                            </a>
                                            <button class="btn btn-outline-danger btn-sm"
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                    data-id="{{ $user->id }}" title="Eliminar">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            @if($users->isEmpty())
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="empty-state mb-0">
                                            <div class="empty-state__icon">
                                                <i class="fas fa-users"></i>
                                            </div>
                                            <h5>Aún no hay usuarios registrados</h5>
                                            <p class="mb-3">Crea el primero para empezar.</p>
                                            <a href="{{ route('users.create') }}" class="btn btn-primary">
                                                <i class="fas fa-user-plus me-2"></i>Crear usuario
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

        <div class="d-flex justify-content-center my-4">
            {{ $users->links() }}
        </div>
    </div>

    <x-delete-modal>
        ¿Estás seguro de que deseas eliminar este usuario?
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
