<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BooksController;
use App\Http\Controllers\LendingsController;
use App\Http\Controllers\StudentsController;
use Illuminate\Support\Facades\Route;

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

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/user-avatar', [AuthController::class, 'addImage']);

    Route::prefix('students')->group(
        function () {
            Route::get('/', [StudentsController::class, 'index']);
            Route::post('/', [StudentsController::class, 'store']);
            Route::get('/{id}', [StudentsController::class, 'show']);
        }
    );

    Route::middleware('role:admin')->group(function () {
        Route::prefix('books')->group(
            function () {
                Route::get('/', [BooksController::class, 'index']);
                Route::post('/', [BooksController::class, 'store']);
                Route::get('/{id}', [BooksController::class, 'show']);
                Route::put('/{id}', [BooksController::class, 'update']);
                Route::delete('/{id}', [BooksController::class, 'destroy']);
            }
        );

        Route::prefix('lendings')->group(
            function () {
                Route::get('/', [LendingsController::class, 'index']);
                Route::post('/', [LendingsController::class, 'create']);
                Route::put('/{id}', [LendingsController::class, 'checked']);
            }
        );
    });
});
