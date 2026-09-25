<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use App\Mail\ContactMeMail;
use App\Models\Lead;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FormController extends Controller
{
    //
    public function contact(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required',
            'g-recaptcha-response' => 'required|captcha',
        ]);

        $data = $request->only(['name', 'email', 'phone', 'message']);

        // Se guarda antes de enviar el correo: si el correo falla o cae en spam,
        // el prospecto no se pierde y aparece en /cuenta/prospectos.
        Lead::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'],
            'source' => 'contacto',
        ]);

        $this->sendOrLog(fn () => Mail::to('info@bienescorp.com')->send(new ContactMail($data)));

        return redirect()->route('contact')->with('success', 'Tu mensaje ha sido enviado correctamente');
    }

    public function contactMe(Request $request)
    {
        $property = Property::find($request->property_id);
        $to = $property->user->email;

        $request->validate([
            'name' => 'required',
            'phone_number' => 'required',
            'email' => 'required|email',
            'message' => 'required',
            'g-recaptcha-response' => 'required|captcha',
        ]);

        $data = $request->only(['name', 'email', 'phone_number', 'message']);
        $data['property'] = $property;

        Lead::create([
            'property_id' => $property->id,
            'user_id' => $property->user_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone_number'],
            'message' => $data['message'],
            'source' => 'propiedad',
        ]);

        $this->sendOrLog(fn () => Mail::to($to)->send(new ContactMeMail($data)));

        return redirect()->route('property', ['slug' => $property->slug])->with('success', 'Tu mensaje ha sido enviado correctamente');
    }

    /**
     * El correo es un extra: si falla, el prospecto ya quedó guardado.
     */
    private function sendOrLog(callable $send): void
    {
        try {
            $send();
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar el correo del formulario: '.$e->getMessage());
        }
    }
}
