<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class DummyAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::factory(2)->create();

        $start = Carbon::now()->subMonths(6)->startOfMonth();
        $end = Carbon::now()->endOfDay();

        $dateRange = [];
        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $dateRange[] = $date->copy();
        }

        foreach ($users as $user) {
            $workDays = collect($dateRange)->random(30);

            foreach ($workDays as $date) {
                $clockIn  = $date->copy()->setTime(9, 0);
                $clockOut = $date->copy()->setTime(18, 0);

                $attendance = Attendance::factory()->create([
                    'user_id' => $user->id,
                    'work_date' => $date->toDateString(),
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                ]);

                BreakTime::factory()->create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $date->copy()->setTime(12, 0),
                    'break_end' => $date->copy()->setTime(13, 0),
                ]);
            }
        }
    }
}
