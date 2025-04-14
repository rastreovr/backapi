<?php

namespace App\Http\Controllers\Navixy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Place extends Controller
{

    static function getPlace(Request $request)
    {
        $hash = $request->input("hash");
        $URL = Navixy::$URL;

        $response = Http::get("$URL/place/list?hash=$hash");

        if (!$response->successful()) {
            return Functions::responseErrorDefault($response);
        }

        return sendApiSuccess(self::mapPlace($response->json()["list"]));
    }

    static function mapPlace(array $data)
    {
        return array_map(function ($value) {
            return [

                "id" => $value["id"],
                "label" => $value["label"],
                "description" => $value["description"],

                "location" => [
                    "address" => $value["location"]["address"],
                    "lat" => $value["location"]["lat"],
                    "lng" => $value["location"]["lng"],
                    "radius" => $value["location"]["radius"]
                ]
            ];
        }, $data);
    }
}
