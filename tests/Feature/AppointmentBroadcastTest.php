<?php

namespace Tests\Feature;

use App\Events\AppointmentStatusChanged;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AppointmentBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_update_dispatches_the_realtime_event(): void
    {
        Event::fake([AppointmentStatusChanged::class]);
        $appointment = Appointment::factory()->create();
        $user = User::factory()->create(['clinic_id' => $appointment->clinic_id]);

        $this->actingAs($user)->patchJson('/api/appointments/'.$appointment->id.'/status', ['status' => 'confirmed'])
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed');

        Event::assertDispatched(fn (AppointmentStatusChanged $event) => $event->appointment->is($appointment));
    }

    public function test_event_payload_contains_only_realtime_fields(): void
    {
        $appointment = Appointment::factory()->create(['status' => 'confirmed']);
        $event = new AppointmentStatusChanged($appointment);

        $this->assertSame(['appointment'], array_keys($event->broadcastWith()));
        $this->assertSame(['id', 'starts_at', 'status', 'urgent'], array_keys($event->broadcastWith()['appointment']));
    }
}
