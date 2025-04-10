<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\GenericControllerTrait;
use App\Models\Usuario\User;
use App\Models\Bitacora_v3;
use App\Models\RolesPermisos\RolesUsuarios;
use App\Utils\Utils;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UsuarioController extends Controller
{
    use GenericControllerTrait {
        index as protected genericIndex;
        show as protected genericShow;
        update as protected genericUpdate;
    }

    protected function model()
    {
        return User::class;
    }

    protected function modelName()
    {
        return "Usuario";
    }

    protected function index(Request $request): JsonResponse
    {
        if ($request->has("for_view") && $request->query("for_view") != "me") {
            $request->user()->allowOrFail("USUARIOS", "VIEW");
        }


        if ($request->has("for_view")) {
            switch ($request->query("for_view")) {
                case "roles":
                    /**
                     * Trae los usuarios con sus roles
                     */
                    return $this->indexForRoles($request);
                case "me":
                    /**
                     * Trae solamente mi usuario
                     */
                    return $this->indexForMe($request);
                default:
                    return sendApiFailure((object) [], "Vista inválida");
            }
        }
        /**
         * Trae todo los usarios
         */
        return $this->genericIndex($request);
    }

    protected function indexForRoles(Request $request): JsonResponse
    {
        $found = User::with("roles")->get();

        if ($found) {
            return sendApiSuccess($found, "Usuarios obtenidos exitosamente");
        }
        return sendApiFailure([], "Fallo al obtener Usuarios");
    }

    protected function indexForMe(Request $request): JsonResponse
    {
        $found = $request
            ->user()
            ->with([
                "roles" => function ($query) {
                    $query->select("roles.id", "roles.nombre");
                },
            ])
            ->first();

        if ($found) {
            return sendApiSuccess($found);
        }
        return sendApiFailure((object) []);
    }

    protected function show(Request $request, int $id): JsonResponse
    {
        $request->user()->allowOrFail("USUARIOS", "VIEW");

        $found = User::where(
            "id",
            "=",
            $id
        )
            ->with("roles")
            ->first()->toArray();

        return sendApiSuccess(
            $found,
            "Obtenidos los datos de '{$this->modelName()}' exitosamente"
        );
    }

    protected function update(Request $request): JsonResponse
    {

        $request->user()->allowOrFail("USUARIOS", "UPDATE");

        $id = $request->id;

        if (!$id) {
            return sendApiFailure(
                (object) [],
                "Parametro Id requerido",
                200
            );
        }

        $usuario = $this->model()::find($id);

        if (!$usuario) {
            return sendApiFailure(
                (object) [],
                "Usuario no encontrado",
                200
            );
        }

        $request->validate([
            // no funciona si la request no tiene header accept: application/json
            "nombre" => ["required", "string"],
            "correo" => ["required", "string", "email"],
            "estatus" => ["required", "int"],
            "tipo" => ["required", "int"],

        ]);

        $usuario->nombre = $request->nombre;
        $usuario->correo = $request->correo;
        $usuario->estatus = $request->estatus;
        $usuario->tipo = $request->tipo;

        $usuario->id_employee_navixy = $request->id_employee_navixy ?? 0;

        if ($request->contrasenia) {

            $request->validate([
                "contrasenia" => ["required", "string", "min:4"],
            ]);

            $usuario->contrasenia = Hash::make($request->contrasenia);
        }


        $bit = sendApiSuccess([
            "id" => $id,
        ]);

        $request
            ->user()
            ->tryLogToBitacora(
                $bit,
                "USUARIOS",
                "UPDATE"
            );

        $data = $usuario->save();

        return sendApiSuccess(
            $data,
            "Datos actualizados exitosamente"
        );
    }

    protected function bitacora(Request $request, int $id): JsonResponse
    {
        $request->user()->allowOrFail("USUARIOS", "VIEW");

        $data = Bitacora_v3::where("id_usuario", $id)->get()->toArray();

        return sendApiSuccess(
            $data,
            "Obtenidos los datos de '{$this->modelName()}' exitosamente"
        );
    }
    public function getFotoPerfil($id)
    {
        $nombreArchivo = User::selectRaw("foto AS nombreArchivoCompleto")
            ->where('id', $id)
            ->value('nombreArchivoCompleto');
        $ruta = 'usuarios/perfil/' . $nombreArchivo;
        return Utils::getArchivoGeneral($ruta);
    }
    public function getFotoUserName($user)
    {
        $nombreArchivo = User::selectRaw("foto AS nombreArchivoCompleto")
            ->where('nombre_usuario', $user)
            ->value('nombreArchivoCompleto');
        $ruta = 'usuarios/perfil/' . $nombreArchivo;
        return Utils::getArchivoGeneral($ruta);
    }
    public function verifyUser(Request $request): JsonResponse
    {
        try {
            // Intentar autenticar al usuario con el token proporcionado
            $user = JWTAuth::parseToken()->authenticate();
            // print_r($user);
            // Verificar si el usuario está activo
            if (!$user->estatus) {
                return sendApiFailure((object)[], "Usuario Inactivo");
            }

            // Obtener roles y permisos del usuario
            $user->load('roles');
            $user->permisos = $user->getPermisos();
            $user->user = $user->nombre_usuario;
            $user->role = "GENERAL";
            // Devolver datos del usuario
            $result = sendApiSuccess($user, "Token verificado exitosamente");

            return $result;
        } catch (JWTException $e) {
            // Manejar diferentes excepciones de JWT
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return sendApiFailure((object)[], "Token expirado");
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return sendApiFailure((object)[], "Token inválido");
            } else {
                return sendApiFailure((object)[], "Token no proporcionado");
            }
        }
    }
}
