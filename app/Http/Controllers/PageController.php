<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyTypeModel;
use App\Models\TransactionTypeModel;
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
        $popularProperties = Property::with(['propertyTypes', 'status'])
            ->where('property_status_id', 1)
            ->orderByDesc('views')
            ->take(6)
            ->get();
        $newestProperties = Property::with(['propertyTypes', 'status'])
            ->where('property_status_id', 1)
            ->orderByDesc('id')
            ->take(6)
            ->get();
        $packages = Package::orderBy('price')->get();

        // Para el buscador del hero.
        $types = PropertyTypeModel::all();
        $transactions = TransactionTypeModel::all();

        // Stats sociales (números reales) para mostrar confianza.
        $stats = [
            'properties' => Property::where('property_status_id', 1)->count(),
            'cities' => Property::where('property_status_id', 1)
                ->whereNotNull('city')
                ->where('city', '!=', '')
                ->distinct('city')
                ->count('city'),
            'agents' => \App\Models\User::has('properties')->count(),
        ];

        // Categorías destacadas con conteos para la sección de tipos populares.
        // Mapeamos por nombre porque la BD usa nombres descriptivos.
        $popularCategories = $this->popularCategories();

        return view('index', compact(
            'popularProperties',
            'newestProperties',
            'banners',
            'packages',
            'types',
            'transactions',
            'stats',
            'popularCategories',
        ));
    }

    /**
     * @return array<int, array{label:string, icon:string, slug:string, count:int}>
     */
    private function popularCategories(): array
    {
        $byType = Property::query()
            ->where('property_status_id', 1)
            ->join('property_property_type', 'properties.id', '=', 'property_property_type.property_id')
            ->join('property_types', 'property_property_type.property_type_id', '=', 'property_types.id')
            ->groupBy('property_types.id', 'property_types.name')
            ->select('property_types.id', 'property_types.name', \DB::raw('count(*) as total'))
            ->orderByDesc('total')
            ->pluck('total', 'name')
            ->toArray();

        // Grupos curados: agregamos varios property types bajo una sola tarjeta.
        $groups = [
            ['label' => 'Casas',       'icon' => 'fa-home',         'slug' => 'casas',       'match' => ['Casa Habitación', 'Casa Comercial', 'Casas de Playa']],
            ['label' => 'Departamentos','icon' => 'fa-building',     'slug' => 'departamentos','match' => ['Departamentos']],
            ['label' => 'Terrenos',    'icon' => 'fa-map-marked-alt','slug' => 'terrenos',    'match' => ['Terrenos', 'Terrenos Residenciales', 'Terrenos Comerciales', 'Terrenos Industriales', 'Terrenos Agrícolas', 'Terrenos Campestres', 'Terrenos Turísticos', 'Terrenos Ejidales']],
            ['label' => 'Comercial',   'icon' => 'fa-store',         'slug' => 'comercial',   'match' => ['Locales Comerciales', 'Oficinas', 'Bodegas', 'Edificios']],
        ];

        $out = [];
        foreach ($groups as $g) {
            $count = 0;
            foreach ($g['match'] as $m) {
                $count += (int) ($byType[$m] ?? 0);
            }
            $out[] = [
                'label' => $g['label'],
                'icon' => $g['icon'],
                'slug' => $g['slug'],
                'count' => $count,
            ];
        }
        return $out;
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
