@extends('layouts.app')

@section('title','勤怠登録画面')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')
<div class="attendance-page">
    <div class="attendance-container">
        <div class="status">
        {{ $status }}
        </div>

        <p id="date" class="date"></p>
        <p id="time" class="time"></p>

        @if ($status === '勤務外')
            <form method="post" action="/attendance/start">
                @csrf
                <button type="submit" class="btn btn-primary">出勤</button>
            </form>
        @endif

        @if ($status === '出勤中')
            <div class="btn-group">
                <form method="post" action="/attendance/end">
                    @csrf
                    <button type="submit" class="btn btn-primary">退勤</button>
                </form>

                <form method="post" action="/attendance/break/start">
                    @csrf
                    <button type="submit" class="btn btn-secondary">休憩入</button>
                </form>
            </div>
        @endif

        @if ($status === '休憩中')
            <form method="post" action="/attendance/break/end">
                @csrf
                <button type="submit" class="btn btn-secondary">休憩戻</button>
            </form>
        @endif

        @if ($status === '退勤済')
            <p class="end-message">お疲れ様でした。</p>
        @endif
    </div>
</div>

<!-- JS -->
<script>
const now = new Date();

const week = ["日", "月", "火", "水", "木", "金", "土"];
const dayName = week[now.getDay()];

const year = now.getFullYear();
const month = now.getMonth() + 1;
const day = now.getDate();

const hours = now.getHours();
const minutes = now.getMinutes();

const dateText =
    year + "年" +
    month + "月" +
    day + "日(" +
    dayName + ")";
    
const timeText =
    String(hours).padStart(2, '0') + ":" +
    String(minutes).padStart(2, '0');

document.getElementById('date').textContent = dateText;
document.getElementById('time').textContent = timeText;
</script>

@endsection