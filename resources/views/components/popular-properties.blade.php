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
                <article class="card h-100 position-relative">
                    <a href="{{ route('property', $property->slug) }}" class="d-block">
                        <img src="{{ $property->photo_main }}" class="card-img-top" alt="{{ $property->title }}" loading="lazy">
                    </a>

                    <div class="types position-absolute top-0 start-0 m-3 d-flex flex-wrap gap-2">
                        @foreach($property->propertyTypes as $propertyType)
                            <span class="badge">{{ $propertyType->name }}</span>
                        @endforeach
                        <span class="badge">{{ $property->transaction->name }}</span>
                    </div>

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-1 text-truncate">{{ $property->title }}</h5>
                        <p class="card-text mb-3 text-muted-2 small text-truncate">
                            <i class="fas fa-map-marker-alt me-1 text-primary"></i>{{ $property->address }}
                        </p>

                        <div class="d-flex flex-wrap gap-3 small text-muted-2 mb-3">
                            @if($property->isLand())
                                <span><i class="fas fa-arrows-alt-h me-1 text-primary"></i>{{ number_format($property->front) }} m frente</span>
                                <span><i class="fas fa-arrows-alt-v me-1 text-primary"></i>{{ number_format($property->depth) }} m fondo</span>
                            @else
                                <span><i class="fas fa-bed me-1 text-primary"></i>{{ $property->bedrooms }} rec.</span>
                                <span><i class="fas fa-bath me-1 text-primary"></i>{{ $property->bathrooms }} baños</span>
                            @endif
                            <span><i class="fas fa-ruler-combined me-1 text-primary"></i>{{ number_format($property->square_feet) }} m²</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                            <span class="mc-price">${{ number_format($property->price) }}</span>
                            <a href="{{ route('property', $property->slug) }}" class="btn btn-link p-0">
                                Ver más <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </article>
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
