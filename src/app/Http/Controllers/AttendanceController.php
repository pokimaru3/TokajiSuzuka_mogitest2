<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceShowRequest;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceCorrectionRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function create(Request $request)
    {
        $dateParam = $request->input('date');
        $targetDate = $dateParam ? Carbon::parse($dateParam)->startOfDay() : Carbon::today();

        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('work_date', $targetDate)
            ->first();

        $work_status = $attendance->work_status ?? 'off_duty';
        $time = Carbon::now()->format('H:i');
        $formattedDate = $targetDate->isoFormat('YYYY年M月D日(ddd)');

        return view('user.attendance_register', compact('work_status', 'formattedDate', 'time', 'attendance'));
    }

    public function store(Request $request)
    {
        $action = $request->input('action');
        $userId = Auth::id();
        $today = Carbon::today();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $userId, 'work_date' => $today],
            ['work_status' => 'off_duty']
        );

        $attendance->refresh();

        switch ($action) {
            case 'clock_in':
                $attendance->update([
                    'clock_in' => Carbon::now(),
                    'work_status' => 'working',
                ]);
                break;

            case 'clock_out':
                $attendance->update([
                    'clock_out' => Carbon::now(),
                    'work_status' => 'finished',
                ]);

                $attendance->refresh();

                $totalBreakMinutes = $attendance->breaks->sum(function ($b) {
                    if ($b->break_start && $b->break_end) {
                        return Carbon::parse($b->break_start)->diffInMinutes(Carbon::parse($b->break_end));
                    }
                    return 0;
                });

                if ($attendance->clock_in && $attendance->clock_out) {
                    $totalWorkMinutes = Carbon::parse($attendance->clock_in)
                        ->diffInMinutes(Carbon::parse($attendance->clock_out)) - $totalBreakMinutes;

                        $attendance->update([
                        'total_break_time' => (int) $totalBreakMinutes,
                        'total_work_time' => (int) max(0, $totalWorkMinutes),
                    ]);
                }
                break;

            case 'break_start':
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => Carbon::now(),
                ]);
                $attendance->update(['work_status' => 'on_break']);
                break;

            case 'break_end':
                $break = BreakTime::where('attendance_id', $attendance->id)
                    ->whereNull('break_end')
                    ->latest()
                    ->first();

                if ($break) {
                    $break->update(['break_end' => Carbon::now()]);
                }

                $attendance->update(['work_status' => 'working']);
                break;
        }

        $statusText = $this->getStatusText($attendance->work_status);
        $buttonsHtml = $this->generateButtonsHtml($attendance->work_status);

        return response()->json([
            'status' => true,
            'status_text' => $statusText,
            'buttons_html' => $buttonsHtml,
        ]);
    }

    private function getStatusText($status)
    {
        return match ($status) {
            'off_duty' => '勤務外',
            'working' => '出勤中',
            'on_break' => '休憩中',
            'finished' => '退勤済',
            default => '勤務外',
        };
    }

    private function generateButtonsHtml($status)
    {
        return match ($status) {
            'off_duty' => '<button class="attendance-button" data-action="clock_in">出勤</button>',
            'working' => '
                <button class="attendance-button" data-action="clock_out">退勤</button>
                <button class="attendance-button" data-action="break_start">休憩入</button>
            ',
            'on_break' => '<button class="attendance-button" data-action="break_end">休憩戻</button>',
            'finished' => '<p class="attendance-message">お疲れ様でした。</p>',
            default => '',
        };
    }

    public function index(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));

        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth = Carbon::parse($month)->endOfMonth();

        $days = collect();
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $days->push($date->toDateString());
        }

        $attendances = Attendance::with('breaks')
            ->where('user_id', Auth::id())
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(fn($a) => $a->work_date->toDateString());

        return view('user.attendance_index', compact('month', 'days', 'attendances'));
    }

    public function show(Request $request, $id = null)
    {
        if ($id) {
            $attendance = Attendance::with(['breaks', 'correctionRequest.correctionBreaks', 'user'])
                ->where('user_id', Auth::id())
                ->findOrFail($id);
        } else {
            $dateParam = $request->input('date');
            $targetDate = $dateParam ? Carbon::parse($dateParam)->startOfDay() : Carbon::today();

            $attendance = Attendance::with(['breaks', 'correctionRequest.correctionBreaks', 'user'])
                ->where('user_id', Auth::id())
                ->whereDate('work_date', $targetDate)
                ->first();

            if (! $attendance) {
                $attendance = new Attendance();
                $attendance->user_id = Auth::id();
                $attendance->work_date = $targetDate;
                $attendance->work_status = 'off_duty';
                $attendance->breaks = collect();
                $attendance->user = Auth::user();
            }
        }

        return view('user.attendance_show', compact('attendance'));
    }

    public function update(AttendanceShowRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $dateString = Carbon::parse($attendance->work_date)->format('Y-m-d');

        $correctionRequest = AttendanceCorrectionRequest::updateOrCreate(
            ['attendance_id' => $attendance->id],
            [
                'user_id' => Auth::id(),
                'requested_clock_in' => $request->clock_in ? Carbon::parse($dateString . ' ' . $request->clock_in) : null,
                'requested_clock_out' => $request->clock_out ? Carbon::parse($dateString . ' ' . $request->clock_out) : null,
                'remarks' => $request->remarks,
                'status' => 'pending',
            ]
        );

        foreach (range(1, 2) as $i) {
            $start = $request->input("break_start_{$i}");
            $end = $request->input("break_end_{$i}");
            $start = $start !== '' ? $start : null;
            $end = $end !== '' ? $end : null;

            if ($start || $end) {
                $correctionRequest->correctionBreaks()->create([
                    'requested_break_start' => $start ? Carbon::parse($dateString . ' ' . $start) : null,
                    'requested_break_end' => $end ? Carbon::parse($dateString . ' ' . $end) : null,
                ]);
            }
        }

        return redirect()
            ->route('attendance.show', $attendance->id);
    }
}
