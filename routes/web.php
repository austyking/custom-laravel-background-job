<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RunnerJobsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

//Route::get('/dashboard', function () {
//    return view('dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/run-job', [RunnerJobsController::class, 'runJob']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', [RunnerJobsController::class, 'index'])->name('dashboard');
    Route::post('/background-jobs/{id}/cancel', [RunnerJobsController::class, 'cancel'])->name('runner.jobs.cancel');

});

require __DIR__.'/auth.php';
