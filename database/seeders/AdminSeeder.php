<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'super_admin')->firstOrFail();
        $adminRole      = Role::where('name', 'admin')->firstOrFail();

        // 超級管理員
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        $superAdmin->roles()->syncWithoutDetaching([$superAdminRole->id]);

        // 一般管理員
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'      => 'Admin',
                'password'  => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);
    }
}
