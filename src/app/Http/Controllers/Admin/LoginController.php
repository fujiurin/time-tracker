<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // 管理者用ログイン画面表示
    public function create()
    {
        return view('admin.auth.login');
    }

    // 管理者ログイン処理
    public function store(AdminLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        $credentials['role'] = 'admin';

        if (!Auth::attempt($credentials)) {

            return back()->withErrors([
                'email' => 'ログイン情報が登録されていません',
            ])->withInput();
        }

        $request->session()->regenerate();

        return redirect('/admin/attendance/list');
    }
}