<?php

namespace App\Http\Controllers\Navixy;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Navixy extends Controller
{
    static $URL = "https://api.navixy.com/v2";

    static $CREDENTIALS = [
        "user" => "paul.couttolenc@controlterrestre.com",
        "password" => "123456"
        // "user" => "desarrollo@kuktrack.net",
        // "password" => "Whtd76gf_4525"
    ];

    public function login()
    {
        $user = self::$CREDENTIALS["user"];
        $password = self::$CREDENTIALS["password"];
        $URL = self::$URL;


        $response = Http::get("$URL/user/auth?login=$user&password=$password");

        if (!$response->successful()) {
            return $this->responseErrorDefault($response, "Error al iniciar sesión");
        }

        return sendApiSuccess([
            "hash" => $response->json()["hash"]
        ]);
    }

    protected function responseErrorDefault($response, $message = "Fallo al procesar petición")
    {

        $data = $response->json();

        return sendApiFailure([
            "code" => $data["status"]["code"] ?? 0,
            "description" => $data["status"]["description"] ?? "",
        ], $message);
    }

    protected function convertArrayToObject($data, $key)
    {
        $arrayData = [];

        foreach ($data as $value) {
            $arrayData[$value[$key]] = $value;
        }

        return $arrayData;
    }

    /**
     * !TRACKER
     */
    public function getTrackers(Request $request)
    {
        $hash = $request->input("hash");
        $URL = self::$URL;

        $response = Http::get("$URL/tracker/list?hash=$hash");

        if (!$response->successful()) {
            return $this->responseErrorDefault($response);
        }

        return sendApiSuccess($this->mapTrackers($response->json()["list"]));
    }

    public function getTracker(Request $request)
    {
        $hash = $request->input("hash");
        $id_tracker = $request->input("id_tracker");

        $URL = self::$URL;

        $response = Http::get("$URL/tracker/read?hash=$hash&tracker_id=$id_tracker");

        if (!$response->successful()) {
            return $this->responseErrorDefault($response);
        }

        return sendApiSuccess($this->mapTrackers([$response->json()["value"]]));
    }


    protected function mapTrackers(array $data)
    {
        return array_map(function ($value) {
            return [
                "id" => $value["id"],
                "label" => $value["label"],
            ];
        }, $data);
    }


    /**
     * !STATE TRACKER
     */

    public function getStateTracker(Request $request, string $id_tracker)
    {
        $hash = $request->input("hash");
        $URL = self::$URL;

        $response = Http::post("$URL/tracker/get_state", [
            "hash" => $hash,
            "tracker_id" => $id_tracker
        ]);

        if (!$response->successful()) {
            return $this->responseErrorDefault($response, "Error al iniciar sesión");
        }

        $state = $response->json()["state"];
        $state["_tracker_id"] = $id_tracker;

        return sendApiSuccess($this->mapStateTrackers([$state])[0]);
    }

    public function getStatesTrackers(Request $request)
    {

        $hash = $request->input("hash");
        $trackers = $request->input("trackers");

        $URL = self::$URL;

        $response = Http::post("$URL/tracker/get_states", [
            "hash" => $hash,
            "trackers" => $trackers,
            "allow_not_exist" => true,
            "list_blocked" => true
        ]);

        if (!$response->successful()) {
            return $this->responseErrorDefault($response, "Error al traer viajes");
        }

        $states = $this->mergeKeyStateTrackers($response->json()["states"]);

        return sendApiSuccess($this->mapStateTrackers($states));
    }

    protected function mergeKeyStateTrackers($data)
    {

        $arrayData = [];

        foreach ($data as $key => $value) {

            $value["_tracker_id"] = $key;

            $arrayData[] = $value;
        }

        return $arrayData;
    }


    protected function mapStateTrackers($data)
    {
        return array_map(function ($value) {
            return [

                "_tracker_id" => $value["_tracker_id"],

                "gps" => [
                    "updated" => $value["gps"]["updated"],
                    "location" => [
                        "lat" => $value["gps"]["location"]["lat"],
                        "lng" => $value["gps"]["location"]["lng"]
                    ],
                    "speed" => $value["gps"]["speed"]
                ]
            ];
        }, $data);
    }


    /**
     * !BASES
     */

    public function getPlace(Request $request)
    {
        $hash = $request->input("hash");
        $URL = self::$URL;

        $response = Http::get("$URL/place/list?hash=$hash");

        if (!$response->successful()) {
            return $this->responseErrorDefault($response);
        }

        return sendApiSuccess($this->mapPlace($response->json()["list"]));
    }

    protected function mapPlace(array $data)
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

    /**
     * !CREATE TASK ROUTE
     */


    protected function getParamCheckPointStart(array $params)
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

    protected function getParamCheckPointEnd(array $params)
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

    public function createTaskRoute(Request $request)
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

        $URL = self::$URL;

