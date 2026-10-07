<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Package;
use App\Models\Property;
use App\Models\PropertyTypeModel;
use App\Models\TransactionTypeModel;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
            ['label' => 'Departamentos', 'icon' => 'fa-building',     'slug' => 'departamentos', 'match' => ['Departamentos']],
            ['label' => 'Terrenos',    'icon' => 'fa-map-marked-alt', 'slug' => 'terrenos',    'match' => ['Terrenos', 'Terrenos Residenciales', 'Terrenos Comerciales', 'Terrenos Industriales', 'Terrenos Agrícolas', 'Terrenos Campestres', 'Terrenos Turísticos', 'Terrenos Ejidales']],
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

    public function privacy()
    {
        return view('privacy');
    }

    public function terms()
    {
        return view('terms');
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

    /**
     * Pagina para el dueño que esta decidiendo donde publicar. Responde en
     * orden lo que se pregunta: cuanta gente la va a ver, como se va a ver,
     * cuanto cuesta y cuanto tarda.
     */
    public function publish(\App\Services\ViewsInsight $insight)
    {
        $audience = $insight->siteSummary(30);

        // Un ejemplo real de ficha vale mas que un mockup: se elige la activa
        // con foto mas vista, para que el dueño vea el formato de verdad.
        $sample = Property::with(['propertyTypes', 'transaction'])
            ->where('property_status_id', 1)
            ->whereNotNull('photo_main')
            ->orderByDesc('views')
            ->first();

        $packages = Package::orderBy('price')->get();

        return view('publish', compact('audience', 'sample', 'packages'));
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

        // Solo se ofrecen para reservar las que siguen Disponibles: una
        // propiedad marcada Rentada no debe aceptar nuevas estancias.
        $properties = Property::where('is_reservable', true)
            ->where('property_status_id', 1)
            ->paginate(20);

        return view('reservations', compact('properties', 'days'));
    }
}
