<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Correction;

class AdminCorrectionTest extends TestCase
{
    use RefreshDatabase;

    // 勤怠情報修正機能（管理者）
    public function test_承認待ちの修正申請が全て表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        Correction::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'note' => '承認待ち申請',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/stamp_correction_request/list');

        $response->assertStatus(200);

        $response->assertSee('承認待ち申請');
    }

    public function test_承認済みの修正申請が全て表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        Correction::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'approved',
            'note' => '承認済み申請',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);

        $response->assertSee('承認済み申請');
    }

    public function test_修正申請の詳細内容が表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        $correction = Correction::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'requested_clock_in' => today()->format('Y-m-d').' 09:00:00',
            'requested_clock_out' => today()->format('Y-m-d').' 18:00:00',
            'note' => '修正申請テスト',
        ]);

        $response = $this->actingAs($admin)
            ->get("/admin/stamp_correction_request/approve/{$correction->id}");

        $response->assertStatus(200);

        $response->assertSee('山田太郎');
        $response->assertSee('修正申請テスト');
    }

    public function test_修正申請の承認処理が正しく行われる()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => today()->format('Y-m-d').' 09:00:00',
            'clock_out' => today()->format('Y-m-d').' 18:00:00',
            'note' => '修正前',
        ]);

        $correction = Correction::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'requested_clock_in' => today()->format('Y-m-d').' 10:00:00',
            'requested_clock_out' => today()->format('Y-m-d').' 19:00:00',
            'note' => '修正後',
        ]);

        $this->actingAs($admin)
            ->post("/admin/stamp_correction_request/approve/{$correction->id}");

        $this->assertDatabaseHas('corrections', [
            'id' => $correction->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'note' => '修正後',
        ]);
    }
}
