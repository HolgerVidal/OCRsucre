<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ApiTokenMiddleware;
use App\Http\Controllers\OcrController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware([ApiTokenMiddleware::class])->group(function () {
    Route::post("obtener-texto-pdf", [OcrController::class, "obtener_texto_pdf"]);

    Route::post("crear-img", [OcrController::class, "crear_img"]);
    Route::post("obtener-texto-img", [OcrController::class, "obtener_texto_img"]);

    Route::post("contar-hojas-pdf", [OcrController::class, "contar_hojas_pdf"]);
});
