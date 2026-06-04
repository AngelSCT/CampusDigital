<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // ── Login ────────────────────────────────────────────────────
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    // ── Register ─────────────────────────────────────────────────
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'unique:users'],
            'student_id' => ['required', 'string', 'max:20', 'unique:users'],
            'career'     => ['required', 'string', 'max:100'],
            'password'   => ['required', 'confirmed', 'min:8'],
        ], [
            'name.required'       => 'El nombre es obligatorio.',
            'email.unique'        => 'Este correo ya está registrado.',
            'student_id.unique'   => 'Esta matrícula ya está registrada.',
            'password.min'        => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'  => 'Las contraseñas no coinciden.',
        ]);

        $user = User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'student_id' => $request->student_id,
            'career'     => $request->career,
            'password'   => Hash::make($request->password),
        ]);

        // Create wallet automatically
        Wallet::create([
            'user_id'       => $user->id,
            'balance'       => 0.00,
            'card_number'   => '4000' . rand(100000000000, 999999999999),
            'monthly_limit' => 5000.00,
            'is_active'     => true,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', '¡Bienvenido! Tu cuenta y monedero han sido creados.');
    }

    // ── Logout ───────────────────────────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
