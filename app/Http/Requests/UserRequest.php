<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|min:8|same:password',
            'role' => 'required|string',

        ];
    }

    // Personaliza los nombres de los atributos
    public function attributes()
    {
        return [
            'name' => 'Nombre de usuario',
            'first_name' => 'Nombre(s)',
            'last_name' => 'Apellido(s)',
            'email' => 'Correo electrónico',
            'phone_number' => 'Número de teléfono',
            'password' => 'Contraseña',
            'password_confirmation' => 'Confirmación de contraseña',
            'role' => 'Rol',
        ];
    }
}
