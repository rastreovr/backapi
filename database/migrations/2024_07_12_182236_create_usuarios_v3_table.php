<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateUsuariosV3Table extends Migration
{
   /**
     * Run the migrations.
     * Ejecutar:
     * php artisan migrate --path=/database/migrations/2024_07_12_182236_create_usuarios_v3_table.php
     * @return void
     */
    public function up()
    {
        Schema::create('usuarios_v3', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('created_at')->useCurrent(); // Utiliza useCurrent() para establecer current_timestamp()
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); // Establece current_timestamp() ON UPDATE current_timestamp()
            $table->string('nombre')->nullable();
            $table->string('nombre_usuario')->nullable()->unique();
            $table->integer('tipo')->default(0)->comment("Columna para especificar el tipo de usuario, 0:Interno, 1:Externo");

            $table->string('correo')->nullable();
            $table->string('foto')->default('default_usuario.png');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('contrasenia');
            $table->integer('estatus')->default(1);
            $table->rememberToken();
            $table->softDeletes();

            $table->index('nombre_usuario');

        });

        DB::statement("ALTER TABLE usuarios_v3 CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin");
        DB::statement('ALTER TABLE usuarios_v3 ENGINE=InnoDB');
        DB::statement("ALTER TABLE usuarios_v3 COMMENT 'Tabla los Usuarios de V3'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usuarios_v3');
    }
}
