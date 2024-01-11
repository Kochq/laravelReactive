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

Route::get('/equipos/{carpeta}', [TxtController::class, 'ultimoReporteCliente']); // Ultimo reporte en general.txt (Todos los equipos)
Route::get('/equipos/{carpeta}/eq/{equipo}', [TxtController::class, 'ultimoReporteEquipo']); // Ultimo reporte de {equipo}

Route::get('/equipos/{carpeta}/fecha/{fecha}', [TxtController::class, 'fechaReporteCliente']); // Reporte en {fecha} (Todos los equipos)
Route::get('/equipos/{carpeta}/fecha/eq/{equipo}/{fecha}', [TxtController::class, 'fechaReporteEquipo']); // Reporte en {fecha} de {equipo}

Route::get('/equipos/{carpeta}/resumen', [TxtController::class, 'resumenUltimoReporteCliente']); // Resumen (estaRegando, estaEncendido...) (Todos los equipos)
Route::get('/equipos/{carpeta}/resumen/{pedido}', [TxtController::class, 'resumenUltimoReporteClientePedido']); // Resumen {pedido} (Todos los equipos)
