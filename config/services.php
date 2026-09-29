<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT'),
    ],
    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    // Las llaves de las pasarelas se leen SIEMPRE por config(), nunca con
    // env() desde una vista: el deploy corre config:cache y ahi env()
    // devuelve null, que fue justo lo que dejo el SDK sin client-id.
    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret' => env('PAYPAL_SECRET'),
        'mode' => env('PAYPAL_MODE', 'live'),
    ],

    'mercadopago' => [
        'public_key' => env('MERCADO_PAGO_PUBLIC_KEY'),
        'access_token' => env('MERCADO_PAGO_ACCESS_TOKEN'),
    ],

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'descriptions_enabled' => filter_var(env('AI_DESCRIPTIONS_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    ],

    'deploy' => [
        'token' => env('DEPLOY_TOKEN', ''),
    ],

    'recaptcha' => [
        'sitekey' => env('NOCAPTCHA_SITEKEY'),
        'secret' => env('NOCAPTCHA_SECRET'),
    ],

];
