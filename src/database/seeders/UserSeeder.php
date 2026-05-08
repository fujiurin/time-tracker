<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 管理者 1人
        User::factory()->create([
            'name' => '管理者',
            'email' => 'admin@test.com',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // 一般ユーザー 3人
        User::factory()->count(3)->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
    }
}
