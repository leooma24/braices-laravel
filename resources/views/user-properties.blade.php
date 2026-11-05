{{--
    Widget embebible: documento HTML completo y suelto, SIN extender el layout.
    Se sirve en /user-properties/{id|slug} para incrustarlo con <iframe> en otro
    sitio. Parametros opcionales: ?cols=1..4 y ?limit=N.
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, follow">
    <title>Propiedades de {{ $owner->name }} | BienesCorp</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bc-cols: {{ $cols }};
            --bc-primary: #0d6efd;
            --bc-text: #212529;
            --bc-muted: #6c757d;
            --bc-border: #e1e1e1;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            background: transparent;
            font-family: "Montserrat", system-ui, -apple-system, sans-serif;
            color: var(--bc-text);
        }
        .bc-wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0.5rem 1rem 1.25rem;
        }
        .bc-grid {
            display: grid;
            grid-template-columns: repeat(var(--bc-cols), minmax(0, 1fr));
            gap: 1rem;
        }
        @media (max-width: 992px) { .bc-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 576px) { .bc-grid { grid-template-columns: 1fr; } }

        .bc-card {
            display: flex;
            flex-direction: column;
            border: 1px solid var(--bc-border);
            border-radius: 0.5rem;
            overflow: hidden;
            background: #fff;
            text-decoration: none;
            color: inherit;
            transition: box-shadow .15s ease, transform .15s ease;
        }
        .bc-card:hover {
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .12);
            transform: translateY(-2px);
        }
        .bc-card.is-featured { border-color: #ff7f6b; }

        .bc-media { position: relative; }
        .bc-media img {
            display: block;
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .bc-badge {
            position: absolute;
            top: .5rem;
            left: .5rem;
            background: #ff7f6b;
            color: #fff;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .02em;
            padding: .2rem .5rem;
            border-radius: .25rem;
        }

        .bc-body {
            padding: .875rem 1rem 1rem;
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
        }
        .bc-title {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0 0 .25rem;
            color: var(--bc-primary);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .bc-address {
            font-size: .85rem;
            color: var(--bc-muted);
            margin: 0 0 .75rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .bc-specs {
            display: flex;
            flex-wrap: wrap;
            gap: .25rem .9rem;
            font-size: .8rem;
            color: var(--bc-muted);
            margin-top: auto;
        }
        .bc-sep { border: 0; border-top: 1px solid var(--bc-border); margin: .75rem 0 .5rem; }
        .bc-price { font-size: 1.25rem; font-weight: 700; color: var(--bc-primary); margin: 0; }
        .bc-price span { font-size: .8rem; font-weight: 600; color: var(--bc-muted); }

        .bc-empty {
            text-align: center;
            color: var(--bc-muted);
            padding: 2rem 1rem;
            font-size: .95rem;
        }
        .bc-foot {
            text-align: right;
            margin-top: 1rem;
            font-size: .8rem;
        }
        .bc-foot a { color: var(--bc-muted); text-decoration: none; }
        .bc-foot a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="bc-wrap">
    @if($properties->isEmpty())
        <p class="bc-empty">Por ahora no hay propiedades publicadas.</p>
    @else
        <div class="bc-grid">
            @foreach($properties as $property)
                <a class="bc-card {{ $property->isFeaturedNow() ? 'is-featured' : '' }}"
                   href="{{ route('property', $property->slug) }}"
                   target="_blank" rel="noopener">
                    <div class="bc-media">
                        <img src="{{ $property->coverUrl() }}"
                             alt="{{ $property->title }}"
                             loading="lazy" width="400" height="200">
                        @if($property->isFeaturedNow())
                            <span class="bc-badge">Destacada</span>
                        @endif
                    </div>
                    <div class="bc-body">
                        <h3 class="bc-title">{{ $property->title }}</h3>
                        <p class="bc-address">{{ $property->address }}</p>

                        <div class="bc-specs">
                            @if($property->isLand())
                                @if($property->square_feet)
                                    <span>{{ number_format($property->square_feet) }} m&sup2; de terreno</span>
                                @endif
                                @if($property->front && $property->depth)
                                    <span>{{ $property->front }} &times; {{ $property->depth }} m</span>
                                @endif
                            @else
                                @if($property->bedrooms)
                                    <span>{{ $property->bedrooms }} {{ $property->bedrooms == 1 ? 'recámara' : 'recámaras' }}</span>
                                @endif
                                @if($property->bathrooms)
                                    <span>{{ $property->bathrooms }} {{ $property->bathrooms == 1 ? 'baño' : 'baños' }}</span>
                                @endif
                                @if($property->square_feet)
                                    <span>{{ number_format($property->square_feet) }} m&sup2;</span>
                                @endif
                            @endif
                        </div>

                        <hr class="bc-sep">
                        <p class="bc-price">
                            ${{ number_format($property->price) }}
                            @if($property->priceSuffix())
                                <span>{{ $property->priceSuffix() }}</span>
                            @endif
                        </p>
                    </div>
                </a>
            @endforeach
        </div>

        <p class="bc-foot">
            <a href="{{ route('properties') }}" target="_blank" rel="noopener">Ver más en BienesCorp &rarr;</a>
        </p>
    @endif
</div>

<script>
    // El iframe no puede crecer solo: le avisa su alto a la pagina que lo
    // incrusta. Si el sitio de destino no escucha, el mensaje se ignora.
    (function () {
        var ultimo = 0;

        function enviarAlto() {
            var alto = Math.ceil(document.documentElement.scrollHeight);
            if (alto === ultimo) {
                return;
            }
            ultimo = alto;
            try {
                parent.postMessage({ bcWidget: 'bienescorp-propiedades', height: alto }, '*');
            } catch (e) {
                // sin padre accesible
            }
        }

        document.addEventListener('DOMContentLoaded', enviarAlto);
        window.addEventListener('load', enviarAlto);
        window.addEventListener('resize', enviarAlto);

        if (window.ResizeObserver) {
            new ResizeObserver(enviarAlto).observe(document.body);
        } else {
            setInterval(enviarAlto, 1000);
        }
    })();
</script>
</body>
</html>
