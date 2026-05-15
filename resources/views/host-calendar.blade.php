@extends('layouts.layout')

@section('title', 'Calendario - ' . $property->title)
@section('description', 'Administrar disponibilidad de la propiedad')

@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
@endpush

@section('content')
    <div class="container py-4">
        <div class="dashboard-header">
            <div>
                <h1>{{ $property->title }}</h1>
                <p>Administrar disponibilidad y bloqueos de fechas.</p>
            </div>
            <a href="{{ route('myProperties') }}" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-2"></i>Mis propiedades
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body">
                        <p class="small text-muted mb-2">
                            Las fechas en gris están bloqueadas (por ti o por reservaciones existentes).
                            Selecciona un rango y elige bloquear o liberar.
                        </p>
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Modificar disponibilidad</h5>
                        <div class="mb-2">
                            <label class="form-label small">Rango</label>
                            <input type="text" id="rangeInput" class="form-control" placeholder="Selecciona rango" readonly>
                            <input type="hidden" id="rangeFrom">
                            <input type="hidden" id="rangeTo">
                        </div>
                        <div class="d-flex gap-2">
                            <button id="blockBtn" class="btn btn-outline-danger flex-fill" disabled>Bloquear</button>
                            <button id="unblockBtn" class="btn btn-outline-success flex-fill" disabled>Liberar</button>
                        </div>
                        <div id="actionMessage" class="alert mt-3 d-none"></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Próximas reservas</h5>
                        @forelse($reservations as $reservation)
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $reservation->user->name }}</strong>
                                    <span class="badge {{ $reservation->status->value === 'confirmada' ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ $reservation->status->label() }}
                                    </span>
                                </div>
                                <small class="text-muted">
                                    {{ $reservation->check_in_date->format('d/m/Y') }} → {{ $reservation->check_out_date->format('d/m/Y') }}
                                </small>
                            </div>
                        @empty
                            <p class="small text-muted mb-0">Sin reservaciones próximas.</p>
                        @endforelse
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
            const updateUrl = @json(route('host.calendar.update', $property->slug));
            let blockedDates = @json($blockedDates);

            function showMessage(text, type) {
                const el = document.getElementById('actionMessage');
                el.className = 'alert mt-3 alert-' + type;
                el.textContent = text;
                el.classList.remove('d-none');
            }

            const cal = flatpickr('#rangeInput', {
                inline: false,
                mode: 'range',
                dateFormat: 'Y-m-d',
                minDate: @json($rangeStart),
                maxDate: @json($rangeEnd),
                locale: 'es',
                onChange: function (selectedDates) {
                    const valid = selectedDates.length === 2;
                    document.getElementById('blockBtn').disabled = !valid;
                    document.getElementById('unblockBtn').disabled = !valid;
                    if (valid) {
                        document.getElementById('rangeFrom').value = cal.formatDate(selectedDates[0], 'Y-m-d');
                        document.getElementById('rangeTo').value = cal.formatDate(selectedDates[1], 'Y-m-d');
                    }
                },
            });

            const inlineCal = flatpickr('#calendar', {
                inline: true,
                mode: 'multiple',
                dateFormat: 'Y-m-d',
                minDate: @json($rangeStart),
                maxDate: @json($rangeEnd),
                locale: 'es',
                showMonths: 2,
                disable: blockedDates,
                clickOpens: false,
            });

            async function updateAvailability(action) {
                const from = document.getElementById('rangeFrom').value;
                const to = document.getElementById('rangeTo').value;
                if (!from || !to) return;

                try {
                    const res = await fetch(updateUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ from, to, action }),
                    });
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        showMessage(err.message || 'Error al actualizar.', 'danger');
                        return;
                    }
                    const data = await res.json();
                    blockedDates = data.blocked;
                    inlineCal.set('disable', blockedDates);
                    inlineCal.redraw();
                    showMessage(action === 'block' ? 'Fechas bloqueadas.' : 'Fechas liberadas.', 'success');
                    cal.clear();
                    document.getElementById('blockBtn').disabled = true;
                    document.getElementById('unblockBtn').disabled = true;
                } catch (e) {
                    showMessage('Error de red.', 'danger');
                }
            }

            document.getElementById('blockBtn').addEventListener('click', () => updateAvailability('block'));
            document.getElementById('unblockBtn').addEventListener('click', () => updateAvailability('unblock'));
        })();
    </script>
@endsection
