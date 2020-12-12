<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\JsonResponseTrait;
use App\Models\Advisor;
use App\Models\Client;
use App\Models\Task;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationApiController extends Controller
{
    use JsonResponseTrait;

    public function index(){
       $notifications = auth()->user()->unreadNotifications()->paginate(10);
       return $this->json('Notifications retrieved successfully',new LengthAwarePaginator(
           $notifications->items(),
           $notifications->total(),
           $notifications->perPage(),
           $notifications->currentPage(),
           [
               'path' => app('url')->current(),
           ]
       ));
    }

    public function markNotification(Request $request){
        auth()->user()
        ->unreadNotifications
        ->when($request->input('id'), function ($query) use ($request) {
            return $query->where('id', $request->input('id'));
        })
        ->markAsRead();
        return $this->json('Mark as read is successful',Response::HTTP_NO_CONTENT);
    }
}
