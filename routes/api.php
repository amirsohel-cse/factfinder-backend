<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// AUTHENTICATIONS ROUTES ARE HERE

Route::post('/register', 'Api\AuthApiController@register');
Route::post('/login', 'Api\AuthApiController@login');
Route::post('/password/email', 'Api\ForgotPasswordController@sendResetLinkEmail');
Route::post('/password/reset', 'Api\ResetPasswordController@reset');

Route::group(['middleware' => ['auth:api']], function() {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout','Api\AuthApiController@logout');

});
Route::group(['prefix' => 'super-admin','middleware' => ['auth:api','role:super-admin']],function (){
    Route::apiResource('dashboard','Api\SuperAdminApiController');
    Route::post('create/admin','Api\AdminApiController@store');
    Route::delete('terminate/{id}/admin','Api\AdminApiController@deleteAdmin');
});
Route::group(['prefix' => 'admin','middleware' => ['auth:api','role:admin']],function (){
    Route::apiResource('dashboard','Api\AdminApiController');
    Route::post('create/advisor','Api\AdvisorApiController@store');
    Route::delete('terminate/{id}/advisor','Api\AdvisorApiController@deleteAdvisor');
});
Route::group(['prefix' => 'advisor','middleware' => ['auth:api','role:advisor']],function (){
    Route::apiResource('dashboard','Api\AdvisorApiController');
    Route::post('create/client','Api\ClientApiController@store');
    Route::delete('terminate/{id}/client','Api\ClientApiController@deleteClient');
});

Route::group(['middleware' => ['auth:api','role:client|advisor']],function (){
    Route::post('task/mark-as-completed','Api\TaskApiController@markAsCompleted');
});
Route::group(['middleware' => ['auth:api','role:super-admin|advisor']],function (){
    Route::apiResource('task','Api\TaskApiController');
});

Route::group(['prefix' => 'client','middleware' => ['auth:api','role:client']],function (){
    Route::apiResource('notifications','Api\NotificationApiController');
    Route::apiResource('vision','Api\VisionApiController');
    Route::post('mark-as-read', 'Api\NotificationApiController@markNotification');
    Route::get('ongoing/tasks', 'Api\ClientApiController@ongoingTasks');
});


