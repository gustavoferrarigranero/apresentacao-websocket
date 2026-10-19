<?php

use App\Http\Controllers\Api\CancelAppointmentController;
use App\Http\Controllers\Api\CreateAppointmentController;
use App\Http\Controllers\Api\UpdateAppointmentStatusController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['status' => 'ok']);
Route::middleware('auth')->group(function () {
    Route::post('/appointments', CreateAppointmentController::class);
    Route::patch('/appointments/{appointment}/status', UpdateAppointmentStatusController::class);
    Route::delete('/appointments/{appointment}', CancelAppointmentController::class);
});
