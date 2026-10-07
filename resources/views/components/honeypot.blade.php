{{--
    Trampa para bots. El reCAPTCHA v2 de casilla sigue dejando pasar spam
    (las granjas de captchas lo resuelven por centavos), asi que se agrega una
    segunda barrera que no le cuesta nada al visitante.

    Dos señales:
      - Un campo que una persona nunca ve y por lo tanto nunca llena.
      - Cuanto tardo en enviarse el formulario: los bots lo mandan al instante.

    El campo se esconde sacandolo de la pantalla y no con display:none, porque
    varios bots omiten a proposito lo que esta oculto de esa forma.
--}}
<div class="hp-wrap" aria-hidden="true">
    <label>Deja este campo vacío
        <input type="text" name="direccion_alterna" tabindex="-1" autocomplete="off" value="">
    </label>
    <input type="hidden" name="hp_ts" value="{{ encrypt(time()) }}">
</div>
