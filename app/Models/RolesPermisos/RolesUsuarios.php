<?php

namespace App\Models\RolesPermisos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Usuario\User;
use App\Models\RolesPermisos\Rol;

class RolesUsuarios extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "roles_usuarios_v3";

    protected $guarded = ["id"];

    public function usuarios() {
        return $this->hasMany(User::class, "id");
    }

    public function rol() {
        return $this->hasOne(Rol::class, 'id', 'id_rol');
    }

    public function usuario() {
        return $this->hasOne(User::class, 'id', 'id_usuario');
    }
}
