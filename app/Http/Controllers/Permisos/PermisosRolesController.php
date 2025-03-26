<?php

namespace App\Http\Controllers\Permisos;

use App\Http\Controllers\Controller;
use App\Models\RolesPermisos\Permiso;
use App\Models\RolesPermisos\PermisosRoles;
use Illuminate\Http\Request;

class PermisosRolesController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
            return $next($request);
        });
    }

    public function index(Request $request, int $rolId)
    {
        $found = Permiso::whereHas("permisos_roles", function ($query) use (
            $rolId
        ) {
            $query->where("id_rol", $rolId);
        })->get();

        if ($found) {
            return sendApiSuccess($found);
        }
        return sendApiFailure();
    }

    public function show(Request $request, int $rolId, int $id)
    {
        $found = Permiso::whereHas("permisos_roles", function ($query) use (
            $rolId,
            $id
        ) {
            $query->where([["id_permiso", $id], ["id_rol", $rolId]]);
        })->first();

        if ($found) {
            return sendApiSuccess($found);
        }
        return sendApiFailure();
    }

    public function store(Request $request, int $rolId)
    {
        $request->user()->allowOrFail("roles", "update");

        $request->validate([
            "permisoId" => ["required"],
        ]);
        $permisoId = $request->get("permisoId");




        /**
         * Validar si los datos ya existen
         */
        $relacionEliminada = PermisosRoles::withTrashed()
            ->where("id_permiso", $permisoId)
            ->where("id_rol", $rolId)
            ->first();


        if ($relacionEliminada) {
            $relacionEliminada->restore();

            $request
                ->user()
                ->tryCreateLog(
                    "ROL",
                    "CREATE",
                    "Ligado el Permiso ID {$permisoId} al Rol ID {$rolId}"
                );

            return sendApiSuccess(
                PermisosRoles::withTrashed()
                    ->where("id_permiso", $permisoId)
                    ->where("id_rol", $rolId)
                    ->first()
            );
        }

        $liga = new PermisosRoles([
            "id_rol" => $rolId,
            "id_permiso" => $permisoId,
        ]);

        if ($liga->save()) {
            $request
                ->user()
                ->tryCreateLog(
                    "ROL",
                    "CREATE",
                    "Ligado el Permiso ID {$permisoId} al Rol ID {$rolId}"
                );

            return sendApiSuccess($liga, "Permiso ligado a Rol exitosamente");
        }
        return sendApiFailure((object) [], "Fallo al ligar Permiso a Rol");
    }

    public function destroy(Request $request, int $rolId, int $id)
    {
        $permisoRol = PermisosRoles::where([
            ["id_permiso", $id],
            ["id_rol", $rolId],
        ])->first();

        if ($permisoRol) {
            $permisoRol->forceDelete();

            $request
                ->user()
                ->tryCreateLog(
                    "ROL",
                    "DELETE",
                    "Desligado el Permiso ID {$id} del Rol ID {$rolId}"
                );

            return sendApiSuccess(
                $permisoRol,
                "Permiso desligado exitosamente"
            );
        }
        return sendApiFailure((object) [], "Fallo al desligar Permiso");
    }
}
