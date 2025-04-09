<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBitacorasV3Table extends Migration
{
   /**
     * Run the migrations.
     * Ejecutar:
     * php artisan migrate --path=/database/migrations/2024_07_12_204148_create_bitacoras_v3_table.php
     * @return void
     */
    public function up()
    {
        Schema::create('bitacoras_v3', function (Blueprint $table) {
            $table->bigIncrements('id'); // Clave primaria autoincremental
            $table->timestamp('created_at')->useCurrent(); // Utiliza useCurrent() para establecer current_timestamp()
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); // Establece current_timestamp() ON UPDATE current_timestamp()
            $table->unsignedBigInteger('id_usuario'); // ID del usuario
            $table->string('modulo')->collation('utf8_bin'); // Módulo
            $table->string('accion')->collation('utf8_bin'); // Acción
            $table->string('comentario')->collation('utf8_bin'); // Comentario
            $table->softDeletes();

            // Índices
            $table->index('id_usuario', 'bitacoras_id_usuario_foreign');

            // Definición de las claves foráneas
            $table->foreign('id_usuario')->references('id')->on('usuarios_v3')->onUpdate('restrict')->onDelete('restrict');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bitacoras_v3');
    }
}
