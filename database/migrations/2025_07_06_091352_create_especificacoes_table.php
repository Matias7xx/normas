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
        Schema::create('especificacoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('arquivo')->nullable();
            $table->boolean('status')->default(true)->comment('Status ativo/inativo');
            $table->unsignedBigInteger('usuario_id')->comment('Usuário que cadastrou');
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['status']);
            $table->index(['nome']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('especificacoes');
    }
};