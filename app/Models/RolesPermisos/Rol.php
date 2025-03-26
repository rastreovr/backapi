<?php

namespace App\Models\RolesPermisos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\RolesPermisos\Permiso;
use App\Models\RolesPermisos\PermisosRoles;
use App\Models\RolesPermisos\RolesUsuarios;
use App\Models\RolesPermisos\RolesAsignables;

class Rol extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "roles_v3";

    protected $guarded = ["id"];

    protected $hidden = ["created_at", "updated_at"];

    public static function boot()
    {
        parent::boot();

        self::deleting(function (Rol $rol) {
            foreach ($rol->roles_usuarios as $rolUsuario) {
                // $rolUsuario->forceDelete();
                $rolUsuario->delete();
            }

            foreach ($rol->permisos_roles as $permisoRol) {
                $permisoRol->delete();
            }
        });
    }

    public function permisos()
    {
        return $this->hasManyThrough(
            Permiso::class,
            PermisosRoles::class,
            "id_rol",
            "id",
            "id",
            "id_permiso"
        );
    }

    public function permisos_roles()
    {
        return $this->hasMany(PermisosRoles::class, "id_rol", "id");
    }

    public function roles_usuarios()
    {
        return $this->hasMany(RolesUsuarios::class, "id_rol");
    }


    public function getRolesMenores()
    {
        $rolesMenores = RolesAsignables::where([["mayor_id_rol", $this->id]])
            ->get()
            ->map(function ($rol) {
                return $rol["menor_id_rol"];
            });

        return Rol::whereIn("id", $rolesMenores)->get();
    }

}
