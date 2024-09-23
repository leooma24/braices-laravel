<?php

namespace App\Http\Requests;

use App\Http\Requests\UserRequest;

class UserUpdateRequest extends UserRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = Parent::rules();
        $rules['password'] = 'nullable|string|min:8';
        $rules['password_confirmation'] = 'nullable|string|min:8|same:password';
        return $rules;
    }


}
