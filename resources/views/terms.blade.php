@extends('layouts.layout')

@section('title', 'BienesCorp - Términos y Condiciones')
@section('description', 'Términos y condiciones de uso de BienesCorp: cuentas, publicación de propiedades, paquetes, reservaciones y pagos.')
@section('og:title', 'BienesCorp - Términos y Condiciones')
@section('og:description', 'Términos y condiciones de uso de BienesCorp')
@section('og:image', asset('BienesCorpLogo.png'))
@section('og:url', url()->current())

@section('content')

    <x-top-background
        :image="asset('JPG-11.jpg')"
        eyebrow="Reglas del servicio"
        subtitle="Las condiciones bajo las que puedes usar la plataforma.">
        Términos y Condiciones
    </x-top-background>

    <div class="container">
        <div class="legal-doc">
            <p class="page-intro">
                Al usar {{ config('legal.nombre_comercial') }} aceptas estos términos. Si no estás de
                acuerdo con alguno, no uses la plataforma.
            </p>

            <h2>1. Qué es este servicio</h2>
            <p>
                {{ config('legal.nombre_comercial') }} es una plataforma donde agentes y propietarios
                publican inmuebles en venta, renta o reservación por estancia. Somos un
                <strong>intermediario tecnológico</strong>: no somos parte de la operación entre quien
                publica y quien contacta, ni actuamos como corredor o notario.
            </p>

            <h2>2. Tu cuenta</h2>
            <ul>
                <li>Debes ser mayor de edad y dar información veraz.</li>
                <li>Eres responsable de tu contraseña y de todo lo que se haga desde tu cuenta.</li>
                <li>Podemos suspender cuentas que incumplan estos términos o publiquen información falsa.</li>
            </ul>

            <h2>3. Publicación de propiedades</h2>
            <p>Al publicar un inmueble declaras que:</p>
            <ul>
                <li>Eres el propietario o tienes autorización para ofrecerlo.</li>
                <li>Los datos, precios y fotografías son reales y corresponden al inmueble.</li>
                <li>Tienes los derechos sobre las imágenes que subes.</li>
            </ul>
            <p>
                Podemos retirar sin previo aviso publicaciones duplicadas, engañosas, con contenido
                ofensivo o que infrinjan derechos de terceros.
            </p>

            <h2>4. Paquetes y pagos</h2>
            <p>
                Publicar requiere un paquete con un número determinado de anuncios. Los pagos se
                procesan mediante PayPal y Mercado Pago; al contratar aceptas también las condiciones de
                esos proveedores. Los precios están en pesos mexicanos (MXN) e incluyen los impuestos
                aplicables salvo que se indique lo contrario.
            </p>
            <p>
                Los paquetes son de consumo digital inmediato: <strong>no son reembolsables</strong> una
                vez que se ha publicado al menos un inmueble con ellos. Si un cobro fue duplicado o
                erróneo, escríbenos y lo resolvemos.
            </p>

            <h2>5. Reservaciones</h2>
            <p>
                En los inmuebles marcados como reservables, la reservación se confirma hasta que el pago
                se acredita. Una reservación pendiente puede expirar automáticamente si no se paga
                dentro del plazo indicado. Las condiciones de la estancia, la entrega de llaves y las
                reglas de la propiedad las define quien publica.
            </p>

            <h2>6. Contenido y responsabilidad</h2>
            <p>
                Verificamos el inventario en la medida de lo razonable, pero la información de cada
                inmueble la captura quien publica y es su responsabilidad. Te recomendamos siempre
                visitar el inmueble y revisar la documentación legal antes de entregar cualquier
                cantidad de dinero. {{ config('legal.nombre_comercial') }} no responde por acuerdos,
                anticipos o daños derivados de la relación entre las partes.
            </p>

            <h2>7. Propiedad intelectual</h2>
            <p>
                La marca, el diseño y el software del sitio son propiedad de
                {{ config('legal.razon_social') }}. El contenido que subes sigue siendo tuyo, pero nos
                otorgas una licencia para mostrarlo y difundirlo dentro de la plataforma y en la
                promoción de tu anuncio.
            </p>

            <h2>8. Datos personales</h2>
            <p>
                El tratamiento de tus datos se rige por nuestro
                <a href="{{ route('privacy') }}">Aviso de Privacidad</a>.
            </p>

            <h2>9. Cambios y ley aplicable</h2>
            <p>
                Podemos modificar estos términos; la versión vigente es siempre la publicada en esta
                página. Para cualquier controversia se aplican las leyes mexicanas y la jurisdicción de
                los tribunales de Los Mochis, Sinaloa.
            </p>

            <h2>10. Contacto</h2>
            <p>
                Escríbenos a <a href="mailto:{{ config('legal.email') }}">{{ config('legal.email') }}</a>
                o al {{ config('legal.telefono') }}.
            </p>

            <p class="legal-doc__date">Última actualización: {{ config('legal.vigencia') }}.</p>
        </div>
    </div>
@endsection
