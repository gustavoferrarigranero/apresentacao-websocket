<?php

namespace App\Http\Controllers\Api;

use App\Events\AppointmentStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateAppointmentStatusController extends Controller
{
    public function __invoke(Request $request, Appointment $appointment): JsonResponse
    {
        abort_unless($request->user()->clinic_id === $appointment->clinic_id, 403);
        $validated = $request->validate(['status' => ['required', 'in:scheduled,confirmed,cancelled,completed']]);
        $appointment->update($validated);
        AppointmentStatusChanged::dispatch($appointment->refresh());

        return response()->json(['data' => $appointment]);
    }
}
