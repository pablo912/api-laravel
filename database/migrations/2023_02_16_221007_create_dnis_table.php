<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dnis', function (Blueprint $table) {
            $table->increments('id');
            $table->string('numero')->index();
            $table->string('nombre_completo')->nullable();
            $table->string('apellido_paterno')->nullable();
            $table->string('apellido_materno')->nullable();
            $table->string('nombres')->nullable();
            $table->string('tipo_seguro')->nullable();
            $table->string('formato')->nullable();
            $table->string('numero_afiliacion')->nullable();
            $table->string('plan_beneficios')->nullable();
            $table->string('fecha_afiliacion')->nullable();
            $table->string('eess')->nullable();
            $table->string('ubicacion')->nullable(); 
            $table->string('fecha_nacimiento')->nullable();
            $table->string('estado_civil')->nullable();
            $table->string('sexo')->nullable();
            $table->string('edad')->nullable();
            $table->string('domicilio')->nullable();
            $table->string('departamento')->nullable();
            $table->string('provincia')->nullable();
            $table->string('distrito')->nullable();
            $table->string('ubigeo2')->nullable();
            $table->text('photo')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dnis');
    }
};
