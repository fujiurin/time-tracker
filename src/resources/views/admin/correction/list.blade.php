@extends('layouts.app')

@section('title','申請一覧（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/correction/list.css') }}">
@endsection

@section('content')
<div class="list-container">
    <h1 class="page-title">申請一覧</h1>

    <!-- ★タブ★ -->
    <div class="tab-menu">
        <a href="{{ route('admin.correction.list', ['status' => 'pending']) }}"
            class="{{ $status === 'pending' ? 'active' : '' }}">
            承認待ち
        </a>

        <a href="{{ route('admin.correction.list', ['status' => 'approved']) }}"
            class="{{ $status === 'approved' ? 'active' : '' }}">
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

        @foreach ($correctionRequests as $correctionRequest)
        <tr>
            <td>
                {{ $correctionRequest->status === 'pending' ? '承認待ち' : '承認済み' }}
            </td>

            <td>
                {{ $correctionRequest->user->name }}
            </td>

            <td>
                {{ $correctionRequest->attendance->work_date->format('Y/m/d') }}
            </td>

            <td>
                {{ $correctionRequest->note }}
            </td>

            <td>
                {{ $correctionRequest->created_at->format('Y/m/d') }}
            </td>

            <td>
                <a href="{{ route('admin.correction.approve', ['attendance_correct_request_id' => $correctionRequest->id]) }}">
                詳細
                </a>
            </td>
        </tr>
        @endforeach
    </table>
</div>

@endsection