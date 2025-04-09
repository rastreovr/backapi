<?php

namespace App\Models\RolesPermisos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\RolesPermisos\Permiso;

class PermisosRoles extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "permisos_roles_v3";
    protected $guarded = ["id"];
    protected $hidden = ["laravel_through_key"];

    /**
     * Genera semillas para crear entidades de PermisosRoles basándose en un módulo y un ID de rol
     * Relaciona todos los permisos de un modulo al Rol brindado
     */
    public static function generateRelationModuloARol(
        string $modulo,
        int $rolId,
        array $except = []
    ) {
        $permisos = Permiso::where("modulo", $modulo)
            ->whereNotIn("modulo", $except)
            ->get();

        return $permisos->map(function ($permiso) use ($rolId) {
            return [
                "id_rol" => $rolId,
                "id_permiso" => $permiso["id"],
            ];
        });
    }
}
