<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebRTCController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/call', [WebRTCController::class, 'index']);

Route::post('/offer', [WebRTCController::class, 'storeOffer']);
Route::get('/offer/{room}', [WebRTCController::class, 'getOffer']);

Route::post('/answer', [WebRTCController::class, 'storeAnswer']);
Route::get('/answer/{room}', [WebRTCController::class, 'getAnswer']);

require __DIR__.'/auth.php';
