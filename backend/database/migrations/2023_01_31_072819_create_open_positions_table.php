<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('open_positions', function (Blueprint $table) {
            $table->id();
            $table->string('RptDt');
            $table->string('TckrSymb');
            $table->string('ISIN');
            $table->string('Asst');
            $table->string('BalQty');
            $table->string('TradAvrgPric');
            $table->string('PricFctr');
            $table->string('BalVal');
            $table->integer('batch_id')->references('id')->on('batches');
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
        Schema::dropIfExists('open_positions');
    }
};
