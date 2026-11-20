@props(['user'])

{{--
    Nadie podia saber su slug: no se mostraba en ninguna pantalla, asi que al
    copiar el codigo del iframe la gente pegaba el marcador "TU-SLUG" tal cual
    y le salia un 404. Aqui se arma solo, con su slug ya puesto.
--}}
@php
    $perfilUrl = route('my.properties', ['slug' => $user->slug]);
    $widgetUrl = route('user.properties', ['user' => $user->slug]);
@endphp

<div class="embed-code" data-widget-url="{{ $widgetUrl }}">
    <div class="mb-3">
        <label class="form-label fw-semibold mb-1">Tu dirección en BienesCorp</label>
        <div class="input-group">
            <input type="text" class="form-control" readonly value="{{ $perfilUrl }}"
                   data-embed-field aria-label="Dirección de tu página en BienesCorp">
            <button class="btn btn-outline-secondary" type="button" data-embed-copy>
                <i class="fas fa-copy me-1" aria-hidden="true"></i>Copiar
            </button>
        </div>
        <small class="text-muted">Compártela tal cual: muestra todas tus propiedades.</small>
    </div>

    <hr>

    <label class="form-label fw-semibold mb-1">Código para tu propia página web</label>
    <p class="text-muted small mb-2">
        Pégalo en tu sitio y tus propiedades aparecerán ahí solas, siempre al día.
        Si no tienes página web, no necesitas esto: usa la dirección de arriba.
    </p>

    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
        <label for="embed-cols-{{ $user->id }}" class="form-label mb-0 small">Columnas</label>
        <select id="embed-cols-{{ $user->id }}" class="form-select form-select-sm w-auto" data-embed-cols>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3" selected>3</option>
            <option value="4">4</option>
        </select>

        <label for="embed-limit-{{ $user->id }}" class="form-label mb-0 small ms-2">Cuántas mostrar</label>
        <select id="embed-limit-{{ $user->id }}" class="form-select form-select-sm w-auto" data-embed-limit>
            <option value="0" selected>Todas</option>
            <option value="3">3</option>
            <option value="6">6</option>
            <option value="9">9</option>
            <option value="12">12</option>
        </select>

        <a href="{{ $widgetUrl }}" target="_blank" rel="noopener"
           class="btn btn-link btn-sm ms-auto p-0" data-embed-preview>
            Ver cómo se verá <i class="fas fa-arrow-up-right-from-square ms-1" aria-hidden="true"></i>
        </a>
    </div>

    <textarea class="form-control font-monospace" rows="8" readonly
              data-embed-field data-embed-snippet
              aria-label="Código para incrustar tus propiedades"></textarea>

    <button class="btn btn-primary btn-sm mt-2" type="button" data-embed-copy data-embed-target="snippet">
        <i class="fas fa-copy me-1" aria-hidden="true"></i>Copiar código
    </button>
    <span class="small text-success ms-2 d-none" data-embed-done>¡Copiado!</span>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                function armarSnippet(widgetUrl, cols, limit) {
                    var src = widgetUrl + '?cols=' + cols + (limit > 0 ? '&limit=' + limit : '');
                    var origen = widgetUrl.split('/').slice(0, 3).join('/');

                    return '<!-- Propiedades BienesCorp -->\n' +
                        '<iframe id="bc-propiedades"\n' +
                        '        src="' + src + '"\n' +
                        '        title="Mis propiedades en BienesCorp"\n' +
                        '        width="100%" height="700"\n' +
                        '        style="border:0; display:block; width:100%;"\n' +
                        '        loading="lazy"\n' +
                        '        referrerpolicy="strict-origin-when-cross-origin"></iframe>\n\n' +
                        '<script>\n' +
                        '  window.addEventListener("message", function (e) {\n' +
                        '    if (e.origin !== "' + origen + '") return;\n' +
                        '    if (!e.data || e.data.bcWidget !== "bienescorp-propiedades") return;\n' +
                        '    var f = document.getElementById("bc-propiedades");\n' +
                        '    if (f) f.style.height = e.data.height + "px";\n' +
                        '  });\n' +
                        '<\/script>';
                }

                function copiar(texto, aviso) {
                    function exito() {
                        if (!aviso) { return; }
                        aviso.classList.remove('d-none');
                        setTimeout(function () { aviso.classList.add('d-none'); }, 2000);
                    }

                    // navigator.clipboard solo existe en HTTPS; el respaldo
                    // cubre http y navegadores viejos.
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(texto).then(exito, function () { respaldo(texto, exito); });
                    } else {
                        respaldo(texto, exito);
                    }
                }

                function respaldo(texto, exito) {
                    var temp = document.createElement('textarea');
                    temp.value = texto;
                    temp.setAttribute('readonly', '');
                    temp.style.position = 'fixed';
                    temp.style.opacity = '0';
                    document.body.appendChild(temp);
                    temp.select();
                    try { document.execCommand('copy'); exito(); } catch (e) { /* sin portapapeles */ }
                    document.body.removeChild(temp);
                }

                function iniciar(caja) {
                    var widgetUrl = caja.getAttribute('data-widget-url');
                    var snippet = caja.querySelector('[data-embed-snippet]');
                    var cols = caja.querySelector('[data-embed-cols]');
                    var limit = caja.querySelector('[data-embed-limit]');
                    var preview = caja.querySelector('[data-embed-preview]');
                    var aviso = caja.querySelector('[data-embed-done]');

                    function refrescar() {
                        var c = cols ? cols.value : 3;
                        var l = limit ? parseInt(limit.value, 10) : 0;
                        if (snippet) { snippet.value = armarSnippet(widgetUrl, c, l); }
                        if (preview) {
                            preview.href = widgetUrl + '?cols=' + c + (l > 0 ? '&limit=' + l : '');
                        }
                    }

                    if (cols) { cols.addEventListener('change', refrescar); }
                    if (limit) { limit.addEventListener('change', refrescar); }
                    refrescar();

                    caja.querySelectorAll('[data-embed-copy]').forEach(function (boton) {
                        boton.addEventListener('click', function () {
                            var campo = boton.getAttribute('data-embed-target') === 'snippet'
                                ? snippet
                                : boton.closest('.input-group').querySelector('[data-embed-field]');
                            if (campo) { copiar(campo.value, aviso); }
                        });
                    });
                }

                document.addEventListener('DOMContentLoaded', function () {
                    document.querySelectorAll('.embed-code').forEach(iniciar);
                });
            })();
        </script>
    @endpush
@endonce
