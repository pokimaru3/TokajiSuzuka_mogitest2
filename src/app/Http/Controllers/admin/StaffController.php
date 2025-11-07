<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StaffController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'user')->get();

        return view('admin.staff_index', compact('users'));
    }

    public function showAttendance($userId, Request $request)
    {
        $user = User::findOrFail($userId);

        $month = $request->input('month', now()->format('Y-m'));

        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth = Carbon::parse($month)->endOfMonth();

        $days = collect();
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $days->push($date->toDateString());
        }

        $days = collect();
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $days->push($date->toDateString());
        }

        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(fn($a) => $a->work_date->toDateString());

        return view('admin.staff_attendance_index', compact('user', 'month', 'days', 'attendances'));
    }
}
