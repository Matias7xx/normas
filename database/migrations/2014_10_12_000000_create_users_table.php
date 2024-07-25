<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create Roles Table
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 80);
            $table->text('description');
            $table->timestamps();
            $table->softDeletes();
        });
        // Create Permission Table
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 80);
            $table->string('slug', 40);
            $table->timestamps();
            $table->softDeletes();
        });
        // Create Roles-Permissions Table
        Schema::create('permission_role', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('role_id')->unsigned();
            $table->integer('permission_id')->unsigned();
            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreign('permission_id')->references('id')->on('permissions');
            $table->timestamps();
        });
        // Create Users Table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('matricula');
            $table->string('email')->unique();
            $table->string('name');
            $table->string('cpf')->nullable();
            $table->string('sexo', 1)->nullable();
            $table->string('telefone')->nullable();
            $table->string('cargo_id')->nullable();
            $table->string('cargo')->nullable();
            $table->string('classe_funcional')->nullable();
            $table->string('nivel_funcional')->nullable();
            $table->string('status')->nullable();
            $table->string('unidade_lotacao_id')->nullable();
            $table->string('unidade_lotacao')->nullable();
            $table->string('srpc')->nullable();
            $table->string('dspc')->nullable();
            $table->string('nivel')->nullable();
            $table->timestamp('email_verified_at')->nullable()->nullable();
            $table->string('password')->nullable();
            $table->integer('role_id')->unsigned();
            $table->foreign('role_id')->references('id')->on('roles');
            $table->boolean('active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('users');
    }
}
