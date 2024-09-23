@extends('layouts.layout')

@section('title', 'BienesCorp - Contacto')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Contacto')
@section('og:description', 'Somos expertos en asesoría.')
@section('og:image', asset('public/BienesCorpLogo.png'))
@section('og:url', url()->current())

@section('content')

    <x-top-background :image="asset('JPG-13.jpg')">
        Contacto
    </x-top-background>

    <div class="container">
        <div class="card shadow p-5 mc-contact">
            <div class="row text-center justify-content-center">

                @if (session('success'))
                    <div class="alert alert-success col-12 col-md-7">
                        {{ session('success') }}
                    </div>
                @endif
                <div class="col-12 col-md-7">
                    <h1 class="text-center mt-2 mb-3">Plataforma de Administración de Inmuebles.</h1>
                    <p class="text-justify">Somos una plataforma desarrollada por Medios Corp.
                        Agencia Digital con mas de 25 años de experiencia en el ramo de
                        desarrollo web. Nuestro principal objetivo es brindarles a los
                        agentes y empresas del ramo inmobiliario nuestro servicio de
                        administración de propiedades en la nube en tiempo real.</p>
                </div>
            </div>
            <hr />
            <div class="row ">
                <div class="col-md-6 col-xs-12">
                    <p>Estamos aquí para ayudarte en todo lo que necesites referente a nuestra
                        plataforma. Si tienes preguntas, necesitas ayuda o deseas conocer más
                        sobre nuestros servicios, no dudes en contactarnos. Nuestro equipo de
                        soporte técnico está disponible para brindarte la atención y resolver
                        cualquier inquietud que puedas tener. Si tienes una petición de mejora
                        a futuro o te gustaría que incluyéramos alguna función  especial en la
                        plataforma no dudes en hacérnoslo saber.</p>

                    <h6 class="text-primary-dark mb-4">¿Tienes dudas? Llámanos</h6>

                    <a href="tel:+526688180202" class="bg-primary-dark text-white display-inline-block p-3 rounded text-decoration-none">
                        <i class="fas fa-phone-alt"></i>
                        +52 668 818 0202
                    </a>
                </div>

                <div class="col-md-6 col-xs-12">
                    <form action="{{ route('contact') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name">Nombre:</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" >
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group">
                                    <label for="email">Correo Electrónico:</label>
                                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" >

                                </div>
                            </div>
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group">
                                    <label for="phone">Teléfono:</label>
                                    <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}" >
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="message">Mensaje:</label>
                            <textarea name="message" id="message" class="form-control">{{ old('message') }}</textarea>
                        </div>
                        <div class="text-center mt-2">
                            {!! NoCaptcha::display() !!}
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary mt-3 px-5">Enviar</button>
                        </div>

                        @if ($errors->has('g-recaptcha-response'))
                            <span class="text-danger">{{ $errors->first('g-recaptcha-response') }}</span>
                        @endif
                    </form>


                </div>
            </div>
        </div>

    </div>

    {!! NoCaptcha::renderJs() !!}

@endsection
