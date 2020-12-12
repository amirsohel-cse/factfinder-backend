<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\JsonResponseTrait;
use App\Http\Requests\Advisor\StoreAdvisor;
use App\Http\Resources\Admin\AdminResource;
use App\Http\Resources\Advisor\AdvisorResource;
use App\Models\Advisor;
use App\Models\Client;
use App\Http\Resources\TaskResource;
use App\Models\Task;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdvisorApiController extends Controller
{
    use JsonResponseTrait;

    public function index()
    {
        $data['clients'] = [];
        $totalClientActiveTaskCount = 0;
        $totalClientOverDueTaskCount = 0;
        $clients = Client::where('advisor_id', auth()->user()->id)->get();
        foreach ($clients as $key => $client) {
            $user= User::find($client->client_id);
            $data['clients'][$key]= $user;
            $data['clients'][$key]['activeTask'] = $user->tasks()->activeTask()->count();
            $data['clients'][$key]['overdueTask'] = $user->tasks()->overDueTask()->count();
            $totalClientActiveTaskCount += $data['clients'][$key]['activeTask'];
            $totalClientOverDueTaskCount += $data['clients'][$key]['overdueTask'];
        }
        $data['overview']['clientCount'] = $clients->count();
        $data['overview']['clientActiveTaskCount'] = $totalClientActiveTaskCount;
        $data['overview']['clientOverdueTaskCount'] = $totalClientOverDueTaskCount;
        $data['overview']['advisorOverdueTaskCount'] = auth()->user()->tasks()->overDueTask()->count();
        return $this->json('Advisor dashboard data retrieved successfully', $data);
    }

    public function store(StoreAdvisor $request)
    {
        $newAdvisor = DB::transaction(function () use ($request) {
            $request['password'] = Hash::make($request['password']);
            $advisor = User::create($request->all() + [
                    'email_verified_at' => now(),
                ]);
            $advisor->assignRole($request->role);
            auth()->user()->advisors()->create([
                'advisor_id' => $advisor->id,
            ]);
            return $advisor;
        });
        return $this->json('Advisor created successfully', new AdvisorResource($newAdvisor), Response::HTTP_CREATED);
    }

    public function deleteAdvisor($id)
    {
        return DB::transaction(function () use ($id) {
            $advisor = User::findOrFail($id);
            $advisor->tasks()->detach();
            $advisor->creatorTasks()->delete();
            $clients = $advisor->clients()->pluck('client_id');
            foreach ($clients as $client) {
                $client = User::find($client);
                $client->tasks()->detach();
                $client->delete();
            }
            Advisor::where('advisor_id',$id)->delete();
            $advisor->delete();
            return $this->json('Advisor removed successfully', Response::HTTP_NO_CONTENT);
        });
    }
}
