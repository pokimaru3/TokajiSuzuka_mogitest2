<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;

class RegisteredUserController extends Controller
{
    public function store(Request $request, CreateNewUser $creator)
    {
        try {
            $user = $creator->create($request->all());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        event(new Registered($user));

        auth()->login($user);

        return redirect()->route('verification.notice');
    }
}
