<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-10-29',
            'clock_in' => '2025-10-29 09:00:00',
            'clock_out' => '2025-10-29 18:00:00',
            'remarks' => '通常勤務',
        ]);

        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)->get("/admin/attendance/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('通常勤務');
    }

    public function test_出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)->put(route('attendance.update', $attendance->id), [
            'clock_in' => '19:00',
            'clock_out' => '09:00',
            'remarks' => '備考',
        ]);

        $response->assertSessionHasErrors(['clock_in']);
        $this->assertStringContainsString(
            '出勤時間もしくは退勤時間が不適切な値です',
            session('errors')->first('clock_in')
        );
    }

    public function test_休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)->put(route('attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start_1' => '19:00',
            'break_end_1' => '19:30',
            'remarks' => '休憩テスト',
        ]);

        $response->assertSessionHasErrors(['break_start_1']);
        $this->assertStringContainsString(
            '休憩時間もしくは退勤時間が不適切な値です',
            session('errors')->first('break_start_1')
        );
    }

    public function test_休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)->put(route('attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start_1' => '17:30',
            'break_end_1' => '19:00',
            'remarks' => '休憩テスト',
        ]);

        $response->assertSessionHasErrors(['break_end_1']);
        $this->assertStringContainsString(
            '休憩時間もしくは退勤時間が不適切な値です',
            session('errors')->first('break_end_1')
        );
    }

    public function test_備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)->put(route('attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'remarks' => '',
        ]);

        $response->assertSessionHasErrors(['remarks']);
        $this->assertStringContainsString(
            '備考を記入してください',
            session('errors')->first('remarks')
        );
    }
}
