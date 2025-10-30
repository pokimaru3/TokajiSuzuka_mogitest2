<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceCorrectionRequest;

class StampCorrectionController extends Controller
{
    public function index()
    {
        $tab = request('tab', 'pending');

        $query = AttendanceCorrectionRequest::with(['user','attendance'])
            ->where('user_id', Auth::id());

        if ($tab === 'approved') {
            $requests = $query->where('status', 'approved')->latest()->get();
        } else {
            $requests = $query->where('status', 'pending')->latest()->get();
        }

        return view('user.application_index', compact('requests', 'tab'));
    }
}
