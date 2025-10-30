<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Http\Requests\AttendanceShowRequest;
use App\Models\User;
use Carbon\Carbon;


class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));

        $attendances = Attendance::with(['user', 'breaks'])
            ->whereDate('work_date', $date)
            ->get();

        return view('admin.attendance_index', compact('attendances', 'date'));
    }

    public function show($id)
    {
        $attendance = Attendance::with('user', 'breaks', 'correctionRequest')->findOrFail($id);

        return view('admin.attendance_show', compact('attendance'));
    }

    public function update(AttendanceShowRequest $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);
        $dateString = Carbon::parse($attendance->work_date)->format('Y-m-d');

        $attendance->clock_in = Carbon::parse($dateString . ' ' . $request->clock_in);
        $attendance->clock_out = Carbon::parse($dateString . ' ' . $request->clock_out);

        if (auth()->user()->role === 'admin') {
            $attendance->remarks = $request->remarks;
            $attendance->save();
        } else {
            if ($request->filled('remarks')) {
                \App\Models\AttendanceCorrectionRequest::create([
                    'attendance_id' => $attendance->id,
                    'user_id' => $attendance->user_id,
                    'requested_clock_in' => $attendance->clock_in,
                    'requested_clock_out' => $attendance->clock_out,
                    'remarks' => $request->remarks,
                    'status' => 'pending',
                ]);
            }
        }

        $breakCount = $attendance->breaks->count() + 1;
        $updatedBreaks = [];

        for ($i = 1; $i <= $breakCount; $i++) {
            $start = $request->input("break_start_{$i}");
            $end = $request->input("break_end_{$i}");

            if (empty($start) && empty($end)) { continue; }

            $break = $attendance->breaks[$i - 1] ?? $attendance->breaks()->make();
            $break->break_start = $start ? Carbon::parse($dateString . ' ' . $start) : null;
            $break->break_end = $end ? Carbon::parse($dateString . ' ' . $end) : null;
            $break->attendance_id = $attendance->id;
            $break->save();
    
            $updatedBreaks[] = $break->id;
        }
        
        $attendance->load('breaks');

        $totalBreakMinutes = $attendance->breaks->sum(function ($break) {
            if ($break->break_start && $break->break_end) {
                return Carbon::parse($break->break_start)->diffInMinutes(Carbon::parse($break->break_end));
            }
            return 0;
        });

        $totalWorkMinutes = 0;
        if ($attendance->clock_in && $attendance->clock_out) {
            $totalWorkMinutes = $attendance->clock_in->diffInMinutes($attendance->clock_out) - $totalBreakMinutes;
            if ($totalWorkMinutes < 0) {
                $totalWorkMinutes = 0;
            }
        }

        $attendance->total_break_time = $totalBreakMinutes;
        $attendance->total_work_time = $totalWorkMinutes;
        $attendance->save();

        return redirect()->route('admin.attendance.list');
    }
}
