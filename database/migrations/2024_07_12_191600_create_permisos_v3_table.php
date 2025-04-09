<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePermisosV3Table extends Migration
{
   /**
     * Run the migrations.
     * Ejecutar:
     * php artisan migrate --path=/database/migrations/2024_07_12_191600_create_permisos_v3_table.php
     * @return void
     */
    public function up()
    {
        Schema::create('permisos_v3', function (Blueprint $table) {
            $table->bigIncrements('id'); // Clave primaria autoincremental
            $table->timestamp('created_at')->useCurrent(); // Utiliza useCurrent() para establecer current_timestamp()
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); // Establece current_timestamp() ON UPDATE current_timestamp()
            $table->string("nombre");
            $table->string("tipo");
            $table->string("modulo");
            $table->softDeletes();

            // Índice único para las columnas nombre, tipo y modulo
            $table->unique(['nombre', 'tipo', 'modulo']);

        });
        DB::statement("ALTER TABLE permisos_v3 CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin");
        DB::statement('ALTER TABLE permisos_v3 ENGINE=InnoDB');
        DB::statement("ALTER TABLE permisos_v3 COMMENT 'Tabla de permisos para los roles'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permisos_v3');
    }
}
