<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    public function test_その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $today = Carbon::today();
        Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => $today,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        Attendance::factory()->create([
            'user_id' => $user2->id,
            'work_date' => $today,
            'clock_in' => '08:45:00',
            'clock_out' => '17:45:00',
        ]);

        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)->get('/admin/attendance/list');
        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee($user2->name);
        $response->assertSee('08:45');
        $response->assertSee('17:45');

        $response->assertSee($today->isoFormat('YYYY年M月D日'));
    }

    public function test_「前日」を押下した時に前の日の勤怠情報が表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $yesterday = Carbon::yesterday();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $yesterday,
            'clock_in' => '09:10:00',
            'clock_out' => '18:10:00',
        ]);

        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=' . $yesterday->format('Y-m-d'));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('09:10');
        $response->assertSee('18:10');
        $response->assertSee($yesterday->isoFormat('YYYY年M月D日'));
    }

    public function test_「翌日」を押下した時に次の日の勤怠情報が表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $tomorrow = Carbon::tomorrow();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $tomorrow,
            'clock_in' => '08:50:00',
            'clock_out' => '17:50:00',
        ]);

        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=' . $tomorrow->format('Y-m-d'));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('08:50');
        $response->assertSee('17:50');
        $response->assertSee($tomorrow->isoFormat('YYYY年M月D日'));
    }
}
