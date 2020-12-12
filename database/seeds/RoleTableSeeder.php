<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\User;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $roles = [
            [
                'name' => 'super-admin',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'admin',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'advisor',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'client',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ];

        foreach ($roles as $role){
            Role::create($role);
        }
    }
}
