@extends('layouts.app')

@section('title','勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/list.css') }}">
@endsection

@section('content')

<div class="list-container">
    <h1 class="page-title">勤怠一覧</h1>
    
    <div class="month-nav">
        <a href="{{ url('/attendance/list?month=' . $previousMonth) }}">← 前月</a>

        <div class="month-picker">
            <i class="fa-regular fa-calendar"></i>
            <span class="month-text">
                {{ $currentMonth->format('Y/m') }}
            </span>
            <input
            type="month"
            value="{{ $currentMonth->format('Y-m') }}"
            onchange="location.href='{{ url('/attendance/list') }}?month=' + this.value">
        </div>

        <a href="{{ url('/attendance/list?month=' . $nextMonth) }}">翌月 →</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($dates as $date)

                @php
                    $attendance = $attendances[$date->format('Y-m-d')] ?? null;

                    $breakMinutes = 0;
                    if ($attendance) {
                        foreach ($attendance->breaks as $break) {
                            if ($break->break_start && $break->break_end) {
                                $breakMinutes += $break->break_start->diffInMinutes($break->break_end);
                            }
                        }
                    }

                    $attendanceMinutes = 0;
                    if ($attendance && $attendance->clock_out) {
                        $clockIn = $attendance->clock_in;
                        $clockOut = $attendance->clock_out;
                        
                        $attendanceMinutes =
                        $clockIn->diffInMinutes($clockOut) - $breakMinutes;
                    }
                @endphp

                <tr>
                    <td>
                        {{ $date->format('m/d') }}
                        ({{ ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek] }})
                    </td>
            
                    <!-- 出勤時間 -->
                    <td>
                        {{ $attendance && $attendance->clock_in
                        ? $attendance->clock_in->format('H:i'): '' }}
                    </td>

                    <!-- 退勤時間 -->
                    <td>
                        {{ $attendance && $attendance->clock_out
                        ? $attendance->clock_out->format('H:i'): '' }}
                    </td>

                    <!-- 休憩時間 -->
                    <td>
                        {{ $breakMinutes > 0? sprintf('%02d:%02d', floor($breakMinutes / 60), $breakMinutes % 60): ''}}
                    </td>
                    
                    <!-- 勤務時間 -->
                    <td>
                        {{ $attendanceMinutes > 0? sprintf('%02d:%02d', floor($attendanceMinutes / 60), $attendanceMinutes % 60): ''}}
                    </td>

                    <td>
                        @if($attendance)
                        <a href="{{ route('attendance.detail', ['id' => $attendance->id ?? 0,'date' => $date->format('Y-m-d')]) }}">詳細</a>
                        @else
                        <span class="disabled-link">詳細</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection