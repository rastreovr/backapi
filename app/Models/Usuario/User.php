<?php

namespace App\Models\Usuario;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;

use App\Models\Bitacora_v3;
use App\Models\RolesPermisos\Rol;
use App\Models\RolesPermisos\RolesUsuarios;
use App\Models\RolesPermisos\Permiso;
use App\Utils\Exceptions\UnauthorizedApiException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ramsey\Uuid\Type\Integer;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable,SoftDeletes;

    /**
     * Agregado para cambiar el nombre de la tabla
     */
    protected $table = 'usuarios_v3';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [
    //     'name', 'email', 'password',
    // ];
    protected $guarded = ["id"];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function getAuthPassword()
    {
        return $this->contrasenia;
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    /**
     * Inicia apartado de roles y permisos
     */
    public function roles()
    {
        return $this->hasManyThrough(
            Rol::class,
            RolesUsuarios::class,
            "id_usuario",
            "id",
            "id",
            "id_rol"
        );
    }
    //Implementacion Bitacora_v3


    public function bitacoras()
    {
        return $this->hasMany(Bitacora_v3::class, "id_usuario");
    }

    /**
     * Crea un registro de log para bitácora para este usuario en particular.
     *
     * @param JsonResponse $response Se asume que siempre tendrá success y data (generada por sendApi)
     * y que data contiene un campo de id
     * @param string $modulo Nombre del módulo
     * @param string $accion: CREATE, UPDATE, DELETE, LOGIN. Alguna de esas
     * @param string $comentario Comewntario del Log de bitácora
     * @return bool true si todo salió  bien con la creación del Log de bitácora
     */
    public function tryLogToBitacoraSimple(
        JsonResponse $response,
        string $modulo,
        string $accion,
        string $comentario = null
    ) {
        // devolver excepcion especial?
        if ($response->getOriginalContent()["success"]) {
            $idUsuario = $this->id;

            if ($accion == "CREATE") {
                $entidadId = $response->getOriginalContent()["data"]["id"];
                $comentario = $comentario ?? "Creado con ID " . $entidadId;
            }

            if ($accion == "UPDATE") {
                $entidadId = $response->getOriginalContent()["data"]["id"];
                $comentario = $comentario ?? "Actualizado con ID " . $entidadId;
            }

            if ($accion == "DELETE") {
                $entidadId = $response->getOriginalContent()["data"]["id"];
                $comentario = $comentario ?? "Eliminado con ID " . $entidadId;
            }

            if ($accion == "LOGIN") {
                $entidadId = $response->getOriginalContent()["data"]["id"];
                $comentario =
                    $comentario ?? "Log-in de usuario con ID " . $entidadId;
            }

            $bitacora = new Bitacora_v3([
                "id_usuario" => $idUsuario,
                "modulo" => $modulo,
                "accion" => $accion,
                "comentario" => $comentario,
            ]);

            return $bitacora->save();
        }
        return false; // throw
    }
    /**
     * Función que se usa en GenericController para logs automáticos a bitacora
     * @param \Illuminate\Http\JsonResponse $response
     * @param string $modulo
     * @param string $accion
     * @param string $comentario
     * @return bool
     */
    public function tryLogToBitacora(
        JsonResponse $response,
        string $modulo,
        string $accion,
        string $comentario = null,
        int $id_usuario = null
    ) {
        $entidadId = $response->getOriginalContent()["data"]["id"] ?? $id_usuario;
        $idUsuario = $this->id;
        $nombreUsuario = $this->nombre_usuario;
        $id_odt = $response->getOriginalContent()["data"]["id_odt"] ?? NULL;

        $comentario =
            $comentario ??
            "USUARIO {$nombreUsuario} realizó {$accion} en módulo {$modulo}, en registro con ID {$entidadId} ";
        if ($response->getOriginalContent()["success"]) {
            $bitacora = new Bitacora_v3([
                "id_usuario" => $idUsuario,
                "id_odt" => $id_odt,
                "modulo" => $modulo,
                "accion" => $accion,
                "comentario" => $comentario,
            ]);

            return $bitacora->save();
        }
        return false;
    }

    /**
     * Función para logs manuales a bitácora, para evitar
     * importar el modelo Bitácora. Útil para Controladores de enlace entre modelos
     * @param string $modulo Nombre del módulo
     * @param string $comentario Comentario del log
     * @return bool
     */
    public function tryCreateLog(
        string $modulo,
        string $accion,
        string $comentario
    ) {
        $idUsuario = $this->id;

        $bitacora = new Bitacora_v3([
            "id_usuario" => $idUsuario,
            "modulo" => $modulo,
            "accion" => $accion,
            "comentario" => $comentario,
        ]);

        return $bitacora->save();
    }


    public function getPermisos()
    {
        $roles = RolesUsuarios::where("id_usuario", $this->id)
            ->get("id_rol")
            ->map(function ($rol) {
                return $rol->id_rol;
            });

        $permisos = Permiso::whereHas("permisos_roles", function ($query) use (
            $roles
        ) {
            $query->whereIn("id_rol", $roles);
        })->get();

        return $permisos;
    }
        /**
     * Undocumented function
     *
     * @param string $modulo
     * @param string $permiso
     * @return void
     */
    public function allow(string $modulo, string $permiso)
    {
        $modulo = strtoupper($modulo);
        $permiso = strtoupper($permiso);

        $allowed = $this->with([
            "roles.permisos" => function ($query) use ($modulo, $permiso) {
                $query->where("nombre", $permiso);
                $query->where("modulo", $modulo);
            },
        ])
            ->where("id", $this->id)
            ->get();

        $permisos = $allowed
            ->pluck("roles")
            ->flatten()
            ->pluck("permisos")
            ->flatten();
        $denies = $permisos->flatMap(function ($permisoObj) {
            if (
                strpos(
                    strtolower($permisoObj["tipo"]),
                    strtolower("FORBID")
                ) !== false
            ) {
                return [$permisoObj];
            }
            return [];
        });

        if (count($denies) > 0) {
            return false;
        }
        return count($permisos) > 0;
    }
       /**
     * Undocumented function
     *
     * @param string $modulo
     * @param string $permiso
     * @return void
     */
    public function allowOrFail(string $modulo, string $permiso)
    {
        if ($this->allow($modulo, $permiso)) {
            return true;
        }
        throw new UnauthorizedApiException();
        // return $this->autenticar($modulo, $permiso) ? true : (throw new UnauthorizedException());
    }

    public function hasRole(string $rolName)
    {
        $userId = $this->id;

        $rolId = Rol::where([["nombre", $rolName]])->first()["id"];

        $found = RolesUsuarios::where([
            ["id_usuario", $userId],
            ["id_rol", $rolId],
        ])->get();

        return count($found) > 0;
    }

    public static function name()
    {
        return static::$table;
    }


    // attributes de permisos de cobro y pago
    public function getVerPreciosPagoAttribute() {
        return $this->allow("GENERAL", "VER_PRECIOS_PAGO");
    }

    public function getVerPreciosCobroAttribute() {
        return $this->allow("GENERAL", "VER_PRECIOS_COBRO");
    }

}
