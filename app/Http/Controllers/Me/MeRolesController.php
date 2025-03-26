<?php

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use App\Models\RolesPermisos\Rol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeRolesController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        /**
         * Si tiene el parametro ?menores
         * Trae los roles que puede asignar
         * Si no trae parametro trae sus roles
         */
        if ($request->has("menores")) {
            return $this->indexForMenores($request);
        }
        return $this->regularIndex($request);
    }

    /**
     * Trae los roles del usuario
     */
    public function regularIndex(Request $request): JsonResponse
    {
        $user = $request->user();

        $found = $user
            ->with("roles")
            ->where("id", $user->id)
            ->first()["roles"];

        if ($found) {
            return sendApiSuccess(
                $found,
                "Roles propios obtenidos exitosamente"
            );
        }
    }

    /**
     * Trae los roles que el usuario puede asignar,
     * Ejemplo: Si es superAdmin puede asignar todo los roles,
     * Pero si es coordinador solo puede asignar ciertos roles (La jerarquia de los roles esta en la tabla "roles_asignables_v3")
     */
    public function indexForMenores(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->hasRole("superadmin")) {
            // superadmin puede asignar todos los roles ever
            return sendApiSuccess(
                Rol::get(),
                "Roles menores obtenidos exitosamente"
            );
        }

        $roles = $user
            ->with("roles")
            ->where("id", $user->id)
            ->first()["roles"]->map(function ($rol) {
            return $rol->getRolesMenores();
        })
            ->flatten();

        if ($roles) {
            return sendApiSuccess(
                $roles,
                "Roles Menores obtenidos exitosamente"
            );
        }
        return sendApiFailure($roles, "Fallo al obtener Roles Menores");
    }
}
