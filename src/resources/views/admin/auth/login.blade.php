@extends('layouts.app')

@section('title','ログイン（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/auth.css') }}">
@endsection

@section('content')
<div class="auth-container">
    <h1 class="page-title">管理者ログイン</h1>

    <form 
    action="{{ route('admin.login.store') }}"
    method="post">
        @csrf

        <div class="form-group">
            <label for="email" class="form-label">メールアドレス</label>
            <input id="email" class="form-input" type="email" name="email" value="{{ old('email') }}">
            @error('email')
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password" class="form-label">パスワード</label>
            <input id="password" class="form-input" type="password" name="password">
            @error('password')
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="form-button">ログインする</button>
    </form>
</div>
@endsection