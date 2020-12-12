<?php

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\User;
class TaskTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Task::class,10)->create();
        foreach (Task::all() as $task){
            $users = User::inRandomOrder()->whereNotBetween('id',[1,10])->take(rand(1,3))->pluck('id');
            foreach ($users as $user){
                $task->users()->attach($user,['status' => rand(0,1)]);
            }
        }
    }
}
