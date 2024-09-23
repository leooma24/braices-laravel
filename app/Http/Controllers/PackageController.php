<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;

class PackageController extends Controller
{
    //
    public function index()
    {
        $packages = Package::paginate();
        return view('admin.packages.index', compact('packages'));
    }

    public function create()
    {
        $package = null;
        return view('admin.packages.form', compact('package'));
    }

    public function new(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'max_listings' => 'required|numeric',
            'price' => 'required|numeric',
            'duration' => 'required|numeric'
        ]);

        $package = Package::create($request->all());

        return redirect()->route('admin.packages.index')->with('success', 'Paquete creado correctamente');
    }

    public function edit(Package $package)
    {
        return view('admin.packages.form', compact('package'));
    }

    public function save(Request $request, Package $package)
    {
        $request->validate([
            'name' => 'required',
            'max_listings' => 'required|numeric',
            'price' => 'required|numeric',
            'duration' => 'required|numeric'
        ]);

        $package->update($request->all());

        return redirect()->route('admin.packages.index')->with('success', 'Paquete actualizado correctamente');
    }

    public function destroy(Package $package)
    {
        $package->delete();
        return redirect()->route('admin.packages.index')->with('success', 'Paquete eliminado correctamente');
    }
}
