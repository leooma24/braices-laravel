<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Requests\PropertyRequestFactory;
use App\Models\PropertyTypeModel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class ValidatePropertyRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $propertyType = $request->input('property_type_id');
        if (!$propertyType) {
            return redirect()->back()->withErrors(['transaction_type_id' => 'El tipo de propiedad es requerido'])->withInput();
        }

        $type = PropertyTypeModel::find($propertyType[0]);
        if (!$type) {
            return redirect()->back()->with('error', 'Tipo de propiedad no encontrado')->withInput();
        }

        $propertyRequest = PropertyRequestFactory::make($type->name);

        $request->merge([
            'price' => str_replace(',', '', $request->input('price')),
        ]);

        $validator = Validator::make(
            $request->all(),
            $propertyRequest->rules(),
            $propertyRequest->messages(),
            $propertyRequest->attributes()
        );

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        return $next($request);
    }
}
