<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'user',        'display_name' => '前台會員'],
            ['name' => 'admin',       'display_name' => '後臺一般管理員'],
            ['name' => 'super_admin', 'display_name' => '後臺超級管理員'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
