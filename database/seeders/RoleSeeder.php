<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder {
    public function run(): void {
        $roles = [
            [
                'name' => 'Администратор',
                'slug' => 'administrator',
            ],
            [
                'name' => 'Руководитель',
                'slug' => 'manager',
            ],
            [
                'name' => 'Пользователь',
                'slug' => 'user',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
