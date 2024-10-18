<?php

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    // return view('welcome');
    return redirect('home');
});

Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function() {
    Route::get('/home', [HomeController::class, 'index']);

    Route::prefix('/categoria')->group(function(){
        Route::get('/listado', [CategoriaController::class, 'listado']);
        Route::post('/ajaxListado', [CategoriaController::class, 'ajaxListado']);
        Route::post('/agregarCategoria', [CategoriaController::class, 'agregarCategoria']);
    });

});
