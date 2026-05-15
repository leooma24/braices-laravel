@extends('layouts.layout')

@section('title', 'BienesCorp - Perfil')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Perfil')
@section('og:description', 'Somos expertos en asesoría.')
@section('og:image', asset('public/BienesCorpLogo.png'))
@section('og:url', url()->current())

@section('content')

    <x-admin-header />

    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1>{{ isset($package->id) ? 'Editar Paquete' : 'Nuevo Paquete' }}</h1>
                <p>Define el plan, precio y características visibles a los usuarios.</p>
            </div>
            <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-2"></i>Paquetes
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="dashboard-card">
                    <div class="dashboard-card__header">
                        <h2><i class="fas fa-box-open text-primary me-2"></i>Datos del paquete</h2>
                    </div>
                    <div class="dashboard-card__body">
                        <form action="{{ route('admin.packages.' . (isset($package->id) ? 'save' : 'new'),  $package->id ?? null ) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <x-form-input name="name" label="Nombre" :value="$package->name ?? ''" />
                            </div>

                            <div class="mb-3">
                                <x-form-input name="price" label="Precio" :value="$package->price ?? ''" type="number" />
                            </div>

                            <div class="mb-3">
                                <x-form-input name="max_listings" label="Cantidad asignada" :value="$package->max_listings ?? ''" type="number" />
                            </div>

                            <div class="mb-3">
                                <x-form-input name="duration" label="Duración" :value="$package->duration ?? ''" type="number" />
                            </div>

                            <div class="mb-3 mt-5">
                                <label><strong>Características</strong></label>
                                <hr />

                                <div class="d-flex mb-3">
                                    <input id="characteristic" placeholder="Ej. Soporte Técnico" type="text" name="characteristic" class="form-control characteristic-input" />
                                    <button type="button" class="btn btn-primary ms-3" id="addCharacteristic" >Agregar</button>
                                </div>

                                <div class="list-items">
                                    @if (isset($package->characteristics))
                                        @foreach ($package->characteristics as $characteristic)
                                            <div class="characteristic-item d-flex mb-3">
                                                <input type="text" name="characteristics[]" value="{{ $characteristic }}" class="form-control characteristic-input" />
                                                <button type="button" class="btn btn-danger ms-3 remove-characteristic">Remove</button>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <div class="form-action-bar">
                                <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-times me-1"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Guardar paquete
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function ()  {
            const addBtn = document.getElementById('addCharacteristic');
            const characteristicsList = document.querySelector('.list-items');

            addBtn.addEventListener('click', function() {
                let characteristic = document.querySelector('input[name="characteristic"]');
                const newCharacteristic = document.createElement('div');
                newCharacteristic.classList.add('characteristic-item', 'd-flex', 'mb-3');

                newCharacteristic.innerHTML = `
                    <input type="text" name="characteristics[]" class="form-control" value="${characteristic.value}" />
                    <button type="button" class="ms-2 btn btn-danger remove-characteristic">Remove</button>
                `;
                characteristicsList.appendChild(newCharacteristic);
                characteristic.value = '';
            });

            characteristicsList.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-characteristic')) {
                    e.target.parentElement.remove();
                }
            });
        }
    </script>

@endsection
