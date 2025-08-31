<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('boletins', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->date('data_publicacao')->default(DB::raw('CURRENT_DATE')); // Data atual como padrão
            $table->string('arquivo'); // Nome do arquivo no MinIO
            $table->boolean('status')->default(true); // Para soft delete
            
            // Campos de auditoria
            $table->unsignedBigInteger('user_id')->default(1); // Quem cadastrou. Usuário padrão (admin)
            $table->foreign('user_id')->references('id')->on('users');
            
            $table->timestamps();
            
            // Índices
            $table->index(['status', 'data_publicacao']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boletins');
    }
};