        $params = $this->getParamsTaskRoute([
            "route" => $request->input("route"),
            "tracker" =>  $request->input("tracker"),
            "hash" => $request->input("hash")
        ]);

        $response = Http::post("$URL/task/route/create", $params);

        if (!$response->successful()) {
            return $this->responseErrorDefault($response);
        }

        return sendApiSuccess($params, "Ruta de Seguimiento creada exitosamente");
    }


    protected function getParamsTaskRoute(array $params)
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

                $this->getParamCheckPointStart([
                    "lat" => $route["origin"]["lat"],
                    "lng" => $route["origin"]["lng"],
                    "address" => $route["origin"]["label"],

                    "radius" => $route["origin"]["radius"],
                    "label" => $route["origin"]["label"],
                    "description" => $route["origin"]["description"] ?? "",

                    "from" => $fromCheckPointStart,
                    "to" => $toCheckPointStart
                ]),

                $this->getParamCheckPointEnd([
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
    public function getListTask(Request $request)
    {

        $hash = $request->input("hash");
        $trackers = $request->input("trackers");
        $status_position_trackers = $request->input("status_position");
        $status_employee_trackers = $request->input("status_employee");;

        $URL = self::$URL;

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
            return $this->responseErrorDefault($response);
        }

        $data = $this->mapListTask($response->json()["list"]);

        if ($status_position_trackers) {

            $data = $this->setPositionTrackerOnListTask([
                "hash" => $hash,
                "trackers" => $trackers,
                "data" => $data
            ]);
        }

        if($status_employee_trackers){

            $data = $this->setEmployeeTrackerOnListTask([
                "hash" => $hash,
                "trackers" => $trackers,
                "data" => $data
            ]);

        }

        return sendApiSuccess($data);
    }

    protected function mapListTask(array $data)
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

                "checkpoints" => array_map(function ($item) {
                    return [
                        "id" => $item["id"],
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

    protected function mapIdTracker($data){
        return array_map(function ($value) {
            return $value["tracker_id"];
        }, $data);
    }

    protected function setPositionTrackerOnListTask($params)
    {
        $id_trackers = $this->mapIdTracker($params["data"]);

        $request = new Request([
            "hash" => $params["hash"],
            "trackers" => $id_trackers
        ]);

        $data = json_decode($this->getStatesTrackers($request)->content(), true);

        $positions = [];

        if ($data["success"]) {
            $positions = $data["data"];
        }

        return $this->mergePositionWithTask($params["data"], $positions);
    }



    protected function mergePositionWithTask($data, $positions)
    {

        $positions = $this->convertArrayToObject($positions, "_tracker_id");

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
    protected function setEmployeeTrackerOnListTask($params)
    {
        $request = new Request([
            "hash" => $params["hash"],
        ]);

        $data = json_decode($this->getEmployees($request)->content(), true);


        $employees = [];

        if ($data["success"]) {
            $employees = $data["data"];
        }

        return $this->mergeEmployeeWithTrackerTask($params["data"], $employees);
    }



    protected function mergeEmployeeWithTrackerTask($data, $employees)
    {

        $employees = $this->convertArrayToObject($employees, "tracker_id");

        $arrayData = [];

        foreach ($data as $value) {

            $key = $value["tracker_id"];

            $position = null;

            if (array_key_exists($key, $employees) && $key) {

                $position = $employees[$key];
            }

            $value["_employee"] = $position;

            $arrayData[] = $value;
        }

        return $arrayData;
    }



    /**
     * !EMPLOYEE
     */

    public function getEmployees(Request $request)
    {

        $hash = $request->input("hash");

        $URL = self::$URL;

        $response = Http::get("$URL/employee/list?hash=$hash");

        if (!$response->successful()) {
            return $this->responseErrorDefault($response);
        }

        return sendApiSuccess($this->mapEmployee($response->json()["list"]));
    }

    public function getEmployee(Request $request, $id_employee)
    {

        if (!$id_employee) {
            return sendApiFailure([], "ID de empleado no asignado. Comuníquese con soporte para solucionarlo.");
        }

        $hash = $request->input("hash");
        $URL = self::$URL;

        $response = Http::get("$URL/employee/read?hash=$hash&employee_id=$id_employee");

        if (!$response->successful()) {
            return $this->responseErrorDefault($response, $response->json()["status"]["description"] ?? null);
        }

        return sendApiSuccess($this->mapEmployee(
            [
                $response->json()["value"]
            ]
        )[0]);
    }

    protected function mapEmployee(array $data)
    {
        return array_map(function ($value) {
            return [
                "id" => $value["id"],
                "tracker_id" => $value["tracker_id"],

                "first_name" => $value["first_name"],
                "middle_name" => $value["middle_name"],
                "last_name" => $value["last_name"],
                "email" => $value["email"]
            ];
        }, $data);
    }
}
