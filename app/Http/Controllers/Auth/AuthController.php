<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }

        $user = Auth::user();
        if (! $user->is_active) {
            Auth::logout();

            return back()->withErrors(['email' => 'Akun Anda nonaktif. Hubungi administrator.'])->onlyInput('email');
        }

        // 2FA: bila sudah dikonfirmasi, tahan sesi sampai kode TOTP valid.
        if ($user->two_factor_confirmed_at) {
            Auth::logout();
            $request->session()->put('2fa:user_id', $user->id);
            $request->session()->put('2fa:remember', $remember);

            return redirect()->route('2fa.challenge');
        }

        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        return redirect()->intended(route('dashboard'));
    }

    public function showChallenge(Request $request)
    {
        abort_unless($request->session()->has('2fa:user_id'), 403);

        return view('auth.2fa');
    }

    public function verifyChallenge(Request $request, TotpService $totp)
    {
        $userId = $request->session()->get('2fa:user_id');
        abort_unless($userId, 403);
        $request->validate(['code' => 'required|string|size:6']);
        $user = User::findOrFail($userId);
        abort_unless($user->two_factor_confirmed_at && $user->two_factor_secret, 403);
        if (! $totp->verify($user->two_factor_secret, $request->code)) {
            return back()->withErrors(['code' => 'Kode verifikasi salah atau kedaluwarsa.']);
        }
        Auth::login($user, $request->session()->pull('2fa:remember', false));
        $request->session()->forget('2fa:user_id');
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
