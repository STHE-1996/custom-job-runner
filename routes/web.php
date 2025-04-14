<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\DashboardController;
use App\Jobs\TestJob;
use App\Jobs\TestJob2;
use App\Http\Controllers\JobController;


Route::get('/', [DashboardController::class, 'index']);

Route::get('/test-fire-job', [TestController::class, 'fireJob']); 
Route::get('/dashboard', [DashboardController::class, 'index']);
Route::delete('/jobs/{id}/cancel', [JobController::class, 'cancel'])->name('jobs.cancel');
Route::post('/retry-job', [JobController::class, 'retryJob'])->name('retry_job');
Route::post('/cancel-job', [JobController::class, 'cancelJob'])->name('cancel_job');



