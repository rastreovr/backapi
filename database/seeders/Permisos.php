<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RolesPermisos\Permiso;

class Permisos extends Seeder
{
    /**
     * Run the database seeds.
        php artisan db:seed --class=Permisos
     * @return void
     */
    public function run()
    {

        Permiso::firstOrCreate([
            "nombre" => "VIEW_SECTION",
            "tipo" => "ALLOW_NAV",
            "modulo" => "SEGUIMIENTO_UNIDAD",
        ]);
        Permiso::firstOrCreate([
            "nombre" => "VIEW_SECTION",
            "tipo" => "ALLOW_NAV",
            "modulo" => "VIAJES",
        ]);


    }
}
