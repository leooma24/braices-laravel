<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Banner;
use App\Models\Package;

class PageController extends Controller
{
    //
    public function index()
    {
        $banners = Banner::orderby('position', 'asc')->get();
        if($banners->count() == 0) {
            $banners = null;
        }
        $popularProperties = Property::with(['type', 'status'])->take(3)->get();
        $newestProperties = Property::with(['type', 'status'])->orderby('id', 'desc')->take(3)->get();
        $packages = Package::orderBy('price')->get();

        return view('index', compact('popularProperties', 'newestProperties', 'banners', 'packages'));
    }

    public function us()
    {
        return view('us');
    }

    public function contact()
    {
        return view('contact');
    }

    public function packages()
    {
        $packages = Package::orderBy('price')->get();
        return view('packages', compact('packages'));
    }

}
