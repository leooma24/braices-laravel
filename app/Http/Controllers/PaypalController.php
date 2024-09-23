<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Package;
use App\Models\Payment;
use App\Models\User;

class PaypalController extends Controller
{
    //
    public function pay(Request $request) {

        $package = Package::find($request->package_id);
        $user =  User::find(auth()->id());

        $payment = Payment::create([
            'external_reference' => $request->orderID,
            'payment_id' => $request->orderID,
            'user_id' => $user->id,
            'package_id' => $package->id,
            'status' => 'Approved'
        ]);

        $user->userPackages()->create([
            'package_id' => $package->id,
            'remaining_listings' => $package->max_listings,
            'expires_at' => now()->addDays($package->days)
        ]);

        return response()->json([
            'message' => 'Pago Aprobado',
            'payment' => $payment
        ]);
    }
}
