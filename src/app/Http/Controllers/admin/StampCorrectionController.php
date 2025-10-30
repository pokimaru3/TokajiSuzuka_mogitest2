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
        $requestData = AttendanceCorrectionRequest::with('attendance')->findOrFail($id);

        $requestData->status = 'approved';
        $requestData->save();

        $attendance = $requestData->attendance;
        $attendance->clock_in = $requestData->requested_clock_in;
        $attendance->clock_out = $requestData->requested_clock_out;
        $attendance->save();

        return response()->json(['status' => 'approved']);
    }
}
