<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceCorrectionRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠詳細画面の「名前」がログインユーザーの氏名になっている()
    {
        $user = User::factory()->create(['name' => '山田太郎']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-10-25'
        ]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertSee('山田太郎');
    }

    public function test_勤怠詳細画面の「日付」が選択した日付になっている()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-10-26'
        ]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertSee('2025年10月26日');
    }

    public function test_「出勤・退勤」時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-10-27',
            'clock_in' => '2025-10-27 09:00:00',
            'clock_out' => '2025-10-27 18:00:00',
        ]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_「休憩」時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-10-28',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '2025-10-28 12:00:00',
            'break_end' => '2025-10-28 13:00:00',
        ]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    public function test_出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->put("/attendance/detail/{$attendance->id}", [
            'clock_in' => '18:00',
            'clock_out' => '09:00',
            'remarks' => 'テスト',
        ]);

        $response->assertSessionHasErrors(['clock_in']);
        $response->assertRedirect();
    }

    public function test_休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->put("/attendance/detail/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => '19:00',
            'break_end' => '19:30',
            'remarks' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors(['break_start']);
    }

    public function test_休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->put("/attendance/detail/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => '17:00',
            'break_end' => '19:00',
            'remarks' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors(['break_end']);
    }

    public function test_備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->put("/attendance/detail/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'remarks' => '',
        ]);

        $response->assertSessionHasErrors(['remarks']);
    }

    public function test_修正申請処理が実行される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->put("/attendance/detail/{$attendance->id}", [
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'remarks' => '修正申請',
        ]);

        $response->assertRedirect("/attendance/detail/{$attendance->id}");
        $this->assertDatabaseHas('attendance_correction_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'remarks' => '修正申請',
        ]);
    }

    public function test_「承認待ち」にログインユーザーが行った申請が全て表示されている()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->subDay(),
        ]);

        AttendanceCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'remarks' => 'テスト',
        ]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get('/stamp_correction_request/list');
        $response->assertStatus(200);
        $response->assertSee('テスト');
        $response->assertSee('承認待ち');
    }

    public function test_「承認済み」に管理者が承認した修正申請が全て表示されている()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        DB::table('attendance_correction_requests')->insert([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'approved',
            'remarks' => 'テスト',
        ]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get('/stamp_correction_request/list?tab=approved');
        $response->assertStatus(200);
        $response->assertSee('テスト');
        $response->assertSee('承認済み');
    }

    public function test_各申請の「詳細」を押下すると勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        DB::table('attendance_correction_requests')->insert([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'remarks' => '詳細テスト申請',
        ]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
    }
}
