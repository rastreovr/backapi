<?php

namespace App\Models\RolesPermisos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class RolesAsignables extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "roles_asignables_v3";
    protected $guarded = ["id"];

    public function mayor()
    {
        return $this->belongsTo(Rol::class, "mayor_id_rol", "id");
    }

    public function menor()
    {
        return $this->hasMany(Rol::class, "id", "menor_id_rol");
    }

}
