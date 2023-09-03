<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BallController;
use App\Http\Controllers\BucketController;

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
    return view('welcome');
});

Route::get('/bucket-and-balls/form', [BucketController::class, 'index'])->name('bucketAndBallFrom');

Route::post('/bucket', [BucketController::class, 'store'])->name('bucket.store');

Route::post('/ball', [BallController::class, 'store'])->name('ball.store');

Route::get('/balls', [BallController::class, 'index'])->name('ball.index');

Route::post('/suggest-buckets', [BucketController::class, 'suggestBuckets'])->name('suggest-buckets');

Route::get('/suggested-buckets-list', [BucketController::class, 'suggestedBucketsList'])->name('suggested-buckets-list');