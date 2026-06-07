@extends('layouts.app')

@section('title','勤怠詳細（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@endsection

@section('content')

<div class="detail-container">
    
    <h1 class="page-title">勤怠詳細</h1>

    <form 
    id="attendance-form"
    action="{{ route('admin.attendance.update', ['id' => $attendance->id ?? 0]) }}"
    method="post" 
    class="detail-content">
        @csrf
        @method('PUT')
        <input type="hidden" name="user_id" value="{{ $attendance->user->id }}">
        <input type="hidden" name="work_date" value="{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y-m-d') }}">

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
                <input 
                type="time" 
                name="clock_in" 
                value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}"> 
                
                <span>～</span>
                
                <input 
                type="time" 
                name="clock_out" 
                value="{{ old('clock_out', optional($attendance->clock_out)->format('H:i')) }}" >
            </div>

            @error('clock_out')
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        @foreach ($attendance->breaks as $index => $break)
        <div class="detail-row">
            <label class="detail-label">休憩{{ $index + 1 }}</label>

            <div class="time-inputs">

                <input
                    type="hidden"
                    name="breaks[{{ $index }}][id]"
                    value="{{ $break->id }}">

                <input 
                type="time" 
                name="breaks[{{ $index }}][break_start]" 
                value="{{ old('breaks.' . $index . '.break_start', optional($break->break_start)->format('H:i')) }}"> 
                <span>～</span>
                <input 
                type="time" 
                name="breaks[{{ $index }}][break_end]" 
                value="{{ old('breaks.' . $index . '.break_end', optional($break->break_end)->format('H:i')) }}">
            </div>
            @error('breaks.' . $index . '.break_start')
            <p class="error-message">{{ $message }}</p>
            @enderror

            @error('breaks.' . $index . '.break_end')
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        @endforeach

        <div class="detail-row">
            <label class="detail-label">休憩{{ $attendance->breaks->count() + 1 }}</label>

            <div class="time-inputs">
                <input 
                type="time" 
                name="breaks[{{ $attendance->breaks->count() }}][break_start]" 
                value="{{ old('breaks.' . $attendance->breaks->count() . '.break_start') }}">
                <span>～</span>
                <input 
                type="time" 
                name="breaks[{{ $attendance->breaks->count() }}][break_end]"
                value="{{ old('breaks.' . $attendance->breaks->count() . '.break_end') }}">
            </div>
            @error('breaks.' . $attendance->breaks->count() . '.break_start')
            <p class="error-message">{{ $message }}</p>
            @enderror

            @error('breaks.' . $attendance->breaks->count() . '.break_end')
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="detail-row">
            <label class="detail-label">備考</label>
            <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
            @error('note')
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>
    </form>

    <div class="button-area">
        <button 
        type="submit" 
        class="submit-button" 
        form="attendance-form">修正</button>
    </div>
</div>

@endsection