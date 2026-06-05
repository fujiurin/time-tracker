@extends('layouts.app')

@section('title','勤怠一覧（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/list.css') }}">
@endsection

@section('content')

<div class="list-container">
    <h1 class="page-title">{{ $date->format('Y年n月j日') }}の勤怠</h1>
    
    <div class="day-nav">
        <a href="{{ route('admin.attendance.list', [
        'date' => $date->copy()->subDay()->toDateString()
        ]) }}">← 前日</a>

        <div class="day-picker">
            <i class="fa-regular fa-calendar"></i>
            <span class="day-text">
                {{ $date->format('Y/m/d') }}
            </span>
        </div>

        <a href="{{ route('admin.attendance.list', [
        'date' => $date->copy()->addDay()->toDateString()
        ]) }}">翌日 →</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($users as $user)

                @php
                    $attendance = $user->attendances->first();

                    $breakMinutes = 0;
                    if ($attendance) {
                        foreach ($attendance->breaks as $break) {
                            if ($break->break_start && $break->break_end) {
                                $breakMinutes += $break->break_start
                                ->diffInMinutes($break->break_end);
                            }
                        }
                    }

                    $workMinutes = 0;
                    if (
                        $attendance &&
                        $attendance->clock_in &&
                        $attendance->clock_out
                        ) {
                            $workMinutes = $attendance->clock_in
                            ->diffInMinutes($attendance->clock_out)
                            - $breakMinutes;
                        }
                @endphp

                <tr>
                    <td>
                        {{ $user->name }}
                    </td>
            
                    <!-- 出勤時間 -->
                    <td>
                        {{ $attendance && $attendance->clock_in
                        ? $attendance->clock_in->format('H:i')
                        : '' }}
                    </td>

                    <!-- 退勤時間 -->
                    <td>
                        {{ $attendance && $attendance->clock_out
                        ? $attendance->clock_out->format('H:i')
                        : '' }}
                    </td>

                    <!-- 休憩時間 -->
                    <td>
                        @if ($breakMinutes > 0)
                            {{ floor($breakMinutes / 60) }}:{{ str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT) }}
                        @endif
                    </td>
                    
                    <!-- 勤務時間 -->
                    <td>
                        @if ($workMinutes > 0)
                            {{ floor($workMinutes / 60) }}:{{ str_pad($workMinutes % 60, 2, '0', STR_PAD_LEFT) }}
                        @endif
                    </td>

                    <td>
                        @if ($attendance)
                            <a href="{{ route('admin.attendance.detail', $attendance->id) }}">詳細</a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection