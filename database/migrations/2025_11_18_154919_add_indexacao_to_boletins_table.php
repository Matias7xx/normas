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
        Schema::table('boletins', function (Blueprint $table) {
            // Campo para armazenar o conteúdo extraído do PDF
            $table->text('conteudo_indexado')->nullable()->after('arquivo');

            // Flag para indicar se o boletim já foi indexado
            $table->boolean('indexado')->default(false)->after('conteudo_indexado');

            // Data/hora da última indexação (útil para re-indexação)
            $table->timestamp('indexado_em')->nullable()->after('indexado');

            //índice para melhorar performance de buscas
            // GIN é o índice recomendado para full-text search no PostgreSQL
            $table->index('indexado', 'idx_boletins_indexado');
        });

        // Criar índice full-text search no PostgreSQL
        DB::statement("CREATE INDEX idx_boletins_conteudo_fulltext ON boletins USING gin(to_tsvector('portuguese', conteudo_indexado))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boletins', function (Blueprint $table) {
            // Remover índice full-text search
            DB::statement("DROP INDEX IF EXISTS idx_boletins_conteudo_fulltext");

            // Remover índice
            $table->dropIndex('idx_boletins_indexado');

            // Remover colunas
            $table->dropColumn(['conteudo_indexado', 'indexado', 'indexado_em']);
        });
    }
};
