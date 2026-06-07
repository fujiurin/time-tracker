<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Correction;
use App\Models\AttendanceBreak;
use Illuminate\Support\Facades\DB;

class AttendanceRequestController extends Controller
{
    // 承認申請一覧表示
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $correctionRequests = Correction::with(['user', 'attendance'])
            ->where('status', $status)
            ->latest()
            ->get();

        return view('admin.correction.list', compact(
            'correctionRequests',
            'status'
        ));
    }

    // 修正申請詳細表示
    public function show($attendance_correct_request_id)
    {
        $correctionRequest = Correction::with([
                'user',
                'attendance.breaks',
                'correctionBreaks',
        ])->findOrFail($attendance_correct_request_id);

        return view('admin.correction.approve', compact('correctionRequest'));
    }

    // 修正申請承認
    public function approve($attendance_correct_request_id)
    {
        $correctionRequest = Correction::with(['attendance.breaks', 'correctionBreaks'])
            ->findOrFail($attendance_correct_request_id);

        if ($correctionRequest->status === 'approved') {
            return redirect()->route('admin.correction.approve', [
                'attendance_correct_request_id' => $correctionRequest->id,
            ]);
        }

        DB::transaction(function () use ($correctionRequest) {

            $attendance = $correctionRequest->attendance;

            $attendance->update([
                'clock_in' => $correctionRequest->requested_clock_in,
                'clock_out' => $correctionRequest->requested_clock_out,
                'note' => $correctionRequest->note,
            ]);

            $attendance->breaks()->delete();

            foreach ($correctionRequest->correctionBreaks as $correctionBreak) {
                AttendanceBreak::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $correctionBreak->break_start,
                    'break_end' => $correctionBreak->break_end,
                ]);
            }

            $correctionRequest->update([
                'status' => 'approved',
            ]);
        });

        return redirect()->route('admin.correction.approve', [
            'attendance_correct_request_id' => $correctionRequest->id,
        ]);
    }
}