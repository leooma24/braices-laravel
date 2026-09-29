<?php

/**
 * Datos del responsable para el aviso de privacidad y los términos.
 *
 * La LFPDPPP exige identificar al responsable del tratamiento con su
 * denominación y domicilio. Llena estos valores (o las variables de
 * entorno equivalentes) antes de dar por publicado el aviso.
 */
return [
    'razon_social' => env('LEGAL_RAZON_SOCIAL', 'BienesCorp'),
    'nombre_comercial' => 'BienesCorp',
    'domicilio' => env('LEGAL_DOMICILIO', 'Los Mochis, Sinaloa, México'),
    'rfc' => env('LEGAL_RFC', ''),
    'email' => env('LEGAL_EMAIL', 'info@bienescorp.com'),
    'telefono' => env('LEGAL_TELEFONO', '+52 668 818 02 02'),
    'whatsapp' => env('LEGAL_WHATSAPP', '526688180202'),

    // Fecha de la última actualización que se muestra al pie de cada documento.
    'vigencia' => env('LEGAL_VIGENCIA', '28 de septiembre de 2026'),
];
