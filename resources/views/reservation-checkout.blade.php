@extends('layouts.layout')

@section('title', 'Confirmar pago - ' . $reservation->property->title)
@section('description', 'Confirmar pago de reservación')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <h1 class="h3 mb-4">Confirmar reservación</h1>

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">{{ $reservation->property->title }}</h5>
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Check-in</dt>
                            <dd class="col-sm-8">{{ $reservation->check_in_date->format('d/m/Y') }} a partir de {{ $reservation->property->check_in_time ?? '15:00' }}</dd>

                            <dt class="col-sm-4">Check-out</dt>
                            <dd class="col-sm-8">{{ $reservation->check_out_date->format('d/m/Y') }} antes de {{ $reservation->property->check_out_time ?? '12:00' }}</dd>

                            <dt class="col-sm-4">Noches</dt>
                            <dd class="col-sm-8">{{ $reservation->nights }}</dd>

                            <dt class="col-sm-4">Huéspedes</dt>
                            <dd class="col-sm-8">{{ $reservation->guests }}</dd>

                            <dt class="col-sm-4">Subtotal</dt>
                            <dd class="col-sm-8">${{ number_format((float) $reservation->subtotal, 2) }}</dd>

                            <dt class="col-sm-4">Limpieza</dt>
                            <dd class="col-sm-8">${{ number_format((float) $reservation->cleaning_fee_snapshot, 2) }}</dd>

                            <dt class="col-sm-4 fw-bold">Total a pagar</dt>
                            <dd class="col-sm-8 fw-bold">${{ number_format((float) $reservation->total_price, 2) }} MXN</dd>
                        </dl>
                    </div>
                </div>

                @if($reservation->expires_at)
                    <p class="small text-muted">
                        Esta reservación se libera automáticamente si no completas el pago antes de
                        <strong>{{ $reservation->expires_at->format('H:i') }}</strong>.
                    </p>
                @endif

                <div id="mp-checkout"></div>

                <p class="small text-muted mt-3">
                    Pago procesado de forma segura por MercadoPago.
                </p>
            </div>
        </div>
    </div>

    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <script>
        const mp = new MercadoPago(@json($mpPublicKey), { locale: 'es-MX' });
        mp.checkout({
            preference: { id: @json($preferenceId) },
            render: {
                container: '#mp-checkout',
                label: 'Pagar con MercadoPago',
            },
        });
    </script>
@endsection
