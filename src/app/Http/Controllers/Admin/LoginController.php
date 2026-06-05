<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create()
    {
        return view('admin.auth.login');
    }

    public function store(AdminLoginRequest $request)
    {

        // 認証情報
        $credentials = $request->only('email', 'password');

        // admin限定
        $credentials['role'] = 'admin';

        // ログイン失敗
        if (!Auth::attempt($credentials)) {

            return back()->withErrors([
                'email' => 'ログイン情報が登録されていません',
            ])->withInput();
        }

        // セッション再生成
        $request->session()->regenerate();

        // 管理画面へ
        return redirect('/admin/attendance/list');
    }
}