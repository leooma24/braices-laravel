@extends('layouts.layout')

@section('title', 'BienesCorp - Nosotros')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Administración de Bienes Inmuebles')
@section('og:description', 'Administración de Bienes Inmuebles')
@section('og:image', asset('BienesCorpLogo.png') )
@section('og:url', url()->current())

@section('content')

    <x-top-background :image="asset('JPG-11.jpg')">
        Sobre Nosotros
    </x-top-background>

    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="row text-center d-flex justify-content-center align-items-center">
                    <div class="col-md-6 col-xs-12">
                        <h1 class="text-center mt-5 mb-3 mc-title">¿Quiénes somos?</h1>
                        <p class="text-justify">Somos una plataforma desarrollada por Medios
                            Corp. Agencia Digital con mas de 25 años de experiencia en el ramo
                            de desarrollo web. Nuestro principal objetivo es brindarles a los
                            agentes y empresas del ramo inmobiliario nuestro servicio de
                            administración de propiedades en la nube en tiempo real. Esta
                            plataforma esta pensada especialmente para reducirle los tiempos
                            al inmobiliario al momento de hacer difusión en linea, en su sitio
                            web, en redes sociales o a través de los diferentes mensajeros que
                            existen en el mercado.
                        </p>
                    </div>
                </div>
                <hr />

                <div class="row mt-5">
                    <div class="col-md-4 col-xs-12">
                        <img src="{{ asset('SVG-26.svg')}}" class="mc-us-svg" alt="Misión">
                        <h1 class="mb-3 text-primary-dark">Misión</h1>
                        <p>Brindar soluciones integrales y confiables
                            en la administración de bienes inmuebles,
                            enfocándonos en maximizar el valor de
                            las propiedades de nuestros clientes, a
                            través de un servicio personalizado y
                            eficiente que les permita disfrutar de su
                            inversión sin preocupaciones.</p>
                    </div>
                    <div class="col-md-4 col-xs-12">
                        <img src="{{ asset('SVG-27.svg')}}" class="mc-us-svg" alt="Misión">
                        <h1 class="mb-3 text-primary-dark">Visión</h1>
                        <p>Ser la empresa líder en la administración
                            de bienes inmuebles a nivel nacional,
                            reconocida por nuestra excelencia en el
                            servicio, innovación y compromiso con la
                            satisfacción de nuestros clientes. Nos
                            esforzamos por establecer relaciones de
                            confianza duraderas y por ser el referente
                            en el sector inmobiliario.</p>
                    </div>
                    <div class="col-md-4 col-xs-12">
                        <img src="{{ asset('SVG-28.svg')}}" class="mc-us-svg" alt="Misión">
                        <h1 class="mb-3 text-primary-dark">Valores</h1>
                        <p class="mb-0">Estos son los valores que nos definen
                            como una empresa profesional: </p>
                            <ul>
                                <li>Integridad</li>
                                <li>Compromiso</li>
                                <li>Innovación</li>
                                <li>Profesionalismo</li>
                                <li>Trabajo en equipo</li>
                            </ul>
                    </div>
                </div>
            </div>

        </div>



    </div>

@endsection
