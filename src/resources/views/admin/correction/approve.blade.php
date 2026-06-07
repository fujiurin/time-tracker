@extends('layouts.app')

@section('title','修正申請承認（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@endsection

@section('content')

@php
    $attendance = $correctionRequest->attendance;
    $isPending = $correctionRequest->status === 'pending';
@endphp

<div class="detail-container">
    
    <h1 class="page-title">勤怠詳細</h1>

    <div class="detail-content">

        <div class="detail-row">
            <p class="detail-label">名前</p>
            <p>{{ $attendance->user->name }}</p>
        </div>

        <div class="detail-row">
            <p class="detail-label">日付</p>
            <p>
                {{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}
                <span class="date-day">
                    {{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}
                </span>
            </p>
        </div>
        
        <div class="detail-row">
            <label class="detail-label">出勤・退勤</label>
            <div class="time-inputs">
                <p>
                    {{ optional($correctionRequest->requested_clock_in)->format('H:i') }}
                    <span>～</span>
                    {{ optional($correctionRequest->requested_clock_out)->format('H:i') }}
                </p>
            </div>
        </div>

        @foreach ($correctionRequest->correctionBreaks as $index => $break)
            <div class="detail-row">
                <label class="detail-label">休憩{{ $index + 1 }}</label>
                <p>
                    {{ optional($break->break_start)->format('H:i') }}
                    <span>～</span>
                    {{ optional($break->break_end)->format('H:i') }}
                </p>
            </div>
        @endforeach

        <div class="detail-row">
            <label class="detail-label">備考</label>
            <p>{{ $correctionRequest->note }}</p>
        </div>
    </div>
    
    <form 
    action="{{ route('admin.correction.approve.store', ['attendance_correct_request_id' => $correctionRequest->id]) }}" 
    method="post">
        @csrf

        <div class="button-area">
            @if($isPending)
                <button 
                type="submit" 
                class="submit-button">承認</button>
            @else
                <button 
                type="button" 
                class="approved-button" 
                disabled>承認済み</button>
            @endif
        </div>
    </form>
</div>

@endsection