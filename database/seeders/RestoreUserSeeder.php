<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RestoreUserSeeder extends Seeder
{
    public function run()
    {
        $data = json_decode(file_get_contents('users_backup.json'), true);

        foreach ($data as $user) {

            // Tambahkan password default karena JSON tidak punya password
            $user['password'] = Hash::make('password123');

            // Jangan lupa unset field yang tidak boleh diisi manual
            unset($user['email_verified_at']);

            User::create($user);
        }
    }
}

