<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceCorrectionBreak;
use Carbon\Carbon;

class AttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_承認待ちの修正申請が全て表示されている()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);

        $attendance1 = Attendance::factory()->create(['user_id' => $user1->id]);
        $attendance2 = Attendance::factory()->create(['user_id' => $user2->id]);

        $pending1 = AttendanceCorrectionRequest::factory()->create([
            'attendance_id' => $attendance1->id,
            'user_id' => $user1->id,
            'status' => 'pending',
        ]);
        $pending2 = AttendanceCorrectionRequest::factory()->create([
            'attendance_id' => $attendance2->id,
            'user_id' => $user2->id,
            'status' => 'pending',
        ]);

        AttendanceCorrectionRequest::factory()->create([
            'attendance_id' => $attendance1->id,
            'user_id' => $user1->id,
            'status' => 'approved',
        ]);

        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)->get('/admin/stamp_correction_request/list?tab=pending');
        $response->assertStatus(200);
        $response->assertSee((string)$pending1->id);
        $response->assertSee((string)$pending2->id);
    }

    public function test_承認済みの修正申請が全て表示されている()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $approved1 = AttendanceCorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'approved',
        ]);
        $approved2 = AttendanceCorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        AttendanceCorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)->get('/admin/stamp_correction_request/list?tab=approved');
        $response->assertStatus(200);
        $response->assertSee((string)$approved1->id);
        $response->assertSee((string)$approved2->id);
    }

    public function test_修正申請の詳細内容が正しく表示されている()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $correction = AttendanceCorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in' => Carbon::parse('2025-10-01 09:00:00'),
            'requested_clock_out' => Carbon::parse('2025-10-01 18:00:00'),
            'remarks' => '修正申請',
            'status' => 'pending',
        ]);

        AttendanceCorrectionBreak::factory()->create([
            'correction_request_id' => $correction->id,
            'requested_break_start' => Carbon::parse('2025-10-01 12:00:00'),
            'requested_break_end' => Carbon::parse('2025-10-01 13:00:00'),
        ]);

        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)->get("/admin/attendance/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertSee('修正申請');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    public function test_修正申請の承認処理が正しく行われる()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '2025-10-01 09:30:00',
            'clock_out' => '2025-10-01 18:10:00',
        ]);

        $correction = AttendanceCorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in' => '2025-10-01 09:00:00',
            'requested_clock_out' => '2025-10-01 18:00:00',
            'status' => 'pending',
        ]);

        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)
            ->post("/admin/stamp_correction_request/approve/{$correction->id}");
        $response->assertStatus(200);
        $response->assertJson(['status' => 'approved']);
        $this->assertDatabaseHas('attendance_correction_requests', [
            'id' => $correction->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '2025-10-01 09:00:00',
            'clock_out' => '2025-10-01 18:00:00',
        ]);
    }
}
