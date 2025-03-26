<?php

namespace App\Utils\Casts;

use Exception;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;


class ColumnsJson implements CastsAttributes {

    public function get($model, $key, $value, $attributes) {
        try {
            $obj = json_decode($value, true);
            if ($obj == null) {
                return [];
            }
            return $obj;
        } catch(Exception $e) {
            return [];
        }

    }

    public function set($model, $key, $value, $attributes) {
        return json_encode($value);
    }
}
