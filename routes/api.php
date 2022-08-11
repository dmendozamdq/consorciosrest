<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

/******************************************************************/
//Ruteo MEDIOS DE PAGO

//Ruteo insertar medios de pago
Route::post('medios_pago/agregar_medio_pago/{nombre}', 'Medios_PagoController@agregar_medio_pago');

//Ruteo borrar medios de pago (PASO ID DEL MEDIO DE PAGO)
Route::put('medios_pago/borrar_medio_pago/{id}', 'Medios_PagoController@borrar_medio_pago');
