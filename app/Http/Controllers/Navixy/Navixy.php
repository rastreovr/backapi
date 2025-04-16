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
            return Functions::responseErrorDefault($response, "Error al iniciar sesión");
        }

        return sendApiSuccess([
            "hash" => $response->json()["hash"]
        ]);
    }

    public function getTrackers(Request $request)
    {
        return Tracker::getTrackers($request);
    }

    public function getTracker(Request $request)
    {
        return Tracker::getTracker($request);
    }

    public function getStateTracker(Request $request, string $id_tracker)
    {
        return Tracker::getStateTracker($request, $id_tracker);
    }

    public function getStatesTrackers(Request $request)
    {
        return Tracker::getStatesTrackers($request);
    }

    public function createTaskRoute(Request $request)
    {
        return Task::createTaskRoute($request);
    }

    public function deleteTaskRoute(Request $request){
        return Task::deleteTaskRoute($request);
    }

    public function getListTask(Request $request)
    {
        return Task::getListTask($request);
    }

    public function getEmployees(Request $request)
    {
        return Employee::getEmployees($request);
    }

    public function getEmployee(Request $request, $id_employee)
    {
        return Employee::getEmployee($request, $id_employee);
    }

    public function getPlace(Request $request)
    {
        return Place::getPlace($request);
    }
}
