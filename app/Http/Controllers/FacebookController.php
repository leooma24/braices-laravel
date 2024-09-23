<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use App\Models\User;

use Laravel\Socialite\Facades\Socialite;

class FacebookController extends Controller
{
    //
    public function login() {
        return Socialite::driver('facebook')->redirect();
    }

    public function callback() {
        $user = Socialite::driver('facebook')->user();
        $authUser = User::where('email', $user->email)->first();
        if($authUser) {
            if(!$authUser->facebook_id) {
                $authUser->facebook_id = $user->id;
                $authUser->save();
            }
        } else {
            $authUser = User::updateOrCreate([
                'facebook_id' => $user->id,
            ], [
                'name' => $user->name,
                'email' => $user->email,
                'facebook_id' => $user->id,
                'facebook_token' => $user->token,
            ]);
        }

        Auth::login($authUser, true);

        return redirect()->route('main');

    }




}
