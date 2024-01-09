<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalidaMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salida_materials', function (Blueprint $table) {
            $table->id();
            $table->string("codigo", 155);
            $table->bigInteger("nro");
            $table->unsignedBigInteger("producto_id");
            $table->double("cantidad", 8, 2);
            $table->date("fecha_salida");
            $table->string("estado")->default("EN PRODUCCIÓN");
            $table->date("fecha_registro")->nullable();
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
        Schema::dropIfExists('salida_materials');
    }
}
