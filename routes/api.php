<?php

use App\Http\Controllers\Api\V1\UsersController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BoardController;
use App\Http\Controllers\Api\V1\ListsController;
use App\Http\Controllers\API\V1\CardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'web'], function () {
    // Users
    Route::apiResource('/users', UsersController::class);
    // Users Boards
    Route::get('/users/{user}/boards', [UsersController::class, 'getUserBoards']);

    // Authentication
    Route::post('/login', [AuthController::class, 'authenticate']);
    Route::post('/register', [AuthController::class, 'register']);

    // Board
    Route::apiResource('/boards', BoardController::class);
    // Board Users
    Route::get('/boards/{id}/users', [BoardController::class, 'getBoardUsers']);
    Route::get('/boards/{id}/lists', [BoardController::class, 'getBoardLists']);

    // List
    Route::apiResource('/lists', ListsController::class);
    Route::post('/boards/{id}/lists', [ListsController::class, 'addList']);

    // Card
    Route::apiResource('/cards', CardController::class);

});