<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials + ['status' => 'active'], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->hasRole('super_admin')) {
                $this->logLogin($user);

                return redirect()->route('superadmin.dashboard');
            }

            if ($user->hasAnyRole(['society_admin', 'manager', 'staff', 'accountant'])) {
                $this->logLogin($user);

                return redirect()->route('society.dashboard');
            }

            if ($user->hasRole('member')) {
                $this->logLogin($user);

                return redirect()->route('member.dashboard');
            }

            ActivityLog::record('login_failed', 'Auth', 'Login rejected: no active application role', $user, [], 'failed', $user);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => 'This account does not have an active application role.'])->onlyInput('email');
        }

        $attempted = User::where('email', $credentials['email'])->first();
        ActivityLog::record(
            'login_failed',
            'Auth',
            "Failed login attempt for {$credentials['email']}",
            $attempted,
            ['user_name' => $attempted?->name ?? $credentials['email'], 'user_email' => $credentials['email']],
            'failed',
            $attempted,
        );

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        if ($user = Auth::user()) {
            ActivityLog::record('logout', 'Auth', "{$user->name} logged out", $user, [], 'success', $user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function logLogin(User $user): void
    {
        ActivityLog::record('login', 'Auth', "{$user->name} logged in", $user, [], 'success', $user);
    }
}
