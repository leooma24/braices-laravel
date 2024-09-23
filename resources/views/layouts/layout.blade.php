<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"
        integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <title>@yield('title')</title>
    <meta name="description" content="@yield('description')" />
    <meta property="og:title" content="@yield('og:title')">
    <meta property="og:description" content="@yield('og:description')" />
    <meta property="og:image" content="@yield('og:image')" />
    <meta property="og:url" content="@yield('og:url')" />
    <meta name="twitter:card" content="summary_large_image" />

    <link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">

    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</head>

<body>
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="myToast" class="toast align-items-center text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
            <div class="toast-body">
                ¡Hola! Bienvenido a BienesCorp, si tienes alguna duda o pregunta, no dudes en contactarnos.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <div class="container">
        <header class="pt-3 pb-2">
            <div class="container-fluid">
                <div class="d-flex justify-content-between justify-content-md-end flex-row align-items-center">
                    <div class="d-block d-md-none" style="width: 140px;">
                        <a class="navbar-brand" href="/">
                            <img src="{{ asset('BienesCorpLogo.png') }}" class="mc-logo" alt="Logo"
                                class="d-inline-block align-text-top"
                                style="max-width: 100%;">

                        </a>
                    </div>

                </div>
            </div>
        </header>


        <nav class="navbar sticky-top navbar-expand-lg text-center text-lg-left"
            aria-label="Thirteenth navbar example">

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample11"
                aria-controls="navbarsExample11" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse d-lg-flex" id="navbarsExample11">
                <a class="navbar-brand col-lg-3 me-0" href="/">
                    <img src="{{ asset('BienesCorpLogo.png') }}" class="mc-logo" alt="Logo"
                        class="d-inline-block align-text-top">

                </a>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('/') ? 'active' : '' }}" aria-current="page"
                            href="/">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('nosotros') ? 'active' : '' }}"
                            href="/nosotros">Sobre Nosotros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('propiedades') ? 'active' : '' }}"
                            href="/propiedades">Propiedades</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('planes') ? 'active' : '' }}"
                            href="/planes">Planes</a>
                    </li>
                    <li class="nav-item me-4">
                        <a class="nav-link {{ Request::is('contacto') ? 'active' : '' }}"
                            href="/contacto">Contacto</a>
                    </li>

                    @auth
                            <li class="nav-item position-relative" style="min-width: 160px;">
                                <button class="nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false" data-bs-display="static">
                                    <span style="min-width: 130px;"
                                        class="badge  p-1 pe-2 text-white bg-primary border border-primary-subtle rounded-pill">
                                        <img class="rounded-circle me-1" width="28" height="28"
                                            src="{{ Auth::user()->photo }}" alt="">{{ Auth::user()->name }}
                                    </span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-start">
                                    <li><a class="dropdown-item py-3" href="{{ route('profile') }}">Datos Personales</a>
                                    </li>
                                    <li><a class="dropdown-item py-3 {{ Request::is('cuenta/mis-propiedades') ? 'active' : '' }}"
                                            href="{{ route('myProperties') }}">Mis Propiedades</a></li>
                                    <li><a class="dropdown-item py-3 {{ Request::is('propiedad/nueva') ? 'active' : '' }}"
                                            href="{{ route('properties.new') }}">Agregar Propiedad</a></li>

                                    @role('admin')
                                        <li><a class="dropdown-item py-3 {{ Request::is('administrador') ? 'active' : '' }}" href="{{ route('admin')}}">Administrador</a> </li>
                                    @endrole

                                    <hr class="dropdown-divider">
                                    <li><a class="dropdown-item" href="{{ route('logout') }}">Salir</a></li>
                                </ul>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="btn btn-secondary mb-1 mc-login {{ Request::is('login') ? 'active' : '' }}" href="/login">Iniciar Sesión</a>
                            </li>
                            <li class="nav-item">
                                <a class="btn btn-primary mc-register {{ Request::is('registrar') ? 'active' : '' }}" href="/registrarse">Registrarse</a>
                            </li>
                        @endauth
                </ul>

            </div>

        </nav>
    </div>


    <div class="main-content mb-5">
        @yield('content')
    </div>


    <div class="container">
        @if(!auth()->check())
            <div class="container text-center d-flex justify-content-center">
                <div class="col-xxl-8 ">
                    <h1 class="mb-3">¡Regístrate o inicia sesión para comenzar y disfrutar de todos nuestros servicios!</h1>
                        <a href="#" class="btn btn-secondary">Iniciar Sesión</a>
                        <a href="#" class="btn btn-primary">Registrarse</a>
                </div>
            </div>
        @endif
        <div class="text-success">
            <hr>
        </div>
        <footer class="py-2 my-4 text-center">
            <img src="{{ asset('BienesCorpLogo.png') }}" class="mc-logo" alt="Logo"
                        class="d-inline-block align-text-top">
            <ul class="nav justify-content-center pb-3 mb-3">
                <li class="nav-item"><a href="/" class="nav-link px-2 text-body-secondary {{ Request::is('/') ? 'active' : '' }}">Inicio</a></li>
                <li class="nav-item"><a href="/nosotros" class="nav-link px-2 text-body-secondary {{ Request::is('nosotros') ? 'active' : '' }}">Nosotros</a></li>
                <li class="nav-item"><a href="/propiedades" class="nav-link px-2 text-body-secondary {{ Request::is('propiedades') ? 'active' : '' }}">Propiedades</a>
                </li>
                <li class="nav-item"><a href="/contacto" class="nav-link px-2 text-body-secondary {{ Request::is('contacto') ? 'active' : '' }}">Contacto</a></li>
            </ul>
            <div class="d-flex justify-content-center flex-column flex-sm-row">
                <a href="tel:6688180202" class="me-3 mb-2 link-body-emphasis text-decoration-none">
                    <i class="fa fa-phone me-2 text-primary-light"></i> +52 668 818 02 02
                </a>
                <a href="mailto:info@bienescorp.com" class="mb-2 me-3 link-body-emphasis text-decoration-none">
                    <i class="fa fa-envelope fa-lg me-2 text-primary-light"></i> info@bienescorp.com
                </a>
            </div>
            <div class="text-success">
                <hr>
            </div>
            <p class="text-center text-body-secondary">&copy; {{ date('Y') }} BienesCorp. Todos los Drechos Reservados</p>
        </footer>
    </div>


    <!--<iframe src="http://braices-laravel.test/user-properties/1" width="100%" height="600px" style="border:none;">-->



    <script defer src="https://use.fontawesome.com/releases/v5.15.4/js/solid.js"
        integrity="sha384-/BxOvRagtVDn9dJ+JGCtcofNXgQO/CCCVKdMfL115s3gOgQxWaX/tSq5V8dRgsbc" crossorigin="anonymous">
    </script>
    <script defer src="https://use.fontawesome.com/releases/v5.15.4/js/fontawesome.js"
        integrity="sha384-dPBGbj4Uoy1OOpM4+aRGfAOc0W37JkROT+3uynUgTHZCHZNMHfGXsmmvYTffZjYO" crossorigin="anonymous">
    </script>
</body>

</html>
