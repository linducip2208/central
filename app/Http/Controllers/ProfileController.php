<?php

namespace App\Http\Controllers;

use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(Request $request, TotpService $totp)
    {
        $user = $request->user();
        $provision = null;
        if (! $user->two_factor_confirmed_at) {
            $secret = $user->two_factor_secret ?: $totp->generateSecret();
            if (! $user->two_factor_secret) {
                $user->forceFill(['two_factor_secret' => $secret])->saveQuietly();
            }
            $provision = $totp->provisioningUrl($user->email, $secret);
        }
        $tokens = $user->tokens()->latest()->take(10)->get();

        return view('profile.show', compact('user', 'provision', 'tokens'));
    }

    public function confirm2fa(Request $request, TotpService $totp)
    {
        $request->validate(['code' => 'required|string|size:6']);
        $user = $request->user();
        abort_unless($user->two_factor_secret, 422, 'Belum ada secret 2FA.');
        abort_if($user->two_factor_confirmed_at, 422, '2FA sudah aktif.');
        if (! $totp->verify($user->two_factor_secret, $request->code)) {
            return back()->withErrors(['code' => 'Kode salah. Coba lagi.']);
        }
        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        return back()->with('success', '2FA diaktifkan.');
    }

    public function disable2fa(Request $request)
    {
        $user = $request->user();
        $user->forceFill(['two_factor_secret' => null, 'two_factor_confirmed_at' => null])->save();

        return back()->with('success', '2FA dinonaktifkan.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $user = $request->user();
        abort_unless(Hash::check($request->current_password, $user->password), 422, 'Password lama salah.');
        $user->forceFill(['password' => $request->password])->save();

        return back()->with('success', 'Password diperbarui.');
    }

    public function createToken(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $token = $request->user()->createToken($request->name)->plainTextToken;

        return back()->with('success', 'Token dibuat. Salin sekarang (hanya tampil sekali): '.$token);
    }

    public function revokeToken(Request $request, string $id)
    {
        $request->user()->tokens()->whereKey($id)->delete();

        return back()->with('success', 'Token dicabut.');
    }
}
