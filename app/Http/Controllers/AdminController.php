<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Banner;
use App\Models\Package;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::paginate();
        return view('admin', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();

        return view('admin.create', compact('roles'));
    }

    public function new(UserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'password' => Hash::make($request->password)
        ]);

        $user->assignRole($request->role);

        return redirect()->route('admin');
    }

    public function save(UserUpdateRequest $request, $id) {
        $user = User::find($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone_number = $request->phone_number;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;

        if($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $user->syncRoles([$request->role]);

        return redirect()->route('admin')->with('success', 'Usuario actualizado correctamente');
    }

    public function edit(Request $request, $id)
    {
        $user = User::find($id);
        $roles = Role::all();

        return view('admin.create', compact('user', 'roles'));
    }

    public function assign(User $user) {
        $packages = Package::all();
        return view('admin.assign', compact('user', 'packages'));
    }

    public function assignAdd(Request $request, User $user) {
        $package = Package::find($request->package_id);
        $user->userPackages()->create([
            'package_id' => $package->id,
            'remaining_listings' => $package->max_listings,
            'expires_at' => now()->addDays($package->days)
        ]);

        return redirect()->route('admin')->with('success', 'Paquetes asignados correctamente');
    }

    public function destroy(Request $request, $id) {
        $user = User::find($id);
        $user->delete();

        return redirect()->route('admin')->with('success', 'Usuario eliminado correctamente');
    }

    public function banners()
    {
        $banners = Banner::paginate();
        return view('admin.banners', compact('banners'));
    }

    public function createBanner()
    {
        $banner = null;
        return view('admin.banner.create', compact('banner'));
    }

    public function newBanner(Request $request) {
        $request->validate([
            'image_path' => 'required|image'
        ]);

        $data = $request->all();
        $photo = $request->file('image_path');
        if($photo) {
            $photoName = time() . '.' . $photo->extension();
            $photo->move(public_path('banners'), $photoName);
            $data['image_path'] = $photoName;
        }

        Banner::create($data);

        return redirect()->route('admin.banners')->with('success', 'Banner creado correctamente');
    }

    public function editBanner(Request $request, $id)
    {
        $banner = Banner::find($id);
        return view('admin.banner.create', compact('banner'));
    }

    public function saveBanner(Request $request, $id) {
        $banner = Banner::find($id);

        $request->validate([
            'image_path' => 'image'
        ]);

        $data = $request->all();
        $photo = $request->file('image_path');
        if($photo) {
            $photoName = time() . '.' . $photo->extension();
            $photo->move(public_path('banners'), $photoName);
            $data['image_path'] = $photoName;
        }

        $banner->update($data);

        return redirect()->route('admin.banners')->with('success', 'Banner actualizado correctamente');
    }

    public function destroyBanner(Request $request, $id) {
        $banner = Banner::find($id);
        $banner->delete();

        return redirect()->route('admin.banners')->with('success', 'Banner eliminado correctamente');
    }
}
