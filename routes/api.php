<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BooksController;
use App\Http\Controllers\LendingsController;
use App\Http\Controllers\NotesController;
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

    Route::get('/get-data-books', [BooksController::class, 'getDataBooks']);
    Route::get('/get-books-today', [BooksController::class, 'getBooksToday']);

    Route::group(['prefix' => 'notes'], function () {
        Route::get('/', [NotesController::class, 'index']);
        Route::get('/latest', [NotesController::class, 'show']);
        Route::post('/', [NotesController::class, 'store']);
        Route::put('/{id}', [NotesController::class, 'update']);
        Route::delete('/{id}', [NotesController::class, 'destroy']);
    });

    Route::prefix('students')->group(
        function () {
            Route::get('/', [StudentsController::class, 'index']);
            Route::get('/actives', [StudentsController::class, 'fetchStudentsActives']);
            Route::post('/', [StudentsController::class, 'store']);
            Route::get('/{id}', [StudentsController::class, 'show']);
        }
    );
    Route::get('/ranking', [StudentsController::class, 'readers']);

    Route::middleware('role:admin')->group(function () {
        Route::prefix('books')->group(
            function () {
                Route::get('/', [BooksController::class, 'index']);
                Route::get('/books-print', [BooksController::class, 'booksPrint']);
                Route::get('/actives', [BooksController::class, 'fetchBooksActives']);
                Route::post('/', [BooksController::class, 'store']);
                Route::put('/{id}', [BooksController::class, 'update']);
                Route::delete('/{id}', [BooksController::class, 'destroy']);
                Route::get('/print', [BooksController::class, 'printBooks']);
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
