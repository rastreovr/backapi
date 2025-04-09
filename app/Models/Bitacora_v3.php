<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bitacora_v3 extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "bitacoras_v3";

    protected $fillable = [
        'id_usuario',
        'modulo',
        'accion',
        'comentario'
    ];


}
