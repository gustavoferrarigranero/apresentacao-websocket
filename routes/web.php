<?php

use App\Models\Appointment;
use App\Models\Clinic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/demo/realtime/{clinic}', function (Clinic $clinic) {
    abort_unless(app()->environment(['local', 'testing']), 404);
    Auth::login($clinic->users()->firstOrFail());

    return view('realtime-dashboard', [
        'clinic' => $clinic,
        'appointments' => $clinic->appointments()->latest()->limit(20)->get(),
    ]);
});

Route::get('test', function () {
    $appointment = Appointment::find(12);

    $appointment->status = 'cancelled';
    $appointment->save();

    App\Events\AppointmentStatusChanged::dispatch($appointment);
});
