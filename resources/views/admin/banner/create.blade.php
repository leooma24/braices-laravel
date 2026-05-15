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
                <h1>{{ isset($banner->id) ? 'Editar Banner' : 'Nuevo Banner' }}</h1>
                <p>Imágenes promocionales que aparecen en el sitio.</p>
            </div>
            <a href="{{ route('admin.banners') }}" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-2"></i>Banners
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
                        <h2><i class="fas fa-image text-primary me-2"></i>Datos del banner</h2>
                    </div>
                    <div class="dashboard-card__body">
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

                            <div class="form-action-bar">
                                <a href="{{ route('admin.banners') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-times me-1"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Guardar banner
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
