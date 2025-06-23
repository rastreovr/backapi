<?php

namespace App\Http\Controllers\Navixy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class Task extends Controller
{

    /**
    PARAMETROS:
        {
        "route": {
            "origin": {
                "lat": 32.486529,
                "lng": -116.9858202,
                "label": "P.º de las Bugambilias 6275, Jardines de Agua Caliente, Residencial Agua Caliente, 22194 Tijuana, B.C., México"
            },
            "destination": {
                "lat": 32.52201,
                "lng": -117.0138731,
                "label": "P.º de los Héroes 301, Zona Urbana Rio Tijuana, 22010 Tijuana, B.C., México"
            },
            "duration": 920
        },
        "tracker": {
            "id": 3303061,
            "label": "cristest"
        },
        "hash": "d0bd4a7cb4110dd92f895a6e53918386"
        }

     */

    static function createTaskRoute(Request $request)
    {

        $request->validate([
            'route' => ['required', 'array'],

            'route.origin' => ['required', 'array'],
            'route.origin.lat' => ['required', 'numeric'],
            'route.origin.lng' => ['required', 'numeric'],
            'route.origin.address' => ['required', 'string'],

            'route.origin.radius' => ['required', 'numeric'],
            'route.origin.label' => ['required', 'string'],

            'route.destination' => ['required', 'array'],
            'route.destination.lat' => ['required', 'numeric'],
            'route.destination.lng' => ['required', 'numeric'],
            'route.destination.address' => ['required', 'string'],

            'route.destination.radius' => ['required', 'numeric'],
            'route.destination.label' => ['required', 'string'],


            'route.duration' => ['required', 'integer', 'min:1'],

            'tracker' => ['required', 'array'],
            'tracker.id' => ['required', 'integer'],
            'tracker.label' => ['required', 'string'],

            'hash' => ['required', 'string', 'size:32']
        ]);

        $URL = Navixy::$URL;

        $time_zone = $request->input("time_zone") ?? null;

        if($time_zone){
            date_default_timezone_set($time_zone);
        }


        $params = self::getParamsTaskRoute([
            "route" => $request->input("route"),
            "tracker" =>  $request->input("tracker"),
            "hash" => $request->input("hash")
        ]);

        $response = Http::post("$URL/task/route/create", $params);

        if (!$response->successful()) {
            return Functions::responseErrorDefault($response);
        }

        return sendApiSuccess($params, "Ruta de Seguimiento creada exitosamente");
    }

    static function getParamCheckPointStart(array $params)
    {

        return [

            "location" => [
                "address" => $params["address"],
                "lat" => $params["lat"],
                "lng" => $params["lng"],
                "radius" => $params["radius"]
            ],

            "label" => $params["label"],
            "description" => $params["description"],

            "from" => $params["from"],
            "to" => $params["to"],

            "max_delay" => 30,
            "min_stay_duration" => 0,
            "tags" => [],
            "form_template_id" => null
        ];
    }

    static function getParamCheckPointEnd(array $params)
    {

        return [

            "location" => [
                "address" => $params["address"],
                "lat" => $params["lat"],
                "lng" => $params["lng"],
                "radius" => $params["radius"]
            ],

            "label" => $params["label"],
            "description" => $params["description"],

            "from" => $params["from"],
            "to" => $params["to"],

            "max_delay" => 0,
            "min_stay_duration" => 30,
            "tags" => [],
            "form_template_id" => null
        ];
    }

    static function getParamsTaskRoute(array $params)
    {
        [
            "route" => $route,
            "tracker" => $tracker,
            "hash" => $hash
        ] = $params;


        $fromCheckPointRoute = Carbon::now()->format('Y-m-d H:i:s');
        $toCheckPointRoute = Carbon::now()->addMinutes($route["duration"] / 60)->format('Y-m-d H:i:s');

        $fromCheckPointStart = Carbon::now()->format('Y-m-d H:i:s');
        $toCheckPointStart = Carbon::now()->addMinutes(10)->format('Y-m-d H:i:s');


        return [
            "hash" => $hash,
            "route" => [
                "tracker_id" => $tracker["id"],
                "label" => $tracker["label"],
                "description" => "",
                "from" => $fromCheckPointRoute,
                "to" => $toCheckPointRoute
            ],
            "checkpoints" => [

                self::getParamCheckPointStart([
                    "lat" => $route["origin"]["lat"],
                    "lng" => $route["origin"]["lng"],
                    "address" => $route["origin"]["label"],

                    "radius" => $route["origin"]["radius"],
                    "label" => $route["origin"]["label"],
                    "description" => $route["origin"]["description"] ?? "",

                    "from" => $fromCheckPointStart,
                    "to" => $toCheckPointStart
                ]),

                self::getParamCheckPointEnd([
                    "lat" => $route["destination"]["lat"],
                    "lng" => $route["destination"]["lng"],
                    "address" => $route["destination"]["address"],

                    "radius" => $route["destination"]["radius"],
                    "label" => $route["destination"]["label"],
                    "description" => $route["destination"]["description"] ?? "",

                    "from" => $fromCheckPointRoute,
                    "to" => $toCheckPointRoute
                ]),
            ],
            "create_form" => false
        ];
    }


