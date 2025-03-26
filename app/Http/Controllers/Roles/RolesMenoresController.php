<?php

namespace App\Http\Controllers\Roles;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Http\Controllers\Controller;
use App\Models\RolesPermisos\Rol;
use App\Models\RolesPermisos\RolesAsignables;


class RolesMenoresController extends Controller
{
    /**
     * Trae directamente el rol, sin intermediario
     */
    public function index(Request $request, int $rolId): JsonResponse
    {
        $found = Rol::where("id", $rolId)
            ->first()
            ->getRolesMenores();

        if ($found) {
            return sendApiSuccess(
                $found,
                "Roles menores obtenidos exitosamente"
            );
        }
        return sendApiFailure([], "Fallo al obtener roles menores");
    }

    /**
     * Trae directamente el rol, sin intermediario
     */
    public function show(
        Request $request,
        int $rolId,
        int $rolMenorId
    ): JsonResponse {
        $found = Rol::whereHas("roles_mayores", function ($query) use ($rolId) {
            return $query->where("mayor_id_rol", $rolId);
        })
            ->where("id", $rolMenorId)
            ->first();

        if ($found) {
            return sendApiSuccess($found, "Rol menor obtenido exitosamente");
        }
        return sendApiFailure((object) [], "Fallo al obtener rol menor");
    }

    public function store(Request $request, int $rolId): JsonResponse
    {
        $request->user()->allowOrFail("ROLES", "UPDATE");

        $request->validate([
            "rolId" => "required",
        ]);

        $rolMenorId = $request->get("rolId");

        $rolCreador = new RolesAsignables([
            "mayor_id_rol" => $rolId,
            "menor_id_rol" => $rolMenorId,
        ]);

        if ($rolCreador->save()) {
            $request
                ->user()
                ->tryCreateLog(
                    "ROLES ASIGNABLES",
                    "CREATE",
                    "Se ha dado permiso al Rol ID {$rolId} de asignar el Rol ID {$rolMenorId}"
                );

            $response = $rolCreador
                ->where("id", $rolCreador->id)
                ->with("menor")
                ->first();
            $response["rol"] = $response["menor"][0];
            unset($response["menor"]);
            return sendApiSuccess(
                $response,
                "Rol creador asignado exitosamente"
            );
        }
        return sendApiFailure((object) [], "Fallo al asignar rol creador");
    }

    /**
     * Solo borra el intermediario, no el rol
     */
    public function destroy(
        Request $request,
        int $rolId,
        int $rolMenorId
    ): JsonResponse {
        $found = RolesAsignables::where([
            ["mayor_id_rol", $rolId],
            ["menor_id_rol", "$rolMenorId"],
        ])->first();

        if ($found) {
            $found->forceDelete();

            $request
                ->user()
                ->tryCreateLog(
                    "ROLES ASIGNABLES",
                    "DELETE",
                    "Se ha quitado el permiso al Rol ID {$rolId} de asignar el Rol Id {$rolMenorId}"
                );

            return sendApiSuccess(
                ["id" => $rolMenorId],
                "Rol menor desligado exitosamente"
            );
        }
        return sendApiFailure((object) [], "Fallo al desligar Rol menor");
    }
}
