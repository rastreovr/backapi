<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateFailedJobsV3Table extends Migration
{
   /**
     * Run the migrations.
     * Ejecutar:
     * php artisan migrate --path=/database/migrations/2024_07_12_185753_create_failed_jobs_v3_table.php
     * @return void
     */
    public function up()
    {
        Schema::create('failed_jobs_v3', function (Blueprint $table) {
            $table->id(); // Clave primaria autoincremental
            $table->string('uuid')->unique(); // UUID único para identificar la tarea fallida
            $table->text('connection'); // Conexión utilizada para la tarea fallida
            $table->text('queue'); // Cola de la tarea fallida
            $table->longText('payload'); // Datos de la tarea fallida
            $table->longText('exception'); // Detalles de la excepción que causó el fallo
            $table->timestamp('failed_at')->useCurrent(); // Fecha y hora del fallo
        });
        DB::statement("ALTER TABLE failed_jobs_v3 CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('failed_jobs_v3');
    }
}
