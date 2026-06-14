<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Correction;

class UserCorrectionTest extends TestCase
{
    use RefreshDatabase;

    // 勤怠詳細情報修正機能（一般ユーザー）
    public function test_出勤時間が退勤時間より後の場合エラーになる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->post('/attendance/correction', [
                'work_date' => today(),
                'requested_clock_in' => '18:00',
                'requested_clock_out' => '09:00',
                'note' => 'テスト',
                'breaks' => [],
            ]);

        $response->assertSessionHasErrors([
            'requested_clock_out'
        ]);
    }

    public function test_休憩開始時間が退勤時間より後の場合エラーになる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->post('/attendance/correction', [
                'work_date' => today(),
                'requested_clock_in' => '09:00',
                'requested_clock_out' => '18:00',
                'note' => 'テスト',
                'breaks' => [
                    [
                        'break_start' => '19:00',
                        'break_end' => '19:30',
                    ]
                ],
            ]);

        $response->assertSessionHasErrors([
            'breaks.0.break_start'
        ]);
    }

    public function test_休憩終了時間が退勤時間より後の場合エラーになる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->post('/attendance/correction', [
                'work_date' => today(),
                'requested_clock_in' => '09:00',
                'requested_clock_out' => '18:00',
                'note' => 'テスト',
                'breaks' => [
                    [
                        'break_start' => '17:00',
                        'break_end' => '19:00',
                    ]
                ],
            ]);

        $response->assertSessionHasErrors([
            'breaks.0.break_end'
        ]);
    }

    public function test_備考未入力の場合エラーになる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->post('/attendance/correction', [
                'work_date' => today(),
                'requested_clock_in' => '09:00',
                'requested_clock_out' => '18:00',
                'note' => '',
                'breaks' => [],
            ]);

        $response->assertSessionHasErrors([
            'note'
        ]);
    }

    public function test_修正申請処理が実行される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->post('/attendance/correction', [
                'work_date' => today(),
                'requested_clock_in' => '09:00',
                'requested_clock_out' => '18:00',
                'note' => '修正申請',
                'breaks' => [],
            ]);

        $this->assertDatabaseHas('corrections', [
            'user_id' => $user->id,
            'note' => '修正申請',
            'status' => 'pending',
        ]);
    }

    public function test_承認待ちの申請が全て表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        Correction::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'note' => '承認待ちテスト',
            'status' => 'pending',
            'requested_clock_in' => now(),
            'requested_clock_out' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('承認待ちテスト');
    }

    public function test_承認済みの申請が全て表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        Correction::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'note' => '承認済みテスト',
            'status' => 'approved',
            'requested_clock_in' => now(),
            'requested_clock_out' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済みテスト');
    }

    public function test_詳細を押すと勤怠詳細画面に遷移できる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        $response = $this->actingAs($user)
            ->get(
                "/attendance/detail/{$attendance->id}?date="
                . today()->format('Y-m-d')
            );

        $response->assertStatus(200);
    }
}
