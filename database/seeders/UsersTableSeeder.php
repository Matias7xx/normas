<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Ocorrencia;
use App\Models\Publicidade;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            /**
             * Audit
             */
            ['name' => 'view_audits', 'details' => 'Auditorias [ver]', 'guard' => 'dashboard'],

            /**
             * Settings
             */
            ['name' => 'view_settings', 'details' => 'Configurações [ver]', 'guard' => 'dashboard'],
            ['name' => 'add_settings', 'details' => 'Configurações [adicionar]', 'guard' => 'dashboard'],
            ['name' => 'edit_settings', 'details' => 'Configurações [editar]', 'guard' => 'dashboard'],
            ['name' => 'delete_settings', 'details' => 'Configurações [apagar]', 'guard' => 'dashboard'],

            /**
             * Permissions
             */
            ['name' => 'view_permissions', 'details' => 'Permissões [ver]', 'guard' => 'dashboard'],
            ['name' => 'add_permissions', 'details' => 'Permissões [adicionar]', 'guard' => 'dashboard'],
            ['name' => 'edit_permissions', 'details' => 'Permissões [editar]', 'guard' => 'dashboard'],
            ['name' => 'delete_permissions', 'details' => 'Permissões [apagar]', 'guard' => 'dashboard'],

            /**
             * Roles
             */
            ['name' => 'view_roles', 'details' => 'Grupos [ver]', 'guard' => 'dashboard'],
            ['name' => 'add_roles', 'details' => 'Grupos [adicionar]', 'guard' => 'dashboard'],
            ['name' => 'edit_roles', 'details' => 'Grupos [editar]', 'guard' => 'dashboard'],
            ['name' => 'delete_roles', 'details' => 'Grupos [apagar]', 'guard' => 'dashboard'],

            /**
             * Users
             */
            ['name' => 'view_users', 'details' => 'Usuários [ver]', 'guard' => 'dashboard'],
            ['name' => 'add_users', 'details' => 'Usuários [adicionar]', 'guard' => 'dashboard'],
            ['name' => 'edit_users', 'details' => 'Usuários [editar]', 'guard' => 'dashboard'],
            ['name' => 'delete_users', 'details' => 'Usuários [apagar]', 'guard' => 'dashboard']
        ];
        foreach($permissions as $permission) {
            Permission::create([
                'name' => $permission['details'],
                'slug' => $permission['name']
            ]);
        }

        /**
         * Create Root role
         */
        $root = Role::create([
            'name' => 'root',
            'description' => 'Root'
        ]);

        /**
         * Create User role
         */
        $user = Role::create([
            'name' => 'user',
            'description' => 'User'
        ]);

        /**
         * Create admin role
         */
        $admin = Role::create([
            'name' => 'admin',
            'description' => 'Administrador'
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => 1,
            'role_id'       => 1,
            'created_at'    => 'now()',
            'updated_at'    => 'now()'
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => 2,
            'role_id'       => 1,
            'created_at'    => 'now()',
            'updated_at'    => 'now()'
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => 3,
            'role_id'       => 1,
            'created_at'    => 'now()',
            'updated_at'    => 'now()'
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => 4,
            'role_id'       => 1,
            'created_at'    => 'now()',
            'updated_at'    => 'now()'
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => 5,
            'role_id'       => 1,
            'created_at'    => 'now()',
            'updated_at'    => 'now()'
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => 6,
            'role_id'       => 1,
            'created_at'    => 'now()',
            'updated_at'    => 'now()'
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => 7,
            'role_id'       => 1,
            'created_at'    => 'now()',
            'updated_at'    => 'now()'
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => 8,
            'role_id'       => 1,
            'created_at'    => 'now()',
            'updated_at'    => 'now()'
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => 9,
            'role_id'       => 1,
            'created_at'    => 'now()',
            'updated_at'    => 'now()'
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => 10,
            'role_id'       => 1,
            'created_at'    => 'now()',
            'updated_at'    => 'now()'
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => 11,
            'role_id'       => 1,
            'created_at'    => 'now()',
            'updated_at'    => 'now()'
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => 12,
            'role_id'       => 1,
            'created_at'    => 'now()',
            'updated_at'    => 'now()'
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => 13,
            'role_id'       => 1,
            'created_at'    => 'now()',
            'updated_at'    => 'now()'
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => 14,
            'role_id'       => 1,
            'created_at'    => 'now()',
            'updated_at'    => 'now()'
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => 15,
            'role_id'       => 1,
            'created_at'    => 'now()',
            'updated_at'    => 'now()'
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => 16,
            'role_id'       => 1,
            'created_at'    => 'now()',
            'updated_at'    => 'now()'
        ]);

        DB::table('permission_role')->insert([
            'permission_id' => 17,
            'role_id'       => 1,
            'created_at'    => 'now()',
            'updated_at'    => 'now()'
        ]);

        User::create([
            'name' => 'Admin',
            'matricula' => '000000',
            'email' =>  'admin@pc.pb.gov.br',
            'role_id' => 1,
            'email_verified_at' => now(),
            'password' => '$2a$10$EmGGVDtN81IXJoqU8nG4AO0YwtgHQmgCWgUr25LgWQwJiCKiOPJXS', // teste
        ]);

        Publicidade::create([
            'usuario_id' => 0,
            'publicidade' => 'publico',
            'status' => true
        ]);
    }
}
