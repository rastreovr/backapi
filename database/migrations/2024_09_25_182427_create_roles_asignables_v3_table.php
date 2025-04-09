<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesAsignablesV3Table extends Migration
{
   /**
     * Run the migrations.
     * Ejecutar:
     * php artisan migrate --path=/database/migrations/2024_09_25_182427_create_roles_asignables_v3_table.php
     * @return void
     */
    public function up()
    {
        Schema::create('roles_asignables_v3', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->useCurrent(); // Utiliza useCurrent() para establecer current_timestamp()
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); // Establece current_timestamp() ON UPDATE current_timestamp()
            $table->unsignedBigInteger("mayor_id_rol");
            $table->unsignedBigInteger("menor_id_rol");

            $table->unique(["mayor_id_rol", "menor_id_rol"]);

            $table
                ->foreign("mayor_id_rol")
                ->references("id")
                ->on("roles_v3");
            $table
                ->foreign("menor_id_rol")
                ->references("id")
                ->on("roles_v3");

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles_asignables_v3');
    }
}
