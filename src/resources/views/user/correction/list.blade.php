@extends('layouts.app')

@section('title','申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/correction/list.css') }}">
@endsection

@section('content')
<div class="list-container">
    <h1 class="page-title">申請一覧</h1>

    <!-- ★タブ★ -->
    <div class="tab-menu">
        <a
        href="{{ route('user.correction.list', ['status' => 'pending']) }}"
        class="{{ request('status', 'pending') === 'pending' ? 'active' : '' }}">
        承認待ち
        </a>

        <a
        href="{{ route('user.correction.list', ['status' => 'approved']) }}"
        class="{{ request('status') === 'approved' ? 'active' : '' }}">
        承認済み
        </a>
    </div>

    <!-- ★テーブル★ -->
    <table class="correction-table">
        <tr>
            <th>状態</th>
            <th>名前</th>
            <th>対象日時</th>
            <th>申請理由</th>
            <th>申請日時</th>
            <th>詳細</th>
        </tr>

        @foreach($corrections as $correction)
        <tr>
            <td>
                {{ $correction->status === 'pending' ? '承認待ち' : '承認済み' }}
            </td>

            <td>
                {{ $correction->user->name }}
            </td>

            <td>
                {{ \Carbon\Carbon::parse($correction->attendance->work_date)->format('Y/m/d') }}
            </td>

            <td>
                {{ $correction->note }}
            </td>

            <td>
                {{ $correction->created_at->format('Y/m/d') }}
            </td>

            <td>
                <a href="{{ route('attendance.detail', [
                'id' => $correction->attendance->id,
                'date' => \Carbon\Carbon::parse(
                $correction->attendance->work_date
                )->format('Y-m-d')
                ]) }}">
                詳細
                </a>
            </td>
        </tr>
        @endforeach
    </table>
</div>

@endsection