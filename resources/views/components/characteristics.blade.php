<section class="section-characteristics py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="mb-2">Por qué BienesCorp</h2>
                <p class="lead text-muted-2 mb-0">Tres razones para publicar y reservar con nosotros.</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-12 col-md-4">
                <div class="characteristic h-100 p-4 rounded-3 bg-white border position-relative">
                    <div class="d-inline-flex align-items-center justify-content-center mb-3 rounded-circle"
                         style="background: var(--color-primary-50); width: 56px; height: 56px;">
                        <img src="{{ asset('house.svg') }}" alt="" width="32" height="32">
                    </div>
                    <h4 class="mb-2">Publicación fácil</h4>
                    <p class="mb-0 text-muted-2">Sube y gestiona tus propiedades en minutos con nuestra interfaz intuitiva y amigable.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="characteristic h-100 p-4 rounded-3 bg-white border position-relative">
                    <div class="d-inline-flex align-items-center justify-content-center mb-3 rounded-circle"
                         style="background: var(--color-primary-50); width: 56px; height: 56px;">
                        <img src="{{ asset('calendar.svg') }}" alt="" width="32" height="32">
                    </div>
                    <h4 class="mb-2">Calendario en tiempo real</h4>
                    <p class="mb-0 text-muted-2">Controla la disponibilidad de tus propiedades publicando y bloqueando fechas al instante.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="characteristic h-100 p-4 rounded-3 bg-white border position-relative">
                    <div class="d-inline-flex align-items-center justify-content-center mb-3 rounded-circle"
                         style="background: var(--color-primary-50); width: 56px; height: 56px;">
                        <img src="{{ asset('search.svg') }}" alt="" width="32" height="32">
                    </div>
                    <h4 class="mb-2">Búsqueda avanzada</h4>
                    <p class="mb-0 text-muted-2">Encuentra y filtra propiedades rápidamente con herramientas pensadas para el comprador mexicano.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .section-characteristics .characteristic {
        transition: transform var(--duration) var(--ease), box-shadow var(--duration) var(--ease);
    }
    .section-characteristics .characteristic:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
    }
</style>
