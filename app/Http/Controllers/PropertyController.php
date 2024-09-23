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

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use PhpParser\PrettyPrinter\Standard;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PropertyController extends Controller
{
    public function getProperties(Request $request)
    {
        $data = $request->all();

        $list = Property::with(['propertyTypes', 'status']);

        if (!empty($data['tipo'])) {
            $list->whereHas('propertyTypes', function($q) use ($data) {
                $q->where('property_type_id', $data['tipo']);
            });
        }
        if(!empty($data['tipo_transaccion'])) {
            $list->where('transaction_type_id', $data['tipo_transaccion']);
        }
        if(!empty($data['precio_minimo'])) {
            $list->where('price', '>=', $data['precio_minimo'],);
        }
        if(!empty($data['precio_maximo'])) {
            $list->where('price', '<=', $data['precio_maximo'],);
        }
        $list = $list->paginate($request->get('per_page', 15));

        $types = PropertyTypeModel::all();
        $transactions = TransactionTypeModel::all();

        return view('properties', compact('list', 'data', 'types', 'transactions'));
    }

    public function getPopularProperties(Request $request)
    {
        $list = Property::with(['type', 'status'])
        ->paginate($request->get('per_page', 15));

        foreach ($list as $key => $property) {
            if(!empty($property['photo_main'])) {
                $list[$key]['photo_main'] = URL::to('/') . '/images/' . $property['photo_main'];
            }
        }

        return response()->json($list, 200);
    }

    public function getBestProperties(Request $request)
    {
        $list = Property::with(['type', 'status'])
        ->orderBy('price', 'desc')
        ->paginate($request->get('per_page', 15));

        foreach ($list as $key => $property) {
            if(!empty($property['photo_main'])) {
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
            $list->whereHas('propertyTypes', function($q) use ($data) {
                $q->where('property_type_id', $data['tipo']);
            });
        }
        if(!empty($data['tipo_transaccion'])) {
            $list->where('transaction_type_id', $data['tipo_transaccion']);
        }
        if(!empty($data['precio_minimo'])) {
            $list->where('price', '>=', $data['precio_minimo'],);
        }
        if(!empty($data['precio_maximo'])) {
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
        $property = Property::with(['propertyTypes', 'status', 'images', 'user', 'countryName', 'stateName', 'townshipName', 'suburbName'])->where('slug', $id)->first();

        $property->increment('views');

        return view('property', compact('property', 'qrCode'));
    }

    public function getUserProperty(Request $request, $slugUser, $slugProperty)
    {
        $qrCode = QrCode::size(150)
        ->generate(URL::to('/') . '/propiedades/' . $slugUser . '/propiedad/' . $slugProperty);
        $property = Property::with(['type', 'status', 'images', 'user', 'countryName', 'stateName', 'townshipName', 'suburbName'])->where('slug', $slugProperty)->first();

        $property->increment('views');

        return view('property', compact('property', 'qrCode', 'slugUser'));
    }

    public function editProperty(Request $request, $id)
    {
        $property = Property::with(['propertyTypes', 'status', 'images', 'user'])->where('slug', $id)->first();

        $countries = Country::all()->toArray();
        $countries = array_merge([['id' => '', 'nombre' => 'Seleccione un país']], $countries );

        $states = State::where('pais', 1)->get()->toArray();
        $states = array_merge([['id' => '', 'nombre' => 'Seleccione un estado']], $states );

        $townships = [];
        if($property->state) {
            $townships = Township::where('estado', $property->state)->get()->toArray();
        }
        $townships = array_merge([['id' => '', 'nombre' => 'Seleccione un municipio']], $townships );

        $suburbs = [];
        if($property->township) {
            $suburbs = Suburb::where('municipio', $property->township)->get()->toArray();
        }
        $suburbs = array_merge([['id' => '', 'nombre' => 'Seleccione una colonia']], $suburbs );


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
        $property = new Standard();
        $types = PropertyTypeModel::all();
        $transactions = TransactionTypeModel::all();
        $status = PropertyStatusModel::all();

        $countries = Country::all()->toArray();
        $countries = array_merge([['id' => '', 'nombre' => 'Seleccione un país']], $countries );

        $states = State::where('pais', 1)->get()->toArray();
        $states = array_merge([['id' => '', 'nombre' => 'Seleccione un estado']], $states );

        $townships = [];
        $townships = array_merge([['id' => '', 'nombre' => 'Seleccione un municipio']], $townships );

        $suburbs = [];
        $suburbs = array_merge([['id' => '', 'nombre' => 'Seleccione una colonia']], $suburbs );

        return view('property-edit', compact(
            'property', 'types',
            'transactions', 'status',
            'countries', 'states',
            'townships', 'suburbs'));
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

    public function saveProperty(Request $request, $id) : RedirectResponse
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

        $photo = $request->file('photo_main');
        if($photo) {
            $photoName = time() . '.' . $photo->extension();
            $photo->move(public_path('images'), $photoName);

            $data['photo_main'] = $photoName;
        }

        $request->validate([
            'images.*' => 'required|file|mimes:jpg,jpeg,png,bmp|max:2048',
        ]);

        $uploadedFiles = $request->file('images');

        if($uploadedFiles) {
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

    public function deleteImage($id, $image) {
        $property = Property::find($id);
        if(!$property) {
            return redirect(route('myProperties'))->with('error', 'La Propiedad no existe');
        }

        $image = $property->images()->find($image);
        if(!$image) {
            return redirect(route('myProperties'))->with('error', 'La Imagen no existe');
        }

        $image->delete();
        return redirect(route('properties.edit', $property->slug))->with('success', 'La Imagen ha sido eliminada');
    }

    public function destroy($id) {
        $property = Property::find($id);
        if(!$property) {
            return redirect(route('myProperties'))->with('error', 'La Propiedad no existe');
        }

        $property->delete();
        return redirect(route('myProperties'))->with('success', 'La Propiedad ha sido eliminada');
    }

    public function create(PropertyRequest $request)
    {
        $user = Auth::user();
        $property = new Property();

        $userPackage = $user->userPackages()
            ->where('remaining_listings', '>', 0)
            ->first();

        $userPackage->remaining_listings -= 1;
        $userPackage->save();

        $data = $request->all();

        $photo = $request->file('photo_main');
        if($photo) {
            $photoName = time() . '.' . $photo->extension();
            $photo->move(public_path('images'), $photoName);

            $data['photo_main'] = $photoName;
        }

        $property->fill($data);
        $property->user_id  = $user->id;
        $property->slug = SlugHelper::createUniqueSlug($property->title, Property::class);
        $property->save();

        $property->propertyTypes()->sync($request->property_type_id);

        return redirect(route('myProperties'))->with('success', 'La Propiedad ha sido creada');
    }

    public function uploadPhoto(request $request) {
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
}
