<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::loginView(function (Request $request) {
            if ($request->is('admin/*')) {
                return view('auth.admin.login');
            }
            return view('auth.user.login');
        });

        Fortify::registerView(function (Request $request) {
            if ($request->is('admin/*')) {
                return view('auth.admin.register');
            } else {
                return view('auth.user.register');
            }
        });

        Fortify::authenticateUsing(function (Request $request) {
            $user = \App\Models\User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                if ($request->is('admin/*')) {
                    return $user->role === 'admin' ? $user : null;
                }
                return $user->role !== 'admin' ? $user : null;
            }

            return null;
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)->by($request->email . $request->ip());
        });

        $this->app->instance(
            \Laravel\Fortify\Contracts\LoginResponse::class,
            new class implements \Laravel\Fortify\Contracts\LoginResponse {
                public function toResponse($request)
                {
                    $user = auth()->user();

                    if ($user->role === 'admin') {
                        return redirect()->intended(\App\Providers\RouteServiceProvider::ADMIN_HOME);
                    }

                    return redirect()->intended(\App\Providers\RouteServiceProvider::HOME);
                }
            }
        );

        $this->app->instance(
            \Laravel\Fortify\Contracts\LogoutResponse::class,
            new class implements \Laravel\Fortify\Contracts\LogoutResponse {
                public function toResponse($request)
                {
                    $userRole = session('admin_logout_role');

                    if ($userRole === 'admin') {
                        return redirect('/admin/login');
                    }

                    return redirect('/login');
                }
            }
        );
    }
}
