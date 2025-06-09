<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVigenteToNormasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('normas', function (Blueprint $table) {
            $table->enum('vigente', ['VIGENTE', 'NÃO VIGENTE', 'EM ANÁLISE'])
                  ->default('EM ANÁLISE')
                  ->nullable()
                  ->after('status');
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
            $table->dropColumn('vigente');
        });
    }
}