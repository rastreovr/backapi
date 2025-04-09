<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\RolesPermisos\Rol;
use App\Models\RolesPermisos\Permiso;
use App\Utils\Database;
use App\Models\RolesPermisos\PermisosRoles;
use App\Models\Usuario\User;
use App\Models\RolesPermisos\RolesUsuarios;
use Illuminate\Support\Facades\Hash;


class Databasaseeder extends Seeder
{
    /**
     * Run the database seeds.
     * php artisan db:seed --class=Databasaseeder
     * @return void
     */
    public function run()
    {


        /**
         * !CREACION DE ROLES
         */

         Rol::firstOrCreate([
            "id" => 1,
            "nombre" => "superadmin",
        ],[
            "descripcion" => "Administrador que puede manipular auth"
        ]);

        Rol::firstOrCreate([
            "id" => 2,
            "nombre" => "administrador",
        ],[
            "descripcion" => "Administrador regular, no puede manipular auth"
        ]);



        /**
         * !PERMISOS MODULOS
         */
        $modulos = [
            "PERMISOS",
            "ROLES",
            "USUARIOS",
        ];
        $id = 1;

        foreach ($modulos as $modulo) {
            Permiso::firstOrCreate([
                "id" => $id,
                "nombre" => "VIEW_SECTION",
                "tipo" => "ALLOW_NAV",
                "modulo" => $modulo,
            ]);
            $id++;

            $crudPermisos = Database::generatePermisosCrud($modulo);
            foreach ($crudPermisos as $crudObj) {
                Permiso::firstOrCreate($crudObj);
            }

            // Incrementa el ID en 4 porque `generatePermisosCrud` genera 4 permisos más.
            $id += 4;
        }



        /**
         * !ASIGNAR LOS PERMISOS AL ROL "SUPERADMIN"
         */
        // superadmin (todos los permisos existentes)
        Permiso::get()->map(function ($permiso) {
            PermisosRoles::firstOrCreate([
                "id_rol" => 1,
                "id_permiso" => $permiso["id"],
            ]);
        });


        /**
         * !CREAR USUARIO
         */



        User::firstOrCreate([
            "id"=>1,
            "nombre"=> "superadmin",
            "nombre_usuario"=> "superadmin",
            "correo"=> "desarrolladoresct@controlterrestre.com",
            "contrasenia"=> Hash::make("superadmin")
        ]);



        /**
         * !ASIGNAMOS EL ROL "SUPERADMIN" AL USUARIO "SUPERADMIN"
         */
        RolesUsuarios::firstOrCreate([
            "id"=>1,
            "id_usuario"=>1,
            "id_rol"=>1
        ]);

    }
}
