<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Attendance;

class AttendanceCorrectionRequestFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'attendance_id' => Attendance::factory(),
            'status' => 'pending',
            'remarks' => $this->faker->sentence(),
        ];
    }
}
