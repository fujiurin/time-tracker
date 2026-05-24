<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'ログイン情報が登録されていません',
            ])->withInput();
        }

        $user = Auth::user();

        if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
            Auth::logout();

            return redirect()->route('verification.notice');
        }

        $request->session()->regenerate();

        return redirect('/attendance');
    }

    public function destroy()
    {
        Auth::logout();

        return redirect('/login');
    }
}
