<?php

namespace Tests\Feature;

use App\Events\AppointmentCancelled;
use App\Events\AppointmentCreated;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\UrgentAppointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RealtimeModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_creation_broadcasts_and_urgent_notification_uses_all_channels(): void
    {
        Event::fake([AppointmentCreated::class]);
        Notification::fake();
        $clinic = Clinic::factory()->create();
        $user = User::factory()->create(['clinic_id' => $clinic->id]);
        $recipient = User::factory()->create(['clinic_id' => $clinic->id]);
        $patient = Patient::factory()->for($clinic)->create();

        $this->actingAs($user)->postJson('/api/appointments', [
            'patient_id' => $patient->id,
            'starts_at' => now()->addDay()->toIso8601String(),
            'urgent' => true,
        ])->assertCreated()->assertJsonPath('data.urgent', true);

        Event::assertDispatched(AppointmentCreated::class);
        Notification::assertSentTo([$user, $recipient], UrgentAppointment::class, function ($notification, $channels) {
            return $channels === ['database', 'broadcast', 'mail'];
        });
    }

    public function test_cancellation_broadcasts_and_cross_clinic_access_is_forbidden(): void
    {
        Event::fake([AppointmentCancelled::class]);
        $appointment = Appointment::factory()->create();
        $authorized = User::factory()->create(['clinic_id' => $appointment->clinic_id]);
        $unauthorized = User::factory()->create(['clinic_id' => Clinic::factory()->create()->id]);

        $this->actingAs($unauthorized)->deleteJson('/api/appointments/'.$appointment->id)->assertForbidden();
        $this->actingAs($authorized)->deleteJson('/api/appointments/'.$appointment->id)
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        Event::assertDispatched(AppointmentCancelled::class);
    }

    public function test_private_and_presence_channels_enforce_clinic_isolation(): void
    {
        $clinic = Clinic::factory()->create();
        $user = User::factory()->create(['clinic_id' => $clinic->id]);
        $otherClinic = Clinic::factory()->create();
        $channels = Broadcast::connection()->getChannels();
        $userChannel = $channels->get('App.Models.User.{id}');
        $private = $channels->get('clinic.{clinicId}');
        $presence = $channels->get('agenda.{clinicId}');

        $this->assertTrue($userChannel($user, $user->id));
        $this->assertFalse($userChannel($user, $user->id + 1));
        $this->assertTrue($private($user, $clinic->id));
        $this->assertFalse($private($user, $otherClinic->id));
        $this->assertSame(['id' => $user->id, 'name' => $user->name], $presence($user, $clinic->id));
        $this->assertFalse($presence($user, $otherClinic->id));
    }

    public function test_demo_dashboard_contains_realtime_state_targets(): void
    {
        $clinic = Clinic::factory()->create();
        User::factory()->create(['clinic_id' => $clinic->id]);

        $this->get('/demo/realtime/'.$clinic->id)
            ->assertOk()
            ->assertSee('data-online-count', false)
            ->assertSee('data-appointments', false)
            ->assertSee('data-clinic-id', false);
    }
}
