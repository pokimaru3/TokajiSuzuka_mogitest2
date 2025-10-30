<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceCorrectionBreak;
use App\Models\AttendanceCorrectionRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceCorrectionBreakFactory extends Factory
{
    protected $model = AttendanceCorrectionBreak::class;

    public function definition()
    {
        return [
            'correction_request_id' => AttendanceCorrectionRequest::factory(),
            'requested_break_start' => Carbon::now()->setTime(12, 0),
            'requested_break_end' => Carbon::now()->setTime(13, 0),
        ];
    }
}
