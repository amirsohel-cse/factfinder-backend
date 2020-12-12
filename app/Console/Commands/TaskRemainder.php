<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskRemainderNotification;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Notification;

class TaskRemainder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:remainder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Task reminder for client and advisor';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tasks = Task::query()
                     ->whereBetween('completion_date',[Carbon::now(),Carbon::now()->addDay(5)])
                     ->get();
        foreach ($tasks as $task)
        {
            $taskUser = $task->users()->wherepivot('status',0)->get();
            $date = Carbon::parse($task->completion_date);
            $now = Carbon::now();
            $diff = $date->diffInDays($now);
            $message ='You have '.$diff.' days left';
            Notification::send($taskUser, new TaskRemainderNotification($task,$diff,$message));
        }
    }
}
