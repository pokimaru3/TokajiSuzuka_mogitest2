<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_自分が行った勤怠情報が全て表示されている()
    {
        $user = User::factory()->create();
        $today = Carbon::today();

        Attendance::factory()->count(3)->create([
            'user_id' => $user->id,
            'work_date' => $today,
        ]);

        $otherUser = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'work_date' => $today,
        ]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get('/attendance/list');

        $todayFormatted = $today->isoFormat('M月D日');
        $response->assertSee($todayFormatted);

        $response->assertDontSee($otherUser->name);
    }

    public function test_勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        $user = User::factory()->create();
        $currentMonth = Carbon::now()->isoFormat('YYYY年M月');

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSee($currentMonth);
    }

    public function test_「前月」を押下した時に表示月の前月の情報が表示される()
    {
        $user = User::factory()->create();
        $previousMonth = Carbon::now()->subMonth();
        $attendanceDate = $previousMonth->copy()->day(10);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $attendanceDate,
        ]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get('/attendance/list?month=' . $previousMonth->format('Y-m'));

        $response->assertSee($attendanceDate->isoFormat('M月D日'));
    }

    public function test_「翌月」を押下した時に表示月の翌月の情報が表示される()
    {
        $user = User::factory()->create();
        $nextMonth = Carbon::now()->addMonth();
        $attendanceDate = $nextMonth->copy()->day(15);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $attendanceDate,
        ]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get('/attendance/list?month=' . $nextMonth->format('Y-m'));

        $response->assertSee($attendanceDate->isoFormat('M月D日'));
    }

    public function test_「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
        ]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);

        $response->assertSee(Carbon::today()->isoFormat('M月D日'));
    }
}
