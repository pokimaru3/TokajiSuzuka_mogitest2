<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class StaffListTest extends TestCase
{
    use RefreshDatabase;

    public function test_管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $users = User::factory()->count(3)->create(['role' => 'user']);

        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)->get('/admin/staff/list');

        $response->assertStatus(200);
        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    public function test_ユーザーの勤怠情報が正しく表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-10-01',
            'clock_in' => '2025-10-01 09:00:00',
            'clock_out' => '2025-10-01 18:00:00',
        ]);

        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$user->id}");

        $response->assertStatus(200);
        $response->assertSee(Carbon::parse('2025-10-01')->isoFormat('M月D日(ddd)'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_「前月」を押下した時に表示月の前月の情報が表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $previousMonth = Carbon::now()->subMonth();
        $attendanceDate = $previousMonth->copy()->day(10);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $attendanceDate,
        ]);

        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)
            ->get('/admin/attendance/staff/' . $user->id . '?month=' . $previousMonth->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSee($attendanceDate->isoFormat('M月D日'));
    }

    public function test_「翌月」を押下した時に表示月の翌月の情報が表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $nextMonth = Carbon::now()->addMonth();
        $attendanceDate = $nextMonth->copy()->day(15);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $attendanceDate,
        ]);

        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $user->id . '?month=' . $nextMonth->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSee($attendanceDate->isoFormat('M月D日'));
    }

    public function test_「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
        ]);

        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)->get("/admin/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $response->assertSee(Carbon::today()->isoFormat('M月D日'));
    }
}
