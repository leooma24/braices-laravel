<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use App\Http\Requests\RegisterRequest;
use App\Models\Package;
use App\Models\UserPackage;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

use App\Helpers\SlugHelper;

class AuthController extends Controller
{

    public function __construct()
    {
        //$this->middleware('auth:api', ['except' => ['login','register', 'me']]);
    }

    public function loginForm() {
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return redirect('/login')->with('error', 'El usuario y/o contraseña son incorrectos.');
        }

        return redirect('/');

    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $data = $request->all();
        unset($data['photo']);
        $data['name'] = $data['first_name'] . ' ' . $data['last_name'];
        $data['slug'] = SlugHelper::createUniqueSlug($data['name'], User::class);
        $user->update($data);
        $user->photo = $request->photo;

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'message' => 'User updated successfully',
        ]);
    }

    public function changePassword(Request $request) {
        $user = Auth::user();
        if (!$user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $request->validate([
            'currentPassword' => 'required|string|min:6',
            'newPassword' => 'required|string|min:6',
            'repeatPassword' => 'required|string|min:6|same:newPassword',
        ]);

        if (!Hash::check($request->currentPassword, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Datos invalidos.',
                'errors' => [
                    'password' => ['La contraseña actual es incorrecta.'],
                ]
            ], 422);
        }

        $user->password = Hash::make($request->newPassword);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'La contraseña se ha actualizado',
        ]);
    }

    public function registerForm() {
        return view('register');
    }

    public function register(RegisterRequest $request){

        $user = User::create([
            'name' => $request->first_name . ' ' . $request->last_name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'photo' => 'leooma.jpg',
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'slug' => SlugHelper::createUniqueSlug($request->first_name . ' ' . $request->last_name, User::class)
        ]);

        Auth::login($user);

        $user->assignRole('user');

        $defaultPackage = Package::find(1);

        UserPackage::create([
            'user_id' => $user->id,
            'package_id' => $defaultPackage->id,
            'remaining_listings' => $defaultPackage->max_listings,
            'expires_at' => now()->addDays($defaultPackage->duration),
        ]);

        // Enviar el correo de verificación con el template personalizado
        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice')->with('success', 'Te hemos enviado un correo de verificación. Por favor, revisa tu bandeja de entrada.');
    }

    protected function verificationUrl($user)
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );
    }

    public function logout()
    {
        Auth::logout();
        return redirect(route('main'));
    }

    public function me() {
        $user = Auth::user();
        $user->photo = $user->photo ? url('/photo-user/' . $user->photo) : null;
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'message' => 'Successfully me',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }


}
