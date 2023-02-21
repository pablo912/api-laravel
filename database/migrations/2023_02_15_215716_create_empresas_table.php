<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmpresasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('empresas', function (Blueprint $table) {

            $table->increments('id');
            $table->string('ruc');
            $table->string('razon');
            $table->string('estado');
            $table->string('condicion');
            $table->string('ubigeo');
            $table->string('tipo_via');
            $table->string('nombre_via');
            $table->string('codigo_zona');
            $table->string('tipo_zona');
            $table->string('numero');
            $table->string('interior');
            $table->string('lote');
            $table->string('departamento');
            $table->string('manzana');
            $table->string('km');
            $table->string('direccion')->nullable();
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('empresas');
    }
}
