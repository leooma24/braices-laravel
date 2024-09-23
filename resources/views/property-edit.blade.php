@extends('layouts.layout')

@section('title', 'BienesCorp - Administración de Bienes Inmuebles')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Administración de Bienes Inmuebles')
@section('og:description', 'Administración de Bienes Inmuebles')
@section('og:image', asset('BienesCorpLogo.png'))
@section('og:url', url()->current())


@section('content')

    <div class="container">
        <div class="row justify-content-center ">
            <div class="col-xs-12 m-5">
                <h1 class="text-center">{{ (isset($property->id) ? 'Editar' : 'Nueva')}} Propiedad</h1>
            </div>
            <div class="col-xs-12">
                @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Datos de la Propiedad</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('property.save' . (isset($property->id) ? '' : '.new'), $property->id ?? null)}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="lat" id="lat" value="{{ $property->lat ?? '25.7910913' }}">
                            <input type="hidden" name="long" id="long" value="{{ $property->long ?? '-108.9959443' }}">
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                  <button class="nav-link active" id="data-tab" data-bs-toggle="tab" data-bs-target="#data" type="button" role="tab" aria-controls="data-tab-pane" aria-selected="true">Datos</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                  <button class="nav-link" id="images-tab" data-bs-toggle="tab" data-bs-target="#images" type="button" role="tab" aria-controls="images-tab-pane" aria-selected="false">Imagenes</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="map-tab" data-bs-toggle="tab" data-bs-target="#map" type="button" role="tab" aria-controls="map-tab-pane" aria-selected="false">Mapa</button>
                                  </li>

                              </ul>
                              <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade pt-3 show active" id="data" role="tabpanel" aria-labelledby="data-tab" tabindex="0">
                                    <div class="row">
                                        <div class="col-xs-12 col-md-6">
                                            <div class="form-floating">
                                                @if(isset($property->photo_main) && $property->photo_main)
                                                    <img class="card-img-top" src="{{ $property->photo_main ?? '' }}" />
                                                @endif
                                                <div class=" mt-3">
                                                    <input name="photo_main" class="form-control p-3 @error('photo_main') {{ 'is-invalid' }} @enderror"
                                                        aria-describedby="invalidPhoto"
                                                     type="file" id="formFile">
                                                     @error('property_type_id')
                                                <div id="invalidPhoto" class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                                  </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-md-6">
                                            <select
                                                multiple
                                                name="property_type_id[]"
                                                class="form-select p-3 mb-3  @error('property_type_id') {{ 'is-invalid' }} @enderror"
                                                aria-describedby="invalidType"
                                                >
                                                <option value="">Tipo de Propiedad</option>
                                                @foreach ($types as $type)
                                                    <option value="{{ $type->id }}"
                                                        @if(in_array($type->id, $property->propertyTypes->pluck('id')->toArray())) selected @endif>
                                                        {{ $type->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('property_type_id')
                                                <div id="invalidType" class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror

                                            <select name="transaction_type_id" class="form-select p-3 mb-3  @error('property_type_id') {{ 'is-invalid' }} @enderror"
                                                aria-label="Large select example"
                                                aria-describedby="invalidTran">
                                                <option value="">Tipo de Transacción</option>
                                                @foreach ($transactions as $transaction)
                                                    <option value="{{ $transaction->id }}"
                                                        {{ isset($property->transaction_type_id) && $transaction->id == $property->transaction_type_id ? 'selected' : ($transaction->id == old('transaction_type_id') ? 'selected' : '') }}>
                                                        {{ $transaction->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('transaction_type_id')
                                                <div id="invalidtran" class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror

                                            <div class="form-floating mb-3">
                                                <input name="title" type="text" class="form-control
                                                    @error('title') {{ 'is-invalid' }} @enderror"
                                                    id="title"
                                                    aria-describedby="invalidTitle"
                                                    placeholder="Titulo"
                                                    title
                                                    value="{{ $property->title ??  old('title') }}"
                                                    >
                                                <label for="title">Titulo</label>
                                                @error('title')
                                                    <div id="invalidTitle" class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="form-floating mb-3">
                                                <input name="address" type="text" class="form-control @error('address') {{ 'is-invalid' }} @enderror" id="address"
                                                    placeholder="Dirección"
                                                    aria-describedby="invalidAddress"
                                                    value="{{ $property->address ?? old('address') }}"
                                                    >
                                                <label for="title">Dirección</label>

                                                @error('address')
                                                    <div id="invalidAddress" class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                        </div>



                                        <div class="col-xs-12 col-md-6">
                                            <div class="form-floating mb-3">
                                                <input name="price" type="text" class="form-control @error('price') {{ 'is-invalid' }} @enderror" id="price"
                                                    value="{{ $property->price ?? old('price') }}"
                                                    aria-describedby="invalidPrice"
                                                    placeholder="Precio">
                                                <label for="price">Precio</label>
                                                @error('price')
                                                    <div id="invalidPrice" class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>


                                        <div class="col-xs-12 col-md-6">
                                            <x-form-input
                                                name="square_feet"
                                                label="Metros cuadrados (Terreno)"
                                                type="number"
                                                value="{{ $property->square_feet ?? '' }}" />

                                        </div>



                                        <div class="col-xs-12 col-md-6">
                                            <div class="form-floating mb-3">
                                                <input name="bedrooms" type="number" class="form-control @error('bedrooms') {{ 'is-invalid' }} @enderror" id="bedrooms"
                                                value="{{ $property->bedrooms ?? old('bedrooms') }}"
                                                aria-describedby="invalidBedrooms"
                                                    placeholder="Cuartos">
                                                <label for="bedrooms">Cuartos</label>
                                                @error('bedrooms')
                                                    <div id="invalidBedrooms" class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-xs-12 col-md-6">
                                          <div class="form-floating mb-3">
                                              <input name="bathrooms" type="number" class="form-control @error('bathrooms') {{ 'is-invalid' }} @enderror" id="bathrooms"
                                              value="{{ $property->bathrooms ?? old('bathrooms') }}"
                                              aria-describedby="invalidBathrooms"
                                                  placeholder="Baños">
                                              <label for="bathrooms">Baños</label>
                                                @error('bathrooms')
                                                    <div id="invalidBathrooms" class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                          </div>
                                        </div>


                                        <div class="col-xs-12 col-md-6">
                                            <x-form-input
                                                name="square_meters_contruction"
                                                label="Metros Cuadrados de Contrucción"
                                                type="number"
                                                value="{{ $property->square_meters_contruction ?? '' }}" />

                                        </div>

                                        <div class="col-xs-12 col-md-6">
                                            <x-form-input
                                                name="front"
                                                label="Metros de Frente"
                                                value="{{ $property->front ?? '' }}" />

                                        </div>

                                        <div class="col-xs-12 col-md-6">
                                            <x-form-input
                                                name="depth"
                                                label="Metros de fondo"
                                                type="number"
                                                value="{{ $property->depth ?? '' }}" />

                                        </div>

                                        <div class="col-xs-12 col-md-6">
                                            <x-form-input
                                                name="levels"
                                                label="Niveles"
                                                type="number"
                                                value="{{ $property->levels ?? '' }}" />

                                        </div>

                                        <div class="col-xs-12 col-md-6">
                                            <x-form-input
                                                name="year_built"
                                                label="Año de Construcción"
                                                type="number"
                                                value="{{ $property->year_built ?? '' }}" />

                                        </div>

                                        <div class="col-xs-12 col-md-6">
                                          <div class="form-floating mb-3">
                                              <input name="lot_size" type="text" class="form-control @error('lot_size') {{ 'is-invalid' }} @enderror" id="lot_size"
                                                value="{{ $property->lot_size ?? old('lot_size') }}"
                                                aria-describedby="invalidLotSize"
                                                  placeholder="Metros Cuadrados">
                                              <label for="lot_size">Tamaño</label>
                                                @error('lot_size')
                                                    <div id="invalidLotSize" class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                          </div>
                                        </div>

                                        <div class="col-xs-12 col-md-6">
                                            <x-form-select
                                                name="country"
                                                label="Pais"
                                                value="1"
                                                :options="$countries"
                                                textLabel="nombre" />
                                        </div>

                                        <div class="col-xs-12 col-md-6">
                                            <x-form-select
                                                name="state"
                                                label="Estado"
                                                :value="$property->state ?? ''"
                                                :options="$states"
                                                textLabel="nombre" />
                                        </div>

                                        <div class="col-xs-12 col-md-6">
                                            <x-form-select
                                                name="township"
                                                label="Municipio"
                                                :value="$property->township ?? ''"
                                                :options="$townships"
                                                textLabel="nombre" />
                                        </div>

                                        <div class="col-xs-12 col-md-6">
                                          <div class="form-floating mb-3">
                                              <input name="city" type="text" class="form-control @error('city') {{ 'is-invalid' }} @enderror" id="city"
                                                value="{{ $property->city ?? old('city') }}"
                                                aria-describedby="invalidCity"
                                                  placeholder="Ciudad">
                                              <label for="city">Ciudad</label>
                                                @error('city')
                                                    <div id="invalidCity" class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                          </div>
                                        </div>

                                        <div class="col-xs-12 col-md-6">
                                            <x-form-select
                                                name="suburb"
                                                label="Colonia"
                                                :value="$property->suburb ?? ''"
                                                :options="$suburbs"
                                                textLabel="nombre"
                                                prop="codigo_postal" />
                                        </div>

                                        <div class="col-xs-12 col-md-6">
                                          <div class="form-floating mb-3">
                                              <input name="zip" type="text" class="form-control @error('zip') {{ 'is-invalid' }} @enderror" id="zip"
                                                value="{{ $property->zip ?? old('zip') }}"
                                                aria-describedby="invalidZip"
                                                  placeholder="Codigo Postal">
                                              <label for="zip">Codigo Postal</label>
                                                @error('zip')
                                                    <div id="invalidZip" class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                          </div>
                                        </div>

                                        <div class="col-xs-12 col-md-6">
                                          <div class="form-floating mb-3">
                                              <input name="youtube" type="text" class="form-control @error('youtube') {{ 'is-invalid' }} @enderror" id="youtube"
                                                value="{{ $property->youtube ?? '' }}"
                                                aria-describedby="invalidYoutube"
                                                  placeholder="Url Youtube">
                                              <label for="youtube">Url Youtube</label>
                                                @error('youtube')
                                                    <div id="invalidYoutube" class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                          </div>
                                        </div>

                                        <div class="col-xs-12 col-md-6 mb-3">
                                          <select name="property_status_id" class="form-select p-3  @error('property_status_id') {{ 'is-invalid' }} @enderror"
                                              aria-label="Large select example"
                                                aria-describedby="invalidStatus"
                                              >
                                              <option value="">Estatus</option>
                                              @foreach ($status as $statu)
                                                  <option value="{{ $statu->id }}"
                                                      {{ isset($property->property_status_id) && $statu->id == $property->property_status_id ? 'selected' : ($statu->id == old('property_status_id') ? 'selected' : '' ) }}>
                                                      {{ $statu->name }}</option>
                                              @endforeach
                                          </select>
                                            @error('property_status_id')
                                                <div id="invalidStatus" class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                      </div>

                                      <div class="col-xs-12 col-md-6">
                                        <div class="form-floating mb-3">
                                            <textarea style="min-height:200px;" name="description" class="form-control @error('description') {{ 'is-invalid' }} @enderror" id="description"
                                                aria-describedby="invalidDescription"
                                                placeholder="Descripcion">{{ $property->description ?? old('description') }}</textarea>
                                            <label for="description">Descripcion</label>
                                            @error('description')
                                                <div id="invalidDescription" class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                      </div>



                                    </div>
                                </div>
                                <div class="tab-pane fade pt-3" id="images" role="tabpanel" aria-labelledby="images-tab" tabindex="0">
                                    <div class="col-xs-12 col-md-6">
                                        <div class="form-floating mb-3">
                                            <div class="mb-3">
                                                <input name="images[]" class="form-control p-3" type="file" id="formImages" multiple>
                                              </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        @if(isset($property->images) && $property->images)
                                            @foreach($property->images as $image)
                                                <div class="col-xs-12 col-md-4 position-relative">
                                                    <img class="card-img-top" src="{{ $image->photo ?? '' }}" />
                                                    <a href="{{ route('property.delete.image', [$property->id, $image->id]) }}" class="position-absolute top-0 end-0 btn btn-danger mt-2 me-3"><i class="fa fa-trash"></i>  </a>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>

                                <div class="tab-pane fade pt-3 show" id="map" role="tabpanel" aria-labelledby="map-tab" tabindex="0">
                                    <div class="col-xs-12">
                                        <div id="googleMap" style="height: 500px; width: 100%;"></div>
                                    </div>
                                </div>

                              </div>

                              <div class="col-xs-12">
                                <div class="d-flex justify-content-end">
                                  <button type="submit" class="btn btn-primary">Guardar</button>
                                  <a href="{{ route('myProperties') }}" class="ms-2 btn btn-secondary">Cancelar</a>
                                </div>
                              </div>

                        </form>
                    </div>

                </div>

            </div>
        </div>

        <script>
            $(document).ready(function() {
                $('#zip').blur(function() {
                    // Obtener el valor del campo de entrada
                    var value = $(this).val();

                    // Realizar la petición AJAX
                    $.ajax({
                        url: `/ajax/zip?search=${value}`, // Cambia esto por la URL de tu servidor
                        type: 'GET', // O 'GET' según sea necesario
                        success: function(response) {
                            // Manejar la respuesta del servidor
                            $('#city').val(response.city);
                            $('#state').val(response.township.estado).change();

                            $('#township').val(response.township.id);
                            $('#suburb').empty();
                            response.suburbs.forEach(element => {
                                $('#suburb').append(`<option value="${element.id}" codigo_postal="${element.codigo_postal}">${element.nombre}</option>`);
                            });
                            $('#suburb').val(response.id);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            // Manejar errores
                            console.error('Error en la petición AJAX:', textStatus, errorThrown);
                        }
                    });
                });

                $('#state').change(function() {
                    // Obtener el valor del campo de entrada
                    var value = $(this).val();

                    // Realizar la petición AJAX
                    $.ajax({
                        url: `/ajax/state?search=${value}`, // Cambia esto por la URL de tu servidor
                        type: 'GET', // O 'GET' según sea necesario
                        success: function(response) {
                            // Manejar la respuesta del servidor
                            $('#township').empty();
                            response.forEach(element => {
                                $('#township').append(`<option value="${element.id}">${element.nombre}</option>`);
                            });
                            console.log('Respuesta del servidor:', response);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            // Manejar errores
                            console.error('Error en la petición AJAX:', textStatus, errorThrown);
                        }
                    });
                })

                $('#suburb').change(function() {
                    const value = $('#suburb option:selected').attr('codigo_postal');
                    $('#zip').val(value);
                })
            });
        </script>

<script async defer
src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&callback=initMap">
</script>

<script>
    function initMap() {
        // Configuración inicial del mapa
        var location = { lat: {{ $property->lat ?? 25.7910913 }}, lng: {{ $property->long ?? -108.9959443 }} };
        var map = new google.maps.Map(document.getElementById('googleMap'), {
            zoom: 14,
            center: location
        });

        // Opcional: agregar un marcador
        var marker = new google.maps.Marker({
            position: location,
            map: map,
            draggable: true,
        });

        google.maps.event.addListener(marker, 'dragend', function(event) {
            var newLat = event.latLng.lat();
            var newLng = event.latLng.lng();

            // Por ejemplo, puedes enviar las coordenadas a un input oculto para enviarlas al backend
            document.getElementById('lat').value = newLat;
            document.getElementById('long').value = newLng;
        });
    }
</script>


    @endsection
