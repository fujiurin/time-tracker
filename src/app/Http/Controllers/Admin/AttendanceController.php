<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Http\Requests\AdminAttendanceRequest;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // 勤怠一覧画面表示
    public function index(Request $request)
    {
        $date = $request->date
            ? Carbon::parse($request->date)
            : Carbon::today();

        $users = User::where('role', 'user')
            ->whereHas('attendances', function ($query) use ($date) {
                $query->whereDate('work_date', $date->toDateString())
                    ->whereNotNull('clock_in');
            })
            ->with([
                'attendances' => function ($query) use ($date) {
                    $query->whereDate('work_date', $date->toDateString())
                        ->whereNotNull('clock_in')
                        ->with('breaks');
                }
            ])
            ->get();

        return view('admin.attendance.list', compact('users', 'date'));
    }

    // 勤怠詳細画面表示
    public function show($id, Request $request)
    {
        // 勤怠データが未登録の日でも詳細画面を表示できるようにする
        if ($id == 0) {
            $user = User::findOrFail($request->user_id);

            $attendance = new Attendance();
            $attendance->user = $user;
            $attendance->work_date = Carbon::parse($request->date);
            $attendance->breaks = collect();

            return view('admin.attendance.detail', compact('attendance'));
        }

        $attendance = Attendance::with([
            'user',
            'breaks',
        ])->findOrFail($id);

        return view('admin.attendance.detail', compact('attendance'));
    }

    // 勤怠情報更新
    public function update(AdminAttendanceRequest $request, $id)
    {
        if ($id == 0) {
            $attendance = Attendance::create([
                'user_id' => $request->user_id,
                'work_date' => $request->work_date,
                'clock_in' => $request->work_date . ' ' . $request->clock_in,
                'clock_out' => $request->work_date . ' ' . $request->clock_out,
                'note' => $request->note,
            ]);
        } else {
            $attendance = Attendance::findOrFail($id);

            $attendance->update([
                'clock_in' =>
                    $attendance->work_date->format('Y-m-d')
                    . ' ' . $request->clock_in,

                'clock_out' =>
                    $attendance->work_date->format('Y-m-d')
                    . ' ' . $request->clock_out,

                'note' => $request->note,
            ]);
        }

        foreach ($request->breaks ?? [] as $breakData) {

            if (!empty($breakData['id'])) {

                $break = AttendanceBreak::find($breakData['id']);

                if ($break) {
                    $break->update([
                        'break_start' =>
                            $attendance->work_date->format('Y-m-d')
                            . ' ' . $breakData['break_start'],

                        'break_end' =>
                            $attendance->work_date->format('Y-m-d')
                            . ' ' . $breakData['break_end'],
                    ]);
                }
            } else {

                if (
                    !empty($breakData['break_start']) ||
                    !empty($breakData['break_end'])
                ) {
                    AttendanceBreak::create([
                        'attendance_id' => $attendance->id,

                        'break_start' =>
                            $attendance->work_date->format('Y-m-d')
                            . ' ' . $breakData['break_start'],

                        'break_end' =>
                            $attendance->work_date->format('Y-m-d')
                            . ' ' . $breakData['break_end'],
                    ]);
                }
            }
        }
        
        return redirect()->route(
            'admin.attendance.detail',
            ['id' => $attendance->id]
        );
    }
}
