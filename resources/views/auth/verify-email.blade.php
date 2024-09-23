@extends('layouts.layout')

@section('content')
<div class="container text-center p-5 my-5">
    @if (session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif
    <h1>Verifica tu correo electrónico</h1>
    <p>Antes de continuar, te pedimos que verifiques tu correo electrónico.</p>
    <p>Revisa tu bandeja de entrada para encontrar el enlace de verificación.</p>
    <p>Si no lo ves, asegúrate de revisar la carpeta de spam.</p>
    <p>Si no has recibido el correo, puedes solicitar un <a href="#" onclick="event.preventDefault(); document.getElementById('resend-verification-form').submit();">nuevo enlace de verificación</a>.</p>


    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form id="resend-verification-form" action="{{ route('verification.resend') }}" method="POST" style="display: none;">
        @csrf
    </form>
</div>
@endsection
