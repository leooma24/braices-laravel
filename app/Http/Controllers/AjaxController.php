<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Suburb;
use App\Models\Township;

class AjaxController extends Controller
{
    //
    public function zip(Request $request)
    {
        $zip = $request->search;

        $suburbs = Suburb::where('codigo_postal', $zip)->get()->toArray();
        $township = Township::find($suburbs[0]['municipio']);

        $response = [
            'suburbs' => $suburbs,
            'city' => $suburbs[0]['ciudad'],
            'township' => $township
        ];

        return response()->json($response);
    }

    public function state(Request $request)
    {
        $state = $request->search;

        //$data = file_get_contents('https://api.opencagedata.com/geocode/v1/json?key=8cc52f2fb8a644c88bcff456eb84bf16&q=los%20mochis%20sinaloa&pretty=1');
        //dd($data);
        $townships = Township::where('estado', $state)->get()->toArray();

        return response()->json($townships);
    }
}
