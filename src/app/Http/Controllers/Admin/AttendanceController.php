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
    public function index(Request $request)
    {
        // 日付取得
        $date = $request->date
            ? Carbon::parse($request->date)
            : Carbon::today();

        // ユーザーとその日の勤怠
        $users = User::where('role', 'user')
            ->with([
                'attendances' => function ($query) use ($date) {
                    $query->whereDate('work_date', $date)
                        ->with('breaks');
                }
            ])                
            ->get();

        // ビューへ
        return view('admin.attendance.list', compact('users', 'date'));
    }

    public function show($id, Request $request)
    {
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
            }else {
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
