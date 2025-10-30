<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('login');
    }
    public function store(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();

        if (!auth()->user()->hasVerifiedEmail()) {
            Auth::logout();
            return redirect()->route('verification.notice')
                ->with('message', 'メール認証を完了してください。');
        }

        return redirect()->route('attendance.create');
    }
}