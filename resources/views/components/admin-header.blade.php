<nav class="admin-subnav">
    <div class="container">
        <ul class="admin-subnav__list">
            <li>
                <a href="{{ route('admin') }}"
                   class="admin-subnav__link {{ Request::is('administrador') ? 'is-active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>Usuarios</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.banners') }}"
                   class="admin-subnav__link {{ Request::is('administrador/banners*') ? 'is-active' : '' }}">
                    <i class="fas fa-image"></i>
                    <span>Banners</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.packages.index') }}"
                   class="admin-subnav__link {{ Request::is('administrador/paquetes*') ? 'is-active' : '' }}">
                    <i class="fas fa-box-open"></i>
                    <span>Paquetes</span>
                </a>
            </li>
        </ul>
    </div>
</nav>

<div class="container">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
</div>
