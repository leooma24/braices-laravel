<div class="prices py-5">
    <div class="container">
        <div class="row">
            <div class="col text-center">
                <h2 class="section-title">Nuestros Planes</h2>
                <p class="section-subtitle text-muted-2 mb-5">Elige el plan que mejor se ajuste a tu volumen. Sin contratos forzosos.</p>
            </div>
        </div>

        @php
            // Solo consideramos promo "visible" si el paquete tiene precio > 0
            // (un descuento sobre el plan gratuito no tiene sentido).
            $anyPromo = $packages->contains(fn($p) => $p->hasActivePromo() && $p->price > 0);
        @endphp

        @if($anyPromo)
            <div class="promo-banner mb-5">
                <div class="promo-banner__icon"><i class="fas fa-bolt"></i></div>
                <div class="promo-banner__body">
                    <strong>Oferta de lanzamiento</strong>
                    <span>Aprovecha el descuento en los primeros cupones de cada plan.</span>
                </div>
            </div>
        @endif

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-4">
            @foreach($packages as $index => $package)
                @php
                    // Promo solo aplica visualmente si hay precio > 0
                    $promo = $package->price > 0 ? $package->activePromotion() : null;
                    $isHighlight = $packages->count() == ($index+1);
                    $effectiveMonthly = $package->effective_monthly;
                    $effectiveAnnual = $package->effective_annual;
                    $regularMonthly = $package->monthly_price;
                    $regularAnnual = $package->annual_price;
                @endphp
                <div class="col">
                    <div class="price-card {{ $isHighlight ? 'price-card--featured' : '' }} {{ $promo ? 'price-card--promo' : '' }}">
                        @if($promo)
                            <div class="price-card__ribbon">
                                <i class="fas fa-fire"></i> -{{ $promo->discount_percent }}%
                            </div>
                        @endif

                        <div class="price-card__header">
                            <h4 class="price-card__name">{{ $package->name }}</h4>
                        </div>

                        <div class="price-card__body">
                            <div class="price-card__pricing">
                                @if($promo)
                                    <div class="price-card__strike">${{ number_format($regularMonthly, 0) }} MXN/mes</div>
                                @endif
                                <div class="price-card__amount">
                                    <span class="price-card__currency">$</span>
                                    <span class="price-card__value">{{ number_format($effectiveMonthly, 0) }}</span>
                                    <span class="price-card__period">{{ $package->price > 0 ? '/mes' : '' }}</span>
                                </div>
                                @if($package->price > 0)
                                    <div class="price-card__annual">
                                        Pago anual:
                                        @if($promo)
                                            <s class="text-muted-2">${{ number_format($regularAnnual, 0) }}</s>
                                        @endif
                                        <strong>${{ number_format($effectiveAnnual, 0) }} MXN</strong>
                                    </div>
                                    <div class="price-card__savings">
                                        <i class="fas fa-gift"></i> 2 meses gratis pagando anual
                                    </div>
                                @else
                                    <div class="price-card__annual text-muted-2">
                                        Para siempre — sin tarjeta de crédito
                                    </div>
                                @endif
                            </div>

                            @if($promo)
                                <div class="price-card__slots">
                                    <i class="fas fa-tags"></i>
                                    @php $left = $package->promo_slots_remaining; @endphp
                                    @if($left > 0)
                                        Quedan <strong>{{ $left }} de {{ $promo->to_count - $promo->from_count + 1 }}</strong> con esta promo
                                    @else
                                        Promoción agotada
                                    @endif
                                </div>
                            @endif

                            <ul class="price-card__features">
                                <li><i class="fas fa-check text-success me-1"></i> {{ number_format($package->max_listings) }} publicaciones</li>
                                @if (isset($package->characteristics))
                                    @foreach ($package->characteristics as $characteristic)
                                        <li><i class="fas fa-check text-success me-1"></i> {{ $characteristic }}</li>
                                    @endforeach
                                @endif
                            </ul>

                            @if($package->price == 0)
                                <a href="{{ route('register') }}" class="btn btn-outline-primary w-100">
                                    Registrate gratis
                                </a>
                            @else
                                @auth
                                    <button
                                        type="button"
                                        data-id="{{ $package->id }}"
                                        data-name="{{ $package->name }}"
                                        data-price="{{ $effectiveAnnual }}"
                                        data-monthly="{{ $effectiveMonthly }}"
                                        data-regular-annual="{{ $regularAnnual }}"
                                        data-discount="{{ $promo->discount_percent ?? 0 }}"
                                        class="btn btn-primary w-100 btn-pagar"
                                        data-bs-toggle="modal" data-bs-target="#myModal">
                                        Contratar plan
                                    </button>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-outline-primary w-100">
                                        Ingresar para contratar
                                    </a>
                                @endauth
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal de compra -->
<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Confirma tu compra</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Plan <strong class="name"></strong> — pago anual</p>
                <div class="d-flex justify-content-between align-items-baseline mb-2">
                    <span class="text-muted-2">Total a pagar:</span>
                    <span class="fs-3 fw-bold text-primary-dark">$<span class="price"></span> MXN</span>
                </div>
                <div class="modal-discount d-none alert alert-success py-2 mb-2 small">
                    <i class="fas fa-bolt me-1"></i> Aplicando <strong class="discount"></strong>% de descuento.
                </div>
                <small class="text-muted-2">Equivalente a $<strong class="monthly"></strong> MXN al mes · incluye 2 meses gratis.</small>
            </div>
            <div class="modal-footer flex-wrap">
                <button type="button" class="btn btn-outline-primary py-1 mt-0" data-bs-dismiss="modal">Cancelar</button>
                <div id="paypal-button-container" style="width: 200px;"></div>
                <div class="btn-checkout"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://sdk.mercadopago.com/js/v2"></script>
