<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/auth/register',[Api\AuthController::class, 'register'])->name('register.api');
Route::post('/auth/login',[Api\AuthController::class, 'login'])->name('login.api');
Route::middleware('auth:api')->group(function () {
    Route::post('/auth/logout', [Api\AuthController::class, 'logout'])->name('logout.api');
    Route::post('/loan/apply', [Api\LoanController::class, 'apply'])->name('loan.apply.api');
    Route::post('/loan/repay', [Api\LoanController::class, 'repay'])->name('loan.apply.repay');
});

Route::put('admin/loan/update-status', [Api\LoanController::class,'updateStatus'])->name('admin.loan.updateStatus.api');
Route::get('admin/loan/show', [Api\LoanController::class,'show'])->name('admin.loan.show.api');
