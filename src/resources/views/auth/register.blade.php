@extends('layouts.app')

@section('title','会員登録')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/auth.css') }}">
@endsection

@section('content')
<div class="auth-container">
    <h1 class="page-title">会員登録</h1>

    <form action="/register" method="post">
        @csrf

        <div class="form-group">
            <label for="name" class="form-label">名前</label>
            <input id="name" class="form-input" type="text" name="name" value="{{ old('name') }}">
            @error('name')
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

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

        <div class="form-group">
            <label for="password_confirmation" class="form-label">パスワード確認</label>
            <input id="password_confirmation" class="form-input" type="password" name="password_confirmation">
        </div>

        <button type="submit" class="form-button">登録する</button>
        <a href="/login" class="link">ログインはこちら</a>
    </form>
</div>
@endsection