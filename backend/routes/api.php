<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpenPositionsController;

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

Route::get('phpinfo', function () {
    phpinfo();
});

Route::get('/download-open-positions', [OpenPositionsController::class, 'downloadFile']);

Route::get('/list-all-unique-assets', [OpenPositionsController::class, 'listAllAssets']);

Route::get('/list-position-by-asset', [OpenPositionsController::class, 'getOpenPositionsByAsset']);
