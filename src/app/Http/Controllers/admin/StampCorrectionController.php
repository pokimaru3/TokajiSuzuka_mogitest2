<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceCorrectionRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StampCorrectionController extends Controller
{
    public function index()
    {
        $tab = request('tab', 'pending');

        $requests = AttendanceCorrectionRequest::with(['user', 'attendance'])
            ->where('status', $tab)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.application_index', compact('requests', 'tab'));
    }

    public function show($id)
    {
        $requestData = AttendanceCorrectionRequest::with([
            'user',
            'attendance.breaks',
            'correctionBreaks'
        ])->findOrFail($id);

        return view('admin.application_approval', compact('requestData'));
    }

    public function approve($id)
    {
        $requestData = AttendanceCorrectionRequest::with(['attendance', 'correctionBreaks'])->findOrFail($id);

        $requestData->status = 'approved';
        $requestData->save();

        $attendance = $requestData->attendance;
        $attendance->clock_in = $requestData->requested_clock_in;
        $attendance->clock_out = $requestData->requested_clock_out;

        if ($requestData->correctionBreaks->isNotEmpty()) {
            $attendance->breaks()->delete();
            foreach ($requestData->correctionBreaks as $correctionBreak) {
                $attendance->breaks()->create([
                    'break_start' => $correctionBreak->requested_break_start,
                    'break_end' => $correctionBreak->requested_break_end
                ]);
            }
        }

        $totalWorkMinutes = $attendance->clock_out->diffInMinutes($attendance->clock_in);
        $totalBreakMinutes = 0;

        foreach ($attendance->breaks as $break) {
            if ($break->break_start && $break->break_end) {
                $totalBreakMinutes += $break->break_end->diffInMinutes($break->break_start);
            }
        }

        $attendance->total_work_time = $totalWorkMinutes - $totalBreakMinutes;
        $attendance->total_break_time = $totalBreakMinutes;
        $attendance->save();

        return response()->json(['status' => 'approved']);
    }
}
