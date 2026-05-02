<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Banner;
use App\Models\Package;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PageController extends Controller
{
    //
    public function index()
    {
        $banners = Banner::orderby('position', 'asc')->get();
        if ($banners->count() == 0) {
            $banners = null;
        }
        $popularProperties = Property::with(['type', 'status'])->where('property_status_id', 1)->take(3)->get();
        $newestProperties = Property::with(['type', 'status'])->where('property_status_id', 1)->orderby('id', 'desc')->take(3)->get();
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

    public function reservations(Request $request)
    {
        $days = 1;
        $checkIn = $request->query('check_in');
        $checkOut = $request->query('check_out');
        if ($checkIn && $checkOut) {
            try {
                $days = max(1, Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut)));
            } catch (\Exception $e) {
                $days = 1;
            }
        }

        $properties = Property::where('is_reservable', true)->paginate(20);
        return view('reservations', compact('properties', 'days'));
    }
}
