<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class StaffController extends Controller
{
    // スタッフ一覧画面表示
    public function index()
    {
        $users = User::where('role', 'user')->get();

        return view('admin.staff.index', compact('users'));
    }

    // スタッフ別勤怠一覧画面表示
    public function attendance(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $currentMonth = $request->month
            ? Carbon::parse($request->month)
            : Carbon::now();

        $previousMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $dates = CarbonPeriod::create($startOfMonth, $endOfMonth);

        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [
                $startOfMonth->toDateString(),
                $endOfMonth->toDateString(),
            ])
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->work_date)->format('Y-m-d');
            });

        return view('admin.staff.attendance', compact(
            'user',
            'currentMonth',
            'previousMonth',
            'nextMonth',
            'dates',
            'attendances'
        ));
    }

    // CSV出力
    public function exportCsv(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $currentMonth = $request->month
            ? Carbon::parse($request->month)
            : Carbon::now();

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $dates = CarbonPeriod::create($startOfMonth, $endOfMonth);

        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [
                $startOfMonth->toDateString(),
                $endOfMonth->toDateString(),
            ])
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->work_date)->format('Y-m-d');
            });

        $fileName = $user->name . '_' . $currentMonth->format('Y-m') . '_attendance.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        return response()->stream(function () use ($dates, $attendances) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($dates as $date) {
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
                    $attendanceMinutes =
                        $attendance->clock_in->diffInMinutes($attendance->clock_out) - $breakMinutes;
                }

                fputcsv($handle, [
                    $date->format('Y/m/d'),
                    $attendance && $attendance->clock_in 
                        ? $attendance->clock_in->format('H:i') : '',
                    $attendance && $attendance->clock_out ? $attendance->clock_out->format('H:i') : '',
                    $breakMinutes > 0 ? sprintf('%02d:%02d', floor($breakMinutes / 60), $breakMinutes % 60) : '',
                    $attendanceMinutes > 0 ? sprintf('%02d:%02d', floor($attendanceMinutes / 60), $attendanceMinutes % 60) : '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
