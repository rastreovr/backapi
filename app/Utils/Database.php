<?php

namespace App\Utils;

class Database {

    public static function generatePermisosCrud(string $modulo, bool $allow = true, array $except = []) {
        $crud = ["CREATE", "VIEW", "UPDATE", "DELETE"];
        $crud = array_diff($crud, $except);
        return array_map(function($crudName) use ($modulo, $allow) {
            return [
                "nombre" => $crudName,
                "tipo" => ($allow ? "ALLOW_" : "FORBID_") . "CRUD",
                "modulo" => $modulo
            ];
        }, $crud);

    }
}
