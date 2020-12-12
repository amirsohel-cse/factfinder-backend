<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Task;
use App\User;
use Faker\Generator as Faker;

$factory->define(Task::class, function (Faker $faker) {
    return [
        'description' => $faker->text(50),
        'type' => $faker->randomElement(['Important','Urgent','Complex']),
        'completion_date' => $faker->date(),
        'completion_time' => $faker->time(),
        'priority' => $faker->randomElement(['1','2','3','5','8','13']),
        'assignee_type' => $faker->randomElement(['Client','Advisor']),
        'user_id' => User::inRandomOrder()->whereNotBetween('id',[15,20])->first()->id,
    ];
});
