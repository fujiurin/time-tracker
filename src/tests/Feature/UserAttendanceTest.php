<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;

class UserAttendanceTest extends TestCase
{
    use RefreshDatabase;

    // ステータス確認機能
    public function test_勤務外ステータスが表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertSee('勤務外');
    }

    public function test_出勤中ステータスが表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertSee('出勤中');
    }

    public function test_休憩中ステータスが表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => now(),
            'break_end' => null,
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertSee('休憩中');
    }

    public function test_退勤済ステータスが表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
            'clock_out' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertSee('退勤済');
    }

    // 出勤機能
    public function test_出勤ボタンが正しく機能する()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->post('/attendance/start');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
        ]);
    }

    public function test_出勤は一日一回のみできる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->post('/attendance/start');

        $this->actingAs($user)
            ->post('/attendance/start');

        $this->assertEquals(
            1,
            Attendance::where('user_id', $user->id)
                ->where('work_date', today())
                ->count()
        );
    }

    public function test_出勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => '09:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);

        $response->assertSee('09:00');
    }

    // 休憩機能
    public function test_休憩ボタンが正しく機能する()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        $this->actingAs($user)
            ->post('/attendance/break/start');

        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendance->id,
        ]);
    }

    public function test_休憩は一日に何回でもできる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        $this->actingAs($user)->post('/attendance/break/start');
        $this->actingAs($user)->post('/attendance/break/start');

        $this->assertEquals(
            2,
            AttendanceBreak::where('attendance_id', $attendance->id)->count()
        );
    }

    public function test_休憩戻ボタンが正しく機能する()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        $break = AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => now(),
            'break_end' => null,
        ]);

        $this->actingAs($user)
            ->post('/attendance/break/end');

        $this->assertDatabaseHas('breaks', [
            'id' => $break->id,
        ]);

        $this->assertDatabaseMissing('breaks', [
            'id' => $break->id,
            'break_end' => null,
        ]);
    }

    public function test_休憩戻は一日に何回でもできる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        $this->actingAs($user)->post('/attendance/break/start');
        $this->actingAs($user)->post('/attendance/break/end');

        $this->actingAs($user)->post('/attendance/break/start');
        $this->actingAs($user)->post('/attendance/break/end');

        $this->assertEquals(
            2,
            AttendanceBreak::where('attendance_id', $attendance->id)->count()
        );
    }

    public function test_休憩時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => '10:00:00',
            'break_end' => '10:30:00',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);

        $response->assertSee('00:30');
    }

    // 退勤機能
    public function test_退勤ボタンが正しく機能する()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        $this->actingAs($user)
            ->post('/attendance/end');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        $this->assertNotNull(
            Attendance::where('user_id', $user->id)
                ->where('work_date', today())
                ->first()
                ->clock_out
        );
    }

    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);

        $response->assertSee('18:00');
    }

    // 勤怠一覧情報取得機能（一般ユーザー）
    public function test_自分の勤怠情報が全て表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->startOfMonth()->addDays(1),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_現在の月が表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);

        $response->assertSee(now()->format('Y/m'));
    }

    public function test_前月の勤怠情報が表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->subMonth()->startOfMonth()->addDay(),
            'clock_in' => '08:00:00',
            'clock_out' => '17:00:00',
        ]);

        $month = now()->subMonth()->format('Y-m');

        $response = $this->actingAs($user)
            ->get("/attendance/list?month={$month}");

        $response->assertStatus(200);

        $response->assertSee('08:00');
        $response->assertSee('17:00');
    }

    public function test_翌月の勤怠情報が表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->addMonth()->startOfMonth()->addDay(),
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $month = now()->addMonth()->format('Y-m');

        $response = $this->actingAs($user)
            ->get("/attendance/list?month={$month}");

        $response->assertStatus(200);

        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    public function test_勤怠詳細画面に遷移できる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}?date=" . today()->format('Y-m-d'));

        $response->assertStatus(200);
    }

    // 勤怠詳細情報取得機能（一般ユーザー）
    public function test_勤怠詳細画面の名前にユーザー名が表示される()
    {
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}?date=" . today()->format('Y-m-d'));

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
    }

    public function test_勤怠詳細画面に選択した日付が表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-14',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/detail/' . $attendance->id . '?date=2026-06-14');

        $response->assertSee('2026年');
        $response->assertSee('6月14日');
    }

    public function test_出勤退勤時間が正しく表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}?date=" . today()->format('Y-m-d'));

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_休憩時間が正しく表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}?date=" . today()->format('Y-m-d'));

        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
