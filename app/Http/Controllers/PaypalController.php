<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Package;
use App\Models\Payment;
use App\Models\User;

class PaypalController extends Controller
{
    public function pay(Request $request)
    {
        $package = Package::findOrFail($request->package_id);
        $user = User::findOrFail(auth()->id());

        // Servidor calcula el monto efectivo (incluyendo promo) — nunca confíes
        // en el precio del cliente.
        $amount = $package->effective_annual;
        $promo = $package->activePromotion();

        $payment = Payment::create([
            'external_reference' => $request->orderID,
            'payment_id' => $request->orderID,
            'user_id' => $user->id,
            'package_id' => $package->id,
            'amount' => $amount,
            'provider' => 'paypal',
            'status' => 'Approved',
        ]);

        $user->userPackages()->create([
            'package_id' => $package->id,
            'remaining_listings' => $package->max_listings,
            'expires_at' => now()->addDays($package->duration ?? 365),
        ]);

        return response()->json([
            'message' => $promo
                ? "Pago aprobado con {$promo->discount_percent}% de descuento aplicado."
                : 'Pago aprobado.',
            'payment' => $payment,
        ]);
    }
}
