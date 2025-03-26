<?php

namespace App\Http\Controllers\Roles;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Controllers\GenericControllerTrait;
use App\Models\RolesPermisos\Rol;

use Illuminate\Http\JsonResponse;

// Roles que no pueden ser borrados
const GUARDED_ROLE_IDS = [1, 2]; // superadmin, admin

class RolController extends Controller
{
    use GenericControllerTrait {
        store as protected genericStore;
        index as protected genericIndex;
        show as protected genericShow;
        destroy as protected genericDestroy;
    }

    /*
    public function __construct(Request $request) {
        $this->middleware(function ($request, $next) {
            JWTAuth::parseToken()->authenticate(); // auth carga el user a al request,
            // se puede usar en el middlware del controlador general o en rutas especificas
            $request->user()->allowOrFail("roles", "all");
            return $next($request);
        });
    }
    */

    public function model()
    {
        return Rol::class;
    }

    public function logToBitacora()
    {
        return true;
    }

    /**
     * ?Crea un nuevo Rol
     * ?Url:http://127.0.0.1:8000/api/v1/roles
     * ?Method:Post
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            "nombre" => ["required", "string"],
            "descripcion" => ["required", "string"],
        ]);

        return $this->genericStore($request);
    }


    public function updateDescription(Request $request)
    {
        $request->validate([
            "descripcion" => "required",
            "id_rol" => "required"
        ]);

        $idRol = $request->input("id_rol");

        if (!Rol::find($idRol)) {
            return sendApiFailure(
                [],
                "El Rol no existe",
                200
            );
        }

        $data = [
            "descripcion" => $request->input("descripcion")
        ];

        $response = Rol::where("id", $idRol)->update($data);


        return sendApiSuccess(
            $response,
            "descripción actualizada correctamente"
        );
    }

    /**
     * ?Si trae el parametro for_view=roles, Trae los roles que el usuario puede asignar
     * ?Si no trae el parametro solo el rol del usuario
     * ?Ejemplo de liga:
     * ?http://127.0.0.1:8000/api/v1/roles/8?for_view=roles
     */
    public function index(Request $request): JsonResponse
    {
        $request->user()->allowOrFail("ROLES", "VIEW");
        if ($request->has("for_view")) {
            switch ($request->query("for_view")) {
                case "roles":
                    return $this->indexForRoles($request);
                default:
                    return sendApiFailure((object) [], "Vista inválida");
            }
        }
        return $this->genericIndex($request);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        if ($request->has("for_view")) {
            switch ($request->query("for_view")) {
                case "roles":
                    return $this->showForRoles($request, $id);
                default:
                    return sendApiFailure((object) [], "Vista inválida");
            }
        }
        return $this->genericShow($request, $id);
    }

    public function indexForRoles(Request $request): JsonResponse
    {
        $found = Rol::with("permisos")
            ->with([
                "roles_usuarios" => function ($query) {
                    $query->select("id_rol");
                },
            ])
            ->get();

        return sendApiSuccess($found, "Roles obtenidos exitosamente");
    }

    public function showForRoles(Request $request, int $id): JsonResponse
    {
        $found = Rol::with("permisos")
            ->where("id", $id)
            ->first();
        if ($found) {
            $found["rolesMenores"] = $found->getRolesMenores();
            return sendApiSuccess($found, "Rol obtenido exitosamente");
        }
        return sendApiFailure((object) [], "Fallo al obtener Rol");
    }

    /**
     * ?Eliminar un rol en especifico
     * ?url:http://127.0.0.1:8000/api/v1/roles/13
     * ?Method: DELETE
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        if (in_array($id, GUARDED_ROLE_IDS)) {
            return sendApiFailure(
                (object) [],
                "No se puede borrar un rol protegido"
            );
        }
        return $this->genericDestroy($request, $id);
    }
}
