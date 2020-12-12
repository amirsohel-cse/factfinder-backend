<?php

namespace App\Http\Controllers\Api;

use App\Models\Admin;
use App\Models\Advisor;
use App\Models\Client;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use phpDocumentor\Reflection\Types\Null_;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Hash;
use function GuzzleHttp\Promise\all;

class UserController extends Controller
{
    public function index(Request $request)
    {
     //
    }

    public function create(Request $request)
    {
        $validator = $request->validate([
            'role' => ['required', 'integer'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = new User;
        $user->name = $request->first_name . " " . $request->last_name;
        $user->first_name = !empty($request->first_name) ? $request->first_name : "firstName";
        $user->last_name = !empty($request->last_name) ? $request->last_name : "lastName";
        $user->username = $request->username;
        $user->email = $request->email;
        $user->username = $request->username;
        $user->password = Hash::make($request->password);
        $user->status = 'activated';
        $user->save();

        $auth_user = auth()->user();
        $user_id = User::where('email', $request->email)->select('id')->first();

        $role_id = $request->input('role');

        if ($role_id == 2) {
            $admin = new Admin();
            $admin->super_admin_id = $auth_user->id;;
            $admin->admin_id = $user_id->id;
            $admin->max_advisor = $request->max_advisor;
            $admin->save();
        }
        if ($role_id == 3) {
            $advisor = new Advisor();
            $advisor->admin_id = $auth_user->id;;
            $advisor->advisor_id = $user_id->id;
            $advisor->save();
        }
        if ($role_id == 4) {
            $client = new Client();
            $client->advisor_id = $auth_user->id;;
            $client->client_id = $user_id->id;
            $client->save();
        }

        $role = Role::findOrFail($role_id);
        $user->assignRole($role->name);

        $data['users'] = $user;
        $data['goto'] = route('admin.user.index');
        return $this->sendResponse($data, "User Created Successfully", Response::HTTP_CREATED);

    }

    public function update(Request $request)
    {
        $user = User::findOrFail($request->id);
        $validator = $request->validate([
            'surname' => ['max:255'],
            'first_name' => ['max:255'],
            'last_name' => ['max:255'],
            'username' => ['string', 'max:255',
                Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id)],

        ]);

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->username = $request->username;
        $user->password = Hash::make($request->password);
        $user->status = 'activated';

        $role_id = $request->input('role');
        $user_role = $user->roles->first();

        if ($user_role->id != $role_id) {
            $user->removeRole($user_role->name);

            $role = Role::findOrFail($role_id);
            $user->assignRole($role->name);
        }

        return response()->json(['success' => true, 'status' => 'success', 'message' => _lang('User Updated'), 'goto' => route('admin.user.index')]);

    }

//    public function deleteAdvisor($id)
//    {
//        DB::transaction(function () use ($id) {
//            $advisor = User::findOrFail($id);
//            $advisor->tasks()->detach();
//            $advisor->creatorTasks()->delete();
//            $clients = $advisor->clients()->pluck('client_id');
//            foreach ($clients as $client) {
//                $client = User::find($client);
//                $client->tasks()->detach();
//                $client->delete();
//            }
//            $advisor->delete();
//        });
//        return $this->sendResponse(true, 'advisor removed successfully', Response::HTTP_OK);
//    }

    public function dashboardCount()
    {
        $admins = User::roleUser(2)->count();
        $advisors = User::roleUser(3)->count();
        $clients = User::roleUser(4)->count();
        $tasks = Task::adminActiveTask()->count();
        $data['admins'] = $admins;
        $data['advisors'] = $advisors;
        $data['clients'] = $clients;
        $data['tasks'] = $tasks;
        return $this->sendResponse($data, 'dashboard data retrieved successfully', Response::HTTP_OK);
    }

    public function adminList()
    {
        $data = [];
        $admins = User::roleUser(2)->get();
        if ($admins->isNotEmpty()) {
            foreach ($admins as $key => $admin) {
                $advisors = Advisor::query()
                    ->where('admin_id', $admin->id)
                    ->get();
                $data[$key] = $admin;
                $data[$key]['advisor_count'] = $advisors->count();
                $client = 0;
                foreach ($advisors as $index => $advisor) {
                    $client += Client::where('advisor_id', $advisor->advisor_id)->count();
                }
                $data[$key]['client_count'] = $client;
            }
        }
        return $this->sendResponse($data, 'Admins retrieved successfully', \Illuminate\Http\Response::HTTP_OK);
    }
}
