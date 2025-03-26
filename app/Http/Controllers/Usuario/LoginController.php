<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Usuario\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $params = $request->only(["usuario", "password"]);
        $credentials = $request->validate([
            "password" => ["required"],
            "usuario" => ["required"],
        ]);

        $credentials = [
            "password" => $params["password"],
            "nombre_usuario" => $params["usuario"],
        ];

        $found = $found = User::where(
            "nombre_usuario",
            "=",
            $params["usuario"]
        )
            ->with("roles")
            ->first();

        if (!$found) {
            error_log("was not found");
            return sendApiFailure((object) [], "Usuario no reconocido");
        }

        $token = JWTAuth::attempt($credentials);
        if ($token) {
            $found["token"] = $token;
            $found["permisos"] = $found->getPermisos();
            $found["user"] = $params["usuario"];
            $found["role"] = "GENERAL";

            if (!$found->estatus) {
                return sendApiFailure((object) [], "Usuario Inactivo");
            }

            $result = sendApiSuccess($found, "Identificado exitosamente");

            return $result;
        }

        return sendApiFailure((object) [], "Contraseña incorrecta");
    }
}
