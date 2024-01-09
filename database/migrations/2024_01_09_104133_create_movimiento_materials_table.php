<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovimientoMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movimiento_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("registro_id");
            $table->string("tipo_registro");
            $table->unsignedBigInteger("material_id");
            $table->string("detalle", 800);
            $table->string("tipo_is", 155);
            $table->double("cantidad_ingreso", 8, 2)->nullable();
            $table->double("cantidad_salida", 8, 2)->nullable();
            $table->double("cantidad_saldo");
            $table->date("fecha");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movimiento_materials');
    }
}
