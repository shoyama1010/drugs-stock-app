<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use App\Models\User;

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
        Fortify::authenticateUsing(function (Request $request) {

            // =========================
            // 管理者ログイン
            // =========================
            if ($request->filled('email')) {
                $user = User::where('email', $request->email)
                    ->where('role', 'admin')
                    ->first();

                if ($user && Hash::check($request->password, $user->password)) {
                    return $user;
                }
            }
            
            // =========================
            // スタッフログイン
            // =========================
            if ($request->employee_code) {
                $user = User::where('employee_code', $request->employee_code)
                    ->where('role', 'staff')
                    ->first();

                if ($user && Hash::check($request->pin, $user->pin)) {
                    return $user;
                }
            }
            return null;
        });
    }
}
