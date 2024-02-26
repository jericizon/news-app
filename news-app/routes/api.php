<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewsAppController;

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

Route::get('/get-pinned-articles', [NewsAppController::class, 'getPinnedArticles']);

Route::post('/news-app', [NewsAppController::class, 'search']);
Route::delete('/unpin-article/{articleId}', [NewsAppController::class, 'unPinArticle']);
Route::post('/pin-article', [NewsAppController::class, 'pinArticle']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
