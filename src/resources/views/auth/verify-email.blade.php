@extends('layouts.app')

@section('title','メール認証')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/auth.css') }}">
@endsection

@section('content')
<div class="auth-container">
    <p class="message">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください
    </p>

    <a class="verify-link" href="http://localhost:8025" target="_blank">
        認証はこちらから
    </a>

    <form action="{{ route('verification.send') }}" method="post">
        @csrf

        <button class="resend-btn" type="submit">
            認証メールを再送する
        </button>
    </form>
</div>
@endsection