<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGruposDuplicadasVerificadosTable extends Migration
{
    public function up()
    {
        Schema::create('grupos_duplicadas_verificados', function (Blueprint $table) {
            $table->id();
            $table->string('hash_grupo')->unique(); // Hash para identificar o grupo
            $table->json('normas_ids'); // IDs das normas do grupo
            $table->unsignedBigInteger('verificado_por')->nullable(); // ID do usuário
            $table->timestamp('verificado_em');
            $table->text('observacoes')->nullable();
            $table->enum('status', ['pendente', 'verificado'])->default('pendente');
            $table->timestamps();

            $table->foreign('verificado_por')->references('id')->on('users');
            $table->index(['hash_grupo', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('grupos_duplicadas_verificados');
    }
}
