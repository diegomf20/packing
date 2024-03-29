<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgramaComercialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('programa_comercial', function (Blueprint $table) {
            $table->id();
            $table->string('codigo',12);
            $table->string('estado',30)->default('Generado');
            $table->integer('productor_id')->nullable();
            $table->string('codigo_operacion',20)->nullable();
            $table->date('fecha_despacho')->nullable();
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
        Schema::dropIfExists('produccion');
    }
}
