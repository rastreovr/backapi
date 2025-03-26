<?php

namespace App\Http\Controllers\Usuario;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\RolesPermisos\Rol;
use App\Models\RolesPermisos\RolesUsuarios;


class RolesUsuariosController extends Controller
{
    public function index(Request $request, int $usuarioId): JsonResponse
    {
        $roles = Rol::whereHas("roles_usuarios", function ($query) use (
            $usuarioId
        ) {
            $query->where("id_usuario", $usuarioId);
        })->get();

        if ($roles) {
            return sendApiSuccess($roles);
        }
        return sendApiFailure([]);
    }

    public function show(
        Request $request,
        int $usuarioId,
        int $id
    ): JsonResponse {
        $rol = Rol::whereHas("roles_usuarios", function ($query) use (
            $usuarioId
        ) {
            $query->where("id_usuario", $usuarioId);
        })
            ->where("id", $id)
            ->first();

        if ($rol) {
            return sendApiSuccess($rol);
        }
        return sendApiFailure((object) [], "Rol no encontrado");
    }

    /**
     * Liga un rol a un usuario
     */
    public function store(Request $request, int $usuarioId): JsonResponse
    {
        $request->validate([
            "id_rol" => "required",
        ]);

        $rolId = $request->all()["id_rol"];


        // // Buscar la relación incluso si ha sido eliminada suavemente
        $relacionEliminada = RolesUsuarios::withTrashed()
            ->where("id_usuario", $usuarioId)
            ->where("id_rol", $rolId)
            ->first();

        if ($relacionEliminada) {
            $relacionEliminada->restore();
            $request
                ->user()
                ->tryCreateLog(
                    "USUARIOS",
                    "CREATE",
                    "Ligado el Rol ID {$rolId} al Usuario ID {$usuarioId}, Nuevamente"
                );

            return sendApiSuccess(RolesUsuarios::with("rol")->first());
        }

        $pivot = new RolesUsuarios([
            "id_rol" => $rolId,
            "id_usuario" => $usuarioId,
        ]);

        if ($pivot->save()) {
            $request
                ->user()
                ->tryCreateLog(
                    "USUARIOS",
                    "CREATE",
                    "Ligado el Rol ID {$rolId} al Usuario ID {$usuarioId}"
                );
            return sendApiSuccess($pivot->with("rol")->first());
        }
        return sendApiFailure((object) []);
    }

    /**
     * ?Elimina el rol al usuario:
     * ?DELETE /usuarios/{usuarioId}/roles/{rolId}
     */
    public function destroy(
        Request $request,
        int $usuarioId,
        int $id
    ): JsonResponse {
        if ($request->has("force")) {
            $pivot = RolesUsuarios::withTrashed()
                ->where([["id_usuario", $usuarioId], ["id_rol", $id]])
                ->first();
            if ($pivot && $pivot->forceDelete()) {
                $request
                    ->user()
                    ->tryCreateLog(
                        "USUARIOS",
                        "DELETE",
                        "Desligado el Rol ID {$id} del Usuario ID {$usuarioId}"
                    );
                return sendApiSuccess((object) []);
            }
        } else {
            $pivot = RolesUsuarios::where([
                ["id_usuario", $usuarioId],
                ["id_rol", $id],
            ])->first();
            if ($pivot && $pivot->delete()) {
                $request
                    ->user()
                    ->tryCreateLog(
                        "USUARIOS",
                        "DELETE",
                        "Desligado el Rol ID {$id} del Usuario ID {$usuarioId}"
                    );
                return sendApiSuccess((object) []);
            }
        }

        return sendApiFailure((object) []);
    }
}
