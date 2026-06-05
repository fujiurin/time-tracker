<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Correction;
use App\Models\CorrectionBreak;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CorrectionRequest;

class CorrectionController extends Controller
{
    // 勤怠修正申請
    public function store(CorrectionRequest $request)
    {
        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'work_date' => $request->work_date,
            ]
        );

        $correction = Correction::create([
            'user_id' => Auth::id(),
            'attendance_id' => $attendance->id,
            'requested_clock_in' =>
            $attendance->work_date->format('Y-m-d') . ' ' . $request->requested_clock_in,
            'requested_clock_out' =>
            $attendance->work_date->format('Y-m-d') . ' ' . $request->requested_clock_out,
            'note' => $request->note,
            'status' => 'pending',
        ]);

        foreach ($request->breaks as $break) {

            if ($break['break_start'] || $break['break_end']) {

                CorrectionBreak::create([
                    'correction_id' => $correction->id,
                    'break_start' =>
                    $attendance->work_date->format('Y-m-d') . ' ' . $break['break_start'],
                    'break_end' =>
                    $attendance->work_date->format('Y-m-d') . ' ' . $break['break_end'],
                ]);
            }
        }

        return redirect()->back();
    }

    // 申請一覧ページ表示
    public function index(Request $request)
    {
        $status = $request->status ?? 'pending';

        $corrections = Correction::where('user_id', Auth::id())
            ->where('status', $status)
            ->get();

        return view('user.correction.list', compact(
            'corrections',
            'status'
        ));
    }
}
