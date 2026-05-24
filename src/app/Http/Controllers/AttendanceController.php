<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\Correction;
use App\Models\CorrectionBreak;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Http\Requests\CorrectionRequest;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('work_date', today())
            ->first();

        $status = '勤務外';

        if ($attendance) {

            $latestBreak = $attendance->breaks()->latest()->first();

            if ($attendance->clock_out) {

                $status = '退勤済';

            } elseif ($latestBreak && !$latestBreak->break_end) {

                $status = '休憩中';

            } else {

                $status = '出勤中';
            }
        }

        return view('user.attendance.index', compact('attendance', 'status'));
    }

    public function start()
    {
        // ★勤怠押してるかどうかを確認
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('work_date', today())
            ->first();

        // ★まだ押してなかったらcreate
        if (!$attendance) {
            Attendance::create([
                'user_id' => Auth::id(),
                'work_date' => today(),
                'clock_in' => now(),
            ]);
        }

        return redirect('/attendance');
    }

    public function end()
    {
        // ★勤怠押してるかどうか確認
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('work_date', today())
            ->first();   

        if($attendance && !$attendance->clock_out) {
            $attendance->clock_out = now();
            $attendance->save();
        }

        return redirect('/attendance');
    }

    public function breakStart()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('work_date', today())
            ->first();

        if ($attendance) {
            AttendanceBreak::create([
                'attendance_id' => $attendance->id,
                'break_start' => now(),
            ]);
        }

        return redirect('/attendance');
    }

    public function breakEnd()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('work_date', today())
            ->first();

        if ($attendance) {

            $latestBreak = $attendance->breaks()
                ->latest()
                ->first();

            if ($latestBreak && !$latestBreak->break_end) {

                $latestBreak->break_end = now();
                $latestBreak->save();
            }
        }

        return redirect('/attendance');
    }

    // 勤怠一覧画面表示
    public function list(Request $request)
    {
        $currentMonth = $request->month
            ? Carbon::parse($request->month)
            : Carbon::now();

        $startOfMonth = $currentMonth->copy()->startOfMonth();

        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $dates = CarbonPeriod::create($startOfMonth, $endOfMonth);

        $attendances = Attendance::where('user_id', Auth::id())
            ->whereDate('work_date', '>=', $startOfMonth)
            ->whereDate('work_date', '<=', $endOfMonth)
            ->with('breaks')
            ->get()
            ->keyBy(fn ($a) => optional($a->work_date)->format('Y-m-d'));
            
            
        $previousMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
        
        return view('user.attendance.list', compact(
            'currentMonth',
            'dates',
            'attendances',
            'previousMonth',
            'nextMonth'
        ));
    }

    // 勤怠詳細画面表示
    public function show($id, Request $request)
    {
        $attendance = Attendance::with('user', 'breaks', 'corrections')
            ->where('user_id', Auth::id())
            ->where('work_date', $request->date)
            ->first();

        $isPending = $attendance->corrections()
            ->where('status', 'pending')
            ->exists();

        return view('user.attendance.detail', compact(
            'attendance',
            'isPending'
        ));
    }
}
