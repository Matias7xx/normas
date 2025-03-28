<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixStatusColumnInNormasChavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('UPDATE normas_chaves SET status = true WHERE status IS NULL');
        DB::statement('ALTER TABLE normas_chaves ALTER COLUMN status SET DEFAULT true');
        DB::statement('ALTER TABLE normas_chaves ALTER COLUMN status SET NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE normas_chaves ALTER COLUMN status DROP DEFAULT');
        DB::statement('ALTER TABLE normas_chaves ALTER COLUMN status DROP NOT NULL');
    }
}
