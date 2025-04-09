<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Models\Usuario\User;
use Illuminate\Http\JsonResponse;

class SignUpController extends Controller
{
    public function __invoke(Request $request)
    {

        $request->validate([
            // no funciona si la request no tiene header accept: application/json
            "correo" => ["required", "string", "email"], // check regex js ?
            "nombre_usuario" => ["required", "string", "min:4", "max:50"],
            "contrasenia" => ["required", "string", "min:4"],
            "nombre" => ["required", "string"],
            "roles" => ["nullable", "array"],
        ]);

        $usuario = User::create([
            "correo" => $request->correo,
            "nombre_usuario" => $request->nombre_usuario,
            "nombre" => $request->get('nombre'),
            "contrasenia" => Hash::make($request->contrasenia),
            "tipo" => $request->tipo ?? 0
        ]);


        $bit = sendApiSuccess([
            "id" => $usuario->id,
        ]);


        $request
            ->user()
            ->tryLogToBitacora(
                $bit,
                "USUARIOS",
                "CREATE"
            );


        return sendApiSuccess($usuario, "Usuario registrado exitosamente");
    }
}
