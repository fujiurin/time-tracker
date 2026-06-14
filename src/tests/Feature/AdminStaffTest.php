<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    public function test_全一般ユーザーの氏名とメールアドレスが表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user1 = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'yamada@test.com',
            'role' => 'user',
        ]);

        $user2 = User::factory()->create([
            'name' => '佐藤花子',
            'email' => 'sato@test.com',
            'role' => 'user',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/staff/list');

        $response->assertStatus(200);

        $response->assertSee('山田太郎');
        $response->assertSee('yamada@test.com');

        $response->assertSee('佐藤花子');
        $response->assertSee('sato@test.com');
    }

    public function test_ユーザーの勤怠情報が正しく表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => today()->format('Y-m-d') . ' 09:00:00',
            'clock_out' => today()->format('Y-m-d') . ' 18:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get("/admin/attendance/staff/{$user->id}");

        $response->assertStatus(200);

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_前月の勤怠情報が表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->subMonth(),
            'clock_in' => '09:00:00',
        ]);

        $month = today()->subMonth()->format('Y-m');

        $response = $this->actingAs($admin)
            ->get("/admin/attendance/staff/{$user->id}?month={$month}");

        $response->assertStatus(200);

        $response->assertSee(
            today()->subMonth()->format('m/d')
        );
    }

    public function test_翌月の勤怠情報が表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->addMonth(),
            'clock_in' => '09:00:00',
        ]);

        $month = today()->addMonth()->format('Y-m');

        $response = $this->actingAs($admin)
            ->get("/admin/attendance/staff/{$user->id}?month={$month}");

        $response->assertStatus(200);

        $response->assertSee(
            today()->addMonth()->format('m/d')
        );
    }

    public function test_詳細を押すと勤怠詳細画面に遷移できる()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => '09:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
    }
}
