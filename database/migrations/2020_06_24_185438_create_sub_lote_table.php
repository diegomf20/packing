<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubLoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_lote', function (Blueprint $table) {
            $table->id();
            $table->integer('lote_id');
            //recepcin de materia
            $table->integer('viaje');
            $table->string('guia',50);            
            $table->decimal('peso_guia',10,4);
            $table->datetime('fecha_recepcion');
            $table->string('estado',30)->nullable();
            //lanzado y descarte
            $table->datetime('fecha_proceso')->nullable();
            $table->decimal('descarte_racimos',10,4)->nullable();
            $table->decimal('descarte_granos',10,4)->nullable();
            $table->integer('cantidad_jabas_descarte')->nullable();
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
        Schema::dropIfExists('sub_lote');
    }
}
