<?php

use App\Http\Controllers\ImageController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StorageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => '/project'], function () {
    Route::get('/', [ProjectController::class, 'index'])->name('show.project');
    Route::post('/', [ProjectController::class, 'store'])->name('store.project');
    Route::get('/{project}', [ProjectController::class, 'edit'])->name('edit.project');
    Route::post('/{project}', [ProjectController::class, 'update'])->name('update.project');
    Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('delete.project');
});

Route::delete('/image/{image}', [ImageController::class, 'destroy'])->name('delete.image');
Route::get('/image/project/{projectId}', [ImageController::class, 'getImageByProjectId'])->name('get.image.project');

Route::get('secure-assets/{path_1?}/{path_2?}/{file?}', [StorageController::class, 'show'])
    ->name('secure-assets');
