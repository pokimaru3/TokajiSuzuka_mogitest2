<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $clockIn = Carbon::parse($this->faker->dateTimeBetween('-6 months', 'now'))->setTime(9, 0);
        $clockOut = (clone $clockIn)->setTime(18, 0);

        return [
            'user_id' => null,
            'work_date' => $clockIn->toDateString(),
            'clock_in' => $clockIn->format('H:i:s'),
            'clock_out' => $clockOut->format('H:i:s'),
            'work_status' => 'finished',
            'total_break_time' => 60,
            'total_work_time' => 480,
        ];
    }
}
