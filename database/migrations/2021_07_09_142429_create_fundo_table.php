<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFundoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fundo', function (Blueprint $table) {
            $table->id();
            $table->string('cod_cartilla',5)->nullable();
            $table->string('nombre_fundo',50);
            $table->string('lugar_produccion',50);
            $table->string('cod_lugar_produccion',50);
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
        Schema::dropIfExists('fundo');
    }
}
