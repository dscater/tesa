<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIngresoMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ingreso_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("material_id")->unsigned();
            $table->unsignedBigInteger("proveedor_id")->unsigned();
            $table->string("descripcion", 800);
            $table->double("cantidad", 8, 2);
            $table->double("peso", 8, 2);
            $table->decimal("precio", 24, 2);
            $table->unsignedBigInteger("tipo_ingreso_id");
            $table->date("fecha_ingreso");
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
        Schema::dropIfExists('ingreso_materials');
    }
}
