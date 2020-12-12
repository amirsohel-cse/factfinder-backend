<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\JsonResponseTrait;
use App\Http\Requests\Admin\StoreAdmin;
use App\Http\Resources\Admin\AdminResource;
use App\Models\Admin;
use App\Models\Advisor;
use App\Models\Client;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;

class AdminApiController extends Controller
{
    use JsonResponseTrait;

    public function index()
    {
        $data = [];
        $data['advisors'] = [];
        $activeTaskCount = 0;
        $overdueTaskCount = 0;
        $advisors = Auth::user()->advisors()->select('advisor_id')->get();
        foreach ($advisors as $key => $advisor) {
            $user = User::find($advisor->advisor_id);
            $data['advisors'][$key] = $user;
            $data['advisors'][$key]['activeTasks'] = $user->tasks()->activeTask()->count();
            $data['advisors'][$key]['overdueTasks'] = $user->tasks()->overDueTask()->count();
            $data['advisors'][$key]['clientCount'] = $user->clients()->count();
            $activeTaskCount += $data['advisors'][$key]['activeTasks'];
            $overdueTaskCount += $data['advisors'][$key]['activeTasks'];
        }
        $data['overview']['totalAdvisorCount'] = $advisors->count();;
        $data['overview']['totalClientCount'] = 0;
        $data['overview']['activeTaskCount'] = $activeTaskCount;
        $data['overview']['overdueTaskCount'] = $overdueTaskCount;
        return $this->json('Admin dashboard data retrieved successfully', $data);
    }

    /**
     * storing a admin.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreAdmin $request)
    {
        $newAdmin = DB::transaction(function () use ($request) {
            $request['password'] = Hash::make($request['password']);
            $admin = User::create($request->all() + [
                    'email_verified_at' => now(),
                ]);
            $admin->assignRole($request->role);
            auth()->user()->admins()->create([
                'admin_id' => $admin->id,
                'max_advisor' => $request->max_advisor,
            ]);
            return $admin;
        });
        return $this->json('Admin created successfully', new AdminResource($newAdmin,$request->max_advisor), Response::HTTP_CREATED);
    }

    /**
     * delete a admin.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAdmin($id)
    {
        $admin = User::findOrFail($id);
        $advisors = $admin->advisors()->get();
        return DB::transaction(function () use ($advisors, $admin,$id) {
            foreach ($advisors as $index) {
                $advisor = User::findOrFail($index->advisor_id);
                $advisor->tasks()->detach();
                $advisor->creatorTasks()->delete();
                $clients = $advisor->clients()->pluck('client_id');
                foreach ($clients as $client) {
                    $client = User::find($client);
                    $client->tasks()->detach();
                    $client->delete();
                }
                Advisor::where('advisor_id',$index->advisor_id)->delete();
                $advisor->delete();
            }
            Admin::where('admin_id',$id);
            $admin->delete();
            return $this->json('Admin removed successfully', Response::HTTP_NO_CONTENT);
        });
    }
}
