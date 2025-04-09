<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateRolesUsuariosV3Table extends Migration
{
   /**
     * Run the migrations.
     * Ejecutar:
     * php artisan migrate --path=/database/migrations/2024_07_12_192405_create_roles_usuarios_v3_table.php
     * @return void
     */
    public function up()
    {
        Schema::create('roles_usuarios_v3', function (Blueprint $table) {
            $table->bigIncrements('id'); // Clave primaria autoincremental
            $table->timestamp('created_at')->useCurrent(); // Utiliza useCurrent() para establecer current_timestamp()
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); // Establece current_timestamp() ON UPDATE current_timestamp()
            $table->unsignedBigInteger('id_usuario'); // ID del usuario
            $table->unsignedBigInteger('id_rol'); // ID del rol
            $table->softDeletes();

            // Índice único para las columnas id_usuario y id_rol
            $table->unique(['id_usuario', 'id_rol'], 'roles_usuarios_usuario_id_rol_id_unique');

            // Índice en la columna id_rol
            $table->index('id_rol', 'roles_usuarios_rol_id_foreign');

            // Definición de las claves foráneas
            $table->foreign('id_usuario')->references('id')->on('usuarios_v3')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('id_rol')->references('id')->on('roles_v3')->onUpdate('restrict')->onDelete('restrict');

        });
        DB::statement("ALTER TABLE roles_usuarios_v3 CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin");
        DB::statement('ALTER TABLE roles_usuarios_v3 ENGINE=InnoDB');
        DB::statement("ALTER TABLE roles_usuarios_v3 COMMENT 'Tabla de relacional entre usuarios y sus roles'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles_usuarios_v3');
    }
}
