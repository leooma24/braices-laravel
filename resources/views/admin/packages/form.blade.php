@extends('layouts.layout')

@section('title', 'BienesCorp - Perfil')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Perfil')
@section('og:description', 'Somos expertos en asesoría.')
@section('og:image', asset('public/BienesCorpLogo.png'))
@section('og:url', url()->current())

@section('content')

    <x-top-background>
        Paquetes
    </x-top-background>

    <div class="container">
        <div class="row ">
            @if ($errors->any())
            <div class="col-xs-12">
                <div class="alert alert-danger mt-3">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
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
            <div class="col-md-4 col-xs-12">

            </div>

            <div class="col-md-8 col-xs-12">
                <div class="card my-3">
                    <div class="card-header">
                        <h3>{{ $package ? 'Editar' : 'Nuevo'  }} Paquete</h3>
                    </div>
                    <div class="card-body">
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

                            <hr />

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Guardar</button>
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
