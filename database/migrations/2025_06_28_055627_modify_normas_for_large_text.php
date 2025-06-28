<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ModifyNormasForLargeText extends Migration
{
    public function up()
    {
        // Usar SQL direto para PostgreSQL
        DB::statement('ALTER TABLE normas ALTER COLUMN descricao TYPE TEXT');
        DB::statement('ALTER TABLE normas ALTER COLUMN resumo TYPE TEXT');
    }

    public function down()
    {
        DB::statement('ALTER TABLE normas ALTER COLUMN descricao TYPE VARCHAR(255)');
        DB::statement('ALTER TABLE normas ALTER COLUMN resumo TYPE VARCHAR(255)');
    }
}