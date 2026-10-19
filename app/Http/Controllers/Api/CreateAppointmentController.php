<?php

namespace App\Http\Controllers\Api;

use App\Events\AppointmentCreated;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Notifications\UrgentAppointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CreateAppointmentController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'patient_id' => ['required', 'integer', Rule::exists('patients', 'id')->where('clinic_id', $user->clinic_id)],
            'starts_at' => ['required', 'date', 'after:now'],
            'urgent' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $appointment = Appointment::create([...$validated, 'clinic_id' => $user->clinic_id, 'status' => 'scheduled']);

        AppointmentCreated::dispatch($appointment);

        if ($appointment->urgent) {
            $appointment->clinic->users->each->notify(new UrgentAppointment($appointment));
        }

        return response()->json(['data' => $appointment], 201);
    }
}
