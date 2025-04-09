<?php

namespace App\Http\Controllers\PermisosUsuarios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario\User;
use App\Utils\Utils;

class Configuration extends Controller
{

    public static function getPermisos($username)
    {

        $registro = User::where('nombre_usuario', $username)
            ->join('permisos_usuarios_v3', 'usuarios_v3.id', '=', 'permisos_usuarios_v3.id_usuario')
            ->select('usuarios_v3.id', 'usuarios_v3.nombre_usuario', 'permisos_usuarios_v3.*')
            ->first();

        return $registro ? $registro->toArray() : null;
    }
}
