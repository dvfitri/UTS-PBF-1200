<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        user::create([
            'name' => 'Administrator',
            'email' => 'admin@mail.com',
            'role' => 'admin',
            'password' => Hash::make('admin'),
        ]);

        // User::factory()->count(5)->create();
    }
}
