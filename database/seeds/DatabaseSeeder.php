<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \Illuminate\Support\Facades\Artisan::call('passport:install');
        $this->command->info('Passport client installed');
        $this->call(PermissionTableSeeder::class);
		$this->call(RoleTableSeeder::class);
		$this->call(UsersTableSeeder::class);
        $this->call(TaskTableSeeder::class);
    }
}
