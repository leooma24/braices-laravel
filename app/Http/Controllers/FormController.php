<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\ContactMail;
use App\Mail\ContactMeMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Property;
use NoCaptcha\Facades\NoCaptcha;

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

        Mail::to('info@bienescorp.com')->send(new ContactMail($data));

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

        Mail::to($to)->send(new ContactMeMail($data));

        return redirect()->route('property', ['slug' => $property->slug])->with('success', 'Tu mensaje ha sido enviado correctamente');
    }
}
