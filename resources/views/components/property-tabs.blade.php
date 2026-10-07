@props(['recent', 'popular' => null])

{{-- "Propiedades Recientes" y "Las mas vistas" eran dos rejillas identicas de
     tres columnas, una tras otra. Misma informacion, el doble de scroll. Aqui
     comparten una sola seccion con pestañas. --}}
<section class="container mt-5 property-types">
    <div class="row mb-4">
        <div class="col d-flex justify-content-between align-items-end flex-wrap gap-3">
            <h2 class="mc-title m-0">Propiedades</h2>
            <a href="/propiedades" class="btn btn-outline-primary btn-sm">
                Ver todas <i class="fas fa-arrow-right ms-1" aria-hidden="true"></i>
            </a>
        </div>
    </div>

    @php($hasPopular = $popular && $popular->isNotEmpty() && $popular->count() >= 3)

    @if($hasPopular)
        <ul class="nav nav-pills property-tabs__nav mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-recientes" data-bs-toggle="pill"
                        data-bs-target="#panel-recientes" type="button" role="tab"
                        aria-controls="panel-recientes" aria-selected="true">Recientes</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-vistas" data-bs-toggle="pill"
                        data-bs-target="#panel-vistas" type="button" role="tab"
                        aria-controls="panel-vistas" aria-selected="false">Las más vistas</button>
            </li>
        </ul>
    @endif

    <div class="tab-content">
        <div class="tab-pane fade show active" id="panel-recientes" role="tabpanel" aria-labelledby="tab-recientes">
            <div class="row g-4">
                @foreach($recent as $property)
                    <div class="col-12 col-md-6 col-lg-4">
                        <x-property-grid-card :property="$property" />
                    </div>
                @endforeach
            </div>
        </div>

        @if($hasPopular)
            <div class="tab-pane fade" id="panel-vistas" role="tabpanel" aria-labelledby="tab-vistas">
                <div class="row g-4">
                    @foreach($popular as $property)
                        <div class="col-12 col-md-6 col-lg-4">
                            <x-property-grid-card :property="$property" />
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="row mt-5">
        <div class="col-12 text-center">
            <a href="/propiedades" class="btn btn-primary btn-lg">
                <i class="fas fa-search me-2" aria-hidden="true"></i>Buscar Propiedades
            </a>
        </div>
    </div>
</section>
