<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePasswordResetsV3Table extends Migration
{
   /**
     * Run the migrations.
     * Ejecutar:
     * php artisan migrate --path=/database/migrations/2024_07_12_184927_create_password_resets_v3_table.php
     * @return void
     */
    public function up()
    {
        Schema::create('password_resets_v3', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email')->index(); // Índice para búsqueda rápida por correo electrónico
            $table->string('token'); // Token único para verificación de restablecimiento
            $table->timestamp('created_at')->useCurrent(); // Utiliza useCurrent() para establecer current_timestamp()
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); // Establece current_timestamp() ON UPDATE current_timestamp()
            $table->timestamp('tiempo_vida')->nullable()->comment('Tiempo de vida del token');

            // Agregar columna para la relación con la tabla usuarios_v3
            $table->unsignedBigInteger('user_id')->nullable()->comment('ID del usuario de la tabla usuarios_v3');
            $table->foreign('user_id')->references('id')->on('usuarios_v3')->onDelete('cascade');
        });
        DB::statement("ALTER TABLE password_resets_v3 CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin");
        DB::statement('ALTER TABLE password_resets_v3 ENGINE=InnoDB');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('password_resets_v3');
    }
}
