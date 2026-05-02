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
        $data['price'] = str_replace(',', '', $data['price']);

        $photo = $request->file('photo_main');
        if ($photo) {
            $photoName = time() . '.' . $photo->extension();
            $photo->move(public_path('images'), $photoName);

            $data['photo_main'] = $photoName;
        }

        $request->validate([
            'images.*' => 'required|file|mimes:jpg,jpeg,png,bmp|max:2048',
        ]);

        $uploadedFiles = $request->file('images');

        if ($uploadedFiles) {
            foreach ($uploadedFiles as $file) {
                // Procesar cada archivo
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('images'), $fileName);
                $property->images()->create([
                    'photo' => $fileName,
                ]);
            }
        }

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
        $property = Property::find($id);
        if (!$property) {
            return redirect(route('myProperties'))->with('error', 'La Propiedad no existe');
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

        $userPackage->remaining_listings -= 1;
        $userPackage->save();

        return redirect(route('myProperties'))->with('success', 'La Propiedad ha sido creada');
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
        $property = Property::find($id);
        if (!$property) {
            abort(404);
        }

        $width = 300;
        $height = 450;
        $image = imagecreatetruecolor($width, $height);
        $backgroundColor = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $backgroundColor);

        // El accessor devuelve URL pública; resolver a path real del filesystem
        $rawPhoto = $property->getRawOriginal('photo_main');
        $propertyImagePath = $rawPhoto ? public_path('images/' . $rawPhoto) : null;
        $extension = $propertyImagePath ? strtolower(pathinfo($propertyImagePath, PATHINFO_EXTENSION)) : null;

        $propertyImage = null;
        if ($propertyImagePath && file_exists($propertyImagePath)) {
            if (in_array($extension, ['jpg', 'jpeg'])) {
                $propertyImage = imagecreatefromjpeg($propertyImagePath);
            } elseif ($extension === 'png') {
                $propertyImage = imagecreatefrompng($propertyImagePath);
            } elseif ($extension === 'gif') {
                $propertyImage = imagecreatefromgif($propertyImagePath);
            } elseif ($extension === 'webp' && function_exists('imagecreatefromwebp')) {
                $propertyImage = imagecreatefromwebp($propertyImagePath);
            }
        }

        $propertyImageHeight = 0;
        if ($propertyImage) {
            $propertyImageWidth = imagesx($propertyImage);
            $propertyImageHeight = imagesy($propertyImage);

            if ($propertyImageWidth > 300) {
                $newWidth = 300;
                $newHeight = (int) (($propertyImageHeight / $propertyImageWidth) * $newWidth);
                $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($resizedImage, $propertyImage, 0, 0, 0, 0, $newWidth, $newHeight, $propertyImageWidth, $propertyImageHeight);
                imagedestroy($propertyImage);
                $propertyImage = $resizedImage;
                $propertyImageWidth = $newWidth;
                $propertyImageHeight = $newHeight;
            }

            imagecopy($image, $propertyImage, 0, 0, 0, 0, $propertyImageWidth, $propertyImageHeight);
            imagedestroy($propertyImage);
        }

        // Asignar un color para el texto
        $textColor = imagecolorallocate($image, 0, 0, 0); // Negro

        $fontPath = public_path('fonts/metropolis.medium.otf'); // Asegúrate de tener esta fuente en la ruta especificada

        // Añadir texto a la imagen
        // Función para centrar el texto
        function centerText($image, $text, $fontSize, $y, $color, $fontPath, $width)
        {
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
            $textWidth = $bbox[2] - $bbox[0];
            $x = ($width - $textWidth) / 2;
            imagettftext($image, $fontSize, 0, $x, $y, $color, $fontPath, $text);
        }

        // Función para dividir el texto en varias líneas si es demasiado largo
        function wrapText($text, $fontSize, $fontPath, $maxWidth)
        {
            $words = explode(' ', $text);
            $lines = [];
            $currentLine = '';

            foreach ($words as $word) {
                $testLine = $currentLine . ' ' . $word;
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $testLine);
                $textWidth = $bbox[2] - $bbox[0];

                if ($textWidth > $maxWidth) {
                    $lines[] = trim($currentLine);
                    $currentLine = $word;
                } else {
                    $currentLine = $testLine;
                }
            }

            $lines[] = trim($currentLine);
            return $lines;
        }

        // Añadir texto a la imagen
        $plus = 25;
        $texts = [
            $property->title,
            $property->suburbName?->nombre,
            '$' . number_format($property->price),
            "{$property->front} x {$property->depth} mts de terreno",
        ];
        if ($property->square_meters_contruction) {
            $texts[] = "Construcción de {$property->square_meters_contruction} m2";
        }
        $texts[] = "Información {$property->user?->phone_number}";

        $y = $propertyImageHeight + $plus;
        foreach ($texts as $text) {
            $lines = wrapText($text, 12, $fontPath, $width);
            foreach ($lines as $line) {
                centerText($image, $line, 12, $y, $textColor, $fontPath, $width);
                $y += $plus;
            }
        }

        // Devolver la imagen directamente (evita race condition con archivo temporal compartido)
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        return response($imageData, 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-store');
    }
}
