<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BooksController;
use App\Http\Controllers\LendingsController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\StarsController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\WishListController;
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
    Route::put('/user/update/{id}', [StudentsController::class, 'update']);
    Route::post('/user-avatar', [AuthController::class, 'addImage']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('books')->group(
        function () {
            Route::get('/', [BooksController::class, 'index']);
            Route::get('/actives', [BooksController::class, 'fetchBooksActives']);
            Route::get('/show/{id}', [BooksController::class, 'show']);
            Route::put('/add-image', [BooksController::class, 'addImage']);
        }
    );

    Route::group(['prefix' => 'notes'], function () {
        Route::get('/', [NotesController::class, 'index']);
        Route::get('/latest', [NotesController::class, 'show']);
        Route::post('/', [NotesController::class, 'store']);
        Route::put('/{id}', [NotesController::class, 'update']);
        Route::delete('/{id}', [NotesController::class, 'destroy']);
    });

    Route::prefix('students')->group(
        function () {
            Route::get('/ranking', [StudentsController::class, 'readers']);
            Route::get('/', [StudentsController::class, 'index']);
            Route::get('/books-reads', [StudentsController::class, 'booksRead']);
            Route::get('/actives', [StudentsController::class, 'fetchStudentsActives']);
            Route::post('/', [StudentsController::class, 'store']);
            Route::get('/{id}', [StudentsController::class, 'show']);
        }
    );

    Route::middleware('role:admin')->group(function () {
        Route::prefix('books')->group(
            function () {
                Route::get('/books-print', [BooksController::class, 'booksPrint']);
                Route::get('/get-data-books', [BooksController::class, 'getDataBooks']);
                Route::get('/get-books-today', [BooksController::class, 'getBooksToday']);
                Route::post('/', [BooksController::class, 'store']);
                Route::put('/update/{id}', [BooksController::class, 'update']);
                Route::delete('/delete/{id}', [BooksController::class, 'destroy']);
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

    Route::middleware('role:students')->group(function () {

        Route::group(['prefix' => 'books'], function () {
            Route::get('ranking-lending-book', [BooksController::class, 'booksMoreLending']);
            Route::get('ranking-rating-book', [BooksController::class, 'booksMoreRating']);
            Route::get('student/book-in-lending', [StudentsController::class, 'bookInLending']);
            Route::get('student/check-read-book/{id}', [StudentsController::class, 'checkUserReadBook']);
        });

        Route::prefix('wish-list')->group(
            function () {
                Route::get('/', [WishListController::class, 'index']);
                Route::post('/', [WishListController::class, 'store']);
                Route::get('/{id}', [WishListController::class, 'show']);
                Route::get('/check-list/{id}', [WishListController::class, 'hasInWishList']);
                Route::delete('/{id}', [WishListController::class, 'destroy']);
            }
        );

        Route::prefix('stars')->group(
            function () {
                Route::post('/', [StarsController::class, 'rateBook']);
                Route::put('/{id}', [StarsController::class, 'updateRate']);
            }
        );
    });
});
