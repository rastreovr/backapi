<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateRolesV3Table extends Migration
{
   /**
     * Run the migrations.
     * Ejecutar:
     * php artisan migrate --path=/database/migrations/2024_07_12_190409_create_roles_v3_table.php
     * @return void
     */
    public function up()
    {
        Schema::create('roles_v3', function (Blueprint $table) {
            $table->bigIncrements('id'); // Clave primaria autoincremental
            $table->timestamp('created_at')->useCurrent(); // Utiliza useCurrent() para establecer current_timestamp()
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); // Establece current_timestamp() ON UPDATE current_timestamp()
            $table->string('nombre')->collation('utf8_bin')->unique(); // Nombre del rol, único
            $table->string('descripcion')->collation('utf8_bin'); // Descripción del rol
            $table->softDeletes();

        });

        DB::statement("ALTER TABLE roles_v3 CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin");
        DB::statement('ALTER TABLE roles_v3 ENGINE=InnoDB');
        DB::statement("ALTER TABLE roles_v3 COMMENT 'Tabla los Roles para los usuarios'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles_v3');
    }
}
