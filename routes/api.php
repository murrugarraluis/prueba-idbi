<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComprobanteController;
use App\Http\Controllers\UserController;

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

// Autenticación
Route::post('/users', [UserController::class,'store']);  //Solo por motivos de pruebas se da esta api sin middelware
Route::post('/login', [AuthController::class, 'login']); // Login y obtención del JWT
Route::post('/logout', [AuthController::class, 'logout'])->middleware('jwt.verify');; // Logout y invalidación del JWT

// Grupo de rutas protegidas con el middleware JWT para asegurarse de que solo los usuarios autenticados puedan acceder.
Route::group(['middleware' => ['jwt.verify']], function () {

    // Comprobantes
    Route::post('/comprobantes', [ComprobanteController::class, 'store']); // Registrar uno o varios comprobantes
    Route::get('/comprobantes', [ComprobanteController::class, 'index']); // Listar comprobantes de forma paginada
    Route::get('/comprobantes/{id}', [ComprobanteController::class, 'show']); // Obtener detalles de un comprobante específico
    Route::delete('/comprobantes/{id}', [ComprobanteController::class, 'destroy']); // Eliminar un comprobante

    Route::get('articulo/{nombreArticulo}/monto-total', [ComprobanteController::class, 'getTotalAmountByArticle']); // Monto total por artículo de un usuario en específico  
    Route::get('comprobantes/monto/total', [ComprobanteController::class, 'getTotalAmount']); // Monto total acumulado por todos los comprobantes de un usuario específico
    

});
