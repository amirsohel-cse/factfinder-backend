<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\JsonResponseTrait;
use App\Http\Requests\Client\StoreClient;
use App\Http\Resources\Client\ClientResource;
use App\Http\Resources\TaskResource;
use App\Models\Client;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClientApiController extends Controller
{
    use JsonResponseTrait;

    public function store(StoreClient $request)
    {
        $newClient = DB::transaction(function () use ($request) {
            $request['password'] = Hash::make($request['password']);
            $client = User::create($request->all() + [
                    'email_verified_at' => now(),
                ]);
            $client->assignRole($request->role);
            auth()->user()->clients()->create([
                'client_id' => $client->id,
            ]);
            return $client;
        });
        return $this->json('Client created successfully', new ClientResource($newClient), Response::HTTP_CREATED);
    }

    public function deleteClient($id)
    {
        return DB::transaction(function () use ($id){
            $client = User::findOrFail($id);
            $client->tasks()->detach();
            Client::where('client_id',$id)->delete();
            $client->delete();
            return $this->json('Client removed successfully', Response::HTTP_NO_CONTENT);
        });
    }

    public function ongoingTasks()
    {
        $created_at = Carbon::parse(auth()->user()->created_at);
        $diff = $created_at->diffInDays(Carbon::now())%14;
        $start_sprint_date = Carbon::now()->subDay($diff);
        $end_sprint_date = Carbon::now()->addDay(14-$diff);
        $tasks = Auth::user()->tasks()->where('status',0)->whereBetween('completion_date',[$start_sprint_date,$end_sprint_date])->paginate(10);
        return $this->json('Ongoing tasks retrieved successfully', new LengthAwarePaginator(
            TaskResource::collection($tasks->items()),
            $tasks->total(),
            $tasks->perPage(),
            $tasks->currentPage(),
            [
                'path' => app('url')->current()
            ]
        ));
    }
}
