<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * TLDs y dominios temporales asociados típicamente a registros bot/abuso.
     * No es exhaustivo — sólo señales fuertes.
     */
    private const BLOCKED_TLDS = [
        '.ru', '.cn', '.xyz', '.top', '.tk', '.ml', '.ga', '.cf', '.gq',
        '.click', '.work', '.icu', '.buzz', '.live', '.fit',
    ];

    private const DISPOSABLE_DOMAINS = [
        'mailinator.com', 'tempmail.com', 'guerrillamail.com', '10minutemail.com',
        'yopmail.com', 'sharklasers.com', 'throwawaymail.com', 'trashmail.com',
        'maildrop.cc', 'getnada.com', 'temp-mail.org', 'fakeinbox.com',
    ];

    public function rules(): array
    {
        return [
            // Honeypot — campo oculto que los bots rellenan. Debe venir vacío.
            'website' => 'nullable|prohibited',

            // Timestamp del cliente para detectar fill instantáneo (bots).
            // Lo validamos en withValidator().
            'form_loaded_at' => 'nullable|integer',

            'first_name' => [
                'required',
                'string',
                'min:2',
                'max:60',
                // Sólo letras latinas, acentos, espacios, apóstrofo y guiones.
                'regex:/^[\pL\s\'\-\.]+$/u',
            ],
            'last_name' => [
                'required',
                'string',
                'min:2',
                'max:60',
                'regex:/^[\pL\s\'\-\.]+$/u',
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                'unique:users',
            ],
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $email = strtolower((string) $this->input('email', ''));
            $name = (string) $this->input('first_name', '') . ' ' . (string) $this->input('last_name', '');

            // 1. Bloquea TLDs sospechosos
            foreach (self::BLOCKED_TLDS as $tld) {
                if (str_ends_with($email, $tld)) {
                    $v->errors()->add('email', 'Este dominio de correo no está permitido. Usa una dirección válida.');
                    return;
                }
            }

            // 2. Bloquea dominios desechables conocidos
            foreach (self::DISPOSABLE_DOMAINS as $domain) {
                if (str_ends_with($email, '@' . $domain)) {
                    $v->errors()->add('email', 'No aceptamos correos temporales. Usa una dirección real.');
                    return;
                }
            }

            // 3. Caracteres no-latinos en el nombre (cirílico, CJK, árabe, etc.)
            //    Operamos sólo en México/Latam — esto es señal de bot.
            if (preg_match('/[\x{0400}-\x{04FF}\x{4e00}-\x{9fff}\x{0600}-\x{06FF}\x{0E00}-\x{0E7F}]/u', $name)) {
                $v->errors()->add('first_name', 'El nombre contiene caracteres no permitidos.');
                return;
            }

            // 4. Email con prefijo aleatorio largo (típico de bots: ah87s2kdfa@gmail.com)
            if (preg_match('/^[a-z0-9]{16,}@/', $email)) {
                $v->errors()->add('email', 'El correo parece generado automáticamente.');
                return;
            }

            // 5. Tiempo mínimo de fill — bots envían el form en <2s.
            $loadedAt = (int) $this->input('form_loaded_at', 0);
            if ($loadedAt > 0) {
                $elapsedMs = (now()->valueOf() - $loadedAt);
                if ($elapsedMs < 2000) {
                    $v->errors()->add('email', 'Formulario enviado demasiado rápido. Inténtalo de nuevo.');
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'first_name' => 'nombre',
            'last_name' => 'apellido',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'password_confirmation' => 'confirmación de contraseña',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.regex' => 'El nombre sólo puede contener letras y espacios.',
            'last_name.regex' => 'El apellido sólo puede contener letras y espacios.',
            'website.prohibited' => 'Detección de bot.',
            'email.email' => 'El correo no es válido.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }
}
