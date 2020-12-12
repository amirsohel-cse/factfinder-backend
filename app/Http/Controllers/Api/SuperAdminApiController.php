<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\JsonResponseTrait;
use App\Http\Resources\SuperAdmin\DashboardCollection;
use App\Models\Client;
use App\Models\Task;
use App\User;
class SuperAdminApiController extends Controller
{
    use JsonResponseTrait;

    public function index(){
        $data['admins'] = [];
        $admins = User::roleUser('admin');
        $adminCount = $admins->count();
        $advisorCount = User::roleUser('advisor')->count();
        $clientCount = User::roleUser('client')->count();
        $activeTaskCount = Task::adminActiveTask()->count();
        $adminList = $admins->get();
        if ($adminList->isNotEmpty())
        {
            foreach ($adminList as $key => $admin) {
                $advisors = $admin->advisors()->get();
                $client = 0;
                foreach ($advisors as $index => $advisor) {
                    $client += Client::where('advisor_id',$advisor->advisor_id)->count();
                }
                $data['admins'][$key]= $admin;
                $data['admins'][$key]['advisorCount'] = $advisors->count();
                $data['admins'][$key]['clientCount'] = $client;
            }
        }
        $data['overview']['totalAdminCount'] = $adminCount;
        $data['overview']['totalAdvisorCount'] = $advisorCount;
        $data['overview']['totalClientCount'] = $clientCount;
        $data['overview']['totalActiveTaskCount'] = $activeTaskCount;
       return $this->json('Super admin dashboard data retrieved successfully',$data);
    }

}
