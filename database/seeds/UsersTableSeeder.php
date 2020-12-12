<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach(Spatie\Permission\Models\Role::all() as $role) {
            $users = factory(User::class, 5)->create();
            foreach($users as $user){
                $user->assignRole($role);
            }
        }
    }
}
