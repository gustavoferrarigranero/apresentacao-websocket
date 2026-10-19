<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Clinic::factory()->count(2)->create()->each(function (Clinic $clinic) {
            User::factory()->create(['clinic_id' => $clinic->id]);
            Patient::factory()->count(8)->for($clinic)->create()->each(function (Patient $patient) use ($clinic) {
                Appointment::factory()->count(3)->for($clinic)->for($patient)->create();
            });
        });
    }
}
