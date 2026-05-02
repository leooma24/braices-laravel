@extends('layouts.layout')

@section('title', 'Reservar - ' . $property->title)
@section('description', Str::limit(strip_tags($property->description), 155))
@section('og:title', 'Reservar ' . $property->title)
@section('og:description', Str::limit(strip_tags($property->description), 155))
@section('og:image', $property->photo_main ?? asset('BienesCorpLogo.png'))
@section('og:url', url()->current())

@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
@endpush

@section('content')
    <div class="container py-4">
        <div class="row g-4">
            {{-- Columna izquierda: detalles propiedad --}}
            <div class="col-lg-7">
                <h1 class="h3 mb-2">{{ $property->title }}</h1>
                <p class="text-muted mb-3">
                    @if($averageRating)
                        <i class="fa fa-star text-warning"></i>
                        <strong>{{ $averageRating }}</strong>
                        <span class="ms-1">({{ $reviewsCount }} {{ $reviewsCount === 1 ? 'reseña' : 'reseñas' }})</span>
                        <span class="mx-2">·</span>
                    @endif
                    {{ $property->suburbName?->nombre }}{{ $property->suburbName ? ', ' : '' }}{{ $property->townshipName?->nombre }}
                </p>

                @if($property->photo_main)
                    <img src="{{ $property->photo_main }}" alt="{{ $property->title }}" class="img-fluid rounded mb-3">
                @endif

                <div class="row text-center mb-3">
                    @if($property->max_guests)
                        <div class="col">
                            <i class="fa fa-users fs-4"></i>
                            <div class="small text-muted">Hasta {{ $property->max_guests }} huéspedes</div>
                        </div>
                    @endif
                    @if($property->bedrooms)
                        <div class="col">
                            <i class="fa fa-bed fs-4"></i>
                            <div class="small text-muted">{{ $property->bedrooms }} {{ $property->bedrooms === 1 ? 'recámara' : 'recámaras' }}</div>
                        </div>
                    @endif
                    @if($property->bathrooms)
                        <div class="col">
                            <i class="fa fa-bath fs-4"></i>
                            <div class="small text-muted">{{ $property->bathrooms }} {{ $property->bathrooms === 1 ? 'baño' : 'baños' }}</div>
                        </div>
                    @endif
                </div>

                <p>{{ $property->description }}</p>
            </div>

            {{-- Columna derecha: widget de reserva --}}
            <div class="col-lg-5">
                <div class="card shadow-sm position-sticky" style="top: 1rem;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-baseline mb-3">
                            <span><strong class="fs-4">${{ number_format($property->price_per_night ?? 0, 0) }}</strong> <span class="text-muted">por noche</span></span>
                        </div>

                        @auth
                        <form id="reservationForm" method="POST" action="{{ route('reservation.store') }}" novalidate>
                        @else
                        <form id="reservationForm" method="GET" action="{{ route('login') }}" novalidate>
                        @endauth
                            @csrf
                            <input type="hidden" name="property_id" value="{{ $property->id }}">

                            <div class="row g-2 mb-2">
                                <div class="col-12">
                                    <label class="form-label small mb-1">Fechas (entrada / salida)</label>
                                    <input type="text" id="dateRange" name="date_range" class="form-control" placeholder="Selecciona fechas" autocomplete="off" readonly>
                                    <input type="hidden" id="checkIn" name="check_in">
                                    <input type="hidden" id="checkOut" name="check_out">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small mb-1">Huéspedes</label>
                                <select id="guests" name="guests" class="form-select">
                                    @php $maxGuests = $property->max_guests ?? 10; @endphp
                                    @for($i = 1; $i <= $maxGuests; $i++)
                                        <option value="{{ $i }}">{{ $i }} {{ $i === 1 ? 'huésped' : 'huéspedes' }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div id="quoteSummary" class="d-none mb-3 small">
                                <hr class="my-2">
                                <div class="d-flex justify-content-between mb-1">
                                    <span id="quoteNightsLabel">Subtotal</span>
                                    <span id="quoteSubtotal">$0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Limpieza</span>
                                    <span id="quoteCleaning">$0</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Total</span>
                                    <span id="quoteTotal">$0</span>
                                </div>
                            </div>

                            <div id="quoteError" class="alert alert-warning small d-none mb-2" role="alert"></div>

                            <button type="submit" id="reserveBtn" class="btn btn-primary w-100" disabled>
                                @auth Reservar @else Inicia sesión para reservar @endauth
                            </button>
                            <p class="small text-muted text-center mb-0 mt-2">No se te cobrará hasta confirmar el pago.</p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/es.js"></script>
    <script>
        (function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const propertyId = @json($property->id);
            const blockedDates = @json($blockedDates);
            const minDate = @json($rangeStart);
            const maxDate = @json($rangeEnd);

            const fp = flatpickr('#dateRange', {
                mode: 'range',
                dateFormat: 'Y-m-d',
                minDate: minDate,
                maxDate: maxDate,
                disable: blockedDates,
                locale: 'es',
                onChange: function (selectedDates) {
                    if (selectedDates.length === 2) {
                        const [a, b] = selectedDates;
                        document.getElementById('checkIn').value = fp.formatDate(a, 'Y-m-d');
                        document.getElementById('checkOut').value = fp.formatDate(b, 'Y-m-d');
                        fetchQuote();
                    } else {
                        document.getElementById('checkIn').value = '';
                        document.getElementById('checkOut').value = '';
                        resetQuote();
                    }
                }
            });

            document.getElementById('guests').addEventListener('change', fetchQuote);

            function resetQuote() {
                document.getElementById('quoteSummary').classList.add('d-none');
                document.getElementById('quoteError').classList.add('d-none');
                document.getElementById('reserveBtn').disabled = true;
            }

            function showError(message) {
                const el = document.getElementById('quoteError');
                el.textContent = message;
                el.classList.remove('d-none');
                document.getElementById('quoteSummary').classList.add('d-none');
                document.getElementById('reserveBtn').disabled = true;
            }

            function formatMoney(value) {
                return '$' + Number(value).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            async function fetchQuote() {
                const checkIn = document.getElementById('checkIn').value;
                const checkOut = document.getElementById('checkOut').value;
                if (!checkIn || !checkOut) {
                    resetQuote();
                    return;
                }

                try {
                    const res = await fetch('{{ route('api.reservations.quote') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            property_id: propertyId,
                            check_in: checkIn,
                            check_out: checkOut,
                            guests: parseInt(document.getElementById('guests').value, 10),
                        }),
                    });

                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        const msg = err.message || (err.errors && Object.values(err.errors).flat()[0]) || 'No se pudo cotizar.';
                        showError(msg);
                        return;
                    }

                    const data = await res.json();
                    if (!data.available) {
                        showError('Las fechas seleccionadas ya no están disponibles.');
                        return;
                    }

                    document.getElementById('quoteError').classList.add('d-none');
                    document.getElementById('quoteNightsLabel').textContent =
                        `${formatMoney(data.subtotal / data.nights)} × ${data.nights} ${data.nights === 1 ? 'noche' : 'noches'}`;
                    document.getElementById('quoteSubtotal').textContent = formatMoney(data.subtotal);
                    document.getElementById('quoteCleaning').textContent = formatMoney(data.cleaning_fee);
                    document.getElementById('quoteTotal').textContent = formatMoney(data.total);
                    document.getElementById('quoteSummary').classList.remove('d-none');
                    document.getElementById('reserveBtn').disabled = false;
                } catch (e) {
                    showError('Error de red. Intenta de nuevo.');
                }
            }

            // El submit nativo del form va a /reservaciones (auth) o a /login (guest).
        })();
    </script>
@endsection
