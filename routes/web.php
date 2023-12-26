<?php

use App\Http\Controllers\TxtController;
use App\Http\Controllers\UserController;
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
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: *');
header('Access-Control-Allow-Headers: *');

Route::get('/', UserController::class);

Route::get('/general/{carpeta}', [TxtController::class, 'parsearCompleto']);
Route::get('/graficos/{carpeta}', [TxtController::class, 'parsearGraficos']);
Route::get('/graficos2/{carpeta}', [TxtController::class, 'parsearGraficos2']);
Route::get('/graficos/{carpeta}/{pedido}', [TxtController::class, 'parsearPedido']);
