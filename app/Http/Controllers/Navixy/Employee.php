<?php

namespace App\Http\Controllers\Navixy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Employee extends Controller
{

    static function getEmployees(Request $request)
    {

        $hash = $request->input("hash");

        $URL = Navixy::$URL;

        $response = Http::get("$URL/employee/list?hash=$hash");

        if (!$response->successful()) {
            return Functions::responseErrorDefault($response);
        }

        return sendApiSuccess(self::mapEmployee($response->json()["list"]));
    }

    static function getEmployee(Request $request, $id_employee)
    {

        if (!$id_employee) {
            return sendApiFailure([], "ID de empleado no asignado. Comuníquese con soporte para solucionarlo.");
        }

        $hash = $request->input("hash");
        $URL = Navixy::$URL;

        $response = Http::get("$URL/employee/read?hash=$hash&employee_id=$id_employee");

        if (!$response->successful()) {
            return Functions::responseErrorDefault($response, $response->json()["status"]["description"] ?? null);
        }

        return sendApiSuccess(self::mapEmployee(
            [
                $response->json()["value"]
            ]
        )[0]);
    }

    static function mapEmployee(array $data)
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
