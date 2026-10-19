<?php

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentStatusChanged implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Appointment $appointment) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('clinic.'.$this->appointment->clinic_id)];
    }

    public function broadcastAs(): string
    {
        return 'appointment.status.changed';
    }

    public function broadcastWith(): array
    {
        return ['appointment' => $this->appointment->only(['id', 'starts_at', 'status', 'urgent'])];
    }
}
