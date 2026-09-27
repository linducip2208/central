<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function token(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required|string', 'device' => 'nullable|string|max:100']);
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['email' => ['Email atau password salah.']]);
        }
        if (! $user->is_active) {
            throw ValidationException::withMessages(['email' => ['Akun nonaktif.']]);
        }
        $user->forceFill(['last_login_at' => now()])->saveQuietly();
        $token = $user->createToken($request->get('device', 'api'))->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'roles' => $user->getRoleNames()],
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('roles');

        return response()->json(['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'roles' => $user->getRoleNames(), 'organization_id' => $user->organization_id]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }
}
