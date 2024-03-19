<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::controller(TaskController::class)
    ->as('tasks.')
    ->prefix('tasks')
    ->middleware('auth:api')
    ->group(static function () {
        Route::post('/', 'store')->name('store');
        Route::get('/', 'index')->name('index');
        Route::get('/{id}', 'show')->name('show');
        Route::match(['put', 'patch'], '/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });

Route::controller(AuthenticationController::class)
    ->as('auth.')
    ->prefix('auth')
    ->group(static function () {
        Route::middleware('auth:api')->group(static function () {
            Route::post('logout', 'logout')->name('logout');
            Route::post('refresh', 'refresh')->name('refresh');
            Route::get('me', 'me')->name('me');
        });

        Route::post('login', 'login')->name('login');
    });
