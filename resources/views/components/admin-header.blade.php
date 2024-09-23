<style>
    .active {
        color: #0d6efd !important;
        border-bottom: 2px solid #0d6efd;
    }
</style>

<nav class="py-2 bg-body-tertiary border-bottom">
    <div class="container d-flex flex-wrap">
      <ul class="nav">
        <li class="nav-item"><a href="{{ route('admin') }}" class="nav-link link-body-emphasis px-2 {{ Request::is('administrador') ? 'active' : '' }}" aria-current="page">Usuarios</a></li>
        <li class="nav-item"><a href="{{ route('admin.banners') }}" class="nav-link link-body-emphasis px-2 {{ Request::is('administrador/banners') ? 'active' : '' }}">Banners</a></li>
        <li class="nav-item"><a href="{{ route('admin.packages.index') }}" class="nav-link link-body-emphasis px-2 {{ Request::is('administrador/paquetes') ? 'active' : '' }}">Paquetes</a></li>
      </ul>
    </div>
  </nav>

<div class="container">
    <div class="row ">
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
                        {{ session('danger') }}
                    </div>
                </div>
            @endif
        <div class="col-xs-12">
            <h1 class="text-center mt-5 mb-3">Bienvenido Administrador</h1>
            <p class="text-justify">Bienvenido al panel de administración de BienesCorp. Aquí podrás gestionar las
                propiedades, clientes y empleados de la empresa. Si tienes alguna duda, por favor contáctanos.</p>
        </div>
    </div>
</div>