    /**
     * !TASK
     */
    static function getListTask(Request $request)
    {

        $hash = $request->input("hash");
        $trackers = $request->input("trackers");
        $status_position_trackers = $request->input("status_position");
        $status_employee_trackers = $request->input("status_employee");;

        $URL = Navixy::$URL;

        $params = [
            "hash" => $hash,
            "types" => [
                "route",
            ]
        ];

        if ($trackers) {
            $params["trackers"] = $trackers;
        }

        $response = Http::post("$URL/task/list", $params);


        if (!$response->successful()) {
            return Functions::responseErrorDefault($response);
        }

        $data = self::mapListTask($response->json()["list"]);

        if ($status_position_trackers) {

            $data = self::setPositionTrackerOnListTask([
                "hash" => $hash,
                "trackers" => $trackers,
                "data" => $data
            ]);
        }

        if ($status_employee_trackers) {

            $data = self::setEmployeeTrackerOnListTask([
                "hash" => $hash,
                "trackers" => $trackers,
                "data" => $data
            ]);
        }

        return sendApiSuccess($data);
    }

    static function mapListTask(array $data)
    {
        return array_map(function ($value) {
            return [
                "id" => $value["id"],
                "label" => $value["label"],
                "creation_date" => $value["creation_date"],

                "from" => $value["from"],
                "to" => $value["to"],
                "status" => $value["status"],

                "user_id" => $value["user_id"],
                "tracker_id" => $value["tracker_id"],

                "type"=>$value["type"],

                "checkpoints" => array_map(function ($item) {
                    return [
                        "id" => $item["id"],

                        "label" => $item["label"],

                        "status" => $item["status"],
                        "location" => [
                            "lat" => $item["location"]["lat"],
                            "lng" => $item["location"]["lng"],
                            "address" => $item["location"]["address"],
                            "radius" => $item["location"]["radius"],
                        ],
                        "from" => $item["from"],
                        "to" => $item["to"],
                        "order" => $item["order"]
                    ];
                }, $value["checkpoints"])

            ];
        }, $data);
    }

    static function mapIdTracker($data)
    {
        $tracker_ids = array_map(function ($value) {
            return $value["tracker_id"];
        }, $data);

        $tracker_ids = array_filter($tracker_ids,function($value){
            return $value;
        });

        return array_unique($tracker_ids);
    }

    static function getArrayKeyPositions(string $hash, array $id_trackers)
    {

        $request = new Request([
            "hash" => $hash,
            "trackers" => $id_trackers
        ]);

        $data = json_decode(Tracker::getStatesTrackers($request)->content(), true);

        $positions = [];

        if ($data["success"]) {
            $positions = $data["data"];
        }

        return Functions::convertArrayToObject($positions, "_tracker_id");
    }

    static function setPositionTrackerOnListTask($params)
    {
        $id_trackers = self::mapIdTracker($params["data"]);

        $positions = self::getArrayKeyPositions($params["hash"], $id_trackers);

        return self::mergePositionWithTask($params["data"], $positions);
    }

    static function mergePositionWithTask($data, $positions)
    {
        $arrayData = [];

        foreach ($data as $value) {

            $key = $value["tracker_id"];

            $position = null;

            if (array_key_exists($key, $positions)) {

                $position = $positions[$key];
            }

            $value["_position"] = $position;

            $arrayData[] = $value;
        }

        return $arrayData;
    }

    /**
     * !Se puede optimizar para un solo valor
     */
    static function setEmployeeTrackerOnListTask($params)
    {
        $employees = Functions::getArrayKeyEmployees($params["hash"]);

        return Functions::mergeEmployeeWithData($params["data"], $employees, "tracker_id");
    }

    /**
     * !DELETE TASK ROUTE
     */

    static function deleteTaskRoute(Request $request){

        $URL = Navixy::$URL;

        $response = Http::post("$URL/task/route/delete",[
            "hash"=>$request->input("hash"),
            "route_id"=>$request->input("route_id")
        ]);

        if (!$response->successful()) {
            return Functions::responseErrorDefault($response);
        }

        return sendApiSuccess([], "Ruta de Seguimiento eliminada exitosamente");
    }
}
