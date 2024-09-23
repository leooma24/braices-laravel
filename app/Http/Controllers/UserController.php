<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ChangePasswordRequest;
use App\Helpers\SlugHelper;
use App\Models\User;

class UserController extends Controller
{
    public function getProfile() {
        return view('profile');
    }

    public function updateProfile(Request $request) {

        $user = Auth::user();
        $data = $request->all();
        $user->fill($data);
        $user->slug = SlugHelper::createUniqueSlug($user->name, User::class);
        $user->save();

        return redirect(route('profile'))->with('success', 'Perfil actualizado');
    }

    public function updatePassword(ChangePasswordRequest $request) {

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect(route('profile'))->with('error', 'Contraseña actual incorrecta');
        }
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect(route('profile'))->with('success', 'Contraseña actualizada');
    }

    public function updatePhoto(Request $request) {
        $user = Auth::user();
        if(!$user->id) {
            return redirect(route('login'))->with('error', 'Usuario no encontrado');
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $photo = $request->file('photo');
        if($photo) {
            $photoName = time() . '.' . $photo->extension();
            $photo->move(public_path('photo-user'), $photoName);

            $user->photo = $photoName;
            $user->save();
        }

        return redirect(route('profile'))->with('success', 'Foto de perfil actualizada');
    }


}
