<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ColumnIdEmployeeNavixy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('usuarios_v3', function (Blueprint $table) {
            $table->unsignedBigInteger('id_employee_navixy')->default(null)->after("nombre_usuario");
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('usuarios_v3', function (Blueprint $table) {
            $table->dropColumn('id_employee_navixy');
        });

    }
}
