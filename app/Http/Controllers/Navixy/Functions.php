<?php

namespace App\Http\Controllers\Navixy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Functions extends Controller
{
    static function responseErrorDefault($response, $message = "Fallo al procesar petición")
    {

        $data = $response->json();

        return sendApiFailure([
            "code" => $data["status"]["code"] ?? 0,
            "description" => $data["status"]["description"] ?? "",
        ], $message);
    }

    static function convertArrayToObject($data, $key)
    {
        $arrayData = [];

        foreach ($data as $value) {
            $arrayData[$value[$key]] = $value;
        }

        return $arrayData;
    }

    static function getArrayKeyEmployees($hash)
    {
        $request = new Request([
            "hash" => $hash,
        ]);

        $data = json_decode(Employee::getEmployees($request)->content(), true);

        $employees = [];

        if ($data["success"]) {
            $employees = $data["data"];
        }

        return self::convertArrayToObject($employees, "tracker_id");
    }

    static function mergeEmployeeWithData($data, $employees, $key_data)
    {
        $arrayData = [];

        foreach ($data as $value) {

            $key = $value[$key_data];

            $position = null;

            if (array_key_exists($key, $employees) && $key) {

                $position = $employees[$key];
            }

            $value["_employee"] = $position;

            $arrayData[] = $value;
        }

        return $arrayData;
    }
}
