<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\TaskNotBelongsToUser;
use App\Http\Controllers\Controller;
use App\Http\Controllers\JsonResponseTrait;
use App\Http\Requests\StoreTaskPost;
use App\Http\Resources\TaskResource;
use App\Models\Client;
use App\Models\Task;
use App\Notifications\NewTaskNotification;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Notification;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskApiController extends Controller
{
    use JsonResponseTrait;

    /**
     * Display a listing of the resource.
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $tasks = auth()->user()->tasks()->with('creator')->paginate(10);
        return $this->json('Tasks retrieved successfully', new LengthAwarePaginator(
            TaskResource::collection($tasks->items()),
            $tasks->total(),
            $tasks->perPage(),
            $tasks->currentPage(),
            [
                'path' => app('url')->current()
            ]
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreTaskPost $request)
    {
//        dd(auth()->user()->roles->first()->name);
        return (auth()->user()->roles->first()->name == "super-admin")
            ? ($this->addTaskBySuperAdmin($request))
            : ($this->addTaskByAdvisor($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addTaskBySuperAdmin(StoreTaskPost $request)
    {
        $task = Task::create($request->all() + [
                'completion_date' => Carbon::parse($request->completion_date)->toDateTimeString(),
                'completion_time' => Carbon::parse($request->completion_time)->toDateTimeString(),
                'user_id' => auth()->user()->id,
            ]);
        if ($request->assignee_type == "Advisor") {
            $advisors = User::roleUser("advisor")->get();
            ($advisors->isNotEmpty())
                ? ($this->assign_task($advisors, $task))
                : ($task);
        } else {
            $clients = User::roleUser("client")->get();
            ($clients->isNotEmpty())
                ? ($this->assign_task($clients, $task))
                : ($task);
        }
        return $this->json('Task created successfully by super admin ', new TaskResource($task),Response::HTTP_CREATED);
    }

    public function addTaskByAdvisor(StoreTaskPost $request)
    {
        $task = Task::create($request->all() + [
                'completion_date' => Carbon::parse($request->completion_date)->toDateTimeString(),
                'completion_time' => Carbon::parse($request->completion_time)->toDateTimeString(),
                'user_id' => auth()->user()->id,
            ]);
        if ($request->assignee_type == 'Client') {
            $advisor_clients = Client::where('advisor_id', auth()->user()->id)->pluck('client_id');
            $clients = User::whereIn('id', $advisor_clients)->get();
            ($clients->isNotEmpty())
                ? ($this->assign_task($clients, $task))
                : ($task);
        } else {
            $task->users()->attach(auth()->user()->id, ['status' => 0]);
            $this->assign_task(auth()->user(), $task);
        }
        return $this->json('Task created by advisor successfully', new TaskResource($task),Response::HTTP_CREATED);
    }

    public function assign_task($users, $task)
    {
        $task->users()->sync($users, ['status' => 0]);
        Notification::send($users, new NewTaskNotification($task));
        return $task;
    }

    public function getAdvisorActiveTask(Request $request)
    {
        $active_task = auth()->user()->tasks()->activeTask()->paginate(10);
        return $this->json('Tasks retrieved successfully', new LengthAwarePaginator(
            TaskResource::collection($active_task->items()),
            $active_task->total(),
            $active_task->perPage(),
            $active_task->currentPage(),
            [
                'path' => app('url')->current()
            ]
        ));
    }

    public function getAdvisorOverdueTask(Request $request)
    {
        $overdue_task = auth()->user()->tasks()->overDueTask()->paginate(10);
        return $this->json('Tasks retrieved successfully', new LengthAwarePaginator(
            TaskResource::collection($overdue_task->items()),
            $overdue_task->total(),
            $overdue_task->perPage(),
            $overdue_task->currentPage(),
            [
                'path' => app('url')->current()
            ]
        ));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return
     */
    public function show(Task $task)
    {
      //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task)
    {
      //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Task $task)
    {
        $this->taskUserCheck($task);
        return DB::transaction(function () use ($task){
            $task->users()->detach();
            $task->delete();
            return $this->json('Task deleted successfully', Response::HTTP_NO_CONTENT);
        });
    }

    public function taskUserCheck($task)
    {
        if(Auth::id() !== $task->user_id)
         throw new TaskNotBelongsToUser;
    }

    public function markAsCompleted(Request $request)
    {
        return DB::transaction(function () use ($request){
            $user = User::findOrFail($request->user_id);
            $user->tasks()->where('task_id',$request->task_id)->update(['status' => 1]);
            return $this->json('Task mark as completed successfully',Response::HTTP_NO_CONTENT);
        });
    }
}
