<?php

namespace App\Models\RolesPermisos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\RolesPermisos\PermisosRoles;
use App\Models\RolesPermisos\Rol;
use App\Models\Usuario\User;
use App\Models\RolesPermisos\RolesUsuarios;

class Permiso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "permisos_v3";

    protected $guarded = ["id"];

    protected $hidden = ["created_at", "updated_at", "deleted_at"];

    public static function boot()
    {
        parent::boot();

        self::deleting(function (Permiso $permiso) {
            foreach ($permiso->permisos_roles as $permisoRol) {
                $permisoRol->forceDelete();
            }
        });
    }

    public function permisos_roles()
    {
        return $this->hasMany(PermisosRoles::class, "id_permiso");
    }

    public function usuarios()
    {
        return $this->hasManyThrough(
            User::class,
            RolesUsuarios::class,
            "id_usuario",
            "id",
            "id",
            "id_rol"
        );
    }

    public function roles()
    {
        return $this->hasManyThrough(
            Rol::class,
            PermisosRoles::class,
            "id_permiso",
            "id",
            "id",
            "id_rol"
        );
    }
}
