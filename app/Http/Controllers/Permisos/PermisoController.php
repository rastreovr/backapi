<?php

namespace App\Http\Controllers\Permisos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\GenericControllerTrait;
use App\Models\RolesPermisos\Permiso;


class PermisoController extends Controller
{
    use GenericControllerTrait {
        store as protected genericStore;
        index as protected genericIndex;
    }

    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
            $request->user()->allowOrFail("PERMISOS", "VIEW_SECTION");
            return $next($request);
        });
    }

    public function model()
    {
        return Permiso::class;
    }

    public function logToBitacora()
    {
        return true;
    }

    public function store(Request $request)
    {
        $request->user()->allowOrFail("PERMISOS", "CREATE");
        $request->validate([
            "nombre" => ["required"],
            "tipo" => ["required"],
            "modulo" => ["required"],
        ]);

        return $this->genericStore($request);
    }


    public function index(Request $request): JsonResponse
    {
        if ($request->has("for_view")) {
            switch ($request->query("for_view")) {
                case "permisos":
                    return $this->indexForPermisos($request);
                default:
                    return sendApiFailure((object) [], "Vista inválida");
            }
        }
        return $this->genericIndex($request);
    }

    public function indexForPermisos(Request $request): JsonResponse
    {
        $found = Permiso::with([
            "roles" => function ($query) {
                $query->select("roles.id", "roles.nombre");
            },
        ])->get();
        return sendApiSuccess($found);
    }
}
