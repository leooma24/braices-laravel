<div class="prices">
    <div class="container">
        <div class="row">
            <div class="col">
                <h2 class="display-6 fw-bold mc-title my-5">Nuestros Planes</h2>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 mb-3">
            @foreach($packages as $index => $package)
                <div class="col">
                    <div class="card mb-4 rounded-3 shadow-sm">
                        <div class="card-header text-center py-3 {{ $packages->count() == ($index+1) ? 'text-bg-primary' : '' }}">
                            <h4 class="my-0 fw-normal">{{ $package->name }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="text-center">
                                <span class="mc-fs-1 d-block mb-0 text-primary-dark">${{number_format($package->price/12)}}</span>
                                <small class="text-muted">MXN Mensual</small>
                            </div>

                            <div class="text-success">
                                <hr>
                            </div>

                            <ul class="list mt-3 mb-4">
                                <li>{{ number_format($package->max_listings)}} Propiedades</li>
                                @if (isset($package->characteristics))
                                    @foreach ($package->characteristics as $characteristic)
                                        <li>{{ $characteristic }}</li>
                                    @endforeach
                                @endif
                            </ul>
                            @if($package->price == 0)
                                <a href="{{ route('register')}}" class="w-100 btn btn-lg btn-outline-primary">Registrate Gratis</a>
                            @else
                                @auth
                                    <button
                                        type="button"
                                        data-id="{{ $package->id }}"
                                        data-name="{{ $package->name }}"
                                        data-price="{{ $package->price}}"
                                        data-unit-price="{{number_format($package->price/12)}}"
                                        class="w-100 btn btn-lg btn-primary btn-pagar"
                                        data-bs-toggle="modal" data-bs-target="#myModal">
                                        Comprar
                                    </button>
                                @else
                                <a href="{{ route('login')}}" class="w-100 btn btn-lg btn-outline-primary">Ingresa</a>
                                @endauth
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Confirmación</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <p>¿Estás seguro de querer comprar el paquete <strong class="name"></strong> ?<br />
                El costo es de $<strong class="price"></strong> MXN Anuales</p>
                (12 meses de $<strong class="unit_price"></strong> MXN)
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary py-1 mt-0" data-bs-dismiss="modal">Cancelar</button>
          <div id="paypal-button-container" style="width: 200px;"></div>
          <div class="btn-checkout"></div>
        </div>
      </div>
    </div>
  </div>


<script src="https://sdk.mercadopago.com/js/v2"></script>
<script src="https://paypal.com/sdk/js?client-id={{ env('PAYPAL_CLIENT_ID')}}&components=buttons&currency=MXN"></script>

<script>

let price = 0
let name = ''
let id = 0
let unit_price = 0

const myToastEl = document.getElementById('myToast')
const myToast = new bootstrap.Toast(myToastEl)
const myModalEl = document.getElementById('myModal')
const myModal = new bootstrap.Modal(myModalEl)

paypal.Buttons({
  style: {
    layout: 'horizontal',
    color:  'gold',
    shape:  'rect',
  },
  createOrder: function(data, actions) {
    return actions.order.create({
      purchase_units: [{
        amount: {
          value: price
        }
      }]
    });
  },
  onApprove: (data, actions) => {
    const { orderID } = data

    fetch(`{{ route('paypal.pay')}}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                orderID: orderID,
                package_id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            myModal.hide();
            myToastEl.querySelector('.toast-body').innerText = data.message
            myToast.show()
        })

  }

}).render('#paypal-button-container');



    myModalEl.addEventListener('show.bs.modal', async event => {
        myModalEl.querySelector('.mercadopago-button')?.remove()
        const package_id = id = event.relatedTarget.attributes['data-id'].value
        price = event.relatedTarget.attributes['data-price'].value
        name = event.relatedTarget.attributes['data-name'].value
        unit_price = event.relatedTarget.attributes['data-unit-price'].value

        myModalEl.querySelector('.price').innerText = price
        myModalEl.querySelector('.name').innerText = name
        myModalEl.querySelector('.unit_price').innerText = unit_price

        const response = await fetch(`{{ route('getProduct')}}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                package_id: package_id
            })
        })
        const data = await response.json()

        const mp = new MercadoPago("{{ env('MERCADO_PAGO_PUBLIC_KEY') }}", {
            locale: 'es-MX'
        });

        mp.checkout({
            preference: {
                id: data.preference_id
            },
            render: {
                container: '.btn-checkout',
                label: 'Mercado Pago',
            }
        });
    })

    myModalEl.addEventListener('hide.bs.modal', async event => {
        myModalEl.querySelector('.mercadopago-button').remove()
    })

</script>
