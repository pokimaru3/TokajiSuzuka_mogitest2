<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceClockTest extends TestCase
{
    use RefreshDatabase;

    public function test_現在の日時情報がUIと同じ形式で出力されている()
    {
        Carbon::setTestNow($now = Carbon::parse('2025-10-28 09:12:00'));

    /** @var \App\Models\User $user */
    $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('attendance.create'));
        $response->assertStatus(200);

        $expectedDate = Carbon::now()->isoFormat('YYYY年M月D日(ddd)');
        $expectedTime = Carbon::now()->format('H:i');

        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);
    }

    public function test_勤務外の場合、勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow(Carbon::parse('2025-10-28 09:00:00'));

    /** @var \App\Models\User $user */
    $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertStatus(200);

        $response->assertSee('勤務外');
    }

    public function test_出勤中の場合、勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow(Carbon::parse('2025-10-28 09:00:00'));

    /** @var \App\Models\User $user */
    $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'work_status' => 'working',
            'clock_in' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertSee('出勤中');
    }

    public function test_休憩中の場合、勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow(Carbon::parse('2025-10-28 12:00:00'));

    /** @var \App\Models\User $user */
    $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'work_status' => 'on_break',
            'clock_in' => Carbon::parse('2025-10-28 09:00:00'),
        ]);

        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertSee('休憩中');
    }

    public function test_退勤済の場合、勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow(Carbon::parse('2025-10-28 18:00:00'));

    /** @var \App\Models\User $user */
    $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'work_status' => 'finished',
            'clock_in' => Carbon::parse('2025-10-28 09:00:00'),
            'clock_out' => Carbon::parse('2025-10-28 18:00:00'),
        ]);

        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertSee('退勤済');
    }
}
