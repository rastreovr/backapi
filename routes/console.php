<?php

use App\Models\PermisosRoles;
use App\Models\Rol;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


/**
 * Clona los permisos del rol from al rol to, con un create
 */
Artisan::command('cloneRole {from} {to}', function ($from, $to) {

    $rolFrom = Rol::where("id", $from)->first();

    if (!$rolFrom) {
        throw new Exception("El rol fuente no existe");
    }

    $rolTo = Rol::where("id", $to)->first();

    if (!$rolTo) {
        throw new Exception("El rol destino no existe");
    }

    $permisosFrom = $rolFrom->permisos->map(function ($permiso) use ($rolTo) {
        return [
            "id_permiso" => $permiso["id"],
            "id_rol" => $rolTo["id"]
        ];
    })->values()->toArray();


    PermisosRoles::insertOrIgnore($permisosFrom);

    echo "Permisos de Rol-{$from} clonados a Rol-{$to}";


});
