<?php

namespace App\Http\Controllers\Navixy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Tracker extends Controller
{

    /**
     * !TRACKER
     */
    static function getTrackers(Request $request)
    {
        $hash = $request->input("hash");
        $URL = Navixy::$URL;
        $status_employee_trackers = $request->input("status_employee");;

        $response = Http::get("$URL/tracker/list?hash=$hash");

        if (!$response->successful()) {
            return Functions::responseErrorDefault($response);
        }

        $data = self::mapTrackers($response->json()["list"]);

        if ($status_employee_trackers) {

            $data = self::setEmployeeTrackerOnListTracker([
                "hash" => $hash,
                "data" => $data
            ]);
        }



        return sendApiSuccess($data);
    }

    static function setEmployeeTrackerOnListTracker($params)
    {
        $employees = Functions::getArrayKeyEmployees($params["hash"]);

        return Functions::mergeEmployeeWithData($params["data"], $employees, "id");
    }

    static function getTracker(Request $request)
    {
        $hash = $request->input("hash");
        $id_tracker = $request->input("id_tracker");
        $URL = Navixy::$URL;
        $status_employee_trackers = $request->input("status_employee");;


        $response = Http::get("$URL/tracker/read?hash=$hash&tracker_id=$id_tracker");

        if (!$response->successful()) {
            return Functions::responseErrorDefault($response);
        }

        $data = self::mapTrackers([$response->json()["value"]]);

        /**
         * !NOTA: OPTIMIZAR
         */
        if ($status_employee_trackers) {

            $data = self::setEmployeeTrackerOnListTracker([
                "hash" => $hash,
                "data" => $data
            ]);
        }


        return sendApiSuccess($data);
    }

    static function mapTrackers(array $data)
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

    static function getStateTracker(Request $request, string $id_tracker)
    {
        $hash = $request->input("hash");
        $URL = Navixy::$URL;

        $response = Http::post("$URL/tracker/get_state", [
            "hash" => $hash,
            "tracker_id" => $id_tracker
        ]);

        if (!$response->successful()) {
            return Functions::responseErrorDefault($response, "Error al iniciar sesión");
        }

        $state = $response->json()["state"];
        $state["_tracker_id"] = $id_tracker;

        return sendApiSuccess(self::mapStateTrackers([$state])[0]);
    }

    static function getStatesTrackers(Request $request)
    {
        $hash = $request->input("hash");
        $trackers = $request->input("trackers");

        $URL = Navixy::$URL;

        $response = Http::post("$URL/tracker/get_states", [
            "hash" => $hash,
            "trackers" => $trackers,
            "allow_not_exist" => true,
            "list_blocked" => true
        ]);

        if (!$response->successful()) {
            return Functions::responseErrorDefault($response, "Error al traer viajes");
        }

        $states = self::mergeKeyStateTrackers($response->json()["states"]);

        return sendApiSuccess(self::mapStateTrackers($states));
    }

    static function mergeKeyStateTrackers($data)
    {

        $arrayData = [];

        foreach ($data as $key => $value) {

            $value["_tracker_id"] = $key;

            $arrayData[] = $value;
        }

        return $arrayData;
    }

    static function mapStateTrackers($data)
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
}
