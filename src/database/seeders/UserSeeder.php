<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 管理者
        User::factory()->create([
            'name' => '管理者',
            'email' => 'admin@test.com',
            'password' => Hash::make('admin111'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // 一般ユーザー1
        User::factory()->create([
            'name' => 'ユーザー1',
            'email' => 'user1@test.com',
            'password' => Hash::make('user1111'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        // 一般ユーザー2
        User::factory()->create([
            'name' => 'ユーザー2',
            'email' => 'user2@test.com',
            'password' => Hash::make('user2222'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        // 一般ユーザー3
        User::factory()->create([
            'name' => 'ユーザー3',
            'email' => 'user3@test.com',
            'password' => Hash::make('user3333'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
    }
}
