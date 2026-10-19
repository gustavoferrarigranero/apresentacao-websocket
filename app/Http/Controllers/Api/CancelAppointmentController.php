<?php

namespace App\Http\Controllers\Api;

use App\Events\AppointmentCancelled;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CancelAppointmentController extends Controller
{
    public function __invoke(Request $request, Appointment $appointment): JsonResponse
    {
        abort_unless($request->user()->clinic_id === $appointment->clinic_id, 403);
        $appointment->update(['status' => 'cancelled']);
        AppointmentCancelled::dispatch($appointment->refresh());

        return response()->json(['data' => $appointment]);
    }
}
