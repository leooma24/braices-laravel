<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

use App\Models\Property;
use App\Models\PropertyTypeModel;
use App\Models\PropertyStatusModel;
use App\Models\TransactionTypeModel;
use App\Models\Country;
use App\Models\State;
use App\Models\Township;
use App\Models\Suburb;
use App\Models\User;

use App\Helpers\SlugHelper;
use App\Services\AIDescriptionService;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PropertyController extends Controller
{
    public function getProperties(Request $request)
    {
        $data = $request->all();

        $list = Property::with(['propertyTypes', 'status'])->where('property_status_id', 1);

        if (!empty($data['tipo'])) {
            $list->whereHas('propertyTypes', function ($q) use ($data) {
                $q->where('property_type_id', $data['tipo']);
            });
        }
        if (!empty($data['tipo_transaccion'])) {
            $list->where('transaction_type_id', $data['tipo_transaccion']);
        }
        if (!empty($data['precio_minimo'])) {
            $list->where('price', '>=', $data['precio_minimo'],);
        }
        if (!empty($data['precio_maximo'])) {
            $list->where('price', '<=', $data['precio_maximo']);
        }
        // Featured primero, despues por id desc (mas recientes)
        $list->orderByDesc('is_featured')
            ->orderByDesc('id');
        $list = $list->paginate($request->get('per_page', 15));

        $types = PropertyTypeModel::all();
        $transactions = TransactionTypeModel::all();

        return view('properties', compact('list', 'data', 'types', 'transactions'));
    }

    public function getPopularProperties(Request $request)
    {
        $list = Property::with(['type', 'status'])
            ->where('property_status_id', 1)
            ->paginate($request->get('per_page', 15));

        foreach ($list as $key => $property) {
            if (!empty($property['photo_main'])) {
                $list[$key]['photo_main'] = URL::to('/') . '/images/' . $property['photo_main'];
            }
        }

        return response()->json($list, 200);
    }

    public function getBestProperties(Request $request)
    {
        $list = Property::with(['type', 'status'])
            ->where('property_status_id', 1)
            ->orderBy('price', 'desc')
            ->paginate($request->get('per_page', 15));

        foreach ($list as $key => $property) {
            if (!empty($property['photo_main'])) {
                $list[$key]['photo_main'] = URL::to('/') . '/images/' . $property['photo_main'];
            }
        }

        return response()->json($list, 200);
    }

    public function getMyProperties(Request $request)
    {
        $user = $request->user();
        $list = Property::where('user_id', $user->id)
            ->with(['type', 'status'])->paginate($request->get('per_page', 15));

        return view('my-properties', compact('list'));
    }

    public function getPropertiesByUser($slug, Request $request)
    {
        $user = User::where('slug', $slug)->first();
        $data = $request->all();

        $list = Property::with(['type', 'status'])->where('user_id', $user->id);

        if (!empty($data['tipo'])) {
            $list->whereHas('propertyTypes', function ($q) use ($data) {
                $q->where('property_type_id', $data['tipo']);
            });
        }
        if (!empty($data['tipo_transaccion'])) {
            $list->where('transaction_type_id', $data['tipo_transaccion']);
        }
        if (!empty($data['precio_minimo'])) {
            $list->where('price', '>=', $data['precio_minimo'],);
        }
        if (!empty($data['precio_maximo'])) {
            $list->where('price', '<=', $data['precio_maximo'],);
        }
        $list = $list->paginate($request->get('per_page', 10));

        $types = PropertyTypeModel::all();
        $transactions = TransactionTypeModel::all();

        $qrCode = QrCode::size(150)
            ->generate(URL::to('/') . '/propiedades/' . $user->slug);

        return view('user-slug-properties', compact('list', 'user', 'qrCode', 'types', 'transactions', 'data'));
    }

    public function getProperty(Request $request, $id)
    {
        $qrCode = QrCode::size(150)
            ->generate(URL::to('/') . '/propiedad/' . $id);
        $property = Property::with(['propertyTypes', 'status', 'images', 'user', 'countryName', 'stateName', 'townshipName', 'suburbName', 'reviews.user'])->where('slug', $id)->first();

        $property->increment('views');

        return view('property', compact('property', 'qrCode'));
    }

    public function getUserProperty(Request $request, $slugUser, $slugProperty)
    {
        $qrCode = QrCode::size(150)
            ->generate(URL::to('/') . '/propiedades/' . $slugUser . '/propiedad/' . $slugProperty);
        $property = Property::with(['type', 'status', 'images', 'user', 'countryName', 'stateName', 'townshipName', 'suburbName', 'reviews.user'])->where('slug', $slugProperty)->first();

        $property->increment('views');

        return view('property', compact('property', 'qrCode', 'slugUser'));
    }

    public function editProperty(Request $request, $id)
    {
        $property = Property::with(['propertyTypes', 'status', 'images', 'user'])->where('slug', $id)->first();

        if (!$property) {
            return redirect(route('myProperties'))->with('error', 'La Propiedad no existe');
        }

        if ($property->user_id !== Auth::id()) {
            abort(403);
        }

        $countries = Country::all()->toArray();
        $countries = array_merge([['id' => '', 'nombre' => 'Seleccione un país']], $countries);

        $states = State::where('pais', 1)->get()->toArray();
        $states = array_merge([['id' => '', 'nombre' => 'Seleccione un estado']], $states);

        $townships = [];
        if ($property->state) {
            $townships = Township::where('estado', $property->state)->get()->toArray();
        }
        $townships = array_merge([['id' => '', 'nombre' => 'Seleccione un municipio']], $townships);

        $suburbs = [];
        if ($property->township) {
            $suburbs = Suburb::where('municipio', $property->township)->get()->toArray();
        }
        $suburbs = array_merge([['id' => '', 'nombre' => 'Seleccione una colonia']], $suburbs);


        $types = PropertyTypeModel::all();
        $transactions = TransactionTypeModel::all();
        $status = PropertyStatusModel::all();

        return view('property-edit', compact(
            'property',
            'types',
            'transactions',
            'status',
            'countries',
            'states',
            'townships',
            'suburbs'
        ));
    }

    public function newProperty(Request $request)
    {
        $user = Auth::user();
        $userPackage = $user->userPackages()
            ->where('remaining_listings', '>', 0)
            ->first();

        if (!$userPackage) {
            return redirect()->route('packages')
                ->with('error', 'Necesitas un paquete con publicaciones disponibles para crear una propiedad.');
        }

        $property = new Property();
        $types = PropertyTypeModel::all();
        $transactions = TransactionTypeModel::all();
        $status = PropertyStatusModel::all();

        $countries = Country::all()->toArray();
        $countries = array_merge([['id' => '', 'nombre' => 'Seleccione un país']], $countries);

        $states = State::where('pais', 1)->get()->toArray();
        $states = array_merge([['id' => '', 'nombre' => 'Seleccione un estado']], $states);

        $townships = [];
        $townships = array_merge([['id' => '', 'nombre' => 'Seleccione un municipio']], $townships);

        $suburbs = [];
        $suburbs = array_merge([['id' => '', 'nombre' => 'Seleccione una colonia']], $suburbs);

        return view('property-edit', compact(
            'property',
            'types',
            'transactions',
            'status',
            'countries',
            'states',
            'townships',
            'suburbs'
        ));
    }

    public function getPropertyTypes(Request $request)
    {
        $list = PropertyTypeModel::all()->toArray();

        return response()->json($list, 200);
    }

    public function getPropertyStatus(Request $request)
    {
        $list = PropertyStatusModel::all()->toArray();

        return response()->json($list, 200);
    }

    public function saveProperty(Request $request, $id): RedirectResponse
    {
        $property = Property::find($id);
        $user = Auth::user();

        if (!$property) {
            return redirect(route('myProperties'))->with('error', 'La Propiedad no existe');
        }

        if ($property->user_id !== $user->id) {
            return redirect(route('myProperties'))->with('error', 'No tienes permisos para editar esta propiedad');
        }

        $data = $request->all();
        if (isset($data['price'])) {
            $data['price'] = str_replace(',', '', $data['price']);
        }

        // Evitar mass-assignment de campos sensibles. user_id y slug no están
        // en $fillable, pero property_status_id sí — y solo admin debería cambiarlo.
        unset($data['property_status_id'], $data['user_id'], $data['slug'], $data['is_featured'], $data['featured_until']);

        $photo = $request->file('photo_main');
        if ($photo) {
            $photoName = time() . '.' . $photo->extension();
            $photo->move(public_path('images'), $photoName);

            $data['photo_main'] = $photoName;
        }

        $request->validate([
            'images.*' => 'sometimes|file|mimes:jpg,jpeg,png,bmp|max:2048',
        ]);

        $this->saveGalleryImages($property, $request->file('images'));

        $property->fill($data);
        $property->propertyTypes()->sync($request->property_type_id);
        $property->save();

        return redirect(route('myProperties'))->with('success', 'La Propieda ha sido Actualizada');
    }

    public function deleteImage($id, $image)
    {
        $property = Property::find($id);
        if (!$property) {
            return redirect(route('myProperties'))->with('error', 'La Propiedad no existe');
        }

        if ($property->user_id !== Auth::id()) {
            abort(403);
        }

        $image = $property->images()->find($image);
        if (!$image) {
            return redirect(route('myProperties'))->with('error', 'La Imagen no existe');
        }

        $image->delete();
        return redirect(route('properties.edit', $property->slug))->with('success', 'La Imagen ha sido eliminada');
    }

    /**
     * Genera una descripción AI vía Claude basada en los datos del formulario.
     */
    public function aiDescription(Request $request, AIDescriptionService $ai)
    {
        if (!filter_var(env('AI_DESCRIPTIONS_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
            abort(404);
        }

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'suburb' => ['nullable', 'string', 'max:100'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'bathrooms' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'square_feet' => ['nullable', 'numeric', 'min:0'],
            'lot_size' => ['nullable', 'numeric', 'min:0'],
            'year_built' => ['nullable', 'integer'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'levels' => ['nullable', 'integer'],
            'front' => ['nullable', 'numeric'],
            'depth' => ['nullable', 'numeric'],
            'transaction' => ['nullable', 'string'],
            'property_types' => ['nullable', 'array'],
            'property_types.*' => ['string'],
            'is_reservable' => ['nullable', 'boolean'],
            'max_guests' => ['nullable', 'integer'],
            'price_per_night' => ['nullable', 'numeric'],
        ]);

        $hasContent = collect($data)->filter(fn ($v) => !is_null($v) && $v !== '' && $v !== [])->count() >= 3;
        if (!$hasContent) {
            return response()->json([
                'error' => 'Captura al menos algunos datos básicos (título, ubicación, tipo) antes de generar la descripción.',
            ], 422);
        }

        try {
            $description = $ai->generate($data);
            return response()->json(['description' => $description]);
        } catch (\Throwable $e) {
            \Log::error('AI description failed', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'No se pudo generar la descripción: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Activa o desactiva el destacado de una propiedad por 30 días.
     * Solo el dueño puede hacerlo. La monetización (paquetes con
     * featured_listings remaining) se puede agregar después.
     */
    public function toggleFeatured($slug)
    {
        $property = Property::where('slug', $slug)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($property->isFeaturedNow()) {
            $property->forceFill([
                'is_featured' => false,
                'featured_until' => null,
            ])->save();
            return redirect()->route('myProperties')
                ->with('success', 'Destacado desactivado.');
        }

        $property->forceFill([
            'is_featured' => true,
            'featured_until' => now()->addDays(30),
        ])->save();
        return redirect()->route('myProperties')
            ->with('success', '¡Tu propiedad quedó destacada por 30 días!');
    }

    public function destroy($id)
    {
        // El parámetro de la ruta es {slug} pero el form envía el id numérico,
        // así que aceptamos cualquiera de los dos.
        $property = Property::where('id', $id)->orWhere('slug', $id)->first();
        if (!$property) {
            return redirect(route('myProperties'))->with('error', 'La Propiedad no existe');
        }

        if ($property->user_id !== Auth::id()) {
            abort(403);
        }

        $property->delete();
        return redirect(route('myProperties'))->with('success', 'La Propiedad ha sido eliminada');
    }

    public function create(Request $request)
    {
        $user = Auth::user();

        $userPackage = $user->userPackages()
            ->where('remaining_listings', '>', 0)
            ->first();

        if (!$userPackage) {
            return redirect()->back()
                ->with('error', 'No tienes paquetes con publicaciones disponibles')
                ->withInput();
        }

        // Validar antes de crear la propiedad para no consumir la publicación si falla.
        $request->validate([
            'images.*' => 'sometimes|file|mimes:jpg,jpeg,png,bmp|max:2048',
        ]);

        $data = array_filter($request->all(), function ($value) {
            return !is_null($value);
        });
        // No permitir que el usuario fije el estatus de la propiedad desde el form
        unset($data['property_status_id'], $data['user_id'], $data['slug']);

        $photo = $request->file('photo_main');
        if ($photo) {
            $photoName = time() . '.' . $photo->extension();
            $photo->move(public_path('images'), $photoName);

            $data['photo_main'] = $photoName;
        }

        $property = new Property();
        $property->fill($data);
        $property->user_id = $user->id;
        $property->property_status_id = 1;
        $property->slug = SlugHelper::createUniqueSlug($property->title, Property::class);
        $property->save();

        $property->propertyTypes()->sync($request->property_type_id);
        $this->saveGalleryImages($property, $request->file('images'));

        $userPackage->remaining_listings -= 1;
        $userPackage->save();

        return redirect(route('myProperties'))->with('success', 'La Propiedad ha sido creada');
    }

    /**
     * Guarda las fotos de la pestaña "Imagenes" (images[]) en public/images.
     */
    private function saveGalleryImages(Property $property, ?array $files): void
    {
        foreach ($files ?? [] as $file) {
            // Generar nombre seguro: timestamp + random (no usar getClientOriginalName
            // directamente — un atacante puede meter caracteres exóticos o paths).
            $fileName = time() . '_' . \Illuminate\Support\Str::random(8) . '.' . $file->extension();
            $file->move(public_path('images'), $fileName);
            $property->images()->create([
                'photo' => $fileName,
            ]);
        }
    }

    public function uploadPhoto(request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $photo = $request->file('image');
        $photoName = time() . '.' . $photo->extension();
        $photo->move(public_path('images'), $photoName);

        return response()->json([
            'status' => 'success',
            'photo' => $photoName,
            'url' => URL::to('/') . '/images/' . $photoName,
        ], 200);
    }

    public function getUserProperties(User $user)
    {
        $properties = Property::where('user_id', $user->id)->get();

        return view('user-properties', compact('properties'));
    }

    public function getImageProperty($id)
    {
        $property = Property::with(['suburbName', 'townshipName', 'transaction', 'user'])->find($id);
        if (!$property) {
            abort(404);
        }

        $stats = [];
        if ($property->square_feet) {
            $stats[] = ['value' => number_format(round($property->square_feet)) . ' m²', 'label' => 'Terreno'];
        }
        if ($property->square_meters_contruction) {
            $stats[] = ['value' => number_format(round($property->square_meters_contruction)) . ' m²', 'label' => 'Construcción'];
        }
        if ($property->bedrooms) {
            $stats[] = ['value' => (string) $property->bedrooms, 'label' => $property->bedrooms == 1 ? 'Recámara' : 'Recámaras'];
        }
        if ($property->bathrooms) {
            $stats[] = ['value' => (string) $property->bathrooms, 'label' => $property->bathrooms == 1 ? 'Baño' : 'Baños'];
        }
        if (count($stats) < 4 && $property->front && $property->depth) {
            $stats[] = ['value' => "{$property->front} x {$property->depth}", 'label' => 'Medidas (m)'];
        }

        // El accessor devuelve URL pública; resolver a path real del filesystem
        $rawPhoto = $property->getRawOriginal('photo_main');

        $renderer = new \App\Services\PropertyShareImage(
            public_path('fonts/metropolis.medium.otf'),
            public_path('fonts/metropolis.black.otf'),
            public_path('BienesCorpLogo.png'),
        );

        $image = $renderer->render([
            'photo' => $rawPhoto ? public_path('images/' . $rawPhoto) : null,
            'operation' => $property->transaction ? 'En ' . mb_strtolower($property->transaction->name) : null,
            'title' => (string) $property->title,
            'price' => '$' . number_format((float) $property->price),
            'location' => implode(', ', array_filter([
                $property->suburbName?->nombre,
                $property->city ?: $property->townshipName?->nombre,
            ])),
            'stats' => $stats,
            'phone' => $property->user?->phone_number,
            'site' => parse_url(config('app.url'), PHP_URL_HOST) ?: 'bienescorp.com',
        ]);

        return response($image, 200)
            ->header('Content-Type', 'image/jpeg')
            ->header('Cache-Control', 'no-store');
    }
}
