<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', fn (User $user, int $id): bool => $user->id === $id);
Broadcast::channel('clinic.{clinicId}', fn (User $user, int $clinicId): bool => (int) $user->clinic_id === $clinicId);

Broadcast::channel('agenda.{clinicId}', function (User $user, int $clinicId): array|false {
    if ((int) $user->clinic_id !== $clinicId) {
        return false;
    }

    return ['id' => $user->id, 'name' => $user->name];
});
