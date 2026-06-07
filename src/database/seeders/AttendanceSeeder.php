<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\Correction;
use App\Models\CorrectionBreak;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::where('role', 'user')->get();

        foreach ($users as $user) {

            for ($i = 1; $i <= 20; $i++) {

                $workDate = Carbon::today()->subDays($i);

                $clockIn = $workDate->copy()->setTime(rand(8, 9), rand(0, 59));
                $clockOut = $workDate->copy()->setTime(rand(17, 19), rand(0, 59));

                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $workDate,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'note' => 'ダミー勤怠データ',
                ]);

                // 休憩作成
                AttendanceBreak::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $workDate->copy()->setTime(12, 0),
                    'break_end' => $workDate->copy()->setTime(13, 0),
                ]);

                // 5日おきに修正申請作成
                if ($i % 5 === 0) {
                    $correction = Correction::create([
                        'user_id' => $user->id,
                        'attendance_id' => $attendance->id,
                        'status' => 'pending',
                        'requested_clock_in' => $clockIn->copy()->subMinutes(15),
                        'requested_clock_out' => $clockOut->copy()->addMinutes(30),
                        'note' => 'ダミーデータ',
                    ]);

                    // 修正申請用の休憩作成
                    CorrectionBreak::create([
                        'correction_id' => $correction->id,
                        'break_start' => $workDate->copy()->setTime(12, 30),
                        'break_end' => $workDate->copy()->setTime(13, 30),
                    ]);
                }
            }
        }
    }
}