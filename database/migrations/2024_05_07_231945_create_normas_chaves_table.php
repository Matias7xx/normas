<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNormasChavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('normas_chaves', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('norma_id')->unsigned()->nullable();
            $table->integer('palavra_chave_id')->unsigned()->nullable();
            $table->foreign('norma_id')->references('id')->on('normas');
            $table->foreign('palavra_chave_id')->references('id')->on('palavra_chaves');
            $table->timestamps();
            $table->boolean('status')->defalt(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('normas_chaves');
    }
}
