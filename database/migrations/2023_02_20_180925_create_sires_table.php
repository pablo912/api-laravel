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
        Schema::create('sires', function (Blueprint $table) {

      
            $table->string('id');
            $table->string('numRuc')->nullable();
            $table->string('nomRazonSocial')->nullable();
            $table->string('perPeriodoTributario')->nullable();
            $table->string('codCar')->nullable();
            $table->string('codTipoCDP')->nullable();
            $table->string('numSerieCDP')->nullable();
            $table->string('numCDP')->nullable();
            $table->string('codTipoCarga')->nullable();
            $table->string('codSituacion')->nullable();
            $table->string('fecEmision')->nullable();
            $table->string('fecVencPag')->nullable();
            $table->string('codTipoDocIdentidad')->nullable();
            $table->string('numDocIdentidad')->nullable();
            $table->string('nomRazonSocialCliente')->nullable();
            $table->string('mtoValFactExpo')->nullable();
            $table->string('mtoBIGravada')->nullable();
            $table->string('mtoDsctoBI')->nullable();
            $table->string('mtoIGV')->nullable();
            $table->string('mtoDsctoIGV')->nullable();
            $table->string('mtoExonerado')->nullable();
            $table->string('mtoInafecto')->nullable();
            $table->string('mtoISC')->nullable();
            $table->string('mtoBIIvap')->nullable();
            $table->string('mtoIvap')->nullable();
            $table->string('mtoIcbp')->nullable();
            $table->string('mtoOtrosTrib')->nullable();
            $table->string('mtoTotalCP')->nullable();
            $table->string('codMoneda')->nullable();
            $table->string('mtoTipoCambio')->nullable();
            $table->string('codEstadoComprobante')->nullable();
            $table->string('mtoValorOpGratuitas')->nullable();
            $table->string('mtoValorFob')->nullable();
            $table->string('indTipoOperacion')->nullable();
            $table->string('numInconsistencias')->nullable();
            $table->string('indEditable')->nullable();
            $table->string('documentoMod')->nullable();
            $table->string('semaforo')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sires');
    }
};
