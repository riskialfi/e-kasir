<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReceiptController;

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
Route::get('/receipt/print/{transactionId}', [App\Http\Controllers\ReceiptController::class, 'print'])->name('receipt.print');