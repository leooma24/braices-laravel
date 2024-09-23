<div class="container mt-5 property-types">
    <div class="row">
        <div class="col">
            <h2 class="mb-5 mc-title">{{ $slot }}</h2>
        </div>
    </div>
    <div class="row">
        @foreach($properties as $property)
        <div class="col-xs-12 col-md-6 col-lg-4">
            <div class="card mb-5 border-1 bg-white">
                <a href="{{ route('property', $property->slug) }}">
                    <img src="{{ $property->photo_main }}" class="card-img-top" alt="...">
                </a>
                <div class="types position-absolute top-0 start-0 m-3">
                    @foreach($property->propertyTypes as $propertyType)
                        <div class="badge p-2 bg-danger">{{ $propertyType->name }}</div>
                    @endforeach
                    <div class="badge p-2 bg-warning rounded-0">{{ $property->transaction->name }}</div>
                </div>

                <div class="card-body">
                    <div class="card-title mb-0"><strong>{{ $property->title }}</strong></div>
                    <p class="card-text mb-2 text-secondary text-truncate">{{ $property->address }}</p>

                    <div class="text-success">
                        <hr>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="me-3">
                            <img src="{{ asset('bed.svg') }}" alt="bed" width="20" height="20" />
                            {{ $property->bedrooms }}
                            Recámaras</span>
                        <span class="me-3">
                            <img src="{{ asset('baths.svg') }}" alt="bed" width="20" height="20" />
                             {{ $property->bathrooms }}
                            Baños</span>
                        <span>
                            <img src="{{ asset('sizes.svg') }}" alt="bed" width="20" height="20" />
                            {{ number_format($property->square_feet) }}
                            m²</span>
                    </div>


                    <div class="text-success">
                        <hr>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="h4 m-0">${{ number_format($property->price) }}</p>
                        <a href="{{ route('property', $property->slug) }}" class="btn btn-link see-more">
                            Ver más <i class="fas fa-arrow-right"></i> </a>
                    </div>


                </div>
            </div>
        </div>
        @endforeach

    </div>

    <div class="row">
        <div class="col-12 text-center">
            <a href="/propiedades" class="btn btn-primary px-5 py-3">
                <i class="fas fa-search fs-5"></i>
                <span class="pl-5">Buscar Propiedades</span>
            </a>

        </div>
    </div>
  </div>
