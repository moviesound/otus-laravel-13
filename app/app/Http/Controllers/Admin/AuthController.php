<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin\Admin;

class AuthController extends Controller
{
    /**
     * Показ формы логина
     */
    public function show()
    {
        if (Auth::guard('admin')->check()) {
            return redirect('/texts');
        }
        return view('guest.login');
    }

    /**
     * Логин
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // пытаемся авторизовать через guard web (он у тебя admin)
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('/texts');
        }

        return redirect()->route('login')
            ->withErrors([
                'email' => 'Неверный email или пароль',
            ])
            ->withInput($request->only('email'));
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