<script src="https://paypal.com/sdk/js?client-id={{ env('PAYPAL_CLIENT_ID') }}&components=buttons&currency=MXN"></script>

<script>
let price = 0;
let monthly = 0;
let name = '';
let id = 0;
let discount = 0;

const myToastEl = document.getElementById('myToast')
const myToast = new bootstrap.Toast(myToastEl)
const myModalEl = document.getElementById('myModal')
const myModal = new bootstrap.Modal(myModalEl)

paypal.Buttons({
    style: { layout: 'horizontal', color: 'gold', shape: 'rect' },
    createOrder: function (data, actions) {
        return actions.order.create({ purchase_units: [{ amount: { value: price } }] });
    },
    onApprove: (data, actions) => {
        const { orderID } = data;
        fetch(`{{ route('paypal.pay') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ orderID, package_id: id })
        })
        .then(r => r.json())
        .then(data => {
            myModal.hide();
            myToastEl.querySelector('.toast-body').innerText = data.message;
            myToast.show();
        });
    }
}).render('#paypal-button-container');

myModalEl.addEventListener('show.bs.modal', async event => {
    myModalEl.querySelector('.mercadopago-button')?.remove()
    const btn = event.relatedTarget
    id = btn.dataset.id
    price = btn.dataset.price
    monthly = btn.dataset.monthly
    name = btn.dataset.name
    discount = parseInt(btn.dataset.discount || '0', 10)

    myModalEl.querySelector('.price').innerText = Number(price).toLocaleString('es-MX')
    myModalEl.querySelector('.monthly').innerText = Number(monthly).toLocaleString('es-MX')
    myModalEl.querySelector('.name').innerText = name

    const discountEl = myModalEl.querySelector('.modal-discount')
    if (discount > 0) {
        myModalEl.querySelector('.discount').innerText = discount
        discountEl.classList.remove('d-none')
    } else {
        discountEl.classList.add('d-none')
    }

    const response = await fetch(`{{ route('getProduct') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ package_id: id })
    })
    const data = await response.json()

    const mp = new MercadoPago("{{ env('MERCADO_PAGO_PUBLIC_KEY') }}", { locale: 'es-MX' });
    mp.checkout({
        preference: { id: data.preference_id },
        render: { container: '.btn-checkout', label: 'Mercado Pago' }
    });
})

myModalEl.addEventListener('hide.bs.modal', () => {
    myModalEl.querySelector('.mercadopago-button')?.remove()
})
</script>
