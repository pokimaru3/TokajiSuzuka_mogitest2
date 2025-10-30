<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_出勤ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::parse('2025-10-28 09:00:00'));
        $user = User::factory()->create();

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get(route('attendance.create'));
        $response->assertSee('出勤');

        $resp = $this->actingAs($user)->postJson('/attendance', ['action' => 'clock_in']);
        $resp->assertStatus(200);
        $resp->assertJsonFragment(['status_text' => '出勤中']);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_status' => 'working',
        ]);
    }

    public function test_出勤は一日一回のみできる()
    {
        Carbon::setTestNow(Carbon::parse('2025-10-28 09:00:00'));
        $user = User::factory()->create();

        /** @var \App\Models\User $user */
        $this->actingAs($user)->postJson('/attendance', ['action' => 'clock_in']);

        $resp = $this->actingAs($user)->postJson('/attendance', ['action' => 'clock_in']);
        $resp->assertStatus(200);

        $count = Attendance::where('user_id', $user->id)->where('work_date', today())->count();
        $this->assertEquals(1, $count);
    }

    public function test_出勤時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow(Carbon::parse('2025-10-28 09:00:00'));
        $user = User::factory()->create();

        /** @var \App\Models\User $user */
        $this->actingAs($user)->postJson('/attendance', ['action' => 'clock_in']);

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('09:00');
    }

    public function test_休憩ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::parse('2025-10-28 10:00:00'));
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'work_status' => 'working',
            'clock_in' => Carbon::parse('2025-10-28 09:00:00'),
        ]);

        /** @var \App\Models\User $user */
        $this->actingAs($user)->get(route('attendance.create'))->assertSee('休憩入');

        $resp = $this->actingAs($user)->postJson('/attendance', ['action' => 'break_start']);
        $resp->assertJsonFragment(['status_text' => '休憩中']);
        $this->assertDatabaseHas('attendances', ['work_status' => 'on_break']);
    }

    public function test_休憩は一日に何回でもできる()
    {
        Carbon::setTestNow(Carbon::parse('2025-10-28 10:00:00'));
        $user = User::factory()->create();

        /** @var \App\Models\User $user */
        $this->actingAs($user)->postJson('/attendance', ['action' => 'clock_in']);

        $this->actingAs($user)->postJson('/attendance', ['action' => 'break_start'])
            ->assertJsonFragment(['status_text' => '休憩中']);

        Carbon::setTestNow($later = Carbon::parse('2025-10-28 10:15:00'));
        $this->actingAs($user)->postJson('/attendance', ['action' => 'break_end'])
            ->assertJsonFragment(['status_text' => '出勤中']);

        Carbon::setTestNow($later2 = Carbon::parse('2025-10-28 11:00:00'));
        $res = $this->actingAs($user)->postJson('/attendance', ['action' => 'break_start']);
        $res->assertStatus(200)
            ->assertJsonFragment(['status_text' => '休憩中']);

        $attendance = Attendance::where('user_id', $user->id)->first();
        $this->assertEquals(2, BreakTime::where('attendance_id', $attendance->id)->count());
    }

    public function test_休憩戻ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::parse('2025-10-28 12:00:00'));
        $user = User::factory()->create();

        /** @var \App\Models\User $user */
        $this->actingAs($user)->postJson('/attendance', ['action' => 'clock_in']);
        $this->actingAs($user)->postJson('/attendance', ['action' => 'break_start']);

        Carbon::setTestNow(Carbon::parse('2025-10-28 12:15:00'));
        $resp = $this->actingAs($user)->postJson('/attendance', ['action' => 'break_end']);
        $resp->assertJsonFragment(['status_text' => '出勤中']);
    }

    public function test_休憩戻は一日に何回でもできる()
    {
        Carbon::setTestNow($now = Carbon::parse('2025-10-28 09:00:00'));
        $user = User::factory()->create();

        /** @var \App\Models\User $user */
        $this->actingAs($user)->postJson('/attendance', ['action' => 'clock_in']);
        $this->actingAs($user)->postJson('/attendance', ['action' => 'break_start']);
        Carbon::setTestNow($later = Carbon::parse('2025-10-28 09:30:00'));
        $this->actingAs($user)->postJson('/attendance', ['action' => 'break_end']);

        Carbon::setTestNow($later2 = Carbon::parse('2025-10-28 10:30:00'));
        $this->actingAs($user)->postJson('/attendance', ['action' => 'break_start']);
        Carbon::setTestNow($later3 = Carbon::parse('2025-10-28 11:00:00'));
        $res = $this->actingAs($user)->postJson('/attendance', ['action' => 'break_end']);

        $res->assertStatus(200)
            ->assertJsonFragment(['status_text' => '出勤中']);

        $attendance = Attendance::where('user_id', $user->id)->first();
        $this->assertEquals(2, BreakTime::where('attendance_id', $attendance->id)->count());
    }

    public function test_休憩時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow(Carbon::parse('2025-10-28 10:00:00'));
        $user = User::factory()->create();

        /** @var \App\Models\User $user */
        $this->actingAs($user)->postJson('/attendance', ['action' => 'clock_in']);
        $this->actingAs($user)->postJson('/attendance', ['action' => 'break_start']);
        Carbon::setTestNow(Carbon::parse('2025-10-28 10:15:00'));
        $this->actingAs($user)->postJson('/attendance', ['action' => 'break_end']);

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('00:15');
    }

    public function test_退勤ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::parse('2025-10-28 18:00:00'));
        $user = User::factory()->create();

        /** @var \App\Models\User $user */
        $this->actingAs($user)->postJson('/attendance', ['action' => 'clock_in']);
        $this->actingAs($user)->get(route('attendance.create'))->assertSee('退勤');

        $resp = $this->actingAs($user)->postJson('/attendance', ['action' => 'clock_out']);
        $resp->assertJsonFragment(['status_text' => '退勤済']);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_status' => 'finished',
        ]);
    }

    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow(Carbon::parse('2025-10-28 09:00:00'));
        $user = User::factory()->create();

        /** @var \App\Models\User $user */
        $this->actingAs($user)->postJson('/attendance', ['action' => 'clock_in']);
        Carbon::setTestNow(Carbon::parse('2025-10-28 18:00:00'));
        $this->actingAs($user)->postJson('/attendance', ['action' => 'clock_out']);

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('18:00');
    }
}
