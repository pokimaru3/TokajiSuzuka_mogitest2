<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use Carbon\Carbon;

class BreakTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_id' => null,
            'break_start' => Carbon::today()->setTime(12, 0),
            'break_end' => Carbon::today()->setTime(13, 0),
        ];
    }
}
