@extends('layouts.layout')

@section('title', 'BienesCorp - Perfil')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Perfil')
@section('og:description', 'Somos expertos en asesoría.')
@section('og:image', asset('public/BienesCorpLogo.png'))
@section('og:url', url()->current())

@section('content')

    <x-top-background>
        Banner
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
                        <h3>{{ $banner ? 'Editar' : 'Nuevo'  }} Banner</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.banners.' . (isset($banner->id) ? 'save' : 'new'),  $banner->id ?? null ) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <x-form-input name="title" id="title" label="Titulo" value="{{ $banner->title ?? old('title') }}" />
                            </div>

                            <div class="mb-3">
                                <x-form-input name="subtitle" id="subtitle" label="Subtitulo" value="{{ $banner->subtitle ?? old('subtitle') }}" />
                            </div>

                            <div class="mb-3">
                                <div class="form-floating mb-3">
                                    @if(isset($banner->image_path) && $banner->image_path)
                                        <img class="card-img-top" src="{{ $banner->image_path ?? '' }}" />
                                    @endif
                                    <div class="mb-3">
                                        <input name="image_path" class="form-control p-3 @error('image_path') {{ 'is-invalid' }} @enderror"
                                            aria-describedby="invalidImagePath"
                                            type="file" id="formFile">
                                        @error('image_path')
                                            <div id="invalidImagePath" class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <x-form-input name="position" id="position" label="Posición" value="{{ $banner->position ?? old('position') }}" type="number" />
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input name="is_active" class="form-check-input" type="checkbox" value="1" id="flexCheckIndeterminate" checked>
                                    <label class="form-check-label" for="flexCheckIndeterminate">
                                      Activo
                                    </label>
                                  </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>

                        </form>
                    </div>
                </div>


            </div>
        </div>
    </div>

@endsection
