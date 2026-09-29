@extends('layouts.layout')

@section('title', 'BienesCorp - Aviso de Privacidad')
@section('description', 'Aviso de privacidad de BienesCorp: qué datos personales recabamos, para qué los usamos y cómo ejercer tus derechos ARCO.')
@section('og:title', 'BienesCorp - Aviso de Privacidad')
@section('og:description', 'Aviso de privacidad de BienesCorp')
@section('og:image', asset('BienesCorpLogo.png'))
@section('og:url', url()->current())

@section('content')

    <x-top-background
        :image="asset('JPG-11.jpg')"
        eyebrow="Transparencia"
        subtitle="Cómo tratamos tus datos personales y cómo puedes ejercer tus derechos.">
        Aviso de Privacidad
    </x-top-background>

    <div class="container">
        <div class="legal-doc">
            <p class="page-intro">
                En cumplimiento de la Ley Federal de Protección de Datos Personales en Posesión de los
                Particulares, {{ config('legal.razon_social') }} pone a tu disposición el presente aviso
                de privacidad.
            </p>

            <h2>1. Responsable de tus datos personales</h2>
            <p>
                {{ config('legal.razon_social') }}, con domicilio en {{ config('legal.domicilio') }},
                es responsable del tratamiento de los datos personales que nos proporcionas a través de
                este sitio.
            </p>

            <h2>2. Qué datos recabamos</h2>
            <p>Según cómo uses el sitio, podemos recabar:</p>
            <ul>
                <li><strong>De identificación y contacto:</strong> nombre, correo electrónico y teléfono, cuando llenas un formulario de contacto o te registras.</li>
                <li><strong>De la propiedad:</strong> los datos e imágenes que capturas al publicar un inmueble, incluida su ubicación.</li>
                <li><strong>De navegación:</strong> dirección IP, tipo de navegador y páginas visitadas.</li>
            </ul>
            <p>
                No recabamos datos personales sensibles. Los pagos se procesan directamente por PayPal y
                Mercado Pago: <strong>no almacenamos números de tarjeta</strong> en nuestros servidores.
            </p>

            <h2>3. Para qué usamos tus datos</h2>
            <ul>
                <li>Poner en contacto a personas interesadas con quien publica un inmueble.</li>
                <li>Crear y administrar tu cuenta, tus propiedades y tus reservaciones.</li>
                <li>Procesar el pago de los paquetes de publicación y de las reservaciones.</li>
                <li>Responder tus dudas y darte soporte.</li>
                <li>Cumplir obligaciones legales y fiscales.</li>
            </ul>

            <h2>4. Con quién los compartimos</h2>
            <p>
                Cuando envías un mensaje sobre un inmueble, tus datos de contacto se comparten con quien
                lo publica, que es justamente el propósito del formulario. Además usamos proveedores que
                tratan datos por nuestra cuenta: el proveedor de hospedaje del sitio, los servicios de
                correo, y las pasarelas de pago PayPal y Mercado Pago. No vendemos tus datos.
            </p>

            <h2>5. Tus derechos ARCO</h2>
            <p>
                Puedes solicitar el <strong>Acceso</strong>, <strong>Rectificación</strong>,
                <strong>Cancelación</strong> u <strong>Oposición</strong> al tratamiento de tus datos,
                así como revocar tu consentimiento, escribiendo a
                <a href="mailto:{{ config('legal.email') }}">{{ config('legal.email') }}</a>.
                Incluye tu nombre, un medio para contactarte y la descripción clara de lo que solicitas.
                Responderemos en un plazo máximo de 20 días hábiles.
            </p>

            <h2>6. Cookies</h2>
            <p>
                Este sitio usa cookies propias para mantener tu sesión iniciada y recordar tus
                preferencias. Puedes deshabilitarlas desde tu navegador, aunque algunas funciones, como
                iniciar sesión, dejarán de operar.
            </p>

            <h2>7. Cambios a este aviso</h2>
            <p>
                Podemos actualizar este aviso. Cualquier cambio se publicará en esta misma página, por lo
                que te sugerimos revisarla periódicamente.
            </p>

            <h2>8. Contacto</h2>
            <p>
                Para cualquier duda sobre este aviso escríbenos a
                <a href="mailto:{{ config('legal.email') }}">{{ config('legal.email') }}</a>
                o llámanos al {{ config('legal.telefono') }}.
            </p>

            <p class="legal-doc__date">Última actualización: {{ config('legal.vigencia') }}.</p>
        </div>
    </div>
@endsection
