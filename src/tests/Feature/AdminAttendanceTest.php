<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    // 勤怠一覧情報取得機能（管理者）
    public function test_その日の全ユーザーの勤怠情報が表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user1 = User::factory()->create([
            'name' => '山田太郎',
            'role' => 'user',
        ]);

        $user2 = User::factory()->create([
            'name' => '佐藤花子',
            'role' => 'user',
        ]);

        Attendance::create([
            'user_id' => $user1->id,
            'work_date' => today(),
            'clock_in' => '09:00:00',
        ]);

        Attendance::create([
            'user_id' => $user2->id,
            'work_date' => today(),
            'clock_in' => '10:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list');

        $response->assertSee('山田太郎');
        $response->assertSee('佐藤花子');
    }

    public function test_現在の日付が表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list');

        $response->assertSee(today()->format('Y/m/d'));
    }

    public function test_前日の勤怠情報が表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'name' => '前日ユーザー',
            'role' => 'user',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->subDay(),
            'clock_in' => '09:00:00',
        ]);

        $date = today()->subDay()->format('Y-m-d');

        $response = $this->actingAs($admin)
            ->get("/admin/attendance/list?date={$date}");

        $response->assertSee('前日ユーザー');
    }

    public function test_翌日の勤怠情報が表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'name' => '翌日ユーザー',
            'role' => 'user',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->addDay(),
            'clock_in' => '09:00:00',
        ]);

        $date = today()->addDay()->format('Y-m-d');

        $response = $this->actingAs($admin)
            ->get("/admin/attendance/list?date={$date}");

        $response->assertSee('翌日ユーザー');
    }

    // 勤怠詳細情報取得・修正機能（管理者）
    public function test_勤怠詳細画面に選択した勤怠情報が表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
            'role' => 'user',
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'note' => 'テスト備考',
        ]);

        $response = $this->actingAs($admin)
            ->get("/admin/attendance/{$attendance->id}");

        $response->assertSee('山田太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_出勤時間が退勤時間より後の場合エラーになる()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $attendance = Attendance::create([
            'user_id' => $admin->id,
            'work_date' => today(),
        ]);

        $response = $this->actingAs($admin)
            ->put("/admin/attendance/{$attendance->id}", [
                'clock_in' => '18:00',
                'clock_out' => '09:00',
                'note' => 'テスト',
            ]);

        $response->assertSessionHasErrors([
            'clock_out'
        ]);
    }

    public function test_休憩開始時間が退勤時間より後の場合エラーになる()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $attendance = Attendance::create([
            'user_id' => $admin->id,
            'work_date' => today(),
        ]);

        $response = $this->actingAs($admin)
            ->put("/admin/attendance/{$attendance->id}", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => 'テスト',
                'breaks' => [
                    [
                        'break_start' => '19:00',
                        'break_end' => '19:30',
                    ]
                ]
            ]);

        $response->assertSessionHasErrors([
            'breaks.0.break_start'
        ]);
    }

    public function test_休憩終了時間が退勤時間より後の場合エラーになる()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $attendance = Attendance::create([
            'user_id' => $admin->id,
            'work_date' => today(),
        ]);

        $response = $this->actingAs($admin)
            ->put("/admin/attendance/{$attendance->id}", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => 'テスト',
                'breaks' => [
                    [
                        'break_start' => '17:00',
                        'break_end' => '19:00',
                    ]
                ]
            ]);

        $response->assertSessionHasErrors([
            'breaks.0.break_end'
        ]);
    }

    public function test_備考未入力の場合エラーになる()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $attendance = Attendance::create([
            'user_id' => $admin->id,
            'work_date' => today(),
        ]);

        $response = $this->actingAs($admin)
            ->put("/admin/attendance/{$attendance->id}", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => '',
            ]);

        $response->assertSessionHasErrors([
            'note'
        ]);
    }
}
