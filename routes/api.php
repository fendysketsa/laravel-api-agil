<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ScheduleController;


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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [UserController::class, 'login']);

Route::get('user-list', [UserController::class, 'userList']);
Route::get('user-list-schedule', [ScheduleController::class, 'userListSchedule']);

Route::group(['middleware' => 'auth:api'], function () {

    Route::post('create-schedule', [ScheduleController::class, 'createSchedule']);
    Route::put('update-schedule', [ScheduleController::class, 'updateSchedule']);
    Route::get('user-schedule-detail', [ScheduleController::class, 'userScheduleDetail']);
    Route::post('user-schedule-delete', [ScheduleController::class, 'userScheduleDelete']);

    Route::post('send-push-notification', [UserController::class, 'sendPushNotification']);

    Route::post('create-team', [UserController::class, 'createTeam']);
    Route::post('store-token', [UserController::class, 'updateToken']);
    Route::get('user-detail', [UserController::class, 'userDetail']);
    Route::post('user-delete', [UserController::class, 'userDelete']);

    Route::post('logout', 'Api\UserController@logout');
});
