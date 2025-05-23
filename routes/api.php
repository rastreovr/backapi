<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/**
 * !USUARIO
 */

use App\Http\Controllers\Usuario\SignUpController;
use App\Http\Controllers\Usuario\LoginController;
use App\Http\Controllers\Usuario\UsuarioController;
use App\Http\Controllers\Usuario\RolesUsuariosController;

/**
 * !PERMISOS
 */

use App\Http\Controllers\Permisos\PermisoController;
use App\Http\Controllers\Permisos\PermisosRolesController;


/**
 * !ROLES
 */

use App\Http\Controllers\Me\MeRolesController;
use App\Http\Controllers\Roles\RolController;
use App\Http\Controllers\Roles\RolesMenoresController;

/**
 * !NAVIXY
 */
use App\Http\Controllers\Navixy\Navixy;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::group(["prefix" => "v1"], function () {
    Route::middleware(["jwt.authCustom"])->group(function () {
        Route::get("/health/auth", function (Request $request) {
            $user = $request->user();
            return sendApiSuccess(
                ["status" => "ok"],
                "Server online, You are authenticated"
            );
        });


        /**
         * !Modulo Usuarios,Roles y Permisos
         */

        Route::prefix('usuario')->group(function () {

            Route::get('all', [UsuarioController::class, "index"]);
            Route::get('id/{id}', [UsuarioController::class, "show"]);
            Route::put('update', [UsuarioController::class, "update"]);
            Route::get('bitacora/{id}', [UsuarioController::class, "bitacora"]);
        });

        Route::resource("usuarios.roles", RolesUsuariosController::class);

        Route::post("auth/sign-up", SignUpController::class);
        Route::get("auth/me", [UsuarioController::class, "verifyUser"]);

        Route::resource("me/roles", MeRolesController::class)->name("index", "me.roles.index");
        Route::resource("roles", RolController::class);

        Route::put('rol/descripcion', [RolController::class, "updateDescription"]);


        Route::resource("permisos", PermisoController::class);
        Route::resource("usuarios", UsuarioController::class);
        Route::resource("roles.menores", RolesMenoresController::class);
        Route::resource("roles.permisos", PermisosRolesController::class);
    });


    Route::prefix('navixy')->group(function () {


        Route::get("login", [Navixy::class,"login"]);


        Route::prefix('tracker')->group(function () {

            Route::get("list", [Navixy::class,"getTrackers"]);

            Route::get("read", [Navixy::class,"getTracker"]);

            Route::get("get_state/{id_tracker}", [Navixy::class,"getStateTracker"]);

            Route::post("get_states", [Navixy::class,"getStatesTrackers"]);

        });


        Route::prefix('place')->group(function () {

            Route::get("list", [Navixy::class,"getPlace"]);

        });


        Route::prefix('task')->group(function () {

            Route::prefix('route')->group(function () {

                Route::post("create", [Navixy::class,"createTaskRoute"]);

                Route::post("delete", [Navixy::class,"deleteTaskRoute"]);

            });

            Route::get("list", [Navixy::class,"getListTask"]);
            Route::post("list", [Navixy::class,"getListTask"]);

        });


        Route::prefix('employee')->group(function () {

            Route::get("list", [Navixy::class,"getEmployees"]);

            Route::get("read/{id}", [Navixy::class,"getEmployee"]);

        });

    });

    //! Termina if para apis que necesitan SESSION



    Route::post("auth/login", LoginController::class);


    Route::prefix('usuarios')->group(function () {

        Route::get('perfil-imagen/{id}', [UsuarioController::class, 'getFotoPerfil']);
        Route::get('perfil-imagen/username/{user}', [UsuarioController::class, 'getFotoUserName']);
    });





    Route::get("testing", function (Request $request) {
        return [
            "ok" => "si funciono",
        ];
    });
});

Route::group(
    ["middleware" => "cors.custom"],
    function () {}
);
