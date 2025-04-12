<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $adminRole = Role::where('slug', 'administrator')->first();

        User::create([
            'first_name' => 'Администратор',
            'last_name' => 'Системы',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role_id' => $adminRole->id,
            'position' => 'Главный администратор',
        ]);
    }
}
