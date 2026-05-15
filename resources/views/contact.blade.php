@extends('layouts.layout')

@section('title', 'BienesCorp - Contacto')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Contacto')
@section('og:description', 'Somos expertos en asesoría.')
@section('og:image', asset('public/BienesCorpLogo.png'))
@section('og:url', url()->current())

@section('content')

    <x-top-background
        :image="asset('JPG-13.jpg')"
        eyebrow="Estamos para ayudarte"
        subtitle="Llámanos, escríbenos por correo o déjanos un mensaje. Nuestro equipo responde en menos de 24 horas.">
        Contacto
    </x-top-background>

    <div class="container">
        <div class="contact-card">
            <div class="contact-card__body">
                @if (session('success'))
                    <div class="alert alert-success mb-4">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    </div>
                @endif

                <div class="row g-5 align-items-start">
                    <div class="col-md-5">
                        <h2 class="mb-3" style="font-family: var(--font-display); font-weight: 700;">
                            Hablemos de tu próximo paso.
                        </h2>
                        <p class="text-muted-2 mb-4">
                            Si tienes preguntas sobre la plataforma, necesitas soporte técnico o quieres
                            que agreguemos una función a la medida, escríbenos. Nuestro equipo está
                            disponible para resolver tus dudas y proponerte la mejor solución.
                        </p>

                        <div class="mb-4">
                            <h6 class="text-uppercase text-muted-2 small mb-2" style="letter-spacing: 0.1em;">¿Prefieres llamar?</h6>
                            <a href="tel:+526688180202" class="contact-call">
                                <i class="fas fa-phone-alt"></i> +52 668 818 0202
                            </a>
                        </div>

                        <div>
                            <h6 class="text-uppercase text-muted-2 small mb-2" style="letter-spacing: 0.1em;">Correo</h6>
                            <a href="mailto:info@bienescorp.com" class="text-decoration-none" style="color: var(--color-primary-dark); font-weight: 600;">
                                <i class="fas fa-envelope me-2"></i>info@bienescorp.com
                            </a>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <form action="{{ route('contact') }}" method="POST">
                            @csrf

                            <div class="form-floating mb-3">
                                <input type="text" name="name" id="name" class="form-control" placeholder="Nombre" value="{{ old('name') }}" required>
                                <label for="name">Nombre</label>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" name="email" id="email" class="form-control" placeholder="Correo" value="{{ old('email') }}" required>
                                        <label for="email">Correo electrónico</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" name="phone" id="phone" class="form-control" placeholder="Teléfono" value="{{ old('phone') }}">
                                        <label for="phone">Teléfono</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-floating mb-3">
                                <textarea name="message" id="message" class="form-control" placeholder="Mensaje" style="height: 130px;" required>{{ old('message') }}</textarea>
                                <label for="message">Cuéntanos en qué podemos ayudarte</label>
                            </div>

                            <div class="d-flex justify-content-center mb-3">
                                {!! NoCaptcha::display() !!}
                            </div>

                            @if ($errors->has('g-recaptcha-response'))
                                <div class="alert alert-danger small py-2">
                                    {{ $errors->first('g-recaptcha-response') }}
                                </div>
                            @endif

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Enviar mensaje
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {!! NoCaptcha::renderJs() !!}

@endsection
