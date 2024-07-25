<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNormasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('normas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('usuario_id')->unsigned()->nullable();
            $table->date('data')->nullable();
            $table->string('descricao', 255)->nullable();
            $table->string('resumo', 255)->nullable();

            $table->integer('publicidade_id')->unsigned()->nullable();
            $table->integer('tipo_id')->unsigned()->nullable();
            $table->integer('orgao_id')->unsigned()->nullable();

            $table->foreign('publicidade_id')->references('id')->on('publicidades');
            $table->foreign('tipo_id')->references('id')->on('tipos');
            $table->foreign('orgao_id')->references('id')->on('orgaos');

            $table->string('anexo', 150)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
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
        Schema::dropIfExists('normas');
    }
}
