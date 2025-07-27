<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDataLimiteVigenciaToNormasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('normas', function (Blueprint $table) {
            //vigência indeterminada
            $table->boolean('vigencia_indeterminada')
                  ->default(true)
                  ->after('vigente')
                  ->comment('Define se a vigência da norma é por tempo indeterminado');
            
            // data limite da vigência
            $table->date('data_limite_vigencia')
                  ->nullable()
                  ->after('vigencia_indeterminada')
                  ->comment('Data limite para mudança automática de status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('normas', function (Blueprint $table) {
            $table->dropColumn(['vigencia_indeterminada', 'data_limite_vigencia']);
        });
    }
}