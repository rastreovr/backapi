<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePermisosRolesV3Table extends Migration
{
   /**
     * Run the migrations.
     * Ejecutar:
     * php artisan migrate --path=/database/migrations/2024_07_12_193627_create_permisos_roles_v3_table.php
     * @return void
     */
    public function up()
    {
        Schema::create('permisos_roles_v3', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->useCurrent(); // Utiliza useCurrent() para establecer current_timestamp()
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); // Establece current_timestamp() ON UPDATE current_timestamp()
            $table->unsignedBigInteger('id_rol'); // ID del rol
            $table->unsignedBigInteger('id_permiso'); // ID del permiso
            $table->softDeletes();


            // Índice único para las columnas id_rol y id_permiso
            $table->unique(['id_rol', 'id_permiso'], 'permisos_roles_id_rol_id_permiso_unique');

            // Índice en la columna id_permiso
            $table->index('id_permiso', 'permisos_roles_id_permiso_foreign');

            // Definición de las claves foráneas
            $table->foreign('id_permiso')->references('id')->on('permisos_v3')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('id_rol')->references('id')->on('roles_v3')->onUpdate('restrict')->onDelete('restrict');

        });
        DB::statement("ALTER TABLE permisos_roles_v3 CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin");
        DB::statement('ALTER TABLE permisos_roles_v3 ENGINE=InnoDB');
        DB::statement("ALTER TABLE permisos_roles_v3 COMMENT 'Tabla de relacional entre permisos y sus roles'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permisos_roles_v3');
    }
}
