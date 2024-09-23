<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'transaction_type_id' => 'required|integer',
            'address' => 'required|string|max:255',
            'price' => 'required|numeric',
            'bedrooms' => 'required|integer',
            'bathrooms' => 'required|integer',
            'square_feet' => 'required|integer',
            'lot_size' => 'required|string|max:255',
            'description' => 'required|string',
            'photo_main' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'zip' => 'required|string|max:255',
            'youtube' => 'nullable|string|max:255',
            'property_status_id' => 'required|integer',
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
        ];
    }

    // Personaliza los nombres de los atributos
    public function attributes()
    {
        return [
            'property_type_id' => 'Tipo de propiedad',
            'transaction_type_id' => 'Tipo de transacción',
            'title' => 'Título',
            'address' => 'Dirección',
            'price' => 'Precio',
            'bedrooms' => 'Habitaciones',
            'bathrooms' => 'Baños',
            'square_feet' => 'Metros cuadrados',
            'lot_size' => 'Tamaño del lote',
            'description' => 'Descripción',
            'photo_main' => 'Foto principal',
            'images.*' => 'Imágenes',
            'country' => 'País',
            'state' => 'Estado',
            'city' => 'Ciudad',
            'zip' => 'Código postal',
            'youtube' => 'Video de Youtube',
            'property_status_id' => 'Estado de la propiedad',
        ];
    }
}
