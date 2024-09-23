<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Payment;
use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use MercadoPago\Item;
use MercadoPago\Preference;
use MercadoPago\Payer;


class MercadoPagoController extends Controller
{
    public function webhook(Request $request)
    {
        $data = $request->all();
        $payment = Payment::where('external_reference', $data['preference_id'])
            ->where('status', 'Pending')->first();
        if(!$payment) {
            return redirect()->route('main')->with('error', 'Pago no encontrado');
        }
        if($data['status'] == 'approved') {
            $payment->payment_id = $data['payment_id'];
            $payment->status = 'Approved';
            $payment->save();

            $package = Package::find($payment->package_id);
            $user = User::find(Auth::id());

            $user->userPackages()->create([
                'package_id' => $package->id,
                'remaining_listings' => $package->max_listings,
                'expires_at' => now()->addDays($package->days)
            ]);
            return redirect()->route('main')->with('success', 'Pago procesado correctamente');
        } else {
            $payment->status = 'Rejected';
            $payment->save();
            return redirect()->route('main')->with('error', 'Pago rechazado');
        }



    }

    public function getProduct(Request $request)
    {
        $user = auth()->user();
        $package = Package::find($request->package_id);
        $payment = Payment::where('user_id', $user->id)
            ->where('package_id', $package->id)
            ->where('status', 'Pending')
            ->first();

        if(!$payment) {
            // Crear una preferencia de pago
            $preference = new Preference();

            // Crear un ítem en la preferencia
            $item = new Item();
            $item->title = $package->name;
            $item->quantity = 1;
            $item->unit_price = $package->price; // Precio en tu moneda local
            $preference->items = [$item];

            $preference->back_urls = [
                "success" => route('webhook'),
                "failure" => route('webhook'),
                "pending" => route('webhook')
            ];

            // Configurar el payer
            $payer = new Payer();
            //$payer->email = 'bancos@medioscorp.com'; // Email del cliente
            $payer->email = auth()->user()->email; // Email del cliente
            $preference->payer = $payer;

            // Guardar la preferencia y obtener la URL de pago
            $preference->save();

            Payment::create([
                'external_reference' => $preference->id,
                'payment_id' => '',
                'user_id' => $user->id,
                'package_id' => $package->id,
                'status' => 'Pending'
            ]);
            $package->preference_id = $preference->id;
        } else {
            $package->preference_id = $payment->external_reference;
        }

        return response()->json($package);

    }
}
