<div class="container mt-5 property-types">
    <div class="row mb-4">
        <div class="col d-flex justify-content-between align-items-end flex-wrap gap-3">
            <h2 class="mc-title m-0">{{ $slot }}</h2>
            <a href="/propiedades" class="btn btn-outline-primary btn-sm">
                Ver todas <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    <div class="row g-4">
        @foreach($properties as $property)
            <div class="col-12 col-md-6 col-lg-4">
                <x-property-grid-card :property="$property" />
            </div>
        @endforeach
    </div>

    <div class="row mt-5">
        <div class="col-12 text-center">
            <a href="/propiedades" class="btn btn-primary btn-lg">
                <i class="fas fa-search me-2"></i>Buscar Propiedades
            </a>
        </div>
    </div>
</div>
